<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: view_participants_edit_delete.php'); exit;
}

$id              = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$power_output    = $_POST['power_output']    ?? '';
$distance        = $_POST['distance_travelled'] ?? '';

// Validate
$errors = [];
if (!$id || $id <= 0)                                                    { $errors[] = 'Invalid participant ID.'; }
if ($power_output === '' || !is_numeric($power_output) || $power_output < 0) { $errors[] = 'Power output must be a number ≥ 0.'; }
if ($distance     === '' || !is_numeric($distance)     || $distance < 0)     { $errors[] = 'Distance must be a number ≥ 0.'; }

if (!empty($errors)) {
    $_SESSION['flash'] = 'Error: ' . implode(' ', $errors);
    header('Location: edit_participant_form.php?id=' . (int)$id);
    exit;
}

try {
    require 'dbconnect.php';
    $pdo = new PDO(
        "mysql:host={$servername};dbname={$database};charset=utf8mb4",
        $username, $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verify participant exists
    $chk = $pdo->prepare("SELECT id FROM participant WHERE id = :id LIMIT 1");
    $chk->execute([':id' => $id]);
    if (!$chk->fetch()) {
        $_SESSION['flash'] = 'Error: Participant not found.';
        header('Location: view_participants_edit_delete.php'); exit;
    }

    $stmt = $pdo->prepare("UPDATE participant SET power_output = :po, distance = :d WHERE id = :id");
    $stmt->execute([':po' => (float)$power_output, ':d' => (float)$distance, ':id' => $id]);

    $_SESSION['flash'] = 'Participant updated successfully.';
    header('Location: view_participants_edit_delete.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['flash'] = 'Error: A database error occurred. Please try again.';
    header('Location: edit_participant_form.php?id=' . (int)$id);
    exit;
}
