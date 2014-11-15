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

$user=new user();
$schule=$user->my["letzte_schule"];

// Uebersicht eines einzelnen Rahmens
if ($_GET["auswertung"]>0) {
	// TODO Auswertung
}

// Speichern eines neuen Rahmens
if($_GET["eintragen"]=="true" and userrigths("zeugnisrahmen", $schule)==2) {
	$neue_fks=0;
//  . +id
//  . -schule
//  . schuljahr
//  S -vorlage
//  S -bildungsgang
//  T titel
//  T schulleiter -> varchar
//  C tendenz
//  A bemerkungen_fuer_lehrer
//  D bearbeitung_ab
//  D bearbeitung_bis
//  BB (-)kopfnoten_zuordnung 5|14 (rahmen); knr_i (value
//  S  -stichtagsnoten_zuordnung 26 (rahmen)
	$id=db_conn_and_sql("INSERT INTO zeugnis_rahmen (name, tendenz, halbjahresnote, bearbeitung_ab, bearbeitung_bis, beschreibung, schule) VALUES (".apostroph_bei_bedarf($_POST["name"]).", ".leer_NULL($_POST["tendenz"]).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["bearbeitung_ab"])).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["bearbeitung_bis"])).", ".apostroph_bei_bedarf($_POST["beschreibung"]).", ".$schule.");");
	echo '<div class="hinweis">';
	for ($i=0; $i<100; $i++)
		if (isset($_POST["klasse_".$i])) {
			$fks=db_conn_and_sql("SELECT * FROM lehrauftrag
				LEFT JOIN klasse ON lehrauftrag.klasse=klasse.id
				LEFT JOIN faecher ON lehrauftrag.fach=faecher.id
				LEFT JOIN users ON lehrauftrag.user=users.user_id
				WHERE lehrauftrag.schuljahr=".$aktuelles_jahr." AND lehrauftrag.klasse=".injaway($_POST["klasse_".$i]));
			while ($fk=sql_fetch_assoc($fks)) {
				if ($fk["fach_klasse"]>0) {
					db_conn_and_sql("INSERT INTO stichtagsnote_fk (rahmen, fach_klasse, user, fertig) VALUES (".$id.", ".$fk["fach_klasse"].", ".$fk["user_id"].", NULL);");
					$neue_fks++;
				}
				else
					echo 'Lehrauftrag '.$fk["kuerzel"]." ".($aktuelles_jahr-$fk["einschuljahr"]+1)." ".$fk["endung"]." (".$fk["user_name"].") ist ohne Eintrag.<br />";
			}
	}
	echo 'Es wurden '.$neue_fks.' Fach-Klasse-Kombinationen beauftragt.</div>';
}

// Bearbeitung eines Stichtagsnotenrahmens speichern
if($_GET["eintragen"]=="bearbeiten" and userrigths("stichtagsnotenrahmen", injaway($_POST["rahmen"]))==2) {
	$neue_fks=0;
	$aktualisierte_fks=0;
	db_conn_and_sql("UPDATE stichtagsnote_rahmen SET name=".apostroph_bei_bedarf($_POST["name"]).", tendenz=".leer_NULL($_POST["tendenz"]).", halbjahresnote=".leer_NULL($_POST["halbjahresnote"]).", bearbeitung_ab=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["bearbeitung_ab"])).", bearbeitung_bis=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["bearbeitung_bis"])).", beschreibung=".apostroph_bei_bedarf($_POST["beschreibung"])." WHERE id=".injaway($_POST["rahmen"]));
	
	echo '<div class="hinweis">';
	
	for ($i=0; $i<100; $i++)
		if (isset($_POST["klasse_".$i])) { // es werden absichtlich keine Stichtagsauftraege geloescht
			// suche alle Lehrauftraege fuer diese Klasse
			$fks=db_conn_and_sql("SELECT lehrauftrag.*, stichtagsnote_fk.rahmen, klasse.*, users.user_name, faecher.kuerzel, users.user_id
				FROM lehrauftrag
					LEFT JOIN klasse ON lehrauftrag.klasse=klasse.id
					LEFT JOIN faecher ON lehrauftrag.fach=faecher.id
					LEFT JOIN users ON lehrauftrag.user=users.user_id
					LEFT JOIN stichtagsnote_fk ON lehrauftrag.fach_klasse=stichtagsnote_fk.fach_klasse AND stichtagsnote_fk.rahmen=".injaway($_POST["rahmen"])."
				WHERE lehrauftrag.schuljahr=".$aktuelles_jahr."
					AND lehrauftrag.klasse=".injaway($_POST["klasse_".$i]));
			while ($fk=sql_fetch_assoc($fks)) {
				if ($fk["fach_klasse"]>0) {
					if ($fk["rahmen"]>0) {
						$aktualisierte_fks++;
					}
					else {
						db_conn_and_sql("INSERT INTO stichtagsnote_fk (rahmen, fach_klasse, user, fertig) VALUES (".injaway($_POST["rahmen"]).", ".$fk["fach_klasse"].", ".$fk["user_id"].", NULL);");
						$neue_fks++;
					}
				}
				else
					echo 'Lehrauftrag '.$fk["kuerzel"]." ".($aktuelles_jahr-$fk["einschuljahr"]+1)." ".$fk["endung"]." (".$fk["user_name"].") ist ohne Eintrag.<br />";
			}
	}
	echo 'Es wurden '.$neue_fks.' neue Fach-Klasse-Kombinationen beauftragt ('.$aktualisierte_fks.' Auftr&auml;ge blieben unber&uuml;hrt).</div>';
}

if($_GET["eintragen"]=="loeschen" and userrigths("stichtagsnotenrahmen", injaway($_GET["loeschen"]))==2) {
	// einzelnoten loeschen
	db_conn_and_sql("DELETE FROM stichtagsnote WHERE rahmen=".injaway($_GET["loeschen"]));
	// fk-Zuordnungen loeschen
	db_conn_and_sql("DELETE FROM stichtagsnote_fk WHERE rahmen=".injaway($_GET["loeschen"]));
	// rahmen loeschen
	db_conn_and_sql("DELETE FROM stichtagsnote_rahmen WHERE id=".injaway($_GET["loeschen"])." LIMIT 1;");
}

$start_ende=schuljahr_start_ende($aktuelles_jahr, $schule);
$stichtagsnoten=db_conn_and_sql("SELECT zeugnis_rahmen.*
	FROM zeugnis_rahmen
		LEFT JOIN stichtagsnote_fk ON stichtagsnote_rahmen.id=stichtagsnote_fk.rahmen
		LEFT JOIN fach_klasse ON stichtagsnote_fk.fach_klasse=fach_klasse.id
		LEFT JOIN klasse ON fach_klasse.klasse=klasse.id AND klasse.schule=".$schule."
	WHERE stichtagsnote_rahmen.bearbeitung_ab>".apostroph_bei_bedarf($start_ende["start"])." AND stichtagsnote_rahmen.bearbeitung_ab<".apostroph_bei_bedarf($start_ende["ende"])."
	ORDER BY stichtagsnote_rahmen.bearbeitung_ab DESC, klasse.einschuljahr DESC, klasse.endung");
	
	?>
<form action="<?php echo $pfad.$formularziel."&amp;eintragen="; if ($_GET["bearbeiten"]>0) echo "bearbeiten"; else echo "true"; ?>" method="post">
	<fieldset>
		<legend><?php if ($_GET["bearbeiten"]>0) echo "Zeugnisauftrag bearbeiten"; else echo "neuer Zeugnisauftrag"; ?></legend>
		<?php if ($_GET["bearbeiten"]>0) {
			echo '<input type="hidden" name="rahmen" value="'.injaway($_GET["bearbeiten"]).'" />';
			$mein_zat=db_conn_and_sql("SELECT * FROM zeugnis_rahmen WHERE id=".injaway($_GET["bearbeiten"]));
			$mein_zat=sql_fetch_assoc($mein_zat);
		}
		?>

		<label for="vorlage">Vorlage<em>*</em>:</label> <select name="vorlage"><option value="1">VL 1</option></select><br />
		<label for="bildungsgang">Bildungsgang<em>*</em>:</label> <select name="bildungsgang"><option value="1">Abitur</option></select><br />
		<label for="titel">Titel<em>*</em>:</label> <input type="text" name="titel" placeholder="z.B. Jahreszeugnis der Grundschule" required="required" size="25" maxlength="100"<?php if ($_GET["bearbeiten"]>0) echo ' value="'.$mein_zat["titel"].'"'; ?>/><br />
		<label for="schulleiter">Schulleiter<em>*</em>:</label> <input type="text" name="schulleiter" required="required" size="25" maxlength="100"<?php if ($_GET["bearbeiten"]>0) echo ' value="'.$mein_zat["schulleiter"].'"'; else echo 'SL lesen'; ?>/><br />
		<label for="tendenz">Tendenz erlaubt:</label> <input type="checkbox" name="tendenz" value="1" title="z.B. 3-, 4+, ..." <?php if ($_GET["bearbeiten"]>0 and $mein_zat["tendenz"]==1) echo 'checked="checked"'; ?>/><br />
		<label for="bearbeitung_ab">Zeitraum<em>*</em>:</label> <input type="text" class="datepicker" id="bearbeitung_ab" name="bearbeitung_ab" size="8" maxlength="10" <?php if ($_GET["bearbeiten"]>0) echo ' value="'.datum_strich_zu_punkt($mein_zat["bearbeitung_ab"]).'"'; ?> />
		- <input type="text" class="datepicker" id="bearbeitung_bis" name="bearbeitung_bis" size="8" maxlength="10" <?php if ($_GET["bearbeiten"]>0) echo ' value="'.datum_strich_zu_punkt($mein_zat["bearbeitung_bis"]).'"'; ?> /><br />
		<label for="bemerkungen_fuer_lehrer">Bemerkungen:</label> <textarea name="bemerkungen_fuer_lehrer" title="Bemerkungen f&uuml;r Zeugnisersteller (Klassenlehrer)"><?php if ($_GET["bearbeiten"]>0) echo $mein_zat["bemerkungen_fuer_lehrer"]; ?></textarea><br />
		<label for="stichtagsnoten-zuordnung">Stichtagsnoten<em>*</em>:</label> <select name="stichtagsnoten_zuordnung">
		<?php $stichtagsnote_rahmen=db_conn_and_sql("SELECT * FROM stichtagsnote_rahmen WHERE stichtagsnote_rahmen.schule=".$schule." ORDER BY bearbeitung_bis");
		while ($stnr=sql_fetch_assoc($stichtagsnote_rahmen))
			echo '<option value="'.$stnr["id"].'">'.$stnr["name"].'</option>';
		?></select><br />
		
		<label style="float: left;">Kopfnotenauftr.<em>*</em>:</label> <p style="margin-left: 120px; width: auto;">
		<?php $kopfnoten_rahmen=db_conn_and_sql("SELECT * FROM kopfnote_rahmen WHERE kopfnote_rahmen.schule=".$schule." ORDER BY bearbeitung_bis");
		if ($_GET["bearbeiten"]>0) {
			$knr_vorauswahl=db_conn_and_sql("SELECT DISTINCT klasse.id AS klasse_id
				FROM stichtagsnote_fk, fach_klasse, klasse
				WHERE stichtagsnote_fk.rahmen=".injaway($_GET["bearbeiten"])."
					AND stichtagsnote_fk.fach_klasse=fach_klasse.id
					AND fach_klasse.klasse=klasse.id
					AND klasse.schule=".$schule."
				ORDER BY klasse.einschuljahr DESC, klasse.endung");
			$knr_auswahl=sql_fetch_assoc($klassen_vorauswahl);
		}
		$vorige_klassenstufe=0;
		$i=0;
		while ($knr=sql_fetch_assoc($kopfnoten_rahmen)) {
			echo ' <input type="checkbox" id="knr_'.$i.'" name="knr_'.$i.'" value="'.$knr["id"].'"';
			if ($_GET["bearbeiten"]>0 and $knr_vorauswahl["knr_id"]==$knr["id"]) {
				$knr_auswahl=sql_fetch_assoc($knr_vorauswahl);
				echo ' checked="checked"';
			}
			echo ' /><label for="knr_'.$i.'" style="width: auto;">'.$knr["name"].'</label>'."\n";
			$i++;
		}
		?>
		</p>
		<input type="submit" value="<?php if ($_GET["bearbeiten"]>0) echo "&auml;ndern"; else echo "hinzuf&uuml;gen"; ?>" />
	</fieldset>
</form>
<script>
	$(function() {
		<?php for ($x=0; $x<$i; $x++)
			echo '$("#knr_'.$x.'").button(); '; ?>
		$("#bearbeitung_ab").datepicker("option", "minDate", new Date('<?php echo $start_ende["start"]; ?>'));
		$("#bearbeitung_ab").datepicker("option", "maxDate", new Date('<?php echo $start_ende["ende"]; ?>'));
		$("#bearbeitung_bis").datepicker("option", "minDate", new Date('<?php echo $start_ende["start"]; ?>'));
		$("#bearbeitung_bis").datepicker("option", "maxDate", new Date('<?php echo $start_ende["ende"]; ?>'));
	});
</script>

<h3>Existierende Stichtagsnoten</h3>
<table class="tabelle">
	<tr><th>Name</th><th>Klassen</th><th>Aktionen</th></tr>
<?php
	$stn_old_id=0;
	$stn_array=array();
	while ($stn=sql_fetch_assoc($stichtagsnoten)) {
		if ($stn["id"]!=$stn_old_id) {
			/*if ($stn_old_id!=0)
				echo '</td><td> <a href="'.$formularziel.'&amp;bearbeiten='.$stn_old_id.'" class="icon" title="bearbeiten"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
					<a href="'.$formularziel.'&amp;eintragen=loeschen&amp;loeschen='.$stn_old_id.'" onclick="if (!confirm(\'Wollen Sie diese Stichtagsnote wirklich l&ouml;schen?\')) return false;" class="icon" title="l&ouml;schen"><img src="'.$pfad.'icons/delete.png" alt="loeschen" /></a>
					</td></tr>';*/
			$stn_array[$stn["id"]]=array("id"=>$stn["id"], "rahmen"=>'<tr><td>'.html_umlaute($stn["name"]).' ('.datum_strich_zu_punkt_uebersichtlich($stn["bearbeitung_ab"], true, false).' - '.datum_strich_zu_punkt_uebersichtlich($stn["bearbeitung_bis"], true, false).')</td><td>'."\n", "klassen"=>array());
			$stn_old_id=$stn["id"];
		}
		
		if ($stn["einschuljahr"]>2000)
			$stn_array[$stn["id"]]["klassen"][] = ($aktuelles_jahr-$stn["einschuljahr"]+1)." ".$stn["endung"];
	}
	if ($stn_old_id!=0)
		foreach($stn_array as $stn) {
			$alle_fks=db_conn_and_sql("SELECT DISTINCT COUNT(stichtagsnote_fk.fach_klasse) AS fks FROM stichtagsnote_fk WHERE stichtagsnote_fk.rahmen=".$stn["id"]);
			$alle_fks=sql_fetch_assoc($alle_fks);
			$fertige_fks=db_conn_and_sql("SELECT DISTINCT COUNT(stichtagsnote_fk.fach_klasse) AS fks FROM stichtagsnote_fk WHERE stichtagsnote_fk.fertig IS NOT NULL AND stichtagsnote_fk.rahmen=".$stn["id"]);
			$fertige_fks=sql_fetch_assoc($fertige_fks);
			echo $stn["rahmen"];
			echo implode(", ", $stn["klassen"])." (".$fertige_fks["fks"]."/".$alle_fks["fks"].")";
			echo '</td><td> <a href="'.$formularziel.'&amp;bearbeiten='.$stn["id"].'" class="icon" title="bearbeiten"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
				<a href="'.$formularziel.'&amp;eintragen=loeschen&amp;loeschen='.$stn["id"].'" onclick="if (!confirm(\'Wollen Sie diese Stichtagsnote wirklich l&ouml;schen?\')) return false;" class="icon" title="l&ouml;schen"><img src="'.$pfad.'icons/delete.png" alt="loeschen" /></a>
				<a href="'.$formularziel.'&amp;auswertung='.$stn["id"].'" class="icon" title="Auswertung ansehen"><img src="'.$pfad.'icons/statistik.png" alt="auswertung" /></a>
				</td></tr>';
		}
?>
</table>
