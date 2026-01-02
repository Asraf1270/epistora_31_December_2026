<?php
require_once 'auth_check.php';
require_once '../db_engine.php';

$logs = DBEngine::readJSON("system_logs.json") ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>System Logs | Epistora Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; background: var(--bg); min-height: 100vh; }
        .log-table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow); }
        .log-table th { background: #f1f5f9; padding: 1rem; text-align: left; font-size: 0.8rem; }
        .log-table td { padding: 1rem; border-top: 1px solid var(--border); font-size: 0.9rem; }
        .badge-action { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.7rem; color: white; }
    </style>
</head>
<body class="admin-layout">

    <?php include 'sidebar.php'; ?>

    <main style="flex: 1; padding: 2rem; overflow-y: auto;">
        <h1>Activity Stream</h1>
        <p>Live audit trail of all platform events.</p>

        <table class="log-table" style="margin-top: 2rem;">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td style="color: var(--text-muted); font-family: monospace;"><?= $log['date'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($log['admin_name']) ?></strong><br>
                        <small>ID: <?= $log['admin_id'] ?></small>
                    </td>
                    <td>
                        <?php 
                        $color = '#3b82f6'; // default blue
                        if(strpos($log['action'], 'DELETE') !== false) $color = '#ef4444';
                        if(strpos($log['action'], 'AUTH') !== false) $color = '#8b5cf6';
                        if(strpos($log['action'], 'PROMOTION') !== false) $color = '#10b981';
                        ?>
                        <span class="badge-action" style="background: <?= $color ?>;"><?= $log['action'] ?></span>
                    </td>
                    <td><?= htmlspecialchars($log['details']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>