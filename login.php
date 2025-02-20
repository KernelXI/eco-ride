<?php
session_start();
require 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT user_id, nickname, email, password, role FROM user WHERE nickname = ? OR email = ?");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['role'] === 'suspended') {
                $error = "Votre compte est suspendu. Veuillez contacter l'assistance.";
            } elseif (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['nickname'] = $user['nickname'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'employee') {
                    header("Location: staff.php");
                } elseif ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $error = "Identifiants invalides.";
            }
        } else {
            $error = "Identifiants invalides.";
        }
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
    <title>Connexion</title>
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
<h1>Connexion</h1>
<?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
<form action="login.php" method="POST">
    <label for="login">Pseudo ou Email:</label>
    <input type="text" id="login" name="login" required><br><br>

    <label for="password">Mot de Passe:</label>
    <input type="password" id="password" name="password" required><br><br>

    <input type="submit" value="Se connecter">
</form>
</body>
</html>
