<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD']!=='POST'){echo json_encode(['success'=>false,'message'=>'Invalid method']);exit;}
if(!checkRateLimit('contact', 15)){echo json_encode(['success'=>false,'message'=>'Please wait a moment before submitting again.']);exit;}

$type = $_POST['form_type'] ?? 'contact';
$name    = trim($_POST['name'] ?? $_POST['full_name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Basic validation
if(!$email || !filter_var($email,FILTER_VALIDATE_EMAIL)){echo json_encode(['success'=>false,'message'=>'Valid email required.']);exit;}
if(empty($_POST['consent'])){echo json_encode(['success'=>false,'message'=>'Please agree to the Privacy Policy to continue.']);exit;}

$db=getDB();

if($type==='application'){
  $phone = trim($_POST['phone']??'');
  $prog  = trim($_POST['program_interest']??'');
  $edu   = trim($_POST['education_level']??'');
  $fn    = trim($_POST['full_name']??'');
  if(!$fn){echo json_encode(['success'=>false,'message'=>'Name required.']);exit;}
  $db->prepare("INSERT INTO applications (full_name,email,phone,program_interest,education_level,message) VALUES (?,?,?,?,?,?)")
     ->execute([$fn,$email,$phone,$prog,$edu,$message]);
  echo json_encode(['success'=>true,'message'=>'Application submitted!']);
  exit;
}

// File upload
$filePath=null;
if(!empty($_FILES['file']['name'])){$filePath=handleUpload('file','uploads/attachments',['pdf','doc','docx','zip','jpg','jpeg','png','gif','webp']);}

// Save contact message
$ip=$_SERVER['REMOTE_ADDR']??'';
$db->prepare("INSERT INTO contact_messages (name,email,subject,message,file_path,ip_address) VALUES (?,?,?,?,?,?)")
   ->execute([$name,$email,$subject,$message,$filePath,$ip]);

echo json_encode(['success'=>true,'message'=>'Message sent!']);
