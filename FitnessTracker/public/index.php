<?php
session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

// Connexion à la base de données
require_once __DIR__ . '/../src/selectBDD.php'; // $pdo doit être défini ici
$connexionBDD = $pdo; // adapter selon votre fichier

// Traitement du changement de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $seance_id = (int) ($_POST['seance_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    $allowed_statuses = ['en_attente', 'terminee', 'ignoree'];
    
    if ($seance_id > 0 && in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE fittracker.seances SET statut = ? WHERE id = ? AND utilisateur_id = ?");
        $stmt->execute([$new_status, $seance_id, $_SESSION['user_id']]);
        // Redirection pour éviter la double soumission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Récupération des séances de l'utilisateur
$user_id = $_SESSION['user_id'];
$sql = "
    SELECT 
        s.id AS seance_id,
        s.date_programmee,
        s.statut,
        s.series,
        s.repetitions,
        s.repos_secondes,
        s.notes,
        e.id AS exercice_id,
        e.nom AS exercice_nom,
        e.description AS exercice_description,
        e.image AS exercice_image,
        e.muscles AS exercice_muscles
    FROM fittracker.seances s
    INNER JOIN fittracker.exercices e ON s.exercice_id = e.id
    WHERE s.utilisateur_id = ?
    ORDER BY s.date_programmee ASC, s.id ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$seances = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regroupement par date
$grouped = [];
foreach ($seances as $seance) {
    $date = $seance['date_programmee'];
    if (!isset($grouped[$date])) {
        $grouped[$date] = [];
    }
    $grouped[$date][] = $seance;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes séances - FitTracker</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f4f4f4;
        color: #333;
        line-height: 1.6;
        padding: 20px;
    }

    header {
        background: #2c3e50;
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    header h1 {
        font-size: 1.8rem;
    }

    .logout {
        background: #e74c3c;
        color: white;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 5px;
        transition: background 0.3s;
    }

    .logout:hover {
        background: #c0392b;
    }

    .day-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
        overflow: hidden;
    }

    .day-header {
        background: #3498db;
        color: white;
        padding: 12px 20px;
        font-size: 1.4rem;
        font-weight: bold;
    }

    .day-header .date {
        font-weight: normal;
        font-size: 0.9rem;
        margin-left: 10px;
    }

    .exercise-item {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .exercise-item:last-child {
        border-bottom: none;
    }

    .exercise-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        background: #f0f0f0;
    }

    .exercise-details {
        flex: 1;
    }

    .exercise-name {
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .exercise-muscles {
        color: #7f8c8d;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .exercise-description {
        margin: 10px 0;
        font-size: 0.9rem;
    }

    .workout-params {
        background: #ecf0f1;
        padding: 8px;
        border-radius: 5px;
        display: inline-block;
        font-size: 0.85rem;
        margin-top: 5px;
    }

    .status {
        margin-top: 10px;
        font-weight: bold;
    }

    .status-en_attente {
        color: #f39c12;
    }

    .status-terminee {
        color: #2ecc71;
    }

    .status-ignoree {
        color: #95a5a6;
    }

    .status-form {
        margin-top: 10px;
    }

    select,
    button {
        padding: 5px 10px;
        border-radius: 4px;
        border: 1px solid #ccc;
        cursor: pointer;
    }

    button {
        background: #3498db;
        color: white;
        border: none;
    }

    button:hover {
        background: #2980b9;
    }

    footer {
        text-align: center;
        margin-top: 30px;
        color: #7f8c8d;
    }

    .no-sessions {
        background: white;
        padding: 30px;
        text-align: center;
        border-radius: 8px;
        color: #7f8c8d;
    }
    </style>
</head>

<body>
    <header>
        <h1>Mes séances d'entraînement</h1>
        <a class="logout" href="logout.php">Se déconnecter</a>
    </header>

    <main>
        <?php if (empty($grouped)): ?>
        <div class="no-sessions">
            <p>Aucune séance programmée pour le moment. Votre programme sera généré automatiquement lors de votre
                inscription.</p>
        </div>
        <?php else: ?>
        <?php foreach ($grouped as $date => $sessions): ?>
        <div class="day-card">
            <div class="day-header">
                <?php 
                            $dateObj = new DateTime($date);
                            $jourSemaine = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'][$dateObj->format('w')];
                            echo htmlspecialchars($jourSemaine) . ' ' . $dateObj->format('d/m/Y');
                        ?>
            </div>
            <?php foreach ($sessions as $seance): ?>
            <div class="exercise-item">
                <?php if (!empty($seance['exercice_image'])): ?>
                <img class="exercise-image" src="<?= htmlspecialchars($seance['exercice_image']) ?>"
                    alt="<?= htmlspecialchars($seance['exercice_nom']) ?>">
                <?php else: ?>
                <div class="exercise-image"
                    style="background: #ddd; display: flex; align-items: center; justify-content: center;">Pas d'image
                </div>
                <?php endif; ?>
                <div class="exercise-details">
                    <div class="exercise-name"><?= htmlspecialchars($seance['exercice_nom']) ?></div>
                    <div class="exercise-muscles"><?= htmlspecialchars($seance['exercice_muscles']) ?></div>
                    <div class="exercise-description"><?= nl2br(htmlspecialchars($seance['exercice_description'])) ?>
                    </div>
                    <div class="workout-params">
                        <?= $seance['series'] ?> séries × <?= $seance['repetitions'] ?> répétitions<br>
                        Repos : <?= $seance['repos_secondes'] ?> secondes
                    </div>
                    <div class="status">
                        Statut :
                        <span class="status-<?= $seance['statut'] ?>">
                            <?php 
                                            $statuts = ['en_attente' => 'À venir', 'terminee' => 'Terminée', 'ignoree' => 'Ignorée'];
                                            echo $statuts[$seance['statut']];
                                        ?>
                        </span>
                    </div>
                    <?php if ($seance['statut'] === 'en_attente'): ?>
                    <form class="status-form" method="post" style="display:inline;">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="seance_id" value="<?= $seance['seance_id'] ?>">
                        <select name="status">
                            <option value="terminee">Marquer comme terminée</option>
                            <option value="ignoree">Marquer comme ignorée</option>
                        </select>
                        <button type="submit">Mettre à jour</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($seance['notes']): ?>
                    <div class="notes">Notes : <?= nl2br(htmlspecialchars($seance['notes'])) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <footer>
        <p>FitTracker - Votre coach personnel</p>
    </footer>
</body>

</html>