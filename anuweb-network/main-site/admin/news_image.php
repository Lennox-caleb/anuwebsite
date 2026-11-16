<?php
/** Picture upload for the news editor. Returns JSON {url} or {error}. */
require_once __DIR__ . '/includes/auth.php';
require_role(['editor', 'super_admin']);
require_once __DIR__ . '/includes/rich_image_upload.php';
rich_image_upload('news-body');
