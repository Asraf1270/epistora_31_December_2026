<?php
require_once 'auth_check.php';
require_once '../config.php';
require_once '../db_engine.php';

$app_id = $_GET['id'] ?? die("Application ID required.");
$app = DBEngine::readJSON("applications/$app_id.json");

if (!$app) die("Application not found.");

// --- DECISION HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $uid = $app['user_id'];

    if ($action === 'approve') {
        // 1. Elevate User Role
        $user_vault = DBEngine::readJSON("user_data/$uid.json");
        $user_vault['role'] = 'writer';
        $user_vault['writer_status'] = 'approved';
        DBEngine::writeJSON("user_data/$uid.json", $user_vault);

        // 2. Update Application Status
        $app['status'] = 'approved';
        DBEngine::writeJSON("applications/$app_id.json", $app);

        // 3. Audit Log
        DBEngine::logAction($_SESSION['user_id'], $_SESSION['user_name'], 'APPROVE_WRITER', "Promoted user $uid to Writer role.");
        
        header("Location: index.php?msg=approved");
    } else {
        // Reject Logic
        $app['status'] = 'rejected';
        DBEngine::writeJSON("applications/$app_id.json", $app);
        DBEngine::logAction($_SESSION['user_id'], $_SESSION['user_name'], 'REJECT_WRITER', "Rejected application $app_id.");
        header("Location: index.php?msg=rejected");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Review Application | Epistora</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .review-card { max-width: 800px; margin: 2rem auto; background: var(--card-bg); padding: 2rem; border-radius: 12px; border: 1px solid var(--border); }
        .data-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; }
        .label { font-size: 0.8rem; color: var(--text-muted); font-weight: bold; }
        .val { font-size: 1.1rem; margin-bottom: 1rem; }
        .sample-box { background: var(--bg); padding: 1.5rem; border-radius: 8px; border-left: 4px solid var(--primary); font-style: italic; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" style="display: block; margin: 2rem 0;">← Back to Dashboard</a>
        
        <div class="review-card">
            <header style="border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                <h1>Writer Application Review</h1>
                <p>Status: <span class="badge" style="background: #fef3c7; color: #92400e;"><?= strtoupper($app['status']) ?></span></p>
            </header>

            <section>
                <h3>Applicant Biodata</h3>
                <div class="data-grid">
                    <div><p class="label">Full Name</p><p class="val"><?= htmlspecialchars($app['biodata']['full_name']) ?></p></div>
                    <div><p class="label">Father's Name</p><p class="val"><?= htmlspecialchars($app['biodata']['father_name']) ?></p></div>
                    <div><p class="label">Email</p><p class="val"><?= $app['biodata']['email'] ?></p></div>
                    <div><p class="label">Phone</p><p class="val"><?= $app['biodata']['phone'] ?></p></div>
                    <div><p class="label">Date of Birth</p><p class="val"><?= $app['biodata']['dob'] ?></p></div>
                    <div><p class="label">Address</p><p class="val"><?= htmlspecialchars($app['biodata']['address']) ?></p></div>
                </div>
            </section>

            <section style="margin-top: 2rem;">
                <h3>Writing Sample</h3>
                <div class="sample-box">
                    <?= nl2br(htmlspecialchars($app['portfolio']['sample'])) ?>
                </div>
            </section>

            <section style="margin-top: 2rem;">
                <h3>External Portfolio Bookmarks</h3>
                <ul style="padding-left: 1.2rem;">
                    <?php foreach($app['portfolio']['bookmarks'] as $link): ?>
                        <li><a href="<?= $link ?>" target="_blank"><?= $link ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <form method="POST" style="margin-top: 3rem; display: flex; gap: 1rem; border-top: 1px solid var(--border); padding-top: 2rem;">
                <button type="submit" name="action" value="approve" style="flex: 2; background: #22c55e; color: white; border: none; padding: 1rem; border-radius: 8px; font-weight: bold; cursor: pointer;">APPROVE & PROMOTE</button>
                <button type="submit" name="action" value="reject" style="flex: 1; background: #ef4444; color: white; border: none; padding: 1rem; border-radius: 8px; font-weight: bold; cursor: pointer;">REJECT</button>
            </form>
        </div>
    </div>
</body>
</html>