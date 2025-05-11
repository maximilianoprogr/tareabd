CREATE TABLE Usuario (
    rut VARCHAR(10) PRIMARY KEY,
    nombre VARCHAR(64) NOT NULL,
    email VARCHAR(64) NOT NULL UNIQUE,
    usuario VARCHAR(24) NOT NULL,
    password VARCHAR(12) NOT NULL,
    tipo ENUM('Autor', 'Revisor') NOT NULL
);

-- Tabla Autor (relacionada directamente por rut)
CREATE TABLE Autor (
    rut VARCHAR(10) PRIMARY KEY,
    FOREIGN KEY (rut) REFERENCES Usuario(rut)
);

-- Tabla Revisor (relacionada directamente por rut)
CREATE TABLE Revisor (
    rut VARCHAR(10) PRIMARY KEY,
    FOREIGN KEY (rut) REFERENCES Usuario(rut)
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
    estado ENUM('En revisión', 'Aprobado', 'Rechazado') NOT NULL,
    FOREIGN KEY (rut_autor) REFERENCES Autor(rut)
);

-- Tabla Articulo_Topico (relación muchos a muchos)
CREATE TABLE Articulo_Topico (
    id_articulo INT,
    id_topico INT,
    PRIMARY KEY (id_articulo, id_topico),
    FOREIGN KEY (id_articulo) REFERENCES Articulo(id_articulo),
    FOREIGN KEY (id_topico) REFERENCES Topico(id_topico)
);

-- Tabla Evaluacion_Articulo (revisión por revisor con reseña y calificación)
CREATE TABLE Evaluacion_Articulo (
    id_articulo INT,
    rut_revisor VARCHAR(10),
    resena VARCHAR(128),
    calificacion INT,
    PRIMARY KEY (id_articulo, rut_revisor),
    FOREIGN KEY (id_articulo) REFERENCES Articulo(id_articulo),
    FOREIGN KEY (rut_revisor) REFERENCES Revisor(rut)
);
