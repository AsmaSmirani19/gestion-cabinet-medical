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

$user_id = $_SESSION['id']; // ID de l'utilisateur connecté
$nom = $_SESSION['nom'];
$prenom = $_SESSION['prenom'];

// --- Vérifier si le patient existe dans la table patients ---
$stmt_check = $conn->prepare("SELECT id_patient FROM patients WHERE user_id = ?");
$stmt_check->bind_param("i", $user_id);
$stmt_check->execute();
$stmt_check->store_result();
$stmt_check->bind_result($patient_id);

if ($stmt_check->num_rows === 0) {
    // Patient non enregistré, l'ajouter
    $stmt_insert = $conn->prepare("INSERT INTO patients (user_id, nom, prenom) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("iss", $user_id, $nom, $prenom);
    if (!$stmt_insert->execute()) {
        die("Erreur lors de l'ajout du patient : " . $stmt_insert->error);
    }
    $patient_id = $conn->insert_id; // récupérer l'id_patient créé
    $stmt_insert->close();
} else {
    $stmt_check->fetch();
}
$stmt_check->close();

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
        // Ajouter les secondes si manquent (format TIME)
        if (strlen($heure) == 5) { // ex: 14:00
            $heure .= ":00"; // devient 14:00:00
        }

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
    <style>
        table { border-collapse: collapse; }
        th, td { padding: 5px 10px; border: 1px solid #000; text-align: center; }
        .en_attente { background-color: #fffa99; }
        .confirme { background-color: #a0f0a0; }
        .annule { background-color: #f0a0a0; }
    </style>
</head>
<body>
<h2>Bienvenue <?= htmlspecialchars($nom); ?> !</h2>
<p>Ceci est votre espace patient.</p>

<a href="logout.php">Déconnexion</a>
<hr>

<!-- Affichage des messages -->
<?php if($message != ""): ?>
    <p style="color:<?= $message_color; ?>"><?= htmlspecialchars($message); ?></p>
<?php endif; ?>

<h3>Mes rendez-vous</h3>
<?php
if ($result->num_rows > 0) {
    echo "<table>
            <tr><th>Date</th><th>Heure</th><th>Statut</th></tr>";
    while($row = $result->fetch_assoc()) {
        $classe = htmlspecialchars($row['statut']);
        echo "<tr class='{$classe}'>
                <td>".htmlspecialchars($row['date_rdv'])."</td>
                <td>".htmlspecialchars($row['heure_rdv'])."</td>
                <td>".htmlspecialchars($row['statut'])."</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>Vous n'avez aucun rendez-vous pour le moment.</p>";
}
$stmt_rdv->close();
?>

<hr>

<h3>Prendre un nouveau rendez-vous</h3>
<form method="POST" action="">
    <label for="date">Date :</label>
    <input type="date" name="date" id="date" required><br><br>

    <label for="heure">Heure :</label>
    <input type="time" name="heure" id="heure" required><br><br>

    <button type="submit" name="rdv">Envoyer la demande</button>
</form>

</body>
</html>

<?php
$conn->close();
?>
