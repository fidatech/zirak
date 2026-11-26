# 06 — Database schema

Key tables and columns. All tables have `id`, `created_at`, `updated_at`; important ones also `deleted_at` (soft deletes). Money = integer minor units + `currency` (`AFN`, `USD`).

## Identity
```
users                id, name, nickname, phone (unique, nullable), email (unique, nullable),
                     password (nullable), locale [ps|prs|en], country, gender (nullable, optional),
                     is_private (bool, default true), avatar_path, last_active_at, referred_by_id
otp_codes            id, phone, code_hash, attempts, expires_at, used_at
roles / permissions  (spatie/laravel-permission)
student_profiles     user_id, level_placed, goals (json), learning_minutes_total, streak_days
```

## Catalog
```
categories           id, slug, name (json translations), parent_id, icon, sort
levels               id, number (0–5), slug, name (json), description (json)
tracks               id, slug, name (json), industry, min_level_id
courses              id, slug, title, subtitle, description, language [ps|prs|en],
                     translation_group_id (uuid, links Pashto/Dari versions),
                     level_id, track_id (nullable), category_id, instructor_id (user),
                     owner_type [institute|instructor], status [draft|in_review|published|archived],
                     price_afn, price_usd, is_free, certificate_type [completion|achievement|none],
                     duration_minutes, thumbnail_path, trailer_video_id, outcomes (json),
                     requirements (json), published_at
course_sections      id, course_id, title, sort
lessons              id, section_id, course_id, title, type [video|text|quiz|assignment|live],
                     bunny_video_id, duration_seconds, audio_path, body (longtext, markdown),
                     prompts (json), attachment_path, is_preview, is_downloadable, sort
programs (P4)        id, slug, title (json), description, price_afn, price_usd, duration_weeks
program_courses      program_id, course_id, sort, is_required
```

## Learning
```
enrollments          id, user_id, course_id (or program_id), source [free|order|voucher|org|scholarship],
                     order_id, status [active|completed|expired|refunded], started_at, completed_at,
                     expires_at (nullable), progress_percent
lesson_progress      id, user_id, lesson_id, enrollment_id, status [started|completed],
                     seconds_watched, last_position, completed_at, synced_from [web|app|offline]
notes                id, user_id, lesson_id, body
study_circles        id, name, course_id, language, is_women_only, leader_id, telegram_link
study_circle_members circle_id, user_id, role
```

## Assessment
```
question_banks       id, course_id, title
questions            id, bank_id, type [single|multiple|true_false|short], body, options (json),
                     correct (json), explanation, difficulty, tags (json)
quizzes              id, lesson_id (nullable), course_id, kind [lesson_quiz|final_exam|placement],
                     question_count, pass_percent, time_limit_minutes, max_attempts, shuffle (bool)
quiz_attempts        id, quiz_id, user_id, questions_snapshot (json), answers (json), score,
                     passed, started_at, submitted_at, ip, user_agent
assignments          id, lesson_id, title, instructions, rubric (json), max_score
submissions          id, assignment_id, user_id, body, file_path, link, status [submitted|graded|returned],
                     score, feedback, graded_by, graded_at
```

## Certification
```
certificate_templates id, name, type [completion|achievement|diploma], language, layout (json), background_path
certificates          id, code (unique, e.g. HA-2027-7K3P9Q), user_id, course_id/program_id,
                      template_id, name_on_certificate, language, score, issued_at,
                      status [valid|revoked], revoked_reason, pdf_path, verification_hash
```

## Commerce & payments
```
coupons              id, code, type [percent|fixed], value, currency, max_uses, used_count,
                     starts_at, ends_at, course_ids (json, nullable), is_scholarship, reason
vouchers             id, code (printable), product_type, product_id, batch_id, agent_id,
                     redeemed_by, redeemed_at, status
voucher_batches      id, agent_id, quantity, price_afn, created_by
orders               id, number, user_id, status [pending|paid|failed|refunded|cancelled],
                     currency, subtotal, discount, total, coupon_id, gateway [hesabpay|stripe|voucher|manual],
                     paid_at, country, meta (json)
order_items          id, order_id, product_type [course|program|membership|exam_fee|book], product_id,
                     price, instructor_id (nullable), instructor_share, platform_share
payments             id, order_id, gateway, gateway_session_id, gateway_transaction_id,
                     amount, currency, status, raw_payload (json), received_at
gateway_events       id, gateway, event_id (unique), type, payload (json), processed_at
memberships          id, user_id, plan [monthly|yearly], status, current_period_end, gateway, gateway_ref
refunds              id, payment_id, amount, reason, status, processed_by
```

## Marketplace (P3)
```
instructor_profiles     user_id, display_name, bio (json), expertise, languages, payout_method,
                        payout_details (encrypted), status [applied|approved|suspended], revenue_share_percent
instructor_applications id, user_id, sample_lesson_url, experience, status, reviewer_id, notes
course_reviews          id, course_id, reviewer_id, status [changes_requested|approved|rejected], checklist (json), notes
ratings                 id, course_id, user_id, stars, comment, is_visible
earnings                id, instructor_id, order_item_id, amount, currency, available_at, payout_id
payouts                 id, instructor_id, amount, currency, method, status, reference, paid_at
```

## Organizations (P3)
```
organizations        id, name, contact_user_id, seats, contract_ends_at
organization_members organization_id, user_id, role [admin|learner]
organization_courses organization_id, course_id
```

## Engagement & system
```
questions_answers    id, lesson_id, user_id, parent_id, body, is_answered, is_hidden
notifications        (Laravel default)
points_ledger        id, user_id, points, reason, ref_type, ref_id
badges / user_badges
ai_tutor_threads     id, user_id, course_id, language
ai_tutor_messages    id, thread_id, role, content, tokens, created_at
agents               id, user_id, city, commission_percent, status
telegram_accounts    user_id, telegram_user_id, chat_id, linked_at
settings             key, value (json)
activity_log         (spatie)
```

## Indexes to add from day one
- `lesson_progress (user_id, lesson_id)` unique
- `enrollments (user_id, course_id)` unique
- `certificates (code)` unique
- `gateway_events (gateway, event_id)` unique
- `courses (language, status, level_id)`
