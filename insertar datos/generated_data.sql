INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES ('289402938s', 'Sean Chase', 'rebecca67@example.com', 'brendamiller', '1c7LdHNz%h', 'Autor');
INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES ('219603656n', 'Theresa Callahan', 'ortegamichele@example.net', 'frank11', '^@%Rs2v#JO', 'Revisor');
INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES ('058333267r', 'Tracy Carpenter', 'richard64@example.com', 'freemanlisa', 'B9hFcwKb@1', 'Autor');
INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES ('878898586a', 'Marvin Jones', 'cphillips@example.net', 'brianrobinson', '$D5+1CIyp2', 'Revisor');
INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES ('241359983w', 'Donna Harris', 'susan89@example.org', 'schaefermichelle', ')2jOremvdT', 'Revisor');
INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES ('435860269z', 'George Graves', 'vhill@example.com', 'keith50', '*__$i4StUX', 'Autor');
INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES ('986321656f', 'Kimberly Crawford', 'brittany95@example.org', 'joshua65', 'aE4!4Tv1q*', 'Revisor');
INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES ('420116140C', 'Jennifer Holt', 'smithwilliam@example.net', 'taylorarmstrong', '(e0wS#p3T^', 'Revisor');
INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES ('028607221m', 'Erin Lynn', 'jonesnatalie@example.org', 'tsantiago', '0N$x2DbC*5', 'Autor');
INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES ('981222269i', 'Angela Brown', 'michaelfox@example.net', 'aferguson', 'd&n7VP%gA&', 'Revisor');
INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES ('123456789A', 'Carlos Perez', 'carlos@example.com', 'carlosp', 'password123', 'Autor');
INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES ('987654321B', 'Ana Lopez', 'ana@example.com', 'analopez', 'password456', 'Revisor');

INSERT INTO Autor (rut) VALUES ('289402938s');
INSERT INTO Autor (rut) VALUES ('058333267r');
INSERT INTO Autor (rut) VALUES ('435860269z');
INSERT INTO Autor (rut) VALUES ('028607221m');
INSERT INTO Autor (rut) VALUES ('123456789A');
INSERT INTO Revisor (rut) VALUES ('219603656n');
INSERT INTO Revisor (rut) VALUES ('878898586a');
INSERT INTO Revisor (rut) VALUES ('241359983w');
INSERT INTO Revisor (rut) VALUES ('986321656f');
INSERT INTO Revisor (rut) VALUES ('420116140C');
INSERT INTO Revisor (rut) VALUES ('981222269i');
INSERT INTO Revisor (rut) VALUES ('987654321B');

INSERT INTO Articulo (id_articulo, titulo, resumen, fecha_envio, rut_autor, estado) VALUES (172, 'Road within explain.', 'Home car next make assume himself. Hospital hand share because. Movie enjoy traditional yeah option thus western.', '2025-03-19', '028607221m', 'Rechazado');
INSERT INTO Articulo (id_articulo, titulo, resumen, fecha_envio, rut_autor, estado) VALUES (50, 'Season much audience letter.', 'Study state heavy anything central difficult simple. Class give training manage study meet somebody receive. Anyone best skin traditional who.', '2024-03-13', '058333267r', 'Aprobado');
INSERT INTO Articulo (id_articulo, titulo, resumen, fecha_envio, rut_autor, estado) VALUES (944, 'Field name time whose.', 'Theory man model them result song. Gas keep choose bag.', '2023-07-02', '435860269z', 'Rechazado');
INSERT INTO Articulo (id_articulo, titulo, resumen, fecha_envio, rut_autor, estado) VALUES (137, 'Hospital experience movie building.', 'Oil for piece report state.
Issue figure weight national look form. Property today could food cultural author.
Road exist already what. Performance court Mr behind point.', '2023-05-25', '058333267r', 'En revisión');
INSERT INTO Articulo (id_articulo, titulo, resumen, fecha_envio, rut_autor, estado) VALUES (604, 'Bank respond recognize ahead different necessary.', 'Voice front item clear exactly market. By fact machine hear participant.
Baby scientist of kid guess.', '2024-11-19', '435860269z', 'Rechazado');
INSERT INTO Articulo (id_articulo, titulo, resumen, fecha_envio, rut_autor, estado) VALUES (101, 'Nuevo Artículo 1', 'Resumen del artículo 1', '2025-05-01', '123456789A', 'En revisión');
INSERT INTO Articulo (id_articulo, titulo, resumen, fecha_envio, rut_autor, estado) VALUES (102, 'Nuevo Artículo 2', 'Resumen del artículo 2', '2025-05-02', '123456789A', 'Aprobado');

INSERT INTO Topico (id_topico, nombre) VALUES (57, 'reason');
INSERT INTO Topico (id_topico, nombre) VALUES (13, 'question');
INSERT INTO Topico (id_topico, nombre) VALUES (24, 'knowledge');

INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES (172, 57);
INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES (50, 24);
INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES (944, 13);
INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES (137, 57);
INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES (604, 57);

INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES (172, '420116140C', 'Vote suggest rich. Matter respond instead idea half product. Campaign try student teacher.', 1);
INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES (50, '986321656f', 'Accept fear mean born hair fine decision. Wide act wind. Court have training action.', 5);
INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES (944, '241359983w', 'Pattern article marriage author any ability.', 1);
INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES (137, '981222269i', 'Himself dog fill knowledge reflect maintain capital. Company young music air. Mean those unit.', 3);
INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES (604, '241359983w', 'Glass able physical today. With strong watch test. Small music very son appear buy.', 4);
INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES (101, '987654321B', 'Buena calidad', 4);
INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES (102, '987654321B', 'Excelente trabajo', 5);

-- Datos de prueba para la tabla Autor_Articulo
INSERT INTO Autor_Articulo (id_articulo, rut_autor) VALUES (1, '123456789A');
INSERT INTO Autor_Articulo (id_articulo, rut_autor) VALUES (1, '987654321B');
INSERT INTO Autor_Articulo (id_articulo, rut_autor) VALUES (2, '123456789A');
INSERT INTO Autor_Articulo (id_articulo, rut_autor) VALUES (3, '987654321B');
INSERT INTO Autor_Articulo (id_articulo, rut_autor) VALUES (3, '435860269Z');
