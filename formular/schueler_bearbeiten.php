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

if ($_POST["klasse"]>0)
	$klasse=$_POST["klasse"];
else
	$klasse=$_GET["klasse"];

if (userrigths("schuelerdaten", $klasse)!=2)
	die("Sie haben nicht die erforderlichen Rechte, um Sch&uuml;ler zu bearbeiten.");

if ($_GET["eintragen"]=="true") {
	$i=0;
	while(isset($_POST["schueler_".$i])) {
		// if (proofuser("schueler", $_POST["schueler_".$i]))
			db_conn_and_sql("UPDATE `schueler` SET `position`=".leer_NULL($_POST['position_'.$i]).", `name`=".apostroph_bei_bedarf($_POST['name_'.$i]).", `vorname`=".apostroph_bei_bedarf($_POST['vorname_'.$i]).", `rufname`=".apostroph_bei_bedarf($_POST['rufname_'.$i]).", `geburtstag`=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST['geburtstag_'.$i])).", `strasse`=".apostroph_bei_bedarf($_POST['strasse_'.$i]).", `ort`=".apostroph_bei_bedarf($_POST['ort_'.$i]).", `email`=".apostroph_bei_bedarf($_POST['email_'.$i]).", `telefon`=".apostroph_bei_bedarf($_POST['telefon_'.$i]).", `bemerkungen`=".apostroph_bei_bedarf($_POST['bemerkungen_'.$i]).", `maennlich`=".leer_NULL($_POST['geschlecht_'.$i]).", `geburtsort`=".apostroph_bei_bedarf($_POST['geburtsort_'.$i]).", `krankenkasse`=".apostroph_bei_bedarf($_POST['krankenkasse_'.$i]).", `notfall`=".apostroph_bei_bedarf($_POST['notfall_'.$i]).", `number`=".apostroph_bei_bedarf($_POST['number_'.$i])." WHERE `id`=".injaway($_POST['schueler_'.$i]));
		/*db_conn_and_sql("DELETE FROM `gruppe` WHERE `schueler`=".$_POST['schueler_'.$i]);
		$gruppen=0;
		while (isset($_POST["gruppe_fach".$gruppen."_".$i])) {
			if ($_POST["gruppe_fach".$gruppen."_".$i]!="-")
				db_conn_and_sql("INSERT INTO `gruppe` (`schueler`,`fach_klasse`) VALUES (".$_POST['schueler_'.$i].", ".$_POST["gruppe_fach".$gruppen."_".$i].");");
			$gruppen++;
		}*/
		$i++;
	}
	header("Location: ../index.php?tab=klassen&auswahl=".$_POST['klasse']);
	exit;
}
else {

	if ($_GET["eintragen"]=="sortieren") {
		$orderby="";
		for ($i=1; $i<=3; $i++) {
			if ($_POST["sort_".$i]=="gender_m") $orderby.="maennlich DESC, ";
			if ($_POST["sort_".$i]=="gender_w") $orderby.="maennlich, ";
			if ($_POST["sort_".$i]=="name") $orderby.="name, ";
			if ($_POST["sort_".$i]=="forename") $orderby.="vorname, ";
		}
		$orderby.="id";
		
		$result=db_conn_and_sql("SELECT * FROM schueler WHERE aktiv=1 AND klasse=".injaway($_GET['klasse'])." ORDER BY ".$orderby);
		for ($i=0; $i<sql_num_rows($result); $i++) {
			db_conn_and_sql("UPDATE schueler SET position=".($i+1)." WHERE id=".sql_result($result, $i, "schueler.id"));
		}
		header("Location: ../index.php?tab=klassen&auswahl=".$_GET['klasse']);
		exit;
	}
$titelleiste="Sch&uuml;ler bearbeiten";
include $pfad."header.php";
?>
	<body>
	<div class="inhalt">
	<h1>Sch&uuml;ler der Klasse <?php echo $school_classes->nach_ids[$_GET["klasse"]]["name"]; ?> bearbeiten</h1>
	<p><form action="<?php echo $pfad; ?>formular/schueler_bearbeiten.php?eintragen=sortieren&amp;klasse=<?php echo $_GET["klasse"]; ?>" method="post">
		<fieldset><legend>Position automatisch anpassen</legend>
		nach
		<select name="sort_1">
			<option value="gender_m">Geschlecht m/w</option>
			<option value="gender_w">Geschlecht w/m</option>
			<option value="name">Nachname</option>
			<option value="forename">Vorname</option>
		</select>
		<select name="sort_2">
			<option value="gender_m">Geschlecht m/w</option>
			<option value="gender_w">Geschlecht w/m</option>
			<option value="name" selected="selected">Nachname</option>
			<option value="forename">Vorname</option>
		</select>
		<select name="sort_3">
			<option value="gender_m">Geschlecht m/w</option>
			<option value="gender_w">Geschlecht w/m</option>
			<option value="name">Nachname</option>
			<option value="forename" selected="selected">Vorname</option>
		</select>
		<input type="submit" value="sortieren" />
	</fieldset>
    </form></p>
    
    <form action="<?php echo $pfad; ?>formular/schueler_bearbeiten.php?eintragen=true" method="post" accept-charset="ISO-8859-1">
		<input type="hidden" name="klasse" value="<?php echo $_GET["klasse"]; ?>" />
	<?php /*$result=db_conn_and_sql("SELECT *
		FROM `schueler` WHERE `schueler`.`id` = ".$_GET["wen"]);*/
		if (!proofuser("klasse", $_GET["klasse"]))
			die("Sie sind hierzu nicht berechtigt.");
		$schueler_der_klasse=db_conn_and_sql("SELECT * FROM `klasse`,`schueler` WHERE `schueler`.`klasse`=`klasse`.`id` AND `klasse`.`id`=".injaway($_GET["klasse"])." ORDER BY `schueler`.`position`");
		
		$edit_recht=' readonly="readonly" style="background-color: yellow;"';
		if (userrigths("schueler_verwaltung", sql_result($schueler_der_klasse, 0, 'klasse.schule'))==2)
			$edit_recht='';
		else
			echo '<div class="hinweis">Gelbe Felder sind nur von Verwaltung oder Schulleitung ver&auml;nderbar. Bemerkungen sind f&uuml;r alle Lehrer sichtbar.</div>';
		?>
    <table class="tabelle" cellspacing="0">
      <tr>
        <th title="Position im Klassenbuch">Pos</th>
        <th title="m&auml;nnlich / weiblich">Name, Vorname, m/w</th>
        <th>Geburtstag / -ort</th>
        <th>Anschrift</th>
        <th>Tel / eMail</th>
        <th>Krankenkasse/Notfallperson</th>
        <th>Bemerkungen</th>
        <!--<th>Gruppe</th>-->
      </tr>
	<?php
		for ($durchzaehlen=0;$durchzaehlen<sql_num_rows($schueler_der_klasse);$durchzaehlen++) {  ?>
      <tr>
        <td><input type="hidden" name="schueler_<?php echo $durchzaehlen; ?>" value="<?php echo sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.id'); ?>" />
				<input type="text" name="position_<?php echo $durchzaehlen; ?>" tabindex="<?php echo $durchzaehlen; ?>" value="<?php echo sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.position'); ?>" size="1" maxlength="2" /></td>
        <td><input type="text" <?php echo $edit_recht; ?>name="name_<?php echo $durchzaehlen; ?>" tabindex="<?php echo 2*$durchzaehlen+1*sql_num_rows($schueler_der_klasse); ?>" value="<?php echo html_umlaute(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.name')); ?>" size="9" maxlength="50" /><br />
			<input type="text" <?php echo $edit_recht; ?>name="vorname_<?php echo $durchzaehlen; ?>" tabindex="<?php echo 2*$durchzaehlen+1+1*sql_num_rows($schueler_der_klasse); ?>" value="<?php echo html_umlaute(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.vorname')); ?>" size="9" maxlength="30" />
			<input type="text" name="rufname_<?php echo $durchzaehlen; ?>" tabindex="<?php echo 2*$durchzaehlen+1+1*sql_num_rows($schueler_der_klasse); ?>" value="<?php echo html_umlaute(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.rufname')); ?>" placeholder="Rufname" title="Helfen Sie Ihren Kollegen, indem Sie den Namen eintragen, mit dem der Sch&uuml;ler angesprochen werden m&ouml;chte. Dies ist unter Anderem bei Doppelnamen (z.B. Luisa Jaqueline) sinnvoll. Dieser Name steht dann in allen Sitzpl&auml;nen." size="5" maxlength="30" />
			<input type="hidden" name="klasse_<?php echo $durchzaehlen; ?>" value="<?php echo sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.klasse'); ?>" />
			<select tabindex="<?php echo $durchzaehlen+3*sql_num_rows($schueler_der_klasse); ?>" <?php echo $edit_recht; ?>name="geschlecht_<?php echo $durchzaehlen; ?>"><option value="1">m</option><option value="0"<?php if(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.maennlich' )=="0") echo ' selected="selected"'; ?>>w</option></select></td>
        <td><input type="text" <?php echo $edit_recht; ?>name="geburtstag_<?php echo $durchzaehlen; ?>" id="geburtstag_<?php echo $durchzaehlen; ?>" tabindex="<?php echo $durchzaehlen+4*sql_num_rows($schueler_der_klasse); ?>" value="<?php echo datum_strich_zu_punkt(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.geburtstag' )); ?>" size="10" maxlength="10" /><br />
			<input type="text" <?php echo $edit_recht; ?>name="geburtsort_<?php echo $durchzaehlen; ?>" tabindex="<?php echo $durchzaehlen+5*sql_num_rows($schueler_der_klasse); ?>" value="<?php echo html_umlaute(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.geburtsort' )); ?>" size="10" maxlength="80" /></td>
        <td><input type="text" <?php echo $edit_recht; ?>name="strasse_<?php echo $durchzaehlen; ?>" tabindex="<?php echo 2*$durchzaehlen+6*sql_num_rows($schueler_der_klasse); ?>" value="<?php echo html_umlaute(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.strasse' )); ?>" size="15" maxlength="80" /><br />
			<input type="text" <?php echo $edit_recht; ?>name="ort_<?php echo $durchzaehlen; ?>" tabindex="<?php echo 2*$durchzaehlen+1+6*sql_num_rows($schueler_der_klasse); ?>" value="<?php echo html_umlaute(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.ort' )); ?>" size="15" maxlength="80" /></td>
        <td><input type="text" <?php echo $edit_recht; ?>name="telefon_<?php echo $durchzaehlen; ?>" value="<?php echo html_umlaute(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.telefon' )); ?>" size="15" maxlength="80" /><br />
			<input type="text" name="email_<?php echo $durchzaehlen; ?>" id="email_<?php echo $durchzaehlen; ?>" tabindex="<?php echo $durchzaehlen+8*sql_num_rows($schueler_der_klasse); ?>" value="<?php echo html_umlaute(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.email' )); ?>" size="15" maxlength="80" /></td>
        <td><input type="text" <?php echo $edit_recht; ?>name="krankenkasse_<?php echo $durchzaehlen; ?>" tabindex="<?php echo $durchzaehlen+9*sql_num_rows($schueler_der_klasse); ?>" value="<?php echo html_umlaute(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.krankenkasse' )); ?>" size="15" maxlength="50" /><br />
			<input type="text" name="notfall_<?php echo $durchzaehlen; ?>" tabindex="<?php echo $durchzaehlen+10*sql_num_rows($schueler_der_klasse); ?>" value="<?php echo html_umlaute(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.notfall' )); ?>" size="15" maxlength="150" /></td>
        <td><textarea name="bemerkungen_<?php echo $durchzaehlen; ?>" tabindex="<?php echo $durchzaehlen+11*sql_num_rows($schueler_der_klasse); ?>" cols="30" rows="2"><?php echo html_umlaute(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.bemerkungen' )); ?></textarea><br />
			<input type="text" <?php echo $edit_recht; ?>name="number_<?php echo $durchzaehlen; ?>" tabindex="<?php echo $durchzaehlen+12*sql_num_rows($schueler_der_klasse); ?>" value="<?php echo html_umlaute(sql_result($schueler_der_klasse, $durchzaehlen, 'schueler.number' )); ?>" placeholder="Sch&uuml;lernummer" size="2" maxlength="10" /></td>
      </tr>
	<?php } ?>
	</table>
	<input type="button" class="button" value="speichern" onclick="auswertung=new Array; for (i=0; i&lt;<?php echo ($durchzaehlen); ?>; i++) { auswertung.push(new Array(1, 'position_' + i,'natuerliche_zahl'), new Array(1, 'name_' + i,'nicht_leer'), new Array(1, 'vorname_' + i,'nicht_leer')); if (document.getElementById('geburtstag_' + i).value!='') auswertung.push(new Array(1, 'geburtstag_' + i,'datum','1950-01-01','<?php echo ($aktuelles_jahr-3); ?>-01-01')); if (document.getElementById('email_' + i).value!='') auswertung.push(new Array(1, 'email_'+i,'email'));} pruefe_formular(auswertung);" />
	</form>
	</div>
	</body>
</html>
<?php
}
?>
