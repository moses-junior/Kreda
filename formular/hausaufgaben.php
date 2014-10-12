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

if ($_GET["loeschen"]=="true") {
	if (!proofuser("hausaufgabe",$_GET["hausaufgabe"]))
		die("Sie sind hierzu nicht berechtigt.");

	//$anzahl=db_conn_and_sql("SELECT * FROM `hausaufgabe_vergessen` WHERE `hausaufgabe`=".$_GET["hausaufgabe"]);
    include $pfad."header.php";
    echo '<body><div class="inhalt"><form action="'.$pfad.'formular/aufgabe_delete.php?bestaetigen=ja&amp;id='.$_GET["id"].'" method="post">
        <p>Die Hausaufgabe  wird endg&uuml;ltig (zusammen mit allen ihr zugeordneten Daten) gel&ouml;scht.</p>';
    echo del_array2echo(delete_db_object("hausaufgabe", array(injaway($_GET["hausaufgabe"])), $pfad, false), "info");
    echo '<p>Wollen Sie das wirklich?</p>
                <input type="button" class="button" value="nein" onclick="window.close()" />
                <input type="button" style="float: right;" class="button" value="ja" onclick="document.location=\'hausaufgaben.php?loeschen=true&amp;confirm=yes&amp;hausaufgabe='.$_GET["hausaufgabe"].'\'" />
            </form></div></body></html>';
	if($_GET["confirm"]=="yes") {
        $deleter=del_array2echo(delete_db_object("hausaufgabe", array(injaway($_GET["hausaufgabe"])), $pfad, false), "sql");
        foreach ($deleter as $del_line)
            db_conn_and_sql($del_line);
        echo '<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>';
        
		exit;
	}
}
else {
	if ($_GET["eintragen"]!="true") {
		$titelleiste="Hausaufgaben";
		include $pfad."header.php";
		
		if ($_GET["aktion"]=="entfernen" and proofuser("hausaufgabe",$_GET["hausaufgabe"]))
			db_conn_and_sql("DELETE FROM `hausaufgabe_abschnitt` WHERE `hausaufgabe`=".injaway($_GET["hausaufgabe"])." AND `abschnitt`=".injaway($_GET["abschnitt"]));
		?>
		<body>
			<div class="inhalt">
				<form action="hausaufgaben.php?plan=<?php echo $_GET["plan"]; ?>&amp;block=<?php echo $_GET["block"]; ?>&amp;eintragen=true<?php if(isset($_GET["hausaufgabe"])) echo '&amp;hausaufgabe='.$_GET["hausaufgabe"]; ?>" method="post" accept-charset="ISO-8859-1">
				<fieldset><legend>Hausaufgabe</legend>
				<ol class="divider">
				<?php if (isset($_GET["hausaufgabe"])) {
					if (!proofuser("hausaufgabe",$_GET["hausaufgabe"]))
						die("Sie sind hierzu nicht berechtigt.");
					
					$alt_ha=db_conn_and_sql("SELECT * FROM `hausaufgabe` WHERE `id`=".injaway($_GET["hausaufgabe"]));
				} ?>
				<!--Hausaufgabe:
				<select name="hausaufgabe" onchange="if(this.value=='neu') document.getElementById('neue_HA').style.visibility='visible'; else document.getElementById('neue_HA').style.visibility='hidden';">
					<option value="neu">neu</option>
					<option value="loeschen">test</option>pruefen
					<?php
					if (!proofuser("block", $_GET["block"]))
						die("Sie sind hierzu nicht berechtigt.");
					
					$block_dieser=db_conn_and_sql("SELECT * FROM `block`,`lernbereich` WHERE `block`.`lernbereich`=`lernbereich`.`id` AND `block`.`id`=".injaway($_GET["block"]));
					$block_ha=db_conn_and_sql("SELECT * FROM `hausaufgabe`,`block`
						WHERE `block`.`lernbereich`=`lernbereich`.`id`
							AND `lernbereich`.`klassenstufe`=".sql_result($block_dieser,0,"lernbereich.klassenstufe")."
							AND `lernbereich`.`lehrplan`=".sql_result($block_dieser,0,"lernbereich.lehrplan")."
							AND `hausaufgabe`.`block`=".injaway($_GET["block"]));
					for($i=0;$i<sql_num_rows($block_ha);$i++) {
						echo '<option value="'.sql_result($block_ha,$i,"hausaufgabe.id").'">'.sql_result($block_ha,$i,"hausaufgabe.bemerkung").' - ZG: '.sql_result($block_ha,$i,"hausaufgabe.zielgruppe").' - noch mehr Infos...</option>';
					} ?>
				</select><br />-->
	
				<?php
				if (@sql_result($alt_ha,0,"hausaufgabe.plan")>0) { ?>
				<li><label for="plan_neu">aufgegeben am<em>*</em>:</label> <select name="plan_neu">
				<?php
					$fach_klasse=db_conn_and_sql("SELECT plan.fach_klasse FROM plan WHERE plan.id=".sql_result($alt_ha,0,"hausaufgabe.plan"));
					$plan=db_conn_and_sql("SELECT * FROM `plan`
						WHERE `fach_klasse`=".sql_result($fach_klasse,0,"plan.fach_klasse")."
							AND `schuljahr`=".$aktuelles_jahr."
							AND ausfallgrund IS NULL
						ORDER BY `datum`");
					for ($i=0;$i<sql_num_rows($plan);$i++) {
						echo '<option value="'.sql_result($plan,$i,"plan.id").'"';
						if (sql_result($plan,$i,"plan.id")==sql_result($alt_ha,0,"hausaufgabe.plan")) {
							echo ' selected="selected"';
							$plan_datum=$i;
						}
						echo '>'.datum_strich_zu_punkt(sql_result($plan,$i,"plan.datum")).'</option>';
					} ?>
					</select></li><?php
				} ?>
				
				<li><label for="abgabedatum">Abgabetermin<em>*</em>:</label> <input type="datum" class="datepicker" name="abgabedatum" value="<?php echo datum_strich_zu_punkt(@sql_result($alt_ha,0,"hausaufgabe.abgabedatum")); ?>" size="7" /><br />
					<label for="mitzaehlen" title="Vergessen mitz&auml;hlen">mitz&auml;hlen:</label> <input type="checkbox" name="mitzaehlen" value="1" title="sollen HA-Vergesser in die Statistik einbezogen werden? (z.B. bei Organisatorischen Dingen)" <?php if(!isset($_GET["hausaufgabe"]) or @sql_result($alt_ha,0,"hausaufgabe.mitzaehlen")) echo 'checked="checked" '; ?>/></li>
				<li><label for="bemerkung" title="Kommentar zur Verbindung dieser HA mit der Unterrichtsstunde">Kommentar:</label> <textarea name="bemerkung" rows="2" cols="50" title="Dieser Kommentar tritt nur in Verbindung mit dieser Unterrichtsstunde auf, ist also in der n&auml;chsten Klasse nicht mehr vorhanden."><?php echo html_umlaute(@sql_result($alt_ha,0,"hausaufgabe.bemerkung")); ?></textarea><br />
					<label for="ziel">Ziel:</label> <input type="text" name="ziel" value="<?php echo html_umlaute(@sql_result($alt_ha,0,"hausaufgabe.ziel")); ?>" title="z.B. Wiederholung / Festigung bestimmter Aufgaben" /></li>
					<!--<select name="arbeitsblatt">
					<?php 
			/* $arbeitsblatt=db_conn_and_sql("SELECT *
            FROM `link`,`lernbereich`
            WHERE `link`.`lernbereich`=`lernbereich`.`id`
				AND `link`.`typ`=1
				AND `lernbereich`.`klassenstufe`=".sql_result($block_dieser,0,"lernbereich.klassenstufe")."
				AND `lernbereich`.`lehrplan`=".sql_result($block_dieser,0,"lernbereich.lehrplan")."
			ORDER BY `lernbereich`.`nummer`");
		$lb=0;
		for($i=0;$i<sql_num_rows($arbeitsblatt);$i++) {
			if (sql_result($arbeitsblatt,$i,"lernbereich.id")!=$lb) {
				if ($lb!=0) echo '</optgroup>';
				$lb=sql_result($arbeitsblatt,$i,"lernbereich.id");
				echo '<optgroup label="LB '.sql_result($arbeitsblatt,$i,"lernbereich.nummer").': '.sql_result($arbeitsblatt,$i,"lernbereich.name").'">';
			}
			echo '<option value="'.sql_result($arbeitsblatt,$i,"link.id").'">'.sql_result($arbeitsblatt,$i,"link.beschreibung").'</option>';
		}*/
			?>
		</select>-->
		<!--<input type="text" name="abschnitt" id="inhalt_ids" value="<?php
				if (isset($_GET["hausaufgabe"])) {
					$abschnitte=db_conn_and_sql("SELECT * FROM `hausaufgabe_abschnitt` WHERE `hausaufgabe`=".sql_result($alt_ha,0,"hausaufgabe.id"));
					if (sql_num_rows($abschnitte)>0)
						for($i=0;$i<sql_num_rows($abschnitte);$i++) echo sql_result($abschnitte,$i,"hausaufgabe_abschnitt.abschnitt").";";
				} ?>" readonly="readonly" />-->
			<?php if ($_GET["hausaufgabe"]>0) {
				// evtl. vorhandene Abschnitte anzeigen
				$db = new db;
				$abschnitte=db_conn_and_sql("SELECT * FROM `hausaufgabe_abschnitt` WHERE `hausaufgabe_abschnitt`.`hausaufgabe`=".injaway($_GET["hausaufgabe"]));
				if (@sql_num_rows($abschnitte)>0) {
					echo'<li><table id="einzelstunde" class="einzelstunde" cellspacing="0" cellpadding="0">
						<tr><th title="Position wird automatisch festgelegt">Aktion</th><th>Inhalt</th></tr>';
					for ($i=0;$i<sql_num_rows($abschnitte);$i++) {
						$ansicht=einzelstundenansicht(sql_result($abschnitte,$i,'hausaufgabe_abschnitt.abschnitt'),"bearbeiten",$pfad);
						$abschnitt=$db->abschnitt(sql_result($abschnitte,$i,'hausaufgabe_abschnitt.abschnitt'));
						echo '<tr>
							<td><a href="hausaufgaben.php?block='.injaway($_GET["block"]).'&amp;plan='.injaway($_GET["plan"]).'&amp;abschnitt='.sql_result($abschnitte,$i,'hausaufgabe_abschnitt.abschnitt').'&amp;hausaufgabe='.injaway($_GET["hausaufgabe"]).'&amp;aktion=entfernen" class="icon" title="Entfernen Sie den Abschnitt aus der Hausaufgabe. Er bleibt im Fundus bestehen."><img src="'.$pfad.'icons/entfernen.png" alt="entfernen" /></a></td>
							<td>'.str_replace("'","\'",$ansicht['inhalt']).'</td>
							</tr>';
					}
					echo '</table></li>';
				}
				?>
				<li><button type="button" onclick="window.open('<?php echo $pfad; ?>abschnittsplanung.php?block=<?php echo $_GET["block"]; ?>&amp;refresh=0&amp;hausaufgabe=<?php echo $_GET["hausaufgabe"]; ?>', 'ID-Uebersicht', 'width=700,height=600,left=100,top=200,resizable=yes,scrollbars=yes');" title="vorhandene Abschnitte eintragen"><img src="<?php echo $pfad; ?>icons/fundus.png" alt="fundus" /> vorhandene Abschnitte eintragen</button>
				<button type="button" onclick="window.open('<?php echo $pfad; ?>formular/abschnitt_neu.php?block=<?php echo $_GET["block"]; ?>&amp;hausaufgabe=<?php echo $_GET["hausaufgabe"]; ?>', 'Neuer Abschnitt', 'width=1100,height=600,left=50,top=50,resizable=yes,scrollbars=yes');" title="neuen Abschnitt erstellen"><img src="<?php echo $pfad; ?>icons/abschnitt.png" alt="abschnitt" /> neuer Abschnitt</button></li>
			<?php
			}
			else echo '<p><span class="hinweis">Hier k&ouml;nnen Sie sp&auml;ter Abschnitte hinzuf&uuml;gen. Speichern Sie die Hausaufgabe zun&auml;chst und bearbeiten Sie dann die erstellte Hausaufgabe.</span></p>'; ?>
	<br /></ol>
	<input type="button" class="button" value="eintragen" onclick="auswertung=new Array(new Array(0, 'abgabedatum','datum','<?php echo ($aktuelles_jahr); ?>-01-01','<?php echo ($aktuelles_jahr+1); ?>-12-31')); pruefe_formular(auswertung);" />
	</fieldset>
	</form>
	</div>
	</body>
	</html>
	<?php
	}
	else {
		if (isset($_GET["hausaufgabe"])) {
			$id=injaway($_GET["hausaufgabe"]);
			if (!proofuser("hausaufgabe",$_GET["hausaufgabe"]))
				die("Sie sind hierzu nicht berechtigt.");
			
			db_conn_and_sql("UPDATE `hausaufgabe`
				SET `ziel`=".apostroph_bei_bedarf($_POST["ziel"]).", `bemerkung`=".apostroph_bei_bedarf($_POST["bemerkung"]).",
					`abgabedatum`='".datum_punkt_zu_strich($_POST["abgabedatum"])."',`mitzaehlen`=".($_POST["mitzaehlen"]+0).",
					`plan`=".leer_NULL($_POST["plan_neu"])."
				WHERE `id`=".$id);
		}
		else {
			if (proofuser("plan",$_GET["plan"]))
				$id=db_conn_and_sql("INSERT INTO `hausaufgabe` (`plan`, `ziel`, `bemerkung`,`abgabedatum`,`mitzaehlen`) VALUES
					(".$_GET["plan"].", ".apostroph_bei_bedarf($_POST["ziel"]).", ".apostroph_bei_bedarf($_POST["bemerkung"]).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["abgabedatum"])).",".($_POST["mitzaehlen"]+0).");");
		}
		$ids = explode(";",$_POST["abschnitt"]); array_pop($ids); // ...weil das letzte leer ist
		for($i=0;$i<count($ids);$i++) db_conn_and_sql("INSERT INTO `hausaufgabe_abschnitt` (`hausaufgabe`,`abschnitt`) VALUES
			(".$id.", ".$ids[$i].");");
		?>
		<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
        <?php
	}
}
?>
