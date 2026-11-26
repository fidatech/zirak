# Deploying Zirak to cPanel (with SSH)

These steps take about 20–30 minutes the first time. Replace `cpuser` with your cPanel username and `zirak.af` with your domain.

## 1. Check the server

In cPanel:

1. **MultiPHP Manager** → set the domain to **PHP 8.3** (or newer).
2. **Select PHP Version → Extensions** (or MultiPHP INI) → make sure these are on: `intl`, `mbstring`, `pdo_mysql`, `gd`, `zip`, `fileinfo`, `openssl`, `curl`, `bcmath`.
3. **SSL/TLS Status** → run **AutoSSL** so the site uses `https://`.

Over SSH, check the command-line PHP version, because it can differ from the website's:

```bash
php -v
# If it is older than 8.3, use the full path, for example:
/opt/cpanel/ea-php83/root/usr/bin/php -v
```

If `php` is too old, use that full path (or add `alias php=/opt/cpanel/ea-php83/root/usr/bin/php` to `~/.bashrc`) for every command below.

Composer: run `composer -V`. If it is missing:

```bash
cd ~ && curl -sS https://getcomposer.org/installer | php && mkdir -p ~/bin && mv composer.phar ~/bin/composer
```

## 2. Create the database

cPanel → **MySQL Databases**:

1. Create a database, e.g. `cpuser_zirak`.
2. Create a user, e.g. `cpuser_zirak`, with a strong password.
3. Add the user to the database with **ALL PRIVILEGES**.

## 3. Get the code

The repository should be **private**. Give the server read access:

```bash
ssh-keygen -t ed25519 -C "zirak-server" -f ~/.ssh/zirak_deploy -N ""
cat ~/.ssh/zirak_deploy.pub
```

Add that public key in GitHub → repository **Settings → Deploy keys** (read-only). Then:

```bash
cat >> ~/.ssh/config <<'CFG'
Host github-zirak
  HostName github.com
  User git
  IdentityFile ~/.ssh/zirak_deploy
CFG

cd ~ && git clone git@github-zirak:fidatech/zirak.git zirak
cd ~/zirak
composer install --no-dev --optimize-autoloader
```

## 4. Point the domain at `public/`

Laravel must be served from its `public` folder, never from the project root.

- **Addon domain or subdomain:** cPanel → **Domains** → Manage → set **Document Root** to `zirak/public`.
- **Main domain (document root fixed to `public_html`):** replace `public_html` with a link:

  ```bash
  mv ~/public_html ~/public_html.bak
  ln -s ~/zirak/public ~/public_html
  ```

## 5. Configure `.env`

```bash
cd ~/zirak
cp .env.example .env
php artisan key:generate
nano .env
```

Set at least:

```dotenv
APP_NAME=Zirak
APP_ENV=production
APP_DEBUG=false
APP_URL=https://zirak.af

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpuser_zirak
DB_USERNAME=cpuser_zirak
DB_PASSWORD=your-password

SESSION_SECURE_COOKIE=true

SMS_DRIVER=log            # until an SMS provider is added
HESABPAY_DRIVER=sandbox   # then "live" after testing
HESABPAY_API_KEY=your-hesabpay-key
BUNNY_LIBRARY_ID=         # when you start uploading videos
```

`HESABPAY_DRIVER=fake` is refused in production on purpose.

## 6. Set up the database and admin

```bash
php artisan migrate --force
php artisan db:seed --class=DemoContentSeeder --force   # optional: demo subjects/courses (re-running resets demo course content)
php artisan zirak:admin you@zirak.af                       # prints a generated password
php artisan storage:link
php artisan filament:assets
php artisan optimize
```

Open `https://zirak.af` and `https://zirak.af/admin`.

## 7. Cron (scheduler + queue)

cPanel → **Cron Jobs** → add, every minute (`* * * * *`):

```bash
cd ~/zirak && php artisan schedule:run >> /dev/null 2>&1
```

(Use the full PHP path from step 1 if needed.) This also processes the queue every minute.

## 8. HesabPay

1. In the HesabPay developer dashboard, set the **webhook URL** to `https://zirak.af/webhooks/hesabpay`.
2. Add and verify `zirak.af` as an allowed domain. Production checkout requires a verified domain, and the success/failure pages must be on it.
3. Buy a course with a real small amount in `sandbox`, then switch to `HESABPAY_DRIVER=live` and run `php artisan optimize` again.
4. If a student pays but the webhook never arrives, check **Admin → Orders**, confirm the payment in the HesabPay dashboard, and use **Mark paid**.

## 9. Updating the site later

```bash
cd ~/zirak
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan filament:assets
php artisan optimize:clear && php artisan optimize
php artisan up
```

Front-end files are pre-built in `public/build`, so the server never needs Node.

## Troubleshooting

| Problem | Fix |
|---|---|
| 500 error right after deploy | `tail -50 storage/logs/laravel.log`; check `.env` database details; run `php artisan optimize:clear` |
| Page without styles | Make sure `public/build` exists (it is in git) and the document root is `zirak/public` |
| "Permission denied" on storage | `chmod -R 775 storage bootstrap/cache` |
| Admin panel looks broken | `php artisan filament:assets` |
| Login code never arrives | Normal with `SMS_DRIVER=log`; the code is in `storage/logs/laravel.log` until an SMS provider is connected |

## Automatic deploys from GitHub (recommended)

After the first manual setup (steps 1–2 and 4–7: server, database, document root, `.env`, admin, cron), every merge to `main` can update the live site by itself. GitHub runs the tests, builds the production files, uploads them to your server over SSH, runs the database migrations and refreshes the caches. If anything fails, the site is put back online and nothing else changes.

With automatic deploys you can skip `git clone` in step 3: GitHub uploads the files. Create the folder first: `mkdir ~/zirak`, put `.env` in it (step 5), and the first deploy fills in the rest. Run step 6 (migrations, admin) after that first deploy.

**1. Create a key for GitHub** (on your own computer, not the server):

```bash
ssh-keygen -t ed25519 -C "github-deploy-zirak" -f zirak_github_deploy -N ""
```

**2. Allow it on the server:** cPanel → **SSH Access** → Manage SSH Keys → **Import Key** → paste the contents of `zirak_github_deploy.pub` → then **Authorize** it.

**3. Add the secrets on GitHub:** repository → **Settings → Secrets and variables → Actions → New repository secret**:

| Secret | Example |
|---|---|
| `CPANEL_SSH_HOST` | `server123.yourhost.com` |
| `CPANEL_SSH_USER` | `cpuser` |
| `CPANEL_SSH_KEY` | the whole contents of the private file `zirak_github_deploy` |
| `CPANEL_APP_PATH` | `/home/cpuser/zirak` |
| `CPANEL_SSH_PORT` | only if your host does not use 22 (many use 2222 or 21098) |
| `CPANEL_PHP` | only if `php -v` on the server is older than 8.3, e.g. `/opt/cpanel/ea-php83/root/usr/bin/php` |

Then delete the key files from your computer, or keep them somewhere safe.

**4. Deploy:** merge a pull request into `main`, or run it by hand: **Actions → deploy → Run workflow**. Watch progress in the Actions tab.

Until the secrets exist, the workflow only prints a note and changes nothing.
