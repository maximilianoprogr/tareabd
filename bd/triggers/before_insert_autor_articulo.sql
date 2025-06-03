-- Este trigger se ejecuta antes de insertar un registro en la tabla 'Autor_Articulo'.
-- Verifica que el autor exista en la tabla 'Autor' antes de permitir la inserción.
DELIMITER $$

CREATE TRIGGER before_insert_autor_articulo
BEFORE INSERT ON Autor_Articulo
FOR EACH ROW
BEGIN
    -- Validar que el autor exista en la tabla Autor
    IF (SELECT COUNT(*) FROM Autor WHERE rut = NEW.rut_autor) = 0 THEN
        -- Si el autor no existe, se genera un error con un mensaje descriptivo.
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se puede asociar un autor que no existe en la tabla Autor.';
    END IF;
END$$

DELIMITER;