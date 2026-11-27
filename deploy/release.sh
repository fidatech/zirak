#!/usr/bin/env bash
# Runs on the cPanel server for every deploy (called by .github/workflows/deploy.yml after the new
# release was unpacked into <app>/.incoming). On the first deploy it also sets up the server with
# cPanel's own "uapi" tool: MySQL database + user, .env, admin account, cron job, the domain's PHP
# version and document root. Nothing secret is ever printed: deploy logs may be public.
#
# Usage: release.sh <app folder, relative to home or absolute> <php binary> <domain>
set -euo pipefail

APP="$1"; PHP="$2"; DOMAIN="$3"
cd "$HOME"
cd "$APP"
APP_DIR="$(pwd -P)"
PHP_BIN="$(command -v "$PHP")"
HAS_UAPI=no; command -v uapi >/dev/null 2>&1 && HAS_UAPI=yes

# Reads uapi JSON from stdin; prints result.data[...path] (arrays as JSON). Fails with uapi's errors.
uapi_data() {
    "$PHP_BIN" -r '
        $d = json_decode(stream_get_contents(STDIN), true);
        $r = $d["result"] ?? [];
        if (empty($r["status"])) { fwrite(STDERR, "uapi: ".implode("; ", (array) ($r["errors"] ?? ["failed"]))."\n"); exit(1); }
        $v = $r["data"] ?? null;
        foreach (array_slice($argv, 1) as $k) { $v = is_array($v) ? ($v[$k] ?? null) : null; }
        echo is_array($v) ? json_encode($v) : (string) $v;
    ' -- "$@"
}

# ---------- 1. Swap in the new release ----------
if [ -f artisan ] && [ -f .env ]; then "$PHP_BIN" artisan down --retry=15 || true; fi
# Keep the SSL certificate check folder (AutoSSL / Let's Encrypt) across releases.
if [ -d public/.well-known ] && [ ! -e .incoming/public/.well-known ]; then mv public/.well-known .incoming/public/.well-known; fi
for item in $(ls -A .incoming); do
    rm -rf "./$item"
    mv ".incoming/$item" "./$item"
done
rmdir .incoming
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

# ---------- 2. First deploy: database and .env ----------
FIRST_DEPLOY=no
if [ ! -f .env ]; then
    FIRST_DEPLOY=yes
    [ "$HAS_UAPI" = yes ] || { echo "::error::No .env on the server and cPanel's uapi is not available to create the database. Add the DB_DATABASE, DB_USERNAME and DB_PASSWORD secrets instead."; exit 1; }
    echo "First deploy: creating the MySQL database with cPanel..."
    PREFIX="$(uapi --output=json Mysql get_restrictions | uapi_data prefix)"
    DB_NAME="${PREFIX}zirak"
    DB_USER="${PREFIX}zirak"
    DB_PASS="$("$PHP_BIN" -r 'echo bin2hex(random_bytes(16));')Zr9"

    if uapi --output=json Mysql list_databases | uapi_data | grep -q "\"database\":\"${DB_NAME}\""; then
        echo "Database ${DB_NAME} already exists; reusing it."
    else
        uapi --output=json Mysql create_database name="$DB_NAME" | uapi_data >/dev/null
        echo "Created database ${DB_NAME}."
    fi
    if uapi --output=json Mysql list_users | uapi_data | grep -q "\"user\":\"${DB_USER}\""; then
        uapi --output=json Mysql set_password user="$DB_USER" password="$DB_PASS" | uapi_data >/dev/null
        echo "Database user ${DB_USER} already existed; gave it a new password."
    else
        uapi --output=json Mysql create_user name="$DB_USER" password="$DB_PASS" | uapi_data >/dev/null
        echo "Created database user ${DB_USER}."
    fi
    uapi --output=json Mysql set_privileges_on_database user="$DB_USER" database="$DB_NAME" privileges="ALL PRIVILEGES" | uapi_data >/dev/null

    umask 077
    D_DB_NAME="$DB_NAME" D_DB_USER="$DB_USER" D_DB_PASS="$DB_PASS" D_SITE="https://${DOMAIN}" \
        "$PHP_BIN" deploy/make-env.php .env.example > .env.new
    mv .env.new .env
    chmod 600 .env
    umask 022
    unset DB_PASS
    echo "Created .env (production, MySQL, new APP_KEY)."
fi

# ---------- 3. Database tables and caches ----------
grep -qE '^APP_KEY=.+' .env || "$PHP_BIN" artisan key:generate --force
"$PHP_BIN" artisan down --retry=15 || true
"$PHP_BIN" artisan migrate --force
[ -L public/storage ] || "$PHP_BIN" artisan storage:link
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan optimize

# ---------- 4. First deploy: admin account ----------
if [ "$FIRST_DEPLOY" = yes ]; then
    ADMIN_EMAIL="admin@${DOMAIN}"
    ADMIN_PASS="$("$PHP_BIN" -r 'echo substr(strtr(base64_encode(random_bytes(18)), "+/", "xy"), 0, 20);')"
    "$PHP_BIN" artisan zirak:admin "$ADMIN_EMAIL" --password="$ADMIN_PASS" >/dev/null
    umask 077
    printf 'Zirak admin login\n\nURL:      https://%s/admin\nEmail:    %s\nPassword: %s\n\nChange the password after logging in, then delete this file.\n' \
        "$DOMAIN" "$ADMIN_EMAIL" "$ADMIN_PASS" > "$HOME/zirak-admin-login.txt"
    umask 022
    unset ADMIN_PASS
    echo "Created the admin account ${ADMIN_EMAIL}; its password is in ~/zirak-admin-login.txt (readable only by the cPanel user)."
fi

# ---------- 5. Cron job (scheduler: queue, reminders, video checks) ----------
CRON_LINE="* * * * * cd ${APP_DIR} && ${PHP_BIN} artisan schedule:run >> /dev/null 2>&1"
if command -v crontab >/dev/null 2>&1; then
    CURRENT="$(crontab -l 2>/dev/null || true)"
    if ! printf '%s\n' "$CURRENT" | grep -qF "$CRON_LINE"; then
        { printf '%s\n' "$CURRENT" | grep -v "${APP_DIR} && .* artisan schedule:run" | sed '/^$/d'; echo "$CRON_LINE"; } | crontab -
        echo "Cron job added: every minute, php artisan schedule:run."
    fi
else
    echo "::warning::crontab is not available. Add this cron job in cPanel → Cron Jobs: ${CRON_LINE}"
fi

# ---------- 6. The domain: website PHP version and document root ----------
if [ "$HAS_UAPI" = yes ]; then
    # Website PHP (can differ from the command-line PHP): needs 8.3+.
    CURRENT_PHP="$(uapi --output=json LangPHP php_get_vhost_versions | "$PHP_BIN" -r '
        $d = json_decode(stream_get_contents(STDIN), true);
        foreach ($d["result"]["data"] ?? [] as $v) { if (($v["vhost"] ?? "") === $argv[1]) { echo $v["version"] ?? ""; } }
    ' -- "$DOMAIN" || true)"
    if [ -n "$CURRENT_PHP" ] && ! printf '%s' "$CURRENT_PHP" | grep -qE 'php8[3-9]$'; then
        BEST="$(uapi --output=json LangPHP php_get_installed_versions | "$PHP_BIN" -r '
            $d = json_decode(stream_get_contents(STDIN), true);
            $vs = array_filter($d["result"]["data"]["versions"] ?? [], fn ($v) => preg_match("/^ea-php(8[3-9])$/", $v));
            rsort($vs); echo $vs[0] ?? "";
        ' || true)"
        if [ -n "$BEST" ]; then
            uapi --output=json LangPHP php_set_vhost_versions vhost="$DOMAIN" version="$BEST" | uapi_data >/dev/null \
                && echo "Website PHP for ${DOMAIN}: ${CURRENT_PHP} → ${BEST}." \
                || echo "::warning::Could not switch the website PHP. In cPanel → MultiPHP Manager choose PHP 8.3 or newer for ${DOMAIN}."
        else
            echo "::warning::${DOMAIN} uses ${CURRENT_PHP} on the web and no PHP 8.3+ is installed for websites. Ask the host to add ea-php83 or ea-php84."
        fi
    fi

    # Document root → <app>/public. Whatever was there is moved to a dated backup, never deleted.
    DOCROOT="$(uapi --output=json DomainInfo single_domain_data domain="$DOMAIN" | uapi_data documentroot || true)"
    if [ -z "$DOCROOT" ]; then
        echo "::warning::cPanel does not list ${DOMAIN} on this account. Add it in cPanel → Domains with document root ${APP_DIR}/public."
    elif [ "$(readlink -f "$DOCROOT" 2>/dev/null || true)" = "${APP_DIR}/public" ]; then
        echo "${DOMAIN} already serves ${APP_DIR}/public."
    else
        if [ -L "$DOCROOT" ]; then
            rm "$DOCROOT"
        elif [ -e "$DOCROOT" ]; then
            BACKUP="${DOCROOT}.before-zirak-$(date +%Y%m%d-%H%M%S)"
            mv "$DOCROOT" "$BACKUP"
            echo "Moved the old contents of ${DOCROOT} to ${BACKUP}."
            if [ -d "$BACKUP/.well-known" ] && [ ! -e public/.well-known ]; then cp -a "$BACKUP/.well-known" public/.well-known; fi
        fi
        ln -s "${APP_DIR}/public" "$DOCROOT"
        echo "${DOMAIN} now serves ${APP_DIR}/public (${DOCROOT} → ${APP_DIR}/public)."
    fi
fi

"$PHP_BIN" artisan up
echo "Deployed $(date -u +%FT%TZ) into ${APP_DIR}."
