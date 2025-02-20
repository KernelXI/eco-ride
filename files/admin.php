<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

try {
    // Nombre de covoiturages par jour
    $stmtRides = $pdo->prepare("SELECT DATE(start_date) as date, COUNT(*) as count FROM ride GROUP BY DATE(start_date)");
    $stmtRides->execute();
    $ridesData = $stmtRides->fetchAll(PDO::FETCH_ASSOC);

    // Crédits gagnés par jour (2 crédits par covoiturage terminé)
    $stmtCredits = $pdo->prepare("SELECT DATE(start_date) as date, COUNT(*) * 2 as total FROM ride WHERE status = 'terminé' GROUP BY DATE(start_date)");
    $stmtCredits->execute();
    $creditsData = $stmtCredits->fetchAll(PDO::FETCH_ASSOC);
    
    // Total des crédits gagnés par la plateforme
    $stmtTotalCredits = $pdo->prepare("SELECT COUNT(*) * 2 as total FROM ride WHERE status = 'terminé'");
    $stmtTotalCredits->execute();
    $totalCredits = $stmtTotalCredits->fetchColumn();
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Tableau de Bord</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
<h1>Tableau de Bord Administrateur</h1>

<h2>Nombre de Covoiturages par Jour</h2>
<canvas id="ridesChart"></canvas>

<h2>Crédits Gagnés par Jour</h2>
<canvas id="creditsChart"></canvas>

<h2>Créer un Compte Employé</h2>
<form action="create_employee.php" method="post">
    <label for="nickname">Pseudo :</label>
    <input type="text" id="nickname" name="nickname" required>

    <label for="email">Email :</label>
    <input type="email" id="email" name="email" required>

    <label for="password">Mot de Passe :</label>
    <input type="password" id="password" name="password" required>

    <input type="submit" value="Créer Compte">
</form>

<h2>Total des Crédits Gagnés par la Plateforme</h2>
<p><?php echo htmlspecialchars($totalCredits); ?> crédits</p>

<h2>Suspendre un Compte</h2>
<form action="suspend_account.php" method="post">
    <label for="identifier">ID, Pseudo ou Email :</label>
    <input type="text" id="identifier" name="identifier" required>

    <label for="role">Sélectionner le Rôle :</label>
    <select id="role" name="role">
        <option value="chauffeur">Chauffeur</option>
        <option value="passager">Passager</option>
        <option value="passager_chauffeur">Passager Chauffeur</option>
        <option value="employee">Employé</option>
        <option value="suspended">Suspendu</option>
    </select>

    <input type="submit" value="Modifier Role">
</form>

<script>
    const ridesData = <?php echo json_encode($ridesData); ?>;
    const creditsData = <?php echo json_encode($creditsData); ?>;

    const ridesLabels = ridesData.map(data => data.date);
    const ridesCounts = ridesData.map(data => data.count);

    const creditsLabels = creditsData.map(data => data.date);
    const creditsTotals = creditsData.map(data => data.total);

    const ctxRides = document.getElementById('ridesChart').getContext('2d');
    const ridesChart = new Chart(ctxRides, {
        type: 'bar',
        data: {
            labels: ridesLabels,
            datasets: [{
                label: 'Covoiturages',
                data: ridesCounts,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const ctxCredits = document.getElementById('creditsChart').getContext('2d');
    const creditsChart = new Chart(ctxCredits, {
        type: 'line',
        data: {
            labels: creditsLabels,
            datasets: [{
                label: 'Crédits Gagnés',
                data: creditsTotals,
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<p><a href="logout.php">Deconnexion</a></p>
</body>
</html>
