<?php
$currentPage = basename($_SERVER['PHP_SELF']);
function nav_active_a(string $file, string $current): string {
    return $file === $current ? ' active' : '';
}
?>
<aside class="sidebar">
  <div class="logo-slot">
    <div class="logo-mark"><img src="../career-syndicate-logo.png" alt="Career Syndicate"></div>
    <div class="logo-caption"><strong>Admin console</strong>Manage InternHub</div>
  </div>
  <nav class="side-nav">
    <a href="index.php"     class="<?= nav_active_a('index.php', $currentPage) ?>">Overview</a>
    <a href="companies.php" class="<?= nav_active_a('companies.php', $currentPage) . nav_active_a('company-edit.php', $currentPage) ?>">Companies</a>
    <a href="students.php"  class="<?= nav_active_a('students.php', $currentPage) . nav_active_a('student-edit.php', $currentPage) ?>">Students</a>
    <a href="postings.php"  class="<?= nav_active_a('postings.php', $currentPage) . nav_active_a('posting-edit.php', $currentPage) ?>">Postings</a>
    <a href="sessions.php"  class="<?= nav_active_a('sessions.php', $currentPage) ?>">Sessions</a>
    <a href="slots.php"     class="<?= nav_active_a('slots.php', $currentPage) . nav_active_a('slot-edit.php', $currentPage) ?>">Open Slots</a>
  </nav>
</aside>
