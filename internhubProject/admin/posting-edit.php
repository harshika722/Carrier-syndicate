<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, c.company_name FROM postings p JOIN companies c ON c.id = p.company_id WHERE p.id = ? AND p.status <> 'draft'");
$stmt->execute([$id]);
$posting = $stmt->fetch();
if (!$posting) { flash_error_set('Posting not found.'); redirect('postings.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $open_positions = (int) ($_POST['open_positions'] ?? 0);
    $accepted = (int) ($_POST['accepted'] ?? 0);
    $on_hold = (int) ($_POST['on_hold'] ?? 0);
    $rejected = (int) ($_POST['rejected'] ?? 0);

    if ($title === '') {
        flash_error_set('Title is required.');
    } else {
        $upd = $pdo->prepare(
            "UPDATE postings SET title=?, description=?, open_positions=?, accepted=?, on_hold=?, rejected=? WHERE id=?"
        );
        $upd->execute([$title, $description, $open_positions, $accepted, $on_hold, $rejected, $id]);
        flash_set('Posting updated.');
        redirect('postings.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit posting — Admin</title>
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
      <div class="page-head"><h1>Edit posting</h1><p><?= h($posting['company_name']) ?></p></div>
      <form method="post" action="posting-edit.php?id=<?= $id ?>" class="form-card">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="field"><label>Title</label><input type="text" name="title" value="<?= h($posting['title']) ?>" required></div>
        <div class="field"><label>Description</label><textarea name="description" rows="4"><?= h($posting['description']) ?></textarea></div>
        <div class="field"><label>Open positions</label><input type="number" name="open_positions" value="<?= (int)$posting['open_positions'] ?>" min="0"></div>
        <div class="field"><label>Accepted</label><input type="number" name="accepted" value="<?= (int)$posting['accepted'] ?>" min="0"></div>
        <div class="field"><label>On hold</label><input type="number" name="on_hold" value="<?= (int)$posting['on_hold'] ?>" min="0"></div>
        <div class="field"><label>Rejected</label><input type="number" name="rejected" value="<?= (int)$posting['rejected'] ?>" min="0"></div>
        <div class="card-actions">
          <button type="submit" class="btn btn-create">Save changes</button>
          <a href="postings.php" class="btn btn-modify">Cancel</a>
        </div>
      </form>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>
