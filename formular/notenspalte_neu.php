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

$titelleiste="Leistungs&uuml;berpr&uuml;fung hinzuf&uuml;gen";
$pfad='../';

if ($_GET["eintragen"]=="true") {
	session_start();
	include $pfad."funktionen.php";
	
	if (!proofuser("fach_klasse",$_POST["fach_klasse"]))
		die("Sie sind hierzu nicht berechtigt.");
	
	if ($_POST["kommentar"]=="Kommentar (optional)")
		$_POST["kommentar"]="";
	db_conn_and_sql("INSERT INTO `notenbeschreibung` (`beschreibung`, `notentyp`, `halbjahresnote`,`fach_klasse`,`datum`,`kommentar`,`bewertungstabelle`, `plan`)
		VALUES (".apostroph_bei_bedarf($_POST["beschreibung"]).", ".injaway($_POST["notentyp"]).", ".($_POST["halbjahresnote"]+0).", ".injaway($_POST["fach_klasse"]).", ".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["datum"])).", ".apostroph_bei_bedarf($_POST["kommentar"]).", ".leer_NULL($_POST["bewertungstabelle"]).", ".leer_NULL($_POST["plan"]).");");
	?>
	<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
	<?php
}
else {
	include $pfad."header.php";
	include $pfad."funktionen.php";
	
	if (isset($_GET["plan"]))
		$fach_klasse=sql_result(db_conn_and_sql("SELECT plan.fach_klasse FROM plan WHERE plan.id=".injaway($_GET["plan"])),0, "plan.fach_klasse");
	else
		$fach_klasse=injaway($_GET["fk"]);
	
	if (!proofuser("fach_klasse",$fach_klasse))
		die("Sie sind hierzu nicht berechtigt.");
	
	// FK-Name:
	//$subject_classes=new subject_classes($aktuelles_jahr);
	?>

	<body>
		<fieldset><legend><?php echo $titelleiste.' f&uuml;r <span class="fk" style="background-color: #'.$subject_classes->cont[$subject_classes->active]["farbe"].';">'.$subject_classes->cont[$subject_classes->active]["name"].'</span>'; ?></legend>
			<form action="<?php echo $pfad; ?>formular/notenspalte_neu.php?eintragen=true" method="post" accept-charset="ISO-8859-1">
				<input type="hidden" name="fach_klasse" value="<?php echo $fach_klasse; ?>" />
				<?php
				$db = new db();
				$schuljahr= $db->aktuelles_jahr();
				$schule=sql_fetch_assoc(db_conn_and_sql("SELECT schule FROM klasse, fach_klasse WHERE fach_klasse.klasse=klasse.id AND fach_klasse.id=".$fach_klasse));
				$schule=$schule["schule"];
				$start_ende=schuljahr_start_ende($schuljahr,$schule);
				$schuljahresbeginn_datum=$start_ende["start"];
				$schuljahresende_datum=$start_ende["ende"];
				
				$notentypen_result = db_conn_and_sql ( 'SELECT * FROM `notentypen` WHERE `id`<11 OR (`schule`='.$schule.' AND `aktiv`=1) ORDER BY `notentypen`.`kuerzel`' );
				
				$halbjahresumbruch=sql_result(db_conn_and_sql("SELECT schuljahr.halbjahreswechsel FROM schuljahr WHERE schuljahr.jahr=".$schuljahr." AND schuljahr.schule=".$schule),0,"schuljahr.halbjahreswechsel");
				
				include($pfad."formular/notenspalten_anderer_fachlehrer_anzeigen.php");
				
				if (isset($_GET["plan"]) and proofuser("plan",$_GET["plan"])) {
					    $plandatum = sql_result(db_conn_and_sql("SELECT plan.datum FROM plan WHERE plan.id=".injaway($_GET["plan"])),0, "plan.datum");
						echo '<script>
						$(function() {
							woche_sichtbar('.datum_zu_woche($plandatum).');
						});</script>';
					 ?>
					<input type="hidden" name="plan" value="<?php echo $_GET["plan"]; ?>" />
					<label for="nothing">Datum<em>*</em>:</label> <?php
						echo datum_strich_zu_punkt($plandatum);
						
				} else { ?>
					
					<label for="datum">Datum<em>*</em>:</label>
					
					<script>
						$(function() {
							$( "#zensurenspalte_datum" ).datepicker("option", "minDate", new Date('<?php echo $schuljahresbeginn_datum; ?>') );
							$( "#zensurenspalte_datum" ).datepicker("option", "maxDate", new Date('<?php echo $schuljahresende_datum; ?>') );
						});
					</script>
					<input type="text" id="zensurenspalte_datum" class="datepicker" placeholder="Datum" name="datum" size="7" maxlength="10" onchange="datenew=this.value.split('.'); datenew[2]+'-'+datenew[1]+'-'+datenew[0]<'<?php echo $halbjahresumbruch; ?>'?document.getElementById('halbjahresnote').checked=true:document.getElementById('halbjahresnote').checked=false; woche_sichtbar(js_datum_zu_woche($( '#zensurenspalte_datum' ).datepicker( 'getDate' )));" />
				<?php } ?><br />
				<label for="halbjahresnote">Halbjahr:</label> <input type="checkbox" id="halbjahresnote" name="halbjahresnote" value="1" title="Gehen die Zensuren (i.A.) in die Halbjahresbewertung mit ein?"<?php
				if (isset($_GET["plan"]) and $plandatum<$halbjahresumbruch)
					echo ' checked="checked"';
				?> /><br />
				<label for="notentyp">Beschreibung<em>*</em>: </label> <select name="notentyp" title="Notentyp"><?php
					while ($n_typ=sql_fetch_assoc($notentypen_result)) { ?>
						<option value="<?php echo $n_typ["id"]; ?>" title="<?php echo html_umlaute($n_typ["name"]); ?>"><?php echo html_umlaute($n_typ["kuerzel"]); ?></option>
					<?php } ?>
					</select>
					<input type="text" name="beschreibung" placeholder="Beschreibung" size="15" maxlength="30" title="Beschreibung" /><br />
				<?php if (!$my_user->my["zensurenkommentare"]) echo '<span style="display: none;">'; ?>
				<label for="kommentar">Kommentar:</label> <input type="text" name="kommentar" placeholder="Kommentar (optional)" size="15" maxlength="30" title="Kommentar (optional)" /><br />
				<?php if (!$my_user->my["zensurenkommentare"]) echo '</span>'; ?>
				<label for="bewertungstabelle">Bewertungstab.<em>*</em>:</label> <select name="bewertungstabelle" title="Bewertungstabelle w&auml;hlen"><?php
					echo bewertungstabelle_select($fach_klasse); ?>
					</select><br />
				<input type="button" class="button" value="eintragen" onclick="auswertung=new Array(<?php if (!isset($_GET["plan"])) echo "new Array(0, 'datum','datum','".$schuljahresbeginn_datum."','".$schuljahresende_datum."'), "; ?>new Array(0, 'beschreibung','nicht_leer','Beschreibung')); pruefe_formular(auswertung);" />
			</form>
		</fieldset>
	</body>
	</html>
<?php } ?>
