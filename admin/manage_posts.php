<?php
require_once 'auth_check.php';
require_once '../config.php';
require_once '../db_engine.php';

$posts = DBEngine::readJSON("posts.json") ?? [];
$message = "";

// --- DELETE HANDLER ---
if (isset($_GET['delete_id'])) {
    $target_id = $_GET['delete_id'];
    
    // 1. Find the title before we delete it (for the logs)
    $title = "Unknown Title";
    foreach ($posts as $p) {
        if ($p['post_id'] === $target_id) { $title = $p['title']; break; }
    }

    // 2. Remove from global index
    $posts = array_filter($posts, function($p) use ($target_id) {
        return $p['post_id'] !== $target_id;
    });
    DBEngine::writeJSON("posts.json", array_values($posts));

    // 3. Delete physical file
    $content_file = DATA_PATH . "post_content/$target_id.json";
    if (file_exists($content_file)) { unlink($content_file); }

    // 4. Log the action
    DBEngine::logAction(
        $_SESSION['user_id'], 
        $_SESSION['user_name'], 
        'DELETE_POST', 
        "Censored post: '$title' (ID: $target_id)"
    );

    $message = "Post successfully removed.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Post Management | Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-layout">
    <div class="admin-grid">
        <?php include 'sidebar.php'; ?>
        
        <main style="padding: 2rem;">
            <header style="display: flex; justify-content: space-between;">
                <h1>Global Content Manager</h1>
                <?php if($message): ?>
                    <div style="background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px;"><?= $message ?></div>
                <?php endif; ?>
            </header>

            <div class="table-container" style="background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border); margin-top: 1rem;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8fafc; border-bottom: 2px solid var(--border);">
                        <tr>
                            <th style="padding: 1rem; text-align: left;">Article</th>
                            <th style="padding: 1rem; text-align: left;">Author</th>
                            <th style="padding: 1rem; text-align: left;">Stats</th>
                            <th style="padding: 1rem; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 1rem;">
                                <strong><?= htmlspecialchars($post['title']) ?></strong><br>
                                <small style="color: var(--text-muted);"><?= $post['post_id'] ?></small>
                            </td>
                            <td style="padding: 1rem;"><?= htmlspecialchars($post['author']) ?></td>
                            <td style="padding: 1rem;">👁️ <?= $post['views'] ?? 0 ?> | ❤️ <?= array_sum($post['reactions'] ?? []) ?></td>
                            <td style="padding: 1rem; text-align: right;">
                                <a href="../post/view/?id=<?= $post['post_id'] ?>" target="_blank" style="margin-right: 1rem;">View</a>
                                <button onclick="confirmDelete('<?= $post['post_id'] ?>')" style="color: #ef4444; border: 1px solid #ef4444; background: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
    function confirmDelete(id) {
        if(confirm("ADMIN: Are you sure you want to delete this content? This cannot be undone.")) {
            window.location.href = "?delete_id=" + id;
        }
    }
    </script>
</body>
</html>