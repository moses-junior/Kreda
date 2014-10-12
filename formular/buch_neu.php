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
$id=db_conn_and_sql("INSERT INTO `buch` (`name`, `kuerzel`, `fach`, `schulart`, `isbn`, `untertitel`,`verlag`, `user`) VALUES
(".apostroph_bei_bedarf($_POST['name']).", ".apostroph_bei_bedarf($_POST['kuerzel']).", ".injaway($_POST['fach']).", ".injaway($_POST['schulart']).", ".apostroph_bei_bedarf($_POST['isbn']).", ".apostroph_bei_bedarf($_POST['untertitel']).", ".apostroph_bei_bedarf($_POST['verlag']).", ".$_SESSION['user_id'].");");

foreach ($_POST["klassenstufe"] as $i)
	db_conn_and_sql("INSERT INTO `buch_klassenstufe` (`buch`, `klassenstufe`, `verwenden`) VALUES
		(".$id.", ".$i.", ".(1).");");
 
header("Location: ../index.php?tab=material&auswahl=buch");
exit;
?>
