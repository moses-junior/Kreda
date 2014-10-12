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
$titelleiste="Lernbereich bearbeiten";
include $pfad."header.php";
include "../funktionen.php";
if($_GET["eintragen"]!="true") {
	if (!proofuser("lernbereich", $_GET["lb"]))
		die("Sie sind hierzu nicht berechtigt.");
	
	$result=db_conn_and_sql("SELECT * FROM `lernbereich` WHERE `lernbereich`.`id`=".injaway($_GET["lb"]));
	?>
  <body>
	<div class="inhalt">
		<form action="<?php echo $pfad; ?>formular/lernbereich_bearbeiten.php?eintragen=true" method="post" accept-charset="ISO-8859-1">
			<fieldset><legend>Lernbereich <?php if ($_GET["lb"]>0) echo '&auml;ndern'; else echo 'hinzuf&uuml;gen'; ?></legend>
			<input type="hidden" name="lernbereich" value="<?php echo $_GET["lb"]; ?>" />
			<!--<input type="hidden" name="lehrplan" value="<?php echo $_GET["lehrplan"]; ?>" />
			<input type="hidden" name="klassenstufe" value="<?php echo $_GET["klasse"]; ?>" />
			<input type="hidden" name="nummer" value="<?php echo sql_num_rows ( $result )+1; ?>" />-->
			<label for="wahl">Wahlpflicht:</label> <input type="checkbox" name="wahl" value="1"<?php if(sql_result($result, 0, "lernbereich.wahl")==true) echo ' checked="checked"'; ?> />
			<br /><label for="name">Name<em>*</em>:</label> <input type="text" name="name" size="40" maxlength="70" value="<?php echo html_umlaute(sql_result($result, 0, "lernbereich.name")); ?>" />
			<br /><label for="ustd">Ustd<em>*</em>:</label> <input type="text" name="ustd" size="2" maxlength="2" value="<?php echo html_umlaute(sql_result($result, 0, "lernbereich.ustd")); ?>" /><br />
			<label for="beschreibung">Beschreibung:</label> <textarea name="beschreibung" cols="70" rows="8"><?php echo html_umlaute(sql_result($result, 0, "lernbereich.beschreibung")); ?></textarea><br />
			<button style="float: right" onclick="auswertung=new Array(new Array(0, 'name','nicht_leer'), new Array(0, 'ustd','natuerliche_zahl')); pruefe_formular(auswertung);"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
			</fieldset>
		</form>
	</div>
		</body>
	</html>

<?php
}
else {
	if (proofuser("lernbereich", $_POST["lernbereich"]))
		db_conn_and_sql("UPDATE `lernbereich`
			SET `name`=".apostroph_bei_bedarf($_POST['name']).", `ustd`=".leer_NULL($_POST['ustd']).",
				`beschreibung`=".apostroph_bei_bedarf($_POST['beschreibung']).", `wahl`=".leer_NULL($_POST["wahl"])."
			WHERE `id`=".leer_NULL($_POST["lernbereich"]));
	?>
    <html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
    <?php
}
?>
