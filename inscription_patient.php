<?php
session_start();

// Connexion à la base
$conn = new mysqli("localhost", "root", "", "cabinet_medical");
if ($conn->connect_error) die("Erreur connexion : " . $conn->connect_error);

$message = "";
$message_color = "";
$error = "";

// Gestion de l'inscription
if (isset($_POST["register"])) {
    $nom = htmlspecialchars(trim($_POST["nom"]));
    $prenom = htmlspecialchars(trim($_POST["prenom"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $password = $_POST["password"];

    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "Cet email existe déjà.";
        $message_color = "red";
    } else {
        // Hachage du mot de passe
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insert
        $stmt2 = $conn->prepare(
            "INSERT INTO users (nom, prenom, email, mot_de_passe, role) 
             VALUES (?, ?, ?, ?, 'patient')"
        );
        $stmt2->bind_param("ssss", $nom, $prenom, $email, $hashed);
        if ($stmt2->execute()) {
            $message = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
            $message_color = "green";
        } else {
            $message = "Erreur lors de l'inscription : " . $stmt2->error;
            $message_color = "red";
        }
        $stmt2->close();
    }

    $stmt->close();
}

// Gestion de la connexion
if (isset($_POST['login'])) {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];

    // Chercher l'utilisateur
    $stmt = $conn->prepare("SELECT id, nom, prenom, role, mot_de_passe FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['role'] = $user['role'];

            $pages = [
                'patient' => 'espace_patient.php',
                'medecin' => 'dashboard_medecin.php',
                'secretaire' => 'dashboard_secretaire.php'
            ];

            if (isset($pages[$user['role']])) header("Location: " . $pages[$user['role']]);
            exit();
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Email incorrect ou compte inexistant.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion / Inscription - Cabinet Médical</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {margin:0;padding:0;box-sizing:border-box;}
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #a1c4fd, #c2e9fb);
            color: #1a1a1a;
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .auth-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            min-height: calc(100vh - 40px);
        }

        .auth-card {
            display: flex;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 1600px; /* Augmentation de la largeur */
            width: 100%;
            position: relative;
        }

        .container {
            width: 50%;
            min-height: 750px; /* Augmentation de la hauteur */
            background: #fff;
            border-radius: 25px;
            box-shadow: 0 0 30px rgba(0,0,0,0.2);
            overflow: hidden;
            position: relative;
            transition: transform 0.9s ease-in-out;
        }

        .form-wrapper {
            display: flex;
            width: 200%;
            transition: transform 0.6s ease;
        }

        .form-wrapper.login-active {
            transform: translateX(0);
        }

        .form-wrapper.register-active {
            transform: translateX(-50%);
        }

        .form-section {
            width: 50%;
            padding: 60px; /* Augmentation du padding */
            box-sizing: border-box;
        }

        .form-section h2 {
            text-align: center;
            margin-bottom: 35px;
            color: #2d3436;
            font-size: 2.5rem; /* Augmentation de la taille */
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .form-section h2 i {
            color: rgba(111,177,252,0.75);
            font-size: 2.8rem;
        }

        .input-group {
            margin-bottom: 30px; /* Augmentation de l'écart */
        }

        .input-group label {
            display: block;
            margin-bottom: 10px;
            color: #34495e;
            font-weight: 600;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .input-group label i {
            color: rgba(111,177,252,0.75);
        }

        .input-group input {
            width: 100%;
            padding: 18px; /* Augmentation du padding */
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
            box-sizing: border-box;
            background: #f8f9fa;
        }

        .input-group input:focus {
            border-color: rgba(111,177,252,0.75);
            box-shadow: 0 0 12px rgba(111,177,252,0.5);
            outline: none;
            background: #fff;
        }

        button[type="submit"] {
            width: 100%;
            padding: 20px; /* Augmentation du padding */
            background: linear-gradient(135deg, #818cf8, #6366f1);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px; /* Augmentation de la taille */
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: 0 6px 20px rgba(99,102,241,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        button[type="submit"]:hover {
            background: linear-gradient(135deg, #6366f1, #4c1d95);
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(99,102,241,0.6);
        }

        .message, .error-message {
            text-align: center;
            margin-top: 20px;
            font-weight: 500;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .message {
            color: green;
        }

        .error-message {
            color: red;
        }

        .login-link, .register-link {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 16px;
        }

        .login-link a, .register-link a {
            color: rgba(111,177,252,0.75);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .login-link a:hover, .register-link a:hover {
            color: #6366f1;
            text-decoration: underline;
        }

        .image-space {
            width: 50%;
            min-height: 750px; /* Augmentation de la hauteur */
            background:
                linear-gradient(135deg, rgba(111,177,252,0.75), rgba(79,140,255,0.75)),
                url("image/ins.jpg") center center no-repeat;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            position: relative;
            transition: transform 0.9s ease-in-out;
        }

        .image-space::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.2);
            z-index: 1;
        }
        

        .image-overlay {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .image-overlay h3 {
            font-size: 4rem; /* Augmentation de la taille */
            font-family:  'Cinzel', serif;
            margin-bottom: 20px;
            text-shadow: 2px 2px 6px rgba(0,0,0,0.5);
            font-weight: 650;
        }

        .image-overlay p {
            font-size: 1.5rem; /* Augmentation de la taille */
            font-family: 'Cinzel', serif;
            opacity: 1;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.69);
            max-width: 400px;
            line-height: 1.6;

            white-space: nowrap;   /* empêche le retour à la ligne */
            max-width: none;
        }

        .highlight {
            background: linear-gradient(90deg, #4a38ecff, #b44db9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
            font-size: 3.2rem;
            animation: shine 3s infinite linear;
        }

        @keyframes shine {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @media (max-width: 1200px) {
            .auth-card {
                flex-direction: column;
            }
            .container {
                width: auto;
                height: auto;
                margin: 10px;
            }
            .form-wrapper {
                flex-direction: column;
                width: 100%;
            }
            .form-section {
                width: 100%;
                padding: 50px;
                flex: 1.3;
            }
            .image-space {
                min-height: 600px;
                padding: 20px;
            }
            .image-overlay h3 {
                font-size: 2.5rem;
            }
            .image-overlay p {
                font-size: 1.2rem;
            }
        }

        .auth-card.register-active .container {
            transform: translateX(100%);
        }

        .auth-card.register-active .image-space {
            transform: translateX(-100%);
        }

        .container,
        .image-space {
            transition: transform 0.8s ease, opacity 0.6s ease;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card" id="authCard">
        <div class="container">
            <div class="form-wrapper login-active" id="formWrapper">
                <!-- FORMULAIRE LOGIN -->
                <div class="form-section" id="loginForm">
                    <h2><i class="fa-solid fa-sign-in-alt"></i> Connexion</h2>
                    <form action="" method="POST">
                        <div class="input-group">
                            <label for="email"><i class="fa-solid fa-envelope"></i> Email :</label>
                            <input type="email" name="email" id="email" placeholder="Entrez votre email" required>
                        </div>
                        <div class="input-group">
                            <label for="password"><i class="fa-solid fa-lock"></i> Mot de passe :</label>
                            <input type="password" name="password" id="password" placeholder="Entrez votre mot de passe" required>
                        </div>
                        <button type="submit" name="login"><i class="fa-solid fa-arrow-right"></i> Se connecter</button>
                        <?php if(isset($error) && $error != ""): ?>
                            <p class="error-message"><i class="fa-solid fa-exclamation-triangle"></i> <?php echo $error; ?></p>
                        <?php endif; ?>
                        <p class="register-link">Pas encore de compte ? <a href="#" onclick="switchToRegister(); return false;"><i class="fa-solid fa-user-plus"></i> Inscrivez-vous ici</a></p>
                    </form>
                </div>

                <!-- FORMULAIRE INSCRIPTION -->
                <div class="form-section" id="registerForm">
                    <h2><i class="fa-solid fa-user-plus"></i> Inscription Patient</h2>
                    <form action="" method="POST">
                        <div class="input-group">
                            <label for="nom"><i class="fa-solid fa-user"></i> Nom :</label>
                            <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required>
                        </div>
                        <div class="input-group">
                            <label for="prenom"><i class="fa-solid fa-user"></i> Prénom :</label>
                            <input type="text" id="prenom" name="prenom" placeholder="Entrez votre prénom" required>
                        </div>
                        <div class="input-group">
                            <label for="email_reg"><i class="fa-solid fa-envelope"></i> Email :</label>
                            <input type="email" id="email_reg" name="email" placeholder="Entrez votre email" required>
                        </div>
                        <div class="input-group">
                            <label for="password_reg"><i class="fa-solid fa-lock"></i> Mot de passe :</label>
                            <input type="password" id="password_reg" name="password" placeholder="Choisissez un mot de passe" required>
                        </div>
                        <button type="submit" name="register"><i class="fa-solid fa-check"></i> S'inscrire</button>
                        <?php if(!empty($message)): ?>
                            <p class="message" style="color: <?= $message_color ?>;">
                                <i class="fa-solid fa-<?= $message_color == 'green' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                                <?= $message ?>
                            </p>
                        <?php endif; ?>
                        <p class="login-link">Avez-vous déjà un compte ? <a href="#" onclick="switchToLogin(); return false;"><i class="fa-solid fa-sign-in-alt"></i> Connectez-vous ici</a></p>
                    </form>
                </div>
            </div>
        </div>

        <!-- IMAGE SPACE -->
        <div class="image-space">
            <div class="image-overlay">
                <h3>Bienvenue au <span class="highlight">Cabinet Médical</span></h3>
                <p>Prenez soin de votre santé avec nos services modernes et personnalisés. <br> Inscrivez-vous ou connectez-vous pour accéder à votre espace dédié.</p>
            </div>
        </div>
    </div>
</div>

<script>
const formWrapper = document.getElementById('formWrapper');
const authCard = document.getElementById('authCard');

function switchToRegister() {
    formWrapper.classList.remove('login-active');
    formWrapper.classList.add('register-active');

    authCard.classList.remove('login-active');
    authCard.classList.add('register-active');
}

function switchToLogin() {
    formWrapper.classList.remove('register-active');
    formWrapper.classList.add('login-active');

    authCard.classList.remove('register-active');
    authCard.classList.add('login-active');
}
</script>

</body>
</html>
