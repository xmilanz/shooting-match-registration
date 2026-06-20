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
- doporučená URL: registrace.domena.cz

### Administrační sekce
- Přihlášení administrátora  
- správa všech závodů
- Seznam závodníků s možností:
  - editace údajů
  - označení platby
  - filtrování a řazení
  - mazání registrací
- Exporty (CSV / XLSX – pokud je implementováno)
- Nastavení parametrů závodu (pokud je součástí instalace)
- doporučená URL: admin.domena.cz
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
   2.1 Zkopírujte obsah adresáře public a admin do požadovaného úložiště
      - příklad struktury pro více závodů
      admin // společný pro všechny závodu
      registrace
         |- nazev-zavodu-1 (URL závodu: https://registrace.domena.cz/nazev-avodu-1)
         |- nazev-zavodu-2 (URL závodu: https://registrace.domena.cz/nazev-avodu-2)
         |- nazev-zavodu-3 (URL závodu: https://registrace.domena.cz/nazev-avodu-3)
         |- ...

   2.2 V adresáři libs (admin i public) rozbalte PhpMailer a PhpSpreadsheet

3. Doplňte přístupové údaje v konfiguračních souborech
   ```bash
   mcedit config/secret/db.php
   mcedit config/secret/smtp.php

 - přístup k databázi
 - konfigurace SMTP

4. DoplŇte vhodné názvy tabulek 
   ```bash
   mcedit config/data.php

 - $table - tabulka závodníků - 
 - $table_matches - tabulka s konfigrací závodů (doporučeno "match_config")
 - $table_admins - tabulka s účty pro přístup do admnistrace (doporučeno "site_admins")
 - $admin_url - URL administrace (doporučeno https://admin.domena.tld)
 - $reg_redirect_url - URL registrace závodů (doporučeno https://registrace.domena.tld);
   - v adresáři registrace můžete vytvořit index.html se seznamem všech závodů
 - podle názvů vlastních závodů (URL) upravte podmínky pro názvy sdílených tabulek (často je vhodné, aby jeden typ závodu používat stejnou tabulku; jinak se pro každý závod vytvoří vlastní tabulky disciplín, startovného)
 - adresa "vývojáře" - můžete ponechat původní

5. Nahrajte na server (Apache / Nginx) - ftp/ssh dle možností

6. Otevřete match_URL
 - vytvoří se tabulky v databázi
 - založí se demo účet s vygenerovaným heslem
 - instalace končí interaktivně - konec, přihlášení do administrace
 - při přihlášení do administrace je vynucoená změna hesla

 7. V administraci nastavte data závodu
 - povinné údaje jsou zvýrazněné červenou hvězdičkou
 - 
 
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
