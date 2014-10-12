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
include($pfad."funktionen.php");

if ($_GET["aendern"]=="true" and proofuser("abschnitt",$_POST["id"])) {
	$id=injaway($_POST['id']);
	
	db_conn_and_sql("UPDATE `abschnitt`
		SET `hefter`=".leer_NULL($_POST['hefter_1']).", `medium`=".injaway($_POST['medium_1']).",`ziel`=".apostroph_bei_bedarf($_POST['ziel_1']).",`minuten`=".injaway($_POST['minuten_1']).",`nachbereitung`=".apostroph_bei_bedarf($_POST['bemerkung_1']).", `sozialform`=".injaway($_POST['sozialform_1']).", `handlungsmuster`=".leer_NULL($_POST['handlungsmuster_1']).", `methode`=".leer_NULL($_POST['method_1'])."
		WHERE `id`=".$id);
	
	$neuer_block='';
	$neue_position=$_POST['position_1'];
	if ($_POST["block_neu"]>0) {
		$neuer_block=', `block`='.leer_NULL($_POST["block_neu"]);
		$neue_position=sql_num_rows(db_conn_and_sql("SELECT block_abschnitt.abschnitt FROM block_abschnitt WHERE block_abschnitt.block=".leer_NULL($_POST["block_neu"])));
	}
	db_conn_and_sql("UPDATE `block_abschnitt` SET `position`=".$neue_position.$neuer_block." WHERE `abschnitt`=".$id." AND `block`=".leer_NULL($_POST["block"]));
	
	?>
	<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
<?php
}
else {
	if (!proofuser("abschnitt", $_GET["welcher"]))
		die("Sie sind hierzu nicht berechtigt.");
	$titelleiste="Abschnitt bearbeiten";
	include $pfad."header.php";
	echo '<body><div class="inhalt">';
	$id_abschnitt=injaway($_GET['welcher']);
	/*if ($_GET['welcher']=="neu") {
		db_conn_and_sql("INSERT INTO `abschnitt` (`inhaltstyp`, `hefter`, `medium`,`ziel`,`minuten`,`nachbereitung`,`sozialform`) VALUES (0, 2, 1, NULL, 10, NULL, 1);");
		$id_abschnitt=sql_insert_id();
		
		db_conn_and_sql("INSERT INTO `block_abschnitt` (`position`,`abschnitt`,`block`) VALUES (".(sql_num_rows(db_conn_and_sql("SELECT * FROM `block_abschnitt` WHERE `block`=".$_GET["block"]))).", ".$id_abschnitt.", ".$_GET['block'].");");
		if ($_GET["plan"]>0) db_conn_and_sql("INSERT INTO `abschnittsplanung` (`abschnitt`, `plan`, `minuten`, `position`) VALUES (".$id_abschnitt.", ".$_GET["plan"].", ".leer_NULL($_GET["minuten"]).", ".$position.");");
	}*/
	$abschnitt=$db->abschnitt($id_abschnitt);
	echo eintragung_abschnitt($abschnitt["id"],$abschnitt["block"],$abschnitt["lehrplan"],$abschnitt["klassenstufe"],$abschnitt["position"]); ?>
	</div>
  </body>
</html><?php
}
?>
