<?php
require_once 'auth_check.php';
require_once '../config.php';
require_once '../db_engine.php';

// Fetch Counts
$posts = DBEngine::readJSON("posts.json") ?? [];
$users = scandir(DATA_PATH . "user_data/");
$user_count = count($users) - 2; // Subtract . and ..

// Scan for Pending Applications
$app_files = glob(DATA_PATH . "applications/*.json");
$pending_apps = [];
foreach ($app_files as $file) {
    $data = json_decode(file_get_contents($file), true);
    if ($data['status'] === 'pending') $pending_apps[] = $data;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <title>Admin Panel | Epistora</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-grid { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }
        .sidebar { background: var(--dark); color: white; padding: 2rem; }
        .sidebar a { display: block; color: #94a3b8; padding: 10px 0; text-decoration: none; }
        .sidebar a:hover { color: white; }
        .stat-box { background: var(--card-bg); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border); }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; }
        .badge-pending { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="admin-grid">
        <aside class="sidebar">
            <h2>Epistora Admin</h2>
            <nav>
                <a href="index.php">📊 Dashboard</a>
                <a href="manage_posts.php">📝 Manage Posts</a>
                <a href="manage_users.php">👥 Manage Users</a>
                <a href="../index.php">🌐 View Site</a>
            </nav>
        </aside>

        <main style="padding: 2rem;">
            <h1>Command Center</h1>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 3rem;">
                <div class="stat-box"><h3>Total Posts</h3><p><?= count($posts) ?></p></div>
                <div class="stat-box"><h3>Registered Users</h3><p><?= $user_count ?></p></div>
                <div class="stat-box"><h3>Pending Apps</h3><p><?= count($pending_apps) ?></p></div>
            </div>

            <h2>Pending Writer Applications</h2>
            <div class="app-list" style="background: white; border-radius: 12px; border: 1px solid var(--border);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; background: #f8fafc;">
                            <th style="padding: 1rem;">Applicant</th>
                            <th style="padding: 1rem;">Date</th>
                            <th style="padding: 1rem;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_apps as $app): ?>
                        <tr style="border-top: 1px solid var(--border);">
                            <td style="padding: 1rem;">
                                <strong><?= $app['biodata']['full_name'] ?></strong><br>
                                <small><?= $app['biodata']['email'] ?></small>
                            </td>
                            <td style="padding: 1rem;"><?= $app['date_human'] ?></td>
                            <td style="padding: 1rem;">
                                <a href="view_application.php?id=<?= $app['app_id'] ?>" class="btn-apply" style="padding: 5px 15px;">Review</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>