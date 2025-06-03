-- Este procedimiento asigna un artículo a un revisor.
-- Recibe como parámetros el ID del artículo y el RUT del revisor.
DELIMITER $$

CREATE PROCEDURE AsignarArticuloRevisor(
    IN p_id_articulo INT, -- ID del artículo a asignar.
    IN p_rut_revisor VARCHAR(10) -- RUT del revisor.
)
BEGIN
    -- Declaramos variables para verificar condiciones.
    DECLARE v_es_autor INT; -- Para verificar si el revisor es autor del artículo.
    DECLARE v_coincidencias INT; -- Para verificar coincidencias de tópicos.

    -- Verificar si el revisor es autor del artículo.
    SELECT COUNT(*) INTO v_es_autor
    FROM Autor_Articulo
    WHERE id_articulo = p_id_articulo AND rut_autor = p_rut_revisor;

    -- Si el revisor es autor, mostramos un error.
    IF v_es_autor > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El revisor no puede ser autor del artículo.';
    END IF;

    -- Verificar si los tópicos del artículo coinciden con los del revisor
    SELECT COUNT(*) INTO v_coincidencias
    FROM Articulo_Topico at
    JOIN Revisor_Topico rt ON at.id_topico = rt.id_topico
    WHERE at.id_articulo = p_id_articulo AND rt.rut_revisor = p_rut_revisor;

    -- Si no hay coincidencias de tópicos, mostramos un error.
    IF v_coincidencias = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No hay coincidencias de tópicos entre el artículo y el revisor.';
    END IF;

    -- Asignar el artículo al revisor
    INSERT INTO Articulo_Revisor (id_articulo, rut_revisor)
    VALUES (p_id_articulo, p_rut_revisor);
END$$

DELIMITER;