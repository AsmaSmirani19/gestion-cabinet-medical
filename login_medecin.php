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
            margin: 0;
        }
        .login-box {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(15px);
            padding: 80px 60px; /* Augmentation du padding pour agrandir */
            border-radius: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 700px; /* Augmentation de la largeur max */
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        .login-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #818cf8, #6366f1);
        }
        .login-box h2 {
            color: #2d3436;
            margin-bottom: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            font-size: 2rem; /* Augmentation de la taille de police */
        }
        .login-box h2 i {
            font-size: 2.5rem;
            color: rgba(111,177,252,0.75);
        }
        .welcome-text {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 25px; /* Augmentation de l'écart */
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        form label {
            font-weight: 600;
            color: #34495e;
            font-size: 16px; /* Augmentation de la taille de police */
            display: flex;
            align-items: center;
            gap: 10px;
        }
        form label i {
            color: rgba(111,177,252,0.75);
            font-size: 18px;
        }
        form input[type="password"] {
            padding: 15px 20px; /* Augmentation du padding */
            border-radius: 15px;
            border: 2px solid #e1e8ed;
            font-size: 16px; /* Augmentation de la taille de police */
            width: 100%;
            box-sizing: border-box;
            transition: 0.3s ease;
            background: #f8f9fa;
        }
        form input[type="password"]:focus {
            border-color: rgba(111,177,252,0.75);
            outline: none;
            box-shadow: 0 0 15px rgba(111,177,252,0.5);
            background: #fff;
        }
        .btn-login {
            background: linear-gradient(135deg, #818cf8, #6366f1);
            box-shadow: 0 8px 20px rgba(99,102,241,0.4);
            color: white;
            padding: 15px 30px; /* Augmentation du padding */
            border-radius: 15px;
            border: none;
            cursor: pointer;
            transition: 0.3s ease;
            font-size: 18px; /* Augmentation de la taille de police */
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            align-self: center;
            margin-top: 10px;
        }
        .btn-login:hover {
            filter: brightness(1.1);
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        .alert {
            padding: 18px 25px; /* Augmentation du padding */
            border-radius: 15px;
            font-size: 16px; /* Augmentation de la taille de police */
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 6px solid #dc3545;
        }
        .alert i { font-size: 20px; }
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }
        .forgot-password a {
            color: rgba(111,177,252,0.75);
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }
        .forgot-password a:hover {
            color: #6366f1;
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .login-box { 
                padding: 50px 30px; 
                max-width: 90%;
            }
            .login-box h2 { font-size: 1.8rem; }
            form { gap: 20px; }
            .btn-login { font-size: 16px; }
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2><i class="fa-solid fa-user-md"></i> Connexion Médecin</h2>
        <p class="welcome-text">Bienvenue dans votre espace sécurisé. Veuillez entrer votre mot de passe pour accéder à votre tableau de bord.</p>
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
