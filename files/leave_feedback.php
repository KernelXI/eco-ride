<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$rideId = isset($_GET['ride_id']) ? intval($_GET['ride_id']) : null;

$message = '';
$note = '';
$status = 'pending';

if ($rideId) {
    try {
        $sql = "SELECT * FROM feedback WHERE ride_id = :ride_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':ride_id', $rideId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $feedback = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($feedback) {
            $message = $feedback['message'];
            $note = $feedback['note'];
            $status = $feedback['status'];
        }
    } catch (PDOException $e) {
        die("Erreur lors de la récupération de l'avis : " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $status === 'pending') {
    $message = $_POST['message'];
    $note = $_POST['note'];

    try {
        if ($feedback) {
            $sql = "UPDATE feedback SET message = :message, note = :note WHERE ride_id = :ride_id AND user_id = :user_id";
        } else {
            $sql = "INSERT INTO feedback (user_id, ride_id, message, note, status) VALUES (:user_id, :ride_id, :message, :note, :status)";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':ride_id', $rideId, PDO::PARAM_INT);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->bindParam(':note', $note, PDO::PARAM_INT);
        if (!$feedback) {
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        }
        $stmt->execute();

        header("Location: ride_history.php");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de l'enregistrement de l'avis : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Laisser un Avis</title>
    <link rel="stylesheet" href="style.css"> <!-- Lien vers le fichier CSS global -->
</head>
<body>
<header>
    <nav>
        <a href="index.php">EcoRide</a>
        <a href="login.php">Connexion</a>
        <a href="register.php">Inscription</a>
        <a href="contact.php">Contact</a>
        <a href="search_rides.php">Recherche de Covoiturages</a>
    </nav>
</header>
<h1>Laisser un Avis pour le Covoiturage</h1>
<form action="" method="post">
    <label for="message">Message :</label><br>
    <textarea name="message" id="message" rows="4" required><?php echo htmlspecialchars($message); ?></textarea><br>

    <label for="note">Note (1 à 5) :</label><br>
    <input type="number" name="note" id="note" min="1" max="5" value="<?php echo htmlspecialchars($note); ?>" required><br><br>

    <?php if ($status === 'validated'): ?>
        <p>Statut : Validé. Vous ne pouvez pas modifier cet avis.</p>
    <?php else: ?>
        <input type="submit" value="Soumettre l'Avis">
    <?php endif; ?>
</form>

<p><a href="ride_history.php">Retour à l'historique des covoiturages</a></p>
</body>
</html>
