<?php
global $table_matches;
global $table_admins;

$dbcreateParam = $_SERVER['dbcreateParam'] ?? 'main';
$dbcreateTable = $_SERVER['dbcreateTable'] ?? '';

switch ($dbcreateParam) {
    case 'match_config':
        $query = "CREATE TABLE $table_matches (
    Zavod_id varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'Nazev zavodu',
    Zavod_datum varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_cas_registrace_zacatek time DEFAULT '12:00',
    Zavod_cas_registrace_konec time DEFAULT '17:00',
    Zavod_zacatek_registrace int(3) DEFAULT '14',
    Zavod_konec_registrace int(3) DEFAULT '2',
    Zavod_registrace_pozastaveno tinyint(1) DEFAULT '0',
    Zavod_more_divisions tinyint(1) DEFAULT '0',
    Zavod_zbrojni_prukaz tinyint(1) DEFAULT '0',
    Zavod_cislo_zbrane tinyint(1) DEFAULT '0',
    Zavod_zobrazovat_sponzory tinyint(1) DEFAULT '0',
    Web_zobrazovat_situace tinyint(1) DEFAULT '0',
    Web_zobrazovat_aliasy tinyint(1) DEFAULT '0',
    Zavod_cas_prematch varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '13:00 - 17:00',
    Zavod_cas_prezence varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '8:00 - 9:00',
    Zavod_cas_main varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '9:00 - 15:00',
    Zavod_cas_main_dopoledne varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
    Zavod_cas_main_odpoledne varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
    Zavod_misto varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_misto_mapa varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_poradatel varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
    Zavod_poradatel_adresa varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_match_director varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
    Zavod_email_poradatel varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_telefon_poradatel varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_range_master varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_email_range_master varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_telefon_range_master varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_stats varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_email_stats varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_telefon_stats varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_hospodar varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_email_hospodar varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_telefon_hospodar varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_email_from varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_stages int(2) DEFAULT '5',
    Zavod_min_pocet_ran int(3) DEFAULT '100',
    Zavod_pocet_dni_na_platbu int(2) DEFAULT '10',
    Zavod_vysledky varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Zavod_propozice varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Squad_prem_max int(3) DEFAULT '10',
    Squad_main_max int(3) DEFAULT '10',
    Banka_ucet_MENA varchar(3) DEFAULT 'CZK',
    Banka_ucet_cislo varchar(20),
    Banka_ucet_kod int(4),
    Banka_nazev varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Banka_adresa varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Klub_web varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    Payment_before tinyint(1) DEFAULT '0',
    PRIMARY KEY (Zavod_id)
    )";
        runQuery($query, "Tabulka [$dbcreateTable] byla vytvořena");
        break;

    case 'main':
        $query = 'CREATE TABLE ' . $dbcreateTable . " (
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
    VarSym int(7) NOT NULL,
    klic int(11) NOT NULL DEFAULT '0',
    OdeslanRegMail TINYINT(1) NULL DEFAULT NULL,
    Vyrazeno varchar(255),
    VyrazenoIP varchar(50),
    Castka float(9,2),
    Mena varchar(3)DEFAULT 'CZK',
    Zavod varchar(100)
    )";
        runQuery($query, "Tabulka závodu [$dbcreateTable] byla vytvořena");
        break;

    case 'setting':
        $query = 'CREATE TABLE ' . $dbcreateTable . " (
    parId int(4)AUTO_INCREMENT PRIMARY KEY,
    parName varchar(20) UNIQUE not null,
    parValue varchar(50),
    parNote varchar(100) DEFAULT NULL
    )";
        runQuery($query, "Tabulka [$dbcreateTable] byla vytvořena");
        $query = "INSERT into $dbcreateTable (parName,parValue) VALUES ('dbver',2)";
        runQuery($query, "Tabulka [$dbcreateTable] byla aktualizována");
        break;

    case 'site_admins':
        // vygenerujeme OTP pro demo ucet
        $pwd = substr(strtr(base64_encode(random_bytes(9)), '+/', 'AB'), 0, 12);
        $password = password_hash($pwd, PASSWORD_DEFAULT);

        $query = 'CREATE TABLE ' . $dbcreateTable . ' (
    id int(4)AUTO_INCREMENT PRIMARY KEY,
    username varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
    password varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
    email varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
    firstname varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
    lastname varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
    role varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
    organizer varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci,
    last_password_change datetime NULL,
    force_password_change tinyint(1) NULL
    )';
        runQuery($query, "Tabulka [$dbcreateTable] byla vytvořena");
        // vytvorime demo admin ucet s OTP
        $query = "insert into $dbcreateTable (username,password,email,firstname,lastname,role,organizer,last_password_change,force_password_change) values
        ('demo', '$password', 'demo@example.com', 'John', 'Doe', 'admin', 'all',NOW(),'1')
";
        runQuery($query, "Tabulka [$dbcreateTable] byla aktualizována");
        // zobrazime modal s heslem k demo uctu
        echo '
        <div class="row modal fade" id="setupModal" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-center">
                    <h4 class="modal-title text-white w-100 fw-bold py-2">Demo účet byl vytvořený</h4>
                </div>
                <div class="modal-body text-center">
                    <div class="fw-bolder">
                        <p>
                            Jméno:</strong> demo<br>
                            Heslo: <span id="pwd" class="text-danger fw-bold">' . $pwd . '</span>
                        </p>
                    </div>
                <div class="alert alert-info mt-3">
                    Heslo se již znovu nezobrazí!<br>Zkopírujte jej do schránky kliknutím na tlačítko <kbd>Kopírovat</kbd>.
                </div>
              </div>
            
              <div class="modal-footer border-top-0">
                <button class="btn btn-primary" onclick="copyPwd()">Kopírovat</button>
                <button type="button" class="btn btn-success" onclick="closeModal()">Dokončit instalaci</button>
                <button type="button" class="btn btn-danger" onclick="closeModalLogin()">Přihlásit se do administace</button>
              </div>
        
            </div>
          </div>
        </div>
        
        <script>
        let copied = false;
        
        function copyPwd() {
            const text = document.getElementById("pwd").innerText;
            navigator.clipboard.writeText(text);
            copied = true;
            alert("Heslo bylo zkopírováno");
        }

        function closeModal() {
            if (!copied) {
                alert("Nejdřív je potřeba zkopírovat heslo!");
                return;
            }
        
            const modalEl = document.getElementById("setupModal");
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();
        
            // krátké zpoždění kvůli animaci modalu
            setTimeout(() => {
                location.reload();
            }, 300);
        }

        function closeModalLogin() {
            if (!copied) {
                alert("Nejdřív je potřeba zkopírovat heslo!");
                return;
            }
        
            const modalEl = document.getElementById("setupModal");
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();
        
            // krátké zpoždění kvůli animaci modalu
            setTimeout(() => {
                location.replace("login.php");
            }, 300);
        }
        
        // auto-open + disable close kliknutím mimo
        document.addEventListener("DOMContentLoaded", function () {
            const modalEl = document.getElementById("setupModal");
            const modal = new bootstrap.Modal(modalEl, {
                backdrop: "static",
                keyboard: false
            });
            modal.show();
        });
        </script>
        ';
        break;

    case 'disciplines':
        $query = 'CREATE TABLE ' . $dbcreateTable . ' (
    Id int(4)AUTO_INCREMENT PRIMARY KEY,
    Name varchar(25) UNIQUE not null,
    Value varchar(100),
    Description varchar(255)
    )';
        runQuery($query, "Tabulka [$dbcreateTable] byla vytvořena");
        $query = "insert into $dbcreateTable (Name,Value,Description) values
        ('MaO', 'Mala odstrelovacka','Ukazkova disciplina')
        ";
        runQuery($query, "Tabulka [$dbcreateTable] byla aktualizována");
        break;

    case 'categories':
        $query = 'CREATE TABLE ' . $dbcreateTable . ' (
    Id int(4)AUTO_INCREMENT PRIMARY KEY,
    Name varchar(25) UNIQUE not null,
    Value varchar(100)
    )';
        runQuery($query, "Tabulka [$dbcreateTable] byla vytvořena");
        $query = "insert into $dbcreateTable (Name,Value) values
        ('REG', 'Regular'),
        ('JUN', 'Junior')
        ";
        runQuery($query, "Tabulka [$dbcreateTable] byla aktualizována");
        break;

    case 'fee':
        $query = 'CREATE TABLE ' . $dbcreateTable . ' (
    Id int(4)AUTO_INCREMENT PRIMARY KEY,
    Count int(1) UNIQUE,
    Value int(3)
    )';
        runQuery($query, "Tabulka [$dbcreateTable] byla vytvořena");
        $query = "insert into $dbcreateTable (Count,Value) values
        ('1', '250'),
        ('2', '150'),
        ('3', '200')
        ";
        runQuery($query, "Tabulka [$dbcreateTable] byla aktualizována");
        break;
}
