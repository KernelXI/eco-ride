<?php
session_start();
include 'db.php'; // Inclure le fichier de connexion à la base de données

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'])) {
    $feedback_id = intval($_POST['feedback_id']);
    $action = $_POST['action'];

    try {
        if ($action === 'validate') {
            validateFeedback($feedback_id);
        } elseif ($action === 'reject') {
            rejectFeedback($feedback_id);
        } elseif ($action === 'validate_refund') {
            validateAndRefundFeedback($feedback_id);
        }

        // Rediriger vers la page de gestion des avis
        header("Location: staff.php");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors du traitement de l'avis : " . $e->getMessage());
    }
} else {
    header("Location: staff.php");
    exit();
}

// Fonctions pour traiter les feedbacks
function validateFeedback($feedback_id) {
    global $pdo;

    // Valider l'avis
    $sql = "UPDATE feedback SET status = 'validated' WHERE feedback_id = :feedback_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':feedback_id', $feedback_id, PDO::PARAM_INT);
    $stmt->execute();

    updateDriverRating($feedback_id);
}

function rejectFeedback($feedback_id) {
    global $pdo;

    // Refuser l'avis
    $sql = "DELETE FROM feedback WHERE feedback_id = :feedback_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':feedback_id', $feedback_id, PDO::PARAM_INT);
    $stmt->execute();
}

function validateAndRefundFeedback($feedback_id) {
    global $pdo;

    // Valider l'avis
    $sql = "UPDATE feedback SET status = 'validated' WHERE feedback_id = :feedback_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':feedback_id', $feedback_id, PDO::PARAM_INT);
    $stmt->execute();

    // Rembourser l'utilisateur
    $stmt = $pdo->prepare("SELECT user_id FROM feedback WHERE feedback_id = :feedback_id");
    $stmt->bindParam(':feedback_id', $feedback_id, PDO::PARAM_INT);
    $stmt->execute();
    $feedback = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($feedback) {
        $user_id = $feedback['user_id'];
        $stmt = $pdo->prepare("UPDATE user SET credit = credit + 2 WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

function updateDriverRating($feedback_id) {
    global $pdo;

    // Récupérer le user_id et le ride_id à partir de l'avis
    $stmt = $pdo->prepare("SELECT user_id, ride_id FROM feedback WHERE feedback_id = :feedback_id");
    $stmt->bindParam(':feedback_id', $feedback_id, PDO::PARAM_INT);
    $stmt->execute();
    $feedback = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($feedback) {
        $ride_id = $feedback['ride_id'];

        // Récupérer le driver_id à partir de l'ride
        $stmt = $pdo->prepare("SELECT driver_id FROM ride WHERE ride_id = :ride_id");
        $stmt->bindParam(':ride_i
