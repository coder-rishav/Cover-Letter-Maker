<?php

declare(strict_types=1);

namespace CoverLetterMaker;

/**
 * Extracts structured, rule-based facts from raw resume text.
 *
 * No network calls, no storage: everything is derived from the supplied text.
 */
final class ResumeParser
{
    private const MAX_LINES_FOR_NAME = 14;

    private const HEADING_PATTERN = '/^(education|skills|technical skills|core skills|projects|'
        . 'personal projects|certifications?|awards?|honou?rs?|languages|interests|references|'
        . 'summary|profile|objective|publications|volunteer(?:ing)?|activities)\b/i';

    private const EXPERIENCE_PATTERN = '/^(?:(?:work|professional|employment|relevant|industry)\s+)?experience\s*:?$/i';

    private const TITLE_PATTERN = '/(?<![\w])'
        . '(?:(?:senior|sr\.?|junior|jr\.?|lead|staff|principal|head of|chief|associate|assistant)\s+)?'
        . '(?:(?:software|frontend|front-end|backend|back-end|full[\s-]?stack|web|mobile|ios|android|devops|cloud|'
        . 'data|qa|quality|machine learning|ai|product|project|program|marketing|sales|ux|ui|graphic|database|'
        . 'network|security|systems?|platform|site reliability|technical|research|business|operations|finance|'
        . 'human resources|customer success|content|social media|account|hr)\s*)?'
        . '(?:engineer|engineering|developer|designer|manager|analyst|scientist|architect|consultant|'
        . 'specialist|lead|intern|administrator|coordinator|executive|director|associate|officer)'
        . '(?![\w])/i';

    public const ACTION_VERBS = [
        'built', 'build', 'led', 'lead', 'improved', 'improve', 'reduced', 'reduce', 'increased', 'increase',
        'designed', 'design', 'developed', 'develop', 'created', 'create', 'launched', 'launch',
        'delivered', 'deliver', 'implemented', 'implement', 'optimized', 'optimise', 'optimized',
        'automated', 'automate', 'migrated', 'migrate', 'mentored', 'mentor', 'managed', 'manage',
        'architected', 'spearheaded', 'streamlined', 'achieved', 'scaled', 'scale', 'saved', 'save',
        'generated', 'generate', 'negotiated', 'negotiate', 'coordinated', 'coordinate', 'established',
        'introduced', 'shipped', 'transformed', 'drove', 'owned', 'resolved', 'resolve', 'trained',
    ];

    public function __construct(private readonly SkillsDictionary $skills = new SkillsDictionary())
    {
    }

    /**
     * @return array{
     *     name: ?string,
     *     email: ?string,
     *     phone: ?string,
     *     links: list<string>,
     *     years: ?int,
     *     skills: list<array{name: string, category: string, count: int, position: int}>,
     *     titles: list<string>,
     *     companies: list<string>,
     *     achievements: list<string>
     * }
     */
    public function parse(string $rawText): array
    {
        $text = $this->normalize($rawText);
        $lines = $text === '' ? [] : explode("\n", $text);

        $email = $this->extractEmail($text);
        $phone = $this->extractPhone($text);
        $links = $this->extractLinks($text);
        $name = $this->extractName($lines, $email);
        $skills = $this->skills->detect($text);
        [$titles, $companies] = $this->extractRoles($lines, $text);

        return [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'links' => $links,
            'years' => $this->extractYears($text),
            'skills' => $skills,
            'titles' => $titles,
            'companies' => $companies,
            'achievements' => $this->extractAchievements($lines),
        ];
    }

    private function normalize(string $text): string
    {
        $text = str_replace("\u{FEFF}", '', $text);
        $text = str_replace(["\r\n", "\r", "\0"], "\n", $text);
        $text = str_replace("\t", ' ', $text);
        $text = (string) preg_replace('/[^\P{C}\n]/u', '', $text);
        $lines = array_map(static function (string $line): string {
            $line = (string) preg_replace('/[ ]{2,}/', ' ', $line);

            return trim($line, " \v\u{00A0}");
        }, explode("\n", $text));

        return trim(implode("\n", $lines));
    }

    private function extractEmail(string $text): ?string
    {
        if (preg_match('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/', $text, $m) === 1) {
            return strtolower($m[0]);
        }

        return null;
    }

    private function extractPhone(string $text): ?string
    {
        preg_match_all('/(?<![\w])\+?\d[\d\s().\-]{7,17}\d(?![\w])/', $text, $matches);

        foreach ($matches[0] as $candidate) {
            $candidate = trim($candidate);
            $digits = (string) preg_replace('/\D/', '', $candidate);
            $length = strlen($digits);

            if ($length < 9 || $length > 15) {
                continue;
            }

            // Date ranges such as "2019 - 2024" are not phone numbers.
            if (preg_match('/^(?:19|20)\d{2}\s*[-\s]\s*(?:19|20)\d{2}$/', $candidate) === 1) {
                continue;
            }

            return $candidate;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function extractLinks(string $text): array
    {
        $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:[a-z0-9-]+\.)*(?:linkedin\.com|github\.com|gitlab\.com|'
            . 'bitbucket\.org|behance\.net|dribbble\.com|medium\.com)\/[^\s,;)<>\]]+|https?:\/\/[^\s,;)<>\]]+/i';

        preg_match_all($pattern, $text, $matches);

        $links = [];
        foreach ($matches[0] as $link) {
            $link = rtrim(trim($link), '.,;:');
            if ($link === '') {
                continue;
            }
            if (!preg_match('#^https?://#i', $link)) {
                $link = 'https://' . $link;
            }
            $links[] = $link;
        }

        return array_values(array_unique(array_slice($links, 0, 5)));
    }

    /**
     * @param list<string> $lines
     */
    private function extractName(array $lines, ?string $email): ?string
    {
        $checked = 0;

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }
            if (++$checked > self::MAX_LINES_FOR_NAME) {
                break;
            }

            if ($this->looksLikeHeading($line)
                || strlen($line) > 70
                || preg_match('/[@\d|]|https?:|www\./i', $line) === 1
            ) {
                continue;
            }

            $candidate = (string) preg_replace('/\s*[|,–—-]\s*(?:resume|curriculum vitae|cv).*$/i', '', $line);
            $candidate = trim($candidate);

            $words = preg_split('/\s+/', $candidate) ?: [];
            if (count($words) < 2 || count($words) > 4) {
                continue;
            }

            if (preg_match('/^[A-Z][a-zA-Z\'’.-]+(?:\s+[A-Z][a-zA-Z\'’.-]+){1,3}$/u', $candidate) === 1
                || preg_match('/^[A-Z][A-Z\'’.-]+(?:\s+[A-Z][A-Z\'’.-]+){1,3}$/u', $candidate) === 1
            ) {
                return $candidate;
            }
        }

        if ($email === null) {
            return null;
        }

        $local = strtolower((string) strstr($email, '@', true));
        $local = (string) preg_replace('/[^a-z]+/', ' ', $local);
        $parts = array_values(array_filter(explode(' ', $local), static fn (string $p): bool => trim($p) !== ''));

        if (count($parts) >= 2 && count($parts) <= 3) {
            return implode(' ', array_map(static fn (string $p): string => ucfirst($p), $parts));
        }

        return null;
    }

    private function looksLikeHeading(string $line): bool
    {
        $stripped = trim($line, " :");

        if (preg_match(self::EXPERIENCE_PATTERN, $stripped) === 1) {
            return true;
        }

        return preg_match(self::HEADING_PATTERN, $stripped) === 1
            || preg_match('/^(resume|curriculum vitae|cv|contact( information)?)$/i', $stripped) === 1;
    }

    private function extractYears(string $text): ?int
    {
        $stated = [];

        if (preg_match_all('/(\d{1,2})\s*\+\s*(?:years?|yrs?)/i', $text, $m) > 0) {
            $stated = array_merge($stated, array_map('intval', $m[1]));
        }

        if (preg_match_all('/(?:over|more than|nearly|approximately|about)\s+(\d{1,2})\s+(?:years?|yrs?)/i', $text, $m) > 0) {
            $stated = array_merge($stated, array_map('intval', $m[1]));
        }

        $yearsPattern = '/(\d{1,2})\s*(?:\+|plus)?\s*(?:years?|yrs?)(?:\s+of)?\s+'
            . '(?:professional\s+|relevant\s+|hands-on\s+|industry\s+|software\s+)?experience/i';
        if (preg_match_all($yearsPattern, $text, $m) > 0) {
            $stated = array_merge($stated, array_map('intval', $m[1]));
        }

        $stated = array_values(array_filter($stated, static fn (int $y): bool => $y > 0 && $y <= 45));

        // A candidate who states their experience explicitly is trusted over date spans,
        // which often include university years and would inflate the total.
        if ($stated !== []) {
            return max($stated);
        }

        $candidates = [];

        $rangePattern = '/(?<!\d)((?:19|20)\d{2})\s*(?:-|–|—|to|until)\s*'
            . '((?:19|20)\d{2}|present|current|now|today)(?!\d)/i';
        if (preg_match_all($rangePattern, $text, $m, PREG_SET_ORDER) > 0) {
            $starts = [];
            $ends = [];
            foreach ($m as $range) {
                $starts[] = (int) $range[1];
                $ends[] = ctype_digit($range[2]) ? (int) $range[2] : (int) date('Y');
            }

            $span = max($ends) - min($starts);
            if ($span > 0 && $span <= 45) {
                $candidates[] = $span;
            }
        }

        if (preg_match_all('/\bsince\s+((?:19|20)\d{2})\b/i', $text, $m) > 0) {
            foreach ($m[1] as $year) {
                $span = (int) date('Y') - (int) $year;
                if ($span > 0 && $span <= 45) {
                    $candidates[] = $span;
                }
            }
        }

        $candidates = array_filter($candidates, static fn (int $y): bool => $y > 0 && $y <= 45);

        return $candidates === [] ? null : max($candidates);
    }

    /**
     * @param list<string> $lines
     * @return array{0: list<string>, 1: list<string>}
     */
    private function extractRoles(array $lines, string $text): array
    {
        $titles = [];
        $companies = [];

        $start = null;
        foreach ($lines as $index => $line) {
            if (preg_match(self::EXPERIENCE_PATTERN, trim($line, " :")) === 1) {
                $start = $index + 1;
                break;
            }
        }

        if ($start !== null) {
            for ($i = $start; $i < count($lines); $i++) {
                $line = $lines[$i];
                if ($line === '') {
                    continue;
                }
                if ($this->looksLikeHeading($line) && !$this->isTitleLine($line)) {
                    break;
                }
                if (strlen($line) > 140) {
                    continue;
                }

                [$title, $company] = $this->parseRoleLine($line);

                if ($title !== null && !in_array($title, $titles, true)) {
                    $titles[] = $title;
                }
                if ($company !== null && !in_array($company, $companies, true)) {
                    $companies[] = $company;
                }

                if (count($titles) >= 4) {
                    break;
                }
            }
        }

        if ($titles === [] && preg_match_all(self::TITLE_PATTERN, $text, $m) > 0) {
            foreach ($m[0] as $match) {
                $match = trim((string) preg_replace('/\s+/', ' ', $match));
                if ($match !== '' && !in_array($match, $titles, true)) {
                    $titles[] = $match;
                }
                if (count($titles) >= 3) {
                    break;
                }
            }
        }

        return [array_slice($titles, 0, 3), array_slice($companies, 0, 3)];
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function parseRoleLine(string $line): array
    {
        $clean = trim((string) preg_replace('/^[•*▪◦●\-–—\s]+/u', '', $line));
        $clean = trim((string) preg_replace('/\s*\((?:remote|hybrid|on-?site)\)\s*$/i', '', $clean));

        if ($clean === '' || preg_match('/[@]|https?:/i', $clean) === 1) {
            return [null, null];
        }

        if (preg_match('/^(.{3,70}?)\s+(?:at|@)\s+(.{2,70})$/i', $clean, $m) === 1) {
            $title = $this->cleanTitle($m[1]);
            if ($title !== null) {
                return [$title, $this->cleanCompany($m[2])];
            }
        }

        $parts = array_values(array_filter(array_map(
            static fn (string $p): string => trim($p),
            preg_split('/\s*[|•·]\s*|\s+[–—-]\s+/u', $clean) ?: []
        ), static fn (string $p): bool => $p !== ''));

        if (count($parts) >= 2) {
            $firstTitle = $this->cleanTitle($parts[0]);
            $secondTitle = $this->cleanTitle($parts[1]);

            if ($firstTitle !== null) {
                $company = $this->firstNonDatePart($parts, 1);

                return [$firstTitle, $company === null ? null : $this->cleanCompany($company)];
            }

            if ($secondTitle !== null) {
                $company = $this->firstNonDatePart($parts, 0);

                return [$secondTitle, $company === null ? null : $this->cleanCompany($company)];
            }
        }

        $title = $this->isTitleLine($clean) ? $this->cleanTitle($clean) : null;
        if ($title !== null) {
            return [$title, null];
        }

        return [null, null];
    }

    /**
     * @param list<string> $parts
     */
    private function firstNonDatePart(array $parts, int $from): ?string
    {
        for ($i = $from; $i < count($parts); $i++) {
            $part = trim($parts[$i]);
            if ($part === '' || preg_match('/^(?:19|20)\d{2}\s*[-–—]?\s*(?:present|current|(?:19|20)\d{2})?$/i', $part) === 1) {
                continue;
            }

            return $part;
        }

        return null;
    }

    private function cleanTitle(string $value): ?string
    {
        $value = trim((string) preg_replace('/\s*\((?:[^)]*)\)\s*$/', '', $value));
        $value = trim($value, " ,;|-–—:");

        if ($value === '' || strlen($value) > 70 || !$this->isTitleLine($value)) {
            return null;
        }

        return $value;
    }

    private function isTitleLine(string $value): bool
    {
        return preg_match(self::TITLE_PATTERN, $value) === 1;
    }

    private function cleanCompany(string $value): ?string
    {
        $value = trim((string) preg_replace('/\s*\((?:[^)]*)\)\s*$/', '', $value));
        $value = trim((string) preg_replace('/\s*(?:19|20)\d{2}\s*[-–—]\s*(?:(?:19|20)\d{2}|present|current).*$/i', '', $value));
        $value = trim($value, " ,;|-–—:");

        if ($value === '' || strlen($value) > 80 || preg_match(self::TITLE_PATTERN, $value) === 1) {
            return null;
        }

        $segments = array_map('trim', explode(',', $value));
        if (count($segments) > 1 && preg_match('/^(remote|hybrid|on-?site)$/i', $segments[1]) !== 1) {
            $value = $segments[0];
        }

        return $value === '' ? null : $value;
    }

    /**
     * @param list<string> $lines
     * @return list<string>
     */
    private function extractAchievements(array $lines): array
    {
        $achievements = [];
        $verbPattern = '/\b(?:' . implode('|', self::ACTION_VERBS) . ')\b/i';

        foreach ($lines as $line) {
            if ($line === '' || strlen($line) > 260) {
                continue;
            }

            $isBullet = preg_match('/^[•*▪◦●\-–—]\s*/u', $line) === 1;
            $body = trim((string) preg_replace('/^[•*▪◦●\-–—\s]+/u', '', $line));
            $body = trim((string) preg_replace('/\s*[|•·]\s*.*$/', '', $body));

            if ($body === '' || strlen($body) < 20) {
                continue;
            }

            $hasNumber = preg_match('/\d/', $body) === 1;
            $hasVerb = preg_match($verbPattern, substr($body, 0, 60)) === 1;
            $startsWithVerb = preg_match('/^(?:' . implode('|', self::ACTION_VERBS) . ')\b/i', $body) === 1;

            if (!$hasNumber && !$hasVerb) {
                continue;
            }
            // Prose without a bullet only counts when it reads as an accomplishment.
            if (!$isBullet && !$startsWithVerb) {
                continue;
            }
            if (preg_match('/@|https?:|www\./i', $body) === 1) {
                continue;
            }
            if (substr_count($body, ',') > 6) {
                continue;
            }

            $body = trim((string) preg_replace('/\s+/', ' ', $body));
            $body = rtrim($body, '.;, ');
            if ($body === '' || in_array($body, $achievements, true)) {
                continue;
            }

            $achievements[] = $body;
            if (count($achievements) >= 6) {
                break;
            }
        }

        return $achievements;
    }
}
