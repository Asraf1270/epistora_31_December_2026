<?php
require_once 'auth_check.php';
require_once '../db_engine.php';

$user_files = glob(DATA_PATH . "user_data/*.json");

if (isset($_POST['update_role'])) {
    $uid = $_POST['user_id'];
    $new_role = $_POST['role'];
    
    $vault = DBEngine::readJSON("user_data/$uid.json");
    $vault['role'] = $new_role;
    DBEngine::writeJSON("user_data/$uid.json", $vault);
    
    header("Location: manage_users.php?status=updated");
    exit;
}
?>