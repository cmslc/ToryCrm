-- ToryCRM Migration 008: Remove bonus points feature
SET NAMES utf8mb4;

ALTER TABLE `contacts` DROP COLUMN IF EXISTS `bonus_points`;
DROP TABLE IF EXISTS `bonus_point_logs`;
