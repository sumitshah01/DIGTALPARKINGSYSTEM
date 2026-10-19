<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
if (isLoggedIn()) auditLog((new Database())->connect(), $_SESSION['user_id'], 'logout');
session_destroy();
header('Location: ' . SITE_URL . '/login.php');