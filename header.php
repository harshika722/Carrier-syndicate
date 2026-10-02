<?php
$flash      = flash_get();
$flashError = flash_error_get();
?>
<header class="topbar">
  <div class="topbar-left">
    <div class="topbar-logo"><img src="../career-syndicate-logo.png" alt="Career Syndicate"></div>
    <div class="topbar-title"><?= h($_SESSION['student_name'] ?? 'Student') ?></div>
  </div>
  <div class="topbar-right">
    <a class="icon-btn" href="profile.php" title="Profile">👤</a>
    <a class="icon-btn" href="logout.php" title="Log out">↩</a>
  </div>
</header>
<?php if ($flash): ?><div class="flash-banner flash-success"><?= h($flash) ?></div><?php endif; ?>
<?php if ($flashError): ?><div class="flash-banner flash-error"><?= h($flashError) ?></div><?php endif; ?>
