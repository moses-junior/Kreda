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
$titelleiste="Neuer Sch&uuml;ler";
include $pfad."header.php";
include "../funktionen.php";

if (userrigths("schuelerdaten", $_GET["klasse"])!=2)
	die("Sie haben nicht die erforderlichen Rechte, um Sch&uuml;ler hinzuzuf&uuml;gen.");

?>
	<body onLoad="self.focus(); document.pupil_form.position_neu.focus()">
	<div class="tooltip" id="tt_position">
		Geben Sie die Position des Sch&uuml;lers im Klassenbuch an. Nach dieser Reihenfolge richtet sich die Klassenbuchansicht, die Zensuren&uuml;bersicht und Anderes. (Die Position ist nicht in allen Schulen alphabetisch geordnet)</div>
	<div class="tooltip" id="tt_geburtstag">
		Wird ein Geburtstag (im Format TT.MM.JJJJ angegeben, wird dieser auf der Startseite angezeigt.</div>
	<div class="tooltip" id="tt_anschrift">
		Mithilfe eines Karten-Service im Internet kann die Adresse per Mausklick angezeigt werden. Au&szlig;erdem wird diese in Briefk&ouml;pfen automatisch verwendet.</div>
	<div class="tooltip" id="tt_email">
		Schreiben Sie mit Ihrem eMail-Client (z.B. Thunderbird, Outlook...) direkt eine eMail.</div>
	<div class="inhalt">
	<div id="mf">
		<ul class="r">
			<li><a href="javascript: opener.location.reload(); window.close();" class="icon">
				<img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="x" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
<?php
if($_GET["eintragen"]=="true") {
	db_conn_and_sql("INSERT INTO `schueler` (`position`, `name`, `vorname`, `geburtstag`, `strasse`, `ort`, `klasse`, `email`, `telefon`, `bemerkungen`,`maennlich`, `geburtsort`, `krankenkasse`, `notfall`) VALUES
		('".injaway($_POST['position_neu'])."', ".apostroph_bei_bedarf($_POST['name_neu']).", ".apostroph_bei_bedarf($_POST['vorname_neu']).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST['geburtstag_neu'])).", ".apostroph_bei_bedarf($_POST['strasse_neu']).", ".apostroph_bei_bedarf($_POST['ort_neu']).", ".injaway($_GET['klasse']).", ".apostroph_bei_bedarf($_POST['email_neu']).", ".apostroph_bei_bedarf($_POST['telefon_neu']).", ".apostroph_bei_bedarf($_POST['bemerkungen_neu']).",".leer_NULL($_POST["geschlecht"]).", ".apostroph_bei_bedarf($_POST['geburtsort_neu']).", ".apostroph_bei_bedarf($_POST['krankenkasse_neu']).", ".apostroph_bei_bedarf($_POST['notfall_neu']).");");
	echo html_umlaute($_POST['vorname_neu'])." ".html_umlaute($_POST['name_neu'])." erfolgreich eingetragen.<br />";
}
?>
	<h1>Sch&uuml;ler der Klasse <?php echo $school_classes->nach_ids[$_GET["klasse"]]["name"]; ?> hinzuf&uuml;gen</h1>
	<form action="<?php echo $pfad; ?>formular/schueler_neu.php?eintragen=true&amp;klasse=<?php echo $_GET["klasse"]; ?>" method="post" accept-charset="ISO-8859-1" name="pupil_form"> <!-- form-Name only because of tabindex 1 on load -->
    <table class="tabelle" cellspacing="0">
      <tr>
        <th>Pos <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_position')" onmouseout="hideWMTT()" /></th>
        <th title="m&auml;nnlich / weiblich">Vorname, Name, m/w</th>
        <th>Geburtstag / -ort <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_geburtstag')" onmouseout="hideWMTT()" /></th>
        <th>Anschrift <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_anschrift')" onmouseout="hideWMTT()" /></th>
        <th>Tel / eMail <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_email')" onmouseout="hideWMTT()" /></th>
        <th>Krankenkasse / Notfallperson</th>
        <th>Bemerkungen</th>
      </tr>
      <tr>
        <td><input type="text" name="position_neu" size="1" maxlength="2" tabindex="1" /></td>
		<td><input type="text" name="vorname_neu" size="9" maxlength="30" tabindex="2" placeholder="Vorname" /><br />
			<input type="text" name="name_neu" size="9" maxlength="50" tabindex="3" placeholder="nachname" />
			<select name="geschlecht" tabindex="4"><option value="1">m</option><option value="0">w</option></select></td>
        <td><input type="text" name="geburtstag_neu" id="geburtstag" size="10" maxlength="10" placeholder="Geburtsdatum" /><br />
			<input type="text" name="geburtsort_neu" size="10" maxlength="35" placeholder="Geburtsort" /></td>
        <td><input type="text" name="strasse_neu" size="10" maxlength="35" placeholder="Stra&szlig;e" /><br />
			<input type="text" name="ort_neu" size="10" maxlength="35" placeholder="PLZ Ort" /></td>
        <td><input type="text" name="telefon_neu" size="10" maxlength="20" placeholder="Telefon" /><br />
			<input type="text" name="email_neu" id="email" size="10" maxlength="35" placeholder="E-Mail" /></td>
        <td><input type="text" name="krankenkasse_neu" size="15" maxlength="50" placeholder="Krankenkasse" /><br />
			<input type="text" name="notfall_neu" size="15" maxlength="150" placeholder="Notfallperson" /></td>
        <td><textarea name="bemerkungen_neu" cols="30" rows="2" placeholder="Bemerkungen f&uuml;r alle Lehrer"></textarea></td>
      </tr>
	</table>
	<input type="button" class="button" value="hinzuf&uuml;gen" tabindex="5" onclick="auswertung=new Array(new Array(0, 'position_neu','natuerliche_zahl'), new Array(0, 'name_neu','nicht_leer'), new Array(0, 'vorname_neu','nicht_leer')); if (document.getElementById('geburtstag').value!='') auswertung.push(new Array(0, 'geburtstag_neu','datum','1950-01-01','<?php echo ($aktuelles_jahr-3); ?>-01-01')); if (document.getElementById('email').value!='') auswertung.push(new Array(0, 'email_neu','email')); pruefe_formular(auswertung);" />
	</form>
	</div>
</body>
</html>
