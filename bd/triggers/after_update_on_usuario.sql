-- Este trigger se ejecuta después de actualizar un registro en la tabla 'Usuario'.
-- Verifica si el tipo de usuario cambió y realiza acciones según el nuevo tipo.
DELIMITER $$

CREATE TRIGGER after_usuario_tipo_update
AFTER UPDATE ON Usuario
FOR EACH ROW
BEGIN
    -- Si el tipo de usuario cambió
    IF NEW.tipo <> OLD.tipo THEN

        -- Si ya no es Autor, eliminarlo de las tablas relacionadas con autores.
        IF NEW.tipo <> 'Autor' THEN
            DELETE FROM Autor_Articulo WHERE rut_autor = NEW.rut;
            DELETE FROM Autor WHERE rut = NEW.rut;
        END IF;

        -- Si ya no es Revisor, eliminarlo de las tablas relacionadas con revisores.
        IF NEW.tipo <> 'Revisor' THEN
            DELETE FROM Articulo_Revisor WHERE rut_revisor = NEW.rut;
            DELETE FROM Revisor_Topico WHERE rut_revisor = NEW.rut;
            DELETE FROM Revisor WHERE rut = NEW.rut;
        END IF;

    END IF;
END$$

DELIMITER; 
-- Tabla Articulo
CREATE TABLE Articulo (
	id_articulo INT PRIMARY KEY, 		-- Identificador único del artículo
	titulo VARCHAR(255) NOT NULL,		-- Título del artículo
	fecha_envio DATE,			 		-- Fecha de envío
	resumen VARCHAR(150)		 		-- Resumen del artículo (máximo 150 caracteres)
);

-- Tabla Autores
CREATE TABLE Autor (
    rut VARCHAR(10) PRIMARY KEY,		-- RUT del autor
    nombre VARCHAR(255) UNIQUE			-- nombre del autor
    email VARCHAR(255) UNIQUE,			-- email del autor
	userid VARCHAR(16) DEFAULT NULL,
	password VARCHAR(8) DEFAULT NULL
);

-- Tabla Autor_Articulo
CREATE TABLE Autor_Articulo (
    rut_autor VARCHAR(10) REFERENCES Autor(rut),		-- Clave foránea a Autores
    id_articulo INT REFERENCES Articulo(id_articulo),	-- Clave foránea a Artículos
	es_contacto BIT DEFAULT 0,							-- Booleano de si es contacto
    PRIMARY KEY (rut_autor, id_articulo)				-- Relacion Muchos a Muchos (*)
);

-- Tabla Topicos
CREATE TABLE Topico (
    id_topico INT PRIMARY KEY,		-- Identificador para el tópico
    nombre_topico VARCHAR(255)		-- Nombre del tópico
);

-- Tabla Articulos_Topicos
CREATE TABLE Articulo_Topico (
    id_articulo INT REFERENCES Articulo(id_articulo),	-- Clave foránea a Artículos
    id_topico INT REFERENCES Topico(id_topico),			-- Clave foránea a Tópicos
    PRIMARY KEY (id_articulo, id_topico)				-- Relacion de Muchos a Muchos (*)
);

-- Tabla Revisores
CREATE TABLE Revisor (
    rut VARCHAR(10) PRIMARY KEY,		-- RUT del revisor
    nombre VARCHAR(255) UNIQUE,         -- Nombre del revisor
    email VARCHAR(255) UNIQUE,          -- Email del revisor
	userid VARCHAR(16) DEFAULT NULL,	-- userid del Revisor
	password VARCHAR(8) DEFAULT NULL	-- contraseña del Revisor
);

-- Tabla Especialidades
CREATE TABLE Especialidad (
    id_especialidad INT PRIMARY KEY,    -- Identificador único para la especialidad
    nombre_especialidad VARCHAR(255)    -- Nombre de la especialidad
);

-- Tabla Revisor_Especialidad
CREATE TABLE Revisor_Especialidad (
    rut_revisor VARCHAR(10) REFERENCES Revisor(rut),				-- Clave foránea a Revisores
    id_especialidad INT REFERENCES Especialidad(id_especialidad),	-- Clave foránea a Especialidades
    PRIMARY KEY (rut_revisor, id_especialidad)						-- Relacion Mucho a Muchos (*)
);

-- Tabla Articulo_Revisor
CREATE TABLE Articulo_Revisor (
    id_articulo INT REFERENCES Articulo(id_articulo),	-- Clave foránea a Artículos
    rut_revisor VARCHAR(10) REFERENCES Revisor(rut),	-- Clave foránea a Revisores
    PRIMARY KEY (id_articulo, rut_revisor)				-- Relacion 3 a Muchos (3..*)
);