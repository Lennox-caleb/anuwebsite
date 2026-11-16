<?php
/**
 * Utility-bar contact block for every unit site: the SAME phone numbers as the main website, and the department's own email.
 * Phones come from the main settings (phone_primary, phone_secondary_2). The email is the setting "email_dept_<site>"
 * (for example email_dept_law = law@anu.ac.ke); if it is empty the general ANU email is shown instead.
 * Change them in Main admin > Site Settings.
 */
if (!function_exists('anu_contact_bar')) {
    function anu_contact_bar(?string $unit = null): void {
        $unit = $unit ?? (defined('UNIT_SLUG') ? UNIT_SLUG : '');
        $get = function (string $k): string { try { return trim((string)main_setting($k)); } catch (Throwable $e) { return ''; } };
        $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
        $p1 = $get('phone_primary'); $p2 = $get('phone_secondary_2');
        $mail = $unit !== '' ? $get('email_dept_' . $unit) : '';
        if ($mail === '' || !filter_var($mail, FILTER_VALIDATE_EMAIL)) $mail = $get('email_marketing');
        $parts = [];
        foreach ([$p1, $p2] as $p) if ($p !== '') $parts[] = '<a href="tel:' . $e(preg_replace('/[^0-9+]/', '', $p)) . '">' . $e($p) . '</a>';
        $html = '';
        if ($parts) $html .= '<i class="bi bi-telephone"></i> ' . implode(' &nbsp;|&nbsp; ', $parts);
        if ($mail !== '') $html .= ($html !== '' ? ' &nbsp;|&nbsp; ' : '') . '<i class="bi bi-envelope"></i> <a href="mailto:' . $e($mail) . '">' . $e($mail) . '</a>';
        echo '<div class="anu-contactbar">' . $html . '</div>' . "\n";
    }
}
