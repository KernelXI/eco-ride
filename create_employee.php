<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE nickname = :nickname OR email = :email");
        $stmt->bindParam(':nickname', $nickname);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $message = "Le pseudo ou l'email est déjà utilisé.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
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
<p><a href="admin.php">Retour à la page admin</a></p>

<h1>Créer un Compte Employé</h1>
<?php if ($message): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form action="create_employee.php" method="post">
    <label for="nickname">Pseudo :</label>
    <input type="text" id="nickname" name="nickname" required><br>

    <label for="email">Email :</label>
    <input type="email" id="email" name="email" required><br>

    <label for="password">Mot de Passe :</label>
    <input type="password" id="password" name="password" required><br>

    <input type="submit" value="Créer Compte">
</form>

<footer>
    <p>&copy; 2025 Covoiturage. Tous droits réservés.</p>
</footer>
</body>
</html>
