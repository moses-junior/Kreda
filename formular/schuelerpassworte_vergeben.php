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
$pfad='../';
include $pfad."funktionen.php";

$user=new user();
$schule=$user->my["letzte_schule"];

if ($_GET["eintragen"]=="true") {
	if($_POST["alle"]==1)
		$schueler_result=db_conn_and_sql("SELECT *, schueler.id AS s_id FROM klasse, schueler
			WHERE klasse.schule=".$schule."
				AND schueler.aktiv=1
				AND schueler.klasse=klasse.id");
	else
		$schueler_result=db_conn_and_sql("SELECT *, schueler.id AS s_id FROM klasse, schueler
			WHERE klasse.schule=".$schule."
				AND schueler.aktiv=1
				AND schueler.klasse=klasse.id
				AND schueler.passwort IS NULL");
	if (sql_num_rows($schueler_result)>0)
		while($schueler=sql_fetch_assoc($schueler_result)) {
			$passwd=randomPassword();
			db_conn_and_sql("UPDATE schueler SET passwort='".md5($passwd)."' WHERE id=".$schueler["s_id"]);
			echo '"'.$schueler["vorname"].' '.$schueler["name"].'";"'.($aktuelles_jahr-$schueler["einschuljahr"]+1).' '.$schueler["endung"].'";"'.$schueler["strasse"].'";"'.$schueler["plz"].' '.$schueler["ort"].'";"'.usernameOfPupil($schueler["name"], $schueler["vorname"], $schueler["number"], $schueler["username"]).'";"'.$passwd.'"'."\n";
		}
	else
		echo 'Keine betreffenden Sch&uuml;ler gefunden.';
}
else {
	$titelleiste="Sch&uuml;lerpassw&ouml;rter erstellen";
	include $pfad."header.php"; ?>
	<body>
	<div class="inhalt">
	<fieldset><legend>Sch&uuml;lerpassw&ouml;rter erstellen</legend>
		<form action="<?php echo $pfad."formular/schuelerpassworte_vergeben.php?eintragen=true"; ?>" method="post" accept-charset="ISO-8859-1">
			<p>Hier werden alle Sch&uuml;lerzugangs-Passw&ouml;rter neu generiert. Wenn Sie das H&auml;kchen setzen, werden bereits vergebene Passw&ouml;rter &uuml;berschrieben.</p>
			<p>Das Ergebnis wird im CSV-Format ausgegeben und muss als solche Datei (z.B.:"schuelerpassworte.csv" - nicht: ".html") gespeichert werden. Diese sollte in einen Serienbrief importiert werden (Zeichensatz ist Westeurop&auml;isch (ISO-8859-1) und der Zeichentrenner ein Semikolon (;).</p>
			<div class="hinweis"><p>F&uuml;hren Sie diese Aktion nur dann durch, wenn Sie genau wissen, was Sie tun.
				Diese Aktion ist nicht reversibel.
				Im schlimmsten Fall m&uuml;ssen Sie alle Passw&ouml;rter nochmals vergeben und somit ALLE Eltern dar&uuml;ber informieren.
				Speichern Sie also unbedingt die Ausgabe nach Bet&auml;tigung der Schaltfl&auml;che und sehen Sie nach, ob die Datei korrekt gespeichert wurde und schlie&szlig;en Sie erst DANACH dieses Fenster.</p>
				<p>Nat&uuml;rlich k&ouml;nnen Sie nachtr&auml;glich einzelne Passw&ouml;rter &auml;ndern. Wenn aber die Aktion durchgef&uuml;hrt wird, werden f&uuml;r alle Sch&uuml;ler ohne Passwort neue Passw&ouml;rter generiert und in der Datenbank gespeichert. Diese Aktion kann nicht wiederholt werden, weil dann bereits alle Sch&uuml;ler ein Passwort haben. Danach ist es also nur noch m&ouml;glich, alle Passw&ouml;rter zu &uuml;berschreiben.</p>
			</div>
			<label for="alle" style="width: auto;">bestehende &uuml;berschreiben:</label> <input type="checkbox" name="alle" value="1" /><br />
			<input type="button" class="button" value="Passw&ouml;rter generieren" onclick="if(!confirm('Sind Sie sicher, dass Sie die Passw&ouml;rter der gew&auml;hlten Sch&uuml;ler neu vergeben m&ouml;chten?')) return false; else submit();" />
		</form>
    </fieldset>
	</div>
	</body>
</html> <?php
}
?>
