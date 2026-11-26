# 10 — Certificates and exams

A certificate is only worth something if people trust it. Trust comes from three things: a **real assessment**, **online verification**, and **clear wording** about what it proves.

## Certificate types

| Type | Name on certificate | Requirement | Where used |
|---|---|---|---|
| Completion | Certificate of Completion | All required lessons completed | Short courses, Level 0 |
| Achievement | Certificate of Achievement | All lessons + final exam score of 70% or more | Levels 1–2, industry tracks |
| Professional | Professional Certificate | Achievement + graded project(s) reviewed by a mentor | Level 3, professional tracks |
| Diploma | Diploma in … (program) | All program courses + capstone project + exam of 75% or more | Levels 4–5, programs |
| Instructor-issued (marketplace) | "Issued by [instructor] on [platform]" | Instructor's rules within platform minimums | Marketplace courses |

## What every certificate shows
- Name as the student chose it (they confirm the spelling once; it cannot change after issue without admin)
- Course or program title, level, language of study, total hours
- Score/grade (Achievement and above)
- Issue date and certificate code (e.g. `HA-2027-7K3P9Q`)
- QR code → `https://yourdomain.com/verify/HA-2027-7K3P9Q`
- Signature of the academic director + institute seal
- For instructor courses: the instructor's name

The certificate is available in Pashto, Dari and English versions, so students can use the English one abroad.

## Verification page
- Public, no login: enter a code or scan the QR
- Shows: valid/revoked, name, course, type, date, score (if the student allows it)
- Rate limited, so codes cannot be guessed in bulk
- Later: **Open Badges 3.0** export, so certificates can be imported into digital wallets, and an "Add to LinkedIn" button (LinkedIn's add-to-profile URL fills in the certificate name, organization, issue date, ID and URL)

## Exam integrity (lightweight and fair)
- Random questions from a bank at least 3× the exam length
- Shuffled options, time limit, a maximum of 3 attempts with a cooldown (e.g. 24 h)
- Record IP/device per attempt; flag impossible patterns (e.g. 40 questions in 2 minutes)
- Practical project for Professional/Diploma: work that must be explained, graded against a rubric
- Optional oral check over a voice call for diplomas
- Clear rule: cheating → certificate revoked, shown as "revoked" on the verify page

## Naming and legal (important)
- Do **not** use "university", "degree", "bachelor", "master's degree" or "accredited" unless you hold that status. In the US and many countries those words are regulated, and misuse can create legal trouble for your LLC.
- Safe words: **Institute, Academy, Certificate, Professional Certificate, Diploma (non-degree), Program**.
- Your "Level 5 – AI Master" should be written as "AI Master Program" or "Advanced AI Specialist Diploma" on certificates, not "Master's".
- Add a line on the verification page: "Non-degree professional certificate issued by [Institute name]."
- Credibility you can build: partnerships with employers who accept your certificates, recognition letters from known organizations, and later credit agreements with accredited universities.

## Certificate design checklist
- [ ] Templates for each type × 3 languages
- [ ] Pashto/Dari text shaped correctly in the PDF (test ټ ځ څ ډ ړ ږ ښ ګ ڼ ۍ ې)
- [ ] Prints well in black and white
- [ ] QR scans from a printed copy
- [ ] PDF smaller than 300 KB
