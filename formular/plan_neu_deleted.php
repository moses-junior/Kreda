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
// Ich denke, die Datei wird nicht mehr benoetigt, oder?
include "../funktionen.php";

if (!proofuser("fach_klasse",$_POST["fach_klasse"]))
	die("Sie sind hierzu nicht berechtigt.");

$id=db_conn_and_sql("INSERT INTO `plan` (`datum`,`startzeit`, `schuljahr`, `fach_klasse`, `nachbereitung`, `bemerkung`, `zusatzziele`, `struktur`, `minuten_verschieben`, `eingangsbemerkungen`, `schlussbemerkungen`, `gedruckt`, `material_da`,`block_1`,`block_2`,`ustd`) VALUES
('".datum_punkt_zu_strich($_POST['datum'])."', ".apostroph_bei_bedarf($_POST['zeit'].":00").", ".leer_NULL($_POST['schuljahr']).", ".injaway($_POST['fach_klasse']).", false, ".apostroph_bei_bedarf($_POST['bemerkung']).", ".apostroph_bei_bedarf($_POST['zusatzziele']).", ".apostroph_bei_bedarf($_POST['struktur']).", ".leer_NULL($_POST['zeit_start']).", ".apostroph_bei_bedarf($_POST['einleitung']).", ".apostroph_bei_bedarf($_POST['schluss']).", false, false,".injaway($_POST["block1"]).",".leer_NULL($_POST["block2"]).",".injaway($_POST["stunden"]).");");


$ids = explode(";",$_POST["inhalt"]); array_pop($ids); // ...weil das letzte leer ist

for ($i=0;$i<count($ids);$i++) {
	$hilf=explode(":",$ids[$i]);
	db_conn_and_sql("INSERT INTO `abschnittsplanung` (`abschnitt`, `plan`, `minuten`, `position`) VALUES
(".$hilf[0].", ".$id.", ".leer_NULL($hilf[1]).", ".$i.");");
}

header("Location: ../index.php?tab=stundenplanung&auswahl=fkplan&fk=".$_POST['fach_klasse']."&plan=".$id."&ansicht=normal");
exit;
?>
