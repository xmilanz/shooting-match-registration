<?php
$konec_registrace_text = ($match_data['Zavod_konec_registrace'] == 0) ? 'o půlnoci před registrací' : $match_data['Zavod_konec_registrace'] . ' den/dny před konáním závodu.';

$volitelnaPlatba = "";

if ($match_data['Zavod_platba_volitelna'] == 1) {
    $volitelnaPlatba = <<<HTML

Pro případ, že se rozhodnete startovné zaplatit předem, posíláme platební údaje:
&nbsp;- účet: {$match_data['Banka_ucet_cislo']}/{$match_data['Banka_ucet_kod']}
&nbsp;- variabilní symbol: ##VAR_SYMBOL##
&nbsp;- částka: ##CASTKA## {$match_data['Banka_ucet_MENA']}
QR kód pro platbu v Kč:
<a href='##QR_LINK##'><img src='##QR_LINK##' /></a>

HTML;
}

$email_registrace_zavod_bez_platby_predem="Dobrý den,

pořadatel závodu vás zaregistroval na závod <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
Datum závodu: ".$match_data['Zavod_datum'].".

##STRELEC##
V souladu s pravidly registrace se startovné ##CASTKA## ". $match_data['Banka_ucet_MENA'] . " platí v hotovosti před závodem na místě.
{$volitelnaPlatba}
Nemůžete-li se z nějakých důvodů zúčastnit závodu, neprodleně nás informujte v odpovědi na tento e-mail nebo kliněte na odkaz <strong>\"zrušit účast\"</strong> vedle svého jména. Uvolníte tak místo dalším zájemcům.

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";

$email_registrace_zavod_bez_platby_predem_text="Dobrý den,

znovu Vám posíláme registrační e-mail závodu <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
Datum závodu: ".$match_data['Zavod_datum'].".

##STRELEC##
V souladu s pravidly registrace se startovné ##CASTKA## ". $match_data['Banka_ucet_MENA'] . " platí v hotovosti před závodem na místě.
{$volitelnaPlatba}
Nemůžete-li se z nějakých důvodů zúčastnit závodu, neprodleně nás informujte v odpovědi na tento e-mail nebo kliněte na odkaz <strong>\"zrušit účast\"</strong> vedle svého jména. Uvolníte tak místo dalším zájemcům.

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";

$email_hromadna_registrace_zavod_bez_platby_predem_text="Dobrý den,

znovu Vám posíláme registrační e-mail závodu <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
Datum závodu: ".$match_data['Zavod_datum'].".

##STRELEC##
V souladu s pravidly registrace se startovné ##CASTKA## " . $match_data['Banka_ucet_MENA'] . " (souhrnná částka za zaregistrované disciplíny) platí v hotovosti před závodem na místě.
{$volitelnaPlatba}
Nemůžete-li se z nějakých důvodů zúčastnit závodu, neprodleně nás informujte v odpovědi na tento e-mail nebo kliněte na odkaz <strong>\"zrušit účast\"</strong> vedle svého jména. Uvolníte tak místo dalším zájemcům.

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";


$email_text_vyrazeni="Dobrý den,

pořadatel závodu vás vyřadil ze závodu <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").

<strong>Vaše registrace byla zrušena.</strong>

##STRELEC##
S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
";

$email_registrace_platba_text_novy_zavodnik="Dobrý den,

pořadatel závodu vás zaregistroval na závod <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
Datum závodu: ".$match_data['Zavod_datum'].".

##STRELEC##
V souladu s pravidly registrace Vás žádáme o úhradu startovného:
&nbsp;- účet: ".$match_data['Banka_ucet_cislo']."/".$match_data['Banka_ucet_kod']."
&nbsp;- variabilní symbol: ##VAR_SYMBOL##
&nbsp;- částka: ##CASTKA## ". $match_data['Banka_ucet_MENA'] . "  

QR kód pro platbu v Kč:
<a href='##QR_LINK##'><img src='##QR_LINK##' /></a>

Potvrďte svojí účast v závodu zaplacením startovného. <strong>Registrace bez platby je platná do <span style=\"color:#ff0000;\">##DatPay##</span>.</strong> Po tomto termínu bude vaše registrace automaticky zrušena.

Nemůžete-li se z nějakých důvodů zúčastnit závodu, neprodleně nás informujte v odpovědi na tento e-mail nebo kliněte na odkaz <strong>\"zrušit účast\"</strong> vedle svého jména. Po připsání platby na účet již není možné startovné vrátit, v souladu s pravidly závodu je však možné jej přenést na jiného závodníka.

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Po připsání platby na účet již nelze startovné vrátit, v souladu s pravidly závodu je však možné jej přenést na jiného závodníka, <strong>nejpozději však den před závodem (emailem nebo telefonem).</strong>
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";

$email_registrace_bez_platby_text_novy_zavodnik="Dobrý den,

pořadatel závodu vás zaregistroval na závod <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
Datum závodu: ".$match_data['Zavod_datum'].".

##STRELEC##
Protože pomáháte při závodu nebo se jakýmkoliv jiným způsobem účastníte jeho organizace, nemusíte platit startovné :). 

Nemůžete-li se z nějakých důvodů zúčastnit závodu, neprodleně nás informujte v odpovědi na tento e-mail nebo kliněte na odkaz <strong>\"zrušit účast\"</strong> vedle svého jména. Uvolníte tak místo dalším zájemcům.

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";

$email_registrace_platba_na_miste_novy_zavodnik="Dobrý den,

pořadatel závodu vás zaregistroval na závod <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
Datum závodu: ".$match_data['Zavod_datum'].".

##STRELEC##
Protože jste se s pořadatelem domluvil(a) na <strong>platbě na místě</strong>, nemusíte platit startovné ##CASTKA## ".$match_data['Banka_ucet_MENA']." před závodem.

Nemůžete-li se z nějakých důvodů zúčastnit závodu, neprodleně nás informujte v odpovědi na tento e-mail nebo kliněte na odkaz <strong>\"zrušit účast\"</strong> vedle svého jména. Uvolníte tak místo dalším zájemcům.

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";

$email_registrace_platba_text="Dobrý den,

znovu Vám posíláme registrační e-mail závodu <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
Datum závodu: ".$match_data['Zavod_datum'].".

##STRELEC##
V souladu s pravidly registrace Vás žádáme o úhradu startovného:
&nbsp;- účet: ".$match_data['Banka_ucet_cislo']."/".$match_data['Banka_ucet_kod']."
&nbsp;- variabilní symbol: ##VAR_SYMBOL##
&nbsp;- částka: ##CASTKA## ".$match_data['Banka_ucet_MENA']."  

QR kód pro platbu v Kč:
<a href='##QR_LINK##'><img src='##QR_LINK##' /></a>

Potvrďte svojí účast v závodu zaplacením startovného. <strong>Registrace bez platby je platná do <span style=\"color:#ff0000;\">##DatPay##</span>.</strong> Po tomto termínu bude vaše registrace automaticky zrušena.

Nemůžete-li se z nějakých důvodů zúčastnit závodu, neprodleně nás informujte v odpovědi na tento e-mail nebo kliněte na odkaz <strong>\"zrušit účast\"</strong> vedle svého jména. Po připsání platby na účet již není možné startovné vrátit, v souladu s pravidly závodu je však možné jej přenést na jiného závodníka.

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Po připsání platby na účet již nelze startovné vrátit, v souladu s pravidly závodu je však možné jej přenést na jiného závodníka, <strong>nejpozději však den před závodem (emailem nebo telefonem).</strong>
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";

$email_registrace_bez_platby_text="Dobrý den,

znovu Vám posíláme registrační e-mail závodu <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
Datum závodu: ".$match_data['Zavod_datum'].".

##STRELEC##
Protože pomáháte při závodu nebo se jakýmkoliv jiným způsobem účastníte jeho organizace, nemusíte platit startovné :). 

Nemůžete-li se z nějakých důvodů zúčastnit závodu, neprodleně nás informujte v odpovědi na tento e-mail nebo kliněte na odkaz <strong>\"zrušit účast\"</strong> vedle svého jména. Uvolníte tak místo dalším zájemcům.

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";

$email_registrace_platba_na_miste="Dobrý den,

znovu Vám posíláme registrační e-mail závodu <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
Datum závodu: ".$match_data['Zavod_datum'].".

##STRELEC##
Protože jste se s pořadatelem domluvil(a) na <strong>platbě na místě</strong>, nemusíte platit startovné ##CASTKA## ".$match_data['Banka_ucet_MENA']."  před závodem.
{$volitelnaPlatba}
Nemůžete-li se z nějakých důvodů zúčastnit závodu, neprodleně nás informujte v odpovědi na tento e-mail nebo kliněte na odkaz <strong>\"zrušit účast\"</strong> vedle svého jména. Uvolníte tak místo dalším zájemcům.

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";

$email_urgence_platba_text="Dobrý den,

dne ##DatReg## jste se zaregistroval(a) na závod <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").

##STRELEC##
Do dnešního dne nedošlo k úhradě platby za registraci na výše uvedený závod. <strong><span style=\"color:#ff0000;\">V souladu s pravidly registrace vyprší ##DatPay##.</span></strong> Pokud jste již platbu provedli, prosíme o zaslání potvrzení. 

Nedojde-li v nejbližší době k úhradě, bude vaše registrace vyřazena z aktuálního squadu a nahrazena jiným zájemcem z řad čekatelů. <strong>Vaše účast v závodě poté není garantována!</strong> 

Údaje pro platbu
&nbsp;- účet: ".$match_data['Banka_ucet_cislo']."/".$match_data['Banka_ucet_kod']."
&nbsp;- variabilní symbol: ##VAR_SYMBOL##
&nbsp;- částka: ##CASTKA## ".$match_data['Banka_ucet_MENA']."  

QR kód pro platbu v Kč:
<a href='##QR_LINK##'><img src='##QR_LINK##' /></a>

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Po připsání platby na účet již nelze startovné vrátit, v souladu s pravidly závodu je však možné jej přenést na jiného závodníka, <strong>nejpozději však den před závodem (emailem nebo telefonem).</strong>
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";

$email_hromadna_registrace_platba_text="Dobrý den,

znovu Vám posíláme registrační e-mail závodu <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
Datum závodu: ".$match_data['Zavod_datum'].".

##STRELEC##
V souladu s pravidly registrace Vás žádáme o úhradu startovného:
&nbsp;- účet: ".$match_data['Banka_ucet_cislo']."/".$match_data['Banka_ucet_kod']."
&nbsp;- variabilní symbol: ##VAR_SYMBOL##
&nbsp;- souhrnná částka za zaregistrované disciplíny: ##CASTKA## ".$match_data['Banka_ucet_MENA']."  

QR kód pro platbu za všechny registrované disciplíny v Kč:
<a href='##QR_LINK##'><img src='##QR_LINK##' /></a>

Potvrďte svojí účast v závodu zaplacením startovného. <strong>Registrace bez platby je platná do <span style=\"color:#ff0000;\">##DatPay##</span>.</strong> Po tomto termínu bude vaše registrace automaticky zrušena.

Nemůžete-li se z nějakých důvodů zúčastnit závodu, neprodleně nás informujte v odpovědi na tento e-mail nebo kliněte na odkaz <strong>\"zrušit účast\"</strong> vedle svého jména. Po připsání platby na účet již není možné startovné vrátit, v souladu s pravidly závodu je však možné jej přenést na jiného závodníka.

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Po připsání platby na účet již nelze startovné vrátit, v souladu s pravidly závodu je však možné jej přenést na jiného závodníka, <strong>nejpozději však den před závodem (emailem nebo telefonem).</strong>
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";


$email_urgence_hromadna_registrace_platba_text="Dobrý den,

dne ##DatReg## jste se zaregistroval(a) do více disciplín na závod <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").

##STRELEC##
Do dnešního dne nedošlo k úhradě platby za registraci na výše uvedený závod. <strong><span style=\"color:#ff0000;\">V souladu s pravidly registrace vyprší ##DatPay##.</span></strong> Pokud jste již platbu provedli, prosíme o zaslání potvrzení. 

Nedojde-li v nejbližší době k úhradě, bude vaše registrace vyřazena z aktuálního squadu a nahrazena jiným zájemcem z řad čekatelů. <strong>Vaše účast v závodě poté není garantována!</strong> 

Údaje pro hromadnou platbu
&nbsp;- účet: ".$match_data['Banka_ucet_cislo']."/".$match_data['Banka_ucet_kod']."
&nbsp;- variabilní symbol: ##VAR_SYMBOL##
&nbsp;- souhrnná částka za zaregistrované disciplíny: ##CASTKA## ".$match_data['Banka_ucet_MENA']."  

QR kód pro platbu za všechny registrované disciplíny v Kč:
<a href='##QR_LINK##'><img src='##QR_LINK##' /></a>

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Po připsání platby na účet již nelze startovné vrátit, v souladu s pravidly závodu je však možné jej přenést na jiného závodníka, <strong>nejpozději však den před závodem (emailem nebo telefonem).</strong>
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";


$email_text_platba="Dobrý den,

zaevidovali jsme úhradu startovného závodu <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . "). 

##STRELEC##
Těšíme se na brzkou viděnou na závodě.

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Po připsání platby na účet již nelze startovné vrátit, v souladu s pravidly závodu je však možné jej přenést na jiného závodníka, <strong>nejpozději však den před závodem (emailem nebo telefonem).</strong>
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";

$email_text_hromadna_platba="Dobrý den,

zaevidovali jsme platbu ##CASTKA## " . $match_data['Banka_ucet_MENA'] . " za startovné do více disciplín závodu <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . "). 

##STRELEC##
Těšíme se na brzkou viděnou na závodě.

Další informace o závodu najdete na adrese <a href='$web_adresa_admin'>$web_adresa_admin</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Po připsání platby na účet již nelze startovné vrátit, v souladu s pravidly závodu je však možné jej přenést na jiného závodníka, <strong>nejpozději však den před závodem (emailem nebo telefonem).</strong>
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";


$email_novy_uzivatel="Dobrý den,

správce registračního systému vytvořil uživatelský účet pro přístup do administrace soutěží <strong>SSAŠ střelnice Prachatice</strong>. Pro přihlášení použijte poslední položku v menu závodu (ikonka 'uživatel').

##UZIVATEL##

<strong><span style=\"color:#ff0000;\">Pro první přihlášení je vynucená zmena hesla.</span></strong> 

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
";



?>