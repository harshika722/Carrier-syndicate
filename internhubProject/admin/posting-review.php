<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('postings.php');
csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    flash_error_set('Invalid posting.');
    redirect('postings.php');
}

$stmt = $pdo->prepare("UPDATE postings SET status = 'approved' WHERE id = ? AND status = 'pending_review'");
$stmt->execute([$id]);

if ($stmt->rowCount() === 1) {
    flash_set('Posting approved and published to students.');
} else {
    flash_error_set('That posting is no longer awaiting review.');
}

redirect('postings.php');
