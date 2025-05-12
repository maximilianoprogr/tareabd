-- Este archivo contiene datos generados automáticamente para pruebas
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
        '51335872-9',
        'Logan Adams',
        'logan.adams@ross-jordan.com',
        'logan39',
        '5)*4g!EhjuLJ',
        'Autor'
    ),
    (
        '24125634-6',
        'Brian Montes',
        'brian.montes@daugherty.com',
        'brian22',
        '5dBJkFxf$3Bh',
        'Autor'
    ),
    (
        '51494089-4',
        'Emily Mullins',
        'emily.mullins@george-jenkins.com',
        'emily76',
        'oY8Juw1v@)SX',
        'Revisor'
    ),
    (
        '58698825-3',
        'Emily Patterson',
        'emily.patterson@griffin-daniel.com',
        'emily78',
        'Ylhu8Xaw3F#J',
        'Revisor'
    ),
    (
        '58840133-7',
        'Ronnie Best',
        'ronnie.best@gray.info',
        'ronnie80',
        'N&p57A3n&(#^',
        'Jefe Comite de Programa'
    ),
    (
        '58683935-2',
        'Patricia Haney',
        'patricia.haney@sloan.biz',
        'patricia13',
        '(1K7utaEqn*W',
        'Jefe Comite de Programa'
    ),
    (
        '43277936-6',
        'Melissa Potter',
        'melissa.potter@hall-rosales.com',
        'melissa39',
        'DS4MCXkv#5k1',
        'Revisor'
    ),
    (
        '69573336-2',
        'Connor Alvarez',
        'connor.alvarez@young-johnson.com',
        'connor61',
        '*oI&BzyL6$Tf',
        'Jefe Comite de Programa'
    ),
    (
        '92246443-9',
        'David Davis',
        'david.davis@miller.org',
        'david47',
        '%TdHF*Iq1xC4',
        'Jefe Comite de Programa'
    ),
    (
        '51390638-6',
        'Michael Mcfarland',
        'michael.mcfarland@herring.com',
        'michael12',
        '8_2p$2Tf#_E)',
        'Autor'
    );

-- Inserts para la tabla Autor
INSERT INTO
    Autor (rut)
VALUES ('51335872-9'),
    ('24125634-6'),
    ('51390638-6');

-- Inserts para la tabla Revisor
INSERT INTO
    Revisor (rut)
VALUES ('51494089-4'),
    ('58698825-3'),
    ('43277936-6');

-- Inserts para la tabla Topico
INSERT INTO
    Topico (nombre)
VALUES ('Machine Learning'),
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
INSERT INTO
    Articulo (
        titulo,
        resumen,
        fecha_envio,
        rut_autor,
        estado
    )
VALUES (
        'Throw meet election.',
        'Official difference race science family sea tonight. Above and simply development expert sit.',
        '2025-03-25',
        '51335872-9',
        'Rechazado'
    ),
    (
        'Six serve unit case.',
        'Executive able then food garden. Theory especially person.',
        '2024-11-17',
        '51390638-6',
        'Aprobado'
    ),
    (
        'Professor study at catch.',
        'Heart city though car share. Weight evening these radio leave.',
        '2024-07-01',
        '51390638-6',
        'En revisión'
    ),
    (
        'Personal increase social.',
        'Room full again vote tend edge. Research help free. Big plan build machine arrive finish sense.',
        '2025-03-17',
        '51335872-9',
        'Aprobado'
    ),
    (
        'Direction born dinner south policy.',
        'Notice exactly movement once begin. Try point subject reason agree.',
        '2024-08-02',
        '51335872-9',
        'Aprobado'
    ),
    (
        'Whatever likely guy hospital team.',
        'Politics offer she. Class option suggest area with care.',
        '2024-07-06',
        '24125634-6',
        'Aprobado'
    ),
    (
        'Place another agree choose.',
        'Start war draw debate listen wind. Decision when will how.',
        '2024-10-22',
        '51335872-9',
        'Aprobado'
    ),
    (
        'Blood local star music.',
        'Something consider others head writer if. Teach western how job.',
        '2025-01-30',
        '24125634-6',
        'Aprobado'
    ),
    (
        'Become condition he.',
        'Society left itself fire new consider material. Sea people into forward short official.',
        '2024-06-21',
        '51390638-6',
        'En revisión'
    ),
    (
        'Claim accept minute.',
        'Nation suggest person him surface. Pass other stop at note effort you.',
        '2024-12-30',
        '51335872-9',
        'En revisión'
    ),
    (
        'Door and week.',
        'Sing later letter law water. Strategy deep close relationship sport.',
        '2025-03-19',
        '51390638-6',
        'Aprobado'
    ),
    (
        'Method beat culture.',
        'Offer figure light go. High wonder thousand article.',
        '2025-01-30',
        '51335872-9',
        'Rechazado'
    ),
    (
        'Brother up thus themselves.',
        'Work to none bed. Tell since example season.',
        '2024-12-27',
        '24125634-6',
        'En revisión'
    ),
    (
        'Sport term people behavior artist.',
        'Source Mr write view stand happy project.',
        '2025-03-30',
        '51335872-9',
        'Aprobado'
    ),
    (
        'Candidate hour speech.',
        'Another chance hope statement.',
        '2024-12-31',
        '24125634-6',
        'Rechazado'
    ),
    (
        'Drug century.',
        'Result character garden network voice. Recognize girl any nor or drug friend.',
        '2024-11-16',
        '51335872-9',
        'Rechazado'
    ),
    (
        'Animal include follow call.',
        'Floor although debate when. Use them sure data where. Much middle time sound gun.',
        '2025-01-08',
        '51390638-6',
        'Aprobado'
    ),
    (
        'Idea indicate relationship interview.',
        'Pressure likely style minute. Leader cold remain pick section.',
        '2024-11-04',
        '24125634-6',
        'Aprobado'
    ),
    (
        'Seek address.',
        'Rate make rate about personal travel. Service risk order stand Democrat.',
        '2025-02-06',
        '51335872-9',
        'Rechazado'
    ),
    (
        'Attack natural old.',
        'Parent institution laugh wind television pass.',
        '2025-03-10',
        '51335872-9',
        'En revisión'
    ),
    (
        'Audience firm build.',
        'Bad throw size. Method particularly rest.',
        '2024-05-16',
        '51335872-9',
        'En revisión'
    ),
    (
        'Pull item low admit.',
        'Bit recently safe court. Black field boy late.',
        '2024-06-13',
        '51390638-6',
        'Aprobado'
    ),
    (
        'Any head address.',
        'Trouble at war perhaps four mother bring. Century prevent really note just.',
        '2024-11-03',
        '51335872-9',
        'Rechazado'
    ),
    (
        'Citizen cost change factor.',
        'Action hour PM spring result a car. A me experience ten entire stay look.',
        '2024-06-20',
        '24125634-6',
        'Aprobado'
    ),
    (
        'Address card strategy hold.',
        'Hot life threat pattern notice. Against sing another very act consider senior.',
        '2024-09-22',
        '51335872-9',
        'Aprobado'
    ),
    (
        'Out actually arrive.',
        'Fire administration heart room forward wonder. Citizen which position alone. Human ten for evening.',
        '2024-10-07',
        '51335872-9',
        'Aprobado'
    ),
    (
        'Great television.',
        'Above beyond of glass allow brother cost. Large other safe oil.',
        '2025-02-01',
        '24125634-6',
        'En revisión'
    ),
    (
        'Magazine minute.',
        'Green degree successful. Effect thousand manager.',
        '2025-02-20',
        '24125634-6',
        'Aprobado'
    ),
    (
        'Significant say nearly hard.',
        'True character its want may. Old put still television discuss reflect.',
        '2024-11-13',
        '51390638-6',
        'Aprobado'
    ),
    (
        'Cell process figure our understand.',
        'There outside accept little or affect. Entire character herself indicate image end.',
        '2025-03-15',
        '24125634-6',
        'En revisión'
    ),
    (
        'Record evidence site.',
        'Air do without fund what both. Reason whether sister understand support radio source.',
        '2025-01-02',
        '51390638-6',
        'Rechazado'
    ),
    (
        'Base physical similar wish.',
        'Tonight instead always citizen. Would account author we life magazine. Role its for bar TV piece.',
        '2024-09-02',
        '51390638-6',
        'Aprobado'
    ),
    (
        'Entire same food example.',
        'Force over tell arm final grow military. Support tax reason way instead.',
        '2025-03-11',
        '51390638-6',
        'Aprobado'
    ),
    (
        'Poor case.',
        'Opportunity phone expect tonight old reason. Mention put yard part sure.',
        '2025-02-24',
        '51335872-9',
        'En revisión'
    ),
    (
        'Social politics success serve.',
        'Our reveal news. Moment cell same cause go.',
        '2025-04-01',
        '24125634-6',
        'Aprobado'
    ),
    (
        'Hour maybe.',
        'Instead role site enough scene. Finally population ok suddenly project free.',
        '2025-02-07',
        '24125634-6',
        'En revisión'
    ),
    (
        'Above study kid beautiful.',
        'Protect table draw. From involve total opportunity item. Rise since probably hard forget.',
        '2024-05-17',
        '24125634-6',
        'Aprobado'
    ),
    (
        'Situation reach show.',
        'Role skill they agreement. Low detail region provide trial rock.',
        '2024-06-28',
        '51390638-6',
        'Rechazado'
    ),
    (
        'Claim suffer loss.',
        'Thousand result doctor inside wear office once. Team attack account power result lay.',
        '2024-09-11',
        '51335872-9',
        'En revisión'
    ),
    (
        'Language scientist.',
        'Attention check work try business. Work radio table act add identify partner.',
        '2024-09-12',
        '51390638-6',
        'Rechazado'
    ),
    (
        'Almost heart tonight.',
        'Election firm read strategy away thus. Public population age. Understand must effort.',
        '2024-12-07',
        '51335872-9',
        'Aprobado'
    ),
    (
        'Under analysis eat action.',
        'Ask citizen sound talk we health training. Everyone economy almost sister recent born.',
        '2024-10-24',
        '51390638-6',
        'En revisión'
    ),
    (
        'Usually form.',
        'Value quality hair able possible. Main attention which read inside most west main.',
        '2025-01-28',
        '51335872-9',
        'En revisión'
    ),
    (
        'Speech say population.',
        'Choose onto continue surface learn. Whether defense tonight when smile how statement.',
        '2025-04-21',
        '51335872-9',
        'Rechazado'
    ),
    (
        'Data car.',
        'Audience want account along maintain maybe. Water three reach president water.',
        '2025-04-08',
        '51335872-9',
        'Aprobado'
    ),
    (
        'Home begin heart.',
        'Writer in could sit win law. Impact ahead system often.',
        '2024-10-12',
        '51335872-9',
        'Aprobado'
    ),
    (
        'Show money factor one.',
        'This follow recently watch focus condition.',
        '2024-11-13',
        '24125634-6',
        'Rechazado'
    ),
    (
        'Cause during fast relationship realize.',
        'Around opportunity leg pretty husband game need player.',
        '2025-04-21',
        '51335872-9',
        'Rechazado'
    ),
    (
        'Morning increase.',
        'Car sport teacher. Note ok idea power. Daughter gun maybe cover American popular.',
        '2025-03-21',
        '51390638-6',
        'Rechazado'
    ),
    (
        'Build color tough.',
        'Wonder yard magazine city long. Participant next type.',
        '2025-01-21',
        '51390638-6',
        'Aprobado'
    );

-- Inserts para la tabla Articulo_Topico
INSERT INTO
    Articulo_Topico (id_articulo, id_topico)
VALUES (1, 9),
    (2, 9),
    (3, 9),
    (4, 6),
    (5, 2),
    (6, 10),
    (7, 4),
    (8, 4),
    (9, 8),
    (10, 5),
    (11, 9),
    (12, 3),
    (13, 7),
    (14, 3),
    (15, 8),
    (16, 10),
    (17, 3),
    (18, 9),
    (19, 9),
    (20, 7),
    (21, 4),
    (22, 4),
    (23, 9),
    (24, 2),
    (25, 7),
    (26, 7),
    (27, 8),
    (28, 6),
    (29, 2),
    (30, 6),
    (31, 7),
    (32, 3),
    (33, 8),
    (34, 5),
    (35, 10),
    (36, 7),
    (37, 5),
    (38, 7),
    (39, 4),
    (40, 5),
    (41, 6),
    (42, 8),
    (43, 1),
    (44, 1),
    (45, 1),
    (46, 1),
    (47, 6),
    (48, 5),
    (49, 2),
    (50, 10);

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
        '51494089-4',
        'List serve decision many speak series get bit north push exactly money white.',
        6
    ),
    (
        2,
        '43277936-6',
        'Full hope thousand part single recent sea miss.',
        10
    ),
    (
        3,
        '58698825-3',
        'Realize push drug maintain able owner interest agent.',
        6
    ),
    (
        4,
        '43277936-6',
        'Significant quickly serious quality condition now either single such.',
        7
    ),
    (
        5,
        '43277936-6',
        'Hot could us price professional skin collection indeed crime career.',
        2
    ),
    (
        6,
        '51494089-4',
        'Three bed property seat minute politics structure.',
        8
    ),
    (
        7,
        '43277936-6',
        'Above financial story important new build stay car race car instead hand.',
        7
    ),
    (
        8,
        '51494089-4',
        'Enter several compare type sometimes name structure later go college.',
        5
    ),
    (
        9,
        '51494089-4',
        'Debate always small direction mission next rule year.',
        4
    ),
    (
        10,
        '58698825-3',
        'Could ground bill forward truth wear effect economic cell bank record.',
        9
    ),
    (
        11,
        '58698825-3',
        'Program what movie under none thus research certain.',
        3
    ),
    (
        12,
        '51494089-4',
        'Natural difficult according play American be quality tonight.',
        2
    ),
    (
        13,
        '43277936-6',
        'Red professor everyone common successful Republican statement.',
        5
    ),
    (
        14,
        '58698825-3',
        'Laugh tough address option guy significant support forward trip.',
        6
    ),
    (
        15,
        '51494089-4',
        'Event short structure include because other cost avoid record.',
        1
    ),
    (
        16,
        '43277936-6',
        'Usually development usually understand sort hot less.',
        9
    ),
    (
        17,
        '51494089-4',
        'Carry despite up agent course near.',
        2
    ),
    (
        18,
        '58698825-3',
        'Magazine again father just picture relate smile table part each.',
        5
    ),
    (
        19,
        '43277936-6',
        'Here fund could note forget sort bill.',
        6
    ),
    (
        20,
        '43277936-6',
        'Control hundred full it keep full suggest point life Mrs notice reflect threat.',
        6
    ),
    (
        21,
        '51494089-4',
        'Remain news way she within number last theory believe pretty try crime network.',
        1
    ),
    (
        22,
        '43277936-6',
        'Old receive song team from man open nearly.',
        4
    ),
    (
        23,
        '58698825-3',
        'Walk doctor million happy free body my service challenge.',
        9
    ),
    (
        24,
        '58698825-3',
        'Power record name set enough agency official enjoy seven everything cause city with.',
        1
    ),
    (
        25,
        '58698825-3',
        'Population second generation visit understand heavy war off here hand back.',
        7
    ),
    (
        26,
        '58698825-3',
        'Sit resource at wind most scientist president experience need.',
        6
    ),
    (
        27,
        '58698825-3',
        'Research risk member character him against light fire entire sign capital.',
        8
    ),
    (
        28,
        '43277936-6',
        'Official study machine option idea box.',
        6
    ),
    (
        29,
        '51494089-4',
        'Either change still science traditional space exactly sign.',
        2
    ),
    (
        30,
        '58698825-3',
        'Around skill practice movie race wish more lawyer.',
        3
    ),
    (
        31,
        '43277936-6',
        'They bring walk central discover everybody state.',
        10
    ),
    (
        32,
        '43277936-6',
        'Board say course that newspaper wind.',
        9
    ),
    (
        33,
        '51494089-4',
        'Card short clear wrong political store throw everyone your risk.',
        6
    ),
    (
        34,
        '58698825-3',
        'Card listen alone important memory popular measure person if.',
        10
    ),
    (
        35,
        '51494089-4',
        'Movie these environmental almost range herself relationship nearly hospital field election anyone.',
        3
    ),
    (
        36,
        '51494089-4',
        'Away record main wait very seem either.',
        3
    ),
    (
        37,
        '51494089-4',
        'Front the soon key stock respond public expert character mouth.',
        3
    ),
    (
        38,
        '58698825-3',
        'Affect tough find standard every list role age.',
        4
    ),
    (
        39,
        '58698825-3',
        'Court carry soldier benefit perform data election against.',
        9
    ),
    (
        40,
        '51494089-4',
        'Player eight minute range north trade beat sea oil resource.',
        10
    ),
    (
        41,
        '43277936-6',
        'Hit must audience space feel money.',
        1
    ),
    (
        42,
        '51494089-4',
        'While million go draw step industry policy.',
        1
    ),
    (
        43,
        '43277936-6',
        'Suggest fall read a still however nor behind successful easy.',
        10
    ),
    (
        44,
        '43277936-6',
        'Create could low three political build test.',
        3
    ),
    (
        45,
        '51494089-4',
        'Reveal amount age perhaps as toward others add physical understand evening.',
        3
    ),
    (
        46,
        '43277936-6',
        'Beat everyone direction no politics edge.',
        2
    ),
    (
        47,
        '51494089-4',
        'Western hold decision scientist condition effort single opportunity player test share.',
        3
    ),
    (
        48,
        '51494089-4',
        'Their if church garden weight simple eight piece center.',
        5
    ),
    (
        49,
        '51494089-4',
        'Day deep property attention girl bill hotel each follow easy.',
        5
    ),
    (
        50,
        '51494089-4',
        'Argue learn point grow fund college while performance owner space fight add society.',
        2
    );

-- Inserts para la tabla Revisor_Topico
INSERT INTO
    Revisor_Topico (rut_revisor, id_topico)
VALUES ('51494089-4', 1),
    ('58698825-3', 10),
    ('43277936-6', 2);