<?php
/**
 * Newsletter email template (table-based HTML with inline styles so it renders
 * in Gmail, Outlook and phones) + a plain-text version.
 * Brand: red #FF0000, gold #FAC823, black. Uses only real site data.
 */

if (!function_exists('anu_nl_origin')) {

    /** Public address used for links/images inside emails (SITE_URL + BASE_URL). */
    function anu_nl_origin(): string
    {
        $site = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        return $site . $base;
    }

    function anu_nl_e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

    /** Turn the admin's plain text into safe email HTML: paragraphs, **bold**, "- " bullets, auto-linked URLs. */
    function anu_nl_format_body(string $text, string $color = '#222222'): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));
        if ($text === '') return '';
        $blocks = preg_split("/\n{2,}/", $text);
        $pStyle = 'margin:0 0 16px 0;font-size:16px;line-height:1.6;color:' . $color . ';';
        $inline = function (string $s): string {
            $s = anu_nl_e($s);
            $s = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $s);
            $s = preg_replace('~(https?://[^\s<]+[^\s<.,;:!?)])~', '<a href="$1" style="color:#000000;text-decoration:underline;">$1</a>', $s);
            return $s;
        };
        $out = '';
        foreach ($blocks as $b) {
            $lines = explode("\n", $b);
            $isList = count($lines) > 0 && count(array_filter($lines, fn($l) => preg_match('/^\s*[-*•]\s+/', $l))) === count($lines);
            if ($isList) {
                $out .= '<ul style="margin:0 0 16px 0;padding-left:22px;font-size:16px;line-height:1.6;color:' . $color . ';">';
                foreach ($lines as $l) $out .= '<li style="margin-bottom:6px;">' . $inline(preg_replace('/^\s*[-*•]\s+/', '', $l)) . '</li>';
                $out .= '</ul>';
            } else {
                $out .= '<p style="' . $pStyle . '">' . implode('<br>', array_map($inline, $lines)) . '</p>';
            }
        }
        return $out;
    }

    /* ------------------------------------------------------------------
     * Rich-text (editor) bodies
     * The editor stores HTML prefixed with <!--rte-->. Older newsletters
     * (plain text with **bold**) keep working through anu_nl_format_body().
     * ------------------------------------------------------------------ */

    function anu_nl_is_rich(string $body): bool
    {
        return strncmp(ltrim($body), '<!--rte-->', 10) === 0;
    }

    /** Is this image address one we allow inside a newsletter? (our own uploads, or an https image) */
    function anu_nl_img_ok(string $src): bool
    {
        $src = trim($src);
        if ($src === '' || strpos($src, '..') !== false) return false;
        foreach (['newsletters', 'news-body'] as $folder) {
            $local = (defined('UPLOADS_URL') ? UPLOADS_URL : '/assets/uploads') . '/' . $folder . '/';
            if (strncmp($src, $local, strlen($local)) === 0) return (bool) preg_match('~^[A-Za-z0-9._/-]+$~', $src);
        }
        return (bool) preg_match('~^https://[^\s"\'<>]+$~i', $src);
    }

    function anu_nl_css_ok(string $prop, string $val): bool
    {
        $val = trim($val);
        if ($prop === 'text-align') return in_array($val, ['left', 'center', 'right', 'justify'], true);
        if ($prop === 'color' || $prop === 'background-color') {
            return (bool) preg_match('/^(#[0-9a-fA-F]{3,8}|rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*[01]?\.?\d*\s*)?\)|[a-zA-Z]{3,20})$/', $val);
        }
        return false;
    }

    function anu_nl_parse_html(string $html): ?array
    {
        if (!class_exists('DOMDocument')) return null;
        $prev = libxml_use_internal_errors(true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="anu-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        $root = null;
        foreach ($doc->getElementsByTagName('div') as $d) { if ($d->getAttribute('id') === 'anu-root') { $root = $d; break; } }
        return $root ? [$doc, $root] : null;
    }

    function anu_nl_inner_html(DOMDocument $doc, DOMNode $root): string
    {
        $out = '';
        foreach ($root->childNodes as $c) $out .= $doc->saveHTML($c);
        return $out;
    }

    /** Whitelist-clean the editor's HTML (no scripts, no event handlers, no odd styles/links/images). */
    function anu_nl_sanitize(string $html): string
    {
        $html = trim(preg_replace('/^\s*<!--rte-->/', '', trim($html)));
        if ($html === '') return '';
        $parsed = anu_nl_parse_html($html);
        if (!$parsed) {
            // No DOM extension on this server: keep a small set of tags and drop every attribute.
            $h = strip_tags($html, '<p><br><strong><b><em><i><u><s><ul><ol><li><h2><h3><blockquote>');
            return preg_replace('/<(\w+)\s[^>]*>/', '<$1>', $h);
        }
        [$doc, $root] = $parsed;
        $allowed = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'blockquote', 'img', 'span'];
        $drop = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'svg', 'math', 'link', 'meta', 'head', 'title', 'noscript', 'template', 'video', 'audio'];

        $walk = function (DOMNode $node) use (&$walk, $allowed, $drop) {
            foreach (iterator_to_array($node->childNodes) as $c) {
                if ($c->nodeType === XML_COMMENT_NODE || $c->nodeType === XML_PI_NODE) { $node->removeChild($c); continue; }
                if ($c->nodeType !== XML_ELEMENT_NODE) continue;
                $tag = strtolower($c->nodeName);
                if (in_array($tag, $drop, true)) { $node->removeChild($c); continue; }
                if (!in_array($tag, $allowed, true)) {           // unknown tag: keep its content only
                    $walk($c);
                    while ($c->firstChild) $node->insertBefore($c->firstChild, $c);
                    $node->removeChild($c);
                    continue;
                }
                $attrs = [];
                foreach (iterator_to_array($c->attributes) as $a) $attrs[strtolower($a->nodeName)] = $a->nodeValue;
                foreach (array_keys($attrs) as $k) $c->removeAttribute($k);

                $styles = [];
                if (!empty($attrs['class']) && preg_match('/ql-align-(center|right|justify)/', $attrs['class'], $m)) $styles['text-align'] = $m[1];
                if (!empty($attrs['style'])) {
                    foreach (explode(';', $attrs['style']) as $decl) {
                        $kv = explode(':', $decl, 2);
                        if (count($kv) === 2 && anu_nl_css_ok(strtolower(trim($kv[0])), $kv[1])) $styles[strtolower(trim($kv[0]))] = trim($kv[1]);
                    }
                }
                if ($tag === 'a') {
                    $href = trim($attrs['href'] ?? '');
                    if (!preg_match('~^[a-z][a-z0-9+.-]*:~i', $href) && preg_match('~^(www\.)?[a-z0-9-]+(\.[a-z0-9-]+)+(/|$|\?|#)~i', $href)) $href = 'https://' . $href;   // "www.anu.ac.ke" typed without https://
                    if (!preg_match('~^(https?://|mailto:|tel:)[^\s"\'<>]+$~i', $href)) { $walk($c); while ($c->firstChild) $node->insertBefore($c->firstChild, $c); $node->removeChild($c); continue; }
                    $c->setAttribute('href', $href);
                } elseif ($tag === 'img') {
                    if (!anu_nl_img_ok($attrs['src'] ?? '')) { $node->removeChild($c); continue; }
                    $c->setAttribute('src', trim($attrs['src']));
                    $c->setAttribute('alt', mb_substr((string)($attrs['alt'] ?? ''), 0, 200));
                    if (!empty($attrs['width']) && ctype_digit($attrs['width']) && (int)$attrs['width'] >= 20 && (int)$attrs['width'] <= 536) $c->setAttribute('width', $attrs['width']);
                    $styles = [];
                }
                if ($styles) { $css = ''; foreach ($styles as $k => $v) $css .= $k . ':' . $v . ';'; $c->setAttribute('style', $css); }
                $walk($c);
            }
        };
        $walk($root);
        return trim(anu_nl_inner_html($doc, $root));
    }

    /**
     * Turn clean editor HTML into email-safe HTML with inline styles.
     * $mode 'preview' keeps image addresses as they are (for the admin preview);
     * 'email' embeds our own uploaded images inside the message (cid:) so they
     * show even when the public address is wrong or images are blocked.
     */
    function anu_nl_style_html(string $clean, string $mode, array &$images, string $color = '#222222'): string
    {
        $parsed = anu_nl_parse_html($clean);
        if (!$parsed) return $clean;
        [$doc, $root] = $parsed;
        $base = 'font-size:16px;line-height:1.6;color:' . $color . ';';
        $map = [
            'p'          => 'margin:0 0 16px 0;' . $base,
            'h1'         => 'margin:24px 0 10px 0;font-size:24px;line-height:1.25;color:#000000;font-weight:800;',
            'h2'         => 'margin:24px 0 10px 0;font-size:22px;line-height:1.25;color:#000000;font-weight:800;',
            'h3'         => 'margin:20px 0 8px 0;font-size:18px;line-height:1.3;color:#000000;font-weight:700;',
            'ul'         => 'margin:0 0 16px 0;padding-left:24px;' . $base,
            'ol'         => 'margin:0 0 16px 0;padding-left:24px;' . $base,
            'li'         => 'margin:0 0 6px 0;' . $base,
            'blockquote' => 'margin:0 0 16px 0;padding:6px 0 6px 16px;border-left:4px solid #FAC823;color:#444444;font-size:16px;line-height:1.6;',
            'a'          => 'color:#000000;text-decoration:underline;',
        ];
        $rootDir = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
        $uploadsUrl = defined('UPLOADS_URL') ? UPLOADS_URL : '/assets/uploads';
        $origin = anu_nl_origin();
        $embedded = 0;

        $walk = function (DOMNode $node) use (&$walk, $map, $mode, &$images, $rootDir, $uploadsUrl, $origin, &$embedded) {
            foreach (iterator_to_array($node->childNodes) as $c) {
                if ($c->nodeType !== XML_ELEMENT_NODE) continue;
                $tag = strtolower($c->nodeName);
                // drop blank spacer paragraphs the editor leaves behind
                if ($tag === 'p' && trim(str_replace("\xC2\xA0", ' ', $c->textContent)) === '' && !$c->getElementsByTagName('img')->length) { $node->removeChild($c); continue; }
                if (isset($map[$tag])) {
                    $c->setAttribute('style', $map[$tag] . $c->getAttribute('style'));
                    if ($tag === 'a') { $c->setAttribute('target', '_blank'); $c->setAttribute('rel', 'noopener'); }
                }
                if ($tag === 'img') {
                    $src = $c->getAttribute('src');
                    $local = strncmp($src, $uploadsUrl . '/newsletters/', strlen($uploadsUrl . '/newsletters/')) === 0;
                    if ($local && $mode === 'email') {
                        $path = $rootDir . '/assets/uploads/' . substr($src, strlen($uploadsUrl) + 1);
                        $size = is_file($path) ? filesize($path) : 0;
                        if ($size > 0 && $embedded + $size <= 6 * 1024 * 1024) {
                            $cid = md5($path) . '@anu.newsletter';
                            $images[$cid] = ['cid' => $cid, 'path' => $path, 'mime' => (function_exists('mime_content_type') ? mime_content_type($path) : 'image/jpeg') ?: 'image/jpeg', 'name' => basename($path)];
                            $embedded += $size;
                            $c->setAttribute('src', 'cid:' . $cid);
                        } else {
                            $c->setAttribute('src', $origin . '/' . ltrim(substr($src, strlen(defined('BASE_URL') ? BASE_URL : '')), '/'));
                        }
                    }
                    $c->setAttribute('style', 'display:block;border:0;max-width:100%;height:auto;margin:0 0 16px 0;');
                }
                $walk($c);
            }
        };
        $walk($root);
        return anu_nl_inner_html($doc, $root);
    }

    /** Plain-text version of clean editor HTML. */
    function anu_nl_html_to_text(string $clean): string
    {
        $h = preg_replace_callback('~<img[^>]*>~i', function ($m) { return preg_match('~alt="([^"]+)"~i', $m[0], $a) ? '[' . $a[1] . ']' : ''; }, $clean);
        $h = preg_replace_callback('~<a\s[^>]*href="([^"]+)"[^>]*>(.*?)</a>~is', function ($m) {
            $t = trim(strip_tags($m[2])); $u = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            return ($t === '' || $t === $u) ? $u : $t . ' (' . $u . ')';
        }, $h);
        $h = preg_replace('~<li[^>]*>~i', "\n- ", $h);
        $h = preg_replace('~</(p|h[1-6]|ul|ol|blockquote)>~i', "\n\n", $h);
        $h = preg_replace('~<br\s*/?>~i', "\n", $h);
        $t = html_entity_decode(strip_tags($h), ENT_QUOTES, 'UTF-8');
        $t = str_replace("\xC2\xA0", ' ', $t);
        return trim(preg_replace("/\n{3,}/", "\n\n", preg_replace('/[ \t]+\n/', "\n", $t)));
    }

    /** Body as email HTML (rich editor body or legacy plain text). */
    function anu_nl_body_html(string $body, string $mode, array &$images): string
    {
        if (anu_nl_is_rich($body)) return anu_nl_style_html(anu_nl_sanitize($body), $mode, $images);
        return anu_nl_format_body($body);
    }

    /** Legacy plain-text body -> clean HTML for loading into the editor. */
    function anu_nl_to_editor_html(string $body): string
    {
        $html = anu_nl_is_rich($body) ? anu_nl_sanitize($body) : anu_nl_sanitize(anu_nl_format_body($body));
        // the editor reads alignment from classes
        return preg_replace_callback('~<(p|h1|h2|h3|li|blockquote)\b([^>]*?)\sstyle="([^"]*)"~i', function ($m) {
            return preg_match('/text-align:\s*(center|right|justify)/', $m[3], $a) ? '<' . $m[1] . $m[2] . ' class="ql-align-' . $a[1] . '" style="' . $m[3] . '"' : $m[0];
        }, $html);
    }

    /** Latest published news / upcoming events pulled from the real tables. */
    function anu_nl_load_news(int $limit = 3): array
    {
        $scope = function_exists('unit_scope_sql') ? unit_scope_sql('news') : '';
        return db()->query("SELECT title, slug, excerpt, published_at FROM news WHERE is_published=1{$scope} ORDER BY published_at DESC LIMIT " . (int)$limit)->fetchAll();
    }

    function anu_nl_load_events(int $limit = 3): array
    {
        $scope = function_exists('unit_scope_sql') ? unit_scope_sql('events') : '';
        return db()->query("SELECT title, slug, location, start_datetime FROM events WHERE is_published=1{$scope} AND start_datetime >= NOW() ORDER BY start_datetime ASC LIMIT " . (int)$limit)->fetchAll();
    }

    /**
     * @param array $nl   newsletter row (subject, preheader, headline, body, button_text, button_url, include_news, include_events)
     * @param array|null $sub subscriber row (full_name) or null for previews
     * @param string $mode 'email' (images embedded in the message) or 'preview' (admin screen)
     * @return array{0:string,1:string,2:array} [html, text, inline images]
     */
    function anu_nl_render(array $nl, ?array $sub, string $unsubUrl, array $news = [], array $events = [], string $mode = 'email'): array
    {
        $origin  = anu_nl_origin();
        $images  = [];
        $logoFile = realpath(__DIR__ . '/../assets/images/anu-logo.png');
        if ($mode === 'preview') {
            $logo = (defined('BASE_URL') ? BASE_URL : '') . '/assets/images/anu-logo.png';
        } elseif ($logoFile && is_file($logoFile)) {
            $cid = 'anu-logo@anu.newsletter';
            $images[$cid] = ['cid' => $cid, 'path' => $logoFile, 'mime' => 'image/png', 'name' => 'anu-logo.png'];
            $logo = 'cid:' . $cid;
        } else {
            $logo = $origin . '/assets/images/anu-logo.png';
        }
        $address = setting('address');
        $phone   = setting('phone_primary');
        $email   = setting('email_marketing');
        $first   = '';
        if ($sub && !empty($sub['full_name'])) { $parts = preg_split('/\s+/', trim($sub['full_name'])); $first = $parts[0] ?? ''; }
        $greeting = $first !== '' ? 'Dear ' . $first . ',' : 'Hello,';
        $pre = trim((string)($nl['preheader'] ?? ''));
        $btnText = trim((string)($nl['button_text'] ?? ''));
        $btnUrl  = trim((string)($nl['button_url'] ?? ''));
        $e = 'anu_nl_e';

        $social = [];
        foreach (['facebook_url' => 'Facebook', 'twitter_url' => 'X', 'instagram_url' => 'Instagram', 'linkedin_url' => 'LinkedIn', 'youtube_url' => 'YouTube'] as $k => $label) {
            $u = setting($k);
            if ($u !== '') $social[] = '<a href="' . $e($u) . '" style="color:#FAC823;text-decoration:none;font-weight:700;">' . $label . '</a>';
        }

        $h  = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
        $h .= '<meta name="x-apple-disable-message-reformatting"><title>' . $e($nl['subject']) . '</title></head>';
        $h .= '<body style="margin:0;padding:0;background:#f1f1ee;">';
        if ($pre !== '') $h .= '<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;font-size:1px;line-height:1px;">' . $e($pre) . str_repeat('&nbsp;&zwnj;', 30) . '</div>';
        $h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f1f1ee;"><tr><td align="center" style="padding:24px 12px;">';
        $h .= '<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#ffffff;font-family:Arial,Helvetica,sans-serif;border-radius:10px;overflow:hidden;">';

        // brand stripe + logo
        $h .= '<tr><td style="height:6px;background:#FF0000;font-size:0;line-height:0;">&nbsp;</td></tr>';
        $h .= '<tr><td align="center" style="padding:22px 24px 18px 24px;background:#ffffff;"><a href="' . $e($origin ?: '#') . '"><img src="' . $e($logo) . '" alt="Africa Nazarene University" height="52" style="display:block;border:0;height:52px;width:auto;"></a></td></tr>';
        $h .= '<tr><td style="height:4px;background:#FAC823;font-size:0;line-height:0;">&nbsp;</td></tr>';

        // headline block
        $h .= '<tr><td style="background:#000000;padding:34px 32px 30px 32px;"><h1 style="margin:0;font-size:28px;line-height:1.2;color:#ffffff;font-weight:800;">' . $e($nl['headline']) . '</h1>';
        $h .= '<div style="width:56px;height:4px;background:#FAC823;margin-top:16px;font-size:0;line-height:0;">&nbsp;</div></td></tr>';

        // body
        $h .= '<tr><td style="padding:30px 32px 8px 32px;"><p style="margin:0 0 16px 0;font-size:16px;line-height:1.6;color:#222222;">' . $e($greeting) . '</p>';
        $h .= anu_nl_body_html((string)$nl['body'], $mode, $images) . '</td></tr>';

        if ($btnText !== '' && $btnUrl !== '') {
            $h .= '<tr><td style="padding:6px 32px 28px 32px;"><table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td style="background:#FAC823;border-radius:6px;">';
            $h .= '<a href="' . $e($btnUrl) . '" style="display:inline-block;padding:14px 28px;font-size:16px;font-weight:800;color:#000000;text-decoration:none;">' . $e($btnText) . '</a></td></tr></table></td></tr>';
        }

        if ($news) {
            $h .= '<tr><td style="padding:8px 32px 8px 32px;"><div style="border-top:1px solid #e2e2e2;padding-top:22px;"><h2 style="margin:0 0 14px 0;font-size:18px;color:#000;border-bottom:3px solid #FF0000;display:inline-block;padding-bottom:4px;">Latest news</h2>';
            foreach ($news as $n) {
                $url = $origin . '/news-single.php?slug=' . rawurlencode($n['slug']);
                $h .= '<div style="margin:0 0 16px 0;"><a href="' . $e($url) . '" style="font-size:16px;font-weight:700;color:#000000;text-decoration:none;">' . $e($n['title']) . '</a>';
                $h .= '<div style="font-size:12px;color:#555;margin-top:3px;">' . $e(date('j M Y', strtotime($n['published_at']))) . '</div>';
                if (!empty($n['excerpt'])) $h .= '<div style="font-size:14px;line-height:1.5;color:#333;margin-top:4px;">' . $e(function_exists('excerpt') ? excerpt($n['excerpt'], 140) : $n['excerpt']) . '</div>';
                $h .= '</div>';
            }
            $h .= '</div></td></tr>';
        }
        if ($events) {
            $h .= '<tr><td style="padding:8px 32px 8px 32px;"><div style="border-top:1px solid #e2e2e2;padding-top:22px;"><h2 style="margin:0 0 14px 0;font-size:18px;color:#000;border-bottom:3px solid #FF0000;display:inline-block;padding-bottom:4px;">Upcoming events</h2>';
            foreach ($events as $ev) {
                $url = $origin . '/events.php';
                $when = date('D j M Y, g:i A', strtotime($ev['start_datetime']));
                $h .= '<div style="margin:0 0 16px 0;"><a href="' . $e($url) . '" style="font-size:16px;font-weight:700;color:#000000;text-decoration:none;">' . $e($ev['title']) . '</a>';
                $h .= '<div style="font-size:14px;color:#333;margin-top:3px;">' . $e($when) . (!empty($ev['location']) ? ' &middot; ' . $e($ev['location']) : '') . '</div></div>';
            }
            $h .= '</div></td></tr>';
        }

        $h .= '<tr><td style="padding:10px 32px 28px 32px;"><p style="margin:0;font-size:16px;line-height:1.6;color:#222;">Warm regards,<br><strong>Africa Nazarene University</strong></p></td></tr>';

        // footer
        $h .= '<tr><td style="background:#000000;padding:26px 32px;text-align:center;">';
        if ($social) $h .= '<div style="margin-bottom:14px;font-size:13px;">' . implode(' &nbsp;&middot;&nbsp; ', $social) . '</div>';
        $h .= '<div style="font-size:13px;line-height:1.6;color:#ffffff;">' . $e($address);
        if ($phone !== '') $h .= '<br>' . $e($phone);
        if ($email !== '') $h .= ' &middot; <a href="mailto:' . $e($email) . '" style="color:#ffffff;text-decoration:underline;">' . $e($email) . '</a>';
        $h .= '</div><div style="margin-top:16px;border-top:1px solid #333;padding-top:14px;font-size:12px;line-height:1.6;color:#dddddd;">You are receiving this because you subscribed to updates from Africa Nazarene University.<br>';
        $h .= '<a href="' . $e($unsubUrl) . '" style="color:#FAC823;text-decoration:underline;">Unsubscribe</a></div></td></tr>';
        $h .= '<tr><td style="height:6px;background:#FAC823;font-size:0;line-height:0;">&nbsp;</td></tr>';
        $h .= '</table></td></tr></table></body></html>';

        // plain text
        $t  = strtoupper($nl['headline']) . "\n" . str_repeat('=', min(60, strlen($nl['headline']))) . "\n\n" . $greeting . "\n\n";
        $t .= (anu_nl_is_rich((string)$nl['body']) ? anu_nl_html_to_text(anu_nl_sanitize((string)$nl['body'])) : trim(str_replace(['**'], '', (string)$nl['body']))) . "\n\n";
        if ($btnText !== '' && $btnUrl !== '') $t .= $btnText . ': ' . $btnUrl . "\n\n";
        if ($news) { $t .= "LATEST NEWS\n"; foreach ($news as $n) $t .= '- ' . $n['title'] . ' (' . date('j M Y', strtotime($n['published_at'])) . ') ' . $origin . '/news-single.php?slug=' . $n['slug'] . "\n"; $t .= "\n"; }
        if ($events) { $t .= "UPCOMING EVENTS\n"; foreach ($events as $ev) $t .= '- ' . $ev['title'] . ' - ' . date('D j M Y, g:i A', strtotime($ev['start_datetime'])) . (!empty($ev['location']) ? ', ' . $ev['location'] : '') . "\n"; $t .= "\n"; }
        $t .= "Warm regards,\nAfrica Nazarene University\n\n--\n" . $address . ($phone ? "\n" . $phone : '') . ($email ? ' | ' . $email : '') . "\n\nYou are receiving this because you subscribed to updates from Africa Nazarene University.\nUnsubscribe: " . $unsubUrl . "\n";

        return [$h, $t, array_values($images)];
    }

    /** Public unsubscribe link for a subscriber row. */
    function anu_nl_unsub_url(array $sub): string
    {
        return anu_nl_origin() . '/unsubscribe.php?t=' . rawurlencode((string)($sub['unsubscribe_token'] ?? ''));
    }
}
