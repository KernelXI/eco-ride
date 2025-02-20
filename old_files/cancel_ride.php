<?php
session_start();
require 'db.php';

// Vérifie si l'utilisateur est connecté et si l'ID du covoiturage est passé en POST
if (isset($_SESSION['user_id']) && isset($_POST['ride_id'])) {
    $userId = $_SESSION['user_id'];
    $rideId = $_POST['ride_id'];

    try {
        // Récupérer le prix du covoiturage
        $stmt = $pdo->prepare("SELECT price FROM ride WHERE ride_id = :rideId");
        $stmt->bindValue(':rideId', $rideId, PDO::PARAM_INT);
        $stmt->execute();
        $ride = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ride) {
            echo "Covoiturage introuvable.";
            exit();
        }

        // Récupérer les utilisateurs qui ont rejoint le covoiturage
        $stmt = $pdo->prepare("SELECT user_id FROM user_ride WHERE ride_id = :rideId");
        $stmt->bindValue(':rideId', $rideId, PDO::PARAM_INT);
        $stmt->execute();
        $participants = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Rembourser le crédit aux participants
        if (!empty($participants)) {
            $pdo->beginTransaction(); // Commencer une transaction

            foreach ($participants as $participantId) {
                $stmt = $pdo->prepare("UPDATE user SET credit = credit + :price WHERE user_id = :participantId");
                $stmt->bindValue(':price', $ride['price'], PDO::PARAM_STR); // Utiliser le prix récupéré
                $stmt->bindValue(':participantId', $participantId, PDO::PARAM_INT);
                $stmt->execute();
            }

            $pdo->commit(); // Valider la transaction
        }

        // Supprimer les entrées de user_ride pour le covoiturage
        $stmt = $pdo->prepare("DELETE FROM user_ride WHERE ride_id = :rideId");
        $stmt->bindValue(':rideId', $rideId, PDO::PARAM_INT);
        $stmt->execute();

        // Supprimer le covoiturage
        $stmt = $pdo->prepare("DELETE FROM ride WHERE ride_id = :rideId");
        $stmt->bindValue(':rideId', $rideId, PDO::PARAM_INT);
        $stmt->execute();

        // Rediriger vers le tableau de bord avec un message de succès
        header('Location: dashboard.php?message=Covoiturage annulé avec succès et les participants ont été remboursés.');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack(); // Annuler la transaction en cas d'erreur
        echo "Erreur: " . $e->getMessage();
    }
} else {
    echo "Erreur: Vous devez être connecté pour annuler un covoiturage.";
}
