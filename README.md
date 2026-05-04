	# Shooting Match Registration

Webová aplikace pro **registraci střelců na soutěž**, včetně **administrační sekce** pro správu závodníků, plateb, kategorií a exportů.  
Projekt je navržen jako jednoduché, přehledné a snadno nasaditelné řešení pro menší i střední střelecké závody.

---

## Funkce

### Veřejná část
- Registrace závodníků pomocí jednoduchého formuláře  
- Validace vstupů  
- Automatické potvrzení registrace (pokud je implementováno SMTP)  
- Přehledné zobrazení informací o závodě
- URL: registrace.domena.cz

### Administrační sekce
- Přihlášení administrátora  
- Seznam závodníků s možností:
  - editace údajů
  - označení platby
  - filtrování a řazení
  - mazání registrací
- Exporty (CSV / XLSX – pokud je implementováno)
- Nastavení parametrů závodu (pokud je součástí instalace)
- URL: admin.domena.cz
---

## Technologie

- **PHP 8+**
- **MySQL / MariaDB**
- **Bootstrap 5** (UI)
- **Vanilla JavaScript** (bez frameworků)
- **Prepared statements** a bezpečné SQL dotazy
- **Modulární struktura** pro snadnou údržbu

---

## Instalace

1. Naklonujte repozitář:
   ```bash
   cd cesta_k_lolálnímu_adresáři
   git clone https://github.com/xmilanz/shooting-match-registration.git

2. Příprava adresářové struktury
2.1 Zkopírujte obsah adresářů public a admin do požadovaného úložiště

2.2 V adresáři libs rozbalte PhpMailer a PhpSpreadsheet
   mcedit config/data.php
   mcedit config/secret/db.php
   mcedit config/secret/smtp.php



2. V konfiguračních souborech upravte
 - přístup k databázi
 - konfigurace SMTP
 - adresa "vývojáře" - můžete ponechat původní

3. Nahrajte projekt na server (Apache / Nginx).

4. Otevřete match_URL/admin a přihlaste se pomocí přednastavených údajů (upravte podle potřeby).
 - přihlášení username: admin, heslo: Registrace-666
 - po přihlášení změňte heslo!!!
 
## Vývojová verze
Vývoj probíhá v samostatném repozitáři

Tento veřejný repozitář obsahuje stabilní verzi určenou pro použití a nasazení.

## Licence
Projekt je licencován pod MIT licencí. Můžete jej používat, upravovat a distribuovat, pokud uvedete autora.

Podrobnosti najdete v souboru LICENSE.

## Poděkování
Projekt vznikl jako praktický nástroj pro organizátory střeleckých závodů a vychází ze systému naprogramovaném Jiřím Šedinou (2010) a uraveném Janem Stanňkem.
Pokud jej použijete, budu rád za zpětnou vazbu nebo zmínku o zdroji. V patičce prosím ponechejte všechny 3 autory

## Kontakt
Máte dotaz nebo návrh na vylepšení?
Vytvořte issue nebo mě kontaktujte přes GitHub.
