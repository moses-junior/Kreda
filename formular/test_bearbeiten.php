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
include $pfad."funktionen.php";
switch ($_GET["aktion"]) {
	case "aufgabe_aus_test_entfernen":
		if (proofuser("test", $_GET["test"]) and proofuser("aufgabe", $_GET["aufgabe"]))
			db_conn_and_sql("DELETE FROM `test_aufgabe` WHERE `test`=".injaway($_GET["test"])." AND `aufgabe`=".injaway($_GET["aufgabe"]));
		header("Location: ../index.php?tab=material&auswahl=test&welcher=".$_GET["test"]);
		exit;
		break;
	case "eintragen":
		// ist der Test nicht neu, wird geprueft, ob der Nutzer diesen bearbeiten darf, ansonsten wird gestoppt
		if ($_POST["test"]!="neu")
			if (!proofuser("test",$_POST["test"]))
				die("Sie sind hierzu nicht berechtigt.");
	
		// Test-Datei wird gegebenenfalls aktualisiert
		$tempname = $_FILES['test_datei']['tmp_name'];
		$name = $_FILES['test_datei']['name'];
	
		if(!empty($_FILES['test_datei']['name'])) {
			//altes loeschen
			$db=new db;
			$testarray=$db->test(injaway($_POST["test"]));
			unlink($pfad.$testarray["url_decode"]);
			
			//neues speichern
			$dateiname=pfad_und_dateiname(injaway($_POST["test_lernbereich"]),'test',$name,$tempname);
			
			db_conn_and_sql("UPDATE `test` SET `url`=".apostroph_bei_bedarf($dateiname["datei"])." WHERE `id`=".injaway($_POST["test"]));
			
		}
	
	
		if ($_POST["test"]=="neu") {
			// Hier kommt man nur hin, wenn ein Arbeitsblatt erstellt werden soll
			$id=db_conn_and_sql("INSERT INTO `test` (`notentyp`, `lernbereich`, `platz_lassen`, `bemerkung`,`vorspann`,`titel`,`arbeitsblatt`, `user`) VALUES (0, ".leer_NULL($_POST['test_lernbereich']).", ".leer_NULL($_POST['platz']).", ".apostroph_bei_bedarf($_POST['bemerkung_test']).", ".apostroph_bei_bedarf($_POST['vorspann']).", ".apostroph_bei_bedarf($_POST['titel']).", 1, ".$_SESSION['user_id'].");");
		}
		else {
			$id=injaway($_POST['test']);
			db_conn_and_sql("UPDATE `test` SET `notentyp`=".($_POST['notentyp']+0).", `platz_lassen`=".leer_NULL($_POST['platz']).", `bearbeitungszeit`=".leer_NULL($_POST['zeit']).", `bemerkung`=".apostroph_bei_bedarf($_POST['bemerkung_test']).", `hilfsmittel`=".apostroph_bei_bedarf($_POST['hilfsmittel']).", `titel`=".apostroph_bei_bedarf($_POST['titel']).", `lernbereich`=".leer_NULL($_POST['test_lernbereich']).", `punkte`=".leer_NULL($_POST['gesamtpunkte']).",`vorspann`=".apostroph_bei_bedarf($_POST['vorspann'])."
				WHERE `id`=".$id);
		}
		
		//Aufgaben bearbeiten
		$i=0;
		while (isset($_POST["aufgabe_id_".$i])) {
			db_conn_and_sql("UPDATE `test_aufgabe` SET `position`=".leer_NULL($_POST['position_A_'.$i]).", `position_b`=".leer_NULL($_POST['position_B_'.$i]).", `neue_seite`=".leer_NULL($_POST['seitenumbruch_A_'.$i]).", `neue_seite_b`=".leer_NULL($_POST['seitenumbruch_B_'.$i]).", `zusatzaufgabe`=".($_POST['zusatzaufgabe_'.$i]+0)." WHERE `aufgabe`=".injaway($_POST["aufgabe_id_".$i])." AND `test`=".$id); $i++;
		}
		
		//neue Aufgaben
			$aufgaben=explode(";",$_POST["aufgaben_ids"]);
			$neue_position=sql_num_rows(db_conn_and_sql("SELECT * FROM `test_aufgabe` WHERE `test`=".$id))+1;
			for ($i=0;$i<(count($aufgaben)-1);$i++) {
				/*$hilf=explode(":",$aufgaben[$i]);
				$einzelpunkte=explode(",",$hilf[1]);
				db_conn_and_sql("INSERT INTO `test_aufgabe` (`test`, `aufgabe`,`zusatzaufgabe`,`position`,`position_b`,`neue_seite`,`neue_seite_b`) VALUES
					(".$_POST['test'].", ".$hilf[0].", ".leer_NULL($einzelpunkte[4]).", ".leer_NULL($einzelpunkte[0]).", ".leer_NULL($einzelpunkte[1]).", ".leer_NULL($einzelpunkte[2]).", ".leer_NULL($einzelpunkte[3]).");");*/
				/*echo "INSERT INTO `test_aufgabe` (`test`, `aufgabe`,`zusatzaufgabe`,`position`,`position_b`,`neue_seite`,`neue_seite_b`) VALUES
					(".$_POST['test'].", ".$aufgaben[$i].", 0, ".($_POST["neue_position"]+$i).", NULL, NULL, NULL);";*/
				db_conn_and_sql("INSERT INTO `test_aufgabe` (`test`, `aufgabe`,`zusatzaufgabe`,`position`,`position_b`,`neue_seite`,`neue_seite_b`) VALUES
					(".$id.", ".$aufgaben[$i].", 0, ".($neue_position+$i).", NULL, NULL, NULL);");
			}
		
		// Themen
		db_conn_and_sql("DELETE FROM themenzuordnung WHERE typ=5 AND id=".$id);
		
		for ($i=0;$i<10;$i++)
			if ($_POST["test_thema_".$i]!="-")
				db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`, `id`, `thema`) VALUES
					(5, ".$id.", ".injaway($_POST["test_thema_".$i]).");");
			?>
<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
<?php

		break;
		default:
			if (!proofuser("test",$_GET["welcher"]))
				die("Sie sind hierzu nicht berechtigt.");
			
			$titelleiste="Test / Arbeitsblatt bearbeiten";
			include($pfad."header.php");
			//$notentyp=db_conn_and_sql("SELECT DISTINCT notentypen.* FROM schule_user, notentypen WHERE notentypen.id<11 OR (notentypen.schule=schule_user.schule AND schule_user.user=".$_SESSION["user_id"].")");
			//$themen=db_conn_and_sql("SELECT * FROM `thema` WHERE `user`=".$_SESSION['user_id']);
			$lernbereiche = $db->lernbereiche();
			?>
			<body>
				<div class="inhalt">
					<div id="mf">
						<ul class="r">
							<li><a href="<?php echo $pfad; ?>test_druckansicht.php?welcher=<?php echo $_GET['welcher']; if ($_GET["typ"]=="arbeitsblatt") echo '&amp;typ=arbeitsblatt'; ?>&amp;datum=<?php echo $_GET["datum"]; ?>" class="icon"><img src="<?php echo $pfad; ?>icons/drucken.png" alt="drucken" /> Druckansicht</a></li>
							<li><a href="javascript: opener.location.reload(); window.close();" class="icon">
								<img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="x" /> Fenster schlie&szlig;en</a></li>
						</ul>
					</div>
			<?php
				if ($_GET["typ"]=="arbeitsblatt")
					test_druckansicht(injaway($_GET["welcher"]),"arbeitsblatt_bearbeiten");
				else
					test_druckansicht(injaway($_GET["welcher"]),"bearbeiten");
				?>
				</div>
			</body>
			</html>
			<?php
		break;

}
?>
