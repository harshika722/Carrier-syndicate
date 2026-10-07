<?php
require_once __DIR__ . '/includes/functions.php';
require_student_login();
csrf_verify();

$sid       = current_student_id();
$postingId = (int) ($_POST['posting_id'] ?? 0);

if ($postingId <= 0) {
    flash_error_set('Invalid posting.');
    redirect('postings.php');
}

$stmt = $pdo->prepare("SELECT id, company_id, open_positions, applications_received FROM postings WHERE id = ? AND status = 'approved'");
$stmt->execute([$postingId]);
$posting = $stmt->fetch();

if (!$posting) {
    flash_error_set('That posting is no longer available.');
    redirect('postings.php');
}

$dupStmt = $pdo->prepare("SELECT id FROM applications WHERE student_id = ? AND posting_id = ?");
$dupStmt->execute([$sid, $postingId]);
if ($dupStmt->fetch()) {
    flash_error_set('You already applied to this posting.');
    redirect('postings.php');
}

$pdo->beginTransaction();
try {
    $ins = $pdo->prepare("INSERT INTO applications (student_id, posting_id, status) VALUES (?, ?, 'applied')");
    $ins->execute([$sid, $postingId]);

    $upd = $pdo->prepare("UPDATE postings SET applications_received = applications_received + 1 WHERE id = ?");
    $upd->execute([$postingId]);

    add_notification($pdo, 'A student applied to one of your postings.', $posting['company_id']);

    $pdo->commit();
    flash_set('Application submitted!');
} catch (Exception $e) {
    $pdo->rollBack();
    flash_error_set('Could not submit your application. Please try again.');
}

redirect('postings.php');

function redirect(string $url) { header("Location: $url"); exit; }
function add_notification(PDO $pdo, string $message, int $companyId) {
    $stmt = $pdo->prepare("INSERT INTO notifications (company_id, message) VALUES (?, ?)");
    $stmt->execute([$companyId, $message]);
}
