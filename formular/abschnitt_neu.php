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
include $pfad."funktionen.php";

if ($_GET["eintragen"]=="true") {
	$inhalt_untertyp=floor($_POST['inhaltstyp_1']);
	$inhaltstyp=round(10*($_POST['inhaltstyp_1']-$inhalt_untertyp)+0.1);
	
	// Abschnitts-Container hinzufuegen
	if ($_GET["abschnitt"]<1 and proofuser("block",$_POST["block"])) {
		$id=db_conn_and_sql("INSERT INTO `abschnitt` (`hefter`, `medium`,`ziel`,`minuten`,`nachbereitung`,`sozialform`, `methode`) VALUES
			(".leer_NULL($_POST['hefter_1']).", ".leer_NULL($_POST['medium_1']).", ".apostroph_bei_bedarf($_POST['ziel_1']).", ".injaway($_POST['minuten_1']).", ".apostroph_bei_bedarf($_POST['bemerkung_1']).", ".leer_NULL($_POST['sozialform_1']).", ".leer_NULL($_POST['method_1']).");");
		
		db_conn_and_sql("INSERT INTO `block_abschnitt` (`position`,`abschnitt`,`block`) VALUES (".(sql_num_rows(db_conn_and_sql("SELECT * FROM `block_abschnitt` WHERE `block`=".injaway($_POST["block"])))).", ".$id.", ".injaway($_POST['block']).");");
		
		// bei plan-Angabe in Plan einfuegen
		if ($_GET["plan"]>0 and proofuser("plan", $_GET["plan"]))
            db_conn_and_sql("INSERT INTO `abschnittsplanung` (`abschnitt`, `plan`, `minuten`, `position`) VALUES
                (".$id.", ".injaway($_GET["plan"]).", ".leer_NULL($_POST['minuten_1']).", ".(sql_num_rows(db_conn_and_sql("SELECT * FROM `abschnittsplanung` WHERE `plan`=".injaway($_GET["plan"])))).");");
		
		// bei hausaufgaben-Angabe in hausaufgabe einfuegen
		if ($_GET["hausaufgabe"]>0 and proofuser("hausaufgabe", $_GET["hausaufgabe"]))
            db_conn_and_sql("INSERT INTO `hausaufgabe_abschnitt` (`abschnitt`, `hausaufgabe`) VALUES (".$id.", ".injaway($_GET["hausaufgabe"]).");");
	}
	else $id=injaway($_GET["abschnitt"]);
	
	if (!proofuser("abschnitt", $id))
		die("Sie sind hierzu nicht berechtigt.a");
	
	switch ($inhaltstyp) {
		case 1:
			db_conn_and_sql("INSERT INTO `ueberschrift` (`abschnitt`, `ebene`, `text`, `typ`) VALUES
				(".$id.", ".injaway($_POST['ueberschrift_ebene_1']).", ".apostroph_bei_bedarf($_POST['ueberschrift_text_1']).", ".apostroph_bei_bedarf($_POST["ueberschrift_typ_1"]).");");
		break;
		
		case 2:
			if ($_POST["test_neu"]) {
				if ($_POST["test_lokal"]=="erstellen") {
					$test_id=db_conn_and_sql("INSERT INTO `test` (`notentyp`, `url`, `lernbereich`, `platz_lassen`, `bearbeitungszeit`, `bemerkung`,`punkte`,`vorspann`, `user`) VALUES
						(".injaway($_POST['test_notentyp']).", null, ".leer_NULL($_POST['test_lernbereich']).", ".leer_NULL($_POST['test_platz']).", ".leer_NULL($_POST['test_zeit']).", null, ".leer_NULL($_POST['test_punkte']).", ".apostroph_bei_bedarf($_POST['test_vorspann']).", ".$_SESSION['user_id'].");");
				
				}
				else {
					$tempname = $_FILES['test_datei']['tmp_name'];
					$name = $_FILES['test_datei']['name'];
					
					if(empty($_FILES['test_datei']['name'])) $err[] = "Eine Datei muss ausgew&auml;hlt werden";
					if(empty($err)) {
						$dateiname=pfad_und_dateiname(injaway($_POST["lernbereich"]),'test',$name,$tempname,"../");
						
						$test_id=db_conn_and_sql("INSERT INTO `test` (`notentyp`, `url`, `lernbereich`, `platz_lassen`, `bearbeitungszeit`, `bemerkung`,`punkte`,`vorspann`, `user`) VALUES
							(".injaway($_POST['test_notentyp']).", ".apostroph_bei_bedarf($dateiname["test_datei"]).", ".leer_NULL($_POST['test_lernbereich']).", null, ".leer_NULL($_POST['test_zeit']).", null, ".leer_NULL($_POST['test_punkte']).", ".apostroph_bei_bedarf($_POST['test_vorspann']).", ".$_SESSION['user_id'].");");
							
					}
				}
				
				for ($i=0;$i<10;$i++)
					if ($_POST["test_thema_".$i]!="-")
						db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`, `id`, `thema`) VALUES
							(5, ".$test_id.", ".injaway($_POST["test_thema_".$i]).");");
				
				db_conn_and_sql("INSERT INTO `test_abschnitt` (`test`, `abschnitt`) VALUES
					(".$test_id.", ".$id.");");
			}
			else {
				$ids = explode(";",injaway($_POST["test_ids"])); array_pop($ids); // ...weil das letzte leer ist
				foreach ($ids as $value) db_conn_and_sql("INSERT INTO `test_abschnitt` (`test`, `abschnitt`) VALUES
					(".$value.", ".$id.");");
			}
			break;
			
		case 3:
			if ($inhalt_untertyp==2) $beispiel=true; else $beispiel=0;
			if ($_POST["aufgabe_neu"]) {
                $aufgaben_id=db_conn_and_sql("INSERT INTO `aufgabe` (`text`, `bemerkung`,`loesung`,`punkte`,`lernbereich`,`bearbeitungszeit`,`schwierigkeitsgrad`, `teilaufgaben_nebeneinander`, `user`) VALUES
                    (".apostroph_bei_bedarf($_POST['text']).", ".apostroph_bei_bedarf($_POST['bemerkung']).", ".apostroph_bei_bedarf($_POST['loesung']).", ".leer_NULL($_POST['punkte']).", ".leer_NULL($_POST['lernbereich']).", ".leer_NULL($_POST['bearbeitungszeit']).", ".leer_NULL($_POST['schwierigkeitsgrad']).", ".leer_NULL($_POST["teilaufgaben_nebeneinander"]).", ".$_SESSION['user_id'].");");
				
				
				$thema=0;
				$verwendete_themen='';
				while($_POST["thema_".$thema]!="-" and $thema<10) {
					$verwendete_themen.=$_POST["thema_".$thema].';';
					db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (1,".$aufgaben_id.",".injaway($_POST["thema_".$thema]).");");
					$thema++;
				}
				
				if ($_POST['art']!="text") {
					/*echo "INSERT INTO `buch_aufgabe` (`aufgabe`, `buch`,`seite`,`nummer`) VALUES
						(".$aufgaben_id.", ".$_POST['art'].", ".leer_NULL($_POST['seite']).", ".apostroph_bei_bedarf($_POST['nummer']).");";*/
					db_conn_and_sql("INSERT INTO `buch_aufgabe` (`aufgabe`, `buch`,`seite`,`nummer`) VALUES
						(".$aufgaben_id.", ".injaway($_POST['art']).", ".leer_NULL($_POST['seite']).", ".apostroph_bei_bedarf($_POST['nummer']).");");
					//db_conn_and_sql("UPDATE `buch` SET `letztes_thema`=".$_POST['thema_0'].", `letzter_lernbereich`= ".$_POST['lernbereich']." WHERE `id`=".$_POST['art']);
				}
				
				/*$bilder_ids = explode(";",$_POST["inhalt_ids"]); array_pop($bilder_ids); // ...weil das letzte leer ist
				foreach ($bilder_ids as $value) {
					$hilf=explode(":",$value);
					db_conn_and_sql("INSERT INTO `grafik_aufgabe` (`grafik`, `aufgabe`,`groesse`) VALUES
				(".$hilf[0].", ".$aufgaben_id.", ".punkt_statt_komma_zahl($hilf[1]).");");
				}*/
                
                // am 30.12.2012 rausgenommen (deprecated)
                // refresh_files("grafic", "task", $id, $_POST['text'].$_POST['loesung']);
                
                
				/*echo "INSERT INTO `aufgabe_abschnitt` (`aufgabe`, `abschnitt`,`beispiel`) VALUES
				(".$aufgaben_id.", ".$id.", ".$beispiel.");";*/
				db_conn_and_sql("INSERT INTO `aufgabe_abschnitt` (`aufgabe`, `abschnitt`,`beispiel`) VALUES
					(".$aufgaben_id.", ".$id.", ".$beispiel.");");
				
				// letzte Themen und LB der fach_klasse aktualisieren
				db_conn_and_sql("UPDATE fach_klasse SET letzter_lernbereich=".injaway($_POST['lernbereich']).", letzte_themen_auswahl=".apostroph_bei_bedarf($verwendete_themen)." WHERE id=".sql_result(db_conn_and_sql("SELECT letzte_fachklasse FROM benutzer WHERE benutzer.id=".$_SESSION['user_id']), 0, "benutzer.letzte_fachklasse"));
			}
			else {
				$ids = explode(";",$_POST["aufgabe_ids"]); array_pop($ids); // ...weil das letzte leer ist
				foreach ($ids as $value) db_conn_and_sql("INSERT INTO `aufgabe_abschnitt` (`aufgabe`, `abschnitt`,`beispiel`) VALUES
					(".$value.", ".$id.", ".$beispiel.");");
			}
			break;
		case 6:
			if ($_POST["material_neu"]) {
				$material_id=db_conn_and_sql("INSERT INTO `material` (`name`, `beschreibung`, `aufbewahrungsort`, `user`) VALUES
					(".apostroph_bei_bedarf($_POST['material_name']).", ".apostroph_bei_bedarf($_POST['material_beschreibung']).", ".apostroph_bei_bedarf($_POST['material_aufbewahrungsort']).", ".$_SESSION['user_id'].");");
				
				db_conn_and_sql("DELETE FROM `themenzuordnung` WHERE `typ`=6 AND `id`=".$id);
				$thema=0;
				while($_POST["material_thema_".$thema]!="-" and $thema<10) {
					db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (6,".$material_id.",".injaway($_POST["material_thema_".$thema]).");");
					$thema++;
				}
				db_conn_and_sql("INSERT INTO `material_abschnitt` (`material`, `abschnitt`) VALUES
					(".$material_id.", ".$id.");");
			}
			else {
				$ids = explode(";",$_POST["material_ids"]); array_pop($ids); // ...weil das letzte leer ist
				foreach ($ids as $value) db_conn_and_sql("INSERT INTO `material_abschnitt` (`material`, `abschnitt`) VALUES
					(".$value.", ".$id.");");
			}
			break;
		case 7:
            // am 30.12.2012 rausgenommen (deprecated)
            // refresh_files("both", "text", $id, $_POST['sonstiges_inhalt']);
			db_conn_and_sql("INSERT INTO `sonstiges` (`abschnitt`, `inhalt`) VALUES
				(".$id.", ".apostroph_bei_bedarf($_POST['sonstiges_inhalt']).");"); break;
		default: echo "Error: Nicht angegebener Inhaltstyp."; break;
	}

	?>
    <html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
	<?php
}
else {
	$titelleiste="Neuer Abschnitt";
	include $pfad."header.php";
	$block1=injaway($_GET["block"]);
	if (!proofuser("block",$_GET["block"]))
		die("Sie sind hierzu nicht berechtigt.");
	if ($_GET["abschnitt"]>0) $javascript='auswertung=new Array();';
	else $javascript='auswertung=new Array(new Array(0, \'minuten_1\',\'natuerliche_zahl\'));';
	$javascript.='
			switch (Math.round((document.getElementById(\'inhaltstyp_1\').value-Math.floor(document.getElementById(\'inhaltstyp_1\').value))*10)) {
				case 1:
					auswertung.push(new Array(0, \'ueberschrift_text_1\',\'nicht_leer\'));
				break;
				
				case 2:
					if (document.getElementById(\'test_neu\').checked) {
						auswertung.push(new Array(0, \'test_thema_0\',\'nicht_leer\',\'-\'), new Array(0, \'test_lernbereich\',\'nicht_leer\', \'-\'));
					}
					else auswertung.push(new Array(0, \'test_ids\',\'nicht_leer\'));
				break;
				
				case 3:
					if (document.getElementById(\'aufgabe_neu\').checked) {
						auswertung.push(new Array(0, \'thema_0\',\'nicht_leer\',\'-\'), new Array(0, \'lernbereich\',\'nicht_leer\',\'-\'));
						if (document.getElementById(\'art\').value!=\'text\') auswertung.push(new Array(0, \'seite\',\'nicht_leer\'));
						else auswertung.push(new Array(0, \'text\',\'nicht_leer\'));
					}
					else auswertung.push(new Array(0, \'aufgabe_ids\',\'nicht_leer\'));
				break;
				
				case 4:
					if (document.getElementById(\'link_neu\').checked) {
						auswertung.push(new Array(0, \'link_beschreibung\',\'nicht_leer\'), new Array(0, \'link_thema_0\',\'nicht_leer\',\'-\'), new Array(0, \'link_lernbereich\',\'nicht_leer\',\'-\'));
						if (document.getElementById(\'link_lokal\').checked) auswertung.push(new Array(0, \'link_url\',\'nicht_leer\',\'http://www.\'));
						else auswertung.push(new Array(0, \'link_file\',\'nicht_leer\'));
					}
					else auswertung.push(new Array(0, \'link_ids\',\'nicht_leer\'));
				break;
				
				case 5:
					if (document.getElementById(\'grafik_neu\').checked) {
						auswertung.push(new Array(0, \'grafik_alt\',\'nicht_leer\'), new Array(0, \'grafik_thema_0\',\'nicht_leer\',\'-\'), new Array(0, \'grafik_lernbereich\',\'nicht_leer\',\'-\'), new Array(0, \'grafik_file\',\'nicht_leer\'));
/* hier muss noch die groessenangabe mit rein !!!! */
					}
					else auswertung.push(new Array(0, \'grafik_ids\',\'nicht_leer\'));
				break;
				
				case 6:
					if (document.getElementById(\'material_neu\').checked) {
						auswertung.push(new Array(0, \'material_name\',\'nicht_leer\'));
					}
					else auswertung.push(new Array(0, \'material_ids\',\'nicht_leer\'));
				break;
				
				case 7:
					auswertung.push(new Array(0, \'sonstiges_inhalt\',\'nicht_leer\'));
				break;
			}
			pruefe_formular(auswertung);';
	
	echo '
	<body>
	<div id="mf">
		<ul class="r">
			<li><a href="javascript:window.close();" class="icon"><img src="'.$pfad.'icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
	
	<div class="tooltip" id="tt_block">
		<p>Um Abschnitte aus dem Fundus sp&auml;ter besser wiederzufinden, ordnen Sie diesen Abschnittscontainer einer Unterrichtseinheit/einem Block zu. Der Abschnitt erscheint dann im Fundus im entsprechenden Block an letzter Stelle. Sollte die Reihenfolge unvern&uuml;nftig sein, k&ouml;nnen Sie diese nach der Eintragung im Fundus korrigieren.</p></div>
	<div class="tooltip" id="tt_zeit">
		<p>Die Zeit eines Abschnitts (in Minuten) sollte f&uuml;r eine durchschnittliche Klasse eingetragen werden, damit Sie einen Richtwert haben.</p>
		<p>Wenn Sie den Abschnitt einer Unterrichtsstunde zuordnen,
		k&ouml;nnen Sie diese (zweite) Zeitangabe anpassen. In der Unterrichts-Nachbereitung werden beide Zeitangaben zur eventuellen Korrektur angeboten.</p></div>
	<div class="tooltip" id="tt_inhalt">
		<p>Der Inhalt eines Abschnittscontainers wird diesem hier zugeordnet. Jegliche Materialien werden automatisch in die Materialdatenbank eingeordnet und bleiben auch dann erhalten, wenn der Abschnittscontainer gel&ouml;scht wird.
		Dies betrifft alle Aufgaben, Tests, Grafiken, Arbeitsbl&auml;tter, Folien, Links und sonstige Materialien.</p>
		<p>Mit dem Symbol <img src="./icons/add.png" alt="add" /> k&ouml;nnen Sie dem Abschnitt mehrere Inhalte zuordnen. So k&ouml;nnen (damit die logische Abgrenzung zum n&auml;chsten Abschnitt gew&auml;hrleistet ist) hier z.B. gleichzeitig eine &Uuml;beschrift, ein Merksatz und zwei Grafiken vorkommen.</p></div>
	<div class="tooltip" id="tt_hefter">
		W&auml;hlen Sie zwischen "nicht aufschreiben", "Merkhefter" und "&Uuml;bungshefter". Je nach Auswahl sieht der Sch&uuml;lerhefter am Ende unterschiedlich aus.
		Wenn Sie nicht verschiedene Hefter nutzen (dies kann in den Einstellungen ver&auml;ndert werden), ist lediglich zwischen Hefter und m&uuml;ndlich zu unterscheiden,
		auch wenn die Auswahlm&ouml;glichkeit bestehen bleibt.</div>
	<div class="tooltip" id="tt_medium">
		Diese Auswahl hat auf den Unterricht keinen weiteren Einfluss. Sie k&ouml;nnen diese Daten lediglich in sp&auml;teren Versionen statistisch auswerten.</div>
	<div class="tooltip" id="tt_kommentar">
		<p>Ein Kommentar ist bei der Abschnittsauswahl lesbar. Auch Nachbereitungskommentare werden in dieses Feld geschrieben.</p></div>
	
    <div id="pictureframe" style="display: none; width: 800px; heigth: 600px;"><iframe name="pictureframe" width="790" height="580" src="'.$pfad.'lessons/picturelib.php"></iframe></div>
    <div id="fileframe" style="display: none; width: 800px; heigth: 600px;"><iframe name="fileframe" width="790" height="580" src="'.$pfad.'lessons/filelib.php"></iframe></div>
    
	<div class="inhalt">';
		$bloecke = db_conn_and_sql("SELECT `block`.*,`lernbereich`.*,`lehrplan`.*,`schulart`.*,`faecher`.*, COUNT(`block_abschnitt`.`abschnitt`) AS `anzahl`
														FROM `block`
															LEFT JOIN `block_abschnitt` ON `block_abschnitt`.`block`=`block`.`id`,`lernbereich`,`lehrplan`, `lp_user`, `schulart`,`faecher`
														WHERE `lernbereich`.`lehrplan`=`lehrplan`.`id`
															AND `block`.`lernbereich`=`lernbereich`.`id`
															AND `lehrplan`.`fach` = `faecher`.`id`
															AND `lehrplan`.`schulart`=`schulart`.`id`
															AND `lp_user`.`lehrplan`=`lehrplan`.`id`
															AND `lp_user`.`user`=".$_SESSION['user_id']."
                                                            AND `lp_user`.`aktiv`=1
														GROUP BY `block`.`id`
														ORDER BY `lehrplan`.`schulart`, `lehrplan`.`fach`, `lernbereich`.`klassenstufe`, `lehrplan`.`zusatz`,`lernbereich`.`nummer`,`block`.`block_hoeher`, `block`.`position`");
	if ($_GET["abschnitt"]>0 and proofuser("abschnitt", $_GET["abschnitt"]))
        $inhalt.='<fieldset><legend>Inhalt zum Abschnitt hinzuf&uuml;gen</legend>
		<form action="'.$pfad.'formular/abschnitt_neu.php?eintragen=true&amp;abschnitt='.injaway($_GET["abschnitt"]).'" method="post" enctype="multipart/form-data" accept-charset="ISO-8859-1" id="edit">'.eintragung_inhaltstypen(0).'<br />
			<input type="button" class="button" value="hinzuf&uuml;gen" onclick="'.$javascript.'" />
			
		</form></fieldset>'; // form +:  onsubmit="return submitForm();"
	else {
	$inhalt.='
		<fieldset>
		<legend><img src="'.$pfad.'/icons/abschnitt.png" alt="abschnitt" title="Abschnitt" /> Neuer Abschnitt</legend>
		<form action="'.$pfad.'formular/abschnitt_neu.php?eintragen=true&amp;plan='.$_GET["plan"].'&amp;abschnitt='.$_GET["abschnitt"].'&amp;hausaufgabe='.$_GET["hausaufgabe"].'" method="post" enctype="multipart/form-data" accept-charset="ISO-8859-1">
		<p>Geh&ouml;rt zu Block: <select name="block">';
		$lb=0;
		for ($h=0;$h<sql_num_rows($bloecke);$h++) {
			if (sql_result($bloecke,$h,"lernbereich.id")!=$lb) {
				if ($lb!=0)
					$inhalt.='</optgroup>';
				$lb=sql_result($bloecke,$h,"lernbereich.id");
				$inhalt.='<optgroup label="'.html_umlaute(sql_result($bloecke,$h,"schulart.kuerzel")).' '.html_umlaute(sql_result($bloecke,$h,"faecher.kuerzel")).' - Kl. '.sql_result($bloecke,$h,"lernbereich.klassenstufe").''.sql_result($bloecke,$h,"lehrplan.zusatz").' LB '.sql_result($bloecke,$h,"lernbereich.nummer").'. '.html_umlaute(sql_result($bloecke,$h,"lernbereich.name")).'">';
			}
			$inhalt.='<option value="'.sql_result($bloecke,$h,"block.id").'"';
			if (sql_result($bloecke,$h,"block.id")==$block1) {$hilf=$h; $inhalt.=' selected="selected"'; }
			$inhalt.=' onclick="document.getElementsByName(\'lernbereich\')[0].value='.$lb.';" >'.html_umlaute(sql_result($bloecke,$h,"block.name")).' ('.sql_result($bloecke,$h,"anzahl").')</option>';
		}
  
		$inhalt.='</optgroup></select> <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_block\')" onmouseout="hideWMTT()" /></p>';
		$lehrplan=sql_result($bloecke,$hilf,"lehrplan.id");
		/* brauch ich nicht mehr: (glaub ich) wahrscheinlich nicht mal lehrplan
			$klasse=sql_result($fachklassen,$fach_klasse_sel,"klasse.id"); 
         <input type="hidden" name="klasse" value="'.$klasse.'" />*/
		$position=sql_result($bloecke,$hilf,"anzahl")+1;
		//echo $lehrplan." ".$klasse." ".$position." ";
// <form name="abschnitt" action="'.$pfad.'formular/abschnitt_neu.php" method="post">
	$inhalt.='
         <input type="hidden" name="lehrplan" value="'.$lehrplan.'" />';
	$inhalt.='<table class="einzelstunde"><tr><td style="vertical-align:top;"><!--<input type="hidden" name="position_1" value="'.($position).'" size="1" maxlength="2" />-->
		<p>
        <label for="minuten_1" style="width: 65px;">Minuten<em>*</em>:</label><br /><input type="text" name="minuten_1" size="1" maxlength="3" value="'.$abschnitt['minuten'].'" /> <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_zeit\')" onmouseout="hideWMTT()" /></p>
		<p>
		<label for="hefter_1" style="width: 65px;">Hefter<em>*</em>:</label><br /><input type="radio" id="hefter_1_0" name="hefter_1" value="0" /> -<br />
                                             <input type="radio" id="hefter_1_1" name="hefter_1" value="1" checked="checked" /> <img src="'.$pfad.'icons/merkteil.png" title="Merkteil" /><br />
                                             <input type="radio" id="hefter_1_2" name="hefter_1" value="2" /> <img src="'.$pfad.'icons/uebungsteil.png" title="&Uuml;bungsteil" /><br /> <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_hefter\')" onmouseout="hideWMTT()" /></p></td>';
		$inhalt.='
        <td style="width: 500px; vertical-align:top;">'.eintragung_inhaltstypen(1).'
			</td>';
			
        $inhalt.='
	    <td style="vertical-align:top;"><p>';
          $inhalt.='
			<p><label for="methods_1">Methode:</label><br /><select id="method_1" name="method_1"><option value="">-</option>';
          include($pfad."basic/localisation/methods.php");
	      while( list($key, $val) = each ($methods) )
            {
                $inhalt.='<option value="'.$key.'"'; if ($abschnitt['methode']==$key) $inhalt.=' selected="selected"'; $inhalt.=' title="Anlass: '.html_umlaute($val['occasion']).' | Bedeutung: '.html_umlaute($val['intend']).' | Tipps/Probleme: '.html_umlaute($val['pointer']).'">'.html_umlaute($val['name']).'</option>';
            }
            
	    $inhalt.='</select></p>
            <label for="medium_1">Medium: <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_medium\')" onmouseout="hideWMTT()" /></label><br /><select id="medium_1" name="medium_1">'; $medium=db_conn_and_sql("SELECT * FROM `medium`");
	      for ($j=0;$j<sql_num_rows($medium);$j++) {
			  $inhalt.='<option value="'.sql_result($medium,$j,"medium.id").'"'; if ($abschnitt['medium']==sql_result($medium,$j,"medium.id")) $inhalt.=' selected="selected"'; $inhalt.=' >'.html_umlaute(sql_result($medium,$j,"medium.kuerzel")).'</option>';
			}  
		$inhalt.='</select></p>
	        <p><label for="sozialform_1">Sozialform:</label><br /><select id="sozialform_1" name="sozialform_1">';
			$soz_form=db_conn_and_sql("SELECT * FROM `sozialform`");
	      for ($j=0;$j<sql_num_rows($soz_form);$j++) {
			$inhalt.='<option value="'.sql_result($soz_form,$j,"sozialform.id").'"'; if ($abschnitt['sozialform']==sql_result($soz_form,$j,"sozialform.id")) $inhalt.=' selected="selected"'; $inhalt.=' >'.html_umlaute(sql_result($soz_form,$j,"sozialform.kuerzel")).'</option>';
		  }  
		$inhalt.='</select></p>
			<p><label for="handlungsmuster_1">Handlungsmuster:</label><br /><select id="handlungsmuster_1" name="handlungsmuster_1"><option value="">-</option>';
			$handlungsmuster=db_conn_and_sql("SELECT * FROM `handlungsmuster`");
	      for ($j=0;$j<sql_num_rows($handlungsmuster);$j++) {
			$inhalt.='<option value="'.sql_result($handlungsmuster,$j,"handlungsmuster.id").'"'; if ($abschnitt['handlungsmuster']==sql_result($handlungsmuster,$j,"handlungsmuster.id")) $inhalt.=' selected="selected"'; $inhalt.=' >'.html_umlaute(sql_result($handlungsmuster,$j,"handlungsmuster.name")).'</option>';
		  }
			
			$inhalt.='</select></p></td>
	        <td style="vertical-align:top;"><p><label for="ziel_1">Ziel:</label><br /><input type="text" name="ziel_1" maxlength="250" size="35" value="'.$abschnitt['ziel'].'" /></p>
				<p><label for="bemerkung_1">Kommentar: <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\'tt_kommentar\')" onmouseout="hideWMTT()" /></label><br /><textarea name="bemerkung_1" cols="40" rows="5">'.$abschnitt['nachbereitung'].'</textarea></p></td></tr></table>';
			
		$inhalt.='<input type="button" class="button" value="eintragen" onclick="'.$javascript.'" />
	</form></fieldset>';
	/* Aufgaben:  */
	}
	echo $inhalt;
	echo '</div></body></html>';
}
?>
