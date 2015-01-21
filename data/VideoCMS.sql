CREATE DATABASE IF NOT EXISTS `VideoCMS`;
USE `VideoCMS`;
--管理员表
DROP TABLE IF EXISTS `VideoCMS_admin`;
CREATE TABLE `VideoCMS_admin`(
`id` tinyint unsigned auto_increment key,
`username` varchar(20) not null unique,
`password` char(32) not null
);