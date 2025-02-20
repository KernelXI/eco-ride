<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['car_id'])) {
    $carId = $_GET['car_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM car WHERE car_id = ? AND user_id = ?");
        $stmt->execute([$carId, $_SESSION['user_id']]);
    } catch (PDOException $e) {
        echo "Erreur: " . $e->getMessage();
    }
}

header("Location: dashboard.php");
exit();
?>
