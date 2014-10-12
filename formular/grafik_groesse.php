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
// hier kommt man nicht mehr hin - loeschen
include "../funktionen.php";
$pfad="../";

if (!proofuser("grafik",$_GET["grafik"]))
	die("Sie sind hierzu nicht berechtigt.");

if ($_GET["eintragen"]=="true") {
	db_conn_and_sql("UPDATE `grafik_abschnitt` SET `groesse`=".punkt_statt_komma_zahl($_POST["groesse"]).", `position`=".leer_NULL($_POST['position'])." WHERE `grafik`=".injaway($_GET["grafik"])." AND `abschnitt`=".injaway($_GET["abschnitt"]));
	$titelleiste="Grafik bearbeiten";
	include $pfad."header.php"; ?>
	<body>
	Fertig<br />
	<input type="button" class="button" value="Fenster schlie&szlig;en" onclick="opener.location.reload(); window.close();" />
	
<?php
}
else {
	$grafik=db_conn_and_sql("SELECT * FROM `grafik_abschnitt`, `grafik` WHERE `grafik_abschnitt`.`grafik`=`grafik`.`id` AND `grafik_abschnitt`.`grafik`=".injaway($_GET["grafik"])." AND `grafik_abschnitt`.`abschnitt`=".injaway($_GET["abschnitt"]));
	$titelleiste="Grafik bearbeiten";
	include $pfad."header.php"; ?>
	<body>
		<div id="mf">
			<ul class="r">
				<li><a href="javascript:window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
			</ul>
		</div>  
	<form action="<?php echo $pfad; ?>formular/grafik_groesse.php?grafik=<?php echo $_GET["grafik"]; ?>&amp;abschnitt=<?php echo $_GET["abschnitt"]; ?>&amp;eintragen=true" method="post" enctype="multipart/form-data">
      <fieldset><legend>Gr&ouml;&szlig;e der Grafik &auml;ndern</legend>
	<ol><li>
      <label for="groesse">Gr&ouml;&szlig;e<em>*</em>:</label> <input type="text" name="groesse" size="3" value="<?php echo kommazahl(sql_result($grafik,0,"grafik_abschnitt.groesse")); ?>" maxlength="4" /><br />
      <label for="position">Position<em>*</em>:</label> <select name="position">
			<option value="0"<?php if (sql_result($grafik,0,"grafik_abschnitt.position")==0) echo ' selected="selected"'; ?>>mittig</option>
			<option value="1"<?php if (sql_result($grafik,0,"grafik_abschnitt.position")==1) echo ' selected="selected"'; ?>>links</option>
			<option value="2"<?php if (sql_result($grafik,0,"grafik_abschnitt.position")==2) echo ' selected="selected"'; ?>>rechts</option></select>
      </li></ol>
		<input type="button" class="button" value="speichern" onclick="auswertung=new Array(new Array(0, 'groesse','pos_komma_zahl')); pruefe_formular(auswertung);" />
      </fieldset>
      </form><?php
		$db=new db;
		$bildarray=$db->grafik(injaway($_GET["grafik"]));
		echo '<img src="'.$pfad.$bildarray["url"].'" alt="bild" title="altes Bild" style="border: 2px green dashed;" />';
}
		?>
		Hier kommt man gar nicht mehr hin.
	</body>
</html>
