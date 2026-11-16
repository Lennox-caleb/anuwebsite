<?php
// Deputy Vice Chancellor welcome messages now live on the Leadership
// pages, managed from admin panel -> Leadership, with each DVC getting
// their own full page. This old URL is kept working by redirecting to
// the new Leadership directory, so existing links/bookmarks and search
// results don't break.
require_once __DIR__ . '/includes/functions.php';
redirect('/leadership.php');
