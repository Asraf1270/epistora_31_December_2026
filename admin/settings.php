<?php
require_once 'auth_check.php';
require_once '../db_engine.php';

$state_file = "../data/system_state.json";
$state = DBEngine::readJSON("system_state.json") ?? ['maintenance_mode' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = isset($_POST['m_mode']);
    $state['maintenance_mode'] = $new_status;
    $state['last_updated_by'] = $_SESSION['user_name'];
    
    DBEngine::writeJSON("system_state.json", $state);
    
    // Log the event
    $log_msg = $new_status ? "Enabled Global Maintenance Mode" : "Disabled Global Maintenance Mode";
    DBEngine::logAction($_SESSION['user_id'], $_SESSION['user_name'], 'SYSTEM_CONFIG', $log_msg);
    
    header("Location: settings.php?saved=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>System Settings | Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-grid">
        <?php include 'sidebar.php'; ?>
        <main style="padding: 2rem;">
            <h1>System Controls</h1>
            <form method="POST" class="stat-box" style="max-width: 400px;">
                <label style="display: flex; align-items: center; cursor: pointer; gap: 15px;">
                    <input type="checkbox" name="m_mode" <?= $state['maintenance_mode'] ? 'checked' : '' ?> style="transform: scale(1.5);">
                    <strong>Activate Maintenance Mode</strong>
                </label>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 10px;">
                    While active, regular users will be redirected to a maintenance screen. Admins retain full access.
                </p>
                <button type="submit" class="btn-apply" style="margin-top: 20px; width: 100%;">Save System State</button>
            </form>
        </main>
    </div>
</body>
</html>