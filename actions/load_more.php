<?php
session_start();
require_once '../db_engine.php';

$posts_per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
$offset = $page * $posts_per_page;

$all_posts = DBEngine::readJSON("../posts.json") ?? [];

// Apply the same sorting as your index
$user_id = $_SESSION['user_id'] ?? null;
$vault = $user_id ? DBEngine::readJSON("../user_data/$user_id.json") : null;
$history = $vault['history'] ?? [];

foreach ($all_posts as &$post) {
    $score = 0;
    if (in_array($post['post_id'], $history)) $score -= 100;
    $score += min(floor(($post['views'] ?? 0) / 10), 30);
    $post['algo_score'] = $score;
}
usort($all_posts, fn($a, $b) => $b['algo_score'] <=> $a['algo_score']);

// Get the slice for this page
$sliced_posts = array_slice($all_posts, $offset, $posts_per_page);

header('Content-Type: application/json');
echo json_encode($sliced_posts);