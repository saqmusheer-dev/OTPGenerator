<?php
declare(strict_types=1);

function web_register(PDO $pdo, array $input): never {
    $name=trim((string)($input['name']??''));
    $email=strtolower(trim((string)($input['email']??'')));
    $password=(string)($input['password']??'');
    if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<8) json_response(['success'=>false,'message'=>'Name, valid email and password (8+ characters) are required'],422);
    try{
        $s=$pdo->prepare('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)');
        $s->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
        $_SESSION['user_id']=(int)$pdo->lastInsertId();
        json_response(['success'=>true,'message'=>'Account created'],201);
    }catch(PDOException $e){
        if((string)$e->getCode()==='23000') json_response(['success'=>false,'message'=>'Email already registered'],409);
        throw $e;
    }
}

function web_login(PDO $pdo,array $input): never {
    $email=strtolower(trim((string)($input['email']??'')));$password=(string)($input['password']??'');
    $s=$pdo->prepare('SELECT * FROM users WHERE email=? AND status="active" LIMIT 1');$s->execute([$email]);$u=$s->fetch();
    if(!$u||!password_verify($password,$u['password_hash'])) json_response(['success'=>false,'message'=>'Invalid email or password'],401);
    session_regenerate_id(true);$_SESSION['user_id']=(int)$u['id'];
    json_response(['success'=>true,'message'=>'Signed in']);
}

function require_user(): int {
    if(empty($_SESSION['user_id'])) json_response(['success'=>false,'message'=>'Authentication required'],401);
    return (int)$_SESSION['user_id'];
}

function web_create_app(PDO $pdo,array $input): never {
    $uid=require_user();$name=trim((string)($input['name']??''));$website=trim((string)($input['website_url']??''));
    if($name==='') json_response(['success'=>false,'message'=>'App name is required'],422);
    $slug=strtolower(trim(preg_replace('/[^a-z0-9]+/i','-', $name),'-')).'-'.bin2hex(random_bytes(3));
    $key='otpk_'.bin2hex(random_bytes(16));$secret='otps_'.bin2hex(random_bytes(32));
    $s=$pdo->prepare('INSERT INTO apps(user_id,name,slug,website_url,api_key_hash,api_key_last4,api_secret_hash,secret_last4) VALUES(?,?,?,?,?,?,?,?)');
    $s->execute([$uid,$name,$slug,$website?:null,hash('sha256',$key),substr($key,-4),hash('sha256',$secret),substr($secret,-4)]);
    json_response(['success'=>true,'message'=>'App created','app'=>['id'=>(int)$pdo->lastInsertId(),'name'=>$name,'api_key'=>$key,'api_secret'=>$secret,'warning'=>'Save the secret now. It cannot be displayed again.']],201);
}

function web_stats(PDO $pdo): never {
    $uid=require_user();
    $s=$pdo->prepare('SELECT COUNT(*) FROM apps WHERE user_id=?');$s->execute([$uid]);$apps=(int)$s->fetchColumn();
    $s=$pdo->prepare('SELECT COUNT(*) FROM verification_requests v JOIN apps a ON a.id=v.app_id WHERE a.user_id=? AND v.status="pending"');$s->execute([$uid]);$pending=(int)$s->fetchColumn();
    $s=$pdo->prepare('SELECT COUNT(*) FROM verification_requests v JOIN apps a ON a.id=v.app_id WHERE a.user_id=? AND v.status="approved" AND DATE(v.approved_at)=CURDATE()');$s->execute([$uid]);$verified=(int)$s->fetchColumn();
    json_response(['success'=>true,'apps'=>$apps,'pending'=>$pending,'verified'=>$verified]);
}

function web_apps(PDO $pdo): never {
    $uid=require_user();$s=$pdo->prepare('SELECT id,name,website_url,status,environment,created_at FROM apps WHERE user_id=? ORDER BY id DESC');$s->execute([$uid]);
    json_response(['success'=>true,'apps'=>$s->fetchAll()]);
}

function web_create_verification(PDO $pdo,array $input): never {
    $uid=require_user();$appId=(int)($input['app_id']??0);$purpose=trim((string)($input['purpose']??'verification'));$userRef=trim((string)($input['user_reference']??''));$method=(string)($input['method']??'qr');
    if(!in_array($method,['otp','qr','approval','deep_link'],true)) json_response(['success'=>false,'message'=>'Invalid verification method'],422);
    $check=$pdo->prepare('SELECT id,name FROM apps WHERE id=? AND user_id=? AND status="active"');$check->execute([$appId,$uid]);$app=$check->fetch();
    if(!$app) json_response(['success'=>false,'message'=>'App not found'],404);
    $verificationId=bin2hex(random_bytes(16));$challenge=bin2hex(random_bytes(24));$expires=date('Y-m-d H:i:s',time()+120);
    $s=$pdo->prepare('INSERT INTO verification_requests(verification_id,app_id,user_reference,purpose,method,status,challenge_hash,expires_at) VALUES(?,?,?,?,?,?,?,?)');
    $s->execute([$verificationId,$appId,$userRef?:null,$purpose,$method,'pending',hash('sha256',$challenge),$expires]);
    $qrUrl=rtrim($GLOBALS['config']['app']['base_url'],'/').'/verify.php?token='.urlencode($challenge).'&id='.urlencode($verificationId);
    json_response(['success'=>true,'message'=>'Verification request created','verification_id'=>$verificationId,'app'=>$app['name'],'status'=>'pending','expires_in'=>120,'qr_url'=>$qrUrl,'token'=>$challenge],201);
}
