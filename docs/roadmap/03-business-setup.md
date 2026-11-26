# 03 — Business setup (weeks 1–2)

## 1. Brand

- [ ] Choose the name. Ideas: **Hoosh Academy** (هوش), **Pohana AI** (پوهنه), **Danesh AI Institute** (دانش). Pick one that works in both Pashto and Dari and in Latin letters.
- [ ] Check that the `.com` domain is free, plus `.af` if possible, and the same handle on YouTube, Facebook, Telegram, TikTok, Instagram and X.
- [ ] Logo: a simple mark that works at 48×48 px (app icon) and in one color.
- [ ] Mascot / faceless presenter: an illustrated character used on thumbnails and intros. This is your "face".
- [ ] Brand colors: 1 primary, 1 accent, plus neutrals. Check contrast on cheap phone screens.
- [ ] Fonts: a Pashto/Dari-capable font (e.g. Vazirmatn, Noto Naskh Arabic or Bahij fonts) + a Latin font. Confirm the font shows the Pashto letters ټ ځ څ ډ ړ ږ ښ ګ ڼ ۍ ې ي correctly.
- [ ] Voice: record your intro line in both languages ("Assalamu alaikum, welcome to …").

## 2. Legal entity and money

### US LLC (for Stripe and international income)
- [ ] Confirm the LLC is in good standing, has an EIN, and has a US business bank account (Mercury, Relay or a traditional bank).
- [ ] Apply for Stripe with the LLC. Describe the business clearly: "Online courses and certification in AI skills."
- [ ] Read Stripe's restricted-business and high-risk-jurisdiction rules **before** applying. Stripe restricts dealings with sanctioned jurisdictions under US OFAC rules, and its list changes. Use Stripe for students and sponsors **outside Afghanistan**, and HesabPay for payments inside Afghanistan. Ask a US accountant or lawyer to confirm this setup is compliant for your case.
- [ ] Set up bookkeeping (Wave, QuickBooks or Xero) and keep the two income streams separate (HesabPay AFN, Stripe USD).

### Inside Afghanistan (for HesabPay)
- [ ] HesabPay **business/merchant account**. It requires business documents. If you are outside Afghanistan, a registered local partner or local business usually holds it. Agree in writing on how funds are held and transferred.
- [ ] After approval, open the HesabPay Developer Portal and create sandbox + live API keys (details in file 09).
- [ ] Ask a local lawyer whether an online education business needs a license or registration with any ministry. Rules change, so document the answer.

## 3. Legal pages (needed before any payment)

- [ ] Terms of Service (accounts, payments, acceptable use, certificates, termination)
- [ ] Privacy Policy (what data, why, how long, no selling, student safety)
- [ ] Refund Policy (e.g. full refund within 7 days if less than 20% of a course is watched)
- [ ] Certificate Policy (what each certificate means, how it can be revoked for cheating)
- [ ] Instructor Agreement (later, for the marketplace — see `templates/instructor-agreement-outline.md`)
- [ ] All pages in English, Pashto and Dari.

## 4. Team (start small)

| Role | When | Type | Notes |
|---|---|---|---|
| You | Now | Founder, Laravel developer, voice | Product, LMS, recording |
| Pashto editor | Week 1 | Part-time, paid per lesson | Reviews every script and subtitle |
| Dari editor | Week 1 | Part-time, paid per lesson | Same |
| Video editor/assistant | Month 3 | Part-time | Edits, thumbnails, uploads |
| Female community moderator | Month 3 | Part-time | Runs women's groups, safety |
| Support agent (Telegram/WhatsApp) | Month 4 | Part-time | Payment help, questions |
| Payment/sales agents | Month 4 | Commission | One per city |
| Subject experts (health, law, finance) | Per track | Per review | Check the professional tracks |

## 5. Tools to sign up for

| Tool | Use |
|---|---|
| Claude (Pro or Max) | Scripts, outlines, quizzes, code with Claude Code |
| ElevenLabs | Voice clone (test Pashto first) |
| HeyGen (optional) | Avatar intros |
| Canva Pro | Thumbnails, slides, social posts |
| CapCut / DaVinci Resolve | Video editing |
| OBS Studio | Screen recording |
| Bunny.net (Stream + Storage) | Video hosting |
| GitHub | Code |
| Laravel Forge or Ploi + a VPS (Hetzner/DigitalOcean) | Hosting |
| Google Workspace or Zoho Mail | Email on your domain |
| Notion or Trello | Content pipeline tracking |

## Checklist to finish this stage
- [ ] Name, logo, colors, mascot
- [ ] Domain and all social handles reserved
- [ ] HesabPay merchant application submitted
- [ ] Stripe application submitted from the LLC
- [ ] Two editors hired
- [ ] Legal pages drafted
