<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nickname = $_POST['nickname'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        $passwordHash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

        $stmt = $pdo->prepare("UPDATE user SET nickname = ?, email = ?, role = ? " . ($passwordHash ? ", password = ?" : "") . " WHERE user_id = ?");
        $params = [$nickname, $email, $role];

        if ($passwordHash) {
            $params[] = $passwordHash;
        }
        $params[] = $userId;

        $stmt->execute($params);

        header("Location: dashboard.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT nickname, email, role FROM user WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Utilisateur introuvable.";
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
    <title>Modifier le Profil</title>
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
<h1>Modifier mon Profil</h1>
<form action="edit_profile.php" method="POST">
    <label for="nickname">Pseudo:</label>
    <input type="text" id="nickname" name="nickname" value="<?php echo htmlspecialchars($user['nickname']); ?>" required><br><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

    <label for="password">Mot de Passe (laisser vide pour ne pas changer):</label>
    <input type="password" id="password" name="password"><br><br>

    <label for="role">Rôle:</label>
    <select id="role" name="role" required>
        <option value="passager" <?php echo ($user['role'] === 'passager') ? 'selected' : ''; ?>>Passager</option>
        <option value="chauffeur" <?php echo ($user['role'] === 'chauffeur') ? 'selected' : ''; ?>>Chauffeur</option>
        <option value="passager_chauffeur" <?php echo ($user['role'] === 'passager_chauffeur') ? 'selected' : ''; ?>>Passager et Chauffeur</option>
    </select><br><br>

    <input type="submit" value="Mettre à
