<?php
session_start();
require '../vendor/autoload.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$contactMessages = $client->eco_ride->contact_messages;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? null;
    $content = $_POST['message'] ?? '';

    try {
        $contactMessages->insertOne([
            'email' => $email,
            'message' => $content,
            'timestamp' => new MongoDB\BSON\UTCDateTime()
        ]);
        $message = "Votre message a été envoyé avec succès !";
    } catch (MongoDB\Driver\Exception\Exception $e) {
        $message = "Erreur lors de l'envoi du message : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Covoiturage</title>
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
    <h1>Contactez-nous</h1>

    <div>
        <h2>Envoyer un message</h2>
        <?php if ($message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="contact.php" method="post">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required>

            <label for="message">Message :</label>
            <textarea id="message" name="message" rows="4" required></textarea>

            <input type="submit" value="Envoyer">
        </form>
    </div>

    <footer>
        <p>&copy; 2025 Covoiturage. Tous droits réservés.</p>
    </footer>
</body>
</html>
