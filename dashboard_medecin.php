<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un médecin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'medecin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base
$conn = new mysqli("localhost", "root", "", "cabinet_medical");
if ($conn->connect_error) die("Erreur connexion : ".$conn->connect_error);

// ---- Gérer confirmation / annulation ----
if(isset($_POST['confirmer'])) {
    $id_rdv = intval($_POST['id_rdv']);
    $stmt = $conn->prepare("UPDATE rendezvous SET statut='confirme' WHERE id_rdv=?");
    $stmt->bind_param("i", $id_rdv);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if(isset($_POST['annuler'])) {
    $id_rdv = intval($_POST['id_rdv']);
    $stmt = $conn->prepare("UPDATE rendezvous SET statut='annule' WHERE id_rdv=?");
    $stmt->bind_param("i", $id_rdv);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// ----- Récupérer RDV du jour -----
$today = date("Y-m-d");
$sql_today = "SELECT r.id_rdv, r.date_rdv, r.heure_rdv, p.nom, p.prenom 
              FROM rendezvous r 
              JOIN patients p ON r.id_patient = p.id_patient 
              WHERE r.date_rdv = ? 
              ORDER BY r.heure_rdv ASC";
$stmt_today = $conn->prepare($sql_today);
$stmt_today->bind_param("s", $today);
$stmt_today->execute();
$result_today = $stmt_today->get_result();
$stmt_today->close();

// ----- RDV en attente -----
$sql_attente = "SELECT r.id_rdv, r.date_rdv, r.heure_rdv, r.statut, p.nom, p.prenom 
                FROM rendezvous r 
                JOIN patients p ON r.id_patient = p.id_patient 
                WHERE r.statut='en_attente'
                ORDER BY r.date_rdv ASC, r.heure_rdv ASC";
$result_attente = $conn->query($sql_attente);

// ----- Patients -----
$sql_patients = "SELECT id, nom, prenom, email FROM users WHERE role='patient' ORDER BY nom ASC";
$result_patients = $conn->query($sql_patients);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard Médecin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="style_d.css?v=5">


</head>
<body>

<header>
    <h1>Bienvenue Dr <?= htmlspecialchars($_SESSION['nom']); ?> !</h1>
</header>

<nav>
    <a href="#" id="linkRdv" class="active"><i class="fa-solid fa-calendar-days"></i> Rendez-vous</a>
    <a href="#" id="linkPatients"><i class="fa-solid fa-user"></i> Patients</a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
</nav>


<div class="container">
    <!-- Section RDV -->
    <div id="sectionRdv">
        <h2>Rendez-vous du jour (<?= htmlspecialchars($today) ?>)</h2>
        <div class="cards">
            <?php if ($result_today && $result_today->num_rows > 0): ?>
                <?php while($row = $result_today->fetch_assoc()): ?>
                <div class="card" style="background:#d0ebff">
                    <h3><?= htmlspecialchars($row['nom'].' '.$row['prenom']) ?></h3>
                    <p>Heure : <?= htmlspecialchars($row['heure_rdv']) ?></p>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-warning"><i class="fa-solid fa-circle-exclamation"></i>Aucun rendez-vous prévu aujourd'hui.</div>
            <?php endif; ?>
        </div>

        <h2>Rendez-vous en attente</h2>
        <table>
            <tr><th>Patient</th><th>Date</th><th>Heure</th><th>Actions</th></tr>
            <?php if ($result_attente && $result_attente->num_rows > 0): ?>
                <?php while($row = $result_attente->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nom'].' '.$row['prenom']) ?></td>
                    <td><?= htmlspecialchars($row['date_rdv']) ?></td>
                    <td><?= htmlspecialchars($row['heure_rdv']) ?></td>
                    <td>
                        <?php if($row['statut'] == 'en_attente'): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="id_rdv" value="<?= intval($row['id_rdv']) ?>">
                                <button name="confirmer" class="btn confirm"><i class="fa-solid fa-check"></i> Confirmer</button>
                            </form>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="id_rdv" value="<?= intval($row['id_rdv']) ?>">
                                <button name="annuler" class="btn cancel"><i class="fa-solid fa-xmark"></i> Annuler</button>
                            </form>
                        <?php elseif($row['statut'] == 'confirme'): ?>
                            <span style="color:green;font-weight:bold;">✔ Confirmé</span>
                        <?php else: ?>
                            <span style="color:red;font-weight:bold;">✖ Annulé</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">Aucun rendez-vous en attente.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Section Patients -->
    <div id="sectionPatients" style="display:none;">
        <h2>Liste des patients</h2>
        <table>
            <tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Actions</th></tr>
            <?php if($result_patients && $result_patients->num_rows > 0): ?>
                <?php while($row = $result_patients->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['nom']) ?></td>
                    <td><?= htmlspecialchars($row['prenom']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <a href="modifier_patient.php?id=<?= intval($row['id']) ?>" class="btn btn-edit">
                            <i class="fa-solid fa-pen"></i> Modifier
                        </a>

                        <a href="patient_consultation.php?id=<?= intval($row['id']) ?>" class="btn btn-view">
                            <i class="fa-solid fa-eye"></i> Consulter
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">Aucun patient trouvé.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
// Onglets simples
const linkRdv = document.getElementById('linkRdv');
const linkPatients = document.getElementById('linkPatients');
const sectionRdv = document.getElementById('sectionRdv');
const sectionPatients = document.getElementById('sectionPatients');

linkRdv.addEventListener('click', ()=>{
    sectionRdv.style.display='block';
    sectionPatients.style.display='none';
    linkRdv.classList.add('active');
    linkPatients.classList.remove('active');
});
linkPatients.addEventListener('click', ()=>{
    sectionRdv.style.display='none';
    sectionPatients.style.display='block';
    linkRdv.classList.remove('active');
    linkPatients.classList.add('active');
});
</script>

</body>
</html>
