<?php
session_start();

//si l’utilisateur est déjà connecté, rediriger vers la page principale
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

//connexion à la base de données
require_once __DIR__ . '/../src/selectBDD.php';

$connexionBDD = $pdo ;

//inclusion des fonctions
require_once __DIR__ . '/../src/functions.php';


$email = '';
$password = '';
$error = '';

//verification de la méthode de requête
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //recuperation et nettoyage des données du formulaire
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    //verification de l'email et de l'existence de l'utilisateur
    if (!emailConforme($email)) {
        $error = "L'email n'est pas valide.";
    } 
    elseif (!emailExiste($connexionBDD, $email)) {
        $error = "Aucun compte trouvé avec cet email.";
    } 
    else {
        //vérifier le mot de passe
        $userId = verifEmailPassword($connexionBDD, $email, $password);
        if ($userId !== false) {
           $_SESSION['user_id'] = $userId;
           header('Location: index.php');
           exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Connexion</title>
    <link rel="stylesheet" href="assets/css/connexion/style.css" />
</head>

<body>
    <main>
        <h1>Connexion</h1>

        <?php if (!empty($error)): ?>
        <p style="color:red;"> <?= htmlspecialchars($error) ?> </p>
        <?php endif; ?>

        <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
        <p style="color:green;">Inscription réussie, vous pouvez maintenant vous connecter.</p>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required />

            <label>Mot de passe</label>
            <input type="password" id="password" name="password" required />

            <button type="submit">Se connecter</button>
        </form>

        <div>
            <p>Vous n'avez pas de compte ?</p>
            <a href="inscription.php"> Créez-en un</a>
        </div>
    </main>
</body>

</html>