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

// ----- Récupérer RDV du jour -----
$today = date("Y-m-d");
$sql_today = "
    SELECT r.id_rdv, r.date_rdv, r.heure_rdv, p.nom, p.prenom 
    FROM rendezvous r 
    JOIN patients p ON r.id_patient = p.id_patient 
    WHERE r.date_rdv = '$today'
    ORDER BY r.heure_rdv ASC
";
$result_today = $conn->query($sql_today);

// ----- RDV en attente -----
$sql_attente = "
    SELECT r.id_rdv, r.date_rdv, r.heure_rdv, p.nom, p.prenom 
    FROM rendezvous r 
    JOIN patients p ON r.id_patient = p.id_patient 
    WHERE r.statut = 'en_attente'
    ORDER BY r.date_rdv ASC, r.heure_rdv ASC
";
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
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Poppins", sans-serif;
    background: linear-gradient(135deg , #a1c4fd , #c2e9fb);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* ---- HEADER ---- */
header {
    margin-top: 70px;
    width: 100%;
    text-align: center;
    padding: 25px 0;
    color: #ffffff;
    font-size: 22px;
    font-weight: 600;
}

/* ---- NAV ---- */
nav {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-bottom: 25px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background: #0d6efd;
    padding: 12px 0;
    z-index: 1000;
}

nav a {
    text-decoration: none;
    color: #ffffff;
    background: rgba(255,255,255,0.25);
    padding: 10px 22px;
    border-radius: 12px;
    font-weight: bold;
    transition: 0.3s;
    font-size: 18px;
}

nav a:hover {
    background: rgba(255,255,255,0.5);
    text-decoration: underline;
}

/* ---- CONTAINER ---- */
.container{
    background: rgba(255, 255, 255, 0.55);
    backdrop-filter: blur(10px);
    padding: 35px;
    width: 90%;
    max-width: 1100px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0, 0, 0 , 0.15);
    margin-bottom: 40px;
    margin-top: 30px;
}

/* ---- TITRES ---- */
h2 {
    color: #2d3436;
    margin-bottom: 20px;
    font-weight: 600;
}

/* ---- CARDS RDV ---- */
.cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: rgba(255,255,255,0.9);
    border-radius: 20px;
    padding: 20px;
    flex: 1 1 250px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-3px);
}

/* ---- BUTTON ---- */
button, .btn {
    padding: 10px 18px;
    font-size: 14px;
    border: none;
    color: white;
    border-radius: 12px;
    cursor: pointer;
    transition: 0.3s ease, box-shadow 0.3s ease;
    margin-right: 5px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Bouton Confirmer = vert */
button[name="confirmer"], .btn.confirm {
    background: linear-gradient(135deg, #28a745, #2ecc71);
}

/* Bouton Annuler = rouge */
button[name="annuler"], .btn.cancel {
    background: linear-gradient(135deg, #e74c3c, #ff4d4d);
}

/* Hover glow */
button:hover, .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
}

/* Icônes dans bouton */
button i, .btn i {
    font-size: 16px;
}


button:hover, .btn:hover {
    transform: translateY(-3px);
    background: linear-gradient(135deg,#2575fc, #6a11cb);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

/* --- STYLE TABLE MODERNE --- */

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    border-radius: 12px;
    overflow: hidden; /* nécessaire pour arrondir */
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

/* En-têtes */
table thead tr {
    background: #4f7cff; /* bleu moderne */
    color: white;
    font-weight: 600;
}

table thead th {
    padding: 14px;
    font-size: 15px;
}

/* Lignes */
table tbody tr {
    border-bottom: 1px solid #eee; /* séparateurs fins */
}

/* Cellules */
table td {
    padding: 12px;
    font-size: 14px;
    color: #333;
}

/* Effet hover */
table tbody tr:hover {
    background: #f2f6ff; /* léger bleu */
    transition: 0.2s;
}

/* Coins arrondis pour le premier et dernier th */
table thead th:first-child {
    border-top-left-radius: 12px;
}
table thead th:last-child {
    border-top-right-radius: 12px;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
    color: #34495e;
}

th {
    background: rgba(255,255,255,0.6);
}
/* ---- Message Aucun RDV ---- */
.msg-rouge {
    color: red;
    font-weight: 600;
    text-align: center;
    margin-bottom: 15px;
}
nav a i {
    margin-right: 8px;
    font-size: 18px;
}
nav a.active {
    background: rgba(255, 255, 255, 0.25);
    padding: 8px 15px;
    border-radius: 8px;
    border: 2px solid rgba(255,255,255,0.6);
}
/* --- Alerte moderne --- */

.alert {
    padding: 15px 20px;
    border-radius: 12px;
    font-size: 15px;
    margin: 15px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.alert-warning {
    background: #fff8e5;
    color: #b27a00;
    border-left: 5px solid #f4c542;
}

.alert i {
    font-size: 20px;
}


</style>
</head>
<body>

<header>
    <h1>Bienvenue Dr <?= htmlspecialchars($_SESSION['nom']); ?> !</h1>
</header>

<nav>
    <a href="#" id="linkRdv">
        <i class="fa-solid fa-calendar-days"></i> Rendez-vous
    </a>

    <a href="#" id="linkPatients">
        <i class="fa-solid fa-user"></i> Patients
    </a>

    <a href="logout.php">
        <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
    </a>
</nav>


<div class="container">

    <!-- Section Rendez-vous -->
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
                <div class="alert alert-warning">
                    <i class="fa-solid fa-circle-exclamation"></i>Aucun rendez-vous prévu aujourd'hui.</div>

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
                        <form method="POST" style="display:inline">
    <input type="hidden" name="id_rdv" value="<?= intval($row['id_rdv']) ?>">
    <button name="confirmer" class="btn confirm">
        <i class="fa-solid fa-check"></i> Confirmer
    </button>
</form>

<form method="POST" style="display:inline">
    <input type="hidden" name="id_rdv" value="<?= intval($row['id_rdv']) ?>">
    <button name="annuler" class="btn cancel">
        <i class="fa-solid fa-xmark"></i> Annuler
    </button>
</form>

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
                        <a href="modifier_patient.php?id=<?= intval($row['id']) ?>" class="btn">Modifier</a>
                        <a href="patient_consultation.php?id=<?= intval($row['id']) ?>" class="btn">Consulter</a>
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
const linkRdv = document.getElementById('linkRdv');
const linkPatients = document.getElementById('linkPatients');
const sectionRdv = document.getElementById('sectionRdv');
const sectionPatients = document.getElementById('sectionPatients');

// Fonction pour changer la classe active
function setActive(link) {
    document.querySelectorAll("nav a").forEach(a => a.classList.remove("active"));
    link.classList.add("active");
}

// Quand on clique sur Rendez-vous
linkRdv.addEventListener('click', function(e){
    e.preventDefault();
    sectionRdv.style.display = 'block';
    sectionPatients.style.display = 'none';
    setActive(linkRdv);
});

// Quand on clique sur Patients
linkPatients.addEventListener('click', function(e){
    e.preventDefault();
    sectionRdv.style.display = 'none';
    sectionPatients.style.display = 'block';
    setActive(linkPatients);
});

// Mettre par défaut "Rendez-vous" actif au chargement
setActive(linkRdv);
</script>



</body>
</html>
