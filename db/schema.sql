-- MySQL Script generated by MySQL Workbench
-- Sun Jul 12 04:37:50 2020
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema WPES
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `WPES` ;

-- -----------------------------------------------------
-- Schema WPES
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `WPES` DEFAULT CHARACTER SET utf8 ;
USE `WPES` ;

-- -----------------------------------------------------
-- Table `WPES`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `WPES`.`users` ;

CREATE TABLE IF NOT EXISTS `WPES`.`users` (
  `iduser` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NULL,
  `password` VARCHAR(255) NULL,
  `full_name` VARCHAR(255) NULL,
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `token_reset` VARCHAR(255) NULL,
  `token_resetexpired` DATETIME NULL,
  `activation_token` VARCHAR(255) NULL,
  PRIMARY KEY (`iduser`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `WPES`.`device`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `WPES`.`device` ;

CREATE TABLE IF NOT EXISTS `WPES`.`device` (
  `iddevice` INT NOT NULL AUTO_INCREMENT,
  `deviceId` VARCHAR(255) NULL,
  `user_id` INT NOT NULL,
  `code_registration` VARCHAR(255) NULL,
  `access_key` VARCHAR(255) NULL,
  `last_ipaddress` VARCHAR(255) NULL,
  `created_at` VARCHAR(255) NULL,
  `updated_at` VARCHAR(255) NULL,
  PRIMARY KEY (`iddevice`),
  INDEX `fk_device_users1_idx` (`user_id` ASC) ,
  CONSTRAINT `fk_device_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `WPES`.`users` (`iduser`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `WPES`.`sensor`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `WPES`.`sensor` ;

CREATE TABLE IF NOT EXISTS `WPES`.`sensor` (
  `idsensor` INT NOT NULL AUTO_INCREMENT,
  `time` TIMESTAMP NULL,
  `type_sensor` VARCHAR(45) NULL,
  `value` FLOAT NULL,
  `device_iddevice` INT NOT NULL,
  PRIMARY KEY (`idsensor`),
  INDEX `fk_sensor_device1_idx` (`device_iddevice` ASC) ,
  CONSTRAINT `fk_sensor_device1`
    FOREIGN KEY (`device_iddevice`)
    REFERENCES `WPES`.`device` (`iddevice`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `WPES`.`log_action`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `WPES`.`log_action` ;

CREATE TABLE IF NOT EXISTS `WPES`.`log_action` (
  `time` TIMESTAMP NOT NULL,
  `action` VARCHAR(255) NULL,
  `device_id` INT NOT NULL,
  PRIMARY KEY (`time`),
  INDEX `fk_log_action_device1_idx` (`device_id` ASC) ,
  CONSTRAINT `fk_log_action_device1`
    FOREIGN KEY (`device_id`)
    REFERENCES `WPES`.`device` (`iddevice`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
