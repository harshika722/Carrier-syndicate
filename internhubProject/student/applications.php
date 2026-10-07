<?php
require_once __DIR__ . '/includes/functions.php';
require_student_login();
$sid = current_student_id();

$stmt = $pdo->prepare(
    "SELECT a.*, p.title, p.type, c.company_name
     FROM applications a
     JOIN postings p ON p.id = a.posting_id
     JOIN companies c ON c.id = p.company_id
    WHERE a.student_id = ? AND p.status = 'approved'
     ORDER BY a.applied_at DESC"
);
$stmt->execute([$sid]);
$rows = $stmt->fetchAll();

$badge = ['applied' => '', 'on_hold' => 'amber', 'accepted' => 'green', 'rejected' => 'red'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My applications — InternHub</title>
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
        <h1>My applications</h1>
        <p>Every posting you've applied to and its current status.</p>
      </div>

      <?php if (!$rows): ?>
        <div class="placeholder-box" style="min-height:160px;">
          <div class="ph-title">You haven't applied to anything yet</div>
          <div><a href="postings.php">Browse open postings</a> to get started.</div>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Title</th><th>Company</th><th>Type</th><th>Applied</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td class="row-title"><?= h($row['title']) ?></td>
                  <td><?= h($row['company_name']) ?></td>
                  <td><?= $row['type'] === 'internship' ? '🎓 Internship' : '💼 Job' ?></td>
                  <td class="row-sub"><?= h(date('d M Y', strtotime($row['applied_at']))) ?></td>
                  <td><?= h(ucfirst(str_replace('_',' ',$row['status']))) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>
