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
include "../funktionen.php";
$pfad="../";
if($_GET["eintragen"]!="true") {
    $titelleiste="Block / Unterrichtseinheit bearbeiten";
    include $pfad."header.php";
    if (!proofuser("block", $_GET["block"]))
		die("Sie sind hierzu nicht berechtigt.");
    ?>
  <body>
	<div class="inhalt">
	
    <?php
	$result=db_conn_and_sql("SELECT * FROM `block` LEFT JOIN `themenzuordnung` ON (`themenzuordnung`.`typ`=2 AND `themenzuordnung`.`id`=`block`.`id`) WHERE `block`.`id`=".injaway($_GET["block"]));
	?>
	<form action="<?php echo $pfad; ?>formular/block_bearb.php?eintragen=true" method="post" accept-charset="ISO-8859-1">
        <fieldset><legend>Block bearbeiten</legend>
			<input type="hidden" name="block" value="<?php echo $_GET["block"]; ?>" />

			<ol class="divider">
			<li><label for="name">Name<em>*</em>:</label> <input type="text" name="name" size="40" maxlength="250" value="<?php echo html_umlaute(sql_result($result,0,"block.name")); ?>" /></li>
			<li><label for="stunden">Stunden<em>*</em>:</label> <input type="text" name="stunden" size="2" maxlength="2" value="<?php echo sql_result($result,0,"block.stunden"); ?>" />
			<label for="puffer" style="width: auto;">+ Puffer:</label> <input type="text" name="puffer" size="2" maxlength="2" value="<?php echo sql_result($result,0,"block.puffer"); ?>" /></li>
			<li><label for="methodisch">methodisch-didaktische &Uuml;berlegungen:</label> <textarea cols="80" rows="3" name="methodisch"><?php echo html_umlaute(sql_result($result,0,"block.methodisch")); ?></textarea><br />
			<label for="verknuepfung_fach">Verkn&uuml;pfung mit Fach/LB:</label> <input type="text" name="verknuepfung_fach" size="45" maxlength="60" value="<?php echo html_umlaute(sql_result($result,0,"block.verknuepfung_fach")); ?>" /></li>
			<li><label for="kommentare">Kommentare:</label> <textarea name="kommentare" cols="80" rows="5"><?php echo html_umlaute(sql_result($result,0,"block.kommentare")); ?></textarea></li>
			<li><?php $selected_tags='';
			for($thema=0; @sql_result($result,$thema,"themenzuordnung.thema")>0; $thema++)
				$selected_tags[$thema]=sql_result($result,$thema,"themenzuordnung.thema");
			echo themen_auswahl($pfad, 'thema', $selected_tags);
			?></li></ol><br />
			<button style="float: right;" onclick="auswertung=new Array(new Array(0, 'name','nicht_leer'), new Array(0, 'thema_0','nicht_leer','-'), new Array(0, 'stunden','natuerliche_zahl')); pruefe_formular(auswertung);"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
        </fieldset>
        </form>
		</div>
	</body>
</html>        
<?php
}
else {
    if (!proofuser("block", $_POST["block"]))
		die("Sie sind hierzu nicht berechtigt.");
	
	db_conn_and_sql("UPDATE `block`
		SET `name`=".apostroph_bei_bedarf($_POST['name']).", `stunden`=".leer_NULL($_POST['stunden']).", `puffer`=".leer_NULL($_POST['puffer']).",
			`methodisch`=".apostroph_bei_bedarf($_POST['methodisch']).", `verknuepfung_fach`=".apostroph_bei_bedarf($_POST['verknuepfung_fach']).",
			`kommentare`=".apostroph_bei_bedarf($_POST['kommentare'])."
		WHERE `id`=".leer_NULL($_POST["block"]));
	
	db_conn_and_sql("DELETE FROM `themenzuordnung` WHERE `typ`=2 AND `id`=".injaway($_POST["block"]));
	$thema=0;
	while($_POST["thema_".$thema]!="-") {
		db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (2,".injaway($_POST["block"]).",".injaway($_POST["thema_".$thema]).");");
		$thema++;
	}
	?>
    <html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
    <?php
}
?>
