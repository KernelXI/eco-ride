<?php
session_start();
require 'db.php';

// Vérifie si l'utilisateur est connecté
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$userId) {
    header("Location: login.php");
    exit();
}

try {
    // Requête pour obtenir l'historique des covoiturages en tant que passager
    $sql = "
        SELECT r.ride_id, r.start_address, r.start_city, r.destination_address, r.destination_city,
               r.start_date, r.start_time, u.nickname AS driver_nickname, r.status, u.user_id AS driver_id
        FROM ride r
        JOIN user_ride ur ON r.ride_id = ur.ride_id
        JOIN user u ON r.driver_id = u.user_id
        WHERE ur.user_id = :user_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des covoiturages : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Covoiturages</title>
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
    <h1>Historique des Covoiturages (Passager)</h1>
    <table>
        <tr>
            <th>Destination</th>
            <th>Date de Départ</th>
            <th>Conducteur</th>
            <th>Action</th>
        </tr>
        <?php foreach ($rides as $ride): ?>
            <tr>
                <td><?php echo htmlspecialchars($ride['destination_city']); ?></td>
                <td><?php echo htmlspecialchars($ride['start_date'] . ' ' . $ride['start_time']); ?></td>
                <td><?php echo htmlspecialchars($ride['driver_nickname']); ?></td>
                <td>
                    <?php if ($ride['status'] === 'terminé'): ?>
                        <a href="leave_feedback.php?ride_id=<?php echo $ride['ride_id']; ?>">Laisser un avis</a>
                    <?php elseif ($ride['status'] === 'en cours'): ?>
                        <form action="cancel_join.php" method="post">
                            <input type="hidden" name="ride_id" value="<?php echo $ride['ride_id']; ?>">
                            <input type="submit" value="Annuler">
                        </form>
                    <?php else: // Covoiturage prévu ?>
                        <?php if ($ride['driver_id'] !== $userId): ?>
                            <form action="cancel_ride.php" method="post">
                                <input type="hidden" name="ride_id" value="<?php echo $ride['ride_id']; ?>">
                                <input type="submit" value="Annuler le Covoiturage">
                            </form>
                        <?php else: ?>
                            <p>Covoiturage prévu</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h1>Covoiturages que vous conduisez</h1>
    <?php
    try {
        // Requête pour obtenir les covoiturages que l'utilisateur conduit
        $sql_driver = "
            SELECT r.ride_id, r.start_address, r.start_city, r.destination_address, r.destination_city,
                   r.start_date, r.start_time, r.status
            FROM ride r
            WHERE r.driver_id = :driver_id
        ";

        $stmt_driver = $pdo->prepare($sql_driver);
        $stmt_driver->bindParam(':driver_id', $userId, PDO::PARAM_INT);
        $stmt_driver->execute();
        $driver_rides = $stmt_driver->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur lors de la récupération des covoiturages : " . $e->getMessage());
    }
    ?>
    
    <table>
        <tr>
            <th>Destination</th>
            <th>Date de Départ</th>
            <th>Statut</th>
            <th>Action</th>
        </tr>
        <?php foreach ($driver_rides as $ride_driver): ?>
            <tr>
                <td><?php echo htmlspecialchars($ride_driver['destination_city']); ?></td>
                <td><?php echo htmlspecialchars($ride_driver['start_date'] . ' ' . $ride_driver['start_time']); ?></td>
                <td><?php echo htmlspecialchars($ride_driver['status']); ?></td>
                <td>
                    <?php if ($ride_driver['status'] === 'prévu'): ?>
                        <form action="cancel_ride.php" method="post">
                            <input type="hidden" name="ride_id" value="<?php echo $ride_driver['ride_id']; ?>">
                            <input type="submit" value="Annuler le Covoiturage">
                        </form>
                    <?php else: ?>
                        Terminé
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="dashboard.php">Retour au tableau de bord</a></p>
</body>
</html>
