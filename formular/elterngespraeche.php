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
$titelleiste="Elternkontakte";
include $pfad."header.php";
include $pfad."funktionen.php";

$gespraechsarten=array('Elterngespr&auml;ch', 'Telefonat', 'eMail', 'Brief', 'Sch&uuml;lergespr&auml;ch');

?>
	<body>
	<div class="inhalt">
	<div id="mf">
		<ul class="r">
			<?php if ($_GET["eintragen"]=="druckansicht") { ?>
			<li>
				<a href="javascript: window.back();" class="icon">
				<img src="<?php echo $pfad; ?>icons/pfeil_links.png" alt="&lt;-" /> zur&uuml;ck</a></li>
			<li>
				<a href="javascript: window.print();" class="icon">
				<img src="<?php echo $pfad; ?>icons/drucken.png" alt="drucker" /> drucken</a></li>
				<?php } ?>
			<li>
				<a href="javascript: opener.location.reload(); window.close();" class="icon">
				<img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="x" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
<?php
if($_GET["eintragen"]=="true") {
	// eintragen
	if ($_POST["id"]>0) {
		if (proofuser("elternkontakt", $_POST["id"]))
			db_conn_and_sql("UPDATE elternkontakt SET datum=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["datum"])).", art=".injaway($_POST["art"]).", inhalt=".apostroph_bei_bedarf($_POST["inhalt"])." WHERE id=".injaway($_POST["id"]));
	}
	else if (proofuser("schueler", $_GET["schueler"]))	{
		db_conn_and_sql("INSERT INTO elternkontakt (datum, art, inhalt, schueler, user) VALUES(".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["datum"])).", ".injaway($_POST["art"]).", ".apostroph_bei_bedarf($_POST["inhalt"]).", ".injaway($_GET["schueler"]).", ".$_SESSION["user_id"].");");
		echo $gespraechsarten[$_POST["art"]]." erfolgreich eingetragen.<br />";
	}
}

if($_GET["eintragen"]=="loeschen") {
	// loeschen
	if (proofuser("elternkontakt", $_GET["id"]))
		db_conn_and_sql("DELETE FROM elternkontakt WHERE id=".injaway($_GET["id"]));
}

if (!proofuser("schueler",$_GET["schueler"]))
	die("Sie sind hierzu nicht berechtigt.");

$result=db_conn_and_sql("SELECT * FROM elternkontakt WHERE schueler=".injaway($_GET["schueler"])." AND user=".$_SESSION["user_id"]." ORDER BY datum DESC");
$schuelerdaten=db_conn_and_sql("SELECT * FROM schueler WHERE id=".injaway($_GET["schueler"]));

if ($_GET["eintragen"]=="druckansicht" and proofuser("elternkontakt", $_GET["id"])) {
	$brief=db_conn_and_sql("SELECT * FROM elternkontakt WHERE id=".injaway($_GET["id"])." AND user=".$_SESSION["user_id"]);
	?>
	<div style="font-family: serif; font-size: 10pt;">
	<div style="position: absolute; top: 8.5cm; left: 0cm;">-</div>
	<div style="position: absolute; top: 12.8cm; left: 0.1cm;">-</div>
	<div style="position: absolute; top: 19cm; left: 0cm;">-</div>
	<?php
	$benutzer=db_conn_and_sql("SELECT * FROM benutzer WHERE id=".$_SESSION['user_id']);
	$schule=db_conn_and_sql("SELECT schule.* FROM klasse, schule WHERE klasse.id=".sql_result($schuelerdaten,0,"schueler.klasse")." AND klasse.schule=schule.id");
	if (file_exists($pfad."daten/schullogos/logo_".sql_result($schule,0,"schule.id").".gif"))
		echo '<img src="'.$pfad.'daten/schullogos/logo_'.sql_result($schule,0,"schule.id").'.gif" alt="schullogo" style="float:left;" />';
	?>
	<div id="absender" style="float: right; position: absolute; right: 1cm; text-align: right;">
	<p><span style="font-weight: bold;"><?php echo html_umlaute(sql_result($schule,0,"schule.name")); ?></span><br />
		<?php echo html_umlaute(sql_result($schule,0,"schule.adresse")); ?><br />
		<?php echo html_umlaute(sql_result($schule,0,"schule.plz_ort")); ?><br />
		<?php if (sql_result($schule,0,"schule.telefon")!="") echo "Tel.: ".html_umlaute(sql_result($schule,0,"schule.telefon")); ?><br />
		<?php if (sql_result($schule,0,"schule.fax")!="") echo "Fax: ".html_umlaute(sql_result($schule,0,"schule.fax")); ?>
	</p>
	<p><?php echo html_umlaute(sql_result($benutzer,0,"benutzer.vorname")." ".sql_result($benutzer,0,"benutzer.name")); ?>
	</p></div>
	
	<div id="empfaenger" style="position: absolute; left: 0.4cm; top: 4cm;">Fam. <?php echo html_umlaute(sql_result($schuelerdaten,0,"schueler.name")); ?><br />
	<?php echo html_umlaute(sql_result($schuelerdaten,0,"schueler.strasse")); ?><br />
	<?php echo html_umlaute(sql_result($schuelerdaten,0,"schueler.ort")); ?></div>

	<div style="position: absolute; right: 1cm; top: 8cm;"><?php echo datum_strich_zu_punkt(sql_result($brief, 0, "elternkontakt.datum")); ?></div>
	<div id="text" style="position: absolute; top: 9cm; left: 1cm;">
		<p>
		<?php echo syntax_zu_html(sql_result($brief, 0, "elternkontakt.inhalt"), 1, 0, $pfad); ?>
		</p>
		</div>
	</div>

	<?php
}
else {
?>
		<form action="<?php echo $pfad; ?>formular/elterngespraeche.php?schueler=<?php echo $_GET["schueler"]; ?>&amp;eintragen=true" method="post" accept-charset="ISO-8859-1">
		<?php if($_GET["eintragen"]=="bearbeiten" and proofuser("elternkontakt",$_GET["id"])) {
				$bearbeiten=db_conn_and_sql("SELECT * FROM elternkontakt WHERE id=".injaway($_GET["id"]));
				echo '<input type="hidden" name="id" value="'.injaway($_GET["id"]).'" />';
			} ?>
			<fieldset><legend>Elternkontakt - <?php echo html_umlaute(sql_result($schuelerdaten,0,"schueler.name")); ?></legend>
			<label for="datum">Datum<em>*</em>:</label> <input type="text" name="datum" class="datepicker" size="7" maxlength="10"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.datum_strich_zu_punkt(sql_result($bearbeiten, 0, "elternkontakt.datum")).'"'; ?> /><br />
			<label for="art">Art<em>*</em>:</label> <select name="art">
			<?php for($i=0; $i<count($gespraechsarten); $i++) {
					echo '<option value="'.$i.'"';
					if($_GET["eintragen"]=="bearbeiten" and $i==sql_result($bearbeiten, 0, "elternkontakt.art")) echo ' selected="selected"';
					echo '>'.$gespraechsarten[$i].'</option>';
				}?>
			</select><br />
			<label for="inhalt">Inhalt<em>*</em>:</label> <textarea name="inhalt" rows="10" cols="100"><?php if($_GET["eintragen"]=="bearbeiten") echo html_umlaute(sql_result($bearbeiten, 0, "elternkontakt.inhalt")); ?></textarea><br />
			<input type="button" class="button" value="<?php if($_GET["eintragen"]=="bearbeiten") echo 'ver&auml;ndern'; else echo 'hinzuf&uuml;gen'; ?>" onclick="auswertung=new Array(new Array(0, 'inhalt','nicht_leer'), new Array(0, 'datum','datum','2009-01-01','<?php echo ($aktuelles_jahr+1); ?>-12-31')); pruefe_formular(auswertung);" />
			</fieldset>
		</form>
<?php
	if (sql_num_rows($result)>0) {
		echo '<h3>Eingetragene Kontakte</h3>
			<table class="tabelle"><tr><th>Datum / Art</th><th>Inhalt</th></tr>';
		for ($i=0; $i<sql_num_rows($result); $i++) {
			echo '<tr><td>'.datum_strich_zu_punkt(sql_result($result, $i, "elternkontakt.datum")).'<br />'.$gespraechsarten[sql_result($result,$i, "elternkontakt.art")].'</td>
				<td>'.nl2br(html_umlaute(sql_result($result, $i, "elternkontakt.inhalt")));
				if (sql_result($result,$i, "elternkontakt.art")==3) echo '
					<a href="'.$pfad.'formular/elterngespraeche.php?schueler='.$_GET["schueler"].'&amp;eintragen=druckansicht&amp;id='.sql_result($result, $i, "elternkontakt.id").'" class="icon"><img src="'.$pfad.'icons/drucken.png" alt="drucker" /></a>';
					echo '
					<a href="'.$pfad.'formular/elterngespraeche.php?schueler='.$_GET["schueler"].'&amp;eintragen=bearbeiten&amp;id='.sql_result($result, $i, "elternkontakt.id").'" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
					<a href="'.$pfad.'formular/elterngespraeche.php?schueler='.$_GET["schueler"].'&amp;eintragen=loeschen&amp;id='.sql_result($result, $i, "elternkontakt.id").'" onclick="if (confirm(\'Der Elternkontakt wird endg&uuml;ltig gel&ouml;scht. Wollen Sie das wirklich?\')==false) return false;" class="icon"><img src="'.$pfad.'icons/delete.png" alt="loeschen" /></a>
				</td></tr>';
		}
		echo '</table>';
	}
	} ?>
	</div>
</body>
</html>
