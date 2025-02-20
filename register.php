<?php
session_start();
require 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = trim($_POST['nickname']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    try {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $emailExists = $stmt->fetchColumn();

        // Vérifier si le pseudo existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE nickname = ?");
        $stmt->execute([$nickname]);
        $nicknameExists = $stmt->fetchColumn();

        if ($emailExists > 0) {
            echo "Cet email est déjà utilisé. Veuillez en choisir un autre.";
        } elseif ($nicknameExists > 0) {
            echo "Ce pseudo est déjà utilisé. Veuillez en choisir un autre.";
        } else {
            // Téléchargement de la photo
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
                $photo = file_get_contents($_FILES['photo']['tmp_name']);

                // Insertion dans la base de données
                $stmt = $pdo->prepare("INSERT INTO user (nickname, email, password, photo) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nickname, $email, $password, $photo]);

                $userId = $pdo->lastInsertId();

                echo "Inscription réussie!";
                header("Location: dashboard.php?user_id=$userId");
                exit();
            } else {
                echo "Erreur lors du téléchargement de la photo.";
            }
        }
    } catch (PDOException $e) {
        echo "Erreur: " . $e->getMessage();
    }
}?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - EcoRide</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php">EcoRide</a>
            <a href="login.php">Connexion</a>
            <a href="register.php" class="active">Inscription</a>
            <a href="contact.php">Contact</a>
            <a href="search_rides.php">Recherche de Covoiturages</a>
        </nav>
    </header>
    <main>
        <h1>Inscription</h1>
        <form action="register.php" method="POST" enctype="multipart/form-data">
            <label for="nickname">Pseudo:</label>
            <input type="text" id="nickname" name="nickname" required><br><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>

            <label for="password">Mot de Passe:</label>
            <input type="password" id="password" name="password" required><br><br>

            <label for="photo">Photo (carré uniquement):</label>
            <input type="file" id="photo" name="photo" accept="image/*" required><br><br>

            <input type="submit" value="S'inscrire">
        </form>
    </main>
    <footer>
        <p>&copy; 2025 EcoRide. Tous droits réservés.</p>
    </footer>
</body>
</html>
