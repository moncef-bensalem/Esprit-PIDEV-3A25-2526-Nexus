-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 06, 2026 at 07:33 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nexus_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidat`
--

CREATE TABLE `candidat` (
  `id_candidat` bigint NOT NULL,
  `nom_complet` varchar(200) NOT NULL,
  `email_contact` varchar(255) NOT NULL,
  `chemin_cv` varchar(500) DEFAULT NULL,
  `score_global_ia` decimal(5,2) DEFAULT NULL,
  `date_ajout` datetime NOT NULL,
  `fk_user_id` int DEFAULT NULL,
  `score_technique` int DEFAULT NULL,
  `score_experience` int DEFAULT NULL,
  `score_formation` int DEFAULT NULL,
  `score_langues` int DEFAULT NULL,
  `score_soft_skills` int DEFAULT NULL
) ;

--
-- Dumping data for table `candidat`
--

INSERT INTO `candidat` (`id_candidat`, `nom_complet`, `email_contact`, `chemin_cv`, `score_global_ia`, `date_ajout`, `fk_user_id`, `score_technique`, `score_experience`, `score_formation`, `score_langues`, `score_soft_skills`) VALUES
(37, 'Alice Martin', 'alice@example.com', NULL, NULL, '2026-04-05 21:12:38', NULL, 0, 0, 0, 0, 0),
(38, 'Bob Durand', 'bob@example.com', NULL, NULL, '2026-04-05 21:12:38', NULL, 0, 0, 0, 0, 0),
(39, 'Charlie Leroy', 'charlie@example.com', NULL, NULL, '2026-04-05 21:12:38', NULL, 0, 0, 0, 0, 0),
(40, 'David Petit', 'david@example.com', NULL, NULL, '2026-04-05 21:12:38', NULL, 0, 0, 0, 0, 0),
(41, 'Eve Roux', 'eve@example.com', NULL, NULL, '2026-04-05 21:12:38', NULL, 0, 0, 0, 0, 0),
(42, 'Frank Zappa', 'frank@music.com', NULL, NULL, '2026-04-05 21:12:38', NULL, 0, 0, 0, 0, 0),
(43, 'Grace Hopper', 'grace@compiler.com', NULL, NULL, '2026-04-05 21:12:38', NULL, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `candidature`
--

CREATE TABLE `candidature` (
  `id_candidature` bigint NOT NULL,
  `date_postulation` datetime NOT NULL,
  `etat_avancement` varchar(50) NOT NULL,
  `score_matching` decimal(5,2) DEFAULT NULL,
  `source_candidature` varchar(100) DEFAULT NULL,
  `fk_offre_id` bigint DEFAULT NULL,
  `fk_candidat_id` bigint DEFAULT NULL,
  `notes` longtext
) ;

--
-- Dumping data for table `candidature`
--

INSERT INTO `candidature` (`id_candidature`, `date_postulation`, `etat_avancement`, `score_matching`, `source_candidature`, `fk_offre_id`, `fk_candidat_id`, `notes`) VALUES
(37, '2026-04-05 21:12:38', 'OFFRE_FAITE', '76.40', 'Indeed', 24, 37, NULL),
(38, '2026-04-05 21:12:38', 'RECU', '74.74', 'Referral', 28, 38, NULL),
(39, '2026-04-05 21:12:38', 'EN_ENTRETIEN', '84.70', 'Referral', 24, 39, NULL),
(40, '2026-04-05 21:12:38', 'EN_ENTRETIEN', '86.97', 'Indeed', 28, 40, NULL),
(41, '2026-04-05 21:12:38', 'RECU', '81.06', 'Referral', 25, 41, NULL),
(42, '2026-04-05 21:12:38', 'REJETE', '62.52', 'Referral', 26, 42, NULL),
(43, '2026-04-05 21:12:38', 'REJETE', '83.89', 'Referral', 24, 43, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `competence`
--

CREATE TABLE `competence` (
  `id` int NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `competence`
--

INSERT INTO `competence` (`id`, `nom`, `categorie`, `description`) VALUES
(1, 'Java', 'Programmation', NULL),
(2, 'Python', 'Programmation', NULL),
(3, 'Spring Boot', 'Framework', NULL),
(4, 'React', 'Framework', NULL),
(7, 'MySQL', 'Base de donn??es', NULL),
(9, 'MongoDB', 'Base de données', 'Base de données NoSQL orientée documents'),
(10, 'Docker', 'DevOps', 'Plateforme de conteneurisation'),
(11, 'Kubernetes', 'DevOps', 'Orchestration de conteneurs'),
(12, 'AWS', 'Cloud', 'Services cloud d\'Amazon'),
(13, 'Azure', 'Cloud', 'Services cloud de Microsoft'),
(14, 'Git', 'Outils', 'Système de contrôle de version'),
(15, 'Jenkins', 'DevOps', 'Serveur d\'intégration continue'),
(16, 'Figma', 'Design', 'Outil de design collaboratif'),
(17, 'Photoshop', 'Design', 'Logiciel de retouche d\'image'),
(18, 'Scrum', 'Méthodologie', 'Méthode agile de gestion de projet'),
(19, 'Kanban', 'Méthodologie', 'Méthode de gestion visuelle des flux'),
(20, 'Leadership', 'Soft Skills', 'Capacité à diriger une équipe');

-- --------------------------------------------------------

--
-- Table structure for table `departement`
--

CREATE TABLE `departement` (
  `id_departement` int NOT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departement`
--

INSERT INTO `departement` (`id_departement`, `libelle`) VALUES
(1, 'Informatique'),
(2, 'RH'),
(3, 'Marketing'),
(4, 'Finance');

-- --------------------------------------------------------

--
-- Table structure for table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260405231513', '2026-04-05 23:32:26', 539);

-- --------------------------------------------------------

--
-- Table structure for table `entretien`
--

CREATE TABLE `entretien` (
  `id_entretien` bigint NOT NULL,
  `date_heure_debut` datetime NOT NULL,
  `lien_visio_salle` varchar(255) DEFAULT NULL,
  `statut_entretien` varchar(50) NOT NULL,
  `candidature_id` bigint DEFAULT NULL,
  `type_id` bigint DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `evaluation`
--

CREATE TABLE `evaluation` (
  `id_evaluation` int NOT NULL,
  `date_creation` datetime NOT NULL,
  `commentaire_global` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `decision_preliminaire` enum('FAVORABLE','DEFAVORABLE','A_REVOIR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fk_candidat_id` int NOT NULL,
  `fk_recruteur_id` int NOT NULL,
  `review_deadline` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evaluation`
--

INSERT INTO `evaluation` (`id_evaluation`, `date_creation`, `commentaire_global`, `decision_preliminaire`, `fk_candidat_id`, `fk_recruteur_id`, `review_deadline`) VALUES
(49, '2026-03-03 10:00:00', 'Excellent profil technique, très motivé.', 'FAVORABLE', 2, 5, NULL),
(50, '2026-03-03 10:30:00', 'Manque d expérience sur les frameworks demandés.', 'DEFAVORABLE', 3, 5, NULL),
(52, '2026-03-03 11:15:00', 'Très bon fit culturel avec l équipe.', 'FAVORABLE', 3, 6, NULL),
(53, '2026-03-03 11:45:00', 'Compétences techniques trop justes pour un poste senior.', 'DEFAVORABLE', 2, 6, NULL),
(55, '2026-03-03 14:00:00', 'Maîtrise parfaite du Cloud et DevOps.', 'FAVORABLE', 4, 7, NULL),
(56, '2026-03-03 14:30:00', 'Difficultés de communication en anglais.', 'DEFAVORABLE', 2, 7, NULL),
(57, '2026-03-03 15:00:00', 'Besoin d un deuxième avis technique.', 'A_REVOIR', 3, 7, '2026-03-12'),
(58, '2026-03-03 10:19:23', 'tres bien', 'FAVORABLE', 2, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offre_competence`
--

CREATE TABLE `offre_competence` (
  `offre_id` bigint NOT NULL,
  `competence_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offre_emploi`
--

CREATE TABLE `offre_emploi` (
  `id_offre` bigint NOT NULL,
  `titre_poste` varchar(255) NOT NULL,
  `description` longtext,
  `departement` varchar(100) NOT NULL,
  `date_cloture` date DEFAULT NULL,
  `statut_offre` varchar(50) NOT NULL,
  `date_creation` datetime NOT NULL,
  `date_modification` datetime DEFAULT NULL,
  `fk_departement_id` int DEFAULT NULL,
  `salaire_propose` double DEFAULT NULL,
  `devise` varchar(10) DEFAULT NULL
) ;

--
-- Dumping data for table `offre_emploi`
--

INSERT INTO `offre_emploi` (`id_offre`, `titre_poste`, `description`, `departement`, `date_cloture`, `statut_offre`, `date_creation`, `date_modification`, `fk_departement_id`, `salaire_propose`, `devise`) VALUES
(17, 'Développeur FullStack Java', 'Nous recherchons un développeur Java/Angular passionné...', 'Informatique', NULL, 'Publiee', '2026-03-02 03:52:04', NULL, 1, 0, 'EUR'),
(18, 'Ingénieur DevOps', 'Expertise en Kubernetes, Docker et CI/CD requise.', 'Infrastructure', NULL, 'Publiee', '2026-03-02 03:52:04', NULL, 1, 0, 'TND'),
(19, 'Product Manager', 'Pilotage de projets agiles et définition de roadmap.', 'Produit', NULL, 'Publiee', '2026-03-02 03:52:04', NULL, 1, 0, 'TND'),
(21, 'Data Scientist', 'Analyse de données complexes et modèles prédictifs.', 'Data & AI', NULL, 'Publiee', '2026-03-02 03:52:04', NULL, 1, 0, 'TND'),
(24, 'Développeur FullStack Java', 'Nous recherchons un développeur Java/Angular passionné...', 'Informatique', NULL, 'Publiee', '2026-04-05 21:12:38', NULL, 1, 0, 'TND'),
(25, 'Ingénieur DevOps', 'Expertise en Kubernetes, Docker et CI/CD requise.', 'Infrastructure', NULL, 'Publiee', '2026-04-05 21:12:38', NULL, 1, 0, 'TND'),
(26, 'Product Manager', 'Pilotage de projets agiles et définition de roadmap.', 'Produit', NULL, 'Publiee', '2026-04-05 21:12:38', NULL, 1, 0, 'TND'),
(27, 'UI/UX Designer', 'Création d\'interfaces élégantes et centrées utilisateur.', 'Design', NULL, 'Publiee', '2026-04-05 21:12:38', NULL, 1, 0, 'TND'),
(28, 'Data Scientist', 'Analyse de données complexes et modèles prédictifs.', 'Data & AI', NULL, 'Publiee', '2026-04-05 21:12:38', NULL, 1, 0, 'TND'),
(29, 'gg,,jhku', 'jfj,hrfjhkgyk', 'info', '2026-04-12', 'PUBLIEE', '2026-04-05 20:19:03', NULL, 1, 1000, 'TND');

-- --------------------------------------------------------

--
-- Table structure for table `planification`
--

CREATE TABLE `planification` (
  `id_event` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `fk_candidature_id` bigint DEFAULT NULL,
  `type_event` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `mode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `lien_meeting` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `localisation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `planification`
--

INSERT INTO `planification` (`id_event`, `user_id`, `fk_candidature_id`, `type_event`, `date`, `mode`, `statut`, `description`, `lien_meeting`, `localisation`, `heure_debut`, `heure_fin`) VALUES
(2, NULL, NULL, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-02-26', 'Présentiel', 'Terminé', 'Discussion motivation', '', '', '00:00:00', '00:00:00'),
(3, NULL, NULL, 'Entretien initial', '2026-02-28', 'En ligne', 'Prévu', 'Présentation profil', 'https://meet.google.com/test2', NULL, '00:00:00', '00:00:00'),
(4, NULL, NULL, 'jngb', '2025-12-12', '', '', '', '', NULL, '00:00:00', '00:00:00'),
(16, NULL, NULL, 'New Entry', '2026-02-17', 'En ligne', 'Prévu', '', '', NULL, '00:00:00', '00:00:00'),
(19, NULL, NULL, 'Nouvelle entrée', '2026-02-23', 'En ligne', 'Prévu', '', '', NULL, '00:00:00', '00:00:00'),
(21, NULL, NULL, 'Nouvelle entrée', '2026-02-23', 'En ligne', 'Prévu', '', '', NULL, '00:00:00', '00:00:00'),
(22, NULL, NULL, 'Nouvelle entrée', '2026-02-23', 'En ligne', 'Prévu', '', '', NULL, '00:00:00', '00:00:00'),
(24, NULL, NULL, 'faycel', '2026-02-23', '', 'Prévu', '', '', '', '00:00:00', '00:00:00'),
(25, NULL, NULL, 'ons', '2026-02-23', '', 'Prévu', '', '', 'Pôle Technologique, 1, 2 rue André Ampère, Cebalat 2083', '00:00:00', '00:00:00'),
(26, NULL, NULL, 'aaaa', '2026-02-12', 'Présentiel', 'Prévu', '', '', NULL, '00:00:00', '00:00:00'),
(27, NULL, NULL, 'eeeeeee', '2026-02-11', 'Présentiel', 'Prévu', '', '', 'Pôle Technologique, 1, 2 rue André Ampère, Cebalat 2083', '00:00:00', '00:00:00'),
(28, NULL, NULL, 'aaaaaaaaaaaaaaaaaaaaaaa', '2026-02-04', 'Présentiel', 'Prévu', '', '', 'Pôle Technologique, 1, 2 rue André Ampère, Cebalat 2083', '00:00:00', '00:00:00'),
(29, NULL, NULL, 'gggg', '2026-02-05', 'Présentiel', 'Prévu', '', '', '', '00:00:00', '00:00:00'),
(30, NULL, NULL, 'zzzzzsszzzzzz', '2026-02-10', 'Présentiel', 'Prévu', '', '', '', '00:00:00', '00:00:00'),
(31, NULL, NULL, 'zzz', '2026-01-01', 'Présentiel', 'Prévu', '', '', '', '00:00:00', '00:00:00'),
(32, NULL, NULL, 'yayayayayyyayyya', '2026-03-03', 'Présentiel', 'Prévu', '', '', '', '00:00:00', '00:00:00'),
(33, NULL, NULL, 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz', '2026-03-04', 'Présentiel', 'Prévu', '', '', '', '00:00:00', '00:00:00'),
(34, NULL, NULL, 'yayayayyayayay', '2026-03-10', 'Présentiel', 'Prévu', '', '', '', '00:00:00', '00:00:00'),
(35, NULL, NULL, 'dddddssdsddsdsdsdsdds', '2026-03-12', 'Présentiel', 'Prévu', '', '', '', '00:00:00', '00:00:00'),
(36, NULL, NULL, 'leeeeeeeeeeeeeeeeeeeeee', '2026-03-17', 'Présentiel', 'Prévu', '', '', '', '00:00:00', '00:00:00'),
(37, NULL, NULL, 'zzzzzzzzzzzzzzzzzzzzz', '2026-03-11', 'Présentiel', 'Prévu', '', '', '', '00:00:00', '00:00:00'),
(39, NULL, NULL, 'yyyyyyyyyyyoooooooo', '2026-03-18', 'Présentiel', 'Prévu', '', '', '', '00:00:00', '00:00:00'),
(40, NULL, NULL, 'eeeeeeeeeeeeeeeee', '2026-03-13', 'Présentiel', 'Prévu', '', '', '', '00:00:00', '00:00:00'),
(41, NULL, NULL, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-03-19', 'Présentiel', 'Prévu', '', '', '', '00:00:00', '00:00:00'),
(42, NULL, NULL, 'sarrrraa', '2026-03-20', 'Présentiel', 'Prévu', '', '', '', '00:00:00', '00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `profil_talent`
--

CREATE TABLE `profil_talent` (
  `id_profil` bigint NOT NULL,
  `titre_professionnel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resume_bio` longtext COLLATE utf8mb4_unicode_ci,
  `niveau_experience` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disponibilite` date DEFAULT NULL,
  `etat_vivier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fk_user_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `id` int NOT NULL,
  `planification_id` int DEFAULT NULL,
  `rating` int NOT NULL,
  `commentaire` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `statut` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`id`, `planification_id`, `rating`, `commentaire`, `created_at`, `statut`) VALUES
(1, 2, 5, '', '2026-03-03 10:31:08', 'NEEDS_FOLLOWUP'),
(2, 3, 5, '', '2026-03-03 10:31:36', 'NEEDS_FOLLOWUP');

-- --------------------------------------------------------

--
-- Table structure for table `score_competence`
--

CREATE TABLE `score_competence` (
  `id_detail` int NOT NULL,
  `nom_critere` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note_attribuee` float NOT NULL,
  `appreciation_specifique` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fk_evaluation_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `score_competence`
--

INSERT INTO `score_competence` (`id_detail`, `nom_critere`, `note_attribuee`, `appreciation_specifique`, `fk_evaluation_id`) VALUES
(1, 'Expertise Java', 18, 'Maîtrise parfaite', 49),
(2, 'Soft Skills', 16, 'Très bon relationnel', 49),
(3, 'Expérience Pro', 15, 'Solide bagage', 49),
(4, 'Formation', 17, 'Cursus prestigieux', 49),
(5, 'Langues', 14, 'Niveau B2 correct', 49),
(6, 'Expertise Java', 8, 'Lacunes importantes', 50),
(7, 'Soft Skills', 12, 'Correct', 50),
(8, 'Expérience Pro', 7, 'Trop junior pour le poste', 50),
(9, 'Formation', 14, 'Bac+5 validé', 50),
(10, 'Langues', 10, 'Anglais limité', 50),
(16, 'Expertise React', 15, 'Bien maîtrisé', 52),
(17, 'Soft Skills', 19, 'Exceptionnel', 52),
(18, 'Expérience Pro', 14, 'Expérience variée', 52),
(19, 'Formation', 15, 'Diplôme reconnu', 52),
(20, 'Langues', 13, 'Francophone', 52),
(21, 'Expertise React', 7, 'Ne connaît pas le framework', 53),
(22, 'Soft Skills', 10, 'Réservé', 53),
(23, 'Expérience Pro', 9, 'Inadéquat', 53),
(24, 'Formation', 12, 'Classique', 53),
(25, 'Langues', 11, 'Passable', 53),
(31, 'Cloud/AWS', 19, 'Expert', 55),
(32, 'Soft Skills', 15, 'Proactif', 55),
(33, 'Expérience Pro', 17, 'Parcours idéal', 55),
(34, 'Formation', 16, 'Spécialisé Cloud', 55),
(35, 'Langues', 18, 'Fluent', 55),
(36, 'Cloud/AWS', 9, 'Notions seulement', 56),
(37, 'Soft Skills', 8, 'Difficile à évaluer', 56),
(38, 'Expérience Pro', 10, 'Moyenne', 56),
(39, 'Formation', 13, 'Bac+5', 56),
(40, 'Langues', 5, 'Gros point de blocage', 56),
(41, 'Cloud/AWS', 12, 'À approfondir', 57),
(42, 'Soft Skills', 13, 'Sérieux', 57),
(43, 'Expérience Pro', 12, 'Correct', 57),
(44, 'Formation', 14, 'Équilibre correct', 57),
(45, 'Langues', 14, 'Correct', 57),
(46, 'java', 20, NULL, 58);

-- --------------------------------------------------------

--
-- Table structure for table `talent`
--

CREATE TABLE `talent` (
  `id` int NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poste` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departement` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_embauche` date DEFAULT NULL,
  `annees_experience` int DEFAULT NULL,
  `niveau_etudes` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `talent`
--

INSERT INTO `talent` (`id`, `nom`, `prenom`, `email`, `telephone`, `poste`, `departement`, `date_embauche`, `annees_experience`, `niveau_etudes`) VALUES
(1, 'Ben Ali', 'Mohamed', 'mohamed.benali@amira.tn', '+216 20 123 456', 'Développeur Full Stack', 'Informatique', '2020-03-15', 5, 'Bac+5'),
(2, 'Trabelsi', 'Amira', 'amira.trabelsi@amira.tn', '+216 21 234 567', 'Chef de Projet IT', 'Informatique', '2019-06-01', 8, 'Bac+5'),
(3, 'Gharbi', 'Karim', 'karim.gharbi@amira.tn', '+216 22 345 678', 'DevOps Engineer', 'Informatique', '2021-01-10', 4, 'Bac+4'),
(4, 'Saidi', 'Fatma', 'fatma.saidi@amira.tn', '+216 23 456 789', 'Designer UX/UI', 'Marketing', '2020-09-20', 3, 'Bac+3'),
(5, 'Bouaziz', 'Ahmed', 'ahmed.bouaziz@amira.tn', '+216 24 567 890', 'Responsable RH', 'Ressources Humaines', '2018-04-12', 10, 'Bac+5'),
(6, 'Mansour', 'Leila', 'leila.mansour@amira.tn', '+216 25 678 901', 'Comptable Senior', 'Finance', '2017-11-05', 12, 'Bac+4'),
(7, 'Dridi', 'Youssef', 'youssef.dridi@amira.tn', '+216 26 789 012', 'Développeur Backend', 'Informatique', '2022-02-28', 2, 'Bac+3'),
(8, 'Feki', 'Sana', 'sana.feki@amira.tn', '+216 27 890 123', 'Responsable Marketing', 'Marketing', '2019-08-15', 6, 'Bac+5'),
(9, 'Jaziri', 'Omar', 'omar.jaziri@amira.tn', '+216 28 901 234', 'Administrateur Système', 'Informatique', '2021-07-01', 4, 'Bac+4'),
(10, 'Kallel', 'Nadia', 'nadia.kallel@amira.tn', '+216 29 012 345', 'Business Analyst', 'Finance', '2020-05-18', 5, 'Bac+5'),
(11, 'Mejri', 'Hatem', 'hatem.mejri@amira.tn', '+216 20 111 222', 'Développeur Mobile', 'Informatique', '2021-09-10', 3, 'Bac+3'),
(12, 'Riahi', 'Rim', 'rim.riahi@amira.tn', '+216 21 222 333', 'Chargée de Recrutement', 'Ressources Humaines', '2022-01-15', 2, 'Bac+4'),
(13, 'Sassi', 'Tarek', 'tarek.sassi@amira.tn', '+216 22 333 444', 'Data Scientist', 'Informatique', '2020-12-01', 4, 'Bac+5'),
(14, 'Touati', 'Mouna', 'mouna.touati@amira.tn', '+216 23 444 555', 'Community Manager', 'Marketing', '2021-03-20', 3, 'Bac+3'),
(15, 'Yahyaoui', 'Sami', 'sami.yahyaoui@amira.tn', '+216 24 555 666', 'Architecte Cloud', 'Informatique', '2018-06-15', 9, 'Bac+5'),
(17, 'Amari', 'Wael', 'wael.amari@amira.tn', '+216 26 777 888', 'Développeur Frontend', 'Informatique', '2022-06-01', 1, 'Bac+3'),
(18, 'Brahmi', 'Asma', 'asma.brahmi@amira.tn', '+216 27 888 999', 'Responsable Communication', 'Marketing', '2020-10-12', 5, 'Bac+5'),
(19, 'Chaari', 'Marwen', 'marwen.chaari@amira.tn', '+216 28 999 000', 'Ingénieur Sécurité', 'Informatique', '2019-02-28', 6, 'Bac+4'),
(20, 'Dahmani', 'Lamia', 'lamia.dahmani@amira.tn', '+216 29 000 111', 'Consultante RH', 'Ressources Humaines', '2021-11-08', 3, 'Bac+5');

-- --------------------------------------------------------

--
-- Table structure for table `talent_competence`
--

CREATE TABLE `talent_competence` (
  `id` int NOT NULL,
  `talent_id` int DEFAULT NULL,
  `competence_id` int DEFAULT NULL,
  `niveau_maitrise` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `annees_pratique` int DEFAULT NULL,
  `date_acquisition` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `type_entretien`
--

CREATE TABLE `type_entretien` (
  `id_type` bigint NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `duree_standard_minutes` int NOT NULL,
  `directives_recruteur` longtext,
  `est_virtuel` tinyint NOT NULL
) ;

--
-- Dumping data for table `type_entretien`
--

INSERT INTO `type_entretien` (`id_type`, `libelle`, `duree_standard_minutes`, `directives_recruteur`, `est_virtuel`) VALUES
(1, 'Entretien RH', 30, 'Evaluation generale du profil, motivation et fit culturel', 1),
(2, 'Test Technique', 90, 'Evaluation des competences techniques via exercices pratiques', 0),
(3, 'Entretien Manager', 60, 'Discussion approfondie sur l experience et les competences manageriales', 1),
(4, 'Entretien Final', 45, 'Presentation de l offre et negociation', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `is_active` tinyint NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `first_name`, `last_name`, `is_active`, `created_at`) VALUES
(1, 'admin@nexus.com', '[\"ROLE_ADMIN\"]', '$2a$10$aSzc4UezdSPqp97v/z0POekqaHyv25eM2bNj.dHe06oRWEJl7Bu6m', 'Admin', 'NEXUS', 1, '2026-03-02 01:43:16'),
(2, 'moncef@esprit.com', '[\"ROLE_CANDIDAT\"]', '$2a$10$NrwnUMhY0sDIxHQIhoh6MOksAwsTKvXFih.gMp/K1vsvU/n.d0uqW', 'moncef', 'ben salem', 1, '2026-03-02 01:43:16'),
(3, 'amira@esprit.tn', '[\"ROLE_CANDIDAT\"]', '$2a$10$NrwnUMhY0sDIxHQIhoh6MOksAwsTKvXFih.gMp/K1vsvU/n.d0uqW', 'amira', 'matteli', 1, '2026-03-02 01:43:16'),
(4, 'haifagaied2@gmail.com', '[\"ROLE_CANDIDAT\"]', '$2a$10$QpQEjPDpDFckEm954IOeLuU5U8q6dhEB.R1HFiLFiQmZtEUiMv1SS', 'Haifa', 'Gaied', 1, '2026-03-02 00:44:06'),
(5, 'mayssemchouria5@gmail.com', '[\"ROLE_CANDIDAT\"]', '$2a$10$FdJoExck3pOD2a2/RiBt3.vlroNZkQ8YsbrBH0/urBdSNWXGxVxjK', 'mayssem', 'chouria', 1, '2026-03-03 09:48:01'),
(6, 'youssef2@gmail.com', '[\"ROLE_CANDIDAT\"]', '$2a$10$xAcig43uAVl1SDg6bI.1leOkPClj.gzLa4N2RzKvgREjnEwI06u8S', 'youssef', 'benhiba', 1, '2026-04-04 22:52:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidat`
--
ALTER TABLE `candidat`
  ADD PRIMARY KEY (`id_candidat`);

--
-- Indexes for table `candidature`
--
ALTER TABLE `candidature`
  ADD PRIMARY KEY (`id_candidature`),
  ADD KEY `IDX_E33BD3B87EE01384` (`fk_offre_id`),
  ADD KEY `IDX_E33BD3B8612647B6` (`fk_candidat_id`);

--
-- Indexes for table `competence`
--
ALTER TABLE `competence`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departement`
--
ALTER TABLE `departement`
  ADD PRIMARY KEY (`id_departement`);

--
-- Indexes for table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `entretien`
--
ALTER TABLE `entretien`
  ADD PRIMARY KEY (`id_entretien`),
  ADD KEY `IDX_2B58D6DAB6121583` (`candidature_id`),
  ADD KEY `IDX_2B58D6DAC54C8C93` (`type_id`);

--
-- Indexes for table `evaluation`
--
ALTER TABLE `evaluation`
  ADD PRIMARY KEY (`id_evaluation`),
  ADD KEY `idx_entretien` (`fk_candidat_id`),
  ADD KEY `idx_recruteur` (`fk_recruteur_id`);

--
-- Indexes for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`);

--
-- Indexes for table `offre_competence`
--
ALTER TABLE `offre_competence`
  ADD PRIMARY KEY (`offre_id`,`competence_id`),
  ADD KEY `IDX_B98A0F5A4CC8505A` (`offre_id`),
  ADD KEY `IDX_B98A0F5A15761DAB` (`competence_id`);

--
-- Indexes for table `offre_emploi`
--
ALTER TABLE `offre_emploi`
  ADD PRIMARY KEY (`id_offre`),
  ADD KEY `idx_offre_statut` (`statut_offre`),
  ADD KEY `idx_offre_departement` (`departement`),
  ADD KEY `idx_offre_date_cloture` (`date_cloture`),
  ADD KEY `IDX_132AD0D1B9F709F7` (`fk_departement_id`);

--
-- Indexes for table `planification`
--
ALTER TABLE `planification`
  ADD PRIMARY KEY (`id_event`),
  ADD KEY `IDX_FFC02E1BC31CFC6A` (`fk_candidature_id`),
  ADD KEY `FK_FFC02E1BA76ED395` (`user_id`);

--
-- Indexes for table `profil_talent`
--
ALTER TABLE `profil_talent`
  ADD PRIMARY KEY (`id_profil`),
  ADD KEY `IDX_67268BA75741EEB9` (`fk_user_id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_794381C6E65142C2` (`planification_id`);

--
-- Indexes for table `score_competence`
--
ALTER TABLE `score_competence`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `idx_evaluation` (`fk_evaluation_id`);

--
-- Indexes for table `talent`
--
ALTER TABLE `talent`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `talent_competence`
--
ALTER TABLE `talent_competence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_98E4CDA18777CEF` (`talent_id`),
  ADD KEY `IDX_98E4CDA15761DAB` (`competence_id`);

--
-- Indexes for table `type_entretien`
--
ALTER TABLE `type_entretien`
  ADD PRIMARY KEY (`id_type`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidat`
--
ALTER TABLE `candidat`
  MODIFY `id_candidat` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidature`
--
ALTER TABLE `candidature`
  MODIFY `id_candidature` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competence`
--
ALTER TABLE `competence`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `departement`
--
ALTER TABLE `departement`
  MODIFY `id_departement` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `entretien`
--
ALTER TABLE `entretien`
  MODIFY `id_entretien` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offre_emploi`
--
ALTER TABLE `offre_emploi`
  MODIFY `id_offre` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `planification`
--
ALTER TABLE `planification`
  MODIFY `id_event` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `profil_talent`
--
ALTER TABLE `profil_talent`
  MODIFY `id_profil` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `talent`
--
ALTER TABLE `talent`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `talent_competence`
--
ALTER TABLE `talent_competence`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `type_entretien`
--
ALTER TABLE `type_entretien`
  MODIFY `id_type` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidature`
--
ALTER TABLE `candidature`
  ADD CONSTRAINT `fk_candidature_candidat` FOREIGN KEY (`fk_candidat_id`) REFERENCES `candidat` (`id_candidat`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_candidature_offre` FOREIGN KEY (`fk_offre_id`) REFERENCES `offre_emploi` (`id_offre`) ON DELETE CASCADE;

--
-- Constraints for table `entretien`
--
ALTER TABLE `entretien`
  ADD CONSTRAINT `FK_2B58D6DAC54C8C93` FOREIGN KEY (`type_id`) REFERENCES `type_entretien` (`id_type`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_entretien_candidature` FOREIGN KEY (`candidature_id`) REFERENCES `candidature` (`id_candidature`) ON DELETE CASCADE;

--
-- Constraints for table `offre_competence`
--
ALTER TABLE `offre_competence`
  ADD CONSTRAINT `FK_B98A0F5A15761DAB` FOREIGN KEY (`competence_id`) REFERENCES `competence` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_B98A0F5A4CC8505A` FOREIGN KEY (`offre_id`) REFERENCES `offre_emploi` (`id_offre`) ON DELETE CASCADE;

--
-- Constraints for table `offre_emploi`
--
ALTER TABLE `offre_emploi`
  ADD CONSTRAINT `FK_132AD0D1B9F709F7` FOREIGN KEY (`fk_departement_id`) REFERENCES `departement` (`id_departement`);

--
-- Constraints for table `planification`
--
ALTER TABLE `planification`
  ADD CONSTRAINT `FK_FFC02E1BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_planif_cand` FOREIGN KEY (`fk_candidature_id`) REFERENCES `candidature` (`id_candidature`) ON DELETE CASCADE;

--
-- Constraints for table `profil_talent`
--
ALTER TABLE `profil_talent`
  ADD CONSTRAINT `fk_profil_user` FOREIGN KEY (`fk_user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `FK_794381C6E65142C2` FOREIGN KEY (`planification_id`) REFERENCES `planification` (`id_event`) ON DELETE CASCADE;

--
-- Constraints for table `talent_competence`
--
ALTER TABLE `talent_competence`
  ADD CONSTRAINT `fk_tc_comp` FOREIGN KEY (`competence_id`) REFERENCES `competence` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tc_talent` FOREIGN KEY (`talent_id`) REFERENCES `talent` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
