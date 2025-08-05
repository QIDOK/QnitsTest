-- SQL код создания базы данных и объектов в ней

-- Создание базы данных
CREATE DATABASE IF NOT EXISTS url_shortener CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE url_shortener;

-- Таблица для хранения оригинальных и коротких URL
CREATE TABLE `urls` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `original_url` text NOT NULL,
    `short_code` varchar(10) NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_short_code` (`short_code`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица для логирования переходов по ссылкам
CREATE TABLE `url_clicks` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `url_id` int(11) NOT NULL,
    `user_agent` text,
    `ip_address` varchar(45),
    `clicked_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_url_id` (`url_id`),
    KEY `idx_clicked_at` (`clicked_at`),
    KEY `idx_is_bot` (`is_bot`),
    CONSTRAINT `fk_url_clicks_url_id` FOREIGN KEY (`url_id`) REFERENCES `urls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
