<?php
/*
 * A small, safe Markdown subset for job descriptions:
 *   ## Heading        - bullet / * bullet      1. numbered
 *   **bold**  *italic*  [link text](https://...)
 * Everything is escaped first, so pasted HTML shows as text and can't run.
 */
declare(strict_types=1);

function md_inline(string $escaped): string
{
    $s = (string) preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped);
    $s = (string) preg_replace('/(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/s', '<em>$1</em>', $s);
    $s = (string) preg_replace_callback('/\[([^\]]+)\]\(((?:https?:\/\/|mailto:)[^\s)]+)\)/i', static function (array $m): string {
        return '<a href="' . $m[2] . '" rel="nofollow noopener" target="_blank">' . $m[1] . '</a>';
    }, $s);
    return $s;
}

function markdown(?string $text): string
{
    $text = str_replace(["\r\n", "\r"], "\n", trim((string) $text));
    if ($text === '') {
        return '';
    }
    $lines = explode("\n", e($text));
    $html = '';
    $para = [];
    $list = null;
    $items = [];

    $flushPara = static function () use (&$para, &$html): void {
        if ($para) {
            $html .= '<p>' . md_inline(implode('<br>', $para)) . '</p>';
            $para = [];
        }
    };
    $flushList = static function () use (&$list, &$items, &$html): void {
        if ($list) {
            $html .= '<' . $list . '>';
            foreach ($items as $it) {
                $html .= '<li>' . md_inline($it) . '</li>';
            }
            $html .= '</' . $list . '>';
            $list = null;
            $items = [];
        }
    };

    foreach ($lines as $raw) {
        $line = rtrim($raw);
        if (trim($line) === '') {
            $flushPara();
            $flushList();
            continue;
        }
        if (preg_match('/^\s*(#{1,4})\s+(.+)$/', $line, $m)) {
            $flushPara();
            $flushList();
            $level = strlen($m[1]) <= 2 ? 'h3' : 'h4';
            $html .= '<' . $level . '>' . md_inline($m[2]) . '</' . $level . '>';
            continue;
        }
        if (preg_match('/^\s*[-*•]\s+(.+)$/u', $line, $m)) {
            $flushPara();
            if ($list !== 'ul') {
                $flushList();
                $list = 'ul';
            }
            $items[] = $m[1];
            continue;
        }
        if (preg_match('/^\s*\d+[.)]\s+(.+)$/', $line, $m)) {
            $flushPara();
            if ($list !== 'ol') {
                $flushList();
                $list = 'ol';
            }
            $items[] = $m[1];
            continue;
        }
        $flushList();
        $para[] = trim($line);
    }
    $flushPara();
    $flushList();
    return $html;
}

/** Plain text version for meta descriptions and structured data. */
function markdown_plain(?string $text): string
{
    $s = (string) preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', (string) $text);
    $s = (string) preg_replace('/^\s*(#{1,4}|[-*•]|\d+[.)])\s+/mu', '', $s);
    $s = str_replace(['**', '*'], '', $s);
    return trim((string) preg_replace('/\s+/', ' ', $s));
}
