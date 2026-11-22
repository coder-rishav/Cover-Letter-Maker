<?php

declare(strict_types=1);

use CoverLetterMaker\Security;

require __DIR__ . '/../vendor/autoload.php';

Security::startSession();

$csrfToken = Security::csrfToken();
$config = [
    'csrf' => $csrfToken,
    'generateUrl' => 'generate.php',
    'pdfUrl' => 'pdf.php',
];
?>
<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="<?= Security::escape($csrfToken) ?>">
    <meta name="description" content="Generate a tailored, professional cover letter from your resume in seconds. Runs locally with no AI APIs and no database.">
    <title>CoverCraft — Cover Letter Maker</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        serif: ['"Source Serif 4"', 'Georgia', 'serif']
                    }
                }
            }
        };
    </script>
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('clm-theme');
                var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (dark) document.documentElement.classList.add('dark');
            } catch (error) { /* localStorage unavailable */ }
        })();
    </script>
    <link rel="stylesheet" href="assets/app.css">
</head>
<body class="min-h-full bg-slate-50 font-sans text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-200">
<a href="#main" class="skip-link">Skip to content</a>

<div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
    <div class="absolute -left-32 -top-40 h-[30rem] w-[30rem] rounded-full bg-indigo-300/30 blur-3xl dark:bg-indigo-500/10"></div>
    <div class="absolute -right-24 top-1/3 h-[26rem] w-[26rem] rounded-full bg-sky-300/30 blur-3xl dark:bg-sky-500/10"></div>
    <div class="absolute bottom-0 left-1/3 h-[22rem] w-[22rem] rounded-full bg-violet-300/20 blur-3xl dark:bg-violet-500/10"></div>
</div>

<header class="sticky top-0 z-40 border-b border-white/60 bg-white/70 backdrop-blur-xl dark:border-slate-800/70 dark:bg-slate-900/60">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 text-sm font-extrabold tracking-tight text-white shadow-lg shadow-indigo-500/30" aria-hidden="true">CC</span>
            <div class="leading-tight">
                <p class="text-[15px] font-bold tracking-tight text-slate-900 dark:text-white">CoverCraft</p>
                <p class="hidden text-[11px] font-medium text-slate-500 sm:block dark:text-slate-400">Local cover letter studio</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="badge-soft hidden md:inline-flex">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                No AI APIs · No database
            </span>
            <button type="button" id="theme-toggle" class="theme-toggle" aria-label="Toggle dark mode" aria-pressed="false" title="Toggle dark mode">
                <svg class="icon-sun h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path>
                </svg>
                <svg class="icon-moon hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>
        </div>
    </div>
</header>

<main id="main" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
    <div class="mb-7 max-w-3xl">
        <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-indigo-200/70 bg-indigo-50/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-indigo-700 dark:border-indigo-400/20 dark:bg-indigo-400/10 dark:text-indigo-300">
            <span class="h-1.5 w-1.5 rounded-full bg-indigo-500" aria-hidden="true"></span>
            Rule-based · Runs locally
        </div>
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
            A cover letter that sounds like <span class="bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-500 bg-clip-text text-transparent">you</span>.
        </h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-base dark:text-slate-400">
            Add the company, the role, and your resume. We extract your experience and skills, then shape a tailored letter you can edit and export as a PDF.
        </p>
    </div>

    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
        <section aria-labelledby="form-heading">
            <form id="letter-form" class="card p-5 sm:p-7" novalidate>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 id="form-heading" class="text-base font-bold tracking-tight text-slate-900 dark:text-white">Your details</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Takes less than a minute.</p>
                    </div>
                    <span class="badge">Step 1 of 2</span>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="company" class="field-label">Company name <span class="req" aria-hidden="true">*</span></label>
                        <input id="company" name="company" type="text" required maxlength="120" autocomplete="organization"
                               class="input" placeholder="Acme Corporation" aria-describedby="company-hint">
                        <p id="company-hint" class="sr-only">Required. The company you are applying to.</p>
                    </div>
                    <div>
                        <label for="role" class="field-label">Job role <span class="req" aria-hidden="true">*</span></label>
                        <input id="role" name="role" type="text" required maxlength="120" autocomplete="organization-title"
                               class="input" placeholder="Senior PHP Engineer" aria-describedby="role-hint">
                        <p id="role-hint" class="sr-only">Required. The job title you are applying for.</p>
                    </div>
                </div>

                <fieldset class="mt-5 min-w-0">
                    <legend class="field-label">Resume <span class="req" aria-hidden="true">*</span></legend>

                    <div class="tablist" role="tablist" aria-label="Resume input method">
                        <button type="button" id="tab-paste" role="tab" aria-selected="true" aria-controls="panel-paste" tabindex="0" class="tab is-active" data-tab="paste">
                            Paste text
                        </button>
                        <button type="button" id="tab-upload" role="tab" aria-selected="false" aria-controls="panel-upload" tabindex="-1" class="tab" data-tab="upload">
                            Upload file
                        </button>
                    </div>

                    <div id="panel-paste" role="tabpanel" aria-labelledby="tab-paste" class="mt-3">
                        <label for="resume-text" class="sr-only">Resume text</label>
                        <textarea id="resume-text" name="resume_text" rows="10" maxlength="60000"
                                  class="input resize-y font-mono text-[12.5px] leading-relaxed"
                                  placeholder="Paste your resume here — summary, experience, skills, education..."
                                  aria-describedby="paste-hint"></textarea>
                        <p id="paste-hint" class="hint">A minimal resume works too. We fill in what is available.</p>
                    </div>

                    <div id="panel-upload" role="tabpanel" aria-labelledby="tab-upload" class="mt-3" hidden>
                        <div id="dropzone" class="dropzone" role="button" tabindex="0"
                             aria-label="Upload your resume. PDF, DOCX or TXT, maximum 5 megabytes.">
                            <input type="file" id="resume-file" name="resume_file" class="sr-only"
                                   accept=".pdf,.docx,.txt,application/pdf,text/plain,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                            <div id="dz-idle">
                                <svg class="mx-auto h-8 w-8 text-indigo-500 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 16V4m0 0L7 9m5-5 5 5"></path>
                                    <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"></path>
                                </svg>
                                <p class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Drag &amp; drop your resume</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    or <span class="font-semibold text-indigo-600 dark:text-indigo-400">browse files</span> · PDF, DOCX, TXT · max 5MB
                                </p>
                            </div>
                            <div id="dz-selected" class="hidden items-center justify-center gap-3 text-left">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300" aria-hidden="true">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <path d="M14 2v6h6M8 13h8M8 17h5"></path>
                                    </svg>
                                </span>
                                <span class="min-w-0">
                                    <span id="file-name" class="block truncate text-sm font-semibold text-slate-800 dark:text-slate-100"></span>
                                    <span id="file-size" class="block text-xs text-slate-500 dark:text-slate-400"></span>
                                </span>
                                <button type="button" id="file-remove" class="ml-1 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-rose-500 dark:hover:bg-slate-800" aria-label="Remove selected file">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                        <path d="M18 6 6 18M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p id="file-error" class="error-text" role="alert" hidden></p>
                        <p class="hint">Your file is parsed in a temporary folder and deleted immediately. Nothing is stored.</p>
                    </div>
                </fieldset>

                <details class="advanced mt-5">
                    <summary class="advanced-summary">
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"></path>
                            </svg>
                            Advanced options
                        </span>
                        <span class="flex items-center gap-2 text-xs font-medium text-slate-400">
                            Optional
                            <svg class="chevron h-4 w-4 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </span>
                    </summary>

                    <div class="mt-4 grid gap-4">
                        <div>
                            <label for="hiring-manager" class="field-label">Hiring manager name</label>
                            <input id="hiring-manager" name="hiring_manager" type="text" maxlength="80" autocomplete="off"
                                   class="input" placeholder="Hiring Manager">
                        </div>

                        <fieldset>
                            <legend class="field-label">Tone</legend>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="tone-option">
                                    <input type="radio" name="tone" value="formal" checked>
                                    <span class="tone-label">Formal</span>
                                </label>
                                <label class="tone-option">
                                    <input type="radio" name="tone" value="modern">
                                    <span class="tone-label">Modern</span>
                                </label>
                                <label class="tone-option">
                                    <input type="radio" name="tone" value="enthusiastic">
                                    <span class="tone-label">Enthusiastic</span>
                                </label>
                            </div>
                        </fieldset>

                        <div>
                            <label for="job-description" class="field-label">Job description</label>
                            <textarea id="job-description" name="job_description" rows="4" maxlength="15000"
                                      class="input resize-y text-sm leading-relaxed"
                                      placeholder="Paste the job posting to match your strongest skills against it..."
                                      aria-describedby="jd-hint"></textarea>
                            <p id="jd-hint" class="hint">When provided, overlapping skills are prioritized and highlighted in the letter.</p>
                        </div>
                    </div>
                </details>

                <button type="submit" id="generate-btn" class="btn-primary mt-6 w-full">
                    <span id="generate-spinner" class="spinner hidden" aria-hidden="true"></span>
                    <svg id="generate-icon" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3z"></path>
                    </svg>
                    <span id="generate-label">Generate cover letter</span>
                </button>

                <p class="mt-3 text-center text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                    Processed on this server with plain PHP. No AI provider, no database, no resume storage.
                </p>
            </form>
        </section>

        <section aria-labelledby="preview-heading" class="lg:sticky lg:top-24">
            <div class="card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3.5 sm:px-5 dark:border-slate-800">
                    <div>
                        <h2 id="preview-heading" class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Live preview</h2>
                        <p id="word-count" class="text-xs text-slate-500 dark:text-slate-400">Your letter will appear here</p>
                    </div>
                    <span class="badge-soft">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"></path>
                        </svg>
                        Editable
                    </span>
                </div>

                <div class="preview-stage">
                    <div id="preview-empty" class="empty-state">
                        <span class="grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-indigo-100 to-violet-100 text-indigo-500 dark:from-indigo-500/15 dark:to-violet-500/15 dark:text-indigo-300" aria-hidden="true">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <path d="M14 2v6h6M8 13h8M8 17h6"></path>
                            </svg>
                        </span>
                        <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Your cover letter will appear here</p>
                        <p class="mt-1 max-w-xs text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            Fill in the company, role, and resume, then press <span class="font-semibold">Generate</span>.
                            You can edit every word before exporting.
                        </p>
                    </div>

                    <article id="letter-paper" class="paper hidden" aria-label="Generated cover letter">
                        <div id="letter-content" class="paper-content font-serif" contenteditable="true" spellcheck="true"
                             role="textbox" aria-multiline="true"
                             aria-label="Cover letter preview. You can edit the text before exporting."></div>
                    </article>
                </div>

                <div class="border-t border-slate-200/80 p-3 sm:p-4 dark:border-slate-800">
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        <button type="button" id="regenerate-btn" class="btn-ghost" disabled>
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path><path d="M21 3v5h-5"></path>
                            </svg>
                            Regenerate
                        </button>
                        <button type="button" id="copy-btn" class="btn-ghost" disabled>
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="9" y="9" width="13" height="13" rx="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            Copy
                        </button>
                        <button type="button" id="pdf-btn" class="btn-dark" disabled>
                            <span id="pdf-spinner" class="spinner spinner--dark hidden" aria-hidden="true"></span>
                            <svg id="pdf-icon" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"></path>
                            </svg>
                            PDF
                        </button>
                        <button type="button" id="txt-btn" class="btn-ghost" disabled>
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"></path>
                            </svg>
                            TXT
                        </button>
                    </div>
                </div>
            </div>

            <div id="detected-card" class="card mt-4 hidden p-5">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Detected from resume</h3>
                    <span id="detected-summary" class="text-[11px] font-medium text-slate-400"></span>
                </div>
                <div id="detected-chips" class="mt-3 flex flex-wrap gap-2"></div>
                <p class="mt-3 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                    This is what the generator used. Edit these fields above and regenerate if something looks off.
                </p>
            </div>
        </section>
    </div>
</main>

<footer class="mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
    <div class="flex flex-col items-center justify-between gap-2 border-t border-slate-200/80 pt-5 text-[11px] text-slate-500 sm:flex-row dark:border-slate-800 dark:text-slate-400">
        <p>Built with plain PHP, Tailwind CSS, and Dompdf. Your resume never leaves this server.</p>
        <p class="flex items-center gap-1.5">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
            No database · No third-party APIs
        </p>
    </div>
</footer>

<div id="toasts" class="toast-stack" aria-live="polite" aria-atomic="false"></div>

<noscript>
    <div class="fixed inset-x-0 bottom-0 z-50 bg-amber-500 p-3 text-center text-sm font-semibold text-amber-950">
        JavaScript is required to generate and download cover letters. Please enable it and reload.
    </div>
</noscript>

<script>
    window.CLM_CONFIG = <?= json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="assets/app.js" defer></script>
</body>
</html>
