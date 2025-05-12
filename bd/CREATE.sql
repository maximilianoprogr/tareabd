
CREATE TABLE Usuario (
    rut VARCHAR(10) PRIMARY KEY,
    nombre VARCHAR(64) NOT NULL,
    email VARCHAR(64) NOT NULL UNIQUE,
    usuario VARCHAR(24) NOT NULL,
    password VARCHAR(12) NOT NULL,
    tipo ENUM(
        'Autor',
        'Revisor',
        'Jefe Comite de Programa'
    ) NOT NULL
);

-- Tabla Autor (relacionada directamente por rut)
CREATE TABLE Autor (
    rut VARCHAR(10) PRIMARY KEY,
    FOREIGN KEY (rut) REFERENCES Usuario (rut) ON DELETE CASCADE
);

-- Tabla Revisor (relacionada directamente por rut)
CREATE TABLE Revisor (
    rut VARCHAR(10) PRIMARY KEY,
    FOREIGN KEY (rut) REFERENCES Usuario (rut) ON DELETE CASCADE
);

-- Tabla Tópico
CREATE TABLE Topico (
    id_topico INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(64) NOT NULL
);

-- Tabla Artículo (referencia directa al rut del Autor)
CREATE TABLE Articulo (
    id_articulo INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(128) NOT NULL,
    resumen VARCHAR(128) NOT NULL,
    fecha_envio DATE NOT NULL,
    rut_autor VARCHAR(10) NOT NULL,
    estado ENUM(
        'En revisión',
        'Aprobado',
        'Rechazado'
    ) NOT NULL,
    FOREIGN KEY (rut_autor) REFERENCES Autor (rut) ON DELETE CASCADE
);

-- Tabla Articulo_Topico (relación muchos a muchos)
CREATE TABLE Articulo_Topico (
    id_articulo INT,
    id_topico INT,
    PRIMARY KEY (id_articulo, id_topico),
    FOREIGN KEY (id_articulo) REFERENCES Articulo (id_articulo) ON DELETE CASCADE,
    FOREIGN KEY (id_topico) REFERENCES Topico (id_topico) ON DELETE CASCADE
);

-- Tabla Evaluacion_Articulo (revisión por revisor con reseña y calificación)
CREATE TABLE Evaluacion_Articulo (
    id_articulo INT,
    rut_revisor VARCHAR(10),
    resena VARCHAR(128),
    calificacion INT,
    PRIMARY KEY (id_articulo, rut_revisor),
    FOREIGN KEY (id_articulo) REFERENCES Articulo (id_articulo) ON DELETE CASCADE,
    FOREIGN KEY (rut_revisor) REFERENCES Revisor (rut) ON DELETE CASCADE
);

-- Tabla Autor_Articulo (relación entre autores y artículos con contacto principal)
CREATE TABLE Autor_Articulo (
    id_articulo INT NOT NULL,
    rut_autor VARCHAR(10) NOT NULL,
    es_contacto BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (id_articulo, rut_autor),
    FOREIGN KEY (id_articulo) REFERENCES Articulo (id_articulo) ON DELETE CASCADE,
    FOREIGN KEY (rut_autor) REFERENCES Autor (rut) ON DELETE CASCADE
);

-- Tabla Revisor_Topico (relación muchos a muchos)
CREATE TABLE Revisor_Topico (
    rut_revisor VARCHAR(10) NOT NULL,
    id_topico INT NOT NULL,
    PRIMARY KEY (rut_revisor, id_topico),
    FOREIGN KEY (rut_revisor) REFERENCES Revisor (rut) ON DELETE CASCADE,
    FOREIGN KEY (id_topico) REFERENCES Topico (id_topico) ON DELETE CASCADE
);

-- Tabla Articulo_Revisor (relación muchos a muchos)
CREATE TABLE Articulo_Revisor (
    id_articulo INT NOT NULL,
    rut_revisor VARCHAR(10) NOT NULL,
    PRIMARY KEY (id_articulo, rut_revisor),
    FOREIGN KEY (id_articulo) REFERENCES Articulo (id_articulo) ON DELETE CASCADE,
    FOREIGN KEY (rut_revisor) REFERENCES Revisor (rut) ON DELETE CASCADE
);