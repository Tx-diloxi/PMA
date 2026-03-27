-- init.sql : modèle de base de données pour FitnessTracker (français)
DROP DATABASE IF EXISTS fittracker;
CREATE DATABASE fittracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fittracker;

CREATE TABLE utilisateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  mot_de_passe VARCHAR(255) NOT NULL,
  prenom VARCHAR(100) DEFAULT NULL,
  nom VARCHAR(100) DEFAULT NULL,
  age INT NOT NULL,
  sexe ENUM('Homme','Femme','Autre') NOT NULL,
  poids DECIMAL(5,2) NOT NULL,
  taille DECIMAL(5,2) NOT NULL,
  objectif VARCHAR(255) NOT NULL,
  jours_disponibilite VARCHAR(255) NOT NULL,
  materiel VARCHAR(255) DEFAULT NULL,
  date_fin DATE NOT NULL,
  calories_objectif INT DEFAULT NULL,
  proteines_objectif INT DEFAULT NULL,
  glucides_objectif INT DEFAULT NULL,
  lipides_objectif INT DEFAULT NULL,
  cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modifie_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE exercices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(150) NOT NULL,
  description TEXT,
  image VARCHAR(255) NOT NULL,
  categorie VARCHAR(100) DEFAULT NULL,
  difficulte ENUM('Débutant','Intermédiaire','Avancé') DEFAULT 'Débutant',
  muscles VARCHAR(255) DEFAULT NULL,
  cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE seances (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  exercice_id INT NOT NULL,
  date_programmee DATE NOT NULL,
  statut ENUM('en_attente','terminee','ignoree') DEFAULT 'en_attente',
  series INT DEFAULT 3,
  repetitions INT DEFAULT 12,
  repos_secondes INT DEFAULT 60,
  notes TEXT,
  modifie_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  FOREIGN KEY (exercice_id) REFERENCES exercices(id) ON DELETE CASCADE
);

CREATE TABLE plan_nutrition (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  calories_totales INT NOT NULL,
  proteines_g INT NOT NULL,
  glucides_g INT NOT NULL,
  lipides_g INT NOT NULL,
  cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modifie_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

CREATE TABLE recommandations_nutrition (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  titre VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  type_repas ENUM('petit_dejeuner','dejeuner','diner','collation') DEFAULT 'collation',
  calories INT DEFAULT NULL,
  proteines INT DEFAULT NULL,
  glucides INT DEFAULT NULL,
  lipides INT DEFAULT NULL,
  lien VARCHAR(255) DEFAULT NULL,
  cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

CREATE TABLE journal_poids (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  date_mesure DATE NOT NULL,
  poids DECIMAL(5,2) NOT NULL,
  cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

INSERT INTO exercices (nom, description, image, categorie, difficulte, muscles) VALUES
('Pompes','Position planche, descendre puis monter','pompes.jpg','Pecs','Débutant','Pectoraux,Triceps,Épaules'),
('Squats','Flexion des genoux et des hanches','squats.jpg','Jambes','Débutant','Quadriceps,Fessiers,Ischio-jambiers'),
('Gainage','Maintien du corps droit en planche','gainage.jpg','Core','Débutant','Abdominaux,Bas du dos');
