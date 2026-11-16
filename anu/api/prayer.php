<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');
if($_SERVER['REQUEST_METHOD']!=='POST'){echo json_encode(['success'=>false]);exit;}
if(!checkRateLimit('prayer')){echo json_encode(['success'=>false,'message'=>'Please wait a moment before submitting again.']);exit;}
$name=trim($_POST['name']??'');$email=trim($_POST['email']??'');$request=trim($_POST['request']??'');
if(!$request){echo json_encode(['success'=>false,'message'=>'Prayer request required.']);exit;}
if(empty($_POST['consent'])){echo json_encode(['success'=>false,'message'=>'Please agree to the Privacy Policy to continue.']);exit;}
$db=getDB();
$db->prepare("INSERT INTO prayer_requests (name,email,request) VALUES (?,?,?)")->execute([$name,$email,$request]);
echo json_encode(['success'=>true]);
