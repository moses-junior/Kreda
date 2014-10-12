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

$titelleiste="Buch bearbeiten";
$pfad="../";
include $pfad."header.php";
include $pfad."funktionen.php";

if (!proofuser("buch", $_GET["buch_id"]))
	die("Sie sind hierzu nicht berechtigt.");

if ($_GET["eintragen"]=="true") {
	db_conn_and_sql("UPDATE `buch` SET `aktiv`=".leer_NULL($_POST["aktiv"]).", `name`=".apostroph_bei_bedarf($_POST['name']).", `kuerzel`=".apostroph_bei_bedarf($_POST['kuerzel']).", `fach`=".injaway($_POST['fach']).", `schulart`=".injaway($_POST['schulart']).", `isbn`=".apostroph_bei_bedarf($_POST['isbn']).", `untertitel`=".apostroph_bei_bedarf($_POST['untertitel']).",`verlag`=".apostroph_bei_bedarf($_POST['verlag'])." WHERE `id`=".$_GET["buch_id"]);
	//alle loeschen
	db_conn_and_sql("DELETE FROM `buch_klassenstufe` WHERE `buch`=".injaway($_GET["buch_id"]));
	foreach ($_POST["klassenstufe"] as $i) db_conn_and_sql("INSERT INTO `buch_klassenstufe` (`buch`, `klassenstufe`) VALUES
		(".injaway($_GET["buch_id"]).", ".$i.");");
 }
$fach=db_conn_and_sql("SELECT * FROM `faecher` WHERE `faecher`.`user`=0 OR `faecher`.`user`=".$_SESSION['user_id']." ORDER BY `faecher`.`kuerzel`");
$buch=db_conn_and_sql("SELECT * FROM `buch`, `buch_klassenstufe` WHERE `buch_klassenstufe`.`buch`=`buch`.`id` AND `buch`.`id`=".injaway($_GET["buch_id"])." ORDER BY `buch_klassenstufe`.`klassenstufe`");
?>
  <body>
	<div id="mf">
		<ul class="r">
			<li><a href="javascript:window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
	<div class="inhalt">
	<form action="<?php echo $pfad; ?>formular/buch.php?buch_id=<?php echo $_GET["buch_id"]; ?>&amp;eintragen=true" method="post" accept-charset="ISO-8859-1">
      <fieldset><legend>Buch bearbeiten</legend>
		<label for="aktiv">aktiv:</label> <input type="checkbox" value="1" name="aktiv"<?php if (sql_result($buch,0,"buch.aktiv")) echo ' checked="checked"'; ?> /><br />
		<label for="name">Titel<em>*</em>:</label> <input type="text" name="name" size="50" maxlength="100" value="<?php echo html_umlaute(sql_result($buch,0,"buch.name")); ?>" /><br />
		<label for="untertitel">Untertitel:</label> <input type="text" name="untertitel" size="50" maxlength="200" value="<?php echo html_umlaute(sql_result($buch,0,"buch.untertitel")); ?>" /><br />
		<label for="kuerzel">K&uuml;rzel<em>*</em>:</label> <input type="text" name="kuerzel" size="5" maxlength="10" value="<?php echo html_umlaute(sql_result($buch,0,"buch.kuerzel")); ?>" /><br />
		<label for="klassenstufe[]">Klassenstufe(n)<em>*</em>:</label>
         <?php $zaehler=0; for($i=1;$i<=13;$i++) { ?>
              <input type="checkbox" id="klassenstufe_<?php echo $i; ?>" name="klassenstufe[]" value="<?php echo $i; ?>" <?php if (@sql_result($buch,$zaehler,"buch_klassenstufe.klassenstufe")==$i) {echo ' checked="checked"'; $zaehler++; } ?> /> <?php echo $i; ?>
         <?php } ?>
		<br />
		<label for="fach">Fach<em>*</em>:</label> <select name="fach"><?php for($i=0;$i<sql_num_rows ( $fach );$i++) { ?>
              <option value="<?php echo @sql_result ( $fach, $i, 'faecher.id' ); ?>"<?php if (sql_result($buch,0,"buch.fach")==@sql_result ( $fach, $i, 'faecher.id' )) echo ' selected="selected"'; ?>><?php echo @sql_result ( $fach, $i, 'faecher.kuerzel' ); ?></option>
		<?php } ?>
		</select>
		<label for="schulart">Schulart<em>*</em>:</label> 
		<select name="schulart"><?php $schulart=db_conn_and_sql("SELECT * FROM `schulart`"); for($i=0;$i<sql_num_rows ( $schulart );$i++) { ?>
              <option value="<?php echo @sql_result ( $schulart, $i, 'schulart.id' ); ?>"<?php if (sql_result($buch,0,"buch.schulart")==@sql_result ( $schulart, $i, 'schulart.id' )) echo ' selected="selected"'; ?>><?php echo @sql_result ( $schulart, $i, 'schulart.kuerzel' ); ?></option>
		<?php } ?>
		</select><br />
		<label for="isbn">ISBN:</label> <input type="text" name="isbn" size="10" maxlength="20" value="<?php echo html_umlaute(sql_result($buch,0,"buch.isbn")); ?>" /><br />
		<label for="verlag">Verlag:</label> <input type="text" name="verlag" size="10" maxlength="200" value="<?php echo html_umlaute(sql_result($buch,0,"buch.verlag")); ?>" /><br />
        <button onclick="fenster('<?php echo $pfad; ?>formular/buch_delete.php?id=<?php echo $_GET["buch_id"]; ?>', ''); return false;"><img src="<?php echo $pfad; ?>icons/delete.png" alt="delete" /> l&ouml;schen</button>
        <button style="float: right;" onclick="auswertung=new Array(new Array(0, 'name','nicht_leer'), new Array(0, 'kuerzel','nicht_leer')); for(i=1;i&lt;14;i++) {if (document.getElementById('klassenstufe_'+i).checked) ausfuehren=true;} if (ausfuehren) pruefe_formular(auswertung);"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
      </fieldset>
      </form>
	<p>  
	<?php
		$buch=db_conn_and_sql("SELECT *, GROUP_CONCAT(klasse.einschuljahr) AS klassenjahr
			FROM buch_aufgabe, buch,
				aufgabe LEFT JOIN aufgabe_abschnitt ON aufgabe_abschnitt.aufgabe=aufgabe.id
				LEFT JOIN abschnitt ON aufgabe_abschnitt.abschnitt=abschnitt.id
				LEFT JOIN abschnittsplanung ON abschnittsplanung.abschnitt=aufgabe_abschnitt.abschnitt
				LEFT JOIN plan ON abschnittsplanung.plan=plan.id
				LEFT JOIN fach_klasse ON plan.fach_klasse=fach_klasse.id AND fach_klasse.anzeigen=1
				LEFT JOIN klasse ON fach_klasse.klasse=klasse.id
			WHERE buch_aufgabe.aufgabe=aufgabe.id
				AND buch_aufgabe.buch=buch.id
				AND buch.id=".injaway($_GET["buch_id"])."
			GROUP BY buch_aufgabe.seite, buch_aufgabe.nummer
			ORDER BY buch.fach, buch.name, buch_aufgabe.seite, buch_aufgabe.nummer");
		$jahr=db_conn_and_sql("SELECT aktuelles_schuljahr FROM benutzer WHERE id=".$_SESSION['user_id']);
		$aktuelles_jahr=sql_result($jahr,0,"aktuelles_schuljahr");
		
		function klasse ($einschuljahr,$aktuelles_jahr) {
			if ($einschuljahr>1900)
				return $aktuelles_jahr-$einschuljahr+1;
			else
				return "";
		}
		function abschnitt_da($abschnitt_id, $block) {
			if ($abschnitt_id>0)
				return '<a href="./abschnitt_bearb.php?welcher='.$abschnitt_id.'">x</a>';
			else
				return '';
		}
		
		echo '<table class="tabelle" cellspacing="0"><tr><th>Buch</th><th>Seite</th><th>Nr.</th><th>Abschn.</th><th>Klasse</th></tr>';
		for ($i=0;$i<sql_num_rows($buch);$i++) {
			echo '
				<tr><td>'.html_umlaute(sql_result($buch,$i,"buch.kuerzel")).'</td>
				<td>'.html_umlaute(sql_result($buch,$i,"buch_aufgabe.seite")).'</td>
				<td>'.html_umlaute(sql_result($buch,$i,"buch_aufgabe.nummer")).'</td>
				<td>'.abschnitt_da(@sql_result($buch,$i,"aufgabe_abschnitt.abschnitt"), @sql_result($buch,$i,"abschnitt.block")).'</td>
				<td>'.klasse(@sql_result($buch,$i,"klassenjahr"),$aktuelles_jahr).html_umlaute(@sql_result($buch,$i,"klasse.endung")).'</td></tr>';
		}
		echo '</table>'; ?>
		</p>
		</div>
	</body>
</html>
