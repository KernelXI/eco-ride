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
    $user_id = $_SESSION['user_id']; // Get the user ID from the session

    try {
        $stmt = $pdo->prepare("INSERT INTO ride (start_address, start_city, destination_address, destination_city, start_date, start_time, destination_date, destination_time, price, car_id, driver_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'prévu')");
        $stmt->execute([$start_address, $start_city, $destination_address, $destination_city, $start_date, $start_time, $destination_date, $destination_time, $price, $car_id, $user_id]);

        echo "Covoiturage créé avec succès!";
        header("Location: dashboard.php"); // Redirigez vers le tableau de bord ou la page appropriée
        exit();
    } catch (PDOException $e) {
        echo "Erreur: " . $e->getMessage();
    }
}
?>
