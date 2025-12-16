<?php
session_start();

$conn = new mysqli("localhost", "root", "", "cabinet_medical");
if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("ID patient manquant");
}

$id_user = (int) $_GET['id'];

/* ===============================
   RÉCUPÉRATION DES DONNÉES
   =============================== */
$sql = "
    SELECT 
        u.id,
        u.nom,
        u.prenom,
        p.date_naissance,
        p.adresse,
        p.telephone,
        p.antecedents_medicaux
    FROM users u
    LEFT JOIN patients p ON u.id = p.id_patient
    WHERE u.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

if (!$patient) {
    die("Patient introuvable.");
}

/* ===============================
   UPDATE UNIQUEMENT (PAS D’INSERT)
   =============================== */
if (isset($_POST['update_patient'])) {

    $date_naissance = !empty($_POST['date_naissance']) ? $_POST['date_naissance'] : NULL;
    $adresse        = !empty($_POST['adresse']) ? $_POST['adresse'] : NULL;
    $telephone      = !empty($_POST['telephone']) ? $_POST['telephone'] : NULL;
    $antecedents    = !empty($_POST['antecedents_medicaux']) ? $_POST['antecedents_medicaux'] : NULL;

    $sql = "
        UPDATE patients
        SET
            date_naissance = ?,
            adresse = ?,
            telephone = ?,
            antecedents_medicaux = ?
        WHERE id_patient = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssi",
        $date_naissance,
        $adresse,
        $telephone,
        $antecedents,
        $id_user
    );

    if ($stmt->execute()) {
        echo "<div style='color:green;'>Patient mis à jour avec succès !</div>";
    } else {
        echo "<div style='color:red;'>Erreur : " . $stmt->error . "</div>";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Patient</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style_d.css?v=7"> <!-- Lien vers le CSS principal pour cohérence -->
    <style>
        /* --- FORMULAIRE PATIENT UPGRADÉ --- */
        .form-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            max-width: 750px;
            margin: 0 auto;
        }

        .form-container h2 {
            text-align: center;
            color: #2d3436;
            margin-bottom: 25px;
            font-weight: 600;
        }

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

        form input[type="text"],
        form input[type="date"],
        form textarea {
            padding: 12px 15px;
            border-radius: 12px;
            border: 2px solid #e1e8ed;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
            transition: 0.3s ease;
            background: #f8f9fa;
        }

        form input[type="text"]:focus,
        form input[type="date"]:focus,
        form textarea:focus {
            border-color: rgba(111,177,252,0.75);
            outline: none;
            box-shadow: 0 0 10px rgba(111,177,252,0.4);
            background: #fff;
        }

        form textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .readonly-info {
            background: rgba(79,140,255,0.1);
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid rgba(79,140,255,0.2);
            color: #34495e;
            font-weight: 500;
        }

        .btn-update {
            background: linear-gradient(135deg, #818cf8, #6366f1); /* Style similaire à btn-manage */
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

        .btn-update:hover {
            filter: brightness(1.08);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        }

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

        .alert i {
            font-size: 18px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
                margin: 20px;
            }

            form {
                gap: 15px;
            }
        }

        .container {
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #b9dcff, #d9ecff);
        }


    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <h2><i class="fa-solid fa-user-edit"></i> Modifier Patient</h2>
        
        <div class="readonly-info">
            <strong>Nom :</strong> <?= htmlspecialchars($patient['nom']) ?> <br>
            <strong>Prénom :</strong> <?= htmlspecialchars($patient['prenom']) ?>
        </div> <br>

        <form method="POST">
            
            <div class="form-group">
                <label><i class="fa-solid fa-calendar"></i> Date de naissance :</label>
                <input type="date" name="date_naissance" value="<?= htmlspecialchars($patient['date_naissance']) ?>" required>
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-map-marker-alt"></i> Adresse :</label>
                <input type="text" name="adresse" value="<?= htmlspecialchars($patient['adresse']) ?>" required>
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-phone"></i> Téléphone :</label>
                <input type="text" name="telephone" value="<?= htmlspecialchars($patient['telephone']) ?>" required>
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-notes-medical"></i> Antécédents médicaux :</label>
                <textarea name="antecedents_medicaux" placeholder="Entrez les antécédents médicaux..."><?= htmlspecialchars($patient['antecedents_medicaux']) ?></textarea>
            </div>

            <button type="submit" name="update_patient" class="btn-update">
                <i class="fa-solid fa-save"></i> Mettre à jour
            </button>
        </form>
    </div>
</div>

</body>
</html>
