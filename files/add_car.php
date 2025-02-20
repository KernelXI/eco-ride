<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branding = $_POST['branding'];
    $model = $_POST['model'];
    $color = $_POST['color'];
    $seats = $_POST['seats'];
    $plate = $_POST['plate'];
    $dateFirstPlate = $_POST['date_first_plate'];
    $ecoType = $_POST['eco_type'];

    try {
        $stmt = $pdo->prepare("INSERT INTO car (user_id, branding, model, color, seats, plate, date_first_plate, eco_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $branding, $model, $color, $seats, $plate, $dateFirstPlate, $ecoType]);

        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        echo "Erreur: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Voiture</title>
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
<h1>Ajouter une Voiture</h1>
<form action="add_car.php" method="POST">
    <label for="branding">Marque:</label>
    <input type="text" id="branding" name="branding" required>

    <label for="model">Modèle:</label>
    <input type="text" id="model" name="model" required>

    <label for="color">Couleur:</label>
    <input type="text" id="color" name="color" required>

    <label for="seats">Nombre de sièges:</label>
    <input type="number" id="seats" name="seats" required>

    <label for="plate">Plaque:</label>
    <input type="text" id="plate" name="plate" required>

    <label for="date_first_plate">Date de première immatriculation:</label>
    <input type="date" id="date_first_plate" name="date_first_plate" required>

    <label for="eco_type">Type écologique:</label>
    <select id="eco_type" name="eco_type" required>
        <option value="Electrique">Électrique</option>
        <option value="Autre">Autre</option>
    </select>

    <input type="submit" value="Ajouter">
</form>
<a href="dashboard.php">Retour au tableau de bord</a>
</body>
</html>
