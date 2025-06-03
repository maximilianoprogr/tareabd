-- Esta vista muestra información de los usuarios para el inicio de sesión.
CREATE VIEW vista_usuarios_login AS
SELECT
    usuario.rut, -- Identificador único del usuario.
    usuario.password, -- Contraseña del usuario.
    usuario.nombre, -- Nombre del usuario.
    usuario.email, -- Correo electrónico del usuario.
    CASE
    -- Determina el rol del usuario según las tablas relacionadas.
        WHEN EXISTS (
            SELECT 1
            FROM Autor
            WHERE
                rut = usuario.rut
        ) THEN 'Autor'
        WHEN EXISTS (
            SELECT 1
            FROM Revisor
            WHERE
                rut = usuario.rut
        ) THEN 'Revisor'
        ELSE 'Jefe Comite de Programa'
    END AS rol -- Rol del usuario.
FROM Usuario usuario;