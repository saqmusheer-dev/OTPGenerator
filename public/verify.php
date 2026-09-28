<?php
declare(strict_types=1);
$token=trim((string)($_GET['token']??''));
$id=trim((string)($_GET['id']??''));
?><!doctype html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Verify — OTPGenerator</title>
<style>
body{margin:0;background:#f6f7fb;font-family:system-ui;color:#151827}
.box{max-width:430px;margin:8vh auto;background:#fff;padding:30px;border-radius:20px;border:1px solid #e4e5ed;text-align:center}
.brand{font-size:22px;font-weight:800}.brand span{color:#6d35e8}.app{font-size:25px;font-weight:800;margin:25px 0 8px}.msg{margin:18px 0;color:#697083}
button{border:0;border-radius:10px;padding:13px 20px;font-weight:800;margin:6px;cursor:pointer}.approve{background:#6d35e8;color:#fff}.reject{background:#eeeef5}
</style>
</head>
<body>
<div class="box">
<div class="brand">OTP<span>Generator</span></div>
<div id="content"><div class="app">Verification request</div><div class="msg">Loading secure request…</div></div>
</div>
<script>
const token=<?php echo json_encode($token);?>;
const id=<?php echo json_encode($id);?>;
async function load(){
 const r=await fetch('index.php?route=verification-info&token='+encodeURIComponent(token)+'&id='+encodeURIComponent(id));
 const j=await r.json();
 if(!j.success){content.innerHTML='<div class="app">Invalid request</div><div class="msg">'+j.message+'</div>';return}
 content.innerHTML='<div class="app">'+j.app+'</div><div class="msg">Purpose: '+j.purpose+'<br>This request expires in '+j.expires_in+' seconds.</div><button class="approve" onclick="act(\'approve\')">Approve</button><button class="reject" onclick="act(\'reject\')">Reject</button>';
}
async function act(action){
 const r=await fetch('index.php?route=verification-action',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({token:token,id:id,action:action})});
 const j=await r.json();
 content.innerHTML='<div class="app">'+(j.success?'✓ '+(action==='approve'?'Verified':'Rejected'):'Error')+'</div><div class="msg">'+j.message+'</div>';
}
load();
</script>
</body>
</html>