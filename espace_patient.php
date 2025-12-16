<?php
session_start();

// Afficher toutes les erreurs PHP pour le debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier si l'utilisateur est connecté et est un patient
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// Connexion à la base
$conn = new mysqli("localhost", "root", "", "cabinet_medical");
if ($conn->connect_error) die("Connexion échouée: " . $conn->connect_error);

// Récupérer les infos du patient depuis la session
$nom = $_SESSION['nom'];
$prenom = $_SESSION['prenom'];

// --- Vérifier si le patient existe dans la table patients ---
$stmt_check = $conn->prepare("SELECT id_patient FROM patients WHERE nom = ? AND prenom = ?");
if (!$stmt_check) die("Erreur prepare: " . $conn->error);

$stmt_check->bind_param("ss", $nom, $prenom);
$stmt_check->execute();
$stmt_check->store_result();
$stmt_check->bind_result($patient_id);
$stmt_check->fetch();
$stmt_check->close();

// Si le patient n'existe pas, on l'ajoute
if (!$patient_id) {
    $stmt_insert = $conn->prepare("INSERT INTO patients (nom, prenom) VALUES (?, ?)");
    if (!$stmt_insert) die("Erreur prepare insert: " . $conn->error);

    $stmt_insert->bind_param("ss", $nom, $prenom);
    if (!$stmt_insert->execute()) {
        die("Erreur lors de l'ajout du patient : " . $stmt_insert->error);
    }
    $patient_id = $conn->insert_id;
    $stmt_insert->close();
}

// --- Gérer l'envoi d'une nouvelle demande de rendez-vous ---
$message = "";
$message_color = "";

if (isset($_POST['rdv'])) {
    $date = $_POST['date'];
    $heure = $_POST['heure'];

    if (empty($date) || empty($heure)) {
        $message = "Veuillez remplir la date et l'heure du rendez-vous.";
        $message_color = "red";
    } else {
        if (strlen($heure) == 5) $heure .= ":00"; // ex: 14:00 -> 14:00:00

        // Vérifier que le créneau n'est pas déjà pris
        $stmt_check_rdv = $conn->prepare(
            "SELECT id_rdv FROM rendezvous WHERE date_rdv=? AND heure_rdv=? AND statut!='annule'"
        );
        $stmt_check_rdv->bind_param("ss", $date, $heure);
        $stmt_check_rdv->execute();
        $stmt_check_rdv->store_result();

        if ($stmt_check_rdv->num_rows > 0) {
            $message = "Ce créneau est déjà pris.";
            $message_color = "red";
        } else {
            $statut = 'en_attente';
            $stmt_insert_rdv = $conn->prepare(
                "INSERT INTO rendezvous (id_patient, date_rdv, heure_rdv, statut) VALUES (?, ?, ?, ?)"
            );
            $stmt_insert_rdv->bind_param("isss", $patient_id, $date, $heure, $statut);

            if ($stmt_insert_rdv->execute()) {
                $message = "Rendez-vous envoyé au médecin !";
                $message_color = "green";
            } else {
                $message = "Erreur lors de l'envoi du rendez-vous : " . $stmt_insert_rdv->error;
                $message_color = "red";
            }
            $stmt_insert_rdv->close();
        }
        $stmt_check_rdv->close();
    }
}

// --- Récupérer les rendez-vous du patient ---
$stmt_rdv = $conn->prepare(
    "SELECT date_rdv, heure_rdv, statut FROM rendezvous WHERE id_patient = ? ORDER BY date_rdv ASC, heure_rdv ASC"
);
$stmt_rdv->bind_param("i", $patient_id);
$stmt_rdv->execute();
$result = $stmt_rdv->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Espace Patient</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
 <link rel="stylesheet" href="style_p.css">
</head>
<body>

<nav>
    <a id="linkRdv" class="active">Mes rendez-vous</a>
    <a id="linkPrendre">Prendre un rendez-vous</a>
    <a href="logout.php">Déconnexion</a>
</nav>

<div class="container section active" id="sectionRdv">
    <h2>Mes rendez-vous</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr><th>Date</th><th>Heure</th><th>Statut</th></tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['date_rdv']) ?></td>
                    <td><?= htmlspecialchars($row['heure_rdv']) ?></td>
                    <td><?= htmlspecialchars($row['statut']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun rendez-vous prévu.</p>
    <?php endif; ?>
</div>

<div class="container section" id="sectionPrendre">
    <h2>Prendre un nouveau rendez-vous</h2>
    <form method="POST">
        <label for="date">Date :</label>
        <input type="date" name="date" id="date" required>
        <label for="heure">Heure :</label>
        <input type="time" name="heure" id="heure" required>
        <button type="submit" name="rdv"><i class="fa-solid fa-paper-plane"></i> Envoyer</button>
    </form>
</div>

<script>
// Onglets simples
const linkRdv = document.getElementById('linkRdv');
const linkPrendre = document.getElementById('linkPrendre');
const sectionRdv = document.getElementById('sectionRdv');
const sectionPrendre = document.getElementById('sectionPrendre');

linkRdv.addEventListener('click', ()=>{
    sectionRdv.classList.add('active');
    sectionPrendre.classList.remove('active');
    linkRdv.classList.add('active');
    linkPrendre.classList.remove('active');
});
linkPrendre.addEventListener('click', ()=>{
    sectionRdv.classList.remove('active');
    sectionPrendre.classList.add('active');
    linkRdv.classList.remove('active');
    linkPrendre.classList.add('active');
});
</script>

</body>
</html>
