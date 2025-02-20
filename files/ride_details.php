<?php
session_start();
require 'db.php';

$userId = $_SESSION['user_id'] ?? null;

if (!isset($_GET['ride_id'])) {
    die("Aucun covoiturage spécifié.");
}

$rideId = intval($_GET['ride_id']);

try {
    // Requête pour obtenir les détails du covoiturage
    $sql = "
        SELECT r.ride_id, r.start_address, r.start_city, r.destination_address, r.destination_city,
               r.start_date, r.start_time, r.destination_date, r.destination_time,
               u.nickname AS driver_nickname, u.user_id AS driver_id, u.photo, c.branding, c.model, c.plate, c.eco_type, u.preferences
        FROM ride r
        JOIN user u ON r.driver_id = u.user_id
        JOIN car c ON r.car_id = c.car_id
        WHERE r.ride_id = :ride_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ride_id', $rideId, PDO::PARAM_INT);
    $stmt->execute();
    $rideDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rideDetails) {
        die("Covoiturage introuvable.");
    }

    $driverId = $rideDetails['driver_id'];

    // Récupérer les avis sur le conducteur
    $sqlFeedback = "
        SELECT f.message, f.note
        FROM feedback f
        JOIN ride r ON f.ride_id = r.ride_id
        WHERE r.driver_id = :driver_id AND f.status = 'validated'
    ";

    $stmtFeedback = $pdo->prepare($sqlFeedback);
    $stmtFeedback->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
    $stmtFeedback->execute();
    $feedbacks = $stmtFeedback->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur lors de la récupération des détails : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Covoiturage</title>
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
<h1>Détails du Covoiturage</h1>

<h2><?php echo htmlspecialchars($rideDetails['start_city'] . ' ➔ ' . $rideDetails['destination_city']); ?></h2>
<p><strong>Chauffeur :</strong> <?php echo htmlspecialchars($rideDetails['driver_nickname']); ?></p>

<!-- Affichage de la photo du conducteur -->
<p><strong>Photo du Chauffeur :</strong></p>
<img src="data:image/jpeg;base64,<?php echo base64_encode($rideDetails['photo']); ?>" alt="Photo de <?php echo htmlspecialchars($rideDetails['driver_nickname']); ?>" style="width: 100px; height: auto;">

<p><strong>Date de départ :</strong> <?php echo htmlspecialchars($rideDetails['start_date'] . ' à ' . $rideDetails['start_time']); ?></p>
<p><strong>Date d'arrivée :</strong> <?php echo htmlspecialchars($rideDetails['destination_date'] . ' à ' . $rideDetails['destination_time']); ?></p>
<p><strong>Adresse de départ :</strong> <?php echo htmlspecialchars($rideDetails['start_address'] . ', ' . $rideDetails['start_city']); ?></p>
<p><strong>Adresse d'arrivée :</strong> <?php echo htmlspecialchars($rideDetails['destination_address'] . ', ' . $rideDetails['destination_city']); ?></p>
<p><strong>Véhicule :</strong> <?php echo htmlspecialchars($rideDetails['branding'] . ' ' . $rideDetails['model']); ?></p>
<p><strong>Numéro de plaque :</strong> <?php echo htmlspecialchars($rideDetails['plate']); ?></p>
<p><strong>Type d'énergie :</strong> <?php echo htmlspecialchars($rideDetails['eco_type']); ?></p>
<p><strong>Préférences du conducteur :</strong> <?php echo htmlspecialchars($rideDetails['preferences']); ?></p>

<h3>Avis sur le Chauffeur</h3>
<?php if ($feedbacks): ?>
    <ul>
        <?php foreach ($feedbacks as $feedback): ?>
            <li>
                <strong>Note :</strong> <?php echo htmlspecialchars($feedback['note']); ?>/5
                <p><?php echo htmlspecialchars($feedback['message']); ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Aucun avis disponible pour ce conducteur.</p>
<?php endif; ?>

<p><a href="search_rides.php">Retour à la recherche de covoiturages</a></p>
</body>
</html>
