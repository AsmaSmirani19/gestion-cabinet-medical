<?php
session_start();

// Connexion à la base
$conn = new mysqli("localhost", "root", "", "cabinet_medical");
if ($conn->connect_error) die("Connexion échouée: " . $conn->connect_error);

$error = "";

// Vérifier si le formulaire est soumis
if (isset($_POST['login'])) {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];

    // Chercher l'utilisateur (ici on suppose que la table 'users' contient nom, prenom, role, mot_de_passe)
    $stmt = $conn->prepare("SELECT id, nom, prenom, role, mot_de_passe FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Vérification du mot de passe
        if (password_verify($password, $user['mot_de_passe'])) {
            // Stocker les infos en session
            $_SESSION['id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];  // <-- IMPORTANT pour espace_patient.php
            $_SESSION['role'] = $user['role'];

            // Redirection selon le rôle
            $pages = [
                'patient' => 'espace_patient.php',
                'medecin' => 'dashboard_medecin.php',
                'secretaire' => 'dashboard_secretaire.php'
            ];

            if (isset($pages[$user['role']])) {
                header("Location: " . $pages[$user['role']]);
                exit();
            } else {
                $error = "Rôle inconnu. Contactez l'administrateur.";
            }
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Email incorrect ou compte inexistant.";
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
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css"> <!-- Ton CSS -->
</head>
<body>
    <div class="container">
        <h2>Connexion</h2>

        <!-- Affichage du message d'erreur -->
        <?php if(isset($error) && $error != "") echo "<p style='color:red;'>$error</p>"; ?>

        <form action="login.php" method="POST">

            <div class="input-group">
                <label for="email">Email :</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="input-group">
                <label for="password">Mot de passe :</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" name="login">Se connecter</button>
        </form>

        <p class="login-link">Pas encore de compte ? <a href="inscription_patient.php">Inscrivez-vous ici</a></p>

    </div>
</body>
</html>
