<?php
$konec_registrace_text = ($match_data['Zavod_konec_registrace'] == 0) ? 'o půlnoci před registrací' : $match_data['Zavod_konec_registrace'] . ' den/dny před konáním závodu.';

$volitelnaPlatba = "";

if ($match_data['Zavod_platba_volitelna'] === 'on') {
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

zaregistroval(a) jste se na závod <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
Datum závodu: ".$match_data['Zavod_datum'].".

##STRELEC##
V souladu s pravidly registrace se startovné ##CASTKA## ". $match_data['Banka_ucet_MENA'] . " platí v hotovosti před závodem na místě.
{$volitelnaPlatba}
Nemůžete-li se z nějakých důvodů zúčastnit závodu, neprodleně nás informujte v odpovědi na tento e-mail nebo kliněte na odkaz <strong>\"zrušit účast\"</strong> vedle svého jména. Uvolníte tak místo dalším zájemcům.

Další informace o závodu najdete na adrese <a href='$reg_url'>$reg_url</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";

$email_hromadna_registrace_zavod_bez_platby_predem="Dobrý den,

zaregistroval(a) jste se do více disciplín na závod <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
Datum závodu: ".$match_data['Zavod_datum'].".

##STRELEC##
V souladu s pravidly registrace se startovné ##CASTKA## " . $match_data['Banka_ucet_MENA'] . " (souhrnná částka za zaregistrované disciplíny) platí v hotovosti před závodem na místě.
{$volitelnaPlatba}
Nemůžete-li se z nějakých důvodů zúčastnit závodu, neprodleně nás informujte v odpovědi na tento e-mail nebo kliněte na odkaz <strong>\"zrušit účast\"</strong> vedle svého jména. Uvolníte tak místo dalším zájemcům.

Další informace o závodu najdete na adrese <a href='$reg_url'>$reg_url</a>

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

zaregistroval(a) jste se na závod <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
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

Další informace o závodu najdete na adrese <a href='$$reg_url'>$$reg_url/</a>

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

zaregistroval(a) jste se do více disciplín na závod <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
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

Další informace o závodu najdete na adrese <a href='$reg_url'>$reg_url</a>

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


$email_text_vyrazeni_vlastni="Dobrý den,

prostřednictvím odkazu z potvrzovacího emailu jste se vyřadil(a) ze závodu <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . "). Pokud jste tuto akci neprovedli, neprodleně nás kontaktujte v odpovědi na tento e-mail.

<strong>Vaše registrace byla zrušena.</strong> 

##STRELEC##
S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
";


$email_registrace_bez_platby_text="Dobrý den,

zaregistroval(a) jste se na závod <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . ").
Datum závodu: ".$match_data['Zavod_datum'].".

##STRELEC##
Protože pomáháte při závodu nebo se jakýmkoliv jiným způsobem účastníte jeho organizace, nemusíte platit startovné :).

Nemůžete-li se z nějakých důvodů zúčastnit závodu, neprodleně nás informujte v odpovědi na tento e-mail nebo kliněte na odkaz <strong>\"zrušit účast\"</strong> vedle svého jména. Uvolníte tak místo dalším zájemcům.

Další informace o závodu najdete na adrese <a href='$$reg_url'>$$reg_url/</a>

S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel registrace
Registrace se uzavírá ". $konec_registrace_text .".
Pořadatelé si vyhrazují právo zařadit závodníky do jednotlivých směn za účelem zajištění hladkého průběhu závodu.
</i></small>
";


$email_text_vyrazeni_automaticke="Dobrý den,

protože nedošlo k zaplacení registračního poplatku v řádném termínu, byl(a) jste vyřazen(a) ze závodu <strong>". htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') . "). Pokud jste již platbu provedli, prosíme o zaslání potvrzení. 

<strong>Vaše registrace byla zrušena.</strong>

##STRELEC##
S pozdravem
<strong>".$match_data['Zavod_poradatel']."</strong>
<i><small>
------
Výpis z pravidel pro úhradu startovaného
Startovné uhraďte tak, aby platba proběhla do 10 dnů od registrace.
U závodníků zaregistrovaných méně jak 10 dní před závodem je třeba startovné zaplatit nejpozději jeden den před prematchem.
V případě neuhrazení startovného v řádném termínu bude Vaše registrace zrušena (neplatí pro organizátory, pomocníky a rozhodčí).
</i></small>
";

?>