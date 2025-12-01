<?php
session_start();

// Connexion à la base
$conn = new mysqli("localhost", "root", "", "cabinet_medical");
if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}

// Si déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['medecin_logged']) && $_SESSION['medecin_logged'] === true) {
    header("Location: dashboard_medecin.php");
    exit();
}

// Vérifier la soumission du formulaire
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_input = trim($_POST['password']);

    // Récupérer le hash du médecin depuis la base
    $stmt = $conn->prepare("SELECT mot_de_passe, nom FROM users WHERE role='medecin' LIMIT 1");
    $stmt->execute();
    $stmt->bind_result($hash_db, $nom);
    $stmt->fetch();
    $stmt->close();

    if ($hash_db && password_verify($password_input, $hash_db)) {
        // Mot de passe correct
        $_SESSION['medecin_logged'] = true;
        $_SESSION['nom'] = $nom;
        header("Location: dashboard_medecin.php");
        exit();
    } else {
        $error = "Mot de passe incorrect !";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Médecin</title>
    <style>
        body { font-family: Arial; display:flex; justify-content:center; align-items:center; height:100vh; background:#f5f7fa; }
        .login-box { background:white; padding:30px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); text-align:center; }
        input { margin:10px 0; padding:10px; width:100%; }
        button { padding:10px 20px; background:#2aa3f0; color:white; border:none; border-radius:5px; cursor:pointer; }
        button:hover { background:#1f7acb; }
        .error { color:red; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Connexion Médecin</h2>
        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="">
            <input type="password" name="password" placeholder="Mot de passe" required><br>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
