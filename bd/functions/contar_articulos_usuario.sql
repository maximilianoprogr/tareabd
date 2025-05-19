DELIMITER $$
CREATE FUNCTION contar_articulos_usuario(rut_usuario VARCHAR(20))
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total
    FROM Articulo a
    JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
    WHERE aa.rut_autor = rut_usuario;
    RETURN total;
END$$
DELIMITER ;
