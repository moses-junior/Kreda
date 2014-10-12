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
	$auswertung_faecher=db_conn_and_sql("SELECT DISTINCT faecher.id, faecher.kuerzel
		FROM stichtagsnote
			LEFT JOIN fach_klasse ON fach_klasse.id=stichtagsnote.fach_klasse
			LEFT JOIN klasse ON klasse.id=fach_klasse.klasse
			LEFT JOIN faecher ON faecher.id=fach_klasse.fach
			LEFT JOIN schueler ON schueler.id=stichtagsnote.schueler
		WHERE stichtagsnote.rahmen=".injaway($_GET["auswertung"])."
		ORDER BY faecher.kuerzel");
	$faecher=array();
	$faecher_header=array();
	while ($auswertung=sql_fetch_assoc($auswertung_faecher)) {
		$faecher_header[]=$auswertung["kuerzel"];
		$faecher[]=array("id"=>$auswertung["id"], "name"=>$auswertung["name"], "kuerzel"=>$auswertung["kuerzel"]);
	}
	
	$auswertung_result=db_conn_and_sql("SELECT *, schueler.id AS s_id
		FROM stichtagsnote
			LEFT JOIN fach_klasse ON fach_klasse.id=stichtagsnote.fach_klasse
			LEFT JOIN klasse ON klasse.id=fach_klasse.klasse
			LEFT JOIN faecher ON faecher.id=fach_klasse.fach
			LEFT JOIN schueler ON schueler.id=stichtagsnote.schueler
		WHERE stichtagsnote.rahmen=".injaway($_GET["auswertung"])."
		ORDER BY klasse.einschuljahr DESC, klasse.endung, schueler.position, schueler.name, schueler.vorname, faecher.kuerzel");
	echo '<table class="tabelle">';
	echo '<tr><th></th><th>'.implode('</th><th>', $faecher_header).'</th></tr>';
	$s_id_old=0;
	$sn_zaehler=0;
	while ($auswertung=sql_fetch_assoc($auswertung_result) or $sn_zaehler==sql_num_rows($auswertung_result)) {
		if ($s_id_old!=$auswertung["s_id"]) {
			if ($s_id_old!=0) {
				while ($i_fach<count($faecher)) {
					$einzeleintraege[]="";
					$i_fach++;
				}
				echo '<tr><td>'.$schueler_kennzeichnung.'</td><td>'.implode("</td><td>", $einzeleintraege).'</td></tr>';
			}
			$schueler_kennzeichnung=($aktuelles_jahr-$auswertung["einschuljahr"]+1).' '.$auswertung["endung"].', '.$auswertung["name"].', '.$auswertung["vorname"];
			$i_fach=0;
			$einzeleintraege=array();
		}
		switch ($auswertung["tendenz"]) {
			case 1: $tend="+"; break;
			case -1: $tend="-"; break;
			default: $tend="";
		}
		while ($auswertung["fach"]!=$faecher[$i_fach]["id"]) {
			$einzeleintraege[]="";
			$i_fach++;
		}
		$einzeleintraege[]=$auswertung["kuerzel"].": ".$auswertung["wert"].$tend;
		$i_fach++;
		$s_id_old=$auswertung["s_id"];
		$sn_zaehler++;
	}
	echo '</table>';
}

// Speichern eines neuen Rahmens
if($_GET["eintragen"]=="true" and userrigths("stichtagsnoten", $schule)==2) {
	$neue_fks=0;
	$id=db_conn_and_sql("INSERT INTO stichtagsnote_rahmen (name, tendenz, halbjahresnote, bearbeitung_ab, bearbeitung_bis, beschreibung, schule) VALUES (".apostroph_bei_bedarf($_POST["name"]).", ".leer_NULL($_POST["tendenz"]).", ".leer_NULL($_POST["halbjahresnote"]).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["bearbeitung_ab"])).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["bearbeitung_bis"])).", ".apostroph_bei_bedarf($_POST["beschreibung"]).", ".$schule.");");
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
$stichtagsnoten=db_conn_and_sql("SELECT DISTINCT stichtagsnote_rahmen.*, klasse.id AS klasse_id, klasse.endung, klasse.einschuljahr
	FROM stichtagsnote_rahmen
		LEFT JOIN stichtagsnote_fk ON stichtagsnote_rahmen.id=stichtagsnote_fk.rahmen
		LEFT JOIN fach_klasse ON stichtagsnote_fk.fach_klasse=fach_klasse.id
		LEFT JOIN klasse ON fach_klasse.klasse=klasse.id AND klasse.schule=".$schule."
	WHERE stichtagsnote_rahmen.bearbeitung_ab>".apostroph_bei_bedarf($start_ende["start"])." AND stichtagsnote_rahmen.bearbeitung_ab<".apostroph_bei_bedarf($start_ende["ende"])."
	ORDER BY stichtagsnote_rahmen.bearbeitung_ab DESC, klasse.einschuljahr DESC, klasse.endung");
	
	?>
<form action="<?php echo $pfad.$formularziel."&amp;eintragen="; if ($_GET["bearbeiten"]>0) echo "bearbeiten"; else echo "true"; ?>" method="post">
	<fieldset>
		<legend><?php if ($_GET["bearbeiten"]>0) echo "Stichtagsnote bearbeiten"; else echo "neue Stichtagsnote"; ?></legend>
		<?php if ($_GET["bearbeiten"]>0) {
			echo '<input type="hidden" name="rahmen" value="'.injaway($_GET["bearbeiten"]).'" />';
			$meine_stn=db_conn_and_sql("SELECT * FROM stichtagsnote_rahmen WHERE id=".injaway($_GET["bearbeiten"]));
			$meine_stn=sql_fetch_assoc($meine_stn);
		}
		?>

		<label for="name">Name<em>*</em>:</label> <input type="text" name="name" placeholder="z.B. Halbjahreszensuren <?php echo date("Y")."/".(date("y")+1); ?> Sek. I" required="required" size="25" maxlength="100"<?php if ($_GET["bearbeiten"]>0) echo ' value="'.$meine_stn["name"].'"'; ?>/><br />
		<label for="tendenz">Tendenz erlaubt:</label> <input type="checkbox" name="tendenz" value="1" title="z.B. 3-, 4+, ..." <?php if ($_GET["bearbeiten"]>0 and $meine_stn["tendenz"]==1) echo 'checked="checked"'; ?>/><br />
		<label for="halbjahresnote">Halbjahreszensur:</label> <input type="checkbox" name="halbjahresnote" value="1" title="Ist dies aktiviert, wird der Durchschnitt des ersten Halbjahres dem entsprechenden Fachlehrer angegeben." <?php if ($_GET["bearbeiten"]>0 and $meine_stn["halbjahresnote"]==1) echo 'checked="checked"'; ?>/><br />
		<label for="bearbeitung_ab">Zeitraum<em>*</em>:</label> <input type="text" class="datepicker" id="bearbeitung_ab" name="bearbeitung_ab" size="8" maxlength="10" <?php if ($_GET["bearbeiten"]>0) echo ' value="'.datum_strich_zu_punkt($meine_stn["bearbeitung_ab"]).'"'; ?> />
		- <input type="text" class="datepicker" id="bearbeitung_bis" name="bearbeitung_bis" size="8" maxlength="10" <?php if ($_GET["bearbeiten"]>0) echo ' value="'.datum_strich_zu_punkt($meine_stn["bearbeitung_bis"]).'"'; ?> /><br />
		<label for="beschreibung">Beschreibung:</label> <textarea name="beschreibung"><?php if ($_GET["bearbeiten"]>0) echo $meine_stn["beschreibung"]; ?></textarea><br />
		<label style="float: left;">Klassen<em>*</em>:</label> <p style="margin-left: 120px; width: auto;">
		<?php $klassen=db_conn_and_sql("SELECT * FROM klasse WHERE klasse.schule=".$schule." AND klasse.einschuljahr>".($aktuelles_jahr-12)." ORDER BY einschuljahr DESC, endung");
		if ($_GET["bearbeiten"]>0) {
			$klassen_vorauswahl=db_conn_and_sql("SELECT DISTINCT klasse.id AS klasse_id
				FROM stichtagsnote_fk, fach_klasse, klasse
				WHERE stichtagsnote_fk.rahmen=".injaway($_GET["bearbeiten"])."
					AND stichtagsnote_fk.fach_klasse=fach_klasse.id
					AND fach_klasse.klasse=klasse.id
					AND klasse.schule=".$schule."
				ORDER BY klasse.einschuljahr DESC, klasse.endung");
			$kl_auswahl=sql_fetch_assoc($klassen_vorauswahl);
		}
		$vorige_klassenstufe=0;
		$i=0;
		while ($klasse=sql_fetch_assoc($klassen)) {
			if ($vorige_klassenstufe!=$klasse["einschuljahr"]) {
				if ($vorige_klassenstufe!=0)
					echo '<br />';
				$vorige_klassenstufe=$klasse["einschuljahr"];
			}
			echo ' <input type="checkbox" id="klasse_'.$i.'" name="klasse_'.$i.'" value="'.$klasse["id"].'"';
			if ($_GET["bearbeiten"]>0 and $kl_auswahl["klasse_id"]==$klasse["id"]) {
				$kl_auswahl=sql_fetch_assoc($klassen_vorauswahl);
				echo ' checked="checked"';
			}
			echo ' /><label for="klasse_'.$i.'" style="width: auto;">'.($aktuelles_jahr-$klasse["einschuljahr"]+1)." ".$klasse["endung"].'</label>'."\n";
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
			echo '$("#klasse_'.$x.'").button(); '; ?>
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
