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

if ($_GET["eintragen"]=="true") {
	
	$plan_id=injaway($_GET['plan']);
	if (!proofuser("plan", $plan_id))
		die("Sie sind hierzu nicht berechtigt.");
	$naechster=injaway($_GET["naechster"]);
	if ($naechster!="neu" and !proofuser("plan", $naechster))
		die("Sie sind hierzu nicht berechtigt.");
	$plan=$db->plan($plan_id);
	
	// Blockkommentar(e) aendern
	db_conn_and_sql("UPDATE `block` SET `kommentare`=".apostroph_bei_bedarf($_POST["block1_kommentar"])." WHERE `id`=".sql_result($plan,0,"block_1"));
	if($_GET["block2"]=="set")
		db_conn_and_sql("UPDATE `block` SET `kommentare`=".apostroph_bei_bedarf($_POST["block2_kommentar"])." WHERE `id`=".sql_result($plan,0,"block_2"));
	//db_conn_and_sql("UPDATE `plan` SET `minuten_verschieben`=".$_POST["plan_minuten_start"]." WHERE `id`=".$plan_id);
	
	$abschnitte=db_conn_and_sql("SELECT * FROM `abschnittsplanung` WHERE `abschnittsplanung`.`plan`=".$plan_id." ORDER BY `abschnittsplanung`.`position`"); // wie unten
	//gibts Abschnitte, die verschoben werden muessen? Wenn ja, gleich Block im naechsten Plan notieren
	for ($i=0;$i<sql_num_rows($abschnitte);$i++) {
		$abschnitt=$db->abschnitt(sql_result($abschnitte,$i,"abschnittsplanung.abschnitt"));
		if ($_POST["aktion_verschieben_".$i]==1)
            if(isset($hilf[0]))
                $hilf[1]=$abschnitt['block'];
            else
                $hilf[0]=$abschnitt['block'];
	}
	//soll der naechste Plan erstellt werden?
	if ($naechster=="neu") {
		if ($_GET['datum']!="") {
			// DIESER ABSCHNITT FUNKTIONIERT NOCH NICHT (JEDENFALLS BEI EINEM VORHANDENEN PLAN) - gar nicht wahr
			if (isset($hilf[0])) { // wenn das zu verschiebende Element einen Block enthaelt (oder zwei bloecke angesprochen werden)
				$naechster=db_conn_and_sql("INSERT INTO `plan` (`datum`,`startzeit`, `schuljahr`, `fach_klasse`, `nachbereitung`, `minuten_verschieben`, `block_1`,`block_2`,`notizen`) VALUES
				('".date("Y-m-d",$_GET['datum'])."', ".apostroph_bei_bedarf($_GET['zeit']).", ".sql_result($plan,0,"schuljahr").", ".sql_result($plan,0,"fach_klasse").", false, 0, false, false,".$hilf[0].", ".leer_NULL($hilf[1]).", ".apostroph_bei_bedarf($_POST["notizen"]).")");
			}
			else if ($_POST["notizen"]!="") { // sonst brauch ich nur einen neuen plan, wenn notizen angegeben sind
				$naechster=db_conn_and_sql("INSERT INTO `plan` (`datum`,`startzeit`, `schuljahr`, `fach_klasse`, `nachbereitung`, `minuten_verschieben`, `block_1`,`notizen`) VALUES
				('".date("Y-m-d",$_GET['datum'])."', ".apostroph_bei_bedarf($_GET['zeit']).", ".sql_result($plan,0,"schuljahr").", ".sql_result($plan,0,"fach_klasse").", false, 0, false, false,".sql_result($plan,0,"block_1").", ".apostroph_bei_bedarf($_POST["notizen"]).")");
			}
		}
	}
	else { db_conn_and_sql("UPDATE `plan` SET `notizen`=".apostroph_bei_bedarf($_POST["notizen"])." WHERE `id`=".$naechster); }
	
	// alle zu verschiebenden Abschnitte in neuen Plan uebernehmen
	$k=0;
	for ($i=0;$i<sql_num_rows($abschnitte);$i++) {
		//Minuten aktualisieren
		db_conn_and_sql("UPDATE `abschnittsplanung`
				SET `minuten`=".$_POST["plan_minuten_".$i]."
				WHERE `plan`=".$plan_id." AND `abschnitt`=".sql_result($abschnitte,$i,"abschnittsplanung.abschnitt"));
		db_conn_and_sql("UPDATE `abschnitt`
				SET `minuten`=".$_POST["abschnitt_minuten_".$i].",`nachbereitung`=".apostroph_bei_bedarf($_POST["nachbereitung_".$i])."
				WHERE `id`=".sql_result($abschnitte,$i,"abschnittsplanung.abschnitt"));
		if ($_POST["aktion_verschieben_".$i]==1) {
			db_conn_and_sql("INSERT INTO `abschnittsplanung` (`plan`, `abschnitt`, `position`)
				VALUES (".$naechster.", ".sql_result($abschnitte,$i,"abschnittsplanung.abschnitt").", ".$k.");");
			$k++;
		}
		if ($_POST["aktion_entfernen_".$i]==1)
			db_conn_and_sql("DELETE FROM `abschnittsplanung` WHERE `plan`=".$plan_id." AND `abschnitt`=".sql_result($abschnitte,$i,"abschnittsplanung.abschnitt")." AND `position`=".sql_result($abschnitte,$i,"abschnittsplanung.position"));
		if ($_POST["aktion_als_HA_".$i]==1) {
			// Datum fuer naechsten Plan bzw. naechste freie stunde:
			$datum = date("Y-m-d",$_GET['datum']);
            if (isset($_GET["naechster"])) {
                $naechster_datum=db_conn_and_sql("SELECT `datum` FROM `plan` WHERE `id`=".injaway($_GET["naechster"]));
                $datum=sql_result($naechster_datum,0,"plan.datum");
            }
			//Hausaufgabe einfuegen
			if ($hausaufgabe_id<1) { // Falls mehrere Abschnitte als HA aufgegeben werden, sollen diese alle in EINE Hausaufgabe rein
				$hausaufgabe_id=db_conn_and_sql("INSERT INTO `hausaufgabe` (`plan`, `ziel`, `bemerkung`, `abgabedatum`, `kontrolliert`, `mitzaehlen`) VALUES (".$plan_id.", 'Stoff aufholen', NULL, '".$datum."', 0, 1)");
			}
			db_conn_and_sql("INSERT INTO `hausaufgabe_abschnitt` (`hausaufgabe`, `abschnitt`) VALUES (".$hausaufgabe_id.", ".sql_result($abschnitte,$i,"abschnittsplanung.abschnitt").");");
		}
	}
	
	// plan ist nachbereitet
	db_conn_and_sql("UPDATE `plan` SET `nachbereitung`=true WHERE `id`=".$plan_id);
	
	// Plan-Einschaetzung
	if (isset($_POST["gesamteindruck"]) or isset($_POST["selbsteinschaetzung"]) or isset($_POST["lerneinschaetzung"]) or isset($_POST["angstfaktor"]) or isset($_POST["lehrersprache"]) or isset($_POST["methode"]) or isset($_POST["stoffbewaeltigung"]) or isset($_POST["lob_geben"]) or isset($_POST["interesse"]) or isset($_POST["lerntempo"]))
		db_conn_and_sql("INSERT INTO `plan_auswertung` (`plan`, `gesamteindruck`, `selbsteinschaetzung`, `lerneinschaetzung`, `angstfaktor`, `lehrersprache`, `methode`, `stoffbewaeltigung`, `lob_geben`,`interesse`,`lerntempo`) VALUES
			(".$plan_id.", ".leer_NULL($_POST["gesamteindruck"]).", ".leer_NULL($_POST["selbsteinschaetzung"]).", ".leer_NULL($_POST["lerneinschaetzung"]).", ".leer_NULL($_POST["angstfaktor"]).", ".leer_NULL($_POST["lehrersprache"]).", ".leer_NULL($_POST["methode"]).", ".leer_NULL($_POST["stoffbewaeltigung"]).", ".leer_NULL($_POST["lob_geben"]).", ".leer_NULL($_POST["interesse"]).", ".leer_NULL($_POST["lerntempo"]).")");
	
	// ---------------------------------- Schueler-Betragen ---------------------------------
	$i=0;
	while ($_POST["verwarnung_schueler_".$i]>0) {
		if ($_POST["verwarnung_".$i]!="0")
			db_conn_and_sql("INSERT INTO `verwarnungen` (`schueler`,`plan`,`anzahl`)
				VALUES (".injaway($_POST["verwarnung_schueler_".$i]).", ".$plan_id.", ".injaway($_POST["verwarnung_".$i]).");");
		$i++;
	}
	// ---------------------------------- Schueler-Mitarbeit ---------------------------------
	$i=0;
	while ($_POST["mitarbeit_schueler_".$i]>0) {
		if ($_POST["mitarbeit_".$i]!="0")
			db_conn_and_sql("INSERT INTO `mitarbeit` (`schueler`,`plan`,`anzahl`)
				VALUES (".injaway($_POST["mitarbeit_schueler_".$i]).", ".$plan_id.", ".injaway($_POST["mitarbeit_".$i]).");");
		$i++;
	}
	// ---------------------------------- Hausaufgaben-Kontrolle --------------------------------
	$i=0;
	while ($_POST["hausaufgabe_id_".$i]>0) {
		$alter_status=db_conn_and_sql("SELECT `kontrolliert` FROM `hausaufgabe` WHERE `id`=".injaway($_POST["hausaufgabe_id_".$i]));
		
		$neuer_status=$_POST["status_".$i];
		if (sql_result($alter_status,0,"hausaufgabe.kontrolliert")==-1 and $_POST["status_".$i]==0)
			$neuer_status=-1;
		
		db_conn_and_sql("UPDATE `hausaufgabe` SET `kontrolliert`=".$neuer_status." WHERE `id`=".injaway($_POST["hausaufgabe_id_".$i])); // `bemerkung`=".apostroph_bei_bedarf($_POST["bemerkung_".$i]).", 
		
		$hausaufgabe_vergessen=db_conn_and_sql("SELECT * FROM `hausaufgabe_vergessen` WHERE `hausaufgabe_vergessen`.`hausaufgabe`=".injaway($_POST["hausaufgabe_id_".$i]));
		$vergesser="";
		$k=0;
		while (isset($_POST["hausaufgabe_".$i."_schueler_".$k."_id"])) {
			$vergesser[$k]['schueler_id']=$_POST["hausaufgabe_".$i."_schueler_".$k."_id"];
			if ($_POST["hausaufgabe_".$i."_schueler_".$k."_checkbox"]==1) {
				$vergesser[$k]['erledigt']=0;
				$vergesser[$k]['anzahl']=$_POST["hausaufgabe_".$i."_schueler_".$k."_anzahl"]+1;
				if ($_POST["hausaufgabe_".$i."_schueler_nichtda_".$k."_checkbox"]!="1") $vergesser[$k]['war_da']=1;
			}
			else {
				$vergesser[$k]['erledigt']=1;
				$vergesser[$k]['anzahl']=$_POST["hausaufgabe_".$i."_schueler_".$k."_anzahl"];
				$vergesser[$k]['war_da']=1;
			}
			$k++;
		}
		if (sql_num_rows($hausaufgabe_vergessen)>0) {
			if ($_POST["status_".$i]==-1 or $_POST["status_".$i]==1) {
				for($k=0;$k<count($vergesser);$k++)
					if ($vergesser[$k]['war_da'])
                        db_conn_and_sql("UPDATE `hausaufgabe_vergessen` SET `erledigt`=".$vergesser[$k]['erledigt'].", `anzahl`=".($vergesser[$k]['anzahl']+0)." WHERE `hausaufgabe`=".injaway($_POST["hausaufgabe_id_".$i])." AND `schueler`=".$vergesser[$k]['schueler_id']);
			}
			if ($_POST["status_".$i]==1) db_conn_and_sql("UPDATE `hausaufgabe_vergessen` SET `erledigt`=1 WHERE `hausaufgabe`=".injaway($_POST["hausaufgabe_id_".$i]));
		}
		else {
			if ($_POST["status_".$i]==-1) {
				for($k=0;$k<count($vergesser);$k++)
					if ($vergesser[$k]['erledigt']==0) db_conn_and_sql("INSERT INTO `hausaufgabe_vergessen` (`hausaufgabe`,`schueler`,`erledigt`,`anzahl`,`bemerkung`)
						VALUES (".injaway($_POST["hausaufgabe_id_".$i]).", ".$vergesser[$k]['schueler_id'].", ".$vergesser[$k]['erledigt'].", 1, NULL);");
			}
		}
		$i++;
	}
	
	
	// ----------------------- Test zurueckgegeben --------------------------------
	
	$i=0;
	while ($_POST["rueckgabe_".$i]>0) {
		if ($_POST["rueckgabe_in_dieser_stunde_".$i]!="")
			db_conn_and_sql("UPDATE `notenbeschreibung` SET `zurueckgegeben`=".apostroph_bei_bedarf($_POST["rueckgabe_in_dieser_stunde_".$i]).", `berichtigung`=".leer_NULL($_POST["rueckgabe_berichtigung_".$i]).", `unterschrift`=".leer_NULL($_POST["rueckgabe_unterschrift_".$i])." WHERE `id`=".injaway($_POST["rueckgabe_".$i]));
		$i++;
	}
	
	// ------------------------------- Berichtigung / Unterschrift ---------------------------------------
	
	$i=0;
	while ($_POST["berichtigung_".$i]>0) {
		$alter_status=db_conn_and_sql("SELECT `berichtigung`, `unterschrift` FROM `notenbeschreibung` WHERE `id`=".injaway($_POST["berichtigung_".$i]));
		
		if (sql_result($alter_status,0,"notenbeschreibung.berichtigung")=="0" and $_POST["berichtigung_status_".$i]==1) db_conn_and_sql("UPDATE `notenbeschreibung` SET `berichtigung`=1 WHERE `id`=".injaway($_POST["berichtigung_".$i]));
		if (sql_result($alter_status,0,"notenbeschreibung.unterschrift")=="0" and $_POST["berichtigung_status_".$i]==1) db_conn_and_sql("UPDATE `notenbeschreibung` SET `unterschrift`=1 WHERE `id`=".injaway($_POST["berichtigung_".$i]));
		
		$vergesser="";
		if ($_POST["berichtigung_status_".$i]=="0" or $_POST["berichtigung_status_".$i]=="1") {
			$berichtigung_vergessen=db_conn_and_sql("SELECT * FROM `berichtigung_vergessen` WHERE `berichtigung_vergessen`.`notenbeschreibung`=".injaway($_POST["berichtigung_".$i]));
			$k=0;
			while (isset($_POST["berichtigung_".$i."_schueler_".$k."_id"])) {
				$vergesser[$k]['schueler_id']=$_POST["berichtigung_".$i."_schueler_".$k."_id"];
				if ($_POST["berichtigung_ber_".$i."_schueler_".$k."_checkbox"]==1) {
					$vergesser[$k]['berichtigung_erledigt']=0;
					$vergesser[$k]['berichtigung_anzahl']=$_POST["berichtigung_ber_".$i."_schueler_".$k."_anzahl"]+0;
					if ($_POST["berichtigung_".$i."_schueler_nichtda_".$k."_checkbox"]!="1") {
						$vergesser[$k]['war_da']=1; $vergesser[$k]['berichtigung_anzahl']++;
					}
				}
				else {
					$vergesser[$k]['berichtigung_erledigt']=1;
					$vergesser[$k]['berichtigung_anzahl']=$_POST["berichtigung_ber_".$i."_schueler_".$k."_anzahl"]+0;
				}
				if ($_POST["berichtigung_unt_".$i."_schueler_".$k."_checkbox"]==1) {
					$vergesser[$k]['unterschrift_erledigt']=0;
					$vergesser[$k]['unterschrift_anzahl']=$_POST["berichtigung_unt_".$i."_schueler_".$k."_anzahl"]+0;
					if ($_POST["berichtigung_".$i."_schueler_nichtda_".$k."_checkbox"]!="1") {
						$vergesser[$k]['war_da']=1; $vergesser[$k]['unterschrift_anzahl']++;
					}
				}
				else {
					$vergesser[$k]['unterschrift_erledigt']=1;
					$vergesser[$k]['unterschrift_anzahl']=$_POST["berichtigung_unt_".$i."_schueler_".$k."_anzahl"]+0;
				}
				$k++;
			}
			
			if (sql_num_rows($berichtigung_vergessen)>0) {
				if ($_POST["berichtigung_status_".$i]=="0" or $_POST["berichtigung_status_".$i]=="1") {
					for($k=0;$k<count($vergesser);$k++) { // if ($vergesser[$k]['war_da']) - brauch ich doch net, sonst werden auch die fertigen nicht mitgezaehlt
						db_conn_and_sql("UPDATE `berichtigung_vergessen`
								SET `berichtigung_erledigt`=".$vergesser[$k]['berichtigung_erledigt'].", `berichtigung_anzahl`=".($vergesser[$k]['berichtigung_anzahl']+0).",
									`unterschrift_erledigt`=".$vergesser[$k]['unterschrift_erledigt'].", `unterschrift_anzahl`=".($vergesser[$k]['unterschrift_anzahl']+0)."
								WHERE `notenbeschreibung`=".injaway($_POST["berichtigung_".$i])." AND `schueler`=".$vergesser[$k]['schueler_id']);
						/*echo "UPDATE `berichtigung_vergessen`
								SET `berichtigung_erledigt`=".$vergesser[$k]['berichtigung_erledigt'].", `berichtigung_anzahl`=".($vergesser[$k]['berichtigung_anzahl']+0).",
									`unterschrift_erledigt`=".$vergesser[$k]['unterschrift_erledigt'].", `unterschrift_anzahl`=".($vergesser[$k]['unterschrift_anzahl']+0)."
								WHERE `notenbeschreibung`=".$_POST["berichtigung_".$i]." AND `schueler`=".$vergesser[$k]['schueler_id']."<br />";*/
					}
				}
				if ($_POST["berichtigung_status_".$i]==1) {
					db_conn_and_sql("UPDATE `berichtigung_vergessen` SET `berichtigung_erledigt`=1, `unterschrift_erledigt`=1 WHERE `notenbeschreibung`=".injaway($_POST["berichtigung_".$i]));
					//echo "UPDATE `berichtigung_vergessen` SET `berichtigung_erledigt`=1, `unterschrift_erledigt`=1 WHERE `notenbeschreibung`=".$_POST["berichtigung_".$i]."<br />";
				}
			}
			else {
				if ($_POST["berichtigung_status_".$i]=="0" or $_POST["berichtigung_status_".$i]==1) {
					for($k=0;$k<count($vergesser);$k++)
						if ($vergesser[$k]['berichtigung_erledigt']=="0" or $vergesser[$k]['unterschrift_erledigt']=="0") {
							// FIXME: klappt das bei berichtigung_erledigt?!?! 27.09.2010
							db_conn_and_sql("INSERT INTO `berichtigung_vergessen` (`notenbeschreibung`,`schueler`,`berichtigung_erledigt`,`berichtigung_anzahl`,`unterschrift_erledigt`,`unterschrift_anzahl`)
								VALUES (".injaway($_POST["berichtigung_".$i]).", ".$vergesser[$k]['schueler_id'].", ".$vergesser[$k]['berichtigung_erledigt'].", ".$vergesser[$k]['berichtigung_anzahl'].", ".$vergesser[$k]['unterschrift_erledigt'].", ".$vergesser[$k]['unterschrift_anzahl'].");");
							/*echo "INSERT INTO `berichtigung_vergessen` (`notenbeschreibung`,`schueler`,`berichtigung_erledigt`,`berichtigung_anzahl`,`unterschrift_erledigt`,`unterschrift_anzahl`)
							VALUES (".$_POST["berichtigung_".$i].", ".$vergesser[$k]['schueler_id'].", ".$vergesser[$k]['berichtigung_erledigt'].", ".$vergesser[$k]['berichtigung_anzahl'].", ".$vergesser[$k]['unterschrift_erledigt'].", ".$vergesser[$k]['unterschrift_anzahl'].");<br />";*/
						}
				}
			}
		}
		$i++;
	}
	?>
    <html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
	
	<?php
}
else {
	$titelleiste="Nachbereitung";
	include($pfad."header.php");
	?>
  <body>
	<div id="mf">
		<ul class="r">
			<li><a href="javascript:window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
	<div class="inhalt">
    
	<?php $plan=$db->plan(injaway($_GET['plan']));
		$plan_id=injaway($_GET["plan"]);
		$block1=db_conn_and_sql("SELECT * FROM `block` WHERE `id`=".sql_result($plan,0,"plan.block_1"));
		if (sql_result($plan,0,"plan.block_2")!="")
			$block2=db_conn_and_sql("SELECT * FROM `block` WHERE `id`=".sql_result($plan,0,"plan.block_2"));
		
		$eintraege=fachklassen_zeitplanung(sql_result($plan,0,"plan.fach_klasse"),sql_result($plan,0,"plan.schuljahr"));
		$j=0; while($eintraege[$j]["plan_id"]!=$_GET['plan']) $j++; $k=$j+1; while($eintraege[$k]["typ"]=="feiertag" or $eintraege[$k]["typ"]=="ausfall") $k++;
		$naechster_plan=@db_conn_and_sql("SELECT * FROM `plan` WHERE `id`=".$eintraege[$k]["plan_id"]); ?>
	<form action="./nachbereiten.php?plan=<?php echo $_GET["plan"]; ?>&amp;eintragen=true&amp;naechster=<?php if (isset($eintraege[$k]["plan_id"])) echo $eintraege[$k]["plan_id"]; else echo "neu&amp;datum=".$eintraege[$k]["datum"]."&amp;zeit=".$eintraege[$k]["zeit"]; if(sql_result($plan,0,"plan.block_2")!="") echo "&amp;block2=set"; ?>" method="post" accept-charset="ISO-8859-1">
	
	<?php //eigentlich Verschwendung von Ressourcen, aber ich brauch die Hausaufgaben
	$plan_ha=planelemente(injaway($_GET["plan"]),"nicht bearbeiten",$pfad); ?>
	<fieldset>
		<legend><img src="<?php echo $pfad; ?>icons/hausaufgaben.png" alt="hausaufgaben" title="Hausaufgaben" /> Hausaufgaben <img id="img_hausaufgaben" src="<?php echo $pfad; ?>icons/clip_<?php if (isset($plan_ha['hausaufgaben_kontrolle'])) echo 'open'; else echo 'closed'; ?>.png" alt="clip" onclick="javascript:clip('hausaufgaben','<?php echo $pfad; ?>')" /></legend>
		<span id="span_hausaufgaben"<?php if (!isset($plan_ha['hausaufgaben_kontrolle'])) echo ' style="display: none"'; ?>>
	<?php
    $schueler=db_conn_and_sql("SELECT *
        FROM schueler, gruppe
        WHERE gruppe.fach_klasse=".sql_result($plan,0,"plan.fach_klasse")."
            AND schueler.aktiv=1
            AND gruppe.schueler=schueler.id
        ORDER BY schueler.position, schueler.name,schueler.vorname");
    if (sql_num_rows($schueler)<1)
        $schueler=db_conn_and_sql("SELECT *
			FROM `schueler`,`klasse`,`fach_klasse`
			WHERE `schueler`.`klasse`=`klasse`.`id`
				AND `fach_klasse`.`klasse`=`klasse`.`id`
				AND `fach_klasse`.`id`=".sql_result($plan,0,"plan.fach_klasse")."
				AND `schueler`.`aktiv`=1
			ORDER BY schueler.position, schueler.name, schueler.vorname");

	if (isset($plan_ha['hausaufgaben_kontrolle'])) {
			echo "<b><u>Hausaufgabenkontrolle:</u></b><br />";
			$k=0;
			foreach ($plan_ha['hausaufgaben_kontrolle'] as $hausaufgabe) {
				if ($k>0) echo "<hr />";
				echo hausaufgabe_zeigen($hausaufgabe).'
 					<a href="'.$pfad.'formular/hausaufgaben.php?plan='.$_GET["plan"].'&amp;block='.sql_result($block1,0,"block.id").'&amp;hausaufgabe='.$hausaufgabe["id"].'" onclick="fenster(this.href,\'Hausaufgaben eintragen\'); return false;" title="bearbeiten" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
					<a href="'.$pfad.'formular/hausaufgaben.php?plan='.$_GET["plan"].'&amp;loeschen=true&amp;hausaufgabe='.$hausaufgabe["id"].'" onclick="fenster(this.href,\'Hausaufgabe l&ouml;schen\'); return false;" title="Hausaufgabe l&ouml;schen" class="icon"><img src="'.$pfad.'icons/delete.png" alt="l&ouml;schen" /></a><br />
					<!-- <b>Wer:</b> '.$hausaufgabe["zielgruppe"]." <b>Ziel:</b> ".$hausaufgabe["ziel"]." <b>Zeit:</b> ".$hausaufgabe["zeit"]." (bis ".$hausaufgabe['abgabedatum'].")";
				if(count($hausaufgabe["aufgaben"])>0) foreach ($hausaufgabe["aufgaben"] as $aufgabe) echo "<br />".$aufgabe["inhalt"];
				echo '<a href="'.$pfad.'formular/hausaufgaben.php?plan='.$plan_id.'&amp;block='.sql_result($block1,0,"block.id").'&amp;hausaufgabe='.$hausaufgabe["id"].'" onclick="fenster(this.href,\'Hausaufgaben eintragen\'); return false;" title="bearbeiten" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
						<a href="'.$pfad.'formular/hausaufgaben.php?plan='.$plan_id.'&amp;loeschen=true&amp;hausaufgabe='.$hausaufgabe["id"].'" onclick="fenster(this.href,\'Hausaufgabe l&ouml;schen\'); return false;" title="Hausaufgabe l&ouml;schen" class="icon"><img src="'.$pfad.'icons/delete.png" alt="l&ouml;schen" /></a>-->
						<input type="hidden" name="hausaufgabe_id_'.$k.'" value="'.$hausaufgabe['id'].'" />'; // '; if ($hausaufgabe['status']==0) echo ' checked="checked"'; echo '
				echo 'Status: <input type="radio" name="status_'.$k.'" checked="checked" value="0" onclick="document.getElementById(\'hausaufgaben_span_'.$k.'\').style.display = \'none\'" /> nicht kontrolliert;
					<input type="radio" name="status_'.$k.'" value="-1" onclick="document.getElementById(\'hausaufgaben_span_'.$k.'\').style.display = \'block\'" /> teilweise;
					<input type="radio" name="status_'.$k.'" value="1" onclick="document.getElementById(\'hausaufgaben_span_'.$k.'\').style.display = \'block\'" /> fertig';
				echo '<!--<br />Bemerkung:<br /><textarea name="bemerkung_'.$k.'" cols="50" rows="5">'.$hausaufgabe['bemerkung'].'</textarea>--><br />';
				
				echo '<span id="hausaufgaben_span_'.$k.'" style="display:none;"><b>Vergessen:</b> '; // '; if ($hausaufgabe['status']!=-1) echo '
                // $schueler=db_conn_and_sql("SELECT * FROM `schueler`,`klasse`,`fach_klasse` WHERE `schueler`.`klasse`=`klasse`.`id` AND `fach_klasse`.`klasse`=`klasse`.`id` AND `fach_klasse`.`id`=".sql_result($plan,0,"plan.fach_klasse")." AND `schueler`.`aktiv`=1 ORDER BY `schueler`.`vorname`");
				$hilf=0;
				$hausaufgabe_vergessen_algemein=db_conn_and_sql("SELECT `hausaufgabe_vergessen`.`schueler` FROM `hausaufgabe_vergessen` WHERE `hausaufgabe_vergessen`.`hausaufgabe`=".$hausaufgabe["id"]." AND `hausaufgabe_vergessen`.`erledigt`=0");
				for($m=0;$m<sql_num_rows($schueler);$m++)
                    if (gehoert_zur_gruppe(sql_result($plan,0,"plan.fach_klasse"), sql_result($schueler,$m,"schueler.id"))) {					
					$hausaufgabe_vergessen=db_conn_and_sql("SELECT * FROM `hausaufgabe_vergessen` WHERE `hausaufgabe_vergessen`.`hausaufgabe`=".$hausaufgabe["id"]." AND `hausaufgabe_vergessen`.`erledigt`=0 AND `hausaufgabe_vergessen`.`schueler`=".sql_result($schueler,$m,"schueler.id"));
					if (sql_num_rows($hausaufgabe_vergessen_algemein)>0) {
						if (sql_result($schueler,$m,"schueler.id")==@sql_result($hausaufgabe_vergessen,0,"hausaufgabe_vergessen.schueler")) {
							echo '<input type="hidden" name="hausaufgabe_'.$k.'_schueler_'.$hilf.'_id" value="'.sql_result($schueler,$m,"schueler.id").'" />
								<input type="hidden" name="hausaufgabe_'.$k.'_schueler_'.$hilf.'_anzahl" value="'.sql_result($hausaufgabe_vergessen,0,"hausaufgabe_vergessen.anzahl").'" />
								<input type="checkbox" name="hausaufgabe_'.$k.'_schueler_'.$hilf.'_checkbox" value="1" onclick="document.getElementById(\'hausaufgabe_'.$k.'_schueler_nichtda_'.$hilf.'_checkbox\').style.display=this.checked==1?\'inline\':\'none\';" title="'.html_umlaute(sql_result($schueler,$m,"schueler.name")).', '.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).' hat die HA nicht gehabt..." />
								<input type="checkbox" id="hausaufgabe_'.$k.'_schueler_nichtda_'.$hilf.'_checkbox" name="hausaufgabe_'.$k.'_schueler_nichtda_'.$hilf.'_checkbox" style="display:none;" value="1" title="...wird nicht mitgez&auml;hlt (war z.B. nicht da)" />
								'.html_umlaute(substr(sql_result($schueler,$m,"schueler.name"),0,1)).'., '.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).' ('.sql_result($hausaufgabe_vergessen,0,"hausaufgabe_vergessen.anzahl").') - ';
							$hilf++;
						}
					}
					else echo '<input type="hidden" name="hausaufgabe_'.$k.'_schueler_'.$m.'_id" value="'.sql_result($schueler,$m,"schueler.id").'" />
						<input type="checkbox" name="hausaufgabe_'.$k.'_schueler_'.$m.'_checkbox" value="1" onclick="document.getElementById(\'hausaufgabe_'.$k.'_schueler_nichtda_'.$m.'_checkbox\').style.display=this.checked==1?\'inline\':\'none\';" title="'.html_umlaute(sql_result($schueler,$m,"schueler.name")).', '.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).' hat die HA nicht gehabt..." />
						<input type="checkbox" id="hausaufgabe_'.$k.'_schueler_nichtda_'.$m.'_checkbox" name="hausaufgabe_'.$k.'_schueler_nichtda_'.$m.'_checkbox" style="display:none;" value="1" title="...wird nicht mitgez&auml;hlt (war z.B. nicht da)" />
						'.html_umlaute(substr(sql_result($schueler,$m,"schueler.name"),0,1)).'., '.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).' - ';
				}
				echo '</span>';
				$k++;
			}
		echo '<hr />';
	}
	if (isset($plan_ha['hausaufgaben_vergeben'])) {
		echo '<b>Heute vergeben:</b>';
		foreach ($plan_ha['hausaufgaben_vergeben'] as $hausaufgabe) echo hausaufgabe_zeigen($hausaufgabe).'
 		<a href="'.$pfad.'formular/hausaufgaben.php?plan='.$_GET["plan"].'&amp;block='.sql_result($block1,0,"block.id").'&amp;hausaufgabe='.$hausaufgabe["id"].'" onclick="fenster(this.href,\'Hausaufgaben eintragen\'); return false;" title="bearbeiten" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
		<a href="'.$pfad.'formular/hausaufgaben.php?plan='.$_GET["plan"].'&amp;loeschen=true&amp;hausaufgabe='.$hausaufgabe["id"].'" onclick="fenster(this.href,\'Hausaufgabe l&ouml;schen\'); return false;" title="Hausaufgabe l&ouml;schen" class="icon"><img src="'.$pfad.'icons/delete.png" alt="l&ouml;schen" /></a><hr />';
	}
	?>
	
	<button name="hausaufgaben_eintragen" type="button" onclick="fenster('<?php echo $pfad; ?>formular/hausaufgaben.php?plan=<?php echo $_GET["plan"]; ?>&amp;block=<?php echo sql_result($block1,0,"block.id"); ?>','Hausaufgaben eintragen');">
			<img src="<?php echo $pfad; ?>icons/hausaufgaben.png" alt="hausaufgaben" /> zus&auml;tzliche Hausaufgabe eintragen</button>
	<!--<input type="checkbox" name="zusatzhausaufgaben" onclick="document.getElementById('zusatz_hausaufgaben').style.display=this.checked==1?'block':'none';" /><br />
	<span id="zusatz_hausaufgaben" style="display:none;">
		Abgabetermin: <input type="text" name="zha_abgabedatum" value="" size="7" /><br />
		Bewerten: <input type="checkbox" name="zha_kontrollieren" value="true" title="wenn mehr als nur die Durchf&uuml;hrung der Hausaufgabe kontrolliert werden soll, setzen Sie hier ein H&auml;kchen" /><br />
		Kommentar zur Verbindung dieser HA mit der Unterrichtsstunde:<br /><textarea name="zha_bemerkung" rows="2" cols="50" title="Dieser Kommentar tritt nur in Verbindung mit dieser Unterrichtsstunde auf, ist also in der n&auml;chsten Klasse nicht mehr vorhanden."></textarea><br />
		Zielgruppe: <input type="text" name="zha_zielgruppe" value="Alle" title="z.B. Alle, zuf&auml;llig Ausgew&auml;hlte, fakultativ, St&ouml;renfriede..." /><br />
		Ziel: <input type="text" name="zha_ziel" title="z.B. Wiederholung / Festigung bestimmter Aufgaben" />
	</span>-->
	</span>
	</fieldset>
	
	<?php
	if (isset($plan_ha['test_rueckgabe']) or isset($plan_ha['berichtigung_kontrolle'])) {
		echo '	<fieldset>
	<legend><img src="'.$pfad.'icons/test.png" alt="test" title="Tests" /> Berichtigung/Unterschrift <img id="img_berichtigung" src="'.$pfad.'icons/clip_open.png" alt="clip" onclick="javascript:clip(\'berichtigung\',\''.$pfad.'\')" /></legend>
		<span id="span_berichtigung">';
		$k=0;
		if (isset($plan_ha['test_rueckgabe'])) foreach ($plan_ha['test_rueckgabe'] as $test_rueckgabe) {
			if ($k>0) echo "<hr />";
			echo '<b>'.$test_rueckgabe['notentyp_kuerzel'].' '.$test_rueckgabe['beschreibung'].'</b>';
			if ($test_rueckgabe['kommentar']!="") echo ' ('.$test_rueckgabe['kommentar'].')';
			echo ' vom: '.$test_rueckgabe['datum'].' (korrigiert am '.$test_rueckgabe['korrigiert'].')<br />
				in dieser Stunde zur&uuml;ckgegeben:
				<input type="hidden" name="rueckgabe_'.$k.'" value="'.$test_rueckgabe["id"].'" />
				<input type="checkbox" name="rueckgabe_in_dieser_stunde_'.$k.'" value="'.sql_result($plan,0,"plan.datum").'" onclick="document.getElementById(\'rueckgabe_span_'.$k.'\').style.display=this.checked==1?\'inline\':\'none\';" />
				<span id="rueckgabe_span_'.$k.'" style="display:none;">
					Berichtigung pr&uuml;fen: <input type="checkbox" name="rueckgabe_berichtigung_'.$k.'" value="0" checked="checked" /> -
					Unterschrift pr&uuml;fen: <input type="checkbox" name="rueckgabe_unterschrift_'.$k.'" value="0" checked="checked" /></span><br />';
			$k++;
		}
		$hilf=$k;
		$k=0;
		
		if (isset($plan_ha['berichtigung_kontrolle'])) foreach ($plan_ha['berichtigung_kontrolle'] as $berichtigung) {
			if ($hilf>0 or $k>0) echo "<hr />";
			echo '<input type="hidden" name="berichtigung_notenbeschreibung_id_'.$k.'" value="'.$berichtigung['id'].'" />';
			echo '<b>';
			if ($berichtigung['berichtigung_gefordert']=="0") echo 'Berichtigung';
			if ($berichtigung['berichtigung_gefordert']=="0" and $berichtigung['unterschrift_gefordert']=="0") echo '/';
			if ($berichtigung['unterschrift_gefordert']=="0") echo 'Unterschrift';
			echo '</b> der '.$berichtigung['notentyp_kuerzel'].' '.$berichtigung['beschreibung'];
			if ($berichtigung['kommentar']!="") echo ' ('.$berichtigung['kommentar'].')';
			echo ' vom: '.$berichtigung['datum'].' (zur&uuml;ckgegeben am '.$berichtigung['zurueckgegeben'].')
				<input type="hidden" name="berichtigung_'.$k.'" value="'.$berichtigung["id"].'" /><br />';
			echo 'Status:
					<input type="radio" name="berichtigung_status_'.$k.'" value="-1" onclick="document.getElementById(\'berichtigung_span_'.$k.'\').style.display = \'none\'" /> nicht kontrolliert;
					<input type="radio" name="berichtigung_status_'.$k.'" value="0" checked="checked"'; /*if ($berichtigung['berichtigung_gefordert']=="0" or $berichtigung['unterschrift_gefordert']=="0") echo ' checked="checked"';*/ echo ' onclick="document.getElementById(\'berichtigung_span_'.$k.'\').style.display = \'block\'" /> teilweise;
					<input type="radio" name="berichtigung_status_'.$k.'" value="1"'; /*if ($berichtigung['berichtigung_gefordert']==1 and $berichtigung['unterschrift_gefordert']==1) echo ' checked="checked"';*/ echo ' onclick="document.getElementById(\'berichtigung_span_'.$k.'\').style.display = \'block\'" /> fertig';
			echo '<span id="berichtigung_span_'.$k.'" style="display: block;">fehlt von: ';
				//$schueler=db_conn_and_sql("SELECT * FROM `schueler`,`klasse`,`fach_klasse` WHERE `schueler`.`klasse`=`klasse`.`id` AND `fach_klasse`.`klasse`=`klasse`.`id` AND `fach_klasse`.`id`=".sql_result($plan,0,"plan.fach_klasse")." ORDER BY `schueler`.`vorname`");
				$hilf=0;
				$berichtigung_vergessen=db_conn_and_sql("SELECT `berichtigung_vergessen`.`schueler` FROM `berichtigung_vergessen` WHERE `berichtigung_vergessen`.`notenbeschreibung`=".$berichtigung["id"]." AND (`berichtigung_vergessen`.`berichtigung_erledigt`=0 OR `berichtigung_vergessen`.`unterschrift_erledigt`=0)");
				for($m=0;$m<sql_num_rows($schueler);$m++) if (gehoert_zur_gruppe(sql_result($plan,0,"plan.fach_klasse"), sql_result($schueler,$m,"schueler.id"))) {
					$berichtigung_vergessen_einzeln=db_conn_and_sql("SELECT * FROM `berichtigung_vergessen` WHERE `berichtigung_vergessen`.`notenbeschreibung`=".$berichtigung["id"]." AND (`berichtigung_vergessen`.`berichtigung_erledigt`=0 OR `berichtigung_vergessen`.`unterschrift_erledigt`=0) AND `berichtigung_vergessen`.`schueler`=".sql_result($schueler,$m,"schueler.id"));
					if (sql_num_rows($berichtigung_vergessen)>0) {
						if (sql_result($schueler,$m,"schueler.id")==@sql_result($berichtigung_vergessen_einzeln,0,"berichtigung_vergessen.schueler")) {
							echo '<input type="hidden" name="berichtigung_'.$k.'_schueler_'.$hilf.'_id" value="'.sql_result($schueler,$m,"schueler.id").'" />
								<input type="hidden" name="berichtigung_ber_'.$k.'_schueler_'.$hilf.'_anzahl" value="'.sql_result($berichtigung_vergessen_einzeln,0,"berichtigung_vergessen.berichtigung_anzahl").'" />
								<input type="hidden" name="berichtigung_unt_'.$k.'_schueler_'.$hilf.'_anzahl" value="'.sql_result($berichtigung_vergessen_einzeln,0,"berichtigung_vergessen.unterschrift_anzahl").'" />';
 							if ($berichtigung['berichtigung_gefordert']=="0" and sql_result($berichtigung_vergessen_einzeln,0,"berichtigung_vergessen.berichtigung_erledigt")!=1) echo '<input type="checkbox" name="berichtigung_ber_'.$k.'_schueler_'.$hilf.'_checkbox" value="1" onclick="document.getElementById(\'berichtigung_'.$k.'_schueler_nichtda_'.$hilf.'_checkbox\').style.display=this.checked==1?\'inline\':\'none\';" title="'.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).' '.html_umlaute(sql_result($schueler,$m,"schueler.name")).' hat die Berichtigung nicht gehabt..." />';
							if ($berichtigung['unterschrift_gefordert']=="0" and sql_result($berichtigung_vergessen_einzeln,0,"berichtigung_vergessen.unterschrift_erledigt")!=1) echo '<input type="checkbox" name="berichtigung_unt_'.$k.'_schueler_'.$hilf.'_checkbox" value="1" onclick="document.getElementById(\'berichtigung_'.$k.'_schueler_nichtda_'.$hilf.'_checkbox\').style.display=this.checked==1?\'inline\':\'none\';" title="'.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).' '.html_umlaute(sql_result($schueler,$m,"schueler.name")).' hat die Unterschrift nicht gehabt..." />';
							echo '<input type="checkbox" id="berichtigung_'.$k.'_schueler_nichtda_'.$hilf.'_checkbox" name="berichtigung_'.$k.'_schueler_nichtda_'.$hilf.'_checkbox" style="display:none;" value="1" title="...soll aber nicht gez&auml;hlt werden (z.B. war nicht da)" />';
							echo html_umlaute(substr(sql_result($schueler,$m,"schueler.name"),0,1)).'., '.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).' (';
							if ($berichtigung['berichtigung_gefordert']=="0") echo 'B'.sql_result($berichtigung_vergessen_einzeln,0,"berichtigung_vergessen.berichtigung_anzahl");
							if ($berichtigung['berichtigung_gefordert']=="0" and $berichtigung['unterschrift_gefordert']=="0") echo '/';
							if ($berichtigung['unterschrift_gefordert']=="0") echo 'U'.sql_result($berichtigung_vergessen_einzeln,0,"berichtigung_vergessen.unterschrift_anzahl");
							echo ') - ';
							$hilf++;
						}
					}
					else {
						echo '<input type="hidden" name="berichtigung_'.$k.'_schueler_'.$hilf.'_id" value="'.sql_result($schueler,$m,"schueler.id").'" />';
						if ($berichtigung['berichtigung_gefordert']=="0") echo '<input type="checkbox" name="berichtigung_ber_'.$k.'_schueler_'.$hilf.'_checkbox" value="1" onclick="document.getElementById(\'berichtigung_'.$k.'_schueler_nichtda_'.$hilf.'_checkbox\').style.display=this.checked==1?\'inline\':\'none\';" title="'.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).' '.html_umlaute(sql_result($schueler,$m,"schueler.name")).' hat die Berichtigung nicht gehabt..." />';
						if ($berichtigung['unterschrift_gefordert']=="0") echo '<input type="checkbox" name="berichtigung_unt_'.$k.'_schueler_'.$hilf.'_checkbox" value="1" onclick="document.getElementById(\'berichtigung_'.$k.'_schueler_nichtda_'.$hilf.'_checkbox\').style.display=this.checked==1?\'inline\':\'none\';" title="'.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).' '.html_umlaute(sql_result($schueler,$m,"schueler.name")).' hat die Unterschrift nicht gehabt..." />';
						echo '<input type="checkbox" id="berichtigung_'.$k.'_schueler_nichtda_'.$hilf.'_checkbox" name="berichtigung_'.$k.'_schueler_nichtda_'.$hilf.'_checkbox" style="display:none;" value="1" title="...soll aber nicht gez&auml;hlt werden (z.B. war nicht da)" />';
						echo html_umlaute(substr(sql_result($schueler,$m,"schueler.name"),0,1)).'., '.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).' - ';
						$hilf++;
					}
				}
				echo '</span>';
				$k++;
			}
		echo "</span></fieldset>";
	}
	?>
	
	
	<table><tr><td>Kommentare zum Block <?php echo html_umlaute(@sql_result($block1,0,"block.name")); ?>:<br />
		<textarea name="block1_kommentar" cols="60" rows="3"><?php echo html_umlaute(@sql_result($block1,0,"block.kommentare")); ?></textarea></td>
		<?php if (sql_result($plan,0,"plan.block_2")!="")
		echo '<td>Kommentare zum Block '.html_umlaute(@sql_result($block2,0,"block.name")).':<br /><textarea name="block2_kommentar" cols="60" rows="3">'.html_umlaute(@sql_result($block2,0,"block.kommentare")).'</textarea></td>'; ?>
	</tr></table>
	<table class="einzelstunde" cellspacing="0"><tr>
			<th title="als Hausaufgabe aufgegeben / Abschnitt in n&auml;chsten Plan &uuml;bernehmen / Abschnitt aus Plan entfernen" style="width:110px;">als HA / &uuml;bernehmen / entfernen</th>
			<th title="Geben Sie hier die korrigierte Anzahl Minuten an, welche Sie f&uuml;r diesen Abschnitt bei dieser Klassenstufe allgemein ben&ouml;tigen werden.">Abschnitts-<br />Zeit <img src="<?php echo $pfad; ?>icons/abschnitt.png" alt="abschnitt" /></th>
			<th><img src="<?php echo $pfad; ?>icons/zeit.png" alt="zeit" title="Zeit" /> tats&auml;chlich ben&ouml;tigte Zeit</th>
			<th>Inhalt</th>
			<th title="Der Nachbereitungskommentar erscheint beim Abschnitt allgemein (ist unabh&auml;ngig vom Plan)"><img src="<?php echo $pfad; ?>icons/kommentar.png" alt="kommentar" /> Nachbereitungskommentar</th></tr>
	<?php
	$abschnitte=db_conn_and_sql("SELECT * FROM `abschnittsplanung` WHERE `abschnittsplanung`.`plan`=".injaway($_GET["plan"])." ORDER BY `abschnittsplanung`.`position`");
	for ($i=0;$i<sql_num_rows($abschnitte);$i++) {
		$abschnitt=$db->abschnitt(sql_result($abschnitte,$i,"abschnittsplanung.abschnitt"));
		echo '<tr><td><!--<input type="checkbox" name="aktion_nix'.$i.'" value="1" title="Abschnitt im Plan lassen" checked="checked" />--><input type="checkbox" name="aktion_als_HA_'.$i.'" value="1" onclick="document.getElementById(\'aktion_entfernen_'.$i.'\').checked=checked;" title="als Hausaufgabe aufgegeben" /><input type="checkbox" name="aktion_verschieben_'.$i.'" value="1" title="Abschnitt in n&auml;chsten Plan &uuml;bernehmen" /><input type="checkbox" id="aktion_entfernen_'.$i.'" name="aktion_entfernen_'.$i.'" value="1" title="Abschnitt aus Plan entfernen" /><br />
				&nbsp;<!--<img src="'.$pfad.'icons/ok.png" alt="ok" title="ok" />--><img src="'.$pfad.'icons/hausaufgaben.png" alt="hausaufgabe" title="als Hausaufgabe aufgeben" /> <img src="'.$pfad.'icons/pfeil_rechts.png" alt="pfeil_rechts" title="in n&auml;chsten Plan &uuml;bernehmen" /> <img src="'.$pfad.'icons/entfernen.png" alt="l&ouml;schen" title="aus Plan entfernen" /></td>
			<td>';
        if (sql_result($abschnitte,$i,"abschnittsplanung.abschnitt")>0)
            echo '<input type="text" name="abschnitt_minuten_'.$i.'" size="1" maxlength="2" value="'.$abschnitt["minuten"].'" /> min';
        echo '</td>
			<td><input type="text" name="plan_minuten_'.$i.'" id="plan_minuten_'.$i.'" size="1" maxlength="2" value="'.sql_result($abschnitte,$i,"abschnittsplanung.minuten").'" /> min</td>
			<td>'.abschnittsinhalt($abschnitt,"bearbeiten",$pfad, injaway($_GET["plan"])).syntax_zu_html(sql_result($abschnitte,$i,"abschnittsplanung.inhalt"), 1, 0, $pfad, "A").'</td>
			<td><textarea name="nachbereitung_'.$i.'" cols="30" rows="3">'.$abschnitt["nachbereitung"].'</textarea></td></tr>';
	}
   ?>
	</table>
	
	<fieldset style="background-color: #f9efe2;"><!--  onmouseover="document.getElementById('verwarnungen_anzeige').style.display='block';" onmouseout="document.getElementById('verwarnungen_anzeige').style.display='none';" -->
		<legend><img src="<?php echo $pfad; ?>icons/verwarnung.png" alt="betragen" title="Betragen" /> Betragen <img id="img_verwarnungen" src="<?php echo $pfad; ?>icons/clip_closed.png" alt="clip" onclick="javascript:clip('verwarnungen','<?php echo $pfad; ?>')" /></legend>
		<span id="span_verwarnungen" style="display: none">
		<ul>
		<?php
		for($m=0;$m<sql_num_rows($schueler);$m++) {
			echo '<li style="float: left; width: 150px; margin-left: 10px; padding-left: 10px;';
			if (!gehoert_zur_gruppe(sql_result($plan,0,"plan.fach_klasse"), sql_result($schueler,$m,"schueler.id"))) echo ' display: none;';
			echo '" title="'.html_umlaute(sql_result($schueler,$m,"schueler.name")).', '.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).'"><input type="hidden" name="verwarnung_schueler_'.$m.'" value="'.sql_result($schueler,$m,"schueler.id").'" />';
			echo html_umlaute(substr(sql_result($schueler,$m,"schueler.name"),0,1)).'., '.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).' <select name="verwarnung_'.$m.'">';
			for($hilf=-3; $hilf<=3;$hilf++) { echo '<option value="'.$hilf.'"'; if ($hilf==0) echo ' selected="selected"'; echo '>'; if ($hilf>0) echo "+"; echo $hilf.'</option>'; }
			echo '</select></li>';
		}
		?>
		</ul>
		</span>
	</fieldset>
	
	<fieldset style="background-color: #f9efe2;"><!--  onmouseover="document.getElementById('verwarnungen_anzeige').style.display='block';" onmouseout="document.getElementById('verwarnungen_anzeige').style.display='none';" -->
		<legend><img src="<?php echo $pfad; ?>icons/mitarbeit.png" alt="verwarnung" title="Mitarbeit" /> Mitarbeit <img id="img_mitarbeit" src="<?php echo $pfad; ?>icons/clip_closed.png" alt="clip" onclick="javascript:clip('mitarbeit','<?php echo $pfad; ?>')" /></legend>
		<span id="span_mitarbeit" style="display: none">
		<ul>
		<?php
        //$schueler=db_conn_and_sql("SELECT * FROM `schueler`,`klasse`,`fach_klasse` WHERE `schueler`.`klasse`=`klasse`.`id` AND `fach_klasse`.`klasse`=`klasse`.`id` AND `fach_klasse`.`id`=".sql_result($plan,0,"plan.fach_klasse")." ORDER BY `schueler`.`id`");
		for($m=0;$m<sql_num_rows($schueler);$m++) {
			echo '<li style="float: left; width: 150px; margin-left: 10px; padding-left: 10px;';
			if (!gehoert_zur_gruppe(sql_result($plan,0,"plan.fach_klasse"), sql_result($schueler,$m,"schueler.id"))) echo ' display: none;';
			echo '" title="'.html_umlaute(sql_result($schueler,$m,"schueler.name")).', '.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).'"><input type="hidden" name="mitarbeit_schueler_'.$m.'" value="'.sql_result($schueler,$m,"schueler.id").'" />';
			echo html_umlaute(substr(sql_result($schueler,$m,"schueler.name"),0,1)).'., '.html_umlaute(sql_result($schueler,$m,"schueler.vorname")).' <select name="mitarbeit_'.$m.'">';
			for($hilf=-3; $hilf<=3;$hilf++) { echo '<option value="'.$hilf.'"'; if ($hilf==0) echo ' selected="selected"'; echo '>'; if ($hilf>0) echo "+"; echo $hilf.'</option>'; }
			echo '</select></li>';
		}
		?>
		</ul>
		</span>
	</fieldset>
	
	<fieldset style="background-color: #e9efe2;"><legend><img src="<?php echo $pfad; ?>icons/statistik.png" alt="statistik" title="Auswertungs-Statistik" /> Auswertungsfragen <img id="img_auswertung" src="<?php echo $pfad; ?>icons/clip_closed.png" alt="clip" onclick="javascript:clip('auswertung','<?php echo $pfad; ?>')" /></legend>
		<span id="span_auswertung" style="display: none;">
		<table cellspacing="0" class="tabelle">
			<tr><th>Beobachtung</th><th>+2 <img src="<?php echo $pfad; ?>icons/smiley_5.png" alt="smiley" /></th><th>+1 <img src="<?php echo $pfad; ?>icons/smiley_4.png" alt="smiley" /></th><th>0 <img src="<?php echo $pfad; ?>icons/smiley_3.png" alt="smiley" /></th><th>-1 <img src="<?php echo $pfad; ?>icons/smiley_2.png" alt="smiley" /></th><th>-2 <img src="<?php echo $pfad; ?>icons/smiley_1.png" alt="smiley" /></th></tr>
			<?php $beobachtungen=array(
				array('name'=>'interesse', 'kurzbeschreibung'=>'Interesse / Motivation', 'title'=>'Fanden die Sch&uuml;ler den Unterricht interessant oder langweilig?'),
				array('name'=>'lerneinschaetzung', 'kurzbeschreibung'=>'Lerneinsch&auml;tzung', 'title'=>'Haben die Sch&uuml;ler viel oder wenig gelernt?'),
				array('name'=>'stoffbewaeltigung', 'kurzbeschreibung'=>'Stoffbew&auml;ltigung', 'title'=>'Haben die Sch&uuml;ler den Stoff verstanden?'),
				array('name'=>'lerntempo', 'kurzbeschreibung'=>'Lerntempo', 'title'=>'War das Lerntempo richtig, oder falsch?'),
				array('name'=>'methode', 'kurzbeschreibung'=>'Methode', 'title'=>'Waren die Unterrichtsmethoden gut?'),
				array('name'=>'lehrersprache', 'kurzbeschreibung'=>'Lehrersprache', 'title'=>'Haben Sie die Thematik gut erkl&auml;rt?'),
				array('name'=>'lob_geben', 'kurzbeschreibung'=>'Lob geben', 'title'=>'Haben Sie die Sch&uuml;ler an passenden Stellen gelobt?'),
				array('name'=>'angstfaktor', 'kurzbeschreibung'=>'Angstfaktor', 'title'=>'Haben Sie sich wohl gef&uuml;hlt?'),
				array('name'=>'selbsteinschaetzung', 'kurzbeschreibung'=>'Selbsteinsch&auml;tzung', 'title'=>'Sind Sie mit Ihrer Leistung zufrieden?')
			);
			
			foreach($beobachtungen as $einzelbeobachtung) {
				echo '<tr><td title="'.$einzelbeobachtung['title'].'">'.$einzelbeobachtung['kurzbeschreibung'].'</td>';
				for ($i=2;$i>=-2;$i--) echo '<td><input type="radio" name="'.$einzelbeobachtung['name'].'" value="'.$i.'" /></td>';
				echo '</tr>';
			}
			/*
			<tr><td title="Die Sch&uuml;ler werden gut &uuml;ber den geplanten Verlauf informiert. Ein roter Faden ist erkennbar.">Klare Struktur</td><td><input type="radio" name="klare_struktur" value="2" /></td><td><input type="radio" name="klare_struktur" value="1" /></td><td><input type="radio" name="klare_struktur" value="-1" /></td><td><input type="radio" name="klare_struktur" value="-2" /></td></tr>
			<tr><td title="Das Unterrichtstempo ist dem Leistungsverm&ouml;gen angepasst. Die vorhandene Zeit wird effektiv genutzt.">Hoher Lernzeitanteil</td><td><input type="radio" name="hoher_lernzeitanteil" value="2" /></td><td><input type="radio" name="hoher_lernzeitanteil" value="1" /></td><td><input type="radio" name="hoher_lernzeitanteil" value="-1" /></td><td><input type="radio" name="hoher_lernzeitanteil" value="-2" /></td></tr>
			<tr><td title="St&ouml;rungen werden z&uuml;gig behoben">St&ouml;rungen</td><td><input type="radio" name="stoerungen" value="2" /></td><td><input type="radio" name="stoerungen" value="1" /></td><td><input type="radio" name="stoerungen" value="-1" /></td><td><input type="radio" name="stoerungen" value="-2" /></td></tr>
			<tr><td title="verst&auml;ndliche Formulierung; fachlich korrekt">Inhaltliche Klarheit</td><td><input type="radio" name="inhaltliche_klarheit" value="2" /></td><td><input type="radio" name="inhaltliche_klarheit" value="1" /></td><td><input type="radio" name="inhaltliche_klarheit" value="-1" /></td><td><input type="radio" name="inhaltliche_klarheit" value="-2" /></td></tr>
			<tr><td title="Leistungserwartungen werden besprochen. Sch&uuml;ler erhalten z&uuml;gig R&uuml;ckmeldungen.">Transparente Leistungserwartung</td><td><input type="radio" name="transparente_leistungserwartung" value="2" /></td><td><input type="radio" name="transparente_leistungserwartung" value="1" /></td><td><input type="radio" name="transparente_leistungserwartung" value="-1" /></td><td><input type="radio" name="transparente_leistungserwartung" value="-2" /></td></tr>
			<tr><td title="Die Methoden passen zu den Inhalten, werden korrekt eingesetzt. Sch&uuml;ler werden angehalten, &uuml;ber den Methodeneinsatz zu reflektieren.">Methodentiefe</td><td><input type="radio" name="methodentiefe" value="2" /></td><td><input type="radio" name="methodentiefe" value="1" /></td><td><input type="radio" name="methodentiefe" value="-1" /></td><td><input type="radio" name="methodentiefe" value="-2" /></td></tr>
			<tr><td title="Umgangston; Regeln werden eingehalten; L&auml;rmpegel entspricht dem Arbeitsprozess">Lernfreundliches Klima</td><td><input type="radio" name="lernfreundliches_klima" value="2" /></td><td><input type="radio" name="lernfreundliches_klima" value="1" /></td><td><input type="radio" name="lernfreundliches_klima" value="-1" /></td><td><input type="radio" name="lernfreundliches_klima" value="-2" /></td></tr>
			<tr><td title="Der Lehrer lobt und ermutigt die Sch&uuml;ler aufgabenbezogen.">Lob geben</td><td><input type="radio" name="lob_geben" value="2" /></td><td><input type="radio" name="lob_geben" value="1" /></td><td><input type="radio" name="lob_geben" value="-1" /></td><td><input type="radio" name="lob_geben" value="-2" /></td></tr>
			<tr><td title="Aufgabensinn ist klar; auf Sch&uuml;lerinteressen wird eingegangen">Sinnstiftendes Kommunizieren</td><td><input type="radio" name="sinnstiftendes_kommunizieren" value="2" /></td><td><input type="radio" name="sinnstiftendes_kommunizieren" value="1" /></td><td><input type="radio" name="sinnstiftendes_kommunizieren" value="-1" /></td><td><input type="radio" name="sinnstiftendes_kommunizieren" value="-2" /></td></tr>
			<tr><td title="Die Sch&uuml;ler stellen von sich aus Verst&auml;ndnisfragen, kritische und weiterf&uuml;hrende Fragen.">Fragen</td><td><input type="radio" name="fragen" value="2" /></td><td><input type="radio" name="fragen" value="1" /></td><td><input type="radio" name="fragen" value="-1" /></td><td><input type="radio" name="fragen" value="-2" /></td></tr>
			<tr><td title="Der Lehrer gibt differenzierte Aufgaben, k&uuml;mmert sich um einzelne Sch&uuml;ler; Leistungsstarke k&ouml;nnen sich aus Routineaufgaben ausklinken und eigenen Schwerpunkten nachgehen.">Individuelles F&ouml;rdern</td><td><input type="radio" name="individuelles_foerdern" value="2" /></td><td><input type="radio" name="individuelles_foerdern" value="1" /></td><td><input type="radio" name="individuelles_foerdern" value="-1" /></td><td><input type="radio" name="individuelles_foerdern" value="-2" /></td></tr>
			<tr><td title="Die &Uuml;bungsaufgaben passen zur Zielstellung.">Passende Aufgaben</td><td><input type="radio" name="passende_aufgaben" value="2" /></td><td><input type="radio" name="passende_aufgaben" value="1" /></td><td><input type="radio" name="passende_aufgaben" value="-1" /></td><td><input type="radio" name="passende_aufgaben" value="-2" /></td></tr>
			<tr><td title="Den Sch&uuml;lern steht ausreichend Zeit zur &Uuml;bung zur Verf&uuml;gung.">Ausreichend Zeit</td><td><input type="radio" name="ausreichend_zeit" value="2" /></td><td><input type="radio" name="ausreichend_zeit" value="1" /></td><td><input type="radio" name="ausreichend_zeit" value="-1" /></td><td><input type="radio" name="ausreichend_zeit" value="-2" /></td></tr>
			<tr><td title="Die eingesetzten Medien und Materialien sind ausreichend und qualit&auml;tsvoll.">Material</td><td><input type="radio" name="material" value="2" /></td><td><input type="radio" name="material" value="1" /></td><td><input type="radio" name="material" value="-1" /></td><td><input type="radio" name="material" value="-2" /></td></tr>
			<tr><td title="Klassenraum ist ansprechend; Licht, Akustik und Bel&uuml;ftung sind gut.">Umgebung</td><td><input type="radio" name="umgebung" value="2" /></td><td><input type="radio" name="umgebung" value="1" /></td><td><input type="radio" name="umgebung" value="-1" /></td><td><input type="radio" name="umgebung" value="-2" /></td></tr>*/
		?>
		</table>
		Bewertung der Stunde insgesamt: <img src="<?php echo $pfad; ?>icons/smiley_5.png" alt="smiley_sehr_gut" title="sehr gut" /> <?php for ($n=3;$n>=-3;$n--) echo '<input type="radio" name="gesamteindruck" value="'.($n+1).'" />'; ?> <img src="<?php echo $pfad; ?>icons/smiley_1.png" alt="smiley_schlecht" title="schlecht" /><br />
		</span>
	</fieldset>
	<label for="notizen" style="width: 25px;"><img src="<?php echo $pfad; ?>icons/note.png" alt="Notizen" title="Notizen/Ziele f&uuml;r die n&auml;chste Unterrichtsstunde" />:</label> <textarea name="notizen" cols="30" rows="3"><?php echo html_umlaute(@sql_result($naechster_plan,0,"plan.notizen")); ?></textarea><br />
	<button onclick="auswertung=new Array(new Array(1, 'plan_minuten_start','natuerliche_zahl')); zaehler=0; while (document.getElementById('plan_minuten_'+zaehler)) { auswertung.push(new Array(1, 'plan_minuten_'+zaehler,'natuerliche_zahl'), new Array(1, 'abschnitt_minuten_'+zaehler,'natuerliche_zahl')); zaehler++; } pruefe_formular(auswertung);"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
	</form>
	</div>
  </body>
</html><?php
}
?>
