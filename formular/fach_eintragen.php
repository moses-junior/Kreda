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
if (userrigths("faecher", $_POST['schule'])!=2)
	die("Sie haben nicht die Rechte, ein Fach dieser Schule einzutragen.");
$eintragen=true;
$faecher=db_conn_and_sql("SELECT faecher.* FROM faecher WHERE faecher.schule=".$_POST["schule"]);
while ($fach=sql_fetch_assoc($faecher))
	if (strcasecmp($_POST["kuerzel"],$fach["kuerzel"])==0 or strcasecmp($_POST["name"],$fach["name"])==0) // falls kuerzel oder name bei der Schule bereits existiert, darf nichts eingetragen werden
		$eintragen=false;
if ($eintragen) {
	$user_typ=db_conn_and_sql("SELECT usertyp FROM schule_user WHERE schule_user.schule=".$_POST["schule"]." AND schule_user.user=".$_SESSION["user_id"]);
	$user_typ=sql_fetch_assoc($user_typ);
	$user_typ=$user_typ["usertyp"];
	if ($user_typ==6) { // einzelnutzer
		$schule=0;
		$user=$_SESSION["user_id"];
	}
	else {
		$schule=$_POST["schule"];
		$user=0;
	}
	db_conn_and_sql("INSERT INTO `faecher` (`name`, `kuerzel`, `anzeigen`, `user`, `schule`) VALUES (".apostroph_bei_bedarf($_POST["name"]).", ".apostroph_bei_bedarf($_POST["kuerzel"]).", 1, ".$user.", ".$schule.");");
	header("Location: ../index.php?tab=einstellungen&auswahl=faecher");
	exit;
}
else
	echo '<div class="hinweis">Das von Ihnen gew&auml;hlte Fach "'.$_POST["name"].'" oder das K&uuml;rzel "'.$_POST["kuerzel"].'" existiert bereits.</div><br />';

?>
