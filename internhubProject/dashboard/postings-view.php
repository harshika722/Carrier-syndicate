<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if (!isset($type)) {
    redirect('index.php');
}

// Search & filter params
$search    = trim($_GET['q'] ?? '');
$filterCol = $_GET['filter'] ?? '';
$validCols = ['open_positions','applications_received','accepted','on_hold','rejected'];
if (!in_array($filterCol, $validCols, true)) $filterCol = '';

$sql    = "SELECT * FROM postings WHERE company_id = ? AND type = ?";
$params = [current_company_id(), $type];

if ($search !== '') {
    $sql    .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($filterCol !== '') {
    $sql .= " AND {$filterCol} > 0";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($typeLabel) ?>s — Company Dashboard</title>
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
      <div class="page-head list-page-head">
        <div>
          <h1><?= $typeIcon ?> <?= h($typeLabel) ?> postings</h1>
          <p>Everything here is read from and written to the <code>postings</code> table.</p>
        </div>
        <a href="posting-form.php?type=<?= h($type) ?>" class="btn btn-create">＋ Add <?= h($typeLabel) ?></a>
      </div>

      <!-- Search & filter bar -->
      <form class="filter-bar" method="get" action="">
        <input type="hidden" name="type" value="<?= h($type) ?>">
        <input type="text" name="q" placeholder="Search title or description…" value="<?= h($search) ?>">
        <select name="filter">
          <option value="">All</option>
          <option value="open_positions"     <?= $filterCol === 'open_positions'     ? 'selected' : '' ?>>Has open positions</option>
          <option value="applications_received" <?= $filterCol === 'applications_received' ? 'selected' : '' ?>>Has applications</option>
          <option value="accepted"           <?= $filterCol === 'accepted'           ? 'selected' : '' ?>>Has accepted</option>
          <option value="on_hold"            <?= $filterCol === 'on_hold'            ? 'selected' : '' ?>>Has on-hold</option>
          <option value="rejected"           <?= $filterCol === 'rejected'           ? 'selected' : '' ?>>Has rejected</option>
        </select>
        <button type="submit" class="btn btn-modify">🔍 Filter</button>
        <?php if ($search !== '' || $filterCol !== ''): ?>
          <a href="<?= $type === 'job' ? 'jobs.php' : 'internships.php' ?>" class="btn btn-danger">✕ Clear</a>
        <?php endif; ?>
      </form>

      <?php if (!$rows): ?>
        <div class="placeholder-box" style="min-height:160px;">
          <div class="ph-title">No <?= h(strtolower($typeLabel)) ?> postings found</div>
          <div><?= ($search || $filterCol) ? 'Try adjusting your search or filter.' : 'Click "Add ' . h($typeLabel) . '" above to create the first one.' ?></div>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>State</th>
                <th>Open</th>
                <th>Applications</th>
                <th>Accepted</th>
                <th>On Hold</th>
                <th>Rejected</th>
                <th>Updated</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td>
                    <div class="row-title"><?= h($row['title']) ?></div>
                    <?php if ($row['description']): ?><div class="row-sub"><?= h(mb_strimwidth($row['description'], 0, 80, '…')) ?></div><?php endif; ?>
                  </td>
                  <td><?= h(ucfirst(str_replace('_', ' ', $row['status'] ?? 'approved'))) ?></td>
                  <td><?= (int)$row['open_positions'] ?></td>
                  <td><?= (int)$row['applications_received'] ?></td>
                  <td><?= (int)$row['accepted'] ?></td>
                  <td><?= (int)$row['on_hold'] ?></td>
                  <td><?= (int)$row['rejected'] ?></td>
                  <td class="row-sub"><?= h(date('d M Y', strtotime($row['updated_at']))) ?></td>
                  <td class="row-actions">
                    <a class="btn btn-modify" href="posting-form.php?type=<?= h($type) ?>&id=<?= (int)$row['id'] ?>">✎ Edit</a>
                    <form class="js-delete" action="posting-delete.php" method="post">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                      <input type="hidden" name="type" value="<?= h($type) ?>">
                      <button type="submit" class="btn btn-danger">🗑 Delete</button>
                    </form>
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
<script src="js/script.js"></script>
</body>
</html>
