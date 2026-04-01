<?php
session_start();

//rediriger si non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

//connexion à la base de données
require_once __DIR__ . '/../src/selectBDD.php';
$connexionBDD = $pdo;

//import des fonctions
require_once __DIR__ . '/../src/functions.php';

//récup l'id de l'utilisateur connecté
$userId = (int) $_SESSION['user_id'];

//initialisation des variables
$message = '';
$error = '';
$seancesSemaine = [];
$seancesParDate = [];
$datesSemaine = [];
$selectedDate = '';
$seancesAujourdhui = [];

//récupération des séances de la semaine courante pour l'utilisateur
try {
    $seancesSemaine = getSeancesSemaineCouranteParUtilisateur($connexionBDD, $userId);

    $lesSceancesDuUser = getSeancesParUtilisateur($connexionBDD, $userId);
    if (empty($lesSceancesDuUser)) {
        $message = "Aucune séance planifiée pour la semaine courante. En cours de génération de séances pour vous...";
        postUserN8N($userId);

        // Recharger les séances après demande de génération.
        $seancesSemaine = getSeancesSemaineCouranteParUtilisateur($connexionBDD, $userId);
        if (!empty($seancesSemaine)) {
            $message = "Séances générées avec succès pour la semaine courante.";
        }
    } else {
        $message = "Voici vos séances planifiées pour la semaine courante.";
    }

    // regrouper par date
    foreach ($seancesSemaine as $item) {
        $date = $item['date_programmee'] ?? '';
        if (!isset($seancesParDate[$date])) {
            $seancesParDate[$date] = [];
        }
        $seancesParDate[$date][] = $item;
    }

    $datesSemaine = array_keys($seancesParDate);
    sort($datesSemaine);

    $selectedDate = $_GET['date'] ?? '';
    if ($selectedDate == '' || !in_array($selectedDate, $datesSemaine, true)) {
        $selectedDate = $datesSemaine[0] ?? '';
    }

    $seancesAujourdhui = $selectedDate !== '' ? getSeancesParUtilisateurParDate($connexionBDD, $userId, $selectedDate) : [];
} catch (Throwable $e) {
    $error = "Une erreur est survenue lors de la récupération des séances : " . htmlspecialchars($e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mes Séances</title>
    <link rel="stylesheet" href="assets/css/Index/style.css" />
</head>

<body>
    <main>
        <h1>Mes Séances</h1>

        <p>Bienvenue sur votre dashboard. Choisissez un jour de la semaine pour afficher les séances :</p>

        <?php if (!empty($error)): ?>
        <p style="color:red; font-weight:bold;">Erreur : <?= htmlspecialchars($error) ?></p>
        <?php elseif (!empty($message)): ?>
        <p style="color:green; font-weight:bold;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if (empty($datesSemaine)): ?>
        <p>Aucune séance planifiée pour la semaine courante.</p>
        <?php else: ?>
        <div class="jours-semaine" style="margin-bottom: 1rem;">
            <?php foreach ($datesSemaine as $date): ?>
            <a href="?date=<?= urlencode($date) ?>"
                style="margin-right: 0.5rem; padding: 0.4rem 0.7rem; border: 1px solid #333; text-decoration: none; <?= $selectedDate == $date ? 'background:#d6f8d6;' : '' ?>">
                <?= htmlspecialchars(getNomJourFr($date) . ' ' . $date) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <h2>Séances du <?= htmlspecialchars(getNomJourFr($selectedDate) . ' ' . $selectedDate) ?></h2>

        <?php if (empty($seancesAujourdhui)): ?>
        <p>Aucune séance à cette date.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Exercice</th>
                    <th>Catégorie</th>
                    <th>Difficulté</th>
                    <th>Muscles</th>
                    <th>Séries</th>
                    <th>Répétitions</th>
                    <th>Repos (s)</th>
                    <th>Statut</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($seancesAujourdhui as $seance): ?>
                <tr>
                    <td><?= htmlspecialchars($seance['date_programmee'] ?? '') ?></td>
                    <td><?= htmlspecialchars($seance['exercice_nom'] ?? '') ?></td>
                    <td><?= htmlspecialchars($seance['exercice_categorie'] ?? '') ?></td>
                    <td><?= htmlspecialchars($seance['exercice_difficulte'] ?? '') ?></td>
                    <td><?= htmlspecialchars($seance['exercice_muscles'] ?? '') ?></td>
                    <td><?= htmlspecialchars($seance['series'] ?? '') ?></td>
                    <td><?= htmlspecialchars($seance['repetitions'] ?? '') ?></td>
                    <td><?= htmlspecialchars($seance['repos_secondes'] ?? '') ?></td>
                    <td><?= htmlspecialchars($seance['statut'] ?? '') ?></td>
                    <td><?= nl2br(htmlspecialchars($seance['notes'] ?? '')) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <?php endif; ?>

        <div style="margin-top: 1.5rem;">
            <a href="logout.php">Se déconnecter</a>
        </div>
    </main>
</body>

</html>