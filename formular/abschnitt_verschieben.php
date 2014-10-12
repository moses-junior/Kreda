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
$pfad="../";
include $pfad."funktionen.php";

if ($_GET["eintragen"]=="true" and proofuser("block", $_GET["alt_block"])) {
	$abschnitte=db_conn_and_sql("SELECT * FROM `block_abschnitt`, `abschnitt`
		WHERE `block_abschnitt`.`abschnitt`=`abschnitt`.`id`
			AND `block_abschnitt`.`block`=".injaway($_GET["alt_block"])."
		ORDER BY `block_abschnitt`.`position`");
	
	// verschiebung an Position $i+-100 (100 sollte doch reichen!)
	if ($_POST["neue_pos"]=="anfang") $position=-100;
	else $position=100;
	
	// gewaehlte Abschnitte verschieben
	for ($i=0;$i<sql_num_rows($abschnitte);$i++) {
		switch ($_POST["action_".$i]) {
		case "move":
			db_conn_and_sql("UPDATE `block_abschnitt` SET `block`=".injaway($_POST["neuer_block"]).", `position`=".($i+$position)." WHERE `block`=".injaway($_GET["alt_block"])." AND `abschnitt`=".sql_result($abschnitte,$i,'block_abschnitt.abschnitt'));
			break;
		case "duplicate":
			// neuen Abschnitt erzeugen
			$abschnitt_id = db_conn_and_sql("INSERT INTO abschnitt (hefter, medium, ziel, minuten, nachbereitung, sozialform, handlungsmuster, inhaltspositionen, aktiv, methode)
				VALUES (".leer_NULL(sql_result($abschnitte,$i,'abschnitt.hefter')).", ".leer_NULL(sql_result($abschnitte,$i,'abschnitt.medium')).", ".apostroph_bei_bedarf(sql_result($abschnitte,$i,'abschnitt.ziel')).", ".leer_NULL(sql_result($abschnitte,$i,'abschnitt.minuten')).", ".apostroph_bei_bedarf(sql_result($abschnitte,$i,'abschnitt.nachbereitung')).", ".leer_NULL(sql_result($abschnitte,$i,'abschnitt.sozialform')).", ".leer_NULL(sql_result($abschnitte,$i,'abschnitt.handlungsmuster')).", ".apostroph_bei_bedarf(sql_result($abschnitte,$i,'abschnitt.inhaltspositionen')).", ".leer_NULL(sql_result($abschnitte,$i,'abschnitt.aktiv')).", ".leer_NULL(sql_result($abschnitte,$i,'abschnitt.methode')).");");
			
			// Ueberschriften und Texte neu erzeugen
			$ueberschriften=db_conn_and_sql("SELECT * FROM ueberschrift WHERE abschnitt=".sql_result($abschnitte,$i,'block_abschnitt.abschnitt'));
			for ($n=0; $n<sql_num_rows($ueberschriften); $n++)
				db_conn_and_sql("INSERT INTO ueberschrift (abschnitt, ebene, text, typ) VALUES (".$abschnitt_id.", ".leer_NULL(sql_result($ueberschriften,$n,'ueberschrift.ebene')).", ".apostroph_bei_bedarf(sql_result($ueberschriften,$n,'ueberschrift.text')).", ".apostroph_bei_bedarf(sql_result($ueberschriften,$n,'ueberschrift.typ')).");");
				
			$texte=db_conn_and_sql("SELECT * FROM sonstiges WHERE abschnitt=".sql_result($abschnitte,$i,'block_abschnitt.abschnitt'));
			for ($n=0; $n<sql_num_rows($texte); $n++)
				db_conn_and_sql("INSERT INTO sonstiges (abschnitt, inhalt, typ) VALUES (".$abschnitt_id.", ".apostroph_bei_bedarf(sql_result($texte,$n,'sonstiges.inhalt')).", ".leer_NULL(sql_result($texte,$n,'sonstiges.typ')).");");
			
			// uebungen, tests, sonstiges material neu verlinken
			$uebungen=db_conn_and_sql("SELECT * FROM aufgabe_abschnitt WHERE abschnitt=".sql_result($abschnitte,$i,'block_abschnitt.abschnitt'));
			for ($n=0; $n<sql_num_rows($uebungen); $n++)
				db_conn_and_sql("INSERT INTO aufgabe_abschnitt (aufgabe, abschnitt, beispiel) VALUES (".leer_NULL(sql_result($uebungen,$n,'aufgabe_abschnitt.aufgabe')).", ".$abschnitt_id.", ".leer_NULL(sql_result($uebungen,$n,'aufgabe_abschnitt.beispiel')).");");
			
			$tests=db_conn_and_sql("SELECT * FROM test_abschnitt WHERE abschnitt=".sql_result($abschnitte,$i,'block_abschnitt.abschnitt'));
			for ($n=0; $n<sql_num_rows($tests); $n++)
				db_conn_and_sql("INSERT INTO test_abschnitt (test, abschnitt) VALUES (".leer_NULL(sql_result($tests,$n,'test_abschnitt.test')).", ".$abschnitt_id.");");
			
			$materialien=db_conn_and_sql("SELECT * FROM material_abschnitt WHERE abschnitt=".sql_result($abschnitte,$i,'block_abschnitt.abschnitt'));
			for ($n=0; $n<sql_num_rows($materialien); $n++)
				db_conn_and_sql("INSERT INTO material_abschnitt (material, abschnitt) VALUES (".leer_NULL(sql_result($materialien,$n,'material_abschnitt.material')).", ".$abschnitt_id.");");
			
			// neuen Abschnitt dem neuen Block zuordnen
			db_conn_and_sql("INSERT INTO `block_abschnitt` (`block`, `abschnitt`, `position`) VALUES (".injaway($_POST["neuer_block"]).", ".$abschnitt_id.", ".($i+$position).");");
			break;
		case "link":
			db_conn_and_sql("INSERT INTO `block_abschnitt` (`block`, `abschnitt`, `position`) VALUES (".injaway($_POST["neuer_block"]).", ".sql_result($abschnitte,$i,'block_abschnitt.abschnitt').", ".($i+$position).");");
			break;
		}
	}
	
	// fortlaufende Positionsnummern bei altem Block
	$abschnitte=db_conn_and_sql("SELECT * FROM `block_abschnitt` WHERE `block_abschnitt`.`block`=".injaway($_GET["alt_block"])." ORDER BY `block_abschnitt`.`position`");
	for ($i=0;$i<sql_num_rows($abschnitte);$i++)
		if (sql_result($abschnitte,$i,'block_abschnitt.position')!=$i)
			db_conn_and_sql("UPDATE `block_abschnitt` SET `position`=".$i." WHERE `block`=".injaway($_GET["alt_block"])." AND `abschnitt`=".sql_result($abschnitte,$i,'block_abschnitt.abschnitt'));

	// fortlaufende Positionsnummern bei neuem Block
	$abschnitte=db_conn_and_sql("SELECT * FROM `block_abschnitt` WHERE `block_abschnitt`.`block`=".injaway($_POST["neuer_block"])." ORDER BY `block_abschnitt`.`position`");
	for ($i=0;$i<sql_num_rows($abschnitte);$i++)
		if (sql_result($abschnitte,$i,'block_abschnitt.position')!=$i)
			db_conn_and_sql("UPDATE `block_abschnitt` SET `position`=".$i." WHERE `block`=".injaway($_POST["neuer_block"])." AND `abschnitt`=".sql_result($abschnitte,$i,'block_abschnitt.abschnitt'));
	?>
	<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
<?php
}
else {
	if (!proofuser("block", $_GET["block"]))
		die("Sie sind hierzu nicht berechtigt.");
   
   $titelleiste="Abschnitte verschieben / kopieren / verlinken";
	include $pfad."header.php"; ?>
	<body>
	<div id="mf">
		<ul class="r">
			<li><a href="javascript:window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
	<div class="inhalt">
	<p>Sie k&ouml;nnen Abschnitte entweder</p>
	<ul>
		<li>in einen anderen Block <strong>verschieben</strong> <img src="<?php echo $pfad ?>icons/doc_move.png" alt="move" />,</li>
		<li>im anderen Block <strong>kopieren</strong> <img src="<?php echo $pfad ?>icons/doc_copy.png" alt="copy" /> (duplizieren), so dass Sie sie dort ver&auml;ndern k&ouml;nnen, und der Abschnitt hier so bleibt, wie er ist (Texte und &Uuml;berschriften werden ver&auml;nderbar neu erzeugt, w&auml;hrend Bilder, Dateien, Aufgaben, Tests und sonstige Materialien wie &uuml;blich verlinkt werden) oder</li>
		<li>in einem andernen Block <strong>verlinken</strong> <img src="<?php echo $pfad ?>icons/doc_link.png" alt="link" />, so dass Sie diesen hier oder dort ver&auml;ndern k&ouml;nnen.</li>
	</ul>
	
	<form action="abschnitt_verschieben.php?eintragen=true&amp;alt_block=<?php echo $_GET["block"]; ?>" method="post">
	<fieldset>
	in welchen Block:
	<select name="neuer_block">
	<?php $bloecke = db_conn_and_sql("SELECT `block`.*,`lernbereich`.*,`lehrplan`.*,`schulart`.*,`faecher`.*, COUNT(`block_abschnitt`.`abschnitt`) AS `anzahl`
														FROM `block`
															LEFT JOIN `block_abschnitt` ON `block_abschnitt`.`block`=`block`.`id`,`lernbereich`,`lehrplan`, `lp_user`, `schulart`,`faecher`
														WHERE `lernbereich`.`lehrplan`=`lehrplan`.`id`
															AND `block`.`lernbereich`=`lernbereich`.`id`
															AND `lehrplan`.`fach` = `faecher`.`id`
															AND `lehrplan`.`schulart`=`schulart`.`id`
															AND `lp_user`.`lehrplan`=`lehrplan`.`id`
															AND `lp_user`.`user`=".$_SESSION['user_id']."
                                                            AND `lp_user`.`aktiv`=1
														GROUP BY `block`.`id`
														ORDER BY `lehrplan`.`schulart`, `lehrplan`.`fach`, `lernbereich`.`klassenstufe`, `lehrplan`.`zusatz`,`lernbereich`.`nummer`,`block`.`block_hoeher`, `block`.`position`");
		$lb=0;
		for ($h=0;$h<sql_num_rows($bloecke);$h++) {
			if (sql_result($bloecke,$h,"lernbereich.id")!=$lb) {
				if ($lb!=0) echo '</optgroup>';
				$lb=sql_result($bloecke,$h,"lernbereich.id");
				echo '<optgroup label="'.html_umlaute(sql_result($bloecke,$h,"schulart.kuerzel")).' '.html_umlaute(sql_result($bloecke,$h,"faecher.kuerzel")).' - Kl. '.sql_result($bloecke,$h,"lernbereich.klassenstufe").''.sql_result($bloecke,$h,"lehrplan.zusatz").' LB '.sql_result($bloecke,$h,"lernbereich.nummer").'. '.html_umlaute(sql_result($bloecke,$h,"lernbereich.name")).'">';
			}
			echo '<option value="'.sql_result($bloecke,$h,"block.id").'"'; if (sql_result($bloecke,$h,"block.id")==$_GET["block"]) echo ' selected="selected"'; echo '>'.html_umlaute(sql_result($bloecke,$h,"block.name")).' ('.sql_result($bloecke,$h,"anzahl").')</option>';
		}
		?>
		</optgroup>
	</select><br />
	und dort
	<select name="neue_pos">
		<option value="ende">an das Ende</option>
		<option value="anfang">an den Anfang</option>
	</select>
	</fieldset><br />
	
	<?php
	$abschnitte=db_conn_and_sql("SELECT * FROM `abschnitt`,`block_abschnitt` WHERE `block_abschnitt`.`abschnitt`=`abschnitt`.`id` AND `block_abschnitt`.`block`=".injaway($_GET["block"])." ORDER BY `block_abschnitt`.`position`");
	?>
    <script type="text/javascript">
	$(function() {
		<?php for ($i=0;$i<sql_num_rows($abschnitte);$i++) { ?>
	
        $( "#section_actions_<?php echo $i; ?>" ).buttonset();
        
        $('#nothing_<?php echo $i; ?>').button();
        $('#move_<?php echo $i; ?>').button();
        $('#duplicate_<?php echo $i; ?>').button();
        $('#link_<?php echo $i; ?>').button();
       <?php } ?>
	});
	</script>
	<style>
		<?php for ($i=0;$i<sql_num_rows($abschnitte);$i++) { ?>
		#section_actions_<?php echo $i; ?> img {
			float: left;
			margin-left: -10px;
			margin-top: -3px;
		}
		
		#section_actions_<?php echo $i; ?> label {
			width: 30px;
			height: 25px;
            float: left;
		}
      <?php } ?>
	</style>
	<table id="einzelstunde" class="einzelstunde" cellspacing="0" cellpadding="0">
   <tr><th style="width:120px;">Pos.</th>
        <th title="in Minuten">Zeit</th>
        <th>Inhalt</th>
        <th title="wird der Schritt von den Sch&uuml;lern in den Hefter &uuml;bernommen?">Hefter</th>
        <th title="optionale Zielangabe des Abschnitts">Ziel / Aktionen</th></tr>
	<?php
	$gesamtzeit=0;
	for ($i=0;$i<sql_num_rows($abschnitte);$i++) {
		$ansicht=einzelstundenansicht(sql_result($abschnitte,$i,'block_abschnitt.abschnitt'),"nicht_bearbeiten",$pfad); $gesamtzeit+=$ansicht['minuten']; ?>
	<tr><td align="center">
				<?php echo (sql_result($abschnitte,$i,'block_abschnitt.position')+1); ?>
				<?php /*if ($_GET["abschnitt"]==sql_result($abschnitte,$i,'block_abschnitt.abschnitt')) echo ' checked="checked"'; */ ?>
            <div id="section_actions_<?php echo $i; ?>">
					<label for="nothing_<?php echo $i; ?>" title="nichts tun">
						<img src="<?php echo $pfad ?>icons/ok.png" alt="ok" /></label>
					<input type="radio" id="nothing_<?php echo $i; ?>" name="action_<?php echo $i; ?>" checked="checked" value="nothing" />
					<label for="move_<?php echo $i; ?>" title="verschieben">
						<img src="<?php echo $pfad ?>icons/doc_move.png" alt="verschieben" /></label>
					<input type="radio" id="move_<?php echo $i; ?>" name="action_<?php echo $i; ?>" value="move" />
					<label for="duplicate_<?php echo $i; ?>" title="kopieren">
						<img src="<?php echo $pfad ?>icons/doc_copy.png" alt="verschieben" /></label>
					<input type="radio" id="duplicate_<?php echo $i; ?>" name="action_<?php echo $i; ?>" value="duplicate" />
					<label for="link_<?php echo $i; ?>" title="verlinken">
						<img src="<?php echo $pfad ?>icons/doc_link.png" alt="verschieben" /></label>
					<input type="radio" id="link_<?php echo $i; ?>" name="action_<?php echo $i; ?>" value="link" />
            </div>
				
				</td>
	        <td style="text-align: center;"><?php echo $ansicht['minuten']; ?>'</td>
	    <td><?php echo $ansicht['inhalt']; ?></td>
	    <td style="text-align: center;"><?php switch ($ansicht['hefter']) { case 0: echo "-"; break; case 1: echo '<img src="'.$pfad.'icons/merkteil.png" alt="Merkteil" title="Merkteil" />'; break; case 2: echo '<img src="'.$pfad.'icons/uebungsteil.png" alt="&Uuml;bungsteil" title="&Uuml;bungsteil" />'; break;} ?></td>
	    <td><?php if($ansicht['ziele']!="") echo "Ziel:<br />".$ansicht['ziele']; if (isset($ansicht['bemerkung'])) echo "<br />";
			if($ansicht['bemerkung']!="") echo "Bemerkung:<br />".$ansicht['bemerkung']; ?></td>
	</tr>
	<?php } ?>
   </table>
   <p>
	<button style="float: right;">Aktion durchf&uuml;hren</button><br style="clear: both;" />
    </p>
	</form>
	</div>
	</body>
	</html>
	<?php
}
?>
