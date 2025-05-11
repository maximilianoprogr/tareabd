-- Inserts para la tabla Usuario
INSERT INTO
    Usuario (
        rut,
        nombre,
        email,
        usuario,
        password,
        tipo
    )
VALUES (
        '12345678-9',
        'Juan Perez',
        'juan.perez@example.com',
        'juanp',
        'password123',
        'Autor'
    ),
    (
        '98765432-1',
        'Maria Lopez',
        'maria.lopez@example.com',
        'marial',
        'password456',
        'Revisor'
    ),
    (
        '11223344-5',
        'Carlos Gomez',
        'carlos.gomez@example.com',
        'carlosg',
        'password789',
        'Jefe Comite de Programa'
    ),
    (
        '22334455-6',
        'Ana Torres',
        'ana.torres@example.com',
        'anatorres',
        'password321',
        'Autor'
    ),
    (
        '33445566-7',
        'Luis Martinez',
        'luis.martinez@example.com',
        'luism',
        'password654',
        'Revisor'
    ),
    (
        '44556677-8',
        'Sofia Ramirez',
        'sofia.ramirez@example.com',
        'sofiam',
        'password987',
        'Jefe Comite de Programa'
    );

-- Inserts para la tabla Autor
INSERT INTO Autor (rut) VALUES ('12345678-9'), ('22334455-6');

-- Inserts para la tabla Revisor
INSERT INTO Revisor (rut) VALUES ('98765432-1'), ('33445566-7');

-- Inserts para la tabla Topico
INSERT INTO
    Topico (nombre)
VALUES ('Inteligencia Artificial'),
    ('Bases de Datos'),
    ('Desarrollo Web'),
    ('Ciberseguridad'),
    ('Machine Learning');

-- Inserts para la tabla Articulo
INSERT INTO
    Articulo (
        titulo,
        resumen,
        fecha_envio,
        rut_autor,
        estado
    )
VALUES (
        'Introducción a la IA',
        'Un artículo sobre los fundamentos de la inteligencia artificial.',
        '2025-05-01',
        '12345678-9',
        'En revisión'
    ),
    (
        'Optimización de Consultas SQL',
        'Técnicas avanzadas para optimizar consultas en bases de datos.',
        '2025-05-02',
        '12345678-9',
        'Aprobado'
    ),
    (
        'Seguridad en Redes',
        'Un artículo sobre cómo proteger redes de ataques cibernéticos.',
        '2025-05-03',
        '22334455-6',
        'En revisión'
    ),
    (
        'Aprendizaje Supervisado',
        'Exploración de técnicas de machine learning supervisado.',
        '2025-05-04',
        '22334455-6',
        'En revisión'
    );

-- Inserts para la tabla Articulo_Topico
INSERT INTO
    Articulo_Topico (id_articulo, id_topico)
VALUES (1, 1),
    (2, 2),
    (3, 4),
    (4, 5);

-- Inserts para la tabla Evaluacion_Articulo
INSERT INTO
    Evaluacion_Articulo (
        id_articulo,
        rut_revisor,
        resena,
        calificacion
    )
VALUES (
        1,
        '98765432-1',
        'Buen artículo, pero necesita más ejemplos.',
        8
    ),
    (
        2,
        '98765432-1',
        'Excelente trabajo, muy detallado.',
        10
    ),
    (
        3,
        '33445566-7',
        'Interesante, pero falta más profundidad.',
        7
    ),
    (
        4,
        '33445566-7',
        'Muy completo y bien explicado.',
        9
    );

-- Inserts para la tabla Revisor_Topico
INSERT INTO
    Revisor_Topico (rut_revisor, id_topico)
VALUES ('98765432-1', 1),
    ('98765432-1', 2),
    ('33445566-7', 4),
    ('33445566-7', 5);

-- Inserts para la tabla Articulo_Revisor
INSERT INTO
    Articulo_Revisor (id_articulo, rut_revisor)
VALUES (1, '98765432-1'),
    (2, '98765432-1'),
    (3, '33445566-7'),
    (4, '33445566-7');