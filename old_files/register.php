<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = $_POST['nickname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
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
}
?>
