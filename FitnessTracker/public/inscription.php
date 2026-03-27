<?php
session_start();

//si l’utilisateur est déjà connecté, rediriger vers la page principale
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

//connexion à la base de données
require_once __DIR__ . '/../src/selectBDD.php';

$connexionBDD = $pdo;

//inclusion des fonctions
require_once __DIR__ . '/../src/functions.php';

$jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
$materiel_options = ['Rien', 'Haltères', 'Barre de traction', 'Tapis de sol', 'Corde à sauter', 'Salle de sport'];

$email = '';
$password = '';
$password_confirm = '';
$pseudo = '';
$age = '';
$sexe = '';
$poids = '';
$taille = '';
$objectif = '';
$jours_disponibilite = [];
$materiel = [];
$date_fin = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $pseudo = trim($_POST['pseudo'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $sexe = $_POST['sexe'] ?? '';
    $poids = trim($_POST['poids'] ?? '');
    $taille = trim($_POST['taille'] ?? '');
    $objectif = trim($_POST['objectif'] ?? '');
    $jours_disponibilite = $_POST['jours_disponibilite'] ?? [];
    if (!is_array($jours_disponibilite)) {
        $jours_disponibilite = [$jours_disponibilite];
    }
    $materiel = $_POST['materiel'] ?? [];
    if (!is_array($materiel)) {
        $materiel = [$materiel];
    }
    $date_fin = trim($_POST['date_fin'] ?? '');

    if (!emailConforme($email)) {
        $error = "L'email n'est pas valide.";
    } elseif (emailExiste($connexionBDD, $email)) {
        $error = "Cet email est déjà utilisé.";
    } elseif (pseudoExiste($connexionBDD, $pseudo)) {
        $error = "Ce pseudo est déjà utilisé.";
    } elseif (empty($password) || strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $password_confirm) {
        $error = "La confirmation du mot de passe ne correspond pas.";
    } elseif (empty($pseudo) || empty($age) || empty($sexe) || empty($poids) || empty($taille) || empty($objectif) || count($jours_disponibilite) === 0 || empty($date_fin)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $stmt = $connexionBDD->prepare(
            "INSERT INTO utilisateurs (email, password, pseudo, age, sexe, poids, taille, objectif, jours_disponibilite, materiel, date_fin) VALUES (:email, :password, :pseudo, :age, :sexe, :poids, :taille, :objectif, :jours_disponibilite, :materiel, :date_fin)"
        );

        $result = $stmt->execute([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'pseudo' => $pseudo,
            'age' => (int) $age,
            'sexe' => $sexe,
            'poids' => (float) $poids,
            'taille' => (float) $taille,
            'objectif' => $objectif,
            'jours_disponibilite' => implode(',', $jours_disponibilite),
            'materiel' => implode(',', $materiel),
            'date_fin' => $date_fin,
        ]);

        if ($result) {
            // --- DÉBUT APPEL N8N ---
            $userId = $connexionBDD->lastInsertId();
            $webhookUrl = 'http://localhost:5678/webhook/fit-inscription-webhook'; // Remplace par l'URL "Production" de ton nœud Webhook n8n

            $data = ['user_id' => $userId];

            $ch = curl_init($webhookUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Timeout court pour ne pas bloquer l'utilisateur
            
            // On exécute l'appel en tâche de fond (on ignore la réponse pour la rapidité)
            curl_exec($ch);
            curl_close($ch);
            // --- FIN APPEL N8N ---

            $success = "Inscription réussie ! Votre programme est en cours de génération.";
            header('Location: connexion.php?registered=1');
            exit;
        } else {
            $error = "Erreur lors de l'inscription. Veuillez réessayer.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inscription</title>
    <link rel="stylesheet" href="assets/css/connexion/style.css" />
</head>

<body>
    <main>
        <h1>Inscription</h1>

        <?php if ($error): ?>
        <p style="color:red;"> <?= htmlspecialchars($error) ?> </p>
        <?php endif; ?>

        <?php if ($success): ?>
        <p style="color:green;"> <?= htmlspecialchars($success) ?> </p>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Email *</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required />

            <label>Mot de passe *</label>
            <input type="password" id="password" name="password" required>

            <label>Confirmer mot de passe *</label>
            <input type="password" id="password_confirm" name="password_confirm" required>

            <label>Pseudo *</label>
            <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($pseudo) ?>" required />

            <label>Age *</label>
            <input type="number" id="age" name="age" min="1" max="150" value="<?= htmlspecialchars($age) ?>" required />

            <label>Sexe *</label>
            <select id="sexe" name="sexe" required>
                <option value="">Sélectionnez</option>
                <option value="Homme" <?= $sexe === 'Homme' ? 'selected' : '' ?>>Homme</option>
                <option value="Femme" <?= $sexe === 'Femme' ? 'selected' : '' ?>>Femme</option>
                <option value="Autre" <?= $sexe === 'Autre' ? 'selected' : '' ?>>Autre</option>
            </select>

            <label>Poids (kg) *</label>
            <input type="number" step="0.01" id="poids" name="poids" value="<?= htmlspecialchars($poids) ?>" required />

            <label>Taille (cm) *</label>
            <input type="number" step="0.01" id="taille" name="taille" value="<?= htmlspecialchars($taille) ?>"
                required />

            <label>Objectif *</label>
            <select id="objectif" name="objectif" required>
                <option value="">Sélectionnez</option>
                <option value="Maintien" <?= $objectif === 'Maintien' ? 'selected' : '' ?>>Maintien</option>
                <option value="Perte de poids" <?= $objectif === 'Perte de poids' ? 'selected' : '' ?>>Perte de poids
                </option>
                <option value="Prise de masse" <?= $objectif === 'Prise de masse' ? 'selected' : '' ?>>Prise de masse
                </option>
                <option value="Amélioration de la condition physique"
                    <?= $objectif === 'Amélioration de la condition physique' ? 'selected' : '' ?>>Amélioration de la
                    condition physique</option>
                <option value="Restructuration corporelle"
                    <?= $objectif === 'Restructuration corporelle' ? 'selected' : '' ?>>Restructuration corporelle
                </option>
            </select>

            <label>Jours de disponibilité *</label>
            <div id="jours_disponibilite">
                <?php foreach ($jours as $jour): ?>
                <label>
                    <input type="checkbox" name="jours_disponibilite[]" value="<?= htmlspecialchars($jour) ?>"
                        <?= in_array($jour, $jours_disponibilite, true) ? 'checked' : '' ?> />
                    <?= htmlspecialchars($jour) ?>
                </label>
                <?php endforeach; ?>
            </div>

            <label>Matériel (facultatif)</label>
            <div id="materiel">
                <?php foreach ($materiel_options as $option): ?>
                <label>
                    <input type="checkbox" name="materiel[]" value="<?= htmlspecialchars($option) ?>"
                        <?= is_array($materiel) && in_array($option, $materiel, true) ? 'checked' : '' ?> />
                    <?= htmlspecialchars($option) ?>
                </label>
                <?php endforeach; ?>
            </div>

            <label>Date de fin de programme *</label>
            <input type="date" id="date_fin" name="date_fin" value="<?= htmlspecialchars($date_fin) ?>" required />

            <button type="submit">S'inscrire</button>
        </form>

        <div>
            <p>Vous avez déjà un compte ?</p>
            <a href="connexion.php">Connectez-vous</a>
        </div>
    </main>
</body>

</html>