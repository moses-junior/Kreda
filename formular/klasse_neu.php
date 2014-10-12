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
$klasse=db_conn_and_sql("INSERT INTO `klasse` (`einschuljahr`, `endung`, `schule`, `schulart`) VALUES
(".injaway($_POST['einschulung']).", ".apostroph_bei_bedarf($_POST['endung']).", ".injaway($_POST['schule']).",".injaway($_POST['schulart']).");");

/*if ($_POST["fach_klasse_anlegen"]==1)
	db_conn_and_sql("INSERT INTO `fach_klasse` (`klasse`,`fach`, `anzeigen`, `farbe`, `gruppen_name`, `lehrplan`, `bewertungstabelle`, `info`, `user`) VALUES
		(".$klasse.", ".injaway($_POST['fach_neu']).", ".leer_NULL($_POST["anzeigen_neu"]).", ".apostroph_bei_bedarf($_POST['farbe_neu']).", ".apostroph_bei_bedarf($_POST['gruppen_name_neu']).", ".leer_NULL($_POST['lehrplan_neu']).", ".leer_NULL($_POST["bewertungstabelle"]).", ".apostroph_bei_bedarf($_POST['info_neu']).", ".$_SESSION['user_id'].");");
*/
header("Location: ../index.php?tab=klassen&zweit=alle"); //auswahl=".$klasse."&option=fk");
exit;
?>
