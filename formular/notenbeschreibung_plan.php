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
$pfad="../";
if ($_GET["aendern"]!="true") {
	if (!proofuser("notenbeschreibung",$_GET["beschreibung"]))
		die("Sie sind hierzu nicht berechtigt.");
	$beschreibung=db_conn_and_sql("SELECT * FROM `notenbeschreibung` WHERE `id`=".injaway($_GET["beschreibung"]));
	
	$select=false;
	$plan=db_conn_and_sql("SELECT * FROM `plan` WHERE `fach_klasse`=".sql_result($beschreibung,0,"notenbeschreibung.fach_klasse")." AND `schuljahr`=".$aktuelles_jahr." ORDER BY `datum`"); ?>
	<form action="<?php echo $pfad; ?>formular/notenbeschreibung_plan.php?aendern=true" method="post" accept-charset="ISO-8859-1">
	<input type="hidden" name="beschreibung" value="<?php echo $_GET["beschreibung"]; ?>" />
	<select name="plan">
		<?php
		for($i=0;$i<sql_num_rows($plan);$i++) {
			echo '<option value="'.sql_result($plan,$i,"plan.id").'"';
			if (!$select and sql_result($plan,$i,"plan.datum")>=sql_result($beschreibung,0,"notenbeschreibung.datum")) { echo ' selected="selected"'; $select=true; }
			echo '>'.datum_strich_zu_punkt(sql_result($plan,$i,"plan.datum")).'</option>';
		} ?>
	</select>
	<input type="submit" value="eintragen" />
	</form>
	<?php
}
else {
	if ($_GET["plan"]!="") {
		if (!proofuser("notenbeschreibung",$_GET["beschreibung"]))
			die("Sie sind hierzu nicht berechtigt.");
		if ($_GET["plan"]=="loeschen")
			db_conn_and_sql("UPDATE `notenbeschreibung` SET `datum`='".injaway($_GET["datum"])."', `plan`=NULL WHERE `id`=".injaway($_GET["beschreibung"]));
		else {
			if (proofuser("plan",$_GET["plan"]))
				db_conn_and_sql("UPDATE `notenbeschreibung` SET `datum`=NULL, `plan`=".injaway($_GET["plan"])." WHERE `id`=".injaway($_GET["beschreibung"]));
		}
		header("Location: ../index.php?tab=stundenplanung&auswahl=fkplan&fk=".$_GET['fk']);
		exit;
	}
	else {
		if (!proofuser("notenbeschreibung",$_POST["beschreibung"]) or !proofuser("plan",$_POST["plan"]))
			die("Sie sind hierzu nicht berechtigt.");
		db_conn_and_sql("UPDATE `notenbeschreibung` SET `datum`=NULL, `plan`=".injaway($_POST["plan"])." WHERE `id`=".injaway($_POST["beschreibung"])); ?>
		<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
	<?php
	}
}
?>
