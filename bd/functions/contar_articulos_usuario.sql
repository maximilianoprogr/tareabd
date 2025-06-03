-- Esta función cuenta cuántos artículos ha escrito un usuario específico.
-- Se le pasa como parámetro el RUT (identificador único) del usuario.
DELIMITER $$

CREATE FUNCTION contar_articulos_usuario(rut_usuario VARCHAR(20))
RETURNS INT
DETERMINISTIC
BEGIN
    -- Declaramos una variable llamada 'total' para guardar el resultado.
    DECLARE total INT;
    
    -- Contamos cuántos artículos están relacionados con el usuario en la tabla 'Autor_Articulo'.
    SELECT COUNT(*) INTO total
    FROM Articulo a
    JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
    WHERE aa.rut_autor = rut_usuario;
    
    -- Devolvemos el total de artículos escritos por el usuario.
    RETURN total;
END$$

DELIMITER;