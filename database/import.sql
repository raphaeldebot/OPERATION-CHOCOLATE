-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema `maison_rosalie`
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema `maison_rosalie`
-- -----------------------------------------------------
DROP DATABASE IF EXISTS maison_rosalie;

CREATE DATABASE maison_rosalie
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE maison_rosalie;

-- -----------------------------------------------------
-- Table `maison_rosalie`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maison_rosalie`.`user` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `user_email` VARCHAR(45) NOT NULL,
  `user_login` VARCHAR(45) NOT NULL,
  `user_pwd` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `user_email_UNIQUE` (`user_email` ASC) VISIBLE,
  UNIQUE INDEX `user_login_UNIQUE` (`user_login` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `maison_rosalie`.`category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maison_rosalie`.`category` (
  `category_id` INT NOT NULL AUTO_INCREMENT,
  `category_title` VARCHAR(45) NOT NULL,
  `category_slug_title` VARCHAR(100) NOT NULL,
  `category_description` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE INDEX `category_slug_title_UNIQUE` (`category_slug_title` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `maison_rosalie`.`recipe`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maison_rosalie`.`recipe` (
  `recipe_id` INT NOT NULL AUTO_INCREMENT,
  `recipe_title` VARCHAR(100) NOT NULL,
  `recipe_slug_title` VARCHAR(100) NOT NULL,
  `recipe_description` TEXT NOT NULL,
  `main_pic` VARCHAR(255) NOT NULL,
  `prep_time` INT NOT NULL,
  `cooking_time` INT NOT NULL,
  `portion` INT NOT NULL,
  `difficulty` ENUM('facile', 'moyen', 'difficile') NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author_id` INT NOT NULL,
  PRIMARY KEY (`recipe_id`),
  UNIQUE INDEX `recipe_slug_title_UNIQUE` (`recipe_slug_title` ASC) VISIBLE,
  INDEX `fk_recipe_user_idx` (`author_id` ASC) VISIBLE,
  CONSTRAINT `fk_recipe_user`
    FOREIGN KEY (`author_id`)
    REFERENCES `maison_rosalie`.`user` (`user_id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `maison_rosalie`.`ingredient`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maison_rosalie`.`ingredient` (
  `ingredient_id` INT NOT NULL AUTO_INCREMENT,
  `ingredient_name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`ingredient_id`),
  UNIQUE INDEX `ingredient_name_UNIQUE` (`ingredient_name` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `maison_rosalie`.`step`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maison_rosalie`.`step` (
  `step_id` INT NOT NULL AUTO_INCREMENT,
  `recipe_id` INT NOT NULL,
  `step_order` INT NOT NULL,
  `step_title` VARCHAR(100) NOT NULL,
  `step_texte` TEXT NOT NULL,
  `step_pic` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`step_id`),
  INDEX `fk_step_recipe_idx` (`recipe_id` ASC) VISIBLE,
  UNIQUE INDEX `uq_step_recipe_order` (`recipe_id` ASC, `step_order` ASC) VISIBLE,
  CONSTRAINT `fk_step_recipe`
    FOREIGN KEY (`recipe_id`)
    REFERENCES `maison_rosalie`.`recipe` (`recipe_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `maison_rosalie`.`comment`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maison_rosalie`.`comment` (
  `id_comment` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `recipe_id` INT NOT NULL,
  `comment_subject` VARCHAR(120) NULL,
  `comment_message` VARCHAR(500) NOT NULL,
  `comment_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comment_status` ENUM('published', 'hidden') NOT NULL DEFAULT 'published',
  PRIMARY KEY (`id_comment`),
  INDEX `fk_comment_user_idx` (`user_id` ASC) VISIBLE,
  INDEX `fk_comment_recipe_idx` (`recipe_id` ASC) VISIBLE,
  CONSTRAINT `fk_comment_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `maison_rosalie`.`user` (`user_id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_comment_recipe`
    FOREIGN KEY (`recipe_id`)
    REFERENCES `maison_rosalie`.`recipe` (`recipe_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `maison_rosalie`.`note`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maison_rosalie`.`note` (
  `note_id` INT NOT NULL AUTO_INCREMENT,
  `recipe_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `note_value` INT NOT NULL,
  `note_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`note_id`),
  INDEX `fk_note_user_idx` (`user_id` ASC) VISIBLE,
  INDEX `fk_note_recipe_idx` (`recipe_id` ASC) VISIBLE,
  UNIQUE INDEX `uq_note_user_recipe` (`user_id` ASC, `recipe_id` ASC) VISIBLE,
  CONSTRAINT `fk_note_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `maison_rosalie`.`user` (`user_id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_note_recipe`
  FOREIGN KEY (`recipe_id`)
  REFERENCES `maison_rosalie`.`recipe` (`recipe_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,

  CONSTRAINT `chk_note_value`
  CHECK (`note_value` BETWEEN 1 AND 5))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `maison_rosalie`.`messageofcontact`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maison_rosalie`.`messageofcontact` (
  `id_contact_message` INT NOT NULL AUTO_INCREMENT,
  `contact_message_name` VARCHAR(100) NOT NULL,
  `contact_message_email` VARCHAR(255) NOT NULL,
  `contact_message_sujet` VARCHAR(120) NOT NULL,
  `contact_message_message` TEXT NOT NULL,
  `contact_message_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contact_message`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `maison_rosalie`.`recipe_ingredient`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maison_rosalie`.`recipe_ingredient` (
  `recipe_id` INT NOT NULL,
  `ingredient_id` INT NOT NULL,
  `recipe_ingredient_quantity` DECIMAL(8,2) NULL,
  `recipe_ingredient_unity` VARCHAR(45) NULL,
  PRIMARY KEY (`recipe_id`, `ingredient_id`),
  INDEX `fk_recipe_ingredient_ingredient_idx` (`ingredient_id` ASC) VISIBLE,
  CONSTRAINT `fk_recipe_ingredient_recipe`
    FOREIGN KEY (`recipe_id`)
    REFERENCES `maison_rosalie`.`recipe` (`recipe_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_recipe_ingredient_ingredient`
    FOREIGN KEY (`ingredient_id`)
    REFERENCES `maison_rosalie`.`ingredient` (`ingredient_id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `maison_rosalie`.`recipe_category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maison_rosalie`.`recipe_category` (
  `recipe_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  PRIMARY KEY (`recipe_id`, `category_id`),
  INDEX `fk_recipe_category_category_idx` (`category_id` ASC) VISIBLE,
  CONSTRAINT `fk_recipe_category_recipe`
    FOREIGN KEY (`recipe_id`)
    REFERENCES `maison_rosalie`.`recipe` (`recipe_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_recipe_category_category`
    FOREIGN KEY (`category_id`)
    REFERENCES `maison_rosalie`.`category` (`category_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


INSERT INTO `maison_rosalie`.`user`
(`user_id`, `user_email`, `user_login`, `user_pwd`, `user_role`)
VALUES
(
    1,
    'admin@maison-rosalie.local',
    'admin_rosalie',
    '$2y$12$jYgVnSq.15loFnA.HjIbXuf/gS1pXS5gD.o7Thz/Mw2iu//R547Hq',
    'admin'
);

-- =====================================================
-- DONNEES RECETTES
-- Source : fiches techniques fournies avec le projet
-- =====================================================


-- -----------------------------------------------------
-- CATEGORIES
-- -----------------------------------------------------

INSERT INTO `maison_rosalie`.`category`
(`category_id`, `category_title`, `category_slug_title`, `category_description`)
VALUES
(1, 'biscuit', 'biscuit', ''),
(2, 'boisson chaude', 'boisson-chaude', ''),
(3, 'gâteau', 'gateau', ''),
(4, 'dessert mousse', 'dessert-mousse', '');


-- -----------------------------------------------------
-- INGREDIENTS
-- -----------------------------------------------------

INSERT INTO `maison_rosalie`.`ingredient`
(`ingredient_id`, `ingredient_name`)
VALUES
(1, 'chocolat noir'),
(2, 'sucre'),
(3, 'farine'),
(4, 'oeuf'),
(5, 'beurre'),
(6, 'lait entier'),
(7, 'crème liquide'),
(8, 'cannelle'),
(9, 'levure');


-- -----------------------------------------------------
-- RECETTES
-- -----------------------------------------------------

INSERT INTO `maison_rosalie`.`recipe`
(
    `recipe_id`,
    `recipe_title`,
    `recipe_slug_title`,
    `recipe_description`,
    `main_pic`,
    `prep_time`,
    `cooking_time`,
    `portion`,
    `difficulty`,
    `author_id`
)
VALUES
(
    1,
    'Brownie-Cookie',
    'brownie-cookie',
    '',
    'assets/images/recipes/brownie-cookie/cover.webp',
    25,
    10,
    15,
    'facile',
    1
),
(
    2,
    'Chocolat chaud à la cannelle',
    'chocolat-chaud-cannelle',
    '',
    'assets/images/recipes/chocolat-chaud-cannelle/cover.webp',
    10,
    15,
    6,
    'facile',
    1
),
(
    3,
    'Fondant au chocolat',
    'fondant-au-chocolat',
    '',
    'assets/images/recipes/fondant-au-chocolat/cover.webp',
    15,
    20,
    6,
    'facile',
    1
),
(
    4,
    'Moelleux au chocolat',
    'moelleurx-au-chocolat',
    '',
    'assets/images/recipes/moelleux-au-chocolat/cover.webp',
    10,
    30,
    6,
    'facile',
    1
),
(
    5,
    'Mousse de chocolat',
    'mousse-au-chocolat',
    '',
    'assets/images/recipes/mousse-au-chocolat/cover.webp',
    30,
    5,
    4,
    'facile',
    1
);


-- -----------------------------------------------------
-- CATEGORIES DES RECETTES
-- -----------------------------------------------------

INSERT INTO `maison_rosalie`.`recipe_category`
(`recipe_id`, `category_id`)
VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 3),
(5, 4);


-- =====================================================
-- INGREDIENTS PAR RECETTE
-- =====================================================


-- Brownie-Cookie

INSERT INTO `maison_rosalie`.`recipe_ingredient`
(`recipe_id`, `ingredient_id`, `recipe_ingredient_quantity`, `recipe_ingredient_unity`)
VALUES
(1, 1, 110, 'g'),
(1, 2, 150, 'g'),
(1, 3, 40, 'g'),
(1, 4, 2, NULL),
(1, 5, 30, 'g');


-- Chocolat chaud à la cannelle

INSERT INTO `maison_rosalie`.`recipe_ingredient`
(`recipe_id`, `ingredient_id`, `recipe_ingredient_quantity`, `recipe_ingredient_unity`)
VALUES
(2, 1, 250, 'g'),
(2, 6, 60, 'cl'),
(2, 7, 40, 'cl'),
(2, 8, 15, 'g');


-- Fondant au chocolat

INSERT INTO `maison_rosalie`.`recipe_ingredient`
(`recipe_id`, `ingredient_id`, `recipe_ingredient_quantity`, `recipe_ingredient_unity`)
VALUES
(3, 1, 200, 'g'),
(3, 5, 200, 'g'),
(3, 2, 200, 'g'),
(3, 3, 100, 'g');


-- Moelleux au chocolat

INSERT INTO `maison_rosalie`.`recipe_ingredient`
(`recipe_id`, `ingredient_id`, `recipe_ingredient_quantity`, `recipe_ingredient_unity`)
VALUES
(4, 1, 200, 'g'),
(4, 5, 125, 'g'),
(4, 9, 30, 'g'),
(4, 3, 125, 'g'),
(4, 2, 250, 'g'),
(4, 4, 4, NULL);


-- Mousse de chocolat

INSERT INTO `maison_rosalie`.`recipe_ingredient`
(`recipe_id`, `ingredient_id`, `recipe_ingredient_quantity`, `recipe_ingredient_unity`)
VALUES
(5, 1, 80, 'g'),
(5, 2, 25, 'g'),
(5, 4, 4, NULL);


-- =====================================================
-- ETAPES : BROWNIE-COOKIE
-- =====================================================

INSERT INTO `maison_rosalie`.`step`
(`recipe_id`, `step_order`, `step_title`, `step_texte`, `step_pic`)
VALUES
(
    1,
    1,
    'Étape 1',
    'Allumer le four à 180°C.',
    'assets/images/recipes/brownie-cookie/step-01.webp'
),
(
    1,
    2,
    'Étape 2',
    'Dans un bol, mixer légèrement les oeufs à l''aide d''un fouet électrique de préférence, puis y ajouter le sucre, le café et battre fort pendant 10 minutes pour que le préparation triple de volume.',
    'assets/images/recipes/brownie-cookie/step-02.webp'
),
(
    1,
    3,
    'Étape 3',
    'Placer le beurre et les deux chocolats en morceaux (pas les pépites) au-dessus d''un bain-marie, et lorsque le mélange est fondu et homogène retirer du feu et mélanger.',
    'assets/images/recipes/brownie-cookie/step-03.webp'
),
(
    1,
    4,
    'Étape 4',
    'Ajouter le chocolat aux oeufs puis incorporer d-él-i-c-a-t-e-m-e-n-t la farine. Rajouter les pépites de chocolat. Laisser reposer dix minutes.',
    'assets/images/recipes/brownie-cookie/step-04.webp'
),
(
    1,
    5,
    'Étape 5',
    'Faire des tas de l''équivalent d''une cuillère à soupe de pâte sur la plaque de four et enfourner 8 à 10 minutes, jusqu''à ce que les cookies se craquèlent.',
    'assets/images/recipes/brownie-cookie/step-05.webp'
),
(
    1,
    6,
    'Étape 6',
    'Laisser refroidir avant de décoller.',
    'assets/images/recipes/brownie-cookie/step-06.webp'
);


-- =====================================================
-- ETAPES : CHOCOLAT CHAUD A LA CANNELLE
-- =====================================================

INSERT INTO `maison_rosalie`.`step`
(`recipe_id`, `step_order`, `step_title`, `step_texte`, `step_pic`)
VALUES
(
    2,
    1,
    'Étape 1',
    'Râper le chocolat. Dans une casserole à fond assez épais, faire frémir le lait avec la crème et la cannelle.',
    ''
),
(
    2,
    2,
    'Étape 2',
    'Retirer la casserole du feu et laisser le mélange infuser. Retirer les bâtons de cannelle et intéger le chocolat.',
    ''
),
(
    2,
    3,
    'Étape 3',
    'Remuer doucement à l''aide d''un fouet : le chocolat doit être parfaitement fondu.',
    ''
),
(
    2,
    4,
    'Étape 4',
    'Laisser la cuisson se poursuivre à feu très doux quelques minutes en fouettant doucement : le chocolat sera encore plus onctueux et crémeux...!',
    ''
);


-- =====================================================
-- ETAPES : FONDANT AU CHOCOLAT
-- =====================================================

INSERT INTO `maison_rosalie`.`step`
(`recipe_id`, `step_order`, `step_title`, `step_texte`, `step_pic`)
VALUES
(
    3,
    1,
    'Étape 1',
    'Préchauffer le four à 150°C (thermostat 5).',
    ''
),
(
    3,
    2,
    'Étape 2',
    'Faire fondre le chocolat avec le beurre dans une casserole.',
    ''
),
(
    3,
    3,
    'Étape 3',
    'Puis verser dans une terrine.',
    ''
),
(
    3,
    4,
    'Étape 4',
    'Ajouter le sucre et mélanger.',
    ''
),
(
    3,
    5,
    'Étape 5',
    'Laisser tiédir, puis ajouter les oeufs et la maïzena.',
    ''
),
(
    3,
    6,
    'Étape 6',
    'Mélanger jusqu''à l''obtention d''une pâte lisse et la verser dans un moule beurré ou muni d''un papier sulfurisé.',
    ''
),
(
    3,
    7,
    'Étape 7',
    'Mettre au four et laisser cuire 15 à 20 min.',
    ''
),
(
    3,
    8,
    'Étape 8',
    'Attendre que le fondant ait refroidi avant de le démouler sinon celui-ci risquerait de se décomposer et le chocolat fondu de couler !!',
    ''
);


-- =====================================================
-- ETAPES : MOELLEUX AU CHOCOLAT
-- =====================================================

INSERT INTO `maison_rosalie`.`step`
(`recipe_id`, `step_order`, `step_title`, `step_texte`, `step_pic`)
VALUES
(
    4,
    1,
    'Étape 1',
    'Préchauffer le four à 180°C (thermostat 6). Faire fondre le chocolat dans une casserole.',
    ''
),
(
    4,
    2,
    'Étape 2',
    'Hors du feu, ajouter le beurre,',
    ''
),
(
    4,
    3,
    'Étape 3',
    'la farine et la levure.',
    ''
),
(
    4,
    4,
    'Étape 4',
    'Fouetter les jaunes d’oeuf avec le sucre',
    ''
),
(
    4,
    5,
    'Étape 5',
    'et un peu d''eau pour faire mousser',
    ''
),
(
    4,
    6,
    'Étape 6',
    'puis l''ajouter à la préparation chocolatée.',
    ''
),
(
    4,
    7,
    'Étape 7',
    'Battre les blancs en neige et les incorporer dans la casserole tout doucement.',
    ''
),
(
    4,
    8,
    'Étape 8',
    'Verser le tout dans un moule beurré à bords hauts',
    ''
),
(
    4,
    9,
    'Étape 9',
    'et enfourner pendant environ 35 minutes.',
    ''
),
(
    4,
    10,
    'Étape 10',
    'On peut ajouter du sucre glace ou du chocolat fondu, pour décorer le dessus du gâteau.',
    ''
);


-- =====================================================
-- ETAPES : MOUSSE DE CHOCOLAT
-- =====================================================

INSERT INTO `maison_rosalie`.`step`
(`recipe_id`, `step_order`, `step_title`, `step_texte`, `step_pic`)
VALUES
(
    5,
    1,
    'Étape 1',
    'A préparer la veille.',
    ''
),
(
    5,
    2,
    'Étape 2',
    'Séparer les blancs d''oeufs des jaunes.',
    ''
),
(
    5,
    3,
    'Étape 3',
    'Mélanger les jaunes avec le sucre jusqu''à ce que le mélange mousse et blanchisse.',
    ''
),
(
    5,
    4,
    'Étape 4',
    'Faire fondre le chocolat au bain-marie et le rajouter au mélange jaune d''oeufs + sucre.',
    ''
),
(
    5,
    5,
    'Étape 5',
    'Monter les blancs d''oeufs en neige avec une pincée de sel.',
    ''
),
(
    5,
    6,
    'Étape 6',
    'Rajouter les blancs délicatement au reste de la préparation en soulevant la masse pour ne pas faire retomber les blancs.',
    ''
),
(
    5,
    7,
    'Étape 7',
    'Mettre au réfrigérateur et consommer le lendemain.',
    ''
),
(
    5,
    8,
    'Étape 8',
    'Avant de la manger, saupoudrer le dessus de la sultane avec des vermicelles colorés ou mettre quelques Smarties pour les enfants, ou les grands !!',
    ''
);

DELIMITER //


CREATE TRIGGER trg_note_no_self_rating_insert
BEFORE INSERT ON note
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1
        FROM recipe
        WHERE recipe_id = NEW.recipe_id
          AND author_id = NEW.user_id
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Un auteur ne peut pas noter sa propre recette';
    END IF;
END//

CREATE TRIGGER trg_note_no_self_rating_update
BEFORE UPDATE ON note
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1
        FROM recipe
        WHERE recipe_id = NEW.recipe_id
          AND author_id = NEW.user_id
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Un auteur ne peut pas noter sa propre recette';
    END IF;
END//

DELIMITER ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
