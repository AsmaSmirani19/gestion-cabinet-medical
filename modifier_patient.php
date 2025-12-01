<?php
session_start();
$conn = new mysqli("localhost", "root", "", "cabinet_medical");
if ($conn->connect_error) die("Erreur connexion : " . $conn->connect_error);

if (!isset($_GET['id'])) die("ID patient manquant");
$id_user = intval($_GET['id']);

// Récupérer les infos du patient depuis users et patients
$sql = "
    SELECT u.id, u.nom, u.prenom, p.date_naissance, p.adresse, p.telephone, p.antecedents_medicaux
    FROM users u
    LEFT JOIN patients p ON u.id = p.id_patient
    WHERE u.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

// Mise à jour si formulaire soumis
if (isset($_POST['update_patient'])) {
    $date_naissance = $_POST['date_naissance'];
    $adresse = $_POST['adresse'];
    $telephone = $_POST['telephone'];
    $antecedents = $_POST['antecedents'];

    $update = $conn->prepare("
        UPDATE patients
        SET date_naissance=?, adresse=?, telephone=?, antecedents_medicaux=?
        WHERE id_patient=?
    ");
    $update->bind_param("ssssi", $date_naissance, $adresse, $telephone, $antecedents, $id_user);
    $update->execute();

    echo "Patient mis à jour avec succès !";
}
?>

<form method="POST">
    <label>Nom: <?= htmlspecialchars($patient['nom']) ?></label><br>
    <label>Prénom: <?= htmlspecialchars($patient['prenom']) ?></label><br><br>

    <label>Date de naissance:</label>
    <input type="date" name="date_naissance" value="<?= htmlspecialchars($patient['date_naissance']) ?>"><br><br>

    <label>Adresse:</label>
    <input type="text" name="adresse" value="<?= htmlspecialchars($patient['adresse']) ?>"><br><br>

    <label>Téléphone:</label>
    <input type="text" name="telephone" value="<?= htmlspecialchars($patient['telephone']) ?>"><br><br>

    <label>Antécédents médicaux:</label><br>
    <textarea name="antecedents"><?= htmlspecialchars($patient['antecedents_medicaux']) ?></textarea><br><br>

    <button type="submit" name="update_patient">Mettre à jour</button>
</form>
