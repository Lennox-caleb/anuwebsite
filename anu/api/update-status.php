<?php
require_once __DIR__ . '/../includes/config.php';
if(!adminLoggedIn()){http_response_code(403);exit;}
if(!verifyCsrf($_GET['csrf']??'')){http_response_code(403);echo json_encode(['success'=>false,'message'=>'Invalid or missing CSRF token']);exit;}
$table=in_array($_GET['table']??'',['contact_messages','prayer_requests','applications'])?$_GET['table']:'';
$id=(int)($_GET['id']??0);$status=trim($_GET['status']??'');
if($table&&$id&&$status){$db=getDB();$db->prepare("UPDATE {$table} SET status=? WHERE id=?")->execute([$status,$id]);}
echo json_encode(['success'=>true]);
