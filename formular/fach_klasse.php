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
if ($_GET["aktion"]=="aendern" and proofuser("fach_klasse", $_POST["fach_klasse"])) {
	db_conn_and_sql ("UPDATE `fach_klasse` SET `anzeigen`=".leer_NULL($_POST["anzeigen"]).", `farbe`=".apostroph_bei_bedarf($_POST["farbe"]).", `gruppen_name`=".apostroph_bei_bedarf($_POST["gruppen_name"]).", `lehrplan`=".leer_NULL($_POST["lehrplan"]).", `bewertungstabelle`=".leer_NULL($_POST["bewertungstabelle"]).", `notenberechnungsvorlage`=".leer_NULL($_POST["notenberechnungsvorlage"]).", `sitzplan_klasse`=".leer_NULL($_POST["sitzplan"]).", `klassenanzeige`=".leer_NULL($_POST["klassenanzeige"]).", `info`=".apostroph_bei_bedarf($_POST["info"])." WHERE `id`=".injaway($_POST["fach_klasse"]));
    if (isset($_POST["lfd_nr"])) {
		if ($_POST["lehrauftrag"]==1)
			$la_fk=$_POST["fach_klasse"];
		else
			$la_fk="NULL";
		db_conn_and_sql("UPDATE lehrauftrag SET fach_klasse=".$la_fk." WHERE schuljahr=".$aktuelles_jahr." AND fach=".injaway($_POST["fach"])." AND klasse=".injaway($_GET["klasse"])." AND user=".$_SESSION["user_id"]." AND lfd_nr=".leer_NULL($_POST["lfd_nr"]));
	}
}
if ($_GET["aktion"]=="neu") {
	$id=db_conn_and_sql("INSERT INTO `fach_klasse` (`klasse`,`fach`, `anzeigen`, `farbe`, `gruppen_name`, `lehrplan`, `bewertungstabelle`, `notenberechnungsvorlage`, `klassenanzeige`, `info`, `user`) VALUES
        (".injaway($_GET['klasse']).", ".injaway($_POST['fach_neu']).", ".leer_NULL($_POST["anzeigen_neu"]).", ".apostroph_bei_bedarf($_POST['farbe_neu']).", ".apostroph_bei_bedarf($_POST['gruppen_name_neu']).", ".leer_NULL($_POST['lehrplan_neu']).", ".leer_NULL($_POST["bewertungstabelle_neu"]).", ".leer_NULL($_POST["notenberechnungsvorlage_neu"]).", ".($_POST["klassenanzeige_neu"]+0).", ".apostroph_bei_bedarf($_POST['info_neu']).", ".$_SESSION['user_id'].");");
    if (isset($_POST["lehrauftrag_lfd_nr"])) {
		if ($_POST["lehrauftrag_lfd_nr"]=="-")
			$_POST["lehrauftrag_lfd_nr"]="";
		db_conn_and_sql("UPDATE lehrauftrag SET fach_klasse=".$id." WHERE schuljahr=".$aktuelles_jahr." AND fach=".injaway($_POST["fach_neu"])." AND klasse=".injaway($_GET["klasse"])." AND user=".$_SESSION["user_id"]." AND lfd_nr=".leer_NULL($_POST["lehrauftrag_lfd_nr"]));
	}
}
header("Location: ../index.php?tab=klassen&auswahl=".$_GET['klasse']."&option=fk");
exit;
?>
