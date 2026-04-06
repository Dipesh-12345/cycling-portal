<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search — Cit-E Cycling Admin</title>
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
    .topbar-nav a{color:var(--muted);text-decoration:none;font-size:.88rem;font-weight:500;margin-left:1.4rem;transition:color .2s;}
    .topbar-nav a:hover{color:var(--yellow);}

    .page-wrap{max-width:720px;margin:0 auto;padding:2.5rem 1.5rem 4rem;}
    .back-btn{display:inline-flex;align-items:center;gap:.4rem;color:var(--muted);text-decoration:none;font-size:.88rem;margin-bottom:1.5rem;transition:color .2s;}
    .back-btn:hover{color:var(--yellow);}
    .page-header{margin-bottom:2rem;}
    .page-header h1{font-family:'Barlow Condensed',sans-serif;font-size:2rem;font-weight:800;text-transform:uppercase;margin:0;}
    .page-header p{color:var(--muted);font-size:.88rem;margin:.3rem 0 0;}

    /* Tab switcher */
    .tab-switch{display:flex;background:var(--navy3);border:1px solid var(--border);border-radius:10px;padding:.3rem;margin-bottom:2rem;gap:.3rem;}
    .tab-btn{flex:1;text-align:center;padding:.65rem 1rem;border-radius:7px;font-family:'Barlow Condensed',sans-serif;font-size:1rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;border:none;background:transparent;color:var(--muted);transition:all .2s;}
    .tab-btn.active{background:var(--yellow);color:var(--navy);}
    .tab-btn:hover:not(.active){color:var(--white);}

    /* Tab panes */
    .tab-pane{display:none;}
    .tab-pane.active{display:block;}

    .c-card{background:#141b2d;border:1px solid var(--border);border-radius:14px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.35);}
    .c-card-header{background:linear-gradient(90deg,var(--navy3),var(--navy2));border-bottom:2px solid var(--yellow);padding:1.1rem 1.6rem;font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--yellow);display:flex;align-items:center;gap:.5rem;}
    .c-card-body{padding:1.8rem;}
    .form-label{font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:.35rem;display:block;}
    .form-control{background:var(--navy3);border:1px solid rgba(255,255,255,.1);color:var(--white);border-radius:8px;padding:.72rem 1rem;font-size:.95rem;width:100%;transition:border-color .2s,box-shadow .2s;}
    .form-control:focus{background:var(--navy3);color:var(--white);border-color:var(--yellow);box-shadow:0 0 0 3px rgba(245,197,24,.15);outline:none;}
    .form-control::placeholder{color:var(--muted);}
    .field-gap{margin-bottom:1.3rem;}
    .row-gap{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
    @media(max-width:500px){.row-gap{grid-template-columns:1fr;}}
    .btn-search{background:var(--yellow);color:var(--navy);border:none;border-radius:8px;padding:.78rem 2rem;font-family:'Barlow Condensed',sans-serif;font-size:1.05rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;cursor:pointer;width:100%;transition:background .2s,transform .15s;display:flex;align-items:center;justify-content:center;gap:.5rem;}
    .btn-search:hover{background:#ffd700;transform:translateY(-1px);}
    .form-hint{color:var(--muted);font-size:.78rem;margin-top:.3rem;}

    
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
      <a href="view_participants_edit_delete.php"><i class="fas fa-users"></i> Participants</a>
      <a href="logout.php" style="color:#fc8181"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </nav>

  <div class="page-wrap">
    <a href="admin_menu.php" class="back-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>

    <div class="page-header fu">
      <h1>Search</h1>
      <p>Find participants by name, or look up club statistics.</p>
    </div>

    <!-- Tab switcher -->
    <div class="tab-switch fu fu-1">
      <button class="tab-btn active" onclick="switchTab('participants', this)">
        <i class="fas fa-user"></i> Participants
      </button>
      <button class="tab-btn" onclick="switchTab('clubs', this)">
        <i class="fas fa-flag"></i> Clubs
      </button>
    </div>

    <!-- Participants tab -->
    <div class="tab-pane active" id="tab-participants">
      <div class="c-card fu fu-1">
        <div class="c-card-header"><i class="fas fa-user-search"></i> Search Participants</div>
        <div class="c-card-body">
          <form action="search_result.php" method="POST" novalidate>
            <input type="hidden" name="search_type" value="participant">
            <div class="row-gap field-gap">
              <div>
                <label class="form-label" for="firstname">First Name</label>
                <input type="text" class="form-control" id="firstname" name="firstname"
                       placeholder="e.g. Jane">
                <div class="form-hint">Leave blank to match any first name.</div>
              </div>
              <div>
                <label class="form-label" for="surname">Surname</label>
                <input type="text" class="form-control" id="surname" name="surname"
                       placeholder="e.g. Smith">
                <div class="form-hint">Leave blank to match any surname.</div>
              </div>
            </div>
            <button type="submit" class="btn-search">
              <i class="fas fa-search"></i> Search Participants
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Clubs tab -->
    <div class="tab-pane" id="tab-clubs">
      <div class="c-card fu fu-1">
        <div class="c-card-header"><i class="fas fa-flag"></i> Search Clubs</div>
        <div class="c-card-body">
          <form action="search_result.php" method="POST" novalidate>
            <input type="hidden" name="search_type" value="club">
            <div class="field-gap">
              <label class="form-label" for="club">Club Name</label>
              <input type="text" class="form-control" id="club" name="club"
                     placeholder="e.g. Roker Rollers">
              <div class="form-hint">Leave blank to show all clubs.</div>
            </div>
            <button type="submit" class="btn-search">
              <i class="fas fa-search"></i> Search Clubs
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  

  <script>
    function switchTab(tab, btn) {
      document.querySelectorAll('.tab-pane').forEach(function(p) { p.classList.remove('active'); });
      document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
      document.getElementById('tab-' + tab).classList.add('active');
      btn.classList.add('active');
    }

    // If arriving from a club search result, pre-select clubs tab
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'clubs') {
      switchTab('clubs', document.querySelectorAll('.tab-btn')[1]);
    }
  </script>
</body>
</html>
