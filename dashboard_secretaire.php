<?php
session_start();

// Vérifier si l'utilisateur est connecté et est une secrétaire
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'secretaire') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Secrétaire</title>
</head>
<body>
<h2>Bienvenue <?= htmlspecialchars($_SESSION['nom']); ?> !</h2>
<p>Ceci est votre dashboard secrétaire.</p>

<a href="logout.php">Déconnexion</a>
</body>
</html>
