<?php
require_once __DIR__ . '/../src/functions.php';
startSession();
$pdo = getPdo();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Collecte et nettoyage des données
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $age = filter_var($_POST['age'] ?? 0, FILTER_VALIDATE_INT);
    $sex = $_POST['sex'] ?? 'Autre';
    $weight = filter_var($_POST['weight'] ?? 0, FILTER_VALIDATE_FLOAT);
    $height = filter_var($_POST['height'] ?? 0, FILTER_VALIDATE_FLOAT);
    $goal = htmlspecialchars(trim($_POST['goal'] ?? ''));
    $endDate = $_POST['end_date'] ?? null;

    // Gestion des tableaux (Jours et Matériel)
    $days = isset($_POST['days']) ? implode(',', (array)$_POST['days']) : '';
    $equipment = isset($_POST['equipment']) ? implode(',', (array)$_POST['equipment']) : '';

    // 2. Validation de base
    if (!$email || strlen($password) < 6 || !$age || !$weight || !$height || empty($days) || !$endDate) {
        $error = "Veuillez remplir tous les champs correctement (Mot de passe : 6 caractères min).";
    } else {
        try {
            $pdo->beginTransaction();

            // Vérification email unique
            $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Cet email est déjà utilisé.');
            }

            // Insertion Utilisateur
            $stmt = $pdo->prepare('
                INSERT INTO utilisateurs (email, mot_de_passe, age, sexe, poids, taille, objectif, jours_disponibilite, materiel, date_fin) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $email, password_hash($password, PASSWORD_DEFAULT), $age, $sex, 
                $weight, $height, $goal, $days, $equipment, $endDate
            ]);
            
            $userId = $pdo->lastInsertId();

            // 3. Appel Webhook n8n
            $payload = [
                'user_id' => $userId, 'email' => $email, 'age' => $age, 'sexe' => $sex,
                'poids' => $weight, 'taille' => $height, 'objectif' => $goal,
                'jours' => $days, 'materiel' => $equipment, 'date_fin' => $endDate
            ];

            $n8n = sendN8nPayload($payload, 'http://localhost:5678/webhook-test/webhook/fitness');

            if ($n8n['is_success']) {
                $data = json_decode($n8n['response'], true);
                if (is_array($data)) {
                    processN8nResponse($pdo, $userId, $data);
                }
            }

            $pdo->commit();
            $_SESSION['user_id'] = $userId;
            header('Location: home.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

/**
 * Traite les données retournées par n8n pour remplir les tables liées
 */
function processN8nResponse($pdo, $userId, $data) {
    // 1. Séances
    if (!empty($data['seances'])) {
        $stmt = $pdo->prepare('INSERT INTO seances (utilisateur_id, exercice_id, date_programmee, statut, series, repetitions, repos_secondes, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($data['seances'] as $s) {
            $exId = $s['exercice_id'] ?? findExerciceIdByName($pdo, $s['exercice_nom'] ?? '');
            if ($exId) {
                $stmt->execute([
                    $userId, $exId, $s['date_programmee'] ?? date('Y-m-d'),
                    $s['statut'] ?? 'en_attente', $s['series'] ?? 3, 
                    $s['repetitions'] ?? 12, $s['repos_secondes'] ?? 60, $s['notes'] ?? null
                ]);
            }
        }
    }
    // 2. Nutrition (Plan et Recommandations)
    if (!empty($data['plan_nutrition'])) {
        $p = $data['plan_nutrition'];
        $pdo->prepare('INSERT INTO plan_nutrition (utilisateur_id, calories_totales, proteines_g, glucides_g, lipides_g) VALUES (?, ?, ?, ?, ?)')
            ->execute([$userId, $p['calories_totales'] ?? 0, $p['proteines_g'] ?? 0, $p['glucides_g'] ?? 0, $p['lipides_g'] ?? 0]);
    }
}

function findExerciceIdByName($pdo, $name) {
    if (empty($name)) return null;
    $stmt = $pdo->prepare('SELECT id FROM exercices WHERE nom = ? LIMIT 1');
    $stmt->execute([$name]);
    return $stmt->fetchColumn() ?: null;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Inscription - FitTracker</title>
</head>

<body>
    <h1>Inscription</h1>

    <?php if ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <fieldset>
            <legend>Informations personnelles</legend>
            <label>Email: <input type="email" name="email" required></label><br>
            <label>Mot de passe: <input type="password" name="password" required minlength="6"></label><br>
            <label>Âge: <input type="number" name="age" min="10" required></label><br>
            <label>Sexe:
                <select name="sex">
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                    <option value="Autre">Autre</option>
                </select>
            </label>
        </fieldset>

        <fieldset>
            <legend>Physique & Objectifs</legend>
            <label>Poids (kg): <input type="number" name="weight" step="0.1" required></label><br>
            <label>Taille (cm): <input type="number" name="height" step="0.1" required></label><br>
            <label>Objectif: <input type="text" name="goal" placeholder="ex: Perte de gras" required></label><br>
            <label>Date de fin souhaitée: <input type="date" name="end_date" required></label>
        </fieldset>

        <fieldset>
            <legend>Entraînement</legend>
            <label>Jours disponibles :</label><br>
            <select name="days[]" multiple required size="7">
                <?php foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'] as $jour): ?>
                <option value="<?= $jour ?>"><?= $jour ?></option>
                <?php endforeach; ?>
            </select><br>

            <label>Matériel :</label><br>
            <select name="equipment[]" multiple size="4">
                <option value="Haltères">Haltères</option>
                <option value="Barre de traction">Barre de traction</option>
                <option value="Tapis de sol">Tapis de sol</option>
                <option value="Rien">Rien (Poids du corps)</option>
            </select>
        </fieldset>

        <button type="submit">Créer mon programme</button>
    </form>

    <p><a href="login.php">Déjà un compte ? Se connecter</a></p>
</body>

</html>