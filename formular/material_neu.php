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
$id=db_conn_and_sql("INSERT INTO `material` (`name`, `beschreibung`, `aufbewahrungsort`, `user`) VALUES
(".apostroph_bei_bedarf($_POST['material_name']).", ".apostroph_bei_bedarf($_POST['material_beschreibung']).", ".apostroph_bei_bedarf($_POST['material_aufbewahrungsort']).", ".$_SESSION['user_id'].");");

db_conn_and_sql("DELETE FROM `themenzuordnung` WHERE `typ`=6 AND `id`=".$id);
$thema=0;
while($_POST["material_thema_".$thema]!="-" and $thema<10) {
	db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (6,".$id.",".injaway($_POST["material_thema_".$thema]).");");
	$thema++;
}

header("Location: ../index.php?tab=material&auswahl=sonstiges");
exit;
?>
