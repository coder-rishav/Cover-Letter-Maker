<?php

declare(strict_types=1);

/**
 * CLI smoke test: parses samples/sample_resume.txt and prints one letter per tone.
 *
 * Usage: php bin/test-generate.php [--minimal] [--job-description]
 */

require __DIR__ . '/../vendor/autoload.php';

use CoverLetterMaker\CoverLetterGenerator;
use CoverLetterMaker\ResumeParser;

$options = $argv;
$useMinimal = in_array('--minimal', $options, true);
$useJobDescription = in_array('--job-description', $options, true);

$resumeText = $useMinimal
    ? "Alex Kim\nalex.kim@example.com\nEXPERIENCE\n- Built internal tools with PHP and MySQL."
    : (string) file_get_contents(__DIR__ . '/../samples/sample_resume.txt');

$jobDescription = $useJobDescription
    ? 'We are hiring a Senior Software Engineer with strong PHP, Laravel, MySQL, AWS, Docker '
        . 'and Kubernetes experience. You will build REST APIs, mentor engineers, and improve CI/CD.'
    : '';

$parser = new ResumeParser();
$generator = new CoverLetterGenerator();
$profile = $parser->parse($resumeText);

echo "=== DETECTED ===\n";
echo 'Name: ', $profile['name'] ?? '(not found)', "\n";
echo 'Email: ', $profile['email'] ?? '(not found)', "\n";
echo 'Phone: ', $profile['phone'] ?? '(not found)', "\n";
echo 'Years: ', $profile['years'] === null ? '(not found)' : (string) $profile['years'], "\n";
echo 'Skills: ', implode(', ', array_column($profile['skills'], 'name')), "\n";
echo 'Titles: ', implode(', ', $profile['titles']), "\n";
echo 'Companies: ', implode(', ', $profile['companies']), "\n";
echo 'Achievements: ', count($profile['achievements']), " found\n";

$failures = 0;

foreach (['formal', 'modern', 'enthusiastic'] as $tone) {
    $result = $generator->generate(
        $profile,
        'Northwind & Co.',
        'Senior PHP Engineer',
        $tone,
        'Priya Sharma',
        $jobDescription
    );

    $letter = $result['letter_html'];
    $plain = $result['plain_text'];

    echo "\n=== {$tone} (", $result['detected']['word_count'], " words) ===\n";
    echo $plain, "\n";

    if (preg_match('/\{[a-z_]+\}/', $letter) === 1) {
        echo "FAIL: unresolved placeholder\n";
        $failures++;
    }
    if ($result['detected']['word_count'] < 250) {
        echo "WARN: shorter than the 250-word target\n";
    }
    if (str_contains($letter, '&lt;script&gt;') === false && str_contains($letter, '<script>')) {
        echo "FAIL: unescaped HTML found\n";
        $failures++;
    }
}

// Escaping check with hostile input.
$hostile = $generator->generate(
    $profile,
    '<script>alert(1)</script>',
    'Engineer & "Lead"',
    'formal',
    'Manager'
);
if (str_contains($hostile['letter_html'], '<script>')) {
    echo "\nFAIL: company/role values were not escaped\n";
    $failures++;
}

exit($failures === 0 ? 0 : 1);
