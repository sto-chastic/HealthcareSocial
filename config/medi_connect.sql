SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "-5:00";
SET GLOBAL event_scheduler = ON;
SET CHARACTER SET utf8;


CREATE TABLE `debug` (
  `id` int(11) NOT NULL,
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

CREATE TABLE `profile_register` (
  `verbal_rich_link` varchar(255),
  `custom_link` varchar(255),
  `username` varchar(100) NOT NULL PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

CREATE TABLE `temp_imgs` (
  `username` varchar(100) NOT NULL PRIMARY KEY,
  `profile_pic` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

CREATE TABLE `scheduler` (
  `id` varchar(14) NOT NULL,
  `type` int(3) NOT NULL,
  `execution_time`datetime NOT NULL,
  `query` text NOT NULL,
  `query2` text,
  `query3` text,
  `query4` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;


CREATE TABLE `users` (
  `first_name` varchar(25) NOT NULL,
  `last_name` varchar(25) NOT NULL,
  `username` varchar(100) NOT NULL PRIMARY KEY,
  `email` varchar(100) NOT NULL,
  `password` varchar(60) NOT NULL,
  `signup_date` datetime NOT NULL,
  `country`varchar(3) NOT NULL,
  `profile_pic` varchar(255) NOT NULL,
  `num_posts` int(11) NOT NULL,
  `num_likes` int(11) NOT NULL,
  `user_closed` varchar(3) NOT NULL,
  `first_name_d` varchar(25) NOT NULL,
  `last_name_d` varchar(25) NOT NULL,
  `user_type` int(1) NOT NULL,
  `messages_token` varchar(32),
  `confirmed_email` bit(1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

/*ALTER TABLE users
  ADD COLUMN last_name_d VARCHAR(25) AS (LOWER(last_name)) PERSISTENT,
  ADD COLUMN first_name_d VARCHAR(25) AS (LOWER(first_name)) PERSISTENT;*/

ALTER TABLE `users`
  ADD KEY `users_lastn_firstn` (`last_name`,`first_name`),
  ADD KEY `users_lastn` (`last_name`),
  ADD KEY `users_firstn` (`first_name`);
  
ALTER TABLE `users`
    ADD KEY `users_lastnd_firstnd` (`last_name_d`,`first_name_d`), 
    ADD KEY `users_lastnd` (`last_name_d`), 
    ADD KEY `users_firstnd` (`first_name_d`);

CREATE TABLE users_health_tables (
        username varchar(100) NOT NULL PRIMARY KEY,
        
        weight varchar(60),
        height varchar(60),
        bmi varchar(60),
        blood_pressure varchar(60),
        blood_sugar varchar(60),
        habits varchar(60),
        OBGYN varchar(60),
        
        pathologies varchar(60),
        surgical_trauma varchar(60),
        hereditary varchar(60),
        pharmacology varchar(60),
        allergies varchar(60)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

CREATE TABLE basic_info_patients (
	username VARCHAR(100) NOT NULL PRIMARY KEY,
    `sex` varchar(1) NOT NULL,
    `blood_type` int(1) NOT NULL,
    `phone_numbers` varchar(18) DEFAULT NULL,
    `birthdate` date NOT NULL,
    `birthplace` varchar(40) DEFAULT NULL,
    `current_residence` varchar(40) DEFAULT NULL,
    `marital_status` varchar(1) DEFAULT NULL,
    `children` int(2) DEFAULT NULL,
    `education_level` varchar(1) DEFAULT NULL,
    `occupation` varchar(100) DEFAULT NULL,
    `religion` varchar(100) DEFAULT NULL,
    `languages` varchar(100) DEFAULT NULL,
    `insurance` varchar(100) NOT NULL,
    `laterality` varchar(1) DEFAULT NULL,
    `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

CREATE TABLE schools (
	id int(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	school VARCHAR(50) /*references public table*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

CREATE TABLE degree (
	id int(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	degree VARCHAR(50) /*references public table*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

CREATE TABLE certifications (
	id int(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	certification VARCHAR(50) /*references public table*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

CREATE TABLE cert_issuer (
	id int(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	issuer VARCHAR(50) /*references public table*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

CREATE TABLE congresses (
	id int(100) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name varchar(255) NOT NULL,
	description text,
	start_date date,
	end_date date,
	profile_pic varchar(255) NOT NULL,
	location varchar(255) NOT NULL,
	contact_info varchar(255) NOT NULL,
	pricing text,
	host VARCHAR(100) NOT NULL,
	attending VARCHAR(100) NOT NULL/*references congress user table, created when a congress is created*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

CREATE TABLE appo_duration (/*appointment durations*/
	id int(1) NOT NULL,
	duration int(2) NOT NULL PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO appo_duration (id, duration) VALUES
	(1, 20),
	(2, 40),
	(3, 60)
;

CREATE TABLE webpages (/*appointment durations*/
    web_page_code int(1) NOT NULL PRIMARY KEY,
    name varchar(30) NOT NULL 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO webpages (web_page_code, name) VALUES
    (1, 'Personal'),
    (2, 'Facebook'),
    (3, 'LinkedIn'),
    (4, 'Instagram'),
    (5, 'Twitter'),
    (6, 'Snapchat'),
    (7, 'Skype')
;

CREATE TABLE basic_info_doctors (
    username VARCHAR(100) NOT NULL PRIMARY KEY,
    specializations varchar(400) NOT NULL /*--List of specializations by id*/,
    sex varchar(1) NOT NULL /*--sex*/,
    insurance_accepted_1 VARCHAR(400)/*--List of insurance by id at main address*/,
    insurance_accepted_2 VARCHAR(400)/*--List of insurance by id at secondary address*/,
    insurance_accepted_3 VARCHAR(400)/*--List of insurance by id at tertiary address*/,
    md_conn int(8)/*-1-Number of doctor connections*/,
    pat_conn int(9)/*-1- number of patient connections */,
    pat_seen int(5)/*-1-Number of patients that have had appointment*/,
    pat_foll int(9)/*--Number of patient followers, CURRENTLY NOT USED*/,
    pat_inter int(9)/*-1-Number of appointments*/,
    pat_rec int(9)/*-1-Number of returning patients*/,
    ad1nick varchar (20)/*--Nickname for main address*/,
    ad1ln1 varchar(50)/*--Address 1 line 1 (street)*/,
    ad1ln2 varchar(50)/*--Address 1 line 2 (office #)*/,
    ad1ln3 varchar(50)/*--Address 1 line 3 (building name)*/,
    ad1city varchar(50)/*--Address 1 city*/,
    ad1adm2 varchar(50)/*--Address 1 state*/,
    adcountry varchar(2)/*--Address country*/,
    ad1lat decimal(10,7)/*--Address 1 latitude*/,
    ad1lng decimal(10,7)/*--Address 1 longitude*/,
    ad2nick varchar (20)/*--Nickname for secondary address*/,
    ad2ln1 varchar(50)/*--Address 2 line 1 (street)*/,
    ad2ln2 varchar(50)/*--Address 2 line 2 (office #)*/,
    ad2ln3 varchar(50)/*--Address 2 line 3 (building name)*/,
    ad2city varchar(50)/*--Address 2 city*/,
    ad2adm2 varchar(50)/*--Address 2 state*/,
    ad2lat decimal(10,7)/*--Address 2 latitude*/,
    ad2lng decimal(10,7)/*--Address 2 longitude*/,
    ad3nick varchar (20)/*--Nickname for tertiary address*/,
    ad3ln1 varchar(50)/*--Address 3 line 1 (street)*/,
    ad3ln2 varchar(50)/*--Address 3 line 2 (office #)*/,
    ad3ln3 varchar(50)/*--Address 3 line 3 (building name)*/,
    ad3city varchar(50)/*--Address 3 city*/,
    ad3adm2 varchar(50)/*--Address 3 state*/,
    ad3lat decimal(10,7)/*--Address 3 latitude*/,
    ad3lng decimal(10,7)/*--Address 3 longitude*/,
    payment_expiration_date date,
    up_to_date bit(1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;


-- Create the calendar table and populate it -----------------------------
CREATE TABLE calendar_table (
	dt DATE NOT NULL PRIMARY KEY,
	y SMALLINT NULL,
	q tinyint NULL,
	m tinyint NULL,
	d tinyint NULL,
	dw tinyint NULL,
	monthName VARCHAR(9) NULL,
	dayName VARCHAR(9) NULL,
	w tinyint NULL,
	isWeekday BINARY(1) NULL,
	isHoliday BINARY(1) NULL,
	holidayDescr VARCHAR(32) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

CREATE TABLE _aux_ints ( i tinyint );
 
INSERT INTO _aux_ints VALUES (0),(1),(2),(3),(4),(5),(6),(7),(8),(9);
 
INSERT INTO calendar_table (dt)
SELECT DATE('2018-05-01') + INTERVAL a.i*10000 + b.i*1000 + c.i*100 + d.i*10 + e.i DAY
FROM _aux_ints a JOIN _aux_ints b JOIN _aux_ints c JOIN _aux_ints d JOIN _aux_ints e
WHERE (a.i*10000 + b.i*1000 + c.i*100 + d.i*10 + e.i) <= 11322 /*approx 30 years, can be calculated SELECT datediff('2040-12-31','2010-01-01');*/
ORDER BY 1;

UPDATE calendar_table
SET isWeekday = CASE WHEN dayofweek(dt) IN (1,7) THEN 0 ELSE 1 END,
	isHoliday = 0,
	y = YEAR(dt),
	q = quarter(dt),
	m = MONTH(dt),
	d = dayofmonth(dt),
	dw = dayofweek(dt),
	monthname = monthname(dt),
	dayname = dayname(dt),
	w = week(dt),
	holidayDescr = '';

-- Set New years and handle when it occurs on a weekend

UPDATE calendar_table SET isHoliday = 1, holidayDescr = 'New Year''s Day' WHERE m = 1 AND d = 1;
 
UPDATE calendar_table c1
LEFT JOIN calendar_table c2 ON c2.dt = c1.dt + INTERVAL 1 DAY
SET c1.isHoliday = 1, c1.holidayDescr = 'Holiday for New Year''s Day'
WHERE c1.dw = 6 AND c2.m = 1 AND c2.dw = 7 AND c2.isHoliday = 1;
 
UPDATE calendar_table c1
LEFT JOIN calendar_table c2 ON c2.dt = c1.dt - INTERVAL 1 DAY
SET c1.isHoliday = 1, c1.holidayDescr = 'Holiday for New Year''s Day'
WHERE c1.dw = 2 AND c2.m = 1 AND c2.dw = 1 AND c2.isHoliday = 1;


-- Finish calendar set up -----------------------------

CREATE TABLE months (
	id int(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	months_eng VARCHAR(20),
	months_es VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO months VALUES ('1','January','Enero'),('2','February','Febrero'),('3','March','Marzo'),('4','April','Abril'),('5','May','Mayo'),('6','June','Junio'),('7','July','Julio'),('8','August','Agosto'),('9','September','Septiembre'),('10','October','Octubre'),('11','November','Noviembre'),('12','December','Diciembre');

CREATE TABLE sex (
    id varchar(1) PRIMARY KEY,
    en VARCHAR(10),
    es VARCHAR(10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO sex VALUES ('m','Male','Masculino'),('f','Female','Femenino');

CREATE TABLE marital_status (
    id varchar(1) PRIMARY KEY,
    en VARCHAR(10),
    es VARCHAR(10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO marital_status VALUES ('m','Married','Casado'),('w','Widowed','Viudo'),('s','Separated','Separado'),('d','Divorced','Divorciado'),('a','Single','Soltero');

CREATE TABLE education_level (
    id varchar(1) PRIMARY KEY,
    en VARCHAR(25),
    es VARCHAR(25)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO education_level VALUES ('e','Elementary School','Primaria'),('h','High School','Bachillerato'),('u','Undergraduate Studies','Pregrado'),('g','Graduate Studies','Posgrado');

CREATE TABLE `insurance_CO` (
  `id` varchar(5) COLLATE utf8_bin NOT NULL PRIMARY KEY,
  `en` varchar(25) COLLATE utf8_bin DEFAULT NULL,
  `es` varchar(25) COLLATE utf8_bin DEFAULT NULL,
  `search_en` varchar(25) COLLATE utf8_bin DEFAULT NULL,
  `search_es` varchar(25) COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;


INSERT INTO `insurance_CO` (`id`, `en`, `es`, `search_en`, `search_es`) VALUES
('CO00', 'Uninsured', 'Particular', '', ''),
('CO01', 'Aliansalud', 'Aliansalud', '', ''),
('CO02', 'Allianz', 'Allianz', '', ''),
('CO03', 'Cafesalud', 'Cafesalud', '', ''),
('CO04', 'Colmedica', 'Colmedica', '', ''),
('CO05', 'Colpatria', 'Colpatria', '', ''),
('CO06', 'Colsanitas', 'Colsanitas', '', ''),
('CO07', 'Comfenalco', 'Comfenalco', '', ''),
('CO08', 'Compensar', 'Compensar', '', ''),
('CO09', 'Coomeva', 'Coomeva', '', ''),
('CO10', 'Ecopetrol', 'Ecopetrol', '', ''),
('CO11', 'ETB', 'ETB', '', ''),
('CO12', 'Famisanar', 'Famisanar', '', ''),
('CO13', 'La Nueva EPS', 'La Nueva EPS', '', ''),
('CO14', 'Liberty', 'Liberty', '', ''),
('CO15', 'Mapfre', 'Mapfre', '', ''),
('CO16', 'Medisanitas', 'Medisanitas', '', ''),
('CO17', 'MetLife', 'MetLife', '', ''),
('CO18', 'Panamerican Life', 'Panamerican Life', '', ''),
('CO19', 'Salud Total', 'Salud Total', '', ''),
('CO20', 'Saludvida', 'Saludvida', '', ''),
('CO21', 'Sanitas', 'Sanitas', '', ''),
('CO22', 'Seguros Bolivar', 'Seguros Bolivar', '', ''),
('CO23', 'Sena', 'Sena', '', ''),
('CO24', 'Suramericana', 'Suramericana', '', ''),
('CO25', 'Susalud', 'Susalud', '', ''),
('CO26', 'Vivir', 'Vivir', '', '');

UPDATE `insurance_CO` SET `search_en` = LCASE(en), `search_es` = LCASE(es) WHERE 1;

CREATE TABLE laterality (
    id varchar(1) PRIMARY KEY,
    en VARCHAR(25),
    es VARCHAR(25)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO laterality VALUES ('r','Right','Derecha'),('l','Left','Izquierda');

CREATE TABLE blood_type (
    id varchar(1) PRIMARY KEY,
    en VARCHAR(25),
    es VARCHAR(25)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO blood_type VALUES ('1','A Positive','A Positivo'),('2','A Negative','A Negativo'),('3','B Positive','B Positivo'),('4','B Negative','B Negativo'),('5','AB Positive','AB Positivo'),('6','AB Negative','AB Negativo'),('7','O Positive','O Positivo'),('8','O Negative','O Negativo');


CREATE TABLE days_week (
	dw int(1) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	days_short_eng VARCHAR(3),
	days_short_es VARCHAR(3),
	days_eng VARCHAR(20),
	days_es VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO days_week VALUES ('1','Sun', 'Dom','Sunday','Domingo'), ('2','Mon', 'Lun','Monday','Lunes'),('3','Tue', 'Mar','Tuesday','Martes'),('4','Wed','Mie','Wednesday','Miércoles'),('5','Thu', 'Jue','Thursday','Jueves'),('6','Fri', 'Vie','Friday','Viernes'),('7','Sat', 'Sab','Saturday','Sábado');

CREATE TABLE `trends` (
  `id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `hits` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

CREATE TABLE payment_methods (
	codename VARCHAR(10) PRIMARY KEY,
	en VARCHAR(30),
	es VARCHAR(30)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO payment_methods VALUES ('part','Self-Paying','Pago Particular'), ('insu','Insurance Payment','Pago con Seguro Médico');


CREATE TABLE hereditary_diseases (
    id int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    en VARCHAR(30),
    es VARCHAR(30),
    percent DECIMAL(8,5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO hereditary_diseases VALUES ('1','cystic fibrosis','fibrosis quística',0),('2','down syndrome','síndrome de down',0),('3','fragile x syndrome','síndrome x frágil',0),('4','clotting problems','problemas de coagulación',0),('5','hyperlipidemia','hiperlipidemia',0),('6','hypercholsterolemia','hipercolesterolemia',0),('7','huntington''s disease','enfermedad de huntington',0),('8','muscular dystrophies','distrofia muscular',0),('9','sickle cell anemia','anemia de células falciformes',0),('10','thalassemias','talasemia',0),('11','cancer (any)','cáncer (cualquier tipo)',100.00000),('12','diabetes','diabetes',0),('13','cardiovascular disease','enfermedad cardiovascular',0),('14','dementia','demencia',0);


CREATE TABLE medicines (
    id int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    en VARCHAR(30),
    es VARCHAR(30),
    dosage VARCHAR(15),
    percent DECIMAL(8,5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO `medicines` (`id`, `en`, `es`, `dosage`, `percent`) VALUES (NULL, 'Yasmin', 'Yasmin', '3mg-0.03mg', '100');



CREATE TABLE symptoms_tables (
    `username` varchar(100) NOT NULL PRIMARY KEY,
    symptoms_table varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;


CREATE TABLE `specializations` (
  `id` int(3) NOT NULL PRIMARY KEY,
  `en` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `es` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `en_search` varchar(500) COLLATE utf8_bin NOT NULL,
  `es_search` varchar(500) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;


CREATE TABLE awards (
    id int(3) PRIMARY KEY,
    name_en VARCHAR(120),
    name_es VARCHAR(120),
    description_en VARCHAR(300),
    description_es VARCHAR(300)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO `awards` (`id`, `name_en`, `name_es`, `description_en`, `description_es`) VALUES
(1, 'Punctuality', 'Puntualidad', 'The doctor has high attention to punctuality. The consult was on schedule.', 'El doctor presta alta atención a la puntualidad. La consulta se desarrolló en el horario establecido.'),
(2, 'Kindness', 'Amabilidad', 'The doctor is very kind.', 'El doctor es muy amable.'),
(3, 'Fixer', 'Solucionador', 'The doctor identified and solved my problem.', 'El doctor identificó y solucionó mi problema.'),
(4, 'Grateful Patient', 'Paciente Agradecido', 'The patient is very pleased and thankful with this doctor.', 'El paciente está muy agradecido con este doctor.'),
(5, 'Nice Office', 'Agradable Consultorio', 'The doctor has a nice office.', 'El doctor tiene un consultorio agradable.'),
(6, 'Kind Staff', 'Empleados Amables', 'The doctor has a kind and helpful staff working with him.', 'El doctor tiene empleados amables y de gran ayuda trabajando con el.'),
(7, 'Questions and Answers Master', 'Maestro de Preguntas y Respuestas', 'The doctor answered my questions in ways I could understand.', 'El doctor respondió mis preguntas en formas que pude entender.'),
(8, 'Clarity', 'Claridad', 'The doctor is very clear in the way he talks and explains concepts.', 'El doctor es muy claro en la forma en que habla y explica conceptos.');

CREATE TABLE `cities_CO` (
	`city_code` varchar(5) NOT NULL PRIMARY KEY,
	`city` varchar(22), 
	`adm2` varchar(24), 
	`country` varchar(2), 
	`population` int(7),
    `city_search` varchar(22) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET= utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT;

INSERT INTO `cities_CO` (`city_code`, `city`, `adm2`, `country`, `population`) VALUES ('CO001', 'Bogotá, D.C.', 'Bogotá, D.C.', 'CO', 6778691),
 ('CO002', 'Medellín', 'Antioquia', 'CO', 2219861),
 ('CO003', 'Cali', 'Valle del Cauca', 'CO', 2075380),
 ('CO004', 'Barranquilla', 'Atlántico', 'CO', 1112889),
 ('CO005', 'Cartagena', 'Bolívar', 'CO', 895400),
 ('CO006', 'Cúcuta', 'Norte de Santander', 'CO', 585543),
 ('CO007', 'Bucaramanga', 'Santander', 'CO', 509918),
 ('CO008', 'Ibagué', 'Tolima', 'CO', 495246),
 ('CO009', 'Soledad', 'Atlántico', 'CO', 455796),
 ('CO010', 'Pereira', 'Risaralda', 'CO', 428397),
 ('CO011', 'Santa Marta', 'Magdalena', 'CO', 414387),
 ('CO012', 'Soacha', 'Cundinamarca', 'CO', 398295),
 ('CO013', 'Villavicencio', 'Meta', 'CO', 384131),
 ('CO014', 'Pasto', 'Nariño', 'CO', 383846),
 ('CO015', 'Montería', 'Córdoba', 'CO', 381284),
 ('CO016', 'Bello', 'Antioquia', 'CO', 373013),
 ('CO017', 'Manizales', 'Caldas', 'CO', 368433),
 ('CO018', 'Valledupar', 'Cesar', 'CO', 348990),
 ('CO019', 'Buenaventura', 'Valle del Cauca', 'CO', 324207),
 ('CO020', 'Neiva', 'Huila', 'CO', 315332),
 ('CO021', 'Palmira', 'Valle del Cauca', 'CO', 278358),
 ('CO022', 'Armenia', 'Quindío', 'CO', 272574),
 ('CO023', 'Popayán', 'Cauca', 'CO', 258653),
 ('CO024', 'Floridablanca', 'Santander', 'CO', 252472),
 ('CO025', 'Sincelejo', 'Sucre', 'CO', 236780),
 ('CO026', 'Itagui', 'Antioquia', 'CO', 235567),
 ('CO027', 'Barrancabermeja', 'Santander', 'CO', 187311),
 ('CO028', 'Tuluá', 'Valle del Cauca', 'CO', 183236),
 ('CO029', 'Envigado', 'Antioquia', 'CO', 175337),
 ('CO030', 'Dosquebradas', 'Risaralda', 'CO', 173452),
 ('CO031', 'Riohacha', 'La Guajira', 'CO', 169311),
 ('CO032', 'San Andres de Tumaco', 'Nariño', 'CO', 161490),
 ('CO033', 'Tunja', 'Boyacá', 'CO', 152419),
 ('CO034', 'Florencia', 'Caquetá', 'CO', 137896),
 ('CO035', 'Girón', 'Santander', 'CO', 135531),
 ('CO036', 'Apartadó', 'Antioquia', 'CO', 134572),
 ('CO037', 'Turbo', 'Antioquia', 'CO', 122780),
 ('CO038', 'Cartago', 'Valle del Cauca', 'CO', 121741),
 ('CO039', 'Magangué', 'Bolívar', 'CO', 121085),
 ('CO040', 'Piedecuesta', 'Santander', 'CO', 116914),
 ('CO041', 'Uribia', 'La Guajira', 'CO', 116674),
 ('CO042', 'Sogamoso', 'Boyacá', 'CO', 114486),
 ('CO043', 'Guadalajara de Buga', 'Valle del Cauca', 'CO', 111487),
 ('CO044', 'Lorica', 'Córdoba', 'CO', 109974),
 ('CO045', 'Ipiales', 'Nariño', 'CO', 109865),
 ('CO046', 'Quibdó', 'Chocó', 'CO', 109121),
 ('CO047', 'Fusagasugá', 'Cundinamarca', 'CO', 107259),
 ('CO048', 'Facatativá', 'Cundinamarca', 'CO', 106067),
 ('CO049', 'Duitama', 'Boyacá', 'CO', 105407),
 ('CO050', 'Yopal', 'Casanare', 'CO', 103754),
 ('CO051', 'Maicao', 'La Guajira', 'CO', 103124),
 ('CO052', 'Pitalito', 'Huila', 'CO', 102937),
 ('CO053', 'Rionegro', 'Antioquia', 'CO', 101046),
 ('CO054', 'Ciénaga', 'Magdalena', 'CO', 100908),
 ('CO055', 'Zipaquirá', 'Cundinamarca', 'CO', 100038),
 ('CO056', 'Malambo', 'Atlántico', 'CO', 99058),
 ('CO057', 'Chía', 'Cundinamarca', 'CO', 97444),
 ('CO058', 'Girardot', 'Cundinamarca', 'CO', 95496),
 ('CO059', 'Jamundí', 'Valle del Cauca', 'CO', 93556),
 ('CO060', 'Yumbo', 'Valle del Cauca', 'CO', 90642),
 ('CO061', 'Ocaña', 'Norte de Santander', 'CO', 90037),
 ('CO062', 'Sahagún', 'Córdoba', 'CO', 86189),
 ('CO063', 'Caucasia', 'Antioquia', 'CO', 85667),
 ('CO064', 'Sabanalarga', 'Atlántico', 'CO', 84410),
 ('CO065', 'Cereté', 'Córdoba', 'CO', 83978),
 ('CO066', 'Aguachica', 'Cesar', 'CO', 80789),
 ('CO067', 'Santander de Quilichao', 'Cauca', 'CO', 80653),
 ('CO068', 'Tierralta', 'Córdoba', 'CO', 78564),
 ('CO069', 'Espinal', 'Tolima', 'CO', 75375),
 ('CO070', 'Calarca', 'Quindío', 'CO', 71605),
 ('CO071', 'La Dorada', 'Caldas', 'CO', 70486),
 ('CO072', 'Garzón', 'Huila', 'CO', 70144),
 ('CO073', 'Villa del Rosario', 'Norte de Santander', 'CO', 69991),
 ('CO074', 'Montelíbano', 'Córdoba', 'CO', 69277),
 ('CO075', 'Candelaria', 'Valle del Cauca', 'CO', 68820),
 ('CO076', 'Manaure', 'La Guajira', 'CO', 68578),
 ('CO077', 'Arauca', 'Arauca', 'CO', 68222),
 ('CO078', 'Caldas', 'Antioquia', 'CO', 68157),
 ('CO079', 'Los Patios', 'Norte de Santander', 'CO', 67441),
 ('CO080', 'Santa Rosa de Cabal', 'Risaralda', 'CO', 67410),
 ('CO081', 'El Carmen de Bolívar', 'Bolívar', 'CO', 66001),
 ('CO082', 'Mosquera', 'Cundinamarca', 'CO', 63584),
 ('CO083', 'San Andrés Sotavento', 'Córdoba', 'CO', 63453),
 ('CO084', 'Turbaco', 'Bolívar', 'CO', 63450),
 ('CO085', 'Madrid', 'Cundinamarca', 'CO', 61599),
 ('CO086', 'Planeta Rica', 'Córdoba', 'CO', 61570),
 ('CO087', 'Copacabana', 'Antioquia', 'CO', 61421),
 ('CO088', 'Arjona', 'Bolívar', 'CO', 60600),
 ('CO089', 'Funza', 'Cundinamarca', 'CO', 60571),
 ('CO090', 'Chigorodó', 'Antioquia', 'CO', 59597),
 ('CO091', 'Corozal', 'Sucre', 'CO', 57300),
 ('CO092', 'Zona Bananera', 'Magdalena', 'CO', 56404),
 ('CO093', 'Fundación', 'Magdalena', 'CO', 56107),
 ('CO094', 'San Andrés', 'San Andrés y Providencia', 'CO', 55426),
 ('CO095', 'Chiquinquirá', 'Boyacá', 'CO', 54949),
 ('CO096', 'Acacías', 'Meta', 'CO', 54753),
 ('CO097', 'Florida', 'Valle del Cauca', 'CO', 54626),
 ('CO098', 'El Banco', 'Magdalena', 'CO', 53544),
 ('CO099', 'Ciénaga de Oro', 'Córdoba', 'CO', 53403),
 ('CO100', 'El Cerrito', 'Valle del Cauca', 'CO', 53244),
 ('CO101', 'Pamplona', 'Norte de Santander', 'CO', 52903),
 ('CO102', 'La Estrella', 'Antioquia', 'CO', 52763),
 ('CO103', 'La Plata', 'Huila', 'CO', 52549),
 ('CO104', 'Agustín Codazzi', 'Cesar', 'CO', 52219),
 ('CO105', 'Chinchiná', 'Caldas', 'CO', 51301),
 ('CO106', 'Granada', 'Meta', 'CO', 50837),
 ('CO107', 'San Marcos', 'Sucre', 'CO', 50336),
 ('CO108', 'Baranoa', 'Atlántico', 'CO', 50261);

INSERT INTO `specializations` (`id`, `en`, `es`, `en_search`, `es_search`) VALUES
(1, 'Acupuncture\\Acupuncturist', 'Acupuntura\\Acupunturista', 'acupunctureacupuncturist', 'acupunturaacupunturista'),
(2, 'Anesthesiology\\Anesthesia\\Anesthesiologist', 'Anestesiología\\Anestesiólogo\\Anestesióloga\\Anestesia', 'anesthesiologyanesthesiaanesthesiologist', 'anestesiologiaanestesiologoanestesiologaanestesia'),
(3, 'Obstetric Anesthesiology\\Obstetric Anesthesia\\Obstetric Anesthesiologist', 'Anestesia obstétrica', 'obstetricanesthesiologyobstetricanesthesiaobstetricanesthesiologist', 'anestesiaobstetrica'),
(4, 'Pediatric Anesthesiology\\Pediatric Anesthesia\\Pediatric Anesthesiologist', 'Anestesia pediátrica', 'pediatricanesthesiologypediatricanesthesiapediatricanesthesiologist', 'anestesiapediatrica'),
(5, 'Cardiovascular Anesthesiology\\Cardiovascular Anesthesia\\Cardiovascular Anesthesiologist', 'Anestesia cardiovascular', 'cardiovascularanesthesiologycardiovascularanesthesiacardiovascularanesthesiologist', 'anestesiacardiovascular'),
(6, 'Transplant Anesthesiology\\Transplant Anesthesia\\Transplant Anesthesiologist', 'Anestesia de trasplantes', 'transplantanesthesiologytransplantanesthesiatransplantanesthesiologist', 'anestesiadetrasplantes'),
(7, 'Regional Anesthesiology\\Regional Anesthesia\\Regional Anesthesiologist', 'Anestesia Regional', 'regionalanesthesiologyregionalanesthesiaregionalanesthesiologist', 'anestesiaregional'),
(8, 'Neuro Anesthesiology\\Neuro Anesthesia\\Neuro Anesthesiologist', 'Neuro anestesia\\Neuro anestesiología', 'neuroanesthesiologyneuroanesthesianeuroanesthesiologist', 'neuroanestesianeuroanestesiologia'),
(9, 'Pain Management\\Pain Medicine', 'Medicina del dolor', 'painmanagementpainmedicine', 'medicinadeldolor'),
(10, 'Audiologist\\Hearing Specialist', 'Fonoaudiología\\Fonoaudiólogo\\Fonoaudióloga', 'audiologisthearingspecialist', 'fonoaudiologiafonoaudiologofonoaudiologa'),
(11, 'Chiropracty\\Chiropractor', 'Quiropráctica\\Quiropráctico', 'chiropractychiropractor', 'quiropracticaquiropractico'),
(12, 'Critical Care\\Intensive Care\\Surgical Critical Care', 'Cuidado crítico\\Cuidado intensivo\\Cuidado crítico quirúrgico\\Cuidado intensivo quirúrgico\\Intensivista', 'criticalcareintensivecaresurgicalcriticalcare', 'cuidadocriticocuidadointensivocuidadocriticoquirurgicocuidadointensivoquirurgicointensivista'),
(13, 'Dentistry\\Dentist', 'Odontología\\Odontólogo\\Odontóloga', 'dentistrydentist', 'odontologiaodontologoodontologa'),
(14, 'Endodoncy\\Endodontist', 'Endodoncia\\Endodoncista', 'endodoncyendodontist', 'endodonciaendodoncista'),
(15, 'Prosthodoncy\\Prosthodontist', 'Prostodoncia\\Prostodontista', 'prosthodoncyprosthodontist', 'prostodonciaprostodontista'),
(16, 'Oral Surgery\\Oral surgeon', 'Cirugía oral\\Cirujano oral\\Cirujana oral\\Cirugía maxilofacial\\Cirujano maxilofacial\\Cirujana maxilofacial', 'oralsurgeryoralsurgeon', 'cirugiaoralcirujanooralcirujanaoralcirugiamaxilofacialcirujanomaxilofacialcirujanamaxilofacial'),
(17, 'Orthodoncy\\Orthodontist', 'Ortodoncia\\Ortodoncista', 'orthodoncyorthodontist', 'ortodonciaortodoncista'),
(18, 'Dermatology\\Dermatologist', 'Dermatología\\Dermatólogo\\Dermatóloga', 'dermatologydermatologist', 'dermatologiadermatologodermatologa'),
(19, 'Dermatopathology\\Dermatopathologist', 'Dermatopatología\\Dermatopatólogo\\Dermatopatóloga', 'dermatopathologydermatopathologist', 'dermatopatologiadermatopatologodermatopatologa'),
(20, 'Nutrition\\Nutritionist\\Dietitian', 'Nutrición\\Nutricionista', 'nutritionnutritionistdietitian', 'nutricionnutricionista'),
(21, 'Otolaryngology\\Otolaryngologist\\Ear, Nose and Throat Doctor', 'Otorrinolaringología\\Otorrinolaringólogo\\Otorrinolaringóloga', 'otolaryngologyotolaryngologistear,noseandthroatdoctor', 'otorrinolaringologiaotorrinolaringologootorrinolaringologa'),
(22, 'Otology\\Otologist', 'Otología\\Otólogo\\Otóloga', 'otologyotologist', 'otologiaotologootologa'),
(23, 'Laryngology\\Laringologist', 'Laringología\\Laringólogo\\Laringóloga', 'laryngologylaringologist', 'laringologialaringologolaringologa'),
(24, 'Pediatric Otolaringology\\Pediatric Otolaryngologist', 'Otorrinolaringología pediátrica\\Otorrinolaringólogo pediatra\\Otorrinolaringóloga Pediatra', 'pediatricotolaringologypediatricotolaryngologist', 'otorrinolaringologiapediatricaotorrinolaringologopediatraotorrinolaringologapediatra'),
(25, 'Emergency Medicine\\Emergency Medicine Physician\\Urgent Care\\Urgent Care Physician', 'Medicina de emergencias\\Emergenciología\\Emergenciólogo\\Emergencióloga\\Urgenciología\\Urgentólogo\\Urgentóloga', 'emergencymedicineemergencymedicinephysicianurgentcareurgentcarephysician', 'medicinadeemergenciasemergenciologiaemergenciologoemergenciologaurgenciologiaurgentologourgentologa'),
(26, 'Family Medicine\\Family Physician\\Preventive Medicine', 'Medicina familiar\\Médico familiar\\Médica familiar', 'familymedicinefamilyphysicianpreventivemedicine', 'medicinafamiliarmedicofamiliarmedicafamiliar'),
(27, 'General Medicine\\General Practitioner', 'Medicina general\\Médico general\\Médica general', 'generalmedicinegeneralpractitioner', 'medicinageneralmedicogeneralmedicageneral'),
(28, 'Geriatric Medicine', 'Geriatría\\Geriatra', 'geriatricmedicine', 'geriatriageriatra'),
(29, 'Hospice and Palliative Medicine', 'Cuidado paliativo', 'hospiceandpalliativemedicine', 'cuidadopaliativo'),
(30, 'Internal Medicine\\Medicine\\Internist\\Medical Doctor', 'Medicina interna\\Médico Internista\\Internista', 'internalmedicinemedicineinternistmedicaldoctor', 'medicinainternamedicointernistainternista'),
(31, 'Allergy and Immunology\\Allergist', 'Alergia e inmunología\\Alergología\\Alergólogo\\Alergóloga', 'allergyandimmunologyallergist', 'alergiaeinmunologiaalergologiaalergologoalergologa'),
(32, 'Cardiology\\Cardiovascular Disease\\Cardiologist', 'Cardiología\\Cardiólogo\\Cardióloga', 'cardiologycardiovasculardiseasecardiologist', 'cardiologiacardiologocardiologa'),
(33, 'Electrophysiology\\Electrophysiologist', 'Electrofisiología\\Electrofisiólogo\\Electrofisiología', 'electrophysiologyelectrophysiologist', 'electrofisiologiaelectrofisiologoelectrofisiologia'),
(34, 'Hemodynamics\\Interventional Cardiology', 'Hemodinamia\\Hemodinamista', 'hemodynamicsinterventionalcardiology', 'hemodinamiahemodinamista'),
(35, 'Cardiovascular Imaging', 'Imágenes cardiovasculares', 'cardiovascularimaging', 'imagenescardiovasculares'),
(36, 'Heart Failure', 'Falla cardiaca', 'heartfailure', 'fallacardiaca'),
(37, 'Endocrinology\\Endocrinologist', 'Endocrinología\\Endocrinólogo\\Endocrinóloga', 'endocrinologyendocrinologist', 'endocrinologiaendocrinologoendocrinologa'),
(38, 'Diabetes and Metabolism', 'Diabetes y metabolismo', 'diabetesandmetabolism', 'diabetesymetabolismo'),
(39, 'Gastroenterology\\Gastroenterologist', 'Gastroenterología\\Gastroenterólogo\\Gastroenteróloga', 'gastroenterologygastroenterologist', 'gastroenterologiagastroenterologogastroenterologa'),
(40, 'Hepatology\\Hepatologist', 'Hepatología\\Hepatólogo\\Hepatóloga', 'hepatologyhepatologist', 'hepatologiahepatologohepatologa'),
(41, 'Hematology\\Hematologist', 'Hematología\\Hematólogo\\Hematóloga', 'hematologyhematologist', 'hematologiahematologohematologa'),
(42, 'Hematology/Oncology\\Hemato-oncologist', 'Hemato-oncología\\Hemato-oncólogo\\Hemato-oncóloga', 'hematologyoncologyhematooncologist', 'hematooncologiahematooncologohematooncologa'),
(43, 'Infectious Diseases\\Infectology\\Infectologist', 'Enfermedades infecciosas\\Infectología\\Infectólogo\\Infectóloga', 'infectiousdiseasesinfectologyinfectologist', 'enfermedadesinfecciosasinfectologiainfectologoinfectologa'),
(44, 'Oncology\\Oncologist', 'Oncología\\Oncólogo\\Oncóloga', 'oncologyoncologist', 'oncologiaoncologooncologa'),
(45, 'Nephrology\\Nephrologist', 'Nefrología\\Nefrólogo\\Nefróloga', 'nephrologynephrologist', 'nefrologianefrologonefrologa'),
(46, 'Rheumatology\\Rheumatologist', 'Reumatología\\Reumatólogo\\Reumatóloga', 'rheumatologyrheumatologist', 'reumatologiareumatologoreumatologa'),
(47, 'Pulmonology\\Pulmonologist', 'Neumología\\Neumólogo\\Neumóloga', 'pulmonologypulmonologist', 'neumologianeumologoneumologa'),
(48, 'Medical Genetics', 'Genética Médica\\Genetista', 'medicalgenetics', 'geneticamedicagenetista'),
(49, 'Naturopathic Medicine\\Naturopathic doctor', 'Médico neuropático\\Medicina alternativa\\Medicina alternativa y complementaria', 'naturopathicmedicinenaturopathicdoctor', 'mediconeuropaticomedicinaalternativamedicinaalternativaycomplementaria'),
(50, 'Neonatology\\Neonatologist\\Neonatal-Perinatal Medicine', 'Neonatología\\Neonatólogo\\Neonatóloga\\Medicina neonatal y perinatal', 'neonatologyneonatologistneonatalperinatalmedicine', 'neonatologianeonatologoneonatologamedicinaneonatalyperinatal'),
(51, 'Neurology\\Neurologist', 'Neurología\\Neurólogo\\Neuróloga', 'neurologyneurologist', 'neurologianeurologoneurologa'),
(52, 'Child Neurology\\Child Neurologist', 'Neurología pediátrica\\Neuropediatría\\Neuropediatra', 'childneurologychildneurologist', 'neurologiapediatricaneuropediatrianeuropediatra'),
(53, 'Cognitive Disorders\\Dementia', 'Trastornos cognitivos\\Demencia', 'cognitivedisordersdementia', 'trastornoscognitivosdemencia'),
(54, 'Headache Medicine', 'Cefaleas', 'headachemedicine', 'cefaleas'),
(55, 'Stroke', 'Enfermedad Cerebro Vascular', 'stroke', 'enfermedadcerebrovascular'),
(56, 'Epilepsy\\Epileptologist', 'Epilepsia\\Epileptólogo\\Epileptóloga', 'epilepsyepileptologist', 'epilepsiaepileptologoepileptologa'),
(57, 'Neuromuscular Disease', 'Enfermedades neuromusculares', 'neuromusculardisease', 'enfermedadesneuromusculares'),
(58, 'Neurophysiology\\Neurophysiologist', 'Neurofisiología\\Neurofisióloga\\Neurofisiólogo', 'neurophysiologyneurophysiologist', 'neurofisiologianeurofisiologaneurofisiologo'),
(59, 'Sleep Medicine', 'Medicina del sueño\\Somnólogo\\Somnóloga', 'sleepmedicine', 'medicinadelsuenosomnologosomnologa'),
(60, 'Neuro-ophtalmology\\Neuro-ophtalmologist', 'Neurooftalmología\\Neurooftalmólogo\\Neurooftalmóloga', 'neuroophtalmologyneuroophtalmologist', 'neurooftalmologianeurooftalmologoneurooftalmologa'),
(61, 'Neuro-otology\\Neuro-otologist', 'Neurootología\\Neurootólogo\\Neurootóloga', 'neurootologyneurootologist', 'neurootologianeurootologoneurootologa'),
(62, 'Neuro-oncology\\Neuro-oncologist', 'Neurooncología\\Neurooncólogo\\Neurooncóloga', 'neurooncologyneurooncologist', 'neurooncologianeurooncologoneurooncologa'),
(63, 'Movement Disorders', 'Movimientos Anormales', 'movementdisorders', 'movimientosanormales'),
(64, 'Neurorehabilitation', 'Neurorehabilitación', 'neurorehabilitation', 'neurorehabilitacion'),
(65, 'Neurological Surgery\\Neurosurgeon', 'Neurocirugía\\Neurocirujano\\Neurocirujana', 'neurologicalsurgeryneurosurgeon', 'neurocirugianeurocirujanoneurocirujana'),
(66, 'Nuclear Medicine', 'Medicina Nuclear', 'nuclearmedicine', 'medicinanuclear'),
(67, 'Nurse practitioner', 'Nurse practitioner', 'nursepractitioner', 'nursepractitioner'),
(68, 'Obstetrics and Gynecology\\OB-GYN', 'Ginecología y obstetricia\\Ginecólogo y obstetra\\Ginecóloga y obstetra', 'obstetricsandgynecologyobgyn', 'ginecologiayobstetriciaginecologoyobstetraginecologayobstetra'),
(69, 'Infertility', 'Fertilidad', 'infertility', 'fertilidad'),
(70, 'Gynecologic Oncology\\Gynecologic Oncologist', 'Ginecología oncológica\\Gineco oncólogo\\Gineco oncóloga', 'gynecologiconcologygynecologiconcologist', 'ginecologiaoncologicaginecooncologoginecooncologa'),
(71, 'Maternal-Fetal Medicine', 'Medicina Materno-Fetal', 'maternalfetalmedicine', 'medicinamaternofetal'),
(72, 'Minimally Invasive Gynecologic Surgery\\Minimally Invasive Gynecologic Surgeon', 'Cirugía ginecológica mínimamente invasiva\\Cirujano ginecológico mínimamente invasivo\\Cirujana ginecológica mínimamente invasiva', 'minimallyinvasivegynecologicsurgeryminimallyinvasivegynecologicsurgeon', 'cirugiaginecologicaminimamenteinvasivacirujanoginecologicominimamenteinvasivocirujanaginecologicaminimamenteinvasiva'),
(73, 'Pediatric and Adolescent Gynecology\\Pediatric and Adolescent Gynecologist', 'Ginecología pediátrica y de adolescentes\\Ginecólogo pediátrico y de adolescentes\\Ginecóloga pediátrica y de adolescentes', 'pediatricandadolescentgynecologypediatricandadolescentgynecologist', 'ginecologiapediatricaydeadolescentesginecologopediatricoydeadolescentesginecologapediatricaydeadolescentes'),
(74, 'Reproductive endocrinology\\Reproductive endocrinologist', 'Endocrinología reproductiva', 'reproductiveendocrinologyreproductiveendocrinologist', 'endocrinologiareproductiva'),
(75, 'Female Pelvic Medicine and Reconstructive Surgery\\Female Pelvic Medicine and Reconstructive Surgeon', 'Cirugía de piso pélvico\\Cirujano de piso pélvico\\Cirujana de piso pélvico', 'femalepelvicmedicineandreconstructivesurgeryfemalepelvicmedicineandreconstructivesurgeon', 'cirugiadepisopelvicocirujanodepisopelvicocirujanadepisopelvico'),
(76, 'Fetal Surgery\\Fetal Surgeon', 'Cirugía fetal\\Cirujano fetal\\Cirujana fetal', 'fetalsurgeryfetalsurgeon', 'cirugiafetalcirujanofetalcirujanafetal'),
(77, 'Ophtalmologist\\Eye Doctor', 'Oftalmología\\Oftalmólogo\\Oftalmóloga', 'ophtalmologisteyedoctor', 'oftalmologiaoftalmologooftalmologa'),
(78, 'Retinology\\Retinologist', 'Retinología\\Retinólogo\\Retinóloga', 'retinologyretinologist', 'retinologiaretinologoretinologa'),
(79, 'Optometry\\Optometrist', 'Optometría\\Optómetra', 'optometryoptometrist', 'optometriaoptometra'),
(80, 'Orthopedic Surgery\\Orthopedic Surgeon', 'Ortopedia y traumatología\\Ortopedista y traumatólogo\\Ortopedista y traumatóloga', 'orthopedicsurgeryorthopedicsurgeon', 'ortopediaytraumatologiaortopedistaytraumatologoortopedistaytraumatologa'),
(81, 'Pediatric Orthopedic Surgery\\Pediatric Orthopedic Surgeon', 'Ortopedia infantil\\Ortopedista infantil', 'pediatricorthopedicsurgerypediatricorthopedicsurgeon', 'ortopediainfantilortopedistainfantil'),
(82, 'Orthopedic Surgery - Spine\\Orthopedic Surgeon - Spine', 'Ortopedia de columna\\Ortopedista de columna', 'orthopedicsurgeryspineorthopedicsurgeonspine', 'ortopediadecolumnaortopedistadecolumna'),
(83, 'Orthopedic Surgery - Knee\\Orthopedic Surgeon - Knee', 'Ortopedia de rodilla\\Ortopedista de rodilla', 'orthopedicsurgerykneeorthopedicsurgeonknee', 'ortopediaderodillaortopedistaderodilla'),
(84, 'Orthopedic Surgery - Hip\\Orthopedic Surgeon - Hip', 'Ortopedia de cadera\\Ortopedista de cadera', 'orthopedicsurgeryhiporthopedicsurgeonhip', 'ortopediadecaderaortopedistadecadera'),
(85, 'Hand Surgery\\Hand surgeon Orthopedic Surgery - Hand\\Plastic Surgery - Hand', 'Cirugía de mano\\Ortopedia de mano\\Cirugía plástica de mano\\Cirujano de mano\\Cirujana de mano', 'handsurgeryhandsurgeonorthopedicsurgeryhandplasticsurgeryhand', 'cirugiademanoortopediademanocirugiaplasticademanocirujanodemanocirujanademano'),
(86, 'Orthopedic Surgery - Foot and Ankle\\Orthopedic Surgeon - Foot and Ankle', 'Ortopedia de pie y tobillo\\Ortopedista de pie y tobillo', 'orthopedicsurgeryfootandankleorthopedicsurgeonfootandankle', 'ortopediadepieytobilloortopedistadepieytobillo'),
(87, 'Orthopedic Surgery - Shoulder and Elbow\\Orthopedic Surgeon - Shoulder and Elbow', 'Ortopedia de hombro y codo\\Ortopedista de hombro y codo', 'orthopedicsurgeryshoulderandelboworthopedicsurgeonshoulderandelbow', 'ortopediadehombroycodoortopedistadehombroycodo'),
(88, 'Orthopedic Surgery - Oncology\\Orthopedic Surgeon - Oncology', 'Ortopedia oncológica\\Ortopedista oncólogo\\Ortopedista oncóloga', 'orthopedicsurgeryoncologyorthopedicsurgeononcology', 'ortopediaoncologicaortopedistaoncologoortopedistaoncologa'),
(89, 'Pediatrics\\Pediatrician', 'Pediatría\\Pediatra', 'pediatricspediatrician', 'pediatriapediatra'),
(90, 'Pediatric Cardiology\\Pediatric Cardiologist', 'Cardiología pediátrica\\Cardiólogo pediatra\\Cardióloga pediatra', 'pediatriccardiologypediatriccardiologist', 'cardiologiapediatricacardiologopediatracardiologapediatra'),
(91, 'Pediatric Dentistry\\Pediatric Dentist', 'Odontología pediátrica\\Odontólogo pediatra\\Odontóloga pediatra', 'pediatricdentistrypediatricdentist', 'odontologiapediatricaodontologopediatraodontologapediatra'),
(92, 'Pediatric Dermatology\\Pediatric Dermatologist', 'Dermatología pediátrica\\Dermatólogo pediatra\\Dermatóloga pediatra', 'pediatricdermatologypediatricdermatologist', 'dermatologiapediatricadermatologopediatradermatologapediatra'),
(93, 'Pediatric Gastroenterology\\Pediatric Gastroenterologist', 'Gastroenterología pediátrica\\Gastroenterólogo pediatra\\Gastroenteróloga pediatra', 'pediatricgastroenterologypediatricgastroenterologist', 'gastroenterologiapediatricagastroenterologopediatragastroenterologapediatra'),
(94, 'Pediatric Infectious Disease\\Pediatric Infectologist', 'Infectología pediátrica\\Infectólogo pediatra\\Infectóloga pediatra', 'pediatricinfectiousdiseasepediatricinfectologist', 'infectologiapediatricainfectologopediatrainfectologapediatra'),
(95, 'Pediatric Nephrology\\Pediatric Nephrologist', 'Nefrología pediátrica\\Nefrólogo pediatra\\Nefróloga pediatra', 'pediatricnephrologypediatricnephrologist', 'nefrologiapediatricanefrologopediatranefrologapediatra'),
(96, 'Pediatric Rheumatology\\Pediatric Rheumatologist', 'Reumatología pediátrica\\Reumatólogo pediátra\\Reumatóloga Pediatra', 'pediatricrheumatologypediatricrheumatologist', 'reumatologiapediatricareumatologopediatrareumatologapediatra'),
(97, 'Pediatric Pulmonology\\Pediatric Pulmonologist', 'Neumología pediátrica\\Neumólogo pediatra\\Neumóloga pediatra', 'pediatricpulmonologypediatricpulmonologist', 'neumologiapediatricaneumologopediatraneumologapediatra'),
(98, 'Pediatric Oncology\\Pediatric Oncologist', 'Oncología pediátrica\\Oncólogo pediatra\\Oncóloga pediatra', 'pediatriconcologypediatriconcologist', 'oncologiapediatricaoncologopediatraoncologapediatra'),
(99, 'Adolescent Medicine', 'Medicina del adolescente', 'adolescentmedicine', 'medicinadeladolescente'),
(100, 'Physical Medicine and Rehabilitation\\Physiatrist', 'Medicina física y rehabilitación\\Fisiatra', 'physicalmedicineandrehabilitationphysiatrist', 'medicinafisicayrehabilitacionfisiatra'),
(101, 'Physical Therapy\\Physical Therapist', 'Terapia física\\Fisioterapeuta', 'physicaltherapyphysicaltherapist', 'terapiafisicafisioterapeuta'),
(102, 'Occupational Therapy\\Occupational Therapist', 'Terapia ocupacional\\Terapeuta ocupacional', 'occupationaltherapyoccupationaltherapist', 'terapiaocupacionalterapeutaocupacional'),
(103, 'Podiatry\\Podiatrist', 'Podología\\Podólogo\\Podóloga', 'podiatrypodiatrist', 'podologiapodologopodologa'),
(104, 'Psychiatry\\Psychiatrist', 'Psiquiatría\\Psiquiatra', 'psychiatrypsychiatrist', 'psiquiatriapsiquiatra'),
(105, 'Child Psychiatry\\Child Psychiatrist', 'Psiquiatría infantil\\Psiquiatra infantil', 'childpsychiatrychildpsychiatrist', 'psiquiatriainfantilpsiquiatrainfantil'),
(106, 'Addiction', 'Adicción', 'addiction', 'adiccion'),
(107, 'Forensics\\Forensic Psychiatrist', 'Psiquiatría forense\\Psiquiatra forense', 'forensicsforensicpsychiatrist', 'psiquiatriaforensepsiquiatraforense'),
(108, 'Psychology\\Psychologist', 'Psicología\\Psicólogo\\Psicóloga', 'psychologypsychologist', 'psicologiapsicologopsicologa'),
(109, 'Psychotherapy\\Psychotherapist', 'Psicoterapia\\Psicoterapeuta', 'psychotherapypsychotherapist', 'psicoterapiapsicoterapeuta'),
(110, 'Radiology\\Radiologist\\Diagnostic Imaging', 'Radiología\\Radiólogo\\Radióloga\\Imágenes Diagnósticas', 'radiologyradiologistdiagnosticimaging', 'radiologiaradiologoradiologaimagenesdiagnosticas'),
(111, 'Neuroradiology\\Neuroradiologist', 'Neuroradiología\\Neuroradiólogo\\Neuroradióloga', 'neuroradiologyneuroradiologist', 'neuroradiologianeuroradiologoneuroradiologa'),
(112, 'Musculoskeletal Imaging', 'Radiología musculoesquelética', 'musculoskeletalimaging', 'radiologiamusculoesqueletica'),
(113, 'Chest Imaging', 'Radiología del tórax', 'chestimaging', 'radiologiadeltorax'),
(114, 'Abdominal Imaging', 'Radiología abdominal\\Radiología gastrointestinal', 'abdominalimaging', 'radiologiaabdominalradiologiagastrointestinal'),
(115, 'Breast Imaging', 'Imágenes de los senos', 'breastimaging', 'imagenesdelossenos'),
(116, 'Head and Neck Imaging', 'Radiología de cabeza y cuello', 'headandneckimaging', 'radiologiadecabezaycuello'),
(117, 'Genitourinary Imaging', 'Radiología genitourinaria', 'genitourinaryimaging', 'radiologiagenitourinaria'),
(118, 'Interventional Radiology\\Interventional Radiologist', 'Radiología intervencionista\\Radiólogo intervencionista\\Radióloga intervencionista', 'interventionalradiologyinterventionalradiologist', 'radiologiaintervencionistaradiologointervencionistaradiologaintervencionista'),
(119, 'Pediatric Radiology\\Pediatric Radiologist', 'Radiología pediátrica\\Radiólogo pediatra\\Radióloga pediatra', 'pediatricradiologypediatricradiologist', 'radiologiapediatricaradiologopediatraradiologapediatra'),
(120, 'Radiation Oncology\\Radiation Oncologist', 'Radioterapia\\Radioterapeuta\\Radioncología', 'radiationoncologyradiationoncologist', 'radioterapiaradioterapeutaradioncologia'),
(121, 'Sports Medicine', 'Medicina del deporte\\Deportólogo\\Deportóloga', 'sportsmedicine', 'medicinadeldeportedeportologodeportologa'),
(122, 'General Surgery\\General Surgeon', 'Cirugía general\\Cirujano general\\Cirujana general', 'generalsurgerygeneralsurgeon', 'cirugiageneralcirujanogeneralcirujanageneral'),
(123, 'Cardiovascular Surgery\\Cardiovascular Surgeon', 'Cirugía cardiovascular\\Cirujano cardiovascular\\Cirujana cardiovascular', 'cardiovascularsurgerycardiovascularsurgeon', 'cirugiacardiovascularcirujanocardiovascularcirujanacardiovascular'),
(124, 'Colon and Rectal Surgery\\Colon and Rectal Surgeon', 'Cirugía de colon y recto\\Cirujano de colon y recto\\Cirujana de colon y recto', 'colonandrectalsurgerycolonandrectalsurgeon', 'cirugiadecolonyrectocirujanodecolonyrectocirujanadecolonyrecto'),
(125, 'Endocrine Surgery\\Endocrine Surgeon', 'Cirugía endocrina\\Cirujano endocrino\\Cirujana endocrina', 'endocrinesurgeryendocrinesurgeon', 'cirugiaendocrinacirujanoendocrinocirujanaendocrina'),
(126, 'Head and Neck Surgery\\Head and Neck Surgeon', 'Cirugía de cabeza y cuello\\Cirujano de cabeza y cuello\\Cirujana de cabeza y cuello', 'headandnecksurgeryheadandnecksurgeon', 'cirugiadecabezaycuellocirujanodecabezaycuellocirujanadecabezaycuello'),
(127, 'Hepatobiliary Surgery\\Hepatobiliary Surgeon', 'Cirugía hepatobiliar\\Cirujano hepatobiliar\\Cirujana hepatobiliar', 'hepatobiliarysurgeryhepatobiliarysurgeon', 'cirugiahepatobiliarcirujanohepatobiliarcirujanahepatobiliar'),
(128, 'Thoracic Surgery\\Thoracic Surgeon', 'Cirugía de tórax\\Cirujano de tórax\\Cirujana de tórax', 'thoracicsurgerythoracicsurgeon', 'cirugiadetoraxcirujanodetoraxcirujanadetorax'),
(129, 'Vascular Surgery\\Vascular Surgeon', 'Cirugía vascular periférico\\Cirujano vascular periférico\\Cirujano vascular periférico', 'vascularsurgeryvascularsurgeon', 'cirugiavascularperifericocirujanovascularperifericocirujanovascularperiferico'),
(130, 'Transplant Surgery\\Transplant Surgeon', 'Cirugía de trasplantes\\Cirujano de trasplantes\\Cirujana de trasplantes', 'transplantsurgerytransplantsurgeon', 'cirugiadetrasplantescirujanodetrasplantescirujanadetrasplantes'),
(131, 'Transplant Surgery - Heart\\Transplant Surgeon - Heart\\Heart Transplant', 'Cirugía de trasplantes de corazón\\Cirujano de trasplantes de corazón\\Cirujana de trasplantes de corazón\\Trasplante cardiaco', 'transplantsurgeryhearttransplantsurgeonhearthearttransplant', 'cirugiadetrasplantesdecorazoncirujanodetrasplantesdecorazoncirujanadetrasplantesdecorazontrasplantecardiaco'),
(132, 'Transplant Surgery - Lung\\Transplant Surgeon - Lung\\Lung Transplant', 'Cirugía de trasplantes de pulmón\\Cirujano de trasplantes de pulmón\\Cirujana de trasplantes de pulmón\\Trasplante pulmonar', 'transplantsurgerylungtransplantsurgeonlunglungtransplant', 'cirugiadetrasplantesdepulmoncirujanodetrasplantesdepulmoncirujanadetrasplantesdepulmontrasplantepulmonar'),
(133, 'Transplant Surgery - Liver\\Transplant Surgeon - Liver\\Hepatic Transplant', 'Cirugía de trasplantes de hígado\\Cirujano de trasplantes de hígado\\Cirujana de trasplantes de hígado\\Trasplante hepático', 'transplantsurgerylivertransplantsurgeonliverhepatictransplant', 'cirugiadetrasplantesdehigadocirujanodetrasplantesdehigadocirujanadetrasplantesdehigadotrasplantehepatico'),
(134, 'Transplant Surgery - Kidney\\Transplant Surgeon - Kidney\\Renal Transplant', 'Cirugía de trasplantes de riñón\\Cirujano de trasplantes de riñón\\Cirujana de trasplantes de riñón\\Trasplante renal', 'transplantsurgerykidneytransplantsurgeonkidneyrenaltransplant', 'cirugiadetrasplantesderinoncirujanodetrasplantesderinoncirujanadetrasplantesderinontrasplanterenal'),
(135, 'Trauma Surgery\\Trauma Surgeon', 'Cirugía de trauma\\Cirujano de trauma\\Cirujana de trauma', 'traumasurgerytraumasurgeon', 'cirugiadetraumacirujanodetraumacirujanadetrauma'),
(136, 'Minimally Invasive Surgery\\Minimally Invasive Surgeon', 'Cirugía mínimamente invasiva\\Cirujano mínimamente invasivo\\Cirujana mínimamente invasiva', 'minimallyinvasivesurgeryminimallyinvasivesurgeon', 'cirugiaminimamenteinvasivacirujanominimamenteinvasivocirujanaminimamenteinvasiva'),
(137, 'Pediatric Surgery\\Pediatric Surgeon', 'Cirugía pediátrica\\Cirujano pediatra\\Cirujana pediatra', 'pediatricsurgerypediatricsurgeon', 'cirugiapediatricacirujanopediatracirujanapediatra'),
(138, 'Surgical Oncology\\Surgical Oncologist', 'Cirugía oncológica\\Cirujano oncólogo\\Cirujana oncóloga', 'surgicaloncologysurgicaloncologist', 'cirugiaoncologicacirujanooncologocirujanaoncologa'),
(139, 'Metabolic Surgery\\Metabolic Surgeon\\Bariatric Surgery\\Bariatric Surgeon', 'Cirugía bariátrica\\Cirujano bariátrico\\Cirujana bariátrica\\Cirugía metabólica', 'metabolicsurgerymetabolicsurgeonbariatricsurgerybariatricsurgeon', 'cirugiabariatricacirujanobariatricocirujanabariatricacirugiametabolica'),
(140, 'Plastic Surgery\\Plastic Surgeon\\Plastic and Reconstructive Surgery\\Plastic and Reconstructive Surgeon', 'Cirugía plástica\\Cirujano plástico\\Cirujana plástica\\Cirugía plástica y reconstructiva\\Cirujano plástico y reconstructivo\\Cirujana plástica y reconstructiva', 'plasticsurgeryplasticsurgeonplasticandreconstructivesurgeryplasticandreconstructivesurgeon', 'cirugiaplasticacirujanoplasticocirujanaplasticacirugiaplasticayreconstructivacirujanoplasticoyreconstructivocirujanaplasticayreconstructiva'),
(141, 'Plastic Surgery - Craniofacial\\Plastic Surgeon - Craniofacial', 'Cirugía plástica craniofacial', 'plasticsurgerycraniofacialplasticsurgeoncraniofacial', 'cirugiaplasticacraniofacial'),
(142, 'Plastic Surgery - Microsurgery\\Microsurgery', 'Microcirugía', 'plasticsurgerymicrosurgerymicrosurgery', 'microcirugia'),
(143, 'Breast Surgery\\Breast Surgeon', 'Cirugía de seno\\Cirujano de seno\\Cirujana de seno Mastología\\Mastólogo\\Mastóloga', 'breastsurgerybreastsurgeon', 'cirugiadesenocirujanodesenocirujanadesenomastologiamastologomastologa'),
(144, 'Travel Medicine', 'Medicina del viajero', 'travelmedicine', 'medicinadelviajero'),
(145, 'Toxicology\\Toxicologist', 'Toxicología\\Toxicólogo\\Toxicóloga', 'toxicologytoxicologist', 'toxicologiatoxicologotoxicologa'),
(146, 'Urology\\Urologist', 'Urología\\Urólogo\\Uróloga', 'urologyurologist', 'urologiaurologourologa'),
(147, 'Pediatric Urology\\Pediatric Urologist', 'Urología pediátrica\\Urólogo pediatra\\Uróloga pediatra', 'pediatricurologypediatricurologist', 'urologiapediatricaurologopediatraurologapediatra'),
(148, 'Male Infertility', 'Infertilidad masculina', 'maleinfertility', 'infertilidadmasculina'),
(149, 'Neurourology', 'Neurourología', 'neurourology', 'neurourologia'),
(150, 'Sexology\\Sexologist', 'Sexología\\Sexólogo\\Sexóloga', 'sexologysexologist', 'sexologiasexologosexologa'),
(151, 'Nursing\\Registered Nurse\\Nurse', 'Enfermería\\Enfermero\\Enfermera', 'nursingregisterednursenurse', 'enfermeriaenfermeroenfermera'),
(152, 'Epidemiology\\Epidemiologist', 'Epidemiología\\Epidemiólogo', 'epidemiologyepidemiologist', 'epidemiologiaepidemiologo'),
(153, 'Aerospace Medicine', 'Medicina aeroespacial', 'aerospacemedicine', 'medicinaaeroespacial'),
(154, 'Public Health', 'Salud pública', 'publichealth', 'saludpublica'),
(155, 'Pathology\\Pathologist', 'Patología\\Patólogo\\Patóloga', 'pathologypathologist', 'patologiapatologopatologa'),
(156, 'Respiratory Therapy\\Respiratory Therapist', 'Terapia respiratoria\\Terapeuta respiratorio', 'respiratorytherapyrespiratorytherapist', 'terapiarespiratoriaterapeutarespiratorio'),
(157, 'Aesthetic Medicine', 'Medicina estética', 'aestheticmedicine', 'medicinaestetica'),
(158, 'Immunology\\Immunologist', 'Inmunología\\Inmunólogo', 'immunologyimmunologist', 'inmunologiainmunologo'),
(159, 'Occupational Health', 'Salud ocupacional', 'occupationalhealth', 'saludocupacional'),
(160, 'Forensic Medicine', 'Medicina forense', 'forensicmedicine', 'medicinaforense');
