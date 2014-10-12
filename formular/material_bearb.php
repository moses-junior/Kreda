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

if ($_GET["aendern"]!="true") {
	$titelleiste="Material bearbeiten";
	include $pfad."header.php"; ?>
  <body>
    <div id="pictureframe" style="display: none; width: 800px; heigth: 600px;"><iframe name="pictureframe" width="790" height="580" src="<?php echo $pfad; ?>lessons/picturelib.php"></iframe></div>
    <div id="fileframe" style="display: none; width: 800px; heigth: 600px;"><iframe name="fileframe" width="790" height="580" src="<?php echo $pfad; ?>lessons/filelib.php"></iframe></div>
	<div class="inhalt">
		<?php echo eintragung_material(injaway($_GET["typ"]), $pfad, injaway($_GET["id"])); ?>
	</div>
  </body>
</html><?php
}
else{
$id=$_POST['id'];

switch ($_GET["typ"]) {
	case 1:
		if (proofuser("ueberschrift", $id))
			db_conn_and_sql("UPDATE `ueberschrift`
				SET `text`=".apostroph_bei_bedarf($_POST['text']).", `ebene`=".leer_NULL($_POST['ebene']).", `typ`=".apostroph_bei_bedarf($_POST['typ'])."
				WHERE `id`=".$id);
	break;
	case 6:
		if (proofuser("material", $id)) {
			db_conn_and_sql("UPDATE `material`
				SET `name`=".apostroph_bei_bedarf($_POST['name']).", `beschreibung`=".apostroph_bei_bedarf($_POST['beschreibung']).", `aufbewahrungsort`=".apostroph_bei_bedarf($_POST['aufbewahrungsort'])."
				WHERE `id`=".$id);
			db_conn_and_sql("DELETE FROM `themenzuordnung` WHERE `typ`=6 AND `id`=".$id);
			$thema=0;
			while($_POST["thema_".$thema]!="-" and $thema<10) {
				db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (6,".$id.",".$_POST["thema_".$thema].");");
				$thema++;
			}
		}
	break;
	case 7:
		if (proofuser("sonstiges", $id))
			db_conn_and_sql("UPDATE `sonstiges`
				SET `inhalt`=".apostroph_bei_bedarf($_POST['inhalt'])."
				WHERE `id`=".$id);
			// 30.12.2012 - deprecated
			//refresh_files("both", "text", $id, $_POST['inhalt']);
	break;
}
/*db_conn_and_sql("UPDATE `aufgabe`
SET `text`=".apostroph_bei_bedarf($_POST['text']).", `bemerkung`=".apostroph_bei_bedarf($_POST['bemerkung']).",`loesung`=".apostroph_bei_bedarf($_POST['loesung']).",`punkte`=".leer_NULL($_POST['punkte']).",`thema`=".$_POST['thema'].",`lernbereich`= ".$_POST['lernbereich'].",`bearbeitungszeit`=".leer_NULL($_POST['bearbeitungszeit']).",`kariert`=".leer_NULL($_POST['kariert']).",`cm`=".leer_NULL($_POST['cm']).",`bildanordnung`=".leer_NULL($_POST['bildanordnung']).",`bildbeschriftung`=".leer_NULL($_POST['bildbeschriftung'])."
WHERE `id`=".$id);

db_conn_and_sql("DELETE FROM `buch_aufgabe` WHERE `aufgabe`=".$id);

if ($_POST['art']!="text") db_conn_and_sql("INSERT INTO `buch_aufgabe` (`aufgabe`, `buch`,`seite`,`nummer`) VALUES
(".$id.", ".$_POST['art'].", ".leer_NULL($_POST['seite']).", ".apostroph_bei_bedarf($_POST['nummer']).");");


db_conn_and_sql("DELETE FROM `grafik_aufgabe` WHERE `aufgabe`=".$id);

$ids = explode(";",$_POST["inhalt_ids"]); array_pop($ids); // ...weil das letzte leer ist
foreach ($ids as $value) {
	$hilf=explode(":",$value);
	$einzeln=explode(",",$hilf[1]);
	db_conn_and_sql("INSERT INTO `grafik_aufgabe` (`grafik`, `aufgabe`,`position`,`groesse`) VALUES
(".$hilf[0].", ".$id.", ".leer_NULL($einzeln[1]).", ".leer_NULL($einzeln[0]).");");
}
*/ ?>
<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html><?php
}
?>
