<?php
/**
 * Epistora DB Engine
 * A Flat-File Database Layer for JSON-based persistence.
 */

require_once 'config.php';

// Verify that the environment is configured correctly
if (!defined('DATA_PATH')) {
    die("Core Engine Error: Configuration constants not loaded. Ensure config.php is included.");
}

class DBEngine {

    /**
     * Internal: Ensures the storage infrastructure exists.
     * Prevents system crashes by auto-generating missing directories.
     */
    private static function ensureStorage() {
        $folders = [DATA_PATH, USER_DATA_PATH, POST_CONTENT_PATH];
        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }
        }
    }

    /**
     * Reads and decodes a JSON file.
     * @param string $filename Path relative to the /data/ folder.
     */
    public static function readJSON($filename) {
        $path = DATA_PATH . $filename;
        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        $decoded = json_decode($content, true);

        // Verify JSON integrity
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }

    /**
     * Writes data to a JSON file with Atomic Locking.
     * Prevents corruption using an exclusive write lock.
     */
    public static function writeJSON($filename, $data) {
        self::ensureStorage();
        $path = DATA_PATH . $filename;

        // Encode with UTF-8 support and readable formatting
        $json_string = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json_string === false) {
            return false; // Stop write if data is not serializable
        }

        /**
         * LOCK_EX (Exclusive Lock) prevents two users from writing to the 
         * same file at the exact same microsecond, which is critical for
         * shared files like 'posts.json' and 'users.json'.
         */
        return file_put_contents($path, $json_string, LOCK_EX);
    }

    /**
     * Initializes a private User Vault.
     * @param string $user_id The unique generated ID.
     */
    public static function initVault($user_id) {
        $filename = "user_data/" . $user_id . ".json";

        // Prevent overwriting existing user data
        if (file_exists(DATA_PATH . $filename)) {
            return false;
        }

        $vault_template = [
            "user_id"    => $user_id,
            "role"       => ROLE_USER,
            "created_at" => date('Y-m-d H:i:s'),
            "profile"    => [
                "name"  => "",
                "email" => ""
            ],
            "settings"   => [
                "bg_color"   => "#ffffff",
                "font_style" => "sans-serif",
                "font_size"  => "16px"
            ],
            "history"       => [],
            "following"     => [],
            "notifications" => []
        ];

        return self::writeJSON($filename, $vault_template);
    }

    /**
     * Atomic Update: Modifies a specific key in a file without reloading it manually elsewhere.
     */
    public static function updateKey($filename, $key, $value) {
        $data = self::readJSON($filename);
        if ($data !== null) {
            $data[$key] = $value;
            return self::writeJSON($filename, $data);
        }
        return false;
    }

    /**
 * Pushes a notification to a specific user's vault.
 */
public static function pushNotification($target_user_id, $type, $from_name, $post_id) {
    $filename = "user_data/" . $target_user_id . ".json";
    $vault = self::readJSON($filename);

    if ($vault) {
        $notification = [
            "id"         => uniqid('ntf_'),
            "type"       => $type, // 'reaction', 'comment', 'follow'
            "from_name"  => $from_name,
            "post_id"    => $post_id,
            "is_read"    => false,
            "timestamp"  => time(),
            "date_human" => date('M d, H:i')
        ];

        // Keep only the last 50 notifications to prevent vault bloat
        array_unshift($vault['notifications'], $notification);
        $vault['notifications'] = array_slice($vault['notifications'], 0, 50);

        return self::writeJSON($filename, $vault);
    }
    return false;
}

public static function logAction($admin_id, $admin_name, $action, $details) {
    $log_file = "system_logs.json";
    $logs = self::readJSON($log_file) ?? [];

    $new_log = [
        "id"         => uniqid('log_'),
        "timestamp"  => time(),
        "date"       => date('Y-m-d H:i:s'),
        "admin_id"   => $admin_id,
        "admin_name" => $admin_name,
        "action"     => $action, // e.g., 'POST_DELETE', 'WRITER_PROMOTION'
        "details"    => $details,
        "ip"         => $_SERVER['REMOTE_ADDR']
    ];

    // Add to the beginning of the array (newest first)
    array_unshift($logs, $new_log);

    // Retention Policy: Keep only the last 1000 logs to prevent file bloat
    if (count($logs) > 1000) {
        $logs = array_slice($logs, 0, 1000);
    }

    return self::writeJSON($log_file, $logs);
}
}