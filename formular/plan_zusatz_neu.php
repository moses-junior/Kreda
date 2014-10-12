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

$hilf=explode(":",$_POST["block"]); // nur vorn wichtig
$block_id=substr($hilf[0],1);
$fachklasse=$_POST['fachklasse'];

if ($fachklasse=="neu") {
	$bestehende_klasse=db_conn_and_sql("SELECT * FROM `klasse` WHERE `einschuljahr`=".injaway($_POST['einschulung'])." AND `endung`='".injaway($_POST['endung'])."' AND `schule`=".injaway($_POST['schule'])." AND `schulart`=".injaway($_POST['schulart']));
	if(@sql_num_rows($bestehende_klasse)>0)
		$klasse_id=sql_result($bestehende_klasse,0,"klasse.id");
	else {
		$klasse_id=db_conn_and_sql("INSERT INTO `klasse` (`einschuljahr`, `endung`, `schule`, `schulart`) VALUES
			(".$_POST['einschulung'].", ".apostroph_bei_bedarf($_POST['endung']).", ".injaway($_POST['schule']).",".injaway($_POST['schulart']).");");
	}
	$fachklasse=db_conn_and_sql("INSERT INTO `fach_klasse` (`klasse`,`fach`, `anzeigen`, `farbe`, `gruppen_name`, `lehrplan`, `info`, `user`) VALUES
		(".$klasse_id.", ".$_POST['fach_neu'].", 1, 'aaa', NULL, ".leer_NULL($_POST['lehrplan_neu']).", NULL, ".$_SESSION['user_id'].");");
}

if(strlen($_POST['zeit'])>6) $zeit=$_POST['zeit']; else $zeit=$_POST['zeit'].':00';

$id=db_conn_and_sql("INSERT INTO `plan` (`datum`,`startzeit`, `schuljahr`, `fach_klasse`,`block_1`) VALUES
('".datum_punkt_zu_strich($_POST['datum'])."', ".apostroph_bei_bedarf($zeit).", ".leer_NULL($_POST['schuljahr']).", ".injaway($fachklasse).", ".leer_NULL($block_id).");");
/*echo "INSERT INTO `plan` (`datum`,`startzeit`, `schuljahr`, `fach_klasse`,`block_1`) VALUES
('".datum_punkt_zu_strich($_POST['datum'])."', '".$zeit."', ".leer_NULL($_POST['schuljahr']).", ".$fachklasse.", ".leer_NULL($block_id).");";*/
header("Location: ../index.php?tab=stundenplanung&auswahl=fkplan&fk=".$fachklasse."&plan=".$id."&ansicht=planung");
exit;
?>
