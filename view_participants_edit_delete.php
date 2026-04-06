<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php'); exit;
}

$participants = [];
$error = '';

try {
    require 'dbconnect.php';
    $pdo = new PDO(
        "mysql:host={$servername};dbname={$database};charset=utf8mb4",
        $username, $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo->query("
        SELECT p.id, p.firstname, p.surname, p.email,
               p.power_output, p.distance, c.name AS club_name
        FROM participant p
        LEFT JOIN club c ON p.club_id = c.id
        ORDER BY p.surname, p.firstname
    ");
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Could not load participants. Please try again later.';
}

// Flash message from edit/delete
$flash = '';
if (!empty($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Participants — Cit-E Cycling Admin</title>
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
    .page-wrap{max-width:1100px;margin:0 auto;padding:2.5rem 1.5rem 4rem;}
    .page-header{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.8rem;}
    .page-header h1{font-family:'Barlow Condensed',sans-serif;font-size:2rem;font-weight:800;text-transform:uppercase;margin:0;}
    .page-header p{color:var(--muted);font-size:.88rem;margin:.3rem 0 0;}
    .search-bar{position:relative;margin-bottom:1.5rem;}
    .search-bar input{background:var(--navy3);border:1px solid rgba(255,255,255,.1);color:var(--white);border-radius:8px;padding:.7rem 1rem .7rem 2.8rem;font-size:.93rem;width:100%;transition:border-color .2s;}
    .search-bar input:focus{border-color:var(--yellow);box-shadow:0 0 0 3px rgba(245,197,24,.12);outline:none;}
    .search-bar input::placeholder{color:var(--muted);}
    .search-bar i{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--muted);}
    .c-card{background:#141b2d;border:1px solid var(--border);border-radius:14px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.35);}
    .c-card-header{background:linear-gradient(90deg,var(--navy3),var(--navy2));border-bottom:2px solid var(--yellow);padding:1.1rem 1.6rem;font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--yellow);display:flex;align-items:center;gap:.5rem;}
    .c-table{width:100%;border-collapse:collapse;}
    .c-table thead th{background:var(--navy3);color:var(--yellow);font-family:'Barlow Condensed',sans-serif;font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;padding:.9rem 1rem;border-bottom:2px solid var(--yellow);white-space:nowrap;}
    .c-table tbody tr{border-bottom:1px solid rgba(255,255,255,.05);transition:background .15s;}
    .c-table tbody tr:hover{background:rgba(245,197,24,.04);}
    .c-table tbody td{padding:.8rem 1rem;font-size:.88rem;color:var(--white);vertical-align:middle;}
    .c-table .td-muted{color:var(--muted);font-size:.82rem;}
    .badge-club{background:rgba(245,197,24,.1);color:var(--yellow);border:1px solid rgba(245,197,24,.25);border-radius:20px;padding:.15rem .6rem;font-size:.75rem;font-family:'Barlow Condensed',sans-serif;font-weight:600;white-space:nowrap;}
    .btn-edit{background:rgba(245,197,24,.1);color:var(--yellow);border:1px solid rgba(245,197,24,.25);border-radius:6px;padding:.3rem .8rem;font-family:'Barlow Condensed',sans-serif;font-size:.82rem;font-weight:700;text-transform:uppercase;text-decoration:none;display:inline-flex;align-items:center;gap:.25rem;transition:all .2s;margin-right:.4rem;}
    .btn-edit:hover{background:var(--yellow);color:var(--navy);}
    .btn-del{background:rgba(229,62,62,.1);color:#fc8181;border:1px solid rgba(229,62,62,.25);border-radius:6px;padding:.3rem .8rem;font-family:'Barlow Condensed',sans-serif;font-size:.82rem;font-weight:700;text-transform:uppercase;text-decoration:none;display:inline-flex;align-items:center;gap:.25rem;transition:all .2s;cursor:pointer;}
    .btn-del:hover{background:var(--danger);color:white;border-color:var(--danger);}
    .empty-state{text-align:center;padding:4rem 2rem;color:var(--muted);}
    .empty-state i{font-size:3rem;color:rgba(245,197,24,.2);margin-bottom:1rem;display:block;}
    .alert-s{background:rgba(56,161,105,.1);border:1px solid rgba(56,161,105,.3);border-radius:8px;padding:.9rem 1.2rem;color:#9ae6b4;font-size:.88rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.6rem;}
    .alert-e{background:rgba(229,62,62,.1);border:1px solid rgba(229,62,62,.3);border-radius:8px;padding:.9rem 1.2rem;color:#fc8181;font-size:.88rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.6rem;}
    .back-btn{display:inline-flex;align-items:center;gap:.4rem;color:var(--muted);text-decoration:none;font-size:.88rem;margin-bottom:1.5rem;transition:color .2s;}
    .back-btn:hover{color:var(--yellow);}
    .table-wrap{overflow-x:auto;}
    
    /* Delete modal */
    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:500;align-items:center;justify-content:center;}
    .modal-overlay.open{display:flex;}
    .modal-box{background:var(--navy2);border:1px solid var(--border);border-radius:14px;padding:2.5rem;max-width:420px;width:90%;text-align:center;box-shadow:0 16px 48px rgba(0,0,0,.6);}
    .modal-icon{width:64px;height:64px;background:rgba(229,62,62,.12);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:#fc8181;margin:0 auto 1.2rem;}
    .modal-box h3{font-family:'Barlow Condensed',sans-serif;font-size:1.5rem;font-weight:800;text-transform:uppercase;margin-bottom:.5rem;}
    .modal-box p{color:var(--muted);font-size:.9rem;line-height:1.5;margin-bottom:1.8rem;}
    .modal-box .name{color:var(--white);font-weight:600;}
    .modal-actions{display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;}
    .btn-del-confirm{background:var(--danger);color:white;border:none;border-radius:8px;padding:.75rem 1.8rem;font-family:'Barlow Condensed',sans-serif;font-size:1rem;font-weight:700;text-transform:uppercase;cursor:pointer;transition:background .2s;}
    .btn-del-confirm:hover{background:#c53030;}
    .btn-cancel{background:transparent;color:var(--muted);border:1px solid rgba(255,255,255,.15);border-radius:8px;padding:.75rem 1.8rem;font-family:'Barlow Condensed',sans-serif;font-size:1rem;font-weight:600;text-transform:uppercase;cursor:pointer;transition:all .2s;}
    .btn-cancel:hover{border-color:var(--yellow);color:var(--yellow);}
    @keyframes fadeUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
    .fu{animation:fadeUp .5s ease both;}
    @media(max-width:640px){.topbar{padding:.75rem 1rem;} .page-wrap{padding:1.5rem 1rem 3rem;} .c-table tbody td,.c-table thead th{padding:.7rem .7rem;font-size:.8rem;}}
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
    <a href="admin_menu.php" class="back-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>

    <div class="page-header fu">
      <div>
        <h1>Participants</h1>
        <p><?= count($participants) ?> registered participant<?= count($participants) !== 1 ? 's' : '' ?></p>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="<?= str_starts_with($flash, 'Error') ? 'alert-e' : 'alert-s' ?> fu">
        <i class="fas <?= str_starts_with($flash, 'Error') ? 'fa-times-circle' : 'fa-check-circle' ?>"></i>
        <?= htmlspecialchars($flash) ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert-e fu"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="search-bar fu">
      <i class="fas fa-search"></i>
      <input type="text" id="tableSearch" placeholder="Filter by name, email or club…">
    </div>

    <div class="c-card fu">
      <div class="c-card-header"><i class="fas fa-users"></i> All Participants</div>
      <div class="table-wrap">
        <?php if (!empty($participants)): ?>
          <table class="c-table" id="participantsTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Club</th>
                <th>Power (W)</th>
                <th>Distance (km)</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($participants as $p): ?>
                <tr>
                  <td class="td-muted"><?= (int)$p['id'] ?></td>
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
                  <td style="white-space:nowrap">
                    <a href="edit_participant_form.php?id=<?= (int)$p['id'] ?>" class="btn-edit">
                      <i class="fas fa-pen"></i> Edit
                    </a>
                    <button class="btn-del"
                            onclick="confirmDelete(<?= (int)$p['id'] ?>, '<?= htmlspecialchars(addslashes($p['firstname'] . ' ' . $p['surname'])) ?>')">
                      <i class="fas fa-trash"></i> Delete
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-users"></i>
            <p>No participants found in the database.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Delete confirmation modal -->
  <div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
      <div class="modal-icon"><i class="fas fa-trash-alt"></i></div>
      <h3>Delete Participant?</h3>
      <p>You are about to permanently remove <span class="name" id="deleteName"></span>. This action cannot be undone.</p>
      <div class="modal-actions">
        <button class="btn-cancel" onclick="closeModal()">Cancel</button>
        <form id="deleteForm" method="POST" action="delete.php" style="display:inline">
          <input type="hidden" name="id" id="deleteId">
          <input type="hidden" name="confirm" value="yes">
          <button type="submit" class="btn-del-confirm">Yes, Delete</button>
        </form>
      </div>
    </div>
  </div>

  

  <script>
    function confirmDelete(id, name) {
      document.getElementById('deleteName').textContent = name;
      document.getElementById('deleteId').value = id;
      document.getElementById('deleteModal').classList.add('open');
    }
    function closeModal() {
      document.getElementById('deleteModal').classList.remove('open');
    }
    document.getElementById('deleteModal').addEventListener('click', function(e) {
      if (e.target === this) closeModal();
    });

    // Live table filter
    document.getElementById('tableSearch').addEventListener('input', function() {
      const q = this.value.toLowerCase();
      document.querySelectorAll('#participantsTable tbody tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  </script>
</body>
</html>
