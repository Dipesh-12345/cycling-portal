<?php
session_start();

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

$error  = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'dbconnect.php';

    $firstname = sanitize($_POST['firstname'] ?? '');
    $surname   = sanitize($_POST['surname']   ?? '');
    $email     = sanitize($_POST['email']     ?? '');
    $terms     = isset($_POST['terms']) ? 1 : 0;

    $errors = [];

    if (empty($firstname) || !preg_match('/^[A-Za-z\s]{2,50}$/', $firstname)) {
        $errors[] = 'First name must be 2–50 letters.';
    }
    if (empty($surname) || !preg_match('/^[A-Za-z\s]{2,50}$/', $surname)) {
        $errors[] = 'Surname must be 2–50 letters.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }
    if (!$terms) {
        $errors[] = 'You must accept the terms and conditions.';
    }

    if (empty($errors)) {
        try {
            $pdo = new PDO(
                "mysql:host={$servername};dbname={$database};charset=utf8mb4",
                $username, $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Check for duplicate email
            $chk = $pdo->prepare("SELECT id FROM interest WHERE email = :email LIMIT 1");
            $chk->execute([':email' => $email]);
            if ($chk->fetch()) {
                $error = 'This email address is already registered.';
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO interest (firstname, surname, email, terms) VALUES (:fn, :sn, :em, :tc)"
                );
                $stmt->execute([':fn' => $firstname, ':sn' => $surname, ':em' => $email, ':tc' => $terms]);
                $success = true;
            }
        } catch (PDOException $e) {
            $error = 'A database error occurred. Please try again later.';
            // Log: error_log($e->getMessage());
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration — Cit-E Cycling</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root{--navy:#0a0f1e;--navy2:#111827;--navy3:#1c2538;--yellow:#f5c518;--white:#f0f4ff;--muted:#8892a4;--border:rgba(245,197,24,.18);}
    *,*::before,*::after{box-sizing:border-box;}
    body{font-family:'Barlow',sans-serif;background:var(--navy);color:var(--white);margin:0;min-height:100vh;}
    .topbar{background:var(--navy2);border-bottom:2px solid var(--yellow);padding:.9rem 2rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
    .topbar-brand{font-family:'Barlow Condensed',sans-serif;font-size:1.5rem;font-weight:800;color:var(--yellow);text-decoration:none;letter-spacing:.08em;text-transform:uppercase;}
    .topbar-brand span{color:var(--white);}
    .topbar-nav a{color:var(--muted);text-decoration:none;font-size:.9rem;font-weight:500;margin-left:1.5rem;transition:color .2s;}
    .topbar-nav a:hover{color:var(--yellow);}
    .page-wrap{max-width:540px;margin:4rem auto;padding:0 1.5rem 4rem;text-align:center;}
    .status-box{background:#141b2d;border:1px solid var(--border);border-radius:14px;padding:3rem 2rem;box-shadow:0 8px 32px rgba(0,0,0,.45);}
    .status-icon{width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2.2rem;margin:0 auto 1.5rem;}
    .status-icon.ok{background:rgba(56,161,105,.15);color:#68d391;}
    .status-icon.fail{background:rgba(229,62,62,.15);color:#fc8181;}
    h1{font-family:'Barlow Condensed',sans-serif;font-size:2rem;font-weight:800;text-transform:uppercase;margin-bottom:.8rem;}
    p{color:var(--muted);font-size:.95rem;line-height:1.6;margin-bottom:1.5rem;}
    .alert-err{background:rgba(229,62,62,.1);border:1px solid rgba(229,62,62,.3);border-radius:8px;padding:1rem 1.2rem;color:#fc8181;font-size:.9rem;text-align:left;margin-bottom:1.5rem;}
    .btn-y{background:var(--yellow);color:var(--navy);border:none;border-radius:8px;padding:.75rem 2rem;font-family:'Barlow Condensed',sans-serif;font-size:1rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;margin:.4rem;transition:background .2s;}
    .btn-y:hover{background:#ffd700;color:var(--navy);}
    .btn-g{background:transparent;color:var(--muted);border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:.72rem 2rem;font-family:'Barlow Condensed',sans-serif;font-size:1rem;font-weight:600;text-transform:uppercase;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;margin:.4rem;transition:all .2s;}
    .btn-g:hover{border-color:var(--yellow);color:var(--yellow);}
    
    @keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
    .fu{animation:fadeUp .5s ease both;}
  </style>
</head>
<body>
  <nav class="topbar">
    <a href="index.html" class="topbar-brand">Cit-E <span>Cycling</span></a>
    <div class="topbar-nav">
      <a href="index.html"><i class="fas fa-home"></i> Home</a>
    </div>
  </nav>

  <div class="page-wrap">
    <div class="status-box fu">
      <?php if ($success): ?>
        <div class="status-icon ok"><i class="fas fa-check"></i></div>
        <h1>You're Registered!</h1>
        <p>Thanks <strong style="color:var(--white)"><?= htmlspecialchars($firstname . ' ' . $surname) ?></strong>!
           We've recorded your interest and will be in touch at <strong style="color:var(--white)"><?= htmlspecialchars($email) ?></strong>.</p>
        <a href="index.html" class="btn-g"><i class="fas fa-home"></i> Back to Home</a>
        <a href="register_form.html" class="btn-y"><i class="fas fa-plus"></i> Register Another</a>
      <?php else: ?>
        <div class="status-icon fail"><i class="fas fa-exclamation-triangle"></i></div>
        <h1>Registration Failed</h1>
        <?php if ($error): ?>
          <div class="alert-err"><i class="fas fa-times-circle" style="margin-right:.5rem"></i><?= $error ?></div>
        <?php endif; ?>
        <p>Please correct the issues above and try again.</p>
        <a href="register_form.html" class="btn-y"><i class="fas fa-arrow-left"></i> Try Again</a>
        <a href="index.html" class="btn-g"><i class="fas fa-home"></i> Home</a>
      <?php endif; ?>
    </div>
  </div>

  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
