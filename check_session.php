<?php
session_start();
include('config.php');

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['timeout' => true]);
    exit();
}

if (checkSessionTimeout()) {
    echo json_encode(['timeout' => true]);
} else {
    echo json_encode(['timeout' => false]);
} 