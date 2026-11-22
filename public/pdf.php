<?php

declare(strict_types=1);

use CoverLetterMaker\PdfExporter;
use CoverLetterMaker\Security;

require __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', '0');

/**
 * @param array<string, string> $payload
 */
function pdfError(int $status, string $message): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, private');
    header('X-Content-Type-Options: nosniff');
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_SLASHES);
    exit;
}

Security::startSession();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    pdfError(405, 'Method not allowed.');
}

if (!Security::isValidCsrf($_POST['csrf_token'] ?? null)) {
    pdfError(419, 'Your session expired. Please refresh the page and try again.');
}

$html = (string) ($_POST['html'] ?? '');
$company = Security::cleanLine((string) ($_POST['company'] ?? ''), 120);
$role = Security::cleanLine((string) ($_POST['role'] ?? ''), 120);
$name = Security::cleanLine((string) ($_POST['name'] ?? ''), 80);
$contact = Security::cleanLine((string) ($_POST['contact'] ?? ''), 200);

if (trim($html) === '') {
    pdfError(422, 'There is no letter to export.');
}
if ($company === '' || $role === '') {
    pdfError(422, 'The company and role are required for the PDF filename.');
}

try {
    $exporter = new PdfExporter();
    $pdf = $exporter->render($html, $name, $contact);
    $filename = $exporter->filename($company, $role);

    http_response_code(200);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: private, no-store, max-age=0');
    header('X-Content-Type-Options: nosniff');
    echo $pdf;
} catch (InvalidArgumentException $exception) {
    pdfError(422, $exception->getMessage());
} catch (Throwable $exception) {
    error_log('PDF export failed: ' . $exception->getMessage());
    pdfError(500, 'Could not generate the PDF. Please try again.');
}
