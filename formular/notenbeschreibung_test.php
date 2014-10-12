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
include "../funktionen.php";
$pfad="../";
$titelleiste="Test an Zensurenspalte h&auml;ngen";

if ($_GET["aendern"]!="true") {
    include $pfad."header.php";
    echo '	<body>
        <div class="inhalt">
    ';
	if (!proofuser("notenbeschreibung",$_GET["beschreibung"]))
		die("Sie sind nicht berechtigt, den Test der Notenspalte zu &auml;ndern.");
	$beschreibung=db_conn_and_sql("SELECT * FROM `notenbeschreibung` WHERE `id`=".injaway($_GET["beschreibung"]));
	
	$select=false;
	echo '<h1>'.$titelleiste.'</h1>';
	echo '<p>F&uuml;r Zensurenspalte: '.sql_result($beschreibung,0,'notenbeschreibung.beschreibung').' der Fach-Klasse-Kombination: '.$subject_classes->nach_ids[sql_result($beschreibung,0,'notenbeschreibung.fach_klasse')]['farbanzeige'].'</p>';
	?>
	
	<form action="<?php echo $pfad; ?>formular/notenbeschreibung_test.php?aendern=true" method="post" accept-charset="ISO-8859-1">
	<input type="hidden" name="beschreibung" value="<?php echo $_GET["beschreibung"]; ?>" />
	<select name="test">
		<option value="">kein Test</option>
		<?php
			// Vor-Auswahl:
			$vorauswahl=db_conn_and_sql("SELECT * FROM `notenbeschreibung`,`plan`,`abschnittsplanung`,`test_abschnitt`
				WHERE `notenbeschreibung`.`plan`=`plan`.`id`
				AND `abschnittsplanung`.`plan`=`plan`.`id`
				AND `test_abschnitt`.`abschnitt`=`abschnittsplanung`.`abschnitt`
				AND `notenbeschreibung`.`id`=".injaway($_GET["beschreibung"]));
				
            $result=db_conn_and_sql("SELECT `test`.*, GROUP_CONCAT(`thema`.`bezeichnung` SEPARATOR ', ') AS `themen`,`lernbereich`.*, `notentypen`.*
                                 FROM `test`,`themenzuordnung`,`thema`,`lernbereich`,`notentypen`, `lehrplan`
                                 WHERE `themenzuordnung`.`id`=`test`.`id`
									AND `themenzuordnung`.`typ`=5
									AND `themenzuordnung`.`thema`=`thema`.`id`
									AND `test`.`lernbereich`=`lernbereich`.`id`
									AND `test`.`notentyp` = `notentypen`.`id`
									AND `test`.`user`=".$_SESSION['user_id']."
									AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
                                 GROUP BY `themenzuordnung`.`id`
                                 ORDER BY `lehrplan`.`fach`, `lernbereich`.`klassenstufe`,`lernbereich`.`nummer`,`themen`");
		$fach=''; $klasse='';
        for ($i=0;$i<sql_num_rows($result);$i++) {
			$result_2=db_conn_and_sql ( 'SELECT *
                       FROM `lernbereich`,`lehrplan`,`schulart`,`faecher`
                       WHERE `lernbereich`.`id`='.sql_result($result,$i,'test.lernbereich').'
                         AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
                         AND `lehrplan`.`schulart` = `schulart`.`id`
                         AND `lehrplan`.`fach` = `faecher`.`id`
                       ORDER BY `lehrplan`.`schulart`,`lehrplan`.`bundesland`,`lehrplan`.`jahr`,`lehrplan`.`fach`,`lernbereich`.`klassenstufe`,`lernbereich`.`nummer`' );
			if ($fach!=sql_result($result_2,0,'faecher.id') or $klasse!=sql_result($result,$i,'lernbereich.klassenstufe')) {
				if ($fach!='' and $klasse!='')
					echo '</optgroup>';
				echo '<optgroup label="'.html_umlaute(sql_result($result_2,0,'schulart.kuerzel')).' '.html_umlaute(sql_result($result_2,0,"faecher.kuerzel")).' - Kl. '.sql_result($result,$i,'lernbereich.klassenstufe').''.sql_result($result,$i,'lehrplan.zusatz').'">';
			}
			$fach=sql_result($result_2,0,'faecher.id');
			$klasse=sql_result($result,$i,'lernbereich.klassenstufe');
			echo '<option value="'.sql_result($result,$i,"test.id").'"';
			if (!$select and sql_result($vorauswahl,0,"test_abschnitt.test")==sql_result($result,$i,"test.id") or sql_result($beschreibung,0,'notenbeschreibung.test')==sql_result($result,$i,"test.id"))
				{ echo ' selected="selected"'; $select=true; }
			echo '>LB '.sql_result($result,$i,'lernbereich.nummer').': '.html_umlaute(sql_result($result,$i,'notentypen.kuerzel')).' '.html_umlaute(sql_result($result,$i,'test.titel')).' '.html_umlaute(sql_result($result,$i,'test.url')).' - Themen: '.html_umlaute(sql_result($result,$i,'themen')).'</option>';
		}
	?>
		</optgroup>
	</select>
	<input type="submit" class="button" value="eintragen" />
	</form>
    </div>
    </body>
</html>
<?php
}
else {
	if (proofuser("notenbeschreibung",$_POST["beschreibung"]))
		db_conn_and_sql("UPDATE `notenbeschreibung` SET `test`=".leer_NULL($_POST["test"])." WHERE `id`=".injaway($_POST["beschreibung"])); ?>
	<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>
	<?php
}
?>
