<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['ride_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$rideId = $_POST['ride_id'];

try {
    $rideStmt = $pdo->prepare("SELECT price FROM ride WHERE ride_id = :rideId");
    $rideStmt->bindValue(':rideId', $rideId, PDO::PARAM_INT);
    $rideStmt->execute();
    $ride = $rideStmt->fetch(PDO::FETCH_ASSOC);

    if (!$ride) {
        echo "Covoiturage introuvable.";
        exit();
    }

    $stmt = $pdo->prepare("SELECT user_id FROM user_ride WHERE ride_id = :rideId");
    $stmt->bindValue(':rideId', $rideId, PDO::PARAM_INT);
    $stmt->execute();
    $participants = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($participants)) {
        $pdo->beginTransaction(); // Commencer une transaction

        foreach ($participants as $participantId) {
            $stmt = $pdo->prepare("UPDATE user SET credit = credit + :price WHERE user_id = :participantId");
            $stmt->bindValue(':price', $ride['price'], PDO::PARAM_STR);
            $stmt->bindValue(':participantId', $participantId, PDO::PARAM_INT);
            $stmt->execute();
        }

        $pdo->commit(); // Valider la transaction
    }

    $stmt = $pdo->prepare("DELETE FROM user_ride WHERE ride_id = :rideId");
    $stmt->bindValue(':rideId', $rideId, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $pdo->prepare("DELETE FROM ride WHERE ride_id = :rideId");
    $stmt->bindValue(':rideId', $rideId, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: dashboard.php?message=Covoiturage annulé avec succès et les participants ont été remboursés.');
    exit();
} catch (PDOException $e) {
    $pdo->rollBack(); // Annuler la transaction en cas d'erreur
    echo "Erreur: " . $e->getMessage();
}
?>
