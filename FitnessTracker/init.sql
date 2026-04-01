DROP SCHEMA IF EXISTS fittracker;

CREATE SCHEMA fittracker CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_unicode_ci;

USE fittracker;

CREATE TABLE fittracker.utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    pseudo VARCHAR(100) DEFAULT NULL,
    age INT NOT NULL,
    sexe ENUM('Homme', 'Femme', 'Autre') DEFAULT 'Homme',
    poids DECIMAL(5, 2) NOT NULL,
    taille DECIMAL(5, 2) NOT NULL,
    objectif VARCHAR(255) DEFAULT 'Maintien',
    niveau ENUM(
        'Débutant',
        'Intermédiaire',
        'Avancé'
    ) DEFAULT 'Débutant',
    jours_disponibilite VARCHAR(255) NOT NULL,
    materiel VARCHAR(255) DEFAULT 'Rien',
    date_fin DATE NOT NULL,
    calories_objectif INT DEFAULT NULL,
    proteines_objectif INT DEFAULT NULL,
    glucides_objectif INT DEFAULT NULL,
    lipides_objectif INT DEFAULT NULL,
    cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modifie_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE fittracker.exercices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    categorie VARCHAR(100) DEFAULT NULL,
    difficulte ENUM(
        'Débutant',
        'Intermédiaire',
        'Avancé'
    ) DEFAULT 'Débutant',
    muscles VARCHAR(255) DEFAULT NULL
);

CREATE TABLE fittracker.seances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    exercice_id INT NOT NULL,
    date_programmee DATE NOT NULL,
    statut ENUM(
        'en_attente',
        'terminee',
        'ignoree'
    ) DEFAULT 'en_attente',
    series INT DEFAULT 3,
    repetitions INT DEFAULT 12,
    repos_secondes INT DEFAULT 60,
    notes TEXT,
    modifie_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES fittracker.utilisateurs (id) ON DELETE CASCADE,
    FOREIGN KEY (exercice_id) REFERENCES fittracker.exercices (id) ON DELETE CASCADE
);

CREATE TABLE fittracker.plan_nutrition (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    calories_totales INT NOT NULL,
    proteines_g INT NOT NULL,
    glucides_g INT NOT NULL,
    lipides_g INT NOT NULL,
    cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modifie_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES fittracker.utilisateurs (id)
);

CREATE TABLE fittracker.recommandations_nutrition (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type_repas ENUM(
        'petit_dejeuner',
        'dejeuner',
        'diner',
        'collation'
    ) DEFAULT 'collation',
    calories INT DEFAULT NULL,
    proteines INT DEFAULT NULL,
    glucides INT DEFAULT NULL,
    lipides INT DEFAULT NULL,
    lien VARCHAR(255) DEFAULT NULL,
    cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES fittracker.utilisateurs (id) ON DELETE CASCADE
);

CREATE TABLE fittracker.journal_poids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    date_mesure DATE NOT NULL,
    poids DECIMAL(5, 2) NOT NULL,
    cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES fittracker.utilisateurs (id) ON DELETE CASCADE
);

-- compte admin de test
INSERT INTO
    fittracker.utilisateurs (
        email,
        password,
        pseudo,
        age,
        sexe,
        poids,
        taille,
        objectif,
        jours_disponibilite,
        materiel,
        date_fin,
        calories_objectif,
        proteines_objectif,
        glucides_objectif,
        lipides_objectif
    )
VALUES (
        'admin@test.local',
        '$2y$10$fyM3PErWrk6aTgVSVk0wmOj2A5qX4PeeuawT/MM.xQ0rxR5eO.LJq',
        'Admin',
        30,
        'Homme',
        80.00,
        180.00,
        'Maintien',
        'Lundi,Mardi,Mercredi,Jeudi,Vendredi',
        'Rien',
        '2099-12-31',
        2500,
        140,
        300,
        70
    );

INSERT INTO
    fittracker.exercices (
        nom,
        description,
        image,
        categorie,
        difficulte,
        muscles
    )
VALUES
    -- Catégorie : Musculation (Poids du corps et charges libres)
    (
        'Pompes classiques',
        'Flexion et extension des bras en appui face au sol en gardant le corps droit.',
        'images/pompes.jpg',
        'Musculation',
        'Intermédiaire',
        'Pectoraux, Triceps, Épaules'
    ),
    (
        'Squats',
        'Flexion des jambes avec le dos droit et le poids sur les talons.',
        'images/squats.jpg',
        'Musculation',
        'Débutant',
        'Quadriceps, Fessiers, Ischio-jambiers'
    ),
    (
        'Tractions (Pronation)',
        'Suspension à une barre et tirage du corps vers le haut jusqu''au menton.',
        'images/tractions.jpg',
        'Musculation',
        'Avancé',
        'Dos (Grand dorsal), Biceps'
    ),
    (
        'Fentes avant',
        'Avancer un pied et fléchir les deux jambes à 90 degrés, puis revenir.',
        'images/fentes.jpg',
        'Musculation',
        'Débutant',
        'Quadriceps, Fessiers'
    ),
    (
        'Développé Couché',
        'Allongé sur un banc, pousser une barre chargée depuis la poitrine vers le haut.',
        'images/developpe_couche.jpg',
        'Musculation',
        'Intermédiaire',
        'Pectoraux, Triceps, Épaules avant'
    ),
    (
        'Soulevé de terre',
        'Soulever une barre au sol en utilisant la force des jambes et du dos tout en gardant la colonne neutre.',
        'images/deadlift.jpg',
        'Musculation',
        'Avancé',
        'Lombaires, Ischio-jambiers, Fessiers, Trapèzes'
    ),
    (
        'Dips',
        'Suspension sur des barres parallèles, descendre le corps en pliant les coudes puis remonter.',
        'images/dips.jpg',
        'Musculation',
        'Intermédiaire',
        'Triceps, Pectoraux (bas)'
    ),
    (
        'Curl Biceps',
        'Flexion du coude avec des haltères ou une barre pour contracter le biceps.',
        'images/curl_biceps.jpg',
        'Musculation',
        'Débutant',
        'Biceps'
    ),
    (
        'Tirage Bûcheron',
        'Tirage d''un haltère à un bras, buste penché en appui sur un banc.',
        'images/bucheron.jpg',
        'Musculation',
        'Intermédiaire',
        'Dos, Biceps, Arrière de l''épaule'
    ),

-- Catégorie : Gainage & Abdominaux
(
    'Planche abdominale',
    'Maintien du corps à l''horizontale en appui sur les avant-bras et les pointes de pieds.',
    'images/planche.jpg',
    'Gainage',
    'Débutant',
    'Ceinture abdominale, Lombaires'
),
(
    'Crunchs',
    'Allongé sur le dos, relever légèrement le buste en contractant les abdominaux.',
    'images/crunchs.jpg',
    'Abdominaux',
    'Débutant',
    'Grand droit de l''abdomen'
),
(
    'Russian Twist',
    'Assis, buste incliné en arrière, effectuer des rotations du torse de gauche à droite.',
    'images/russian_twist.jpg',
    'Abdominaux',
    'Intermédiaire',
    'Obliques, Grand droit'
),
(
    'Dragon Flag',
    'Allongé sur un banc, soulever tout le corps droit en appui sur les épaules.',
    'images/dragon_flag.jpg',
    'Abdominaux',
    'Avancé',
    'Ceinture abdominale (intense)'
),

-- Catégorie : Cardio & HIIT
(
    'Burpees',
    'Enchaînement dynamique : squat, planche, pompe, retour en squat et saut vertical.',
    'images/burpees.jpg',
    'Cardio',
    'Avancé',
    'Corps complet'
),
(
    'Jumping Jacks',
    'Sauts sur place en écartant et resserrant simultanément les bras et les jambes.',
    'images/jumping_jacks.jpg',
    'Cardio',
    'Débutant',
    'Mollets, Épaules, Cardio'
),
(
    'Mountain Climbers',
    'En position de planche, ramener alternativement les genoux vers la poitrine rapidement.',
    'images/mountain_climbers.jpg',
    'Cardio',
    'Intermédiaire',
    'Cardio, Ceinture abdominale, Épaules'
),
(
    'Corde à sauter',
    'Sauts continus par-dessus une corde en rotation.',
    'images/corde_sauter.jpg',
    'Cardio',
    'Débutant',
    'Mollets, Cardio, Avant-bras'
),

-- Catégorie : Musculation (supplémentaire)
(
    'Pompes diamant',
    'Pompes avec les mains jointes formant un diamant sous la poitrine pour cibler intensément les triceps.',
    'images/pompes_diamant.jpg',
    'Musculation',
    'Intermédiaire',
    'Triceps, Pectoraux intérieurs'
),
(
    'Tractions supination',
    'Suspension à une barre avec paumes tournées vers soi et tirage du corps jusqu''au menton.',
    'images/tractions_supination.jpg',
    'Musculation',
    'Avancé',
    'Grand dorsal, Biceps'
),
(
    'Squats sumo',
    'Squats avec pieds très écartés et pointes tournées vers l''extérieur.',
    'images/squats_sumo.jpg',
    'Musculation',
    'Débutant',
    'Quadriceps, Adducteurs, Fessiers'
),
(
    'Fentes arrière',
    'Reculer un pied et fléchir les deux jambes à 90 degrés avant de revenir.',
    'images/fentes_arriere.jpg',
    'Musculation',
    'Débutant',
    'Quadriceps, Fessiers, Ischio-jambiers'
),
(
    'Développé militaire',
    'Pousser une barre ou des haltères au-dessus de la tête en partant des épaules.',
    'images/developpe_militaire.jpg',
    'Musculation',
    'Intermédiaire',
    'Épaules, Triceps'
),
(
    'Rowing barre',
    'Tirage d''une barre vers le buste en position penchée avec le dos droit.',
    'images/rowing_barre.jpg',
    'Musculation',
    'Intermédiaire',
    'Dos, Biceps'
),
(
    'Élévations latérales',
    'Lever les haltères latéralement jusqu''à hauteur des épaules.',
    'images/elevations_laterales.jpg',
    'Musculation',
    'Débutant',
    'Deltoïdes latéraux'
),
(
    'Soulevé de terre roumain',
    'Flexion du buste avec barre en gardant les jambes presque tendues et la colonne neutre.',
    'images/romanian_deadlift.jpg',
    'Musculation',
    'Intermédiaire',
    'Ischio-jambiers, Fessiers, Lombaires'
),
(
    'Extensions triceps',
    'Extension des coudes au-dessus de la tête avec un haltère ou une barre.',
    'images/extensions_triceps.jpg',
    'Musculation',
    'Débutant',
    'Triceps'
),
(
    'Flyes pectoraux',
    'Écartement des bras avec haltères en position allongée sur un banc.',
    'images/flyes_pectoraux.jpg',
    'Musculation',
    'Intermédiaire',
    'Pectoraux'
),
(
    'Élévations mollets',
    'Monter sur la pointe des pieds avec ou sans charge pour contracter les mollets.',
    'images/elevations_mollets.jpg',
    'Musculation',
    'Débutant',
    'Mollets'
),
(
    'Curl marteau',
    'Flexion du coude avec haltères en prise neutre pour travailler les avant-bras et biceps.',
    'images/curl_marteau.jpg',
    'Musculation',
    'Débutant',
    'Biceps, Avant-bras'
),

-- Catégorie : Gainage & Abdominaux (supplémentaire)
(
    'Planche latérale',
    'Maintien du corps droit sur un avant-bras et le côté du pied, alterner les côtés.',
    'images/planche_laterale.jpg',
    'Gainage',
    'Intermédiaire',
    'Obliques, Ceinture abdominale'
),
(
    'Relevés de jambes',
    'Allongé sur le dos, soulever les jambes tendues vers le plafond en contractant les abdos.',
    'images/releves_jambes.jpg',
    'Abdominaux',
    'Intermédiaire',
    'Abdominaux inférieurs'
),
(
    'Bicycle crunches',
    'Alterner genou droit avec coude gauche et vice versa en rotation du torse.',
    'images/bicycle_crunches.jpg',
    'Abdominaux',
    'Débutant',
    'Obliques, Grand droit'
),
(
    'V-ups',
    'Soulever simultanément buste et jambes pour toucher les pieds en formant un V.',
    'images/v_ups.jpg',
    'Abdominaux',
    'Avancé',
    'Ceinture abdominale'
),
(
    'Superman',
    'Allongé ventre au sol, soulever bras et jambes simultanément pour renforcer le dos.',
    'images/superman.jpg',
    'Gainage',
    'Débutant',
    'Lombaires, Fessiers, Épaules arrière'
),
(
    'Hollow body hold',
    'Allongé sur le dos, creuser le ventre et soulever épaules et jambes tendues.',
    'images/hollow_body.jpg',
    'Gainage',
    'Avancé',
    'Ceinture abdominale profonde'
),
(
    'Side plank hip dips',
    'En planche latérale, descendre et remonter les hanches de façon contrôlée.',
    'images/side_plank_dips.jpg',
    'Gainage',
    'Intermédiaire',
    'Obliques, Ceinture abdominale'
),

-- Catégorie : Cardio & HIIT (supplémentaire)
(
    'High knees',
    'Course sur place en levant les genoux haut vers la poitrine le plus rapidement possible.',
    'images/high_knees.jpg',
    'Cardio',
    'Intermédiaire',
    'Cardio, Quadriceps, Mollets'
),
(
    'Butt kicks',
    'Course sur place en ramenant les talons vers les fessiers de façon rapide.',
    'images/butt_kicks.jpg',
    'Cardio',
    'Débutant',
    'Cardio, Ischio-jambiers'
),
(
    'Skaters',
    'Sauts latéraux alternés avec flexion des jambes et balancement des bras.',
    'images/skaters.jpg',
    'Cardio',
    'Intermédiaire',
    'Cardio, Jambes, Fessiers'
),
(
    'Tuck jumps',
    'Sauts verticaux en ramenant les genoux vers la poitrine au maximum.',
    'images/tuck_jumps.jpg',
    'Cardio',
    'Avancé',
    'Cardio, Jambes, Abdominaux'
),
(
    'Shadow boxing',
    'Enchaînement de coups de poing, esquives et déplacements rapides dans le vide.',
    'images/shadow_boxing.jpg',
    'Cardio',
    'Intermédiaire',
    'Corps complet, Cardio, Épaules'
),
(
    'Jumping lunges',
    'Fentes avant alternées avec un saut explosif entre chaque répétition.',
    'images/jumping_lunges.jpg',
    'Cardio',
    'Avancé',
    'Quadriceps, Fessiers, Cardio'
),
(
    'Sprint sur place',
    'Course rapide sur place avec pompage des bras et genoux haut.',
    'images/sprint_place.jpg',
    'Cardio',
    'Avancé',
    'Cardio complet, Mollets'
),
(
    'Bear crawl',
    'Déplacement à quatre pattes en alternant mains et pieds rapidement.',
    'images/bear_crawl.jpg',
    'Cardio',
    'Intermédiaire',
    'Corps complet, Cardio, Épaules'
);