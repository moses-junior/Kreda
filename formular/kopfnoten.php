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


// Speichern eines neuen Rahmens
if($_GET["eintragen"]=="true" and userrigths("kopfnoten", $schule)==2) {
	$neue_fks=0;
	$id=db_conn_and_sql("INSERT INTO kopfnote_rahmen (name, bearbeitung_ab, bearbeitung_bis, art, beschreibung, schule, faktor) VALUES (".apostroph_bei_bedarf($_POST["name"]).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["bearbeitung_ab"])).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["bearbeitung_bis"])).", ".leer_NULL($_POST["art"]).", ".apostroph_bei_bedarf($_POST["beschreibung"]).", ".$schule.", ".leer_NULL($_POST["faktor"]).");");
	
	for ($i=1; $i<10; $i++) {
		if ($_POST["kategorie_".$i]!="-")
			db_conn_and_sql("INSERT INTO kopfnotenkat_rahmen (kn_kat, rahmen, position) VALUES (".injaway($_POST["kategorie_".$i]).", ".$id.", ".$i.");");
	}
	
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
					db_conn_and_sql("INSERT INTO kopfnote_fk (rahmen, fach_klasse, user, fertig) VALUES (".$id.", ".$fk["fach_klasse"].", ".$fk["user_id"].", NULL);");
					$neue_fks++;
				}
				else
					echo 'Lehrauftrag '.$fk["kuerzel"]." ".($aktuelles_jahr-$fk["einschuljahr"]+1)." ".$fk["endung"]." (".$fk["user_name"].") ist ohne Eintrag.<br />";
			}
	}
	echo 'Es wurden '.$neue_fks.' Fach-Klasse-Kombinationen beauftragt.</div>';
}

// Bearbeitung eines kopfnotenrahmens speichern
if($_GET["eintragen"]=="bearbeiten" and userrigths("kopfnotenrahmen", injaway($_POST["rahmen"]))==2) {
	$neue_fks=0;
	$aktualisierte_fks=0;
	db_conn_and_sql("UPDATE kopfnote_rahmen SET name=".apostroph_bei_bedarf($_POST["name"]).", bearbeitung_ab=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["bearbeitung_ab"])).", bearbeitung_bis=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["bearbeitung_bis"])).", art=".leer_NULL($_POST["art"]).", beschreibung=".apostroph_bei_bedarf($_POST["beschreibung"]).", schule=".$schule.", faktor=".leer_NULL($_POST["faktor"])." WHERE id=".injaway($_POST["rahmen"]));
	
	echo '<div class="hinweis">';
	
	for ($i=0; $i<100; $i++)
		if (isset($_POST["klasse_".$i])) { // es werden absichtlich keine kopfauftraege geloescht
			// suche alle Lehrauftraege fuer diese Klasse
			$fks=db_conn_and_sql("SELECT lehrauftrag.*, kopfnote_fk.rahmen, klasse.*, users.user_name, faecher.kuerzel, users.user_id
				FROM lehrauftrag
					LEFT JOIN klasse ON lehrauftrag.klasse=klasse.id
					LEFT JOIN faecher ON lehrauftrag.fach=faecher.id
					LEFT JOIN users ON lehrauftrag.user=users.user_id
					LEFT JOIN kopfnote_fk ON lehrauftrag.fach_klasse=kopfnote_fk.fach_klasse AND kopfnote_fk.rahmen=".injaway($_POST["rahmen"])."
				WHERE lehrauftrag.schuljahr=".$aktuelles_jahr."
					AND lehrauftrag.klasse=".injaway($_POST["klasse_".$i]));
			while ($fk=sql_fetch_assoc($fks)) {
				if ($fk["fach_klasse"]>0) {
					if ($fk["rahmen"]>0) {
						$aktualisierte_fks++;
					}
					else {
						db_conn_and_sql("INSERT INTO kopfnote_fk (rahmen, fach_klasse, user, fertig) VALUES (".injaway($_POST["rahmen"]).", ".$fk["fach_klasse"].", ".$fk["user_id"].", NULL);");
						$neue_fks++;
					}
				}
				else
					echo 'Lehrauftrag '.$fk["kuerzel"]." ".($aktuelles_jahr-$fk["einschuljahr"]+1)." ".$fk["endung"]." (".$fk["user_name"].") ist ohne Eintrag.<br />";
			}
	}
	echo 'Es wurden '.$neue_fks.' neue Fach-Klasse-Kombinationen beauftragt ('.$aktualisierte_fks.' Auftr&auml;ge blieben unber&uuml;hrt).</div>';
}

if($_GET["eintragen"]=="loeschen" and userrigths("kopfnotenrahmen", injaway($_GET["loeschen"]))==2) {
	// einzelnoten loeschen
	db_conn_and_sql("DELETE FROM kopfnote WHERE rahmen=".injaway($_GET["loeschen"]));
	// fk-Zuordnungen loeschen
	db_conn_and_sql("DELETE FROM kopfnote_fk WHERE rahmen=".injaway($_GET["loeschen"]));
	// rahmen loeschen
	db_conn_and_sql("DELETE FROM kopfnote_rahmen WHERE id=".injaway($_GET["loeschen"])." LIMIT 1;");
}

$start_ende=schuljahr_start_ende($aktuelles_jahr, $schule);
$kopfnoten=db_conn_and_sql("SELECT DISTINCT kopfnote_rahmen.*, klasse.id AS klasse_id, klasse.endung, klasse.einschuljahr
	FROM kopfnote_rahmen
		LEFT JOIN kopfnote_fk ON kopfnote_rahmen.id=kopfnote_fk.rahmen
		LEFT JOIN fach_klasse ON kopfnote_fk.fach_klasse=fach_klasse.id
		LEFT JOIN klasse ON fach_klasse.klasse=klasse.id AND klasse.schule=".$schule."
	WHERE kopfnote_rahmen.bearbeitung_ab>".apostroph_bei_bedarf($start_ende["start"])." AND kopfnote_rahmen.bearbeitung_ab<".apostroph_bei_bedarf($start_ende["ende"])."
	ORDER BY kopfnote_rahmen.bearbeitung_ab DESC, klasse.einschuljahr DESC, klasse.endung");
	
	?>
<form action="<?php echo $pfad.$formularziel."&amp;eintragen="; if ($_GET["bearbeiten"]>0) echo "bearbeiten"; else echo "true"; ?>" method="post">
	<fieldset>
		<legend><?php if ($_GET["bearbeiten"]>0) echo "Kopfnote bearbeiten"; else echo "neue Kopfnote"; ?></legend>
		<?php if ($_GET["bearbeiten"]>0) {
			echo '<input type="hidden" name="rahmen" value="'.injaway($_GET["bearbeiten"]).'" />';
			$meine_kn=db_conn_and_sql("SELECT * FROM kopfnote_rahmen WHERE id=".injaway($_GET["bearbeiten"]));
			$meine_kn=sql_fetch_assoc($meine_kn);
		}
		
		$schulkategorien_result=db_conn_and_sql("SELECT * FROM kopfnoten_kategorie WHERE schule=".$schule." ORDER BY name");
		$schulkategorien=array();
		while($s_kat=sql_fetch_assoc($schulkategorien_result))
			$schulkategorien[]=$s_kat;
		?>

		<label for="name">Name<em>*</em>:</label> <input type="text" name="name" placeholder="z.B. Halbjahreskopfnoten <?php echo date("Y")."/".(date("y")+1); ?> Sek. I" required="required" size="25" maxlength="100"<?php if ($_GET["bearbeiten"]>0) echo ' value="'.$meine_kn["name"].'"'; ?>/><br />
		<label for="faktor">Faktor:</label> <input type="text" name="faktor" size="1" title="Falls mehrere Kopfnoten mit denselben Kategorien erstellt wurden, kann mit dem Faktor eine Wichtung vorgegeben werden. Keine Kommazahlen erlaubt." value="<?php if ($_GET["bearbeiten"]>0) echo $meine_kn["faktor"]; else echo "1"; ?>" /><br />
		<label for="art">Art:</label> <select name="art"><?php foreach ($kopfnoten_arten as $kna) {echo '<option value="'.$kna["id"].'"'; if ($_GET["bearbeiten"]>0 and $meine_kn["art"]==$kna["id"]) echo ' selected="selected"'; echo '>'.$kna["name"].'</option>';} ?></select><br />
		<label for="kategorie_1">Kategorien:</label> <?php
			if ($_GET["bearbeiten"]>0)
				$zugeordnete_kategorien=db_conn_and_sql("SELECT * FROM kopfnotenkat_rahmen WHERE rahmen=".$meine_kn["id"]." ORDER BY position");
			for ($i=1; $i<10; $i++) {
				if ($_GET["bearbeiten"]>0)
					$my_kn_kat=sql_fetch_assoc($zugeordnete_kategorien);
				if ($i==1 or $my_kn_kat["kn_kat"]>0)
					$zusatz=' style="display: inline;" onchange="this.value==\'-\'?document.getElementById(\'kategorie_'.($i+1).'\').style.display=\'none\':document.getElementById(\'kategorie_'.($i+1).'\').style.display=\'inline\'"';
				else
					$zusatz=' style="display: none;" onchange="this.value==\'-\'?document.getElementById(\'kategorie_'.($i+1).'\').style.display=\'none\':document.getElementById(\'kategorie_'.($i+1).'\').style.display=\'inline\'"';
				if ($my_kn_kat["kn_kat"]>0)
					$zusatz.=' disabled="disabled"';
				
				echo '<select name="kategorie_'.$i.'" id="kategorie_'.$i.'"'.$zusatz.'>';
				
				if ($i==1)
					echo '<option value="-">untschiedliche (Fachlehrer)</option>';
				else
					echo '<option value="-">-</option>';
				
				foreach($schulkategorien as $s_kat) {
					echo '<option value="'.$s_kat["id"].'"';
					if ($_GET["bearbeiten"]>0 and $my_kn_kat["kn_kat"]==$s_kat["id"])
						echo ' selected="selected"';
					echo '>'.$s_kat["name"].'</option>'; 
				}
				echo '</select>';
			}
			?><br />
		<label for="bearbeitung_ab">Zeitraum<em>*</em>:</label> <input type="text" class="datepicker" id="bearbeitung_ab" name="bearbeitung_ab" size="8" maxlength="10" <?php if ($_GET["bearbeiten"]>0) echo ' value="'.datum_strich_zu_punkt($meine_kn["bearbeitung_ab"]).'"'; ?> />
		- <input type="text" class="datepicker" id="bearbeitung_bis" name="bearbeitung_bis" size="8" maxlength="10" <?php if ($_GET["bearbeiten"]>0) echo ' value="'.datum_strich_zu_punkt($meine_kn["bearbeitung_bis"]).'"'; ?> /><br />
		<label for="beschreibung">Beschreibung:</label> <textarea name="beschreibung"><?php if ($_GET["bearbeiten"]>0) echo $meine_kn["beschreibung"]; ?></textarea><br />
		<label style="float: left;">Klassen<em>*</em>:</label> <p style="margin-left: 120px; width: auto;">
		<?php $klassen=db_conn_and_sql("SELECT * FROM klasse WHERE klasse.schule=".$schule." AND klasse.einschuljahr>".($aktuelles_jahr-12)." ORDER BY einschuljahr DESC, endung");
		if ($_GET["bearbeiten"]>0) {
			$klassen_vorauswahl=db_conn_and_sql("SELECT DISTINCT klasse.id AS klasse_id
				FROM kopfnote_fk, fach_klasse, klasse
				WHERE kopfnote_fk.rahmen=".injaway($_GET["bearbeiten"])."
					AND kopfnote_fk.fach_klasse=fach_klasse.id
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

<h3>Existierende Kopfnoten</h3>
<table class="tabelle">
	<tr><th>Name</th><th>Klassen</th><th>Aktionen</th></tr>
<?php
	$kn_old_id=0;
	$kn_array=array();
	while ($kn=sql_fetch_assoc($kopfnoten)) {
		if ($kn["id"]!=$kn_old_id) {
			/*if ($kn_old_id!=0)
				echo '</td><td> <a href="'.$formularziel.'&amp;bearbeiten='.$kn_old_id.'" class="icon" title="bearbeiten"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
					<a href="'.$formularziel.'&amp;eintragen=loeschen&amp;loeschen='.$kn_old_id.'" onclick="if (!confirm(\'Wollen Sie diese kopfnote wirklich l&ouml;schen?\')) return false;" class="icon" title="l&ouml;schen"><img src="'.$pfad.'icons/delete.png" alt="loeschen" /></a>
					</td></tr>';*/
			$kn_array[$kn["id"]]=array("id"=>$kn["id"], "rahmen"=>'<tr><td>'.html_umlaute($kn["name"]).' ('.datum_strich_zu_punkt_uebersichtlich($kn["bearbeitung_ab"], true, false).' - '.datum_strich_zu_punkt_uebersichtlich($kn["bearbeitung_bis"], true, false).')</td><td>'."\n", "klassen"=>array());
			$kn_old_id=$kn["id"];
		}
		
		if ($kn["einschuljahr"]>2000)
			$kn_array[$kn["id"]]["klassen"][] = ($aktuelles_jahr-$kn["einschuljahr"]+1)." ".$kn["endung"];
	}
	if ($kn_old_id!=0)
		foreach($kn_array as $kn) {
			$alle_fks=db_conn_and_sql("SELECT DISTINCT COUNT(kopfnote_fk.fach_klasse) AS fks FROM kopfnote_fk WHERE kopfnote_fk.rahmen=".$kn["id"]);
			$alle_fks=sql_fetch_assoc($alle_fks);
			$fertige_fks=db_conn_and_sql("SELECT DISTINCT COUNT(kopfnote_fk.fach_klasse) AS fks FROM kopfnote_fk WHERE kopfnote_fk.fertig IS NOT NULL AND kopfnote_fk.rahmen=".$kn["id"]);
			$fertige_fks=sql_fetch_assoc($fertige_fks);
			echo $kn["rahmen"];
			echo implode(", ", $kn["klassen"])." (".$fertige_fks["fks"]."/".$alle_fks["fks"].")";
			echo '</td><td> <a href="'.$formularziel.'&amp;bearbeiten='.$kn["id"].'" class="icon" title="bearbeiten"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
				<a href="'.$formularziel.'&amp;eintragen=loeschen&amp;loeschen='.$kn["id"].'" onclick="if (!confirm(\'Wollen Sie diese Kopfnote wirklich l&ouml;schen?\')) return false;" class="icon" title="l&ouml;schen"><img src="'.$pfad.'icons/delete.png" alt="loeschen" /></a>
				<a href="'.$pfad.'formular/kopfnoten_auswertung.php?auswertung='.$kn["id"].'" onclick="fenster(this.href, \'unwichtiger Titel\'); return false;" class="icon" title="Auswertung ansehen"><img src="'.$pfad.'icons/statistik.png" alt="auswertung" /></a>
				</td></tr>';
		}
?>
</table>
