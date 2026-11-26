# 16 — Telegram: channel, groups and bot

Telegram is light on data and widely used, so it is your daily classroom and support desk.

## Structure

| Telegram space | Type | Purpose |
|---|---|---|
| **@YourBrand** | Public channel | Daily AI tip, new lessons, announcements (both languages, tagged) |
| **@YourBrand_ps** / **@YourBrand_prs** | Optional language channels | Once posting volume is high |
| **Community group (Pashto)** / **(Dari)** | Public groups with topics enabled | Questions, sharing results. Topics: Introductions, Questions, Show your work, Jobs & freelance |
| **Women-only study groups** | Private, invite via LMS only | Female moderator, no phone numbers visible (Telegram privacy settings), strict rules |
| **Course study circles** | Private groups per cohort | Linked from the LMS course page |
| **@YourBrandBot** | Bot | Account linking, reminders, quizzes, support, AI tutor lite |
| **Support** | Bot menu → human agent | Payment and account help |

## Bot features (build in Sprint 6; Laravel + nutgram)

| Command / feature | What it does | Phase |
|---|---|---|
| `/start` | Choose language, short intro, buttons: Free courses · My learning · Help | MVP |
| Link account | One-time code from the LMS profile → links the Telegram user to the LMS user | MVP |
| Daily reminder | "Your next lesson: … (8 min)" with an Open button (deep link into the app) | MVP |
| Lesson quiz in chat | Telegram quiz polls with explanations; result saved to the LMS | P2 |
| New lesson / course alerts | Based on enrolled courses and level | MVP |
| Payment status | "Your payment was received, the course is unlocked" | P2 |
| `/certificate` | Lists certificates with verify links | P2 |
| `/ask` | AI tutor lite: questions about the current course, in their language | P2 |
| `/help` | FAQ buttons + "Talk to a human" | MVP |
| Channel auto-post | When a lesson is published on YouTube/LMS, post it to the channel | P2 |
| Telegram Mini App | Opens the LMS inside Telegram | P3 |

## Group rules (pin in every group, both languages)
1. Respect everyone; no politics, no religious arguments, no insults.
2. No sharing phone numbers or personal details; no private messages to members without consent.
3. Stay on topic: AI, learning, work.
4. No ads or links without admin approval.
5. Breaking the rules → warning → removal.

## Safety settings
- Hide the member list in private groups where possible; admins only can add members
- Slow mode in large groups
- Anti-spam bot (e.g. Combot or Group Help) + your own moderators
- Women-only groups: female admins only; verified invite links from the LMS only

## Channel posting plan (daily)
| Time | Post |
|---|---|
| Morning | AI tip of the day (image + 3 lines + prompt to copy) |
| Afternoon | New lesson / Short link |
| Evening (2–3×/week) | Quiz poll, student work, course announcement |
