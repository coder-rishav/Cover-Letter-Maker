/* CoverCraft — front-end behaviour (vanilla JS). */
(function () {
    'use strict';

    var config = window.CLM_CONFIG || {};
    var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || config.csrf || '';

    var form = document.getElementById('letter-form');
    if (!form) {
        return;
    }

    var companyInput = document.getElementById('company');
    var roleInput = document.getElementById('role');
    var resumeText = document.getElementById('resume-text');
    var hiringManagerInput = document.getElementById('hiring-manager');
    var jobDescription = document.getElementById('job-description');
    var toneInputs = Array.prototype.slice.call(document.querySelectorAll('input[name="tone"]'));

    var tabs = Array.prototype.slice.call(document.querySelectorAll('[role="tab"]'));
    var panels = {
        paste: document.getElementById('panel-paste'),
        upload: document.getElementById('panel-upload')
    };

    var dropzone = document.getElementById('dropzone');
    var fileInput = document.getElementById('resume-file');
    var dzIdle = document.getElementById('dz-idle');
    var dzSelected = document.getElementById('dz-selected');
    var fileName = document.getElementById('file-name');
    var fileSize = document.getElementById('file-size');
    var fileRemove = document.getElementById('file-remove');
    var fileError = document.getElementById('file-error');

    var generateBtn = document.getElementById('generate-btn');
    var generateSpinner = document.getElementById('generate-spinner');
    var generateIcon = document.getElementById('generate-icon');
    var generateLabel = document.getElementById('generate-label');

    var paper = document.getElementById('letter-paper');
    var letterContent = document.getElementById('letter-content');
    var previewEmpty = document.getElementById('preview-empty');
    var wordCount = document.getElementById('word-count');

    var regenerateBtn = document.getElementById('regenerate-btn');
    var copyBtn = document.getElementById('copy-btn');
    var pdfBtn = document.getElementById('pdf-btn');
    var txtBtn = document.getElementById('txt-btn');
    var pdfSpinner = document.getElementById('pdf-spinner');
    var pdfIcon = document.getElementById('pdf-icon');

    var detectedCard = document.getElementById('detected-card');
    var detectedChips = document.getElementById('detected-chips');
    var detectedSummary = document.getElementById('detected-summary');

    var toasts = document.getElementById('toasts');
    var themeToggle = document.getElementById('theme-toggle');

    var MAX_FILE_BYTES = 5 * 1024 * 1024;
    var ALLOWED_EXTENSIONS = ['pdf', 'docx', 'txt'];

    var state = {
        activeTab: 'paste',
        file: null,
        meta: {
            company: '',
            role: '',
            name: '',
            contact: ''
        },
        waiting: false
    };

    /* ------------------------------------------------------------------ */
    /* Theme                                                              */
    /* ------------------------------------------------------------------ */

    function applyTheme(dark) {
        document.documentElement.classList.toggle('dark', dark);

        if (themeToggle) {
            themeToggle.setAttribute('aria-pressed', dark ? 'true' : 'false');
            themeToggle.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
            var sun = themeToggle.querySelector('.icon-sun');
            var moon = themeToggle.querySelector('.icon-moon');
            if (sun) sun.classList.toggle('hidden', dark);
            if (moon) moon.classList.toggle('hidden', !dark);
        }

        try {
            localStorage.setItem('clm-theme', dark ? 'dark' : 'light');
        } catch (error) {
            /* Storage can be unavailable in private modes. */
        }
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            applyTheme(!document.documentElement.classList.contains('dark'));
        });
        applyTheme(document.documentElement.classList.contains('dark'));
    }

    /* ------------------------------------------------------------------ */
    /* Tabs                                                               */
    /* ------------------------------------------------------------------ */

    function activateTab(name, moveFocus) {
        state.activeTab = name;

        tabs.forEach(function (tab) {
            var selected = tab.dataset.tab === name;
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.tabIndex = selected ? 0 : -1;
            tab.classList.toggle('is-active', selected);
            if (selected && moveFocus) {
                tab.focus();
            }
        });

        Object.keys(panels).forEach(function (key) {
            if (panels[key]) {
                panels[key].hidden = key !== name;
            }
        });
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            activateTab(tab.dataset.tab, false);
        });

        tab.addEventListener('keydown', function (event) {
            var index = tabs.indexOf(tab);
            var next = null;

            if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                next = tabs[(index + 1) % tabs.length];
            } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                next = tabs[(index - 1 + tabs.length) % tabs.length];
            } else if (event.key === 'Home') {
                next = tabs[0];
            } else if (event.key === 'End') {
                next = tabs[tabs.length - 1];
            }

            if (next) {
                event.preventDefault();
                activateTab(next.dataset.tab, true);
            }
        });
    });

    /* ------------------------------------------------------------------ */
    /* File upload                                                        */
    /* ------------------------------------------------------------------ */

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function showFileError(message) {
        if (!fileError) return;
        if (message) {
            fileError.textContent = message;
            fileError.hidden = false;
        } else {
            fileError.textContent = '';
            fileError.hidden = true;
        }
    }

    function resetFile() {
        state.file = null;
        if (fileInput) fileInput.value = '';
        if (dzIdle) dzIdle.hidden = false;
        if (dzSelected) {
            dzSelected.classList.add('hidden');
            dzSelected.classList.remove('flex');
        }
        if (fileName) fileName.textContent = '';
        if (fileSize) fileSize.textContent = '';
    }

    function selectFile(file) {
        if (!file) return;
        showFileError('');

        var extension = (file.name.split('.').pop() || '').toLowerCase();

        if (ALLOWED_EXTENSIONS.indexOf(extension) === -1) {
            resetFile();
            showFileError('Please choose a PDF, DOCX, or TXT file.');
            toast('Unsupported file type. Use PDF, DOCX, or TXT.', 'error');
            return;
        }
        if (file.size === 0) {
            resetFile();
            showFileError('That file is empty. Please choose another one.');
            return;
        }
        if (file.size > MAX_FILE_BYTES) {
            resetFile();
            showFileError('That file is ' + formatBytes(file.size) + '. The limit is 5 MB.');
            toast('File is too large. The limit is 5 MB.', 'error');
            return;
        }

        state.file = file;
        if (fileName) fileName.textContent = file.name;
        if (fileSize) fileSize.textContent = formatBytes(file.size) + ' · ready to parse';
        if (dzIdle) dzIdle.hidden = true;
        if (dzSelected) {
            dzSelected.classList.remove('hidden');
            dzSelected.classList.add('flex');
        }
    }

    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function (event) {
            if (event.target === fileInput || event.target === fileRemove
                || (fileRemove && fileRemove.contains(event.target))) {
                return;
            }
            fileInput.click();
        });

        dropzone.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                fileInput.click();
            }
        });

        ['dragenter', 'dragover'].forEach(function (name) {
            dropzone.addEventListener(name, function (event) {
                event.preventDefault();
                event.stopPropagation();
                dropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'dragend'].forEach(function (name) {
            dropzone.addEventListener(name, function () {
                dropzone.classList.remove('is-dragover');
            });
        });

        dropzone.addEventListener('drop', function (event) {
            event.preventDefault();
            event.stopPropagation();
            dropzone.classList.remove('is-dragover');
            if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length) {
                selectFile(event.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', function () {
            if (fileInput.files && fileInput.files.length) {
                selectFile(fileInput.files[0]);
            }
        });
    }

    if (fileRemove) {
        fileRemove.addEventListener('click', function (event) {
            event.stopPropagation();
            resetFile();
            showFileError('');
            if (fileInput) fileInput.focus();
        });
    }

    /* ------------------------------------------------------------------ */
    /* Toasts                                                             */
    /* ------------------------------------------------------------------ */

    function toast(message, type) {
        if (!toasts) return;

        var element = document.createElement('div');
        element.className = 'toast toast--' + (type || 'info');
        element.setAttribute('role', type === 'error' ? 'alert' : 'status');

        var dot = document.createElement('span');
        dot.className = 'mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-current';
        dot.setAttribute('aria-hidden', 'true');

        var text = document.createElement('span');
        text.textContent = message;

        element.appendChild(dot);
        element.appendChild(text);
        toasts.appendChild(element);

        var remove = function () {
            if (!element.isConnected) return;
            element.classList.add('is-leaving');
            window.setTimeout(function () {
                element.remove();
            }, 220);
        };

        element.addEventListener('click', remove);
        window.setTimeout(remove, type === 'error' ? 6000 : 4000);
    }

    /* ------------------------------------------------------------------ */
    /* Letter rendering                                                   */
    /* ------------------------------------------------------------------ */

    function countWords() {
        var text = (letterContent.innerText || '').replace(/\s+/g, ' ').trim();
        return text ? text.split(' ').length : 0;
    }

    function updateWordCount(edited) {
        if (!wordCount) return;
        var count = countWords();
        if (!count) {
            wordCount.textContent = 'Your letter will appear here';
            return;
        }
        wordCount.textContent = count + ' words' + (edited ? ' · edited' : '');
    }

    function addChip(label, value, modifier) {
        var chip = document.createElement('span');
        chip.className = 'chip' + (modifier ? ' ' + modifier : '');

        if (label) {
            var chipLabel = document.createElement('span');
            chipLabel.className = 'chip-label';
            chipLabel.textContent = label;
            chip.appendChild(chipLabel);
        }

        var chipValue = document.createElement('span');
        chipValue.textContent = value;
        chip.appendChild(chipValue);

        detectedChips.appendChild(chip);
    }

    function renderDetected(detected) {
        if (!detectedChips) return;
        detectedChips.innerHTML = '';

        addChip('Name', detected.name || 'Not found', '');
        addChip('Experience', detected.years ? detected.years + (detected.years === 1 ? ' year' : ' years') : 'Not found', '');

        if (detected.titles && detected.titles.length) {
            addChip('Recent role', detected.titles[0], '');
        }

        var matches = Array.isArray(detected.jd_matches) ? detected.jd_matches : [];
        var skills = Array.isArray(detected.top_skills) && detected.top_skills.length
            ? detected.top_skills
            : (Array.isArray(detected.skills) ? detected.skills.slice(0, 8) : []);

        skills.slice(0, 8).forEach(function (skill) {
            addChip('', skill, matches.indexOf(skill) !== -1 ? 'chip--match' : 'chip--skill');
        });

        if (!skills.length) {
            addChip('', 'No skills detected', '');
        }

        if (detectedSummary) {
            var parts = [];
            if (detected.skill_count) parts.push(detected.skill_count + ' skills parsed');
            if (matches.length) parts.push(matches.length + ' JD matches');
            detectedSummary.textContent = parts.join(' · ');
        }

        if (detectedCard) {
            detectedCard.classList.remove('hidden');
            detectedCard.classList.add('fade-up');
        }
    }

    function setActionsEnabled(enabled) {
        [regenerateBtn, copyBtn, pdfBtn, txtBtn].forEach(function (button) {
            if (button) button.disabled = !enabled;
        });
    }

    function renderLetter(data) {
        if (!data || !data.letter_html) {
            toast('The server returned an empty letter. Please try again.', 'error');
            return;
        }

        letterContent.innerHTML = data.letter_html;
        letterContent.classList.remove('is-edited');
        paper.classList.remove('hidden');
        previewEmpty.classList.add('hidden');

        paper.classList.remove('fade-up');
        void paper.offsetWidth;
        paper.classList.add('fade-up');

        state.meta = {
            company: companyInput.value.trim(),
            role: roleInput.value.trim(),
            name: data.detected && data.detected.name ? data.detected.name : '',
            contact: data.detected
                ? [data.detected.email, data.detected.phone].filter(Boolean).join(' | ')
                : ''
        };

        setActionsEnabled(true);
        updateWordCount(false);
        renderDetected(data.detected || {});
    }

    /* ------------------------------------------------------------------ */
    /* Validation & generation                                            */
    /* ------------------------------------------------------------------ */

    function clearInvalid(input) {
        if (input) input.removeAttribute('aria-invalid');
    }

    [companyInput, roleInput, resumeText].forEach(function (input) {
        if (!input) return;
        input.addEventListener('input', function () {
            clearInvalid(input);
        });
    });

    function validateForm() {
        clearInvalid(companyInput);
        clearInvalid(roleInput);
        showFileError('');

        if (!companyInput.value.trim()) {
            companyInput.setAttribute('aria-invalid', 'true');
            companyInput.focus();
            toast('Please add the company name.', 'error');
            return false;
        }

        if (!roleInput.value.trim()) {
            roleInput.setAttribute('aria-invalid', 'true');
            roleInput.focus();
            toast('Please add the job role.', 'error');
            return false;
        }

        if (state.activeTab === 'upload') {
            if (!state.file) {
                showFileError('Choose a resume file, or switch to “Paste text”.');
                dropzone.focus();
                toast('Please choose a resume file.', 'error');
                return false;
            }
            return true;
        }

        if (resumeText.value.trim().length < 30) {
            resumeText.setAttribute('aria-invalid', 'true');
            resumeText.focus();
            toast('Please paste at least a few lines of your resume.', 'error');
            return false;
        }

        return true;
    }

    function setGenerating(active) {
        state.waiting = active;

        if (generateBtn) generateBtn.disabled = active;
        if (generateSpinner) generateSpinner.classList.toggle('hidden', !active);
        if (generateIcon) generateIcon.classList.toggle('hidden', active);
        if (generateLabel) generateLabel.textContent = active ? 'Generating…' : 'Generate cover letter';

        if (regenerateBtn) regenerateBtn.disabled = active || paper.classList.contains('hidden');
    }

    async function generate() {
        if (state.waiting) return;
        if (!validateForm()) return;

        setGenerating(true);

        var data = new FormData();
        data.append('csrf_token', csrfToken);
        data.append('company', companyInput.value.trim());
        data.append('role', roleInput.value.trim());
        data.append('hiring_manager', hiringManagerInput ? hiringManagerInput.value.trim() : '');
        data.append('tone', (toneInputs.filter(function (input) { return input.checked; })[0] || {}).value || 'formal');
        data.append('job_description', jobDescription ? jobDescription.value : '');

        if (state.activeTab === 'upload' && state.file) {
            data.append('resume_file', state.file, state.file.name);
        } else {
            data.append('resume_text', resumeText.value);
        }

        try {
            var response = await fetch(config.generateUrl || 'generate.php', {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            var payload = null;
            try {
                payload = await response.json();
            } catch (parseError) {
                payload = null;
            }

            if (!response.ok || !payload || payload.ok !== true) {
                throw new Error((payload && payload.error) || 'Could not generate the letter. Please try again.');
            }

            renderLetter(payload);
            toast('Cover letter ready. Edit anything you like before exporting.', 'success');

            if (window.matchMedia('(max-width: 1023px)').matches) {
                paper.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } catch (error) {
            toast(error.message || 'Network error. Please try again.', 'error');
        } finally {
            setGenerating(false);
        }
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        generate();
    });

    if (regenerateBtn) {
        regenerateBtn.addEventListener('click', function () {
            generate();
        });
    }

    /* ------------------------------------------------------------------ */
    /* Editing                                                            */
    /* ------------------------------------------------------------------ */

    var editTimer = null;

    letterContent.addEventListener('input', function () {
        letterContent.classList.add('is-edited');
        window.clearTimeout(editTimer);
        editTimer = window.setTimeout(function () {
            updateWordCount(true);
        }, 180);
    });

    letterContent.addEventListener('paste', function (event) {
        event.preventDefault();
        var clipboard = event.clipboardData || window.clipboardData;
        var text = clipboard ? clipboard.getData('text/plain') : '';
        if (!text) return;

        var inserted = false;
        try {
            inserted = document.execCommand('insertText', false, text);
        } catch (error) {
            inserted = false;
        }

        if (!inserted) {
            var selection = window.getSelection();
            if (selection && selection.rangeCount) {
                var range = selection.getRangeAt(0);
                range.deleteContents();
                range.insertNode(document.createTextNode(text));
                range.collapse(false);
                selection.removeAllRanges();
                selection.addRange(range);
            }
        }

        letterContent.classList.add('is-edited');
        updateWordCount(true);
    });

    /* ------------------------------------------------------------------ */
    /* Export helpers                                                     */
    /* ------------------------------------------------------------------ */

    function safePart(value) {
        var cleaned = (value || '').replace(/[^A-Za-z0-9_-]+/g, '_').replace(/_{2,}/g, '_').replace(/^_+|_+$/g, '');
        return cleaned.slice(0, 40) || 'Letter';
    }

    function letterFileName(extension) {
        return 'Cover_Letter_' + safePart(state.meta.company) + '_' + safePart(state.meta.role) + '.' + extension;
    }

    function saveBlob(blob, filename) {
        var url = URL.createObjectURL(blob);
        var link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.setTimeout(function () {
            URL.revokeObjectURL(url);
        }, 1500);
    }

    function readLetterText() {
        return (letterContent.innerText || '').replace(/\u00a0/g, ' ').trim();
    }

    async function copyLetter() {
        var text = readLetterText();
        if (!text) {
            toast('There is no letter to copy yet.', 'error');
            return;
        }

        try {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(text);
            } else {
                throw new Error('Clipboard API unavailable');
            }
            toast('Letter copied to clipboard.', 'success');
        } catch (error) {
            var area = document.createElement('textarea');
            area.value = text;
            area.setAttribute('readonly', '');
            area.style.position = 'fixed';
            area.style.opacity = '0';
            document.body.appendChild(area);
            area.select();
            var copied = false;
            try {
                copied = document.execCommand('copy');
            } catch (copyError) {
                copied = false;
            }
            area.remove();
            toast(copied ? 'Letter copied to clipboard.' : 'Copy failed. Select the text and copy manually.', copied ? 'success' : 'error');
        }
    }

    function downloadText() {
        var text = readLetterText();
        if (!text) {
            toast('There is no letter to download yet.', 'error');
            return;
        }
        saveBlob(new Blob([text], { type: 'text/plain;charset=utf-8' }), letterFileName('txt'));
        toast('Text file downloaded.', 'success');
    }

    async function downloadPdf() {
        var text = readLetterText();
        if (!text || text.length < 20) {
            toast('Generate a letter before downloading a PDF.', 'error');
            return;
        }

        pdfBtn.disabled = true;
        if (pdfSpinner) pdfSpinner.classList.remove('hidden');
        if (pdfIcon) pdfIcon.classList.add('hidden');

        try {
            var body = new FormData();
            body.append('csrf_token', csrfToken);
            body.append('html', letterContent.innerHTML);
            body.append('company', state.meta.company);
            body.append('role', state.meta.role);
            body.append('name', state.meta.name);
            body.append('contact', state.meta.contact);

            var response = await fetch(config.pdfUrl || 'pdf.php', {
                method: 'POST',
                body: body,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                var message = 'Could not build the PDF. Please try again.';
                try {
                    var payload = await response.json();
                    if (payload && payload.error) message = payload.error;
                } catch (parseError) {
                    /* keep the default message */
                }
                throw new Error(message);
            }

            var blob = await response.blob();
            var disposition = response.headers.get('Content-Disposition') || '';
            var match = /filename="?([^";]+)"?/i.exec(disposition);
            var filename = match && match[1] ? match[1] : letterFileName('pdf');

            saveBlob(blob, filename);
            toast('PDF downloaded.', 'success');
        } catch (error) {
            toast(error.message || 'Could not build the PDF.', 'error');
        } finally {
            pdfBtn.disabled = false;
            if (pdfSpinner) pdfSpinner.classList.add('hidden');
            if (pdfIcon) pdfIcon.classList.remove('hidden');
        }
    }

    if (copyBtn) copyBtn.addEventListener('click', copyLetter);
    if (txtBtn) txtBtn.addEventListener('click', downloadText);
    if (pdfBtn) pdfBtn.addEventListener('click', downloadPdf);

    /* ------------------------------------------------------------------ */
    /* Start                                                              */
    /* ------------------------------------------------------------------ */

    setActionsEnabled(false);
    activateTab('paste', false);
})();
