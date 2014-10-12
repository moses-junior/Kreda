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

if (!proofuser("fach_klasse", $_POST["fach_klasse"]))
	die("Sie sind hierzu nicht berechtigt.");

$i=0; while(!isset($_POST["datum_".$i]) and $i<=200) $i++; if($i>=200) $i=0;
while(isset($_POST["datum_".$i])) {
	$hilf=explode(":",$_POST["block_".$i]); // nur vorn wichtig
	$block_id=substr($hilf[0],1);
	if ($block_id=="") $block_id="NULL";
    //echo 'block'.$block_id;
	$ausfall="NULL"; if ($_POST["ausfall_".$i]!="") $ausfall="'".$_POST["ausfall_".$i]."'";
	if ($block_id!="NULL" or $ausfall!="NULL")
        db_conn_and_sql("INSERT INTO `plan` (`datum`,`startzeit`, `schuljahr`, `fach_klasse`, `nachbereitung`, `gedruckt`, `material_da`,`ausfallgrund`,`block_1`,`ustd`) VALUES
('".date("Y-m-d",injaway($_POST['datum_'.$i]))."', ".apostroph_bei_bedarf($_POST['zeit_'.$i]).", ".injaway($_POST['schuljahr']).", ".injaway($_POST['fach_klasse']).", false, false, false,".$ausfall.",".$block_id.",".injaway($_POST['ustd_'.$i]).");");
		/*echo $block_id."_".$ausfall.": "."INSERT INTO `plan` (`datum`,`startzeit`, `schuljahr`, `fach_klasse`, `nachbereitung`, `minuten_verschieben`, `gedruckt`, `material_da`,`ausfallgrund`,`block_1`) VALUES
('".date("Y-m-d",$_POST['datum_'.$i])."', '".$_POST['zeit_'.$i]."', ".$_POST['schuljahr'].", ".$_POST['fach_klasse'].", false, 0, false, false,".$ausfall.",".$block_id.");";*/
	$i++;
}

header("Location: ../index.php?tab=stundenplanung&auswahl=fkplan&fk=".$_POST['fach_klasse']);
exit;
?>
