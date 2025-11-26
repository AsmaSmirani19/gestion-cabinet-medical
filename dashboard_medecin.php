<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un médecin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'medecin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Médecin</title>
</head>
<body>
<h2>Bienvenue Dr <?= htmlspecialchars($_SESSION['nom']); ?> !</h2>
<p>Ceci est votre dashboard médecin.</p>

<a href="logout.php">Déconnexion</a>
</body>
</html>
