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
    // Vérifier que le covoiturage existe et obtenir son prix
    $rideStmt = $pdo->prepare("SELECT price FROM ride WHERE ride_id = ?");
    $rideStmt->execute([$rideId]);
    $ride = $rideStmt->fetch(PDO::FETCH_ASSOC);

    if (!$ride) {
        echo "Covoiturage introuvable.";
        exit();
    }

    // Récupérer le prix du covoiturage
    $ridePrice = $ride['price'];

    // Vérifier le crédit de l'utilisateur
    $userStmt = $pdo->prepare("SELECT credit FROM user WHERE user_id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user['credit'] < $ridePrice) {
        echo "Crédit insuffisant pour rejoindre ce covoiturage.";
        exit();
    }

    // Ajouter l'utilisateur à user_ride
    $stmt = $pdo->prepare("INSERT INTO user_ride (ride_id, user_id) VALUES (?, ?)");
    $stmt->execute([$rideId, $userId]);

    // Réduire le crédit de l'utilisateur en fonction du prix du covoiturage
    $creditStmt = $pdo->prepare("UPDATE user SET credit = credit - ? WHERE user_id = ?");
    $creditStmt->execute([$ridePrice, $userId]);

    header("Location: dashboard.php?message=Covoiturage rejoint avec succès.");
    exit();
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
