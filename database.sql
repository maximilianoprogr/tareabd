-- Procedimiento almacenado para asignar un artículo a un revisor
DELIMITER //
CREATE PROCEDURE AsignarArticuloRevisor(
    IN p_id_articulo INT,
    IN p_rut_revisor VARCHAR(10)
)
BEGIN
    -- Verificar si el revisor es autor del artículo
    IF EXISTS (
        SELECT 1 FROM Autor_Articulo WHERE id_articulo = p_id_articulo AND rut_autor = p_rut_revisor
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede asignar un artículo a un revisor que sea autor.';
    ELSE
        -- Verificar si ya existe la asignación
        IF NOT EXISTS (
            SELECT 1 FROM Articulo_Revisor WHERE id_articulo = p_id_articulo AND rut_revisor = p_rut_revisor
        ) THEN
            INSERT INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES (p_id_articulo, p_rut_revisor);
        END IF;
    END IF;
END;
//
DELIMITER ;

-- Trigger para evitar eliminar revisores con artículos asignados
DELIMITER //
CREATE TRIGGER BeforeDeleteRevisor
BEFORE DELETE ON Revisor
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1 FROM Articulo_Revisor WHERE rut_revisor = OLD.rut
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede eliminar un revisor con artículos asignados.';
    END IF;
END;
//
DELIMITER ;

-- View para mostrar artículos evaluados con autores y tópicos
CREATE VIEW ArticulosEvaluados AS
SELECT 
    a.id_articulo,
    a.titulo,
    a.resumen,
    GROUP_CONCAT(DISTINCT t.nombre_topico) AS topicos,
    GROUP_CONCAT(DISTINCT u.nombre) AS autores
FROM Articulo a
LEFT JOIN Articulo_Topico at ON a.id_articulo = at.id_articulo
LEFT JOIN Topico t ON at.id_topico = t.id_topico
LEFT JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
LEFT JOIN Usuario u ON aa.rut_autor = u.rut
GROUP BY a.id_articulo;

-- Función para verificar si un artículo tiene menos de dos revisores
DELIMITER //
CREATE FUNCTION ArticuloConMenosDeDosRevisores(p_id_articulo INT)
RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE revisor_count INT;
    SELECT COUNT(*) INTO revisor_count
    FROM Articulo_Revisor
    WHERE id_articulo = p_id_articulo;
    RETURN revisor_count < 2;
END;
//
DELIMITER ;

-- Agregar columna 'rol' a la tabla 'Usuario'
ALTER TABLE Usuario
ADD COLUMN rol ENUM('autor', 'revisor') NOT NULL AFTER password;

-- Tabla Autor_Articulo (relación entre autores y artículos)
CREATE TABLE Autor_Articulo (
    id_articulo INT NOT NULL,
    rut_autor VARCHAR(10) NOT NULL,
    PRIMARY KEY (id_articulo, rut_autor),
    FOREIGN KEY (id_articulo) REFERENCES Articulo(id_articulo) ON DELETE CASCADE,
    FOREIGN KEY (rut_autor) REFERENCES Usuario(rut) ON DELETE CASCADE
);
