-- INSERT para la tabla Usuario
INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES
('123456789', 'Brittany Kirk', 'vgrant@example.com', 'bkirk', 'password123', 'Autor'),
('987654321', 'Rose Warner', 'michael60@example.net', 'rwarner', 'password456', 'Autor'),
('79521047K', 'Lisa Roth', 'johnathanhoward@example.com', 'lroth', 'password789', 'Revisor'),
('150484080', 'Joshua Roberts', 'jamesbeth@example.org', 'jroberts', 'password101', 'Revisor');

-- INSERT para la tabla Autor
INSERT INTO Autor (rut) VALUES
('123456789'),
('987654321');

-- INSERT para la tabla Revisor
INSERT INTO Revisor (rut) VALUES
('79521047K'),
('150484080');

-- INSERT para la tabla Articulo
INSERT INTO Articulo (id_articulo, titulo, fecha_envio, resumen, rut_autor, estado) VALUES
(1, 'Respond magazine Republican.', '2020-12-03', 'Skin theory we central teach indicate head far specific environment rise.', '123456789', 'En revisión'),
(2, 'El mish es clave.', '2023-04-29', 'Always three hit understand organization trial develop.', '987654321', 'En revisión');

-- INSERT para la tabla Topico
INSERT INTO Topico (id_topico, nombre) VALUES
(5, 'Ciencia'),
(6, 'Tecnología');

-- INSERT para la tabla Articulo_Topico
INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES
(1, 5),
(2, 6);

-- INSERT para la tabla Evaluacion_Articulo
INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES
(1, '79521047K', 'Reseña de ejemplo', 5),
(1, '150484080', 'Otra reseña de ejemplo', 4),
(2, '79521047K', 'Reseña adicional', 3);