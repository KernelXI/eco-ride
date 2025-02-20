<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT role FROM user WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !in_array($user['role'], ['chauffeur', 'passager_chauffeur'])) {
        echo "Vous n'avez pas l'autorisation de créer un covoiturage.";
        exit();
    }
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Covoiturage</title>
    <link rel="stylesheet" href="styles.css"> <!-- Lien vers le fichier CSS -->
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
<main>
    <h1>Créer un Covoiturage</h1>
    <form action="submit_ride.php" method="POST">
        <label for="start_address">Adresse de départ:</label>
        <input type="text" id="start_address" name="start_address" required><br><br>

        <label for="start_city">Ville de départ:</label>
        <input type="text" id="start_city" name="start_city" required><br><br>

        <label for="destination_address">Adresse d'arrivée:</label>
        <input type="text" id="destination_address" name="destination_address" required><br><br>

        <label for="destination_city">Ville d'arrivée:</label>
        <input type="text" id="destination_city" name="destination_city" required><br><br>

        <label for="start_date">Date de départ:</label>
        <input type="date" id="start_date" name="start_date" required><br><br>

        <label for="start_time">Heure de départ:</label>
        <input type="time" id="start_time" name="start_time" required><br><br>

        <label for="destination_date">Date d'arrivée:</label>
        <input type="date" id="destination_date" name="destination_date" required><br><br>

        <label for="destination_time">Heure d'arrivée:</label>
        <input type="time" id="destination_time" name="destination_time" required><br><br>

        <label for="price">Prix:</label>
        <input type="number" id="price" name="price" required step="1"><br><br>

        <label for="car_id">Voiture:</label>
        <select id="car_id" name="car_id" required>
            <?php
            // Récupérer les voitures de l'utilisateur
            $carStmt = $pdo->prepare("SELECT car_id, branding, model FROM car WHERE user_id = ?");
            $carStmt->execute([$userId]);
            $cars = $carStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($cars as $car) {
                echo '<option value="' . htmlspecialchars($car['car_id']) . '">' . htmlspecialchars($car['branding'] . ' ' . $car['model']) . '</option>';
            }
            ?>
        </select><br><br>

        <input type="submit" value="Créer le Covoiturage">
    </form>
    <p><a href="dashboard.php">Retour au tableau de bord</a></p>
</main>
<footer>
    <p>&copy; 2025 EcoRide. Tous droits réservés.</p>
</footer>
</body>
</html>
