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

if ($_GET["eintragen"]=="true") {
	$id=db_conn_and_sql("INSERT INTO `aufgabe` (`text`, `bemerkung`,`loesung`,`punkte`,`lernbereich`,`bearbeitungszeit`,`schwierigkeitsgrad`,`teilaufgaben_nebeneinander`, `user`) VALUES
	(".apostroph_bei_bedarf($_POST['text']).", ".apostroph_bei_bedarf($_POST['bemerkung']).", ".apostroph_bei_bedarf($_POST['loesung']).", ".leer_NULL($_POST['punkte']).", ".injaway($_POST['lernbereich']).", ".leer_NULL($_POST['bearbeitungszeit']).", ".leer_NULL($_POST['schwierigkeitsgrad']).", ".leer_NULL($_POST['teilaufgaben_nebeneinander']).", ".$_SESSION['user_id'].");");

	$verwendete_themen='';
	$thema=0;
	while($_POST["thema_".$thema]!="-" and $thema<10) {
		$verwendete_themen.=injaway($_POST["thema_".$thema]).';';
		db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (1,".$id.",".injaway($_POST["thema_".$thema]).");");
		$thema++;
	}
	db_conn_and_sql("UPDATE fach_klasse SET letzter_lernbereich=".injaway($_POST['lernbereich']).", letzte_themen_auswahl=".apostroph_bei_bedarf($verwendete_themen)." WHERE id=".sql_result(db_conn_and_sql("SELECT letzte_fachklasse FROM benutzer WHERE benutzer.id=".$_SESSION['user_id']), 0, "benutzer.letzte_fachklasse"));
	
	if ($_POST['art']!="text") {
		db_conn_and_sql("INSERT INTO `buch_aufgabe` (`aufgabe`, `buch`,`seite`,`nummer`) VALUES
			(".$id.", ".injaway($_POST['art']).", ".leer_NULL($_POST['seite']).", ".apostroph_bei_bedarf($_POST['nummer']).");");
		//db_conn_and_sql("UPDATE `buch` SET `letztes_thema`=".$_POST['thema'].", `letzter_lernbereich`= ".$_POST['lernbereich']." WHERE `id`=".$_POST['art']);
	}

	/*$ids = explode(";",$_POST["inhalt_ids"]); array_pop($ids); // ...weil das letzte leer ist
	foreach ($ids as $value) {
		$hilf=explode(":",$value);
		db_conn_and_sql("INSERT INTO `grafik_aufgabe` (`grafik`, `aufgabe`,`groesse`) VALUES
			(".$hilf[0].", ".$id.", ".punkt_statt_komma_zahl($hilf[1]).");");
	}*/
    refresh_files("grafic", "task", $id, $_POST['text'].$_POST['loesung']);
	
	if ($_GET["einzeltyp"]==2) $beispiel=1; else $beispiel=0;
	if ($_GET["abschnitt"]>0 and proofuser("abschnitt", $_GET["abschnitt"]))
		db_conn_and_sql("INSERT INTO `aufgabe_abschnitt` (`aufgabe`, `abschnitt`,`beispiel`) VALUES (".$id.", ".injaway($_GET["abschnitt"]).", ".$beispiel.");");
	if ($_GET["test"]>0 and proofuser("test", $_GET["test"]))
		db_conn_and_sql("INSERT INTO `test_aufgabe` (`test`, `aufgabe`,`position`) VALUES (".$_GET["test"].", ".$id.", ".(sql_num_rows(db_conn_and_sql("SELECT * FROM `test_aufgabe` WHERE `test`=".injaway($_GET["test"])))+1).");");
?>
	<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
<?php
}
else {
	$titelleiste="Aufgabe erstellen";
	include $pfad."header.php"; ?>
	<body>
    <div id="pictureframe" style="display: none; width: 800px; heigth: 600px;"><iframe name="pictureframe" width="790" height="580" src="<?php echo $pfad; ?>lessons/picturelib.php"></iframe></div>
    <div id="fileframe" style="display: none; width: 800px; heigth: 600px;"><iframe name="fileframe" width="790" height="580" src="<?php echo $pfad; ?>lessons/filelib.php"></iframe></div>
	
	<div class="inhalt">
	<fieldset><legend>Aufgabe erstellen</legend>
		<form action="<?php echo $pfad."formular/aufgabe_neu.php?eintragen=true&amp;abschnitt=".$_GET["abschnitt"]."&amp;einzeltyp=".$_GET["einzeltyp"]."&amp;test=".$_GET["test"]; ?>" method="post" accept-charset="ISO-8859-1">
		<?php echo eintragung_aufgabe("neu", $pfad); ?>
		<input type="button" class="button" value="eintragen" onclick="auswertung=new Array(new Array(0, 'thema_0','nicht_leer', '-'), new Array(0, 'lernbereich','nicht_leer', '-')); if (document.getElementById('art').value!='text') auswertung.push(new Array(0, 'seite','nicht_leer')); else auswertung.push(new Array(0, 'text','nicht_leer')); pruefe_formular(auswertung); return false;" />
		</form>
    </fieldset>
	</div>
	</body>
</html> <?php
}
?>
