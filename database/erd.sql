CREATE TABLE `authentications` (
    `id` int NOT NULL,
    `user_id` int NOT NULL,
    `unique_identifier` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `type` enum('EMAIL_VERIFICATION','PASSWORD_RESET') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
    `id` int NOT NULL,
    `username` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `full_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    `password_salt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `is_admin` tinyint(1) NOT NULL DEFAULT '0',
    `is_social` tinyint(1) NOT NULL DEFAULT '0',
    `email_verified` tinyint(1) NOT NULL DEFAULT '0',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `authentications`
    ADD PRIMARY KEY (`id`),
    ADD KEY `user_id` (`user_id`);

ALTER TABLE `users`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `authentications`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `authentications`
    ADD CONSTRAINT `authentications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
