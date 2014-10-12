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
// wird das ueberhaupt noch verwendet?

$pfad="../";
include($pfad."funktionen.php");

if (!proofuser("fach_klasse",$_POST["fach_klasse"]))
	die("Sie sind hierzu nicht berechtigt.");

if ($_POST["kommentar"]=="(Kommentar)")
	$_POST["kommentar"]="";
db_conn_and_sql("INSERT INTO `notenbeschreibung` (`beschreibung`, `notentyp`, `halbjahresnote`,`fach_klasse`,`datum`,`kommentar`,`bewertungstabelle`)
VALUES (".apostroph_bei_bedarf($_POST["beschreibung"]).", ".injaway($_POST["notentyp"]).", ".($_POST["halbjahresnote"]+0).", ".injaway($_POST["fach_klasse"]).", '".datum_punkt_zu_strich($_POST["datum"])."', ".apostroph_bei_bedarf($_POST["kommentar"]).", ".leer_NULL($_POST["bewertungstabelle"]).");");
header("Location: ../index.php?tab=noten&auswahl=".$_POST['fach_klasse']."&eintragen=true");
exit;
?>
