-- Crear trigger para validar que un artículo tenga al menos un autor asignado y un autor de contacto
DELIMITER / /

CREATE TRIGGER after_articulo_insert
AFTER INSERT ON Articulo
FOR EACH ROW
BEGIN
    DECLARE num_autores INT;
    DECLARE num_contactos INT;

    -- Contar el número de autores asignados al artículo
    SELECT COUNT(*) INTO num_autores
    FROM Autor_Articulo
    WHERE id_articulo = NEW.id;

    -- Contar el número de autores de contacto asignados al artículo
    SELECT COUNT(*) INTO num_contactos
    FROM Autor_Articulo
    WHERE id_articulo = NEW.id AND es_contacto = 1;

    -- Verificar que haya al menos un autor y un autor de contacto
    IF num_autores = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El artículo debe tener al menos un autor asignado.';
    END IF;

    IF num_contactos = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El artículo debe tener un autor de contacto asignado.';
    END IF;
END //

DELIMITER;