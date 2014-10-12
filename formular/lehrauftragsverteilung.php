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

// Indiware-Import
if ($_GET["eintragen"]=="import") {
	if(empty($_FILES['import_file']['name']))
		$err[] = "Eine Datei muss ausgew&auml;hlt werden";

	if(empty($err)) {
		$tempname = $_FILES['import_file']['tmp_name'];
		$name = $_FILES['import_file']['name'];
		
		$type = $_FILES['import_file']['type'];
		$size = $_FILES['import_file']['size'];
		
		$xmlFile = $_FILES['import_file']['tmp_name'];
		
		include($pfad."formular/import_von_indiware.php");
		
		$importiere_von_indiware=true;
	}
	else
		foreach($err as $error)
			echo '<br />'.$error;
}

$angestellte=db_conn_and_sql("SELECT *
	FROM schule_user, users
	WHERE users.user_id=schule_user.user
		AND schule_user.schule=".$schule."
	ORDER BY user_name");

$kuerzel_zu_id=array();
while ($lehrer=sql_fetch_assoc($angestellte)) {
	$kuerzel_zu_id[$lehrer["user_name"]]=$lehrer["user_id"];
}

if ($_GET["eintragen"]=="true") {
	// Klassenlehrer
	$klasse=0;
	$fach=0;
	while (isset($_POST["kl_".$klasse])) {
		$aktuelle_klasse=$_POST["klasse_id_".$klasse];
		if ($_POST["kl_".$klasse]!="") {
			$einzel_kuerzel=explode(",", $_POST["kl_".$klasse]);
			for ($i=0; $i<2; $i++) {
				$einzel_kuerzel[$i]=trim($einzel_kuerzel[$i]);
				if ($kuerzel_zu_id[$einzel_kuerzel[$i]]>0)
					$einzel_kuerzel[$i]=$kuerzel_zu_id[$einzel_kuerzel[$i]];
				else
					$einzel_kuerzel[$i]="NULL";
			}
			db_conn_and_sql("UPDATE klasse SET klassenlehrer=".$einzel_kuerzel[0].", klassenlehrer2=".$einzel_kuerzel[1]." WHERE id=".$aktuelle_klasse);
		}
		else
			db_conn_and_sql("UPDATE klasse SET klassenlehrer=NULL, klassenlehrer2=NULL WHERE id=".$aktuelle_klasse);
		$klasse++;
	}
	
	// Fachlehrer
	$klasse=0;
	$fach=0;
	while (isset($_POST["fach_".$klasse."_".$fach])) {
		$aktuelle_klasse=$_POST["klasse_id_".$klasse];
		while (isset($_POST["fach_".$klasse."_".$fach])) {
			$bestehende_lehrauftraege=db_conn_and_sql("SELECT * FROM lehrauftrag WHERE schuljahr=".injaway($_POST["schuljahr"])." AND klasse=".$aktuelle_klasse." AND fach=".injaway($_POST["fach_id_".$fach])." ORDER BY lfd_nr;");
			if ($_POST["fach_".$klasse."_".$fach]!="") {
				$einzel_kuerzel=explode(",", $_POST["fach_".$klasse."_".$fach]);
				$leer=0;
				if ($_POST["fach_".$klasse."_".$fach."_gemeinsam"]==1)
					$gemeinsam=1;
				else
					$gemeinsam=0;
				for ($i=0; $i<count($einzel_kuerzel); $i++) {
					$einzel_kuerzel[$i]=trim($einzel_kuerzel[$i]);
					if ($einzel_kuerzel[$i]!="") {
						if (sql_num_rows($bestehende_lehrauftraege)<($i+1))
							db_conn_and_sql("INSERT INTO lehrauftrag (user, schuljahr, klasse, fach, lfd_nr, gemeinsame_noten)
								VALUES (".$kuerzel_zu_id[$einzel_kuerzel[$i]].", ".injaway($_POST["schuljahr"]).", ".$aktuelle_klasse.", ".injaway($_POST["fach_id_".$fach]).", ".$i.", ".$gemeinsam.");");
						else {
							$lehrauftrag_eintrag=sql_fetch_assoc($bestehende_lehrauftraege);
							if ($lehrauftrag_eintrag["user"]==$kuerzel_zu_id[$einzel_kuerzel[$i]] and $lehrauftrag_eintrag["schuljahr"]==$_POST["schuljahr"] and $lehrauftrag_eintrag["klasse"]==$aktuelle_klasse and $lehrauftrag_eintrag["fach"]==$_POST["fach_id_".$fach] and $lehrauftrag_eintrag["lfd_nr"]==$i)
								db_conn_and_sql("UPDATE lehrauftrag SET gemeinsame_noten=".$gemeinsam." WHERE user=".$lehrauftrag_eintrag["user"]." AND schuljahr=".$lehrauftrag_eintrag["schuljahr"]." AND klasse=".$lehrauftrag_eintrag["klasse"]." AND fach=".$lehrauftrag_eintrag["fach"]." AND lfd_nr=".$lehrauftrag_eintrag["lfd_nr"].";");
							else {
								db_conn_and_sql("DELETE FROM lehrauftrag WHERE schuljahr=".injaway($_POST["schuljahr"])." AND klasse=".$aktuelle_klasse." AND fach=".injaway($_POST["fach_id_".$fach])." AND lfd_nr=".$i.";");
								db_conn_and_sql("INSERT INTO lehrauftrag (user, schuljahr, klasse, fach, lfd_nr, gemeinsame_noten)
									VALUES (".$kuerzel_zu_id[$einzel_kuerzel[$i]].", ".injaway($_POST["schuljahr"]).", ".$aktuelle_klasse.", ".injaway($_POST["fach_id_".$fach]).", ".$i.", ".$gemeinsam.");");
							}
						}
					}
					else
						$leer++;
				}
				if (sql_num_rows($bestehende_lehrauftraege)>$i-$leer)
					db_conn_and_sql("DELETE FROM lehrauftrag WHERE schuljahr=".injaway($_POST["schuljahr"])." AND klasse=".$aktuelle_klasse." AND fach=".injaway($_POST["fach_id_".$fach])." AND lfd_nr>=".($i-$leer).";");
			}
			else
				if (sql_num_rows($bestehende_lehrauftraege)>0)
					db_conn_and_sql("DELETE FROM lehrauftrag WHERE schuljahr=".injaway($_POST["schuljahr"])." AND klasse=".$aktuelle_klasse." AND fach=".injaway($_POST["fach_id_".$fach]).";");
			$fach++;
		}
		$fach=0;
		$klasse++;
	}
		
}
	
	//$verfuegbare_lehrer=Array();
	//while ($lehrer=sql_fetch_assoc($angestellte)) {
	//	$verfuegbare_lehrer[]=array("id"=>$lehrer["user_id"], "kuerzel"=>html_umlaute($lehrer["user_name"]));
	//	$verfuegbare_lehrer_nach_ids[$lehrer["user_id"]]=html_umlaute($lehrer["user_name"]);
	//}
?>
 <script>
$(function() {
	var availableTeachers = [
	<?php
	sql_reset_pointer($angestellte); // bei $kuerzel_zu_id schon verwendet
	
	$id_zu_kuerzel=array();
	$erstes=true;
	while ($lehrer=sql_fetch_assoc($angestellte)) {
		if ($erstes)
			$erstes=false;
		else
			echo ',';
		$id_zu_kuerzel[$lehrer["user_id"]]=html_umlaute($lehrer["user_name"]);
		echo '"'.html_umlaute($lehrer["user_name"]).'"';
		
		// Lehrer-Import von Indiware
		if (isset($import_lehrer[$lehrer["user_name"]]))
			$import_lehrer[$lehrer["user_name"]]["id"]=$lehrer["user_id"];
	}
	?>
	];
	function split( val ) {
		return val.split( /,\s*/ );
	}
	function extractLast( term ) {
		return split( term ).pop();
	}
	// um Autovervollstaendigung zu aktivieren muss man das a entfernen
	$( ".ateacherAuto" )
		// don't navigate away from the field on tab when selecting an item
		.bind( "keydown", function( event ) {
			if ( event.keyCode === $.ui.keyCode.TAB &&
					$( this ).autocomplete( "instance" ).menu.active ) {
				event.preventDefault();
			}
		})
		.autocomplete({
			minLength: 0,
			source: function( request, response ) {
				// delegate back to autocomplete, but extract the last term
				response( $.ui.autocomplete.filter(
				availableTeachers, extractLast( request.term ) ) );
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function( event, ui ) {
				var terms = split( this.value );
				// remove the current input
				terms.pop();
				// add the selected item
				terms.push( ui.item.value );
				// add placeholder to get the comma-and-space at the end
				terms.push( "" );
				this.value = terms.join( ", " );
				return false;
			}
		});
});
</script>

<?php
	function lehrer_select($name, $vorauswahl, $display_onchange=false) {
		$return='<input type="text" name="'.$name.'" class="teacherAuto" value="'.$vorauswahl.'" size="2" />'; // onkeydown="typeof this.value.trim(\' \').split(\',\')[1] == \'undefined\'?document.getElementById(\''.$name.'_span\').style.display=\'none\':document.getElementById(\''.$name.'_span\').style.display=\'inline\'"
		//$return.='<span id="'.$name.'_span" style="display: none;"><br />';
		//$return.='<input type="checkbox" name="'.$name.'_gemeinsam" title="gemeinsame Noten" value="1" /> gem.</span>';
		/*global $verfuegbare_lehrer;
		$return ='<select name="'.$name.'"';
		if ($display_onchange!=false)
			$return.=' onchange="document.getElementById(\''.$display_onchange.'\').style.display=\'block\'"';
		$return .='>';
		$return.='<option value="0">-</option>';
		foreach ($verfuegbare_lehrer as $lehrer) {
			$selected='';
			if ($lehrer["id"]==$vorauswahl)
				$selected=' selected="selected"';
			$return.='<option'.$selected.' value="'.$lehrer["id"].'">'.$lehrer["kuerzel"].'</option>';
		}
		$return.='</select>';*/
		return $return;
	}
	
	$schuljahr=$aktuelles_jahr;
	//if ($_GET["schuljahr"]>2000)
	//	$schuljahr=injaway($_GET["schuljahr"]);
	?>
	<form action="<?php echo $pfad.$formularziel."&amp;eintragen=true"; ?>" method="post">
		<input type="hidden" name="schuljahr" value="<?php echo $schuljahr; ?>" />
		<fieldset>
			<legend>Lehrauftragsverteilung f&uuml;r <?php echo $schuljahr." / ".($schuljahr+1); ?>
				<!--<select name="schuljahr" onchange="window.location.href = '<?php echo $pfad; ?>index.php?tab=lav&amp;schuljahr='+this.value;"><?php
					$schuljahre_result=db_conn_and_sql("SELECT * FROM schuljahr WHERE schule=".$schule." ORDER BY jahr DESC");
					while ($einzel_schuljahr=sql_fetch_assoc($schuljahre_result)) {
						echo '<option value="'.$einzel_schuljahr["jahr"].'"';
						if ($schuljahr==$einzel_schuljahr["jahr"])
							echo ' selected="selected"';
						echo '>'.$einzel_schuljahr["jahr"].'/'.($einzel_schuljahr["jahr"]+1).'</option>';
					}
				?>
				</select>-->
			</legend>
			<?php
			$faecher=db_conn_and_sql("SELECT * FROM faecher WHERE schule=0 OR schule=".$schule." ORDER BY faecher.kuerzel");
			$klassen=db_conn_and_sql("SELECT * FROM klasse WHERE klasse.schule=".$schule);
			
			echo '<table class="tabelle">';
			echo '<tr><th>Klasse</th>';
			$i_klasse=0;
			while ($i_klasse<$school_classes->length()) {
				if ($school_classes->cont[$i_klasse]["schule"]==$schule) {
					if ($i_klasse==13)
						echo '<th></th>';
					echo '<th>'.$school_classes->cont[$i_klasse]["name"];
					echo '<input type="hidden" name="klasse_id_'.$i_klasse.'" value="'.$school_classes->cont[$i_klasse]["id"].'" /></th>';
				}
				$i_klasse++;
			}
			echo '</tr><tr><th>Klassenlehrer</th>';
			$i_klasse=0;
			while ($i_klasse<$school_classes->length()) {
				if ($i_klasse==13)
					echo '<th>Klassenlehrer</th>';
				if ($school_classes->cont[$i_klasse]["schule"]==$schule) {
					// Klassenlehrer
					echo '<td>';
					echo '';
					$klassenlehrer_result=db_conn_and_sql("SELECT klassenlehrer, klassenlehrer2 FROM klasse WHERE id=".$school_classes->cont[$i_klasse]["id"]);
					$klassenlehrer_result=sql_fetch_assoc($klassenlehrer_result);
					if ($klassenlehrer_result["klassenlehrer"]>0 or $klassenlehrer_result["klassenlehrer2"]>0)
						$vorauswahl=$id_zu_kuerzel[$klassenlehrer_result["klassenlehrer"]].", ".$id_zu_kuerzel[$klassenlehrer_result["klassenlehrer2"]];
					else
						$vorauswahl="";
					echo lehrer_select('kl_'.$i_klasse, $vorauswahl);
					//echo '<br />';
					
					// Stellvertreter
					//echo lehrer_select('kl2_'.$i_klasse, $school_classes->cont[$i_klasse]["klassenlehrer2"]);
					echo '</td>';
				}
				$i_klasse++;
			}
			echo '</tr>';
			
			$i_fach=0;
			while ($fach=sql_fetch_assoc($faecher)) {
				if (($i_fach+1)/10==floor(($i_fach+1)/10)) {
					echo '</tr>';
					echo '<tr><th>Klasse</th>';
					$i_klasse=0;
					while ($i_klasse<$school_classes->length()) {
						if ($i_klasse==13)
							echo '<th></th>';
						if ($school_classes->cont[$i_klasse]["schule"]==$schule)
							echo '<th>'.$school_classes->cont[$i_klasse]["name"].'</th>';
						$i_klasse++;
					}
				}
				echo '</tr><tr>';
				echo '<th><input type="hidden" name="fach_id_'.$i_fach.'" value="'.$fach["id"].'" />'."\n";
				echo $fach["kuerzel"].'</th>';
				$i_klasse=0;
				while ($i_klasse<$school_classes->length()) {
					if ($i_klasse==13)
						echo '<th>'.$fach["kuerzel"].'</th>';
					if ($school_classes->cont[$i_klasse]["schule"]==$schule) {
						echo '<td>';
						//$zugehoerige_fach_klassen = db_conn_and_sql("SELECT * FROM fach_klasse WHERE klasse=".$school_classes->cont[$i_klasse]["id"]." AND fach=".$fach["id"]);
						//while ($fach_klasse_besetzt=sql_fetch_assoc($zugehoerige_fach_klassen)) {
						//	echo $fach_klasse_besetzt["user"].'<br />';
						//}
						$bestehende_lehrauftraege=db_conn_and_sql("SELECT * FROM lehrauftrag WHERE klasse=".$school_classes->cont[$i_klasse]["id"]." AND fach=".$fach["id"]." AND schuljahr=".$schuljahr);
						$i_la=0;
						$vorauswahl=array();
						while ($lehrauftrag=sql_fetch_assoc($bestehende_lehrauftraege)) {
							$vorauswahl[]=$id_zu_kuerzel[$lehrauftrag["user"]];
							$i_la++;
						}
						
						if ($importiere_von_indiware) {
							// Import von Indiware
							$la_hier=array();
							foreach($lehrauftrag_indiware as $la) {
								if ($la["fach"]==$fach["id"] and $la["klasse"]==$school_classes->cont[$i_klasse]["id"])
									$la_hier[]=$la["lehrer"];
							}
							$vorauswahl=implode(", ",$la_hier);
						}
						else
							if ($vorauswahl!="")
								$vorauswahl=implode(", ",$vorauswahl);
						echo lehrer_select('fach_'.$i_klasse.'_'.$i_fach, $vorauswahl);
						echo '</td>'."\n";
					}
					$i_klasse++;
				}
				$i_fach++;
			}
			?>
			</table>
			<?php
			if ($importiere_von_indiware) {
				echo '<br /><div class="hinweis"><b>Folgende Lehrerk&uuml;rzel k&ouml;nnen nicht zugeordnet werden:</b> (Diese m&uuml;ssen zuvor angelegt werden)<br />';
				foreach($import_lehrer as $le)
					if ($le["id"]==-1)
						echo $le["kuerzel"].", ";
				echo '</div>';
			}
			?>
			<input type="submit" value="speichern" />
		</fieldset>
	</form>
	
	<form action="<?php echo $pfad.$formularziel."&amp;eintragen=import"; ?>" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
		<fieldset><legend>Importiere aus Indiware-XML-Datei</legend>
			<label for="link_file">Datei<em>*</em>:</label>
			<input type="file" name="import_file" size="5" /> <input type="submit" value="importieren" />
		</fieldset>
	</form>
