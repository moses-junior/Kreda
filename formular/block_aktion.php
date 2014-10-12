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
$id=injaway($_GET["id"]);
$ziel=injaway($_GET["ziel"]);
switch ($_GET["aktion"]) {
	case "lb_tausch":
		if (!proofuser("lernbereich", $id) or !proofuser("lernbereich", $ziel))
			die("Sie sind hierzu nicht berechtigt.");
		$result_1=db_conn_and_sql("SELECT * FROM `lernbereich` WHERE `id`=".$id);
		$result_2=db_conn_and_sql("SELECT * FROM `lernbereich` WHERE `id`=".$ziel);
		db_conn_and_sql("UPDATE `lernbereich` SET `nummer`='".sql_result($result_1,0,"lernbereich.nummer")."' WHERE `id`=".$ziel);
		db_conn_and_sql("UPDATE `lernbereich` SET `nummer`='".sql_result($result_2,0,"lernbereich.nummer")."' WHERE `id`=".$id);
	break;
	case "block_tausch":
		if (!proofuser("block", $id) or !proofuser("block", $ziel))
			die("Sie sind hierzu nicht berechtigt.");
		$result_1=db_conn_and_sql("SELECT * FROM `block` WHERE `id`=".$id);
		$result_2=db_conn_and_sql("SELECT * FROM `block` WHERE `id`=".$ziel);
		db_conn_and_sql("UPDATE `block` SET `position`=".sql_result($result_1,0,"block.position")." WHERE `id`=".$ziel);
		db_conn_and_sql("UPDATE `block` SET `position`=".sql_result($result_2,0,"block.position")." WHERE `id`=".$id);
	break;
}
 
header("Location: ../index.php?tab=stundenplanung&auswahl=lernbereiche&lehrplan=".$_GET['lehrplan']."&klasse=".$_GET['klasse']);
exit;
?>
