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

if (!proofuser("plan",$_GET["plan"]))
	die("Sie sind hierzu nicht berechtigt.");

if ($_GET["aktion"]=="loeschen" and proofuser("plan",$_GET["plan"])) {
    db_conn_and_sql("DELETE FROM `abschnittsplanung` WHERE `plan`=".injaway($_GET["plan"])." AND `abschnitt`=".injaway($_GET["abschnitt"])." AND `position`=".injaway($_GET["position"]));
}

$stundenabschnitte=db_conn_and_sql("SELECT * FROM `abschnittsplanung` WHERE `abschnittsplanung`.`plan`=".injaway($_GET["plan"])." ORDER BY `abschnittsplanung`.`position`");

// fortlaufende Positionsnummern:
for ($i=0;$i<sql_num_rows($stundenabschnitte);$i++)
    db_conn_and_sql("UPDATE `abschnittsplanung` SET `position`=".$i." WHERE `plan`=".injaway($_GET["plan"])." AND `abschnitt`=".sql_result($stundenabschnitte,$i,'abschnittsplanung.abschnitt')." AND position=".sql_result($stundenabschnitte,$i,'abschnittsplanung.position'));

if ($_GET["aktion"]=="hoch" or $_GET["aktion"]=="runter")
	for ($i=0;$i<sql_num_rows($stundenabschnitte);$i++) {
		if ($_GET["aktion"]=="hoch" and sql_result($stundenabschnitte,$i,'abschnittsplanung.position')==$_GET["id"]) {
			db_conn_and_sql("UPDATE `abschnittsplanung` SET `position`=99 WHERE `plan`=".injaway($_GET["plan"])." AND `abschnitt`=".sql_result($stundenabschnitte,($i-1),'abschnittsplanung.abschnitt')." AND position=".sql_result($stundenabschnitte,($i-1),'abschnittsplanung.position'));
			db_conn_and_sql("UPDATE `abschnittsplanung` SET `position`=".($i-1)." WHERE `plan`=".injaway($_GET["plan"])." AND `abschnitt`=".sql_result($stundenabschnitte,$i,'abschnittsplanung.abschnitt')." AND position=".sql_result($stundenabschnitte,$i,'abschnittsplanung.position'));
			db_conn_and_sql("UPDATE `abschnittsplanung` SET `position`=".($i)." WHERE `plan`=".injaway($_GET["plan"])." AND `abschnitt`=".sql_result($stundenabschnitte,($i-1),'abschnittsplanung.abschnitt')." AND position=99");
		}
		if ($_GET["aktion"]=="runter" and sql_result($stundenabschnitte,$i,'abschnittsplanung.position')==$_GET["id"]) {
			db_conn_and_sql("UPDATE `abschnittsplanung` SET `position`=99 WHERE `plan`=".injaway($_GET["plan"])." AND `abschnitt`=".sql_result($stundenabschnitte,($i+1),'abschnittsplanung.abschnitt')." AND position=".sql_result($stundenabschnitte,($i+1),'abschnittsplanung.position'));
			db_conn_and_sql("UPDATE `abschnittsplanung` SET `position`=".($i+1)." WHERE `plan`=".injaway($_GET["plan"])." AND `abschnitt`=".sql_result($stundenabschnitte,$i,'abschnittsplanung.abschnitt')." AND position=".sql_result($stundenabschnitte,$i,'abschnittsplanung.position'));
			db_conn_and_sql("UPDATE `abschnittsplanung` SET `position`=".($i)." WHERE `plan`=".injaway($_GET["plan"])." AND `abschnitt`=".sql_result($stundenabschnitte,($i+1),'abschnittsplanung.abschnitt')." AND position=99");
		}
	}

header("Location: ../index.php?tab=stundenplanung&auswahl=fkplan&fk=".$_GET['fach_klasse']."&plan=".$_GET['plan']."&ansicht=planung");
exit;
?>
