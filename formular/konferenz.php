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

/*include "../funktionen.php";
$pfad="../";
$titelleiste="Konferenz";
include $pfad."header.php";

?>
	<body>
	<div class="inhalt">
	<div id="mf">
		<ul class="r">
			<li><a href="javascript: opener.location.reload(); window.close();" class="icon">
				<img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="x" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
<?php*/

if($_GET["eintragen"]=="true") {
	// eintragen
	if ($_POST["id"]>0) {
		if (proofuser("konferenz", $_POST["id"]))
			db_conn_and_sql("UPDATE konferenz SET titel=".apostroph_bei_bedarf($_POST["titel"]).", datum=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["datum"])).", zeit=".apostroph_bei_bedarf($_POST["zeit"]).", schule=".injaway($_POST["schule"]).", ort=".apostroph_bei_bedarf($_POST["ort"]).", inhalt=".apostroph_bei_bedarf($_POST["inhalt"])." WHERE id=".injaway($_POST["id"]));
	}
	else {
		db_conn_and_sql("INSERT INTO konferenz (datum, zeit, schule, ort, inhalt, titel, klasse, user) VALUES(".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["datum"])).", ".apostroph_bei_bedarf($_POST["zeit"].":00").", ".injaway($_POST["schule"]).", ".apostroph_bei_bedarf($_POST["ort"]).", ".apostroph_bei_bedarf($_POST["inhalt"]).", ".apostroph_bei_bedarf($_POST["titel"]).", ".leer_NULL($_POST["klasse"]).", ".$_SESSION['user_id'].");");
		echo "Konferenz/Elternabend erfolgreich eingetragen.<br />";
	}
}

if($_GET["eintragen"]=="loeschen" and proofuser("konferenz", $_GET["id"])) {
	// loeschen
	db_conn_and_sql("DELETE FROM konferenz WHERE id=".injaway($_GET["id"]));
}

if ($_GET["auswahl"]>0)
	$klasse="&amp;auswahl=".$_GET["auswahl"];
else
	$klasse='';

?>
	<form action="<?php echo $pfad.$formularziel; ?>&amp;eintragen=true<?php echo $klasse; ?>" method="post" accept-charset="ISO-8859-1">
	<?php if($_GET["eintragen"]=="bearbeiten") {
			if (!proofuser("konferenz", $_GET["id"]))
				die("Sie sind hierzu nicht berechtigt.");
			$bearbeiten=db_conn_and_sql("SELECT * FROM konferenz WHERE id=".injaway($_GET["id"]));
			echo '<input type="hidden" name="id" value="'.$_GET["id"].'" />'; // bei name stand vorher name="klasse" - funktioniert Elternabend noch?
		} ?>
		<fieldset><legend><?php if ($_GET["auswahl"]>0) echo 'Elternabend'; else echo 'Konferenz'; if($_GET["eintragen"]=="bearbeiten") echo ' bearbeiten'; else echo ' neu <img id="img_konferenzen" src="'.$pfad.'icons/clip_closed.png" alt="clip" onclick="javascript:clip(\'konferenzen\', \''.$pfad.'\')" />'; ?></legend>
		<span id="span_konferenzen" <?php if($_GET["eintragen"]!="bearbeiten") echo ' style="display: none;"'; ?>>
		<?php if ($_GET["auswahl"]>0) echo '<input type="hidden" name="klasse" value="'.$_GET["auswahl"].'" />'; ?>
		<label for="titel">Titel<em>*</em>:</label> <input type="text" name="titel" size="20" maxlength="250"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.html_umlaute(sql_result($bearbeiten, 0, "konferenz.titel")).'"'; ?> /><br />
		<label for="datum">Datum<em>*</em>:</label> <input type="text" class="datepicker" name="datum" size="7" maxlength="10"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.datum_strich_zu_punkt(sql_result($bearbeiten, 0, "konferenz.datum")).'"'; ?> />
		<label for="zeit">Zeit<em>*</em>:</label> <input type="time" name="zeit" size="5" maxlength="7"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.substr(sql_result($bearbeiten, 0, "konferenz.zeit"),0,5).'"'; ?> /><br />
		<label for="schule">Schule<em>*</em>:</label> <select name="schule"><?php
			$schulen=db_conn_and_sql("SELECT * FROM schule, schule_user WHERE schule_user.schule=schule.id AND schule_user.user=".$_SESSION['user_id']." ORDER BY schule_user.aktiv DESC");
			if ($_GET["auswahl"]>0 and proofuser("klasse", $_GET["auswahl"]))
				$schule_vorauswahl=sql_result(db_conn_and_sql("SELECT * FROM klasse WHERE klasse.id=".injaway($_GET["auswahl"])),0,"klasse.schule");
			else
				$schule_vorauswahl='';
			for ($i=0;$i<sql_num_rows($schulen);$i++) {
				echo '<option value="'.sql_result($schulen, $i, "schule.id").'"';
				if(($_GET["eintragen"]=="bearbeiten" and sql_result($schulen, $i, "schule.id")==sql_result($bearbeiten, 0, "konferenz.schule"))
					or ($_GET["eintragen"]!="bearbeiten" and $schule_vorauswahl==sql_result($schulen, $i, "schule.id"))) echo ' selected="selected"';
				echo ' title="'.sql_result($schulen, $i, "schule.name").'">'.sql_result($schulen, $i, "schule.kuerzel").'</option>';
			}
			?>
			</select><br />
		<label for="ort">Ort<em>*</em>:</label> <input type="text" name="ort" size="12" maxlength="250"<?php if($_GET["eintragen"]=="bearbeiten") echo ' value="'.html_umlaute(sql_result($bearbeiten, 0, "konferenz.ort")).'"'; ?> /><br />
		<label for="inhalt">Inhalt<em>*</em>:</label> <textarea name="inhalt" class="markItUp" rows="10" cols="100"><?php if($_GET["eintragen"]=="bearbeiten") echo html_umlaute(sql_result($bearbeiten, 0, "konferenz.inhalt")); ?></textarea><br />
		<input type="button" class="button" value="<?php if($_GET["eintragen"]=="bearbeiten") echo 'speichern'; else echo 'hinzuf&uuml;gen'; ?>" onclick="auswertung=new Array(new Array(0, 'datum','datum','<?php echo ($aktuelles_jahr-1); ?>-01-01','<?php echo ($aktuelles_jahr+1); ?>-12-31')); pruefe_formular(auswertung);" />
		</span>
		</fieldset>
	</form>
	<?php // aus Test raus: new Array(0, 'inhalt','nicht_leer')
		if ($_GET["auswahl"]>0 and proofuser("klasse", $_GET["auswahl"]))
			$klasse_sql=" WHERE klasse=".injaway($_GET["auswahl"]);
		else
			$klasse_sql=' WHERE klasse IS NULL AND user='.$_SESSION['user_id'];
		
		$result=db_conn_and_sql("SELECT * FROM konferenz".$klasse_sql." ORDER BY datum DESC, zeit DESC");
		
		if (@sql_num_rows($result)>0) {
			echo '<h3>Eingetragene '; if ($_GET["auswahl"]>0) echo 'Elternabende'; else echo 'Konferenzen'; echo '</h3>
				<table class="tabelle"><tr><th>Datum / Ort</th><th>Inhalt</th></tr>';
			for ($i=0; $i<sql_num_rows($result); $i++) {
				echo '<tr><td style="text-align: center">'.datum_strich_zu_punkt(sql_result($result, $i, "konferenz.datum")).'<br />'.substr(sql_result($result, $i, "konferenz.zeit"),0,5).' Uhr<br />'.html_umlaute(sql_result($result,$i, "konferenz.ort")).'</td>
					<td><span style="font-weight: bold;">'.html_umlaute(sql_result($result, $i, "konferenz.titel")).'</span><br />'.syntax_zu_html(sql_result($result, $i, "konferenz.inhalt"), 1, 0, '../', 'A').'
						<a href="'.$pfad.$formularziel.'&amp;eintragen=bearbeiten'.$klasse.'&amp;id='.sql_result($result, $i, "konferenz.id").'" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
						<a href="'.$pfad.$formularziel.'&amp;eintragen=loeschen'.$klasse.'&amp;id='.sql_result($result, $i, "konferenz.id").'" onclick="if (confirm(\'Die Konferenz wird endg&uuml;ltig gel&ouml;scht. Wollen Sie das wirklich?\')==false) return false;" class="icon"><img src="'.$pfad.'icons/delete.png" alt="loeschen" /></a>
					</td></tr>';
			}
			echo '</table>';
		}
		/*
	?>
	</div>
</body>
</html>*/ ?>
