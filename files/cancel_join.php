<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$rideId = $_POST['ride_id'];

try {
    $checkStmt = $pdo->prepare("SELECT * FROM user_ride WHERE ride_id = ? AND user_id = ?");
    $checkStmt->execute([$rideId, $userId]);
    $joinedRide = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$joinedRide) {
        echo "Vous n'avez pas rejoint ce covoiturage.";
        exit();
    }

    $rideStmt = $pdo->prepare("SELECT price FROM ride WHERE ride_id = ?");
    $rideStmt->execute([$rideId]);
    $ride = $rideStmt->fetch(PDO::FETCH_ASSOC);

    if (!$ride) {
        echo "Covoiturage introuvable.";
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM user_ride WHERE ride_id = ? AND user_id = ?");
    $stmt->execute([$rideId, $userId]);

    $creditStmt = $pdo->prepare("UPDATE user SET credit = credit + ? WHERE user_id = ?");
    $creditStmt->execute([$ride['price'], $userId]);

    header("Location: dashboard.php?message=Participation annulée et crédit remboursé.");
    exit();
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
