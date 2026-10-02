<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('postings.php');
}

csrf_verify();

$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$id || !in_array($action, ['approve', 'reject'], true)) {
    flash_set('Invalid review request.');
    redirect('postings.php');
}

$newStatus = $action === 'approve' ? 'approved' : 'rejected';

$stmt = $pdo->prepare("
    UPDATE postings
    SET status = ?
    WHERE id = ?
      AND status = 'submitted'
");

$stmt->execute([$newStatus, $id]);

if ($stmt->rowCount() > 0) {
    flash_set(
        $action === 'approve'
            ? 'Posting approved successfully.'
            : 'Posting rejected successfully.'
    );
} else {
    flash_set('Posting was already reviewed or does not exist.');
}

redirect('postings.php');