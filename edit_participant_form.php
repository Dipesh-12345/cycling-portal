<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php'); exit;
}

$error       = '';
$participant = null;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header('Location: view_participants_edit_delete.php'); exit;
}

try {
    require 'dbconnect.php';
    $pdo = new PDO(
        "mysql:host={$servername};dbname={$database};charset=utf8mb4",
        $username, $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo->prepare("SELECT * FROM participant WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$participant) {
        $error = 'Participant not found.';
    }
} catch (PDOException $e) {
    $error = 'A database error occurred. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Participant — Cit-E Cycling Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root{--navy:#0a0f1e;--navy2:#111827;--navy3:#1c2538;--yellow:#f5c518;--white:#f0f4ff;--muted:#8892a4;--danger:#e53e3e;--border:rgba(245,197,24,.18);}
    *,*::before,*::after{box-sizing:border-box;}
    body{font-family:'Barlow',sans-serif;background:var(--navy);color:var(--white);margin:0;min-height:100vh;}
    .topbar{background:var(--navy2);border-bottom:2px solid var(--yellow);padding:.9rem 2rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
    .topbar-brand{font-family:'Barlow Condensed',sans-serif;font-size:1.5rem;font-weight:800;color:var(--yellow);text-decoration:none;letter-spacing:.08em;text-transform:uppercase;}
    .topbar-brand span{color:var(--white);}
    .topbar-nav a{color:var(--muted);text-decoration:none;font-size:.88rem;font-weight:500;margin-left:1.4rem;transition:color .2s;}
    .topbar-nav a:hover{color:var(--yellow);}
    .page-wrap{max-width:580px;margin:0 auto;padding:2.5rem 1.5rem 4rem;}
    .back-btn{display:inline-flex;align-items:center;gap:.4rem;color:var(--muted);text-decoration:none;font-size:.88rem;margin-bottom:1.5rem;transition:color .2s;}
    .back-btn:hover{color:var(--yellow);}
    .page-header{margin-bottom:2rem;}
    .page-header h1{font-family:'Barlow Condensed',sans-serif;font-size:2rem;font-weight:800;text-transform:uppercase;margin:0;}
    .page-header p{color:var(--muted);font-size:.88rem;margin:.3rem 0 0;}
    .c-card{background:#141b2d;border:1px solid var(--border);border-radius:14px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.35);}
    .c-card-header{background:linear-gradient(90deg,var(--navy3),var(--navy2));border-bottom:2px solid var(--yellow);padding:1.1rem 1.6rem;font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--yellow);display:flex;align-items:center;gap:.5rem;}
    .c-card-body{padding:1.8rem;}
    .form-label{font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:.35rem;display:block;}
    .form-label .req{color:var(--yellow);}
    .form-control{background:var(--navy3);border:1px solid rgba(255,255,255,.1);color:var(--white);border-radius:8px;padding:.72rem 1rem;font-size:.95rem;width:100%;transition:border-color .2s,box-shadow .2s;}
    .form-control:focus{background:var(--navy3);color:var(--white);border-color:var(--yellow);box-shadow:0 0 0 3px rgba(245,197,24,.15);outline:none;}
    .form-control:disabled{background:rgba(255,255,255,.04);color:var(--muted);cursor:not-allowed;}
    .form-control.invalid{border-color:var(--danger)!important;}
    .err-msg{color:#fc8181;font-size:.8rem;margin-top:.3rem;display:none;}
    .err-msg.show{display:block;}
    .form-hint{color:var(--muted);font-size:.78rem;margin-top:.3rem;}
    .field-gap{margin-bottom:1.3rem;}
    .disabled-label{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:8px;padding:.72rem 1rem;color:var(--muted);font-size:.92rem;width:100%;}
    .btn-save{background:var(--yellow);color:var(--navy);border:none;border-radius:8px;padding:.8rem 2rem;font-family:'Barlow Condensed',sans-serif;font-size:1.05rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;cursor:pointer;width:100%;transition:background .2s,transform .15s;display:flex;align-items:center;justify-content:center;gap:.5rem;}
    .btn-save:hover{background:#ffd700;transform:translateY(-1px);}
    .btn-back-f{background:transparent;color:var(--muted);border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:.75rem 2rem;font-family:'Barlow Condensed',sans-serif;font-size:1rem;font-weight:600;text-transform:uppercase;cursor:pointer;width:100%;margin-top:.75rem;transition:all .2s;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:.4rem;}
    .btn-back-f:hover{border-color:var(--yellow);color:var(--yellow);}
    .alert-e{background:rgba(229,62,62,.1);border:1px solid rgba(229,62,62,.3);border-radius:8px;padding:.9rem 1.2rem;color:#fc8181;font-size:.88rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.6rem;}
    
    @keyframes fadeUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
    .fu{animation:fadeUp .5s ease both;}
  </style>
</head>
<body>
  <nav class="topbar">
    <a href="admin_menu.php" class="topbar-brand">Cit-E <span>Cycling</span></a>
    <div class="topbar-nav">
      <a href="view_participants_edit_delete.php"><i class="fas fa-users"></i> Participants</a>
      <a href="logout.php" style="color:#fc8181"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </nav>

  <div class="page-wrap">
    <a href="view_participants_edit_delete.php" class="back-btn">
      <i class="fas fa-arrow-left"></i> All Participants
    </a>

    <div class="page-header fu">
      <h1>Edit Participant</h1>
      <?php if ($participant): ?>
        <p>Updating performance data for <strong style="color:var(--white)"><?= htmlspecialchars($participant['firstname'] . ' ' . $participant['surname']) ?></strong></p>
      <?php endif; ?>
    </div>

    <?php if ($error): ?>
      <div class="alert-e fu"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($participant): ?>
      <div class="c-card fu">
        <div class="c-card-header"><i class="fas fa-pen"></i> Performance Data</div>
        <div class="c-card-body">
          <form id="editForm" action="edit_participant.php" method="POST" novalidate>
            <input type="hidden" name="id" value="<?= (int)$participant['id'] ?>">

            <div class="field-gap">
              <label class="form-label">First Name</label>
              <div class="disabled-label"><?= htmlspecialchars($participant['firstname']) ?></div>
            </div>

            <div class="field-gap">
              <label class="form-label">Surname</label>
              <div class="disabled-label"><?= htmlspecialchars($participant['surname']) ?></div>
            </div>

            <div class="field-gap">
              <label class="form-label" for="power_output">
                Power Output <span class="req">*</span>
              </label>
              <input type="number" class="form-control" id="power_output" name="power_output"
                     value="<?= htmlspecialchars($participant['power_output']) ?>"
                     min="0" step="0.01" placeholder="0.00">
              <div class="form-hint">Watts (W) · Must be 0 or greater</div>
              <div class="err-msg" id="err-power">Please enter a valid power output (0 or more).</div>
            </div>

            <div class="field-gap">
              <label class="form-label" for="distance_travelled">
                Distance Travelled <span class="req">*</span>
              </label>
              <input type="number" class="form-control" id="distance_travelled" name="distance_travelled"
                     value="<?= htmlspecialchars($participant['distance']) ?>"
                     min="0" step="0.01" placeholder="0.00">
              <div class="form-hint">Kilometres (km) · Must be 0 or greater</div>
              <div class="err-msg" id="err-distance">Please enter a valid distance (0 or more).</div>
            </div>

            <button type="submit" class="btn-save">
              <i class="fas fa-save"></i> Save Changes
            </button>
            <a href="view_participants_edit_delete.php" class="btn-back-f">
              <i class="fas fa-times"></i> Cancel
            </a>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>

  

  <script>
    document.getElementById('editForm')?.addEventListener('submit', function(e) {
      let ok = true;
      const pw = document.getElementById('power_output');
      const dt = document.getElementById('distance_travelled');

      ['err-power','err-distance'].forEach(function(id) {
        document.getElementById(id).classList.remove('show');
      });
      [pw, dt].forEach(function(el) { el.classList.remove('invalid'); });

      if (pw.value === '' || isNaN(parseFloat(pw.value)) || parseFloat(pw.value) < 0) {
        document.getElementById('err-power').classList.add('show');
        pw.classList.add('invalid');
        ok = false;
      }
      if (dt.value === '' || isNaN(parseFloat(dt.value)) || parseFloat(dt.value) < 0) {
        document.getElementById('err-distance').classList.add('show');
        dt.classList.add('invalid');
        ok = false;
      }
      if (!ok) e.preventDefault();
    });
  </script>
</body>
</html>
