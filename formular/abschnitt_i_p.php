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
$abschnitt=injaway($_GET["abschnitt"]);
if (!proofuser("abschnitt",$abschnitt))
	die("Sie sind hierzu nicht berechtigt.");

if ($_GET["aktion"]=="hoch" or $_GET["aktion"]=="runter")
	db_conn_and_sql("UPDATE `abschnitt` SET `inhaltspositionen`=".apostroph_bei_bedarf($_GET["positionen"])." WHERE `id`=".$abschnitt);

if ($_GET["aktion"]=="entfernen") {
	switch ($_GET["typ"]) {
		case "u": if (proofuser("ueberschrift",$_GET["id"])) db_conn_and_sql("DELETE FROM `ueberschrift` WHERE `id`=".injaway($_GET["id"])); break;
		case "t": db_conn_and_sql("DELETE FROM `test_abschnitt` WHERE `test`=".injaway($_GET["id"])." AND `abschnitt`=".$abschnitt); break;
		case "a": db_conn_and_sql("DELETE FROM `aufgabe_abschnitt` WHERE `aufgabe`=".injaway($_GET["id"])." AND `abschnitt`=".$abschnitt); break;
		case "m": db_conn_and_sql("DELETE FROM `material_abschnitt` WHERE `material`=".injaway($_GET["id"])." AND `abschnitt`=".$abschnitt); break;
		case "l": db_conn_and_sql("DELETE FROM `link_abschnitt` WHERE `link`=".injaway($_GET["id"])." AND `abschnitt`=".$abschnitt); break;
		case "g": db_conn_and_sql("DELETE FROM `grafik_abschnitt` WHERE `grafik`=".injaway($_GET["id"])." AND `abschnitt`=".$abschnitt); break;
		case "s": if (proofuser("sonstiges",$_GET["id"])) db_conn_and_sql("DELETE FROM `sonstiges` WHERE `id`=".injaway($_GET["id"])); break;
	}
}
/*
if ($_GET["plan"]>0) {
	if ($_GET["fk"]>0) {
		header("Location: ../index.php?tab=stundenplanung&auswahl=fkplan&fk=".$_GET["fk"]."&plan=".$_GET["plan"]."&ansicht=planung");
		exit;
	}
	else {
		header("Location: ../formular/nachbereiten.php?plan=".$_GET["plan"]);
		exit;
	}
}
else {
	header("Location: ../index.php?tab=stundenplanung&auswahl=lernbereiche&lehrplan=".$_GET['lehrplan']."&klasse=".$_GET['klasse']."&block=".$_GET['block']."&eintragen=abschnitte");
	exit;
}*/
?>
<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
