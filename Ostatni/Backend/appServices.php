<?php
require_once __DIR__ . '/../Database/db.php';

if (!function_exists('fetchUserContact')) {
    function fetchUserContact(int $userId): ?array
    {
        global $conn;
        $stmt = $conn->prepare('SELECT id, username, email FROM users WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        $user = null;
        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
        } else {
            $stmt->bind_result($id, $username, $email);
            if ($stmt->fetch()) {
                $user = [
                    'id' => $id,
                    'username' => $username,
                    'email' => $email
                ];
            }
        }

        $stmt->close();
        return $user ?: null;
    }
}

if (!function_exists('fetchPostSummary')) {
    function fetchPostSummary(int $postId): ?array
    {
        global $conn;
        $stmt = $conn->prepare('SELECT id, title, user_id FROM posts WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $postId);
        $stmt->execute();

        $post = null;
        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            $post = $result ? $result->fetch_assoc() : null;
        } else {
            $stmt->bind_result($id, $title, $user_id);
            if ($stmt->fetch()) {
                $post = [
                    'id' => $id,
                    'title' => $title,
                    'user_id' => $user_id
                ];
            }
        }

        $stmt->close();
        return $post ?: null;
    }
}

if (!function_exists('getWorkflowStateName')) {
    function getWorkflowStateName(int $stateId): ?string
    {
        global $conn;
        $stmt = $conn->prepare('SELECT state FROM workflow WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $stateId);
        $stmt->execute();

        $stateName = null;
        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stateName = $row['state'] ?? null;
        } else {
            $stmt->bind_result($state);
            if ($stmt->fetch()) {
                $stateName = $state;
            }
        }

        $stmt->close();
        return $stateName;
    }
}

if (!function_exists('fetchAssignmentOwnerId')) {
    function fetchAssignmentOwnerId(int $postId, int $reviewerId): ?int
    {
        global $conn;
        $stmt = $conn->prepare('SELECT assigned_by FROM post_assignments WHERE post_id = ? AND reviewer_id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('ii', $postId, $reviewerId);
        $stmt->execute();
        $stmt->bind_result($assignedBy);
        $owner = $stmt->fetch() ? (int)$assignedBy : null;
        $stmt->close();
        return $owner ?: null;
    }
}

if (!function_exists('buildFrontendUrl')) {
    function buildFrontendUrl(string $relativePath = ''): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(dirname(dirname($scriptName)), '/\\');
        if ($baseDir === '.' || $baseDir === DIRECTORY_SEPARATOR) {
            $baseDir = '';
        }

        $base = rtrim($protocol . '://' . $host . $baseDir, '/');
        return $base . '/' . ltrim($relativePath, '/');
    }
}

