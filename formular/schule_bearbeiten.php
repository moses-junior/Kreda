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
include "../funktionen.php";
if ($_GET["eintragen"]=="true" and userrigths("schuldaten",$_POST["schule"])) {
	$i=0;
	db_conn_and_sql("UPDATE `schule` SET `aktiv`=".leer_NULL($_POST['aktiv']).", `kuerzel`=".apostroph_bei_bedarf($_POST['kurz']).", `name`=".apostroph_bei_bedarf($_POST['name']).", `adresse`=".apostroph_bei_bedarf($_POST['adresse']).", `plz_ort`=".apostroph_bei_bedarf($_POST['plz_ort']).", `telefon`=".apostroph_bei_bedarf($_POST['telefon']).", `fax`=".apostroph_bei_bedarf($_POST['fax']).", `schulleiter`=".leer_NULL($_POST['schulleiter'])." WHERE `id`=".injaway($_POST['schule']));
	db_conn_and_sql("UPDATE `schule_user` SET `aktiv`=".leer_NULL($_POST['aktiv'])." WHERE `user`=".$_SESSION['user_id']." AND `schule`=".injaway($_POST['schule']));
	db_conn_and_sql("DELETE FROM `schule_schulart` WHERE `schule`=".injaway($_POST['schule']));
	$schularten=0;
	while ($schularten<=$_POST["anzahl_schularten"]) {
		if ($_POST["schulart_".$schularten]>0)
			db_conn_and_sql("INSERT INTO `schule_schulart` (`schule`,`schulart`) VALUES (".injaway($_POST['schule']).", ".injaway($_POST["schulart_".$schularten]).");");
		$schularten++;
	}
	
	// bestehende Raeume aktualisieren
	$i=0;
	while($_POST["raum_".$i]!="") {
		db_conn_and_sql("UPDATE `raum` SET `aktiv`=".leer_NULL($_POST["raum_aktiv_".$i]).", `kommentar`=".apostroph_bei_bedarf($_POST["raumkommentar_".$i])." WHERE `id`=".injaway($_POST["raum_".$i]));
		$i++;
	}
	
	// neue Raeume eintragen
	$i=0;
	while(isset($_POST["raum_neu_".$i]) and $_POST["raum_neu_".$i]!="") {
		db_conn_and_sql("INSERT INTO `raum` SET `aktiv`=1, `name`=".apostroph_bei_bedarf($_POST["raum_neu_".$i]).",`kommentar`=".apostroph_bei_bedarf($_POST["raumkommentar_neu_".$i]).", `schule`=".injaway($_POST["schule"]));
		$i++;
	}
	
	//neue Stundenzeit eintragen
	if ($_POST["zeit_uhr_0"]!="") {
		/*db_conn_and_sql("INSERT INTO `stundenzeiten_beschreibung` (`beschreibung`,`gilt_seit`, `schule`, `schulart`) VALUES (".apostroph_bei_bedarf($_POST['name']).", '".date("Y-m-d",time())."', ".$_POST["schule"].", ".$_POST["stundenzeiten_schulart_neu"].");");
		$stz_b_id=sql_insert_id();*/
		$stundenzeiten=db_conn_and_sql("SELECT * FROM stundenzeiten WHERE schule=".injaway($_POST["schule"])." ORDER BY stundenzeiten.beginn");
		for ($i=0; $i<sql_num_rows($stundenzeiten); $i++) {
			if ($_POST["zeit_uhr_".$i]!="")
				db_conn_and_sql("UPDATE stundenzeiten SET beginn=".apostroph_bei_bedarf($_POST["zeit_uhr_".$i].":00")." WHERE id=".sql_result($stundenzeiten,$i,"id"));
			// else loeschen wird nicht erlaubt, da eventuell Stunden dort geplant sind - evtl. stundenzeiten.aktiv hinzufuegen
		}
		while($_POST["zeit_uhr_".$i]!="") {
			db_conn_and_sql("INSERT INTO `stundenzeiten` (`beginn`, `schule`) VALUES (".apostroph_bei_bedarf($_POST["zeit_uhr_".$i].":00").", ".injaway($_POST["schule"]).");");
			$i++;
		}
	}
	
	header("Location: ../index.php?tab=einstellungen&auswahl=schulen&erstellen=raum");
	exit;
}
else {
	$titelleiste="Schule bearbeiten";
	include $pfad."header.php";
	
	if (!userrigths("schuldaten",$_GET["schule"]))
		die("Sie sind hierzu nicht berechtigt.");
	
	?>
	<body>
	<div id="mf">
		<ul class="r">
			<li><a href="javascript:window.back();" class="icon"><img src="<?php echo $pfad; ?>icons/pfeil_links.png" alt="zurueck" /> zur&uuml;ck</a></li>
		</ul>
	</div>	<div class="inhalt">
    <form action="<?php echo $pfad; ?>formular/schule_bearbeiten.php?eintragen=true&amp;jahr=<?php echo $_GET["jahr"]; ?>" method="post" accept-charset="ISO-8859-1">
		<input type="hidden" name="schule" value="<?php echo $_GET["schule"]; ?>" />
	<?php
		$schule=db_conn_and_sql("SELECT * FROM `schule_user`, `schule` LEFT JOIN `schule_schulart` ON `schule_schulart`.`schule`=`schule`.`id` WHERE `schule_user`.`schule`=`schule`.`id` AND `schule_user`.`user`=".$_SESSION['user_id']." AND `schule`.`id`=".injaway($_GET["schule"])." ORDER BY `schule_schulart`.`schulart`");
		?>
    <fieldset><legend>Schul-Daten</legend>
		<label for="aktiv">Schule anzeigen:</label> <input type="checkbox" name="aktiv" value="1" <?php if (sql_result($schule,0,"schule_user.aktiv")) echo ' checked="checked"'; ?> /><br />
		<label for="kurz">K&uuml;rzel<em>*</em>:</label> <input type="text" name="kurz" size="5" maxlength="8" value="<?php echo html_umlaute(sql_result($schule,0,'schule.kuerzel')); ?>" />
		<label for="name">Schulname<em>*</em>:</label> <input type="text" name="name" size="35" maxlength="50" value="<?php echo html_umlaute(sql_result($schule,0,'schule.name')); ?>" /><br />
		<label for="schulart_0">Schulart(en)<em>*</em>:</label> <?php $schulart=db_conn_and_sql("SELECT * FROM `schulart` ORDER BY `schulart`.`id`");
		$schularten_aktiv=0;
		// Workaround: durch einen Bug gibt es eine eingetragene schulart 0 bei schule_schulart
		if (@sql_result($schule,$schularten_aktiv,'schule_schulart.schulart')==0)
			$schularten_aktiv++;
		
		echo '<input type="hidden" name="anzahl_schularten" value="'.sql_num_rows($schulart).'" />';
	  for ($i=0;$i<sql_num_rows($schulart);$i++) { ?>
			<input type="checkbox" name="schulart_<?php echo $i; ?>" value="<?php echo sql_result($schulart,$i,'schulart.id'); ?>"<?php if(sql_result($schulart,$i,'schulart.id')==@sql_result($schule,$schularten_aktiv,'schule_schulart.schulart')) { echo ' checked="checked"'; $schularten_aktiv++; } ?> /><?php echo html_umlaute(sql_result($schulart,$i,'schulart.kuerzel'));
       } ?><br />
		<label for="adresse">Adresse:</label> <input type="text" name="adresse" size="35" maxlength="150" value="<?php echo html_umlaute(sql_result($schule,0,'schule.adresse')); ?>" />
		<label for="plz_ort">PLZ Ort:</label> <input type="text" name="plz_ort" size="35" maxlength="150" value="<?php echo html_umlaute(sql_result($schule,0,'schule.plz_ort')); ?>" /><br />
		<label for="telefon">Telefon:</label> <input type="text" name="telefon" size="35" maxlength="50" value="<?php echo html_umlaute(sql_result($schule,0,'schule.telefon')); ?>" />
		<label for="fax">Fax:</label> <input type="text" name="fax" size="35" maxlength="50" value="<?php echo html_umlaute(sql_result($schule,0,'schule.fax')); ?>" /><br />
		<label for="schulleiter">Schulleiter:</label> <input type="text" name="schulleiter" size="35" maxlength="150" value="<?php echo html_umlaute(sql_result($schule,0,'schule.schulleiter')); ?>" />
    </fieldset>
	
	<fieldset><legend>R&auml;ume</legend>
		<div class="hinweis">Hier k&ouml;nnen neue R&auml;ume eingetragen werden. Alte R&auml;ume bleiben aufgrund der Datenkonsistenz bestehen und k&ouml;nnen lediglich deaktiviert werden. Im jeweils zweiten Feld k&ouml;nnen au&szlig;erdem Kommentare f&uuml;r einen Raum hinterlassen werden. Es reicht aus, wenn Sie nur die R&auml;ume eintragen, in denen Sie unterrichten.</div>
		Bestehende R&auml;ume:
		<ul>
		<?php $raeume=db_conn_and_sql("SELECT *
				FROM `raum` WHERE `raum`.`schule`=".injaway($_GET["schule"])." ORDER BY `raum`.`name`");
			for($k=0;$k<sql_num_rows($raeume);$k++) {
				echo '<li style="float:left; margin-right:25px;">'.html_umlaute(sql_result($raeume,$k,"raum.name")).': <input type="checkbox" name="raum_aktiv_'.$k.'" value="1"';
				if (sql_result($raeume,$k,"raum.aktiv")) echo ' checked="checked"';
				echo ' title="aktiv?" /><input type="hidden" name="raum_'.$k.'" value="'.html_umlaute(sql_result($raeume,$k,"raum.id")).'" />
				<!--<input type="text" name="raumkommentar_'.$k.'" value="'.html_umlaute(sql_result($raeume,$k,"raum.kommentar")).'" size="5" maxlength="100" title="Raumkommentar" />--></li>';
			}
			?>
		</ul>
		<p style="clear: both;">Neu:</p>
		<ul>
		<?php
			for($rn=0;$rn<30;$rn++) {
				echo '<li style="float:left; margin-right:25px;';
				if ($rn>0) echo ' display:none;';
				echo '" id="rn_'.$rn.'"><input type="text" name="raum_neu_'.$rn.'" size="2" maxlength="10" onkeypress="document.getElementById(\'rn_'.($rn+1).'\').style.display=\'block\'" />
				<!--<input type="text" name="raumkommentar_neu_'.$rn.'" size="5" maxlength="100" title="Raumkommentar" />--></li>';
			}
		?>
		</ul>
	</fieldset>

	<fieldset><legend>Unterrichtszeiten</legend>
		<div class="hinweis"><p>Um den Stundenplan nutzen zu k&ouml;nnen, m&uuml;ssen Sie hier den Beginn der Unterrichtszeiten der Schule im Format HH:MM eintragen.<br />Eine Unterrichtsstunde ist prinzipiell 45 Minuten lang. Bei Doppelstunden tragen Sie eine anschlie&szlig;ende zus&auml;tzliche Einzelstunde ein.</p></div>
		<p>Bestehende Zeiten:
		<?php
			$einzelzeiten=db_conn_and_sql("SELECT * FROM `stundenzeiten` WHERE `stundenzeiten`.`schule`=".injaway($_GET["schule"])." ORDER BY `stundenzeiten`.`beginn`");
			for($rn=0;$rn<sql_num_rows($einzelzeiten);$rn++) {
				echo substr(sql_result($einzelzeiten,$rn,"stundenzeiten.beginn"),0,5).' | ';
			}
		?>
		</p>
		Neu:
		<ul>
		<?php
				for($rn=0;$rn<20;$rn++) {
					echo '<li style="float:left; margin-right:25px;';
					if ($rn>8 and $rn>sql_num_rows($einzelzeiten))
						echo ' display: none;';
					echo '" id="zt_'.$rn.'">
					<input type="text" id="zeit_uhr_'.$rn.'" name="zeit_uhr_'.$rn.'" size="4" maxlength="5" value="'.substr(sql_result($einzelzeiten,$rn,"stundenzeiten.beginn"),0,5).'" onkeypress="document.getElementById(\'zt_'.($rn+1).'\').style.display=\'block\'" />
					</li>';
				}
		?>
		</ul>
	</fieldset>
	
    <input type="button" class="button" value="speichern" onclick="auswertung=new Array(new Array(0, 'kurz','nicht_leer'), new Array(0, 'name','nicht_leer'));
	i=0;
	while(getElementById('zeit_uhr_'+i).value!='') {
        if (getElementById('zeit_uhr_'+i).value.length==4)
            getElementById('zeit_uhr_'+i).value='0'+getElementById('zeit_uhr_'+i).value;
        auswertung.push(new Array(0,'zeit_uhr_'+i,'zeit','03:00','23:30')); i++;}
	pruefe_formular(auswertung);" />
	</form>
	</div>
	</body>
	</html>
<?php
}
?>
