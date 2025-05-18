DELIMITER $$

CREATE TRIGGER after_usuario_tipo_update
AFTER UPDATE ON Usuario
FOR EACH ROW
BEGIN
    -- Si el tipo de usuario cambió
    IF NEW.tipo <> OLD.tipo THEN

        -- Si ya no es Autor, eliminarlo de Autor y de Autor_Articulo
        IF NEW.tipo <> 'Autor' THEN
            DELETE FROM Autor_Articulo WHERE rut_autor = NEW.rut;
            DELETE FROM Autor WHERE rut = NEW.rut;
        END IF;

        -- Si ya no es Revisor, eliminarlo de Revisor, Revisor_Topico y Articulo_Revisor
        IF NEW.tipo <> 'Revisor' THEN
            DELETE FROM Articulo_Revisor WHERE rut_revisor = NEW.rut;
            DELETE FROM Revisor_Topico WHERE rut_revisor = NEW.rut;
            DELETE FROM Revisor WHERE rut = NEW.rut;
        END IF;

    END IF;
END$$

DELIMITER ;