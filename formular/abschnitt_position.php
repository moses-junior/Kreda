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
$id=$_GET["id"];

if ($_GET["aktion"]=="loeschen" and proofuser("abschnitt", $_GET["abschnitt"])) {
    if ($_GET["plan"]>0)
        db_conn_and_sql("DELETE FROM abschnittsplanung WHERE abschnitt=".injaway($_GET["abschnitt"]));
	
    // ist der gleiche abschnitt in einem weiteren Block vorhanden, wird der abschnitt nicht geloescht
    if (sql_num_rows(db_conn_and_sql("SELECT * FROM block_abschnitt WHERE abschnitt=".injaway($_GET["abschnitt"])))<=1) {
		db_conn_and_sql("DELETE FROM `block_abschnitt` WHERE `abschnitt`=".injaway($_GET["abschnitt"]));
		db_conn_and_sql("DELETE FROM `abschnitt` WHERE `abschnitt`.`id`=".injaway($_GET["abschnitt"]));
		//db_conn_and_sql("DELETE FROM link_abschnitt WHERE abschnitt=".$_GET["abschnitt"]);
		db_conn_and_sql("DELETE FROM aufgabe_abschnitt WHERE abschnitt=".injaway($_GET["abschnitt"]));
		//db_conn_and_sql("DELETE FROM grafik_abschnitt WHERE abschnitt=".$_GET["abschnitt"]);
		db_conn_and_sql("DELETE FROM material_abschnitt WHERE abschnitt=".injaway($_GET["abschnitt"]));
		db_conn_and_sql("DELETE FROM test_abschnitt WHERE abschnitt=".injaway($_GET["abschnitt"]));
		db_conn_and_sql("DELETE FROM sonstiges WHERE abschnitt=".injaway($_GET["abschnitt"]));
		db_conn_and_sql("DELETE FROM ueberschrift WHERE abschnitt=".injaway($_GET["abschnitt"]));
	}
	else // funktioniert nur aus der block-uebersicht heraus oder wenn im plan (bearbeiten) der zugeordnete block zutrifft
		if ($_GET["block"]>0)
			db_conn_and_sql("DELETE FROM `block_abschnitt` WHERE `abschnitt`=".injaway($_GET["abschnitt"])." AND `block`=".injaway($_GET["block"]));
}

if (proofuser("block", $_GET["block"]))
	$abschnitte=db_conn_and_sql("SELECT * FROM `block_abschnitt` WHERE `block_abschnitt`.`block`=".injaway($_GET["block"])." ORDER BY `block_abschnitt`.`position`");
else
	die("Sie sind hierzu nicht berechtigt.");

// fortlaufende Positionsnummern:
for ($i=0;$i<sql_num_rows($abschnitte);$i++)
	if (sql_result($abschnitte,$i,'block_abschnitt.position')!=$i) db_conn_and_sql("UPDATE `block_abschnitt` SET `position`=".$i." WHERE `abschnitt`=".sql_result($abschnitte,$i,'block_abschnitt.abschnitt'));

if ($_GET["aktion"]=="hoch" or $_GET["aktion"]=="runter")
	for ($i=0;$i<sql_num_rows($abschnitte);$i++) {
		if ($_GET["aktion"]=="hoch" and sql_result($abschnitte,$i,'block_abschnitt.position')==$_GET["pos"]) {
			db_conn_and_sql("UPDATE `block_abschnitt` SET `position`=".($i-1)." WHERE `block`=".$_GET["block"]." AND `abschnitt`=".sql_result($abschnitte,$i,'block_abschnitt.abschnitt'));
			db_conn_and_sql("UPDATE `block_abschnitt` SET `position`=".($i)." WHERE `block`=".$_GET["block"]." AND `abschnitt`=".sql_result($abschnitte,($i-1),'block_abschnitt.abschnitt'));
		}
		if ($_GET["aktion"]=="runter" and sql_result($abschnitte,$i,'block_abschnitt.position')==$_GET["pos"]) {
			db_conn_and_sql("UPDATE `block_abschnitt` SET `position`=".($i+1)." WHERE `block`=".$_GET["block"]." AND `abschnitt`=".sql_result($abschnitte,$i,'block_abschnitt.abschnitt'));
			db_conn_and_sql("UPDATE `block_abschnitt` SET `position`=".($i)." WHERE `block`=".$_GET["block"]." AND `abschnitt`=".sql_result($abschnitte,($i+1),'block_abschnitt.abschnitt'));
		}
	}

if ($_GET["aktion"]=="hoch") $hilf=-1;
if ($_GET["aktion"]=="runter") $hilf=1;

if ($_GET["plan"]>0) {
    header("Location: ../formular/plan_position.php?id=0&plan=".$_GET["plan"]."&fach_klasse=".$_GET["fk"]);
}
else
    header("Location: ../index.php?tab=stundenplanung&auswahl=lernbereiche&lehrplan=".$_GET['lehrplan']."&klasse=".$_GET['klasse']."&block=".$_GET['block']."&eintragen=abschnitte#abschnitt_anker_".($_GET["pos"]+$hilf));
exit;
?>
