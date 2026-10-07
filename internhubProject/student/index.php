<?php
require_once __DIR__ . '/includes/functions.php';
require_student_login();
$sid = current_student_id();

$countStmt = $pdo->prepare("SELECT a.status, COUNT(*) AS n FROM applications a JOIN postings p ON p.id = a.posting_id WHERE a.student_id = ? AND p.status = 'approved' GROUP BY a.status");
$countStmt->execute([$sid]);
$counts = ['applied' => 0, 'on_hold' => 0, 'accepted' => 0, 'rejected' => 0];
foreach ($countStmt->fetchAll() as $row) { $counts[$row['status']] = (int)$row['n']; }
$totalApplications = array_sum($counts);

$recentStmt = $pdo->prepare(
    "SELECT p.id, p.title, p.type, c.company_name
     FROM postings p JOIN companies c ON c.id = p.company_id
    WHERE p.open_positions > 0 AND p.status = 'approved'
     ORDER BY p.created_at DESC LIMIT 5"
);
$recentStmt->execute();
$recentPostings = $recentStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard — InternHub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="shell">
  <?php include 'includes/header.php'; ?>
  <div class="body">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main">
      <div class="page-head">
        <h1>Welcome, <?= h($_SESSION['student_name'] ?? 'Student') ?></h1>
        <p>Browse open roles and track every application from here.</p>
      </div>

      <div class="grid">
        <div class="card">
          <div class="card-head"><div class="card-title"><div class="card-icon">📄</div><h3>My applications</h3></div></div>
          <div class="stat-list">
            <div class="stat-row"><span class="stat-label">Total applied</span><a href="applications.php" class="stat-count"><?= $totalApplications ?></a></div>
            <div class="stat-row"><span class="stat-label">On hold</span><a href="applications.php" class="stat-count"><?= $counts['on_hold'] ?></a></div>
            <div class="stat-row"><span class="stat-label">Accepted</span><a href="applications.php" class="stat-count"><?= $counts['accepted'] ?></a></div>
            <div class="stat-row"><span class="stat-label">Rejected</span><a href="applications.php" class="stat-count"><?= $counts['rejected'] ?></a></div>
          </div>
          <div class="card-actions"><a href="postings.php" class="btn btn-create">＋ Find roles</a></div>
        </div>

        <div class="card">
          <div class="card-head"><div class="card-title"><div class="card-icon">✨</div><h3>Newly posted</h3></div></div>
          <?php if (!$recentPostings): ?>
            <div class="card-note">No open postings yet — check back soon.</div>
          <?php else: ?>
            <div class="stat-list">
              <?php foreach ($recentPostings as $p): ?>
                <div class="stat-row">
                  <span class="stat-label"><?= h($p['title']) ?> · <?= h($p['company_name']) ?></span>
                  <a href="postings.php" class="stat-count"><?= $p['type'] === 'internship' ? '🎓' : '💼' ?></a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="card-actions"><a href="postings.php" class="btn btn-modify">Browse all</a></div>
        </div>
      </div>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>
