CREATE VIEW vista_usuarios_login AS
SELECT 
    usuario.rut,
    usuario.password,
    usuario.nombre,
    usuario.email,
    CASE 
        WHEN EXISTS (SELECT 1 FROM Autor WHERE rut = usuario.rut) THEN 'Autor'
        WHEN EXISTS (SELECT 1 FROM Revisor WHERE rut = usuario.rut) THEN 'Revisor'
        ELSE 'Jefe Comite de Programa'
    END AS tipo
FROM Usuario usuario;