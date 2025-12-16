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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* --- RESET ET BODY --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #a1c4fd, #c2e9fb);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* --- NAV --- */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 14px 0;
            display: flex;
            justify-content: center;
            gap: 30px;
            background: rgba(111,177,252,0.75);
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.4);
            z-index: 1000;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 12px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        nav a:hover {
            background: rgba(147, 197, 253, 0.25);
        }

        nav a.active {
            border: 2px solid rgba(255,255,255,0.7);
        }

        /* --- CONTAINER --- */
        .container {
            margin-top: 100px;
            width: 90%;
            max-width: 750px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            display: none;
        }

        .container.active {
            display: block;
        }

        /* --- TITRES --- */
        h2 {
            text-align: center;
            color: #2d3436;
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* --- TABLE --- */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-top: 20px;
        }

        table thead tr {
            background: rgba(79,140,255,0.1);
            color: #34495e;
            font-weight: 600;
        }

        table thead th {
            padding: 14px;
            font-size: 15px;
        }

        table tbody tr {
            border-bottom: 1px solid #eee;
        }

        table td {
            padding: 12px;
            font-size: 14px;
            color: #333;
        }

        table tbody tr:hover {
            background: #f2f6ff;
            transition: 0.2s;
        }

        /* --- FORMULAIRE --- */
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        form label {
            font-weight: 600;
            color: #34495e;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        form label i {
            color: rgba(111,177,252,0.75);
        }

        form input[type="date"],
        form input[type="time"] {
            padding: 12px 15px;
            border-radius: 12px;
            border: 2px solid #e1e8ed;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
            transition: 0.3s ease;
            background: #f8f9fa;
        }

        form input[type="date"]:focus,
        form input[type="time"]:focus {
            border-color: rgba(111,177,252,0.75);
            outline: none;
            box-shadow: 0 0 10px rgba(111,177,252,0.4);
            background: #fff;
        }

        .btn-submit {
            background: linear-gradient(135deg, #818cf8, #6366f1);
            box-shadow: 0 6px 15px rgba(99, 102, 241, 0.35);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: 0.3s ease;
            font-size: 16px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            align-self: center;
        }

        .btn-submit:hover {
            filter: brightness(1.08);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        }

        /* --- ALERT --- */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }

        .alert i {
            font-size: 18px;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            nav {
                gap: 15px;
                padding: 10px 0;
            }

            nav a {
                padding: 8px 15px;
                font-size: 14px;
            }

            .container {
                padding: 20px;
                margin-top: 80px;
            }

            form {
                gap: 15px;
            }

            table {
                font-size: 12px;
            }

            table th, table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>

<nav>
    <a id="linkRdv" class="active"><i class="fa-solid fa-calendar-days"></i> Mes rendez-vous</a>
    <a id="linkPrendre"><i class="fa-solid fa-calendar-plus"></i> Prendre un rendez-vous</a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
</nav>

<div class="container section active" id="sectionRdv">
    <h2><i class="fa-solid fa-calendar-check"></i> Mes rendez-vous</h2>
    <?php if (!empty($message)): ?>
        <div class="alert <?php echo ($message_color == 'green') ? 'alert-success' : 'alert-danger'; ?>">
            <i class="fa-solid <?php echo ($message_color == 'green') ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
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
                    <td>
                        <?php
                        $statut = htmlspecialchars($row['statut']);
                        if ($statut == 'en_attente') {
                            echo '<span style="color:orange;font-weight:bold;">⏳ En attente</span>';
                        } elseif ($statut == 'confirme') {
                            echo '<span style="color:green;font-weight:bold;">✔ Confirmé</span>';
                        } else {
                            echo '<span style="color:red;font-weight:bold;">✖ Annulé</span>';
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; color: #666;">Aucun rendez-vous prévu.</p>
    <?php endif; ?>
</div>

<div class="container section" id="sectionPrendre">
    <h2><i class="fa-solid fa-calendar-plus"></i> Prendre un nouveau rendez-vous</h2>
    <?php if (!empty($message)): ?>
        <div class="alert <?php echo ($message_color == 'green') ? 'alert-success' : 'alert-danger'; ?>">
            <i class="fa-solid <?php echo ($message_color == 'green') ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="date"><i class="fa-solid fa-calendar"></i> Date :</label>
            <input type="date" name="date" id="date" required>
        </div>
        <div class="form-group">
            <label for="heure"><i class="fa-solid fa-clock"></i> Heure :</label>
            <input type="time" name="heure" id="heure" required>
        </div>
        <button type="submit" name="rdv" class="btn-submit">
            <i class="fa-solid fa-paper-plane"></i> Envoyer
        </button>
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
