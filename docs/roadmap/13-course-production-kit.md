# 13 — Course production kit

Everything needed to produce courses, from the first outline to the printed book. One script feeds four outputs: video, audio, textbook chapter, and quiz.

## A. Before you record anything (once)

- [ ] **Glossary** of about 500 AI terms in English / Dari / Pashto (`templates/glossary-starter.csv` has the first 30, as drafts for your editors). Every script must use the glossary words.
- [ ] **Style guide** (one page): formal or friendly tone (recommend friendly-respectful), how to address the student, how to write English tool names (keep the original: "ChatGPT", "Claude"), numbers (Eastern Arabic ۱۲۳ vs Western 123 — pick one per language and stick to it), units, examples.
- [ ] **Slide template** in Canva/Google Slides: RTL, brand colors, title slide, step slide, prompt slide, summary slide, end card.
- [ ] **Video intro (5 s) and outro (10 s)** with your mascot and jingle.
- [ ] **Recording setup:** USB mic, quiet room, soft materials on the walls, phone screen mirroring (scrcpy for Android, free) and OBS.
- [ ] **Voice decision:** record a 2-minute test in your real voice and the same text as a voice clone (ElevenLabs); play both to 5 native listeners in each language and pick based on their reaction.
- [ ] **Folder structure** (see section F).

## B. The pipeline for each course

| Step | Who | Tool | Output |
|---|---|---|---|
| 1. Course outline | You + AI | Claude, `templates/course-outline-template.md` | Outcomes, modules, lessons, project |
| 2. Lesson scripts (Dari first) | AI drafts, you guide | Claude, `templates/lesson-script-template.md` | 1 script per lesson |
| 3. Native review | Dari editor | Google Docs (suggestions) | Corrected Dari script |
| 4. Pashto version | AI adapts from the Dari script + Pashto editor rewrites | Claude + editor | Pashto script (not a literal translation) |
| 5. Slides / screen plan | You | Canva / screen recording | Visuals per step |
| 6. Record | You (or voice clone) | OBS, scrcpy, mic / ElevenLabs | Raw video + clean audio |
| 7. Edit | You / video editor | CapCut / DaVinci Resolve | Final video with subtitles |
| 8. Export | Editor | Handbrake / CapCut | 1080p master → Bunny makes 240–720p; separate MP3/Opus audio (64 kbps mono) |
| 9. Quiz | AI drafts, editor checks | `templates/quiz-template.csv` | 3–5 questions per lesson + exam bank |
| 10. Book chapter | AI formats the script | Word/Affinity template | Chapter with prompts, exercises, QR |
| 11. Upload + QA | You | LMS admin | Published lesson; checked on a phone |
| 12. Social clips | Editor | CapCut | 2–3 short clips per lesson for YouTube Shorts/TikTok/Reels |

## C. Lesson rules
- 5–12 minutes; one skill per lesson
- Start with a real Afghan situation; end with "Now you do it"
- Show the phone screen for beginner levels (most students use phones)
- Say every prompt aloud AND put it in the copy box
- No talking-head filler; if nothing is happening on screen, cut it
- Subtitles in the same language, plus English for technical words

## D. Prompts for producing content with Claude

**Course outline**
```
You are a curriculum designer for an online AI institute teaching Afghan learners in Dari and Pashto.
Create a course outline for: [COURSE NAME], Level [N], for [AUDIENCE].
Include: 4–6 measurable outcomes; 4–8 modules; 3–6 lessons per module (5–12 min each);
for each lesson a one-line goal, the hands-on task, and the tool used; one practice project per module;
a final project; prerequisites. Use examples from Afghan daily life and work (shops, farming, trade,
teaching, freelancing). Mobile-first: assume students use Android phones with slow internet.
Output as a table.
```

**Lesson script (Dari first)**
```
Write a lesson script in Dari (Afghan Dari, not Iranian Farsi vocabulary) for lesson [X] of [COURSE].
Goal: [GOAL]. Length: about [N] minutes spoken. Follow this structure:
Hook (real Afghan situation) → What you will learn → Step-by-step demo with exact screen actions in [brackets]
→ The exact prompts (in a box) → Common mistakes → Your task → Summary in 3 bullets.
Use these glossary terms: [paste]. Friendly, respectful tone, short sentences, simple words.
```

**Pashto adaptation**
```
Adapt this Dari lesson script into natural Pashto for Afghan learners. Do not translate word for word;
rewrite it so it sounds like a Pashto teacher speaking. Keep the screen actions and prompts.
Use these Pashto glossary terms: [paste]. Mark any term you are unsure of with [CHECK].
```

**Quiz**
```
From this lesson script, write 5 multiple-choice questions in [LANGUAGE] that test understanding and
application, not memorization. Each: 4 options, 1 correct, a one-sentence explanation. Output as CSV with
columns: type,question,option_a,option_b,option_c,option_d,correct,explanation,difficulty.
```

**Textbook chapter**
```
Turn this lesson script into a textbook chapter in [LANGUAGE] using the chapter template:
learning goals, key words (Pashto | Dari | English), explanation, worked example, prompts box,
exercises, mini project, 10-question quiz. Write it for print: no "click here", describe screens in words,
and mark where screenshots go as [SCREENSHOT: description].
```

## E. Quality check before publishing (every lesson)
- [ ] Native editor approved the script
- [ ] Audio: no echo, no noise, even volume (normalize to about -16 LUFS)
- [ ] Every prompt shown on screen and in the copy box
- [ ] Subtitles checked
- [ ] Plays on a cheap Android phone at 240p and 360p
- [ ] Quiz answers verified
- [ ] Watched once by someone who is not on the team

## F. File and naming system
```
/Courses
  /L1-AI-Beginner
    /prs (Dari)
      /M01-What-is-AI
        L01_script.docx
        L01_slides.pdf
        L01_raw.mp4
        L01_final_1080p.mp4
        L01_audio.opus
        L01_quiz.csv
        L01_shorts/ (clips)
    /ps (Pashto)
      ...
  /book
    L1_prs_book_v1.0.pdf
    L1_ps_book_v1.0.pdf
```
Back up masters to two places (cloud + external drive).

## G. Production speed targets
- Month 1–2: 10 finished lessons/week (5 per language) while the LMS is being built
- Month 3+: 20 lessons/week with two editors and a video editor
- Level 0 ≈ 15 lessons, Level 1 ≈ 30 lessons, Level 2 ≈ 60 lessons (per language)
