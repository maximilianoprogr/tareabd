-- Esta función cuenta cuántos artículos tiene asignados un revisor específico.
-- Se le pasa como parámetro el RUT (identificador único) del revisor.
DELIMITER $$

CREATE FUNCTION contar_articulos_a_revisar(rut VARCHAR(20))
    RETURNS INT
    DETERMINISTIC
    BEGIN
        -- Declaramos una variable llamada 'total' para guardar el resultado.
        DECLARE total INT;
        
        -- Contamos cuántos registros hay en la tabla 'Articulo_Revisor' donde el RUT del revisor coincide.
        SELECT COUNT(*) INTO total FROM Articulo_Revisor WHERE rut_revisor = rut;
        
        -- Devolvemos el total de artículos asignados al revisor.
        RETURN total;
    END$$

DELIMITER;