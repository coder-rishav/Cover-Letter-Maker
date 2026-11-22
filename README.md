# CoverCraft — Cover Letter Maker

A self-contained PHP cover-letter generator. Enter a **company**, a **job role**, and a **resume**, and get a polished, personalized letter you can edit in the browser and export as **PDF** or **TXT**.

The letter is generated entirely on the server with rule-based PHP logic — no AI provider, no external API calls, and no database.

---

## Requirements

- PHP **8.1+** with `dom`, `mbstring`, `fileinfo`, and `zip` extensions
- [Composer](https://getcomposer.org/)
- A modern browser (JavaScript enabled)

## Quick start

```bash
composer install
php -S localhost:8000 -t public
```

Open <http://localhost:8000>.

The first page load uses the Tailwind CDN, Google Fonts (Inter / Source Serif 4), and Dompdf. After installation, no runtime code calls an external service.

### CLI smoke test

```bash
php bin/test-generate.php                  # sample resume, all three tones
php bin/test-generate.php --job-description
php bin/test-generate.php --minimal        # sparse resume, graceful fallbacks
```

The script parses `samples/sample_resume.txt`, prints the detected facts and one letter per tone, and checks for unresolved placeholders and unescaped input.

---

## What gets extracted

`src/ResumeParser.php` reads plain text from any of the supported inputs and extracts:

| Fact | Method |
| --- | --- |
| Candidate name | First plausible name line, or a name derived from the email prefix |
| Email / phone / links | Regex, validated phone digit counts, LinkedIn/GitHub/portfolio URLs |
| Years of experience | Stated "7+ years" patterns first; otherwise date-range spans ("2019 – Present") |
| Skills | `src/SkillsDictionary.php`: ~400 curated skills in 8 categories with aliases |
| Recent titles & companies | Lines around the Experience heading, with whole-document title fallback |
| Achievements | Bullet or action-verb lines containing metrics or outcomes |

Skills are matched with boundary-aware regexes. Short or all-caps aliases (`C++`, `Go`, `REST`, `AWS`) are case-sensitive so ordinary prose does not create false positives.

## How the letter is built

1. `src/CoverLetterGenerator.php` picks one variant per section from `templates/letters/{formal,modern,enthusiastic}.php` (openings, experience, achievement, skills, closing, plus supporting sentences).
2. When a **job description** is supplied, its skills are extracted with the same dictionary, resume skills are re-ranked by overlap, and the overlapping skills are called out in the letter.
3. Placeholders (`{company}`, `{role}`, `{years_phrase}`, `{skills_phrase}`, `{title_phrase}`, `{achievement_sentence}`, …) are filled with escaped values.
4. Every optional fact has a natural-language fallback — missing years, titles, companies, achievements, or skills never produce an empty placeholder or broken sentence.
5. A word-count guard tops up unusually short combinations, keeping letters in the **250–400 word** target range.

Output is a greeting, four body paragraphs, and a signature with the contact details found in the resume.

## Endpoints

| Endpoint | Purpose |
| --- | --- |
| `public/index.php` | Two-panel UI, inline CSRF token, live editable preview |
| `public/generate.php` | AJAX (JSON) endpoint: parses resume, returns `{letter_html, plain_text, detected}` |
| `public/pdf.php` | Accepts the final edited HTML and streams an A4 PDF |

### PDF export

- `src/PdfExporter.php` sanitizes the posted HTML (allowlist: `p`, `br`, `strong`, `em`, `ul`, `li`; all attributes dropped), prepends the candidate header, and renders A4 with 2.5 cm margins using Dompdf.
- Filename: `Cover_Letter_{Company}_{Role}.pdf`, with unsafe characters replaced.
- Because the preview HTML is posted, edits made in the contenteditable preview are reflected in the PDF.

## Security

- Session-based **CSRF token** checked on every POST (`generate.php`, `pdf.php`).
- All user values are escaped with `htmlspecialchars` before they enter a letter; DOM output is rebuilt through an allowlist sanitizer.
- Uploads: extension + `finfo` MIME check, **5 MB** limit, random filename in `storage/tmp/`, parsed, then **deleted in a `finally` block**.
- Resume text is never written to a database and is not kept in the session — only the parsed result is returned for that request.
- PDF generation has remote resources disabled in Dompdf.

## Project layout

```
cover-letter-maker/
├── public/
│   ├── index.php
│   ├── generate.php
│   ├── pdf.php
│   └── assets/
│       ├── app.js
│       └── app.css
├── src/
│   ├── ResumeParser.php
│   ├── ResumeFileReader.php     # upload validation + PDF/DOCX/TXT text extraction
│   ├── SkillsDictionary.php
│   ├── CoverLetterGenerator.php
│   ├── PdfExporter.php
│   └── Security.php
├── templates/letters/
│   ├── formal.php
│   ├── modern.php
│   └── enthusiastic.php
├── storage/tmp/                 # temp uploads (gitignored, deleted immediately)
├── samples/sample_resume.txt
├── bin/test-generate.php
├── composer.json
└── README.md
```

## UI notes

- Responsive two-panel layout: form on the left, A4-style editable preview on the right; stacks on mobile.
- Dark/light toggle persisted in `localStorage`, with a no-flash inline theme script.
- Resume tabs for pasted text and drag-and-drop upload (shows file name/size).
- Chips for detected name, experience, recent role, skills, and JD matches.
- Toasts for success/error, loading spinners during requests, and CSS-only micro-animations.
- Labels, focus-visible rings, ARIA tabs, an `aria-live` toast region, and a skip link.

## Limitations

- Rule-based generation: the letter is only as specific as the resume text. There is no semantic understanding of achievements.
- Scanned/image-only PDFs contain no text layer; the app asks you to paste the resume instead (no OCR).
- Skills are recognized only if they appear in the built-in dictionary (or aliases).
- The UI's styling relies on the Tailwind CDN at load time; the generation and export logic remain fully local.

---

Built with plain PHP, Dompdf, Smalot PdfParser, and PHPWord. Sample resume: Rishav Mandal.
