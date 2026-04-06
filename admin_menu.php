<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php'); exit;
}
$admin = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');

// Fetch stats
$stats = ['participants' => 0, 'clubs' => 0, 'interest' => 0];
try {
    require 'dbconnect.php';
    $pdo = new PDO(
        "mysql:host={$servername};dbname={$database};charset=utf8mb4",
        $username, $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stats['participants'] = $pdo->query("SELECT COUNT(*) FROM participant")->fetchColumn();
    $stats['clubs']        = $pdo->query("SELECT COUNT(*) FROM club")->fetchColumn();
    $stats['interest']     = $pdo->query("SELECT COUNT(*) FROM interest")->fetchColumn();
} catch (PDOException $e) { /* silently skip stats */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Cit-E Cycling Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <nav class="topbar">
    <a href="admin_menu.php" class="topbar-brand">Cit-E <span>Cycling</span></a>
    <div class="topbar-nav">
      <a href="view_participants_edit_delete.php"><i class="fas fa-users"></i> Participants</a>
      <a href="search_form.php"><i class="fas fa-search"></i> Search</a>
      <a href="index.html"><i class="fas fa-globe"></i> Site</a>
      <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </nav>

  <div class="page-wrap">
    <div class="page-header fu">
      <h1>Dashboard</h1>
      <p>Welcome back, <strong class="highlight-admin"><?= $admin ?></strong> · <?= date('l, j F Y') ?></p>
    </div>

    <div class="scene fu">
      <div class="cycle-3d" aria-hidden="true">
        <svg class="bike" viewBox="0 0 800 400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Animated cycling illustration">
          <!-- wheels -->
          <g class="wheels" transform="translate(0,40)">
            <circle class="wheel" cx="200" cy="260" r="80" stroke-width="10" />
            <circle class="wheel" cx="560" cy="260" r="80" stroke-width="10" />
          </g>
          <!-- frame -->
          <path class="frame" d="M200 260 L320 180 L420 220 L560 260 M320 180 L360 140 L480 140" stroke-linecap="round" stroke-linejoin="round" />
          <!-- seat/pedals -->
          <circle class="pedal" cx="420" cy="220" r="8" />
        </svg>
        <div class="bike-shadow" aria-hidden="true"></div>
      </div>
    </div>

    <div class="stats-grid fu fu-1">
      <div class="stat-chip">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div>
          <div class="stat-val"><?= $stats['participants'] ?></div>
          <div class="stat-lbl">Participants</div>
        </div>
      </div>
      <div class="stat-chip">
        <div class="stat-icon"><i class="fas fa-flag"></i></div>
        <div>
          <div class="stat-val"><?= $stats['clubs'] ?></div>
          <div class="stat-lbl">Clubs</div>
        </div>
      </div>
      <div class="stat-chip">
        <div class="stat-icon"><i class="fas fa-envelope"></i></div>
        <div>
          <div class="stat-val"><?= $stats['interest'] ?></div>
          <div class="stat-lbl">Interest Registrations</div>
        </div>
      </div>
    </div>

    <div class="nav-grid fu fu-2">
      <div class="c-card">
        <div class="c-card-header"><i class="fas fa-bicycle"></i> Competition</div>
        <div class="c-card-body">
          <a href="view_participants_edit_delete.php" class="nav-btn">
            <div class="nb-icon"><i class="fas fa-users"></i></div>
            <div class="nb-text"><strong>Participants</strong><small>View, edit and delete participant records</small></div>
          </a>
          <a href="search_form.php" class="nav-btn">
            <div class="nb-icon"><i class="fas fa-search"></i></div>
            <div class="nb-text"><strong>Search</strong><small>Find participants or look up clubs</small></div>
          </a>
        </div>
      </div>

      <div class="c-card">
        <div class="c-card-header"><i class="fas fa-cog"></i> Administration</div>
        <div class="c-card-body">
          <a href="index.html" class="nav-btn">
            <div class="nb-icon"><i class="fas fa-globe"></i></div>
            <div class="nb-text"><strong>Public Site</strong><small>View the public-facing competition page</small></div>
          </a>
          <a href="logout.php" class="nav-btn danger">
            <div class="nb-icon"><i class="fas fa-sign-out-alt"></i></div>
            <div class="nb-text"><strong>Logout</strong><small>End your admin session securely</small></div>
          </a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
