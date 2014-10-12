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

$pfad="./";
$titelleiste = 'Abschnittsplanung';
include($pfad."header.php");
include($pfad."funktionen.php");

$abschnitt_get=injaway($_GET["abschnitt"]);
if (isset($_GET["abschnitt"]) and !sql_result(db_conn_and_sql("SELECT block.user FROM block, block_abschnitt WHERE block_abschnitt.block=block.id AND block_abschnitt.abschnitt=".$abschnitt_get),0,"user")==$_SESSION['user_id'])
	die("Sie sind hierzu nicht berechtigt.");
if (isset($_GET["abschnitt"])) {
	if ($_GET["hausaufgabe"]>0) {
		db_conn_and_sql("INSERT INTO `hausaufgabe_abschnitt` (`abschnitt`, `hausaufgabe`) VALUES (".$abschnitt_get.", ".injaway($_GET["hausaufgabe"]).");");
	}
	else {
		$position=sql_num_rows(db_conn_and_sql("SELECT * FROM `abschnittsplanung` WHERE `plan`=".$_GET["plan"]));
		db_conn_and_sql("INSERT INTO `abschnittsplanung` (`abschnitt`, `plan`, `minuten`, `position`) VALUES (".$abschnitt_get.", ".injaway($_GET["plan"]).", ".leer_NULL($_GET["minuten"]).", ".$position.");");
	}
	echo '<body onload="opener.location.reload();">';
}
else echo '<body>';
?>
  	<div id="mf">
		<ul class="r">
			<li><a href="javascript:window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
	<div class="inhalt">

  <?php $result = db_conn_and_sql("SELECT `block`.*,`lernbereich`.*,`hoeher`.*, `lehrplan`.*,`schulart`.*,`faecher`.*, COUNT(`block_abschnitt`.`abschnitt`) AS `anzahl`
                                                       FROM `block` LEFT JOIN `block` AS `hoeher` ON `block`.`block_hoeher`=`hoeher`.`id`,`lernbereich`,`lehrplan`,`schulart`,`faecher`,`block_abschnitt`
                                                       WHERE `lernbereich`.`lehrplan`=`lehrplan`.`id`
                                                           AND `block`.`lernbereich`=`lernbereich`.`id`
                                                           AND `lehrplan`.`fach` = `faecher`.`id`
                                                           AND `lehrplan`.`schulart`=`schulart`.`id`
															AND `block_abschnitt`.`block`=`block`.`id`
															AND `block`.`user`=".$_SESSION['user_id']."
														GROUP BY `block`.`id`
													   ORDER BY `lehrplan`.`schulart`, `lehrplan`.`fach`,`lernbereich`.`klassenstufe`,`lernbereich`.`nummer`,`hoeher`.`position`, `block`.`position`"); ?>  
  Block: <select onchange="window.location.href = 'abschnittsplanung.php?block='+this.value+'&amp;plan=<?php echo $_GET["plan"]; ?>&amp;hausaufgabe=<?php echo $_GET["hausaufgabe"]; ?>';">
<?php $lb=0; for ($i=0;$i<sql_num_rows($result);$i++) {
		if (sql_result($result,$i,"lernbereich.id")!=$lb) {
			if ($lb!=0) echo '</optgroup>';
			$lb=sql_result($result,$i,"lernbereich.id");
			echo '<optgroup label="'.html_umlaute(sql_result($result,$i,"schulart.kuerzel")).' '.html_umlaute(sql_result($result,$i,"faecher.kuerzel")).' - Kl. '.sql_result($result,$i,"lernbereich.klassenstufe").' - '.sql_result($result,$i,"lernbereich.nummer").'. '.html_umlaute(sql_result($result,$i,"lernbereich.name")).'">'; }?>
	<option value="<?php echo sql_result($result,$i,"block.id"); ?>"<?php if (sql_result($result,$i,"block.id")==$_GET["block"]) echo ' selected="selected"'; ?>><?php if (sql_result($result,$i,"hoeher.id")>0) echo sql_result($result,$i,"hoeher.position").'.'; echo sql_result($result,$i,"block.position").'. '.html_umlaute(sql_result($result,$i,"block.name")); ?> (<?php echo sql_result($result,$i,"anzahl"); ?>)</option><?php } ?>
  </optgroup></select>  
  <?php
	$block = injaway($_GET["block"]);
	if (sql_result(db_conn_and_sql("SELECT user FROM block WHERE block.id=".$block),0,"user")!=$_SESSION['user_id'])
		die("Sie sind hierzu nicht berechtigt.");
	$db = new db;
	if ($_GET["plan"]>0) {
		$fach_klasse=db_conn_and_sql("SELECT * FROM plan WHERE plan.id=".injaway($_GET["plan"]));
		$fach_klasse=sql_result($fach_klasse,0,"plan.fach_klasse");
	}
	else if ($_GET["hausaufgabe"]>0) {
		$fach_klasse=db_conn_and_sql("SELECT * FROM hausaufgabe, plan WHERE plan.id=hausaufgabe.plan AND hausaufgabe.id=".injaway($_GET["hausaufgabe"]));
		$fach_klasse=sql_result($fach_klasse,0,"plan.fach_klasse");
	}
	
	if ($fach_klasse>0 and sql_result(db_conn_and_sql("SELECT user FROM fach_klasse WHERE id=".$fach_klasse),0,"user")!=$_SESSION['user_id'])
		die("Sie sind hierzu nicht berechtigt.");
	
	$abschnitte=db_conn_and_sql("SELECT * FROM `abschnitt`, `block_abschnitt` WHERE `block_abschnitt`.`abschnitt`=`abschnitt`.`id` AND `block_abschnitt`.`block`=".$block." ORDER BY `block_abschnitt`.`position`");
	if (@sql_num_rows($abschnitte)>0) {
		echo'<table id="einzelstunde" class="einzelstunde" cellspacing="0" cellpadding="0">
			<tr><th>Zeit<br />(in min)</th><th title="Position wird automatisch festgelegt - nur bei Bedarf &auml;ndern (tritt nach Speicherung in Kraft)">Aktion</th><th>Inhalt</th><th title="wird der Schritt von den Sch&uuml;lern in den Hefter &uuml;bernommen?">Hefter</th><th>Medium /<br />Sozialform</th><th title="optionale Zielangabe des Abschnitts">Ziel / Bemerkung</th></tr>';
		for ($i=0;$i<sql_num_rows($abschnitte);$i++) {
			if ($fach_klasse>0) {
				$in_fk_verwendet=db_conn_and_sql("SELECT * FROM abschnittsplanung, plan WHERE abschnittsplanung.abschnitt=".sql_result($abschnitte,$i,'abschnitt.id')." AND abschnittsplanung.plan=plan.id AND plan.fach_klasse=".$fach_klasse);
				$als_HA_verwendet=db_conn_and_sql("SELECT * FROM hausaufgabe_abschnitt, hausaufgabe,plan WHERE hausaufgabe_abschnitt.hausaufgabe=hausaufgabe.id AND hausaufgabe_abschnitt.abschnitt=".sql_result($abschnitte,$i,'abschnitt.id')." AND hausaufgabe.plan=plan.id AND plan.fach_klasse=".$fach_klasse);
			}
			$ansicht=einzelstundenansicht(sql_result($abschnitte,$i,'abschnitt.id'),"nicht bearbeiten",$pfad);
			$abschnitt=$db->abschnitt(sql_result($abschnitte,$i,'abschnitt.id'));
			echo '<tr>
				<td';
			if (sql_num_rows($in_fk_verwendet)>0) echo ' style="background-color: #b2daa4;" title="in einer Unterrichtsstunde verwendet"';
			else if (sql_num_rows($als_HA_verwendet)>0) echo ' style="background-color: #caf3bc;" title="als HA aufgegeben"';
			else echo ' style="background-color: #f3e6bc;" title="in dieser Fach-Klasse-Kombination noch nicht verwendet"';
			echo '><input type="text" id="zeit_'.sql_result($abschnitte,$i,'abschnitt.id').'" value="'.$ansicht['minuten'].'" size="1" maxlength="3" /></td>
				<td><input type="button" class="button" value="eintragen" onclick="if (document.getElementById(\'zeit_'.sql_result($abschnitte,$i,'abschnitt.id').'\').value>0) window.location.href = \'abschnittsplanung.php?block='.$_GET["block"].'&amp;plan='.$_GET["plan"].'&amp;abschnitt='.sql_result($abschnitte,$i,'abschnitt.id').'&amp;hausaufgabe='.$_GET["hausaufgabe"].'&amp;minuten=\'+document.getElementById(\'zeit_'.sql_result($abschnitte,$i,'abschnitt.id').'\').value+\'#ankerabschnitt_'.sql_result($abschnitte,$i,'abschnitt.id').'\'; else document.getElementById(\'zeit_'.sql_result($abschnitte,$i,'abschnitt.id').'\').style.border=\'solid red 1px\';" /></td>
				<td><a name="ankerabschnitt_'.sql_result($abschnitte,$i,'abschnitt.id').'"></a>'.str_replace("'","\'",$ansicht['inhalt']).'</td>
				<td align="center">';
				switch ($ansicht['hefter']) { case 0: echo "-"; break; case 1: echo '<img src="'.$pfad.'icons/merkteil.png" alt="Merkteil" title="Merkteil" />'; break; case 2: echo '<img src="'.$pfad.'icons/uebungsteil.png" alt="&Uuml;bungsteil" title="&Uuml;bungsteil" />'; break; }
				echo '</td>
				<td>'.$ansicht['medium'].' /<br />'.$ansicht['sozialform'].'</td>
				<td>'.$ansicht['ziele'].'</td>
			</tr>';
		}
		echo '</table>';
	}
	else echo "keine Abschnitte in diesem Block vorhanden";
	?>
	</div>
  </body>
</html>
