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
$schulart=db_conn_and_sql("SELECT * FROM `schulart`");
$id=db_conn_and_sql("INSERT INTO `schule` (`name`, `kuerzel`, `adresse`, `plz_ort`, `telefon`, `fax`, `schulleiter`, `user`) VALUES
(".apostroph_bei_bedarf($_POST['name']).", ".apostroph_bei_bedarf($_POST['kurz']).", ".apostroph_bei_bedarf($_POST['adresse']).",".apostroph_bei_bedarf($_POST['plz_ort']).",".apostroph_bei_bedarf($_POST['telefon']).",".apostroph_bei_bedarf($_POST['fax']).", NULL, ".$_SESSION['user_id'].");");

db_conn_and_sql("INSERT INTO schule_user (schule, user, aktiv) VALUES (".$id.", ".$_SESSION['user_id'].", 1)");

for ($i=0;$i<sql_num_rows($schulart);$i++)
	if ($_POST["schulart_".$i]=="1")
		db_conn_and_sql("INSERT INTO `schule_schulart` (`schule`,`schulart`) VALUES (".$id.",".sql_result($schulart,$i,"schulart.id").");");
 
header("Location: ../formular/schule_bearbeiten.php?schule=".$id."&jahr=schulen");
exit;
?>
