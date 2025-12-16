<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'medecin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cabinet_medical");
if ($conn->connect_error) die("Erreur connexion : " . $conn->connect_error);

$id_patient = intval($_GET['id']);
$success_msg = "";

/* ================== AJOUT CONSULTATION ================== */
if (isset($_POST['enregistrer'])) {
    $diagnostic   = $_POST['diagnostic'];
    $prescription = $_POST['prescription'];
    $remarques    = $_POST['remarques'];
    $date         = date("Y-m-d");

    $stmt = $conn->prepare("
        INSERT INTO consultations 
        (id_patient, date_consultation, diagnostic, prescription, remarques)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $id_patient, $date, $diagnostic, $prescription, $remarques);
    $stmt->execute();
    $stmt->close();

    $success_msg = "Consultation enregistrée avec succès !";
}

/* ================== HISTORIQUE CONSULTATIONS ================== */
$sql_hist = "
    SELECT date_consultation, diagnostic, prescription, remarques
    FROM consultations
    WHERE id_patient = $id_patient
    ORDER BY date_consultation DESC
";
$result_hist = $conn->query($sql_hist);

/* ================== INFOS PATIENT ================== */
$sql_patient = "SELECT nom, prenom FROM users WHERE id = $id_patient";
$res_patient = $conn->query($sql_patient);
$patient = $res_patient->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Consultations Patient</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #b9dcff, #d9ecff);
            font-family: 'Segoe UI', sans-serif;
        }

        .form-container {
            background: #fff;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            max-width: 750px;
            width: 100%;
        }

        h2, h3 {
            text-align: center;
            color: #2d3436;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        label {
            font-weight: 600;
            color: #34495e;
        }

        input, textarea {
            padding: 12px;
            border-radius: 12px;
            border: 2px solid #e1e8ed;
            font-size: 14px;
        }

        textarea {
            min-height: 100px;
        }

        button {
            background: linear-gradient(135deg, #818cf8, #6366f1);
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            filter: brightness(1.1);
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 12px;
            margin: 15px 0;
            text-align: center;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background: #eef4ff;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        tr:hover {
            background: #f5f8ff;
        }
    </style>
</head>
<body>

<div class="form-container">

    <h2>
        <i class="fa-solid fa-stethoscope"></i>
        Consultations de <?= htmlspecialchars($patient['nom'] . ' ' . $patient['prenom']) ?>
    </h2>

    <?php if (!empty($success_msg)) : ?>
        <div class="alert-success">
            <i class="fa-solid fa-check-circle"></i> <?= $success_msg ?>
        </div>
    <?php endif; ?>

    <!-- FORMULAIRE -->
    <form method="POST">
        <label>Diagnostic</label>
        <input type="text" name="diagnostic" required>

        <label>Prescription</label>
        <input type="text" name="prescription">

        <label>Remarques</label>
        <textarea name="remarques"></textarea>

        <button type="submit" name="enregistrer">
            <i class="fa-solid fa-save"></i> Enregistrer
        </button>
    </form>

    <hr style="margin:30px 0">

    <!-- HISTORIQUE -->
    <h3><i class="fa-solid fa-history"></i> Historique</h3>

    <?php if ($result_hist->num_rows > 0) : ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Diagnostic</th>
                    <th>Prescription</th>
                    <th>Remarques</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_hist->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['date_consultation']) ?></td>
                        <td><?= htmlspecialchars($row['diagnostic']) ?></td>
                        <td><?= htmlspecialchars($row['prescription']) ?></td>
                        <td><?= htmlspecialchars($row['remarques']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p style="text-align:center;color:#666;">Aucune consultation enregistrée.</p>
    <?php endif; ?>

</div>

</body>
</html>

<?php $conn->close(); ?>
