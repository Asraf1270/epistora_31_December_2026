<?php
// admin/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" style="width: 260px; background: var(--dark); color: white; min-height: 100vh; padding: 2rem 1rem; flex-shrink: 0;">
    <div class="sidebar-header" style="padding-bottom: 2rem; border-bottom: 1px solid #334155; margin-bottom: 2rem;">
        <h2 style="color: var(--primary); font-size: 1.2rem; margin: 0;">EPISTORA ADMIN</h2>
        <small style="color: #94a3b8;">System Control v<?= APP_VERSION ?></small>
    </div>

    <nav class="sidebar-nav">
        <ul style="list-style: none; padding: 0;">
            <li style="margin-bottom: 0.5rem;">
                <a href="index.php" style="display: block; padding: 0.8rem 1rem; color: <?= ($current_page == 'index.php') ? 'white' : '#94a3b8' ?>; text-decoration: none; border-radius: 8px; background: <?= ($current_page == 'index.php') ? '#334155' : 'transparent' ?>;">📊 Dashboard</a>
            </li>
            <li style="margin-bottom: 0.5rem;">
                <a href="manage_posts.php" style="display: block; padding: 0.8rem 1rem; color: <?= ($current_page == 'manage_posts.php') ? 'white' : '#94a3b8' ?>; text-decoration: none; border-radius: 8px; background: <?= ($current_page == 'manage_posts.php') ? '#334155' : 'transparent' ?>;">📝 Manage Posts</a>
            </li>
            <li style="margin-bottom: 0.5rem;">
                <a href="manage_users.php" style="display: block; padding: 0.8rem 1rem; color: <?= ($current_page == 'manage_users.php') ? 'white' : '#94a3b8' ?>; text-decoration: none; border-radius: 8px; background: <?= ($current_page == 'manage_users.php') ? '#334155' : 'transparent' ?>;">👥 Manage Users</a>
            </li>
            <li style="margin-bottom: 0.5rem;">
                <a href="system_logs.php" style="display: block; padding: 0.8rem 1rem; color: <?= ($current_page == 'system_logs.php') ? 'white' : '#94a3b8' ?>; text-decoration: none; border-radius: 8px; background: <?= ($current_page == 'system_logs.php') ? '#334155' : 'transparent' ?>;">📜 System Logs</a>
            </li>
            <li style="margin-bottom: 0.5rem;">
                <a href="settings.php" style="display: block; padding: 0.8rem 1rem; color: <?= ($current_page == 'settings.php') ? 'white' : '#94a3b8' ?>; text-decoration: none; border-radius: 8px; background: <?= ($current_page == 'settings.php') ? '#334155' : 'transparent' ?>;">⚙️ Settings</a>
            </li>
            <li style="margin-bottom: 0.5rem;">
                <a href="backup.php" style="display: block; padding: 0.8rem 1rem; color: <?= ($current_page == 'backup.php') ? 'white' : '#94a3b8' ?>; text-decoration: none; border-radius: 8px;">📦 Data Backup</a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer" style="margin-top: auto; padding-top: 2rem;">
        <a href="../index.php" style="display: block; text-align: center; padding: 0.8rem; background: var(--primary); color: white; text-decoration: none; border-radius: 8px; font-weight: bold;">Exit Admin</a>
    </div>
</aside>