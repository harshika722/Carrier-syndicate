<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$type = ($_GET['type'] ?? '') === 'job' ? 'job' : 'internship';
$typeLabel = $type === 'job' ? 'Job' : 'Internship';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

$posting = [
    'title' => '', 'description' => '', 'open_positions' => 0,
    'applications_received' => 0, 'accepted' => 0, 'on_hold' => 0, 'rejected' => 0,
];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM postings WHERE id = ? AND type = ? AND company_id = ?");
    $stmt->execute([$id, $type, current_company_id()]);
    $found = $stmt->fetch();
    if (!$found) redirect($type === 'job' ? 'jobs.php' : 'internships.php');
    $posting = $found;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $id ? 'Edit' : 'Add' ?> <?= h($typeLabel) ?> — Company Dashboard</title>
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
        <h1><?= $id ? '✎ Edit' : '＋ Add' ?> <?= h($typeLabel) ?></h1>
        <p><?= $id ? 'Update this posting and save it as a draft or submit it for review.' : 'Save a draft now or submit the completed posting for admin review.' ?></p>
        <?php if ($id): ?>
          <p>Current state: <strong><?= h(ucfirst(str_replace('_', ' ', $posting['status'] ?? 'approved'))) ?></strong></p>
        <?php endif; ?>
      </div>

      <form class="form-card" method="post" action="posting-save.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)($posting['id'] ?? 0) ?>">
        <input type="hidden" name="type" value="<?= h($type) ?>">

        <label class="field">
          <span>Title</span>
          <input type="text" name="title" required maxlength="150" value="<?= h($posting['title']) ?>" placeholder="e.g. Frontend Engineering Intern">
        </label>

        <label class="field">
          <span>Description</span>
          <textarea name="description" rows="4" placeholder="Role details, requirements, etc."><?= h($posting['description']) ?></textarea>
        </label>

        <div class="field-grid">
          <label class="field">
            <span>Open positions</span>
            <input type="number" name="open_positions" min="0" value="<?= (int)$posting['open_positions'] ?>">
          </label>
          <label class="field">
            <span>Applications received</span>
            <input type="number" name="applications_received" min="0" value="<?= (int)$posting['applications_received'] ?>">
          </label>
          <label class="field">
            <span>Accepted</span>
            <input type="number" name="accepted" min="0" value="<?= (int)$posting['accepted'] ?>">
          </label>
          <label class="field">
            <span>On hold</span>
            <input type="number" name="on_hold" min="0" value="<?= (int)$posting['on_hold'] ?>">
          </label>
          <label class="field">
            <span>Rejected</span>
            <input type="number" name="rejected" min="0" value="<?= (int)$posting['rejected'] ?>">
          </label>
        </div>

        <div class="form-actions">
          <a href="<?= $type === 'job' ? 'jobs.php' : 'internships.php' ?>" class="btn btn-modify">Cancel</a>
          <button type="submit" name="action" value="save_draft" formnovalidate class="btn btn-modify">Save as Draft</button>
          <button type="submit" name="action" value="submit_for_review" class="btn btn-create">Submit for Review</button>
        </div>
      </form>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
<script src="js/script.js"></script>
</body>
</html>
