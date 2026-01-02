<?php
session_start();
require_once '../../config.php';
require_once '../../db_engine.php';

if ($_SESSION['role'] !== ROLE_ADMIN) die("Unauthorized.");

// Approval Logic
if (isset($_GET['approve'])) {
    $pid = $_GET['approve'];
    $post_data = DBEngine::readJSON("post_content/$pid.json");

    if ($post_data) {
        // 1. Update Global Index
        $posts_index = DBEngine::readJSON("posts.json") ?? [];
        $posts_index[] = [
            "post_id" => $pid,
            "title"   => $post_data['title'],
            "preview" => substr(strip_tags($post_data['content']), 0, 150) . "...",
            "author"  => $post_data['author'],
            "date"    => date('Y-m-d')
        ];
        DBEngine::writeJSON("posts.json", $posts_index);

        // 2. Update Status in content file
        $post_data['status'] = 'published';
        DBEngine::writeJSON("post_content/$pid.json", $post_data);
        
        echo "<p>Post approved and indexed!</p>";
    }
}

// Display Pending List
$files = glob(POST_CONTENT_PATH . "*.json");
echo "<h2>Pending Review</h2>";

foreach ($files as $file) {
    $data = json_decode(file_get_contents($file), true);
    if ($data['status'] === 'pending') {
        echo "<div style='border:1px solid #ccc; padding:10px; margin:5px;'>";
        echo "<h3>{$data['title']}</h3>";
        echo "<p>By: {$data['author']}</p>";
        echo "<details><summary>Read Full Content</summary><p>{$data['content']}</p></details>";
        echo "<a href='?approve={$data['post_id']}'>Approve & Publish</a>";
        echo "</div>";
    }
}