<?php
/**
 * Rich-text helpers for public pages (news articles).
 * Editor-written bodies are stored as "<!--rte-->" + clean HTML. Older articles are plain text
 * (blank line = new paragraph); both are shown well formatted.
 */
require_once __DIR__ . '/newsletter_template.php';   // sanitiser lives there

if (!function_exists('anu_rt_plain_to_html')) {

    /** Plain text -> paragraphs, line breaks, "- " bullet lists and clickable web addresses. */
    function anu_rt_plain_to_html(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));
        if ($text === '') return '';
        $inline = function (string $s): string {
            $s = htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
            return preg_replace('~(https?://[^\s<]+[^\s<.,;:!?)])~', '<a href="$1" rel="noopener">$1</a>', $s);
        };
        $out = '';
        foreach (preg_split("/\n{2,}/", $text) as $block) {
            $lines = explode("\n", $block);
            $isList = count(array_filter($lines, fn($l) => preg_match('/^\s*[-*•]\s+/', $l))) === count($lines);
            if ($isList) {
                $out .= '<ul>';
                foreach ($lines as $l) $out .= '<li>' . $inline(preg_replace('/^\s*[-*•]\s+/', '', $l)) . '</li>';
                $out .= '</ul>';
            } else {
                $out .= '<p>' . implode('<br>', array_map($inline, $lines)) . '</p>';
            }
        }
        return $out;
    }

    /** Body of an article, ready to print inside <div class="news-body">. */
    function anu_rt_public_html(string $body): string
    {
        $body = trim($body);
        if ($body === '') return '';
        return anu_nl_is_rich($body) ? anu_nl_sanitize($body) : anu_rt_plain_to_html($body);
    }
}
