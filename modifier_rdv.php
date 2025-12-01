<?php
session_start();
if(!isset($_SESSION['id']) || $_SESSION['role'] !== 'medecin'){
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cabinet_medical");
if($conn->connect_error) die("Erreur connexion : ".$conn->connect_error);

$id_rdv = intval($_GET['id'] ?? 0);
if($id_rdv <= 0){
    die("ID de rendez-vous invalide.");
}

// Récupérer les infos du RDV
$sql = "SELECT * FROM rendezvous WHERE id_rdv=$id_rdv";
$res = $conn->query($sql);
$rdv = $res->fetch_assoc();
if(!$rdv){
    die("Rendez-vous introuvable.");
}

// Ici tu peux créer un formulaire pour modifier le RDV
?>
<form method="POST" action="modifier_rdv_action.php">
    <input type="hidden" name="id_rdv" value="<?= $id_rdv ?>">
    <label>Date :</label>
    <input type="date" name="date_rdv" value="<?= htmlspecialchars($rdv['date_rdv']) ?>"><br>
    <label>Heure :</label>
    <input type="time" name="heure_rdv" value="<?= htmlspecialchars($rdv['heure_rdv']) ?>"><br>
    <button name="modifier">Modifier</button>
</form>
