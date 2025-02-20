<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Utilisateur non connecté.";
    header("Location: login.php");
    exit();
}

// Rediriger les employés vers la page staff
if (isset($_SESSION['role']) && $_SESSION['role'] === 'employee') {
    header("Location: staff.php");
    exit();
}

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin.php");
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT nickname, email, password, role, photo, credit FROM user WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Utilisateur introuvable.";
        exit();
    }

    // Récupérer les voitures de l'utilisateur
    $carStmt = $pdo->prepare("SELECT * FROM car WHERE user_id = ?");
    $carStmt->execute([$userId]);
    $cars = $carStmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les covoiturages prévus ou en cours
    $rideStmt = $pdo->prepare("
        SELECT r.start_address, r.start_city, r.destination_address, r.destination_city,
               r.start_date, r.start_time, r.destination_date, r.destination_time,
               r.status, u.nickname AS driver_nickname, c.branding, c.model, c.plate
        FROM ride r
        LEFT JOIN user_ride ur ON r.ride_id = ur.ride_id
        LEFT JOIN user u ON r.driver_id = u.user_id
        LEFT JOIN car c ON r.car_id = c.car_id
        WHERE ur.user_id = ? OR r.driver_id = ?
        AND (r.status = 'prévu' OR r.status = 'en cours')
    ");
    $rideStmt->execute([$userId, $userId]); // Vérifiez les covoiturages pour l'utilisateur
    $ongoingRides = $rideStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord</title>
    <link rel="stylesheet" href="styles.css"> <!-- Lien vers le fichier CSS -->
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
<main>
    <section>
        <div>
            <div>
                <h1>Bienvenue, <?php echo htmlspecialchars($user['nickname']); ?>!</h1>
                <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                <p>Mot de Passe: ********</p>
                <p>Rôle: <?php echo htmlspecialchars($user['role']); ?></p>
                <p>Crédit: <?php echo htmlspecialchars($user['credit']); ?> €</p>
            </div>
            

        <?php if ($user['photo']): ?>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($user['photo']); ?>" alt="Votre Photo" style="max-width: 200px; max-height: 200px;">
        <?php endif; ?>
        </div>
    
    </section>
    

    

    <h2>Vos Voitures:</h2>
    <ul>
        <?php foreach ($cars as $car): ?>
            <li>
                <?php echo htmlspecialchars($car['branding'] . ' ' . $car['model'] . ' - Plaque: ' . htmlspecialchars($car['plate'])); ?>
                <a href="del_car.php?car_id=<?php echo $car['car_id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette voiture ?');">Supprimer</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Covoiturages Prévu ou En Cours:</h2>
    <?php if (count($ongoingRides) > 0): ?>
        <ul>
            <?php foreach ($ongoingRides as $ride): ?>
                <li>
                    <?php echo htmlspecialchars($ride['start_city'] . ' ➔ ' . $ride['destination_city']); ?>
                    <p><strong>Chauffeur:</strong> <?php echo htmlspecialchars($ride['driver_nickname']); ?></p>
                    <p><strong>Voiture:</strong> <?php echo htmlspecialchars($ride['branding'] . ' ' . $ride['model'] . ' - Plaque: ' . htmlspecialchars($ride['plate'])); ?></p>
                    <p><strong>Date de départ:</strong> <?php echo htmlspecialchars($ride['start_date']); ?> à <?php echo htmlspecialchars($ride['start_time']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($ride['status']); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun covoiturage prévu ou en cours.</p>
    <?php endif; ?>

    <p><a href="ride_history.php">Covoiturages</a></p>
    <p><a href="edit_profile.php">Modifier mon profil</a></p>
    <p><a href="add_car.php">Ajouter une voiture</a></p>
    <p><a href="create_ride.php">Créer un Covoiturage</a></p>
    <p><a href="search_rides.php">Rechercher des Covoiturages</a></p>
    <p><a href="logout.php">Se déconnecter</a></p>
</main>
<footer>
    <p>&copy; 2025 EcoRide. Tous droits réservés.</p>
</footer>
</body>
</html>
