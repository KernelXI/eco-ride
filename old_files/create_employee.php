<?php
session_start();
include 'db.php'; // Inclure le fichier de connexion à la base de données

// Vérifier si l'utilisateur est connecté et a le rôle "admin"
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Initialiser une variable pour les messages d'erreur ou de succès
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Vérifier que le pseudo ou l'email n'est pas déjà utilisé
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE nickname = :nickname OR email = :email");
        $stmt->bindParam(':nickname', $nickname);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $message = "Le pseudo ou l'email est déjà utilisé.";
        } else {
            // Hacher le mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insérer l'utilisateur dans la base de données
            $stmt = $pdo->prepare("INSERT INTO user (nickname, email, password, role) VALUES (:nickname, :email, :password, 'employee')");
            $stmt->bindParam(':nickname', $nickname);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();

            $message = "Compte créé avec succès !";
        }
    } catch (PDOException $e) {
        die("Erreur lors de la création du compte : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Compte Employé</title>
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
