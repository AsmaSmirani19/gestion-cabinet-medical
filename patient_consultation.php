<?php
session_start();
if(!isset($_SESSION['id']) || $_SESSION['role'] !== 'medecin'){
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cabinet_medical");
if($conn->connect_error) die("Erreur connexion : ".$conn->connect_error);

$id_patient = intval($_GET['id']);

// Ajouter une consultation
if(isset($_POST['enregistrer'])){
    $diagnostic = $_POST['diagnostic'];
    $prescription = $_POST['prescription'];
    $remarques = $_POST['remarques'];
    $date = date("Y-m-d");

    $stmt = $conn->prepare("INSERT INTO consultations (id_patient, date_consultation, diagnostic, prescription, remarques) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $id_patient, $date, $diagnostic, $prescription, $remarques);
    $stmt->execute();
    $stmt->close();
}

// Récupérer l'historique des consultations
$sql_hist = "SELECT * FROM consultations WHERE id_patient=$id_patient ORDER BY date_consultation DESC";
$result_hist = $conn->query($sql_hist);

// Récupérer infos patient
$sql_patient = "SELECT nom, prenom FROM users WHERE id=$id_patient";
$res_patient = $conn->query($sql_patient);
$patient = $res_patient->fetch_assoc();
?>

<h2>Consultations de <?= htmlspecialchars($patient['nom'].' '.$patient['prenom']) ?></h2>

<!-- Formulaire ajout consultation -->
<form method="POST">
    <label>Diagnostic :</label><br>
    <input type="text" name="diagnostic" required><br>
    <label>Prescription :</label><br>
    <input type="text" name="prescription"><br>
    <label>Remarques :</label><br>
    <textarea name="remarques"></textarea><br>
    <button name="enregistrer">Enregistrer</button>
</form>

<hr>

<!-- Historique des consultations -->
<h3>Historique des consultations</h3>
<?php
if($result_hist->num_rows > 0){
    echo "<table>
            <tr><th>Date</th><th>Diagnostic</th><th>Prescription</th><th>Remarques</th></tr>";
    while($row = $result_hist->fetch_assoc()){
        echo "<tr>
                <td>".htmlspecialchars($row['date_consultation'])."</td>
                <td>".htmlspecialchars($row['diagnostic'])."</td>
                <td>".htmlspecialchars($row['prescription'])."</td>
                <td>".htmlspecialchars($row['remarques'])."</td>
              </tr>";
    }
    echo "</table>";
}else{
    echo "<p>Aucune consultation enregistrée.</p>";
}
$conn->close();
?>
