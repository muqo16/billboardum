<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['admin_logged'])) {
    http_response_code(404);
    include __DIR__ . '/../404.php';
    exit;
}
