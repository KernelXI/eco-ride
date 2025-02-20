<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

try {
    // Requête pour obtenir les avis en attente avec les informations nécessaires
    $sql = "
        SELECT f.feedback_id, f.message, f.note, f.status, f.ride_id, f.user_id AS feedback_user_id,
               u1.nickname AS feedback_nickname, u1.email AS feedback_email,
               r.start_address, r.start_city, r.destination_address, r.destination_city,
               u2.nickname AS driver_nickname, u2.email AS driver_email
        FROM feedback f
        JOIN user u1 ON f.user_id = u1.user_id
        JOIN ride r ON f.ride_id = r.ride_id
        JOIN user u2 ON r.driver_id = u2.user_id
        WHERE f.status = 'pending'
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des avis : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Avis - Staff</title>
    <link rel="stylesheet" href="styles.css"> <!-- Lien vers le fichier CSS global -->
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
<h1>Liste des Avis en Attente</h1>

<table>
    <tr>
        <th>Covoiturage</th>
        <th>Pseudo du Participant</th>
        <th>Email du Participant</th>
        <th>Pseudo du Chauffeur</th>
        <th>Email du Chauffeur</th>
        <th>Message</th>
        <th>Note</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($feedbacks as $feedback): ?>
        <tr>
            <td>
                <?php echo htmlspecialchars($feedback['start_address'] . ', ' . $feedback['start_city']) . ' → ' .
                             htmlspecialchars($feedback['destination_address'] . ', ' . $feedback['destination_city']); ?>
            </td>
            <td><?php echo htmlspecialchars($feedback['feedback_nickname']); ?></td>
            <td><?php echo htmlspecialchars($feedback['feedback_email']); ?></td>
            <td><?php echo htmlspecialchars($feedback['driver_nickname']); ?></td>
            <td><?php echo htmlspecialchars($feedback['driver_email']); ?></td>
            <td><?php echo htmlspecialchars($feedback['message']); ?></td>
            <td><?php echo htmlspecialchars($feedback['note']); ?></td>
            <td>
                <form action="process_feedback.php" method="post">
                    <input type="hidden" name="feedback_id" value="<?php echo $feedback['feedback_id']; ?>">
                    <button type="submit" name="action" value="validate">Valider</button>
                    <button type="submit" name="action" value="reject">Refuser</button>
                    <button type="submit" name="action" value="validate_refund">Valider et Rembourser</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<p><a href="logout.php">Deconnexion</a></p>
</body>
</html>
