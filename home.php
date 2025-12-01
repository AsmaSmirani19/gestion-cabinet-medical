<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cabinet Médical</title>
    <style>
        /* Style global */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        /* Conteneur principal */
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 20px;
        }

        /* Titre */
        h1 {
            color: #1f3d7a; /* bleu foncé */
            font-size: 3rem;
            margin-bottom: 10px;
        }

        /* Texte descriptif */
        p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        /* Boutons */
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            font-size: 1rem;
            font-weight: bold;
            color: white;
            background-color: #2aa3f0; /* bleu style Maiia */
            border: none;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn:hover {
            background-color: #1f7acb;
            transform: translateY(-3px);
        }

        /* Footer simple */
        footer {
            margin-top: 50px;
            font-size: 0.9rem;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bienvenue au Cabinet Médical</h1>
        <p>Connectez-vous selon votre rôle pour accéder à votre espace.</p>

        <a href="inscription_patient.php" class="btn">Patient</a>
        <a href="login_secretaire.php" class="btn">Secrétaire</a>
        <a href="dashboard_medecin.php" class="btn">Médecin</a>

        <footer>
            &copy; 2025 Cabinet Médical. Tous droits réservés.
        </footer>
    </div>
</body>
</html>
