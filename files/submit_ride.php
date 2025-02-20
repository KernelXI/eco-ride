<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_address = $_POST['start_address'];
    $start_city = $_POST['start_city'];
    $destination_address = $_POST['destination_address'];
    $destination_city = $_POST['destination_city'];
    $start_date = $_POST['start_date'];
    $start_time = $_POST['start_time'];
    $destination_date = $_POST['destination_date'];
    $destination_time = $_POST['destination_time'];
    $price = $_POST['price'];
    $car_id = $_POST['car_id'];
    $user_id = $_SESSION['user_id']; // Récupérer l'ID de l'utilisateur à partir de la session

    try {
        $stmt = $pdo->prepare("INSERT INTO ride (start_address, start_city, destination_address, destination_city, start_date, start_time, destination_date, destination_time, price, car_id, driver_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'prévu')");
        $stmt->execute([$start_address, $start_city, $destination_address, $destination_city, $start_date, $start_time, $destination_date, $destination_time, $price, $car_id, $user_id]);

        header("Location: dashboard.php?message=Covoiturage créé avec succès!"); // Redirige vers le tableau de bord avec un message
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
    <title>Créer un Covoiturage</title>
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
