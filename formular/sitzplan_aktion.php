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
include $pfad."funktionen.php";

if ($_GET["aktion"]=="empfehlen" and proofuser("sitzplan_klasse", $_GET["sitzplan_klasse_id"]) and userrigths("sitzplan_von_kl", $_GET["sitzplan_klasse_id"])) {
	db_conn_and_sql("UPDATE klasse SET `kl_sitzplan`=".injaway($_GET["sitzplan_klasse_id"])." WHERE `id`=".injaway($_GET["klasse_id"]));
	header("Location: ../index.php?tab=klassen&auswahl=".$_GET["klasse_id"]."&option=sitzplan&sitzplan_klasse=".$_GET["sitzplan_klasse_id"]);
	exit;
}

if ($_GET["aktion"]=="ueberschreiben") {
	$klasse_id=db_conn_and_sql("SELECT klasse FROM sitzplan_klasse WHERE id=".injaway($_GET["sitzplan_klasse_id"]));
	$klasse_id=sql_fetch_assoc($klasse_id);
	$klassensitzplan=db_conn_and_sql("SELECT kl_sitzplan FROM klasse WHERE klasse.id=".$klasse_id["klasse"]);
	$klassensitzplan=sql_fetch_assoc($klassensitzplan);
	if ($klassensitzplan["kl_sitzplan"]!=$_GET["sitzplan_klasse_id"])
		$klasse_id["klasse"]=0;
	if (proofuser("sitzplan_klasse", $_GET["sitzplan_klasse_id"]) or userrigths("sitzplan_von_kl", $_GET["sitzplan_klasse_id"])==2) {
		if (isset($_POST["name"]) and isset($_POST["datum"]))
			db_conn_and_sql("UPDATE `sitzplan_klasse` SET `name`=".apostroph_bei_bedarf($_POST["name"]).", `seit`='".datum_punkt_zu_strich($_POST["datum"])."' WHERE `id`=".injaway($_GET["sitzplan_klasse_id"]));
		$i=0;
		db_conn_and_sql("DELETE FROM `sitzplan_platz` WHERE `sitzplan_klasse`=".injaway($_GET["sitzplan_klasse_id"]));
		while ($i<200) {
			if (isset($_GET["platz"][$i])) {
				db_conn_and_sql("INSERT INTO `sitzplan_platz` (`sitzplan_klasse`,`objekt`,`schueler`) VALUES (".injaway($_GET["sitzplan_klasse_id"]).", ".injaway($_GET["platz"][$i]).", ".injaway($_GET["schueler"][$i]).")");
			}
			$i++;
		}
		header("Location: ../index.php?tab=klassen&auswahl=".$_GET["klasse_id"]."&option=sitzplan&sitzplan_klasse=".$_GET["sitzplan_klasse_id"]);
		exit;
	}
	else
		die("Sie haben nicht die erforderlichen Rechte, den Sitzplan zu &uuml;berschreiben.");
}

if ($_GET["aktion"]=="loeschen" and proofuser("sitzplan_klasse", $_GET["sitzplan_klasse_id"])) {
    $deleter=del_array2echo(delete_db_object("sitzplan_klasse", array(injaway($_GET["sitzplan_klasse_id"])), $pfad, false), "sql");
            foreach ($deleter as $del_line)
                db_conn_and_sql($del_line);
	header("Location: ../index.php?tab=klassen&auswahl=".$_GET["klasse_id"]."&option=sitzplan");
	exit;
}

?>
