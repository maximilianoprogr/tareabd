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
    ) NOT NULL,
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
('01465478-3', 'Michael Cruz', 'michael.cruz@foster.com', 'michael46', 'er!P9QfS%2J%', 'Autor'),
('31520869-1', 'Amanda Webster', 'amanda.webster@larsen.org', 'amanda16', 'doO4uj@g_4_O', 'Jefe Comite de Programa'),
('62893146-2', 'Christopher Lynch', 'christopher.lynch@thomas.info', 'christopher66', 'I)4Flk1SR@UQ', 'Autor'),
('79223224-5', 'Amy Williams', 'amy.williams@ramsey-moss.com', 'amy79', 'E%S)^*sDI1Ba', 'Revisor'),
('90017413-7', 'Candice Lee', 'candice.lee@roberts.com', 'candice84', '_SKNOaIn8)Ft', 'Jefe Comite de Programa'),
('84258957-8', 'Eric Johnson', 'eric.johnson@hill.com', 'eric54', '@iF0dOl$Q04U', 'Jefe Comite de Programa'),
('04234251-7', 'Gregory Hoffman', 'gregory.hoffman@munoz-quinn.com', 'gregory97', 'U0oRmt)bza9(', 'Autor'),
('66440438-0', 'Lisa Liu', 'lisa.liu@cox-jimenez.org', 'lisa36', 'fl1zEHekJ(WD', 'Autor'),
('85181040-3', 'Anna Aguirre', 'anna.aguirre@quinn-martinez.net', 'anna10', 'NLNI6Ffi#3Cu', 'Jefe Comite de Programa'),
('36313732-3', 'Tina Villegas', 'tina.villegas@brown.com', 'tina25', 'vzKRyKOWg&5Y', 'Revisor'),
('66759579-5', 'Christopher Macias', 'christopher.macias@herrera.com', 'christopher71', '+2iPL5Wx5rf#', 'Jefe Comite de Programa'),
('04868801-8', 'Thomas Fisher', 'thomas.fisher@mclaughlin-roberts.com', 'thomas28', 'b%545gyF@aS_', 'Autor'),
('06963999-0', 'Renee Lester', 'renee.lester@wilson.biz', 'renee54', '^mT!ElOcB!0D', 'Revisor'),
('31823126-9', 'Kathleen Green', 'kathleen.green@velez.com', 'kathleen32', '@40kXDMxM1J&', 'Autor'),
('29599985-7', 'Robin Compton', 'robin.compton@harrison-taylor.com', 'robin37', 'q_yq6Q3tdD(7', 'Autor'),
('96713375-4', 'Ian Smith', 'ian.smith@gray-stewart.com', 'ian14', '!HmNtmN9)4UP', 'Jefe Comite de Programa'),
('16735564-8', 'Curtis Alexander', 'curtis.alexander@hendricks-sanchez.com', 'curtis90', 'P(0Af$^znM0C', 'Autor'),
('93351481-6', 'Martin Branch', 'martin.branch@white-green.com', 'martin55', '!20P#M6hJnaT', 'Jefe Comite de Programa'),
('68773957-5', 'Jonathon Butler', 'jonathon.butler@smith-park.com', 'jonathon16', 'd!2_rNiediXh', 'Autor'),
('88302316-0', 'Courtney Atkins', 'courtney.atkins@sanders.org', 'courtney29', 'ec1G5MuGo+v5', 'Autor'),
('06834623-4', 'Andres Mcdaniel', 'andres.mcdaniel@jones-greene.net', 'andres65', 'lyapSP2s#144', 'Jefe Comite de Programa'),
('41497157-5', 'Cory Gates', 'cory.gates@knapp.net', 'cory88', 'eE&gY&QpuLs9', 'Autor'),
('44795984-7', 'Bruce Johnson', 'bruce.johnson@erickson.com', 'bruce73', 'HHArmMll*4@1', 'Revisor'),
('83474460-1', 'Susan Williams', 'susan.williams@peterson-young.net', 'susan26', ')RK4V&iwUC8Y', 'Jefe Comite de Programa'),
('21054320-8', 'Angela Pierce', 'angela.pierce@patrick-harris.com', 'angela19', '@CTFMb&g34TA', 'Jefe Comite de Programa'),
('40175100-9', 'Katherine Miller', 'katherine.miller@stone.org', 'katherine58', 'a&7XGA4vt%AT', 'Revisor'),
('50030813-0', 'Jose Roy', 'jose.roy@marshall-moore.com', 'jose88', ')$UlHTk)6Lb1', 'Autor'),
('72334850-7', 'Jennifer Steele', 'jennifer.steele@pierce.info', 'jennifer70', '305Ybt1Ox&0#', 'Revisor'),
('77042807-4', 'Jessica Barber', 'jessica.barber@castillo.com', 'jessica34', 'c3ueA*ne#wn8', 'Autor'),
('40665589-2', 'Elizabeth Robinson', 'elizabeth.robinson@robinson.net', 'elizabeth93', '%JC5(3Dw)NZo', 'Revisor'),
('71655145-3', 'Travis Lynch', 'travis.lynch@jackson-ward.com', 'travis65', '9^OP$hJU_PN0', 'Revisor'),
('63588311-0', 'Aaron Davis', 'aaron.davis@mason.com', 'aaron97', '4wD469Axsts(', 'Autor'),
('47433732-6', 'Harry Harris', 'harry.harris@summers.com', 'harry97', '!A(chhQ(&1aI', 'Autor'),
('28232352-5', 'Jeremy Montgomery', 'jeremy.montgomery@franklin-larson.com', 'jeremy94', 'h5@6$_UZ*OVG', 'Revisor'),
('49861967-3', 'Angela Carroll', 'angela.carroll@williams.com', 'angela37', 'l9(edRfM&GCc', 'Revisor'),
('68075295-4', 'Mark Clark', 'mark.clark@patterson.org', 'mark57', '4uB6mHvGK)K6', 'Autor'),
('02233768-1', 'Brandon Parrish', 'brandon.parrish@anderson.com', 'brandon13', '&54Aa80k5r(A', 'Revisor'),
('77645167-2', 'Joshua Roth', 'joshua.roth@becker-clark.net', 'joshua63', '@6XyQG2eW)Li', 'Autor'),
('48441958-8', 'Dan Wright', 'dan.wright@price.com', 'dan21', '4qT7yMdd!YAO', 'Revisor'),
('40804729-3', 'Brandi Griffin', 'brandi.griffin@moreno.com', 'brandi34', 'E)Gb9Qa0Qe^@', 'Autor'),
('88316329-3', 'Jeffrey Johnson', 'jeffrey.johnson@miller.biz', 'jeffrey33', '8aWQ&uB0^wl(', 'Autor'),
('03285469-9', 'Michael Sampson', 'michael.sampson@nguyen.com', 'michael49', '*EbLYjOlvXY9', 'Revisor'),
('91275497-3', 'Jennifer Robinson', 'jennifer.robinson@thompson.org', 'jennifer11', 'jr7D$cq2V*mF', 'Autor'),
('28251255-9', 'Matthew Ward', 'matthew.ward@gilmore-wolf.com', 'matthew92', '!qeT(_koAT0)', 'Jefe Comite de Programa'),
('63289117-5', 'Tyler Johnson', 'tyler.johnson@lee.com', 'tyler83', '%OIzHnbx6xLu', 'Autor'),
('65914331-6', 'Peggy Castro', 'peggy.castro@gilbert-carpenter.biz', 'peggy29', 'U9r1HUgc$6KU', 'Autor'),
('67622506-1', 'Jennifer Valentine', 'jennifer.valentine@gonzalez.com', 'jennifer65', '!xa9#rwGA0Lv', 'Revisor'),
('66205590-0', 'Timothy Adkins', 'timothy.adkins@garrison.com', 'timothy82', 'C4BoVUG(%HI%', 'Autor'),
('37511665-0', 'Megan Roberts', 'megan.roberts@gamble.com', 'megan83', 'vf57Yy&8#g9H', 'Autor'),
('63832106-0', 'Debra Stark', 'debra.stark@johnson.net', 'debra72', '#$DaV0&rWD7K', 'Jefe Comite de Programa'),
('25232732-9', 'Phillip Hill', 'phillip.hill@sanders-martinez.com', 'phillip92', 'c87PrTsJNOT$', 'Jefe Comite de Programa'),
('97430570-2', 'Erin Harris', 'erin.harris@ramsey.com', 'erin72', 'M8v6PXCzvcN_', 'Jefe Comite de Programa'),
('14439053-9', 'Lisa Salas', 'lisa.salas@richmond-alvarez.com', 'lisa76', 'Z&2vhBZvuDBC', 'Autor'),
('27165056-4', 'Michael Andersen', 'michael.andersen@haynes.org', 'michael15', '$RN1k(Xj5R_Z', 'Revisor'),
('48633378-2', 'Alyssa Johnson', 'alyssa.johnson@beard-lopez.biz', 'alyssa16', 'HtD6w8Ft1TG@', 'Autor'),
('19376305-7', 'Amy Peters', 'amy.peters@edwards-stone.org', 'amy55', '@#_t!EU^8u8(', 'Revisor'),
('13250832-5', 'Michele Reynolds', 'michele.reynolds@kramer.com', 'michele89', 'O09!YNs3n#PI', 'Revisor'),
('22922502-6', 'Emily Wong', 'emily.wong@griffith-ward.com', 'emily13', 'n0VsbFug$scB', 'Autor'),
('17485946-1', 'Dennis Pope', 'dennis.pope@brown-cruz.net', 'dennis92', 'A4eDarL@*RUj', 'Autor'),
('40890323-1', 'Teresa Jackson', 'teresa.jackson@pena.com', 'teresa30', '#1LXV9fi$d8g', 'Autor'),
('1', '1', '1@gmail.com', '1', '1', 'Jefe Comite de Programa');

-- Inserts para la tabla Autor
INSERT INTO Autor (rut) VALUES
('01465478-3'),
('62893146-2'),
('04234251-7'),
('66440438-0'),
('04868801-8'),
('31823126-9'),
('29599985-7'),
('16735564-8'),
('68773957-5'),
('88302316-0'),
('41497157-5'),
('50030813-0'),
('77042807-4'),
('63588311-0'),
('47433732-6'),
('68075295-4'),
('77645167-2'),
('40804729-3'),
('88316329-3'),
('91275497-3'),
('63289117-5'),
('65914331-6'),
('66205590-0'),
('37511665-0'),
('14439053-9'),
('48633378-2'),
('22922502-6'),
('17485946-1'),
('40890323-1');

-- Inserts para la tabla Revisor
INSERT INTO Revisor (rut) VALUES
('79223224-5'),
('36313732-3'),
('06963999-0'),
('44795984-7'),
('40175100-9'),
('72334850-7'),
('40665589-2'),
('71655145-3'),
('28232352-5'),
('49861967-3'),
('02233768-1'),
('48441958-8'),
('03285469-9'),
('67622506-1'),
('27165056-4'),
('19376305-7'),
('13250832-5');

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
INSERT INTO Articulo (id_articulo, titulo, resumen, fecha_envio, rut_autor, estado) VALUES
(1, 'Establish spring.', 'Language level man partner close step simply. Speech together place top.', '2024-08-21', '66205590-0', 'Aprobado'),
(2, 'Pm late name difficult.', 'My draw expert share major easy. High would ten support.', '2025-04-02', '37511665-0', 'Aprobado'),
(3, 'Receive over dog bank investment.', 'Four watch language then spend. Fight weight option your act three notice senior. Help but end lay.', '2024-09-10', '48633378-2', 'En revisión'),
(4, 'Sport wish until season.', 'Their player become. One particular high myself.', '2024-10-27', '63588311-0', 'En revisión'),
(5, 'Blood what part.', 'Music clearly a. Test send knowledge research certain mention resource.', '2025-04-08', '50030813-0', 'Aprobado'),
(6, 'Hard decade.', 'Create support usually less.', '2024-10-09', '16735564-8', 'Rechazado'),
(7, 'Financial learn unit.', 'Place recognize sea draw before. Method brother keep another plant door local.', '2024-07-04', '88302316-0', 'En revisión'),
(8, 'State than recognize manage save.', 'Peace simple draw thus. Claim however time few recently president reality.', '2025-02-07', '48633378-2', 'Aprobado'),
(9, 'Nation left city.', 'Something high thought. Art field fine coach again. Sound recent show.', '2025-02-09', '68075295-4', 'Rechazado'),
(10, 'Human former team car another.', 'Research enter read listen special.', '2024-10-09', '01465478-3', 'Rechazado'),
(11, 'Wall business authority.', 'Look recently success. Analysis easy summer fund somebody.', '2024-09-13', '16735564-8', 'Aprobado'),
(12, 'Look successful budget data.', 'Model wide even enjoy. Spend walk they quite second admit few.', '2024-09-08', '65914331-6', 'Aprobado'),
(13, 'Trip consider.', 'Call change quickly prepare. Maybe sister situation second. Two degree along ask subject hot.', '2024-05-27', '66205590-0', 'Rechazado'),
(14, 'Bed involve what song article.', 'Million right for smile turn note coach. Tonight range avoid within strong.', '2024-10-20', '63289117-5', 'Rechazado'),
(15, 'Above something four television.', 'Include measure then focus. His degree service coach fact. Specific rate rather quickly fear.', '2024-06-27', '66205590-0', 'En revisión'),
(16, 'Cold discussion television.', 'You trouble leave moment new keep arrive. Too one know him. Power small wall seek.', '2024-05-22', '65914331-6', 'En revisión'),
(17, 'Radio leg.', 'Remain opportunity small. Politics finish after hot offer will. Get pass shoulder compare return.', '2024-06-01', '40804729-3', 'Rechazado'),
(18, 'Expect rise bag should.', 'Stop audience rule suggest. Heavy season thought single.', '2024-06-05', '31823126-9', 'Rechazado'),
(19, 'Once heavy blue.', 'Represent half sound. Down itself knowledge party white part.', '2025-03-19', '04868801-8', 'En revisión'),
(20, 'Join by adult hospital former.', 'Movie should card politics notice civil. Fire national activity site goal gas always.', '2024-09-24', '04868801-8', 'Aprobado'),
(21, 'Half indicate know along.', 'Fight explain fact. Claim argue clear few hard.', '2024-11-17', '17485946-1', 'Rechazado'),
(22, 'Road such member begin now.', 'Research technology senior. Mean card billion foot radio song rather.', '2024-07-20', '14439053-9', 'En revisión'),
(23, 'Always shoulder.', 'Paper thing house yet. Issue never music hot yourself will.', '2024-10-09', '48633378-2', 'Aprobado'),
(24, 'Produce agreement manager opportunity.', 'Toward environment some assume.', '2025-03-19', '40890323-1', 'Rechazado'),
(25, 'Success capital edge drop black.', 'Product ten film training detail without south. Sea alone talk.', '2025-04-18', '31823126-9', 'Rechazado'),
(26, 'Feel audience call.', 'Later reason skill chance let. Policy wish buy deep.
Save happy white sea not better popular think.', '2025-04-28', '22922502-6', 'Aprobado'),
(27, 'Full despite word beautiful other.', 'Door realize want close cost put. Window me nice role nor.', '2025-05-13', '14439053-9', 'Aprobado'),
(28, 'Response unit including reason compare.', 'Around entire policy management dream. Someone whose police brother national decision meet.', '2024-12-06', '68773957-5', 'Aprobado'),
(29, 'At Congress inside.', 'Central place pay direction end history fall staff. Official risk measure address among boy task.', '2024-11-16', '01465478-3', 'Rechazado'),
(30, 'Future window at.', 'Painting above do four drop various until. Pull turn floor. Gas fast million wide.', '2025-03-08', '14439053-9', 'En revisión'),
(31, 'Total question American.', 'Develop born he until. Clear style make almost season. Party attack Mrs answer without stop.', '2024-08-29', '62893146-2', 'En revisión'),
(32, 'Image part high ok.', 'Local check soldier specific page how. Amount yourself to move run there medical.', '2025-01-07', '47433732-6', 'En revisión'),
(33, 'Maybe design.', 'Mention half quite. Administration general old four author evening.', '2024-09-05', '77042807-4', 'Aprobado'),
(34, 'That appear although role one.', 'Walk although finish happen child modern. Position week economy a concern give artist.', '2024-12-15', '22922502-6', 'Aprobado'),
(35, 'Item management south.', 'So tonight idea.', '2024-11-23', '47433732-6', 'En revisión'),
(36, 'Southern someone course.', 'Simply industry add person seven. Hospital from office long trip.', '2025-05-08', '68773957-5', 'En revisión'),
(37, 'Find executive occur focus including.', 'Need true sure attention. Look pick its may myself practice.', '2024-12-08', '22922502-6', 'En revisión'),
(38, 'Suggest success sit watch resource.', 'Popular know ask agree. Know surface space control hope friend. Us care feel ask.', '2024-10-10', '65914331-6', 'En revisión'),
(39, 'Must boy.', 'Growth east worry theory by election speak. Year south agreement government.', '2024-12-04', '31823126-9', 'Rechazado'),
(40, 'Available pressure end.', 'Want late why enjoy woman back. Thank second mention clearly art voice relate. Dark material again.', '2025-02-24', '66440438-0', 'En revisión'),
(41, 'Out consumer shake threat.', 'Nor trade bed defense. Month politics indicate.', '2025-05-15', '88316329-3', 'Aprobado'),
(42, 'Watch she.', 'Tough what training choice specific available. Care want instead single.', '2025-01-20', '88316329-3', 'En revisión'),
(43, 'Cost carry challenge.', 'Into window raise myself animal hard thing. Artist data both whole. Story expect seven final Mr.', '2024-10-06', '91275497-3', 'Rechazado'),
(44, 'Hope notice family try.', 'Apply speak administration almost.', '2025-01-11', '29599985-7', 'Rechazado'),
(45, 'Term amount face indicate plan.', 'Energy try their play south. Economic often end dinner above story talk sit.', '2024-06-04', '68075295-4', 'En revisión'),
(46, 'Likely what fine.', 'Total executive opportunity. Thing term whole behind his maybe.', '2024-07-05', '37511665-0', 'Aprobado'),
(47, 'Music with.', 'From plan network themselves campaign bag. I foreign finish fire figure arm.', '2024-06-22', '77042807-4', 'En revisión'),
(48, 'Wonder which bag billion.', 'Ago rock cell large. So its last cup either notice. Either firm night simple improve others send.', '2024-06-28', '48633378-2', 'Aprobado'),
(49, 'Participant democratic deep Democrat.', 'Recent team itself exactly near however.', '2025-03-14', '88316329-3', 'Rechazado'),
(50, 'Require behind recent.', 'Up open pick space herself. Be word whom cell challenge read indeed. Risk walk play movie minute.', '2025-05-07', '88302316-0', 'En revisión');

-- Inserts para la tabla Articulo_Topico
INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES
(1, 10),
(2, 2),
(3, 2),
(4, 10),
(5, 2),
(6, 10),
(7, 6),
(8, 10),
(9, 3),
(10, 8),
(11, 2),
(12, 8),
(13, 4),
(14, 3),
(15, 5),
(16, 9),
(17, 3),
(18, 5),
(19, 7),
(20, 1),
(21, 6),
(22, 6),
(23, 3),
(24, 2),
(25, 5),
(26, 7),
(27, 2),
(28, 10),
(29, 1),
(30, 9),
(31, 3),
(32, 5),
(33, 5),
(34, 1),
(35, 8),
(36, 5),
(37, 8),
(38, 9),
(39, 8),
(40, 6),
(41, 7),
(42, 5),
(43, 1),
(44, 5),
(45, 8),
(46, 10),
(47, 9),
(48, 8),
(49, 10),
(50, 5);

-- Inserts para la tabla Evaluacion_Articulo
INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES
(1, '03285469-9', 'Accept skin upon executive pressure participant whether growth chair run bad husband.', 6),
(2, '48441958-8', 'Prove share toward box my base standard tree process network sometimes.', 10),
(3, '27165056-4', 'Land center security threat name positive ten nature ok single education.', 6),
(4, '27165056-4', 'Later effect institution game capital bed even kid Mr several standard either hour.', 7),
(5, '79223224-5', 'Serve never question two wear factor central management people police book.', 9),
(6, '40665589-2', 'Power hair yet political capital simple before he describe model.', 9),
(7, '67622506-1', 'Another affect position always bar seek part low think.', 5),
(8, '48441958-8', 'Clear everybody machine pressure author its military never site.', 7),
(9, '48441958-8', 'Travel into half red born range tonight.', 8),
(10, '03285469-9', 'Growth sea agree score every onto agreement ok.', 5),
(11, '06963999-0', 'Speech yourself they since continue use I he.', 2),
(12, '40175100-9', 'Professional foot college vote pick ground.', 3),
(13, '72334850-7', 'Power on worker oil travel officer together popular somebody also beat size.', 7),
(14, '40665589-2', 'Affect agreement stage team nation agree accept receive.', 10),
(15, '67622506-1', 'Point government pull tax music the answer risk.', 6),
(16, '02233768-1', 'Impact suggest smile thought over maybe political spend.', 10),
(17, '13250832-5', 'Seat interest last cause artist police detail out language push within something both.', 6),
(18, '40175100-9', 'Available painting skill do entire music population.', 1),
(19, '71655145-3', 'During again environmental mother already data project cup this itself serious.', 7),
(20, '06963999-0', 'Bill fish network option south society along detail.', 1),
(21, '49861967-3', 'Quite but step room lead movement something condition.', 6),
(22, '06963999-0', 'Possible interesting game positive rate million next.', 5),
(23, '71655145-3', 'Modern attack record whom star cover.', 8),
(24, '03285469-9', 'Ability personal who line expert man prepare.', 4),
(25, '13250832-5', 'Blue move some woman entire prove attention trade attorney.', 5),
(26, '36313732-3', 'Them see adult enough address probably down choose successful piece.', 6),
(27, '79223224-5', 'Race remain response course stay worry during year return ask economic help.', 10),
(28, '03285469-9', 'Home fear easy practice food where let.', 2),
(29, '02233768-1', 'Particular get site energy across painting help away team.', 1),
(30, '71655145-3', 'Agency participant hot human real environmental movie about live cold close which base.', 3),
(31, '40665589-2', 'Ball we store down building throw turn style else floor produce form.', 2),
(32, '03285469-9', 'Through attorney this rest board feel produce.', 5),
(33, '40175100-9', 'Officer many back whole clear yard main lead.', 4),
(34, '48441958-8', 'Suffer dream process create detail for image these foot.', 4),
(35, '72334850-7', 'Decision size goal beat plant agency want street among.', 4),
(36, '28232352-5', 'Account performance practice away music rule evidence serve.', 3),
(37, '79223224-5', 'Hand human evidence wall avoid state actually home TV whatever.', 3),
(38, '28232352-5', 'Always role foreign establish everyone church daughter pretty send agreement knowledge fast.', 3),
(39, '40665589-2', 'Security miss happen couple idea charge whom still per cover strategy show.', 8),
(40, '19376305-7', 'Region wife leave treatment call kid natural establish relationship.', 4),
(41, '19376305-7', 'Without begin radio management cover drive.', 6),
(42, '49861967-3', 'Forget speech hour name ready pressure family the live drug three.', 8),
(43, '40175100-9', 'Technology trip let level find memory above reach record specific.', 1),
(44, '71655145-3', 'Walk three church but cold throw us.', 6),
(45, '44795984-7', 'Need stock company foot lead cold hour easy effort experience pay.', 3),
(46, '71655145-3', 'Work difficult area direction yeah land everybody difference.', 8),
(47, '72334850-7', 'Wind society many wall culture federal whole himself point near listen herself them.', 3),
(48, '49861967-3', 'Son media available story either a real candidate win control page science red.', 2),
(49, '40665589-2', 'Federal low series friend key quality.', 3),
(50, '03285469-9', 'Behind accept study series task price person debate.', 9);

-- Inserts para la tabla Revisor_Topico
INSERT INTO Revisor_Topico (rut_revisor, id_topico) VALUES
('79223224-5', 4),
('36313732-3', 3),
('36313732-3', 1),
('06963999-0', 10),
('44795984-7', 9),
('40175100-9', 6),
('40175100-9', 9),
('40175100-9', 10),
('72334850-7', 7),
('72334850-7', 1),
('40665589-2', 8),
('71655145-3', 2),
('71655145-3', 9),
('71655145-3', 7),
('28232352-5', 9),
('49861967-3', 8),
('02233768-1', 2),
('48441958-8', 3),
('48441958-8', 9),
('03285469-9', 7),
('03285469-9', 10),
('67622506-1', 5),
('27165056-4', 7),
('19376305-7', 10),
('13250832-5', 7),
('13250832-5', 1);

-- Inserts para la tabla Autor_Articulo
INSERT INTO Autor_Articulo (id_articulo, rut_autor, es_contacto) VALUES
(1, '37511665-0', 1),
(1, '01465478-3', 0),
(2, '04234251-7', 1),
(2, '63588311-0', 0),
(3, '41497157-5', 1),
(4, '17485946-1', 1),
(4, '88302316-0', 0),
(4, '14439053-9', 0),
(5, '22922502-6', 1),
(5, '66205590-0', 0),
(6, '63289117-5', 1),
(6, '62893146-2', 0),
(6, '22922502-6', 0),
(7, '88316329-3', 1),
(8, '40890323-1', 1),
(8, '41497157-5', 0),
(9, '31823126-9', 1),
(9, '66205590-0', 0),
(9, '01465478-3', 0),
(10, '50030813-0', 1),
(10, '91275497-3', 0),
(10, '04868801-8', 0),
(11, '16735564-8', 1),
(12, '77042807-4', 1),
(12, '63588311-0', 0),
(13, '68075295-4', 1),
(13, '88302316-0', 0),
(14, '04868801-8', 1),
(14, '88316329-3', 0),
(15, '63289117-5', 1),
(15, '41497157-5', 0),
(16, '66440438-0', 1),
(16, '40890323-1', 0),
(17, '88316329-3', 1),
(17, '68773957-5', 0),
(18, '63289117-5', 1),
(19, '17485946-1', 1),
(19, '14439053-9', 0),
(19, '41497157-5', 0),
(20, '62893146-2', 1),
(20, '41497157-5', 0),
(20, '04234251-7', 0),
(21, '91275497-3', 1),
(21, '29599985-7', 0),
(22, '01465478-3', 1),
(22, '17485946-1', 0),
(22, '88316329-3', 0),
(23, '77042807-4', 1),
(23, '40890323-1', 0),
(24, '41497157-5', 1),
(24, '04868801-8', 0),
(24, '04234251-7', 0),
(25, '88302316-0', 1),
(26, '40804729-3', 1),
(27, '29599985-7', 1),
(28, '62893146-2', 1),
(28, '63588311-0', 0),
(29, '04234251-7', 1),
(30, '91275497-3', 1),
(30, '04234251-7', 0),
(30, '22922502-6', 0),
(31, '40804729-3', 1),
(31, '47433732-6', 0),
(32, '68773957-5', 1),
(33, '63588311-0', 1),
(34, '68773957-5', 1),
(34, '66205590-0', 0),
(34, '91275497-3', 0),
(35, '63588311-0', 1),
(36, '40804729-3', 1),
(36, '37511665-0', 0),
(37, '47433732-6', 1),
(37, '16735564-8', 0),
(37, '40804729-3', 0),
(38, '50030813-0', 1),
(39, '91275497-3', 1),
(39, '88316329-3', 0),
(40, '41497157-5', 1),
(40, '31823126-9', 0),
(41, '37511665-0', 1),
(42, '22922502-6', 1),
(43, '66205590-0', 1),
(43, '68075295-4', 0),
(44, '77645167-2', 1),
(44, '66205590-0', 0),
(45, '66440438-0', 1),
(45, '14439053-9', 0),
(46, '63289117-5', 1),
(46, '40890323-1', 0),
(46, '31823126-9', 0),
(47, '66440438-0', 1),
(47, '88316329-3', 0),
(48, '17485946-1', 1),
(48, '48633378-2', 0),
(48, '40890323-1', 0),
(49, '66205590-0', 1),
(50, '17485946-1', 1);

-- Inserts para la tabla Articulo_Revisor
INSERT INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES
(1, '19376305-7'),
(1, '06963999-0'),
(2, '71655145-3'),
(3, '02233768-1'),
(4, '40175100-9'),
(5, '02233768-1'),
(5, '71655145-3'),
(6, '03285469-9'),
(6, '06963999-0'),
(7, '40175100-9'),
(8, '06963999-0'),
(8, '19376305-7'),
(9, '36313732-3'),
(10, '49861967-3'),
(11, '71655145-3'),
(11, '02233768-1'),
(12, '49861967-3'),
(13, '79223224-5'),
(14, '36313732-3'),
(15, '67622506-1'),
(16, '44795984-7'),
(16, '71655145-3'),
(17, '36313732-3'),
(17, '48441958-8'),
(18, '67622506-1'),
(19, '72334850-7'),
(20, '13250832-5'),
(20, '72334850-7'),
(21, '40175100-9'),
(22, '40175100-9'),
(23, '48441958-8'),
(23, '36313732-3'),
(24, '02233768-1'),
(25, '67622506-1'),
(26, '13250832-5'),
(26, '03285469-9'),
(27, '71655145-3'),
(27, '02233768-1'),
(28, '19376305-7'),
(28, '03285469-9'),
(29, '72334850-7'),
(30, '40175100-9'),
(30, '48441958-8'),
(31, '48441958-8'),
(31, '36313732-3'),
(32, '67622506-1'),
(33, '67622506-1'),
(34, '72334850-7'),
(35, '49861967-3'),
(35, '40665589-2'),
(36, '67622506-1'),
(37, '49861967-3'),
(37, '40665589-2'),
(38, '44795984-7'),
(39, '49861967-3'),
(40, '40175100-9'),
(41, '13250832-5'),
(41, '03285469-9'),
(42, '67622506-1'),
(43, '13250832-5'),
(43, '36313732-3'),
(44, '67622506-1'),
(45, '49861967-3'),
(45, '40665589-2'),
(46, '03285469-9'),
(46, '06963999-0'),
(47, '44795984-7'),
(47, '48441958-8'),
(48, '49861967-3'),
(48, '40665589-2'),
(49, '40175100-9'),
(49, '06963999-0'),
(50, '67622506-1');
