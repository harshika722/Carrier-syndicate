<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$type = ($_POST['type'] ?? '') === 'job' ? 'job' : 'internship';

$status = ($_POST['status'] ?? 'submitted') === 'draft'
    ? 'draft'
    : 'submitted';

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

$open = max(0, (int) ($_POST['open_positions'] ?? 0));
$applications = max(0, (int) ($_POST['applications_received'] ?? 0));
$accepted = max(0, (int) ($_POST['accepted'] ?? 0));
$onHold = max(0, (int) ($_POST['on_hold'] ?? 0));
$rejected = max(0, (int) ($_POST['rejected'] ?? 0));

$companyId = current_company_id();

/*
 * Title is required because the database column is NOT NULL.
 */
if ($title === '') {
    flash_set('Title is required — please try again.');

    redirect(
        'posting-form.php?type=' . urlencode($type) .
        ($id ? '&id=' . $id : '')
    );
}

/*
 * Enforce one draft total per company.
 *
 * This applies across BOTH internships and jobs.
 */
if ($status === 'draft') {

    $draftCheck = $pdo->prepare("
        SELECT id
        FROM postings
        WHERE company_id = ?
          AND status = 'draft'
          AND id != ?
        LIMIT 1
    ");

    $draftCheck->execute([$companyId, $id]);

    if ($draftCheck->fetch()) {
        flash_set(
            'You already have a draft posting. Submit or delete it before creating another draft.'
        );

        redirect(
            'posting-form.php?type=' . urlencode($type) .
            ($id ? '&id=' . $id : '')
        );
    }
}

if ($id) {

    $stmt = $pdo->prepare("
        UPDATE postings
        SET
            status = ?,
            title = ?,
            description = ?,
            open_positions = ?,
            applications_received = ?,
            accepted = ?,
            on_hold = ?,
            rejected = ?
        WHERE id = ?
          AND type = ?
          AND company_id = ?
    ");

    $stmt->execute([
        $status,
        $title,
        $description,
        $open,
        $applications,
        $accepted,
        $onHold,
        $rejected,
        $id,
        $type,
        $companyId
    ]);

    add_notification(
        $pdo,
        ucfirst($type) . " posting \"$title\" was updated."
    );

    flash_set(
        $status === 'draft'
            ? 'Posting saved as draft.'
            : 'Posting submitted for review.'
    );

} else {

    $stmt = $pdo->prepare("
        INSERT INTO postings
        (
            company_id,
            type,
            status,
            title,
            description,
            open_positions,
            applications_received,
            accepted,
            on_hold,
            rejected
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $companyId,
        $type,
        $status,
        $title,
        $description,
        $open,
        $applications,
        $accepted,
        $onHold,
        $rejected
    ]);

    add_notification(
        $pdo,
        ucfirst($type) . " posting \"$title\" was added."
    );

    flash_set(
        $status === 'draft'
            ? 'Posting saved as draft.'
            : 'Posting submitted for review.'
    );
}

redirect($type === 'job' ? 'jobs.php' : 'internships.php');