<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$counts = [];
foreach (['companies','students','sessions','slots','applications'] as $t) {
    $counts[$t] = (int) $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
}
$counts['postings'] = (int) $pdo->query("SELECT COUNT(*) FROM postings WHERE status <> 'draft'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Overview — InternHub</title>
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
      <div class="page-head"><h1>Overview</h1><p>Everything on InternHub, at a glance.</p></div>
      <div class="grid">
        <div class="card">
          <div class="card-head"><div class="card-title"><div class="card-icon">🏢</div><h3>Companies</h3></div></div>
          <div class="stat-list"><div class="stat-row"><span class="stat-label">Registered companies</span><a href="companies.php" class="stat-count"><?= $counts['companies'] ?></a></div></div>
          <div class="card-actions"><a href="companies.php" class="btn btn-modify">Manage</a></div>
        </div>
        <div class="card">
          <div class="card-head"><div class="card-title"><div class="card-icon">🎓</div><h3>Students</h3></div></div>
          <div class="stat-list"><div class="stat-row"><span class="stat-label">Registered students</span><a href="students.php" class="stat-count"><?= $counts['students'] ?></a></div></div>
          <div class="card-actions"><a href="students.php" class="btn btn-modify">Manage</a></div>
        </div>
        <div class="card">
          <div class="card-head"><div class="card-title"><div class="card-icon">📄</div><h3>Postings</h3></div></div>
          <div class="stat-list">
            <div class="stat-row"><span class="stat-label">All postings</span><a href="postings.php" class="stat-count"><?= $counts['postings'] ?></a></div>
            <div class="stat-row"><span class="stat-label">Applications submitted</span><span class="stat-count"><?= $counts['applications'] ?></span></div>
          </div>
          <div class="card-actions"><a href="postings.php" class="btn btn-modify">Manage</a></div>
        </div>
        <div class="card">
          <div class="card-head"><div class="card-title"><div class="card-icon">🎥</div><h3>Sessions</h3></div></div>
          <div class="stat-list"><div class="stat-row"><span class="stat-label">All sessions</span><a href="sessions.php" class="stat-count"><?= $counts['sessions'] ?></a></div></div>
          <div class="card-actions"><a href="sessions.php" class="btn btn-modify">Manage</a></div>
        </div>
        <div class="card">
          <div class="card-head"><div class="card-title"><div class="card-icon">🎯</div><h3>Open slots</h3></div></div>
          <div class="stat-list"><div class="stat-row"><span class="stat-label">Slot categories</span><a href="slots.php" class="stat-count"><?= $counts['slots'] ?></a></div></div>
          <div class="card-actions"><a href="slots.php" class="btn btn-modify">Manage</a></div>
        </div>
      </div>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>
