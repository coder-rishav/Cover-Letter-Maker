<?php

declare(strict_types=1);

use CoverLetterMaker\CoverLetterGenerator;
use CoverLetterMaker\ResumeFileReader;
use CoverLetterMaker\ResumeParser;
use CoverLetterMaker\Security;

require __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');

Security::startSession();

/**
 * @param array<string, mixed> $payload
 */
function respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    respond(405, ['ok' => false, 'error' => 'Method not allowed.']);
}

if (!Security::isValidCsrf($_POST['csrf_token'] ?? null)) {
    respond(419, ['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.']);
}

$company = Security::cleanLine((string) ($_POST['company'] ?? ''), 120);
$role = Security::cleanLine((string) ($_POST['role'] ?? ''), 120);
$hiringManager = Security::cleanLine((string) ($_POST['hiring_manager'] ?? ''), 80);
$tone = (string) ($_POST['tone'] ?? 'formal');
$jobDescription = trim((string) ($_POST['job_description'] ?? ''));
$resumeText = trim((string) ($_POST['resume_text'] ?? ''));

if ($company === '') {
    respond(422, ['ok' => false, 'error' => 'Please add the company name.']);
}
if ($role === '') {
    respond(422, ['ok' => false, 'error' => 'Please add the job role you are applying for.']);
}
if (!in_array($tone, ['formal', 'modern', 'enthusiastic'], true)) {
    $tone = 'formal';
}
if (mb_strlen($jobDescription) > 15_000) {
    respond(422, ['ok' => false, 'error' => 'The job description is too long. Please shorten it.']);
}
if (mb_strlen($resumeText) > 60_000) {
    respond(422, ['ok' => false, 'error' => 'The resume text is too long. Please shorten it.']);
}

if ($hiringManager === '') {
    $hiringManager = 'Hiring Manager';
}

try {
    $upload = $_FILES['resume_file'] ?? null;
    $hasUpload = is_array($upload) && (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

    if ($hasUpload) {
        /** @var array<string, mixed> $upload */
        $resumeText = (new ResumeFileReader())->read($upload);
    }

    if (mb_strlen($resumeText) > 60_000) {
        respond(422, ['ok' => false, 'error' => 'The resume contains too much text. Please trim it and try again.']);
    }

    if (trim($resumeText) === '') {
        respond(422, ['ok' => false, 'error' => 'Please paste your resume or upload a file.']);
    }
    if (mb_strlen($resumeText) < 30) {
        respond(422, ['ok' => false, 'error' => 'The resume is too short to generate a meaningful letter.']);
    }

    $profile = (new ResumeParser())->parse($resumeText);
    $result = (new CoverLetterGenerator())->generate(
        $profile,
        $company,
        $role,
        $tone,
        $hiringManager,
        $jobDescription
    );

    respond(200, [
        'ok' => true,
        'letter_html' => $result['letter_html'],
        'plain_text' => $result['plain_text'],
        'detected' => $result['detected'],
    ]);
} catch (InvalidArgumentException $exception) {
    respond(422, ['ok' => false, 'error' => $exception->getMessage()]);
} catch (Throwable $exception) {
    error_log('Cover letter generation failed: ' . $exception->getMessage());
    respond(500, ['ok' => false, 'error' => 'Something went wrong while building the letter. Please try again.']);
}
