<?php
/**
 * Clean URLs (no .php in the address bar).
 *
 * When the site's .htaccess carries the ANU clean-URL rules AND the self-test
 * marker file (.clean-urls-ok) is present, every internal link, form action and
 * redirect this page produces is written without ".php". If the rules are ever
 * removed, this switches itself off and the site keeps working with ".php".
 * Only href / action / src / formaction attributes of the HTML page and the
 * Location header are touched; external links, mailto:, tel:, #anchors and
 * non-HTML responses (JSON, files) are left alone.
 */
if (!function_exists('anu_clean_urls_boot')) {

    function anu_clean_urls_enabled(string $siteRoot): bool
    {
        static $cache = [];
        if (isset($cache[$siteRoot])) return $cache[$siteRoot];
        $ok = false;
        if (is_file($siteRoot . '/.clean-urls-ok')) {
            $ht = @file_get_contents($siteRoot . '/.htaccess');
            $ok = is_string($ht) && strpos($ht, 'BEGIN ANU-CLEAN-URLS') !== false;
        }
        return $cache[$siteRoot] = $ok;
    }

    /** "/a/b/page.php?x=1#y" -> "/a/b/page?x=1#y";  ".../index.php" -> ".../" */
    function anu_clean_one(string $u): string
    {
        if ($u === '' || $u[0] === '#') return $u;
        if (preg_match('~^(?:[a-z][a-z0-9+.\-]*:|//)~i', $u)) return $u;      // absolute / mailto: / tel: / javascript: / data:
        if (!preg_match('~^([^?#]*?)\.php((?:[?#].*)?)$~s', $u, $m)) return $u;
        $path = $m[1];
        if (preg_match('~(^|/)index$~', $path)) {
            $path = preg_replace('~index$~', '', $path);
            if ($path === '') $path = './';
        }
        return $path . $m[2];
    }

    function anu_clean_html(string $html): string
    {
        if (stripos($html, '.php') === false) return $html;
        // <meta http-equiv="refresh" content="1;url=page.php?x=1">
        $html = preg_replace_callback('~(<meta\b[^>]*\bhttp-equiv\s*=\s*["\']?refresh["\']?[^>]*\bcontent\s*=\s*(["\']))((?:(?!\2).)*?)(\2)~is', function ($m) {
            $c = html_entity_decode($m[3], ENT_QUOTES);
            if (!preg_match('~^(\s*\d+\s*;\s*url=)(.*)$~is', $c, $x)) return $m[0];
            $n = $x[1] . anu_clean_one(trim($x[2]));
            return $m[1] . htmlspecialchars($n, ENT_QUOTES) . $m[4];
        }, $html);
        return preg_replace_callback(
            '~\b(href|action|formaction|src)\s*=\s*(["\'])((?:(?!\2).){0,2000})\2~is',
            function ($m) {
                $old = html_entity_decode($m[3], ENT_QUOTES);
                $new = anu_clean_one($old);
                if ($new === $old) return $m[0];
                return $m[1] . '=' . $m[2] . htmlspecialchars($new, ENT_QUOTES) . $m[2];
            },
            $html
        );
    }

    function anu_clean_urls_boot(string $siteRoot): void
    {
        static $started = false;
        if ($started || PHP_SAPI === 'cli' || !anu_clean_urls_enabled($siteRoot)) return;
        $started = true;

        header_register_callback(function () {
            foreach (headers_list() as $h) {
                if (stripos($h, 'Location:') === 0) {
                    $new = anu_clean_one(trim(substr($h, 9)));
                    if ($new !== trim(substr($h, 9))) { $code = http_response_code() ?: 302; header('Location: ' . $new, true, $code); }
                }
            }
        });

        ob_start(function ($buf) {
            foreach (headers_list() as $h) {                                   // leave JSON, downloads, etc. alone
                if (stripos($h, 'Content-Type:') === 0 && stripos($h, 'text/html') === false) return $buf;
            }
            if ($buf === '' || strpos($buf, '<') === false) return $buf;
            return anu_clean_html($buf);
        });
    }
}
