<?php
session_start();
require 'db.php';

$userId = $_SESSION['user_id'] ?? null;

$filters = [
    'eco_type' => $_POST['eco_type'] ?? null,
    'max_price' => isset($_POST['max_price']) ? intval($_POST['max_price']) : null,
    'max_duration' => $_POST['max_duration'] ?? null,
    'min_rating' => $_POST['min_rating'] ?? null,
    'start_city' => $_POST['start_city'] ?? null,
    'destination_city' => $_POST['destination_city'] ?? null,
    'departure_date' => $_POST['departure_date'] ?? null,
    'departure_time' => $_POST['departure_time'] ?? null,
    'arrival_date' => $_POST['arrival_date'] ?? null,
    'arrival_time' => $_POST['arrival_time'] ?? null,
];

$ongoingRides = [];

try {
    // Préparer la requête de base
    $query = "
        SELECT r.ride_id, r.start_address, r.start_city, r.destination_address, r.destination_city,
               r.start_date, r.start_time, r.destination_date, r.destination_time,
               r.status, u.nickname AS driver_nickname, u.photo, u.note AS driver_rating, c.branding, c.model, c.plate,
               r.driver_id, r.price,
               c.seats - (SELECT COUNT(*) FROM user_ride WHERE ride_id = r.ride_id) AS available_seats, 
               (SELECT COUNT(*) FROM user_ride WHERE ride_id = r.ride_id) AS total_passengers,
               (SELECT COUNT(*) FROM user_ride WHERE ride_id = r.ride_id AND user_id = :userId) AS has_joined
        FROM ride r
        JOIN user u ON r.driver_id = u.user_id
        JOIN car c ON r.car_id = c.car_id
        WHERE r.status IN ('prévu')
    ";

    // Ajouter des conditions en fonction des filtres
    $conditions = [];
    if ($filters['eco_type'] && $filters['eco_type'] !== 'Tous') {
        $conditions[] = "c.eco_type = :eco_type";
    }
    if ($filters['max_price']) {
        $conditions[] = "r.price <= :max_price";
    }
    if ($filters['max_duration'] !== null && $filters['max_duration'] !== '') {
        $conditions[] = "TIMESTAMPDIFF(MINUTE, CONCAT(r.start_date, ' ', r.start_time), CONCAT(r.destination_date, ' ', r.destination_time)) <= :max_duration";
    }
    if ($filters['min_rating']) {
        $conditions[] = "u.user_id IN (SELECT user_id FROM feedback WHERE note >= :min_rating)";
    }
    if ($filters['start_city']) {
        $conditions[] = "r.start_city LIKE :start_city";
    }
    if ($filters['destination_city']) {
        $conditions[] = "r.destination_city LIKE :destination_city";
    }
    if ($filters['departure_date']) {
        $conditions[] = "r.start_date = :departure_date";
    }
    if ($filters['departure_time']) {
        $conditions[] = "r.start_time >= :departure_time";
    }
    if ($filters['arrival_date']) {
        $conditions[] = "r.destination_date = :arrival_date";
    }
    if ($filters['arrival_time']) {
        $conditions[] = "r.destination_time <= :arrival_time";
    }

    // Ajouter les conditions à la requête
    if ($conditions) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    // Préparer la requête
    $stmt = $pdo->prepare($query);
    
    // Lier les valeurs
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    if ($filters['eco_type'] && $filters['eco_type'] !== 'Tous') {
        $stmt->bindValue(':eco_type', $filters['eco_type'], PDO::PARAM_STR);
    }
    if ($filters['max_price']) {
        $stmt->bindValue(':max_price', $filters['max_price'], PDO::PARAM_STR);
    }
    if ($filters['max_duration'] !== null && $filters['max_duration'] !== '') {
        $stmt->bindValue(':max_duration', $filters['max_duration'], PDO::PARAM_INT);
    }
    if ($filters['min_rating']) {
        $stmt->bindValue(':min_rating', $filters['min_rating'], PDO::PARAM_INT);
    }
    if ($filters['start_city']) {
        $stmt->bindValue(':start_city', '%' . $filters['start_city'] . '%', PDO::PARAM_STR);
    }
    if ($filters['destination_city']) {
        $stmt->bindValue(':destination_city', '%' . $filters['destination_city'] . '%', PDO::PARAM_STR);
    }
    if ($filters['departure_date']) {
        $stmt->bindValue(':departure_date', $filters['departure_date'], PDO::PARAM_STR);
    }
    if ($filters['departure_time']) {
        $stmt->bindValue(':departure_time', $filters['departure_time'], PDO::PARAM_STR);
    }
    if ($filters['arrival_date']) {
        $stmt->bindValue(':arrival_date', $filters['arrival_date'], PDO::PARAM_STR);
    }
    if ($filters['arrival_time']) {
        $stmt->bindValue(':arrival_time', $filters['arrival_time'], PDO::PARAM_STR);
    }

    $stmt->execute();
    $ongoingRides = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de Covoiturages</title>
    <link rel="stylesheet" href="styles.css">
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
<h1>Covoiturages en Cours</h1>

<h2>Filtres de Recherche</h2>
<form method="post">
    <label for="eco_type">Type de voiture :</label>
    <select id="eco_type" name="eco_type">
        <option value="">--Sélectionner--</option>
        <option value="Tous" <?php echo ($filters['eco_type'] === 'Tous') ? 'selected' : ''; ?>>Tous</option>
        <option value="Électrique" <?php echo ($filters['eco_type'] === 'Électrique') ? 'selected' : ''; ?>>Électrique</option>
    </select><br><br>

    <label for="max_price">Prix maximum :</label>
    <input type="number" id="max_price" name="max_price" step="1" value="<?php echo htmlspecialchars($filters['max_price']); ?>"><br><br>

    <label for="max_duration">Durée maximum (en minutes) :</label>
    <input type="number" id="max_duration" name="max_duration" min="0" value="<?php echo htmlspecialchars($filters['max_duration']); ?>"><br><br>

    <label for="min_rating">Note minimale des chauffeurs :</label>
    <input type="number" id="min_rating" name="min_rating" min="1" max="5" value="<?php echo htmlspecialchars($filters['min_rating']); ?>"><br><br>

    <label for="start_city">Ville de départ :</label>
    <input type="text" id="start_city" name="start_city" value="<?php echo htmlspecialchars($filters['start_city']); ?>"><br><br>

    <label for="destination_city">Ville d'arrivée :</label>
    <input type="text" id="destination_city" name="destination_city" value="<?php echo htmlspecialchars($filters['destination_city']); ?>"><br><br>

    <label for="departure_date">Date de départ :</label>
    <input type="date" id="departure_date" name="departure_date" value="<?php echo htmlspecialchars($filters['departure_date']); ?>"><br><br>

    <label for="departure_time">Heure de départ :</label>
    <input type="time" id="departure_time" name="departure_time" value="<?php echo htmlspecialchars($filters['departure_time']); ?>"><br><br>

    <label for="arrival_date">Date d'arrivée :</label>
    <input type="date" id="arrival_date" name="arrival_date" value="<?php echo htmlspecialchars($filters['arrival_date']); ?>"><br><br>

    <label for="arrival_time">Heure d'arrivée :</label>
    <input type="time" id="arrival_time" name="arrival_time" value="<?php echo htmlspecialchars($filters['arrival_time']); ?>"><br><br>

    <input type="submit" value="Appliquer les filtres">
</form>

<?php if (count($ongoingRides) > 0): ?>
    <ul>
        <?php foreach ($ongoingRides as $ride): ?>
            <li>
                <strong><?php echo htmlspecialchars($ride['start_city'] . ' ➔ ' . $ride['destination_city']); ?></strong>
                <p><strong>Chauffeur:</strong> <?php echo htmlspecialchars($ride['driver_nickname']); ?></p>
                <p><strong>Note du Chauffeur:</strong> <?php echo htmlspecialchars($ride['driver_rating']); ?>/5</p>
                <p><strong>Photo du Chauffeur:</strong></p>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($ride['photo']); ?>" alt="Photo de <?php echo htmlspecialchars($ride['driver_nickname']); ?>" style="width: 100px; height: auto;">
                <p><strong>Places restantes:</strong> <?php echo htmlspecialchars($ride['available_seats']); ?></p>
                <p><strong>Prix:</strong> <?php echo htmlspecialchars($ride['price']); ?> €</p>
                <p><strong>Date de départ:</strong> <?php echo htmlspecialchars($ride['start_date']); ?> à <?php echo htmlspecialchars($ride['start_time']); ?></p>
                <p><a href="ride_details.php?ride_id=<?php echo $ride['ride_id']; ?>">Voir les détails</a></p>

                <?php if ($userId): ?>
                    <?php if ($ride['driver_id'] == $userId): ?>
                        <form action="cancel_ride.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce covoiturage?');">
                            <input type="hidden" name="ride_id" value="<?php echo $ride['ride_id']; ?>">
                            <button type="submit">Annuler le covoiturage</button>
                        </form>
                    <?php elseif ($ride['has_joined'] > 0): ?>
                        <form action="cancel_join.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler votre participation?');">
                            <input type="hidden" name="ride_id" value="<?php echo $ride['ride_id']; ?>">
                            <button type="submit">Annuler ma participation</button>
                        </form>
                    <?php else: ?>
                        <form action="join_ride.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir rejoindre ce covoiturage?');">
                            <input type="hidden" name="ride_id" value="<?php echo $ride['ride_id']; ?>">
                            <button type="submit">Rejoindre</button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <p><a href="login.php">Connectez-vous pour rejoindre ce covoiturage</a></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Aucun covoiturage en cours.</p>
<?php endif; ?>

<a href="dashboard.php">Retour au tableau de bord</a>
</body>
</html>
