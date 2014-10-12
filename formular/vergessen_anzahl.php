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

$pfad="../";
$titelleiste="Vergesser-Anzahl &auml;ndern";
$mit_style=true;
include $pfad."header.php";
include $pfad."funktionen.php"; ?>

	<script type="text/javascript">
	/*<![CDATA[*/
		function deaktivieren(schueler, art) {
			if (art=='hausaufgabe') {
				if (document.getElementById('schueler_anzahl_'+schueler).value=='')
					display='none';
				else display='inline';
				document.getElementById('schueler_erledigt_'+schueler).style.display=display;
				document.getElementById('schueler_bemerkung_'+schueler).style.display=display;
			}
			else {
				if (document.getElementById('schueler_anzahl_ber_'+schueler) && document.getElementById('schueler_anzahl_unt_'+schueler)) {
					if (document.getElementById('schueler_anzahl_ber_'+schueler).value=='' && document.getElementById('schueler_anzahl_unt_'+schueler).value=='')
						display='none';
					else display='inline';
				}
				else {
					if (document.getElementById('schueler_anzahl_ber_'+schueler)) {
						if (document.getElementById('schueler_anzahl_ber_'+schueler).value=='')
							display='none';
						else display='inline';
					}
					else {
						if (document.getElementById('schueler_anzahl_unt_'+schueler).value=='')
							display='none';
						else display='inline';
					}
				}
				if (document.getElementById('schueler_erledigt_ber_'+schueler)) document.getElementById('schueler_erledigt_ber_'+schueler).style.display=display;
				if (document.getElementById('schueler_erledigt_unt_'+schueler)) document.getElementById('schueler_erledigt_unt_'+schueler).style.display=display;
			}
		}
	/*]]>*/
	</script>
  </head>
  <body>
	<div class="inhalt">
	<?php
	if ($_GET["eintragen"]!="true") {
	?>
	<form action="vergessen_anzahl.php?eintragen=true<?php if(isset($_GET["hausaufgabe"])) echo '&amp;hausaufgabe='.$_GET["hausaufgabe"]; else echo '&amp;notenbeschreibung='.$_GET["notenbeschreibung"]; ?>" method="post" accept-charset="ISO-8859-1">
	<fieldset><legend><?php echo $titelleiste; ?></legend>
	<?php
	if ($_GET["hausaufgabe"]>0) {
		if (!proofuser("hausaufgabe",$_GET["hausaufgabe"]))
			die("Sie sind hierzu nicht berechtigt.");
		$zusatz=' LEFT JOIN `hausaufgabe_vergessen` ON `hausaufgabe_vergessen`.`schueler`=`schueler`.`id` AND `hausaufgabe_vergessen`.`hausaufgabe`='.injaway($_GET["hausaufgabe"]).' ';
	}
	else {
		if (!proofuser("notenbeschreibung",$_GET["notenbeschreibung"]))
			die("Sie sind hierzu nicht berechtigt.");
		
		$zusatz=' LEFT JOIN `berichtigung_vergessen` ON `berichtigung_vergessen`.`schueler`=`schueler`.`id` AND `berichtigung_vergessen`.`notenbeschreibung`='.injaway($_GET["notenbeschreibung"]).' ';
		$notenbeschreibung=db_conn_and_sql("SELECT * FROM `notenbeschreibung` WHERE `id`=".injaway($_GET["notenbeschreibung"]));
		echo sql_result($notenbeschreibung,0,"notenbeschreibung.berichtigung")!="";
		if (sql_result($notenbeschreibung,0,"notenbeschreibung.berichtigung")!="")
			echo 'Berichtigung erforderlich<br />';
		if (sql_result($notenbeschreibung,0,"notenbeschreibung.unterschrift")!="")
			echo 'Unterschrift erforderlich<br />';
	}
	if (!proofuser("fach_klasse",$_GET["fk"]))
		die("Sie sind hierzu nicht berechtigt.");
				echo '<table class="tabelle"><tr><th>Sch&uuml;ler</th><th>Anzahl</th><th>erledigt</th><th>Bemerkung</th></tr>';
                $schueler = db_conn_and_sql("SELECT *
                    FROM gruppe, schueler".$zusatz."
                    WHERE gruppe.fach_klasse=".injaway($_GET["fk"])."
                        AND schueler.aktiv=1
                        AND gruppe.schueler=schueler.id
                    ORDER BY schueler.position, schueler.name,schueler.vorname");
                if (sql_num_rows($schueler)<1)
                    $schueler=db_conn_and_sql("SELECT * FROM  `klasse`,`fach_klasse`,`schueler`".$zusatz." WHERE `schueler`.`klasse`=`klasse`.`id` AND `fach_klasse`.`klasse`=`klasse`.`id` AND `fach_klasse`.`id`=".injaway($_GET["fk"])." ORDER BY `schueler`.`position`");
				for ($s=0;$s<sql_num_rows($schueler);$s++) {
					echo '<tr><td>'.html_umlaute(sql_result($schueler,$s,"schueler.vorname")).' '.html_umlaute(substr(sql_result($schueler,$s,"schueler.name"),0,1)).'.</td>';
						echo '<td><input type="hidden" name="schueler_id_'.$s.'" value="'.sql_result($schueler,$s,"schueler.id").'" />';
						if ($_GET["hausaufgabe"]!="") {
							echo '<input type="text" id="schueler_anzahl_'.$s.'" name="schueler_anzahl_'.$s.'" value="'.sql_result($schueler,$s,"hausaufgabe_vergessen.anzahl").'" onchange="deaktivieren('.$s.',\'hausaufgabe\');" size="1" maxlength="2" /></td><td>
								<input type="checkbox" id="schueler_erledigt_'.$s.'" name="schueler_erledigt_'.$s.'" value="1"'; if(sql_result($schueler,$s,"hausaufgabe_vergessen.anzahl")=="") echo ' style="display:none;"'; if (sql_result($schueler,$s,"hausaufgabe_vergessen.erledigt")) echo ' checked="checked"'; echo ' title="fertig" /></td><td>
								<input type="text" id="schueler_bemerkung_'.$s.'" name="schueler_bemerkung_'.$s.'" value="'.html_umlaute(sql_result($schueler,$s,"hausaufgabe_vergessen.bemerkung")).'"'; if(sql_result($schueler,$s,"hausaufgabe_vergessen.anzahl")=="") echo ' style="display:none;"'; echo ' size="15" maxlength="100" />';
						}
						else {
							if (sql_result($notenbeschreibung,0,"notenbeschreibung.berichtigung")!="") echo '<input type="text" id="schueler_anzahl_ber_'.$s.'" name="schueler_anzahl_ber_'.$s.'" value="'.sql_result($schueler,$s,"berichtigung_vergessen.berichtigung_anzahl").'" onkeyup="deaktivieren('.$s.',\'test\');" title="Berichtigung" size="1" maxlength="2" />';
							if (sql_result($notenbeschreibung,0,"notenbeschreibung.unterschrift")!="") echo '<input type="text" id="schueler_anzahl_unt_'.$s.'" name="schueler_anzahl_unt_'.$s.'" value="'.sql_result($schueler,$s,"berichtigung_vergessen.unterschrift_anzahl").'" onkeyup="deaktivieren('.$s.',\'test\');" title="Unterschrift" size="1" maxlength="2" />';
							echo '</td><td>';
							if (sql_result($notenbeschreibung,0,"notenbeschreibung.berichtigung")!="") {echo '<input type="checkbox" id="schueler_erledigt_ber_'.$s.'" name="schueler_erledigt_ber_'.$s.'" value="1"'; if(sql_result($schueler,$s,"berichtigung_vergessen.berichtigung_anzahl")=="") echo ' style="display:none;"'; if (sql_result($schueler,$s,"berichtigung_vergessen.berichtigung_erledigt")) echo ' checked="checked"'; echo ' title="fertig" />';}
							if (sql_result($notenbeschreibung,0,"notenbeschreibung.unterschrift")!="") {echo '<input type="checkbox" id="schueler_erledigt_unt_'.$s.'" name="schueler_erledigt_unt_'.$s.'" value="1"'; if(sql_result($schueler,$s,"berichtigung_vergessen.unterschrift_anzahl")=="") echo ' style="display:none;"'; if (sql_result($schueler,$s,"berichtigung_vergessen.unterschrift_erledigt")) echo ' checked="checked"'; echo ' title="fertig" />';}
							echo '</td><td>/';
						}
						echo '</td>';
					echo '</tr>';
				}
				echo '</table>';
		?>
		<input type="button" class="button" value="eintragen" onclick="auswertung=new Array(); zaehler=0; while (document.getElementById('schueler_anzahl_'+zaehler)) { if (document.getElementById('schueler_anzahl_'+zaehler).value!='') auswertung.push(new Array(0, 'schueler_anzahl_'+zaehler,'ganze_zahl')); zaehler++; }
			zaehler=0; while (document.getElementById('schueler_anzahl_ber_'+zaehler)) { if (document.getElementById('schueler_anzahl_ber_'+zaehler).value!='') auswertung.push(new Array(0, 'schueler_anzahl_ber_'+zaehler,'ganze_zahl')); zaehler++; }
			zaehler=0; while (document.getElementById('schueler_anzahl_unt_'+zaehler)) { if (document.getElementById('schueler_anzahl_unt_'+zaehler).value!='') auswertung.push(new Array(0, 'schueler_anzahl_unt_'+zaehler,'ganze_zahl')); zaehler++; }
			pruefe_formular(auswertung);" />
		</fieldset>
		</form>
		<?php
	}
	else {
		$alle_mit_ha_fertig=true;
		$alle_mit_ber_fertig=true; $alle_mit_unt_fertig=true;
		$i=0;
		while(isset($_POST["schueler_id_".$i])) {
			if ($_GET["hausaufgabe"]>0) {
				if (!proofuser("hausaufgabe",$_GET["hausaufgabe"]))
					die("Sie sind hierzu nicht berechtigt.");
				db_conn_and_sql("DELETE FROM `hausaufgabe_vergessen` WHERE `hausaufgabe`=".injaway($_GET["hausaufgabe"])." AND `schueler`=".injaway($_POST["schueler_id_".$i]));
				if ($_POST["schueler_anzahl_".$i]!="") {
					db_conn_and_sql("INSERT INTO `hausaufgabe_vergessen` (`hausaufgabe`,`schueler`,`anzahl`,`erledigt`, `bemerkung`) VALUES (".injaway($_GET["hausaufgabe"]).", ".injaway($_POST["schueler_id_".$i]).", ".($_POST["schueler_anzahl_".$i]+0).", ".($_POST["schueler_erledigt_".$i]+0).", ".apostroph_bei_bedarf($_POST["schueler_bemerkung_".$i]).")");
					if ($_POST["schueler_erledigt_".$i]!=1) $alle_mit_ha_fertig=false;
				}
			}
			else {
				if (!proofuser("notenbeschreibung",$_GET["notenbeschreibung"]))
					die("Sie sind hierzu nicht berechtigt.");
				
				db_conn_and_sql("DELETE FROM `berichtigung_vergessen` WHERE `notenbeschreibung`=".injaway($_GET["notenbeschreibung"])." AND `schueler`=".injaway($_POST["schueler_id_".$i]));
				if ((isset($_POST["schueler_anzahl_ber_".$i]) and $_POST["schueler_anzahl_ber_".$i]!="") or (isset($_POST["schueler_anzahl_unt_".$i]) and $_POST["schueler_anzahl_unt_".$i]!="")) {
					db_conn_and_sql("INSERT INTO `berichtigung_vergessen` (`notenbeschreibung`,`schueler`,`berichtigung_anzahl`,`unterschrift_anzahl`,`berichtigung_erledigt`,`unterschrift_erledigt`) VALUES (".injaway($_GET["notenbeschreibung"]).", ".injaway($_POST["schueler_id_".$i]).", ".($_POST["schueler_anzahl_ber_".$i]+0).", ".($_POST["schueler_anzahl_unt_".$i]+0).", ".($_POST["schueler_erledigt_ber_".$i]+0).", ".($_POST["schueler_erledigt_unt_".$i]+0).")");
					if ($_POST["schueler_erledigt_ber_".$i]==0) $alle_mit_ber_fertig=false;
					if ($_POST["schueler_erledigt_unt_".$i]==0) $alle_mit_unt_fertig=false;
				}
			}
			$i++;
		}
		if ($_GET["hausaufgabe"]>0) {
			if (!proofuser("hausaufgabe",$_GET["hausaufgabe"]))
				die("Sie sind hierzu nicht berechtigt.");
			
			if ($alle_mit_ha_fertig) {
				db_conn_and_sql("UPDATE `hausaufgabe` SET `kontrolliert`=1 WHERE `id`=".injaway($_GET["hausaufgabe"]));
				echo 'Alle Hausaufgaben erledigt.<br />';
			}
			else db_conn_and_sql("UPDATE `hausaufgabe` SET `kontrolliert`=-1 WHERE `id`=".injaway($_GET["hausaufgabe"]));
		}
		if ($_GET["notenbeschreibung"]>0) {
			if (!proofuser("notenbeschreibung",$_GET["notenbeschreibung"]))
				die("Sie sind hierzu nicht berechtigt.");
			
			if ($alle_mit_ber_fertig) {
				db_conn_and_sql("UPDATE `notenbeschreibung` SET `berichtigung`=1 WHERE `id`=".injaway($_GET["notenbeschreibung"]));
				echo 'Alle Berichtigungen erledigt.<br />';
			}
			else if (sql_result(db_conn_and_sql("SELECT `berichtigung` FROM `notenbeschreibung` WHERE `id`=".injaway($_GET["notenbeschreibung"])),0,"notenbeschreibung.berichtigung")==1) db_conn_and_sql("UPDATE `notenbeschreibung` SET `berichtigung`=0 WHERE `id`=".injaway($_GET["notenbeschreibung"]));
			if ($alle_mit_unt_fertig) {
				db_conn_and_sql("UPDATE `notenbeschreibung` SET `unterschrift`=1 WHERE `id`=".injaway($_GET["notenbeschreibung"]));
				echo 'Alle Unterschriften erledigt.<br />';
			}
			else if (sql_result(db_conn_and_sql("SELECT `unterschrift` FROM `notenbeschreibung` WHERE `id`=".injaway($_GET["notenbeschreibung"])),0,"notenbeschreibung.unterschrift")==1) db_conn_and_sql("UPDATE `notenbeschreibung` SET `unterschrift`=0 WHERE `id`=".injaway($_GET["notenbeschreibung"]));
		}
		?>
		Fertig<br />
		<input type="button" class="button" value="Fenster schlie&szlig;en" onclick="opener.location.reload(); window.close();" />	
		<?php
	} ?>
	</div>
	</body>
</html>
