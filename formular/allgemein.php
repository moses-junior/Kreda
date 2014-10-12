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
//db_conn_and_sql("UPDATE `benutzer` SET `aktuelles_schuljahr`=".apostroph_bei_bedarf($_POST["schuljahr"])." WHERE `id`=".$_SESSION['user_id']);
if (userrigths("feste_feiertage", $_POST['schule'])!=2)
	die("Sie haben nicht die Rechte, Schuljahresdaten zu editieren.");

$result = db_conn_and_sql ( 'SELECT * FROM `feste_feiertage`' );
for ($i=0;$i<sql_num_rows ( $result ); $i++) {
	db_conn_and_sql("UPDATE `feiertage_schule` SET `aktiv`=".leer_NULL($_POST["feiertag_".($i+1)])." WHERE `ff`=".($i+1)." AND schule=".$_POST['schule']);
	if (sql_num_rows(db_conn_and_sql("SELECT * FROM feiertage_schule WHERE ff=".($i+1)." AND schule=".$_POST['schule']))<1)
		db_conn_and_sql("INSERT INTO `feiertage_schule` (ff, schule, aktiv) VALUES (".($i+1).", ".$_POST['schule'].", ".leer_NULL($_POST["feiertag_".($i+1)]).")");
}

header("Location: ../index.php?tab=einstellungen&auswahl=schuljahr&jahr=allgemein&schule=".$_POST['schule']);
exit;
?>
