<?php
session_start();

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: admin_menu.php');
    exit;
}

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

$error      = '';
$form_user  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_user = sanitize($_POST['username'] ?? '');
    $form_pass = $_POST['password'] ?? '';

    if (empty($form_user) || empty($form_pass)) {
        $error = 'Please fill in both username and password.';
    } else {
        require 'dbconnect.php';
        $db_host = $servername;
        $db_name = $database;
        $db_user = $username;
        $db_pass = $password;

        try {
            $pdo = new PDO(
                "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
                $db_user, $db_pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $stmt = $pdo->prepare("SELECT id, username, password FROM user WHERE username = :u LIMIT 1");
            $stmt->execute([':u' => $form_user]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $form_pass === $user['password']) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id']        = $user['id'];
                $_SESSION['admin_username']  = $user['username'];
                header('Location: admin_menu.php');
                exit;
            } else {
                $error = 'Invalid username or password. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'A database error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — Cit-E Cycling</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root{--navy:#0a0f1e;--navy2:#111827;--navy3:#1c2538;--yellow:#f5c518;--white:#f0f4ff;--muted:#8892a4;--danger:#e53e3e;--border:rgba(245,197,24,.18);}
    *,*::before,*::after{box-sizing:border-box;}
    body{font-family:'Barlow',sans-serif;background:var(--navy);color:var(--white);margin:0;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem 1rem;}
    .login-wrap{width:100%;max-width:420px;}
    .brand{font-family:'Barlow Condensed',sans-serif;font-size:1.5rem;font-weight:800;color:var(--yellow);text-decoration:none;letter-spacing:.08em;text-transform:uppercase;display:block;text-align:center;margin-bottom:2rem;}
    .brand span{color:var(--white);}
    .login-box{background:#141b2d;border:1px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:0 16px 48px rgba(0,0,0,.55);}
    .login-header{background:linear-gradient(135deg,var(--navy3),var(--navy2));border-bottom:2px solid var(--yellow);padding:2rem;text-align:center;}
    .login-header .icon{width:60px;height:60px;background:rgba(245,197,24,.12);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:var(--yellow);margin:0 auto 1rem;}
    .login-header h1{font-family:'Barlow Condensed',sans-serif;font-size:1.7rem;font-weight:800;text-transform:uppercase;color:var(--white);margin:0;}
    .login-header p{color:var(--muted);font-size:.85rem;margin:.4rem 0 0;}
    .login-body{padding:2rem;}
    .form-label{font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:.35rem;display:block;}
    .input-wrap{position:relative;margin-bottom:1.2rem;}
    .input-icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.9rem;pointer-events:none;}
    .form-control{background:var(--navy3);border:1px solid rgba(255,255,255,.1);color:var(--white);border-radius:8px;padding:.72rem 1rem .72rem 2.8rem;font-size:.95rem;width:100%;transition:border-color .2s,box-shadow .2s;}
    .form-control:focus{background:var(--navy3);color:var(--white);border-color:var(--yellow);box-shadow:0 0 0 3px rgba(245,197,24,.15);outline:none;}
    .form-control::placeholder{color:var(--muted);}
    .toggle-pw{position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;padding:0;font-size:.9rem;transition:color .2s;}
    .toggle-pw:hover{color:var(--yellow);}
    .alert-err{background:rgba(229,62,62,.1);border:1px solid rgba(229,62,62,.3);border-radius:8px;padding:.9rem 1.1rem;color:#fc8181;font-size:.88rem;margin-bottom:1.2rem;display:flex;align-items:flex-start;gap:.6rem;}
    .btn-login{background:var(--yellow);color:var(--navy);border:none;border-radius:8px;padding:.8rem;font-family:'Barlow Condensed',sans-serif;font-size:1.05rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;width:100%;cursor:pointer;transition:background .2s,transform .15s;display:flex;align-items:center;justify-content:center;gap:.5rem;}
    .btn-login:hover{background:#ffd700;transform:translateY(-1px);}
    .btn-home{background:transparent;color:var(--muted);border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:.75rem;font-family:'Barlow Condensed',sans-serif;font-size:1rem;font-weight:600;text-transform:uppercase;width:100%;margin-top:.75rem;cursor:pointer;transition:all .2s;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:.4rem;}
    .btn-home:hover{border-color:var(--yellow);color:var(--yellow);}
    @keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
    .fu{animation:fadeUp .5s ease both;}
  </style>
</head>
<body>
  <div class="login-wrap fu">
    <a href="index.html" class="brand">Cit-E <span>Cycling</span></a>
    <div class="login-box">
      <div class="login-header">
        <div class="icon"><i class="fas fa-user-shield"></i></div>
        <h1>Admin Login</h1>
        <p>Enter your credentials to access the dashboard</p>
      </div>
      <div class="login-body">
        <?php if ($error): ?>
          <div class="alert-err">
            <i class="fas fa-exclamation-circle" style="margin-top:.1rem;flex-shrink:0"></i>
            <span><?= htmlspecialchars($error) ?></span>
          </div>
        <?php endif; ?>
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" novalidate>
          <div>
            <label class="form-label" for="username">Username</label>
            <div class="input-wrap">
              <i class="fas fa-user input-icon"></i>
              <input type="text" class="form-control" id="username" name="username"
                     placeholder="Enter username" autocomplete="username" required
                     value="<?= htmlspecialchars($form_user) ?>">
            </div>
          </div>
          <div>
            <label class="form-label" for="password">Password</label>
            <div class="input-wrap">
              <i class="fas fa-lock input-icon"></i>
              <input type="password" class="form-control" id="password" name="password"
                     placeholder="Enter password" autocomplete="current-password" required>
              <button type="button" class="toggle-pw" aria-label="Toggle password" onclick="togglePw()">
                <i class="fas fa-eye" id="pw-icon"></i>
              </button>
            </div>
          </div>
          <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Log In</button>
          <a href="index.html" class="btn-home"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </form>
      </div>
    </div>
  </div>
  <script>
    function togglePw() {
      const pw = document.getElementById('password');
      const ic = document.getElementById('pw-icon');
      pw.type = pw.type === 'password' ? 'text' : 'password';
      ic.className = pw.type === 'text' ? 'fas fa-eye-slash' : 'fas fa-eye';
    }
  </script>
</body>
</html>
