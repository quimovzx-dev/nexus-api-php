<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
@mkdir(__DIR__.'/../data',0777,true);
$db=new PDO('sqlite:'.__DIR__.'/../data/nexus.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE IF NOT EXISTS users(id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,email TEXT UNIQUE NOT NULL,password_hash TEXT NOT NULL,token_hash TEXT,created_at TEXT NOT NULL)');
function body():array{$x=json_decode(file_get_contents('php://input'),true);return is_array($x)?$x:[];}
function out(int $s,array $x):never{http_response_code($s);echo json_encode($x,JSON_PRETTY_PRINT);exit;}
function req(array $d,array $f):void{foreach($f as $k)if(!isset($d[$k])||trim((string)$d[$k])==='')out(422,['error'=>"Missing field: $k"]);}
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'/';$method=$_SERVER['REQUEST_METHOD'];
if($method==='GET'&&$path==='/health')out(200,['service'=>'NEXUS API','status'=>'ok','time'=>gmdate('c')]);
if($method==='POST'&&$path==='/register'){
$d=body();req($d,['name','email','password']);if(!filter_var($d['email'],FILTER_VALIDATE_EMAIL))out(422,['error'=>'Invalid email']);if(strlen($d['password'])<8)out(422,['error'=>'Password too short']);
try{$q=$db->prepare('INSERT INTO users(name,email,password_hash,created_at) VALUES(?,?,?,?)');$q->execute([$d['name'],strtolower($d['email']),password_hash($d['password'],PASSWORD_DEFAULT),gmdate('c')]);out(201,['message'=>'User created','id'=>(int)$db->lastInsertId()]);}catch(PDOException $e){out(409,['error'=>'Email already registered']);}}
if($method==='POST'&&$path==='/login'){
$d=body();req($d,['email','password']);$q=$db->prepare('SELECT * FROM users WHERE email=?');$q->execute([strtolower($d['email'])]);$u=$q->fetch(PDO::FETCH_ASSOC);
if(!$u||!password_verify($d['password'],$u['password_hash']))out(401,['error'=>'Invalid credentials']);
$t=bin2hex(random_bytes(32));$q=$db->prepare('UPDATE users SET token_hash=? WHERE id=?');$q->execute([hash('sha256',$t),$u['id']]);out(200,['token'=>$t,'user'=>['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email']]]);}
if($method==='GET'&&$path==='/me'){
$auth=$_SERVER['HTTP_AUTHORIZATION']??'';if(!preg_match('/Bearer\s+(.+)/i',$auth,$m))out(401,['error'=>'Bearer token required']);
$q=$db->prepare('SELECT id,name,email,created_at FROM users WHERE token_hash=?');$q->execute([hash('sha256',$m[1])]);$u=$q->fetch(PDO::FETCH_ASSOC);if(!$u)out(401,['error'=>'Invalid token']);$u['id']=(int)$u['id'];out(200,['user'=>$u]);}
out(404,['error'=>'Route not found']);
