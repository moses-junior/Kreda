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

$pfad='../';
$titelleiste="Klasse bearbeiten";
include $pfad."header.php";
include $pfad."funktionen.php";

?>
  <body>
	<div class="inhalt">
<?php

if (userrigths("klassendaten",$_GET["id"])<2)
	die("Sie haben nicht die Rechte, die Klasse zu bearbeiten.");

if ($_GET["eintragen"]=="true") {
	db_conn_and_sql("UPDATE `klasse` SET `einschuljahr`=".injaway($_POST['einschulung']).", `endung`=".apostroph_bei_bedarf($_POST['endung']).", `schule`=".injaway($_POST['schule']).", `schulart`=".injaway($_POST['schulart'])." WHERE `id`=".injaway($_GET["id"]));
	?>
    <html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
    <?php
}
else {
	$klasse=db_conn_and_sql("SELECT * FROM `klasse` WHERE `id`=".injaway($_GET["id"])); ?>
	<form action="<?php echo $pfad; ?>formular/klasse_bearbeiten.php?id=<?php echo $_GET["id"]; ?>&amp;eintragen=true" method="post" accept-charset="ISO-8859-1">
		<fieldset><legend>Klasse bearbeiten</legend>
        <p>
            <div class="hinweis">Sie sollten eine Klasse nur dann bearbeiten, wenn sich tats&auml;chlich in dieser Klasse z.B. die Endung oder die Schule ge&auml;ndert hat, oder Sie sich bei der Erstellung verschrieben haben. Wenn Sie sich unsicher sind, legen Sie besser eine <strong>neue</strong> Klasse an.</div>
        </p>
		im Schuljahr <?php echo $aktuelles_jahr.'/'.($aktuelles_jahr+1); ?> Klasse: 
      <select name="einschulung">
        <?php $sel=false; for ($i=1; $i<=13; $i++) { ?>
        <option value="<?php echo ($aktuelles_jahr-$i+1); ?>"<?php if (($aktuelles_jahr-$i+1)==sql_result($klasse,0,"klasse.einschuljahr")) {echo ' selected="selected"'; $sel=true;} ?>><?php echo $i; ?></option>
        <?php }
		if (!$sel) echo '<option value="'.sql_result($klasse,0,"klasse.einschuljahr").'" selected="selected">'.($aktuelles_jahr-sql_result($klasse,0,"klasse.einschuljahr")+1).'</option>';
		?>
      </select>
      Endung: <input type="text" name="endung" size="3" maxlength="8" value="<?php echo sql_result($klasse,0,"klasse.endung"); ?>" />
      Schule: <select name="schule"><?php $schule=db_conn_and_sql("SELECT * FROM schule, schule_user WHERE schule_user.schule=schule.id AND schule_user.user=".$_SESSION['user_id']); for ($i=0;$i<sql_num_rows($schule);$i++) if (sql_result($schule,$i,'schule_user.aktiv') or sql_result($schule,$i,'schule.id')==sql_result($klasse,0,"klasse.schule")) { ?>
        <option value="<?php echo sql_result($schule,$i,'schule.id'); ?>"<?php if (sql_result($schule,$i,'schule.id')==sql_result($klasse,0,"klasse.schule")) echo ' selected="selected"'; ?>><?php echo html_umlaute(sql_result($schule,$i,'schule.kuerzel')); ?></option>
      <?php } ?>
      </select>
	  Schulart: <select name="schulart"><?php $schulart=db_conn_and_sql("SELECT * FROM `schulart`"); for ($i=0;$i<sql_num_rows($schulart);$i++) { ?>
        <option value="<?php echo sql_result($schulart,$i,'schulart.id'); ?>"<?php if (sql_result($schulart,$i,'schulart.id')==sql_result($klasse,0,"klasse.schulart")) echo ' selected="selected"'; ?>><?php echo html_umlaute(sql_result($schulart,$i,'schulart.kuerzel')); ?></option>
      <?php } ?>
      </select><br />
      <?php /*if (sql_num_rows(db_conn_and_sql("SELECT fach_klasse.id FROM fach_klasse WHERE anzeigen=1 AND klasse=".injaway($_GET["id"])))==0) { */ ?>
      <button onclick="document.location.href='<?php echo $pfad; ?>formular/klasse_delete.php?id=<?php echo $_GET["id"]; ?>&amp;option=delete'; return false;" title="kann je nach Anzahl der Eintragungen bzw. Rechnerleistung einige Zeit in Anspruch nehmen"><img src="<?php echo $pfad; ?>icons/delete.png" alt="delete" /> Klasse l&ouml;schen</button>
      <?php /*} else { ?>
      <button onclick="document.location.href='<?php echo $pfad; ?>formular/klasse_delete.php?id=<?php echo $_GET["id"]; ?>&amp;option=deactivate'; return false;"><img src="<?php echo $pfad; ?>icons/plan_weg.png" alt="deactivate" /> Klasse deaktivieren</button>
      <?php }*/ ?>
      <button style="float: right;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> Klassendaten speichern</button>
    </fieldset>
	</form>
<?php } ?>
	</div>
	</body>
</html>
