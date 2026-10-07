<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$type = ($_POST['type'] ?? '') === 'job' ? 'job' : 'internship';
$action = $_POST['action'] ?? '';
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$open = max(0, (int) ($_POST['open_positions'] ?? 0));
$applications = max(0, (int) ($_POST['applications_received'] ?? 0));
$accepted = max(0, (int) ($_POST['accepted'] ?? 0));
$onHold = max(0, (int) ($_POST['on_hold'] ?? 0));
$rejected = max(0, (int) ($_POST['rejected'] ?? 0));

if (!in_array($action, ['save_draft', 'submit_for_review'], true)) {
    flash_set('Choose whether to save this posting as a draft or submit it for review.');
    redirect('posting-form.php?type=' . urlencode($type) . ($id ? '&id=' . $id : ''));
}

if ($action === 'submit_for_review' && $title === '') {
    flash_set('Title is required — please try again.');
    redirect('posting-form.php?type=' . urlencode($type) . ($id ? '&id=' . $id : ''));
}

$companyId = current_company_id();
$status = $action === 'save_draft' ? 'draft' : 'pending_review';

try {
    $pdo->beginTransaction();

    // Serialize this company's saves so two requests cannot create two drafts.
    $companyLock = $pdo->prepare("SELECT id FROM companies WHERE id = ? FOR UPDATE");
    $companyLock->execute([$companyId]);
    if (!$companyLock->fetchColumn()) {
        throw new RuntimeException('Company account not found.');
    }

    if ($id) {
        $ownedPosting = $pdo->prepare("SELECT id FROM postings WHERE id = ? AND type = ? AND company_id = ? FOR UPDATE");
        $ownedPosting->execute([$id, $type, $companyId]);
        if (!$ownedPosting->fetchColumn()) {
            throw new RuntimeException('Posting not found.');
        }
    }

    if ($status === 'draft') {
        $draftCheck = $pdo->prepare("SELECT id FROM postings WHERE company_id = ? AND status = 'draft' AND id <> ? LIMIT 1");
        $draftCheck->execute([$companyId, $id]);
        if ($draftCheck->fetchColumn()) {
            throw new RuntimeException('Your company already has a draft. Edit or delete it before saving another.');
        }
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE postings SET title=?, description=?, open_positions=?, applications_received=?, accepted=?, on_hold=?, rejected=?, status=? WHERE id=? AND type=? AND company_id=?");
        $stmt->execute([$title, $description, $open, $applications, $accepted, $onHold, $rejected, $status, $id, $type, $companyId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO postings (company_id, type, title, description, open_positions, applications_received, accepted, on_hold, rejected, status) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$companyId, $type, $title, $description, $open, $applications, $accepted, $onHold, $rejected, $status]);
    }

    $pdo->commit();
    flash_set($status === 'draft' ? 'Draft saved.' : 'Posting submitted for admin review.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash_set($e instanceof RuntimeException ? $e->getMessage() : 'Could not save the posting. Please try again.');
}

redirect($type === 'job' ? 'jobs.php' : 'internships.php');
