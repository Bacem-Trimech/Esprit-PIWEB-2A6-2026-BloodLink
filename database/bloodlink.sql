-- Base de données : gslaboratoire (2 entités : analyse_medicale et compte_rendu_analyse)
CREATE DATABASE IF NOT EXISTS `gslaboratoire` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gslaboratoire`;

-- --------------------------------------------------------
-- Entité 1 : AnalyseMedicale
-- --------------------------------------------------------

CREATE TABLE `analyse_medicale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type_echantillon` varchar(30) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `delai_heures` int(11) NOT NULL,
  `a_jeun` tinyint(1) NOT NULL DEFAULT 0,
  `date_ajout` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Entité 2 : CompteRenduAnalyse (lié à une analyse)
-- --------------------------------------------------------

CREATE TABLE `compte_rendu_analyse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_analyse` int(11) NOT NULL,
  `nom_patient` varchar(100) NOT NULL,
  `date_prelevement` date NOT NULL,
  `resultat` text NOT NULL,
  `interpretation` varchar(10) NOT NULL,
  `commentaire` varchar(255) DEFAULT NULL,
  `date_creation` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_analyse` (`id_analyse`),
  CONSTRAINT `compte_rendu_analyse_ibfk_1` FOREIGN KEY (`id_analyse`) REFERENCES `analyse_medicale` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Données de test
-- --------------------------------------------------------

INSERT INTO `analyse_medicale` (`id`, `nom`, `description`, `type_echantillon`, `prix`, `delai_heures`, `a_jeun`, `date_ajout`) VALUES
(1, 'Numération formule sanguine', 'Mesure des globules rouges, blancs et plaquettes', 'Sang', 18.00, 24, 0, CURDATE()),
(2, 'Glycémie à jeun', 'Taux de sucre dans le sang', 'Sang', 6.50, 4, 1, CURDATE()),
(3, 'Examen cytobactériologique des urines', 'Recherche d''infection urinaire', 'Urine', 22.00, 48, 0, CURDATE()),
(4, 'Bilan lipidique', 'Cholestérol total, HDL, LDL et triglycérides', 'Sang', 30.00, 24, 1, CURDATE());

INSERT INTO `compte_rendu_analyse` (`id_analyse`, `nom_patient`, `date_prelevement`, `resultat`, `interpretation`, `commentaire`, `date_creation`) VALUES
(1, 'Ahmed Ben Salah', CURDATE(), 'Globules rouges : 4,8 M/mm3\nGlobules blancs : 7 200/mm3\nPlaquettes : 250 000/mm3', 'Normal', NULL, CURDATE()),
(2, 'Salma Trabelsi', CURDATE(), 'Glycémie : 1,45 g/L', 'Anormal', 'Contrôle conseillé avec le médecin traitant', CURDATE()),
(4, 'Ahmed Ben Salah', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Cholestérol total : 1,70 g/L\nLDL : 0,95 g/L\nTriglycérides : 0,90 g/L', 'Normal', NULL, DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
(3, 'Mariem Gharbi', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Présence de leucocytes : 10^5/mL', 'Anormal', 'Antibiogramme à demander', CURDATE());
