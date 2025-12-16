<?php
session_start();

// Connexion à la base
$conn = new mysqli("localhost", "root", "", "cabinet_medical");
if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}

if (isset($_SESSION['role']) && $_SESSION['role'] === 'medecin') {
    header("Location: dashboard_medecin.php");
    exit();
}
// Vérifier la soumission du formulaire
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_input = trim($_POST['password']);

    // Récupérer l'ID, le nom et le mot de passe hashé du médecin depuis la BDD
    $stmt = $conn->prepare("SELECT id, nom, mot_de_passe FROM users WHERE role='medecin' LIMIT 1");
    $stmt->execute();
    $stmt->bind_result($medecin_id, $nom, $hash_db);
    $stmt->fetch();
    $stmt->close();

    if ($hash_db && password_verify($password_input, $hash_db)) {
        // Mot de passe correct → stocker les infos réelles dans la session
        $_SESSION['id']   = $medecin_id;
        $_SESSION['nom']  = $nom;
        $_SESSION['role'] = 'medecin';

        header("Location: dashboard_medecin.php");
        exit();

    } else {
        $error = "Mot de passe incorrect !";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Médecin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #a1c4fd, #c2e9fb);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            padding: 60px;
            border-radius: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        .login-box h2 {
            color: #2d3436;
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
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
        form input[type="password"] {
            padding: 12px 15px;
            border-radius: 12px;
            border: 2px solid #e1e8ed;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
            transition: 0.3s ease;
            background: #f8f9fa;
        }
        form input[type="password"]:focus {
            border-color: rgba(111,177,252,0.75);
            outline: none;
            box-shadow: 0 0 10px rgba(111,177,252,0.4);
            background: #fff;
        }
        .btn-login {
            background: linear-gradient(135deg, #818cf8, #6366f1);
            box-shadow: 0 6px 15px rgba(99,102,241,0.35);
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
        .btn-login:hover {
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
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        .alert i { font-size: 18px; }
        @media (max-width: 768px) {
            .login-box { padding: 30px 20px; }
            form { gap: 15px; }
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2><i class="fa-solid fa-user-md"></i> Connexion Médecin</h2>
        <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Mot de passe :</label>
                <input type="password" name="password" id="password" placeholder="Entrez votre mot de passe" required>
            </div>
            <button type="submit" class="btn-login">
                <i class="fa-solid fa-sign-in-alt"></i> Se connecter
            </button>
        </form>
    </div>
</body>
</html>