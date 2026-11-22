<?php

declare(strict_types=1);

namespace CoverLetterMaker;

use InvalidArgumentException;
use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;

/**
 * Validates an uploaded resume, extracts its plain text, and deletes the temp file.
 *
 * The file lives on disk only for the duration of parsing; nothing is stored.
 */
final class ResumeFileReader
{
    public const MAX_BYTES = 5 * 1024 * 1024;

    private const MIME_TYPES = [
        'pdf' => ['application/pdf'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'txt' => ['text/plain'],
    ];

    public function __construct(
        private readonly string $tempDirectory = __DIR__ . '/../storage/tmp'
    ) {
    }

    /**
     * @param array<string, mixed> $file Entry from $_FILES
     */
    public function read(array $file): string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException($this->uploadErrorMessage($error));
        }

        $tmpName = $file['tmp_name'] ?? '';
        $originalName = $file['name'] ?? '';
        $size = (int) ($file['size'] ?? 0);

        if (!is_string($tmpName) || $tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new InvalidArgumentException('The upload could not be verified. Please try again.');
        }

        if (!is_string($originalName) || $originalName === '') {
            throw new InvalidArgumentException('The uploaded file is missing a name.');
        }

        if ($size <= 0) {
            throw new InvalidArgumentException('The uploaded file is empty.');
        }

        if ($size > self::MAX_BYTES) {
            throw new InvalidArgumentException('The file is larger than the 5MB limit.');
        }

        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        if (!isset(self::MIME_TYPES[$extension])) {
            throw new InvalidArgumentException('Please upload a PDF, DOCX, or TXT file.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpName);
        if (!is_string($mime) || !in_array($mime, self::MIME_TYPES[$extension], true)) {
            throw new InvalidArgumentException('The file contents do not match its .' . $extension . ' extension.');
        }

        if (!is_dir($this->tempDirectory) && !mkdir($this->tempDirectory, 0775, true) && !is_dir($this->tempDirectory)) {
            throw new InvalidArgumentException('The upload directory is not available.');
        }

        $storedPath = $this->tempDirectory . DIRECTORY_SEPARATOR
            . bin2hex(random_bytes(16)) . '.' . $extension;

        if (!move_uploaded_file($tmpName, $storedPath)) {
            throw new InvalidArgumentException('The upload could not be saved for processing.');
        }

        try {
            $text = match ($extension) {
                'pdf' => $this->readPdf($storedPath),
                'docx' => $this->readDocx($storedPath),
                default => $this->readText($storedPath),
            };
        } finally {
            if (is_file($storedPath)) {
                @unlink($storedPath);
            }
        }

        $text = trim($text);
        if (mb_strlen($text) < 30) {
            throw new InvalidArgumentException(
                'We could not read enough text from that file. If it is a scanned PDF, please paste the resume text instead.'
            );
        }

        return $text;
    }

    private function readPdf(string $path): string
    {
        try {
            $pdf = (new PdfParser())->parseFile($path);
            $text = $pdf->getText();
        } catch (Throwable) {
            throw new InvalidArgumentException('That PDF could not be read. Please try a different file.');
        }

        return (string) $text;
    }

    private function readDocx(string $path): string
    {
        try {
            $document = IOFactory::load($path);
        } catch (Throwable) {
            throw new InvalidArgumentException('That DOCX file could not be read. Please try a different file.');
        }

        $text = '';
        foreach ($document->getSections() as $section) {
            $text .= $this->collect($section) . "\n";
        }

        return $text;
    }

    private function readText(string $path): string
    {
        $text = file_get_contents($path);
        if ($text === false) {
            throw new InvalidArgumentException('That text file could not be read.');
        }

        if (!mb_check_encoding($text, 'UTF-8')) {
            $converted = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
            $text = is_string($converted) ? $converted : $text;
        }

        return $text;
    }

    private function collect(mixed $element): string
    {
        if (!is_object($element)) {
            return '';
        }

        if ($element instanceof TextBreak) {
            return "\n";
        }

        if ($element instanceof TextRun) {
            return $element->getText() . "\n";
        }

        if ($element instanceof Table) {
            $rows = '';
            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    $rows .= $this->collect($cell) . ' ';
                }
                $rows .= "\n";
            }

            return $rows;
        }

        if ($element instanceof AbstractContainer) {
            $text = '';
            foreach ($element->getElements() as $child) {
                $text .= $this->collect($child);
            }

            return $text;
        }

        if (method_exists($element, 'getText')) {
            $text = $element->getText();
            if (is_string($text)) {
                return $text . "\n";
            }
        }

        return '';
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The file is larger than the allowed limit.',
            UPLOAD_ERR_PARTIAL => 'The upload did not finish. Please try again.',
            UPLOAD_ERR_NO_FILE => 'Please choose a resume file to upload.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'The server could not receive the file. Please try again.',
            default => 'The upload failed. Please try again.',
        };
    }
}
