<?php
session_start();

//si l’utilisateur est déjà connecté, rediriger vers la page principale
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

//connexion à la base de données
require_once __DIR__ . '/../src/selectBDD.php';

$connexionBDD = $pdo ;

//inclusion des fonctions
require_once __DIR__ . '/../src/functions.php';

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Séances</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }

    h1 {
        margin-bottom: 10px;
    }

    .seance {
        margin-bottom: 24px;
        border: 1px solid #ccc;
        padding: 14px;
        border-radius: 8px;
    }

    .seance h2 {
        margin: 0 0 10px;
    }

    .exercice {
        margin-bottom: 12px;
        padding: 10px;
        background: #f7f7f7;
        border-radius: 6px;
    }

    .exercice img {
        max-width: 100px;
        max-height: 80px;
        display: inline-block;
        vertical-align: middle;
        margin-right: 12px;
    }

    .exercice-details {
        display: inline-block;
        vertical-align: middle;
        max-width: calc(100% - 130px);
    }

    .meta {
        color: #555;
        font-size: .9rem;
    }

    .status {
        text-transform: capitalize;
        font-weight: 700;
    }

    .logout {
        margin-top: 10px;
        display: inline-block;
    }
    </style>
</head>

<body>
    <header>
        <h1>Mes séances</h1>
        <a class="logout" href="logout.php">Se déconnecter</a>
    </header>

    <?php
    // Récupération des séances et exercices pour l'utilisateur connecté
    $stmt = $connexionBDD->prepare(
        "SELECT s.id AS seance_id, s.date_programmee, s.statut, s.series, s.repetitions, s.repos_secondes, s.notes,
                e.nom AS exercice_nom, e.description AS exercice_description, e.image AS exercice_image,
                e.categorie AS exercice_categorie, e.difficulte AS exercice_difficulte, e.muscles AS exercice_muscles
         FROM seances s
         JOIN exercices e ON s.exercice_id = e.id
         WHERE s.utilisateur_id = :uid
         ORDER BY s.date_programmee ASC, s.id ASC"
    );
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $seances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$seances) {
        echo '<p>Aucune séance planifiée pour l’instant. Créez une séance ou attendez la génération du programme.</p>';
    } else {
        $grouped = [];
        foreach ($seances as $row) {
            $date = $row['date_programmee'];
            if (!isset($grouped[$date])) {
                $grouped[$date] = [];
            }
            $grouped[$date][] = $row;
        }

        foreach ($grouped as $date => $items) {
            echo '<div class="seance">';
            echo '<h2>Séance du ' . htmlspecialchars($date) . '</h2>';
            echo '<p class="meta">Statut : <span class="status">' . htmlspecialchars($items[0]['statut']) . '</span> | Séries : ' . intval($items[0]['series']) . ' | Répétitions : ' . intval($items[0]['repetitions']) . ' | Repos : ' . intval($items[0]['repos_secondes']) . 's</p>';
            if (!empty($items[0]['notes'])) {
                echo '<p><strong>Notes :</strong> ' . nl2br(htmlspecialchars($items[0]['notes'])) . '</p>';
            }

            foreach ($items as $item) {
                echo '<div class="exercice">';
                if (!empty($item['exercice_image'])) {
                    echo '<img src="' . htmlspecialchars($item['exercice_image']) . '" alt="' . htmlspecialchars($item['exercice_nom']) . '">';
                }
                echo '<div class="exercice-details">';
                echo '<h3>' . htmlspecialchars($item['exercice_nom']) . '</h3>';
                if ($item['exercice_categorie']) {
                    echo '<p class="meta">Catégorie : ' . htmlspecialchars($item['exercice_categorie']) . ' | Difficulté : ' . htmlspecialchars($item['exercice_difficulte']) . '</p>';
                }
                if ($item['exercice_muscles']) {
                    echo '<p class="meta">Muscles ciblés : ' . htmlspecialchars($item['exercice_muscles']) . '</p>';
                }
                if ($item['exercice_description']) {
                    echo '<p>' . nl2br(htmlspecialchars($item['exercice_description'])) . '</p>';
                }
                echo '</div>';
                echo '</div>';
            }

            echo '</div>';
        }
    }
    ?>

</body>

</html>