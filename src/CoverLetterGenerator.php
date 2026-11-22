<?php

declare(strict_types=1);

namespace CoverLetterMaker;

use RuntimeException;

/**
 * Assembles a personalized cover letter from parsed resume facts and a tone template.
 *
 * Every value injected into a template is escaped, and every optional fact has a
 * natural-language fallback so the letter never contains an empty placeholder.
 */
final class CoverLetterGenerator
{
    private const TONES = ['formal', 'modern', 'enthusiastic'];

    private const FALLBACK_SKILLS_PHRASE = 'several core technologies';

    private const FALLBACK_ACHIEVEMENT = 'I approach each project with a focus on measurable outcomes and long-term maintainability.';

    /** @var array<string, array<string, mixed>> */
    private array $templates = [];

    public function __construct(
        private readonly SkillsDictionary $dictionary = new SkillsDictionary(),
        private readonly string $templateDirectory = __DIR__ . '/../templates/letters',
    ) {
    }

    /**
     * @param array{
     *     name: ?string, email: ?string, phone: ?string, links: list<string>, years: ?int,
     *     skills: list<array{name: string, category: string, count: int, position: int}>,
     *     titles: list<string>, companies: list<string>, achievements: list<string>
     * } $profile
     * @return array{letter_html: string, plain_text: string, detected: array<string, mixed>}
     */
    public function generate(
        array $profile,
        string $company,
        string $role,
        string $tone,
        string $hiringManager,
        string $jobDescription = ''
    ): array {
        $tone = in_array($tone, self::TONES, true) ? $tone : 'formal';
        $company = Security::cleanLine($company, 120);
        $role = Security::cleanLine($role, 120);
        $hiringManager = $hiringManager === '' ? 'Hiring Manager' : Security::cleanLine($hiringManager, 80);
        $jobDescription = trim($jobDescription);

        if ($company === '' || $role === '') {
            throw new RuntimeException('A company name and a job role are required.');
        }

        $templates = $this->templatesFor($tone);

        $resumeSkills = $profile['skills'];
        $jdSkills = $jobDescription === '' ? [] : $this->dictionary->detect($jobDescription);
        $skills = $this->rankSkills($resumeSkills, $jdSkills);
        $selectedSkills = $this->pickTopSkills($skills);
        $jdMatches = $this->jdMatches($resumeSkills, $jdSkills);

        $recentCompany = $profile['companies'][0] ?? null;
        $recentTitle = $profile['titles'][0] ?? null;

        $context = [
            'company' => Security::escape($company),
            'role' => Security::escape($role),
            'hiring_manager' => Security::escape($hiringManager),
            'name' => Security::escape($profile['name'] ?? ''),
            'years_phrase' => Security::escape($this->yearsPhrase($profile['years'])),
            'duration_phrase' => Security::escape($this->durationPhrase($profile['years'])),
            'skills_phrase' => Security::escape($this->skillsPhrase($selectedSkills)),
            'title_phrase' => Security::escape($recentTitle ?? 'software professional'),
            'recent_company_phrase' => $recentCompany === null ? '' : ' at ' . Security::escape($recentCompany),
            'achievement_sentence' => Security::escape($this->achievementSentence($profile['achievements'])),
        ];

        $paragraphs = [
            $this->fill($this->pick($templates['openings']), $context),
            trim($this->fill($this->pick($templates['experiences']), $context) . ' '
                . $this->fill($this->pick($templates['achievements']), $context) . ' '
                . $this->fill($this->pick($templates['extras']['fit']), $context)),
            trim($this->fill($this->pick($templates['skills']), $context) . ' '
                . $this->fill($this->pick($templates['extras']['craft']), $context) . ' '
                . $this->jdNote($jdMatches, $jdSkills) . ' '
                . $this->fill($this->pick($templates['extras']['bridge']), $context)),
            $this->fill($this->pick($templates['closings']), $context),
        ];

        $paragraphs = array_map([$this, 'tidy'], $paragraphs);

        // Guarantee the 250-word floor even for very sparse resumes and short variations.
        if (str_word_count(implode(' ', $paragraphs)) < 245) {
            $paragraphs[2] = $this->tidy(
                $paragraphs[2] . ' ' . $this->fill($this->pick($templates['extras']['boost']), $context)
            );
        }

        $date = date('F j, Y');
        $greeting = 'Dear ' . $context['hiring_manager'] . ',';
        $signature = $this->signatureHtml((string) ($profile['name'] ?? ''), $profile);
        $valediction = Security::escape((string) ($templates['valediction'] ?? 'Sincerely,'));

        $letterHtml = '<p>' . Security::escape($date) . '</p>'
            . '<p>' . $greeting . '</p>';

        foreach ($paragraphs as $paragraph) {
            $letterHtml .= '<p>' . $paragraph . '</p>';
        }

        $letterHtml .= '<p>' . $valediction . '<br>' . $signature . '</p>';

        $plainText = $this->toPlainText($letterHtml);
        $wordCount = str_word_count($plainText);

        return [
            'letter_html' => $letterHtml,
            'plain_text' => $plainText,
            'detected' => [
                'name' => $profile['name'],
                'email' => $profile['email'],
                'phone' => $profile['phone'],
                'links' => $profile['links'],
                'years' => $profile['years'],
                'skills' => array_slice(array_column($skills, 'name'), 0, 12),
                'all_skills' => array_column($resumeSkills, 'name'),
                'skill_count' => count($resumeSkills),
                'top_skills' => array_column($selectedSkills, 'name'),
                'titles' => $profile['titles'],
                'companies' => $profile['companies'],
                'achievements_found' => count($profile['achievements']),
                'jd_matches' => array_column($jdMatches, 'name'),
                'word_count' => $wordCount,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function templatesFor(string $tone): array
    {
        if (isset($this->templates[$tone])) {
            return $this->templates[$tone];
        }

        $path = rtrim($this->templateDirectory, '/\\') . DIRECTORY_SEPARATOR . $tone . '.php';
        if (!is_file($path)) {
            throw new RuntimeException('Letter template not found: ' . $tone);
        }

        $template = require $path;
        if (!is_array($template)) {
            throw new RuntimeException('Letter template is invalid: ' . $tone);
        }

        foreach (['openings', 'experiences', 'achievements', 'skills', 'closings'] as $section) {
            if (!isset($template[$section]) || !is_array($template[$section]) || $template[$section] === []) {
                throw new RuntimeException('Letter template section missing: ' . $section);
            }
        }

        foreach (['fit', 'craft', 'bridge', 'boost'] as $section) {
            if (!isset($template['extras'][$section]) || !is_array($template['extras'][$section])) {
                throw new RuntimeException('Letter template extras missing: ' . $section);
            }
        }

        $this->templates[$tone] = $template;

        return $template;
    }

    /**
     * @param list<array{name: string, category: string, count: int, position: int}> $resumeSkills
     * @param list<array{name: string, category: string, count: int, position: int}> $jdSkills
     * @return list<array{name: string, category: string, count: int, position: int}>
     */
    private function rankSkills(array $resumeSkills, array $jdSkills): array
    {
        if ($jdSkills === []) {
            return $resumeSkills;
        }

        $jdOrder = [];
        foreach ($jdSkills as $jdSkill) {
            $jdOrder[$jdSkill['name']] = count($jdOrder);
        }

        $matched = [];
        $rest = [];

        foreach ($resumeSkills as $skill) {
            if (isset($jdOrder[$skill['name']])) {
                $matched[] = $skill;
                continue;
            }
            $rest[] = $skill;
        }

        usort($matched, static fn (array $a, array $b): int => $jdOrder[$a['name']] <=> $jdOrder[$b['name']]);

        return array_merge($matched, $rest);
    }

    /**
     * Chooses 3-5 skills, preferring hands-on technical skills over soft skills.
     *
     * @param list<array{name: string, category: string, count: int, position: int}> $skills
     * @return list<array{name: string, category: string, count: int, position: int}>
     */
    private function pickTopSkills(array $skills): array
    {
        $coreCategories = ['languages', 'frameworks', 'databases', 'cloud_devops', 'data_ai'];

        $core = array_values(array_filter(
            $skills,
            static fn (array $skill): bool => in_array($skill['category'], $coreCategories, true)
        ));
        $technical = array_values(array_filter(
            $skills,
            static fn (array $skill): bool => $skill['category'] !== 'soft_skills'
        ));

        $pool = count($core) >= 3 ? $core : (count($technical) >= 3 ? $technical : $skills);

        return array_slice($pool, 0, min(5, max(3, count($pool))));
    }

    /**
     * @param list<array{name: string, category: string, count: int, position: int}> $resumeSkills
     * @param list<array{name: string, category: string, count: int, position: int}> $jdSkills
     * @return list<array{name: string, category: string, count: int, position: int}>
     */
    private function jdMatches(array $resumeSkills, array $jdSkills): array
    {
        if ($jdSkills === []) {
            return [];
        }

        $resumeNames = array_column($resumeSkills, 'name');
        $matches = [];

        foreach ($jdSkills as $jdSkill) {
            if (in_array($jdSkill['name'], $resumeNames, true)) {
                $matches[] = $jdSkill;
            }
        }

        return $matches;
    }

    /**
     * @param list<array{name: string, category: string, count: int, position: int}> $jdMatches
     * @param list<array{name: string, category: string, count: int, position: int}> $jdSkills
     */
    private function jdNote(array $jdMatches, array $jdSkills): string
    {
        if ($jdSkills === []) {
            return '';
        }

        if ($jdMatches === []) {
            return 'I have read your description closely, and the priorities it sets out align with the kind of work I have done throughout my career.';
        }

        $highlight = array_slice(array_map(
            static fn (array $skill): string => Security::escape($skill['name']),
            $jdMatches
        ), 0, 3);

        return 'Your description emphasizes ' . $this->joinList($highlight)
            . ', which map directly to the work I have delivered.';
    }

    /**
     * @param list<array{name: string, category: string, count: int, position: int}> $skills
     */
    private function skillsPhrase(array $skills): string
    {
        if ($skills === []) {
            return self::FALLBACK_SKILLS_PHRASE;
        }

        $names = array_map(static fn (array $skill): string => $skill['name'], $skills);

        return $this->joinList($names);
    }

    /**
     * @param list<string> $items
     */
    private function joinList(array $items): string
    {
        $items = array_values($items);

        if ($items === []) {
            return '';
        }
        if (count($items) === 1) {
            return $items[0];
        }
        if (count($items) === 2) {
            return $items[0] . ' and ' . $items[1];
        }

        $last = array_pop($items);

        return implode(', ', $items) . ', and ' . $last;
    }

    private function yearsPhrase(?int $years): string
    {
        if ($years === null || $years <= 0) {
            return 'a strong track record';
        }
        if ($years === 1) {
            return 'a year of hands-on experience';
        }
        if ($years >= 5) {
            return 'over ' . $years . ' years of hands-on experience';
        }

        return $years . ' years of professional experience';
    }

    private function durationPhrase(?int $years): string
    {
        if ($years === null || $years <= 0) {
            return 'several years';
        }
        if ($years === 1) {
            return 'a year';
        }

        return $years . ' years';
    }

    /**
     * @param list<string> $achievements
     */
    private function achievementSentence(array $achievements): string
    {
        if ($achievements === []) {
            return self::FALLBACK_ACHIEVEMENT;
        }

        $withNumbers = array_values(array_filter(
            $achievements,
            static fn (string $item): bool => preg_match('/\d/', $item) === 1
        ));
        $pool = $withNumbers !== [] ? $withNumbers : $achievements;
        $achievement = $pool[array_rand($pool)];
        $achievement = rtrim(trim($achievement), '.;, ');

        if ($achievement === '') {
            return self::FALLBACK_ACHIEVEMENT;
        }

        if (preg_match('/^I\s+[a-z]/i', $achievement) === 1) {
            return ucfirst($achievement) . '.';
        }

        $verbPattern = '/^(' . implode('|', ResumeParser::ACTION_VERBS) . ')\b/i';
        if (preg_match($verbPattern, $achievement) === 1) {
            return 'In a recent role, I ' . lcfirst($achievement) . '.';
        }

        return 'One accomplishment I am particularly proud of: ' . $achievement . '.';
    }

    /**
     * @param array<string, mixed> $profile
     */
    private function signatureHtml(string $name, array $profile): string
    {
        $lines = [];

        if (trim($name) !== '') {
            $lines[] = Security::escape($name);
        }

        $contact = array_values(array_filter([
            $profile['email'] ?? null,
            $profile['phone'] ?? null,
        ], static fn ($value): bool => is_string($value) && $value !== ''));

        if ($contact !== []) {
            $lines[] = Security::escape(implode(' | ', $contact));
        }

        $link = $profile['links'][0] ?? null;
        if (is_string($link) && $link !== '') {
            $lines[] = Security::escape($link);
        }

        return implode('<br>', $lines);
    }

    /**
     * @param list<string> $variations
     * @param array<string, string> $context
     */
    private function pick(array $variations): string
    {
        return $variations[random_int(0, count($variations) - 1)];
    }

    /**
     * @param array<string, string> $context
     */
    private function fill(string $template, array $context): string
    {
        if (preg_match_all('/\{([a-z_]+)\}/', $template, $matches) > 0) {
            $unknown = array_diff(array_unique($matches[1]), array_keys($context));
            if ($unknown !== []) {
                throw new RuntimeException('Unknown letter placeholder: {' . implode('}, {', $unknown) . '}');
            }
        }

        $replace = [];
        foreach ($context as $key => $value) {
            $replace['{' . $key . '}'] = $value;
        }

        return strtr($template, $replace);
    }

    private function tidy(string $text): string
    {
        $text = (string) preg_replace('/\s+/u', ' ', $text);
        $text = (string) preg_replace('/\s+([,.;:!?])/u', '$1', $text);
        $text = (string) preg_replace('/\.{2,}/u', '.', $text);
        $text = (string) preg_replace('/\(\s+/', '(', $text);

        return trim($text);
    }

    private function toPlainText(string $html): string
    {
        $text = (string) preg_replace('/<br\s*\/?>/i', "\n", $html);
        $text = (string) preg_replace('/<\/p>/i', "\n\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = (string) preg_replace('/[ \t]+/', ' ', $text);
        $text = (string) preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
}
