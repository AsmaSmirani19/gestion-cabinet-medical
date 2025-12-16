<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Cabinet Médical</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Plateforme médicale moderne pour patients, secrétaires et médecins">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* ===================== VARIABLES ===================== */
:root{
    --accent:#f9a825;
    --dark:#003b44;
    --light:#f7fbfc;
    --primary:#4f8cff; /* remplace le vert */
    --secondary:#6fb1fc; /* bleu secondaire doux */
}

/* ===================== BASE ===================== */
*{box-sizing:border-box;margin:0;padding:0}
body{
    font-family:'Segoe UI',sans-serif;
    background:#fff;
    color:#333;
}

/* ===================== HEADER ===================== */
header{
    position:fixed;
    top:0;
    width:100%;
    padding:15px 8%;
    background:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
    z-index:1000;
}

.logo{
    font-size:1.5rem;
    font-weight:700;
    color:var(--primary);
}

nav a{
    margin-left:25px;
    text-decoration:none;
    color:#444;
    font-weight:500;
}

nav a.btn{
    background:var(--primary);
    color:white;
    padding:8px 18px;
    border-radius:25px;
}

/* ===================== HERO ===================== */
.hero{
    min-height:50vh;          /* AVANT: 85vh */
    padding:110px 8% 40px;    /* AVANT: 140px 8% 60px */

    background:
        linear-gradient(
            rgba(79,140,255,0.75),
            rgba(111,177,252,0.75)
        ),
        url("image/back.jpg") center / cover no-repeat;

    color:white;
    text-align:center;
    border-bottom-left-radius:60px;
    border-bottom-right-radius:60px;

    display:flex;
    align-items:center;
    justify-content:center;
}

.hero h1{
    font-size:3rem;
    margin-bottom:15px;
}

.hero p{
    font-size:1.2rem;
    opacity:.95;
}

/* ===================== SEARCH BAR ===================== */
.search-bar{
    margin:40px auto 0;
    background:white;
    border-radius:50px;
    display:flex;
    padding:10px;
    max-width:700px;
    box-shadow:0 20px 50px rgba(0,0,0,.2);
}

.search-bar input{
    flex:1;
    border:none;
    outline:none;
    padding:12px 15px;
    font-size:1rem;
}

.search-bar button{
    background:var(--accent);
    border:none;
    color:white;
    padding:12px 30px;
    border-radius:40px;
    font-size:1rem;
    cursor:pointer;
}

/* ===================== SERVICES ===================== */
.services{
    padding:90px 8%;
    background:var(--light);
    text-align:center;
}

.services h2{
    font-size:2.2rem;
    margin-bottom:50px;
    color:var(--dark);
}

.services-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(230px,1fr));
    gap:30px;
}

.service-card{
    background:white;
    padding:35px 25px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    transition:.3s;
}

.service-card.active{
    background:var(--primary);
    color:white;
}

.service-card i{
    font-size:40px;
    margin-bottom:15px;
}

.service-card h3{
    margin-bottom:10px;
}

.service-card:hover{
    transform:translateY(-10px);
}

/* ===================== ROLES ===================== */
.roles{
    padding:90px 8%;
    text-align:center;
}

.roles h2{
    font-size:2.2rem;
    margin-bottom:50px;
    color:var(--dark);
}

.roles-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:40px;
}

.role-card{
    background:white;
    border-radius:25px;
    padding:40px 30px;
    box-shadow:0 15px 40px rgba(0,0,0,.1);
    transition:.4s;
    text-decoration:none;
    color:#333;
}

.role-card i{
    font-size:55px;
    color:var(--primary);
    margin-bottom:20px;
}

.role-card:hover{
    transform:translateY(-12px);
    background: #4f8cff;  /* bleu doux */
    color:white;
}

.role-card:hover i{
    color:white;
}

/* ===================== À PROPOS ===================== */
.about{
    padding:90px 8%;
    background:white;
    text-align:center;
}

.about h2{
    font-size:2.2rem;
    margin-bottom:50px;
    color:var(--dark);
}

.about-content{
    max-width:800px;
    margin:0 auto;
    line-height:1.8;
    font-size:1.1rem;
}

.contact-info{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:30px;
    margin-top:50px;
}

.contact-item{
    background:var(--light);
    padding:30px;
    border-radius:15px;
    box-shadow:0 10px 30px rgba(0,0,0,.05);
}

.contact-item i{
    font-size:40px;
    color:var(--primary);
    margin-bottom:15px;
}

.contact-item h3{
    margin-bottom:10px;
    color:var(--dark);
}

/* ===================== FOOTER ===================== */
footer{
    background:#002f36;
    color:white;
    text-align:center;
    padding:30px;
}

/* ===================== RESPONSIVE ===================== */
@media(max-width:768px){
    .hero h1{font-size:2.2rem}
    header{padding:15px 5%}
    .contact-info{grid-template-columns:1fr;}
}
</style>
</head>

<body>

<!-- HERO -->
<section class="hero">
    <div>
        <h1>Cabinet Médical</h1>
        <p>Prenez soin de votre santé avec nos services modernes et personnalisés.</p>
    </div>
</section>

<!-- ROLES -->
<section class="roles">
    <h2>Accès aux espaces</h2>

    <div class="roles-grid">
        <a href="inscription_patient.php" class="role-card">
            <i class="fa-solid fa-user"></i>
            <h3>Espace Patient</h3>
            <p>Consultation du dossier médical, rendez-vous et suivi personnalisé</p>
        </a>

        <a href="login_secretaire.php" class="role-card">
            <i class="fa-solid fa-calendar-days"></i>
            <h3>Espace Secrétariat Médical</h3>
            <p>Gestion des rendez-vous, patients et planning médical</p>
        </a>

        <a href="login_medecin.php" id="openMedLogin" class="role-card">
            <i class="fa-solid fa-stethoscope"></i>
            <h3>Espace Médecin</h3>
            <p>Consultations, diagnostics et dossiers patients</p>
        </a>
    </div>
</section>

<!-- À PROPOS -->
<section class="about">
    <h2>À Propos de Nous</h2>
    <div class="about-content">
        <p>Notre cabinet médical est dédié à fournir des soins de santé de qualité supérieure, en mettant l'accent sur la prévention, le diagnostic précis et le traitement personnalisé. Avec une équipe de professionnels expérimentés, nous nous engageons à offrir un environnement accueillant et moderne pour tous nos patients.</p>
        <p>Que vous soyez patient, secrétaire ou médecin, notre plateforme numérique facilite l'accès à vos espaces dédiés pour une gestion optimale de votre santé.</p>
    </div>

    <div class="contact-info">
        <div class="contact-item">
            <i class="fa-solid fa-envelope"></i>
            <h3>Email Officiel</h3>
            <p>contact@cabinetmedical.fr</p>
        </div>
        <div class="contact-item">
            <i class="fa-solid fa-phone"></i>
            <h3>Numéro de Téléphone</h3>
            <p>+33 1 23 45 67 89</p>
        </div>
        <div class="contact-item">
            <i class="fa-solid fa-map-marker-alt"></i>
            <h3>Adresse</h3>
            <p>123 Rue de la Santé<br>75001 Paris, France</p>
        </div>
        <div class="contact-item">
            <i class="fa-solid fa-building"></i>
            <h3>Adresse Locale</h3>
            <p>Cabinet Médical Central<br>45 Avenue des Soins<br>75002 Paris, France</p>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <p>&copy; 2025 MedicaHelp – Tous droits réservés</p>
</footer>

</body>
</html>
