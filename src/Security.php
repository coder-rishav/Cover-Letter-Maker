<?php

declare(strict_types=1);

namespace CoverLetterMaker;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use InvalidArgumentException;

/**
 * Session, CSRF and sanitization helpers shared by every endpoint.
 */
final class Security
{
    public const ALLOWED_HTML_TAGS = ['p', 'br', 'strong', 'em', 'ul', 'li'];

    private const SESSION_KEY = '_clm_csrf';

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public static function csrfToken(): string
    {
        self::startSession();

        if (!isset($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function isValidCsrf(?string $token): bool
    {
        self::startSession();

        return is_string($token)
            && isset($_SESSION[self::SESSION_KEY])
            && is_string($_SESSION[self::SESSION_KEY])
            && hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    public static function escape(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Normalizes a single-line user input.
     */
    public static function cleanLine(string $value, int $maxLength = 120): string
    {
        $value = strip_tags($value);
        $value = (string) preg_replace('/[^\P{C}\n]/u', '', $value);
        $value = (string) preg_replace('/\s+/u', ' ', $value);
        $value = trim($value);

        if (mb_strlen($value) > $maxLength) {
            $value = mb_substr($value, 0, $maxLength);
            $value = rtrim($value);
        }

        return $value;
    }

    /**
     * Rebuilds letter HTML from an allowlist of tags. All attributes are dropped.
     */
    public static function sanitizeLetterHtml(string $html, int $maxLength = 200_000): string
    {
        if (strlen($html) > $maxLength) {
            throw new InvalidArgumentException('The letter is too long to export.');
        }

        if (trim($html) === '') {
            throw new InvalidArgumentException('The letter is empty.');
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="clm-letter-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($loaded === false) {
            throw new InvalidArgumentException('The letter could not be read.');
        }

        $root = $document->getElementById('clm-letter-root');
        if ($root === null) {
            throw new InvalidArgumentException('The letter could not be read.');
        }

        $clean = trim(self::serializeNode($root));
        $clean = (string) preg_replace('/(?:<p>\s*<\/p>|<li>\s*<\/li>)/u', '', $clean);
        $clean = trim($clean);

        if ($clean === '') {
            throw new InvalidArgumentException('The letter is empty after sanitization.');
        }

        return $clean;
    }

    public static function safeFilenamePart(string $value): string
    {
        $value = (string) preg_replace('/[^A-Za-z0-9_-]+/', '_', $value);
        $value = trim($value, '_');
        $value = (string) preg_replace('/_{2,}/', '_', $value);

        if (mb_strlen($value) > 40) {
            $value = rtrim(mb_substr($value, 0, 40), '_');
        }

        return $value === '' ? 'Letter' : $value;
    }

    private static function serializeNode(DOMNode $node): string
    {
        $output = '';

        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMText) {
                $output .= self::escape($child->nodeValue);
                continue;
            }

            if (!$child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'head'], true)) {
                continue;
            }

            if ($tag === 'br') {
                $output .= '<br>';
                continue;
            }

            if ($tag === 'b' || $tag === 'i') {
                $tag = $tag === 'b' ? 'strong' : 'em';
            }

            $inner = self::serializeNode($child);
            $output .= in_array($tag, self::ALLOWED_HTML_TAGS, true)
                ? '<' . $tag . '>' . $inner . '</' . $tag . '>'
                : $inner;
        }

        return $output;
    }
}
