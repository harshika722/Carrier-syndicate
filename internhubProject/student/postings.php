<?php
require_once __DIR__ . '/includes/functions.php';
require_student_login();
$sid = current_student_id();

$type   = $_GET['type'] ?? '';
$search = trim($_GET['q'] ?? '');
if (!in_array($type, ['internship', 'job'], true)) $type = '';

$sql = "SELECT p.*, c.company_name FROM postings p JOIN companies c ON c.id = p.company_id WHERE p.status = 'approved'";
$params = [];
if ($type !== '') { $sql .= " AND p.type = ?"; $params[] = $type; }
if ($search !== '') { $sql .= " AND (p.title LIKE ? OR c.company_name LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$postings = $stmt->fetchAll();

// Which postings has this student already applied to?
$appliedStmt = $pdo->prepare("SELECT posting_id, status FROM applications WHERE student_id = ?");
$appliedStmt->execute([$sid]);
$applied = [];
foreach ($appliedStmt->fetchAll() as $row) { $applied[$row['posting_id']] = $row['status']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Browse postings — InternHub</title>
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
        <h1>Browse internships &amp; jobs</h1>
        <p>Open postings from every verified company on InternHub.</p>
      </div>

      <form class="filter-bar" method="get" action="">
        <input type="text" name="q" placeholder="Search title or company…" value="<?= h($search) ?>">
        <select name="type">
          <option value="">All types</option>
          <option value="internship" <?= $type === 'internship' ? 'selected' : '' ?>>Internships</option>
          <option value="job" <?= $type === 'job' ? 'selected' : '' ?>>Jobs</option>
        </select>
        <button type="submit" class="btn btn-modify">🔍 Filter</button>
        <?php if ($search !== '' || $type !== ''): ?><a href="postings.php" class="btn btn-danger">✕ Clear</a><?php endif; ?>
      </form>

      <?php if (!$postings): ?>
        <div class="placeholder-box" style="min-height:160px;">
          <div class="ph-title">No postings found</div>
          <div>Try a different search or check back later.</div>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Title</th><th>Company</th><th>Type</th><th>Open positions</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($postings as $row): $status = $applied[$row['id']] ?? null; ?>
                <tr>
                  <td>
                    <div class="row-title"><?= h($row['title']) ?></div>
                    <?php if ($row['description']): ?><div class="row-sub"><?= h(mb_strimwidth($row['description'], 0, 90, '…')) ?></div><?php endif; ?>
                  </td>
                  <td><?= h($row['company_name']) ?></td>
                  <td><?= $row['type'] === 'internship' ? '🎓 Internship' : '💼 Job' ?></td>
                  <td><?= (int)$row['open_positions'] ?></td>
                  <td class="row-actions">
                    <?php if ($status): ?>
                      <span class="btn btn-modify" style="pointer-events:none;">Status: <?= h(ucfirst(str_replace('_',' ',$status))) ?></span>
                    <?php else: ?>
                      <form action="apply.php" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="posting_id" value="<?= (int)$row['id'] ?>">
                        <button type="submit" class="btn btn-create">Apply</button>
                      </form>
                    <?php endif; ?>
                  </td>
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
