<?php
session_start(); // Démarre la session
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Covoiturage</title>
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
    <h1>Covoiturage</h1>

    
    <div class="search-container">
        <h2>Rechercher un Covoiturage</h2>
        <form action="search_rides.php" method="post">
            <label for="start_address">Adresse de départ :</label>
            <input type="text" id="start_address" name="start_address">
            
            <label for="start_city">Ville de départ :</label>
            <input type="text" id="start_city" name="start_city" required>
            
            <label for="destination_address">Adresse d'arrivée :</label>
            <input type="text" id="destination_address" name="destination_address">
            
            <label for="destination_city">Ville d'arrivée :</label>
            <input type="text" id="destination_city" name="destination_city" required>
            
            <label for="departure_date">Date de départ :</label>
            <input type="date" id="departure_date" name="departure_date" required>
            
            <label for="departure_time">Heure de départ :</label>
            <input type="time" id="departure_time" name="departure_time">
            
            <label for="arrival_date">Date d'arrivée :</label>
            <input type="date" id="arrival_date" name="arrival_date" required>
            
            <label for="arrival_time">Heure d'arrivée :</label>
            <input type="time" id="arrival_time" name="arrival_time">
            
            <input type="submit" value="Rechercher">
        </form>
    </div>
    
    <div class="presentation">
        <h2>Présentation de l'Entreprise</h2>
        <p>
            Bienvenue sur notre plateforme de covoiturage, où nous nous engageons à offrir une expérience de voyage durable et économique. 
            Notre mission est de relier les conducteurs et les passagers pour partager des trajets, réduire les coûts et minimiser l'empreinte carbone. 
            Grâce à notre service, chaque voyage devient une opportunité de rencontre et de partage.
        </p>
        <img src="images/image1.jpg" alt="Covoiturage" style="width: 300px; height: auto;">
        <p>
            Nous croyons fermement que le covoiturage est une solution efficace pour améliorer la mobilité et contribuer à la protection de l'environnement. 
            En choisissant de voyager ensemble, vous participez à la réduction du trafic routier et des émissions de CO2. 
            Rejoignez notre communauté et ensemble, faisons une différence pour notre planète.
        </p>
        <img src="images/image2.jpg" alt="Environnement" style="width: 300px; height: auto;">
        <p>
            Notre plateforme est facile à utiliser, que vous soyez conducteur ou passager. 
            Vous pouvez rapidement trouver un covoiturage qui correspond à vos besoins, que ce soit pour un trajet quotidien ou une escapade le week-end. 
            Inscrivez-vous dès aujourd'hui et découvrez tous les avantages du covoiturage avec nous.
        </p>
        <img src="images/image3.jpg" alt="Voyage" style="width: 300px; height: auto;">
    </div>

    <footer>
        <p>&copy; 2025 EcoRide. Tous droits réservés.</p>
            <a href="mentions_legales.php">Mentions Légales</a>
            <a href="contact.php">Contact</a>
    </footer>
</body>
</html>
