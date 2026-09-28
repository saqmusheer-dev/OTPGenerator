<?php
declare(strict_types=1);
session_start();
$config = require dirname(__DIR__) . '/config.php';
$GLOBALS['config'] = $config;
require dirname(__DIR__) . '/src/bootstrap.php';

$loggedIn = isset($_SESSION['user_id']);
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>OTPGenerator — Verification Hub</title>
<style>
:root{--bg:#f6f7fb;--card:#fff;--ink:#151827;--muted:#697083;--brand:#6d35e8;--line:#e7e8ef}
*{box-sizing:border-box}body{margin:0;background:var(--bg);font-family:Inter,system-ui,-apple-system,Segoe UI,sans-serif;color:var(--ink)}
header{height:68px;background:#fff;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 5%;position:sticky;top:0;z-index:5}
.logo{font-weight:800;font-size:21px}.logo span{color:var(--brand)}
.container{max-width:1120px;margin:0 auto;padding:34px 20px}.hero{text-align:center;padding:55px 15px 35px}.hero h1{font-size:clamp(34px,6vw,58px);margin:0 0 14px}.hero p{color:var(--muted);font-size:18px;max-width:680px;margin:auto;line-height:1.6}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:28px}.card{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:24px;box-shadow:0 8px 30px #17182a08}.icon{font-size:28px}.card h3{margin:12px 0 8px}.muted{color:var(--muted)}button,.btn{border:0;border-radius:10px;background:var(--brand);color:white;padding:12px 18px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-block}.secondary{background:#eeeef5;color:var(--ink)}input{width:100%;padding:13px;border:1px solid var(--line);border-radius:10px;margin:7px 0 13px;font-size:15px}label{font-size:13px;font-weight:700}.panel{max-width:460px;margin:30px auto;background:white;border:1px solid var(--line);border-radius:18px;padding:28px}.error{color:#b42318;background:#fff0ee;padding:10px;border-radius:9px;margin:10px 0;display:none}.success{color:#087443;background:#eafaf1;padding:10px;border-radius:9px;margin:10px 0;display:none}
@media(max-width:760px){.grid{grid-template-columns:1fr}header{padding:0 18px}}
</style>
</head>
<body>
<header><div class="logo">OTP<span>Generator</span></div><div><?php if($loggedIn): ?><a class="btn secondary" href="dashboard.php">Dashboard</a><?php else: ?><a class="btn" href="#register">Get Started</a><?php endif; ?></div></header>
<main class="container">
<section class="hero">
<h1>Your Verification Hub.</h1>
<p>Verify users across QuiGo, Adizzle and customer applications using OTP, QR scanning and secure approval — without SMS or WhatsApp.</p>
<?php if(!$loggedIn): ?><p style="margin-top:24px"><a class="btn" href="#register">Create OTPGenerator Account</a></p><?php endif; ?>
</section>
<section class="grid">
<div class="card"><div class="icon">🔢</div><h3>OTP Code</h3><p class="muted">Generate short-lived verification codes inside OTPGenerator.</p></div>
<div class="card"><div class="icon">📷</div><h3>QR Verify</h3><p class="muted">Scan a verification QR displayed by a connected website or app.</p></div>
<div class="card"><div class="icon">✅</div><h3>One-Tap Approval</h3><p class="muted">Approve or reject login and verification requests securely.</p></div>
</section>
<?php if(!$loggedIn): ?>
<section id="register" class="panel">
<h2>Create account</h2><p class="muted">Start testing your own connected applications.</p>
<div id="err" class="error"></div><div id="ok" class="success"></div>
<form id="registerForm">
<label>Name</label><input name="name" required>
<label>Email</label><input name="email" type="email" required>
<label>Password</label><input name="password" type="password" minlength="8" required>
<button>Create Account</button>
</form>
<p class="muted" style="margin-top:18px">Already registered? <a href="login.php">Sign in</a></p>
</section>
<?php endif; ?>
</main>
<script>
document.getElementById('registerForm')?.addEventListener('submit',async e=>{
 e.preventDefault(); const f=new FormData(e.target), data=Object.fromEntries(f);
 const r=await fetch('index.php?route=register',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
 const j=await r.json(); const box=document.getElementById(j.success?'ok':'err'); box.textContent=j.message; box.style.display='block'; if(j.success)e.target.reset();
});
</script>
</body></html>
