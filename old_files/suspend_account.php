<?php
session_start();
include 'db.php'; // Inclure le fichier de connexion à la base de données

// Vérifier si l'utilisateur est connecté et a le rôle "admin"
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['identifier'])) {
    $identifier = $_POST['identifier'];
    $role = $_POST['role'];

    try {
        // Rechercher l'utilisateur par ID, pseudo ou email
        $stmt = $pdo->prepare("
            SELECT user_id 
            FROM user 
            WHERE user_id = :user_id OR nickname = :nickname OR email = :email
        ");
        
        // Préparation des valeurs
        $identifier_value = $identifier;

        // Exécution de la requête
        $stmt->bindParam(':user_id', $identifier_value, PDO::PARAM_STR);
        $stmt->bindParam(':nickname', $identifier_value, PDO::PARAM_STR);
        $stmt->bindParam(':email', $identifier_value, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $user_id = $user['user_id'];

            // Suspendre ou mettre à jour le rôle de l'utilisateur
            if ($role === 'suspended') {
                $sql = "UPDATE user SET role = 'suspended' WHERE user_id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $message = "Le compte a été suspendu.";
            } else {
                // Mettre à jour le rôle (optionnel, selon votre logique)
                $sql = "UPDATE user SET role = :role WHERE user_id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':role', $role, PDO::PARAM_STR);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $message = "Le rôle de l'utilisateur a été mis à jour.";
            }
        } else {
            $message = "Aucun utilisateur trouvé avec cet ID, pseudo ou email.";
        }
    } catch (PDOException $e) {
        die("Erreur lors de la suspension du compte : " . $e->getMessage());
    }
}
    ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suspendre un Compte</title>
</head>
<body>
<header>
        <nav>
        <a href="index.php">EcoRide</a>
            <a href="login.php">Connexion</a>
            <a href="register.html">Inscription</a>
            <a href="contact.php">Contact</a>
            <a href="search_rides.php">Recherche de Covoiturages</a>
        </nav>
    </header>
    <p><a href="admin.php">Retour à la page admin</a></p>
</body>
</html>