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
$id=db_conn_and_sql("INSERT INTO `block` (`block_hoeher`, `stunden`, `puffer`, `name`, `methodisch`, `verknuepfung_fach`, `kommentare`, `position`, `lernbereich`, `user`) VALUES
(".leer_NULL($_POST['block_1']).", ".leer_NULL($_POST['stunden']).", ".leer_NULL($_POST['puffer']).", ".apostroph_bei_bedarf($_POST['name']).", ".apostroph_bei_bedarf($_POST['methodisch']).", ".apostroph_bei_bedarf($_POST['verknuepfung_fach']).", ".apostroph_bei_bedarf($_POST['kommentare']).", ".leer_NULL($_POST['position']).", ".leer_NULL($_POST['lernbereich']).", ".$_SESSION['user_id'].");");

 	$thema=0;
	while($_POST["thema_".$thema]!="-") {
		db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (2,".$id.",".injaway($_POST["thema_".$thema]).");");
		$thema++;
	}
	if ($_POST["gleich_weiter"]) {
		header("Location: ../index.php?tab=stundenplanung&auswahl=lernbereiche&lehrplan=".$_POST['lehrplan']."&klasse=".$_POST['klasse']);
		exit;
	}
?>
<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>

