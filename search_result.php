<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: search_form.php'); exit;
}

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

$search_type = $_POST['search_type'] ?? 'participant';
$results     = [];
$club_stats  = [];
$error       = '';

try {
    require 'dbconnect.php';
    $pdo = new PDO(
        "mysql:host={$servername};dbname={$database};charset=utf8mb4",
        $username, $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if ($search_type === 'participant') {
        $firstname = sanitize($_POST['firstname'] ?? '');
        $surname   = sanitize($_POST['surname']   ?? '');

        $sql    = "SELECT p.id, p.firstname, p.surname, p.email, p.power_output, p.distance, c.name AS club_name
                   FROM participant p
                   LEFT JOIN club c ON p.club_id = c.id
                   WHERE 1=1";
        $params = [];

        if ($firstname !== '') {
            $sql .= " AND p.firstname LIKE :fn";
            $params[':fn'] = "%{$firstname}%";
        }
        if ($surname !== '') {
            $sql .= " AND p.surname LIKE :sn";
            $params[':sn'] = "%{$surname}%";
        }
        $sql .= " ORDER BY p.surname, p.firstname";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // Club search
        $club_name = sanitize($_POST['club'] ?? '');

        $sql    = "SELECT id, name, location FROM club WHERE 1=1";
        $params = [];
        if ($club_name !== '') {
            $sql .= " AND name LIKE :cn";
            $params[':cn'] = "%{$club_name}%";
        }
        $sql .= " ORDER BY name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Stats + members per club
        foreach ($results as &$club) {
            $s = $pdo->prepare("
                SELECT COUNT(*)           AS total,
                       AVG(power_output)  AS avg_power,
                       AVG(distance)      AS avg_dist,
                       SUM(power_output)  AS total_power,
                       SUM(distance)      AS total_dist,
                       MAX(power_output)  AS max_power,
                       MAX(distance)      AS max_dist
                FROM participant WHERE club_id = :cid
            ");
            $s->execute([':cid' => $club['id']]);
            $club['stats'] = $s->fetch(PDO::FETCH_ASSOC);

            $m = $pdo->prepare("SELECT firstname, surname, power_output, distance FROM participant WHERE club_id = :cid ORDER BY surname LIMIT 5");
            $m->execute([':cid' => $club['id']]);
            $club['members'] = $m->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($club);
    }

} catch (PDOException $e) {
    $error = 'A database error occurred. Please try again.';
}

$count = count($results);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Results — Cit-E Cycling Admin</title>
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
    .page-wrap{max-width:1000px;margin:0 auto;padding:2.5rem 1.5rem 4rem;}
    .back-btn{display:inline-flex;align-items:center;gap:.4rem;color:var(--muted);text-decoration:none;font-size:.88rem;margin-bottom:1.5rem;transition:color .2s;}
    .back-btn:hover{color:var(--yellow);}
    .page-header{margin-bottom:2rem;}
    .page-header h1{font-family:'Barlow Condensed',sans-serif;font-size:2rem;font-weight:800;text-transform:uppercase;margin:0;}
    .page-header p{color:var(--muted);font-size:.88rem;margin:.3rem 0 0;}
    .result-count{display:inline-flex;align-items:center;gap:.4rem;background:rgba(245,197,24,.1);border:1px solid rgba(245,197,24,.25);border-radius:20px;padding:.2rem .9rem;font-family:'Barlow Condensed',sans-serif;font-size:.88rem;color:var(--yellow);margin-top:.6rem;}

    /* Table */
    .c-card{background:#141b2d;border:1px solid var(--border);border-radius:14px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.35);}
    .c-card-header{background:linear-gradient(90deg,var(--navy3),var(--navy2));border-bottom:2px solid var(--yellow);padding:1.1rem 1.6rem;font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--yellow);display:flex;align-items:center;gap:.5rem;}
    .table-wrap{overflow-x:auto;}
    .c-table{width:100%;border-collapse:collapse;}
    .c-table thead th{background:var(--navy3);color:var(--yellow);font-family:'Barlow Condensed',sans-serif;font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;padding:.9rem 1rem;border-bottom:2px solid var(--yellow);white-space:nowrap;}
    .c-table tbody tr{border-bottom:1px solid rgba(255,255,255,.05);transition:background .15s;}
    .c-table tbody tr:hover{background:rgba(245,197,24,.04);}
    .c-table tbody td{padding:.8rem 1rem;font-size:.88rem;color:var(--white);vertical-align:middle;}
    .td-muted{color:var(--muted);font-size:.82rem;}
    .badge-club{background:rgba(245,197,24,.1);color:var(--yellow);border:1px solid rgba(245,197,24,.25);border-radius:20px;padding:.15rem .6rem;font-size:.75rem;font-family:'Barlow Condensed',sans-serif;font-weight:600;white-space:nowrap;}

    /* Club cards */
    .club-card{background:#141b2d;border:1px solid var(--border);border-radius:14px;margin-bottom:1.5rem;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,.3);}
    .club-card-header{background:linear-gradient(90deg,var(--navy3),var(--navy2));border-bottom:2px solid var(--yellow);padding:1.2rem 1.6rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;}
    .club-name{font-family:'Barlow Condensed',sans-serif;font-size:1.4rem;font-weight:800;text-transform:uppercase;color:var(--white);}
    .club-location{color:var(--muted);font-size:.88rem;display:flex;align-items:center;gap:.3rem;}
    .club-card-body{padding:1.5rem;}
    .club-stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.8rem;margin-bottom:1.5rem;}
    .cs{background:var(--navy3);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:1rem;text-align:center;}
    .cs-val{font-family:'Barlow Condensed',sans-serif;font-size:1.5rem;font-weight:800;color:var(--yellow);}
    .cs-lbl{font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-top:.15rem;}
    .members-title{font-family:'Barlow Condensed',sans-serif;font-size:.88rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:.8rem;}
    .member-row{display:flex;align-items:center;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:.85rem;}
    .member-row:last-child{border-bottom:none;}
    .member-name{color:var(--white);}
    .member-stats{color:var(--muted);font-size:.78rem;display:flex;gap:1rem;}

    /* Empty / error */
    .empty-state{text-align:center;padding:4rem 2rem;color:var(--muted);}
    .empty-state i{font-size:3rem;color:rgba(245,197,24,.15);display:block;margin-bottom:1rem;}
    .alert-e{background:rgba(229,62,62,.1);border:1px solid rgba(229,62,62,.3);border-radius:8px;padding:.9rem 1.2rem;color:#fc8181;font-size:.88rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.6rem;}

    /* Action row */
    .action-row{display:flex;gap:.75rem;flex-wrap:wrap;margin-top:2rem;}
    .btn-y{background:var(--yellow);color:var(--navy);border:none;border-radius:8px;padding:.7rem 1.6rem;font-family:'Barlow Condensed',sans-serif;font-size:.95rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;transition:background .2s;}
    .btn-y:hover{background:#ffd700;color:var(--navy);}
    .btn-g{background:transparent;color:var(--muted);border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:.68rem 1.6rem;font-family:'Barlow Condensed',sans-serif;font-size:.95rem;font-weight:600;text-transform:uppercase;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;transition:all .2s;}
    .btn-g:hover{border-color:var(--yellow);color:var(--yellow);}

    
    @keyframes fadeUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
    .fu{animation:fadeUp .5s ease both;}
    .fu-1{animation-delay:.1s;}
  </style>
</head>
<body>
  <nav class="topbar">
    <a href="admin_menu.php" class="topbar-brand">Cit-E <span>Cycling</span></a>
    <div class="topbar-nav">
      <a href="admin_menu.php"><i class="fas fa-th-large"></i> Dashboard</a>
      <a href="search_form.php"><i class="fas fa-search"></i> Search</a>
      <a href="logout.php" style="color:#fc8181"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </nav>

  <div class="page-wrap">
    <a href="search_form.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Search</a>

    <div class="page-header fu">
      <h1>Search Results</h1>
      <?php if (!$error): ?>
        <div class="result-count">
          <i class="fas fa-filter"></i>
          <?= $count ?> <?= $search_type === 'participant' ? 'participant' : 'club' ?><?= $count !== 1 ? 's' : '' ?> found
        </div>
      <?php endif; ?>
    </div>

    <?php if ($error): ?>
      <div class="alert-e fu"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>

    <?php elseif ($search_type === 'participant'): ?>
      <!-- ── Participant results ── -->
      <?php if (empty($results)): ?>
        <div class="c-card fu">
          <div class="empty-state">
            <i class="fas fa-user-slash"></i>
            <p>No participants matched your search. Try different terms.</p>
          </div>
        </div>
      <?php else: ?>
        <div class="c-card fu fu-1">
          <div class="c-card-header"><i class="fas fa-users"></i> Matching Participants</div>
          <div class="table-wrap">
            <table class="c-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Club</th>
                  <th>Power (W)</th>
                  <th>Distance (km)</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($results as $p): ?>
                  <tr>
                    <td><?= htmlspecialchars($p['firstname'] . ' ' . $p['surname']) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($p['email']) ?></td>
                    <td>
                      <?php if ($p['club_name']): ?>
                        <span class="badge-club"><?= htmlspecialchars($p['club_name']) ?></span>
                      <?php else: ?>
                        <span class="td-muted">—</span>
                      <?php endif; ?>
                    </td>
                    <td><?= number_format((float)$p['power_output'], 2) ?></td>
                    <td><?= number_format((float)$p['distance'], 2) ?></td>
                    <td>
                      <a href="edit_participant_form.php?id=<?= (int)$p['id'] ?>"
                         style="background:rgba(245,197,24,.1);color:var(--yellow);border:1px solid rgba(245,197,24,.25);border-radius:6px;padding:.28rem .75rem;font-family:'Barlow Condensed',sans-serif;font-size:.8rem;font-weight:700;text-transform:uppercase;text-decoration:none;display:inline-flex;align-items:center;gap:.25rem;transition:all .2s;"
                         onmouseover="this.style.background='var(--yellow)';this.style.color='var(--navy)'"
                         onmouseout="this.style.background='rgba(245,197,24,.1)';this.style.color='var(--yellow)'">
                        <i class="fas fa-pen"></i> Edit
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <!-- ── Club results ── -->
      <?php if (empty($results)): ?>
        <div class="c-card fu">
          <div class="empty-state">
            <i class="fas fa-flag"></i>
            <p>No clubs matched your search. Try a different name.</p>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($results as $i => $club): ?>
          <div class="club-card fu" style="animation-delay:<?= $i * 0.08 ?>s">
            <div class="club-card-header">
              <span class="club-name"><?= htmlspecialchars($club['name']) ?></span>
              <span class="club-location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($club['location']) ?></span>
            </div>
            <div class="club-card-body">
              <?php $st = $club['stats']; ?>
              <div class="club-stats-grid">
                <div class="cs">
                  <div class="cs-val"><?= (int)$st['total'] ?></div>
                  <div class="cs-lbl">Members</div>
                </div>
                <div class="cs">
                  <div class="cs-val"><?= number_format((float)$st['avg_power'], 1) ?></div>
                  <div class="cs-lbl">Avg Power (W)</div>
                </div>
                <div class="cs">
                  <div class="cs-val"><?= number_format((float)$st['avg_dist'], 1) ?></div>
                  <div class="cs-lbl">Avg Dist (km)</div>
                </div>
                <div class="cs">
                  <div class="cs-val"><?= number_format((float)$st['total_dist'], 0) ?></div>
                  <div class="cs-lbl">Total km</div>
                </div>
                <div class="cs">
                  <div class="cs-val"><?= number_format((float)$st['max_power'], 0) ?></div>
                  <div class="cs-lbl">Top Power (W)</div>
                </div>
              </div>

              <?php if (!empty($club['members'])): ?>
                <div class="members-title"><i class="fas fa-users" style="margin-right:.4rem"></i>Top Members (up to 5)</div>
                <?php foreach ($club['members'] as $m): ?>
                  <div class="member-row">
                    <span class="member-name"><?= htmlspecialchars($m['firstname'] . ' ' . $m['surname']) ?></span>
                    <span class="member-stats">
                      <span><i class="fas fa-bolt" style="color:var(--yellow);margin-right:.25rem"></i><?= number_format((float)$m['power_output'], 1) ?> W</span>
                      <span><i class="fas fa-road" style="color:var(--muted);margin-right:.25rem"></i><?= number_format((float)$m['distance'], 1) ?> km</span>
                    </span>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endif; ?>

    <div class="action-row fu">
      <a href="search_form.php" class="btn-y"><i class="fas fa-search"></i> New Search</a>
      <a href="admin_menu.php" class="btn-g"><i class="fas fa-th-large"></i> Dashboard</a>
    </div>
  </div>

  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
