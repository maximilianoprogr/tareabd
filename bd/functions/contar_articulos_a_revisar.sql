
    DELIMITER $$
    CREATE FUNCTION contar_articulos_a_revisar(rut VARCHAR(20))
    RETURNS INT
    DETERMINISTIC
    BEGIN
        DECLARE total INT;
        SELECT COUNT(*) INTO total FROM Articulo_Revisor WHERE rut_revisor = rut;
        RETURN total;
    END$$
    DELIMITER ;

