<?php
session_start();
require_once __DIR__ . '/../src/db.php';

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $sex = $_POST['sex'] ?? 'Autre';
    $weight = floatval($_POST['weight'] ?? 0);
    $height = floatval($_POST['height'] ?? 0);
    $goal = trim($_POST['goal'] ?? '');
    $days = trim($_POST['days'] ?? '');
    $equipment = trim($_POST['equipment'] ?? '');
    $end_date = $_POST['end_date'] ?? null;

    if (!$email || !$password || !$age || !$weight || !$height || !$goal || !$days || !$end_date) {
        $error = 'Tous les champs requis doivent être remplis.';
    } else {
        $check = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email');
        $check->execute(['email' => $email]);
        if ($check->fetch()) {
            $error = 'Email déjà utilisé.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO utilisateurs (email,mot_de_passe,age,sexe,poids,taille,objectif,jours_disponibilite,materiel,date_fin) VALUES (:email,:mot_de_passe,:age,:sexe,:poids,:taille,:objectif,:jours,:materiel,:date_fin)'
            );
            $stmt->execute([
                'email' => $email,
                'mot_de_passe' => $password_hash,
                'age' => $age,
                'sexe' => $sex,
                'poids' => $weight,
                'taille' => $height,
                'objectif' => $goal,
                'jours' => $days,
                'materiel' => $equipment,
                'date_fin' => $end_date,
            ]);

            $user_id = $pdo->lastInsertId();

            $webhookUrl = 'https://exemple-n8n-webhook.fake/webhook/fitness';
            $payload = [
                'user_id' => $user_id,
                'email' => $email,
                'age' => $age,
                'sexe' => $sex,
                'poids' => $weight,
                'taille' => $height,
                'objectif' => $goal,
                'jours' => $days,
                'materiel' => $equipment,
                'date_fin' => $end_date,
            ];

            $ch = curl_init($webhookUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                $error = 'Échec webhook n8n : ' . $curlError;
            } else {
                $_SESSION['user_id'] = $user_id;
                header('Location: home.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Inscription - FitTracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="container">
        <h1>Inscription</h1>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (!empty($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="post" action="register.php" class="form" id="register-form">
            <label>Email<input type="email" name="email" required></label>
            <label>Mot de passe<input type="password" name="password" required minlength="6"></label>
            <label>Âge<input type="number" name="age" min="10" required></label>
            <label>Sexe
                <select name="sex" required>
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                    <option value="Autre">Autre</option>
                </select>
            </label>
            <label>Poids (kg)<input type="number" name="weight" step="0.1" required></label>
            <label>Taille (cm)<input type="number" name="height" step="0.1" required></label>
            <label>Objectif physique<input type="text" name="goal" required></label>
            <label>Jours de disponibilité (ex: Lundi,Mercredi,Vendredi)<input type="text" name="days" required></label>
            <label>Matériel disponible<input type="text" name="equipment"></label>
            <label>Date de fin<input type="date" name="end_date" required></label>
            <button type="submit" class="btn">S'inscrire</button>
        </form>

        <p>Déjà inscrit ? <a href="login.php">Connexion</a></p>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#register-form').on('submit', function (e) {});
        });
    </script>
</body>
</html>
