<?php

declare(strict_types=1);
include "header.php";

$download = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>';

// Pozastaveni registrace
if ($match_data['Zavod_registrace_pozastaveno']) {
    $match_data = array_merge($match_data, [
        'Squad_main_max' => '',
        'zavod_categories' => '-',
        'Zavod_datum' => '-',
    ]);
}

// ziskame placeni zavodu
$FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
$FeeStmt->execute();
$line = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
if (count($line) < 3) {
    $_SESSION['toast'] = [
        'type' => 'danger',
        'message' => 'V databázi nejsou 3 hodnoty startovného.',
        'duration' => 3000

    ];
}
$FeeStmt->close();
?>

<div class="row">
    <div class="col-md-6">
        <div class="article">
            <div class="caption mb-2 p-2">
                <h3>Základní informace</h3>
            </div>
            <dl class="row  text-start">
                <dt class="col-4 text-end text-start pe-0">Název soutěže:</dt>
                <dd class="col-8 ps-2 fw-bold text-uppercase"><?= htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8'); ?></dd>
                <dt class="col-4 text-end text-start pe-0">Druh soutěže:</dt>
                <dd class="col-8 ps-2"><?= $druh_souteze ?></dd>
                <dt class="col-4 text-end text-start pe-0 <?= hidden(!$isRegistracePozastavena) ?>">Stav:</dt>
                <dd class="col-8 ps-2 text-danger <?= hidden(!$isRegistracePozastavena) ?>">Pozastavená registrace</dd>
                <dt class="col-4 text-end text-start pe-0">Pořadatel:</dt>
                <dd class="col-8 ps-2"><?= !empty($match_data['Zavod_poradatel']) ? htmlspecialchars($match_data['Zavod_poradatel'], ENT_QUOTES, 'UTF-8') : '<mark class="text-danger small">nenastaveno v administraci</mark>' ?></dd>                <dt class="col-4 text-end text-start pe-0">Datum:</dt>
                <dd class="col-8 ps-2"><?= htmlspecialchars($match_data['Zavod_datum'], ENT_QUOTES, 'UTF-8'); ?></dd>
                <dt class="col-4 text-end text-start pe-0">Místo:</dt>
                <dd class="col-8 ps-2"><?= !empty($match_data['Zavod_misto']) ? htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') : ''; ?>&nbsp;&nbsp;
                    <a href="<?= !empty($match_data['Zavod_misto_mapa']) ? htmlspecialchars($match_data['Zavod_misto_mapa'], ENT_QUOTES, 'UTF-8') : ''; ?>" target="_blank" rel="noopener">
                        <i class='fas fa-crosshairs text-dark'></i>
                    </a>
                </dd>
                <dt class="col-4 text-end text-start pe-0">Placení:</dt>
                <dd class="col-8 ps-2"><?= ($match_data['Payment_before']) ? 'do ' . $match_data['Zavod_pocet_dni_na_platbu'] . ' dnů od registrace' : 'na místě' ?></dd>
                <dt class="col-4 text-end text-start pe-0">Startovné:</dt>
                <dd class="col-8 ps-2">
                    <?php
                    $mena = $match_data['Banka_ucet_MENA'];
                    [$first, $second, $extra] = [$line[0]['Value'], $line[1]['Value'], $line[2]['Value']];

                    if ($first === $second && $first === $extra) {
                        echo "{$first} {$mena}";
                    } else {
                        echo "<small>";
                        echo "- první disciplína {$first} {$mena}<br>";
                        if ($second === $extra) {
                            echo "- další disciplíny {$second} {$mena}";
                        } else {
                            echo "- druhá disciplína {$second} {$mena}<br>";
                            echo "- další disciplíny {$extra} {$mena}";
                        }
                        echo "</small>";
                    }
                    ?>
                    <br><span class="small text-danger">+100 Kč při registraci na místě</span>
                </dd>
                <dt class="col-12 text-center py-3">
                    <form method="get" action="<?= !empty($match_data['Zavod_propozice']) ? htmlspecialchars($match_data['Zavod_propozice'], ENT_QUOTES, 'UTF-8') : '' ?>">
                        <?= empty($match_data['Zavod_propozice']) ? '<mark class="text-danger small">nenastaveno v administraci</mark>' : '' ?>
                        <button class="btn btn-outline-dark btn-sm" type="submit"><?= $download; ?>&nbsp;&nbsp;STÁHNOUT PROPOZICE
                        </button>
                    </form>
                </dt>
            </dl>
        </div>
    </div>

    <div class="col-md-6">
        <div class="article pb-1 mb-4 <?= hidden($match_data['Zavod_zobrazovat_sponzory'] == 0) ?>">
            <div class="caption mb-2 p-2 ">
                <h3>Sponzoři závodu</h3>
            </div>
            <?= $sponzor ?>
        </div>

        <div class="article">
            <div class="caption mb-2 p-2">
                <h3>Bezpečnost</h3>
            </div>
            <ol class="text-danger pe-2 text-start">
                <li>Jakákoliv manipulace se zbraní mimo stanoviště je zakázaná.</li>
                <li>Zbraň musí být v pouzdru / v zavazadle / vybitá.</li>
                <li>Střelec smí vyjmout zbraň z pouzdra pouze na stanovišti na povel řídícího střelby.</li>
                <li>Po ukončení střelby střelec ukáže čárovému rozhodčímu prázdnou zbraň.</li>
            </ol>
            <p class="text-center fw-bold text-danger pb-2">Jakékoliv porušení zásad bezpečnosti znamená okamžitou DISKVALIFIKACI!!!</p>
        </div>
    </div>
</div>

<div class="row my-3">
    <div class="col-md-6">
        <div class="article">
            <div class="caption mb-2 p-2">
                <h3>Vedení závodu</h3>
            </div>
            <dl class="row  text-start">
                <?php if (!empty($match_data['Zavod_match_director'])): ?>
                    <dt class="col-4 text-end text-start pe-0">Ředitel soutěže:</dt>
                    <dd class="col-8 ps-2"><?= htmlspecialchars($match_data['Zavod_match_director'], ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endif; ?>

                <?php if (!empty($match_data['Zavod_email_poradatel'])): ?>
                    <dt class="col-4 text-end text-start pe-0">E-mail:</dt>
                    <dd class="col-6 ps-2"><?= htmlspecialchars($match_data['Zavod_email_poradatel'], ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endif; ?>

                <?php if (!empty($match_data['Zavod_telefon_poradatel'])): ?>
                    <dt class="col-4 text-end text-start pe-0">Telefon:</dt>
                    <dd class="col-6 ps-2"><?= htmlspecialchars($match_data['Zavod_telefon_poradatel'], ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endif; ?>

                <?php if (!empty($match_data['Zavod_range_master'])): ?>
                    <dt class="col-4 text-end text-start pe-0">Hlavní rozhodčí:</dt>
                    <dd class="col-6 ps-2"><?= htmlspecialchars($match_data['Zavod_range_master'], ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endif; ?>

                <?php if (!empty($match_data['Zavod_email_range_master'])): ?>
                    <dt class="col-4 text-end text-start pe-0">E-mail:</dt>
                    <dd class="col-6 ps-2"><?= htmlspecialchars($match_data['Zavod_email_range_master'], ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endif; ?>

                <?php if (!empty($match_data['Zavod_telefon_range_master'])): ?>
                    <dt class="col-4 text-end text-start pe-0">Telefon:</dt>
                    <dd class="col-6 ps-2"><?= $match_data['Zavod_telefon_range_master']; ?></dd>
                <?php endif; ?>

                <?php if (!empty($match_data['Zavod_stats'])): ?>
                    <dt class="col-4 text-end text-start pe-0">Statistik:</dt>
                    <dd class="col-6 ps-2 "><?= htmlspecialchars($match_data['Zavod_stats'], ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endif; ?>

                <?php if (!empty($match_data['Zavod_email_stats'])): ?>
                    <dt class="col-4 text-end text-start pe-0">E-mail:</dt>
                    <dd class="col-6 ps-2"><?= htmlspecialchars($match_data['Zavod_email_stats'], ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endif; ?>

                <?php if (!empty($match_data['Zavod_telefon_stats'])): ?>
                    <dt class="col-4 text-end text-start pe-0">Telefon:</dt>
                    <dd class="col-6 ps-2"><?= htmlspecialchars($match_data['Zavod_telefon_stats'], ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endif; ?>

                <?php if (!empty($match_data['Zavod_hospodar'])): ?>
                    <dt class="col-4 text-end text-start pe-0">Hospodář:</dt>
                    <dd class="col-6 ps-2"><?= htmlspecialchars($match_data['Zavod_hospodar'], ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endif; ?>

                <?php if (!empty($match_data['Zavod_email_hospodar'])): ?>
                    <dt class="col-4 text-end text-start pe-0">E-mail:</dt>
                    <dd class="col-6 ps-2"><?= htmlspecialchars($match_data['Zavod_email_hospodar'], ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endif; ?>

                <?php if (!empty($match_data['Zavod_telefon_hospodar'])): ?>
                    <dt class="col-4 text-end text-start pe-0">Telefon:</dt>
                    <dd class="col-6 ps-2"><?= htmlspecialchars($match_data['Zavod_telefon_hospodar'], ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endif; ?>
            </dl>
        </div>
    </div>

    <div class="col-md-6">
        <div class="article">
            <div class="caption mb-2 p-2">
                <h3>Časový plán</h3>
            </div>
            <table class="<?= hidden($isRegistracePozastavena) ?> table table-borderless m-2">
                <tr class="<?= hidden($match_data['Squad_prem_max'] == 0); ?>">
                    <td><strong>Prematch</strong></td>
                    <td><?= "$denPrematch " . $datumPrematch->format('j.n.Y') . "" ?></td>
                    <td><?= htmlspecialchars($match_data['Zavod_cas_prematch'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                    <td><strong>Prezentace</strong></td>
                    <td><?= "$denZavod " . $datumZavod->format('j.n.Y') . "" ?></td>
                    <td><?= htmlspecialchars($match_data['Zavod_cas_prezence'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr class=" <?= (empty($match_data['Zavod_cas_main_dopoledne']) or empty($match_data['Zavod_cas_main_odpoledne'])) ? "" : "d-none"; ?>">
                    <td><strong>Závod</strong></td>
                    <td><?= "$denZavod " . $datumZavod->format('j.n.Y') . "" ?></td>
                    <td><?= htmlspecialchars($match_data['Zavod_cas_main'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr class="<?= (empty($match_data['Zavod_cas_main_dopoledne']) or empty($match_data['Zavod_cas_main_odpoledne'])) ? "d-none" : ""; ?>">
                    <td><strong>Závod</strong></td>
                    <td><?= "$denZavod " . $datumZavod->format('j.n.Y') . "" ?></td>
                    <td>dopolední směna: <?php if (!empty($match_data['Zavod_cas_main_dopoledne'])) echo htmlspecialchars($match_data['Zavod_cas_main_dopoledne'], ENT_QUOTES, 'UTF-8'); ?><br>
                        odpolední směna: <?php if (!empty($match_data['Zavod_cas_main_odpoledne'])) echo htmlspecialchars($match_data['Zavod_cas_main_odpoledne'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php
$zavod_disciplines = [];
$result = $conn->query("SELECT * FROM " . $conn->real_escape_string($table_disciplines) . " ORDER BY Id");
while ($row = $result->fetch_assoc()) {
    $zavod_disciplines[$row['Id']] = $row['Value'];
}
?>
<div class="row my-3">
    <div class="col">
        <div class="article">
            <div class="caption mb-2 p-2">
                <h3>Další informace</h3>
            </div>
            <dl class="row  text-start">
                <dt class="col-sm-2 text-sm-end text-start ms-2 <?= (strpos($table, 'k4m') !== false) ? "d-none" : ""; ?>">Disciplíny:</dt>
                <dd class="col-sm-9 text-wrap px-4 <?= (strpos($table, 'k4m') !== false) ? "d-none" : ""; ?>"><?= implode(', ', $zavod_disciplines); ?>
                    <span class="text-danger small"><br>K vyhodnocení disciplíny dojde při počtu 3 a více závodníků</span>
                </dd>
                <dt class="col-sm-2 text-sm-end text-start ms-2">Povinná výbava:</dt>
                <dd class="col-sm-9 px-4">Ochrana sluchu a zraku (sluchátka, brýle)</dd>
                <dt class="col-sm-2 text-sm-end text-start ms-2">Účast:</dt>
                <dd class="col-sm-9 px-4">Držitelé zbrojního oprávnění</dd>
                <dt class="col-sm-2 text-sm-end text-start ms-2">Zbraně a střelivo:</dt>
                <dd class="col-sm-9 px-4">Dle propozic</dd>
                <dt class="col-sm-2 text-sm-end text-start ms-2">Občerstvení:</dt>
                <dd class="col-sm-9 px-4">Zajištěno na střelnici</dd>
                <dt class="col-sm-2 text-sm-end text-start ms-2">Poznámky:</dt>
                <dd class="col-sm-9 px-4">
                    Střelci startují na vlastní náklady nebo na náklady vysílající organizace, na vlastní nebezpečí. Střelnice může vystavit doklad o zaplacení startovného.<br>
                    Střelci jsou odpovědní za způsobenou škodu a újmu.<br>
                    Pořadatel si vyhrazuje právo změn dle vzniklých objektivních situací.</dd>
            </dl>
        </div>
    </div>
</div>

<div class="row my-3">
    <div class="col-md-12">
        <div class="article">
            <div class="caption mb-2 p-2">
                <h3>Pravidla registrace</h3>
            </div>
            <ul class="pb-3 text-start">
                <li>Registrace se uzavírá <?= ($match_data['Zavod_konec_registrace'] == 0) ? 'o půlnoci před registrací' : "$match_data[Zavod_konec_registrace] den/dny před konáním závodu" ?>.</li>
                <li>Pořadatelé si vyhrazují právo zařadit závodníků do jednotlivých směn za účelem zajištění hladkého průběhu závodu.</li>
                <li>Nezadá-li závodník při registraci platný e-mail, vystavuje se riziku, že nebude informován o případných změnách závodu.</li>
            </ul>
        </div>
    </div>
</div>

<div class="row my-3 <?= hidden($match_data['Payment_before'] == 0); ?>">
    <div class="col-md-6">
        <div class="article">
            <div class="caption mb-2 p-2">
                <H3>Úhrada startovného</H3>
            </div>
            <ul class="pb-3 text-start">
                <li>Startovné uhraďte tak, aby platba proběhla do <?= $match_data['Zavod_pocet_dni_na_platbu']; ?> dnů od registrace.<br>
                    - <span class="text-danger">u závodníků zaregistrovaných méně jak <?= $match_data['Zavod_pocet_dni_na_platbu']; ?> dní před závodem je třeba startovné zaplatit <strong>nejpozději jeden den před závodem</strong></span>
                <li>V případě neuhrazení startovného v řádném termínu je registrace zrušena.<br>
                    <i>- neplatí pro organizátory, pomocníky a rozhodčí</i>
                <li><strong>Startovné je nevratné, lze jej přenést na jiného závodníka.</strong></li>
                <li>Při hromadné registraci platí závodník celkovou částku za všechny zaregistrované disciplíny.</li>
                <li>Platíte-li za více závodníků, uveďte pouze jedno číslo (variabilní symbol) a o platbě informujte pořadatele <a href='mailto:<?= $match_data['Zavod_email_from'] ?>'>e-mailem</a>.</b></i>
                <li><strong>Při platbě startovaného předem není registrace na místě možná.</strong></li>
            </ul>
        </div>
    </div>

    <div class="col-md-6">
        <div class="article">
            <div class="caption mb-2 p-2">
                <H3>Platební údaje</H3>
            </div>
            <p class="font-monospace px-3  text-start">Číslo účtu: <?= "$match_data[Banka_ucet_cislo]/$match_data[Banka_ucet_kod]" ?><br>
                Jméno příjemce: <?= $match_data['Zavod_poradatel'] ?><br>
                Adresa příjemce: <?= $match_data['Zavod_poradatel_adresa'] ?>
            <p class="font-monospace px-3 pb-3">Banka: <?= $match_data['Banka_nazev'] ?><br>
                Adresa banky: <?= $match_data['Banka_adresa'] ?></p>
        </div>
    </div>
</div>

<div class="row my-3">
    <div class="col">
        <div class="article">
            <div class="accordion accordion-flush" id="accordionFlushExample">
                <div class="accordion-item">
                    <div class="accordion-header">
                        <button class="caption mb-2 p-2 accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                            <h3>Souhlas se zpracováním osobních údajů</h3>
                        </button>
                    </div>
                    <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <ol>
                                <li>Udělujete tímto dobrovolně souhlas pořadateli <?= !empty($match_data['Zavod_poradatel']) ? htmlspecialchars($match_data['Zavod_poradatel'], ENT_QUOTES, 'UTF-8') : '<mark class="text-danger small">nenastaveno v administraci</mark>' ?> (dále jen "Správce"), aby ve smyslu zákona č.101/2000 Sb., o ochraně osobních údajů (dále jen "zákon o ochraně osobních údajů") zpracovávala tyto osobní údaje:
                                    <ul>
                                        <li>jméno a příjmení</li>
                                        <li>datum narození</li>
                                        <li>e-mailová adresa</li>
                                        <li>telefonní číslo</li>
                                        <li>fotografie a videa z průběhu akce</li>
                                    </ul>
                                </li>
                                <li>Jméno, příjmení, datum narození, telefonní číslo, e-mailovou adresu a fotografie a videa z průběhu akce je nutné zpracovat:
                                    <ol type=a>
                                        <li>za účelem registrace, evidence a vyhodnocení závodů který organizuje Správce.</li>
                                        <li>pro marketingové účely Správce, tj. zejména zveřejňování informací o průběžné činnosti Správce.</li>
                                    </ol>
                                    <br>Tyto údaje budou Správcem zpracovávány po dobu 5 let ode dne udělení souhlasu.<br><br>
                                </li>
                                <li>S výše uvedeným zpracováním udělujete svůj výslovný souhlas a prohlašujete, že poskytnuté osobní údaje jsou pravdivé. Souhlas lze vzít kdykoliv zpět, zasláním e-mailu nebo dopisu Správci.</li><br>
                                <li>Osobní údaje bude Správce zpracovávat manuálně nebo automaticky prostřednictvím svých zaměstnanců nebo dalších pořadatelů pověřených Správcem. Pro Správce mohou data zpracovávat případně i další poskytovatelé zpracovatelských softwarů, služeb a aplikací, které však v současné době Správce nevyužívá.</li><br>
                                <li>Vezměte, prosím, na vědomí, že podle zákona o ochraně osobních údajů máte právo:
                                    <ul>
                                        <li>vzít váš souhlas kdykoliv zpět,</li>
                                        <li>požadovat po nás informaci, jaké vaše osobní údaje zpracováváme,</li>
                                        <li>požadovat po nás vysvětlení ohledně zpracování osobních údajů,</li>
                                        <li>vyžádat si u nás přístup k těmto údajům a tyto nechat aktualizovat nebo opravit,</li>
                                        <li>požadovat po nás výmaz těchto osobních údajů,</li>
                                        <li>v případě pochybností o dodržování povinností souvisejících se zpracováním osobních údajů obrátit se na nás nebo na Úřad pro ochranu osobních údajů.</li>
                                    </ul>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
<?php include "footer.php"; ?>