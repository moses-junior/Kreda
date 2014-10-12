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

$pfad='../';
include $pfad."funktionen.php";

if ($_GET["eintragen"]=="true" and proofuser("plan",$_POST["plan"]) and proofuser("schueler",$_POST["pupil"])) {
	if ($_POST["betragen"]!=0)
		db_conn_and_sql("INSERT INTO verwarnungen (plan, schueler, anzahl) VALUES
			(".injaway($_POST["plan"]).", ".injaway($_POST['pupil']).", ".injaway($_POST['betragen']).");");
	if ($_POST["mitarbeit"]!=0)
		db_conn_and_sql("INSERT INTO mitarbeit (plan, schueler, anzahl) VALUES
			(".injaway($_POST["plan"]).", ".injaway($_POST['pupil']).", ".injaway($_POST['mitarbeit']).");");
}

if ($_GET["eintragen"]=="delete" and proofuser("plan",$_GET["plan"]) and proofuser("schueler",$_GET["pupil"])) {
	if ($_GET["type"]==1)
		db_conn_and_sql("DELETE FROM verwarnungen WHERE plan=".injaway($_GET["plan"])." AND schueler=".injaway($_GET["pupil"]));
	else
		db_conn_and_sql("DELETE FROM mitarbeit WHERE plan=".injaway($_GET["plan"])." AND schueler=".injaway($_GET["pupil"]));
}

if ($_GET["eintragen"]=="edit" and proofuser("plan",$_GET["plan"]) and proofuser("schueler",$_GET["pupil"])) {
	if ($_GET["type"]==1)
		db_conn_and_sql("UPDATE verwarnungen SET anzahl=".injaway($_GET["count"])." WHERE plan=".injaway($_GET["plan"])." AND schueler=".injaway($_GET["pupil"]));
	else
		db_conn_and_sql("UPDATE mitarbeit SET anzahl=".injaway($_GET["count"])." WHERE plan=".injaway($_GET["plan"])." AND schueler=".injaway($_GET["pupil"]));
}

if (!proofuser("fach_klasse",$_GET["fach_klasse"]))
	die("Sie sind hierzu nicht berechtigt.");
		$klasse=db_conn_and_sql("SELECT * FROM klasse, fach_klasse
			WHERE klasse.id=fach_klasse.klasse
				AND fach_klasse.id=".injaway($_GET["fach_klasse"]));
		$schueler=schueler_von_fachklasse(injaway($_GET["fach_klasse"]));
		$plan=db_conn_and_sql("SELECT * FROM fach_klasse, faecher, plan
			WHERE fach_klasse.id=".injaway($_GET["fach_klasse"])."
				AND fach_klasse.fach=faecher.id
				AND fach_klasse.id=plan.fach_klasse
				AND plan.schuljahr=".$aktuelles_jahr."
				AND plan.ausfallgrund IS NULL
			ORDER BY plan.datum, plan.startzeit");
		$titelleiste="Betragen und Mitarbeit der Klasse ".($aktuelles_jahr-sql_result($klasse,0,"klasse.einschuljahr")+1).sql_result($klasse,0,"klasse.endung");
		include $pfad."header.php";
		?>
			<body>
			<script>
				$(function() {
	            $( '#edit_entry' ).dialog({
	               autoOpen: false,
						height: 350,
	               width: 680,
						title: 'Eintrag bearbeiten',
						modal: true,
						buttons: {
							"entfernen": function() {
								window.location='<?php echo $pfad; ?>pupil/betr_ma.php?fach_klasse=' + document.getElementById('fach_klasse_id').value + '&eintragen=delete&plan=' + document.getElementById('entry_plan_id').value + '&pupil=' + document.getElementById('entry_pupil_id').value + '&type=' + document.getElementById('entry_type').value;
							},
							Cancel: function() {
								$( this ).dialog( "close" );
							},
							"OK": function() {
								window.location='<?php echo $pfad; ?>pupil/betr_ma.php?fach_klasse=' + document.getElementById('fach_klasse_id').value + '&eintragen=edit&plan=' + document.getElementById('entry_plan_id').value + '&pupil=' + document.getElementById('entry_pupil_id').value + '&type=' + document.getElementById('entry_type').value + '&count=' + document.getElementById('entry').value;
							}
						},
						close: function() {
							allFields.val( "" ).removeClass( "ui-state-error" );
						}
					});
					
					$( ".icon" )
						.click(function() {
							$( "#edit_entry" ).dialog( "open" );
						});
				});
			</script>
			<div id="edit_entry">
				<form>
					<fieldset>
						<label for="name">Name</label>
						<input type="text" name="name" id="name" disabled="disabled" readonly="readonly" class="text ui-widget-content ui-corner-all" /><br />
						<input type="hidden" id="entry_type" value="" />
						<input type="hidden" id="entry_pupil_id" value="" />
						<input type="hidden" id="entry_plan_id" value="" />
						<label for="plan_entry">Stunde vom</label>
						<input type="text" name="plan_entry" id="plan_entry" size="5" disabled="disabled" readonly="readonly" value="" class="text ui-widget-content ui-corner-all" /><br />
						<label for="entry" id="entry_label">Anzahl</label>
						<select name="entry" id="entry">
						<?php for ($i=-5; $i<6; $i++)
							echo '<option value="'.$i.'">'.$i.'</option>'; ?>
							</select>
					</fieldset>
				</form>
			</div>
			
			<div class="inhalt">
			<input type="hidden" id="fach_klasse_id" value="<?php echo $_GET["fach_klasse"]; ?>" />
			<form action="<?php echo $pfad; ?>pupil/betr_ma.php?eintragen=true&amp;fach_klasse=<?php echo $_GET["fach_klasse"]; ?>" method="POST" accept-charset="ISO-8859-1">
				<table class="tabelle"><tr>
					<th>Datum</th>
					<th>Sch&uuml;ler</th>
					<th>Betragen</th>
					<th>Mitarbeit</th>
				</tr>
				<tr>
					<td><select name="plan">
						<?php
						$vorauswahl_getroffen=false;
						for ($i=0; $i<sql_num_rows($plan); $i++) {
							echo '<option style="background-color: #'.sql_result($plan,$i,"fach_klasse.farbe").'" value="'.sql_result($plan,$i,"plan.id").'"';
							if (!$vorauswahl_getroffen and (sql_result($plan,$i,"plan.datum")>date("Y-m-d") or sql_num_rows($plan)-1==$i)) {
								echo ' selected="selected"';
								$vorauswahl_getroffen=true;
							}
							echo '>'.datum_strich_zu_punkt_uebersichtlich(sql_result($plan,$i,"plan.datum"), "wochentag_kurz", false).' '.substr(sql_result($plan,$i,"plan.startzeit"),0,5).' ('.$subject_classes->cont[$subject_classes->active]["name"].')</option>';
						} ?>
						</select>
					</td>
					<td><select name="pupil">
						<?php for ($i=0; $i<sql_num_rows($schueler); $i++) {
							echo '<option value="'.sql_result($schueler,$i,"schueler.id").'">'.sql_result($schueler,$i,"schueler.position").'. '.sql_result($schueler,$i,"schueler.name").', '.sql_result($schueler,$i,"schueler.vorname").'</option>';
						} ?>
							</select>
					</td>
					<td><select name="betragen">
						<?php for ($i=-5; $i<6; $i++) {
							echo '<option value="'.$i.'"';
							if ($i==0) echo ' selected="selected"';
							echo '>'.$i.'</option>';
						} ?>
							</select>
					</td>
					<td><select name="mitarbeit">
						<?php for ($i=-5; $i<6; $i++) {
							echo '<option value="'.$i.'"';
							if ($i==0) echo ' selected="selected"';
							echo '>'.$i.'</option>';
						} ?>
							</select>
						<input type="submit" value="eintragen" />
					</td>
				</tr>
				<?php
					$eintragungen=db_conn_and_sql("SELECT *
						FROM fach_klasse, faecher, plan
							LEFT JOIN verwarnungen ON verwarnungen.plan=plan.id
							LEFT JOIN mitarbeit ON mitarbeit.plan=plan.id
							LEFT JOIN schueler ON verwarnungen.schueler=schueler.id OR mitarbeit.schueler=schueler.id
						WHERE fach_klasse.id=".injaway($_GET["fach_klasse"])."
							AND fach_klasse.fach=faecher.id
							AND fach_klasse.id=plan.fach_klasse
							AND plan.schuljahr=".$aktuelles_jahr."
							AND plan.ausfallgrund IS NULL
						ORDER BY plan.datum, plan.startzeit, schueler.name, schueler.vorname");
					for($i=0; $i<sql_num_rows($eintragungen); $i++) {
						// Vermeidung, dass ein Schueler am selben Tag zwei Eintraege bekommt, wenn er MA und Verwarnungen eingetragen hat
						if ($i<sql_num_rows($eintragungen)-1
								and sql_result($eintragungen,$i+1,"schueler.id")==sql_result($eintragungen,$i+1,"verwarnungen.schueler")
								and sql_result($eintragungen,$i+1,"schueler.id")==sql_result($eintragungen,$i+1,"mitarbeit.schueler")
								and sql_result($eintragungen,$i,"schueler.id")==sql_result($eintragungen,$i+1,"schueler.id"))
							$i++;
						// einen plan nur anzeigen, wenn mindestens eine Verwarnung oder ein Mitarbeit-Eintrag besteht
						if (sql_result($eintragungen,$i,"verwarnungen.schueler")>0 or sql_result($eintragungen,$i,"mitarbeit.schueler")>0) {
						echo '<tr>
							<td style="background-color: #'.sql_result($eintragungen,$i,"fach_klasse.farbe").'">'.datum_strich_zu_punkt_uebersichtlich(sql_result($eintragungen,$i,"plan.datum"), "wochentag_kurz", false).' ('.$subject_classes->cont[$subject_classes->active]["name"].')</td>
							<td>'.sql_result($eintragungen,$i,"schueler.position").'. '.sql_result($eintragungen,$i,"schueler.name").', '.sql_result($eintragungen,$i,"schueler.vorname").'</td>
							<td>';
							if (sql_result($eintragungen,$i,"schueler.id")==sql_result($eintragungen,$i,"verwarnungen.schueler"))
								echo sql_result($eintragungen,$i,"verwarnungen.anzahl").' <a href="" class="icon" onclick="document.getElementById(\'name\').value=\''.sql_result($eintragungen,$i,"schueler.name").', '.sql_result($eintragungen,$i,"schueler.vorname").'\';
									document.getElementById(\'plan_entry\').value=\''.datum_strich_zu_punkt_uebersichtlich(sql_result($eintragungen,$i,"plan.datum"), "wochentag_kurz", false).'\';
									document.getElementById(\'entry_plan_id\').value='.sql_result($eintragungen,$i,"verwarnungen.plan").';
									document.getElementById(\'entry_pupil_id\').value='.sql_result($eintragungen,$i,"verwarnungen.schueler").';
									document.getElementById(\'entry_type\').value=1;
									document.getElementById(\'entry\').value='.sql_result($eintragungen,$i,"verwarnungen.anzahl").';
									document.getElementById(\'entry_label\').innerHTML=\'Betragen\';
									return false;"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>';
							echo '</td>
							<td>';
							if (sql_result($eintragungen,$i,"schueler.id")==sql_result($eintragungen,$i,"mitarbeit.schueler"))
								echo sql_result($eintragungen,$i,"mitarbeit.anzahl").' <a href="" class="icon" onclick="document.getElementById(\'name\').value=\''.sql_result($eintragungen,$i,"schueler.name").', '.sql_result($eintragungen,$i,"schueler.vorname").'\';
									document.getElementById(\'plan_entry\').value=\''.datum_strich_zu_punkt_uebersichtlich(sql_result($eintragungen,$i,"plan.datum"), "wochentag_kurz", false).'\';
									document.getElementById(\'entry_plan_id\').value='.sql_result($eintragungen,$i,"mitarbeit.plan").';
									document.getElementById(\'entry_pupil_id\').value='.sql_result($eintragungen,$i,"mitarbeit.schueler").';
									document.getElementById(\'entry_type\').value=2;
									document.getElementById(\'entry\').value='.sql_result($eintragungen,$i,"mitarbeit.anzahl").';
									document.getElementById(\'entry_label\').innerHTML=\'Mitarbeit\';
									return false;"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>';
							echo '</td>
						</tr>';
						}
					}
				?>
				</table>
				
			</form>
			</div>
			</body>
		</html>

