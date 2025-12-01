<?php
session_start();

// Afficher toutes les erreurs PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base
$conn = new mysqli("localhost", "root", "", "cabinet_medical");
if ($conn->connect_error) die("Erreur connexion : " . $conn->connect_error);

$message = "";
$message_color = "";

// Lorsque le formulaire est soumis
if (isset($_POST["register"])) {

    $nom = htmlspecialchars(trim($_POST["nom"]));
    $prenom = htmlspecialchars(trim($_POST["prenom"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $password = $_POST["password"];

    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        die("Erreur préparation SQL: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result(); 

    if ($stmt->num_rows > 0) {
        $message = "Cet email existe déjà.";
        $message_color = "red";
    } else {
        // Hachage du mot de passe
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insert
        $stmt2 = $conn->prepare(
            "INSERT INTO users (nom, prenom, email, mot_de_passe, role) 
             VALUES (?, ?, ?, ?, 'patient')"
        );
        if (!$stmt2) {
            die("Erreur préparation SQL INSERT: " . $conn->error);
        }
        $stmt2->bind_param("ssss", $nom, $prenom, $email, $hashed);

        if ($stmt2->execute()) {
            $message = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
            $message_color = "green";
        } else {
            $message = "Erreur lors de l'inscription : " . $stmt2->error;
            $message_color = "red";
        }
        $stmt2->close();
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Patient</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <div class="container">
        <h2>Inscription Patient</h2>
        <form action="inscription_patient.php" method="POST">
            
            <div class="input-group">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" required>
            </div>

            <div class="input-group">
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>

            <div class="input-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="input-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">S'inscrire</button>

            <p class="login-link">Déjà inscrit ? <a href="login.php">Connectez-vous</a></p>
        </form>
    </div>
</body>
</html>

