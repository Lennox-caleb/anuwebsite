<?php
/**
 * Outgoing email settings (newsletter + test emails).
 *
 * HOW TO USE (once, on the server):
 *   1. Copy this file to   main-site/config/mail.php
 *   2. Fill in the values below with your real mail account details.
 *   3. Never share or upload config/mail.php -- it holds a password.
 *
 * If config/mail.php does not exist, the site falls back to PHP's built-in
 * mail(), which only works when the server itself has a mail program
 * (such as postfix) installed and set up.
 */
return [
    'driver'     => 'smtp',                       // 'smtp' (recommended) or 'mail'
    'host'       => 'smtp.example.com',           // your mail provider's SMTP server
    'port'       => 587,                          // 587 = STARTTLS, 465 = SSL, 25 = none
    'secure'     => 'tls',                        // 'tls' (port 587), 'ssl' (port 465) or '' (none)
    'username'   => 'newsletter@your-domain.ac.ke',
    'password'   => '',                           // the mailbox / app password
    'from_email' => 'newsletter@your-domain.ac.ke',   // use an address on your own domain
    'from_name'  => 'Africa Nazarene University',
    'reply_to'   => '',                           // optional, e.g. marketing@anu.ac.ke
];
