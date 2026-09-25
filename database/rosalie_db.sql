-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema rosalie
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema rosalie
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `rosalie` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ;
USE `rosalie` ;

-- -----------------------------------------------------
-- Table `rosalie`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rosalie`.`user` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `user_email` VARCHAR(45) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_0900_ai_ci' NOT NULL,
  `user_login` VARCHAR(45) NOT NULL,
  `user_pwd` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `user_email_UNIQUE` (`user_email` ASC) VISIBLE,
  UNIQUE INDEX `user_login_UNIQUE` (`user_login` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rosalie`.`category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rosalie`.`category` (
  `category_id` INT NOT NULL AUTO_INCREMENT,
  `category_title` VARCHAR(45) NOT NULL,
  `category_slug_title` VARCHAR(100) NOT NULL,
  `category_description` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE INDEX `category_slug_title_UNIQUE` (`category_slug_title` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rosalie`.`recipe`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rosalie`.`recipe` (
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
    REFERENCES `rosalie`.`user` (`user_id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rosalie`.`ingredient`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rosalie`.`ingredient` (
  `ingredient_id` INT NOT NULL AUTO_INCREMENT,
  `ingredient_name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`ingredient_id`),
  UNIQUE INDEX `ingredient_name_UNIQUE` (`ingredient_name` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rosalie`.`step`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rosalie`.`step` (
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
    REFERENCES `rosalie`.`recipe` (`recipe_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rosalie`.`comment`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rosalie`.`comment` (
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
    REFERENCES `rosalie`.`user` (`user_id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_comment_recipe`
    FOREIGN KEY (`recipe_id`)
    REFERENCES `rosalie`.`recipe` (`recipe_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rosalie`.`note`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rosalie`.`note` (
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
    REFERENCES `rosalie`.`user` (`user_id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_note_recipe`
  FOREIGN KEY (`recipe_id`)
  REFERENCES `rosalie`.`recipe` (`recipe_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,

  CONSTRAINT `chk_note_value`
  CHECK (`note_value` BETWEEN 1 AND 5))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rosalie`.`messageofcontact`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rosalie`.`messageofcontact` (
  `id_contact_message` INT NOT NULL AUTO_INCREMENT,
  `contact_message_name` VARCHAR(100) NOT NULL,
  `contact_message_email` VARCHAR(255) NOT NULL,
  `contact_message_sujet` VARCHAR(120) NOT NULL,
  `contact_message_message` TEXT NOT NULL,
  `contact_message_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contact_message`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rosalie`.`recipe_ingredient`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rosalie`.`recipe_ingredient` (
  `recipe_id` INT NOT NULL,
  `ingredient_id` INT NOT NULL,
  `recipe_ingredient_quantity` DECIMAL(8,2) NULL,
  `recipe_ingredient_unity` VARCHAR(45) NULL,
  PRIMARY KEY (`recipe_id`, `ingredient_id`),
  INDEX `fk_recipe_ingredient_ingredient_idx` (`ingredient_id` ASC) VISIBLE,
  CONSTRAINT `fk_recipe_ingredient_recipe`
    FOREIGN KEY (`recipe_id`)
    REFERENCES `rosalie`.`recipe` (`recipe_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_recipe_ingredient_ingredient`
    FOREIGN KEY (`ingredient_id`)
    REFERENCES `rosalie`.`ingredient` (`ingredient_id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rosalie`.`recipe_category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rosalie`.`recipe_category` (
  `recipe_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  PRIMARY KEY (`recipe_id`, `category_id`),
  INDEX `fk_recipe_category_category_idx` (`category_id` ASC) VISIBLE,
  CONSTRAINT `fk_recipe_category_recipe`
    FOREIGN KEY (`recipe_id`)
    REFERENCES `rosalie`.`recipe` (`recipe_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_recipe_category_category`
    FOREIGN KEY (`category_id`)
    REFERENCES `rosalie`.`category` (`category_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

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
