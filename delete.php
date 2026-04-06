<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: view_participants_edit_delete.php'); exit;
}

$id      = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$confirm = $_POST['confirm'] ?? '';

if (!$id || $id <= 0 || $confirm !== 'yes') {
    header('Location: view_participants_edit_delete.php'); exit;
}

try {
    require 'dbconnect.php';
    $pdo = new PDO(
        "mysql:host={$servername};dbname={$database};charset=utf8mb4",
        $username, $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verify exists first
    $chk = $pdo->prepare("SELECT id FROM participant WHERE id = :id LIMIT 1");
    $chk->execute([':id' => $id]);
    if (!$chk->fetch()) {
        $_SESSION['flash'] = 'Error: Participant not found.';
        header('Location: view_participants_edit_delete.php'); exit;
    }

    $stmt = $pdo->prepare("DELETE FROM participant WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $_SESSION['flash'] = 'Participant deleted successfully.';
    header('Location: view_participants_edit_delete.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['flash'] = 'Error: A database error occurred. Please try again.';
    header('Location: view_participants_edit_delete.php');
    exit;
}
