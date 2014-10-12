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
$titelleiste="Neue Vorlage erstellen";
include $pfad."header.php";
include $pfad."funktionen.php";

$user=new user();
$schule=$user->my["letzte_schule"];

$notentypen_result = db_conn_and_sql ( 'SELECT * FROM `notentypen` WHERE `aktiv`=1 AND (`schule`='.$schule.' OR `id`<11) ORDER BY `notentypen`.`kuerzel`' );

?>
<body>
<div class="inhalt">
	<div id="mf">
		<ul class="r">
			<li><a href="javascript: opener.location.reload(); window.close();" class="icon">
				<img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="x" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>

<?php
if ($_GET["eintragen"]=="neu") {
	// vorlagendaten erstellen
	$vorlagen_id=db_conn_and_sql("INSERT INTO `notenberechnungsvorlage` (`name`, `beschreibung`, `schule`, `aktiv`, `user`) VALUES
	(".apostroph_bei_bedarf($_POST['vorlagen_name']).", ".apostroph_bei_bedarf($_POST['vorlagen_beschreibung']).", ".leer_NULL($_POST['vorlagen_schule']).", 1, ".$_SESSION['user_id'].");");
}
if ($_GET["eintragen"]=="bearbeiten") {
	if (!proofuser("notenberechnungsvorlage",$_POST["vorlagen_id"]) and userrigths("notenberechnungsvorlagen", $schule)!=2)
		die("Sie sind nicht berechtigt, diese Zensurenberechnungsvorlage zu bearbeiten.");
	$vorlagen_id=$_POST["vorlagen_id"];
	db_conn_and_sql("UPDATE `notenberechnungsvorlage` SET `name`=".apostroph_bei_bedarf($_POST['vorlagen_name']).", `beschreibung`=".apostroph_bei_bedarf($_POST['vorlagen_beschreibung']).", `schule`=".leer_NULL($_POST['vorlagen_schule']).", `aktiv`=".leer_NULL($_POST['vorlagen_aktiv'])." WHERE `id`=".$vorlagen_id);
}
if ($_GET["eintragen"]=="bearbeiten" or $_GET["eintragen"]=="neu") {
	// Falls eine neue Notengruppe erstellt werden soll:
	$gruppe_neu_id='';
	if ($_POST["neue_notengruppe"]) {
		$gruppe_neu_id=db_conn_and_sql("INSERT INTO `notengruppe` (`faktor`) VALUES (".injaway($_POST["notengruppen_faktor"]).");");
	}
	
	// Notenberechnung eintragen
	$notentypen=db_conn_and_sql("SELECT * FROM `notentypen` WHERE `id`<11 OR `schule`=".$schule);
	for ($i=0;$i<sql_num_rows($notentypen);$i++) {
		if ($_POST["gruppe_".$i]=="neu")
			$gruppen_id=$gruppe_neu_id;
		else
			$gruppen_id=$_POST["gruppe_".$i];
		$notenberechnung=db_conn_and_sql("SELECT * FROM `notenberechnung` WHERE `vorlage`=".$vorlagen_id." AND `notentyp`=".injaway($_POST["notentypen_id_".$i]));
		if ($gruppen_id==0) $gruppen_id='';
		// warum hab ich das vorher gemacht?!?: if ($gruppen_id>0) {
			if (@sql_num_rows($notenberechnung)>0)
				db_conn_and_sql("UPDATE `notenberechnung` SET `faktor`=".punkt_statt_komma_zahl($_POST["faktor_".$i]).", `gruppe`=".leer_NULL($gruppen_id)." WHERE `vorlage`=".$vorlagen_id." AND `notentyp`=".injaway($_POST["notentypen_id_".$i]));
			else
				db_conn_and_sql("INSERT INTO `notenberechnung` (`vorlage`, `notentyp`, `faktor`, `gruppe`) VALUES (".$vorlagen_id.", ".injaway($_POST["notentypen_id_".$i]).", ".punkt_statt_komma_zahl($_POST["faktor_".$i]).", ".leer_NULL($gruppen_id).");");
	}
	
	// hinterher nicht mehr benoetigte Notengruppen loeschen
	$notenberechnung=db_conn_and_sql("SELECT * FROM notengruppe LEFT JOIN notenberechnung ON notenberechnung.gruppe=notengruppe.id WHERE notenberechnung.gruppe IS NULL");
	if(@sql_num_rows($notenberechnung)>0)
		for ($i=0;$i<sql_num_rows($notenberechnung); $i++)
			db_conn_and_sql("DELETE FROM notengruppe WHERE id=".sql_result($notenberechnung,$i,"notengruppe.id"));
	
}

if (isset($_GET["id"])) {
	$vorlagen_id=$_GET["id"];
	if (!proofuser("notenberechnungsvorlage",$vorlagen_id) and userrigths("notenberechnungsvorlagen", $schule)!=2)
		die("Sie sind nicht berechtigt, diese Zensurenberechnungsvorlage zu bearbeiten.");
}
if ($vorlagen_id>0)
	$notenvorlage_result=db_conn_and_sql("SELECT * FROM notenberechnungsvorlage WHERE id=".$vorlagen_id);
 ?>
<form action="<?php echo $pfad; ?>formular/notenberechnungsvorlage.php?eintragen=<?php if ($vorlagen_id!="") echo "bearbeiten"; else echo "neu"; ?>" method="post" accept-charset="ISO-8859-1">
      <fieldset><legend><?php if ($vorlagen_id!="") echo "Notenberechnung bearbeiten"; else echo "Neue Notenberechnung eintragen"; ?></legend>
      <input type="hidden" name="vorlagen_id" value="<?php echo $vorlagen_id; ?>" />
      <a href="" onclick="javascript: document.getElementById('bsp_noten_tabelle').style.display='block'; return false;" title="hier klicken, um Beispiele anzuzeigen">Beispiel</a>
      <br />
	<span id="bsp_noten_tabelle" style="display: none;">
	<table class="tabelle" cellspacing="0">
		<tr><th>Beispiele</th><?php for ($i=0;$i<sql_num_rows($notentypen_result);$i++) echo '<th title="'.html_umlaute(sql_result($notentypen_result,$i,"notentypen.name")).'">'.html_umlaute(sql_result($notentypen_result,$i,"notentypen.kuerzel")).'</th>' ?><th>Berechnung</th></tr>
		<tr><td>alle gleichwertig</td><?php for ($i=0;$i<sql_num_rows($notentypen_result);$i++) echo '<td>1</td>'; ?><td>einfaches arithm. Mittel</td></tr>
		<tr><td>KA doppelt</td><?php for ($i=0;$i<sql_num_rows($notentypen_result);$i++) if (html_umlaute(sql_result($notentypen_result,$i,"notentypen.kuerzel"))=="KA") echo '<td>2</td>'; else echo '<td>1</td>'; ?><td>z.B. (MDL+KK+2*KA+MDL)/5</td></tr>
		<tr><td>MDL zusammengefasst</td><?php for ($i=0;$i<sql_num_rows($notentypen_result);$i++) if (html_umlaute(sql_result($notentypen_result,$i,"notentypen.kuerzel"))=="MDL") echo '<td>GA(2f) 1</td>'; else echo '<td>1</td>'; ?><td>aus mehreren mdl. Zensuren eine bilden und als 2-fache Note werten</td></tr>
		<tr><td>KA-Gruppe / Restgruppe</td><?php for ($i=0;$i<sql_num_rows($notentypen_result);$i++) if (html_umlaute(sql_result($notentypen_result,$i,"notentypen.kuerzel"))=="KA") echo '<td>GA(3f) 1</td>'; else echo '<td>GB(2f) 1</td>'; ?><td>(3*(alle Klassenarbeiten)+2*(Rest))/5</td></tr>
	</table>
	<br />
	<br />
	</span>
      
	<label for="vorlagen_name">Name:</label> <input type="text" name="vorlagen_name" value="<?php if($vorlagen_id!="") echo sql_result($notenvorlage_result,0,"notenberechnungsvorlage.name"); ?>" size="10" maxlength="30" /><br />
	<label for="vorlagen_beschreibung">Beschreibung:</label> <input type="text" name="vorlagen_beschreibung" value="<?php if($vorlagen_id!="") echo sql_result($notenvorlage_result,0,"notenberechnungsvorlage.beschreibung"); ?>" size="20" maxlength="200" /><br />
	<label for="vorlagen_schule">&Ouml;ffentlich:</label> <select name="vorlagen_schule">
		<option value="">eigene Vorlage</option>
		<?php
		$meine_schulen=db_conn_and_sql("SELECT * FROM schule, schule_user WHERE schule.id=schule_user.schule AND schule_user.aktiv=1 AND schule_user.user=".$_SESSION["user_id"]);
		if (sql_num_rows($meine_schulen)>0)
			for ($i=0; $i<sql_num_rows($meine_schulen); $i++) {
				echo '<option value="'.sql_result($meine_schulen,$i,"schule.id").'"';
				if ($vorlagen_id>0 and sql_result($notenvorlage_result,0,"notenberechnungsvorlage.schule")==sql_result($meine_schulen,$i,"schule.id"))
					echo ' selected="selected"';
				echo '>'.sql_result($meine_schulen,$i,"schule.kuerzel").'</option>';
			}
		?>
	</select><br />
	<?php if ($vorlagen_id!="") { ?>
		<label for="vorlagen_aktiv">Aktiv:</label> <input type="checkbox" name="vorlagen_aktiv" value="1"<?php if(sql_result($notenvorlage_result,0,"notenberechnungsvorlage.aktiv")==1) echo ' checked="checked"'; ?> /><br />
	<?php } ?>
	<?php if ($vorlagen_id>0)
			$notengruppen=db_conn_and_sql("SELECT DISTINCT notengruppe.id, notengruppe.faktor
				FROM notenberechnung, notengruppe
				WHERE vorlage=".$vorlagen_id." AND notenberechnung.gruppe=notengruppe.id"); ?>
	Zensurengruppe "G<?php echo chr(@sql_num_rows($notengruppen)+65); ?>" erstellen
	<input type="checkbox" id="neue_notengruppe" name="neue_notengruppe" value="1" onchange="if (this.checked==true) getElementById('neue_gruppe_erstellen').style.display='inline'; else getElementById('neue_gruppe_erstellen').style.display='none';" />
	<span id="neue_gruppe_erstellen" style="display: none;">
	mit Faktor: <input type="text" name="notengruppen_faktor" value="1" size="1" maxlength="5" title="Faktor, mit dem die Zensurengruppe multipliziert wird (Zensurengruppenwichtung)" />
	<span class="hinweis">Weitere Zensurengruppen k&ouml;nnen nach dem Eintragen hinzugef&uuml;gt werden</span></span>
	<table class="tabelle" cellspacing="0">
		<tr><th></th><?php for ($i=0;$i<sql_num_rows($notentypen_result);$i++) echo '<th title="'.html_umlaute(sql_result($notentypen_result,$i,"notentypen.name")).'">'.html_umlaute(sql_result($notentypen_result,$i,"notentypen.kuerzel")).'</th>' ?></tr>
		<tr><td>Faktor</td><?php
		$auswertung='';
		for ($i=0;$i<sql_num_rows($notentypen_result);$i++) {
			if ($i>0) $auswertung.=", ";
			$auswertung.="new Array(0, 'faktor_".$i."','pos_komma_zahl')";
			if ($vorlagen_id>0)
				$notenberechnung=db_conn_and_sql("SELECT * FROM `notenberechnung` WHERE `vorlage`=".$vorlagen_id." AND `notentyp`=".sql_result($notentypen_result,$i,"notentypen.id"));
			echo '<td>
				<input type="hidden" name="notentypen_id_'.$i.'" value="'.sql_result($notentypen_result,$i,"notentypen.id").'" />
				<select name="gruppe_'.$i.'" onchange="if (this.value==\'neu\') {getElementById(\'neue_gruppe_erstellen\').style.display=\'inline\'; getElementById(\'neue_notengruppe\').checked=true;}"><option value="0">-</option>';
			for ($j=0;$j<sql_num_rows($notengruppen);$j++) {
                echo '<option value="'.sql_result($notengruppen,$j,"notengruppe.id").'"';
                if (@sql_result($notenberechnung,0,"notenberechnung.gruppe")==@sql_result($notengruppen,$j,"notengruppe.id"))
                    echo ' selected="selected"'; echo '>G'.chr($j+65).' ('.(sql_result($notengruppen,$j,"notengruppe.faktor")+0).'f)</option>';
            }
			echo '<option value="neu">G'.chr($j+65).'</option></select>
				<input type="text" name="faktor_'.$i.'" value="';
			if (@sql_num_rows($notenberechnung)>0)
				echo kommazahl(sql_result($notenberechnung,0,"notenberechnung.faktor"));
            else echo "1";
			echo '" size="1" maxlength="5" title="Faktor, mit dem die Note multipliziert wird (Einzelzensurenwichtung) - auch innerhalb einer Zensurengruppe" /></td>';
		} ?></tr>
			</table>
		<input type="button" class="button" value="speichern" onclick="auswertung=new Array(<?php echo $auswertung; ?>); pruefe_formular(auswertung);" />
      </fieldset>
      </form>
</div>
</body>
</html>
