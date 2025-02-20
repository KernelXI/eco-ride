<?php
session_start();
include 'db.php'; // Inclure le fichier de connexion à la base de données

// Vérifier si l'utilisateur est connecté et a le rôle "employee"
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'])) {
    $feedback_id = intval($_POST['feedback_id']);
    $action = $_POST['action'];

    try {
        if ($action === 'validate') {
            // Valider l'avis
            $sql = "UPDATE feedback SET status = 'validated' WHERE feedback_id = :feedback_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':feedback_id', $feedback_id, PDO::PARAM_INT);
            $stmt->execute();

            // Récupérer le user_id et le ride_id à partir de l'avis
            $stmt = $pdo->prepare("SELECT user_id, ride_id FROM feedback WHERE feedback_id = :feedback_id");
            $stmt->bindParam(':feedback_id', $feedback_id, PDO::PARAM_INT);
            $stmt->execute();
            $feedback = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($feedback) {
                $ride_id = $feedback['ride_id'];

                // Récupérer le driver_id à partir de l'ride
                $stmt = $pdo->prepare("SELECT driver_id FROM ride WHERE ride_id = :ride_id");
                $stmt->bindParam(':ride_id', $ride_id, PDO::PARAM_INT);
                $stmt->execute();
                $ride = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($ride) {
                    $driver_id = $ride['driver_id'];

                    // Calculer la nouvelle note globale pour le conducteur
                    $stmt = $pdo->prepare("
                        SELECT AVG(note) AS average_rating
                        FROM feedback
                        WHERE ride_id IN (SELECT ride_id FROM ride WHERE driver_id = :driver_id) 
                        AND status = 'validated'
                    ");
                    $stmt->bindParam(':driver_id', $driver_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $average = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Mettre à jour la note globale du conducteur
                    if ($average) {
                        $stmt = $pdo->prepare("UPDATE user SET note = :new_rating WHERE user_id = :driver_id");
                        $new_rating = $average['average_rating'];
                        $stmt->bindParam(':new_rating', $new_rating, PDO::PARAM_STR);
                        $stmt->bindParam(':driver_id', $driver_id, PDO::PARAM_INT);
                        $stmt->execute();
                    }
                }
            }
        } elseif ($action === 'reject') {
            // Refuser l'avis
            $sql = "DELETE FROM feedback WHERE feedback_id = :feedback_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':feedback_id', $feedback_id, PDO::PARAM_INT);
            $stmt->execute();
        } elseif ($action === 'validate_refund') {
            // Valider l'avis et rembourser
            $sql = "UPDATE feedback SET status = 'validated' WHERE feedback_id = :feedback_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':feedback_id', $feedback_id, PDO::PARAM_INT);
            $stmt->execute();

            // Logique de remboursement (exemple)
            // Récupérer user_id à partir de l'avis
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

        // Rediriger vers la page de gestion des avis
        header("Location: staff.php");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors du traitement de l'avis : " . $e->getMessage());
    }
} else {
    // Rediriger vers la page de gestion des avis si l'accès n'est pas valide
    header("Location: staff.php");
    exit();
}
