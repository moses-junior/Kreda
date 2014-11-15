<?php
//  This file is part of Kreda.
//
//  Kreda is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  Kreda is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero Public License for more details.
//
//  You should have received a copy of the GNU Affero Public License
//  along with Kreda.  If not, see <http://www.gnu.org/licenses/>.
//
//  Diese Datei ist Teil von Kreda.
//
//  Kreda ist Freie Software: Sie können es unter den Bedingungen
//  der GNU Affero Public License, wie von der Free Software Foundation,
//  Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
//  veröffentlichten Version, weiterverbreiten und/oder modifizieren.
//
//  Kreda wird in der Hoffnung, dass es nützlich sein wird, aber
//  OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
//  Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
//  Siehe die GNU Affero Public License für weitere Details.
//
//  Sie sollten eine Kopie der GNU Affero Public License zusammen mit diesem
//  Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.

session_start();
$pfad="../";
include($pfad."funktionen.php");

$my_user=new user();

// gefixt: ein Test verlief dann doch erfolgreich (vorheriger Bug: ohne angehängten Test können keine Einzelpunkte eingetragen werden -> trägt erste Note ein und bricht dann ab)

if ($_GET["hinzufuegen"]=="true") {
	$id=injaway($_GET["beschreibung"]);
	if (!proofuser("notenbeschreibung", $id))
		die("Sie sind hierzu nicht berechtigt.");
	
	// Falls ein YubiKey beim Benutzer eingetragen ist, soll dieser hier ueberprueft werden
	if ($my_user->my["token_id"]!="") {
		$otp=$_POST["token_otp"];
		
		if (substr ($otp, 0, 12) == $my_user->my["token_id"]) {
			require_once $pfad."login/yubikeyPHPclass/Yubikey.php";
			
			$token = new Yubikey(YUBIKEY_API_ID, YUBIKEY_SIGNATURE_KEY);
			
			$token->setCurlTimeout(20);
			$token->setTimestampTolerance(500);
			
			if (!$token->verify($otp))
				die("Yubikey-&Uuml;berpr&uuml;fung ist fehlgeschlagen: ".$token->getLastResponse().'<br />Sollte das ein Fehler sein, versuchen Sie es <a href="javascript:history.back()">erneut</a>.');
		}
		else
			die("Der YubiKey geh&ouml;rt Ihnen nicht.");
	}
	
	db_conn_and_sql("UPDATE `notenbeschreibung` SET `beschreibung`=".apostroph_bei_bedarf($_POST["beschreibung"]).", `kommentar`=".apostroph_bei_bedarf($_POST["kommentar"]).", `datum`='".datum_punkt_zu_strich($_POST["datum"])."', `korrigiert`=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["korrigiert"])).", `zurueckgegeben`=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["zurueckgegeben"])).", `halbjahresnote`=".($_POST["halbjahresnote"]+0).", `notentyp`=".injaway($_POST["notentyp"]).", `gesamtpunktzahl`=".leer_NULL($_POST["gesamtpunktzahl"]).", `berichtigung`=".leer_NULL($_POST["berichtigung"]).", `unterschrift`=".leer_NULL($_POST["unterschrift"]).", `mitzaehlen`=".injaway($_POST["mitzaehlen"]).", `notenspiegel_zeigen`=".leer_NULL($_POST["notenspiegel_zeigen"]).", `bewertungstabelle`=".leer_NULL($_POST["bewertungstabelle"])." WHERE `id`=".$id);
	// Planzuordnung -> Datum weg
	if ($_POST["plan"]!="")
		db_conn_and_sql("UPDATE `notenbeschreibung` SET `datum`=NULL, `plan`=".leer_NULL($_POST["plan"])." WHERE `id`=".$id);
	
	//ist Test im Plan?
	$test_da=db_conn_and_sql("SELECT *, IF( `test`.`url` IS NULL , `aufgabe`.`punkte` , `test`.`punkte` ) AS `punkte_add`
		FROM `notenbeschreibung`
			LEFT JOIN `test` ON `notenbeschreibung`.`test`=`test`.`id`
			LEFT JOIN `test_aufgabe` ON `test_aufgabe`.`test`=`test`.`id`
			LEFT JOIN `aufgabe` ON `test_aufgabe`.`aufgabe`=`aufgabe`.`id`
		WHERE `notenbeschreibung`.`id`=".$id."
		ORDER BY `test_aufgabe`.`position`");
	$gruppe_a_zaehler='';
	$gruppe_b_zaehler='';
	//Gesamtpunktzahl eintragen
	if(sql_num_rows($test_da)>0 and @sql_result($test_da,0,"punkte_add")>0) {
		$punkte_a=0; $punkte_b=0;
		for ($k=0;$k<sql_num_rows($test_da);$k++) {
			if (sql_result($test_da,$k,"test_aufgabe.position")>0)
				$gruppe_a_zaehler[]=array("zaehler"=>$k, "position"=>sql_result($test_da,$k,"test_aufgabe.position"));
			if (sql_result($test_da,$k,"test_aufgabe.position_b")>0)
				$gruppe_b_zaehler[]=array("zaehler"=>$k, "position"=>sql_result($test_da,$k,"test_aufgabe.position_b"));
			
			if (sql_result($test_da,$k,"test_aufgabe.zusatzaufgabe")!=1) {
				if (sql_result($test_da,$k,"test_aufgabe.position")>0)
					$punkte_a+=sql_result($test_da,$k,"punkte_add");
				if (sql_result($test_da,$k,"test_aufgabe.position_b")>0)
					$punkte_b+=sql_result($test_da,$k,"punkte_add");
			}
		}
		db_conn_and_sql("UPDATE `notenbeschreibung` SET `gesamtpunktzahl`=".$punkte_a." WHERE `id`=".$id);
		//gruppe B Aufgaben sortieren
		for ($i=0;$i<(count($gruppe_b_zaehler)-1);$i++)
			for ($k=$i+1;$k<count($gruppe_b_zaehler);$k++)
				if ($gruppe_b_zaehler[$i]["position"]>$gruppe_b_zaehler[$k]["position"]) {
					$hilf=$gruppe_b_zaehler[$i];
					$gruppe_b_zaehler[$i]=$gruppe_b_zaehler[$k];
					$gruppe_b_zaehler[$k]=$hilf;
				}
	}
			
	$zusatzinfos=db_conn_and_sql("SELECT * FROM `notenbeschreibung`,`fach_klasse`,`klasse` WHERE `notenbeschreibung`.`fach_klasse`=`fach_klasse`.`id` AND `fach_klasse`.`klasse`=`klasse`.`id` AND `notenbeschreibung`.`id`=".$id);
	//Einzelne Noten
	$i=0;
	while (isset($_POST["schueler_".$i])) {
		// auseinanderzerren des select-inputs
		$_POST["zusatz_".$i] = "0";
		if (substr($_POST["wert_".$i], -1) == "+")
			$_POST["zusatz_".$i] = "1";
		if (substr($_POST["wert_".$i], -1) == "-")
			$_POST["zusatz_".$i] = "-1";
		if ($_POST["zusatz_".$i] != "0")
			$_POST["wert_".$i] = substr($_POST["wert_".$i], 0, strlen($_POST["wert_".$i])-2);
			
		if ($_POST["speichern_".$i]=="true") {
			$result=db_conn_and_sql("SELECT * FROM `noten` WHERE `schueler`=".injaway($_POST["schueler_".$i])." AND `beschreibung`=".$id);
			$noten_id=@sql_result($result,0,"noten.id");
			if (sql_num_rows($result)>0)
				db_conn_and_sql("UPDATE `noten` SET `kommentar`=".apostroph_bei_bedarf($_POST["kommentar_".$i]).", `zusatzpunkte`=".($_POST["zusatzpunkte_".$i]+0).", `halbjahresnote`=".($_POST["halbjahresnote_".$i]+0).", `datum`='".datum_punkt_zu_strich($_POST["datum_".$i])."', `wert`=".leer_NULL($_POST["wert_".$i]).", `zusatz`=".leer_NULL($_POST["zusatz_".$i])." WHERE `id`=".$noten_id);
			else {
				if ($_POST["datum_".$i]!="")
					$noten_id=db_conn_and_sql("INSERT INTO `noten` (`beschreibung`, `schueler`, `kommentar`,`zusatzpunkte`,`halbjahresnote`,`datum`,`wert`,`zusatz`)
						VALUES (".$id.", ".injaway($_POST["schueler_".$i]).", ".apostroph_bei_bedarf($_POST["kommentar_".$i]).", ".($_POST["zusatzpunkte_".$i]+0).", ".($_POST["halbjahresnote_".$i]+0).", '".datum_punkt_zu_strich($_POST["datum_".$i])."', ".leer_NULL($_POST["wert_".$i]).", ".leer_NULL($_POST["zusatz_".$i]).");");
			}
			
			// in Bewertung mit einbeziehen?
			if ($_POST["mitzaehlen"]==-1)
				db_conn_and_sql("UPDATE `noten` SET `mitzaehlen`=".($_POST["mitzaehlen_".$i]+0)." WHERE `id`=".$noten_id);
			
			db_conn_and_sql("DELETE FROM `note_aufgabe` WHERE `note`=".$noten_id." AND `schueler`=".$_POST["schueler_".$i]);
			
			// Punkte eintragen
			$ids = explode("/",$_POST["punkte_".$i]);
			for ($n=0;$n<count($ids);$n++)
				$ids[$n]=str_replace(",", ".",$ids[$n]);
			
			$testpunkte=0;
			$gesamtpunktzahl_gruppe=sql_result($zusatzinfos,0,"notenbeschreibung.gesamtpunktzahl");
			// wenn dort nichts eingetragen ist, dann von angehaengtem Test holen
			if ($gesamtpunktzahl_gruppe<1)
                $gesamtpunktzahl_gruppe=sql_result($test_da,0,"test.punkte");
			
			if (count($ids)>1 or (sql_num_rows($test_da)==1 and sql_result($test_da,0,"aufgabe.punkte")>1))
				for($j=0;$j<count($ids);$j++) {
					if($ids[$j]!="-")
						$testpunkte+=$ids[$j];
					$gruppen_zaehler=0;
					if ($_POST["gruppe_b_".$i]==1)
						$gruppen_zaehler=$gruppe_b_zaehler[$j]["zaehler"];
					else {
						if (count($gruppe_a_zaehler)>1)
							$gruppen_zaehler=$gruppe_a_zaehler[$j]["zaehler"];
						else $gruppen_zaehler=0;
					}
					if ($_POST["gruppe_b_".$i]==1)
						$gesamtpunktzahl_gruppe=$punkte_b;
					else $gesamtpunktzahl_gruppe=$punkte_a;
					
					// Abfrage eigentlich nicht mehr noetig, seit alle note_aufgabe geloescht werden
					/*$note_aufgabe_da=db_conn_and_sql("SELECT * FROM `note_aufgabe` WHERE `note`=".$noten_id." AND `aufgabe`=".sql_result($test_da,$gruppen_zaehler,"aufgabe.id"));
					if (@sql_num_rows($note_aufgabe_da)>0) {
						if ($ids[$j]!="-") db_conn_and_sql("UPDATE `note_aufgabe`
							SET `punkte`=".$ids[$j].", `test`=".sql_result($test_da,$gruppen_zaehler,"test.id").", `klassenstufe`=".($_GET["schuljahr"]-sql_result($zusatzinfos,0,"klasse.einschuljahr")+1).", `schulart`=".sql_result($zusatzinfos,0,"klasse.schulart").", `notentyp`=".sql_result($zusatzinfos,0,"notenbeschreibung.notentyp").", `schueler`=".$_POST["schueler_".$i].", `aufgabenpunkte`=".sql_result($test_da,$gruppen_zaehler,"aufgabe.punkte")."
							WHERE `note`=".$noten_id." AND `aufgabe`=".sql_result($test_da,$gruppen_zaehler,"aufgabe.id"));
					}
					else*/ if ($ids[$j]!="-" and sql_result($test_da,$gruppen_zaehler,"test.id")>0)
						db_conn_and_sql("INSERT INTO `note_aufgabe` (`note`,`aufgabe`,`punkte`,`test`,`klassenstufe`,`schulart`,`notentyp`,`schueler`,`aufgabenpunkte`)
							VALUES (".$noten_id.", ".sql_result($test_da,$gruppen_zaehler,"aufgabe.id").", ".$ids[$j].", ".sql_result($test_da,$gruppen_zaehler,"test.id").", ".(injaway($_GET["schuljahr"])-sql_result($zusatzinfos,0,"klasse.einschuljahr")+1).", ".sql_result($zusatzinfos,0,"klasse.schulart").", ".sql_result($zusatzinfos,0,"notenbeschreibung.notentyp").", ".injaway($_POST["schueler_".$i]).", ".sql_result($test_da,$gruppen_zaehler,"aufgabe.punkte").")");
				}
			
			if ($_POST["punkte_".$i]!="") {
				if (count($ids)<=1) {
					$testpunkte=$ids[0];
					db_conn_and_sql("UPDATE `noten` SET `punkte`=".$testpunkte." WHERE `id`=".$noten_id);
				}
				
				// Falls kein Test zugeordnet wurde und die Punkte einzeln angegeben wurden
				if ($gesamtpunktzahl_gruppe<1)
					$gesamtpunktzahl_gruppe=$_POST["gesamtpunktzahl"];
				
				$berechnung=db_conn_and_sql("SELECT *
					FROM `notenbeschreibung`,`bewertungstabelle`,`bewertung_note`
					WHERE `notenbeschreibung`.`bewertungstabelle`=`bewertungstabelle`.`id`
						AND `notenbeschreibung`.`id`=".$id."
						AND `bewertungstabelle`.`id`=`bewertung_note`.`bewertungstabelle`
					ORDER BY `bewertung_note`.`prozent_bis`");
				for ($j=0;$j<sql_num_rows($berechnung);$j++) {
					$berechnete_punkte=$testpunkte+str_replace(",", ".",$_POST["zusatzpunkte_".$i]);
					//echo ($testpunkte+$_POST["zusatzpunkte_".$i])."/".sql_result($zusatzinfos,0,"notenbeschreibung.gesamtpunktzahl")."=".(($testpunkte+$_POST["zusatzpunkte_".$i])/sql_result($zusatzinfos,0,"notenbeschreibung.gesamtpunktzahl")).">".(sql_result($berechnung,$j,"bewertung_note.prozent_bis")/100)."<br>";
					//echo ($berechnete_punkte+0.5)/sql_result($zusatzinfos,0,"notenbeschreibung.gesamtpunktzahl").">=".(sql_result($berechnung,($j+1),"bewertung_note.prozent_bis")/100)."<br>";
					// runden auf halbe Punkte
					// vorher: if ($berechnete_punkte/$gesamtpunktzahl_gruppe)>=(sql_result($berechnung,$j,"bewertung_note.prozent_bis"))
					// schuelerfreundlicher: round(2*sql_result($berechnung,$j,"bewertung_note.prozent_bis")*$gesamtpunktzahl_gruppe/100)/2
					if ($berechnete_punkte>=sql_result($berechnung,$j,"bewertung_note.prozent_bis")*$gesamtpunktzahl_gruppe/100) {
						$berechneter_notenzusatz=0;
						if ($j==sql_num_rows($berechnung)-1) {
							if ($berechnete_punkte>=$gesamtpunktzahl_gruppe)
								$berechneter_notenzusatz=1;
						}
						else
							// vorher gerundet (schuelerfreundlich): ($berechnete_punkte+0.5)>=round(2*sql_result($berechnung,$j+1,"bewertung_note.prozent_bis")*$gesamtpunktzahl_gruppe/100)/2)  und  ($berechnete_punkte-0.5)<round(2*sql_result($berechnung,$j,"bewertung_note.prozent_bis")*$gesamtpunktzahl_gruppe/100)/2
							//round(2*sql_result($punkte_noten,$m,'bewertung_note.prozent_bis')*$beschreibung[$i]['gesamtpunktzahl']/100+0.499)/2
							if (($berechnete_punkte+0.5)>=sql_result($berechnung,$j+1,"bewertung_note.prozent_bis")*$gesamtpunktzahl_gruppe/100)
								$berechneter_notenzusatz=1;
						if (($berechnete_punkte-0.5)<sql_result($berechnung,$j,"bewertung_note.prozent_bis")*$gesamtpunktzahl_gruppe/100)
							$berechneter_notenzusatz=-1;
						$berechnete_note=sql_result($berechnung,$j,"bewertung_note.note");
					}
				}
				
				if (sql_result($berechnung,0,"bewertungstabelle.punkte")==1)
					$berechneter_notenzusatz=0;
				
				// das heisst wirklich "geamtpunktzahl" (ohne s)
				db_conn_and_sql("UPDATE `noten` SET
					`wert`=".$berechnete_note.",
					`zusatz`=".$berechneter_notenzusatz.",
					`geamtpunktzahl`=".leer_NULL($gesamtpunktzahl_gruppe).",
					`gruppe_b`=".($_POST["gruppe_b_".$i]+0).",
					`punkte`=".$testpunkte.",
					`zusatzpunkte`=".str_replace(",", ".",$_POST["zusatzpunkte_".$i])."
					WHERE `id`=".$noten_id);
			}
			
			//leere Noten loeschen: gaeng auch mit einer zusaetzlichen Abfrage, ob notenwert eingetragen ist
			if ($_POST["wert_".$i]=="" and ($_POST["punkte_".$i]=="" or $gesamtpunktzahl_gruppe<1)) {
				db_conn_and_sql("DELETE FROM `noten` WHERE `id`=".$noten_id);
				db_conn_and_sql("DELETE FROM `note_aufgabe` WHERE `note`=".$noten_id);
				$schuelername=db_conn_and_sql("SELECT * FROM `schueler` WHERE `id`=".injaway($_POST["schueler_".$i]));
				echo 'Note von '.html_umlaute(sql_result($schuelername,0,"schueler.vorname")).' '.html_umlaute(sql_result($schuelername,0,"schueler.name")).' wurde gel&ouml;scht, weil weder eine Note noch eine Punktzahl eingetragen wurde.<br />';
			}
		}
		$i++;
	}
	
	// Notenhash aktualisieren
	notenhash_von_fach_klasse(sql_result($zusatzinfos,0,"notenbeschreibung.fach_klasse"), injaway($_GET["schuljahr"]), true);
	?>
	<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
<?php

}
else {
	$titelleiste = "Noten bearbeiten";
	include $pfad."header.php";
	
	if (!proofuser("notenbeschreibung", $_GET["beschreibung"]))
		die("Sie sind hierzu nicht berechtigt.");
	?>
	<script type="text/javascript">
		function prozentsatz_ermitteln(welcher, punkte_noten, tendenz_erlaubt) {
			var gesamtpunktzahl = 0; // GP des SCHUELERS ist hier gemeint
			var ep = new Array();
			ep = document.getElementById('punkte_'+welcher).value.split('/');
			for (var i=0; i<ep.length; i++) {
				var einzelpkt = parseFloat(ep[i].replace(/\,/g,"."));
				if (!isNaN(einzelpkt))
					gesamtpunktzahl += einzelpkt;
			}
			gesamtpunktzahl += parseFloat(document.getElementById('zusatzpunkte_'+welcher).value.replace(/\,/g,"."));
			var gesamtpunktzahl_test=parseInt(document.getElementById('gesamtpunktzahl').value);
			var prozent=parseInt(gesamtpunktzahl / gesamtpunktzahl_test*100);
			var berechneter_notenzusatz=0;
			var berechnete_note=0;
			var zusatz_position;
			
			for(i=0; i<punkte_noten.length; i++) {
				if (gesamtpunktzahl>=punkte_noten[i][1]*gesamtpunktzahl_test/100) {
					berechneter_notenzusatz='';
					if (i==(punkte_noten.length-1)) {
						if (gesamtpunktzahl>=gesamtpunktzahl_test)
							berechneter_notenzusatz='+';
					}
					else
						if ((gesamtpunktzahl+0.5)>=punkte_noten[i+1][1]*gesamtpunktzahl_test/100)
							berechneter_notenzusatz='+';
					if ((gesamtpunktzahl-0.5)<punkte_noten[i][1]*gesamtpunktzahl_test/100)
						berechneter_notenzusatz='-';
					//switch
					zusatz_position=2;
					if (berechneter_notenzusatz=='-')
						zusatz_position=1;
					if (berechneter_notenzusatz=='+')
						zusatz_position=0;
					berechnete_note=punkte_noten[i][0];
					if (tendenz_erlaubt)
						document.getElementById('wert_'+welcher).selectedIndex=(punkte_noten.length-i)*3-zusatz_position;
					else
						document.getElementById('wert_'+welcher).selectedIndex=(punkte_noten.length-i);
					
					if (document.getElementById('punkte_'+welcher).value=='')
						document.getElementById('wert_'+welcher).selectedIndex=0;
				}
			}
			if (parseFloat(document.getElementById('punkte_'+welcher).value.replace(/\,/g,"."))==gesamtpunktzahl)
				document.getElementById('prozentsatz_'+welcher).innerHTML=prozent + '%';
			else
				document.getElementById('prozentsatz_'+welcher).innerHTML=String(gesamtpunktzahl).replace(/[.]/,",")+" P.: "+prozent + '%';
		}
	</script>

  <body>
		
	<div id="mf">
		<ul class="r">
			<li><a href="<?php echo $pfad; ?>formular/hilfe.php?inhalt=noten_bearbeiten" class="icon" onclick="fenster(this.href, 'hilfe'); return false;"><img src="<?php echo $pfad; ?>icons/hilfe.png" alt="hilfe" /> Hilfe</a></li>
			<li><a id="pv" href="javascript:window.print()">diese Seite drucken</a></li>
			<li><a href="javascript:window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
	<div class="inhalt">
	<form action="<?php echo $pfad; ?>formular/noten_bearbeiten.php?beschreibung=<?php echo $_GET["beschreibung"]; ?>&amp;schuljahr=<?php echo $_GET["schuljahr"]; ?>&amp;hinzufuegen=true" method="post" accept-charset="ISO-8859-1">
		<fieldset><legend>Allgemeine Informationen</legend>
		<ol class="divider">
		<?php $notenbeschreibung=db_conn_and_sql("SELECT * FROM `notenbeschreibung` WHERE `id`=".injaway($_GET["beschreibung"]));
        $fach_klasse=sql_result($notenbeschreibung,0,"notenbeschreibung.fach_klasse");
		$db = new db();
		$schuljahr= $db->aktuelles_jahr();
		$schule=sql_fetch_assoc(db_conn_and_sql("SELECT schule FROM klasse, fach_klasse WHERE fach_klasse.klasse=klasse.id AND fach_klasse.id=".$fach_klasse));
		$schule=$schule["schule"];
		$start_ende=schuljahr_start_ende($schuljahr,$schule);
        $schuljahresbeginn_datum=$start_ende["start"];
        $schuljahresende_datum=$start_ende["ende"];
        
        include($pfad."formular/notenspalten_anderer_fachlehrer_anzeigen.php");
		?>
        <li><label for="notentyp">Beschreibung<em>*</em>:</label>
        <script>
            $(function() {
                $( "#zensurenspalte_korrigiert" ).datepicker("option", "minDate", new Date('<?php echo $schuljahresbeginn_datum; ?>') );
                $( "#zensurenspalte_korrigiert" ).datepicker("option", "maxDate", new Date('<?php echo $schuljahresende_datum; ?>') );
                $( "#zurueckgegeben" ).datepicker("option", "minDate", new Date('<?php echo $schuljahresbeginn_datum; ?>') );
                $( "#zurueckgegeben" ).datepicker("option", "maxDate", new Date('<?php echo $schuljahresende_datum; ?>') );
            });
        </script>
		<select name="notentyp">
            <?php
            // TODO: Vorauswahl eines nicht aktiven Notentyps
            $result = db_conn_and_sql ('SELECT * FROM `notentypen` WHERE `aktiv`=TRUE AND (id<11 OR schule='.$schule.')');
            for($k=0;$k<sql_num_rows ( $result );$k++) { ?>
              <option value="<?php echo sql_result ( $result, $k, 'notentypen.id' ); ?>" <?php if(sql_result($notenbeschreibung,0,"notenbeschreibung.notentyp")==sql_result ( $result, $k, 'notentypen.id' )) echo 'selected="selected" '; ?>title="<?php echo sql_result($result, $k,"notentypen.name"); ?>"><?php echo sql_result ( $result, $k, 'notentypen.kuerzel' ); ?></option>
            <?php } ?>
            </select>
		<?php
		echo '<input type="text" name="beschreibung" value="'.html_umlaute(sql_result($notenbeschreibung,0,"notenbeschreibung.beschreibung")).'" size="15" maxlength="25" />';
		echo '<br />';
		if (!$my_user->my["zensurenkommentare"])
			echo '<span style="display: none;">';
		echo '<label for="kommentar">Kommentar:</label> <input type="text" name="kommentar" value="'.html_umlaute(sql_result($notenbeschreibung,0,"notenbeschreibung.kommentar")).'" size="20" maxlength="100" /><br />';
		if (!$my_user->my["zensurenkommentare"])
			echo '</span>';
		
		
		
		echo '<label for="datum">Datum<em>*</em>:</label> ';
		if (sql_result($notenbeschreibung,$i,"notenbeschreibung.plan")==NULL)
            echo '
            <script>
                $(function() {
                    $( "#zensurenspalte_datum" ).datepicker("option", "minDate", new Date(\''.$schuljahresbeginn_datum.'\') );
                    $( "#zensurenspalte_datum" ).datepicker("option", "maxDate", new Date(\''.$schuljahresende_datum.'\') );
                    woche_sichtbar(js_datum_zu_woche($( \'#zensurenspalte_datum\' ).datepicker( \'getDate\' )));
                });
            </script>
            <input type="text" id="zensurenspalte_datum" class="datepicker" name="datum" value="'.datum_strich_zu_punkt(sql_result($notenbeschreibung,0,"notenbeschreibung.datum")).'" size="7" maxlength="10" onchange="woche_sichtbar(js_datum_zu_woche($( \'#zensurenspalte_datum\' ).datepicker( \'getDate\' )));" title="		<p>Das Datum kann im Format TT.MM.JJJJ eingegeben werden. Bevorzugt sollte die Zensur jedoch mit einer Unterrichtsstunde verbunden werden. Wenn Sie n&auml;mlich die Unterrichtsstunde verschieben, wird die Zensur ebenfalls verschoben.</p><p>Speichern sie eventuelle &Auml;nderungen an der Halbjahreszensur-Auswahl bzw. des Datumswechsels, BEVOR Sie Zensuren eintragen (Standardauswahl der Einzelnoten wird dann aktualisiert).</p>" />
			<a href="'.$pfad.'formular/notenbeschreibung_plan.php?beschreibung='.sql_result($notenbeschreibung,$i,"notenbeschreibung.id").'" onclick="fenster(this.href,\'anh&auml;ngen\'); return false;" class="icon" title="an eine geplante Unterrichsstunde anh&auml;ngen"><img src="'.$pfad.'icons/plan.png" alt="test" /></a>';
		else {
			$plan=db_conn_and_sql("SELECT * FROM `plan` WHERE `fach_klasse`=".$fach_klasse." AND `schuljahr`=".$aktuelles_jahr." AND ausfallgrund IS NULL ORDER BY `datum`");
			$plan_zu_woche=array();
			while($pl=sql_fetch_assoc($plan))
				$plan_zu_woche[]=$pl["id"].": ".datum_zu_woche($pl["datum"]);
			?>
			<script>var plan_zu_woche = {<?php echo implode(", ", $plan_zu_woche); ?>};
				$(function() {
					woche_sichtbar(plan_zu_woche[document.getElementById('zensurenspalte_datum').value]);
				});
			</script>
			<select name="plan" id="zensurenspalte_datum" onchange="woche_sichtbar(plan_zu_woche[this.value]);"><option value="">-</option>
				<?php
				for($i=0;$i<sql_num_rows($plan);$i++) {
					echo '<option value="'.sql_result($plan,$i,"plan.id").'"';
					if (sql_result($plan,$i,"plan.id")==sql_result($notenbeschreibung,0,"notenbeschreibung.plan")) {echo ' selected="selected"'; $plan_datum=$i; }
					echo '>'.datum_strich_zu_punkt(sql_result($plan,$i,"plan.datum")).'</option>';
				} ?>
			</select>
			<img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" title="<p>Das Datum h&auml;ngt an einer Unterrichtsstunde (empfohlen). Wollen Sie die Verbindung dennoch l&ouml;sen, kann dies im Stoffverteilungsplan der Fach-Klasse-Kombination geschehen.</p><p>Speichern sie &Auml;nderungen an der Halbjahreszensur-Auswahl bzw. des Datumswechsels ab, bevor Sie Zensuren eintragen.</p>" />
			<?php }
		echo ' <input type="checkbox" id="halbjahresnote" name="halbjahresnote" value="1"';
		if (sql_result($notenbeschreibung,0,"notenbeschreibung.halbjahresnote")) echo ' checked="checked"';
		echo '><label for="halbjahresnote" style="width: 150px; height: 30px;">Halbjahreszensur</label></input> <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" title="<p>Wenn die Zensurenspalte noch vor dem Halbjahresumbruch in der Gesamtansicht stehen soll, muss die Halbjahreszensur auf aktiv gesetzt sein.</p><p>Dies hat ebenfalls Auswirkung auf die Vorauswahl der Einzelzensuren (in der Tabelle neben dem Datum). Ob die einzelne Zensur tats&auml;chlich in die Halbjahresberechnung eingeht, oder nicht, k&ouml;nnen Sie dort jeweils einzeln entscheiden.</p><p>Speichern sie &Auml;nderungen an der Halbjahreszensur-Auswahl bzw. des Datumswechsels ab, bevor Sie Zensuren eintragen.</p>" />'; ?>
		<label for="bewertungstabelle" style="width: 150px;margin-left: 30px;">Bewertungstabelle: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" title="<p>Die Zensuren werden bei eingegebenen Punktzahlen automatisch nach der gew&auml;hlten Bewertungstabelle berechnet.</p><p>Nur Schulleiter k&ouml;nnen Bewertungstabellen erstellen.</p><p>Unter 'Zensuren' - 'Berechnung' kann die Standard-Bewertungstabelle der gew&auml;hlten Fach-Klasse eingestellt werden.</p>" /></label>
            <select name="bewertungstabelle"><?php
				$schule=sql_fetch_assoc(db_conn_and_sql("SELECT schule FROM klasse, fach_klasse WHERE fach_klasse.klasse=klasse.id AND fach_klasse.id=".sql_result($notenbeschreibung,0,"notenbeschreibung.fach_klasse")));
				$schule=$schule["schule"];
                $bewertungstabelle=db_conn_and_sql("SELECT * FROM `bewertungstabelle` WHERE `user`=".$_SESSION['user_id']." OR `schule`=".$schule." ORDER BY `bewertungstabelle`.`name`");
                for ($j=0;$j<sql_num_rows($bewertungstabelle);$j++)
                    if (sql_result($bewertungstabelle,$j,"bewertungstabelle.aktiv") or sql_result($bewertungstabelle,$j,"bewertungstabelle.id")==sql_result($notenbeschreibung,0,"notenbeschreibung.bewertungstabelle")) {
                        echo '<option value="'.sql_result($bewertungstabelle,$j,"bewertungstabelle.id").'"';
                        if (sql_result($bewertungstabelle,$j,"bewertungstabelle.id")==sql_result($notenbeschreibung,0,"notenbeschreibung.bewertungstabelle"))
                            echo ' selected="selected"';
                        echo '>'.html_umlaute(sql_result($bewertungstabelle,$j,"bewertungstabelle.name")).'</option>';
                    }
            ?>
            </select></li>
		<li><label for="korrigiert">korrigiert am: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" title="Wenn ein Test korrigiert ist, gilt er als bearbeitet und wird nicht mehr auf der Startseite als 'zu Erledigen' angezeigt. Der Test wird au&szlig;erdem in der n&auml;chstfolgenden Unterrichtsstunden-Druckansicht zum Zur&uuml;ckgeben notiert." /></label>
            <input type="text" class="datepicker" id="zensurenspalte_korrigiert" name="korrigiert" onchange="document.getElementById('zurueckgegeben').style.display='inline';"<?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.datum").@sql_result($plan,$plan_datum,"plan.datum")>date("Y-m-d")) echo ' style="display: none;"'; ?> value="<?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.korrigiert")!=NULL) echo datum_strich_zu_punkt(sql_result($notenbeschreibung,0,"notenbeschreibung.korrigiert")); else echo date("d.m.Y"); ?>" size="7" maxlength="10" />
		<label for="zurueckgegeben" style="width: 130px;margin-left: 30px;">Zur&uuml;ckgegeben: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" title="Das Feld 'Zur&uuml;ckgegeben' entscheidet, ab wann Eltern &amp; Sch&uuml;ler ihre Zensuren sehen." /></label>
            <input type="text" id="zurueckgegeben" class="datepicker" name="zurueckgegeben" onchange="document.getElementById('ber_und_unt').style.display='inline';" value="<?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.zurueckgegeben")!=NULL) echo datum_strich_zu_punkt(sql_result($notenbeschreibung,0,"notenbeschreibung.zurueckgegeben")); else echo date("d.m.Y"); ?>" size="7" maxlength="10" />
		<label for="notenspiegel_zeigen" style="width: 190px;margin-left: 30px;">Zensurenspiegel zeigen: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" title="Falls das H&auml;kchen entfernt wird, sehen Eltern &amp; Sch&uuml;ler den Zensurenspiegel nicht (sinnvoll bei m&uuml;ndlichen Zensuren)." /></label>
			<input type="checkbox" name="notenspiegel_zeigen" value="1"<?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.notenspiegel_zeigen")=="1") echo ' checked="checked"'; ?> />
        </li>
        
		<?php if (!$my_user->my["zensuren_nicht_zaehlen"]) echo '<span style="display: none;">'; ?>
		<li><label for="mitzaehlen">Bewertung: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" title="Falls Sie einzelne Zensuren gern in der Zensurenliste stehen haben m&ouml;chten, aber diese nicht in die Durchschnitts-Berechnung eingehen sollen, k&ouml;nnen Sie entweder die gesamte Zensurenspalte auf 'nicht bewerten' setzen oder 'einzeln w&auml;hlen'. Damit erscheint ein Zus&auml;tzliches Auswahlfeld, in dem Sie einzelne Sch&uuml;ler an- bzw. abw&auml;hlen k&ouml;nnen." />
		</label> Normal <input type="radio" name="mitzaehlen" onclick="mitzaehlen_anzeigen('none');" value="1"<?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.mitzaehlen")=="1") echo ' checked="checked"'; ?> />
			| einzeln w&auml;hlen <input type="radio" name="mitzaehlen" onclick="mitzaehlen_anzeigen('inline');" value="-1"<?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.mitzaehlen")=="-1") echo ' checked="checked"'; ?> />
			| nicht bewerten <input type="radio" name="mitzaehlen" onclick="mitzaehlen_anzeigen('none');" value="0"<?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.mitzaehlen")=="0") echo ' checked="checked"'; ?> /></li>
		<?php if (!$my_user->my["zensuren_nicht_zaehlen"]) echo '</span>'; ?>
		
		<?php if (!$my_user->my["zensuren_unt_ber"]) echo '<span style="display: none;">'; ?>
		<span id="ber_und_unt"<?php if(sql_result($notenbeschreibung,0,"notenbeschreibung.zurueckgegeben")=="") echo ' style="display: none;"'; ?>>
		<li>
			<label for="berichtigung">Berichtigung: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" title="Die Felder 'Unterschrift' und 'Berichtigung' werden in der Unterrichtsstunden-Nachbereitung automatisch ausgef&uuml;llt. Sie stehen hier nur zu Nachbesserungszwecken zur Auswahl." /></label>
			<input type="radio" name="berichtigung" value=""<?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.berichtigung")=="") echo ' checked="checked"'; ?> /> nicht erforderlich
			<input type="radio" name="berichtigung" value="0"<?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.berichtigung")==="0") echo ' checked="checked"'; ?> /> unvollst&auml;ndig
			<input type="radio" name="berichtigung" value="1"<?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.berichtigung")=="1") echo ' checked="checked"'; ?> /> fertig<br />
			<label for="unterschrift">Unterschrift:</label>
			<input type="radio" name="unterschrift" value=""<?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.unterschrift")=="") echo ' checked="checked"'; ?> /> nicht erforderlich
			<input type="radio" name="unterschrift" value="0"<?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.unterschrift")==="0") echo ' checked="checked"'; ?> /> unvollst&auml;ndig
			<input type="radio" name="unterschrift" value="1"<?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.unterschrift")=="1") echo ' checked="checked"'; ?> /> fertig
		</li>
		</span>
		<?php if (!$my_user->my["zensuren_unt_ber"]) echo '</span>'; ?>
			<?php
	$test_da=db_conn_and_sql("SELECT *, IF( `test`.`url` IS NULL , `aufgabe`.`punkte` , `test`.`punkte` ) AS `punkte_add`
	FROM `notenbeschreibung`
		LEFT JOIN `test` ON `notenbeschreibung`.`test`=`test`.`id`
		LEFT JOIN `test_aufgabe` ON `test_aufgabe`.`test`=`test`.`id`
		LEFT JOIN `aufgabe` ON `test_aufgabe`.`aufgabe`=`aufgabe`.`id`
	WHERE `notenbeschreibung`.`id`=".injaway($_GET["beschreibung"])."
	ORDER BY `test_aufgabe`.`position`");
	if (sql_result($test_da,0,"punkte_add")<1) { ?>
		<?php if (!$my_user->my["zensurenpunkte"]) echo '<span style="display: none;">'; ?>
		<li><label for="gesamtpunktzahl">Gesamtpunktzahl:</label> <input type="text" id="gesamtpunktzahl" name="gesamtpunktzahl" value="<?php echo html_umlaute(sql_result($notenbeschreibung,0,"notenbeschreibung.gesamtpunktzahl")); ?>" size="2" maxlength="4" /> <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" title="Die Zensuren werden weitgehend selbst berechnet - Kreda unterscheidet:
			<ul><li>Angabe einer Zensur ohne Punkteangabe</li>
			<li>Eine <b>Gesamtpunktzahl</b> wird angegeben und bei Sch&uuml;lern die Test-Punktzahl: Die Zensur wird mit Hilfe der gew&auml;hlten Bewertungstabelle berechnet. Falls Sie im zugeordneten Test eine Gesamtpunktzahl angegeben haben, k&ouml;nnen Sie dieses Feld auch frei lassen.</li>
			<li>Ein Test mit eingetragenen Aufgaben wird der Zensurenspalte zugeordnet: Die Zensur wird mit Hilfe der Einzelaufgabenpunktzahlen und der gew&auml;hlten Bewertungstabelle berechnet. Dies hat den Vorteil, dass eine Statistik zu den einzelnen Aufgaben erstellt wird.</li>
			</ul>" /> <?php if (sql_result($test_da,0,"test.punkte")>0) { ?><span class="hinweis">Im Test angegeben: <?php echo html_umlaute(sql_result($test_da,0,"test.punkte")); ?></span><?php } ?></li>
		<?php if (!$my_user->my["zensurenpunkte"]) echo '</span>'; ?>
	<?php } else {
			$testpunkte=0;
			for($i=0;$i<sql_num_rows($test_da);$i++) {
				// TODO noch Gruppe A/B berücksichtigen
				/*			if (sql_result($test_da,$i,"test_aufgabe.position")>0) $gruppe_a_zaehler[]=array("zaehler"=>$i, "position"=>sql_result($test_da,$i,"test_aufgabe.position"));
							if (sql_result($test_da,$i,"test_aufgabe.position_b")>0) $gruppe_b_zaehler[]=array("zaehler"=>$i, "position"=>sql_result($test_da,$i,"test_aufgabe.position_b"));
							
							if (@sql_result($test_da,$i,"test_aufgabe.position")!="") {
								if ($i>0) echo "/";*/
				if (sql_result($test_da,$i,"zusatzaufgabe")!="1" and sql_result($test_da,$i,"position")>0)
					$testpunkte+=(sql_result($test_da,$i,"punkte_add")+0);
			}
			echo '<input type="hidden" id="gesamtpunktzahl" value="'.$testpunkte.'" />'; 
		} ?>
		</ol>
		</fieldset>
		
		<fieldset>
		<table class="tabelle" cellspacing="0">
		<tr><th>Sch&uuml;ler</th>
			<th<?php if (!$my_user->my["zensurenpunkte"]) echo ' style="display: none;"'; ?>>Punkte + Zusatz<br />
				<a href="<?php echo $pfad; ?>formular/notenbeschreibung_test.php?beschreibung=<?php echo $_GET["beschreibung"]; ?>" onclick="fenster(this.href,  'test zuordnen'); return false;" class="icon" title="mit einem Test verkn&uuml;pfen"><img src="<?php echo $pfad; ?>icons/test.png" alt="test" /></a>
				<img src="<?php echo $pfad.'icons/'; if (sql_result($notenbeschreibung,0,"notenbeschreibung.test")!=NULL) echo 'haekchen.png" title="Ein Test wurde zugeordnet" alt="haekchen"'; else echo 'abhaken.png" title="Es wurde kein Test zugeordnet" alt="kein haekchen"'; ?> />
				 <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" title="<p>Wenn der Zensur ein Test zugeordet wurde (dies geschieht mit dem Symbol <img src='<?php echo $pfad; ?>icons/test.png' alt='test' />), in dem sich einzelne Aufgaben mit angegebener Punktzahl befinden, k&ouml;nnen Sie hier die Einzelpunktzahlen mit einem '/' (Slash) getrennt eingeben. Auch Kommazahlen sind m&ouml;glich.</p>
		<p>'Zusatz' ist im Normalfall immer '0', auch wenn es sich um eine Zusatzaufgabe im Test handelt. Falls sich allerdings - abweichend von den erreichten Punkten im Test - ein anderer Notenwert ergeben soll, kann dies durch Angabe von (auch negativen) Zusatzpunkten geschehen.</p>" />
                 <?php if (sql_result($notenbeschreibung,0,"notenbeschreibung.test")!=NULL) echo '<a href="'.$pfad.'test_druckansicht.php?welcher='.sql_result($notenbeschreibung,0,"notenbeschreibung.test").'&amp;datum=" title="Test &ouml;ffnen" onclick="fenster(this.href,  \'test ansehen\'); return false;" class="icon"><img src="'.$pfad.'icons/suchen.png" alt="Lupe" /></a>'; ?><br />
				<?php
				$gruppe_a_zaehler='';
				$gruppe_b_zaehler='';
				$gruppe_b_punkte='';
				if (sql_result($test_da,0,"punkte_add")>0) {
					echo 'A: ';
					for($i=0;$i<sql_num_rows($test_da);$i++) {
						if (sql_result($test_da,$i,"test_aufgabe.position")>0) $gruppe_a_zaehler[]=array("zaehler"=>$i, "position"=>sql_result($test_da,$i,"test_aufgabe.position"));
						if (sql_result($test_da,$i,"test_aufgabe.position_b")>0) $gruppe_b_zaehler[]=array("zaehler"=>$i, "position"=>sql_result($test_da,$i,"test_aufgabe.position_b"));
						
						if (sql_result($test_da,$i,"test_aufgabe.position")!="") {
							if ($i>0) echo "/";
							echo '<span title="'.sql_result($test_da,$i,"test_aufgabe.position").'. '.html_umlaute(sql_result($test_da,$i,"aufgabe.text")).'">'.(sql_result($test_da,$i,"punkte_add")+0).'</span>';
						}
					}
					//gruppe B Aufgaben sortieren
					for ($i=0;$i<(count($gruppe_b_zaehler)-1);$i++)
						for ($k=$i+1;$k<count($gruppe_b_zaehler);$k++)
							if ($gruppe_b_zaehler[$i]["position"]>$gruppe_b_zaehler[$k]["position"]) {
								$hilf=$gruppe_b_zaehler[$i];
								$gruppe_b_zaehler[$i]=$gruppe_b_zaehler[$k];
								$gruppe_b_zaehler[$k]=$hilf;
							}
					
					if ($gruppe_b_zaehler!='') {
						echo '<br />B: ';
						$gruppe_b_existiert=true;
						for($i=0;$i<count($gruppe_b_zaehler);$i++) {
							echo '<span title="'.html_umlaute(sql_result($test_da,$gruppe_b_zaehler[$i]["zaehler"],"aufgabe.text")).'">'.(sql_result($test_da,$gruppe_b_zaehler[$i]["zaehler"],"punkte_add")+0).'</span>';
							if ($i<count($gruppe_b_zaehler)-1) echo "/";
						}
					}
				}
					?></th>
			<th title="falls Punkte angegeben werden, wird die Note automatisch berechnet">Note</th>
			<th>Datum</th>
			<th title="optional"<?php if (!$my_user->my["zensurenkommentare"]) echo ' style="display: none;"'; ?>>Kommentar</th></tr>
		<?php
        $schueler=schueler_von_fachklasse(sql_result($notenbeschreibung,0,"notenbeschreibung.fach_klasse"));
		$schueler_array='';
		while($schueler_row=sql_fetch_assoc($schueler))
			$schueler_array[]=$schueler_row;
        
		// zuvor vergebene Noten an nicht mehr aktuelle (Gruppenwechsel) Schueler -> schueler dazunehmen
		$schueler_add=db_conn_and_sql("SELECT DISTINCT schueler.id, schueler.name, schueler.vorname, schueler.position
			FROM schueler, noten
			WHERE noten.beschreibung=".injaway($_GET["beschreibung"])."
				AND schueler.id=noten.schueler");
		
		while ($schueler_row=sql_fetch_assoc($schueler_add)) {
			$schueler_nicht_vorhanden=true;
			foreach ($schueler_array as $vorhanden)
				if ($vorhanden["id"]==$schueler_row["id"])
					$schueler_nicht_vorhanden=false;
			if ($schueler_nicht_vorhanden)
				$schueler_array[]=$schueler_row;
		}
        
        // fuer select-input werden alle Noten benoetigt
        // TODO: bei Wechsel der Bewertungstabelle, werden die neuen Noten nicht nachgeladen
		$waehlbare_noten=db_conn_and_sql("SELECT bewertung_note.note, bewertungstabelle.punkte, bewertung_note.prozent_bis
			FROM `notenbeschreibung`,`bewertungstabelle`,`bewertung_note`
			WHERE `notenbeschreibung`.`bewertungstabelle`=`bewertungstabelle`.`id`
				AND `notenbeschreibung`.`id`=".injaway($_GET["beschreibung"])."
				AND `bewertungstabelle`.`id`=`bewertung_note`.`bewertungstabelle`
			ORDER BY `bewertung_note`.`prozent_bis` DESC");
		$eintraege_js_array=array();
		while ($note_javascript=sql_fetch_assoc($waehlbare_noten)) {
			//$eintraege_js_array[]='new Array('.$note_javascript["note"].', '..')';
			$eintraege_js_array[]='new Array('.$note_javascript["note"].', '.$note_javascript["prozent_bis"].')';
			if ($note_javascript["punkte"])
				$tendenz_erlaubt=0;
			else
				$tendenz_erlaubt=1;
		}
		// umsortieren (Rueckwaerts)
		$hilf=array();
		for($i=count($eintraege_js_array)-1;$i>=0;$i--)
			$hilf[]=$eintraege_js_array[$i];
		$eintraege_js_array=$hilf;
		
		echo '<script>
		punkte_noten=new Array('.implode(",", $eintraege_js_array).');
		
		function set_toggle_to_save(id) {
			$(\'#speichern_\'+id).prop(\'checked\', true).button("refresh");
		}
		
		$(function() {
			$("#halbjahresnote").button(); ';
		for($i=0; $i<count($schueler_array); $i++) { // if (gehoert_zur_gruppe(sql_result($notenbeschreibung,0,"notenbeschreibung.fach_klasse"),$schueler_array[$i]["id"]))
				echo ' $("#speichern_'.$i.'").button();
					$("#halbjahresnote_'.$i.'").button();';
				if ($gruppe_b_existiert)
					echo ' $("#gruppe_b_'.$i.'").button().click(function () {
						if ($(this).is(\':checked\')) {
							set_toggle_to_save('.$i.');
							$(this).button(\'option\', \'label\', \'B\');
						} else {
							$(this).button(\'option\', \'label\', \'A\');
							set_toggle_to_save('.$i.');
						}
					});';
		}
		echo '});</script>
		<style>
		.toggle_btn ~ label {
			width: 27px;
			height: 22px;
		}
		.toggle_btn ~ label span img {
			float: left;
			margin-left: -5px;
			margin-top: 1px;
		}
		.toggle_btn ~ label span {
			margin-left: -5px;
			margin-top: -3px;
		}
		</style>';
		
		for($i=0; $i<count($schueler_array); $i++) {
			// if (gehoert_zur_gruppe(sql_result($notenbeschreibung,0,"notenbeschreibung.fach_klasse"),$schueler_array[$i]["id"]))
			$note=db_conn_and_sql("SELECT * FROM `noten`
				WHERE `noten`.`beschreibung`=".injaway($_GET["beschreibung"])."
					AND `noten`.`schueler`=".$schueler_array[$i]["id"]);
			if (sql_result($note,0,"noten.gruppe_b")==1)
				$order_by='position_b';
			else
				$order_by='position';
			$punkte_aufgegliedert='';
			if (sql_result($note,0,"noten.id")>0)
				$punkte_aufgegliedert=db_conn_and_sql("SELECT *, IF( `test`.`url` IS NULL , `aufgabe`.`punkte` , `test`.`punkte` ) AS `punkte_add`
				FROM `notenbeschreibung`
					LEFT JOIN `test` ON `notenbeschreibung`.`test`=`test`.`id`
					LEFT JOIN `test_aufgabe` ON `test_aufgabe`.`test`=`test`.`id` AND `test_aufgabe`.".$order_by." IS NOT NULL
					LEFT JOIN `aufgabe` ON `test_aufgabe`.`aufgabe`=`aufgabe`.`id`
					LEFT JOIN `note_aufgabe` ON `aufgabe`.`id`=`note_aufgabe`.`aufgabe` AND `note_aufgabe`.`note`=".sql_result($note,0,"noten.id")." AND `note_aufgabe`.`schueler`=".$schueler_array[$i]["id"]."
				WHERE `notenbeschreibung`.`id`=".injaway($_GET["beschreibung"])."
				ORDER BY `test_aufgabe`.`".$order_by."`");
				
			echo '<tr onmouseover="document.getElementById(\'spalte_'.$i.'\').className=\'over\';" onmouseout="document.getElementById(\'spalte_'.$i.'\').className=\'\';">';
			echo '<td id="spalte_'.$i.'"><input type="checkbox" class="toggle_btn" id="speichern_'.$i.'" name="speichern_'.$i.'" value="true"><label for="speichern_'.$i.'" title="Zeile beim Speichern ber&uuml;cksichtigen? Wenn nicht aktiviert, gehen get&auml;tigte &Auml;nderungen verloren."><img src="'.$pfad.'icons/page_save.png" alt="Save" /></label></input>
				'.html_umlaute($schueler_array[$i]["name"]).', '.html_umlaute($schueler_array[$i]["vorname"]).'<input type="hidden" name="schueler_'.$i.'" value="'.$schueler_array[$i]["id"].'" /></td>';
			
			echo '<td';
				if (!$my_user->my["zensurenpunkte"]) echo ' style="display: none;"';
				echo '>';
				if ($gruppe_b_existiert) {
					echo '<input type="checkbox" class="toggle_btn" id="gruppe_b_'.$i.'" name="gruppe_b_'.$i.'" value="1" tabindex="'.(1*count($schueler_array)+$i+1).'"';
					if (sql_result($note,0,"noten.gruppe_b")==1)
						echo ' checked="checked"';
					echo ' onchange="set_toggle_to_save('.$i.')"><label for="gruppe_b_'.$i.'" title="Gruppe A oder B im Test?">';
					if (sql_result($note,0,"noten.gruppe_b")==1) echo 'B'; else echo 'A'; echo '</label></input> ';
					
				}
			echo '<input type="text" id="punkte_'.$i.'" name="punkte_'.$i.'" tabindex="'.(2*count($schueler_array)+$i+1).'" value="';
			if (sql_result($test_da,0,"punkte_add")>0) {
				$gruppenzaehler=$gruppe_a_zaehler;
				if (sql_result($note,0,"noten.gruppe_b")==1) $gruppenzaehler=$gruppe_b_zaehler;
				
				if (sql_num_rows($punkte_aufgegliedert)>0)
					for($k=0;$k<count($gruppenzaehler);$k++) {
						//echo sql_result($test_da,$gruppenzaehler[$k]["zaehler"],"test_aufgabe.aufgabe")."=".@sql_result($punkte_aufgegliedert,$k,"test_aufgabe.aufgabe");
						if (sql_result($test_da,$gruppenzaehler[$k]["zaehler"],"test_aufgabe.aufgabe")==sql_result($punkte_aufgegliedert,$k,"test_aufgabe.aufgabe") and sql_result($punkte_aufgegliedert,$k,"note_aufgabe.punkte")!='')
							echo kommazahl(sql_result($punkte_aufgegliedert,$k,"note_aufgabe.punkte"));
						else echo '-';
						if (($k+1)<count($gruppenzaehler)) echo "/";
					}
				else
					echo sql_result($note,0,"noten.punkte");
			}
			else if (sql_result($note,0,"noten.punkte")!="") echo str_replace(".",",",sql_result($note,0,"noten.punkte")*1);
			echo '" size="12" maxlength="60" onchange="set_toggle_to_save('.$i.'); document.getElementById(\'wert_'.$i.'\').disabled=this.value==\'\'?false:true; prozentsatz_ermitteln('.$i.', punkte_noten, '.$tendenz_erlaubt.');" />
				<input type="text" id="zusatzpunkte_'.$i.'" name="zusatzpunkte_'.$i.'" tabindex="'.(3*count($schueler_array)+$i+1).'" value="'.@kommazahl(sql_result($note,0,"noten.zusatzpunkte")).'" size="1" maxlength="5" onchange="set_toggle_to_save('.$i.'); prozentsatz_ermitteln('.$i.', punkte_noten, '.$tendenz_erlaubt.');" />
				<span id="prozentsatz_'.$i.'"></span></td>';
				
			echo '<td>
					<select name="wert_'.$i.'" id="wert_'.$i.'" tabindex="'.(4*count($schueler_array)+$i+1).'"';
					if (@sql_result($note,0,"noten.punkte")!="" or (sql_result($note,0,"noten.zusatzpunkte")+0)!=0)
						echo ' disabled="disabled"';
					echo ' onchange="set_toggle_to_save('.$i.')" /><option value=""></option>';
					for($wn=0; $wn<sql_num_rows($waehlbare_noten); $wn++) {
						$ausgewaehltes='';
						$ausgewaehltes_minus='';
						$ausgewaehltes_plus='';
						if (@sql_result($note,0,"noten.wert")===@sql_result($waehlbare_noten,$wn,"bewertung_note.note") and @sql_result($note,0,"noten.zusatz")!="1" and @sql_result($note,0,"noten.zusatz")!="-1")
							$ausgewaehltes=' selected="selected"';
						if (@sql_result($note,0,"noten.wert")===@sql_result($waehlbare_noten,$wn,"bewertung_note.note") and @sql_result($note,0,"noten.zusatz")=="-1")
							$ausgewaehltes_minus=' selected="selected"';
						if (@sql_result($note,0,"noten.wert")===@sql_result($waehlbare_noten,$wn,"bewertung_note.note") and @sql_result($note,0,"noten.zusatz")=="1")
							$ausgewaehltes_plus=' selected="selected"';
						echo '<option value="'.@sql_result($waehlbare_noten,$wn,"bewertung_note.note").'"'.$ausgewaehltes.'>'.@sql_result($waehlbare_noten,$wn,"bewertung_note.note").'</option>';
						if (@sql_result($waehlbare_noten,$wn,"bewertungstabelle.punkte")!=1) {
							echo '<option value="'.@sql_result($waehlbare_noten,$wn,"bewertung_note.note").'_-"'.$ausgewaehltes_minus.'>'.@sql_result($waehlbare_noten,$wn,"bewertung_note.note").'-</option>';
							echo '<option value="'.@sql_result($waehlbare_noten,$wn,"bewertung_note.note").'_+"'.$ausgewaehltes_plus.'>'.@sql_result($waehlbare_noten,$wn,"bewertung_note.note").'+</option>';
						}
					}
					echo '</select>';
					
					/*echo '<input type="text" name="wert_'.$i.'" id="wert_'.$i.'" tabindex="'.(2*$i+1).'" value="'.@sql_result($note,0,"noten.wert").'" size="1" maxlength="2"';
					// @sql_result($test_da,0,"notenbeschreibung.test")!=NULL or 
					if (@sql_result($note,0,"noten.punkte")!="" or (@sql_result($note,0,"noten.zusatzpunkte")+0)!=0)
						echo ' disabled="disabled"';
					echo ' onchange="set_toggle_to_save('.$i.');" />
					<select name="zusatz_'.$i.'" tabindex="'.(2*$i+2).'" onchange="set_toggle_to_save('.$i.')"';
					if (@sql_result($note,0,"noten.punkte")!="" or (@sql_result($note,0,"noten.zusatzpunkte")+0)!=0)
						echo ' disabled="disabled"';
					echo '><option value="0"></option><option value="1"';
					if(@sql_result($note,0,"noten.zusatz")=="1")
						echo ' selected="selected"';
					echo '>+</option><option value="-1"';
					if(@sql_result($note,0,"noten.zusatz")=="-1")
						echo ' selected="selected"';
					echo '>-</option></select>';
					*/
					
					echo '<input type="checkbox" id="mitzaehlen_'.$i.'" name="mitzaehlen_'.$i.'"';
					if (sql_result($notenbeschreibung,0,"notenbeschreibung.mitzaehlen")!="-1")
						echo ' style="display: none;"';
					echo ' title="diese Note bewerten?" value="1" ';
					if (sql_num_rows($note)==0 or @sql_result($note,0,"noten.mitzaehlen"))
						echo ' checked="checked"';
					echo ' onchange="set_toggle_to_save('.$i.')" />
				</td>
				<td><input type="text" class="datepicker" name="datum_'.$i.'" tabindex="'.(5*count($schueler_array)+$i+1).'" value="'; if (sql_num_rows($note)==0){ if (sql_result($notenbeschreibung,0,"notenbeschreibung.datum")!="") echo datum_strich_zu_punkt(sql_result($notenbeschreibung,0,"notenbeschreibung.datum")); else echo datum_strich_zu_punkt(sql_result($plan,$plan_datum,"plan.datum")); } else echo datum_strich_zu_punkt(@sql_result($note,0,"noten.datum"));
			echo '" size="7" maxlength="10" onchange="set_toggle_to_save('.$i.')" /><input type="checkbox" class="toggle_btn" id="halbjahresnote_'.$i.'" name="halbjahresnote_'.$i.'" value="1"'; if ((sql_num_rows($note)==0 and sql_result($notenbeschreibung,0,"notenbeschreibung.halbjahresnote")=="1") or @sql_result($note,0,"noten.halbjahresnote")=="1") echo ' checked="checked"';
			echo ' onchange="set_toggle_to_save('.$i.')"><label for="halbjahresnote_'.$i.'" title="Hier kann f&uuml;r jede einzelne Zensur gew&auml;hlt werden, ob sie im Halbjahr ber&uuml;cksichtigt werden soll. Aktivieren Sie dazu den Button."><span>HJ</span></label></input></td>';
			
			echo '<td';
			if (!$my_user->my["zensurenkommentare"])
				echo ' style="display: none;"';
			echo '><input type="text" name="kommentar_'.$i.'" tabindex="'.(6*count($schueler_array)+$i+1).'" value="'.html_umlaute(@sql_result($note,0,"noten.kommentar")).'" size="12" maxlength="250" onchange="set_toggle_to_save('.$i.')" /></td></tr>';
			//}
			// falls der Schueler nicht zur Gruppe gehoert:
			//else echo '<input type="hidden" name="schueler_'.$i.'" value="'.$schueler_array[$i]["id"].'" />'; // ist zwar in der Tabelle, müsste aber auch ohne funktionieren
		}
		?>
		</table>
		</fieldset>
		<button style="float: right;" onclick="auswertung=new Array(new Array(0, 'beschreibung','nicht_leer'));
			if (document.getElementById('token_otp')) auswertung.push(new Array(0, 'token_otp','yubikey'));
			i=0; while(document.getElementById('speichern_'+i)) {
				if (document.getElementById('speichern_'+i).checked==true)
					auswertung.push(new Array(0, 'datum_'+i,'datum','<?php echo ($aktuelles_jahr); ?>-01-01','<?php echo ($aktuelles_jahr+1); ?>-12-31'), new Array(0, 'punkte_'+i, 'einzelpunkte'), new Array(0, 'zusatzpunkte_'+i,'komma_zahl')); i++;
			} pruefe_formular(auswertung); return false;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
		<?php
		
		// yubikey-laenge wird oben ueberprueft, da teilweise ein Zeichen fehlt und es dadurch zu Fehlern kommt
		if ($my_user->my["token_id"]!="") {
			echo '<p>YubiKey-OTP: <input type="text" name="token_otp" id="token_otp" size="15" placeholder="Best&auml;tigungspasswort" autocomplete="off" /><br /></p>';
		}
		else
			echo '<p><br /><br /></p>';
		?>
	</form>
	</div>
	</body>
	</html>
<?php
	
} ?>
