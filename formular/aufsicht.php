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

switch ($_GET["eintragen"]) {
	case "true":
		// neue/vorhandene Aufsicht
		if ($_POST["aufsicht_id"]>0) {
			db_conn_and_sql("UPDATE aufsicht SET schule=".injaway($_POST["schule"]).", schuljahr=".injaway($_POST['schuljahr']).", wochentag=".injaway($_POST['wochentag']).", woche=".leer_NULL($_POST["woche"]).", nach_stunde=".injaway($_POST["nach_stunde"]).", bemerkung=".apostroph_bei_bedarf($_POST['bemerkung'])."
				WHERE `id`=".injaway($_POST["aufsicht_id"]));
		}
		else
			db_conn_and_sql("INSERT INTO aufsicht (schule, schuljahr, wochentag, woche, nach_stunde, bemerkung, user) VALUES
				(".injaway($_POST["schule"]).", ".injaway($_POST['schuljahr']).", ".injaway($_POST['wochentag']).", ".leer_NULL($_POST["woche"]).", ".injaway($_POST["nach_stunde"]).", ".apostroph_bei_bedarf($_POST['bemerkung']).", ".$_SESSION["user_id"].");");
		?>
		<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
		<?php
		break;
	case "loeschen":
		if (proofuser("aufsicht",$_GET["aufsicht_id"]))
		db_conn_and_sql("DELETE FROM aufsicht WHERE id=".injaway($_GET["aufsicht_id"]));
		?>
		<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
		<?php
	break;
	default:
		$titelleiste="Aufsicht f&uuml;r den Stundenplan von ".$aktuelles_jahr."/".($aktuelles_jahr+1);
		include $pfad."header.php";
		?>
			<body>
			<div class="inhalt">
			<form action="<?php echo $pfad; ?>formular/aufsicht.php?eintragen=true" method="POST" accept-charset="ISO-8859-1">
				<?php
					$woche=2;
					if($_GET["aufsicht_id"]>0 and proofuser("aufsicht", $_GET["aufsicht_id"])) {
						$bearbeiten=db_conn_and_sql("SELECT * FROM aufsicht WHERE id=".injaway($_GET["aufsicht_id"]));
						$bearbeiten=sql_fetch_assoc($bearbeiten);
						$woche=$bearbeiten["woche"];
						echo '<input type="hidden" name="aufsicht_id" value="'.injaway($_GET["aufsicht_id"]).'" />';
					}
				?>
				<input type="hidden" name="schuljahr" value="<?php echo $aktuelles_jahr; ?>" />
				<fieldset><legend>Aufsicht <?php if($_GET["aufsicht_id"]>0) echo '&auml;ndern'; else echo 'hinzuf&uuml;gen'; ?> (Stundenplan <?php echo $aktuelles_jahr."/".($aktuelles_jahr+1); ?>)</legend>
				<label for="woche">Woche:</label> <input type="radio" name="woche"<?php if ($woche==2) echo ' checked="checked"'; ?> value="2" /> beide
					<input type="radio" name="woche"<?php if ($woche==0) echo ' checked="checked"'; ?> value="0" /> A-Woche
					<input type="radio" name="woche"<?php if ($woche==1) echo ' checked="checked"'; ?> value="1" /> B-Woche<br />
				<label for="wochentag">Wochentag:</label>
				<select name="wochentag">
					<?php for ($i=1;$i<=5;$i++) {
						echo '<option value="'.$i.'"';
						if ($_GET["aufsicht_id"]>0 and $bearbeiten["wochentag"]==$i) echo ' selected="selected"';
						echo '>'.$wochennamen_kurz[$i].'</option>';
					} ?>
				</select><br />
				<label for="schule">Schule:</label> <select name="schule"><?php
					$schule=db_conn_and_sql("SELECT * FROM schule, schule_user WHERE schule_user.aktiv=1 AND schule_user.schule=schule.id AND schule_user.user=".$_SESSION['user_id']);
					for ($i=0;$i<sql_num_rows($schule);$i++) { ?>
						<option value="<?php echo sql_result($schule,$i,'schule.id'); ?>"<?php if ($_GET["aufsicht_id"]>0 and $bearbeiten["schule"]==sql_result($schule,$i,'schule.id')) echo ' selected="selected"'; ?>><?php echo html_umlaute(sql_result($schule,$i,'schule.kuerzel')); ?></option>
					<?php } ?>
				</select><br />
				<label for="nach_stunde">Vor welcher Ustd:</label> <select name="nach_stunde"><?php
					for($i=0;$i<15;$i++) {
						echo '<option value="'.$i.'"';
						if($_GET["aufsicht_id"]>0 and $i==$bearbeiten["nach_stunde"]) echo ' selected="selected"';
						echo '>'.($i+1).'.</option>';
					} ?>
						</select><br />
				<label for="bemerkung">Text<em>*</em>:</label> <input type="text" name="bemerkung" size="10" value="<?php if($_GET["aufsicht_id"]>0) echo html_umlaute($bearbeiten["bemerkung"]); ?>" maxlength="50" /><br />
                <?php if ($_GET["aufsicht_id"]>0) echo '<button onclick="document.location.href=\''.$pfad.'formular/aufsicht.php?aufsicht_id='.$bearbeiten["id"].'&amp;eintragen=loeschen\'; return false;" title="diese Aufsicht l&ouml;schen"><img src="'.$pfad.'icons/delete.png" alt="loeschen" /> l&ouml;schen</button>'; ?>
				<button style="float: right;" onclick="auswertung=new Array(new Array(0, 'bemerkung','nicht_leer')); pruefe_formular(auswertung); return false;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
			</fieldset>
			</form>
			</div>
			</body>
		</html>
	<?php
	break;
}
?>
