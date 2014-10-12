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
$titelleiste="Sch&uuml;lerliste";
include $pfad."header.php";
include $pfad."funktionen.php";

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

if($_GET["eintragen"]=="true") {
	// bearbeiten
	if ($_POST["id"]>0) {
		if (proofuser("liste", $_POST["id"])) {
			db_conn_and_sql("UPDATE liste SET name=".apostroph_bei_bedarf($_POST["name"]).", typ=".apostroph_bei_bedarf($_POST["typ"]).", abgeschlossen=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["abgeschlossen"])).", faellig=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["faellig"]))." WHERE id=".injaway($_POST["id"]));
			foreach($_POST["schueler"] as $i)
				if (sql_num_rows(db_conn_and_sql("SELECT * FROM liste_schueler WHERE liste=".$_POST["id"]." AND schueler=".$i))<1) {
					db_conn_and_sql("INSERT INTO liste_schueler (liste, schueler) VALUES (".$_POST["id"].", ".$i.");");
				}
		}
		echo '<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>';
	}
	// eintragen
	else {
		if (proofuser("fach_klasse", $_POST["fach_klasse"])) {
			$id=db_conn_and_sql("INSERT INTO liste (fach_klasse, name, typ, abgeschlossen, erstelldatum, faellig) VALUES(".injaway($_POST["fach_klasse"]).", ".apostroph_bei_bedarf($_POST["name"]).", ".apostroph_bei_bedarf($_POST["typ"]).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["abgschlossen"])).", '".date("Y-m-d")."', ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["faellig"])).");");
			foreach($_POST["schueler"] as $i)
				db_conn_and_sql("INSERT INTO liste_schueler (liste, schueler) VALUES (".$id.", ".$i.");");
			echo '<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>';
		}
	}
}

?>
	<form action="<?php echo $pfad; ?>formular/liste_bearbeiten.php?eintragen=true" method="post" accept-charset="ISO-8859-1">
	<?php
		// nur wenn nicht "bearbeiten"
		$fk_id = $subject_classes->cont[$subject_classes->active]["id"];
		if (!proofuser("fach_klasse", $fk_id))
			die("Sie sind hierzu nicht berechtigt.");
		
		if($_GET["eintragen"]=="bearbeiten") {
			$bearbeiten=db_conn_and_sql("SELECT * FROM liste, liste_schueler WHERE liste_schueler.liste=liste.id AND fach_klasse=".$fk_id." AND id=".injaway($_GET["id"]));
			echo '<input type="hidden" name="id" value="'.injaway($_GET["id"]).'" />';
		} ?>
		<script>
			checkflag=false;
			function check(field) {
				if (checkflag == false) {
					for (i = 0; i < field.length; i++) {
						if (field[i].disabled == false)
							field[i].checked = true;
					}
					checkflag = true;
				}
				else {
					for (i = 0; i < field.length; i++) {
						if (field[i].disabled == false)
							field[i].checked = false;
					}
					checkflag = false;
				}
			}
		</script>
		<div class="tooltip" id="tt_typ">
			<p>Verwenden Sie folgende Syntax: <pre>[Typ-Buchstabe][Titel]||Typ-Buchstabe][Titel]||...</pre></p>
			<p>Typ-Buchstaben</p>
			<ul>
				<li>C: Checkbox (H&auml;kchen setzen)</li>
				<li>Q: Rationale Zahl (0,5; 1,87; -7...)</li>
				<li>Z: Ganze Zahl (... -2; -1; 0; 1; 2; ...)</li>
				<li>T: Textfeld</li>
			</ul>
			<p>Am Ende wird ein Typ Checkbox mit Aufschrift "fertig" eingef&uuml;gt. Dies geschieht automatisch.</p>
			<p>Beispiele:</p>
			<ul>
				<li><pre>ZAnzahl</pre> Zweispaltige Liste mit "Anzahl" als ganze Zahl und fertig als Checkbox.</li>
				<li><pre>Cbezahlt||QBetrag||TBeschreibung</pre>Vierspaltige Liste mit "bezahlt" als Checkbox, "Betrag" als rationale Zahl, "Beschreibung" als Textfeld und "fertig" als Checkbox.</li>
				<li><pre>[frei lassen]</pre>Standardliste mit der Spalte "fertig" als Checkbox.</li>
			</ul>
		</div>

		<fieldset><legend>Sch&uuml;lerliste von <?php echo $subject_classes->cont[$subject_classes->active]["farbanzeige"]; if($_GET["eintragen"]=="bearbeiten") echo ' bearbeiten'; else echo ' neu'; ?></legend>
		<input type="hidden" name="fach_klasse" value="<?php echo $fk_id; ?>" />
		<label for="name">Name<em>*</em>:</label> <input type="text" name="name" size="10" maxlength="20"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.html_umlaute(sql_result($bearbeiten, 0, "liste.name")).'"'; ?> required="required" /><br />
		<label for="typ">Typ:  <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_typ')" onmouseout="hideWMTT()" /></label> <input type="text" name="typ" size="10" maxlength="200"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.html_umlaute(sql_result($bearbeiten, 0, "liste.typ")).'"'; ?> /><br />
		<label for="faellig">F&auml;llig am:</label> <input type="text" class="datepicker" name="faellig" size="7" maxlength="10"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.datum_strich_zu_punkt(sql_result($bearbeiten, 0, "liste.faellig")).'"'; ?> /><br />
		<label for="abgeschlossen">Abgeschlossen:</label> <input type="checkbox" onchange="this.checked==true?document.getElementById('abgeschlossendate').style.visibility='visible':document.getElementById('abgeschlossendate').style.visibility='hidden'" /> <input type="text" class="datepicker" id="abgeschlossendate"<?php if ($_GET["eintragen"]!="bearbeiten" or sql_result($bearbeiten, 0, "liste.abgeschlossen")=="") echo ' style="visibility: hidden"'; ?> name="abgeschlossen" size="7" maxlength="10"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.datum_strich_zu_punkt(sql_result($bearbeiten, 0, "liste.abgeschlossen")).'"'; ?> /><br />
		<label for="schueler[]">Sch&uuml;ler<em>*</em>:</label> alle markieren: <input type="checkbox" onclick="check(this.form.elements['schueler[]']);" /><br />
		<?php
		$schueler=schueler_von_fachklasse($fk_id);
		$schuelerzaehler=0;
		for ($i=0; $i<sql_num_rows($schueler); $i++) { ?>
			<input type="checkbox" name="schueler[]"<?php
			if ($_GET["eintragen"]=="bearbeiten")
				if (sql_num_rows(db_conn_and_sql("SELECT * FROM liste_schueler WHERE liste_schueler.liste=".$_GET["id"]." AND liste_schueler.schueler=".sql_result($schueler, $i, "schueler.id")))>0)
					echo ' checked="checked" disabled="disabled"';
			?> value="<?php echo sql_result($schueler, $i, "schueler.id"); ?>" />
			<?php echo sql_result($schueler, $i, "schueler.name").", ".sql_result($schueler, $i, "schueler.vorname"); ?>
			<br />
			<?php
		}
		?>
		<input type="submit" value="<?php if($_GET["eintragen"]=="bearbeiten") echo 'speichern'; else echo 'hinzuf&uuml;gen'; ?>" />
		</fieldset>
	</form>
	</div>
</body>
</html>
