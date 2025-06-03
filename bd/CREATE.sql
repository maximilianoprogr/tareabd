-- Este script crea las tablas necesarias para el sistema.

-- Tabla Usuario: almacena información básica de los usuarios.
CREATE TABLE Usuario (
    rut VARCHAR(10) PRIMARY KEY, -- Identificador único del usuario.
    nombre VARCHAR(64) NOT NULL, -- Nombre del usuario.
    email VARCHAR(64) NOT NULL UNIQUE, -- Correo electrónico único.
    usuario VARCHAR(24) NOT NULL, -- Nombre de usuario.
    password VARCHAR(12) NOT NULL, -- Contraseña del usuario.
    tipo ENUM(
        'Autor',
        'Revisor',
        'Jefe Comite de Programa'
    ) NOT NULL -- Tipo de usuario.
);

-- Tabla Autor: almacena información de los autores.
CREATE TABLE Autor (
    rut VARCHAR(10) PRIMARY KEY, -- Identificador único del autor.
    FOREIGN KEY (rut) REFERENCES Usuario (rut) ON DELETE CASCADE -- Relación con la tabla Usuario.
);

-- Tabla Revisor: almacena información de los revisores.
CREATE TABLE Revisor (
    rut VARCHAR(10) PRIMARY KEY, -- Identificador único del revisor.
    FOREIGN KEY (rut) REFERENCES Usuario (rut) ON DELETE CASCADE -- Relación con la tabla Usuario.
);

-- Tabla Tópico: almacena los diferentes tópicos disponibles.
CREATE TABLE Topico (
    id_topico INT AUTO_INCREMENT PRIMARY KEY, -- Identificador único del tópico.
    nombre VARCHAR(64) NOT NULL -- Nombre del tópico.
);

-- Tabla Artículo: almacena información básica de los artículos.
CREATE TABLE Articulo (
    id_articulo INT AUTO_INCREMENT PRIMARY KEY, -- Identificador único del artículo.
    titulo VARCHAR(128) NOT NULL UNIQUE, -- Título del artículo.
    resumen VARCHAR(128), -- Resumen del artículo.
    fecha_envio DATE NOT NULL, -- Fecha de envío del artículo.
    estado ENUM(
        'En revisión',
        'Aprobado',
        'Rechazado'
    ) NOT NULL -- Estado del artículo.
);

-- Tabla Articulo_Topico: relaciona artículos con tópicos.
CREATE TABLE Articulo_Topico (
    id_articulo INT, -- Identificador del artículo.
    id_topico INT, -- Identificador del tópico.
    PRIMARY KEY (id_articulo, id_topico), -- Clave primaria compuesta.
    FOREIGN KEY (id_articulo) REFERENCES Articulo (id_articulo) ON DELETE CASCADE, -- Relación con la tabla Articulo.
    FOREIGN KEY (id_topico) REFERENCES Topico (id_topico) ON DELETE CASCADE -- Relación con la tabla Topico.
);

-- Tabla Evaluacion_Articulo: almacena las evaluaciones de los artículos.
CREATE TABLE Evaluacion_Articulo (
    id_articulo INT, -- Identificador del artículo evaluado.
    rut_revisor VARCHAR(10), -- Identificador del revisor que evalúa.
    resena VARCHAR(128), -- Reseña del artículo.
    calificacion INT, -- Calificación otorgada.
    calidad_tecnica BOOLEAN, -- Evaluación de la calidad técnica.
    originalidad BOOLEAN, -- Evaluación de la originalidad.
    valoracion_global BOOLEAN, -- Valoración global de la evaluación.
    argumentos_valoracion VARCHAR(128), -- Argumentos de la valoración.
    comentarios_autores VARCHAR(128), -- Comentarios para los autores.
    PRIMARY KEY (id_articulo, rut_revisor), -- Clave primaria compuesta.
    FOREIGN KEY (id_articulo) REFERENCES Articulo (id_articulo) ON DELETE CASCADE, -- Relación con la tabla Articulo.
    FOREIGN KEY (rut_revisor) REFERENCES Revisor (rut) ON DELETE CASCADE -- Relación con la tabla Revisor.
);

-- Tabla Autor_Articulo: relaciona autores con artículos.
CREATE TABLE Autor_Articulo (
    id_articulo INT NOT NULL, -- Identificador del artículo.
    rut_autor VARCHAR(10) NOT NULL, -- Identificador del autor.
    es_contacto BOOLEAN NOT NULL DEFAULT FALSE, -- Indica si es el contacto principal.
    PRIMARY KEY (id_articulo, rut_autor), -- Clave primaria compuesta.
    FOREIGN KEY (id_articulo) REFERENCES Articulo (id_articulo) ON DELETE CASCADE, -- Relación con la tabla Articulo.
    FOREIGN KEY (rut_autor) REFERENCES Autor (rut) ON DELETE CASCADE -- Relación con la tabla Autor.
);

-- Tabla Revisor_Topico: relaciona revisores con tópicos.
CREATE TABLE Revisor_Topico (
    rut_revisor VARCHAR(10) NOT NULL, -- Identificador del revisor.
    id_topico INT NOT NULL, -- Identificador del tópico.
    PRIMARY KEY (rut_revisor, id_topico), -- Clave primaria compuesta.
    FOREIGN KEY (rut_revisor) REFERENCES Revisor (rut) ON DELETE CASCADE, -- Relación con la tabla Revisor.
    FOREIGN KEY (id_topico) REFERENCES Topico (id_topico) ON DELETE CASCADE -- Relación con la tabla Topico.
);

-- Tabla Articulo_Revisor: relaciona artículos con revisores.
CREATE TABLE Articulo_Revisor (
    id_articulo INT NOT NULL, -- Identificador del artículo.
    rut_revisor VARCHAR(10) NOT NULL, -- Identificador del revisor.
    PRIMARY KEY (id_articulo, rut_revisor), -- Clave primaria compuesta.
    FOREIGN KEY (id_articulo) REFERENCES Articulo (id_articulo) ON DELETE CASCADE, -- Relación con la tabla Articulo.
    FOREIGN KEY (rut_revisor) REFERENCES Revisor (rut) ON DELETE CASCADE -- Relación con la tabla Revisor.
);