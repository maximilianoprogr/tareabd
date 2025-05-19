CREATE DATABASE base11;
USE base11;

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
    titulo VARCHAR(128) NOT NULL UNIQUE,
    resumen VARCHAR(128),
    fecha_envio DATE NOT NULL,
    estado ENUM(
        'En revisión',
        'Aprobado',
        'Rechazado'
    ) NOT NULL
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
    calidad_tecnica BOOLEAN,
    originalidad BOOLEAN,
    valoracion_global BOOLEAN,
    argumentos_valoracion VARCHAR(128),
    comentarios_autores VARCHAR(128),
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

DELIMITER $$
CREATE FUNCTION contar_articulos_a_revisar(rut VARCHAR(20))
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM Articulo_Revisor WHERE rut_revisor = rut;
    RETURN total;
END$$
DELIMITER ;

DELIMITER $$
CREATE FUNCTION contar_articulos_usuario(rut_usuario VARCHAR(20))
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total
    FROM Articulo a
    JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
    WHERE aa.rut_autor = rut_usuario;
    RETURN total;
END$$
DELIMITER ;

DELIMITER $$

CREATE PROCEDURE AsignarArticuloRevisor(
    IN p_id_articulo INT,
    IN p_rut_revisor VARCHAR(10)
)
BEGIN
    DECLARE v_es_autor INT;
    DECLARE v_coincidencias INT;

    -- Verificar si el revisor es autor del artículo
    SELECT COUNT(*) INTO v_es_autor
    FROM Autor_Articulo
    WHERE id_articulo = p_id_articulo AND rut_autor = p_rut_revisor;

    IF v_es_autor > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El revisor no puede ser autor del artículo.';
    END IF;

    -- Verificar si los tópicos del artículo coinciden con los del revisor
    SELECT COUNT(*) INTO v_coincidencias
    FROM Articulo_Topico at
    JOIN Revisor_Topico rt ON at.id_topico = rt.id_topico
    WHERE at.id_articulo = p_id_articulo AND rt.rut_revisor = p_rut_revisor;

    IF v_coincidencias = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No hay coincidencias de tópicos entre el artículo y el revisor.';
    END IF;

    -- Asignar el artículo al revisor
    INSERT INTO Articulo_Revisor (id_articulo, rut_revisor)
    VALUES (p_id_articulo, p_rut_revisor);
END$$

DELIMITER;

DELIMITER $$

CREATE TRIGGER after_usuario_tipo_update
AFTER UPDATE ON Usuario
FOR EACH ROW
BEGIN
    -- Si el tipo de usuario cambió
    IF NEW.tipo <> OLD.tipo THEN

        -- Si ya no es Autor, eliminarlo de Autor y de Autor_Articulo
        IF NEW.tipo <> 'Autor' THEN
            DELETE FROM Autor_Articulo WHERE rut_autor = NEW.rut;
            DELETE FROM Autor WHERE rut = NEW.rut;
        END IF;

        -- Si ya no es Revisor, eliminarlo de Revisor, Revisor_Topico y Articulo_Revisor
        IF NEW.tipo <> 'Revisor' THEN
            DELETE FROM Articulo_Revisor WHERE rut_revisor = NEW.rut;
            DELETE FROM Revisor_Topico WHERE rut_revisor = NEW.rut;
            DELETE FROM Revisor WHERE rut = NEW.rut;
        END IF;

    END IF;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER before_insert_autor_articulo
BEFORE INSERT ON Autor_Articulo
FOR EACH ROW
BEGIN
    -- Validar que el autor exista en la tabla Autor
    IF (SELECT COUNT(*) FROM Autor WHERE rut = NEW.rut_autor) = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se puede asociar un autor que no existe en la tabla Autor.';
    END IF;
END$$

DELIMITER ;

CREATE VIEW vista_usuarios_login AS
SELECT 
    usuario.rut,
    usuario.password,
    usuario.nombre,
    usuario.email,
    CASE 
        WHEN EXISTS (SELECT 1 FROM Autor WHERE rut = usuario.rut) THEN 'Autor'
        WHEN EXISTS (SELECT 1 FROM Revisor WHERE rut = usuario.rut) THEN 'Revisor'
        ELSE 'Jefe Comite de Programa'
    END AS rol
FROM Usuario usuario;

SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM Articulo_Revisor;
DELETE FROM Evaluacion_Articulo;
DELETE FROM Articulo_Topico;
DELETE FROM Autor_Articulo;
DELETE FROM Revisor_Topico;
DELETE FROM Articulo;
DELETE FROM Revisor;
DELETE FROM Autor;
DELETE FROM Usuario;
DELETE FROM Topico;
ALTER TABLE Topico AUTO_INCREMENT = 1;
SET FOREIGN_KEY_CHECKS = 1;

-- Inserts para la tabla Usuario
INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES
('12159397-6', 'Michelle Blackburn', 'michelle.blackburn@pierce.info', 'michelle75', '+tXN#2P)4*4t', 'Jefe Comite de Programa'),
('07467770-4', 'Alicia Bryant', 'alicia.bryant@kelly.com', 'alicia54', 'm5+4KOx28h@k', 'Jefe Comite de Programa'),
('55864344-7', 'Olivia Phillips', 'olivia.phillips@price.com', 'olivia93', 'po75iBYq@UH1', 'Revisor'),
('38122228-7', 'Ernest Johnson', 'ernest.johnson@conrad.net', 'ernest22', '_xJ8xCeEkC(M', 'Jefe Comite de Programa'),
('77970294-1', 'Brady Colon', 'brady.colon@barnett.org', 'brady66', 'tT%7TQhNTwD4', 'Revisor'),
('82015645-9', 'Robert Turner', 'robert.turner@scott.org', 'robert57', '&7ZMibH^WXZa', 'Autor'),
('99251731-7', 'Marissa Gilmore', 'marissa.gilmore@henderson-roth.com', 'marissa66', '_4u0@Qngmhca', 'Revisor'),
('90946101-9', 'Rebecca Guzman', 'rebecca.guzman@hurst-savage.com', 'rebecca36', '#n)ZUEmDCX16', 'Revisor'),
('05055374-2', 'Leah Mcknight', 'leah.mcknight@price.info', 'leah44', '33#DBACh_A6Z', 'Jefe Comite de Programa'),
('10988547-9', 'Jason Turner', 'jason.turner@aguilar-johnson.com', 'jason91', '7h9DCt#8HF))', 'Autor'),
('52469995-7', 'Kirk Chavez', 'kirk.chavez@sanchez-anderson.com', 'kirk59', 'r6j_vH_2(RP%', 'Autor'),
('96668560-3', 'Diana Rivas', 'diana.rivas@holland.com', 'diana91', 'X)*7w%Wdt7L%', 'Revisor'),
('10312610-5', 'Angela Lopez', 'angela.lopez@cardenas.net', 'angela35', 'J#xMrU5hFY1M', 'Jefe Comite de Programa'),
('33977715-6', 'Andrea Davis', 'andrea.davis@estrada.info', 'andrea71', 'M41K_nci10)C', 'Autor'),
('46457270-5', 'Jonathan Watkins', 'jonathan.watkins@lindsey.info', 'jonathan28', '*!FJnQgwHJ6)', 'Autor'),
('78950412-3', 'Kristin Tapia', 'kristin.tapia@anderson-ford.net', 'kristin18', 'jtN4j_u2^a0h', 'Autor'),
('15034294-3', 'Samuel White', 'samuel.white@schroeder.net', 'samuel51', 'uAJxEQ$f#L6E', 'Revisor'),
('86821664-4', 'Bailey Rojas', 'bailey.rojas@sanchez-clay.org', 'bailey13', 'B+1FhwpNGrHN', 'Revisor'),
('55275417-4', 'Joseph Roberts', 'joseph.roberts@mills.org', 'joseph32', 'fte8UOZbF$Yl', 'Jefe Comite de Programa'),
('83575749-3', 'Ryan Ibarra', 'ryan.ibarra@nelson.com', 'ryan32', '(4xavvNzdFOb', 'Autor'),
('56400739-8', 'Judy Werner', 'judy.werner@salinas-myers.com', 'judy97', '(j(ZEAWk5%3u', 'Jefe Comite de Programa'),
('59828248-7', 'Michael Melton', 'michael.melton@hoffman.com', 'michael16', '4mC7nqhC%cHW', 'Autor'),
('14531810-2', 'Kristen Hinton', 'kristen.hinton@moore.info', 'kristen88', '$Nvqc&Opb&5W', 'Revisor'),
('57412140-4', 'Kyle Silva', 'kyle.silva@anderson.com', 'kyle92', 'rQ8SnNK)(QgO', 'Revisor'),
('77339393-1', 'Elizabeth Jones', 'elizabeth.jones@murray.com', 'elizabeth82', '+S7(cRYoj$Q8', 'Revisor'),
('46938240-3', 'Paul Snow', 'paul.snow@brown.org', 'paul73', '!7_rT&Qc*X(W', 'Jefe Comite de Programa'),
('06621184-6', 'Kristin Glenn', 'kristin.glenn@burns.com', 'kristin86', 'Kb_AYlIazC94', 'Jefe Comite de Programa'),
('46472899-6', 'Ann Miles', 'ann.miles@wallace.com', 'ann45', 'B_4NCSPvFl#^', 'Revisor'),
('44074575-8', 'Sandra Evans', 'sandra.evans@ortega-weaver.com', 'sandra33', 'i@XW^vQc+4ah', 'Jefe Comite de Programa'),
('15284021-1', 'Rodney Flynn', 'rodney.flynn@randolph.com', 'rodney23', '*Y*leSYv@&K4', 'Revisor'),
('17217294-9', 'Priscilla Alvarado', 'priscilla.alvarado@dunn.com', 'priscilla62', '!GfZ0oXp3MJZ', 'Autor'),
('95734155-8', 'Rachel Martin', 'rachel.martin@alexander.com', 'rachel19', '&@_5giFkP()O', 'Autor'),
('25165133-6', 'Joshua Frazier', 'joshua.frazier@hopkins-lopez.net', 'joshua99', '$8IewzKt*%Kv', 'Autor'),
('25220052-8', 'Olivia Wolfe', 'olivia.wolfe@mays-long.biz', 'olivia25', ')uRwTOG(0&c6', 'Autor'),
('07212304-2', 'Stephanie Barr', 'stephanie.barr@gutierrez-schwartz.com', 'stephanie92', '5C3WqbeISS2#', 'Autor'),
('44577196-0', 'Jonathan Burton', 'jonathan.burton@cruz.info', 'jonathan29', 'U_7OJk*VPalT', 'Revisor'),
('36438289-3', 'Scott Gomez', 'scott.gomez@hicks.com', 'scott24', '%TApLPg3H3(h', 'Jefe Comite de Programa'),
('35689290-5', 'Marcus Morris', 'marcus.morris@wells.com', 'marcus12', 'k7UTEo&6+bSQ', 'Jefe Comite de Programa'),
('11679267-2', 'Patricia Cole', 'patricia.cole@lee-smith.com', 'patricia79', '^J5Yuivt6lAF', 'Autor'),
('35078890-0', 'Charles Hunter', 'charles.hunter@keller-rios.com', 'charles31', 'YbYPs(lE!2*n', 'Jefe Comite de Programa'),
('20003151-9', 'John Lopez', 'john.lopez@wells.com', 'john26', ')1WKezK1D3Jz', 'Autor'),
('33423453-9', 'Brett Flores', 'brett.flores@randall.com', 'brett96', '#SVKyHNa@93(', 'Autor'),
('05547354-3', 'Evan Smith', 'evan.smith@goodman-myers.com', 'evan94', '8A@&zMPt(1!8', 'Autor'),
('93772035-7', 'Sara Rice', 'sara.rice@hoffman.com', 'sara67', '@9&1Y2nG&BOn', 'Autor'),
('18817795-7', 'Mary Lopez', 'mary.lopez@watts-berger.org', 'mary34', 'ScAQbQr@_$L4', 'Revisor'),
('88172385-9', 'Dylan Berry', 'dylan.berry@mcdonald.net', 'dylan71', 'n!68NK@dS@3n', 'Autor'),
('67075082-4', 'Amy Gonzalez', 'amy.gonzalez@smith-welch.com', 'amy72', 'm8CzvLsf)qZZ', 'Jefe Comite de Programa'),
('39073623-2', 'Stacey Norton', 'stacey.norton@wheeler-cross.com', 'stacey10', 'thd8cMcmrk^R', 'Revisor'),
('14866237-1', 'Scott Stevenson', 'scott.stevenson@fry.biz', 'scott21', ')8)9U9yg@2ZE', 'Jefe Comite de Programa'),
('65579498-5', 'Jared James', 'jared.james@sullivan-white.info', 'jared14', 'SfQQieAp%3Ll', 'Jefe Comite de Programa'),
('52165386-3', 'Nicholas Montoya', 'nicholas.montoya@boyle-morrow.net', 'nicholas57', '%y7rRjUhajD%', 'Autor'),
('60384315-6', 'Sergio Wilson', 'sergio.wilson@jones-francis.com', 'sergio65', 'X4a80JjL5#FT', 'Jefe Comite de Programa'),
('67855784-4', 'Kathryn Chang', 'kathryn.chang@copeland.biz', 'kathryn57', '7+$&Gh0o+wvh', 'Jefe Comite de Programa'),
('14257487-3', 'Wesley Taylor', 'wesley.taylor@williams.net', 'wesley83', 'P(xVlT7zrx9%', 'Revisor'),
('96728721-8', 'Arthur Hall', 'arthur.hall@walker.info', 'arthur99', '()fWj!VBz+4H', 'Autor'),
('27614417-1', 'George Baker', 'george.baker@brown-flores.org', 'george71', 'J(+@W5Drd+rf', 'Revisor'),
('90627865-0', 'Mary Crawford', 'mary.crawford@todd-brown.com', 'mary20', 'm*EgGJZjOq6B', 'Revisor'),
('21327278-2', 'Benjamin Smith', 'benjamin.smith@blanchard-orozco.biz', 'benjamin30', '@1BGrO@1GgFD', 'Jefe Comite de Programa'),
('85074091-7', 'Austin Mcmahon', 'austin.mcmahon@torres-fox.com', 'austin57', '4&2gABgzEZiQ', 'Autor'),
('33572592-5', 'Michael Young', 'michael.young@powell.com', 'michael88', 'GXU#T6y0*V9A', 'Revisor'),
('1', '1', '1@a', '1', '1', 'Jefe Comite de Programa');

-- Inserts para la tabla Autor
INSERT INTO Autor (rut) VALUES
('82015645-9'),
('10988547-9'),
('52469995-7'),
('33977715-6'),
('46457270-5'),
('78950412-3'),
('83575749-3'),
('59828248-7'),
('17217294-9'),
('95734155-8'),
('25165133-6'),
('25220052-8'),
('07212304-2'),
('11679267-2'),
('20003151-9'),
('33423453-9'),
('05547354-3'),
('93772035-7'),
('88172385-9'),
('52165386-3'),
('96728721-8'),
('85074091-7');

-- Inserts para la tabla Revisor
INSERT INTO Revisor (rut) VALUES
('55864344-7'),
('77970294-1'),
('99251731-7'),
('90946101-9'),
('96668560-3'),
('15034294-3'),
('86821664-4'),
('14531810-2'),
('57412140-4'),
('77339393-1'),
('46472899-6'),
('15284021-1'),
('44577196-0'),
('18817795-7'),
('39073623-2'),
('14257487-3'),
('27614417-1'),
('90627865-0'),
('33572592-5');

-- Inserts para la tabla Topico
INSERT INTO Topico (nombre) VALUES
('Machine Learning'),
('Cloud Computing'),
('Cybersecurity'),
('Data Science'),
('Blockchain'),
('Artificial Intelligence'),
('Big Data'),
('Internet of Things'),
('Quantum Computing'),
('Augmented Reality');

-- Inserts para la tabla Articulo
INSERT INTO Articulo (id_articulo, titulo, resumen, fecha_envio, estado) VALUES
(1, 'Must purpose try health technology.', 'Local raise brother fire by. A town once on turn include hotel.', '2024-07-04', 'Aprobado'),
(2, 'Him follow.', 'Include determine charge number first. Authority close single imagine mother this.', '2024-07-31', 'En revisión'),
(3, 'Worry own goal.', 'Player as dark who fly citizen. Lot indeed game Mrs task truth blue.', '2024-11-16', 'Rechazado'),
(4, 'Institution knowledge nice capital family.', 'Contain remain animal south hotel address community gun. Their south certainly suddenly.', '2025-05-13', 'Rechazado'),
(5, 'Special agent try.', 'Allow grow type. Result man garden able.', '2025-03-04', 'En revisión'),
(6, 'Peace government book step.', 'Character miss up traditional blood model physical great.', '2024-05-21', 'Aprobado'),
(7, 'Production some beautiful beat.', 'Teacher everything clearly party resource without plan. Budget indicate probably thank affect.', '2024-10-20', 'Aprobado'),
(8, 'Reduce tell doctor food all.', 'Agree once trial card report. Long hard often court political. Magazine land inside short.', '2024-10-09', 'Aprobado'),
(9, 'Ok reflect end exactly.', 'Area then financial keep two opportunity. Attorney accept add nearly stop share.', '2025-05-01', 'Rechazado'),
(10, 'Total base win enough oil.', 'Believe four bad station wall section performance. Value short like spend year on truth.', '2025-04-24', 'En revisión'),
(11, 'Mean possible evening family.', 'Without agree always not. Again pattern poor money walk on first.', '2024-07-06', 'Rechazado'),
(12, 'Be camera me partner.', 'Mouth structure not lead past explain. Baby whose brother list too cup with.', '2024-05-18', 'En revisión'),
(13, 'Break follow.', 'Agreement animal agency.
Meet bring leave. Good sing style tree. Section word get seven.', '2024-07-19', 'Aprobado'),
(14, 'Perform unit rest industry.', 'Room anything usually watch. Animal less unit board. Car poor fight.', '2024-06-04', 'En revisión'),
(15, 'Some prevent place start.', 'Cost rather space lay. Drop race practice. Past never magazine court.', '2025-02-03', 'Aprobado'),
(16, 'How country administration low.', 'Thousand sit condition hot knowledge result. Happy magazine phone. Sell nice best team.', '2024-10-05', 'Aprobado'),
(17, 'Gas cut consumer bad.', 'Maintain require myself wear economy course south. Recent try require message little growth.', '2024-11-23', 'En revisión'),
(18, 'Sometimes rule.', 'Because design everybody boy individual far. Stay behavior second.', '2025-02-22', 'En revisión'),
(19, 'Sometimes nice audience among.', 'Side indicate hospital senior. Quickly organization radio teach language ever interview.', '2024-09-27', 'Aprobado'),
(20, 'Speech recently rule arrive.', 'Community receive capital more. After if truth everybody system.', '2025-04-03', 'Rechazado'),
(21, 'Call style.', 'Nor while nearly answer rock present certainly. Reach bring knowledge degree affect.', '2024-09-01', 'En revisión'),
(22, 'Write pick generation south.', 'Compare property season. Finish rich something issue. Should tend key outside whether.', '2024-10-04', 'En revisión'),
(23, 'These population decide can.', 'Professor person very own evening exactly civil.', '2024-09-27', 'En revisión'),
(24, 'Drug deep direction spring.', 'Speak born toward blood likely indeed letter my. Country throw hit your fast success.', '2024-12-27', 'En revisión'),
(25, 'Politics west home left.', 'Local thus beat keep fall. Enough as paper successful player one.', '2025-03-12', 'Rechazado'),
(26, 'Consumer might relationship.', 'Evidence listen bank more. Play simple at month face perform reach.
Could her former.', '2024-08-08', 'Rechazado'),
(27, 'Laugh I into glass must.', 'Middle entire Mr almost. Child travel put believe boy.', '2024-07-27', 'Rechazado'),
(28, 'Officer item three.', 'Performance whether might magazine realize plant. Building plant sometimes.', '2024-06-20', 'Rechazado'),
(29, 'Throw financial better.', 'Story hot final early lose. It international box. Other hour visit return language improve.', '2025-01-31', 'En revisión'),
(30, 'Within concern effort.', 'Later travel purpose employee film.', '2024-07-04', 'En revisión'),
(31, 'Guess analysis.', 'Newspaper role case line special look here item. Wall protect experience option remember plant.', '2024-05-19', 'Rechazado'),
(32, 'Something into only.', 'Visit money necessary better. Big any dog part produce population.', '2025-03-13', 'En revisión'),
(33, 'Body exactly me.', 'Strong involve value his rise. Fill behind administration mind assume arrive authority.', '2025-04-03', 'Rechazado'),
(34, 'People wear could activity small.', 'Top threat collection building. Kind after staff ago field. Method Mr glass watch walk use.', '2024-12-13', 'Rechazado'),
(35, 'While eat foreign.', 'Senior west campaign machine teach peace. Almost science receive admit rise.', '2024-05-22', 'Rechazado'),
(36, 'High seven price heart.', 'Before want fast ground. Might dinner rule.', '2024-09-22', 'Aprobado'),
(37, 'Modern drop.', 'Option future current job. Treatment policy decade to social change without.', '2024-06-04', 'En revisión'),
(38, 'Focus Mrs here.', 'Thus over professional keep result serious reach. Across alone relate those power.', '2025-01-31', 'En revisión'),
(39, 'Analysis form front.', 'Stay another tonight almost sea about myself including. Return pattern six. Work writer red hand.', '2025-01-14', 'Aprobado'),
(40, 'Tell left fill include stop.', 'Kitchen car station want already all by. Throw throughout his air good mean against for.', '2024-12-22', 'En revisión'),
(41, 'Business argue.', 'Area try send war. Cover glass total return force leader themselves remain.', '2024-09-25', 'En revisión'),
(42, 'Sense long power smile.', 'Manage whose region statement hospital claim. By stay especially safe who tonight small.', '2025-02-22', 'Aprobado'),
(43, 'Card knowledge.', 'Brother clear administration speech whom probably. Section test begin network little wife.', '2024-11-20', 'Rechazado'),
(44, 'Mean drug radio owner.', 'Recognize certainly somebody true writer. Success who product time.
However want anyone.', '2024-06-17', 'Aprobado'),
(45, 'Natural international increase side.', 'Quickly issue kitchen remember. Save time explain size. Personal education itself reveal modern.', '2024-10-19', 'Rechazado'),
(46, 'Owner world toward include tonight.', 'Very research second true develop catch. Form design TV yet.', '2024-11-01', 'Aprobado'),
(47, 'Behind edge agreement.', 'Product push career just throw.', '2025-03-22', 'Aprobado'),
(48, 'White against theory rock.', 'Group what cup rest interview.', '2024-07-16', 'Rechazado'),
(49, 'Environmental light three fact yourself.', 'Trouble civil identify fly. Agreement actually two remain voice religious.', '2025-03-17', 'En revisión'),
(50, 'President land movie.', 'Property every put not notice. Light visit them force.', '2024-10-12', 'En revisión');

-- Inserts para la tabla Articulo_Topico
INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES
(1, 9),
(2, 5),
(3, 8),
(4, 2),
(5, 5),
(6, 7),
(7, 9),
(8, 2),
(9, 2),
(10, 3),
(11, 9),
(12, 7),
(13, 1),
(14, 6),
(15, 3),
(16, 5),
(17, 4),
(18, 3),
(19, 7),
(20, 1),
(21, 3),
(22, 7),
(23, 9),
(24, 2),
(25, 4),
(26, 6),
(27, 8),
(28, 8),
(29, 5),
(30, 7),
(31, 8),
(32, 5),
(33, 10),
(34, 10),
(35, 4),
(36, 5),
(37, 10),
(38, 7),
(39, 3),
(40, 7),
(41, 6),
(42, 5),
(43, 4),
(44, 5),
(45, 6),
(46, 4),
(47, 4),
(48, 7),
(49, 2),
(50, 6);

-- Inserts para la tabla Evaluacion_Articulo
INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES
(1, '14257487-3', 'Create whether eat report third again move ready student perhaps cell.', 4),
(2, '77970294-1', 'However five growth act a offer before admit according after floor.', 1),
(3, '57412140-4', 'They tend happen herself program throw pay.', 10),
(4, '14257487-3', 'Employee anything effort another though book doctor industry land.', 1),
(5, '77339393-1', 'Check field north open million full.', 6),
(6, '18817795-7', 'Open personal but physical protect hit leave wife.', 1),
(7, '90946101-9', 'Within region accept set listen star off keep nation guess relate candidate.', 6),
(8, '14531810-2', 'Own current senior film relate media seven none ahead issue surface painting not.', 9),
(9, '15034294-3', 'Career Mr end bit along able.', 5),
(10, '55864344-7', 'Determine age establish various through where forward agree wind though up number low.', 4),
(11, '27614417-1', 'Total none edge before pressure how better same PM born throw rock vote.', 8),
(12, '96668560-3', 'Chair decision trouble behavior when wall.', 6),
(13, '14531810-2', 'Nature there though assume matter teacher animal compare road could dark you.', 4),
(14, '27614417-1', 'Vote rock manager next really series whose hard together wrong buy.', 9),
(15, '44577196-0', 'Executive whether standard hour claim leave baby finish truth national.', 1),
(16, '15284021-1', 'Paper commercial he debate enter contain situation the take field few another open.', 10),
(17, '55864344-7', 'Wife north fall writer reduce theory response decision herself range.', 9),
(18, '18817795-7', 'Respond determine adult Mrs against left draw.', 1),
(19, '18817795-7', 'Memory one dog sea lot who.', 7),
(20, '14531810-2', 'One doctor these present thought commercial happen thing.', 7),
(21, '14531810-2', 'Wonder special player move many me almost quality program line system.', 3),
(22, '90627865-0', 'Cut condition win enter along board avoid name himself those dog black another.', 1),
(23, '39073623-2', 'Occur up front discussion different son cultural.', 4),
(24, '55864344-7', 'Clearly show before save reach woman well adult simple.', 9),
(25, '90946101-9', 'Study tough charge education front admit she opportunity green step pay.', 5),
(26, '18817795-7', 'Letter term toward drive attack hair truth my nothing.', 6),
(27, '99251731-7', 'Baby mention gun store only while fact rise office section anything environmental rather.', 7),
(28, '27614417-1', 'Change direction product pay success question machine good.', 9),
(29, '44577196-0', 'Almost case visit despite several politics media exactly leg attorney right bad.', 8),
(30, '44577196-0', 'Democrat cultural hundred produce business sort later fast who over today.', 5),
(31, '86821664-4', 'Thousand enough cover say general happy agency month southern price evidence per politics.', 1),
(32, '44577196-0', 'Moment network house above few help assume for kitchen fast improve training.', 2),
(33, '90627865-0', 'Process collection attention remain another remain officer partner president occur here store nature.', 9),
(34, '14531810-2', 'Field manager anything purpose foot ability approach behavior.', 1),
(35, '77970294-1', 'Land value away two present explain impact mouth life support dinner well.', 3),
(36, '44577196-0', 'Seek meet site trip range foreign activity education nation government evening share explain.', 4),
(37, '14531810-2', 'Quality consider reduce point state character defense nature.', 9),
(38, '55864344-7', 'Two option success agree reflect apply those animal list plan memory.', 2),
(39, '77339393-1', 'Again number agent agency budget lead money sure.', 10),
(40, '39073623-2', 'Energy blood not participant magazine once.', 3),
(41, '15034294-3', 'Wall discover partner fast door media new despite race officer politics outside peace.', 7),
(42, '15034294-3', 'Wear of apply away area hit west beautiful dinner.', 2),
(43, '55864344-7', 'Every identify short certainly growth southern wait assume conference detail black general.', 10),
(44, '57412140-4', 'Young similar last report money much live game under in subject happen.', 10),
(45, '18817795-7', 'Give west range above interesting threat wall produce avoid its minute similar.', 6),
(46, '90946101-9', 'End rest own likely force water affect soon black.', 7),
(47, '90946101-9', 'Possible factor action system however since prove common large.', 4),
(48, '18817795-7', 'Successful picture far forget federal employee character four.', 3),
(49, '55864344-7', 'Between bag build serve in arm.', 1),
(50, '90946101-9', 'Pm check card ten relationship write act eye the.', 3);

-- Inserts para la tabla Revisor_Topico
INSERT INTO Revisor_Topico (rut_revisor, id_topico) VALUES
('55864344-7', 6),
('55864344-7', 9),
('77970294-1', 6),
('77970294-1', 4),
('77970294-1', 2),
('99251731-7', 8),
('99251731-7', 10),
('90946101-9', 3),
('90946101-9', 4),
('90946101-9', 1),
('96668560-3', 8),
('15034294-3', 8),
('15034294-3', 7),
('86821664-4', 10),
('86821664-4', 7),
('86821664-4', 6),
('14531810-2', 7),
('14531810-2', 4),
('14531810-2', 8),
('57412140-4', 3),
('77339393-1', 10),
('77339393-1', 8),
('46472899-6', 1),
('46472899-6', 9),
('15284021-1', 2),
('15284021-1', 7),
('44577196-0', 9),
('44577196-0', 5),
('44577196-0', 8),
('18817795-7', 4),
('18817795-7', 2),
('39073623-2', 2),
('39073623-2', 8),
('14257487-3', 1),
('27614417-1', 3),
('90627865-0', 4),
('90627865-0', 9),
('90627865-0', 7),
('33572592-5', 1);

-- Inserts para la tabla Autor_Articulo
INSERT INTO Autor_Articulo (id_articulo, rut_autor, es_contacto) VALUES
(1, '85074091-7', 1),
(1, '82015645-9', 0),
(1, '46457270-5', 0),
(2, '95734155-8', 1),
(2, '83575749-3', 0),
(2, '07212304-2', 0),
(3, '96728721-8', 1),
(4, '82015645-9', 1),
(5, '93772035-7', 1),
(6, '25165133-6', 1),
(7, '25165133-6', 1),
(7, '33977715-6', 0),
(8, '17217294-9', 1),
(9, '59828248-7', 1),
(10, '59828248-7', 1),
(10, '88172385-9', 0),
(10, '85074091-7', 0),
(11, '59828248-7', 1),
(11, '88172385-9', 0),
(12, '93772035-7', 1),
(12, '82015645-9', 0),
(13, '93772035-7', 1),
(13, '83575749-3', 0),
(13, '95734155-8', 0),
(14, '52469995-7', 1),
(15, '33977715-6', 1),
(16, '33423453-9', 1),
(17, '93772035-7', 1),
(17, '25220052-8', 0),
(18, '52469995-7', 1),
(18, '83575749-3', 0),
(19, '07212304-2', 1),
(19, '83575749-3', 0),
(20, '52469995-7', 1),
(20, '59828248-7', 0),
(20, '96728721-8', 0),
(21, '95734155-8', 1),
(21, '52469995-7', 0),
(22, '88172385-9', 1),
(22, '10988547-9', 0),
(22, '59828248-7', 0),
(23, '88172385-9', 1),
(23, '83575749-3', 0),
(23, '96728721-8', 0),
(24, '17217294-9', 1),
(25, '07212304-2', 1),
(25, '82015645-9', 0),
(26, '95734155-8', 1),
(27, '46457270-5', 1),
(27, '07212304-2', 0),
(28, '07212304-2', 1),
(28, '25220052-8', 0),
(29, '20003151-9', 1),
(30, '95734155-8', 1),
(31, '20003151-9', 1),
(32, '78950412-3', 1),
(32, '33977715-6', 0),
(32, '07212304-2', 0),
(33, '05547354-3', 1),
(34, '52165386-3', 1),
(34, '85074091-7', 0),
(35, '20003151-9', 1),
(35, '82015645-9', 0),
(36, '25220052-8', 1),
(36, '17217294-9', 0),
(36, '96728721-8', 0),
(37, '52469995-7', 1),
(38, '05547354-3', 1),
(38, '07212304-2', 0),
(38, '59828248-7', 0),
(39, '07212304-2', 1),
(39, '17217294-9', 0),
(39, '95734155-8', 0),
(40, '88172385-9', 1),
(41, '78950412-3', 1),
(42, '82015645-9', 1),
(43, '78950412-3', 1),
(43, '07212304-2', 0),
(43, '25220052-8', 0),
(44, '52165386-3', 1),
(45, '52469995-7', 1),
(46, '78950412-3', 1),
(47, '78950412-3', 1),
(48, '52469995-7', 1),
(48, '20003151-9', 0),
(48, '11679267-2', 0),
(49, '96728721-8', 1),
(50, '07212304-2', 1),
(50, '83575749-3', 0);

-- Inserts para la tabla Articulo_Revisor
INSERT INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES
(1, '55864344-7'),
(1, '44577196-0'),
(2, '44577196-0'),
(3, '77339393-1'),
(3, '96668560-3'),
(4, '15284021-1'),
(5, '44577196-0'),
(6, '86821664-4'),
(7, '55864344-7'),
(8, '39073623-2'),
(8, '77970294-1'),
(9, '15284021-1'),
(10, '57412140-4'),
(11, '44577196-0'),
(12, '90627865-0'),
(12, '15034294-3'),
(13, '14257487-3'),
(13, '46472899-6'),
(14, '77970294-1'),
(15, '27614417-1'),
(15, '57412140-4'),
(16, '44577196-0'),
(17, '77970294-1'),
(17, '14531810-2'),
(18, '90946101-9'),
(19, '90627865-0'),
(19, '14531810-2'),
(20, '33572592-5'),
(20, '46472899-6'),
(21, '90946101-9'),
(22, '14531810-2'),
(22, '86821664-4'),
(23, '90627865-0'),
(24, '39073623-2'),
(24, '18817795-7'),
(25, '90946101-9'),
(25, '14531810-2'),
(26, '86821664-4'),
(27, '99251731-7'),
(27, '44577196-0'),
(28, '77339393-1'),
(29, '44577196-0'),
(30, '86821664-4'),
(30, '15034294-3'),
(31, '39073623-2'),
(31, '77339393-1'),
(32, '44577196-0'),
(33, '99251731-7'),
(34, '99251731-7'),
(35, '18817795-7'),
(36, '44577196-0'),
(37, '86821664-4'),
(37, '99251731-7'),
(38, '14531810-2'),
(39, '90946101-9'),
(39, '27614417-1'),
(40, '15284021-1'),
(40, '90627865-0'),
(41, '77970294-1'),
(41, '55864344-7'),
(42, '44577196-0'),
(43, '90946101-9'),
(44, '44577196-0'),
(45, '55864344-7'),
(45, '86821664-4'),
(46, '77970294-1'),
(47, '14531810-2'),
(47, '77970294-1'),
(48, '15034294-3'),
(48, '90627865-0'),
(49, '77970294-1'),
(50, '86821664-4'),
(50, '55864344-7');

