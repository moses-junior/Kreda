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
if ($_POST["test_lokal"]=="erstellen") {
	$id=db_conn_and_sql("INSERT INTO `test` (`notentyp`, `url`, `lernbereich`, `bemerkung`,`vorspann`, `user`) VALUES
		(".$_POST['test_notentyp'].", null, ".leer_NULL($_POST['test_lernbereich']).", null, ".apostroph_bei_bedarf($_POST['test_vorspann']).", ".$_SESSION['user_id'].");");

}
else {
$tempname = $_FILES['test_datei']['tmp_name'];
$name = $_FILES['test_datei']['name'];

if(empty($_FILES['test_datei']['name']))
	$err[] = "Eine Datei muss ausgew&auml;hlt werden";

if(empty($err)) {
	$dateiname=pfad_und_dateiname($_POST["test_lernbereich"],'test',$name,$tempname);
	
	$id=db_conn_and_sql("INSERT INTO `test` (`notentyp`, `url`, `lernbereich`, `bemerkung`,`vorspann`, `user`) VALUES
	(".$_POST['test_notentyp'].", ".apostroph_bei_bedarf($dateiname["datei"]).", ".leer_NULL($_POST['test_lernbereich']).", null, ".apostroph_bei_bedarf($_POST['test_vorspann']).", ".$_SESSION['user_id'].");");

}
}

for ($i=0;$i<3;$i++)
	if ($_POST["test_thema_".$i]!="-" and $id>0)
		db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`, `id`, `thema`) VALUES
			(5, ".$id.", ".$_POST["test_thema_".$i].");");

header("Location: ../index.php?tab=material&auswahl=test&welcher=".$id);
exit;
?>
