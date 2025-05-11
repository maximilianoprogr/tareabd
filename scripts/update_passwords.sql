-- Script SQL para actualizar las contraseñas con hashes
UPDATE Usuario
SET password = '$2y$10$eImiTXuWVxfM37uY4JANjQe5Jx1g1z1W4YfQ2J3F5J1g1z1W4YfQ2'
WHERE
    password = '1c7LdHNz%h';

UPDATE Usuario
SET password = '$2y$10$eImiTXuWVxfM37uY4JANjQe5Jx1g1z1W4YfQ2J3F5J1g1z1W4YfQ2'
WHERE
    password = '^@%Rs2v#JO';
-- Agregar más líneas para cada contraseña...