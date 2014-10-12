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
include($pfad."funktionen.php");

if ($_GET["aendern"]!="true") {
	$titelleiste="Aufgabe bearbeiten";
	include $pfad."header.php";
 ?>
  <body>
    <div id="pictureframe" style="display: none; width: 800px; heigth: 600px;"><iframe name="pictureframe" width="790" height="580" src="<?php echo $pfad; ?>lessons/picturelib.php"></iframe></div>
    <div id="fileframe" style="display: none; width: 800px; heigth: 600px;"><iframe name="fileframe" width="790" height="580" src="<?php echo $pfad; ?>lessons/filelib.php"></iframe></div>
	
	<div class="inhalt">
		<fieldset><legend>Aufgabe bearbeiten</legend>
		<form action="<?php echo $pfad; ?>formular/aufgabe_bearb.php?aendern=true" method="post" accept-charset="ISO-8859-1">
		<?php if (!proofuser("aufgabe", $_GET["welche"]))
			die("Sie sind hierzu nicht berechtigt.");
		echo eintragung_aufgabe(injaway($_GET["welche"]), $pfad); ?>
        <p>
        <button onclick="fenster('<?php echo $pfad; ?>formular/aufgabe_delete.php?id=<?php echo $_GET["welche"]; ?>', ''); return false;"><img src="<?php echo $pfad; ?>icons/delete.png" alt="delete" /> l&ouml;schen</button>
		<button style="float: right;" onclick="auswertung=new Array(new Array(0, 'thema_0','nicht_leer', '-'), new Array(0, 'lernbereich','nicht_leer', '-')); if (document.getElementById('art').value!='text') auswertung.push(new Array(0, 'seite','nicht_leer')); else auswertung.push(new Array(0, 'text','nicht_leer')); pruefe_formular(auswertung); return false;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
        </p>
		</form>
		</fieldset>
	</div>
  </body>
</html><?php
}
else{
	$id=injaway($_POST['id']);
	if (!proofuser("aufgabe", $id))
		die("Sie sind hierzu nicht berechtigt.");
	
	db_conn_and_sql("UPDATE `aufgabe`
		SET `text`=".apostroph_bei_bedarf($_POST['text']).", `bemerkung`=".apostroph_bei_bedarf($_POST['bemerkung']).",`loesung`=".apostroph_bei_bedarf($_POST['loesung']).",`punkte`=".leer_NULL(punkt_statt_komma_zahl($_POST['punkte'])).", `lernbereich`=".injaway($_POST['lernbereich']).",`bearbeitungszeit`=".leer_NULL(punkt_statt_komma_zahl($_POST['bearbeitungszeit'])).", `schwierigkeitsgrad`=".leer_NULL($_POST['schwierigkeitsgrad']).", `teilaufgaben_nebeneinander`=".leer_NULL($_POST['teilaufgaben_nebeneinander'])."
		WHERE `id`=".$id);

	db_conn_and_sql("DELETE FROM `themenzuordnung` WHERE `typ`=1 AND `id`=".$id);
	$thema=0;
	while($_POST["thema_".$thema]!="-") {
		db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (1,".$id.",".injaway($_POST["thema_".$thema]).");");
		$thema++;
	}


db_conn_and_sql("DELETE FROM `buch_aufgabe` WHERE `aufgabe`=".$id);

if ($_POST['art']!="text") {
	db_conn_and_sql("INSERT INTO `buch_aufgabe` (`aufgabe`, `buch`,`seite`,`nummer`) VALUES
		(".$id.", ".injaway($_POST['art']).", ".leer_NULL($_POST['seite']).", ".apostroph_bei_bedarf($_POST['nummer']).");");
	//db_conn_and_sql("UPDATE `buch` SET `letztes_thema`=".$_POST['thema_0'].", `letzter_lernbereich`= ".$_POST['lernbereich']." WHERE `id`=".$_POST['art']);
}


// alte Bilder werden gelassen - db_conn_and_sql("DELETE FROM `grafik_aufgabe` WHERE `aufgabe`=".$id);

/*
// neue Bilder hinzufuegen
$ids = explode(";",$_POST["inhalt_ids"]); array_pop($ids); // ...weil das letzte leer ist
foreach ($ids as $value) {
	$hilf=explode(":",$value);
	db_conn_and_sql("INSERT INTO `grafik_aufgabe` (`grafik`, `aufgabe`,`groesse`) VALUES
(".$hilf[0].", ".$id.", ".punkt_statt_komma_zahl($hilf[1]).");");
}*/

	// am 30.12.2012 rausgenommen (deprecated)
    // refresh_files("grafic", "task", $id, $_POST['text'].$_POST['loesung']);
?>
<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
<?php
}
?>
