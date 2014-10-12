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

$pfad="../";
$titelleiste="Sch&uuml;lerlisteneintr&auml;ge verwalten";
include $pfad."header.php";
include $pfad."funktionen.php";

$fk_id = $subject_classes->cont[$subject_classes->active]["id"];
if (!proofuser("fach_klasse", $fk_id))
	die("Sie sind hierzu nicht berechtigt.");

if ($_GET["eintragen"]==true)
	$_GET["id"]=$_POST["id"];

$listeneintraege = db_conn_and_sql("SELECT * FROM liste, liste_schueler, schueler
	WHERE liste_schueler.schueler=schueler.id
		AND liste_schueler.liste=liste.id
		AND liste.fach_klasse=".$fk_id."
		AND liste.id=".injaway($_GET["id"])."
	ORDER BY schueler.klasse, schueler.position, schueler.maennlich, schueler.name, schueler.vorname");

$listentyp = explode("||", sql_result($listeneintraege, 0, "liste.typ"));
$typen="";
foreach ($listentyp as $eintrag) {
	$text=substr($eintrag, 1);
	switch (substr($eintrag, 0, 1)) {
		case "Z": $typen[]=array("Z", $text); break; // ZZ - ganze Zahl
		case "Q": $typen[]=array("Q", $text); break; // QQ - Dezimalzahl
		case "T": $typen[]=array("T", $text); break; // Text
		default:  $typen[]=array("C", $text); break; // Checkbox
	}
}

?>
	<body>
	<div class="inhalt">
	<div id="mf">
		<ul class="r">
			<li><a href="javascript: opener.location.reload(); window.close();" class="icon">
				<img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="x" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
<?php

if($_GET["eintragen"]=="true" and $_POST["id"]>0) {
	if (proofuser("liste", $_POST["id"])) {
		// bei Bedarf abgeschlossen setzen
		if ($_POST["abgeschlossen"]!="")
			db_conn_and_sql("UPDATE liste SET abgeschlossen=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["abgeschlossen"]))." WHERE id=".$_POST["id"]);
		// Listeneintraege setzen
		$i=0;
		while (isset($_POST["s".$i])) {
			$listeninhalt="";
			for ($k=0; $k<count($typen); $k++)
				$listeninhalt.=$_POST["e".$i."-".$k]."||";
			db_conn_and_sql("UPDATE liste_schueler SET inhalt=".apostroph_bei_bedarf($listeninhalt).", fertig=".leer_NULL($_POST['e'.$i.'fertig'])." WHERE liste=".$_POST["id"]." AND schueler=".$_POST["s".$i]);
			$i++;
		}
		/*db_conn_and_sql("UPDATE liste SET name=".apostroph_bei_bedarf($_POST["name"]).", typ=".apostroph_bei_bedarf($_POST["typ"]).", abgeschlossen=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["abgeschlossen"])).", faellig=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["faellig"]))." WHERE id=".injaway($_POST["id"]));
		foreach($_POST["schueler"] as $i)
			if (sql_num_rows(db_conn_and_sql("SELECT * FROM liste_schueler WHERE liste=".$_POST["id"]." AND schueler=".$i))<1) {
				db_conn_and_sql("INSERT INTO liste_schueler (liste, schueler) VALUES (".$_POST["id"].", ".$i.");");
			}
		*/
	}
	echo '<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>';
}

?>
	<form action="<?php echo $pfad; ?>formular/listeneintraege.php?eintragen=true" method="post" accept-charset="ISO-8859-1">
	<?php
		echo '<input type="hidden" name="id" value="'.injaway($_GET["id"]).'" />';
		?>
		<fieldset><legend>Sch&uuml;lerlisteneintr&auml;ge von <?php echo $subject_classes->cont[$subject_classes->active]["farbanzeige"]; ?> bearbeiten (<?php echo html_umlaute(sql_result($listeneintraege, 0, "liste.name")); ?>)</legend>
		<?php
		echo '<table class="tabelle"><tr><td>&nbsp;</td>';
		if ($typen[0][1]!="")
			for ($k=0; $k<count($typen); $k++)
				echo '<th>'.$typen[$k][1].'</th>';
		echo '<th>fertig</th></tr>';
		if (sql_num_rows($listeneintraege)>0)
		for ($i=0; $i<sql_num_rows($listeneintraege); $i++) {
			echo '<tr><td><input type="hidden" name="s'.$i.'" value="'.sql_result($listeneintraege, $i, "schueler.id").'" />'.sql_result($listeneintraege, $i, "schueler.name").', '.sql_result($listeneintraege, $i, "schueler.vorname").'</td>';
			if ($typen[0][1]!="") {
				$werte=explode("||", sql_result($listeneintraege, $i, "liste_schueler.inhalt"));
				for ($k=0; $k<count($typen); $k++) {
					switch($typen[$k][0]) {
						case "Z": echo '<td><input type="number" name="e'.$i.'-'.$k.'" size="1" maxlength="5" min="-1000" max="1000" value="'.$werte[$k].'" /></td>'; break;
						case "Q": echo '<td><input type="text" name="e'.$i.'-'.$k.'" size="1" maxlength="7" min="-1000" max="1000" value="'.$werte[$k].'" /></td>'; break;
						case "T": echo '<td><input type="text" name="e'.$i.'-'.$k.'" size="5" maxlength="20" value="'.$werte[$k].'" /></td>'; break;
						case "C": echo '<td><input type="checkbox" name="e'.$i.'-'.$k.'" value="1"'; if ($werte[$k]==1) echo ' checked="checked"'; echo ' /></td>'; break;
					}
				}
			}
			echo '<td><input type="checkbox" name="e'.$i.'fertig" value="1"';
			if (sql_result($listeneintraege, $i, "liste_schueler.fertig")==1)
				echo ' checked="checked"';
			echo ' /></td></tr>';
		}
		echo '</table>';
		?>
		<label for="abgeschlossen">Abgeschlossen:</label> <input type="checkbox" onchange="this.checked==true?document.getElementById('abgeschlossendate').style.visibility='visible':document.getElementById('abgeschlossendate').style.visibility='hidden'" /> <input type="text" class="datepicker" id="abgeschlossendate"<?php if (sql_result($listeneintraege, 0, "liste.abgeschlossen")=="") echo ' style="visibility: hidden"'; ?> name="abgeschlossen" size="7" maxlength="10"<?php if($_GET["eintragen"]=="bearbeiten" and sql_result($listeneintraege, 0, "liste.abgeschlossen")!="") echo ' value="'.datum_strich_zu_punkt(sql_result($listeneintraege, 0, "liste.abgeschlossen")).'"'; ?> /><br />
		<input type="submit" value="speichern" />
		</fieldset>
	</form>
	</div>
</body>
</html>
