<?php
$dbcreateParam = $_SERVER['dbcreateParam'] ?? 'hlavni';
$dbcreateTable = $_SERVER['dbcreateTable'] ?? '';

switch ($dbcreateParam) {
    case "hlavni":
        $query = "CREATE TABLE " . $dbcreateTable . " (
    Cislo int(4)AUTO_INCREMENT PRIMARY KEY,
    Alias varchar(16),
    Prijmeni varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Jmeno varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
    ZP varchar(255),
    ObcanskyPrukaz varchar(15),
    CisloZbrane varchar(255),
    Region varchar(3),
    Disciplina varchar(255),
    DisciplinaReg varchar(255),
    Kategorie varchar(20),
    Divize varchar(3),
    Faktor varchar(3),
    Squad varchar(3),
    SquadReg varchar(3),
    Staff varchar(3),
    Mail varchar(40),
    Poznamka varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    DatReg varchar (11),
    DatPay varchar (11),
    RegistraceIP varchar (50),
    Urgence varchar(255),
    Zaplaceno varchar(3),
    ZaplatiNaMiste varchar(3),
    DatumZaplaceni varchar(255),
    VarSym varchar(7) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    klic int(11) NOT NULL DEFAULT '0',
    OdeslanRegMail varchar(3),
    Vyrazeno varchar(255),
    VyrazenoIP varchar(50),
    Castka float(9,2),
    Mena varchar(3),
    Zavod varchar(25)
    )";
        runQuery($query, "Tabulka [$dbcreateTable] byla vytvořena");
        break;

    case "nastaveni":
        $query = "CREATE TABLE " . $dbcreateTable . " (
    parId int(4)AUTO_INCREMENT PRIMARY KEY,
    parName varchar(20) UNIQUE not null,
    parValue varchar(50),
    parValueI FLOAT(9,3),
	parNote1 varchar(100) DEFAULT NULL,
	parNote2 varchar(100) DEFAULT NULL
    )";
        runQuery($query, "Tabulka [$dbcreateTable] byla vytvořena");
        $query = "INSERT into $dbcreateTable (parName,parValueI) VALUES ('dbver',1)";
        runQuery($query, "Tabulka [$dbcreateTable] byla aktualizována");
        break;

    case "match_config":
        $query = "CREATE TABLE match_config (
    Zavod_id varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'Nazev zavodu',
    Zavod_datum varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_cas_registrace varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '17:00',
    Zavod_zacatek_registrace int(3) DEFAULT '30',
    Zavod_konec_registrace int(3) DEFAULT '1',
    Zavod_registrace_pozastaveno varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_more_divisions varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_zbrojni_prukaz varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'on',
    Zavod_cislo_zbrane varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'on',
    Zavod_zobrazovat_sponzory varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Web_zobrazovat_situace varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'on',
    Web_zobrazovat_aliasy varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
    Zavod_cas_prematch varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '13:00 - 17:00',
    Zavod_cas_prezence varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '8:00 - 9:00',
    Zavod_cas_main varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '9:00 - 15:00',
    Zavod_cas_main_dopoledne varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
    Zavod_cas_main_odpoledne varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
    Zavod_misto varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'Misto konani zavodu',
    Zavod_misto_mapa varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'Odkaz na mapu',
    Zavod_poradatel varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'Poradatel',
    Zavod_poradatel_adresa varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'Adresa',
    Zavod_match_director varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'poradatel',
    Zavod_email_poradatel varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'email poradatele',
    Zavod_telefon_poradatel varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '+420 420 123 456 789',
    Zavod_range_master varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'reditel zavodu',
    Zavod_email_range_master varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'email reditele zavodu',
    Zavod_telefon_range_master varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '+420 123 456 789',
    Zavod_stats varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'statistik',
    Zavod_email_stats varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'email statistika',
    Zavod_telefon_stats varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '+420 420 123 456 789',
    Zavod_hospodar varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'hospodar',
    Zavod_email_hospodar varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'email hospodare',
    Zavod_telefon_hospodar varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '+420 420 123 456 789',
    Zavod_email_from varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'auth-smtp@example.com',
    Zavod_stages int(2) DEFAULT '8',
    Zavod_min_pocet_ran int(3)DEFAULT '0',
    Zavod_pocet_dni_na_platbu int(2) DEFAULT '10',
    Zavod_vysledky varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'https://website',
    Zavod_propozice varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
    Squad_prem_max int(3) DEFAULT NULL,
    Squad_main_max int(3) DEFAULT '100',
    Banka_ucet_MENA varchar(3) DEFAULT 'CZK',
    Banka_ucet_cislo varchar(20) DEFAULT '12345678',
    Banka_ucet_kod varchar(4) DEFAULT '0000',
    Banka_nazev varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'nazev banky',
    Banka_adresa varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'adresa banky',
    Klub_web varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'webove stranky klubu',
    Payment_before varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
    PRIMARY KEY (Zavod_id)
    )";
        runQuery($query, "Tabulka [$dbcreateTable] byla vytvořena");
        break;

    case "site_admins":
        $query = "CREATE TABLE site_admins (
    id int(4)AUTO_INCREMENT PRIMARY KEY,
    username varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
    password varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    email varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
    firstname varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
    lastname varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci
    )";
        runQuery($query, "Tabulka [$dbcreateTable] byla vytvořena");
        break;


    case "disciplines":
        $query = "CREATE TABLE " . $dbcreateTable . " (
    Id int(4)AUTO_INCREMENT PRIMARY KEY,
    Name varchar(25) UNIQUE not null,
    Value varchar(100),
    Description varchar(255)
    )";
        runQuery($query, "Tabulka [$dbcreateTable] byla vytvořena");
        break;

    case "fee":
        $query = "CREATE TABLE " . $dbcreateTable . " (
    Id int(4)AUTO_INCREMENT PRIMARY KEY,
    Count int(1) UNIQUE,
    Value int(3)
    )";
        runQuery($query, "Tabulka [$dbcreateTable] byla vytvořena");
        $query = "insert into $dbcreateTable (Count,Value) values
	('1', '250'),
	('2', '150'),
	('3', '200')
	";
        runQuery($query, "Tabulka [$dbcreateTable] byla aktualizována");
        break;
}
