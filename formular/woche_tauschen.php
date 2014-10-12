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

if (isset($_GET["schuljahr"]) and isset($_GET["datum"]) and isset($_GET["schule"]) and userrigths("ab-wochentausch",injaway($_GET["schule"]))) {
	if (sql_num_rows(db_conn_and_sql("SELECT * FROM woche_ab WHERE schuljahr=".injaway($_GET["schuljahr"])." AND schule=".injaway($_GET["schule"])." AND datum=".apostroph_bei_bedarf($_GET["datum"])))>0)
		db_conn_and_sql("DELETE FROM woche_ab WHERE schuljahr=".injaway($_GET["schuljahr"])." AND schule=".injaway($_GET["schule"])." AND datum=".apostroph_bei_bedarf($_GET["datum"]));
	else
		db_conn_and_sql("INSERT INTO woche_ab (schuljahr, datum, user, schule) VALUES (".injaway($_GET['schuljahr']).", ".apostroph_bei_bedarf($_GET['datum']).", ".$_SESSION['user_id'].", ".injaway($_GET["schule"]).");");
	header("Location: ../index.php?tab=stundenplan&auswahl=kalender");
	exit;
}
else
	echo "Keine Berechtigung.";
?>
