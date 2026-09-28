-- Restore the documented local admin login without dropping or recreating tables.
-- Run this against the meridian_hospital database in phpMyAdmin.
INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `role`, `status`)
VALUES (
  'Sajid (Super Admin)',
  'Mohammadsajid1114@gmail.com',
  '$2y$12$184ac/uu5VUWGRv330n7DORu1XJJeB.2xQbO0we3YKHLW5/HCMcJu',
  '01622295857',
  'admin',
  'active'
)
ON DUPLICATE KEY UPDATE
  `password_hash` = VALUES(`password_hash`),
  `role` = 'admin',
  `status` = 'active';

INSERT INTO `admins` (`user_id`, `permission_level`)
SELECT `id`, 'super_admin'
FROM `users`
WHERE `email` = 'Mohammadsajid1114@gmail.com'
ON DUPLICATE KEY UPDATE `permission_level` = 'super_admin';
