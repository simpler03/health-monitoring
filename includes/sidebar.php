<?php
// Sidebar include with active link highlighting.
// Requires BaseConfig and an active user session.
$user = $_SESSION ?? [];
$current_role = $user['role'] ?? 'viewer';
$activePage = basename($_SERVER['PHP_SELF']);
$navItems = [
    ['href' => 'dashboard.php', 'label' => 'Dashboard', 'roles' => ['admin', 'input', 'viewer']],
    ['href' => 'evaluations.php', 'label' => 'Evaluations', 'roles' => ['admin', 'input', 'viewer']],
    ['href' => 'new-evaluation.php', 'label' => 'New Evaluation', 'roles' => ['admin', 'input']],
    ['href' => 'facilities.php', 'label' => 'Facilities', 'roles' => ['admin', 'input', 'viewer']],
    ['href' => 'users.php', 'label' => 'Users', 'roles' => ['admin']],
    ['href' => 'audit-log.php', 'label' => 'Audit Log', 'roles' => ['admin']],
    ['href' => 'profile.php', 'label' => 'Profile', 'roles' => ['admin', 'input', 'viewer']],
];
?>
<div class="sidebar">
    <div class="logo">📊 Health Monitoring</div>
    <ul class="nav-menu">
        <?php foreach ($navItems as $nav): ?>
            <?php if (in_array($current_role, $nav['roles'])): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($nav['href']); ?>" class="<?php echo $activePage === $nav['href'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($nav['label']); ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <div class="user-info">
        <p><strong><?php echo htmlspecialchars($user['full_name'] ?? 'Guest'); ?></strong></p>
        <p><?php echo ucfirst(htmlspecialchars($current_role)); ?></p>
        <p style="font-size: 12px; color: rgba(255,255,255,0.7);"><?php echo htmlspecialchars($user['facility_name'] ?? 'N/A'); ?></p>
        <form action="logout.php" method="POST" style="margin-top: 10px;">
            <button type="submit" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</button>
        </form>
    </div>
</div>
