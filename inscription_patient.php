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
    <link rel = "stylesheet" href ="style_p.css">
    <style>
        * {margin:0;padding:0;box-sizing:border-box;}
       body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1a1a1a;
            line-height: 1.6;

            display: flex;
            justify-content: center; /* horizontal centering */
            align-items: center;     /* vertical centering */
            min-height: 100vh;

            padding: 20px; /* espace autour */
        }

       /* Wrapper centré */
            .auth-wrapper {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100%;
                min-height: calc(100vh - 40px); /* laisse un petit espace en haut et bas */
            }


       .auth-card {
            display: flex;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            overflow: hidden;
            max-width: 1400px;
            width: 100%;
            position: relative;
        }


            .container { 
                width: 50%;              /* IMPORTANT */
                min-height: 650px;
                background: #fff;

                border-radius: 30px;
                box-shadow: 0 0 30px rgba(0,0,0,0.2);
                overflow: hidden;

                position: relative;
                transition: transform 0.9s ease-in-out;
            }


        .form-wrapper {
            display: flex;
            width: 200%;
            transition: transform 0.5s ease;
        }

        .form-wrapper.login-active {
            transform: translateX(0);
        }

        .form-wrapper.register-active {
            transform: translateX(-50%);
        }

        .form-section {
            width: 50%;
            padding: 50px; /* Agrandi de 40px à 50px */
            box-sizing: border-box;
        }

        .form-section h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #00285a;
            font-size: 2.2rem; /* Agrandi de 2rem à 2.2rem */
        }

        .input-group {
            margin-bottom: 25px; /* Agrandi de 20px à 25px */
        }

        .input-group label {
            display: block;
            margin-bottom: 8px; /* Agrandi de 5px à 8px */
            color: #555;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 15px; /* Agrandi de 12px à 15px */
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1.1rem; /* Agrandi de 1rem à 1.1rem */
            transition: border-color 0.3s, box-shadow 0.3s;
            box-sizing: border-box;
        }

        .input-group input:focus {
            border-color: #4fc3f7;
            box-shadow: 0 0 10px rgba(79, 195, 247, 0.3);
            outline: none;
        }

        button[type="submit"] {
            width: 100%;
            padding: 18px; /* Agrandi de 15px à 18px */
            background: linear-gradient(45deg, #4fc3f7, #29b6f6);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem; /* Agrandi de 1.1rem à 1.2rem */
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: 0 4px 15px rgba(79, 195, 247, 0.4);
        }

        button[type="submit"]:hover {
            background: linear-gradient(45deg, #29b6f6, #0277bd);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(79, 195, 247, 0.6);
        }

        .message, .error-message {
            text-align: center;
            margin-top: 15px;
            font-weight: 500;
        }

        .message {
            color: green;
        }

        .error-message {
            color: red;
        }

        .login-link, .register-link {
            text-align: center;
            margin-top: 25px; /* Agrandi de 20px à 25px */
            color: #555;
        }

        .login-link a, .register-link a {
            color: #4fc3f7;
            text-decoration: none;
            transition: color 0.3s;
        }

        .login-link a:hover, .register-link a:hover {
            color: #0277bd;
        }

       .image-space { 
    width: 50%;              /* IMPORTANT */
    min-height: 750px;
    background: 
        linear-gradient(135deg, rgba(0,40,90,0.6), rgba(0,100,150,0.6)),
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
            background: rgba(0,0,0,0.15);
            z-index: 1;
        }

        .image-overlay {
            position: relative;
            z-index: 2;
        }

        .image-overlay h3 {
            font-size: 2.5rem; /* Agrandi de 2rem à 2.5rem */
            margin-bottom: 15px; /* Agrandi de 10px à 15px */
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .image-overlay p {
            font-size: 1.2rem; /* Agrandi de 1.1rem à 1.2rem */
            opacity: 0.9;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
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
                padding: 40px; /* Agrandi de 30px à 40px */
                flex: 1.3;
            }
            .image-space {
                min-height: 700px; /* Agrandi de 620px à 700px */
                padding: 20px;
            }
            .image-overlay h3 {
                font-size: 2rem; /* Agrandi de 1.5rem à 2rem */
            }
            .image-overlay p {
                font-size: 1.1rem; /* Agrandi de 1rem à 1.1rem */
            }
        }

        /* ===== SLIDE ANIMATION ===== */
.auth-card.register-active .container {
    transform: translateX(100%);
}

.auth-card.register-active .image-space {
    transform: translateX(-100%);
}

.highlight {
    background: linear-gradient(90deg, #4fc3f7, #29b6f6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: bold;
    font-size: 2.8rem;
    animation: shine 2s infinite linear;
}

/* Animation légère pour l'effet "shine" */
@keyframes shine {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}



    /* Animation douce */
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
                    <h2>Connexion</h2>
                    <form action="" method="POST">
                        <div class="input-group">
                            <label for="email">Email :</label>
                            <input type="email" name="email" id="email" required>
                        </div>
                        <div class="input-group">
                            <label for="password">Mot de passe :</label>
                            <input type="password" name="password" id="password" required>
                        </div>
                        <button type="submit" name="login">Se connecter</button>
                        <?php if(isset($error) && $error != ""): ?>
                            <p class="error-message"><?php echo $error; ?></p>
                        <?php endif; ?>
                        <p class="register-link">Pas encore de compte ? <a href="#" onclick="switchToRegister(); return false;">Inscrivez-vous ici</a> </p>
                    </form>
                </div>

                <!-- FORMULAIRE INSCRIPTION -->
                <div class="form-section" id="registerForm">
                    <h2>Inscription Patient</h2>
                    <form action="" method="POST">
                        <div class="input-group">
                            <label for="nom">Nom :</label>
                            <input type="text" id="nom" name="nom" required>
                        </div>
                        <div class="input-group">
                            <label for="prenom">Prénom :</label>
                            <input type="text" id="prenom" name="prenom" required>
                        </div>
                        <div class="input-group">
                            <label for="email_reg">Email :</label>
                            <input type="email" id="email_reg" name="email" required>
                        </div>
                        <div class="input-group">
                            <label for="password_reg">Mot de passe :</label>
                            <input type="password" id="password_reg" name="password" required>
                        </div>
                        <button type="submit" name="register">S'inscrire</button>
                        <?php if(!empty($message)): ?>
                            <p class="message" style="color: <?= $message_color ?>;">
                                <?= $message ?>
                            </p>
                        <?php endif; ?>
                        <p class="login-link"> Avez-vous déjà un compte ? <a href="#" onclick="switchToLogin(); return false;">Connectez-vous ici</a></p>
                    </form>
                </div>
            </div>
        </div>

        <!-- IMAGE SPACE -->
        <div class="image-space">
            <div class="image-overlay">
                <h3>Bienvenue <span class="highlight"></span></h3>
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
