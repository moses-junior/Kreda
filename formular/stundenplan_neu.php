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
$pfad='../';
include $pfad."funktionen.php";

if ($_GET["eintragen"]=="true" and proofuser("fach_klasse", $_POST['fach_klasse'])) {
	// neuer Raum, vorhandener Raum?
	if ($_POST["raum_neu_checkbox"]==1) {
		$zwischenresult=db_conn_and_sql('SELECT * FROM `fach_klasse`,`klasse`,`schule` WHERE `klasse`.`schule`=`schule`.`id` AND `fach_klasse`.`klasse`=`klasse`.`id` AND `fach_klasse`.`id`='.injaway($_POST['fach_klasse']));
		$raum_id=db_conn_and_sql("INSERT INTO raum (name, schule, aktiv) VALUES (".apostroph_bei_bedarf($_POST["raum_neu"]).", ".sql_result($zwischenresult,0,"schule.id").",1);");
	}
	else $raum_id=injaway($_POST["raum"]);
	
	// neue/vorhandene Stunde
	if ($_POST["stundenplan_id"]>0) {
		db_conn_and_sql("UPDATE `stundenplan` SET `fach_klasse`=".injaway($_POST['fach_klasse']).", `gerade_woche`=".leer_NULL($_POST["woche"]).", `wochentag`=".injaway($_POST['wochentag']).", `raum`=".$raum_id.", `stundenzeit`=".apostroph_bei_bedarf($_POST['zeit'])."
			WHERE `id`=".injaway($_POST["stundenplan_id"]));
	}
	else db_conn_and_sql("INSERT INTO `stundenplan` (`schuljahr`, `fach_klasse`, `gerade_woche`, `wochentag`, `raum`, `stundenzeit`) VALUES
		(".injaway($_POST['schuljahr']).", ".injaway($_POST['fach_klasse']).", ".leer_NULL($_POST["woche"]).", ".injaway($_POST['wochentag']).", ".$raum_id.", ".apostroph_bei_bedarf($_POST['zeit']).");");
	?>
    <html><head><script type="text/javascript">opener.location.href='<?php echo $pfad; ?>index.php?tab=stundenplan&auswahl=stundenplan&bearbeiten=true'; window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
	<?php
}
else



    if ($_GET["eintragen"]=="dragndrop") {
	// neuer Raum, vorhandener Raum?
	
	if (!proofuser("fach_klasse",$_GET["fach_klasse"]))
		die("Sie sind hierzu nicht berechtigt.");
	
	if ($_GET["raum_neu_checkbox"]==1) {
		$zwischenresult=db_conn_and_sql('SELECT * FROM `fach_klasse`,`klasse`,`schule` WHERE `klasse`.`schule`=`schule`.`id` AND `fach_klasse`.`klasse`=`klasse`.`id` AND `fach_klasse`.`id`='.injaway($_GET['fach_klasse']));
		$raum_id=db_conn_and_sql("INSERT INTO raum (name, schule, aktiv) VALUES (".apostroph_bei_bedarf($_GET["raum_neu"]).", ".sql_result($zwischenresult,0,"schule.id").",1);");
	}
	else $raum_id=injaway($_GET["raum"]);
	
    db_conn_and_sql("INSERT INTO `stundenplan` (`schuljahr`, `fach_klasse`, `gerade_woche`, `wochentag`, `raum`, `stundenzeit`) VALUES
		(".injaway($_GET['schuljahr']).", ".injaway($_GET['fach_klasse']).", ".leer_NULL($_GET["woche"]).", ".injaway($_GET['wochentag']).", ".$raum_id.", ".apostroph_bei_bedarf($_GET['zeit']).");");
	header("Location: ".$pfad."index.php?tab=stundenplan&auswahl=stundenplan&fk=".$_GET['fach_klasse']."&room=".$raum_id."&week=".$_GET["woche"]);
	exit;
}
else {
$titelleiste="Unterrichtsstunde an den Stundenplan von ".$aktuelles_jahr."/".($aktuelles_jahr+1)." anh&auml;ngen";
include $pfad."header.php";
?>
	<body>
	<div class="inhalt">
    <form action="<?php echo $pfad; ?>formular/stundenplan_neu.php?eintragen=true" method="POST" accept-charset="ISO-8859-1">
		<?php
			$woche=2;
			if ($_GET["stundenplan_id"]>0 and proofuser("stundenplan", $_GET["stundenplan_id"])) {
				$stundenplan=db_conn_and_sql("SELECT * FROM `stundenplan` WHERE `id`=".injaway($_GET["stundenplan_id"]));
				$fach_klasse=sql_result($stundenplan,0,"stundenplan.fach_klasse");
				$woche=sql_result($stundenplan,0,"stundenplan.gerade_woche");
				$raum=sql_result($stundenplan,0,"stundenplan.raum");
				$stundenzeit=sql_result($stundenplan,0,"stundenplan.stundenzeit");
				echo '<input type="hidden" name="stundenplan_id" value="'.$_GET["stundenplan_id"].'" />';
			}
		?>
		<input type="hidden" name="schuljahr" value="<?php echo $aktuelles_jahr; ?>" />
		<fieldset><legend>Unterrichtsstunde <?php if($_GET["stundenplan_id"]>0) echo '&auml;ndern'; else echo 'hinzuf&uuml;gen'; ?> (Stundenplan <?php echo $aktuelles_jahr."/".($aktuelles_jahr+1); ?>)</legend>
		<!--<input type="hidden" name="schule" value="<?php echo sql_result($aktive_schulen,$k,"schule.id"); ?>" />-->
        <label for="fach_klasse">Fach-Klasse:</label>
        <select name="fach_klasse"><?php $zwischenresult=db_conn_and_sql('SELECT *
			FROM `fach_klasse`,`klasse`,`faecher`,`schule`
			WHERE `klasse`.`schule`=`schule`.`id`
				AND `fach_klasse`.`user`='.$_SESSION['user_id'].'
				AND `fach_klasse`.`fach`=`faecher`.`id`
				AND `fach_klasse`.`klasse`=`klasse`.`id`
			ORDER BY `schule`.`id`, `klasse`.`einschuljahr` DESC, `klasse`.`endung`');
          for ($i=0;$i<sql_num_rows($zwischenresult);$i++)
			if ($fach_klasse==sql_result ($zwischenresult, $i, 'fach_klasse.id') or (sql_result ($zwischenresult, $i, 'fach_klasse.anzeigen') and sql_result ($zwischenresult, $i, 'schule.aktiv'))) { ?>
				<option value="<?php echo sql_result ($zwischenresult, $i, 'fach_klasse.id'); ?>"<?php if ($fach_klasse==sql_result ($zwischenresult, $i, 'fach_klasse.id')) echo ' selected="selected"'; ?>><?php echo sql_result ( $zwischenresult, $i, 'schule.kuerzel' ).' - '.($aktuelles_jahr-sql_result ($zwischenresult, $i, 'klasse.einschuljahr')+1)." ".sql_result ( $zwischenresult, $i, 'klasse.endung' )." ".sql_result ( $zwischenresult, $i, 'faecher.kuerzel' )." ".sql_result ( $zwischenresult, $i, 'fach_klasse.gruppen_name' ); ?></option>
          <?php } ?>
        </select><br />
        <label for="woche">Woche:</label> <input type="radio" name="woche"<?php if ($woche==2) echo ' checked="checked"'; ?> value="2" /> beide
               <input type="radio" name="woche"<?php if ($woche==0) echo ' checked="checked"'; ?> value="0" /> A-Woche
               <input type="radio" name="woche"<?php if ($woche==1) echo ' checked="checked"'; ?> value="1" /> B-Woche<br />
        <label for="raum">Raum:</label>
		(Neu? <input type="checkbox" name="raum_neu_checkbox" value="1" onclick="document.getElementById('raum_neu').style.display=this.checked==1?'inline':'none'; document.getElementById('raum_vorhanden').style.display=this.checked==1?'none':'inline';" />)
        <select name="raum" id="raum_vorhanden">
			<?php $zwischenresult=db_conn_and_sql('SELECT * FROM `raum`,`schule`, `schule_user`
				WHERE `raum`.`schule`=`schule_user`.`schule`
					AND `schule_user`.`schule`=`schule`.`id`
				ORDER BY `raum`.`name`');
			for ($i=0;$i<sql_num_rows($zwischenresult);$i++)
				if ($raum==sql_result ($zwischenresult, $i, 'raum.id') or  (sql_result ( $zwischenresult, $i, 'raum.aktiv' ) and sql_result ( $zwischenresult, $i, 'schule.aktiv' ))) { ?>
				<option value="<?php echo sql_result ( $zwischenresult, $i, 'raum.id' ); ?>"<?php if ($raum==sql_result ($zwischenresult, $i, 'raum.id')) echo ' selected="selected"'; ?>><?php echo sql_result ( $zwischenresult, $i, 'schule.kuerzel' ).' - '.sql_result ( $zwischenresult, $i, 'raum.name' ); ?></option>
			<?php } ?>
        </select>
		<input type="text" name="raum_neu" id="raum_neu" size="5" maxlength="10" style="display: none;" />
		<br />
		<label for="wochentag">Wochentag:</label>
        <select name="wochentag">
			<?php for ($i=1;$i<=5;$i++) {echo '<option value="'.$i.'"'; if ($_GET["wochentag"]==$i) echo ' selected="selected"'; echo '>'.$wochennamen_lang[$i].'</option>';} ?>
        </select><br />
		<label for="zeit">Zeit:</label> <select name="zeit"><?php
		$zeit=db_conn_and_sql("SELECT *
			FROM `stundenzeiten`, `schule`, `schule_user`
			WHERE `stundenzeiten`.`schule`=`schule`.`id`
				AND `schule_user`.`schule`=`schule`.`id`
				AND `schule_user`.`user`=".$_SESSION['user_id']."
            ORDER BY `schule`.`name`, `stundenzeiten`.`beginn`");
					for($i=0;$i<sql_num_rows($zeit);$i++) if ($stundenzeit==sql_result($zeit,$i,"stundenzeiten.id") or sql_result($zeit,$i,"schule.aktiv")) {
						echo '<option value="'.sql_result($zeit,$i,"stundenzeiten.id").'"';
						if ($stundenzeit>0) {
							if ($stundenzeit==sql_result($zeit,$i,"stundenzeiten.id")) echo ' selected="selected"';
						} else if ($_GET["stundenzeit"]>=sql_result($zeit,$i,"stundenzeiten.beginn")) echo ' selected="selected"';
						echo '>'.html_umlaute(sql_result($zeit,$i,"schule.kuerzel")).' - '.substr(sql_result($zeit,$i,"stundenzeiten.beginn"),0,5).' Uhr</option>';
					} ?>
				</select><br />
        <input class="button" type="submit" value="eintragen" />
      </fieldset>
    </form>
	</div>
	</body>
</html>
<?php
}
