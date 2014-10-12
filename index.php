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
//  OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
//  Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
//  Siehe die GNU Affero Public License für weitere Details.
//
//  Sie sollten eine Kopie der GNU Affero Public License zusammen mit diesem
//  Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.

$pfad="./";

include($pfad."basic/titelleiste.php");
include($pfad."header.php");
include($pfad."funktionen.php");

?>
  <body>
<?php
// onbeforeunload="if (!confirm('wirklich?')) false;"
$navigation = $titelleiste;

//vorerst entfernt:
//include($pfad."basic/update.php");
//include($pfad."basic/upgrade.php");
include($pfad."basic/menu.php");
?>
	<img src="look/programm_name.png" class="programm" />
	<div class="logo"></div>
	<!--<div class="programmname">Kreda<div>Lehrstoff-Verwaltung</div></div>-->
	<?php if (!isset($_GET["tab"])) { ?>
    <div class="navigation"><?php echo $navigation; ?><a href="<?php echo $pfad; ?>formular/hilfe.php?inhalt=start" style="position:absolute; z-index: 2; right: 80px;" onclick ="fenster(this.href, 100); return false;" class="icon"><img src="<?php echo $pfad; ?>icons/hilfe.png" alt="hilfe" /></a></div>
	<div class="tooltip" id="tt_nachbereitung">
		Hier k&ouml;nnen Sie Ihre Unterrichtsstunden nachbereiten
		<ul>
			<li>Hausaufgaben- und Unterschriftenkontrolle</li>
			<li>Nachkorrektur von Unterrichtsabschnitten (Zeit und Inhalte)</li>
			<li>statistische Auswertung der Stunde</li>
			<li>Notizen f&uuml;r die n&auml;chste Stunde</li>
			</ul>
		</div>
	<div class="tooltip" id="tt_druckansicht">
		Alle Unterrichtsstunden der jeweiligen Tage werden druckfertig ausgegeben. In der Druckvorschau k&ouml;nnen einzelne Zoomstufen eingestellt werden. Au&szlig;erdem ist es &uuml;bersichtlicher, zweiseitig zu drucken und so nur A5-Bl&auml;tter zu haben.
		</div>
	<div class="tooltip" id="tt_geburtstage">
		Falls Sie die Geburtstage der Sch&uuml;ler angegeben haben, sehen Sie hier die Namen und Alter der Geburtstage der letzten 5 und n&auml;chsten 7 Tage.
		</div>
	<div class="tooltip" id="tt_tests">
		<p>Wird in den n&auml;chsten 14 Tagen ein Test geschrieben, zeigt Kreda diesen solange an, bis der jeweiligen "Zensurenspalte" ein Test zugeordnet wird (= der Test ist vorbereitet).</p>
		<p>Ist der Test-Termin vorbei, gibt es eine Test-Nachbereitungs-Anzeige. Erst wenn Sie dann das Feld "vollst&auml;ndig korrigiert am" mit einem Datum f&uuml;llen, wird der Test ausgeblendet. Au&szlig;erdem ist er damit zur R&uuml;ckgabe an die Klasse bereit (wird automatisch in der Druckansicht der Unterrichtsstunde erg&auml;nzt).</p>
		</div>
	<div class="inhalt">
		<?php
		/*$myquerytest=db_conn_and_sql("SELECT `abschnitt`.`id` FROM `abschnitt`");
		$test='';
		for ($i=0; $i<sql_num_rows($myquerytest); $i++) {
			$testid=sql_result(db_conn_and_sql("SELECT `block_abschnitt`.`abschnitt` FROM `block_abschnitt` WHERE `block_abschnitt`.`abschnitt`=".sql_result($myquerytest,$i,"abschnitt.id")),0,"abschnitt");
			if ($testid>0)
				$test=$test;
			else
				$test.=sql_result($myquerytest,$i,"abschnitt.id").", ";
		}
		echo $test;*/
		?>
		<div style="float: right; width: 500px;">
			<p><?php
			$formularziel="index.php";
			include $pfad."formular/notizen.php"; ?></p>
		</div>
			<?php
			$fruehestes_datum=date("Y-m-d",$timestamp-60*60*24*7); // 7 Tage
			$plan=db_conn_and_sql("SELECT *
			FROM `fach_klasse`,`klasse`,`faecher`,`plan` LEFT JOIN `block` AS `block1` ON `plan`.`block_1`=`block1`.`id` LEFT JOIN `block` AS `block2` ON `plan`.`block_2`=`block2`.`id`
			WHERE `plan`.`schuljahr`=".$aktuelles_jahr."
				AND `plan`.`fach_klasse`=`fach_klasse`.`id`
				AND `fach_klasse`.`klasse`=`klasse`.`id`
				AND `fach_klasse`.`fach`=`faecher`.`id`
				AND `plan`.`ausfallgrund` IS NULL
				AND `plan`.`nachbereitung`<>TRUE
				AND `plan`.`datum`<=".$CURDATE."
				AND `plan`.`datum`>'".$fruehestes_datum."'
				AND `fach_klasse`.`user`=".$_SESSION['user_id']."
			ORDER BY `plan`.`datum` DESC, `plan`.`startzeit` DESC");
			// AND `plan`.`vorbereitet`=TRUE
			
			$geburtstag=db_conn_and_sql("SELECT DISTINCT name,vorname, geburtstag, klasse.*,
					CONCAT_WS('-',YEAR(".$CURDATE."),LPAD(MONTH(schueler.geburtstag),2,'0'),LPAD(DAY(schueler.geburtstag),2,'0')) AS `geburtstagsdatum`,
					(YEAR(".$CURDATE.")-YEAR(schueler.geburtstag)) AS `alter`
				FROM `fach_klasse`,`klasse`,`schueler`
				WHERE `fach_klasse`.`anzeigen`=1
					AND `fach_klasse`.`klasse`=`klasse`.`id`
					AND `schueler`.`klasse`=`klasse`.`id`
					AND `schueler`.`aktiv`=1
					AND	".$CURDATE." -INTERVAL 5 DAY < CONCAT_WS('-',YEAR(".$CURDATE."),LPAD(MONTH(schueler.geburtstag),2,'0'),LPAD(DAY(schueler.geburtstag),2,'0'))
					AND	".$CURDATE." +INTERVAL 7 DAY > CONCAT_WS('-',YEAR(".$CURDATE."),LPAD(MONTH(schueler.geburtstag),2,'0'),LPAD(DAY(schueler.geburtstag),2,'0'))
					AND `fach_klasse`.`user`=".$_SESSION['user_id']."
					ORDER BY geburtstagsdatum");
			
		$drucken_heute=db_conn_and_sql("SELECT * FROM `plan`,`fach_klasse`
			WHERE `plan`.`datum`=".$CURDATE."
				AND `plan`.`gedruckt`<>1
				AND `plan`.`ausfallgrund` IS NULL
				AND `plan`.`fach_klasse`=`fach_klasse`.`id`
				AND `fach_klasse`.`user`=".$_SESSION['user_id']."
			ORDER BY `plan`.`startzeit`");
			$drucken_morgen=db_conn_and_sql("SELECT * FROM `plan`,`fach_klasse`
			WHERE `plan`.`datum`=".$CURDATE." + INTERVAL 1 DAY
				AND `plan`.`gedruckt`<>1
				AND `plan`.`ausfallgrund` IS NULL
				AND `plan`.`fach_klasse`=`fach_klasse`.`id`
				AND `fach_klasse`.`user`=".$_SESSION['user_id']."
			ORDER BY `plan`.`startzeit`");
			
			$auswerten=db_conn_and_sql("SELECT *, IF(`notenbeschreibung`.`datum` IS NULL,`plan`.`datum`,`notenbeschreibung`.`datum`) as `MyDatum`
			FROM `notentypen`, `fach_klasse`, `klasse`, `faecher`, `notenbeschreibung` LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
			WHERE `notenbeschreibung`.`notentyp`=`notentypen`.`id`
				AND `notenbeschreibung`.`fach_klasse`=`fach_klasse`.`id`
				AND `fach_klasse`.`klasse`=`klasse`.`id`
				AND `fach_klasse`.`fach`=`faecher`.`id`
				AND (`notenbeschreibung`.`datum`>=".$CURDATE." - INTERVAL 21 DAY OR `plan`.`datum`>=".$CURDATE." - INTERVAL 21 DAY)
				AND (`notenbeschreibung`.`datum`<=".$CURDATE." OR `plan`.`datum`<=".$CURDATE.")
				AND `notenbeschreibung`.`korrigiert` IS NULL
				AND `fach_klasse`.`user`=".$_SESSION['user_id']."
			GROUP BY `notenbeschreibung`.`id`
			ORDER BY `MyDatum`, `klasse`.`einschuljahr` DESC");
			
			//ging net richtig, aber bestimmt mit unterabfrage moeglich
				/*LEFT JOIN `abschnittsplanung` ON `abschnittsplanung`.`plan`=`plan`.`id`
				LEFT JOIN `test_abschnitt` ON `abschnittsplanung`.`abschnitt`=`test_abschnitt`.`abschnitt`*/
			$beschreibung=db_conn_and_sql("SELECT *, IF(`notenbeschreibung`.`datum` IS NULL,`plan`.`datum`,`notenbeschreibung`.`datum`) as `MyDatum`
			FROM `notentypen`, `fach_klasse`, `klasse`, `faecher`, `notenbeschreibung`
				LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
			WHERE `notenbeschreibung`.`notentyp`=`notentypen`.`id`
				AND `notenbeschreibung`.`fach_klasse`=`fach_klasse`.`id`
				AND `fach_klasse`.`klasse`=`klasse`.`id`
				AND `fach_klasse`.`fach`=`faecher`.`id`
				AND (`notenbeschreibung`.`datum`>".$CURDATE." OR `plan`.`datum`>".$CURDATE.")
				AND (`notenbeschreibung`.`datum`<".$CURDATE." + INTERVAL 14 DAY OR `plan`.`datum`<".$CURDATE." + INTERVAL 14 DAY )
				AND `notenbeschreibung`.`test` IS NULL
				AND `fach_klasse`.`user`=".$_SESSION['user_id']."
			GROUP BY `notenbeschreibung`.`id`
			ORDER BY `MyDatum`, `klasse`.`einschuljahr` DESC");
			// COUNT(noten.id) AS `notenanzahl`, , `noten` LEFT JOIN `notenbeschreibung` ON `noten`.`beschreibung`=`notenbeschreibung`.`id` GROUP BY `notenbeschreibung`.`id`
			
			$db=new db;
			$jahr=$db->aktuelles_jahr();
			if ($subject_classes->cont[$subject_classes->active]["id"]>0) {
				$schule=db_conn_and_sql("SELECT klasse.schule FROM klasse, fach_klasse WHERE klasse.id=fach_klasse.klasse AND fach_klasse.id=".$subject_classes->cont[$subject_classes->active]["id"]);
				$schule=sql_fetch_assoc($schule);
				$schule=$schule["schule"];
				
				if ($schule>0)
					$kopfnoten_zu_erledigen=db_conn_and_sql("SELECT name, bearbeitung_ab, bearbeitung_bis
					FROM kopfnote_rahmen
					WHERE schule=".$schule."
						AND bearbeitung_ab<=".$CURDATE."
						AND bearbeitung_bis>=".$CURDATE);
			}
			$letzte_zensuren_aenderungen=db_conn_and_sql("SELECT id, notenhash FROM fach_klasse
				WHERE anzeigen=1 AND user=".$_SESSION["user_id"]." AND notenhash IS NOT NULL
				ORDER BY notenhash DESC
				LIMIT 3");
			
		if (sql_num_rows($plan)==0 and
			sql_num_rows($geburtstag)==0 and
			sql_num_rows($drucken_heute)==0 and
			sql_num_rows($drucken_morgen)==0 and
			sql_num_rows($auswerten)==0 and
			sql_num_rows($beschreibung)==0 and
			sql_num_rows($letzte_zensuren_aenderungen)==0)
		echo '<p>Klicken Sie oben in der Navigationsleiste auf das Hilfe-Symbol um eine <a href="'.$pfad.'formular/hilfe.php?inhalt=start" onclick ="fenster(this.href, 100); return false;" class="icon">Starthilfe</a> zu bekommen.</p>';
		
		if (sql_num_rows($plan)>0) { ?>
		<b>Nachbereitung</b> offen f&uuml;r (letzte 7 Tage): <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_nachbereitung')" onmouseout="hideWMTT()" /><ul><?php
			for ($i=0;$i<sql_num_rows($plan);$i++) {
				echo '<li>'.datum_strich_zu_punkt_uebersichtlich(sql_result($plan,$i,"plan.datum"),"wochentag_kurz", false).' '.zeit_formatieren(sql_result($plan,$i,"plan.startzeit")).' Uhr: '.$subject_classes->nach_ids[sql_result($plan,$i,"fach_klasse.id")]["farbanzeige"];
				if (sql_result($plan,$i,"plan.datum")==date("Y-m-d",$timestamp)) echo ' <a href="javascript:fenster(\''.$pfad.'lessons/durchfuehransicht.php?plan='.sql_result($plan,$i,"plan.id").'\',\'Durchf&uuml;hransicht\')" class="icon" title="Durchf&uuml;hransicht der Stunde"><img src="./icons/durchfuehrung.png" alt="durchfuehren" /></a>';
				echo ' <a href="javascript:fenster(\''.$pfad.'formular/nachbereiten.php?plan='.sql_result($plan,$i,"plan.id").'\',\'Nachbereitung\')" class="icon" title="nachbereiten"><img src="./icons/nachbereiten.png" alt="nachbereiten" /></a></li>';
			} ?>
			</ul>
		<?php }
		
		if (sql_num_rows($geburtstag)>0) { ?>
		<div>
		<b>Geburtstage:</b> <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_geburtstage')" onmouseout="hideWMTT()" />
		<img id="img_geburtstage" src="<?php echo $pfad; ?>icons/clip_closed.png" alt="clip" onclick="javascript:clip('geburtstage', '<?php echo $pfad; ?>')" />
		<span id="span_geburtstage" style="display: none;"><br />
	    <ul><?php
			for ($i=0;$i<sql_num_rows($geburtstag);$i++) {
					echo '<li';
					if (substr(sql_result($geburtstag,$i,"geburtstagsdatum"),5)==date("m-d",$timestamp)
					or substr(sql_result($geburtstag,$i,"geburtstagsdatum"),5)==date("m-d",$timestamp+60*60*24)) echo ' style="color:red;"';
					echo '>'.datum_strich_zu_punkt(sql_result($geburtstag,$i,"schueler.geburtstag")).' ('.sql_result($geburtstag,$i,"alter").' J.) '.$wochennamen_kurz[date("w",mktime(0,0,0,substr(sql_result($geburtstag,$i,"geburtstagsdatum"),5,2),substr(sql_result($geburtstag,$i,"geburtstagsdatum"),8,2),substr(sql_result($geburtstag,$i,"geburtstagsdatum"),0,4)))].': '.html_umlaute(sql_result($geburtstag,$i,"schueler.name")).', '.html_umlaute(sql_result($geburtstag,$i,"schueler.vorname")).' <a href="index.php?tab=klassen&amp;auswahl='.sql_result($geburtstag,$i,"klasse.id").'">Kl. '.($aktuelles_jahr-sql_result($geburtstag,$i,"klasse.einschuljahr")+1)." ".sql_result($geburtstag,$i,"klasse.endung").'</a></li>
					'; }?></ul>
		</span>
		</div><?php }
		
		if (sql_num_rows($drucken_heute)>0 or sql_num_rows($drucken_morgen)>0) { ?>
			<div>
			<p>
			<b>Unterricht <img src="<?php echo $pfad; ?>icons/drucken.png" alt="drucker" />:</b> <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_druckansicht')" onmouseout="hideWMTT()" />
			<?php
			if (sql_num_rows($drucken_heute)>0) {  ?>
				<a href="<?php echo $pfad; ?>einzelstunde.php?plan_0=<?php echo sql_result($drucken_heute,0,"plan.id"); for ($i=1;$i<sql_num_rows($drucken_heute);$i++) echo '&amp;plan_'.$i.'='.sql_result($drucken_heute,$i,"plan.id"); ?>">heute (<?php echo datum_strich_zu_punkt_uebersichtlich(sql_result($drucken_heute,0,"plan.datum"),"wochentag_kurz",0); ?>)</a>
				<?php if (sql_num_rows($drucken_morgen)>0) echo " - ";}
			if (sql_num_rows($drucken_morgen)>0) { ?>
				<a href="<?php echo $pfad; ?>einzelstunde.php?plan_0=<?php echo sql_result($drucken_morgen,0,"plan.id"); for ($i=1;$i<sql_num_rows($drucken_morgen);$i++) echo '&amp;plan_'.$i.'='.sql_result($drucken_morgen,$i,"plan.id"); ?>">morgen (<?php echo datum_strich_zu_punkt_uebersichtlich(sql_result($drucken_morgen,0,"plan.datum"),"wochentag_kurz",0); ?>)</a>
			<?php } ?>
		</p></div><?php }
		
		if (@sql_num_rows($auswerten)>0 or @sql_num_rows($beschreibung)>0) { ?>
			<p><b><img src="<?php echo $pfad; ?>icons/test.png" alt="test" style="float: left; padding-right: 5px;" /> Tests:</b> <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_tests')" onmouseout="hideWMTT()" /><br />
			<?php
			if (@sql_num_rows($auswerten)>0) { ?>
		<?php
		// if (sql_result($auswerten,$i,"notenanzahl")<1)
		for ($i=0;$i<@sql_num_rows($auswerten);$i++) {
			echo datum_strich_zu_punkt_uebersichtlich(sql_result($auswerten,$i,"MyDatum"),"wochentag_kurz",0).': '.$subject_classes->nach_ids[sql_result($auswerten,$i,"fach_klasse.id")]["farbanzeige"].' '.html_umlaute(sql_result($auswerten,$i,"notentypen.kuerzel")).' - '.html_umlaute(sql_result($auswerten,$i,"notenbeschreibung.beschreibung")).'
				<a href="'.$pfad.'formular/noten_bearbeiten.php?beschreibung='.sql_result ( $auswerten, $i, 'notenbeschreibung.id' ).'" onclick="fenster(\''.$pfad.'formular/noten_bearbeiten.php?beschreibung='.sql_result ( $auswerten, $i, 'notenbeschreibung.id' ).'&amp;schuljahr='.$aktuelles_jahr.'\',\'Noten bearbeiten\'); return false;" title="bearbeiten" class="icon"><img src="./icons/zensur.png" alt="auswerten" /></a><br />';
			}
			}
		if (sql_num_rows($beschreibung)>0) { ?>
			<?php
			for ($i=0;$i<sql_num_rows($beschreibung);$i++) {
				echo '<div style="height: 20px;">';
				echo datum_strich_zu_punkt_uebersichtlich(sql_result($beschreibung,$i,"MyDatum"),"wochentag_kurz",0).': '.$subject_classes->nach_ids[sql_result($beschreibung,$i,"fach_klasse.id")]["farbanzeige"].' '.html_umlaute(sql_result($beschreibung,$i,"notentypen.kuerzel")).' - '.html_umlaute(sql_result($beschreibung,$i,"notenbeschreibung.beschreibung"));
				echo ' <a href="'.$pfad.'formular/notenbeschreibung_test.php?beschreibung='.sql_result($beschreibung,$i,"notenbeschreibung.id").'"  title="vorbereiten" class="icon" onclick="fenster(this.href, \'Fenstertitel\'); return false;"><img src="'.$pfad.'icons/test.png" alt="test" style="padding-right: 5px;" /></a>';
				//if(sql_result($beschreibung,$i,"abschnittsplanung.plan")) echo sql_result($beschreibung,$i,"test_abschnitt.abschnitt").' <img src="'.$pfad.'icons/nachbereitet.png" alt="fertig" />';
				echo '</div>';
			}
		}
		?>
		</p>
		<?php 
		}
		
		$konferenzen=db_conn_and_sql("SELECT * FROM konferenz WHERE datum>=".$CURDATE." AND datum<".$CURDATE." + INTERVAL 7 DAY AND user=".$_SESSION['user_id']);
		if (sql_num_rows($konferenzen)>0) {
			echo '<p>';
			for ($i=0;$i<sql_num_rows($konferenzen);$i++) {
				echo '<span onmouseover="document.getElementById(\'konferenz_'.$i.'\').style.visibility=\'visible\';" onmouseout="document.getElementById(\'konferenz_'.$i.'\').style.visibility=\'hidden\';">
					<img src="'.$pfad.'icons/kalender.png" alt="kalender" /> '.datum_strich_zu_punkt_uebersichtlich(sql_result($konferenzen, $i, "konferenz.datum"),"wochentag_kurz",0).' '.zeit_formatieren(sql_result($konferenzen, $i, "konferenz.zeit")).': <b>'.sql_result($konferenzen, $i, "konferenz.titel").'</b>';
				if (sql_result($konferenzen, $i, "konferenz.klasse")>0) // Elternabend oder Konferenz bearbeiten
					echo ' <a id="konferenz_'.$i.'" href="'.$pfad.'index.php?tab=klassen&amp;auswahl='.sql_result($konferenzen, $i, "konferenz.klasse").'&amp;option=elternabend&amp;eintragen=bearbeiten&amp;klasse='.sql_result($konferenzen, $i, "konferenz.klasse").'&amp;id='.sql_result($konferenzen, $i, "konferenz.id").'" class="icon" style="visibility: hidden;"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>';
				else echo ' <a id="konferenz_'.$i.'" href="'.$pfad.'index.php?tab=stundenplan&amp;auswahl=konferenz&amp;eintragen=bearbeiten&amp;id='.sql_result($konferenzen, $i, "konferenz.id").'" class="icon" style="visibility: hidden;"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>';
				echo '</span><br />';
			}
			echo '</p>';
		}
		
		if (sql_num_rows($kopfnoten_zu_erledigen)>0) {
			echo '<p><b>Kopfnoten:</b><ul>';
			while($kopfnoten = sql_fetch_assoc($kopfnoten_zu_erledigen))
				echo '<li>'.$kopfnoten["name"].' ('.datum_strich_zu_punkt_uebersichtlich($kopfnoten["bearbeitung_ab"], true, false).' - '.datum_strich_zu_punkt_uebersichtlich($kopfnoten["bearbeitung_bis"], true, false).') <a href="'.$pfad.'index.php?tab=noten&option=kopfnote" class="icon"><img src="'.$pfad.'icons/kopfnote.png" alt="Kopfnoten" /></a></li>';
			echo '</ul></p>';
		}
		
		if (sql_num_rows($letzte_zensuren_aenderungen)>0) {
			echo '<p><b>Zensuren&auml;nderungen:</b><ul>';
			while($letzte_aenderung = sql_fetch_assoc($letzte_zensuren_aenderungen))
				echo '<li>'.datum_strich_zu_punkt_uebersichtlich(substr($letzte_aenderung["notenhash"],0,10), true, false).' - '.substr($letzte_aenderung["notenhash"],11,5).' Uhr - '.$subject_classes->nach_ids[$letzte_aenderung["id"]]["farbanzeige"].'</li>';
			echo '</ul></p>';
		}
		
		?>
		<p style="color: lightgrey;">Bitte alle Benutzbarkeitsprobleme, Ideen und Programmfehler als <a href="http://kirche-neudorf.de/flyspray/index.php?do=toplevel&project=2" target="blank">Aufgabe</a> melden. Bitte sehen Sie nach, ob es nicht ein Aufgabenduplikat gibt.</p>
		<?php if ($_SESSION["user_id"]==1) { ?>
			<p><a href="<?php echo $pfad; ?>offline/offlinestart.php">Offline-Version</a></p>
		<?php } ?>
		<br style="clear: both;" />
		<div style="text-align: right; font-size: 7pt;"><a href="<?php echo $pfad; ?>info.php" onclick="fenster(this.href, '&Uuml;ber diese Software'); return false;">&Uuml;ber Kreda <?php echo $programmversion; ?></a></div>
	    <div style="text-align: center; font-size:7pt; color: #a5c3d4;">Soli Deo Gloria</div>
	</div>
		<?php
	}
	
	
	
	if ($_GET['tab']=='klassen') {
		$result = db_conn_and_sql ( 'SELECT DISTINCT `klasse`.`id`, `klasse`.`einschuljahr`,`klasse`.`endung`
			FROM `klasse`,`fach_klasse`
			WHERE `fach_klasse`.`klasse`=`klasse`.`id`
				AND `fach_klasse`.`anzeigen`=1
				AND `fach_klasse`.`user`='.$_SESSION['user_id'].'
			ORDER BY `klasse`.`einschuljahr` DESC, `klasse`.`endung`' );
    
    $gewaehlte_klasse=$subject_classes->cont[$subject_classes->active]["klasse_id"];
    if ($_GET["auswahl"]>0)
		$gewaehlte_klasse=injaway($_GET["auswahl"]);
    $gewaehlte_fach_klasse=$subject_classes->cont[$subject_classes->active]["id"];
    
	if ($_GET["zweit"]!="alle" and isset($_GET["new_sc"]))
		$gewaehlte_klasse=sql_result(db_conn_and_sql("SELECT fach_klasse.klasse FROM benutzer, fach_klasse WHERE benutzer.letzte_fachklasse=fach_klasse.id AND fach_klasse.user=".$_SESSION['user_id']),0,"fach_klasse.klasse");
	
	if ($_GET["zweit"]!="alle" and proofuser("klasse",$gewaehlte_klasse)) {
		
		if ($_GET["option"]=="schueleruebersicht" or !isset($_GET["option"])) {
		$rechte_an_schuelern=userrigths("schuelerdaten", $gewaehlte_klasse);
		
		if ($rechte_an_schuelern>0) {
			// konkrete Klasse gewaehlt -> dann werden die Schueler der Klasse angezeigt (sonst die der Fach-Klasse)
			if ($_GET["auswahl"]>0)
				$result = db_conn_and_sql("SELECT *
					FROM `schueler`
					WHERE `schueler`.`klasse` = ".injaway($gewaehlte_klasse)."
					ORDER BY `schueler`.`position`, `schueler`.`name`, `schueler`.`vorname`");
			else
				$result = schueler_von_fachklasse($gewaehlte_fach_klasse);
		}
		else
			die("Sie haben nicht die erforderlichen Rechte, um Daten der Klasse einzusehen."); ?>
		 <div class="navigation_3">
			 <?php if ($rechte_an_schuelern==2) { ?>
			<a href="<?php echo $pfad; ?>formular/schueler_neu.php?klasse=<?php echo $gewaehlte_klasse; ?>" onclick="fenster(this.href,600); return false;" class="auswahl">
				<img src="<?php echo $pfad; ?>icons/neu.png" alt="neu" /> Sch&uuml;ler hinzuf&uuml;gen</a>
			<?php }
			if (sql_num_rows ( $result )>0) {
				if ($rechte_an_schuelern==2) { ?>
					<a href="<?php echo $pfad; ?>formular/schueler_bearbeiten.php?klasse=<?php echo $gewaehlte_klasse; ?>" class="auswahl">
						<img src="<?php echo $pfad; ?>icons/edit.png" alt="bearbeiten" /> Sch&uuml;ler bearbeiten</a>
				<?php } ?>
				<a href="index.php?tab=klassen&amp;auswahl=<?php echo $gewaehlte_klasse; ?>&amp;option=schueleruebersicht&amp;option_2=schuelerliste"<?php if ($_GET["option_2"]=="schuelerliste") echo ' class="selected"'; else echo ' class="auswahl"'; ?>><img src="<?php echo $pfad; ?>/icons/schuelerliste.png" alt="schuelerliste" /> Sch&uuml;lerliste</a>
				<!--<a href="<?php echo $pfad; ?>formular/schueler_druckansicht.php?fach_klasse=<?php echo $gewaehlte_fach_klasse; ?>" onclick="fenster(this.href, 'Klassenbuchansicht'); return false;" class="auswahl"><img src="<?php echo $pfad; ?>icons/klassenbuch.png" alt="klassenbuch" /> Klassenbuchansicht</a>-->
			<?php }
			echo $navigation; ?></div>
		<div class="inhalt">
	<?php
		if ($_GET["option_2"]!="schuelerliste") {
    if (@sql_num_rows ( $result )<1)
		echo 'Der Klasse sollten Sch&uuml;ler hinzugef&uuml;gt werden.';
    else { ?>
    <table class="tabelle" cellspacing="0">
      <tr>
		<th>Pos</th>
        <th>Name, Vorname</th>
        <th><img src="<?php echo $pfad; ?>/icons/anschrift.png" alt="anschrift" title="Anschrift (Stra&szlig;e; PLZ Ort)" /></th>
        <th><img src="<?php echo $pfad; ?>/icons/geburtstag.png" alt="geburtstag" title="Geburtsdatum" />/Geb.ort</th>
        <th><img src="<?php echo $pfad; ?>/icons/telefon.png" alt="telefon" title="Notfall-Telefonnummer" />/<img src="<?php echo $pfad; ?>/icons/email.png" alt="email" title="eMail-Adresse" /></th>
        <th>Notfallperson/KK</th>
        <th title="Bemerkungen"><img src="<?php echo $pfad; ?>/icons/kommentar.png" alt="bemerkung" title="Bemerkungen" /></th>
        <th class="nicht_drucken">Aktionen</th>
      </tr>
      <?php
      for($i=0;$i<(sql_num_rows ( $result ));$i++) { ?>
      <tr<?php if (sql_result ( $result, $i, 'schueler.aktiv' )!=1) echo ' style="color: lightgray;"'; ?>>
        <td><?php echo sql_result ( $result, $i, 'schueler.position' ); ?></td>
        <td<?php
			if (sql_result ( $result, $i, 'schueler.aktiv' )!=1)
				echo ' style="text-decoration: line-through; color: lightgray;"';
			echo '>';
			echo pictureOfPupil(sql_result ( $result, $i, 'schueler.name' ), sql_result ( $result, $i, 'schueler.vorname' ), sql_result ( $result, $i, 'schueler.number' ), sql_result ( $result, $i, 'schueler.username' ), $pfad, 'height="40" style="float:right"');
			echo html_umlaute(@sql_result ( $result, $i, 'schueler.name' )).", ".html_umlaute(@sql_result ( $result, $i, 'schueler.vorname' ))." "; if(@sql_result ( $result, $i, 'schueler.maennlich' )) echo '<img src="./icons/male.png" alt="maennlich" style="height: 15px;" />'; else echo '<img src="./icons/female.png" alt="weiblich" style="height: 15px;" />'; ?></td>
        <td><?php echo html_umlaute(@sql_result ( $result, $i, 'schueler.strasse' )); ?><br /><?php echo html_umlaute(@sql_result ( $result, $i, 'schueler.ort' )); if (@sql_result ( $result, $i, 'schueler.strasse' )!="" and @sql_result ( $result, $i, 'schueler.ort' )!="") { ?> <a href="http://maps.google.de/maps?q=<?php echo str_replace(" ","+",html_umlaute(@sql_result ( $result, $i, 'schueler.strasse' ))); ?>%3B+<?php echo str_replace(" ","+",html_umlaute(@sql_result ( $result, $i, 'schueler.ort' ))); ?>" onclick="fenster(this.href, 'Karte'); return false;" class="icon" title="bei Google-Maps nachschlagen"><img src="<?php echo $pfad; ?>icons/karte.png" alt="karte" /></a><?php } ?></td>
        <td><?php echo datum_strich_zu_punkt(@sql_result ( $result, $i, 'schueler.geburtstag' )); ?><br /><?php echo html_umlaute(@sql_result ( $result, $i, 'schueler.geburtsort' )); ?></td>
        <td><?php echo html_umlaute(@sql_result ( $result, $i, 'schueler.telefon' )); ?><br /><a href="mailto:<?php echo html_umlaute(@sql_result ( $result, $i, 'schueler.email' )); ?>" title="eMail versenden"><?php echo html_umlaute(@sql_result ( $result, $i, 'schueler.email' )); ?></a></td>
        <td><?php echo html_umlaute(@sql_result ( $result, $i, 'schueler.notfall' )); ?><br /><?php echo html_umlaute(@sql_result ( $result, $i, 'schueler.krankenkasse' )); ?></td>
        <td><?php
			$kontakte=db_conn_and_sql("SELECT datum, inhalt FROM elternkontakt WHERE elternkontakt.schueler=".sql_result($result, $i, 'schueler.id')." AND elternkontakt.user=".$_SESSION["user_id"]." ORDER BY datum DESC");
			$kontakte_anzahl=sql_num_rows($kontakte);
			echo '<a href="'.$pfad.'formular/elterngespraeche.php?schueler='.sql_result($result, $i, 'schueler.id').'" onclick="fenster(this.href, \'Eltern\'); return false;" class="icon" title="Eltern- bzw. Sch&uuml;lerkontakte';
			if ($kontakte_anzahl>0) echo ' (letzter am: '.datum_strich_zu_punkt(sql_result($kontakte,0,"elternkontakt.datum"))."): ".html_umlaute(sql_result($kontakte,0,"elternkontakt.inhalt"));
			echo '"><img src="'.$pfad.'icons/elternkontakt.png" alt="elternkontakt" /> '.$kontakte_anzahl.'</a> <span title="'.nl2br(html_umlaute(@sql_result ( $result, $i, 'schueler.bemerkungen' ))).'">';
			if (sql_result ( $result, $i, 'schueler.bemerkungen' )!="")
				echo nl2br(html_umlaute(substr(sql_result ( $result, $i, 'schueler.bemerkungen' ),0,20))).'...';
			echo '</span>';
			?></td>
        <td class="nicht_drucken">
			<?php
			if ($rechte_an_schuelern!=0) { //alle Lehrer
				echo '<a href="'.$pfad.'eltern/elternansicht.php?schueler='.sql_result ( $result, $i, 'schueler.id' ).'" title="Daten des Sch&uuml;lers ansehen" class="icon"><img src="'.$pfad.'icons/schueler.png" alt="schueler" /></a>';
			}
			if ($rechte_an_schuelern==2) { // nur Admin, SL und KL
			?> 
            <a href="<?php echo $pfad; ?>formular/schueler_verschieben.php?wen=<?php echo sql_result ( $result, $i, 'schueler.id' ); ?>&amp;ursprungsklasse=<?php echo $gewaehlte_klasse; ?>" title="Den Sch&uuml;ler in eine andere Klasse schieben" class="icon" onclick="fenster(this.href, 'Sch&uuml;ler verschieben'); return false;"><img src="<?php echo $pfad; ?>icons/verschieben.png" alt="verschieben" /></a>
			<?php
				if (@sql_result ( $result, $i, 'schueler.aktiv' )==1) {
					echo ' <a href="'.$pfad.'formular/schueler_loeschen.php?wen='.sql_result ( $result, $i, 'schueler.id' ).'&amp;auswahl='.$gewaehlte_klasse.'&amp;aktiv=0" title="aus der Klasse entfernen" class="icon"><img src="'.$pfad.'icons/plan_weg.png" alt="entfernen" /></a>';
				}
				else {
					echo ' <a href="'.$pfad.'formular/schueler_loeschen.php?wen='.sql_result ( $result, $i, 'schueler.id' ).'&amp;auswahl='.$gewaehlte_klasse.'&amp;aktiv=1" title="wieder eingliedern" class="icon"><img src="'.$pfad.'icons/plan.png" alt="eingliedern" /></a>';
					if (userrigths("klassendaten", $gewaehlte_klasse)==2)
						echo ' <a href="'.$pfad.'formular/schueler_loeschen.php?wen='.sql_result ( $result, $i, 'schueler.id' ).'&amp;auswahl='.$gewaehlte_klasse.'&amp;endgueltig=ja&amp;vorschau=ja" onclick="fenster(this.href, \'Sch&uuml;ler l&ouml;schen\'); return false;" title="l&ouml;schen" class="icon"><img src="'.$pfad.'icons/delete.png" alt="loeschen" /></a>';
				}
			} ?>
			</td>
      </tr>
      <?php } ?>
    </table>
    <?php }
	}
	
	if (!$_GET["auswahl"]>0)
		$result=schueler_von_fachklasse($gewaehlte_fach_klasse);
	
	// ---------------- Schuelerlisten ----------------------
	if ($_GET["option_2"]=="schuelerliste" and proofuser("klasse",$gewaehlte_klasse)) {
		?>
			<a href="<?php echo $pfad; ?>formular/liste_bearbeiten.php" onclick="fenster(this.href, 'Sch&uuml;lerliste'); return false;">neue Liste</a>
			
    <?php
		if ($subject_classes->cont[$subject_classes->active]["id"]>0)
		$listen_mit_schuelern_der_klasse = db_conn_and_sql ( 'SELECT liste.* FROM liste
			WHERE liste.fach_klasse='.$subject_classes->cont[$subject_classes->active]["id"].'
				AND liste.abgeschlossen IS NULL
			ORDER BY liste.erstelldatum, liste.id' );
		if ($subject_classes->cont[$subject_classes->active]["id"]>0)
		$schueler_mit_listen = db_conn_and_sql ( 'SELECT DISTINCT schueler.id, schueler.name, schueler.vorname FROM schueler, liste, liste_schueler
			WHERE liste.fach_klasse='.$subject_classes->cont[$subject_classes->active]["id"].'
				AND schueler.aktiv=1
				AND schueler.id=liste_schueler.schueler
				AND liste_schueler.liste=liste.id
				AND liste.abgeschlossen IS NULL
			ORDER BY schueler.position, schueler.name, schueler.vorname' );
		?>
		<table class="tabelle"><tr><th><?php echo $subject_classes->cont[$subject_classes->active]["farbanzeige"]."<br />Stand: ".date("d.m.y"); ?></th>
		<?php
		for ($liste=0; $liste<sql_num_rows($listen_mit_schuelern_der_klasse); $liste++) {
			echo '<th>'.sql_result($listen_mit_schuelern_der_klasse,$liste,"liste.name").' <a href="'.$pfad.'formular/liste_bearbeiten.php?eintragen=bearbeiten&amp;id='.sql_result($listen_mit_schuelern_der_klasse,$liste,"liste.id").'" onclick="fenster(this.href, \'Liste bearbeiten\'); return false;" class="icon" title="Grunddaten der Liste bearbeiten"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>
				<br />('.datum_strich_zu_punkt_uebersichtlich(sql_result($listen_mit_schuelern_der_klasse,$liste,"liste.erstelldatum"), false, false).')<br />f&auml;llig: '.datum_strich_zu_punkt_uebersichtlich(sql_result($listen_mit_schuelern_der_klasse,$liste,"liste.faellig"), false, false).'<br />
				 <a href="'.$pfad.'formular/listeneintraege.php?id='.sql_result($listen_mit_schuelern_der_klasse,$liste,"liste.id").'" onclick="fenster(this.href, \'Listeneintr&auml;ge\'); return false;" class="icon" title="Listeneintr&auml;ge ver&auml;ndern"><img src="'.$pfad.'icons/schuelerliste.png" alt="liste" /></a></th>';
		}
		echo '</tr>';
		for ($schueler=0; $schueler<sql_num_rows($schueler_mit_listen); $schueler++) {
			echo '<tr><td>'.sql_result($schueler_mit_listen, $schueler,"schueler.name").', '.sql_result($schueler_mit_listen, $schueler,"schueler.vorname").'</td>';
			for ($liste=0; $liste<sql_num_rows($listen_mit_schuelern_der_klasse); $liste++) {
				echo '<td>';
				$eintrag=db_conn_and_sql("SELECT * FROM liste_schueler WHERE liste=".sql_result($listen_mit_schuelern_der_klasse,$liste,"liste.id")." AND schueler=".sql_result($schueler_mit_listen, $schueler,"schueler.id"));
				if (sql_num_rows($eintrag)>0) {
					$listeneintragsinhalt='';
					$hilf=explode("||", sql_result($eintrag, 0, "inhalt"));
					foreach ($hilf as $listenhilfseintraege) {
						if ($listenhilfseintraege!="")
							if ($listeneintragsinhalt!="")
								$listeneintragsinhalt.="; ";
							$listeneintragsinhalt.=$listenhilfseintraege;
					}
					
					echo '<input type="checkbox" disabled="disabled" ';
					if (sql_result($eintrag, 0, "fertig")==1)
						echo 'checked="checked" ';
					echo '/> '.$listeneintragsinhalt;
				}
				echo '</td>';
			}
			echo '</tr>';
		}
		?>
		</tr>
		</table>
		<?php
    }
	
	
	echo '</div>';
	
    }
	
	// ------------------- HA-Statistik -------------------------
	if ($_GET["option"]=="statistik") { ?>
		<div class="navigation_3"><?php echo $navigation; ?></div>
		<div class="inhalt">

	<p>
	<?php
	if (proofuser("klasse",$gewaehlte_klasse)) {
		//$schueler=db_conn_and_sql("SELECT * FROM `schueler` WHERE `schueler`.`klasse`=".injaway($_GET["auswahl"])." AND `schueler`.`aktiv`=1 ORDER BY `schueler`.`position`");
		$schueler=schueler_von_fachklasse($subject_classes->cont[$subject_classes->active]["id"]);
	}
	// TODO will ich eine Uebersicht der Klasse mit allen meinen Faechern, ODER will ich EINE Fach-Klasse, aber auch mit Schuelern anderer Klassen??
	//$fach_klasse=db_conn_and_sql("SELECT * FROM `fach_klasse`,`faecher` WHERE `fach_klasse`.`anzeigen`=1 AND `fach_klasse`.`fach`=`faecher`.`id` AND `fach_klasse`.`klasse`=".injaway($_GET["auswahl"])." AND `fach_klasse`.`user`=".$_SESSION['user_id']);
	$db=new db;
	$jahr=$db->aktuelles_jahr();
	$schule=db_conn_and_sql("SELECT klasse.schule FROM klasse, fach_klasse WHERE klasse.id=fach_klasse.klasse AND fach_klasse.id=".$subject_classes->cont[$subject_classes->active]["id"]);
	$schule=sql_fetch_assoc($schule);
	$schule=$schule["schule"];
	$start_ende=schuljahr_start_ende($jahr, $schule);
	/*if (@sql_num_rows($fach_klasse)>0) {
		echo '<ul class="statistik_fach_klassen">';
		for($fk=0;$fk<sql_num_rows($fach_klasse);$fk++) echo '<li style="background-color: #'.html_umlaute(sql_result($fach_klasse,$fk,"fach_klasse.farbe")).'">'.html_umlaute(sql_result($fach_klasse,$fk,"faecher.kuerzel")).' '.html_umlaute(sql_result($fach_klasse,$fk,"fach_klasse.gruppen_name")).' <a href="index.php?tab=stundenplanung&amp;auswahl=hausaufgaben&amp;fk='.sql_result($fach_klasse,$fk,"fach_klasse.id").'" class="icon" title="Hausaufgaben der Fach-Klasse-Kombination bearbeiten"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a></li>';
		echo '</ul>';
	}*/
	?>
	</p>
	<div class="tooltip" id="tt_ha">
		Hier werden alle in einer Fach-Klasse-Kombination vergessenen Hausaufgaben angezeigt.</div>
	<div class="tooltip" id="tt_ha_nm">
		Hier werden alle in einer Fach-Klasse-Kombination vergessenen Hausaufgaben angezeigt, die nicht mitgez&auml;hlt werden sollen.</div>
	<div class="tooltip" id="tt_ber_unt">
		Hier werden alle in einer Fach-Klasse-Kombination vergessenen Berichtigungen und Unterschriften angezeigt.</div>
	<div class="tooltip" id="tt_ha_ber">
		Hier werden alle in einer Fach-Klasse-Kombination vergessenen Hauaufgaben + Berichtigungen angezeigt. Dies ist ein sinnvoller Indikator zum Vergleich von Sch&uuml;lern.</div>
	<div class="tooltip" id="tt_verwarnungen">
		Hier werden alle Betragensaufzeichnungen einer Fach-Klasse-Kombination angezeigt.</div>
	<div class="tooltip" id="tt_mitarbeit">
		Hier werden alle Mitarbeitsaufzeichnungen einer Fach-Klasse-Kombination angezeigt.</div>
	<?php
	
	if (sql_num_rows($schueler)>=1)
	for($i=0;$i<sql_num_rows($schueler);$i++) {
		//for($fk=0;$fk<sql_num_rows($fach_klasse);$fk++) {
			$vergessen[$i]['ha_anzahl']=0;
			$vergessen[$i]['ha_text']='';
			$vergessen[$i]['ha_nicht_zaehlen_anzahl']=0;
			$vergessen[$i]['ha_nicht_zaehlen_text']='';
			$vergessen[$i]['ber_anzahl']=0;
			$vergessen[$i]['ber_text']='';
			$vergessen[$i]['unt_anzahl']=0;
			$vergessen[$i]['unt_text']='';
			$hausaufgaben=db_conn_and_sql("SELECT *
				FROM `hausaufgabe_vergessen`, `hausaufgabe`,`plan`
				WHERE `hausaufgabe`.`abgabedatum`>='".$start_ende["start"]."'
					AND `hausaufgabe`.`abgabedatum`<='".$start_ende["ende"]."'
					AND `hausaufgabe_vergessen`.`hausaufgabe`=`hausaufgabe`.`id`
					AND `hausaufgabe`.`plan`=`plan`.`id`
					AND `plan`.`fach_klasse`=".$subject_classes->cont[$subject_classes->active]["id"]."
					AND `hausaufgabe_vergessen`.`schueler`=".sql_result($schueler,$i,"schueler.id"));
			if (sql_num_rows($hausaufgaben)>0) {
				for ($k=0;$k<sql_num_rows($hausaufgaben);$k++)
					if (sql_result($hausaufgaben,$k,"hausaufgabe.mitzaehlen")) {
						$vergessen[$i]['ha_text'].= datum_strich_zu_punkt(sql_result($hausaufgaben,$k,"plan.datum")).' ('.sql_result($hausaufgaben,$k,"hausaufgabe_vergessen.anzahl").') <br />';
						$vergessen[$i]['ha_anzahl']+=sql_result($hausaufgaben,$k,"hausaufgabe_vergessen.anzahl");
					}
					else {
						$vergessen[$i]['ha_nicht_zaehlen_text'].= sql_result($hausaufgaben,$k,"plan.datum").' ('.sql_result($hausaufgaben,$k,"hausaufgabe_vergessen.anzahl").') <br />';
						$vergessen[$i]['ha_nicht_zaehlen_anzahl']+=sql_result($hausaufgaben,$k,"hausaufgabe_vergessen.anzahl");
					}
			}
			$tests=db_conn_and_sql("SELECT *, IF(`notenbeschreibung`.`datum` IS NULL,`plan`.`datum`,`notenbeschreibung`.`datum`) as `MyDatum`
				FROM `berichtigung_vergessen`, `notenbeschreibung`
					LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
				WHERE (('".$start_ende["start"]."'<=`notenbeschreibung`.`datum` AND '".$start_ende["ende"]."'>=`notenbeschreibung`.`datum`)
						OR ('".$start_ende["start"]."'<=`plan`.`datum` AND '".$start_ende["ende"]."'>=`plan`.`datum`))
					AND `berichtigung_vergessen`.`notenbeschreibung`=`notenbeschreibung`.`id`
					AND `notenbeschreibung`.`fach_klasse`=".$subject_classes->cont[$subject_classes->active]["id"]."
					AND `berichtigung_vergessen`.`schueler`=".sql_result($schueler,$i,"schueler.id"));
			if (sql_num_rows($tests)>0)
				for ($k=0;$k<sql_num_rows($tests);$k++) {
					$test_datum=sql_result($tests,$k,"MyDatum");
					if (sql_result($tests,$k,"berichtigung_vergessen.berichtigung_anzahl")>0) {
						$vergessen[$i]['ber_text'].= sql_result($tests,$k,"notenbeschreibung.beschreibung").' '.datum_strich_zu_punkt($test_datum).' ('.sql_result($tests,$k,"berichtigung_vergessen.berichtigung_anzahl").') <br />';
						$vergessen[$i]['ber_anzahl']+=sql_result($tests,$k,"berichtigung_vergessen.berichtigung_anzahl");
					}
					if (sql_result($tests,$k,"berichtigung_vergessen.unterschrift_anzahl")>0) {
						$vergessen[$i]['unt_text'].= sql_result($tests,$k,"notenbeschreibung.beschreibung").' '.datum_strich_zu_punkt($test_datum).' ('.sql_result($tests,$k,"berichtigung_vergessen.unterschrift_anzahl").') <br />';
						$vergessen[$i]['unt_anzahl']+=sql_result($tests,$k,"berichtigung_vergessen.unterschrift_anzahl");
					}
				}
			
			$verwarnungen=db_conn_and_sql("SELECT * FROM `verwarnungen`, `plan`
				WHERE `plan`.`datum`>='".$start_ende["start"]."'
					AND `plan`.`datum`<='".$start_ende["ende"]."'
					AND `verwarnungen`.`plan`=`plan`.`id`
					AND `plan`.`fach_klasse`=".$subject_classes->cont[$subject_classes->active]["id"]."
					AND `verwarnungen`.`schueler`=".sql_result($schueler,$i,"schueler.id"));
			if (sql_num_rows($verwarnungen)>0) {
				for ($k=0;$k<sql_num_rows($verwarnungen);$k++)
					if (@sql_result($verwarnungen,$k,"verwarnungen.anzahl")!=0) {
						$vergessen[$i]['ver_text'].=datum_strich_zu_punkt(sql_result($verwarnungen,$k,"plan.datum")).' ('.sql_result($verwarnungen,$k,"verwarnungen.anzahl").') - '; // <br /> geht leider nicht
						$vergessen[$i]['ver_anzahl']+=sql_result($verwarnungen,$k,"verwarnungen.anzahl");
					}
			}
			
			$mitarbeit=db_conn_and_sql("SELECT * FROM `mitarbeit`, `plan`
				WHERE `plan`.`datum`>='".$start_ende["start"]."'
					AND `plan`.`datum`<='".$start_ende["ende"]."'
					AND `mitarbeit`.`plan`=`plan`.`id`
					AND `plan`.`fach_klasse`=".$subject_classes->cont[$subject_classes->active]["id"]."
					AND `mitarbeit`.`schueler`=".sql_result($schueler,$i,"schueler.id"));
			if (sql_num_rows($mitarbeit)>0) {
				for ($k=0;$k<sql_num_rows($mitarbeit);$k++)
					if (@sql_result($mitarbeit,$k,"mitarbeit.anzahl")!=0) {
						$vergessen[$i]['ma_text'].=datum_strich_zu_punkt(sql_result($mitarbeit,$k,"plan.datum")).' ('.sql_result($mitarbeit,$k,"mitarbeit.anzahl").') - '; // <br /> geht leider nicht
						$vergessen[$i]['ma_anzahl']+=sql_result($mitarbeit,$k,"mitarbeit.anzahl");
					}
			}
		// }
		
	}
	
	// Javascript - Tabelle zum Ordnen
	echo '<script>
		var Tabellendaten = new Array(';
	if (sql_num_rows($schueler)>=1)
	for($i=0;$i<sql_num_rows($schueler);$i++) {
		// sortier-Reihenfolge, Anzeige, Title
		if ($i>0) echo ','."\n";
		echo 'new Array("'.$i.'","'.sql_result($schueler,$i,"schueler.position").'. '.sql_result($schueler,$i,"schueler.name").' '.sql_result($schueler,$i,"schueler.vorname").'",""),';
		echo 'new Array("'.($i/100+$vergessen[$i]['ha_anzahl']).'","'.$vergessen[$i]['ha_anzahl'].'","'.$vergessen[$i]['ha_text'].'"),';
		echo 'new Array("'.($i/100+$vergessen[$i]['ha_nicht_zaehlen_anzahl']).'","'.$vergessen[$i]['ha_nicht_zaehlen_anzahl'].'","'.$vergessen[$i]['ha_nicht_zaehlen_text'].'"),';
		echo 'new Array("'.($i/100+$vergessen[$i]['ber_anzahl']).'","'.$vergessen[$i]['ber_anzahl']." / ".$vergessen[$i]['unt_anzahl'].'","B: '.$vergessen[$i]['ber_text'].' / U: '.$vergessen[$i]['unt_text'].'"),';
		echo 'new Array("'.($i/100+$vergessen[$i]['ha_anzahl']+$vergessen[$i]['ber_anzahl']).'","'.($vergessen[$i]['ha_anzahl']+$vergessen[$i]['ber_anzahl']).'","'.$vergessen[$i]['ha_text'].' / B: '.$vergessen[$i]['ber_text'].'"),';
		echo 'new Array("'.($i/100+$vergessen[$i]['ma_anzahl']).'","'.$vergessen[$i]['ma_anzahl'].'","'.$vergessen[$i]['ma_text'].'"),';
		echo 'new Array("'.($i/100+$vergessen[$i]['ver_anzahl']).'","'.$vergessen[$i]['ver_anzahl'].'","'.$vergessen[$i]['ver_text'].'")';
	}
	echo ');
	
var Spaltenueberschriften = new Array(
\''.$subject_classes->cont[$subject_classes->active]["farbanzeige"]."<br />Stand: ".date("d.m.y").'\',
\'HA <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\\\'tt_ha\\\')" onmouseout="hideWMTT()" />\',
\'HA <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\\\'tt_ha_nm\\\')" onmouseout="hideWMTT()" /><br />nicht gez.\',
\'Ber / Unt <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\\\'tt_ber_unt\\\')" onmouseout="hideWMTT()" />\',
\'HA+Ber <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\\\'tt_ha_ber\\\')" onmouseout="hideWMTT()" />\',
\'MA <a href="'.$pfad.'pupil/betr_ma.php?fach_klasse='.$gewaehlte_fach_klasse.'" class="icon" onclick="fenster(this.href); return false;"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a> <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\\\'tt_mitarbeit\\\')" onmouseout="hideWMTT()" />\',
\'Betragen <a href="'.$pfad.'pupil/betr_ma.php?fach_klasse='.$gewaehlte_fach_klasse.'" class="icon" onclick="fenster(this.href); return false;"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a> <img src="'.$pfad.'icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT(\\\'tt_verwarnungen\\\')" onmouseout="hideWMTT()" />\'
);

var Spaltensortierungen = new Array(
 "numerisch","numerisch","numerisch","numerisch","numerisch","numerisch","numerisch"
);

var sortierte_Tabellendaten = new Array(Tabellendaten.length);

var Spalten = Spaltenueberschriften.length;
var Zeilen = Tabellendaten.length / Spalten;

var breite = new Array('; for ($i=0; $i<count($vergessen); $i++) echo ($vergessen[$i]['ha_anzahl']+$vergessen[$i]['ber_anzahl']).","; echo '0);
var maximum=0;
var i=0;
   while (i<breite.length) {
      if (breite[i]>maximum)
         maximum = breite[i];
      i++;
   }

$(function() {
	Erzeuge_Sortierzeile(-1,\'\');
	Schreibe_Tabelle(Tabellendaten);
}); ';
	echo '</script>';
	?>
	<div id="Tabelle"></div>
	</div>
    <?php }
	
	if ($_GET["option"]=="fk") {
	$result = db_conn_and_sql ( 'SELECT *
                               FROM `faecher`, `fach_klasse`, `klasse`
                               WHERE `fach_klasse`.`klasse` = `klasse`.`id`
                                 AND `fach_klasse`.`fach` = `faecher`.`id`
                                 AND `fach_klasse`.`user`='.$_SESSION['user_id'].'
                                 AND `klasse`.`id` ='.$gewaehlte_klasse );
	$bewertungstabelle=db_conn_and_sql("SELECT * FROM `bewertungstabelle`
		WHERE `bewertungstabelle`.`aktiv`=1
			AND `bewertungstabelle`.`user`=".$_SESSION['user_id']."
		ORDER BY `bewertungstabelle`.`name`");
	$lehrplan=db_conn_and_sql("SELECT * FROM `lehrplan`,`faecher`,`schulart`,`lp_user`
		WHERE `lehrplan`.`fach`=`faecher`.`id`
			AND `lehrplan`.`schulart`=`schulart`.`id`
			AND `lehrplan`.`id`=`lp_user`.`lehrplan`
			AND `lp_user`.`user`=".$_SESSION['user_id']."
		ORDER BY `lehrplan`.`bundesland`, `lehrplan`.`schulart`, `lehrplan`.`fach`,`lehrplan`.`jahr` DESC");
	// TODO private Sitzplaene ausblenden (sitzplan-klasse benoetigt noch "user")
    $sitzplan=db_conn_and_sql("SELECT * FROM sitzplan, sitzplan_klasse
		WHERE sitzplan_klasse.sitzplan=sitzplan.id
			AND sitzplan_klasse.klasse=".$gewaehlte_klasse."
			AND sitzplan_klasse.user=".$_SESSION["user_id"]."
		ORDER BY sitzplan.name, sitzplan_klasse.seit DESC");
		?>
		<div class="navigation_3"><?php echo $navigation; ?></div>
		<div class="inhalt">
		<div class="tooltip" id="tt_fach">
			<p>W&auml;hlen Sie hier das Unterrichtsfach aus, in dem die Klasse unterrichtet wird. Ist das gew&uuml;nschte Fach nicht in der Auswahl, sollten Sie zun&auml;chst in den Einstellungen Ihre F&auml;cher angeben.</p>
			<p>Falls Sie z.B. wie im Fach Informatik &uuml;blich die Klasse in Gruppen unterteilen, legen Sie mehrere Fach-Klasse-Kombinationen mit dem gleichen Fach an und weisen diesen sp&auml;ter die entsprechenden Sch&uuml;ler zu.</p></div>
		<div class="tooltip" id="tt_anzeigen">
			Solange Sie die Fach-Klasse unterrichten, sollte diese "aktiv" sein. Dadurch ist sie im Fach-Klasse-W&auml;hler ausw&auml;hlbar und Zensuren bzw. Unterrichtsplanung ist m&ouml;glich.</div>
		<div class="tooltip" id="tt_farbe">
			Die hier gew&auml;hlte Farbe wird sowohl auf dem Stundenplan, im Fach-Klasse-W&auml;hler und anderen Stellen genutzt.</div>
		<div class="tooltip" id="tt_gruppenname">
			<p>Soll die Klasse in Gruppen geteilt werden, um z.B. 14-t&auml;gigen Unterricht durchzuf&uuml;hren, wie das oft im Informatikunterricht der Fall ist, k&ouml;nnen Sie hier einen Gruppennamen angeben. Diese Gruppe kann dann einzelnen Sch&uuml;lern zugeordnet werden.</p>
			<p>Sollten Sie also zwei Gruppen unterrichten, erstellen Sie auch zwei verschiedene Fach-Klasse-Kombinationen mit dem selben Fach und unterschiedlichem Gruppennamen.
			Auch wenn es sich um einen Neigungskurs oder F&ouml;rderunterricht handelt (bei denen nicht alle Sch&uuml;ler in der gew&auml;hlten Klasse sind), geben Sie einen Gruppennamen an, damit dieser den einzelnen Sch&uuml;lern zugeordnet werden kann.</p>
			<p>Falls Klassenstufe und Endung nicht mit angezeigt werden sollen (z.B. bei klassen&uuml;bergreifenden Lerngruppen), k&ouml;nnen Sie dies beim Gruppennamen einstellen. Durch Unterbringen von %k im Gruppennamen k&ouml;nnen Sie die Klassenstufe an gew&uuml;nschter Stelle dennoch anzeigen lassen.</p></div>
		<div class="tooltip" id="tt_lehrplan">
			<p>Geben Sie den Lehrplan an, nach dem die Klasse unterrichtet wird.</p> <div class="hinweis">Dieser muss zun&auml;chst bei "Unterricht" - "Fundus/Lehrpl&auml;ne" - "Lehrplan hinzuf&uuml;gen" erstellt werden.</div></div>
		<div class="tooltip" id="tt_bewertungstabelle">
			Die hier festzulegende Standard-Bewertungstabelle ist die Vorauswahl bei jeglichen Zensuren-Spalten. Die Auswahl kann dort nat&uuml;rlich f&uuml;r die einzelnen Zensurenspalten ver&auml;ndert werden. Kontrollieren Sie zun&auml;chst, ob Ihre Bewertungstabelle korrekt eingestellt ist ("Einstellungen" - "Zensuren").</div>
		<div class="tooltip" id="tt_informationen">
			Zusatzinformationen (z.B. Unterricht im Vorjahr)</div>

	<fieldset><legend>Neue Fach-Klasse-Kombination <img id="img_fk_komb" src="<?php echo $pfad.'icons/clip_'; if (isset($_GET["lehrauftrag"])) echo 'open'; else echo 'closed'; ?>.png" alt="clip" onclick="javascript:clip('fk_komb', '<?php echo $pfad; ?>')" /></legend>
	<span id="span_fk_komb"<?php if (!isset($_GET["lehrauftrag"])) echo ' style="display: none;"'; ?>>
    <form action="<?php echo $pfad; ?>formular/fach_klasse.php?aktion=neu&amp;klasse=<?php echo $gewaehlte_klasse; ?>" method="post" accept-charset="ISO-8859-1">
    
	<table class="tabelle" cellspacing="0">
      <tr>
		<th>Fach <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_fach')" onmouseout="hideWMTT()" /></th>
        <th>Einstellungen</th>
		<th>Informationen <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_informationen')" onmouseout="hideWMTT()" /></th>
        <th>Aktion</th>
      </tr>
      <tr>
        <td><?php if (isset($_GET["fach"])) {
			$fach = db_conn_and_sql ('SELECT `faecher`.`id`, `faecher`.`kuerzel`, `faecher`.`name` FROM `faecher` WHERE `faecher`.`id`='.injaway($_GET["fach"]));
			$fach = sql_fetch_assoc($fach);
			echo '<input type="hidden" name="fach_neu" value="'.injaway($_GET["fach"]).'" />'.$fach["kuerzel"];
		}
		else { ?><select name="fach_neu">
               <?php
               $faecher_auswahl = db_conn_and_sql ('SELECT `faecher`.`id`, `faecher`.`kuerzel`, `faecher`.`name`
						FROM `faecher`, `klasse`
						WHERE `klasse`.`id`='.$gewaehlte_klasse.'
							AND ((`faecher`.`user`=0 AND `faecher`.`schule`=0)
								OR (`faecher`.`user`=0 AND `faecher`.`schule`=`klasse`.`schule` AND `faecher`.`anzeigen`=1)
								OR (`faecher`.`anzeigen`=1 AND `faecher`.`user`='.$_SESSION['user_id'].'))');
                     while ($fach=sql_fetch_assoc ($faecher_auswahl))
						echo '<option value="'.$fach["id"].'" title="'.html_umlaute($fach["name"]).'">'.html_umlaute($fach["kuerzel"]).'</option>'."\n"; ?>
					</select>
		<?php } ?></td>
         <td><label for="anzeigen_neu">aktiv: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_anzeigen')" onmouseout="hideWMTT()" /></label> <input type="checkbox" name="anzeigen_neu" checked="checked" value="1" /><br />
			<?php if (isset($_GET["lehrauftrag"])) echo '<input type="hidden" name="lehrauftrag_lfd_nr" value="'.injaway($_GET["lehrauftrag"]).'" />'; ?>
			<label for="farbe_neu">Farbe <img src="<?php echo $pfad; ?>icons/farben.png" title="Farbe" alt="farben" />: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_farbe')" onmouseout="hideWMTT()" /></label>
			<select name="farbe_neu"><?php
				foreach ($farbenarray as $value) { ?><option value="<?php echo $value[0]; ?>" style="background-color:#<?php echo $value[0]; ?>;"<?php if(@sql_result ( $result, $i, 'fach_klasse.farbe' )==$value[0]) echo ' selected="selected"'; ?>><?php echo $value[1]; ?></option><?php } ?></select><br />
			<label for="gruppen_name_neu">Gruppenn. <img src="<?php echo $pfad; ?>icons/gruppe.png" title="Gruppenname" alt="gruppen_name" />: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_gruppenname')" onmouseout="hideWMTT()" /></label>
			<input type="text" name="gruppen_name_neu" size="5" maxlength="15" />
			<input type="checkbox" name="klassenanzeige_neu" value="1" checked="checked" title="Deaktivieren Sie diese Checkbox, falls Klassenstufe und Endung nicht mit angezeigt werden sollen. Durch Unterbringen von %k im Gruppennamen k&ouml;nnen Sie die Klassenstufe an gew&uuml;nschter Stelle dennoch anzeigen lassen." /> <br />
			<label for="lehrplan_neu">Lehrplan: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_lehrplan')" onmouseout="hideWMTT()" /></label>
			<select name="lehrplan_neu" title="Lehrplan eingeben"><?php if (sql_num_rows($lehrplan)>0) for ($j=0;$j<sql_num_rows($lehrplan);$j++) echo '<option value="'.sql_result($lehrplan,$j,"lehrplan.id").'">'.$bundesland[sql_result($lehrplan,$j,"lehrplan.bundesland")]['kuerzel'].' '.html_umlaute(sql_result($lehrplan,$j,"schulart.kuerzel")).' '.html_umlaute(sql_result($lehrplan,$j,"faecher.kuerzel")).' '.sql_result($lehrplan,$j,"lehrplan.jahr").' '.sql_result($lehrplan,$j,"lehrplan.zusatz").' ('.sql_result($lehrplan,$j,"lehrplan.von").'-'.sql_result($lehrplan,$j,"lehrplan.bis").')</option>'; ?></select><br />
			<label for="bewertungstabelle_neu">Bewertungst.: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_bewertungstabelle')" onmouseout="hideWMTT()" /></label>
			<select name="bewertungstabelle_neu" title="legen Sie hier die Standard-Bewertungstabelle der Fach-Klassen-Kombination fest"><?php echo bewertungstabelle_select(0); ?></select><br />
			<label for="notenberechnungsvorlage_neu">Zensurenber.:</label> <select name="notenberechnungsvorlage_neu" title="legen Sie hier die Zensurenberechnung der Fach-Klassen-Kombination fest">
				<?php echo notenberechnungsvorlagen_select(0,0); ?>
			</select><br />
            <label for="sitzplan_neu">Sitzplan:</label> <select name="sitzplan_neu" title="legen Sie hier den Sitzplan der Fach-Klassen-Kombination fest">
            <option value="">keiner (oder Klassenlehrer)</option>
            <?php if (sql_num_rows($sitzplan)>0) for ($j=0;$j<sql_num_rows($sitzplan);$j++) {
				echo '<option value="'.sql_result($sitzplan,$j,"sitzplan_klasse.id").'">'.html_umlaute(sql_result($sitzplan,$j,"sitzplan.name")).' - '.html_umlaute(sql_result($sitzplan,$j,"sitzplan_klasse.name")).' ('.datum_strich_zu_punkt(sql_result($sitzplan,$j,"sitzplan_klasse.seit")).')</option>';
			} ?></select>
			</td>
		 <td><textarea name="info_neu" rows="8" cols="25"></textarea></td>
         <td><input class="button" type="submit" value="hinzuf&uuml;gen" /></td>
      </tr>
    </table>
    </form>
	</span>
	</fieldset>
		
		<?php
	if (sql_num_rows ( $result )<1) echo '<p><span class="hinweis">Sie m&uuml;ssen mindestens ein Fach eintragen, in dem die Klasse von Ihnen unterrichtet wird.</span></p>'; 
	else { ?>
	<?php
	
	for($i=0;$i<sql_num_rows ( $result );$i++) {
		$bewertungstabelle_ausgewaehlt=false; ?>
	<p>
    <form action="<?php echo $pfad; ?>formular/fach_klasse.php?aktion=aendern&amp;klasse=<?php echo $gewaehlte_klasse; ?>" method="post" accept-charset="ISO-8859-1">
	<fieldset>
    <input type="hidden" name="fach_klasse" value="<?php echo sql_result ( $result, $i, 'fach_klasse.id' ); ?>" />
	<table class="tabelle" cellspacing="0">
      <tr>
		 <td style="width: 50px;"><?php echo html_umlaute(sql_result ( $result, $i, 'faecher.kuerzel' )); ?></td>
         <td style="background-color:#<?php echo html_umlaute(@sql_result ( $result, $i, 'fach_klasse.farbe' )); ?>;">
			<label for="anzeigen">aktiv:</label> <input type="checkbox" name="anzeigen"<?php if (sql_result ( $result, $i, 'fach_klasse.anzeigen' )) echo ' checked="checked"'; ?> value="1" /><br />
			<label for="farbe">Farbe <img src="<?php echo $pfad; ?>icons/farben.png" title="Farbe" alt="farben" />:</label> <select name="farbe"><?php
				foreach ($farbenarray as $value) { ?><option value="<?php echo $value[0]; ?>" style="background-color:#<?php echo $value[0]; ?>;"<?php if(@sql_result ( $result, $i, 'fach_klasse.farbe' )==$value[0]) echo ' selected="selected"'; ?>><?php echo $value[1]; ?></option><?php } ?></select><br />
			<label for="gruppen_name">Gruppenname <img src="<?php echo $pfad; ?>icons/gruppe.png" title="Gruppenname" alt="gruppen_name" />:</label>
			<input type="text" name="gruppen_name" value="<?php echo html_umlaute(@sql_result ( $result, $i, 'fach_klasse.gruppen_name' )); ?>" size="5" maxlength="15" />
			<input type="checkbox" value="1" name="klassenanzeige"<?php if (sql_result ( $result, $i, 'fach_klasse.klassenanzeige' )==1) echo ' checked="checked"'; ?> title="Deaktivieren Sie diese Checkbox, falls Klassenstufe und Endung nicht mit angezeigt werden sollen. Durch Unterbringen von %k im Gruppennamen k&ouml;nnen Sie die Klassenstufe an gew&uuml;nschter Stelle dennoch anzeigen lassen." /><br />
			<label for="lehrplan">Lehrplan:</label> <select name="lehrplan" title="Lehrplan eingeben"><?php if (sql_num_rows($lehrplan)>0) for ($j=0;$j<sql_num_rows($lehrplan);$j++) {
				echo '<option value="'.sql_result($lehrplan,$j,"lehrplan.id").'"';
				if (sql_result($lehrplan,$j,"lehrplan.id")==@sql_result ( $result, $i, 'fach_klasse.lehrplan' )) echo ' selected="selected"';
				echo '>'.$bundesland[sql_result($lehrplan,$j,"lehrplan.bundesland")]['kuerzel'].' '.html_umlaute(sql_result($lehrplan,$j,"schulart.kuerzel")).' '.html_umlaute(sql_result($lehrplan,$j,"faecher.kuerzel")).' '.sql_result($lehrplan,$j,"lehrplan.jahr").' '.sql_result($lehrplan,$j,"lehrplan.zusatz").' ('.sql_result($lehrplan,$j,"lehrplan.von").'-'.sql_result($lehrplan,$j,"lehrplan.bis").')</option>';
			} ?></select><br />
			<?php $lehrauftraege_result=db_conn_and_sql("SELECT * FROM lehrauftrag WHERE schuljahr=".$aktuelles_jahr." AND fach=".injaway(sql_result ( $result, $i, 'faecher.id' ))." AND klasse=".$gewaehlte_klasse." AND user=".$_SESSION["user_id"]." AND (fach_klasse IS NULL OR fach_klasse=".sql_result ( $result, $i, 'fach_klasse.id' ).")");
			if (sql_num_rows($lehrauftraege_result)>0) { ?>
				<input type="hidden" name="fach" value="<?php echo injaway(sql_result ( $result, $i, 'faecher.id' )); ?>" />
				<label for="lehrauftrag">Lehrauftrag:</label>
				<?php $checked=-1; $erster_la=-1;
					while ($lat = sql_fetch_assoc($lehrauftraege_result)) {
						if ($erster_la==-1)
							$erster_la=$lat["lfd_nr"];
						if (sql_result ( $result, $i, 'fach_klasse.id' )==$lat["fach_klasse"])
							$checked=$lat["lfd_nr"];
					} ?>
				<input type="hidden" name="lfd_nr" value="<?php if ($checked!=-1) echo $checked; else echo $erster_la; ?>" />
				<input type="checkbox" name="lehrauftrag"<?php if ($checked!=-1) echo ' checked="checked"'; ?> value="1" title="Haben Sie vom Schulleiter einen Lehrauftrag bekommen, k&ouml;nnen Sie diesen f&uuml;r die neue Fach-Klasse-Kombination nutzen, um z.B. Zensuren eintragen zu k&ouml;nnen." /><br />
				<?php
			} ?>
			<label for="bewertungstabelle">Bewertungstab.:</label> <select name="bewertungstabelle" title="legen Sie hier die Standard-Bewertungstabelle der Fach-Klassen-Kombination fest">
				<?php echo bewertungstabelle_select(sql_result ( $result, $i, 'fach_klasse.id' )); ?>
			</select><br />
			<label for="notenberechnungsvorlage">Zensurenber.:</label> <select name="notenberechnungsvorlage" title="legen Sie hier die Zensurenberechnung der Fach-Klassen-Kombination fest">
				<?php echo notenberechnungsvorlagen_select(sql_result ( $result, $i, 'fach_klasse.id' ),0); ?>
			</select><br />
            <label for="sitzplan">Sitzplan:</label> <select name="sitzplan" title="legen Sie hier den Sitzplan der Fach-Klassen-Kombination fest">
            <option value="">keiner (oder Klassenlehrer)</option>
            <?php if (sql_num_rows($sitzplan)>0) for ($j=0;$j<sql_num_rows($sitzplan);$j++) {
				echo '<option value="'.sql_result($sitzplan,$j,"sitzplan_klasse.id").'"';
				if (sql_result($sitzplan,$j,"sitzplan_klasse.id")==sql_result ( $result, $i, 'fach_klasse.sitzplan_klasse' )) echo ' selected="selected"';
				echo '>'.html_umlaute(sql_result($sitzplan,$j,"sitzplan.name")).' - '.html_umlaute(sql_result($sitzplan,$j,"sitzplan_klasse.name")).' ('.datum_strich_zu_punkt(sql_result($sitzplan,$j,"sitzplan_klasse.seit")).')</option>';
			} ?></select></td>
		 <td><textarea name="info" rows="8" cols="25"><?php echo html_umlaute(sql_result ( $result, $i, 'fach_klasse.info' )); ?></textarea></td>
         <td valign="bottom">
            <p><button style="width: 100%" onclick="fenster('<?php echo $pfad; ?>formular/fach_klasse_group.php?id=<?php echo sql_result ( $result, $i, 'fach_klasse.id' ); ?>', 'Gruppenzuordnung einer Fach-Klasse-Kombination'); return false;" title="Sch&uuml;lerzuordnungen einer Fach-Klasse-Kombination festlegen"><img src="<?php echo $pfad; ?>icons/gruppe.png" alt="delete" /> Gruppe</button></p>
            <?php if (userrigths("fachklasse_loeschen", sql_result ( $result, $i, 'fach_klasse.id' ))==2 or proofuser("fach_klasse", sql_result ( $result, $i, 'fach_klasse.id' ))) { ?>
            <p><button style="width: 100%" onclick="fenster('<?php echo $pfad; ?>formular/fach_klasse_delete.php?id=<?php echo sql_result ( $result, $i, 'fach_klasse.id' ); ?>', 'Fach-Klasse-Kombination l&ouml;schen'); return false;"><img src="<?php echo $pfad; ?>icons/delete.png" alt="delete" /> l&ouml;schen</button></p>
            <?php } ?>
            <p><button style="width: 100%" onclick="document.getElementsByTagName('form')[<?php echo $i; ?>].submit;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button></p></td>
      </tr>
	</table>
    </fieldset>
	</form>
	</p>
      <?php } ?>
	<?php } ?>
	
	<!--<input type="checkbox" onclick="style.visibility=this.checked==1?'visible':'hidden';">neue Fach-Klasse-Kombination
    <form name="fk-komb-neu" style="visibility: hidden;">
    </form>-->
	</div>

    <?php  }
	
		if ($_GET["option"]=="sitzplan") { ?>
		<div class="navigation_3">
	<?php
		if ($_GET["neu"]>0) {
			$_GET["sitzplan_klasse"]=db_conn_and_sql("INSERT INTO `sitzplan_klasse` (`name`,`klasse`,`seit`,`sitzplan`, `user`) VALUES ('Name', ".$gewaehlte_klasse.", '".date("Y-m-d", $timestamp)."', ".injaway($_GET["neu"]).", ".$_SESSION["user_id"].");");
		}
		
		$school_classes = new school_classes($aktuelles_jahr);
		$schule=db_conn_and_sql("SELECT schule FROM klasse WHERE klasse.id=".$gewaehlte_klasse);
		$schule=sql_fetch_assoc($schule);
		$schule=$schule["schule"];
		
		$sitzplan_arten=db_conn_and_sql("SELECT * FROM `sitzplan` WHERE (`aktiv`=1 AND `schule`=".$schule.") OR `id`=1 ORDER BY `sitzplan`.`name`");
		
		$sitzplaene=db_conn_and_sql("SELECT * FROM `sitzplan_klasse` LEFT JOIN `klasse` ON `klasse`.`kl_sitzplan`=`sitzplan_klasse`.`id`,`sitzplan`
			WHERE `sitzplan_klasse`.`sitzplan`=`sitzplan`.`id`
				AND `sitzplan_klasse`.`klasse`=".$school_classes->cont[$school_classes->active]["id"]."
				AND `sitzplan_klasse`.`user`=".$_SESSION["user_id"]."
			ORDER BY `sitzplan_klasse`.`sitzplan`, `sitzplan_klasse`.`seit` DESC");
		
		$sitzplan_id=sql_result($sitzplaene,0,"sitzplan_klasse.id");
		if (isset($_GET["sitzplan_klasse"])) {
			$klasse_ueberpruefung=db_conn_and_sql("SELECT sitzplan_klasse.klasse FROM sitzplan_klasse WHERE sitzplan_klasse.id=".injaway($_GET["sitzplan_klasse"])." AND `sitzplan_klasse`.`klasse`=".$school_classes->cont[$school_classes->active]["id"]);
			if (sql_num_rows($klasse_ueberpruefung)>0)
				$sitzplan_id=injaway($_GET["sitzplan_klasse"]);
		}
		
		if (isset($_GET["sitzplan_klasse"]) and $_GET["sitzplan_klasse"]>0
				and sql_num_rows($sitzplaene)>0
				and (!proofuser("sitzplan_klasse",$sitzplan_id) and userrigths("sitzplan_von_kl", $sitzplan_id)==0))
			die("Sie sind nicht berechtigt, den Sitzplan zu bearbeiten.");
		
		if ($school_classes->cont[$school_classes->active]["kl_sitzplan"]!=NULL)
			echo '<a class="auswahl" href="index.php?tab=klassen&amp;auswahl='.$gewaehlte_klasse.'&amp;option=sitzplan&amp;sitzplan_klasse='.$school_classes->cont[$school_classes->active]["kl_sitzplan"].'">Klassenlehrer</a>';
		
		for($i=0;$i<sql_num_rows($sitzplan_arten);$i++) {
			echo '<a href="index.php?tab=klassen&amp;auswahl='.$gewaehlte_klasse.'&amp;option=sitzplan';
			$n=0;
			while($n<sql_num_rows($sitzplaene) and sql_result($sitzplan_arten,$i,"sitzplan.id")!=sql_result($sitzplaene,$n,"sitzplan_klasse.sitzplan"))
			   $n++;
			if ($n<sql_num_rows($sitzplaene))
			   echo '&amp;sitzplan_klasse='.sql_result($sitzplaene,$n,"sitzplan_klasse.id");
			else
			   echo '&amp;sitzplan_klasse=0&amp;art='.sql_result($sitzplan_arten,$i,"sitzplan.id");
			//else echo '&amp;neu='.sql_result($sitzplan_arten,$i,"sitzplan.id");
			echo '"';
			while($n<sql_num_rows($sitzplaene) and $sitzplan_id!=sql_result($sitzplaene,$n,"sitzplan_klasse.id"))
				$n++;
			if ((sql_result($sitzplan_arten,$i,"sitzplan.id")==sql_result($sitzplaene,$n,"sitzplan_klasse.sitzplan") and $n<sql_num_rows($sitzplaene))
					or $_GET["art"]==sql_result($sitzplan_arten,$i,"sitzplan.id"))
				echo ' class="selected"';
			else
				echo ' class="auswahl"';
			echo '>'.html_umlaute(sql_result($sitzplan_arten,$i,"sitzplan.name")).'</a>
			';
		}
	?>
		<?php echo $navigation; ?></div>
		<?php
		$start=array("x"=>20, "y"=>130);
		$faktor=75;
		
		if ($sitzplan_id>0)
			$sitzplan=db_conn_and_sql("SELECT *
				FROM `sitzplan_klasse`, `sitzplan_objekt`
				WHERE `sitzplan_klasse`.`id`=".$sitzplan_id."
					AND `sitzplan_objekt`.`sitzplan`=`sitzplan_klasse`.`sitzplan`
					AND `sitzplan_klasse`.`user`=".$_SESSION["user_id"]."
				ORDER BY `sitzplan_klasse`.`seit` DESC, `sitzplan_objekt`.`id`");
		
		echo '<div class="inhalt" style="height: '.(100+$faktor*12).'px;">
			<form id="form" action="'.$pfad.'formular/sitzplan_aktion.php?aktion=ueberschreiben&amp;sitzplan_klasse_id='.$sitzplan_id.'&amp;klasse_id='.$gewaehlte_klasse.'" method="post" accept-charset="ISO-8859-1">
			<fieldset>';

		include($pfad."formular/sitzplan.php");
		
		echo '<div id="kopfdaten">';
		for($i=sql_num_rows($sitzplaene)-1;$i>=0;$i--) {
			if (sql_result($sitzplaene,$i-1,"sitzplan_klasse.id")==$sitzplan_id and sql_result($sitzplaene,$i-1,"sitzplan_klasse.sitzplan")==sql_result($sitzplaene,$i,"sitzplan_klasse.sitzplan"))
				echo '<a href="index.php?tab=klassen&amp;auswahl='.$gewaehlte_klasse.'&amp;option=sitzplan&amp;sitzplan_klasse='.sql_result($sitzplaene,$i,"sitzplan_klasse.id").'" class="icon" title="vorheriger Sitzplan"><img src="'.$pfad.'icons/pfeil_links.png" alt="Pfeil_links" /></a> ';
			if (sql_result($sitzplaene,$i,"sitzplan_klasse.id")==$sitzplan_id) {
				echo '<input type="text" name="name" size="12" value="'.html_umlaute(sql_result($sitzplaene,$i,"sitzplan_klasse.name")).'" />
						<input type="text" class="datepicker" name="datum" size="7" value="'.datum_strich_zu_punkt(sql_result($sitzplaene,$i,"sitzplan_klasse.seit")).'" />';
				if (sql_result($sitzplaene,$i,"klasse.kl_sitzplan")>0)
					echo ' | <img src="'.$pfad.'icons/sitzplan_prefered_selected.png" alt="empfohlen" title="dieser Sitzplan wird als Vorgabe anderen Fachlehrern angeboten" />';
				else
					if (userrigths("sitzplan_von_kl", $school_classes->cont[$school_classes->active]["id"])==2)
						echo ' | <a href="'.$pfad.'formular/sitzplan_aktion.php?aktion=empfehlen&amp;sitzplan_klasse_id='.$sitzplan_id.'&amp;klasse_id='.$gewaehlte_klasse.'" class="icon" title="diesen Sitzplan als Vorgabe anderen Fachlehrern anbieten"><img src="'.$pfad.'icons/sitzplan_prefered.png" alt="empfohlen" /></a>';
				echo ' | 
						<a href="javascript: window.print();" class="icon" title="drucken"><img src="'.$pfad.'icons/drucken.png" alt="drucken" /></a> |
						<a href="'.$pfad.'index.php?tab=klassen&amp;auswahl='.$gewaehlte_klasse.'&amp;option=sitzplan&amp;neu='.sql_result($sitzplaene,$i,"sitzplan_klasse.sitzplan").'" class="icon" title="neuen Sitzplan f&uuml;r Sitzordnung \''.html_umlaute(sql_result($sitzplaene,$i,"sitzplan.name")).'\' erstellen"><img src="'.$pfad.'icons/neu.png" alt="neu" /></a> |
						<a href="'.$pfad.'formular/sitzplan_aktion.php?aktion=loeschen&amp;sitzplan_klasse_id='.$sitzplan_id.'&amp;klasse_id='.$gewaehlte_klasse.'" class="icon" title="diesen Sitzplan l&ouml;schen" onclick="if (confirm(\'Der Sitzplan wird endg&uuml;ltig gel&ouml;scht. Wollen Sie das wirklich?\')==false) return false;"><img src="'.$pfad.'icons/delete.png" alt="loeschen" /></a>';
			}
			if (sql_result($sitzplaene,$i+1,"sitzplan_klasse.id")==$sitzplan_id and sql_result($sitzplaene,$i+1,"sitzplan_klasse.sitzplan")==sql_result($sitzplaene,$i,"sitzplan_klasse.sitzplan"))
				echo ' | <a href="index.php?tab=klassen&amp;auswahl='.$gewaehlte_klasse.'&amp;option=sitzplan&amp;sitzplan_klasse='.sql_result($sitzplaene,$i,"sitzplan_klasse.id").'" class="icon" title="n&auml;chster Sitzplan"><img src="'.$pfad.'icons/pfeil_rechts.png" alt="Pfeil_rechts" /></a> ';
		}
		
		if (sql_num_rows($sitzplaene)==0 and isset($_GET["neu"])) {
			echo '<input type="text" name="name" size="12" placeholder="Sitzplanname" />
					<input type="text" class="datepicker" name="datum" size="7" />';
		}
		echo '</div>';
		
		?>
		</form>
		<span class="nicht_drucken hinweis"><a href="<?php echo $pfad; ?>formular/hilfe.php?inhalt=drucktipps" onclick="fenster(this.href, 'Tipps'); return false;">Drucktipps</a></span>
	</div>

    <?php }
	
	}
    else {
    if ($_GET["zweit"]=="alle") { ?>
		<div class="navigation_2"><?php echo $navigation; ?></div>
		<div class="inhalt">
		<div class="tooltip" id="tt_klassenanzeige">
			Um eine Klasse auszuw&auml;hlen, ben&ouml;tigt sie mindestens eine aktive Fach-Klasse-Kombination. Ob eine Fach-Klasse-Kombination aktiv ist, sehen Sie an dem H&auml;kchen.
			<ul><li>Bewegen Sie die Maus &uuml;ber die Tabellenzeilen und klicken Sie auf das <img src="<?php echo $pfad; ?>icons/edit.png" alt="edit" />-Symbol der Klasse, um deren Stammdaten zu bearbeiten (wird angezeigt, falls Sie dazu berechtigt sind),</li>
				<li>auf das gleiche Symbol bei Sch&uuml;lern, um jene zu erg&auml;nzen (wird angezeigt, falls Sie dazu berechtigt sind)</li>
				<li>oder auf "<img src="<?php echo $pfad; ?>icons/fach_klasse.png" alt="edit" /> F&auml;cher", um Fach-Klasse-Kombinationen anzulegen, oder zu bearbeiten.</li>
			</ul>
			</div>
	<?php if (userrigths("schuldaten",$schule)==2) { ?>
	<form action="<?php echo $pfad; ?>formular/klasse_neu.php" method="post" accept-charset="ISO-8859-1">
    <fieldset><legend>Neue Klasse anlegen  <img id="img_neue_klasse" src="<?php echo $pfad; ?>icons/clip_closed.png" alt="clip" onclick="javascript:clip('neue_klasse', '<?php echo $pfad; ?>')" /></legend>
	<span id="span_neue_klasse" style="display: none;">
      im Schuljahr <?php echo $aktuelles_jahr.'/'.($aktuelles_jahr+1); ?> Klasse: 
      <select name="einschulung">
        <?php for ($i=1; $i<=13; $i++) { ?>
        <option value="<?php echo ($aktuelles_jahr-$i+1); ?>"><?php echo $i; ?></option>
        <?php } ?>
      </select>
      Endung: <input type="text" name="endung" size="3" maxlength="8" />
      Schule: <select name="schule"><?php
      $schule=db_conn_and_sql("SELECT * FROM `schule`, `schule_user`
		WHERE `schule`.`id`=`schule_user`.`schule`
			AND `schule_user`.`aktiv`=1
			AND `schule_user`.`user`=".$_SESSION['user_id']);
      for ($i=0;$i<sql_num_rows($schule);$i++) { ?>
        <option value="<?php echo sql_result($schule,$i,'schule.id'); ?>"><?php echo html_umlaute(sql_result($schule,$i,'schule.kuerzel')); ?></option>
      <?php } ?>
      </select>
	  Schulart: <select name="schulart"><?php $schulart=db_conn_and_sql("SELECT * FROM `schulart`"); for ($i=0;$i<sql_num_rows($schulart);$i++) { ?>
        <option value="<?php echo sql_result($schulart,$i,'schulart.id'); ?>"><?php echo html_umlaute(sql_result($schulart,$i,'schulart.kuerzel')); ?></option>
      <?php } ?>
      </select>
      
      <?php
	$bewertungstabelle=db_conn_and_sql("SELECT * FROM `bewertungstabelle`
		WHERE `bewertungstabelle`.`aktiv`=1
			AND `bewertungstabelle`.`user`=".$_SESSION['user_id']."
		ORDER BY `bewertungstabelle`.`name`");
	$lehrplan=db_conn_and_sql("SELECT * FROM `lehrplan`,`faecher`,`schulart`,`lp_user`
		WHERE `lehrplan`.`fach`=`faecher`.`id`
			AND `lehrplan`.`schulart`=`schulart`.`id`
			AND `lehrplan`.`id`=`lp_user`.`lehrplan`
			AND `lp_user`.`user`=".$_SESSION['user_id']."
		ORDER BY `lehrplan`.`bundesland`, `lehrplan`.`schulart`, `lehrplan`.`fach`,`lehrplan`.`jahr` DESC");
		?>
		<div class="tooltip" id="tt_fach">
			<p>W&auml;hlen Sie hier das Unterrichtsfach aus, in dem die Klasse unterrichtet wird. Ist das gew&uuml;nschte Fach nicht in der Auswahl, sollten Sie zun&auml;chst in den Einstellungen Ihre F&auml;cher angeben.</p>
			<p>Falls Sie z.B. wie im Fach Informatik &uuml;blich die Klasse in Gruppen unterteilen, legen Sie mehrere Fach-Klasse-Kombinationen mit dem gleichen Fach an und weisen diesen sp&auml;ter die entsprechenden Sch&uuml;ler zu.</p></div>
		<div class="tooltip" id="tt_anzeigen">
			Solange Sie die Fach-Klasse unterrichten, sollte diese "aktiv" sein. Das hei&szlig;t, dass die Klasse mit diesem Fach in den Zensuren-, Unterrichtsgrobplanungs- und Hausaufgaben-Tabs auftaucht.</div>
		<div class="tooltip" id="tt_farbe">
			Die hier gew&auml;hlte Farbe wird sowohl auf dem Stundenplan, als auch in den Zensuren- und Unterrichts-Tabs genutzt.</div>
		<div class="tooltip" id="tt_gruppenname">
			<p>Soll die Klasse in Gruppen geteilt werden, um z.B. 14-t&auml;gigen Unterricht zu t&auml;tigen, wie das oft im Informatikunterricht der Fall ist, k&ouml;nnen Sie hier einen Gruppennamen angeben. Diese Gruppe kann dann einzelnen Sch&uuml;lern zugeordnet werden.</p>
			<p>Sollten Sie also zwei Gruppen unterrichten, erstellen Sie auch zwei verschiedene Fach-Klasse-Kombinationen mit dem selben Fach und unterschiedlichem Gruppennamen.
			Auch wenn es sich um einen Neigungskurs oder F&ouml;rderunterricht handelt (bei denen nicht alle Sch&uuml;ler der Klasse dabei sind), geben Sie einen Gruppennamen an, damit dieser den einzelnen Sch&uuml;lern zugeordnet werden kann.</p></div>
		<div class="tooltip" id="tt_lehrplan">
			<p>Geben Sie den Lehrplan an, nach dem die Klasse unterrichtet wird.</p> <div class="hinweis">Dieser muss zun&auml;chst im in "Unterricht" - "Fundus/Lehrpl&auml;ne" - "Lehrplan hinzuf&uuml;gen" erstellt werden.</div></div>
		<div class="tooltip" id="tt_bewertungstabelle">
			Die hier festzulegende Standard-Bewertungstabelle ist die Vorauswahl bei jeglichen Zensuren-Spalten. Die Auswahl kann dort nat&uuml;rlich f&uuml;r die einzelnen Spalten ver&auml;ndert werden. Kontrollieren Sie zun&auml;chst, ob Ihre Bewertungstabelle korrekt eingestellt ist ("Einstellungen" - "Zensuren").</div>
	<!--
	<fieldset><legend><input type="checkbox" title="deaktivieren, falls Sie nur die Klasse anlegen wollen" checked="checked" name="fach_klasse_anlegen" value="1" /> Fach-Klasse-Kombination</legend>
    
	<table class="tabelle" cellspacing="0">
      <tr>
		<th>Fach <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_fach')" onmouseout="hideWMTT()" /></th>
        <th>Gruppenname /<br />Lehrplan ...</th>
      </tr>
      <tr>
        <td><select name="fach_neu">
               <?php $faecher_auswahl = db_conn_and_sql ('SELECT * FROM `faecher` WHERE `faecher`.`user`=0 OR `faecher`.`user`='.$_SESSION['user_id']);
                     for ($k=0;$k<sql_num_rows ($faecher_auswahl);$k++)
						echo '<option value="'.sql_result ( $faecher_auswahl, $k, 'faecher.id' ).'" title="'.html_umlaute(sql_result ( $faecher_auswahl, $k, 'faecher.name' )).'">'.html_umlaute(sql_result ( $faecher_auswahl, $k, 'faecher.kuerzel' )).'</option>'."\n"; ?>
					</select></td>
         <td><label for="anzeigen_neu">aktiv: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_anzeigen')" onmouseout="hideWMTT()" /></label> <input type="checkbox" name="anzeigen_neu" checked="checked" value="1" /><br />
			<label for="farbe">Farbe <img src="<?php echo $pfad; ?>icons/farben.png" title="Farbe" alt="farben" />: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_farbe')" onmouseout="hideWMTT()" /></label> <select name="farbe_neu"><?php
			foreach ($farbenarray as $value) { ?><option value="<?php echo $value[0]; ?>" style="background-color:#<?php echo $value[0]; ?>;"<?php if(@sql_result ( $result, $i, 'fach_klasse.farbe' )==$value[0]) echo ' selected="selected"'; ?>><?php echo $value[1]; ?></option><?php } ?></select><br />
			<label for="gruppen_name">Gruppenn. <img src="<?php echo $pfad; ?>icons/gruppe.png" title="Gruppenname" alt="gruppen_name" />: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_gruppenname')" onmouseout="hideWMTT()" /></label> <input type="text" name="gruppen_name_neu" size="5" maxlength="15" /><br />
			<label for="lehrplan">Lehrplan: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_lehrplan')" onmouseout="hideWMTT()" /></label> <select name="lehrplan_neu" title="Lehrplan eingeben"><?php if (sql_num_rows($lehrplan)>0) for ($j=0;$j<sql_num_rows($lehrplan);$j++) echo '<option value="'.sql_result($lehrplan,$j,"lehrplan.id").'">'.$bundesland[sql_result($lehrplan,$j,"lehrplan.bundesland")]['kuerzel'].' '.html_umlaute(sql_result($lehrplan,$j,"schulart.kuerzel")).' '.html_umlaute(sql_result($lehrplan,$j,"faecher.kuerzel")).' '.sql_result($lehrplan,$j,"lehrplan.jahr").' '.sql_result($lehrplan,$j,"lehrplan.zusatz").' ('.sql_result($lehrplan,$j,"lehrplan.von").'-'.sql_result($lehrplan,$j,"lehrplan.bis").')</option>'; ?></select><br />
			<label for="bewertungstabelle">Bewertungst.: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_bewertungstabelle')" onmouseout="hideWMTT()" /></label> <select name="bewertungstabelle" title="legen Sie hier die Standard-Bewertungstabelle der Fach-Klassen-Kombination fest"><?php for ($j=0;$j<sql_num_rows($bewertungstabelle);$j++) echo '<option value="'.sql_result($bewertungstabelle,$j,"bewertungstabelle.id").'">'.html_umlaute(sql_result($bewertungstabelle,$j,"bewertungstabelle.name")).'</option>'; ?></select></td>
      </tr>
    </table>
	</fieldset>
	-->
      
      <br />
      
      <input type="button" class="button" value="neue Klasse erstellen" onclick="auswertung=new Array(new Array(0, 'schule','nicht_leer')); pruefe_formular(auswertung);" /> <!-- , new Array(0, 'lehrplan_neu','nicht_leer'), new Array(0, 'bewertungstabelle','nicht_leer') -->
      </span>
    </fieldset>
    </form>
	<br />
	<?php } ?>
    
    <script>
        function highlight_schoolclass_cells (k) {
            document.getElementById("schoolclass_"+k).style.visibility="visible";
            document.getElementById("pupils_"+k).style.visibility="visible";
            document.getElementById("subj_"+k).style.visibility="visible";
        }
        function highlight_schoolclass_cells_out (k) {
            document.getElementById("schoolclass_"+k).style.visibility="hidden";
            document.getElementById("pupils_"+k).style.visibility="hidden";
            document.getElementById("subj_"+k).style.visibility="hidden";
        }
    </script>
    
    <table class="tabelle" cellspacing="0">
      <tr><th>Klasse</th><th>Sch&uuml;ler</th><th>aktiv <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_klassenanzeige')" onmouseout="hideWMTT()" /></th><th>Fach-Klasse-Kombinationen<br />bearbeiten</th></tr>
    <?php
		$klasse=0;
		// TODO $schulklassen = new school_classes($aktuelles_jahr);
      $schulklassen = db_conn_and_sql ( 'SELECT *
			FROM `schule`, `schule_user`, `klasse`
			WHERE `einschuljahr`>('.$aktuelles_jahr.')-12
				AND `klasse`.`schule`=`schule`.`id`
				AND `schule`.`id`=`schule_user`.`schule`
				AND `schule_user`.`user`='.$_SESSION['user_id'].'
			ORDER BY schule_user.aktiv DESC, klasse.schule DESC, klasse.einschuljahr DESC, klasse.endung' );
			
	if (sql_num_rows ($schulklassen)>0)
      for ($i=0; $i<sql_num_rows ($schulklassen);$i++) {
	  	  $fach_klasse = db_conn_and_sql('SELECT * FROM `fach_klasse`, `faecher` WHERE `fach_klasse`.`klasse`='.sql_result ( $schulklassen, $i, 'klasse.id' ).' AND `faecher`.`id`=`fach_klasse`.`fach` AND `fach_klasse`.`user`='.$_SESSION['user_id'].' ORDER BY `faecher`.`name`');
		  $klasse=sql_result ( $schulklassen, $i, 'klasse.id' ); ?>
        <tr onmouseover="highlight_schoolclass_cells(<?php echo $klasse; ?>);" onmouseout="highlight_schoolclass_cells_out(<?php echo $klasse; ?>);">
           <td><?php echo $school_classes->nach_ids[sql_result ( $schulklassen, $i, 'klasse.id' )]["name"]; ?>
            <span style="color: gray; float: right;"><?php echo html_umlaute(@sql_result ( $schulklassen, $i, 'schule.kuerzel' )); ?></span>
            <span id="schoolclass_<?php echo ($klasse); ?>" style="visibility: hidden;">
            <?php if (userrigths("klassendaten", $klasse)==2) { ?>
			<a href="<?php echo $pfad; ?>formular/klasse_bearbeiten.php?id=<?php echo sql_result ( $schulklassen, $i, 'klasse.id' ); ?>" onclick="fenster(this.href, 'Klasse bearbeiten'); return false;" title="Klassen-Endung, Einschuljahr oder Schulart &auml;ndern" class="icon"><img src="<?php echo $pfad; ?>icons/edit.png" alt="bearbeiten" /></a>
			<?php } ?>
			</span></td>
           <td><?php
		   $anzahl = db_conn_and_sql ('SELECT COUNT(`schueler`.`id`) AS `anzahl` FROM `schueler` WHERE `schueler`.`aktiv`=TRUE AND `klasse`='.$klasse);
           echo sql_result($anzahl,0,"anzahl");
           echo '<span id="pupils_'.($klasse).'" style="visibility: hidden;">';
           if (userrigths("schuelerdaten", $klasse)>0) { ?>
			<a href="index.php?tab=klassen&amp;auswahl=<?php echo sql_result ( $schulklassen, $i, 'klasse.id' ); ?>" class="icon" title="Sch&uuml;lerdaten ansehen"><img src="<?php echo $pfad; ?>icons/schueler.png" alt="edit" /></a>
           <?php } ?>
			</span>
           </td>
           <td><?php
           if (sql_num_rows($fach_klasse)>0)
              for ($fk=0; $fk<sql_num_rows($fach_klasse); $fk++) {
				if (sql_result ( $fach_klasse, $fk, 'fach_klasse.id' )) { ?>
					<span style="background-color: #<?php echo html_umlaute(@sql_result($fach_klasse,$fk,'fach_klasse.farbe')); ?>">
						<img src="<?php echo $pfad; ?>icons/<?php if (@sql_result($fach_klasse,$fk,'fach_klasse.anzeigen')==true) echo 'haekchen.png" alt="haekchen"'; else echo 'abhaken.png" alt="kein haekchen"'; ?> /> <?php echo html_umlaute(@sql_result($fach_klasse,$fk,'faecher.kuerzel')); ?>
						<?php if (@sql_result($fach_klasse,$fk,'fach_klasse.gruppen_name')!=NULL) echo ' (G)'; ?>
					</span>&nbsp;
               <?php } else { ?>keine Fach-Klasse-Kombination<?php } ?>
      <?php   }
			echo '</td><td><a id="subj_'.($klasse).'" href="index.php?tab=klassen&amp;option=fk&amp;auswahl='.sql_result ( $schulklassen, $i, 'klasse.id' ).'" class="icon" style="visibility: hidden;"><img src="'.$pfad.'icons/kurs.png" alt="fach" /> Fach-Klassen</a></td></tr>'; 
      } ?>
    </table></div>

    <?php } else if ($_GET["zweit"]=="schuelersuche") { ?>
		<div class="navigation_2"><?php echo $navigation; ?></div>
		<div class="inhalt">(Geht noch nicht)
	<form action="..." method="post" accept-charset="ISO-8859-1">
		Klassen: <select name="klasse">
			<option value="alle">Alle</option>
			<option value="bis_13">bis 13. Schuljahr</option>
			<option value="aktiv">aktive</option>
			<option value="ehemalig">ehemalige</option>
			<option value="x">Einzelklassen</option>
		</select><br />
		Folgende Felder durchsuchen: <input type="checkbox" name="name" value="1" /> Name <input type="checkbox" name="vorname" value="1" /> Vorname <input type="checkbox" name="adresse" value="1" /> Adresse <input type="checkbox" name="geburtstag" value="1" /> Geburtstag <input type="checkbox" name="telefon" value="1" /> Telefon <input type="checkbox" name="bemerkungen" value="1" /> Bemerkungen <input type="checkbox" name="email" value="1" /> eMail<br />
		Suchbegriff: <input type="text" name="suchbegriff" size="10" />
		<input type="submit" value="suchen" />
	</form>
	<hr />
	<form action="..." method="post" accept-charset="ISO-8859-1">
		Klassen: <select name="klasse">
			<option value="alle">Alle</option>
			<option value="bis_13">bis 13. Schuljahr</option>
			<option value="aktiv">aktive</option>
			<option value="ehemalig">ehemalige</option>
			<option value="x">Einzelklassen</option>
		</select><br />
		<table>
			<tr><td>Name</td><td>Vorname</td><td>Adresse</td><td>Geburtstag</td><td>Telefon</td><td>Bemerkungen</td><td>eMail</td><td>Schule</td></tr>
			<tr>
				<td><input type="text" name="name" size="7" /></td>
				<td><input type="text" name="vorname" size="7" /></td>
				<td><input type="text" name="adresse" size="7" /></td>
				<td><select name="monat"><option value="1">Jan</option></select></td>
				<td><input type="text" name="telefon" size="7" /></td>
				<td><input type="text" name="bemerkungen" size="7" /></td>
				<td><input type="text" name="email" size="7" /></td>
				<td><select name="schule"><option value="1">Schule 1</option></select></td></tr>
		</table>
		<input type="submit" value="suchen" />
	</form>
	</div>
	<?php }
	}
	
	
	
	if ($_GET["option"]=="fehlzeiten" and proofuser("klasse", $gewaehlte_klasse)) { ?>
		<div class="navigation_3"><?php echo $navigation; ?></div>
		<div class="inhalt">
	<?php
	$klasse_id=$gewaehlte_klasse;
	//$schueler=db_conn_and_sql("SELECT * FROM `schueler` WHERE `schueler`.`klasse`=".$klasse_id." AND `schueler`.`aktiv`=1 ORDER BY `schueler`.`position`");
	$schueler=schueler_von_fachklasse($subject_classes->cont[$subject_classes->active]["id"]);
	
	if (isset($_GET["fehlzeit_bis"]))
		db_conn_and_sql("UPDATE `klasse` SET `fehlzeiten_erledigt_bis`='".injaway($_GET["fehlzeit_bis"])."' WHERE `id`=".$klasse_id);
	$fehlenzeiten_bis=db_conn_and_sql("SELECT `fehlzeiten_erledigt_bis` FROM `klasse` WHERE `id`=".$klasse_id);
	
	// fuer javascriptuebepruefung und ueberhaupt brauch ich start und ende des Schuljahres
	$schule=db_conn_and_sql("SELECT schule FROM klasse WHERE klasse.id=".$klasse_id);
	$schule=sql_fetch_assoc($schule);
	$schule=$schule["schule"];
	$start_ende=schuljahr_start_ende($aktuelles_jahr,$schule);
	
	if (sql_num_rows($schueler)>0) {
		// Uebersicht
		if (isset($_GET["monat"])) $monat=$_GET["monat"];
		else $monat=date("n",$timestamp);
		if (isset($_GET["jahr"])) $jahr=$_GET["jahr"];
		else $jahr=date("Y",$timestamp);
		
		// damit bei gesetztem "erledigt bis" der richtige Monat benutzt wird:
		if ($_GET["monat"]<1
			and sql_result($fehlenzeiten_bis,0,"fehlzeiten_erledigt_bis")!=""
			and $start_ende["start"]>=sql_result($fehlenzeiten_bis,0,"fehlzeiten_erledigt_bis")
			and $start_ende["ende"]<=sql_result($fehlenzeiten_bis,0,"fehlzeiten_erledigt_bis")) {
				$monat=substr(sql_result($fehlenzeiten_bis,0,"fehlzeiten_erledigt_bis"),5,2);
				$jahr=substr(sql_result($fehlenzeiten_bis,0,"fehlzeiten_erledigt_bis"),0,4);
			}
		?>
	<span class="nicht_drucken">
		<ul class="r">
			<li><a href="<?php echo $pfad; ?>formular/fehlzeiten.php?klasse=<?php echo $klasse_id; if ($monat>0) echo '&amp;monat='.$monat.'&amp;jahr='.$jahr; ?>&amp;eintragen=uebersicht&amp;ansicht=druck" onclick="fenster(this.href, 'Druckansicht Fehlzeiten'); return false;" class="icon"><img src="<?php echo $pfad; ?>icons/drucken.png" alt="drucker" /> Druckansicht</a></li>
			<li><a href="<?php echo $pfad; ?>formular/fehlzeiten.php?klasse=<?php echo $klasse_id; ?>" onclick="fenster(this.href, 'Fehlzeit eintragen'); return false;" class="icon"><img src="<?php echo $pfad; ?>icons/neu.png" alt="neu" /> Fehlzeit eintragen</a></li>
		</ul>
	</span>
	<h1 style="font-size:12pt; margin-bottom:3px;">Vers&auml;umnisse</h1>
	<table cellspacing="0" class="fehlzeiten">
		<tr>
			<th colspan="32" rowspan="2" style="font-weight: bold; font-size: 12pt;">Monat
				<?php
				$lfd_monat=date("m",mktime(0,0,0,substr($start_ende["start"],5,2),substr($start_ende["start"],8,2),substr($start_ende["start"],0,4)));
				$ende_monat=12+date("m",mktime(0,0,0,substr($start_ende["ende"],5,2),substr($start_ende["ende"],8,2),substr($start_ende["ende"],0,4)));
				?>
				<select onchange="window.location.href = '<?php echo $pfad; ?>index.php?tab=klassen&amp;auswahl=<?php echo $gewaehlte_klasse; ?>&amp;option=fehlzeiten&amp;monat='+this.value+'&amp;eintragen=uebersicht';">
				<?php
				$auswahl_ist_vorgekommen=false;
				while ($lfd_monat<=$ende_monat) {
					if($lfd_monat<=12)
						$echter_monat=mktime(0,0,0,$lfd_monat,1,$aktuelles_jahr);
					else
						$echter_monat=mktime(0,0,0,$lfd_monat-12,1,$aktuelles_jahr+1);
					echo '<option value="'.date("n",$echter_monat).'&amp;jahr='.date("Y",$echter_monat).'"';
					
					if (date("n",$echter_monat)==$monat and date("Y",$echter_monat)==$jahr) {
						echo ' selected="selected"';
						$auswahl_ist_vorgekommen=true;
					}
					if ($lfd_monat+1>$ende_monat and !$auswahl_ist_vorgekommen) {
						$monat=date("n",$echter_monat);
						$jahr=date("Y",$echter_monat);
						echo ' selected="selected"';
					}
					echo '>'.$monatsnamen_lang[date("n",$echter_monat)].' '.date("Y",$echter_monat).'</option>';
					$lfd_monat++;
				} ?>
				</select>
			</th>
			<th colspan="4" style="font-weight: bold; height: 0.3cm">Monatssumme</th><th colspan="4" style="font-weight: bold;">Gesamtsumme</th></tr>
		<tr><th colspan="2" style="height: 0.3cm">E/K</th><th colspan="2">U</th><th colspan="2">E/K</th><th colspan="2">U</th></tr>
		<?php
			echo '<tr><td class="nummer">Fertig<br />bis:</td>';
			for ($i=1;$i<32;$i++) {
				$datum=datum_punkt_zu_strich($i.'.'.$monat.'.'.$jahr);
				echo '<td class="nummer"><a href="'.$pfad.'index.php?tab=klassen&amp;auswahl='.$gewaehlte_klasse.'&amp;option=fehlzeiten&amp;eintragen=uebersicht&amp;fehlzeit_bis='.$datum.'" title="&Uuml;berpr&uuml;fung der Fehlzeiten bis zum '.$i.'.'.$monat.'.'.$jahr.' abgeschlossen">';
				if (sql_result($fehlenzeiten_bis,0,"fehlzeiten_erledigt_bis")<$datum) echo 'O'; else echo 'x';
				echo '</a></td>';
			}
			echo '<td colspan="8"></td></tr>'; ?>
		<tr><td class="nummer" style="width: 1cm;">Nr.</td>
			<?php
			for ($i=0; $i<sql_num_rows($schueler); $i++) {
				$fehlen[$i]=db_conn_and_sql("SELECT * FROM `schueler_fehlt`
					WHERE `schueler_fehlt`.`schueler`=".sql_result($schueler,$i,"schueler.id")."
						AND ((`schueler_fehlt`.`startdatum`>'".$start_ende["start"]."' AND `schueler_fehlt`.`startdatum`<'".$jahr."-".$monat."-32')
							OR (`schueler_fehlt`.`enddatum`>'".$start_ende["start"]."' AND `schueler_fehlt`.`enddatum`<'".$jahr."-".$monat."-32'))
					ORDER BY `schueler_fehlt`.`startdatum`");
			}
			$schuljahr_id=db_conn_and_sql("SELECT `schuljahr`.`jahr`,`schuljahr`.`schule`
				FROM `klasse`,`schuljahr`
				WHERE `klasse`.`id`=".$gewaehlte_klasse."
					AND `klasse`.`schule`=`schuljahr`.`schule`
					AND `schuljahr`.`jahr`=".$aktuelles_jahr."
				ORDER BY `schuljahr`.`schule` DESC");
			$wochentage=schuljahr_uebersicht($aktuelles_jahr,$schule);
			$tag=1; $woche=0; $der_ausgewaehlte_monat_hat_tage=date("t",mktime(0,0,0,$monat,1,$jahr));
			if (date("n",$wochentage[$tag][$woche]["datum"])==$monat) {
				$hilf=1;
				while (mktime(5,0,0,$monat,$hilf,$jahr)<$wochentage[$tag][$woche]["datum"]) {
					echo '<td rowspan="'.(sql_num_rows($schueler)+1).'" title="Sommerferien" class="nummer"></td>';
					$frei_im_monat[$hilf]=true;
					$hilf++;
				}
			}
			
			while ($wochentage[$tag][$woche]["datum"]<=mktime(5,0,0,$monat,$der_ausgewaehlte_monat_hat_tage,$jahr) and $woche<53) {
				if (date("m",mktime(0,0,0,$monat,$der_ausgewaehlte_monat_hat_tage,$jahr))==date("m",$wochentage[$tag][$woche]["datum"])) {
					if ($wochentage[$tag][$woche]["unterricht"]!=1 and $wochentage[$tag][$woche]["fehltage_mitzaehlen"]!=1) {
						$frei_im_monat[date("j",$wochentage[$tag][$woche]["datum"])]=true;
						echo '<td rowspan="'.(sql_num_rows($schueler)+1).'" title="'.$wochentage[$tag][$woche]["unterricht"].'" class="nummer">&nbsp;</td>';
					} else {
						echo '<td class="nummer">'.date("j",$wochentage[$tag][$woche]["datum"]).'</td>';
						$frei_im_monat[date("j",$wochentage[$tag][$woche]["datum"])]=false;
					}
					
					if ($tag==5) { //Wochenende
						if (date("n",$wochentage[$tag][$woche]["datum"]+60*60*24)==$monat) {
							$frei_im_monat[date("j",$wochentage[$tag][$woche]["datum"]+60*60*24)]=true;
							echo '<td rowspan="'.(sql_num_rows($schueler)+1).'" title="Samstag" class="nummer">&nbsp;</td>';
						}
						if (date("n",$wochentage[$tag][$woche]["datum"]+60*60*24*2)==$monat) {
							$frei_im_monat[date("j",$wochentage[$tag][$woche]["datum"]+60*60*24*2)]=true;
							echo '<td rowspan="'.(sql_num_rows($schueler)+1).'" title="Sonntag" class="nummer">&nbsp;</td>';
						}
					}
					
					for ($i=0; $i<sql_num_rows($schueler); $i++)
						if (@sql_num_rows($fehlen[$i])>0)
							for ($a=0;$a<sql_num_rows($fehlen[$i]);$a++)
								if (sql_result($fehlen[$i],$a,"schueler_fehlt.startdatum")<=date("Y-m-d",$wochentage[$tag][$woche]["datum"])
								and sql_result($fehlen[$i],$a,"schueler_fehlt.enddatum")>=date("Y-m-d",$wochentage[$tag][$woche]["datum"]))
									$schueler_fehlt[$i][date("j",$wochentage[$tag][$woche]["datum"])]=$a+1; // um 0 auszuschliessen (wird danach wieder runtergerechnet)
				}
				
				for ($i=0; $i<sql_num_rows($schueler); $i++)
					if (@sql_num_rows($fehlen[$i])>0)
						for ($a=0;$a<sql_num_rows($fehlen[$i]);$a++)
							if (sql_result($fehlen[$i],$a,"schueler_fehlt.startdatum")<=date("Y-m-d",$wochentage[$tag][$woche]["datum"])
							and sql_result($fehlen[$i],$a,"schueler_fehlt.enddatum")>=date("Y-m-d",$wochentage[$tag][$woche]["datum"])
							and ($wochentage[$tag][$woche]["unterricht"]==1 or $wochentage[$tag][$woche]["fehltage_mitzaehlen"]==1))
								if (sql_result($fehlen[$i],$a,"schueler_fehlt.nur_stunden")>0) {
									if (sql_result($fehlen[$i],$a,"schueler_fehlt.entschuldigt")>0) {
										$schueler_fehlt[$i]["gesamt"]["e_k"]["stunden"]+=sql_result($fehlen[$i],$a,"schueler_fehlt.nur_stunden");
										if (date("m",mktime(0,0,0,$monat,$der_ausgewaehlte_monat_hat_tage,$jahr))==date("m",$wochentage[$tag][$woche]["datum"])) $schueler_fehlt[$i]["monat"]["e_k"]["stunden"]+=sql_result($fehlen[$i],$a,"schueler_fehlt.nur_stunden");
									}
									else {
										$schueler_fehlt[$i]["gesamt"]["u"]["stunden"]+=sql_result($fehlen[$i],$a,"schueler_fehlt.nur_stunden");
										if (date("m",mktime(0,0,0,$monat,$der_ausgewaehlte_monat_hat_tage,$jahr))==date("m",$wochentage[$tag][$woche]["datum"])) $schueler_fehlt[$i]["monat"]["u"]["stunden"]+=sql_result($fehlen[$i],$a,"schueler_fehlt.nur_stunden");
									}
								}
								else {
									if (sql_result($fehlen[$i],$a,"schueler_fehlt.entschuldigt")>0) {
										$schueler_fehlt[$i]["gesamt"]["e_k"]["tage"]++;
										if (date("m",mktime(0,0,0,$monat,$der_ausgewaehlte_monat_hat_tage,$jahr))==date("m",$wochentage[$tag][$woche]["datum"])) $schueler_fehlt[$i]["monat"]["e_k"]["tage"]++;
									}
									else {
										$schueler_fehlt[$i]["gesamt"]["u"]["tage"]++;
										if (date("m",mktime(0,0,0,$monat,$der_ausgewaehlte_monat_hat_tage,$jahr))==date("m",$wochentage[$tag][$woche]["datum"])) $schueler_fehlt[$i]["monat"]["u"]["tage"]++;
									}
								}
				$tag++; if ($tag>5) {
					 //Wochenende bei Monatsumbruch
					if (date("n",$wochentage[$tag-1][$woche]["datum"]+60*60*24)==$monat and date("j",$wochentage[$tag-1][$woche]["datum"]+60*60*24)<2) {
						$frei_im_monat[date("j",$wochentage[$tag-1][$woche]["datum"]+60*60*24)]=true;
						echo '<td rowspan="'.(sql_num_rows($schueler)+1).'" title="Samstag" class="nummer">&nbsp;</td>';
					}
					if (date("n",$wochentage[$tag-1][$woche]["datum"]+60*60*24*2)==$monat and date("j",$wochentage[$tag-1][$woche]["datum"]+60*60*24*2)<3) {
						$frei_im_monat[date("j",$wochentage[$tag-1][$woche]["datum"]+60*60*24*2)]=true;
						echo '<td rowspan="'.(sql_num_rows($schueler)+1).'" title="Sonntag" class="nummer">&nbsp;</td>';
					}
					$tag=1; $woche++;
				}
			}
			
			$hilf=$der_ausgewaehlte_monat_hat_tage+1;
			if (substr($start_ende["ende"],5,2)+0==$monat)
				$hilf=substr($start_ende["ende"],8,2)+0;
			
			while($hilf<32) {
				echo '<td rowspan="'.(sql_num_rows($schueler)+1).'" title="gibts in diesem Monat nicht" class="nummer">&nbsp;</td>';
				$frei_im_monat[$hilf]=true;
				$hilf++;
			} ?>
			<td class="nummer" style="width: 0.5cm">Tg.</td><td class="nummer" style="width: 0.5cm">Std.</td><td class="nummer" style="width: 0.5cm">Tg.</td><td class="nummer" style="width: 0.5cm">Std.</td><td class="nummer" style="width: 0.5cm">Tg.</td><td class="nummer" style="width: 0.5cm">Std.</td><td class="nummer" style="width: 0.5cm">Tg.</td><td class="nummer" style="width: 0.5cm">Std.</td></tr>
		<?php		
		function zahl_zu_buchstabe($zahl) {
			switch ($zahl) {
				case 0: return "U"; break;
				case 1: return "E"; break;
				case 2: return "K"; break;
				default: return "Fehler"; break;
			}
		}
		
		for ($i=0; $i<sql_num_rows($schueler); $i++) { ?>
			<tr><td title="<?php echo html_umlaute(sql_result($schueler,$i,"schueler.name")).", ".html_umlaute(sql_result($schueler, $i, "schueler.vorname")); ?>" style="<?php if(floor($i/2)!=$i/2) echo 'background: lightgray;'; if (floor(($i+1)/5)==($i+1)/5) echo ' border-bottom-width: 0.6mm;'; ?>"><?php echo sql_result($schueler,$i,"schueler.position"); ?></td>
				<?php
				for($k=1;$k<32;$k++) if (!$frei_im_monat[$k]) {
					echo '<td style="font-size:6pt;';
					if(floor($i/2)!=$i/2) echo ' background: lightgray;'; if (floor(($i+1)/5)==($i+1)/5) echo ' border-bottom-width: 0.6mm;';
					echo '">';
					if ($schueler_fehlt[$i][$k]>0) {
						if($_GET["ansicht"]!="druck") echo '<a href="'.$pfad.'formular/fehlzeiten.php?klasse='.$gewaehlte_klasse.'&amp;schueler='.sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.schueler").'&amp;startdatum='.sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.startdatum").'" onclick="javascript:fenster(this.href, \'Fehlzeiten\'); return false;" title="'.html_umlaute(sql_result($schueler,$i,"schueler.vorname")).': '.datum_strich_zu_punkt(sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.startdatum")).' - '.datum_strich_zu_punkt(sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.enddatum")).' '.html_umlaute(@sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.bemerkung")).'">';
						echo sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.nur_stunden").zahl_zu_buchstabe(sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.entschuldigt"));
						if($_GET["ansicht"]!="druck") echo '</a>';
					}
					else echo "";
					echo '</td>';
				} ?>
				<td style="<?php if(floor($i/2)!=$i/2) echo 'background: lightgray;'; if (floor(($i+1)/5)==($i+1)/5) echo ' border-bottom-width: 0.6mm;'; ?>"><?php if ($schueler_fehlt[$i]["monat"]["e_k"]["tage"]!=0) echo $schueler_fehlt[$i]["monat"]["e_k"]["tage"]; ?></td>
				<td style="<?php if(floor($i/2)!=$i/2) echo 'background: lightgray;'; if (floor(($i+1)/5)==($i+1)/5) echo ' border-bottom-width: 0.6mm;'; ?>"><?php if ($schueler_fehlt[$i]["monat"]["e_k"]["stunden"]!=0) echo $schueler_fehlt[$i]["monat"]["e_k"]["stunden"]; ?></td>
				<td style="<?php if(floor($i/2)!=$i/2) echo 'background: lightgray;'; if (floor(($i+1)/5)==($i+1)/5) echo ' border-bottom-width: 0.6mm;'; ?>"><?php if ($schueler_fehlt[$i]["monat"]["u"]["tage"]!=0) echo $schueler_fehlt[$i]["monat"]["u"]["tage"]; ?></td>
				<td style="<?php if(floor($i/2)!=$i/2) echo 'background: lightgray;'; if (floor(($i+1)/5)==($i+1)/5) echo ' border-bottom-width: 0.6mm;'; ?>"><?php if ($schueler_fehlt[$i]["monat"]["u"]["stunden"]!=0) echo $schueler_fehlt[$i]["monat"]["u"]["stunden"]; ?></td>
				<td style="<?php if(floor($i/2)!=$i/2) echo 'background: lightgray;'; if (floor(($i+1)/5)==($i+1)/5) echo ' border-bottom-width: 0.6mm;'; ?>"><?php if ($schueler_fehlt[$i]["gesamt"]["e_k"]["tage"]!=0) echo $schueler_fehlt[$i]["gesamt"]["e_k"]["tage"]; ?></td>
				<td style="<?php if(floor($i/2)!=$i/2) echo 'background: lightgray;'; if (floor(($i+1)/5)==($i+1)/5) echo ' border-bottom-width: 0.6mm;'; ?>"><?php if ($schueler_fehlt[$i]["gesamt"]["e_k"]["stunden"]!=0) echo $schueler_fehlt[$i]["gesamt"]["e_k"]["stunden"]; ?></td>
				<td style="<?php if(floor($i/2)!=$i/2) echo 'background: lightgray;'; if (floor(($i+1)/5)==($i+1)/5) echo ' border-bottom-width: 0.6mm;'; ?>"><?php if ($schueler_fehlt[$i]["gesamt"]["u"]["tage"]!=0) echo $schueler_fehlt[$i]["gesamt"]["u"]["tage"]; ?></td>
				<td style="<?php if(floor($i/2)!=$i/2) echo 'background: lightgray;'; if (floor(($i+1)/5)==($i+1)/5) echo ' border-bottom-width: 0.6mm;'; ?>"><?php if ($schueler_fehlt[$i]["gesamt"]["u"]["stunden"]!=0) echo $schueler_fehlt[$i]["gesamt"]["u"]["stunden"]; ?></td>
			</tr>
		<?php } ?>
	</table>
	<?php
	}
	
	?>	
		</div>
    <?php
		}
		
		if ($_GET["option"]=="elternabend" and proofuser("klasse", $gewaehlte_klasse)) {
			echo '<div class="navigation_3">'.$navigation.'</div>
				<div class="inhalt">';
			$formularziel="index.php?tab=klassen&amp;auswahl=".$gewaehlte_klasse."&amp;option=elternabend";
			include $pfad."formular/konferenz.php";
			echo '</div>';
		}
    }




    if ($_GET['tab']=='stundenplan') { ?>
    <div class="tab_2">
		<a href="index.php?tab=stundenplan&amp;auswahl=stundenplan"<?php if ($_GET["auswahl"]=="stundenplan") echo ' class="selected"'; ?>><img src="<?php echo $pfad; ?>icons/stundenplan.png" alt="stundenplan" title="Stundenplan" /> Stundenplan</a>
	    <a href="index.php?tab=stundenplan&amp;auswahl=kalender"<?php if ($_GET["auswahl"]=="kalender") echo ' class="selected"'; ?>><img src="<?php echo $pfad; ?>icons/kalender.png" alt="kalender" title="Kalender" /> Kalender</a>
		<a href="index.php?tab=stundenplan&amp;auswahl=konferenz"<?php if ($_GET["auswahl"]=="konferenz") echo ' class="selected"'; ?>><img src="<?php echo $pfad; ?>icons/konferenz.png" alt="konferenz" /> Konferenz</a>
		<a href="index.php?tab=stundenplan&amp;auswahl=kollegen"<?php if ($_GET["auswahl"]=="kollegen") echo ' class="selected"'; ?>><img src="<?php echo $pfad; ?>icons/kollegen.png" alt="kollegen" /> Kollegen</a>
	</div>
	<div class="navigation_2">
		<?php if ($_GET["auswahl"]=="stundenplan") { ?>
		<a href="javascript:window.print()" class="icon" title="diese Seite drucken"><img src="<?php echo $pfad; ?>icons/drucken.png" alt="drucken" /></a> |
		<?php
            /* <input type="checkbox" <?php if ($_GET["bearbeiten"]=="true") echo ' checked="checked"'; ?> onchange="k=11; while (document.getElementsByTagName('a')[k]) { if (document.getElementsByTagName('a')[k].className=='icon') document.getElementsByTagName('a')[k].style.display=this.checked==true?'inline':'none'; k++; }" /><img src="<?php echo $pfad; ?>icons/edit.png" alt="bearbeiten" title="bearbeiten" /> |*/
        }
			if ($_GET["auswahl"]=="kalender")
                echo '
                    <a href="'.$pfad.'index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr='.$aktuelles_jahr.'">Ferien eintragen</a> | '; ?>
		<?php echo $navigation; ?> <a href="<?php echo $pfad; ?>formular/hilfe.php?inhalt=stundenplan" onclick ="fenster(this.href, 100); return false;" class="icon"><img src="<?php echo $pfad; ?>icons/hilfe.png" alt="hilfe" /></a></div>
	<div class="inhalt">
    <?php
	if ($_GET["auswahl"]=="stundenplan") {
		
		// ------------------ Stundenplan -------------------------------
    $result = db_conn_and_sql ( 'SELECT *
					FROM `stundenplan`, `raum`, `schule`, `schule_user`, `fach_klasse`, `faecher`, `klasse`, `stundenzeiten`
					  WHERE `stundenplan`.`fach_klasse`=`fach_klasse`.`id`
                        AND `stundenplan`.`raum`=`raum`.`id`
                        AND `raum`.`schule`=`schule`.`id`
                        AND `schule_user`.`schule`=`schule`.`id`
                        AND `schule_user`.`user`='.$_SESSION['user_id'].'
                        AND `fach_klasse`.`fach`=`faecher`.`id`
                        AND `fach_klasse`.`klasse`=`klasse`.`id`
                        AND `fach_klasse`.`user`='.$_SESSION['user_id'].'
                        AND `stundenplan`.`stundenzeit`=`stundenzeiten`.`id`
                        AND `stundenplan`.`schuljahr` = '.$aktuelles_jahr.'
					ORDER BY `stundenplan`.`wochentag`,`stundenzeiten`.`beginn`,`stundenplan`.`gerade_woche`');
	
	// Wenn keine Eintragung existiert, soll der Stundenplan mit Stundenzeiten der ausgewaehlten Schule bestueckt werden
    if (@sql_result ( $result, 0, 'stundenplan.id' )<1) {
		$result = db_conn_and_sql ( 'SELECT *
					FROM `stundenzeiten`, `schule_user`, `schule` LEFT JOIN `raum` ON `raum`.`schule`=`schule`.`id`
					WHERE `stundenzeiten`.`schule`=`schule`.`id`
						AND `schule_user`.`schule`=`schule`.`id`
						AND `schule_user`.`user`='.$_SESSION['user_id'].'
						AND `schule_user`.`aktiv`=1
					ORDER BY `stundenzeiten`.`beginn`');
		$null_stunden=true;
	}
	
	
    $stundenplan=''; $beschreibung='';
    for ($i=0;$i<sql_num_rows ( $result );$i++) {
		$stundenplan[$i]['id']=@sql_result ( $result, $i, 'stundenplan.id' );
		$hilf=explode(":",sql_result ( $result, $i, 'stundenzeiten.beginn' ));
		$stundenplan[$i]['zeit']=mktime($hilf[0],$hilf[1],$hilf[2],1,@sql_result ( $result, $i, 'stundenplan.wochentag' ),2007); //2007 beginnt mit montag
		$stundenplan[$i]['fach']=html_umlaute(@sql_result ( $result, $i, 'faecher.kuerzel' ));
		$stundenplan[$i]['stundenzeit_nr']=@sql_result ( $result, $i, 'stundenplan.stundenzeit' );
		$stundenplan[$i]['klasse']=@sql_result ( $result, $i, 'klasse.id' );
		$stundenplan[$i]['fk_id']=@sql_result ( $result, $i, 'fach_klasse.id' );
		$stundenplan[$i]['fk_info']=html_umlaute(@sql_result ( $result, $i, 'fach_klasse.info' ));
		//$stundenplan[$i]['gruppen_name']=html_umlaute(@sql_result ( $result, $i, 'fach_klasse.gruppen_name' ));
		$stundenplan[$i]['raum']=html_umlaute(@sql_result ( $result, $i, 'raum.name' ));
		$stundenplan[$i]['raumkommentar']=html_umlaute(@sql_result ( $result, $i, 'raum.kommentar' ));
		$stundenplan[$i]['schule']=@sql_result ( $result, $i, 'stundenzeiten.schule' );
		$stundenplan[$i]['schule_id']=@sql_result ( $result, $i, 'schule.id' );
		$schule[$stundenplan[$i]['schule_id']]['schule_id']=@sql_result ( $result, $i, 'schule.id' );
		$schule[$stundenplan[$i]['schule_id']]['anzahl']++; if (@sql_result ( $result, $i, 'stundenplan.gerade_woche' )<2) $schule[$stundenplan[$i]['schule_id']]['anzahl']-=0.5;
		$schule[$stundenplan[$i]['schule_id']]['schule_kuerzel']=html_umlaute(@sql_result ( $result, $i, 'schule.kuerzel' ));
		//$stundenplan[$i]['beschreibung']=@sql_result ( $result, $i, 'stundenzeiten.beschreibung' );
		/*$beschreibung[@sql_result ( $result, $i, 'stundenzeiten.beschreibung' )]['id']=@sql_result ( $result, $i, 'stundenzeiten.beschreibung' );
		$beschreibung[@sql_result ( $result, $i, 'stundenzeiten.beschreibung' )]['anzahl']++; if (@sql_result ( $result, $i, 'stundenplan.gerade_woche' )<2) $beschreibung[@sql_result ( $result, $i, 'stundenzeiten.beschreibung' )]['anzahl']-=0.5;
		$beschreibung[@sql_result ( $result, $i, 'stundenzeiten.beschreibung' )]['schule_id']=@sql_result ( $result, $i, 'schule.id' );
		$beschreibung[@sql_result ( $result, $i, 'stundenzeiten.beschreibung' )]['schule_kuerzel']=@sql_result ( $result, $i, 'schule.kuerzel' );
		$beschreibung[@sql_result ( $result, $i, 'stundenzeiten.beschreibung' )]['schulart_kuerzel']=@sql_result ( $result, $i, 'schulart.kuerzel' );*/
		//$stundenplan[$i]['klassenstufe']=$aktuelles_jahr-@sql_result ( $result, $i, 'klasse.einschuljahr' )+1;
		//$stundenplan[$i]['endung']=@sql_result ( $result, $i, 'klasse.endung' );
		$stundenplan[$i]['gerade_woche']=@sql_result ( $result, $i, 'stundenplan.gerade_woche' );
		if (@sql_result ( $result, $i, 'fach_klasse.farbe' )=="") $stundenplan[$i]['stylefarbe']=""; else $stundenplan[$i]['stylefarbe']=' style="background-color: #'.html_umlaute(@sql_result ( $result, $i, 'fach_klasse.farbe' )).'"';
		if (@sql_result ( $result, $i, 'fach_klasse.farbe' )=="") $stundenplan[$i]['farbe']=""; else $stundenplan[$i]['farbe']='#'.html_umlaute(@sql_result ( $result, $i, 'fach_klasse.farbe' ));
    }
    
    //sortieren nach Zeit
    for ($i=0;$i<(count($stundenplan)-1);$i++)
      for ($j=$i+1;$j<count($stundenplan);$j++)
        if ($stundenplan[$i]['zeit']<$stundenplan[$i]['zeit']) {
          $hilf=$stundenplan[$i];
          $stundenplan[$i]=$stundenplan[$j];
          $stundenplan[$j]=$hilf;
        }
    ?>
	<div style="text-align: center">
    <h1>Stundenplan - Schuljahr <?php echo substr($aktuelles_jahr,2,2).'/'.substr($aktuelles_jahr+1,2,2); ?></h1>
    </div>
    
    <!--Drag&Drop Stundenplan-->
	<div class="tooltip" id="tt_stundenplan_eintrag">
		<p>Sie k&ouml;nnen eine Unterrichtsstunde in Ihren Stundenplan eintragen, indem Sie die Fach-Klasse-Kombination, die Woche und den Raum angeben und schlie&szlig;lig das farbige K&auml;stchen per Drag&amp;Drop in Ihren Stundenplan ziehen.</p>
        <p>Sollten Sie eine Eintragung entfernen m&uuml;ssen, fahren Sie mit der Maus &uuml;ber den entsprechenden Eintrag. Daraufhin erscheint das anzuklickende <img src="<?php echo $pfad; ?>icons/delete.png" alt="delete" />-Symbol.<br />
        <div class="hinweis">Abh&auml;ngige Unterrichtsstunden bleiben in der Grobplanung bestehen. Ein versehentlich gel&ouml;schter Eintrag kann ohne Probleme ein zweites Mal erstellt werden.</div></p>
	</div>
    
    <div class="nicht_drucken" style="float:left;">
		<form>
		<input type="hidden" name="schuljahr" value="<?php echo $aktuelles_jahr; ?>" />
		<fieldset><legend>Unterrichtsstunde hinzuf&uuml;gen <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_stundenplan_eintrag')" onmouseout="hideWMTT()" /></legend>
		<!--<input type="hidden" name="schule" value="<?php echo sql_result($aktive_schulen,$k,"schule.id"); ?>" />-->
        <?php
        $woche=2;
        $schule_optgroup=0;
        $zwischenresult=db_conn_and_sql('SELECT *
            FROM `fach_klasse`,`klasse`,`faecher`,`schule`, `schule_user`
            WHERE `klasse`.`schule`=`schule`.`id`
				AND `schule_user`.`schule`=`schule`.`id`
				AND `schule_user`.`user`='.$_SESSION['user_id'].'
                AND `fach_klasse`.`fach`=`faecher`.`id`
                AND `fach_klasse`.`klasse`=`klasse`.`id`
                AND `fach_klasse`.`user`='.$_SESSION['user_id'].'
            ORDER BY `schule`.`kuerzel`, `klasse`.`einschuljahr` DESC, `klasse`.`endung`'); // AND schule.aktiv=1 AND fach_klasse.anzeigen=1
        $raumresult=db_conn_and_sql('SELECT *
            FROM `raum`,`schule`, `schule_user`
            WHERE `raum`.`schule`=`schule`.`id`
				AND `schule_user`.`schule`=`schule`.`id`
				AND `schule_user`.`user`='.$_SESSION['user_id'].'
            ORDER BY `raum`.`schule` DESC, `raum`.`name`');
        ?>
        <label for="fach_klasse">Fach-Klasse:</label>
        <script>
            fk_school = new Array();
            fk_color = new Array();
            fk_name = new Array();
            <?php for ($i=0;$i<sql_num_rows($zwischenresult);$i++) { ?>
                fk_school[<?php echo sql_result ($zwischenresult, $i, 'fach_klasse.id'); ?>]=<?php echo sql_result ( $zwischenresult, $i, 'schule.id' ); ?>;
                fk_color[<?php echo sql_result ($zwischenresult, $i, 'fach_klasse.id'); ?>]='#<?php echo sql_result ( $zwischenresult, $i, 'fach_klasse.farbe' ); ?>';
                fk_name[<?php echo sql_result ($zwischenresult, $i, 'fach_klasse.id'); ?>]='<?php echo $subject_classes->nach_ids[sql_result ($zwischenresult, $i, 'fach_klasse.id')]["name"]; ?>';
            <?php } ?>
            
            function fill_fk_dragger(fk, room) {
                if (room!=-1)
                    document.getElementById('dragger_room').innerHTML=room;
                if (fk!=-1) {
                    document.getElementById('dragger_fk').innerHTML=fk_name[fk];
                    document.getElementById('drag_fk').style.backgroundColor=fk_color[fk];
                }
            }
            
            function school_changer(fk) {
                // select leeren
                while (document.getElementById('raum_vorhanden').length>0)
                    document.getElementById('raum_vorhanden').options[document.getElementById('raum_vorhanden').length - 1] = null;
                <?php
                $schul_id_fuer_wechsel=0;
                for ($i=0;$i<sql_num_rows($raumresult);$i++) {
                    if ($schul_id_fuer_wechsel!=sql_result ( $raumresult, $i, 'schule.id' )) {
                        if ($schul_id_fuer_wechsel!=0)
                            echo '}';
                        $schul_id_fuer_wechsel=sql_result ( $raumresult, $i, 'schule.id' );
                        echo ' if (fk_school[fk]=='.sql_result ( $raumresult, $i, 'schule.id' ).') { ';
                    } ?>
                    raumeintrag = new Option('<?php echo sql_result ( $raumresult, $i, 'raum.name' ); ?>', <?php echo sql_result ( $raumresult, $i, 'raum.id' ); ?>, false, <?php if ($_GET["room"]==sql_result ($raumresult, $i, 'raum.id')) {echo 'true'; $selected_room_index_for_dragger=$i; } else echo 'false'; ?>);
                    document.getElementById('raum_vorhanden').options[document.getElementById('raum_vorhanden').length] = raumeintrag;<?php
                } ?>
                }
            }
            
            $(function() {
                school_changer(document.getElementById('fk_selector').value);
                fill_fk_dragger(document.getElementById('fk_selector').value, <?php if (isset($selected_room_index_for_dragger)) echo "'".sql_result ( $raumresult, $selected_room_index_for_dragger, 'raum.name' )."'"; else echo "document.getElementById('raum_vorhanden').options[0].text"; ?>);
                
                $( "#drag_fk" ).draggable({ revert: "invalid" });
                
                $( ".drop_fk" ).droppable({
                    hoverClass: "ui-state-highlight",
                    drop: function( event, ui ) {
                        for(i=0;i<=2;i++)
                            if (document.getElementsByName('woche')[i].checked)
                                week=document.getElementsByName('woche')[i].value;
                        if (document.getElementById('raum_neu_checkbox').checked)
                            neuer_raum=1;
                        else
                            neuer_raum=0;
                        //alert($( this ).find("input[name='zeit_schule_"+fk_school[document.getElementById('fk_selector').value]+"']").val());
                        //alert($( this ).find("input[name='wochentag']").val());
                        document.location.href='<?php echo $pfad; ?>formular/stundenplan_neu.php?eintragen=dragndrop&raum_neu_checkbox='+neuer_raum+'&raum_neu='+document.getElementById('raum_neu').value+'&raum='+document.getElementById('raum_vorhanden').value+'&schuljahr=<?php echo $aktuelles_jahr; ?>&fach_klasse='+document.getElementById('fk_selector').value+'&woche='+week+'&wochentag='+$( this ).find("input[name='wochentag']").val()+'&zeit='+$( this ).find("input[name='zeit_schule_"+fk_school[document.getElementById('fk_selector').value]+"']").val();
                    }
                });
            });            
        </script>
        <select name="fach_klasse" id="fk_selector" onchange="school_changer(this.value); fill_fk_dragger(this.value, -1);"><?php
          for ($i=0;$i<sql_num_rows($zwischenresult);$i++)
			if ($fach_klasse==sql_result ($zwischenresult, $i, 'fach_klasse.id')
                    or (sql_result ($zwischenresult, $i, 'fach_klasse.anzeigen')
                    and sql_result ($zwischenresult, $i, 'schule_user.aktiv'))) {
                if ($schule_optgroup!=sql_result ($zwischenresult, $i, 'schule.id')) {
                    if ($schule_optgroup!=0)
                        echo '</optgroup>';
                    $schule_optgroup=sql_result ($zwischenresult, $i, 'schule.id');
                    echo '<optgroup label="'.sql_result ( $zwischenresult, $i, 'schule.kuerzel' ).'">';
                } ?>
				<option value="<?php echo sql_result ($zwischenresult, $i, 'fach_klasse.id'); ?>" <?php if ($_GET["fk"]==sql_result ($zwischenresult, $i, 'fach_klasse.id')) echo ' selected="selected"'; ?>><?php
					echo $subject_classes->nach_ids[sql_result ($zwischenresult, $i, 'fach_klasse.id')]["name"]; ?></option>
          <?php } ?>
        </optgroup></select><br />
        <label for="woche">Woche:</label> <div style="display: inline-block; width: auto;"><input type="radio" name="woche"<?php if (!isset($_GET["week"]) or $_GET["week"]==2) echo ' checked="checked"'; ?> value="2" /> beide<br />
               <input type="radio" name="woche"<?php if (isset($_GET["week"]) and $_GET["week"]==0) echo ' checked="checked"'; ?> value="0" /> A-Woche<br />
               <input type="radio" name="woche"<?php if ($_GET["week"]==1) echo ' checked="checked"'; ?> value="1" /> B-Woche</div><br />
        <label for="raum">Raum:</label>
        <?php
        // hoechstens Einzelnutzer duerfen gleich hier einen Raum anlegen
		$user=new user();
        if ($user->my["einzelnutzer"]) { ?>
			(Neu? <input type="checkbox" name="raum_neu_checkbox" id="raum_neu_checkbox" value="1" onclick="document.getElementById('raum_neu').style.display=this.checked==1?'inline':'none'; document.getElementById('raum_vorhanden').style.display=this.checked==1?'none':'inline';" />)
		<?php } ?>
        <select name="raum" id="raum_vorhanden" onchange="fill_fk_dragger(-1, this.options[this.selectedIndex].text);"></select>
		<input type="text" name="raum_neu" id="raum_neu" size="5" onkeyup="fill_fk_dragger(-1, this.value);" maxlength="10" style="display: none;" />
        <!-- TODO leeres Feld vermeiden -->
        <p style="text-align: center;">
            <div id="drag_fk" style="background-color: pink; width: 95px; margin-left: auto; margin-right: auto;">
                <span id="dragger_fk">FK</span><br />
                <span id="dragger_room">room</span>
            </div>
        </p>
      </fieldset>
      </form>
    </div>
    
    <!--Stundenplan anzeigen-->
	<div style="text-align: center">
    <table style="margin-left: auto; margin-right: auto;" class="stundenplan" cellspacing="0">
      <tr>
        <th>&nbsp;</th>
        <th style="min-width: 100px;">Montag</th>
        <th style="min-width: 100px;">Dienstag</th>
        <th style="min-width: 100px;">Mittwoch</th>
        <th style="min-width: 100px;">Donnerstag</th>
        <th style="min-width: 100px;">Freitag</th>
        <?php if (count($schule)>=1)
        foreach($schule as $hilf) {
			echo '<th>'.$hilf['schule_kuerzel'];
			if (!$null_stunden) echo '<br />('.$hilf['anzahl'].' Ustd)</th>';
			//if ($queryhilf!="") $queryhilf.= ' OR '; $queryhilf.='`stundenzeiten_beschreibung`.`id`='.$hilf["id"];
		} ?>
      </tr>
    <?php
	$aufsicht_result=db_conn_and_sql("SELECT * FROM aufsicht WHERE schuljahr=".$aktuelles_jahr." AND user=".$_SESSION['user_id']." ORDER BY nach_stunde, wochentag");
	$aufsicht_zaehler=0;
	
    $stundenzeiten_result=db_conn_and_sql('SELECT `stundenzeiten`.`beginn`,`stundenzeiten`.`schule`, stundenzeiten.id, `schule_user`.`aktiv`
																FROM `stundenzeiten`,`schule`, `schule_user`
																WHERE `stundenzeiten`.`schule`=`schule`.`id`
																	AND `schule_user`.`schule`=`schule`.`id`
																	AND `schule_user`.`user`='.$_SESSION['user_id'].'
																ORDER BY `stundenzeiten`.`schule`, `beginn` ASC'); // WHERE `stundenzeiten`.`beschreibung`=`stundenzeiten_beschreibung`.`id`  // AND ('.$queryhilf.')
    $stundenzeiten='';
    for($i=0;$i<sql_num_rows($stundenzeiten_result);$i++) {
		$hilfszeit=explode(":",sql_result($stundenzeiten_result,$i,'stundenzeiten.beginn'));
		$stundenzeiten[sql_result($stundenzeiten_result,$i,'stundenzeiten.schule')][]=array($hilfszeit[0],$hilfszeit[1], "id"=>sql_result($stundenzeiten_result,$i,'stundenzeiten.id'));
		if (sql_result($stundenzeiten_result,$i,'schule_user.aktiv')) $stundenzeit_neu[]=$hilfszeit[0].":".$hilfszeit[1].":00";
	}
	$max_stundenposition=0;
	if (count($schule)>=1)
	foreach($schule as $hilf) {
		$stundenzeiten[$hilf["schule_id"]][]=array(22,00);
		if (count($stundenzeiten[$hilf["schule_id"]])>$max_stundenposition)
            $max_stundenposition=count($stundenzeiten[$hilf["schule_id"]]);
	}
    
    for ($i=0;$i<$max_stundenposition-1;$i++) { ?>
      <tr>
        <td class="mittig"><?php echo ($i+1); ?>. Std</td>
    <?php
      for ($j=1;$j<=5;$j++) { ?>
        <td class="drop_fk"><input type="hidden" name="wochentag" value="<?php echo $j; ?>" />
		<?php
        $alle_aktiven_schulen=db_conn_and_sql("SELECT * FROM schule, schule_user
			WHERE schule_user.schule=schule.id
				AND schule_user.aktiv=1
				AND schule_user.user=".$_SESSION['user_id']);
        for($hilf=0; $hilf<sql_num_rows($alle_aktiven_schulen); $hilf++) { ?>
            <input type="hidden" name="zeit_schule_<?php echo sql_result($alle_aktiven_schulen, $hilf, "schule.id"); ?>" value="<?php echo $stundenzeiten[sql_result($alle_aktiven_schulen, $hilf, "schule.id")][$i]['id']; ?>" />
        <?php }
            if (sql_num_rows($aufsicht_result)>$aufsicht_zaehler)
                if (sql_result($aufsicht_result,$aufsicht_zaehler,"aufsicht.wochentag")==$j and sql_result($aufsicht_result,$aufsicht_zaehler,"aufsicht.nach_stunde")==$i) {
                    echo '<div class="aufsicht" style="float: ';
                    switch (sql_result($aufsicht_result,$aufsicht_zaehler,"aufsicht.woche")) {
                        case 0: echo "left; margin-right: 7px; border-left: darkred solid 2px;"; break;
                        case 1: echo "right; border-right: darkred solid 2px;"; break;
                    }
                    echo '"><a href="'.$pfad.'formular/aufsicht.php?aufsicht_id='.sql_result($aufsicht_result,$aufsicht_zaehler,"aufsicht.id").'" onclick="fenster(this.href, \'Aufsicht\'); return false;">'.sql_result($aufsicht_result,$aufsicht_zaehler,"aufsicht.bemerkung").'</a></div>';
                    if (sql_result($aufsicht_result,$aufsicht_zaehler,"aufsicht.woche")!=2)	echo '<br class="aufsicht" />';
                    $aufsicht_zaehler++;
                }
			
			for($k=0;$k<count($stundenplan);$k++)
				if (date('w',$stundenplan[$k]['zeit'])==$j
						and mktime($stundenzeiten[$stundenplan[$k]['schule_id']][$i][0], $stundenzeiten[$stundenplan[$k]['schule_id']][$i][1],0,1,$j,2007)<=$stundenplan[$k]['zeit']
						and mktime($stundenzeiten[$stundenplan[$k]['schule_id']][$i+1][0], $stundenzeiten[$stundenplan[$k]['schule_id']][$i+1][1],0,1,$j,2007)>$stundenplan[$k]['zeit']) { //noch aendern mit echten stundenzeiten - immer noch?
                    $onmouse=' onmouseover="document.getElementById(\'del_'.$stundenplan[$k]['id'].'\').style.display=\'inline\'; document.getElementById(\'wo_break_'.$stundenplan[$k]['id'].'\').style.display=\'inline\';" onmouseout="document.getElementById(\'del_'.$stundenplan[$k]['id'].'\').style.display=\'none\'; document.getElementById(\'wo_break_'.$stundenplan[$k]['id'].'\').style.display=\'none\';"';
                    if ($stundenplan[$k]['gerade_woche']==2)
                          {echo "<div style=\"clear: both; background-color: #".$subject_classes->nach_ids[$stundenplan[$k]['fk_id']]["farbe"].";\"".$onmouse.">".$subject_classes->nach_ids[$stundenplan[$k]['fk_id']]["name"]."<div style=\"font-style: italic; \" title=\"".$stundenplan[$k]['raumkommentar']."\">".$stundenplan[$k]['raum'];}
					else {
						echo "<div style=\"background-color: #".$subject_classes->nach_ids[$stundenplan[$k]['fk_id']]["farbe"]."; float: ";
						switch ($stundenplan[$k]['gerade_woche']) {case 0: echo "left; margin-right: 7px; border-left: darkred solid 2px;"; break; case 1: echo "right; border-right: darkred solid 2px;"; break;}
						echo "\"".$onmouse.">".$subject_classes->nach_ids[$stundenplan[$k]['fk_id']]["name"]."<br /><div style=\"font-style: italic; float: left;\" title=\"".$stundenplan[$k]['raumkommentar']."\">".$stundenplan[$k]['raum'];
                        switch ($stundenplan[$k]['gerade_woche']) { case 0: echo "(A)"; break; case 1: echo "(B)"; break;}
						//echo '<br />';
					}
					//echo '<a href="'.$pfad.'formular/stundenplan_neu.php?wochentag='.$j.'&amp;stundenplan_id='.$stundenplan[$k]['id'].'" onclick="fenster(this.href,\'Stunde hinzuf&uuml;gen\'); return false;" class="icon" name="bearbeiten"'; if ($_GET["bearbeiten"]!="true") echo ' style="display: none;"'; echo '><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>';
					// Stundenplanverbindung nur anklickbar, wenn zwei Stunden aufeinander folgen
					/*if ($stundenplan[$k]['stundenzeit_nr']==$stundenplan[$k+1]['stundenzeit_nr']-1
							and $stundenplan[$k]['fk_id']==$stundenplan[$k+1]['fk_id'])
						echo '<a href="'.$pfad.'formular/stundenplan_verbindung.php?stunden_id='.$stundenplan[$k]['id'].'" id="wo_break_'.$stundenplan[$k]['id'].'" style="float: right; display: none;" title="Unterrichtsstunde ohne Pause unterrichten" class="icon"><img src="'.$pfad.'icons/ohne_pause.png" alt="ohne Pause" /></a>';*/
					echo '<a href="'.$pfad.'formular/stundenplan_loeschen.php?was1='.$stundenplan[$k]['id'].'&amp;auswahl='.$_GET["auswahl"].'" id="del_'.$stundenplan[$k]['id'].'" name="bearbeiten"'; if ($_GET["bearbeiten"]!="true") echo ' style="float: right; display: none;"'; echo ' title="Eintrag l&ouml;schen" class="icon"><img src="'.$pfad.'icons/delete.png" alt="l&ouml;schen" /></a>
                    </div>
					</div>';
				}
				//echo '<a href="'.$pfad.'formular/stundenplan_neu.php?wochentag='.$j.'&amp;stundenzeit='.$stundenzeit_neu[$i].'" onclick="fenster(this.href,\'Stunde hinzuf&uuml;gen\'); return false;" class="icon" name="bearbeiten" style="clear: both;'; if ($_GET["bearbeiten"]!="true") echo ' display: none;'; echo '"><img src="'.$pfad.'icons/neu.png" alt="neu" /></a>';
			?>
        </td>
      <?php } ?>
        <?php 
		foreach($schule as $value) {
			$hilf=mktime($stundenzeiten[$value['schule_id']][$i][0],$stundenzeiten[$value['schule_id']][$i][1],0,1,1,2007);
			if($stundenzeiten[$value['schule_id']][$i][0]!=22 and $stundenzeiten[$value['schule_id']][$i][0]!=0)
                echo '<td class="mittig">'.date("H:i",$hilf).'<br />'.date("H:i",$hilf+(45*60)).'</td>';
            else
                echo '<td></td>';
        } ?>
      </tr>

      <?php
      }
    ?>
    </table>
	<div class="nicht_drucken"><p><a href="<?php echo $pfad; ?>formular/aufsicht.php" onclick="fenster(this.href, 'Aufsicht'); return false;">Aufsicht eintragen</a></p></div>
    </div>
    <?php // } else echo 'Kein Stundenplan f&uuml;r '.$aktuelles_jahr.' eingetragen. Tragen Sie <a href="'.$pfad.'formular/stundenplan_neu.php?wochentag='.$j.'&amp;stundenzeit='.$stundenzeit_neu[$i].'" onclick="fenster(this.href,\'Stunde hinzuf&uuml;gen\'); return false;" class="icon"><img src="'.$pfad.'icons/neu.png" alt="neu" /> hier</a> die erste Stunde ein.';
	}
	
	
	if ($_GET["auswahl"]=="kalender") {
		$schulen=db_conn_and_sql("SELECT * FROM `schule`, `schule_user`
			WHERE `schule_user`.`aktiv`=1
				AND `schule_user`.`schule`=`schule`.`id`
				AND `schule_user`.`user`=".$_SESSION['user_id']."
			ORDER BY `schule_user`.`schule` DESC");
		
	echo '<select onchange="document.location.href=\''.$pfad.'index.php?tab=stundenplan&amp;auswahl=kalender&amp;schule=\'+this.value">';
	$ausgewaehlte_schule=0;
	while ($schulweise=sql_fetch_assoc($schulen)) {
		if ($ausgewaehlte_schule==0)
			$ausgewaehlte_schule=$schulweise["id"];
		echo '<option value="'.$schulweise["id"].'"';
		if ($schulweise["id"]==injaway($_GET["schule"])) {
			echo ' selected="selected"';
			$ausgewaehlte_schule=$schulweise["id"];
		}
		echo '>'.$schulweise["name"].'</option>';
		// $ausgewaehlte_schule=4;
	}
	echo '</select><br />';
		
	$rechte_am_schuljahr=userrigths("ab-wochentausch", $ausgewaehlte_schule);
	
	$start_ende=schuljahr_start_ende($aktuelles_jahr,$ausgewaehlte_schule);
	
	$konferenzen=db_conn_and_sql("SELECT * FROM konferenz
		WHERE user=".$_SESSION['user_id']."
			AND schule=".$ausgewaehlte_schule."
		ORDER BY konferenz.datum, konferenz.zeit");
		
	$konferenz_zaehler=0;
	if (sql_num_rows($konferenzen)>0)
		while ($konferenz_zaehler<(sql_num_rows($konferenzen)) and // Abbruchbedingung
				sql_result($konferenzen,$konferenz_zaehler,"konferenz.datum")<$start_ende["start"])
			$konferenz_zaehler++;
		
	
	$wochentage=schuljahr_uebersicht($aktuelles_jahr,$ausgewaehlte_schule);
		
	echo '<table class="tabelle" cellspacing="0" style="text-align:center; margin-right: 20px; float: left;"><tr><th>A/B</th>';
	for ($wochentag=1;$wochentag<=5;$wochentag++) {
		$j=0;  
		for ($woche=0;$woche<count($wochentage[$wochentag]);$woche++)
			if ($wochentage[$wochentag][$woche]["unterricht"]==1)
				$j++;
		echo '<th>'.$wochennamen_kurz[date("w",$wochentage[$wochentag][1]["datum"])]."<br />(".$j.")</th>";
	}
	echo '</tr>';
	for ($woche=0;$woche<count($wochentage[5])+1;$woche++) {
		if ($woche==15 or $woche==31) echo '</table><table class="tabelle" cellspacing="0" style="text-align:center; margin-right: 20px; float: left;">';
		if (isset($wochentage[1][$woche]["datum"])){
			echo '<tr>';
			if ($wochentage[1][$woche]["a_woche"])
				echo '<td class="a_woche">'.$wochentage[1][$woche]["lfd_woche"].' A';
			else
				echo '<td class="b_woche">'.$wochentage[1][$woche]["lfd_woche"].' B';
			if ($rechte_am_schuljahr)
				echo ' <a href="'.$pfad.'formular/woche_tauschen.php?schuljahr='.$aktuelles_jahr.'&amp;schule='.$ausgewaehlte_schule.'&amp;datum='.date("Y-m-d",$wochentage[1][$woche]["datum"]-60*60*24).'" class="icon" title="Diese Woche und die nachfolgenden tauschen"><img src="'.$pfad.'icons/ab_woche_tauschen.png" alt="ab_woche" /></a>';
			echo '</td>';
			for ($wochentag=1;$wochentag<=5;$wochentag++) {
				$konferenz_title='';
				if ($konferenz_zaehler<sql_num_rows($konferenzen) and sql_result($konferenzen,$konferenz_zaehler,"konferenz.datum")<=date("Y-m-d",$wochentage[$wochentag][$woche]["datum"])) {
					$konferenz_title=html_umlaute(sql_result($konferenzen,$konferenz_zaehler,"konferenz.titel")).' ('.datum_strich_zu_punkt(sql_result($konferenzen,$konferenz_zaehler,"konferenz.datum")).' '.substr(sql_result($konferenzen,$konferenz_zaehler,"konferenz.zeit"),0,5).' Uhr); ';
					$konferenz_zaehler++;
				}
				if ($wochentage[$wochentag][$woche]["unterricht"]==1) {
					echo '<td title="'.$konferenz_title.'" ';
					if (modulo(date("m",$wochentage[$wochentag][$woche]["datum"]),2)==1) echo 'class="monat_1"'; else echo 'class="monat_2"';
					if ($konferenz_title!='') echo ' style="background-color: orange;"';
					echo '>'.date("d.m.",$wochentage[$wochentag][$woche]["datum"]).'</td>';
				}
				else {
					echo '<td title="'.$konferenz_title.$wochentage[$wochentag][$woche]["unterricht"].'" ';
					if (modulo(date("m",$wochentage[$wochentag][$woche]["datum"]),2)==1) echo 'class="monat_1"'; else echo 'class="monat_2"';
					if ($konferenz_title!='') echo ' style="background-color: orange;"';
					echo '>-</td>';
				}
			}
			echo "</tr>";
		}
	}
	echo '</table>';
		echo '<br style="clear: both;" /><p><fieldset style="clear: both;"><legend>Feiertage</legend><form>';
	  		$tag=besondere_tage($aktuelles_jahr, sql_result(db_conn_and_sql("SELECT * FROM schule, schule_user WHERE schule.id=schule_user.schule AND schule_user.aktiv=1 AND schule_user.user=".$_SESSION["user_id"]." ORDER BY schule.id DESC"),0,"schule.id"));
	  		for ($i=0;$i<count($tag);$i++) { echo '<label for="'.$tag[$i]['name'].'" style="width: 250px">'.$tag[$i]['name'].':</label> <span name="'.$tag[$i]['name'].'">'.date("d.m.Y",$tag[$i]['datum']).'</span>'; ?> <br /><?php }
			echo '</fieldset></form></p>';
	}
	
	if ($_GET["auswahl"]=="konferenz") {
		$formularziel = "index.php?tab=stundenplan&amp;auswahl=konferenz";
		include $pfad."formular/konferenz.php";
	}

	if ($_GET["auswahl"]=="kollegen") {
		$formularziel = "index.php?tab=stundenplan&amp;auswahl=kollegen";
		include $pfad."formular/kollegen.php";
	}

	?>
	</div>
    <?php
    }
    
    
	
	
	
	
	
	// --------------------------------------------- ZENSUREN --------------------------------------------------------------
    if ($_GET['tab']=='noten') {
        if (isset($_GET['auswahl']))
            $_GET['auswahl']=$subject_classes->cont[$subject_classes->active]["id"];
    
    
    if (isset($_GET['auswahl']) and proofuser("fach_klasse",$_GET["auswahl"])) {
        //db_conn_and_sql("UPDATE benutzer SET letzte_fachklasse=".$_GET["auswahl"]." WHERE id=1");

		$db=new db;
		$jahr=$db->aktuelles_jahr();
		
		$notenansicht=noten_von_fachklasse(injaway($_GET["auswahl"]), $aktuelles_jahr, true); // TODO: (doppelt) wird bis jetzt noch nicht bei "Eintragen" verwendet!
		?>
    <div class="navigation_3">
		<a href="javascript:window.print();" class="icon"><img src="<?php echo $pfad; ?>icons/drucken.png" alt="drucken"  title="diese Seite drucken" /></a> |
		<?php echo $navigation; ?></div>
	<div class="inhalt">
    <?php
    if (isset($_GET['eintragen']) or !isset($notenansicht['schueler'])) {
    ?>
	<fieldset><legend>Zensurenspalten</legend>
	<form action="<?php echo $pfad; ?>formular/notenbeschreibung_hinzufuegen.php" method="post" accept-charset="ISO-8859-1">
	<table>
	<?php
		// TODO: auf funktion noten_von_fachklasse umstellen
		$schule=db_conn_and_sql("SELECT schule FROM klasse, fach_klasse WHERE klasse.id=fach_klasse.klasse AND fach_klasse.id=".$_GET["auswahl"]);
		$schule=sql_fetch_assoc($schule);
		$schule=$schule["schule"];
		$start_ende=schuljahr_start_ende($jahr,$schule);
		
		$beschreibung=db_conn_and_sql("SELECT *, IF(`notenbeschreibung`.`datum` IS NULL,`plan`.`datum`,`notenbeschreibung`.`datum`) as `MyDatum`
			FROM `notentypen`,`bewertungstabelle`, `notenbeschreibung` LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
			WHERE (('".$start_ende["start"]."'<=`notenbeschreibung`.`datum` AND '".$start_ende["ende"]."'>=`notenbeschreibung`.`datum`)
				OR ('".$start_ende["start"]."'<=`plan`.`datum` AND '".$start_ende["ende"]."'>=`plan`.`datum`))
				AND `notenbeschreibung`.`notentyp`=`notentypen`.`id`
				AND `notenbeschreibung`.`bewertungstabelle`=`bewertungstabelle`.`id`
				AND `notenbeschreibung`.`fach_klasse`=".injaway($_GET["auswahl"])."
			ORDER BY `MyDatum`");
		
		for ($i=0;$i<sql_num_rows($beschreibung);$i++) {
			echo '<tr onmouseover="document.getElementById(\'spalte_'.$i.'\').className=\'over\';" onmouseout="document.getElementById(\'spalte_'.$i.'\').className=\'\';">
				<td>'.datum_strich_zu_punkt(sql_result($beschreibung,$i,"MyDatum")).'</td><td>';
			if (sql_result($beschreibung,$i,"notenbeschreibung.halbjahresnote")) echo "HJ"; else echo "GJ";
			echo '</td><td id="spalte_'.$i.'">'.html_umlaute(sql_result($beschreibung,$i,"notentypen.kuerzel")).' '.html_umlaute(sql_result($beschreibung,$i,"notenbeschreibung.beschreibung")).' ';
			//if (sql_result($beschreibung,$i,"bewertungstabelle.id")!=sql_result ( $result, $fk_result_nr, 'fach_klasse.bewertungstabelle' )) {
				echo '['.html_umlaute(sql_result($beschreibung,$i,"bewertungstabelle.name"));
				if (sql_result($beschreibung,$i,"bewertungstabelle.punkte")) echo " (Pkte)] "; else echo "] ";
			//}
			echo '</td><td>';
			if (sql_result($beschreibung,$i,"notenbeschreibung.plan")==NULL) echo '<a href="'.$pfad.'formular/notenbeschreibung_plan.php?beschreibung='.sql_result($beschreibung,$i,"notenbeschreibung.id").'" onclick="fenster(this.href,  \'Unterrichtsstunde zuordnen\'); return false;" class="icon" title="an eine geplante Unterrichsstunde anh&auml;ngen"><img src="./icons/plan.png" alt="plan" /></a>';
			echo '</td><td>';
			echo '<img src="'.$pfad.'icons/'; if (sql_result($beschreibung,$i,"notenbeschreibung.test")!=NULL) echo 'haekchen.png" alt="haekchen"'; else echo 'abhaken.png" alt="kein haekchen"'; echo ' title="'; if (sql_result($beschreibung,$i,"notenbeschreibung.test")!=NULL) echo 'Ein Test wurde zugeordnet" checked="checked"'; else echo 'Es wurde kein Test zugeordnet"'; echo ' />';
			if (sql_result($beschreibung,$i,"notenbeschreibung.test")==NULL) echo '<a href="'.$pfad.'formular/notenbeschreibung_test.php?beschreibung='.sql_result($beschreibung,$i,"notenbeschreibung.id").'" onclick="fenster(this.href,  \'test zuordnen\'); return false;" class="icon" title="mit einem Test verkn&uuml;pfen"><img src="./icons/test.png" alt="test" /></a>';
			echo '</td><td>';
			echo '<a href="'.$pfad.'formular/noten_bearbeiten.php?beschreibung='.sql_result ( $beschreibung, $i, 'notenbeschreibung.id' ).'" onclick="fenster(\''.$pfad.'formular/noten_bearbeiten.php?beschreibung='.sql_result ( $beschreibung, $i, 'notenbeschreibung.id' ).'&amp;schuljahr='.$aktuelles_jahr.'\',\'Noten bearbeiten\'); return false;" title="bearbeiten" class="icon"><img src="./icons/edit.png" alt="bearbeiten" /></a>';
			echo '</td><td>';
			if (sql_num_rows(db_conn_and_sql("SELECT * FROM `noten` WHERE `noten`.`beschreibung`=".sql_result ($beschreibung, $i, 'notenbeschreibung.id')))==0) echo ' <a href="'.$pfad.'formular/notenbeschreibung_entfernen.php?auswahl='.$_GET["auswahl"].'&amp;notenbeschreibung_id='.sql_result ( $beschreibung, $i, 'notenbeschreibung.id' ).'&amp;eintragen=true" class="icon" onclick="if (confirm(\'Die Zensurenspalte wird gel&ouml;scht. Wollen Sie das wirklich?\')==false) return false;"><img src="'.$pfad.'icons/delete.png" alt="delete" title="l&ouml;schen" /></a>';
			echo '</td></tr>';
		} ?>
	</table>
	</form>
	<button onclick="javascript:fenster('<?php echo $pfad.'formular/notenspalte_neu.php?fk='.$_GET["auswahl"]; ?>', 'Neue Zensur')"><img src="<?php echo $pfad; ?>icons/zensur.png" alt="test" /> Leistungs&uuml;berpr&uuml;fung hinzuf&uuml;gen</button>
    </fieldset>
	
	<br />
	
	<fieldset><legend>Zensurenberechnung</legend>
	<form action="<?php echo $pfad; ?>formular/notenberechnung_eintragen.php" method="post" accept-charset="ISO-8859-1">
	<input type="hidden" name="fach_klasse" value="<?php echo injaway($_GET["auswahl"]); ?>" />
	
	<?php
	$user=new user();
	$schule=$user->my["letzte_schule"];
	?>
	<label for="vorlage" style="width: 220px;">Zensurenberechnungsvorlage:</label>
	<select id="vorlage" name="vorlage"<?php if (userrigths("notenberechnungsvorlagen", $schule)!=2) echo " onchange=\"this.value.split('-')[1]=='v'?getElementById('nbv_bearbeiten_button').style.visibility='visible':getElementById('nbv_bearbeiten_button').style.visibility='hidden'\""; ?>>
		<?php echo notenberechnungsvorlagen_select(injaway($_GET["auswahl"]),"nbv_bearbeiten_button"); ?>
	</select>
	<?php
	// button vorher visible/hidden setzen
	$vorlagen_der_schule=db_conn_and_sql("SELECT notenberechnungsvorlage.user
			FROM fach_klasse, schule_user, notenberechnungsvorlage
			WHERE fach_klasse.id=".injaway($_GET["auswahl"])."
				AND notenberechnungsvorlage.id=fach_klasse.notenberechnungsvorlage
				AND schule_user.schule=notenberechnungsvorlage.schule
				AND schule_user.user=".$_SESSION["user_id"]."
			ORDER BY notenberechnungsvorlage.name");
	$vorlagen_der_schule=sql_fetch_assoc($vorlagen_der_schule);
	
	if (userrigths("notenberechnungsvorlagen", $schule)==2) {
		?>
		<button id="nbv_bearbeiten_button" onclick="javascript: fenster('<?php echo $pfad; ?>formular/notenberechnungsvorlage.php?id='+document.getElementById('vorlage').value.split('-')[0], 'Zweitfenster');"<?php if ($vorlagen_der_schule["user"]!=$_SESSION["user_id"] and userrigths("zensurentypen", $schule)!=2) echo ' style="visibility: hidden;"'; ?>><img src="<?php echo $pfad; ?>icons/edit.png" /> Vorlage bearbeiten</button>
		<button onclick="javascript: fenster('<?php echo $pfad; ?>formular/notenberechnungsvorlage.php', 'Zweitfenster');"><img src="<?php echo $pfad; ?>icons/neu.png" /> Vorlage erstellen</button><br />
		<?php
	} ?>
	<label for="bewertungstabelle" style="width: 220px;">Standard-Bewertungstabelle:</label>
	<select name="bewertungstabelle" title="legen Sie hier die Standard-Bewertungstabelle der Fach-Klassen-Kombination fest">
		<?php echo bewertungstabelle_select(injaway($_GET["auswahl"])); ?>
	</select><br />
	<input type="submit" onsubmit="getElementById('vorlage').value=getElementById('vorlage').value.split('-')[0]" value="speichern" />
	</form>
	</fieldset>
	
    <?php } else {
		$max_eintraege_noten=15;
		$startnote=0;
		if (count($notenansicht['notenbeschreibung'])>$max_eintraege_noten and $_GET["anzeigen"]!="alle") {
			$startnote=count($notenansicht['notenbeschreibung'])-$max_eintraege_noten;
			echo '<p><span class="hinweis">Es werden lediglich die letzten '.$max_eintraege_noten.' Eintr&auml;ge angezeigt. <a href="'.$pfad.'index.php?tab=noten&amp;auswahl='.$_GET["auswahl"].'&amp;anzeigen=alle">[Alle anzeigen]</a></span></p>';
		}
		
		function notenspaltendatum_anzeigen($j, $notenansicht) {
			$datumszaehler=0;
			$eintragungen=0;
			$i=0;
			while ($i<count($notenansicht['schueler']) and $datumszaehler<5 and $i<30) {
				if (substr($notenansicht['schueler'][$i]['noten'][$j]['datum'],0,6)==$notenansicht['notenbeschreibung'][$j]['datum'])
					$datumszaehler++;
				if ($notenansicht['schueler'][$i]['noten'][$j]['wert']!="")
					$eintragungen++;
				$i++;
			}
			
			// wenn weniger als die Haelfte der gezaehlten Schueler das genutzte Datum haben
			if ($eintragungen>0 and $datumszaehler/$eintragungen<0.5)
				return false;
			else
				return true;
		}
	?>
    <table class="tabelle2" cellspacing="0">
      <tr>
        <th><?php echo $subject_classes->cont[$subject_classes->active]["farbanzeige"]."<br />".$notenansicht['berechnungsvorlage']."<br />Stand: ".date("d.m.y"); ?></th><?php
		
		$halbjahresumbruch=false;
		
		if (count($notenansicht['schueler'])>0)
        for($i=$startnote;$i<count($notenansicht['notenbeschreibung']);$i++) {
			if (!$halbjahresumbruch and $notenansicht['notenbeschreibung'][$i]['halbjahresnote']!=1) {
				$halbjahresumbruch=$i;
				if ($notenansicht['notenbeschreibung'][0]['halbjahresnote']!=0)
					echo '<th>HJ</th>';
			} ?>
			<th style="vertical-align: top;">
			<?php
				if ($notenansicht['notenbeschreibung'][$i]['meine_fach_klasse']) {
					echo '<a href="'.$pfad.$notenansicht['notenbeschreibung'][$i]['link'].'" onclick="fenster(this.href,\'Noten bearbeiten\'); return false;" title="'.$notenansicht['notenbeschreibung'][$i]['beschreibung']." <br />".$notenansicht['notenbeschreibung'][$i]['kommentar'];
					if ($notenansicht['notenbeschreibung'][$i]['nichtInGruppe'])
						echo "<br />".'Dieser Zensurentyp ist in der Berechnungsvorlage nicht in einer Zensurengruppe enthalten - Achten Sie auf eventuelle Berechnungsfehler (es sei denn, alle Zensuren sind gleichwertig)." style="color: orange;';
					echo '">';
					echo $notenansicht['notenbeschreibung'][$i]['notentyp_kuerzel'].'</a>';
					if ($notenansicht['notenbeschreibung'][$i]['notenspiegel']!=false)
						echo ' <a href="'.$pfad.'formular/test_auswertung.php?beschreibung_id='.$notenansicht['notenbeschreibung'][$i]['id'].'" onclick="fenster(this.href,\'Test-Auswertung\'); return false;" class="icon" title="Test-Auswertung"><img src="'.$pfad.'icons/test_auswertung.png" alt="A" /></a>';
					echo '<br />';
					// Test, ob Datum angezeigt werden soll
					if (notenspaltendatum_anzeigen($i, $notenansicht))
						echo $notenansicht['notenbeschreibung'][$i]['datum'];
					echo '<br />';
					if ($notenansicht['notenbeschreibung'][$i]['notenspiegel']!=false) {
						if ($notenansicht['notenbeschreibung'][$i]['punkte_oder_zensuren']!=1) {
							echo '<span style="font-weight: normal; font-size: 7pt;">';
							foreach ($notenansicht['notenbeschreibung'][$i]['notenspiegel'] as $n) {
								echo '<span';
								if($n['punkte_bis_zahl']>0) echo ' title="bis: '.$n['punkte_bis'].' Punkte"';
								echo '>'.$n['note'].'</span>&nbsp;';
							}
							echo '<br />';
							foreach ($notenansicht['notenbeschreibung'][$i]['notenspiegel'] as $n)
								echo $n['anzahl_schueler'].'&nbsp;';
							if ($notenansicht['notenbeschreibung'][$i]['durchschnitt']>0)
								echo '<br />&Oslash; '.number_format ($notenansicht['notenbeschreibung'][$i]['durchschnitt'], 2, ',', '.' );
							echo '</span>';
						}
					}
					else
						echo '<br /><a href="'.$pfad.'formular/notenbeschreibung_entfernen.php?auswahl='.$_GET["auswahl"].'&amp;notenbeschreibung_id='.$notenansicht['notenbeschreibung'][$i]['id'].'" class="icon" onclick="if (confirm(\'Die Zensurenspalte wird gel&ouml;scht. Wollen Sie das wirklich?\')==false) return false;"><img src="'.$pfad.'icons/delete.png" alt="delete" title="l&ouml;schen" /></a>';
				}
				else { // Notenspalten eines anderen Fachlehrers
					echo '<span title="'.$notenansicht['notenbeschreibung'][$i]['beschreibung']." <br />".$notenansicht['notenbeschreibung'][$i]['kommentar'];
					if ($notenansicht['notenbeschreibung'][$i]['nichtInGruppe'])
						echo "<br />".'Dieser Zensurentyp ist in der Berechnungsvorlage nicht in einer Zensurengruppe enthalten - Achten Sie auf eventuelle Berechnungsfehler (es sei denn, alle Zensuren sind gleichwertig)." style="color: blue;';
					echo '">';
					echo $notenansicht['notenbeschreibung'][$i]['notentyp_kuerzel'].'</span>';
					echo '<br />'.$notenansicht['notenbeschreibung'][$i]['datum'];
					echo '<br />'.$notenansicht['notenbeschreibung'][$i]['lehrer_kuerzel'];
				}
			?>
			</th>
			<?php
        } ?>
        <th><?php if ($halbjahresumbruch===false) echo "HJ"; else echo "GJ"; ?></th>
        <th><a href="javascript:fenster('<?php echo $pfad.'formular/notenspalte_neu.php?fk='.$_GET["auswahl"]; ?>', 'Neue Zensur')" title="Leistungs&uuml;berpr&uuml;fung hinzuf&uuml;gen" class="icon"><img src="<?php echo $pfad; ?>icons/zensur.png" alt="test" /></a></th>
      </tr>

    <?php
    $schueler_zaehler=0;
	if (count($notenansicht['schueler'])>0)
	for($i=0;$i<count($notenansicht['schueler']);$i++) { // if(gehoert_zur_gruppe($_GET["auswahl"], $notenansicht['schueler'][$i]['id']))
		$schueler_zaehler=$notenansicht['schueler'][$i]['position']; ?>
		<tr><td<?php
			if(($i+1)/2!=round(($i+1)/2) or ($i+1)/5==round(($i+1)/5)) echo ' style="';
			if(($i+1)/2!=round(($i+1)/2)) echo 'background-color: lightgrey;';
			if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;';
			if(($i+1)/2!=round(($i+1)/2) or ($i+1)/5==round(($i+1)/5)) echo '"'; ?>>
		<?php echo ($i+1).'&nbsp;'.$notenansicht['schueler'][$i]['name'].',&nbsp;'.$notenansicht['schueler'][$i]['vorname']; ?></td>
		
		<?php
        for($j=$startnote;$j<count($notenansicht['notenbeschreibung']);$j++) {
			if ($halbjahresumbruch!=false and $halbjahresumbruch==$j and $notenansicht['notenbeschreibung'][0]['halbjahresnote']==1) {
				echo '<td style="font-weight: bold; text-align: center;';
				if(($i+1)/2!=round(($i+1)/2)) echo ' background-color: lightgrey;';
				if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;';
				echo '">';
				echo '<span title="Berechnung:'."<br />".$notenansicht['schueler'][$i]['halbjahres_schnitt_berechnung'].'">'.$notenansicht['schueler'][$i]['halbjahres_schnitt_komma'].'</span></td>';
					
			}
			echo '<td style="text-align:center;';
			if(($i+1)/2!=round(($i+1)/2)) echo ' background-color: lightgrey;';
			if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;';
			if (!$notenansicht['notenbeschreibung'][$j]['meine_fach_klasse'])
				echo ' color: gray;';
			if ($notenansicht['schueler'][$i]['noten'][$j]["mitzaehlen"]==0 and $notenansicht['schueler'][$i]['noten'][$j]['wert']!="")
				echo 'text-decoration: line-through; color: #999;';
			echo '" title="'.$notenansicht['schueler'][$i]['noten'][$j]['punktzahl_mit_komma'].' Pkte | '.$notenansicht['schueler'][$i]['noten'][$j]['datum'].' | '.$notenansicht['schueler'][$i]['noten'][$j]['kommentar'];
			if ($notenansicht['schueler'][$i]['noten'][$j]['halbjahresnote']!=$notenansicht['notenbeschreibung'][$j]['halbjahresnote'] and $notenansicht['schueler'][$i]['noten'][$j]['wert']>0) {
				echo ' | ';
				if ($notenansicht['schueler'][$i]['noten'][$j]['halbjahresnote'])
					echo 'geht schon in Berechnung zur Halbjahresnote ein';
				else
					echo 'geht erst in Berechnung zur Ganzjahresnote ein';
			}
			echo '">
				<span';
			if ($notenansicht['schueler'][$i]['noten'][$j]['halbjahresnote']!=$notenansicht['notenbeschreibung'][$j]['halbjahresnote'] and $notenansicht['schueler'][$i]['noten'][$j]['wert']>0)
                echo ' class="grade_on_wrong_side"';
			if ($notenansicht['schueler'][$i]['noten'][$j]['kommentar']!="") echo ' style="font-weight: bold;"';
			echo '>'.$notenansicht['schueler'][$i]['noten'][$j]['wert'].$notenansicht['schueler'][$i]['noten'][$j]['notenzusatz'].'</span>';
			if ($notenansicht['schueler'][$i]['noten'][$j]['punktzahl_mit_komma']!="" and ($notenansicht['notenbeschreibung'][$j]['gesamtpunktzahl']>0 or $notenansicht['schueler'][$i]['noten'][$j]['einzelpunkte'][0]['pkt']>0)) {
				echo '<span style="font-size:9px; color: #555;"> <sup>'.$notenansicht['schueler'][$i]['noten'][$j]['punktzahl_mit_komma'].'</sup>/<sub>';
				//if ($notenansicht['schueler'][$i]['noten'][$j]['gruppe_b'])
				//	echo $notenansicht['notenbeschreibung'][$j]['gesamtpunktzahl_b'];
				//else echo $notenansicht['notenbeschreibung'][$j]['gesamtpunktzahl'];
				echo $notenansicht['schueler'][$i]['noten'][$j]['gesamtpunktzahl'];
				echo '</sub></span>';
			}
			echo '</td>';
		} ?><td style="font-weight: bold; text-align: center;<?php if(($i+1)/2!=round(($i+1)/2)) echo ' background-color: lightgrey;'; if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;'; ?>"><?php
			// Wenn die erste Notenspalte mit Punkten (anstelle von Zensuren) beginnt, wird der Durchschnitt ab dem Halbjahr neu gerechnet (in allen Bundeslaendern so?)
			if ($notenansicht['notenbeschreibung'][0]['punkte_oder_zensuren']==1) {
				if ($halbjahresumbruch==false)
					echo '<span title="Berechnung:'."<br />".$notenansicht['schueler'][$i]['halbjahres_schnitt_berechnung'].'">'.$notenansicht['schueler'][$i]['halbjahres_schnitt_komma'].'</span></td>';
				else
					echo '<span title="Berechnung:'."<br />".$notenansicht['schueler'][$i]['halbjahr_2_schnitt_berechnung'].'">'.$notenansicht['schueler'][$i]['halbjahr_2_schnitt_komma'].'</span>';
			}
			else
				echo '<span title="Berechnung:'."<br />".$notenansicht['schueler'][$i]['ganzjahres_schnitt_berechnung'].'">'.$notenansicht['schueler'][$i]['ganzjahres_schnitt_komma'].'</span>'; ?></td>
        <td></td></tr><?php
        $schueler_zaehler++;
	} ?>
    </table>
    
    <?php
		echo '<br /><div class="hinweis"';
		$letzte_aenderung=notenhash_von_fach_klasse(injaway($_GET["auswahl"]), $aktuelles_jahr);
		if ($letzte_aenderung[0])
			echo ">Ihre letzte &Auml;nderung war am ".datum_strich_zu_punkt_uebersichtlich(substr($letzte_aenderung[1],0,10), true, false)." um ".substr($letzte_aenderung[1],11,5)." Uhr";
		else {
			echo " style=\"background-color: #C44;\">Ihre Zensuren k&ouml;nnten manipuliert worden sein!";
			if ($letzte_aenderung[1])
				echo "<br />Ihre letzte &Auml;nderung fand am ".datum_strich_zu_punkt_uebersichtlich(substr($letzte_aenderung[1],0,10), true, false)." um ".substr($letzte_aenderung[1],11,5)." Uhr statt.";
		}
		echo '</div>';
    }
    }
    
    if ($_GET["option"]=="jahresuebersicht") { ?>
    <div class="navigation_2"><?php echo $navigation; ?></div>
	<div class="inhalt">
	<p><b>Geplante Zensuren:</b>
		<ul><?php
		$schule=db_conn_and_sql("SELECT schule FROM schule_user WHERE schule_user.usertyp>0 AND schule_user.usertyp<5 AND schule_user.user=".$_SESSION["user_id"]);
		$schule=sql_fetch_assoc($schule);
		$schule=$schule["schule"];
		$start_ende=schuljahr_start_ende($aktuelles_jahr,$schule);
		$startdatum=$start_ende["start"];
		$enddatum=$start_ende["ende"];
		
            $geplant=db_conn_and_sql("SELECT *
                FROM `fach_klasse`,`klasse`,`faecher`,`notentypen`,`notenbeschreibung` LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
                WHERE `fach_klasse`.`klasse`=`klasse`.`id`
					AND `fach_klasse`.`user`=".$_SESSION['user_id']."
                    AND `notenbeschreibung`.`fach_klasse`=`fach_klasse`.`id`
                    AND `notenbeschreibung`.`notentyp`=`notentypen`.`id`
                    AND `fach_klasse`.`fach`=`faecher`.`id`
                    AND (('".$startdatum."'<=`notenbeschreibung`.`datum` AND '".$enddatum."'>=`notenbeschreibung`.`datum`)
                    OR ('".$startdatum."'<=`plan`.`datum` AND '".$enddatum."'>=`plan`.`datum`))
                ORDER BY `plan`.`datum` DESC, `notenbeschreibung`.`datum` DESC");
            if(@sql_num_rows($geplant)>0) {
                for ($i=0;$i<sql_num_rows($geplant);$i++) {
                    $geplante_daten[]=array(
                        'datum'=>sql_result($geplant,$i,"notenbeschreibung.datum"),
                        'woche'=>datum_zu_woche(sql_result($geplant,$i,"notenbeschreibung.datum")),
                        'beschreibung'=>html_umlaute(sql_result($geplant,$i,"notenbeschreibung.beschreibung")),
                        'fk_id'=>sql_result($geplant,$i,"fach_klasse.id"),
                        'notentyp'=>html_umlaute(sql_result($geplant,$i,"notentypen.kuerzel")),
                        'fach'=>html_umlaute(sql_result($geplant,$i,"faecher.kuerzel")));
                    // plan.datum hat hoehere Prioritaet
                    if (strlen(sql_result($geplant,$i,"plan.datum"))>1) {
                        $geplante_daten[$i]['datum']=sql_result($geplant,$i,"plan.datum");
                        $geplante_daten[$i]['woche']=datum_zu_woche(sql_result($geplant,$i,"plan.datum"));
                    }
                }
                // nach Datum sortieren
                for ($i=0;$i<count($geplante_daten)-1;$i++)
                    for ($k=$i+1;$k<count($geplante_daten);$k++)
                        if ($geplante_daten[$i]["datum"]<$geplante_daten[$k]["datum"]) {
                            $hilf=$geplante_daten[$i];
                            $geplante_daten[$i]=$geplante_daten[$k];
                            $geplante_daten[$k]=$hilf;
                        }
                if (@count($geplante_daten)>0)
                    foreach ($geplante_daten as $value)
                        echo '<li>KW '.$value["woche"].' - '.datum_strich_zu_punkt($value["datum"]).' '.$subject_classes->nach_ids[$value["fk_id"]]["farbanzeige"].' '.$value["notentyp"].' '.$value["beschreibung"].'</li>'; 
            }
        ?>
		</ul>
	</p>
    
    
    <?php }
    
    if ($_GET["option"]=="stichtagsnote") { ?>
		<div class="navigation_2"><?php echo $navigation; ?></div><div class="inhalt">
		<?php
		$formularziel=$pfad."index.php?tab=noten&amp;option=stichtagsnote";
		include($pfad."formular/stichtagsnoten_eintragen.php");
		?>
		</div>
	<?php
	}
    
    if ($_GET["option"]=="kopfnote") { ?>
		<div class="navigation_2"><?php echo $navigation; ?></div><div class="inhalt">
		<?php
		$formularziel=$pfad."index.php?tab=noten&amp;option=kopfnote";
		include($pfad."formular/kopfnoten_eintragen.php");
		?>
		</div>
	<?php
	}
    
    ?>

    <div class="nicht_drucken">
		<?php if(!isset($subject_classes->cont[0]))
			echo 'Erstellen Sie zun&auml;chst mindestens eine aktive Fach-Klassen-Kombination.'; ?>
	</div>




	</div>
	<?php }
	
	
	
    if ($_GET['tab']=='material') { ?>
    <div class="tab_2">
        <a href="index.php?tab=material&amp;auswahl=themen"<?php if ($_GET["auswahl"]=="themen") echo ' class="selected"'; ?> title="Themen"><img src="<?php echo $pfad; ?>icons/thema.png" alt="thema" /> Thema</a>
        <a href="index.php?tab=material&amp;auswahl=aufgaben"<?php if ($_GET["auswahl"]=="aufgaben") echo ' class="selected"'; ?> title="Aufgaben"><img src="<?php echo $pfad; ?>icons/aufgaben.png" alt="aufgaben" /> Aufgabe</a>
        <a href="index.php?tab=material&amp;auswahl=test"<?php if ($_GET["auswahl"]=="test") echo ' class="selected"'; ?> title="Tests"><img src="<?php echo $pfad; ?>icons/test.png" alt="test" /> Test</a>
        <a href="index.php?tab=material&amp;auswahl=link"<?php if ($_GET["auswahl"]=="link") echo ' class="selected"'; ?> title="Arbeitsblatt / Folie / Link"><img src="<?php echo $pfad; ?>icons/arbeitsblatt.png" alt="link" /> AB</a>
        <a href="index.php?tab=material&amp;auswahl=buch"<?php if ($_GET["auswahl"]=="buch") echo ' class="selected"'; ?> title="B&uuml;cher"><img src="<?php echo $pfad; ?>icons/buch.png" alt="buch" /> Buch</a>
        <a href="index.php?tab=material&amp;auswahl=grafik"<?php if ($_GET["auswahl"]=="grafik") echo ' class="selected"'; ?> title="Grafiken und Bilder"><img src="<?php echo $pfad; ?>icons/grafik.png" alt="grafik" /> Grafik</a>
        <a href="index.php?tab=material&amp;auswahl=sonstiges"<?php if ($_GET["auswahl"]=="sonstiges") echo ' class="selected"'; ?> title="sonstige Materialien"><img src="<?php echo $pfad; ?>icons/sonstiges_material.png" alt="sonstiges_material" /> Sonstiges</a>
        <a href="index.php?tab=material&amp;auswahl=suche"<?php if ($_GET["auswahl"]=="suche") echo ' class="selected"'; ?> title="Materialien suchen"><img src="<?php echo $pfad; ?>icons/suchen.png" alt="suchen" /> Suche</a>
      </div>
    <div class="navigation_2"><?php echo $navigation.' <a href="'.$pfad.'formular/hilfe.php?inhalt=material" onclick="fenster(this.href, \'Hilfe\'); return false;" class="icon"><img src="'.$pfad.'icons/hilfe.png" alt="hilfe" title="Hilfe" /></a>'; ?></div>
	<div class="inhalt">
    <?php
	 if (!isset($_GET["auswahl"])) echo 'W&auml;hlen Sie den Materialtyp aus.';
      $themen = $db->themen();
      $lernbereiche = $db->lernbereiche();
	  
    if ($_GET["auswahl"]=="themen") { ?>
      <p><button onclick="fenster('<?php echo $pfad; ?>formular/thema_neu.php', 'neues Thema');"><img src="<?php echo $pfad; ?>icons/neu.png" alt="neu" /> neues Thema anlegen</button></p>
      <p><h2>Themen&uuml;bersicht:</h2>
		<ul>
      <?php
		function rekursiv_themen($themen, $oberthema_id) {
			$zusatz_inhalt.='<ul>';
			foreach($themen[$oberthema_id] as $unterthema)
				if (isset($unterthema["id"]) && $unterthema["id"]!=$unterthema["fach_kuerzel"]) {
					$zusatz_inhalt.= '<li>'.$unterthema["bezeichnung"].' <a href="'.$pfad.'formular/thema_bearbeiten.php?thema_id='.$unterthema["id"].'" onclick="fenster(this.href,\'Thema bearbeiten\'); return false;" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a></li>';
					if (count($themen[$unterthema["id"]])>0)
						$zusatz_inhalt.=rekursiv_themen($themen, $unterthema["id"]);
				}
			$zusatz_inhalt.='</ul>';
			return $zusatz_inhalt;
		}
		
		if ($themen!="")
        foreach ($themen as $oberthema) {
			if (count($themen[$oberthema["id"]])>1) echo '<li>'.$oberthema["fach_kuerzel"].": ".$oberthema["bezeichnung"].' <a href="'.$pfad.'formular/thema_bearbeiten.php?thema_id='.$oberthema["id"].'" onclick="fenster(this.href,\'Thema bearbeiten\'); return false;" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a></li>';
			if (count($themen[$oberthema["id"]])>4) echo rekursiv_themen($themen, $oberthema["id"]);
		}
      ?>
		</ul>
      </p>
      <?php  }
	if ($_GET['auswahl']=="aufgaben") {
		eingetragenes_zeigen("aufgabe",true,$pfad.'index.php?tab=material&amp;auswahl=aufgaben','','', $pfad);
		echo '<p><button onclick="fenster(\''.$pfad.'formular/aufgabe_neu.php\', \'neue Aufgabe\');"><img src="'.$pfad.'icons/neu.png" alt="neu" /> neue Aufgabe anlegen</button></p>';
	}
	
	
		
		if ($_GET['auswahl']=="test") { ?>
			<form action="./formular/test_neu.php" method="post" enctype="multipart/form-data" accept-charset="ISO-8859-1">
				<fieldset><legend>neuer Test</legend>
					<?php echo eintragung_test(); ?>
				<!--    Gesamtpunktzahl: <input type="text" name="punkte" size="3" maxlength="3" /> <input type="text" id="punkte_anzahl" readonly="readonly" /><br />
					<img src="<?php echo $pfad; ?>/icons/zeit.png" alt="zeit" title="Bearbeitungszeit" />: <input type="text" name="zeit" size="3" maxlength="3" /> min <input type="text" id="zeit_gesamt" readonly="readonly" /><br />-->
					<input type="button" class="button" value="eintragen" onclick="auswertung=new Array(new Array(0, 'test_thema_0','nicht_leer', '-'), new Array(0, 'test_lernbereich','nicht_leer', '-')); pruefe_formular(auswertung);" /><br />
				</fieldset>
			</form>
			<?php eingetragenes_zeigen("test",true,'','','', $pfad); ?>
    <?php } ?>
	
	
      <?php if ($_GET["auswahl"]=="link") {
		eingetragenes_zeigen("link",true,$pfad.'index.php?tab=material&amp;auswahl=link','','', $pfad); ?>
		<button onclick="fenster('<?php echo $pfad; ?>formular/link_neu.php', 'Link erstellen');"><img src="<?php echo $pfad; ?>icons/neu.png" alt="neu" /> erstellen (Arbeitsblatt, Folie, Link)</button>
      
      <?php }
	  
	  if ($_GET["auswahl"]=="buch") {
      $fach = db_conn_and_sql ( 'SELECT * FROM `faecher` WHERE `user`=0 OR `user`='.$_SESSION['user_id'].' ORDER BY `name`' ); ?>
      <form action="<?php echo $pfad; ?>formular/buch_neu.php" method="post" accept-charset="ISO-8859-1">
      <fieldset><legend>Buch erstellen</legend>
     <label for="name">Titel<em>*</em>:</label> <input type="text" name="name" size="50" maxlength="100" /><br />
      <label for="untertitel">Untertitel:</label> <input type="text" name="untertitel" size="50" maxlength="200" /><br />
      <label for="kuerzel">K&uuml;rzel<em>*</em>:</label> <input type="text" name="kuerzel" size="5" maxlength="10" /><br />
      <label for="klassenstufe[]">Klassenstufe(n)<em>*</em>:</label>
        <?php for($i=1;$i<=13;$i++) { ?>
              <input type="checkbox" id="klassenstufe_<?php echo $i; ?>" name="klassenstufe[]" value="<?php echo $i; ?>" /> <?php echo $i; ?>
         <?php } ?>
      <br />
      <label for="fach">Fach<em>*</em>:</label> <select name="fach"><?php for($i=0;$i<sql_num_rows ( $fach );$i++) { ?>
              <option value="<?php echo @sql_result ( $fach, $i, 'faecher.id' ); ?>"><?php echo html_umlaute(@sql_result ( $fach, $i, 'faecher.kuerzel' )); ?></option>
      <?php } ?>
      </select>
	  <label for="schulart">Schulart<em>*</em>:</label>  
	  <select name="schulart"><?php $schulart=db_conn_and_sql("SELECT * FROM `schulart`"); for($i=0;$i<sql_num_rows ( $schulart );$i++) { ?>
              <option value="<?php echo @sql_result ( $schulart, $i, 'schulart.id' ); ?>"><?php echo html_umlaute(@sql_result ( $schulart, $i, 'schulart.kuerzel' )); ?></option>
      <?php } ?>
      </select><br />
      <label for="isbn">ISBN:</label> <input type="text" name="isbn" size="10" maxlength="20" /><br />
      <label for="verlag">Verlag:</label> <input type="text" name="verlag" size="10" maxlength="200" /><br />
      <input type="button" class="button" value="eintragen" onclick="auswertung=new Array(new Array(0, 'name','nicht_leer'), new Array(0, 'kuerzel','nicht_leer')); for(i=1;i&lt;14;i++) {if (document.getElementById('klassenstufe_'+i).checked) ausfuehren=true;} if (ausfuehren) pruefe_formular(auswertung);" />
      </fieldset>
      </form>
      <?php eingetragenes_zeigen("buch",false,$pfad.'formular/buch.php','','', $pfad); ?>

      <?php }
	  
	  if ($_GET["auswahl"]=="grafik") {
		eingetragenes_zeigen("grafik",true,$pfad.'index.php?tab=material&amp;auswahl=grafik',"","", $pfad);
		?>
		<button onclick="fenster('<?php echo $pfad; ?>formular/grafik_neu.php', 'Grafik erstellen');" class="icon"><img src="<?php echo $pfad; ?>icons/neu.png" alt="neu" /> Grafik erstellen</button>
		<?php
		}
		
	  if ($_GET["auswahl"]=="sonstiges") { ?>
      <form action="<?php echo $pfad; ?>formular/material_neu.php" method="post" accept-charset="ISO-8859-1">
      <fieldset><legend>Erstellen</legend>
		<?php echo eintragung_sonstiges_material(); ?>
		<br />
      <input type="button" class="button" value="eintragen" onclick="auswertung=new Array(new Array(0, 'material_name','nicht_leer')); pruefe_formular(auswertung);" />
      </fieldset>
      </form>
      <?php eingetragenes_zeigen("material",true,$pfad.'index.php?tab=material&amp;auswahl=sonstiges',"","", $pfad); ?>

      <?php }
	  if ($_GET["auswahl"]=="suche") { ?>
		<fieldset>
			<legend>Materialsuche - Funktioniert noch nicht</legend>
			<form>
			Thema: <select><option value="">-</option>
			<?php $db=new db;
			echo $db->themenoptions(0); ?></select><br />
			<input type="checkbox" checked="checked" name="aufgaben" /> Aufgaben; <input type="checkbox" checked="checked" name="tests" /> Tests; <input type="checkbox" checked="checked" name="arbeitsblaetter" /> Arbeitsbl&auml;tter; <input type="checkbox" checked="checked" name="folien" /> Folien; <input type="checkbox" checked="checked" name="links" /> Links/Dateien; <input type="checkbox" checked="checked" name="bilder" /> Bilder; <input type="checkbox" checked="checked" name="sonstiges" /> sonstiges Material durchsuchen<br />
			Schulart: <select name="schulart"><option value="">alle</option><?php
				$schulart=db_conn_and_sql("SELECT * FROM `schulart`");
				for($i=0;$i<sql_num_rows($schulart);$i++) {
					echo '<option value="'.sql_result($schulart,$i,"schulart.id").'"';
					if ($_POST["schulart"]==sql_result($schulart,$i,"schulart.id")) echo ' selected="selected"';
					echo '>'.html_umlaute(sql_result($schulart,$i,"schulart.kuerzel")).'</option>';
				}
				echo '</select>';
				
				echo ' Kl. <select name="klasse"><option value="">alle</option>';
				for($i=1;$i<14;$i++) {
					echo '<option value="'.$i.'"';
					if ($_POST["klasse"]==$i) echo ' selected="selected"';
					echo '>'.$i.'</option>';
				}
				echo '</select>';
				
				echo '<br />in einem Abschnitt verwendet: <input type="radio" value="1" name="abschnittslink"'; if($_POST["abschnittslink"]==1) echo ' checked="checked"'; echo ' /> Ja <input type="radio" value="0" name="abschnittslink"'; if($_POST["abschnittslink"]=="0") echo ' checked="checked"'; echo ' /> Nein <input type="radio" value="-1" name="abschnittslink"'; if($_POST["abschnittslink"]!=1 and $_POST["abschnittslink"]!="0") echo ' checked="checked"'; echo ' /> unwichtig'; ?>
			<br />Text (Aufgabentext, Beschreibung): <input type="text" name="suchtext" /> <input type="submit" value="anzeigen" />
			</form>
		</fieldset>
      <?php } ?>

    </div>
    <?php }
	
	
	
	
	
    if ($_GET['tab']=='stundenplanung') {
        if (isset($_GET['fk']))
            $_GET['fk']=$subject_classes->cont[$subject_classes->active]["id"];
		?>
      <div class="tab_2">
        <a href="index.php?tab=stundenplanung&amp;auswahl=lernbereiche&amp;aktion=alle"<?php if ($_GET["auswahl"]=="lernbereiche") echo ' class="selected"'; ?> title="Lehrplan / Lernbereiche / Unterreichtseinheiten / Abschnitte"><img src="<?php echo $pfad; ?>icons/fundus.png" alt="fundus" /> Fundus / Lehrpl&auml;ne</a>
        <!--<a href="index.php?tab=stundenplanung&amp;auswahl=abschnitt">Unterreichtseinheiten / Bl&ouml;cke / Abschnitte</a>-->
        <a href="index.php?tab=stundenplanung&amp;auswahl=fkplan"<?php if ($_GET["auswahl"]=="fkplan") echo ' class="selected"'; ?>><img src="<?php echo $pfad; ?>icons/einzelstunde.png" alt="einzelstunde" /> Einzelstunden</a>
        <!--<a href="index.php?tab=stundenplanung&amp;auswahl=einzelstunden"<?php if ($_GET["auswahl"]=="einzelstunden") echo ' class="selected"'; ?>>Einzelstunden</a>-->
	    <a href="index.php?tab=stundenplanung&amp;auswahl=hausaufgaben"<?php if ($_GET["auswahl"]=="hausaufgaben") echo ' class="selected"'; ?>><img src="<?php echo $pfad; ?>icons/hausaufgaben.png" alt="hausaufgaben" title="Hausaufgaben" /> Hausaufgaben-&Uuml;bersicht</a>  
	    <a href="index.php?tab=stundenplanung&amp;auswahl=planstatistik"<?php if ($_GET["auswahl"]=="planstatistik") echo ' class="selected"'; ?>><img src="<?php echo $pfad; ?>icons/statistik_auswertung.png" alt="statistik" title="Einzelstunden-Statistik" /> Einzelstunden-Statistik</a>  
      </div>
	
	<?php
	
		// -------------------------------- Lernbereiche ------------------------------------------------------
	    if ($_GET["auswahl"]=="lernbereiche") { ?>
      <div class="tab_3">
        <a href="index.php?tab=stundenplanung&amp;auswahl=lernbereiche&amp;aktion=alle"<?php if ($_GET["aktion"]=="alle") echo ' class="selected"'; ?>>alle Lehrpl&auml;ne</a>
      <?php $result=db_conn_and_sql("SELECT DISTINCT `lernbereich`.`klassenstufe`, `schulart`.`kuerzel`,`faecher`.`kuerzel`,`lehrplan`.*
                                 FROM `faecher`,`fach_klasse`,`klasse`,`schulart`,`lehrplan`,`lernbereich`
                                 WHERE `fach_klasse`.`fach`=`faecher`.`id`
                                   AND `fach_klasse`.`anzeigen`=1
                                   AND `fach_klasse`.`user`=".$_SESSION['user_id']."
                                   AND `fach_klasse`.`klasse`=`klasse`.`id`
                                   AND `fach_klasse`.`lehrplan`=`lehrplan`.`id`
                                   AND `lehrplan`.`schulart`=`schulart`.`id`
                                   AND `lehrplan`.`fach`=`faecher`.`id`
                                   AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
                                   AND `lernbereich`.`klassenstufe`=(".$aktuelles_jahr."-`klasse`.`einschuljahr`+1)
                                 ORDER BY `lernbereich`.`klassenstufe`,`faecher`.`id`,`lehrplan`.`id`,`lernbereich`.`nummer`");
		$schulart_anzeigen=0;
		for($i=0;$i<sql_num_rows ( $result );$i++) if (sql_result ( $result, $i, 'schulart.kuerzel' )!=sql_result ( $result, 0, 'schulart.kuerzel' )) $schulart_anzeigen=1;
        for($i=0;$i<sql_num_rows ( $result );$i++) { ?>
          <a href="index.php?tab=stundenplanung&amp;auswahl=lernbereiche&amp;lehrplan=<?php echo sql_result ( $result, $i, 'lehrplan.id' ); ?>&amp;klasse=<?php echo sql_result ( $result, $i, 'lernbereich.klassenstufe' ); ?>" title="<?php echo 'Lehrplan: '.$bundesland[sql_result ( $result, $i, 'lehrplan.bundesland' )]['kuerzel'].' '.html_umlaute(sql_result ( $result, $i, 'schulart.kuerzel' )).' '.sql_result ( $result, $i, 'lehrplan.jahr' ); ?>"<?php if ($_GET["klasse"]==sql_result ( $result, $i, 'lernbereich.klassenstufe' ) and $_GET["lehrplan"]==sql_result ( $result, $i, 'lehrplan.id' )) echo ' class="selected"'; ?>>
            <?php if ($schulart_anzeigen) echo html_umlaute(@sql_result ( $result, $i, 'schulart.kuerzel' ))." "; echo html_umlaute(@sql_result ( $result, $i, 'faecher.kuerzel' ))." ".@sql_result ( $result, $i, 'lernbereich.klassenstufe' ); ?></a>
        <?php
        } ?>
      </div>
    <div class="navigation_3"><?php echo $navigation; ?></div>
	<div class="inhalt">
        <?php
         if ($_GET["aktion"]=="alle") { // Alle Lehrplaene anzeigen ?>
			<div class="tooltip" id="tt_lehrplan">
				<p>Klicken Sie auf eine Klassenstufe des entsprechenden Fachs um diese mit Lernbereichen auszustatten.</p>
				<p>Die Farbe der Klassenstufe verr&auml;t: in der jeweiligen Stufe befinden sich bereits Lernbereiche (normaler Link), oder noch keine (grau).</p>
				<p>Wenn Sie bereits eine Fach-Klasse-Kombination eingetragen haben (und diese auch auf "anzeigen" gesetzt ist), wird die entsprechende Klassenstufe automatisch als Tab angezeigt und Sie k&ouml;nnen auch dar&uuml;ber zu den Lernbereichen gelangen.</p>
				<p>Die Aktivit&auml;t der Lehrpl&auml;ne entscheidet &uuml;ber die Anzeige in verschiedenen Men&uuml;s.</p></div>
		<fieldset><legend>Lehrpl&auml;ne <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_lehrplan')" onmouseout="hideWMTT()" /></legend>
		<form action="<?php echo $pfad; ?>formular/lp_aktiv.php" method="post" accept-charset="ISO-8859-1">
      <ol class="divider">
<?php
        $result = db_conn_and_sql ( "SELECT *
                                 FROM `schulart`,`faecher`,`lp_user`,`lehrplan` LEFT JOIN `lernbereich` ON `lernbereich`.`lehrplan`=`lehrplan`.`id`
                                 WHERE `lehrplan`.`schulart`=`schulart`.`id`
                                    AND `lehrplan`.`fach`=`faecher`.`id`
									AND (`faecher`.`user`=0 OR `faecher`.`user`=".$_SESSION['user_id'].")
                                    AND `lp_user`.`lehrplan`=`lehrplan`.`id`
                                    AND `lp_user`.`user`=".$_SESSION['user_id']."
                                GROUP BY `lernbereich`.`klassenstufe`, `lehrplan`.`zusatz`, `lehrplan`.`schulart`, `lehrplan`.`fach`, `lehrplan`.`bundesland`, `lehrplan`.`jahr`
                                ORDER BY `lp_user`.`aktiv` DESC, `schulart`.`kuerzel`,`faecher`.`kuerzel`,`lehrplan`.`id`, `lernbereich`.`klassenstufe`, `lernbereich`.`nummer`");
		$hilf=0;
		$inaktive_lehrplaene=false;
        for($i=0;$i<sql_num_rows ( $result ); $i++) {
			// inaktive ausblenden
			if (!sql_result($result, $i, 'lp_user.aktiv') && !$inaktive_lehrplaene) {
				$inaktive_lehrplaene=true;
				echo '<fieldset><legend>inaktive Lehrpl&auml;ne <img id="img_lehrplan_inactive" src="'.$pfad.'icons/clip_closed.png" alt="clip" onclick="javascript:clip(\'lehrplan_inactive\', \''.$pfad.'\')" /></legend><span id="span_lehrplan_inactive" style="display: none;"><ol>';
			}
			
            if ($i==0 or sql_result ($result, $i, 'lehrplan.jahr')!=sql_result ($result, $i-1, 'lehrplan.jahr')
                        or sql_result ($result, $i, 'lehrplan.bundesland')!=sql_result ($result, $i-1, 'lehrplan.bundesland')
                        or sql_result ($result, $i, 'lehrplan.schulart')!=sql_result ($result, $i-1, 'lehrplan.schulart')
                        or sql_result ($result, $i, 'lehrplan.fach')!=sql_result ($result, $i-1, 'lehrplan.fach'))
                    echo '<li>';
            echo '<label for="aktiv_'.$hilf.'">'.$bundesland[sql_result ( $result, $i, 'lehrplan.bundesland' )]['kuerzel'].' '.html_umlaute(sql_result ( $result, $i, 'schulart.kuerzel' )).' '.sql_result ( $result, $i, 'lehrplan.jahr' )." ".html_umlaute(@sql_result ( $result, $i, 'faecher.kuerzel' )).':</label>
			<input type="hidden" name="lp_id_'.$hilf.'" value="'.sql_result ( $result, $i, 'lehrplan.id' ).'" />
			<a href="'.$pfad.'formular/lehrplan_bearbeiten.php?id='.sql_result ( $result, $i, 'lehrplan.id' ).'" class="icon" onclick="fenster(this.href, \'\'); return false;"><img src="'.$pfad.'icons/edit.png" alt="edit" /></a>';
			$hilf++;
			for ($k=1;$k<14;$k++)
                if (sql_result ( $result, $i, 'lernbereich.klassenstufe' )==$k
                        or ($k>=sql_result ( $result, $i, 'lehrplan.von' ) and $k<=sql_result ( $result, $i, 'lehrplan.bis' ))) {
                    // blaue oder graue Link-Hinterlegung bzw. LK/GK - Hervorhebung
                    $zusatz='';
                    $class_lb='';
                    if (sql_result ( $result, $i, 'lernbereich.klassenstufe' )!=$k)
                        $class_lb=' class="keine_lernbereiche"';
                    if (sql_result ($result, $i, 'lehrplan.zusatz')!='')
                        $zusatz='<sub>'.html_umlaute(sql_result ($result, $i, 'lehrplan.zusatz')).'</sub>';
                    echo ' <a href="index.php?tab=stundenplanung&amp;auswahl=lernbereiche&amp;lehrplan='.sql_result ( $result, $i, 'lehrplan.id' ).'&amp;klasse='.$k.'"'.$class_lb.'>'.$k.$zusatz.'</a>';
                    
                    // wenn die naechste Klassenstufe existiert
                    if (sql_result ($result, $i, 'lehrplan.id')==@sql_result ($result, $i+1, 'lehrplan.id')
                            and sql_result ( $result, $i, 'lernbereich.klassenstufe' )==$k)
                        $i++;
                }
                if (sql_result ($result, $i, 'lehrplan.jahr')!=@sql_result ($result, $i+1, 'lehrplan.jahr')
                        or sql_result ($result, $i, 'lehrplan.bundesland')!=@sql_result ($result, $i+1, 'lehrplan.bundesland')
                        or sql_result ($result, $i, 'lehrplan.schulart')!=@sql_result ($result, $i+1, 'lehrplan.schulart')
                        or sql_result ($result, $i, 'lehrplan.fach')!=@sql_result ($result, $i+1, 'lehrplan.fach'))
                    echo '</li>';
                else
                    echo '<br />';
			}
			if ($inaktive_lehrplaene)
				echo '</ol></span></fieldset>'; ?>
      </ol>
		<!--<input type="button" class="button" value="Aktivit&auml;t der Lehrpl&auml;ne speichern" onclick="speichern=0; zaehler=0; while (document.getElementById('aktiv_'+zaehler)) { if(document.getElementById('aktiv_'+zaehler).checked) speichern=1; zaehler++; }
		if (speichern) document.getElementsByTagName('form')[0].submit(); else alert('Mindestens ein Lehrplan muss aktiv sein.');" />-->
		</form>
		</fieldset>
		<?php
         /*$lehrplan=db_conn_and_sql("SELECT DISTINCT `lehrplan`.*, `schulart`.*, `faecher`.*, `lernbereich`.`zusatz`
                                 FROM `lehrplan`,`schulart`,`faecher`
                                 WHERE `lehrplan`.`schulart`=`schulart`.`id`
                                   AND `lehrplan`.`fach`=`faecher`.`id`
                                 ORDER BY `schulart`.`kuerzel`,`faecher`.`kuerzel`,`lehrplan`.`id`, `lernbereich`.`zusatz`");*/
         $fach = db_conn_and_sql ( 'SELECT * FROM `faecher` WHERE `user`=0 OR `user`='.$_SESSION['user_id'].' ORDER BY `name`' );
         $schulart = db_conn_and_sql ("SELECT * FROM `schulart`"); ?>
        <div class="tooltip" id="tt_abzweig">
            <p>Gibt es im Lehrplan eine Verzweigung (z.B. Deutsch Leistungskurs Klasse 11 und Deutsch Grundkurs Klasse 11), bei der verschiedene Lernbereichsrichtungen aufkommen, erstellen Sie einen weiteren Lehrplan (z.B. von Klassenstufe 11 bis 12) mit der Abzweigung "LK".</p>
            <p><div class="hinweis">Nutzen Sie am besten K&uuml;rzel aus maximal zwei Buchstaben.</div></p></div>
         <form action="<?php echo $pfad; ?>formular/lehrplan_neu.php" method="post" accept-charset="ISO-8859-1">
         <fieldset><legend>Lehrplan hinzuf&uuml;gen <img id="img_lehrplan" src="<?php echo $pfad; ?>icons/clip_closed.png" alt="clip" onclick="javascript:clip('lehrplan', '<?php echo $pfad; ?>')" /></legend>
		<span id="span_lehrplan" style="display: none;">
		<ol class="divider"> 
         <li><label for="jahr">LP-Jahr<em>*</em>:</label> <input type="text" name="jahr" size="3" maxlength="4" /><br />
         <label for="bundesland">Bundesland<em>*</em>:</label> <select name="bundesland"><?php for ($i=0;$i<count($bundesland); $i++) echo '<option value="'.$i.'">'.$bundesland[$i]['name'].' ('.$bundesland[$i]['kuerzel'].')</option>'; ?></select></li>
         <li><label for="schulart">Schulart<em>*</em>:</label> <select name="schulart"><?php for($i=0;$i<sql_num_rows ( $schulart );$i++) { ?>
                 <option value="<?php echo @sql_result ( $schulart, $i, 'schulart.id' ); ?>"><?php echo html_umlaute(@sql_result ( $schulart, $i, 'schulart.kuerzel' )); ?></option>
         <?php } ?></select><br />
         <label for="fach">Fach<em>*</em>:</label> <select name="fach"><?php for($i=0;$i<sql_num_rows ( $fach );$i++) { ?>
                 <option value="<?php echo @sql_result ( $fach, $i, 'faecher.id' ); ?>"><?php echo html_umlaute(@sql_result ( $fach, $i, 'faecher.name' )); ?></option>
         <?php } ?>
         </select><br />
            <label for="von">Klassenstufen<em>*</em>:</label> <input type="text" name="von" size="2" maxlength="2" value="1" /> - <input type="text" name="bis" size="2" maxlength="2" value="13" /><br />
            <label for="zusatz">Abzweigung: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_abzweig')" onmouseout="hideWMTT()" /></label> <input type="text" name="zusatz" size="2" maxlength="10" />
         </li></ol>
         <button style="float: right;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
         (sp&auml;ter: LP importieren)
		</span>
         </fieldset>
         </form>
			
			
         <?php }
		 
		if (isset($_GET["lehrplan"])) {
            if (!isset($_GET["eintragen"])) {
            /*$result=db_conn_and_sql("SELECT DISTINCT `lernbereich`.*, `schulart`.`kuerzel`,`faecher`.`kuerzel`,`lehrplan`.*
                                 FROM `faecher`,`fach_klasse`,`klasse`,`schule`,`schulart`,`lehrplan`,`lernbereich`
                                 WHERE `fach_klasse`.`fach`=`faecher`.`id`
                                   AND `fach_klasse`.`anzeigen`=1
                                   AND `fach_klasse`.`klasse`=`klasse`.`id`
                                   AND `klasse`.`schule`=`schule`.`id`
                                   AND `schule`.`schulart` = `schulart`.`id`
                                   AND `lehrplan`.`schulart`=`schule`.`schulart`
                                   AND `lehrplan`.`fach`=`faecher`.`id`
                                   AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
                                   AND `lernbereich`.`klassenstufe`=(".$aktuelles_jahr."-`klasse`.`einschuljahr`+1)
                                 ORDER BY `schulart`.`id`,`faecher`.`id`,`lehrplan`.`id`,`lernbereich`.`nummer`");*/ ?>
	<div class="tooltip" id="tt_block_eintragung">
		F&uuml;r diese Eintragungen sollten Sie sich etwas Zeit nehmen. Hier k&ouml;nnen Sie nicht nur die im Lehrplan vorhandenen Lernbereiche &uuml;bernehmen, sondern viel mehr ins Detail gehen:
		Unterteilen Sie die Lernbereiche in mehrere Unterrichtseinheiten und (bei Bedarf) Unterrichtseinheiten in mehrere Bl&ouml;cke.
		&Uuml;ber das Symbol <img src="<?php echo $pfad; ?>icons/neu_block.png" alt="block_neu" /> werden ausgehend von einem Lernbereich Unterrichtseinheiten erstellt (analog gilt dies auch f&uuml;r Unterrichtseinheiten und deren Bl&ouml;cke).</div>
	<div class="tooltip" id="tt_abschnitt_eintragung">
		<p>In Unterrichtseinheiten oder in Bl&ouml;cken k&ouml;nnen Abschnitte &uuml;ber das Symbol <img src="<?php echo $pfad; ?>icons/abschnitte.png" alt="abschnitte" /> erstellt werden.
		Die erstellten Abschnitte bilden den <b>Fundus</b>. Auf diesen k&ouml;nnen Sie zur&uuml;ckgreifen, wenn Sie die Klassenstufe ein weiteres Mal bekommen.</p>
		<p>Es ist g&uuml;nstig, wenn die Abschnitte in einer geordneten Reihenfolge auftauchen, damit Sie sp&auml;ter nicht zu viel &uuml;berlegen m&uuml;ssen - dies ist im hier verlinkten Abschnittsmen&uuml; (<img src="<?php echo $pfad; ?>icons/abschnitte.png" alt="abschnitte" />) zu erledigen.</p>
		<p>Die Erstellung der Abschnitte ist allerdings auch aus der Einzelstundenplanung heraus m&ouml;glich (weniger umst&auml;ndlich).</p></div>
	<div class="tooltip" id="tt_block_loeschen">
		Wollen Sie einen Lernbereich, eine Unterrichtseinheit oder einen Block l&ouml;schen, d&uuml;rfen sich darin keine Unterelemente befinden. Verschieben Sie also jegliche Abschnitte in einen anderen Block und klicken Sie dann auf das Symbol <img src="<?php echo $pfad; ?>icons/delete.png" alt="delete" />. Soll ein Lernbereich gel&ouml;scht werden, m&uuml;ssen Sie zuvor alle Unterrichtseinheiten und Bl&ouml;cke entfernen.</div>
	<div class="tooltip" id="tt_block_bearbeiten">
		Lernbereiche, Unterrichtseinheiten und Bl&ouml;cke k&ouml;nnen mit dem Symbol <img src="<?php echo $pfad; ?>icons/edit.png" alt="bearbeiten" /> bearbeitet und mit <img src="<?php echo $pfad; ?>icons/hoch.png" alt="hoch" /> bzw. <img src="<?php echo $pfad; ?>icons/runter.png" alt="runter" /> in ihrer Reihenfolge ver&auml;ndert werden.</div>
       <span class="hinweis">LB/UE/Block: <img src="<?php echo $pfad; ?>icons/neu_block.png" alt="block_neu" /> <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_block_eintragung')" onmouseout="hideWMTT()" /> |
		Abschnitte: <img src="<?php echo $pfad; ?>icons/abschnitte.png" alt="abschnitte" /> <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_abschnitt_eintragung')" onmouseout="hideWMTT()" /> |
		L&ouml;schen: <img src="<?php echo $pfad; ?>icons/delete.png" alt="delete" /> <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_block_loeschen')" onmouseout="hideWMTT()" /> |
		Bearbeiten: <img src="<?php echo $pfad; ?>icons/edit.png" alt="edit" /> <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_block_bearbeiten')" onmouseout="hideWMTT()" />
	   </span>
	  <ul>
<?php
        $result = db_conn_and_sql ( "SELECT * FROM `lernbereich`,`lp_user`
			WHERE `lernbereich`.`lehrplan`=".injaway($_GET["lehrplan"])."
				AND `lernbereich`.`klassenstufe`=".injaway($_GET["klasse"]).$zusatz."
				AND `lernbereich`.`lehrplan`=`lp_user`.`lehrplan`
				AND `lp_user`.`user`=".$_SESSION['user_id']."
			ORDER BY `lernbereich`.`nummer`");
		$wahl_zahler=1;
		$formular_zaehler=0;
		$es_gibt_nicht_eine_UE=1;
        for($i=0;$i<sql_num_rows ( $result );$i++) { ?>
        <li onmouseover="this.className='over'; document.getElementById('aktionen_lb_<?php echo $i; ?>').style.visibility='visible';" onmouseout="this.className='lehrplan'; document.getElementById('aktionen_lb_<?php echo $i; ?>').style.visibility='hidden';" class="lehrplan" style="width: 650px; clear: both;" title="<?php echo @html_umlaute(sql_result ( $result, $i, 'lernbereich.beschreibung' )); ?>">
				<?php echo "(LB ";
                if (sql_result ( $result, $i, 'lernbereich.wahl' )) {
                    echo "W";
                    echo $wahl_zahler;
                    $wahl_zahler++;
                }
                else
                    echo @sql_result ( $result, $i, 'lernbereich.nummer' );
                echo ') ';
				echo @html_umlaute(sql_result ( $result, $i, 'lernbereich.name' ));
				$result_ue = db_conn_and_sql ( "SELECT `block`.*, SUM(`abschnitt`.`minuten`) AS `gesamtzeit` FROM `block` LEFT JOIN `block_abschnitt` ON `block`.`id`=`block_abschnitt`.`block` LEFT JOIN `abschnitt` ON `abschnitt`.`id`=`block_abschnitt`.`abschnitt` WHERE `block`.`block_hoeher` IS NULL AND `block`.`lernbereich`=".sql_result ( $result, $i, 'lernbereich.id' )." GROUP BY `block`.`id` ORDER BY `block`.`position`"); ?>
			<span id="aktionen_lb_<?php echo $i; ?>" class="aktionen_block">
			<?php
				if ($i>0) { ?><a href="<?php echo $pfad; ?>formular/block_aktion.php?aktion=lb_tausch&amp;id=<?php echo sql_result ( $result, $i, 'lernbereich.id' ); ?>&amp;ziel=<?php echo sql_result ( $result, $i-1, 'lernbereich.id' ); ?>&amp;lehrplan=<?php echo $_GET["lehrplan"]; ?>&amp;klasse=<?php echo $_GET["klasse"]; ?>" class="icon" title="Lernbereich mit oberem tauschen"><img src="<?php echo $pfad; ?>icons/hoch.png" alt="hoch" /></a><?php }
				if (($i+1)<sql_num_rows ( $result )) { ?><a href="<?php echo $pfad; ?>formular/block_aktion.php?aktion=lb_tausch&amp;id=<?php echo sql_result ( $result, $i, 'lernbereich.id' ); ?>&amp;ziel=<?php echo sql_result ( $result, $i+1, 'lernbereich.id' ); ?>&amp;lehrplan=<?php echo $_GET["lehrplan"]; ?>&amp;klasse=<?php echo $_GET["klasse"]; ?>" class="icon" title="Lernbereich mit unterem tauschen"><img src="<?php echo $pfad; ?>icons/runter.png" alt="runter" /></a><?php }
				?>
				<a href="<?php echo $pfad ?>formular/lernbereich_bearbeiten.php?lehrplan=<?php echo $_GET['lehrplan']; ?>&amp;klasse=<?php echo $_GET['klasse']; ?>&amp;lb=<?php echo @sql_result ( $result, $i, 'lernbereich.id' ); ?>" onclick="fenster(this.href,'Lernbereich bearbeiten'); return false;" class="icon" title="bearbeiten"><img src="<?php echo $pfad; ?>icons/edit.png" alt="bearbeiten" /></a>
				<a href="<?php echo $pfad; ?>formular/block_popup.php?lehrplan=<?php echo $_GET['lehrplan']; ?>&amp;klasse=<?php echo $_GET['klasse']; ?>&amp;lb=<?php echo @sql_result ( $result, $i, 'lernbereich.id' ); ?>&amp;neue_pos=<?php echo sql_num_rows ( $result_ue )+1; ?>&amp;eintragen=ue" onclick="document.getElementById('ue_<?php echo $i; ?>').style.display='block'; return false;" class="icon" title="neue Unterrichtseinheit erstellen"><img src="<?php echo $pfad; ?>icons/neu_block.png" alt="neu" /></a>
				<?php if (sql_num_rows ( $result_ue )==0) { ?><a href="<?php echo $pfad; ?>formular/lb_block_loeschen.php?was=lernbereich&amp;id=<?php echo sql_result ( $result, $i, 'lernbereich.id' ); ?>&amp;klasse=<?php echo $_GET["klasse"]; ?>&amp;lehrplan=<?php echo $_GET["lehrplan"]; ?>" class="icon" title="l&ouml;schen" onclick="if (confirm('Der Lernbereich wird gel&ouml;scht. Wollen Sie das wirklich?')==false) return false;"><img src="<?php echo $pfad; ?>icons/delete.png" alt="l&ouml;schen" /></a> <?php } ?>
			</span>
			<span style="color: brown; float: right;">[<?php echo html_umlaute(@sql_result ( $result, $i, 'lernbereich.ustd' )); ?>]</span>
		</li>
        <ul>
<?php
		   $j=0;
           if (sql_num_rows ( $result_ue )>0) {
                $es_gibt_nicht_eine_UE=0;
                for($j=0;$j<sql_num_rows ( $result_ue );$j++) { ?>
           <li onmouseover="this.className='over'; document.getElementById('aktionen_ue_<?php echo $i.'_'.$j; ?>').style.visibility='visible';" onmouseout="this.className='unterrichtseinheit'; document.getElementById('aktionen_ue_<?php echo $i.'_'.$j; ?>').style.visibility='hidden';" class="unterrichtseinheit" style="width: 700px; clear: both;" title="<?php echo 'Thema: '.html_umlaute(@sql_result (db_conn_and_sql("SELECT GROUP_CONCAT(thema.bezeichnung SEPARATOR ', ') AS themen FROM thema, themenzuordnung WHERE thema.id=themenzuordnung.thema AND themenzuordnung.typ=2 AND themenzuordnung.id=".@sql_result ( $result_ue, $j, 'block.id' )." GROUP BY themenzuordnung.id") , 0, 'themen' )).' - Beschr: '.html_umlaute(@sql_result($result_ue, $j, 'block.kommentare')).' - Fach-Verbindungen: '.html_umlaute(@sql_result($result_ue, $j, 'block.verknuepfung_fach')).' - meth.-did.: '.html_umlaute(@sql_result ( $result_ue, $j, 'block.methodisch' )); ?>">
		   <?php echo "(UE ".@sql_result ( $result_ue, $j, 'block.position' ).") ";
            $result_block = db_conn_and_sql ( "SELECT `block`.*, SUM(`abschnitt`.`minuten`) AS `gesamtzeit` FROM `block` LEFT JOIN `block_abschnitt` ON `block`.`id`=`block_abschnitt`.`block` LEFT JOIN `abschnitt` ON `abschnitt`.`id`=`block_abschnitt`.`abschnitt` WHERE `block`.`block_hoeher`=".sql_result ( $result_ue, $j, 'block.id' )." GROUP BY `block`.`id` ORDER BY `block`.`position`"); ?>
				<a href="index.php?tab=stundenplanung&amp;auswahl=lernbereiche&amp;lehrplan=<?php echo $_GET['lehrplan']; ?>&amp;klasse=<?php echo $_GET['klasse']; ?>&amp;block=<?php echo @sql_result ( $result_ue, $j, 'block.id' ); ?>&amp;eintragen=abschnitte" class="icon" title="Abschnittsplanung"><img src="./icons/abschnitte.png" alt="abschnitte" />
				<?php echo html_umlaute(@sql_result ( $result_ue, $j, 'block.name' )); ?></a>
			<span id="aktionen_ue_<?php echo $i.'_'.$j; ?>" class="aktionen_block">
				<?php
				if ($j>0) { ?><a href="<?php echo $pfad; ?>formular/block_aktion.php?aktion=block_tausch&amp;id=<?php echo sql_result ( $result_ue, $j, 'block.id' ); ?>&amp;ziel=<?php echo sql_result ( $result_ue, $j-1, 'block.id' ); ?>&amp;lehrplan=<?php echo $_GET["lehrplan"]; ?>&amp;klasse=<?php echo $_GET["klasse"]; ?>" class="icon" title="Unterrichtseinheit mit oberer tauschen"><img src="<?php echo $pfad; ?>icons/hoch.png" alt="hoch" /></a><?php }
				if (($j+1)<sql_num_rows ( $result_ue )) { ?><a href="<?php echo $pfad; ?>formular/block_aktion.php?aktion=block_tausch&amp;id=<?php echo sql_result ( $result_ue, $j, 'block.id' ); ?>&amp;ziel=<?php echo sql_result ( $result_ue, $j+1, 'block.id' ); ?>&amp;lehrplan=<?php echo $_GET["lehrplan"]; ?>&amp;klasse=<?php echo $_GET["klasse"]; ?>" class="icon" title="Unterrichtseinheit mit unterer tauschen"><img src="<?php echo $pfad; ?>icons/runter.png" alt="runter" /></a><?php } ?>
				<a href="<?php echo $pfad ?>formular/block_bearb.php?block=<?php echo sql_result ( $result_ue, $j, 'block.id' ); ?>" onclick="fenster(this.href,'Block bearbeiten'); return false;" class="icon" title="bearbeiten"><img src="<?php echo $pfad; ?>icons/edit.png" alt="bearbeiten" /></a>
				<a href="<?php echo $pfad; ?>formular/block_popup.php?lehrplan=<?php echo $_GET['lehrplan']; ?>&amp;klasse=<?php echo $_GET['klasse']; ?>&amp;block_1=<?php echo @sql_result ( $result_ue, $j, 'block.id' ); ?>&amp;neue_pos=<?php echo sql_num_rows ( $result_block )+1; ?>&amp;lb=<?php echo @sql_result ( $result, $i, 'lernbereich.id' ); ?>&amp;eintragen=block" onclick="document.getElementById('block_<?php echo $i.'_'.$j; ?>').style.display='block'; return false;" class="icon" title="neuen Block erstellen"><img src="<?php echo $pfad; ?>icons/neu_block.png" alt="neu" /></a>
				<?php if (sql_num_rows ( $result_block )==0 and @sql_result ( $result_ue, $j, 'gesamtzeit' )==0) { ?><a href="<?php echo $pfad; ?>formular/lb_block_loeschen.php?was=block&amp;id=<?php echo sql_result ( $result_ue, $j, 'block.id' ); ?>&amp;klasse=<?php echo $_GET["klasse"]; ?>&amp;lehrplan=<?php echo $_GET["lehrplan"]; ?>" class="icon" title="l&ouml;schen" onclick="if (confirm('Die Unterrichtseinheit wird gel&ouml;scht. Wollen Sie das wirklich?')==false) return false;"><img src="<?php echo $pfad; ?>icons/delete.png" alt="l&ouml;schen" /></a> <?php } ?>
			</span>
			<span style="color: brown; font-size: 8pt; float: right;" title="Die UE enth&auml;lt [gr&uuml;n] Unterrichtsstunden. [braun] Unterrichtsstunden (+ Puffer) waren vorgesehen.">[<span style="color: green;"><?php echo round(@sql_result ( $result_ue, $j, 'gesamtzeit' )/45,1),'</span> / '.@sql_result ( $result_ue, $j, 'block.stunden' ); if (@sql_result ( $result_ue, $j, 'block.puffer' )>0) echo '+'.@sql_result ( $result_ue, $j, 'block.puffer' ); ?> Ustd]</span>
			</li>
           <ul>
<?php
			  $k=0;
              if (sql_num_rows ( $result_block )>0) {
                for($k=0;$k<sql_num_rows ( $result_block );$k++) { ?>
              <li onmouseover="this.className='over'; document.getElementById('aktionen_block_<?php echo $i.'_'.$j.'_'.$k; ?>').style.visibility='visible';" onmouseout="this.className='block'; document.getElementById('aktionen_block_<?php echo $i.'_'.$j.'_'.$k; ?>').style.visibility='hidden';" class="block" style="width: 700px; clear: both;" title="<?php echo 'Thema: '.html_umlaute(@sql_result (db_conn_and_sql("SELECT GROUP_CONCAT(thema.bezeichnung SEPARATOR ', ') AS themen FROM thema, themenzuordnung WHERE thema.id=themenzuordnung.thema AND themenzuordnung.typ=2 AND themenzuordnung.id=".@sql_result ( $result_block, $k, 'block.id' )." GROUP BY themenzuordnung.id") , 0, 'themen' )).' - Beschr: '.html_umlaute(@sql_result($result_block, $k, 'block.kommentare')).' - Fach-Verbindungen: '.html_umlaute(@sql_result($result_block, $k, 'block.verknuepfung_fach')).' - meth.-did.: '.html_umlaute(@sql_result ( $result_block, $k, 'block.methodisch' )); ?>">
			  <?php echo "(Block ".($k+1).") "; ?>
				<a href="index.php?tab=stundenplanung&amp;auswahl=lernbereiche&amp;lehrplan=<?php echo $_GET['lehrplan']; ?>&amp;klasse=<?php echo $_GET['klasse']; ?>&amp;block=<?php echo @sql_result ( $result_block, $k, 'block.id' ); ?>&amp;eintragen=abschnitte" class="icon" title="Abschnittsplanung"><img src="<?php echo $pfad; ?>icons/abschnitte.png" alt="neu" />
			 <?php echo @html_umlaute(sql_result ( $result_block, $k, 'block.name' )); ?></a>
				<span id="aktionen_block_<?php echo $i.'_'.$j.'_'.$k; ?>" class="aktionen_block">
					<?php
					if ($k>0) { ?><a href="<?php echo $pfad; ?>formular/block_aktion.php?aktion=block_tausch&amp;id=<?php echo sql_result ( $result_block, $k, 'block.id' ); ?>&amp;ziel=<?php echo sql_result ( $result_block, $k-1, 'block.id' ); ?>&amp;lehrplan=<?php echo $_GET["lehrplan"]; ?>&amp;klasse=<?php echo $_GET["klasse"]; ?>" class="icon" title="Block mit oberem tauschen"><img src="<?php echo $pfad; ?>icons/hoch.png" alt="hoch" /></a><?php }
					if (($k+1)<sql_num_rows ( $result_block )) { ?><a href="<?php echo $pfad; ?>formular/block_aktion.php?aktion=block_tausch&amp;id=<?php echo sql_result ( $result_block, $k, 'block.id' ); ?>&amp;ziel=<?php echo sql_result ( $result_block, $k+1, 'block.id' ); ?>&amp;lehrplan=<?php echo $_GET["lehrplan"]; ?>&amp;klasse=<?php echo $_GET["klasse"]; ?>" class="icon" title="Block mit unterem tauschen"><img src="<?php echo $pfad; ?>icons/runter.png" alt="runter" /></a><?php } ?>
					<a href="<?php echo $pfad ?>formular/block_bearb.php?block=<?php echo sql_result ( $result_block, $k, 'block.id' ); ?>" onclick="fenster(this.href,'Block bearbeiten'); return false;" class="icon" title="bearbeiten"><img src="<?php echo $pfad; ?>icons/edit.png" alt="bearbeiten" /></a>
					<?php if (@sql_result ( $result_block, $k, 'gesamtzeit' )==0) { ?><a href="<?php echo $pfad; ?>formular/lb_block_loeschen.php?was=block&amp;id=<?php echo sql_result ( $result_block, $k, 'block.id' ); ?>&amp;klasse=<?php echo $_GET["klasse"]; ?>&amp;lehrplan=<?php echo $_GET["lehrplan"]; ?>" class="icon" title="l&ouml;schen" onclick="if (confirm('Der Block wird gel&ouml;scht. Wollen Sie das wirklich?')==false) return false;"><img src="<?php echo $pfad; ?>icons/delete.png" alt="l&ouml;schen" /></a><?php } ?>
				</span>
				<span style="color: brown; font-size: 8pt; float: right;" title="Der Block enth&auml;lt [gr&uuml;n] Unterrichtsstunden und [braun] Unterrichtsstunden (+ Puffer) sind vorgesehen">[<span style="color: green;"><?php echo round(@sql_result ( $result_block, $k, 'gesamtzeit' )/45,1),'</span> / '.@sql_result ( $result_block, $k, 'block.stunden' ); if (@sql_result ( $result_block, $k, 'block.puffer' )>0) echo '+'.@sql_result ( $result_block, $k, 'block.puffer' ); ?> Ustd]</span>
				</li>
		<?php }
			} ?>
			<li class="block" id="block_<?php echo $i.'_'.$j; ?>" style="display: none;">
				<form action="<?php echo $pfad; ?>formular/block_neu.php" method="post" accept-charset="ISO-8859-1">
					(Block <?php echo $k+1; ?>) <label for="name" style="padding-left: 30px; vertical-align: middle; width: auto;">Name<em>*</em>:</label> <input type="text" name="name" size="20" />
					<label for="stunden" style="padding-left: 20px; vertical-align: middle; width: auto;">Stunden<em>*</em>:</label> <input type="text" name="stunden" size="1" />
					<input type="hidden" name="block_1" value="<?php echo sql_result($result_ue,$j,"block.id"); ?>" />
					<input type="hidden" name="lehrplan" value="<?php echo $_GET["lehrplan"]; ?>" />
					<input type="hidden" name="klasse" value="<?php echo $_GET["klasse"]; ?>" />
					<input type="hidden" name="lernbereich" value="<?php echo sql_result($result,$i,"lernbereich.id"); ?>" />
					<input type="hidden" name="position" value="<?php echo $k+1; $k=0; ?>" />
					<input type="hidden" name="thema_0" value="-" />
					<input type="hidden" name="gleich_weiter" value="1" />
					<input type="button" class="button" value="eintragen" onclick="auswertung=new Array(new Array(<?php echo $formular_zaehler; ?>, 'name','nicht_leer'), new Array(<?php echo $formular_zaehler; $formular_zaehler++; ?>, 'stunden','natuerliche_zahl')); pruefe_formular(auswertung);" />
				</form></li>
           </ul>
<?php } ?>
<?php } ?>
			<li class="unterrichtseinheit" id="ue_<?php echo $i; ?>" style="display: none;">
				<form action="<?php echo $pfad; ?>formular/block_neu.php" method="post" accept-charset="ISO-8859-1">
					(UE <?php echo $j+1; ?>) <label for="name" style="padding-left: 30px; vertical-align: middle; width: auto;">Name<em>*</em>:</label> <input type="text" name="name" size="20" />
					<label for="stunden" style="padding-left: 20px; vertical-align: middle; width: auto;">Stunden<em>*</em>:</label> <input type="text" name="stunden" size="1" />
					<input type="hidden" name="lehrplan" value="<?php echo $_GET["lehrplan"]; ?>" />
					<input type="hidden" name="klasse" value="<?php echo $_GET["klasse"]; ?>" />
					<input type="hidden" name="lernbereich" value="<?php echo sql_result($result,$i,"lernbereich.id"); ?>" />
					<input type="hidden" name="position" value="<?php echo $j+1; $j=0; ?>" />
					<input type="hidden" name="thema_0" value="-" />
					<input type="hidden" name="gleich_weiter" value="1" />
					<input type="button" class="button" value="eintragen" onclick="auswertung=new Array(new Array(<?php echo $formular_zaehler; ?>, 'name','nicht_leer'), new Array(<?php echo $formular_zaehler; $formular_zaehler++; ?>, 'stunden','natuerliche_zahl')); pruefe_formular(auswertung);" />
				</form></li>
        </ul>
		<?php } ?>
      </ul>
		<?php if (sql_num_rows ( $result )<1 or $es_gibt_nicht_eine_UE)
			echo '<div class="hinweis">Wenn Sie die Unterrichts-Grobplanung der Klassenstufe '.$_GET["klasse"].' im Fach '.html_umlaute(sql_result(db_conn_and_sql("SELECT * FROM faecher, lehrplan WHERE lehrplan.fach=faecher.id AND lehrplan.id=".$_GET["lehrplan"]),0,"faecher.name")).' nutzen wollen, ben&ouml;tigen Sie mindestens einen Lernbereich und darin mindestens eine Unterrichtseinheit.</div>';
		?>
	
		<form action="<?php echo $pfad; ?>formular/lernbereich_neu.php" method="post" accept-charset="ISO-8859-1">
			<fieldset><legend>Lernbereich hinzuf&uuml;gen</legend>
			<input type="hidden" name="lehrplan" value="<?php echo $_GET["lehrplan"]; ?>" />
			<input type="hidden" name="klassenstufe" value="<?php echo $_GET["klasse"]; ?>" />
			<input type="hidden" name="nummer" value="<?php echo sql_num_rows ( $result )+1; ?>" />
			<label for="wahl">Wahlpflicht:</label> <input type="checkbox" name="wahl" value="1" /><br />
            <label for="name">Name<em>*</em>:</label> <input type="text" name="name" size="40" maxlength="50" /><br />
            <label for="ustd">Ustd<em>*</em>:</label> <input type="text" name="ustd" size="2" maxlength="2" /><br />
			<label for="beschreibung">Beschreibung:</label> <textarea cols="50" rows="4" name="beschreibung"></textarea><br />
			<input type="button" class="button" value="speichern" onclick="auswertung=new Array(new Array(<?php echo $formular_zaehler; ?>, 'name','nicht_leer'), new Array(<?php echo $formular_zaehler; ?>, 'ustd','natuerliche_zahl')); pruefe_formular(auswertung);" />
			</fieldset>
		</form>

      <?php 
      } else {
	if ($_GET["eintragen"]=="abschnitte" and proofuser("block",$_GET["block"])) { ?>
		<fieldset>
			<legend>Block-Informationen</legend>
			<form action="<?php echo $pfad; ?>formular/block_kommentare.php?block=<?php echo $_GET["block"]; ?>&amp;lehrplan=<?php echo $_GET["lehrplan"]; ?>&amp;klasse=<?php echo $_GET["klasse"]; ?>" method="post" accept-charset="ISO-8859-1">
				<label for="kommentare" style="width: 200px;">Block-Kommentare:</label><br /><textarea name="kommentare" rows="5" cols="150"><?php $block=db_conn_and_sql("SELECT `kommentare`, `methodisch` FROM `block` WHERE `block`.`id`=".injaway($_GET["block"])." AND `block`.`user`=".$_SESSION['user_id']); echo html_umlaute(sql_result($block,0,"block.kommentare")); ?></textarea><br />
				<label for="methodisch" style="width: 300px;">methodische &Uuml;berlegungen:</label><br /><textarea name="methodisch" rows="5" cols="150"><?php echo html_umlaute(sql_result($block,0,"block.methodisch")); ?></textarea>
				<br />
				<input type="submit" class="button" value="speichern" />
			</form>
        </fieldset>
		<br />
	<!-- gibts nochmal in abschnitt_neu.php -->
	<div class="tooltip" id="tt_position">
		Ver&auml;ndern Sie hier die Reihenfolge der Abschnittscontainer.</div>
	<div class="tooltip" id="tt_zeit">
		<p>Die Zeit eines Abschnitts (in Minuten) sollte f&uuml;r eine durchschnittliche Klasse eingetragen werden, damit Sie einen Richtwert haben.</p>
		<p>Wenn Sie den Abschnitt einer Unterrichtsstunde zuordnen,
		k&ouml;nnen Sie diese (zweite) Zeitangabe anpassen. In der Unterrichts-Nachbereitung werden beide Zeitangaben zur eventuellen Korrektur angeboten.</p></div>
	<div class="tooltip" id="tt_inhalt">
		<p>Der Inhalt eines Abschnittscontainers wird diesem hier zugeordnet. Jegliche Materialien werden automatisch in die Materialdatenbank eingeordnet und bleiben auch dann erhalten, wenn der Abschnittscontainer gel&ouml;scht wird.
		Dies betrifft alle Aufgaben, Tests, Grafiken, Arbeitsbl&auml;tter, Folien, Links und sonstige Materialien.</p>
		<p>Mit dem Symbol <img src="./icons/add.png" alt="add" /> k&ouml;nnen Sie dem Abschnitt mehrere Inhalte zuordnen. So k&ouml;nnen (damit die logische Abgrenzung zum n&auml;chsten Abschnitt gew&auml;hrleistet ist) hier z.B. gleichzeitig eine &Uuml;beschrift, ein Merksatz und zwei Grafiken vorkommen.</p></div>
	<div class="tooltip" id="tt_hefter">
		W&auml;hlen Sie zwischen "nicht aufschreiben", "Merkhefter" und "&Uuml;bungshefter". Je nach Auswahl sieht der Sch&uuml;lerhefter am Ende unterschiedlich aus.
		Wenn Sie nicht verschiedene Hefter nutzen (dies kann in den Einstellungen ver&auml;ndert werden), ist lediglich zwischen Hefter und m&uuml;ndlich zu unterscheiden,
		auch wenn die Auswahlm&ouml;glichkeit bestehen bleibt.</div>
	<div class="tooltip" id="tt_medium">
		Diese Auswahl hat auf den Unterricht keinen weiteren Einfluss. Sie k&ouml;nnen diese Daten lediglich in sp&auml;teren Versionen statistisch auswerten.</div>
	<div class="tooltip" id="tt_aktionen">
		Bearbeiten Sie den Abschnittscontainer, verschieben Sie ihn in einen anderen Block, oder l&ouml;schen Sie denselben.</div>
	<table id="einzelstunde" class="einzelstunde" cellspacing="0" cellpadding="0">
   <tr><th style="width:60px;">Pos.<br /><img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_position')" onmouseout="hideWMTT()" /></th>
		<th>Zeit <br /><img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_zeit')" onmouseout="hideWMTT()" /></th>
		<th>Inhalt <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_inhalt')" onmouseout="hideWMTT()" /></th>
		<th>Hefter <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_hefter')" onmouseout="hideWMTT()" /></th>
		<th>Medium <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_medium')" onmouseout="hideWMTT()" /> /<br />Sozialform /<br />Handlungsmuster</th>
		<th>Ziel / Aktionen <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_aktionen')" onmouseout="hideWMTT()" /></th></tr>
	<?php
	$gesamtzeit=0;
	$abschnitte=db_conn_and_sql("SELECT * FROM `abschnitt`,`block_abschnitt` WHERE `block_abschnitt`.`abschnitt`=`abschnitt`.`id` AND `block_abschnitt`.`block`=".injaway($_GET["block"])." ORDER BY `block_abschnitt`.`position`");
	for ($i=0;$i<sql_num_rows($abschnitte);$i++) {
		$ansicht=einzelstundenansicht(sql_result($abschnitte,$i,'block_abschnitt.abschnitt'),"bearbeiten",$pfad); $gesamtzeit+=$ansicht['minuten']; ?>
		<tr><td align="center"><?php
				if ($i>0) echo '<a href="'.$pfad.'formular/abschnitt_position.php?pos='.$i.'&amp;block='.$_GET["block"].'&amp;lehrplan='.$_GET["lehrplan"].'&amp;klasse='.$_GET["klasse"].'&amp;aktion=hoch" class="icon" title="hochschieben"><img src="./icons/hoch.png" alt="hoch" /></a>&nbsp;';
				echo (sql_result($abschnitte,$i,'block_abschnitt.position')+1);
				if ($i!=sql_num_rows($abschnitte)-1) echo '&nbsp;<a href="'.$pfad.'formular/abschnitt_position.php?pos='.$i.'&amp;block='.$_GET["block"].'&amp;lehrplan='.$_GET["lehrplan"].'&amp;klasse='.$_GET["klasse"].'&amp;aktion=runter" class="icon" title="runterschieben"><img src="./icons/runter.png" alt="runter" /></a>'; ?></td>
	        <td align="center"><?php echo $ansicht['minuten']; ?></td>
			<td><a name="abschnitt_anker_<?php echo $i; ?>"></a><?php echo $ansicht['inhalt']; ?><a href="javascript:fenster('<?php echo $pfad; ?>formular/inhalt_hinzufuegen.php?abschnitt=<?php echo sql_result($abschnitte,$i,'abschnitt.id'); ?>');" class="icon" title="weiteren Inhalt hinzuf&uuml;gen"><img src="<?php echo $pfad; ?>icons/add.png" alt="add" /></a></td>
			<td><?php switch ($ansicht['hefter']) { case 0: echo "-"; break; case 1: echo '<img src="./icons/merkteil.png" alt="Merkteil" title="Merkteil" />'; break; case 2: echo '<img src="./icons/uebungsteil.png" alt="&Uuml;bungsteil" title="&Uuml;bungsteil" />'; break;} ?></td>
			<td><?php echo $ansicht['medium']; ?> /<br /><?php echo $ansicht['sozialform']; if (isset($ansicht['handlungsmuster'])) echo "/ <br />"; 
				if($ansicht['handlungsmuster']!="") echo $ansicht['handlungsmuster']; ?></td>
			<td><?php if($ansicht['ziele']!="") echo '<img src="'.$pfad.'icons/ziele.png" alt="ziel" title="Ziel" />: '.$ansicht['ziele']; if (isset($ansicht['bemerkung'])) echo "<br />";
				if($ansicht['bemerkung']!="") echo '<img src="'.$pfad.'icons/kommentar.png" alt="bemerkung" title="bemerkung" />: '.$ansicht['bemerkung']; ?>
            <br />
            <span class="edit_things">
                <a href="<?php echo $pfad; ?>formular/abschnitt_bearb.php?welcher=<?php echo sql_result($abschnitte,$i,'abschnitt.id'); ?>" onclick="fenster(this.href, 'Abschnitt bearbeiten'); return false;" class="icon" title="bearbeiten"><img src="<?php echo $pfad; ?>icons/edit.png" alt="bearbeiten" /></a>
                <a href="<?php echo $pfad; ?>formular/abschnitt_position.php?abschnitt=<?php echo sql_result($abschnitte,$i,'abschnitt.id'); ?>&amp;block=<?php echo $_GET["block"]; ?>&amp;lehrplan=<?php echo $_GET["lehrplan"]; ?>&amp;klasse=<?php echo $_GET["klasse"]; ?>&amp;aktion=loeschen" class="icon" title="l&ouml;schen" onclick="if (confirm('Der Abschnitt wird mit jeglichen Daten (&Uuml;beschriften, Texte) endg&uuml;ltig gel&ouml;scht. Wollen Sie das wirklich?')==false) return false;"><img src="<?php echo $pfad; ?>icons/delete.png" alt="l&ouml;schen" /></a>
            </span></td>
		</tr>
	<?php } ?>
   </table>
      <p><img src="<?php echo $pfad; ?>/icons/zeit.png" alt="zeit" title="Gesamtzeit" />: <?php echo $gesamtzeit; ?> min = <?php echo floor($gesamtzeit/45)." US + ".($gesamtzeit-45*floor($gesamtzeit/45))." min"; ?></p>
	<?php
	echo '
		<button onclick="window.open(\''.$pfad.'formular/abschnitt_verschieben.php?block='.$_GET["block"].'\', \'Bl&ouml;cke verschieben\', \'width=1200,height=600,left=100,top=200,resizable=yes,scrollbars=yes\');" title="Abschnitte aus diesem in einen anderen Block verschieben, kopieren oder verlinken"><img src="'.$pfad.'icons/doc_copy.png" alt="copy" /> Abschnitte verschieben / kopieren / verlinken</button>
		<button onclick="window.open(\''.$pfad.'formular/abschnitt_neu.php?block='.(0+$_GET["block"]).'\', \'Neuer Abschnitt\', \'width=1100,height=600,left=50,top=50,resizable=yes,scrollbars=yes\');" title="neuen Abschnitt erstellen"><img src="'.$pfad.'icons/abschnitt.png" alt="abschnitt" /> neuer Abschnitt</button>';
		
		$lernbereich=db_conn_and_sql("SELECT * FROM `block`,`lernbereich` WHERE `block`.`lernbereich`=`lernbereich`.`id` AND `block`.`id`=".injaway($_GET["block"]));
      }
      } // ende Eintragungen (ue / block / abschnitte)
      } // ende Lehrplan
      } // ende Lernbereiche
	
	// ------------------------------------------------ Einzelstunden (fk-zeitplanung und Einzelstundenplanung) ---------------------------------------------------------
	  if ($_GET["auswahl"]=="fkplan") {
      $result=db_conn_and_sql("SELECT *
							FROM `fach_klasse`,`klasse`,`faecher`
							WHERE `fach_klasse`.`klasse` = `klasse`.`id`
								AND `fach_klasse`.`fach` = `faecher`.`id`
								AND `fach_klasse`.`anzeigen`=1
								AND `fach_klasse`.`user`=".$_SESSION['user_id']."
							ORDER BY `klasse`.`einschuljahr` DESC, `faecher`.`kuerzel`,`fach_klasse`.`gruppen_name`");
       ?>
      <div class="tab_3">
      <?php for ($i=0;$i<sql_num_rows($result);$i++) { ?>
        <a href="index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk=<?php echo sql_result($result,$i,'fach_klasse.id'); ?>"<?php if ($_GET["fk"]==sql_result($result,$i,'fach_klasse.id')) echo ' class="selected"'; ?>><span style="background-color:#<?php echo html_umlaute(@sql_result ( $result, $i, 'fach_klasse.farbe' )); ?>;"><?php echo html_umlaute(sql_result($result,$i,'faecher.kuerzel'))." ".($aktuelles_jahr-sql_result($result,$i,'klasse.einschuljahr')+1)." ".sql_result($result,$i,'klasse.endung')." ".html_umlaute(sql_result($result,$i,'fach_klasse.gruppen_name')); ?></span></a>
      <?php }
      if ($_GET["fk"]>0)
        db_conn_and_sql("UPDATE benutzer SET letzte_fachklasse=".injaway($_GET["fk"])." WHERE id=".$_SESSION['user_id']);
      ?>
        <!--<a href="index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;plan=alle"<?php if ($_GET["plan"]=="alle") echo ' class="selected"'; ?>>Plan&uuml;bersicht</a>-->
      </div>
	
	
	
      <?php if (isset($_GET["fk"]) and !isset($_GET["plan"])) {
		  if (!proofuser("fach_klasse", $_GET["fk"]))
			die("Sie sind nicht berechtigt, diese Fach-Klasse-Kombination zu bearbeiten.");
		if ($_GET["fk"]>0)
			$aktueller_plan=db_conn_and_sql("SELECT plan.id FROM plan WHERE plan.fach_klasse=".injaway($_GET["fk"])." AND plan.datum>".$CURDATE." ORDER BY plan.datum LIMIT 1"); ?>
	<div class="navigation_3">
	    <?php if (sql_num_rows($aktueller_plan)>0) { ?>
			<a href="index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk=<?php echo $_GET['fk']; ?>&amp;plan=<?php echo sql_result($aktueller_plan,0,"plan.id"); ?>&amp;ansicht=planung"<?php if ($_GET["ansicht"]=="planung") echo ' class="selected"'; else echo ' class="auswahl"'; ?>><img src="<?php echo $pfad; ?>icons/edit.png" title="Bearbeiten" alt="bearbeiten" /> n&auml;chste Stunde bearbeiten</a>
		<?php } ?>
			<a href="<?php echo $pfad; ?>formular/lb_uebersicht.php?fk=<?php echo $_GET["fk"]; ?>" class="auswahl">Lernbereichs-&Uuml;bersicht</a>
			<?php echo $navigation; ?>
		</div>
		<div class="inhalt" style="min-height: 685px;">
    
    <form action="<?php echo $pfad; ?>formular/fk_zeitplan_neu.php" method="post" accept-charset="ISO-8859-1">
    <script>
	$(function() {
        $( "#all_weeks" ).button();
		/*$( "#dragndrop" ).draggable({
			items: "li:not(.ui-state-disabled)"
		});

		$( "#dragndrop" ).draggable({
			cancel: ".ui-state-disabled"
		});*/
		$( "#dragndrop ol li" ).draggable({revert: true});
        
        $( ".droppable" ).droppable({
            hoverClass: "ui-state-active",
			drop: function( event, ui ) {
				$( this )
					.addClass( "ui-state-highlight" )
					.find( "p" )
						.html( ui.draggable.text() );
				$( this )
					.find( "input" )
						.val(ui.draggable.attr('id'));
			}
		});
	});
    
    // Inhalt runterrutschend (depreaced)
    /*$(document).ready(function(){
        el_top = $('#content_navi').offset({border: true}).top;
        //alert(el_top);
        $(window).scroll(function(){
		scroll_top = $(this).scrollTop();
		new_top = scroll_top-el_top;
		el = $('#content_navi');
		if(scroll_top>el_top){
			el.css({
			position: 'relative'
			});
			el.animate({'top' : new_top}, 400);
			$(el).dequeue();
		}else{
			el.css({
                top: 0
			});
		}

        });
    
    
    });*/

	</script>
    <div class="drag_liste nicht_drucken"> <!--id="content_navi" Inhalt rutscht mit-->
       <fieldset>
        <legend>Block-Eintragung: Lehrplan
            <?php $lehrplaene_klasse=db_conn_and_sql("SELECT *
								FROM `schulart`,`faecher`,`lp_user`, `lehrplan` LEFT JOIN `lernbereich` ON `lernbereich`.`lehrplan`=`lehrplan`.`id`
								WHERE `lehrplan`.`schulart`=`schulart`.`id`
									AND `lp_user`.`lehrplan`=`lehrplan`.`id`
									AND `lp_user`.`user`=".$_SESSION['user_id']."
									AND `lehrplan`.`fach`=`faecher`.`id`
									AND (`faecher`.`user`=0 OR `faecher`.`user`=".$_SESSION['user_id'].")
									AND lp_user.aktiv=1
								GROUP BY `lernbereich`.`klassenstufe`, `lehrplan`.`schulart`, `lehrplan`.`fach`, `lehrplan`.`bundesland`, `lehrplan`.`jahr`, `lehrplan`.`zusatz`
								ORDER BY `schulart`.`kuerzel`,`faecher`.`kuerzel`,`lehrplan`.`id`,`lernbereich`.`klassenstufe`,`lernbereich`.`nummer`");
                                 
        if ($_GET["lb"]>0 and proofuser("lernbereich",$_GET["lb"]))
            $my_lb = db_conn_and_sql("SELECT * FROM lernbereich WHERE id=".injaway($_GET["lb"]));
        else {
            $my_lb = db_conn_and_sql ( "SELECT * FROM `fach_klasse`,`lernbereich`
                                                    WHERE `fach_klasse`.`id`=".injaway($_GET["fk"])."
                                                     AND `fach_klasse`.`letzter_lernbereich`=`lernbereich`.`id`");
            if (sql_num_rows($my_lb)>0 and sql_result($my_lb,0,"lernbereich.lehrplan")>0)
				$my_lb = db_conn_and_sql ( "SELECT * FROM `lernbereich`, `lehrplan`
                                                    WHERE `lernbereich`.`lehrplan`=`lehrplan`.`id`
                                                     AND `lehrplan`.`id`=".sql_result($my_lb,0,"lernbereich.lehrplan")."
                                                     AND `lernbereich`.`klassenstufe`=".sql_result($my_lb,0,"lernbereich.klassenstufe")."
                                                    ORDER BY `lernbereich`.`nummer`");
        }
            /* besser? kollidiert aber mit info sek II-LP, weil es da keine 12 gibt
            $my_lb = db_conn_and_sql ( "SELECT * FROM `fach_klasse`,`klasse`,`lernbereich`,`lehrplan`
                                                    WHERE `fach_klasse`.`id`=".$_GET["fk"]."
                                                     AND `fach_klasse`.`klasse`=`klasse`.`id`
                                                     AND `fach_klasse`.`lehrplan`=`lehrplan`.`id`
                                                     AND `klasse`.`schulart`=`lehrplan`.`schulart`
                                                     AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
                                                     AND `lernbereich`.`klassenstufe`=(".$aktuelles_jahr."-`klasse`.`einschuljahr`+1)
                                                    ORDER BY `lernbereich`.`nummer`"); */
            ?>
            <select onchange="document.location.href='<?php echo $pfad; ?>index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk=<?php echo $_GET["fk"]; if ($_GET["anzeigen"]=="alle") echo '&amp;anzeigen=alle'; ?>&amp;lb='+this.value;"><?php
                for ($i=0; $i<sql_num_rows($lehrplaene_klasse); $i++) if (sql_result($lehrplaene_klasse, $i, "lernbereich.klassenstufe")>0) {
                    echo '<option value="'.sql_result($lehrplaene_klasse, $i, "lernbereich.id").'"';
                    if (sql_result ( $my_lb, 0, 'lernbereich.id' )==sql_result($lehrplaene_klasse, $i, "lernbereich.id"))
                        { echo ' selected="selected"'; $ausgewaehlter_lp=$i; }
                    echo '>'.sql_result($lehrplaene_klasse, $i, "schulart.kuerzel").' '.sql_result($lehrplaene_klasse, $i, "faecher.kuerzel").' - Kl. '.sql_result($lehrplaene_klasse, $i, "lernbereich.klassenstufe").' '.sql_result($lehrplaene_klasse, $i, "lehrplan.zusatz").'</option>';
                } ?></select>
          <?php if (sql_num_rows($lehrplaene_klasse)>0) {?>
          <a href="<?php echo $pfad; ?>index.php?tab=stundenplanung&amp;auswahl=lernbereiche&amp;lehrplan=<?php echo sql_result($lehrplaene_klasse, $ausgewaehlter_lp, "lernbereich.lehrplan"); ?>&amp;klasse=<?php echo sql_result($lehrplaene_klasse, $ausgewaehlter_lp, "lernbereich.klassenstufe"); ?>" class="icon"><img src="<?php echo $pfad; ?>icons/fundus.png" alt="fundus" title="gew&auml;hlten Lehrplan bearbeiten" /></a>
          <?php } ?>
        </legend>
            <button style="float: right; z-index:5;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
	<ul id="dragndrop">
<?php
		$hinweis_funktion_drag_und_drop_noch_nicht=1;
		
        $aktiver_lb_der_fk=sql_result(db_conn_and_sql("SELECT fach_klasse.letzter_lernbereich FROM fach_klasse WHERE id=".injaway($_GET["fk"])), 0, "fach_klasse.letzter_lernbereich");
        if (sql_result($my_lb,0,"lernbereich.lehrplan")>0)
			$result = db_conn_and_sql ( "SELECT * FROM `lernbereich`
									WHERE `lernbereich`.`lehrplan`=".sql_result ( $my_lb, 0, 'lernbereich.lehrplan' )."
										AND `lernbereich`.`klassenstufe`=".sql_result ( $my_lb, 0, 'lernbereich.klassenstufe' )."
									ORDER BY `lernbereich`.`nummer`");
		else $result ='';
		$wahl_zahler=1;
		if (sql_num_rows ( $result )<1)
			echo '<div class="hinweis" style="height: 60px;">Tragen Sie zun&auml;chst Lernbereiche und darin Unterrichtseinheiten (und Bl&ouml;cke) ein.<br />("Fundus / Lehrpl&auml;ne" - Lehrplan-Klassenstufe ausw&auml;hlen - "neuer Lernbereich")</div>';
		else
			for($i=0;$i<sql_num_rows ( $result );$i++) {
				if (sql_result ( $result, $i, 'lernbereich.id' )==$aktiver_lb_der_fk)
					$lb_aktiv=true;
				else
					$lb_aktiv=false;
            ?>
        <li><?php echo "LB "; if (sql_result ( $result, $i, 'lernbereich.wahl' )) {echo "W"; echo $wahl_zahler; $wahl_zahler++; } else echo @sql_result ( $result, $i, 'lernbereich.nummer' ); ?>: <strong><?php echo @html_umlaute(sql_result ( $result, $i, 'lernbereich.name' )); ?></strong> [<?php echo @sql_result ( $result, $i, 'lernbereich.ustd' ); ?>]
<?php
           $result_ue = db_conn_and_sql ( "SELECT `block`.* FROM `block` WHERE `block`.`block_hoeher` IS NULL AND `block`.`lernbereich`=".sql_result ( $result, $i, 'lernbereich.id' )." ORDER BY `block`.`position`");
           if (sql_num_rows ( $result_ue )>0) { ?>
            <img id="img_lb<?php echo $i; ?>" src="<?php echo $pfad; ?>icons/clip_<?php if ($lb_aktiv) echo 'open'; else echo 'closed'; ?>.png" alt="clip" onclick="javascript:clip('lb<?php echo $i; ?>', '<?php echo $pfad; ?>')" />
            <span id="span_lb<?php echo $i; ?>"<?php if (!$lb_aktiv) echo ' style="display: none;"'; ?>>
            <a href="<?php echo $pfad; ?>formular/aktuellen_lb_der_fk_speichern.php?fk=<?php echo $_GET["fk"]; ?>&amp;lb=<?php echo sql_result ( $result, $i, 'lernbereich.id' ); ?>" onclick="fenster(this.href, 'no title'); return false;" title="als aktuellen Lernbereich der Fach-Klasse speichern" class="icon" style="float: right;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="speichern" /></a>
            <ol>
<?php
             for($j=0;$j<sql_num_rows ( $result_ue );$j++) { ?>
           <li id="b<?php echo @sql_result ( $result_ue, $j, 'block.id'); ?>" title="<?php echo 'Beschr: '.html_umlaute(@sql_result ( $result_ue, $j, 'block.kommentare' )); ?>">
			<?php echo ($j+1).". "; ?>
			<?php echo @html_umlaute(sql_result ( $result_ue, $j, 'block.name' )); ?>
			[<?php echo @sql_result ( $result_ue, $j, 'block.stunden' ); if (@sql_result ( $result_ue, $j, 'block.puffer' )>0) echo "+".(0+@sql_result ( $result_ue, $j, 'block.puffer' )); ?>]
			</li>
<?php
              $result_block = db_conn_and_sql ( "SELECT * FROM `block` WHERE `block`.`block_hoeher`=".sql_result ( $result_ue, $j, 'block.id' )." ORDER BY `block`.`position`");
              if (sql_num_rows ( $result_block )>0) { ?>
           <ol>
<?php
                for($k=0;$k<sql_num_rows ( $result_block );$k++) { ?>
				<li id="b<?php echo @sql_result ( $result_block, $k, 'block.id' ); ?>" title="<?php echo 'Beschr: '.html_umlaute(@sql_result ( $result_block, $k, 'block.kommentare' )); ?>">
				<?php echo ($j+1).".".($k+1).". "; ?>
				<?php echo @html_umlaute(sql_result ( $result_block, $k, 'block.name' )); ?>
				[<?php echo @sql_result ( $result_block, $k, 'block.stunden' ); if (@sql_result ( $result_block, $k, 'block.puffer' )>0) echo "+".(0+@sql_result ( $result_block, $k, 'block.puffer' )); ?>]
			</li>
<?php } ?>
           </ol>
<?php }} ?>
        </ol>
        </span>
<?php }
		else
			if ($hinweis_funktion_drag_und_drop_noch_nicht) {
                if (@sql_result ( $result, $i, 'lehrplan.id' )>0)
                    echo '<div class="hinweis" style="height: 3em; margin-top: 5px;">F&uuml;gen Sie im <a href="index.php?tab=stundenplanung&amp;auswahl=lernbereiche&amp;lehrplan='.sql_result ( $result, $i, 'lehrplan.id' ).'&amp;klasse='.sql_result ( $result, $i, 'lernbereich.klassenstufe' ).'">Fundus</a> des Lernbereichs mindestens eine Unterrichtseinheit ein, um Drag&amp;Drop zu nutzen.</div>';
                else
                    echo '<div class="hinweis" style="height: 3em; margin-top: 5px;">F&uuml;gen Sie zun&auml;chst einen Lehrplan mit Ihrem Unterrichtsfach hinzu, um Unterrichtseinheiten nutzen zu k&ouml;nnen.</div>';
				$hinweis_funktion_drag_und_drop_noch_nicht=0;
			} ?>
            </li><?php
		} ?>
      </ul>
	  </fieldset>

	</div>
	
	<div class="tooltip" id="tt_grobplanung_datum">
		Wenn Sie in Ihrem Stundenplan Eintr&auml;ge vorgenommen haben, werden die Daten selbst&auml;ndig berechnet. Auch Ferientermine, schulfreie Tage, Feiertage und A/B-Wochen werden ber&uuml;cksichtigt. Wenn Sie eine au&szlig;erplanm&auml;&szlig;ige Stunde unterrichten, k&ouml;nnen Sie diese als "Zusatzstunde" eintragen.</div>
	<div class="tooltip" id="tt_grobplanung_block" style="width: 600px;">
		<p>Ordnen Sie den Textfeldern die Unterrichtseinheiten und Bl&ouml;cke aus der "Block-Eintragung" zu. Ziehen Sie dazu mit gedr&uuml;ckter linker Maustaste den Block in das entsprechende Feld ohne Eintrag. Nach dem Speichern ist die Eintragung abgeschlossen.</p>
		<p>Nun steht in der Grobplanung hier der Titel des Blocks und verschiedene Symbole:
			<ul><li><img src="<?php echo $pfad; ?>icons/zensur.png" alt="test"/> bedeutet, dass in dieser Unterrichtsstunde eine Zensur geplant ist. Diese kann entweder mit festem Datum (<img src="<?php echo $pfad; ?>icons/plan_weg.png" alt="plan"/>) eingetragen, oder der Unterrichtsstunde angehangen (<img src="<?php echo $pfad; ?>icons/plan.png" alt="plan"/>) sein. Letzteres hat den Vorteil, dass bei einer Verschiebung der Unterrichtsstunde auch die Zensur verschoben wird. Um diesen Status zu wechseln, klicken Sie auf das entsprechende Symbol.</li>
				<li>Um direkt zur Unterrichtsstunden-Detailplanung zu gelangen klicken Sie auf die Block-Bezeichnung der jeweiligen Tabellenzelle.</li>
				<li><img src="<?php echo $pfad; ?>icons/note.png" alt="Notiz" /> bedeutet, dass dieser Unterrichtsstunde eine Notiz zugeordnet wurde. Bewegen Sie den Mauszeiger &uuml;ber das Symbol, um die Notiz zu lesen.</li>
				<li>Vor dem Blocktitel steht entweder das Symbol <img src="<?php echo $pfad; ?>icons/vorbereitet_nicht.png" alt="nicht vorbereitet" />, <img src="<?php echo $pfad; ?>icons/vorbereitet.png" alt="vorbereitet" /> oder <img src="<?php echo $pfad; ?>icons/nachbereitet.png" alt="nachbereitet" />. Die Unterrichtsstunde ist damit als "nicht vorbereitet", "vorbereitet" oder "nachbereitet" gekennzeichnet.</li></ul>
		</p></div>
	<div class="tooltip" id="tt_grobplanung_aktion" style="width: 700px;">
		<ul>
			<li>Um eine Unterrichtsstunde <em>vorzubereiten</em>, klicken Sie auf deren Titel.</li>
			<li>Oft m&uuml;ssen in der Grobplanung Korrekturen vorgenommen werden.
				Wollen Sie mehrere Unterrichtsstunden in einem Rutsch <em>nach unten schieben</em>, um z.B. eine zus&auml;tzliche Unterrichtsstunde einzuschieben, nutzen Sie das Symbol <img src="./icons/down.png" alt="runter" />, womit alle unteren Unterrichtsstunden bis zum n&auml;chsten Freiraum nach unten geschoben werden. Gibt es keinen weiteren Freiraum, wird das Symbol nicht angezeigt. Analog funktioniert das mit dem Symbol <img src="./icons/up.png" alt="hoch" />.
				Zum Vertauschen von Unterrichtsstunden gibt es die Symbole <img src="<?php echo $pfad; ?>icons/hoch.png" alt="hoch" /> und <img src="<?php echo $pfad; ?>icons/runter.png" alt="runter" />. Wenn das zu tauschende Feld belegt ist, werden die Unterrichtsstunden vertauscht - wenn nicht wird die einzelne Unterrichtsstunde um ein Feld verschoben (das Symbol hat dann die gleiche Auswirkung, wie der gr&uuml;ne Pfeil).</li>
			<li>Um einen <em>Unterrichtsausfall</em> einzutragen, darf bei dem entsprechenden Datum kein Block eingetragen sein.
				Verschieben Sie also zun&auml;chst alle eventuell vorhandenen Unterrichtsstunden um ein Feld nach unten, oder l&ouml;schen Sie die vorhandene Unterrichtsstunde. Danach k&ouml;nnen Sie per Klick auf <img src="<?php echo $pfad; ?>icons/ausfall.png" alt="ausfall" /> den Ausfallgrund eintragen und speichern.</li>
			<li>Die <em>Block-Zuordnung</em> bzw. ein eventueller <em>Alternativ-Titel</em> kann &uuml;ber das Symbol <img src="<?php echo $pfad; ?>icons/edit.png" alt="bearbeiten" /> nachtr&auml;glich ge&auml;ndert werden.</li>
			<li>Soll die Unterrichtsstunde <em>gel&ouml;scht</em> werden, k&ouml;nnen Sie dies mit dem Symbol <img src="<?php echo $pfad; ?>icons/delete.png" alt="l&ouml;schen" /> erledigen. Bedenken Sie, dass dabei auch alle eventuell eingetragenen Abschnittszuordnungen gel&ouml;scht werden.</li>
		</ul>
		</div>

	<div style="width:50%;">
	  <input type="hidden" name="fach_klasse" value="<?php echo $_GET["fk"]; ?>" />
	  <input type="hidden" name="schuljahr" value="<?php echo $aktuelles_jahr; ?>" />
      <fieldset><legend>Stoffverteilungsplan</legend>
            <input type="checkbox" id="all_weeks"<?php if($_GET["anzeigen"]=="alle") echo ' checked="checked"'; ?> onclick="document.location.href='index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk=<?php echo $_GET["fk"];
                if($_GET["anzeigen"]!="alle")
                    echo '&amp;anzeigen=alle';
                if($_GET["lb"]>0)
                    echo '&amp;lb='.$_GET["lb"];
                ?>';" />
                <label for="all_weeks" style="width: auto; float: right;" title="Schalten Sie hier um, ob Sie die letzten zwei Wochen und alle folgenden (Standard) oder alle Wochen des Schuljahres anzeigen m&ouml;chten.">alle Wochen anzeigen</label>
      <table class="tabelle" cellspacing="0" style="clear: both;">
        <tr><th width="110px">Datum <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_grobplanung_datum')" onmouseout="hideWMTT()" /></th>
		<th>Block <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_grobplanung_block')" onmouseout="hideWMTT()" /> /
		Aktion <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_grobplanung_aktion')" onmouseout="hideWMTT()" /></th></tr>
		<?php
		$db=new db;
		$jahr=$db->aktuelles_jahr();
		$schule=db_conn_and_sql("SELECT klasse.schule FROM klasse, fach_klasse WHERE klasse.id=fach_klasse.klasse AND fach_klasse.id=".injaway($_GET["fk"]));
		$schule=sql_fetch_assoc($schule);
		$schule=$schule["schule"];
		$start_ende=schuljahr_start_ende($jahr, $schule);
		if (proofuser("fach_klasse",$_GET["fk"]))
			$notenbeschreibung=db_conn_and_sql("SELECT *, IF(`notenbeschreibung`.`datum` IS NULL,`plan`.`datum`,`notenbeschreibung`.`datum`) as `MyDatum`
				FROM `notentypen`,`notenbeschreibung` LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
				WHERE `notenbeschreibung`.`fach_klasse`=".injaway($_GET["fk"])."
					AND `notenbeschreibung`.`notentyp`=`notentypen`.`id`
					AND (('".$start_ende["start"]."'<=`notenbeschreibung`.`datum` AND '".$start_ende["ende"]."'>=`notenbeschreibung`.`datum`)
						OR ('".$start_ende["start"]."'<=`plan`.`datum` AND '".$start_ende["ende"]."'>=`plan`.`datum`))
				ORDER BY `MyDatum`");
		$n=0;
		//$schuljahr_id=db_conn_and_sql("SELECT `schuljahr`.`id`,`schule`.`id` FROM `fach_klasse`,`klasse`,`schule`,`schuljahr` WHERE `fach_klasse`.`klasse`=`klasse`.`id` AND `klasse`.`schule`=`schule`.`id` AND (`schuljahr`.`schule`=`schule`.`id` OR `schuljahr`.`schule`=0) AND `schuljahr`.`jahreszahl`=".$aktuelles_jahr." ORDER BY `schuljahr`.`schule` DESC");
		$eintraege=fachklassen_zeitplanung(injaway($_GET["fk"]),$aktuelles_jahr); //sql_result($schuljahr_id,0,"schuljahr.id"),sql_result($schuljahr_id,0,"schule.id"));
		if (count($eintraege)>1)
        for ($i=0; $i<count($eintraege);$i++)
		    if ($_GET["anzeigen"]=="alle"
                    or $eintraege[$i]["datum"]>($timestamp-60*60*24*21)
                    or ($i+7)>count($eintraege)) { // alle anzeigen oder nicht laenger als 3 Montage her oder (damit keine Leer-Anzeigen sind) mindestens die letzten 7 Eintraege anzeigen
				if (isset($eintraege[$i]["lernbereich"])) echo '<tr><td colspan="2" class="ui-widget-header" style="text-align: center;">'.$eintraege[$i]["lernbereich"].'</td></tr>'; ?>
				<tr onmouseover="document.getElementById('aktionen_menue_<?php echo $i; ?>').style.visibility='visible';" onmouseout="if (document.getElementById('ausfallgrundangabe_<?php echo $eintraege[$i]["fortlaufende_nummer"]; ?>') &amp;&amp; document.getElementById('ausfallgrundangabe_<?php echo $eintraege[$i]["fortlaufende_nummer"]; ?>').value!='') 1; else document.getElementById('aktionen_menue_<?php echo $i; ?>').style.visibility='hidden';">
					<td><?php
					echo $wochennamen_kurz[date("w",$eintraege[$i]["datum"])].' '; echo date("d.m.",$eintraege[$i]["datum"]); if ($eintraege[$i]["stunden"]>0) echo " (".$eintraege[$i]["stunden"].")"; ?></td>
                    <td style="width: 500px;">
					<?php // Tests in Planung
					$test_dabei=false;
					$datum=explode("-",@sql_result ( $notenbeschreibung, $n, 'MyDatum' ));
					while(@mktime(0,0,0,$datum[1],$datum[2],$datum[0])<=$eintraege[$i]["datum"] and $n<sql_num_rows($notenbeschreibung) and (sql_result ( $notenbeschreibung, $n, 'notenbeschreibung.plan' )=='' or sql_result ( $notenbeschreibung, $n, 'notenbeschreibung.plan' )==$eintraege[$i]["plan_id"])) {
						if (@mktime(0,0,0,$datum[1],$datum[2],$datum[0])>$eintraege[$i]["datum"]-60*60*24*7) {
							$test_dabei=true;
							if ($eintraege[$i]["typ"]=="eingetragen" or $eintraege[$i]["typ"]=="zusatz") {
								echo html_umlaute(sql_result ( $notenbeschreibung, $n, 'notentypen.kuerzel' ))." ".html_umlaute(sql_result ( $notenbeschreibung, $n, 'notenbeschreibung.beschreibung' )); ?>
								<a href="<?php echo $pfad; ?>formular/noten_bearbeiten.php?beschreibung=<?php echo sql_result ( $notenbeschreibung, $n, 'notenbeschreibung.id' ); ?>&amp;schuljahr=<?php echo $aktuelles_jahr; ?>" onclick="fenster('<?php echo $pfad; ?>formular/noten_bearbeiten.php?beschreibung=<?php echo sql_result ( $notenbeschreibung, $n, 'notenbeschreibung.id' ); ?>&amp;schuljahr=<?php echo $aktuelles_jahr; ?>','Noten bearbeiten'); return false;" class="icon" title="Test vom <?php echo datum_strich_zu_punkt(sql_result ( $notenbeschreibung, $n, 'MyDatum' )); ?> bearbeiten">
								<img src="<?php echo $pfad; ?>icons/zensur.png" alt="test" /></a>
								<a href="<?php echo $pfad; ?>formular/notenbeschreibung_plan.php?beschreibung=<?php echo sql_result ( $notenbeschreibung, $n, 'notenbeschreibung.id' ); ?>&amp;plan=<?php if(sql_result($notenbeschreibung, $n, 'notenbeschreibung.plan')>0) echo 'loeschen'; else echo $eintraege[$i]["plan_id"]; ?>&amp;fk=<?php echo $_GET["fk"]; ?>&amp;datum=<?php echo date("Y-m-d",$eintraege[$i]["datum"]); ?>&amp;aendern=true" onclick="fenster(this.href,  \'Unterrichtsstunde zuordnen\'); return false;" class="icon" title="<?php if(sql_result($notenbeschreibung, $n, 'notenbeschreibung.plan')>0) echo 'Note von Unterrichtsstunde trennen'; else echo 'mit dieser Unterrichsstunde verbinden' ?>"><img src="<?php echo $pfad; ?>icons/<?php if(sql_result($notenbeschreibung, $n, 'notenbeschreibung.plan')>0) echo 'plan'; else echo 'plan_weg' ?>.png" alt="plan" /></a>
								<br />
						<?php } }
						$n++;
						$datum=explode("-",@sql_result ( $notenbeschreibung, $n, 'MyDatum' ));
					} // Test-Planung ende
					if ($eintraege[$i]["typ"]=="eingetragen" or $eintraege[$i]["typ"]=="zusatz") {
							if($eintraege[$i]["nachbereitung"]) echo '<img src="'.$pfad.'icons/nachbereitet.png" alt="nachbereitet" title="nachbereitet" /> ';
							else {
								if($eintraege[$i]["vorbereitet"]) echo '<img src="'.$pfad.'icons/vorbereitet.png" alt="vorbereitet" title="vorbereitet" /> ';
								else echo '<img src="'.$pfad.'icons/vorbereitet_nicht.png" alt="nicht vorbereitet" title="nicht vorbereitet" /> ';
							}
							echo '<a href="index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk='.$_GET["fk"].'&amp;plan='.$eintraege[$i]["plan_id"].'&amp;ansicht=planung&amp;blocknummer='.$eintraege[$i]["block_1_id"].'&amp;blocknummer2='.$eintraege[$i]["block_2_id"].'"';
							if ($eintraege[$i]["alternativtitel"]!="") echo ' title="'; else echo '>';
							echo $eintraege[$i]["block_1"]; if($eintraege[$i]["block_2"]!="") echo ' &amp; '.$eintraege[$i]["block_2"];
							if ($eintraege[$i]["alternativtitel"]!="") echo '">'.$eintraege[$i]["alternativtitel"];
							echo '</a>';
							if ($eintraege[$i]["bemerkung"]!="") echo '<br />'.$eintraege[$i]["bemerkung"];
							if ($eintraege[$i]["notizen"]!="") echo ' <img src="'.$pfad.'icons/note.png" alt="Notiz" title="Notiz: '.$eintraege[$i]["notizen"].'" />';
							echo '<div id="aktionen_menue_'.$i.'" class="aktionen_menue">';
							//naechster Termin
							$k=1;
							while(($eintraege[$i+$k]["typ"]=="feiertag" or $eintraege[$i+$k]["typ"]=="ausfall") and $k<200) $k++;
							$j=1;
							while(($eintraege[$i-$j]["typ"]=="feiertag" or $eintraege[$i-$j]["typ"]=="ausfall") and ($i-$j)>=0) $j++;
							if ($eintraege[$i-$j]["typ"]=="frei_fuer_eintragung") echo '<a href="'.$pfad.'formular/fk_zeitplan_aktion.php?aktion=hoch&amp;machs=kurz&amp;aendern=true&amp;wie=einzel&amp;plan='.$eintraege[$i]["plan_id"].'&amp;eintrag='.$i.'" title="um eins nach oben schieben" class="icon"><img src="./icons/hoch.png" alt="hoch" /></a>';
							if ($eintraege[$i-$j]["typ"]=="eingetragen" or $eintraege[$i-$j]["typ"]=="zusatz") echo '<a href="'.$pfad.'formular/fk_zeitplan_aktion.php?aktion=hoch&amp;machs=kurz&amp;aendern=true&amp;wie=tauschen&amp;plan='.$eintraege[$i]["plan_id"].'&amp;eintrag='.$i.'" title="mit oberem tauschen" class="icon"><img src="./icons/hoch.png" alt="hoch" /></a>';
							if ($eintraege[$i+$k]["typ"]=="frei_fuer_eintragung") echo '<a href="'.$pfad.'formular/fk_zeitplan_aktion.php?aktion=runter&amp;machs=kurz&amp;aendern=true&amp;wie=einzel&amp;plan='.$eintraege[$i]["plan_id"].'&amp;eintrag='.$i.'" title="um eins nach unten schieben" class="icon"><img src="./icons/runter.png" alt="runter" /></a>';
							if ($eintraege[$i+$k]["typ"]=="eingetragen" or $eintraege[$i+$k]["typ"]=="zusatz") echo '<a href="'.$pfad.'formular/fk_zeitplan_aktion.php?aktion=runter&amp;machs=kurz&amp;aendern=true&amp;wie=tauschen&amp;plan='.$eintraege[$i]["plan_id"].'&amp;eintrag='.$i.'" title="mit unterem tauschen" class="icon"><img src="./icons/runter.png" alt="runter" /></a>';
							
							if ($eintraege[$i-$j+1]["typ"]!="frei_fuer_eintragung") {
								$zaehle_runterschieb_eintraege=0;
								while(($eintraege[$i-$j]["typ"]!="frei_fuer_eintragung") and ($i-$j)>=0) {if ($eintraege[$i-$j]["typ"]=="eingetragen" or $eintraege[$i-$j]["typ"]=="zusatz") $zaehle_runterschieb_eintraege++; $j++;}
								if (($i-$j)>=0) echo '<a href="'.$pfad.'formular/fk_zeitplan_aktion.php?aktion=hoch&amp;plan='.$eintraege[$i]["plan_id"].'&amp;eintrag='.$i.'&amp;aendern=true&amp;machs=kurz&amp;ziel='.($i-$j).'&amp;wie=mehrere" title="Diesen und die '.$zaehle_runterschieb_eintraege.' Eintr&auml;ge davor nach oben schieben" class="icon"><img src="./icons/up.png" alt="hoch" /></a> ';
							}
							if ($eintraege[$i+$k-1]["typ"]!="frei_fuer_eintragung") {
								$zaehle_runterschieb_eintraege=0;
								while(($eintraege[$i+$k]["typ"]!="frei_fuer_eintragung") and $k<200) {if ($eintraege[$i+$k]["typ"]=="eingetragen" or $eintraege[$i+$k]["typ"]=="zusatz") $zaehle_runterschieb_eintraege++; $k++;}
								if ($k<200) echo '<a href="'.$pfad.'formular/fk_zeitplan_aktion.php?aktion=runter&amp;plan='.$eintraege[$i]["plan_id"].'&amp;eintrag='.$i.'&amp;aendern=true&amp;machs=kurz&amp;ziel='.($i+$k).'&amp;wie=mehrere" title="Diesen und die '.($zaehle_runterschieb_eintraege).' n&auml;chsten Eintr&auml;ge nach unten schieben" class="icon"><img src="./icons/down.png" alt="runter" /></a> ';
							}
                            echo '<a href="javascript:fenster(\''.$pfad.'formular/fk_zeitplan_aktion.php?aktion=bearbeiten&amp;plan='.$eintraege[$i]["plan_id"].'\', \'bearbeiten\')" title="bearbeiten: Name, Bl&ouml;cke, Notizen..." class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a> ';
							if (!$test_dabei)
                                echo '<a href="javascript:fenster(\''.$pfad.'formular/fk_zeitplan_aktion.php?aktion=loeschen&amp;plan='.$eintraege[$i]["plan_id"].'\', \'loeschen\')" title="l&ouml;schen" class="icon"><img src="'.$pfad.'icons/delete.png" alt="l&ouml;schen" /></a>
                                <a href="javascript:fenster(\''.$pfad.'formular/notenspalte_neu.php?plan='.$eintraege[$i]["plan_id"].'\', \'Neue Zensur\')" title="Leistungs&uuml;berpr&uuml;fung hinzuf&uuml;gen" class="icon"><img src="'.$pfad.'icons/zensur.png" alt="test" /></a>';
							// Ausfall: ich loese das lieber mit manuellem Runterschieben und danach Ausfall eintragen
							// echo '<a href="javascript:fenster(\'./formular/fk_zeitplan_aktion.php?aktion=ausfall&amp;plan='.$eintraege[$i]["plan_id"].'\', \'Ausfall\')" title="Ausfall: alle darunter liegenden Eintr&auml;ge um eins nach unten r&uuml;cken" class="icon"><img src="./icons/ausfall.png" alt="ausfall" /></a>';
							echo '</div>';
					}
					if ($eintraege[$i]["typ"]=="frei_fuer_eintragung") {
							echo '<div class="droppable drop_block"><input type="hidden" name="block_'.$eintraege[$i]["fortlaufende_nummer"].'" />
                                            <p> - ohne Eintrag - </p></div>
										<input type="hidden" name="datum_'.$eintraege[$i]["fortlaufende_nummer"].'" value="'.$eintraege[$i]["datum"].'" />
										<input type="hidden" name="ustd_'.$eintraege[$i]["fortlaufende_nummer"].'" value="'.$eintraege[$i]["stunden"].'" />
										<input type="hidden" name="zeit_'.$eintraege[$i]["fortlaufende_nummer"].'" value="'.$eintraege[$i]["zeit"].'" />
										<div id="aktionen_menue_'.$i.'" class="aktionen_menue">
											<a href="javascript:document.getElementById(\'ausfall_'.$i.'\').style.display=\'inline\';" onclick="document.getElementById(\'ausfall_'.$i.'\').style.display=\'inline\'; return false;" title="Ausfall - Ausfallgrund angeben" class="icon" style="float: left"><img src="./icons/ausfall.png" alt="ausfall" /></a>
											<div id="ausfall_'.$i.'" style="display: none; float: left;">&nbsp;Grund: <input type="text" name="ausfall_'.$eintraege[$i]["fortlaufende_nummer"].'" id="ausfallgrundangabe_'.$eintraege[$i]["fortlaufende_nummer"].'" size="8" title="falls die Stunde ausfallen soll, hier den Grund angeben" maxlength="50" /></div>
										</div>';
					}
					if ($eintraege[$i]["typ"]=="ausfall") {
							echo "<b>".$eintraege[$i]["grund"].'</b>
							<div id="aktionen_menue_'.$i.'" class="aktionen_menue">
                                <a href="javascript:fenster(\'./formular/fk_zeitplan_aktion.php?aktion=bearbeiten&amp;plan='.$eintraege[$i]["plan_id"].'\', \'bearbeiten\')" title="bearbeiten" class="icon"><img src="./icons/edit.png" alt="bearbeiten" /></a>
                                <a href="javascript:fenster(\'./formular/fk_zeitplan_aktion.php?aktion=loeschen&amp;plan='.$eintraege[$i]["plan_id"].'\', \'loeschen\')" title="l&ouml;schen" class="icon"><img src="./icons/delete.png" alt="l&ouml;schen" /></a>
							</div>';
					}
					if ($eintraege[$i]["typ"]=="feiertag") {
							echo '<b>'.$eintraege[$i]["grund"].'</b>';
					}
					?></td></tr><?php
			} else {
				// damit der richtige Start-Test gewaehlt ist bei "weniger Wochen anzeigen"
				$datum=explode("-",@sql_result ( $notenbeschreibung, $n, 'MyDatum' ));
				while(@mktime(0,0,0,$datum[1],$datum[2],$datum[0])<=$eintraege[$i]["datum"] and $n<sql_num_rows($notenbeschreibung) and (sql_result ( $notenbeschreibung, $n, 'notenbeschreibung.plan' )=='' or sql_result ( $notenbeschreibung, $n, 'notenbeschreibung.plan' )==$eintraege[$i]["plan_id"])) {
					$n++;
					$datum=explode("-",@sql_result ( $notenbeschreibung, $n, 'MyDatum' ));
				}
			} ?>
      </table>
      <br />
      <div style="text-align: center;">
      <button><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
      </div>
      </fieldset>
	</div>
    </form>
    
	<div class="nicht_drucken" style="width: 50%">
	  <form action="<?php echo $pfad; ?>formular/plan_zusatz_neu.php?schuljahr=<?php echo $aktuelles_jahr; ?>" method="post" accept-charset="ISO-8859-1">
		<fieldset>
			<legend>Zusatzstunde <img id="img_zusatzstunde" src="./icons/clip_closed.png" alt="clip" onclick="javascript:clip('zusatzstunde', '<?php echo $pfad; ?>')" /></legend>
			<span id="span_zusatzstunde" style="display: none;">
			<input type="hidden" name="schuljahr" value="<?php echo $aktuelles_jahr; ?>" />
			<input type="hidden" name="fachklasse" value="<?php echo $_GET["fk"]; ?>" />
			<label for="block">Block<em>*</em>:</label> <div class="droppable drop_block" style="width: 60%; float: none;"><input type="hidden" name="block" />
                                            <p> - ohne Eintrag - </p></div><br />
			<label for="datum">Datum<em>*</em>:</label> <input type="text" class="datepicker" name="datum" size="7" maxlength="10" /><br />
			<label for="zeit">Zeit<em>*</em>:</label> <select name="zeit"><?php
				$zeit=db_conn_and_sql("SELECT `stundenzeiten`.`beginn`, `stundenzeiten`.`id`
					FROM `stundenzeiten`,`klasse`,`fach_klasse`
					WHERE `fach_klasse`.`klasse`=`klasse`.`id`
							AND `klasse`.`schule`=`stundenzeiten`.`schule`
							AND `fach_klasse`.`id`=".injaway($_GET["fk"])."
					ORDER BY `stundenzeiten`.`beginn`");
					for($i=0;$i<sql_num_rows($zeit);$i++) echo '<option value="'.sql_result($zeit,$i,"stundenzeiten.beginn").'">'.substr(sql_result($zeit,$i,"stundenzeiten.beginn"),0,5).' Uhr</option>'; ?>
				</select><br />
			<input type="button" class="button" value="eintragen" onclick="auswertung=new Array(new Array(1, 'datum','datum','<?php echo ($aktuelles_jahr); ?>-01-01','<?php echo ($aktuelles_jahr+1); ?>-12-31'), new Array(1, 'zeit','zeit','03:00','23:30')); pruefe_formular(auswertung);" />
			</span>
		</fieldset>
	  </form>
	</div>
      
      <?php }
	  
	  if (!isset($_GET["fk"]) and !isset($_GET["uebersicht"]) and !isset($_GET["plan"])) {
		  $nachbereitungstage=14; $vorbereitungstage=7; ?>
	<div class="navigation_3"><?php echo $navigation; ?></div>
		<div class="inhalt">
		<p>Um zur Grobplanung zu gelangen, klicken Sie oben auf die gew&uuml;nschte Fach-Klasse-Kombination</p>

		<p><b>Nachbereitung offen f&uuml;r (letzte <?php echo $nachbereitungstage; ?> Tage):</b><ul>
			<?php
			/*
			$lernbereiche=db_conn_and_sql("SELECT * FROM block, block_abschnitt WHERE block_abschnitt.block=block.id AND block.lernbereich=27");
			$lb=0;
			for ($i=0;$i<sql_num_rows($lernbereiche);$i++) {
				echo 'INSERT INTO block (id, block_hoeher, stunden, name, methodisch, verknuepfung_fach, kommentare, position, puffer, lernbereich) VALUES ('.sql_result($lernbereiche, $i, "block.id").', '.leer_NULL(sql_result($lernbereiche, $i, "block.block_hoeher")).', '.leer_NULL(sql_result($lernbereiche, $i, "block.stunden")).', '.apostroph_bei_bedarf(html_umlaute(sql_result($lernbereiche, $i, "block.name"))).', '.apostroph_bei_bedarf(html_umlaute(sql_result($lernbereiche, $i, "block.methodisch"))).', '.apostroph_bei_bedarf(html_umlaute(sql_result($lernbereiche, $i, "block.verknuepfung_fach"))).', '.apostroph_bei_bedarf(html_umlaute(sql_result($lernbereiche, $i, "block.kommentare"))).', '.leer_NULL(sql_result($lernbereiche, $i, "block.position")).', '.leer_NULL(sql_result($lernbereiche, $i, "block.puffer")).', '.leer_NULL(sql_result($lernbereiche, $i, "block.lernbereich")).');<br />';
			}
			*/
			
			
			
			$fruehestes_datum=date("Y-m-d",$timestamp-60*60*24*$nachbereitungstage);
			$plan=db_conn_and_sql("SELECT *
			FROM `fach_klasse`,`klasse`,`faecher`,`plan` LEFT JOIN `block` AS `block1` ON `plan`.`block_1`=`block1`.`id` LEFT JOIN `block` AS `block2` ON `plan`.`block_2`=`block2`.`id`
			WHERE `plan`.`schuljahr`=".$aktuelles_jahr."
				AND `plan`.`fach_klasse`=`fach_klasse`.`id`
				AND `fach_klasse`.`klasse`=`klasse`.`id`
				AND `fach_klasse`.`fach`=`faecher`.`id`
				AND `fach_klasse`.`user`=".$_SESSION['user_id']."
				AND `plan`.`ausfallgrund` IS NULL
				AND `plan`.`nachbereitung`<>TRUE
				AND `plan`.`datum`<=".$CURDATE."
				AND `plan`.`datum`>'".$fruehestes_datum."'
			ORDER BY `plan`.`datum` DESC, `plan`.`startzeit` DESC");
			// AND `plan`.`vorbereitet`=TRUE
			
			// TODO Wochentag und Datum mit Funktionen loesen
			for ($i=0;$i<sql_num_rows($plan);$i++)
				echo '<li>'.$wochennamen_kurz[date("w",mktime(1,0,0,substr(sql_result($plan,$i,"plan.datum"),5,2),substr(sql_result($plan,$i,"plan.datum"),8,2),substr(sql_result($plan,$i,"plan.datum"),0,4)))].' '.(substr(sql_result($plan,$i,"plan.datum"),8,2)+0).'.'.(substr(sql_result($plan,$i,"plan.datum"),5,2)+0).'. '.(substr(sql_result($plan,$i,"plan.startzeit"),0,2)+0).'&thinsp;<sup style="text-decoration: underline; font-size: 7pt;">'.substr(sql_result($plan,$i,"plan.startzeit"),3,2).'</sup> Uhr: '.$subject_classes->nach_ids[sql_result($plan,$i,"fach_klasse.id")]["farbanzeige"].'
				<a href="javascript:fenster(\'./formular/nachbereiten.php?plan='.sql_result($plan,$i,"plan.id").'\',\'Nachbereitung\')" class="icon" title="nachbereiten"><img src="./icons/nachbereiten.png" alt="nachbereiten" /></a></li>'; ?>
			</ul>
		</p>
		<p><b>Vorbereitung f&uuml;r n&auml;chste <?php echo $vorbereitungstage; ?> Tage:</b></p>
		<ul>
			<?php
			$spaetestes_datum=date("Y-m-d",$timestamp+60*60*24*$vorbereitungstage);
			$plan=db_conn_and_sql("SELECT *
			FROM `fach_klasse`,`klasse`,`faecher`,`plan` LEFT JOIN `block` AS `block1` ON `plan`.`block_1`=`block1`.`id`
				LEFT JOIN `block` AS `block2` ON `plan`.`block_2`=`block2`.`id`
				LEFT JOIN `notenbeschreibung` ON `plan`.`id`=`notenbeschreibung`.`plan`
				LEFT JOIN `notenbeschreibung` AS `notenbeschreibung_2` ON (`plan`.`datum`=`notenbeschreibung_2`.`datum` AND `plan`.`fach_klasse`=`notenbeschreibung_2`.`fach_klasse`)
			WHERE `plan`.`schuljahr`=".$aktuelles_jahr."
				AND `plan`.`fach_klasse`=`fach_klasse`.`id`
				AND `fach_klasse`.`klasse`=`klasse`.`id`
				AND `fach_klasse`.`fach`=`faecher`.`id`
				AND `fach_klasse`.`user`=".$_SESSION['user_id']."
				AND `plan`.`datum`<'".$spaetestes_datum."'
				AND (`plan`.`datum`>'".date("Y-m-d",$timestamp)."' OR (`plan`.`datum`='".date("Y-m-d",$timestamp)."' AND `plan`.`startzeit`>'".date("H:i:s",$timestamp)."'))
				AND `plan`.`ausfallgrund` IS NULL
			ORDER BY `plan`.`datum` ASC, `plan`.`startzeit` ASC"); //				AND `plan`.`vorbereitet`<>TRUE
			for ($i=0;$i<sql_num_rows($plan);$i++) {
				echo '<li>'.$wochennamen_kurz[date("w",mktime(1,0,0,substr(sql_result($plan,$i,"plan.datum"),5,2),substr(sql_result($plan,$i,"plan.datum"),8,2),substr(sql_result($plan,$i,"plan.datum"),0,4)))].' '.(substr(sql_result($plan,$i,"plan.datum"),8,2)+0).'.'.(substr(sql_result($plan,$i,"plan.datum"),5,2)+0).'.: ';
				do {
					echo ' - <a href="'.$pfad.'index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk='.sql_result($plan,$i,"plan.fach_klasse").'&amp;plan='.sql_result($plan,$i,"plan.id").'&amp;ansicht=planung&amp;blocknummer='.sql_result($plan,$i,"plan.block_1").'&amp;blocknummer2='.sql_result($plan,$i,"plan.block_2").'&amp;new_sc='.sql_result($plan,$i,"plan.fach_klasse").'" class="icon" title="vorbereiten">'.$subject_classes->nach_ids[sql_result($plan,$i,"fach_klasse.id")]["name"].'</a> '.(substr(sql_result($plan,$i,"plan.startzeit"),0,2)+0).'&thinsp;<sup style="text-decoration: underline; font-size: 7pt;">'.substr(sql_result($plan,$i,"plan.startzeit"),3,2).'</sup> ';
					if (sql_result($plan,$i,"notenbeschreibung.id")>1 or sql_result($plan,$i,"notenbeschreibung_2.id")>1) {
						echo '<img src="'.$pfad.'icons/zensur.png" alt="test" title="Zensur geplant" />';
						// wenn zwei Zensuren auf einen Tag fallen, wird der so eliminiert
						while (sql_result($plan,$i,"plan.id")==@sql_result($plan,$i+1,"plan.id")) {
							echo '<img src="'.$pfad.'icons/zensur.png" alt="test" title="Zensur geplant" />';
							$i++;
						}
					}
					if (sql_result($plan,$i,"plan.vorbereitet"))
						echo '<img src="'.$pfad.'icons/vorbereitet.png" alt="vorbereitet" title="vorbereitet" />';
					else
						echo '<img src="'.$pfad.'icons/vorbereitet_nicht.png" alt="nicht_vorbereitet"  title="noch nicht vorbereitet" />';
					$i++;
				}
				while (@sql_result($plan,$i,"plan.datum")==sql_result($plan,$i-1,"plan.datum"));
				$i--;
				echo '</li>';
			} ?>
			</ul>
			<p>
			<form action="<?php echo $pfad; ?>formular/plan_zusatz_neu.php?schuljahr=<?php echo $aktuelles_jahr; ?>" method="post" accept-charset="ISO-8859-1">
			<fieldset>
			<legend>Zusatzstunde <img id="img_zusatzstunde" src="./icons/clip_closed.png" alt="clip" onclick="javascript:clip('zusatzstunde', '<?php echo $pfad; ?>')" /></legend>
			<span id="span_zusatzstunde" style="display: none;">
			<input type="hidden" name="schuljahr" value="<?php echo $aktuelles_jahr; ?>" />
			<select name="fachklasse" onchange="if(this.value=='neu') document.getElementById('neue_fk').style.display='block'; else document.getElementById('neue_fk').style.display='none';">
				<!--<option value="neu">neue Fach-Klasse</option>-->
				<?php $fachklassen=db_conn_and_sql("SELECT *
					FROM `fach_klasse`,`klasse`,`schule`,`faecher`
					WHERE `fach_klasse`.`fach`=`faecher`.`id`
						AND `fach_klasse`.`user`=".$_SESSION['user_id']."
						AND `klasse`.`schule`=`schule`.`id`
						AND `fach_klasse`.`klasse`=`klasse`.`id`
						AND `klasse`.`einschuljahr`>".($aktuelles_jahr-13)."
					ORDER BY `schule`.`kuerzel`, `faecher`.`kuerzel`, `klasse`.`einschuljahr` DESC, `klasse`.`endung`, `fach_klasse`.`gruppen_name`");
				for ($i=0;$i<sql_num_rows($fachklassen);$i++)
					echo '<option value="'.sql_result($fachklassen,$i,"fach_klasse.id").'">'.html_umlaute(sql_result($fachklassen,$i,"schule.kuerzel")).' '.html_umlaute(sql_result($fachklassen,$i,"faecher.kuerzel")).' '.($aktuelles_jahr-sql_result($fachklassen,$i,"klasse.einschuljahr")+1).sql_result($fachklassen,$i,"klasse.endung").' '.html_umlaute(sql_result($fachklassen,$i,"fach_klasse.gruppen_name")).'</option>';
				?>
			</select><br />
			<fieldset id="neue_fk" style="display: none;"><legend>Neue Fach-Klasse-Kombination</legend><!-- soll besser per Hand angelegt werden (deaktiviert) -->
				<table><tr><td><label for="einschulung">Klassenstufe<em>*</em>:</label></td><td><select name="einschulung"><?php for($i=1;$i<14; $i++) echo '<option value="'.($aktuelles_jahr-$i+1).'">'.$i.'</option>'; ?></select></td><td>
				<label for="endung">Endung:</label></td><td><input type="text" name="endung" size="2" maxlength="5" /></td><td>
				<label for="schulart">Schulart<em>*</em>:</label></td><td><select name="schulart"><?php $schulart=db_conn_and_sql("SELECT * FROM `schulart` ORDER BY `schulart`.`kuerzel`"); for($i=0;$i<sql_num_rows($schulart);$i++) echo '<option value="'.sql_result($schulart,$i,"schulart.id").'">'.html_umlaute(sql_result($schulart,$i,"schulart.kuerzel")).'</option>'; ?></select></td></tr><tr><td>
				<label for="schule">Schule<em>*</em>:</label></td><td colspan="5"><select name="schule"><?php $schulen=db_conn_and_sql("SELECT * FROM `schule`, `schule_user` WHERE `schule_user`.`aktiv`=1 AND `schule_user`.`schule`=`schule`.`id` AND `schule_user`.`user`=".$_SESSION['user_id']); for($i=0;$i<sql_num_rows($schulen);$i++) echo '<option value="'.sql_result($schulen,$i,"schule.id").'">'.html_umlaute(sql_result($schulen,$i,"schule.kuerzel")).'</option>'; ?></select></td></tr><tr><td>
				<label for="fach_neu">Fach<em>*</em>:</label></td><td><select name="fach_neu"><?php $faecher=db_conn_and_sql("SELECT * FROM `faecher` WHERE `faecher`.`user`=0 OR `faecher`.`user`=".$_SESSION['user_id']); for($i=0;$i<sql_num_rows($faecher);$i++) echo '<option value="'.sql_result($faecher,$i,"faecher.id").'">'.html_umlaute(sql_result($faecher,$i,"faecher.kuerzel")).'</option>'; ?></select></td><td>
				<!-- folgende zwei noch nicht integriert -->
				<label for="bewertungstabelle_neu">Bewertungstab.<em>*</em>:</label></td><td><select name="bewertungstabelle_neu"><?php echo bewertungstabelle_select(injaway($_GET["auswahl"])); ?></select></td><td>
				<label for="notenberechnungsvorlage_neu">Zensurenberechnung<em>*</em>:</label></td><td><select name="notenberechnungsvorlage_neu"><?php echo notenberechnungsvorlagen_select(injaway($_GET["auswahl"]),0); ?></select></td><td>
				
				<label for="lehrplan_neu">Lehrplan<em>*</em>:</label></td><td colspan="3"><select name="lehrplan_neu"><?php $lehrplan=db_conn_and_sql("SELECT * FROM `lehrplan`, `lp_user`, `faecher`,`schulart` WHERE `lehrplan`.`id`=`lp_user`.`lehrplan` AND `lp_user`.`user=".$_SESSION['user_id']." AND `lehrplan`.`fach`=`faecher`.`id` AND `lehrplan`.`schulart`=`schulart`.`id` ORDER BY `lehrplan`.`aktiv` DESC, `lehrplan`.`bundesland`, `lehrplan`.`schulart`, `lehrplan`.`fach`,`lehrplan`.`jahr` DESC"); for ($j=0;$j<sql_num_rows($lehrplan);$j++) echo '<option value="'.sql_result($lehrplan,$j,"lehrplan.id").'">'.$bundesland[sql_result($lehrplan,$j,"lehrplan.bundesland")]['kuerzel'].' '.html_umlaute(sql_result($lehrplan,$j,"schulart.kuerzel")).' '.html_umlaute(sql_result($lehrplan,$j,"faecher.kuerzel")).' '.sql_result($lehrplan,$j,"lehrplan.jahr").'</option>'; ?></select></td></tr></table>
			</fieldset>
			<label for="datum">Datum<em>*</em>:</label> <input type="text" class="datepicker" name="datum" size="7" maxlength="10" /><br />
			<label for="zeit">Zeit<em>*</em>:</label> <input type="time" name="zeit" size="4" maxlength="5" value="07:40" /> Uhr<br />
			<input type="button" class="button" value="eintragen" onclick="auswertung=new Array(new Array(0, 'datum','datum','<?php echo ($aktuelles_jahr); ?>-01-01','<?php echo ($aktuelles_jahr+1); ?>-12-31'), new Array(0, 'zeit','zeit','03:00','23:30')); pruefe_formular(auswertung);" />
			</span>
		</fieldset>
	  </form>  

		</p>

	<?php }
	
	// Einzelstundenansicht
	  if(isset($_GET["plan"])) {
	  if ($_GET["plan"]!="alle") { ?>
      <div class="navigation_3">
	    <a href="index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk=<?php echo $_GET['fk']; ?>"<?php if ($_GET["plan"]<1) echo ' class="selected"'; else echo ' class="auswahl"'; ?>>Stoffverteilungsplan</a>
	    <a href="index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk=<?php echo $_GET['fk']; ?>&amp;plan=<?php echo $_GET['plan']; ?>&amp;ansicht=planung"<?php if ($_GET["ansicht"]=="planung") echo ' class="selected"'; else echo ' class="auswahl"'; ?>><img src="<?php echo $pfad; ?>icons/edit.png" title="Bearbeiten" alt="bearbeiten" /> Bearbeiten</a>
        <a href="index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk=<?php echo $_GET['fk']; ?>&amp;plan=<?php echo $_GET['plan']; ?>&amp;ansicht=druck"<?php if ($_GET["ansicht"]=="druck") echo ' class="selected"'; else echo ' class="auswahl"'; ?>><img src="<?php echo $pfad; ?>icons/drucken.png" title="Druckansicht" alt="drucker" /> Druckansicht</a>
        <?php if (sql_result(db_conn_and_sql("SELECT * FROM benutzer WHERE id=".$_SESSION['user_id']), 0, "benutzer.ansicht_2")!="") { ?><a href="index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk=<?php echo $_GET['fk']; ?>&amp;plan=<?php echo $_GET['plan']; ?>&amp;ansicht=zweitansicht"<?php if ($_GET["ansicht"]=="zweitansicht") echo ' class="selected"'; else echo ' class="auswahl"'; ?>>Zweitansicht</a><?php } ?>
        <a href="index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk=<?php echo $_GET['fk']; ?>&amp;plan=<?php echo $_GET['plan']; ?>&amp;ansicht=hefter"<?php if ($_GET["ansicht"]=="hefter") echo ' class="selected"'; else echo ' class="auswahl"'; ?>><img src="<?php echo $pfad; ?>icons/hefter.png" title="Sch&uuml;lerhefter" alt="hefter" /> Sch&uuml;lerhefter</a>
        <a href="javascript:fenster('<?php echo $pfad; ?>lessons/durchfuehransicht_lehrer.php?plan=<?php echo $_GET['plan']; ?>','Durchf&uuml;hransicht')" class="auswahl">Durchf&uuml;hr_1</a>
        <a href="javascript:fenster('<?php echo $pfad; ?>lessons/durchfuehransicht.php?plan=<?php echo $_GET['plan']; ?>','Durchf&uuml;hransicht')" class="auswahl">Durchf&uuml;hr_2</a>
        <?php echo $navigation; ?></div>
	<div class="inhalt">
      <?php } ?>
      <?php if($_GET["plan"]=="alle") { ?>
	    <div class="navigation_3"><?php echo $navigation; ?></div>
	    <div class="inhalt">
             <?php $plaene=db_conn_and_sql("SELECT `plan`.*, COUNT(`abschnittsplanung`.`abschnitt`) AS `anzahl`
								FROM `fach_klasse`, `plan` LEFT JOIN `abschnittsplanung` ON `abschnittsplanung`.`plan`=`plan`.`id`
								WHERE `fach_klasse`.`user`=".$_SESSION['user_id']."
									AND `fach_klasse`.`id`=`plan`.`fach_klasse`
									AND `plan`.`schuljahr`=".$aktuelles_jahr."
									AND `plan`.`ausfallgrund` IS NULL
									AND `plan`.`datum`<".$CURDATE."
								GROUP BY `plan`.`id`
								ORDER BY `plan`.`datum` DESC,`plan`.`startzeit` DESC");
	                for ($i=0;$i<sql_num_rows($plaene);$i++) {
						echo '<a href="javascript:fenster(\''.$pfad.'lessons/durchfuehransicht.php?plan='.sql_result($plaene,$i,"plan.id").'\',\'Durchf&uuml;hransicht\')">'.datum_strich_zu_punkt(sql_result($plaene,$i,'plan.datum'))." ".substr(sql_result($plaene,$i,'plan.startzeit'),0,-3)." (".sql_result($plaene,$i,'anzahl').")</a>";
						if (sql_result($plaene,$i,'plan.nachbereitung')==false) echo ' <a href="javascript:fenster(\''.$pfad.'formular/nachbereiten.php?plan='.sql_result($plaene,$i,"plan.id").'\',\'Nachbereitung\')">[nachbereiten]</a>';
						echo "<br />";
					} 
				} ?>
	  
      <?php if ($_GET["ansicht"]=="planung" and proofuser("plan", $_GET["plan"]))
		echo eintragung_plan($_GET["plan"]);
	
	if (($_GET["ansicht"]=="zweitansicht" or $_GET["ansicht"]=="druck") and proofuser("plan", $_GET["plan"])) {
				$checkboxen=db_conn_and_sql("SELECT * FROM `plan` WHERE `plan`.`id`=".injaway($_GET["plan"])); ?>
		<div class="nicht_drucken">
		<form action="<?php echo $pfad; ?>formular/plan_vorbereitet.php?ansicht=<?php echo $_GET["ansicht"]; ?>&amp;plan=<?php echo $_GET["plan"]; ?>&amp;fk=<?php echo $_GET["fk"]; ?>" method="post" accept-charset="ISO-8859-1">
			<a href="javascript:window.print();" title="drucken" class="icon"><img src="<?php echo $pfad; ?>icons/drucken.png" alt="drucken" /></a> | 
			<input type="checkbox" name="vorbereitet" value="1"<?php if(sql_result($checkboxen,0,"plan.vorbereitet")) echo ' checked="checked"' ?> /> vorbereitet
			<!-- | <input type="checkbox" name="material_da" value="1"<?php if(sql_result($checkboxen,0,"plan.material_da")) echo ' checked="checked"' ?> /> Material liegt vor |
			<input type="checkbox" name="gedruckt" value="1"<?php if(sql_result($checkboxen,0,"plan.gedruckt")) echo ' checked="checked"' ?> /> gedruckt-->
			<input type="submit" class="button" value="speichern" />
		</form>
		</div>
	<?php }
	
	if ($_GET["ansicht"]=="druck" and proofuser("plan", $_GET["plan"])) {
		$plan=planelemente(injaway($_GET["plan"]),"nicht bearbeiten",$pfad); ?>
		<div>
		<?php
		einzelstunde_druckansicht($plan, sql_result(db_conn_and_sql("SELECT * FROM benutzer WHERE id=".$_SESSION['user_id']), 0, "benutzer.druckansicht")); ?>
		</div>
      <?php }
	  
	if ($_GET["ansicht"]=="zweitansicht" and proofuser("plan", $_GET["plan"])) {
		$plan=planelemente($_GET["plan"],"nicht bearbeiten",$pfad); ?>
		<div>
		<?php
		einzelstunde_druckansicht($plan, sql_result(db_conn_and_sql("SELECT * FROM benutzer WHERE id=".$_SESSION['user_id']), 0, "benutzer.ansicht_2")); ?>
		</div>
		<?php }
		
	if ($_GET["ansicht"]=="hefter" and proofuser("plan", $_GET["plan"])) {
		$plan=planelemente($_GET["plan"],"nicht bearbeiten",$pfad); ?>
			<div class="nicht_drucken">
			<a href="index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk=<?php echo $_GET["fk"]; ?>&amp;plan=<?php echo $_GET["plan"]; ?>&amp;ansicht=hefter&amp;jahresansicht=true">Jahreshefter</a><br />
			</div>
		<?php
			if ($_GET["jahresansicht"]=="true" and proofuser("fach_klasse", $_GET["fk"])) {
				$plan_ids=db_conn_and_sql("SELECT `plan`.`id` FROM `plan`, `abschnittsplanung` where `abschnittsplanung`.`plan`=`plan`.`id` AND `plan`.`fach_klasse`=".injaway($_GET["fk"])." AND `plan`.`schuljahr`=".$plan["schuljahr"]." GROUP BY `plan`.`id` ORDER BY `plan`.`datum`");
				echo "<h4>".$plan["fachklassen_name"]."</h4>";
				for ($k=0;$k<sql_num_rows($plan_ids); $k++) {
					$plan=planelemente(sql_result($plan_ids,$k,"plan.id"),"nicht bearbeiten",$pfad);
					echo '<h5 style="text-align: right; color: grey; border-top: 1px solid grey;">'.$plan["datum"].'</h5>';
					for ($i=0;$i<count($plan['abschnitte']);$i++) if ($plan['abschnitte'][$i]['hefter']=="1" or $plan['abschnitte'][$i]['hefter']=="2") { ?>
						<div style="<?php if ($plan['abschnitte'][$i]['hefter']=="1") echo 'border-left: 3px solid darkred;'; else echo 'border-left: 3px solid lightblue;'; ?> padding-left: 7px;"><p><?php echo $plan['abschnitte'][$i]['inhalt']; ?></p></div>
					<?php }
				}
			}
			else {
				echo "<h4>".$plan["fachklassen_name"].": ".$plan["datum"]."</h4>"; ?>
				<h2>Merkteil</h2>
				<?php for ($i=0;$i<count($plan['abschnitte']);$i++) if ($plan['abschnitte'][$i]['hefter']=="1") { ?>
				<p><?php echo $plan['abschnitte'][$i]['inhalt']; ?></p>
				<?php } ?>
				<h2>&Uuml;bungsteil</h2>
				<?php for ($i=0;$i<count($plan['abschnitte']);$i++) if ($plan['abschnitte'][$i]['hefter']=="2") { ?>
				<p><?php echo $plan['abschnitte'][$i]['inhalt']; ?></p>
				<?php }
			}
		}
	} 
	}


   if ($_GET['auswahl']=='einzelstunden') { ?>

      <div class="tab_3">
            <?php $result=db_conn_and_sql("SELECT `klasse`.`einschuljahr`, `klasse`.`endung`, `faecher`.`kuerzel`, `schule`.`kuerzel`, `fach_klasse`.`id`, `fach_klasse`.`farbe`
                                 FROM `faecher`,`fach_klasse`,`klasse`,`schule`
                                 WHERE `fach_klasse`.`fach`=`faecher`.`id`
                                   AND `fach_klasse`.`anzeigen`=1
                                   AND `fach_klasse`.`user`=".$_SESSION['user_id']."
                                   AND `fach_klasse`.`klasse`=`klasse`.`id`
                                   AND `klasse`.`schule`=`schule`.`id`
                                 ORDER BY `klasse`.`einschuljahr` DESC,`faecher`.`kuerzel`");
        for($i=0;$i<@sql_num_rows ( $result );$i++) { ?>
          <a href="index.php?tab=stundenplanung&amp;auswahl=fkplan&amp;fk=<?php echo @sql_result ( $result, $i, 'fach_klasse.id' ); ?>"<?php if ($_GET["fk"]==sql_result($result,$i,'fach_klasse.id')) echo ' class="selected"'; ?>><?php echo html_umlaute(@sql_result ( $result, $i, 'faecher.kuerzel' ))." Kl. ".($aktuelles_jahr-@sql_result ( $result, $i, 'klasse.einschuljahr' )+1)." ".@sql_result ( $result, $i, 'klasse.endung' ); ?> <b style="background-color:#<?php echo html_umlaute(@sql_result ( $result, $i, 'fach_klasse.farbe' )); ?>;">&nbsp;&nbsp;&nbsp;</b></a>
        <?php } ?>
      </div>
	  <?php if (!isset($_GET["plan"]) and !isset($_GET["fk"])) { ?>
      <div class="navigation_3"><?php echo $navigation; ?></div>
	  deleted
      <?php } if (!isset($_GET["plan"]) and isset($_GET["fk"])) { ?>
	   deleted  
      <?php }
	
    }
	





	if ($_GET["auswahl"]=="hausaufgaben") { ?>
		<?php
      $result=db_conn_and_sql("SELECT *
                           FROM `fach_klasse`,`klasse`,`faecher`
                           WHERE `fach_klasse`.`klasse` = `klasse`.`id`
                             AND `fach_klasse`.`fach` = `faecher`.`id`
                             AND `fach_klasse`.`user`=".$_SESSION['user_id']."
                             AND `fach_klasse`.`anzeigen`=1
							ORDER BY `klasse`.`einschuljahr` DESC, `faecher`.`kuerzel`,`fach_klasse`.`gruppen_name`");
       ?>
	<div class="navigation_3"><?php echo $navigation; ?></div>
		<div class="inhalt">
		<?php
			$aktuelle_fk=$subject_classes->cont[$subject_classes->active]["id"];
			$maximale_eintraege=20;
			if ($_GET["ha_fk"]=="konkret") {
                db_conn_and_sql("UPDATE benutzer SET letzte_fachklasse=".$aktuelle_fk." WHERE id=".$_SESSION['user_id']);
                
				$db=new db;
				$jahr=$db->aktuelles_jahr();
				$schule=db_conn_and_sql("SELECT klasse.schule FROM klasse, fach_klasse WHERE klasse.id=fach_klasse.klasse AND fach_klasse.id=".$aktuelle_fk);
				$schule=sql_fetch_assoc($schule);
				$schule=$schule["schule"];
				$start_ende=schuljahr_start_ende($jahr, $schule);
				$schuljahresende=$start_ende["ende"];
				$schuljahresbeginn=$start_ende["start"];

				$tests=db_conn_and_sql("SELECT *, IF(`notenbeschreibung`.`datum` IS NULL,`plan`.`datum`,`notenbeschreibung`.`datum`) as `MyDatum`
					FROM `notenbeschreibung` LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
					WHERE `notenbeschreibung`.`fach_klasse`=".$aktuelle_fk."
						AND (`berichtigung` IS NOT NULL OR `unterschrift` IS NOT NULL)
					ORDER BY `MyDatum`");
				
				$hausaufgaben=db_conn_and_sql("SELECT * FROM `plan`,`hausaufgabe`
					WHERE `hausaufgabe`.`plan`=`plan`.`id` AND `plan`.`schuljahr`=".$aktuelles_jahr." AND `plan`.`fach_klasse`=".$aktuelle_fk."
					ORDER BY `hausaufgabe`.`abgabedatum`");
				$aufgaben=''; $i=0; $k=0;
				if (sql_num_rows($hausaufgaben)>0 or sql_num_rows($tests)>0)
				while ($i<sql_num_rows($hausaufgaben) or $k<sql_num_rows($tests)) {
					$nb_datum=@sql_result($tests,$k,"MyDatum");
					//echo '<br />'.sql_result($hausaufgaben,$i,"plan.datum").'<'.$nb_datum.'('.(sql_result($hausaufgaben,$i,"plan.datum")<$nb_datum).'): ';
					
					if (sql_num_rows($hausaufgaben)>0 and sql_num_rows($hausaufgaben)>$i
						and (sql_result($hausaufgaben,$i,"plan.datum")<$nb_datum or $nb_datum=="")
						and sql_result($hausaufgaben,$i,"plan.datum")!='') {
						$aufgaben[]=array(
							'typ'=>'hausaufgabe', 'id'=>sql_result($hausaufgaben,$i,"hausaufgabe.id"),
							'titel'=>html_umlaute(sql_result($hausaufgaben,$i,"hausaufgabe.bemerkung")),
							'datum'=>sql_result($hausaufgaben,$i,"plan.datum"),
							'fertig'=>sql_result($hausaufgaben,$i,"hausaufgabe.kontrolliert"),
							'link'=>$pfad.'formular/hausaufgaben.php?plan='.sql_result($hausaufgaben,$i,"plan.id").'&amp;block='.sql_result($hausaufgaben,$i,"plan.block_1").'&amp;hausaufgabe='.sql_result($hausaufgaben,$i,"hausaufgabe.id"),
							'link_anzahl'=>$pfad.'formular/vergessen_anzahl.php?hausaufgabe='.sql_result($hausaufgaben,$i,"hausaufgabe.id").'&amp;fk='.$aktuelle_fk);
						$i++;
					}
					else {
						if ($schuljahresbeginn<=$nb_datum and $schuljahresende>=$test_datum) {
						$aufgaben[]=array(
							'typ'=>'test',
							'id'=>sql_result($tests,$k,"notenbeschreibung.id"),
							'titel'=>html_umlaute(sql_result($tests,$k,"notenbeschreibung.beschreibung")),
							'datum'=>$nb_datum,
							'fertig'=>0,
							'berichtigung_erforderlich'=>sql_result($tests,$k,"notenbeschreibung.berichtigung"),
							'unterschrift_erforderlich'=>sql_result($tests,$k,"notenbeschreibung.unterschrift"),
							'link'=>$pfad.'formular/noten_bearbeiten.php?beschreibung='.sql_result($tests,$k,"notenbeschreibung.id"),
							'link_anzahl'=>$pfad.'formular/vergessen_anzahl.php?notenbeschreibung='.sql_result($tests,$k,"notenbeschreibung.id").'&amp;fk='.$aktuelle_fk);
						if ((sql_result($tests,$k,"notenbeschreibung.berichtigung")==NULL or sql_result($tests,$k,"notenbeschreibung.berichtigung")==1)
							and (sql_result($tests,$k,"notenbeschreibung.unterschrift")==NULL or sql_result($tests,$k,"notenbeschreibung.unterschrift")==1)) $aufgaben[count($aufgaben)-1]['fertig']=1;
						}
						$k++;
					}
					//echo $aufgaben[count($aufgaben)-1]['datum'].' $i='.$i.' $k='.$k;
				}
				
				if ($aufgaben!='') {
					$start_aufgabe=0;
					if (count($aufgaben)>$maximale_eintraege and $_GET["anzeigen"]!="alle") {
						echo '<p><span class="hinweis">Es werden lediglich die letzten '.$maximale_eintraege.' Eintr&auml;ge angezeigt. <a href="'.$pfad.'index.php?tab=stundenplanung&amp;auswahl=hausaufgaben&amp;ha_fk=konkret&amp;anzeigen=alle">[Alle anzeigen]</a></span></p>';
						$start_aufgabe=count($aufgaben)-$maximale_eintraege;
					}
					echo '<table class="tabelle"><tr><th>Datum</th>';
					for ($i=$start_aufgabe;$i<count($aufgaben);$i++) echo '<td title="'.$aufgaben[$i]['titel'].'">'.substr($aufgaben[$i]['datum'],8,2).'.<br />'.$monatsnamen_kurz[substr($aufgaben[$i]['datum'],5,2)+0].'</td>';
					echo '</tr><tr><th>HA/Note</th>';
					for ($i=$start_aufgabe;$i<count($aufgaben);$i++) { echo '<td><a href="'.$aufgaben[$i]['link'].'" onclick="fenster(this.href,\'Bearbeiten\'); return false;" class="icon" title="'.$aufgaben[$i]['titel'].'"><img src="'.$pfad.'icons/'; if ($aufgaben[$i]['typ']=='hausaufgabe') echo 'hausaufgaben'; else echo 'zensur'; echo '.png" alt="bearbeiten" /></a><br />
						<img src="'.$pfad.'icons/'; if ($aufgaben[$i]['fertig']==1) echo 'haekchen.png" alt="haekchen"'; else echo 'abhaken.png" alt="kein haekchen"'; echo ' title="fertig?" /></td>'; }
					echo '</tr><tr><th class="nicht_drucken">Anzahl</th>';
					for ($i=$start_aufgabe;$i<count($aufgaben);$i++) echo '<td class="nicht_drucken"><a href="'.$aufgaben[$i]['link_anzahl'].'" onclick="fenster(this.href,\'Anzahl &auml;ndern\'); return false;" class="icon" title="Anzahl &auml;ndern"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a></td>';
					echo '</tr>';
					
                    $schueler = db_conn_and_sql("SELECT *
                        FROM schueler, gruppe
                        WHERE gruppe.fach_klasse=".$aktuelle_fk."
                            AND schueler.aktiv=1
                            AND gruppe.schueler=schueler.id
                        ORDER BY schueler.position, schueler.name,schueler.vorname");
                    if (sql_num_rows($schueler)<1)
                        $schueler = db_conn_and_sql ('SELECT *
                            FROM `fach_klasse`, `schueler`
                            WHERE `schueler`.`klasse`=`fach_klasse`.`klasse`
                                AND `fach_klasse`.`id`='.$aktuelle_fk.'
                                AND `schueler`.`aktiv`=1
                            ORDER BY `schueler`.`position`, `schueler`.`name`,`schueler`.`vorname`');
					for ($s=0;$s<sql_num_rows($schueler);$s++) if (gehoert_zur_gruppe(injaway($aktuelle_fk),sql_result($schueler,$s,"schueler.id"))) {
						echo '<tr><td>'.html_umlaute(sql_result($schueler,$s,"schueler.vorname")).' '.html_umlaute(substr(sql_result($schueler,$s,"schueler.name"),0,1)).'.</td>';
						for ($i=$start_aufgabe;$i<count($aufgaben);$i++) {
							echo '<td>';
							if ($aufgaben[$i]['typ']=='hausaufgabe') {
								$einzel_HA=db_conn_and_sql("SELECT * FROM `hausaufgabe_vergessen` WHERE `hausaufgabe`=".$aufgaben[$i]['id']." AND `schueler`=".sql_result($schueler,$s,"schueler.id"));
								if (sql_num_rows($einzel_HA)>0)
									echo sql_result($einzel_HA,0,"hausaufgabe_vergessen.anzahl");
							}
							else {
								$einzel_test=db_conn_and_sql("SELECT * FROM `berichtigung_vergessen` WHERE `notenbeschreibung`=".$aufgaben[$i]['id']." AND `schueler`=".sql_result($schueler,$s,"schueler.id"));
								if (sql_num_rows($einzel_test)>0) {
									if ($aufgaben[$i]['berichtigung_erforderlich']!="") echo sql_result($einzel_test,0,"berichtigung_vergessen.berichtigung_anzahl");
									echo ' ';
									if ($aufgaben[$i]['unterschrift_erforderlich']!="") echo sql_result($einzel_test,0,"berichtigung_vergessen.unterschrift_anzahl");
								}
							}
							echo '</td>';
						}
						echo '</tr>';
					}
					echo '</table>';
				}
				else echo 'Bisher keine Hausaufgaben / Berichtigungen aufgegeben.';
			}
			else {
				echo '<span class="hinweis">Um alle Hausaufgaben einer Fach-Klasse-Kombination anzuzeigen, w&auml;hlen Sie oben die gew&uuml;nschte Fach-Klassen-Kombination aus und klicken Sie <a href="index.php?tab=stundenplanung&auswahl=hausaufgaben&ha_fk=konkret">hier</a>.</span>';
				$tests=db_conn_and_sql("SELECT *, IF(`notenbeschreibung`.`datum` IS NULL,`plan`.`datum`,`notenbeschreibung`.`datum`) as `MyDatum`
					FROM `fach_klasse`,`faecher`,`klasse`, `notenbeschreibung` LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
					WHERE  `fach_klasse`.`fach`=`faecher`.`id`
						AND `fach_klasse`.`user`=".$_SESSION['user_id']."
						AND `fach_klasse`.`klasse`=`klasse`.`id`
						AND `notenbeschreibung`.`fach_klasse`=`fach_klasse`.`id`
						AND (`berichtigung` <>1 OR `unterschrift` <>1)
						AND (`berichtigung` IS NOT NULL OR `unterschrift` IS NOT NULL)
					ORDER BY `MyDatum`");
				
				$hausaufgaben=db_conn_and_sql("SELECT * FROM `fach_klasse`,`faecher`,`klasse`, `plan`,`hausaufgabe`
					WHERE  `fach_klasse`.`fach`=`faecher`.`id`
						AND `fach_klasse`.`user`=".$_SESSION['user_id']."
						AND `fach_klasse`.`klasse`=`klasse`.`id`
						AND `plan`.`fach_klasse`=`fach_klasse`.`id`
						AND `hausaufgabe`.`plan`=`plan`.`id`
						AND `plan`.`schuljahr`=".$aktuelles_jahr."
						AND `hausaufgabe`.`kontrolliert`<>1
					ORDER BY `hausaufgabe`.`abgabedatum`");
				if (@sql_num_rows($tests)>0) {
					/*
					// Das braeuchte ich, wenn ein Test nach Schuljahreswechsel nicht mehr angezeigt werden soll, aber das ist ja gar nicht erwÃŒnscht (wenn doch, noch anpassen)
					$schuljahresende=db_conn_and_sql("SELECT * FROM schuljahr WHERE schuljahr.jahr=".$aktuelles_jahr);
					$schuljahresbeginn=sql_result($schuljahresende,0,"schuljahr.beginn");
					$schuljahresende=sql_result($schuljahresende,0,"schuljahr.ende"); */
					
					if ($schuljahresbeginn<=$nb_datum and $schuljahresende>=$test_datum)
					echo '<h4>Anstehende Berichtigung-/Unterschrift-Kontrollen:</h4><ul>';
					for ($i=0;$i<sql_num_rows($tests);$i++) {
                        $mein_testdatum=sql_result($tests,$i,"notenbeschreibung.datum");
                        if ($mein_testdatum=="") $mein_testdatum=sql_result($tests,$i,"plan.datum");
                        echo '<li>'.datum_strich_zu_punkt($mein_testdatum).' ('.$subject_classes->nach_ids[sql_result($tests,$i,"fach_klasse.id")]["farbanzeige"].') '.html_umlaute(sql_result($tests,$i,"notenbeschreibung.beschreibung")).' <a href="'.$pfad.'formular/vergessen_anzahl.php?notenbeschreibung='.sql_result($tests,$i,"notenbeschreibung.id").'&amp;fk='.sql_result($tests,$i,"fach_klasse.id").'" onclick="fenster(this.href, \'bearbeiten\'); return false;" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a></li>';
                    }
					echo '</ul>';
				}
				if (@sql_num_rows($hausaufgaben)>0) {
					echo '<h4>Anstehende Hausaufgaben-Kontrollen:</h4><ul>';
					for ($i=0;$i<sql_num_rows($hausaufgaben);$i++)
                        echo '<li>'.datum_strich_zu_punkt(sql_result($hausaufgaben,$i,"plan.datum")).' ('.$subject_classes->nach_ids[sql_result($hausaufgaben,$i,"fach_klasse.id")]["farbanzeige"].') '.html_umlaute(sql_result($hausaufgaben,$i,"hausaufgabe.bemerkung")).' <a href="'.$pfad.'formular/vergessen_anzahl.php?hausaufgabe='.sql_result($hausaufgaben,$i,"hausaufgabe.id").'&amp;fk='.sql_result($hausaufgaben,$i,"fach_klasse.id").'" onclick="fenster(this.href, \'bearbeiten\'); return false;" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a></li>';
					echo '</ul>';
				}
			}
	} 

	
	
	
	
	if ($_GET["auswahl"]=="planstatistik") {
		$result=db_conn_and_sql("SELECT *
                           FROM `fach_klasse`,`klasse`,`faecher`
                           WHERE `fach_klasse`.`klasse` = `klasse`.`id`
                             AND `fach_klasse`.`fach` = `faecher`.`id`
                             AND `fach_klasse`.`anzeigen`=1
                             AND `fach_klasse`.`user`=".$_SESSION['user_id']."
							ORDER BY `klasse`.`einschuljahr` DESC, `faecher`.`kuerzel`,`fach_klasse`.`gruppen_name`");
       ?>
      <div class="tab_3">
      <?php for ($i=0;$i<sql_num_rows($result);$i++) { ?>
        <a href="index.php?tab=stundenplanung&amp;auswahl=planstatistik&amp;fk=<?php echo sql_result($result,$i,'fach_klasse.id'); ?>"<?php if ($_GET["fk"]==sql_result($result,$i,'fach_klasse.id')) echo ' class="selected"'; ?>><span style="background-color:#<?php echo html_umlaute(@sql_result ( $result, $i, 'fach_klasse.farbe' )); ?>;"><?php echo html_umlaute(sql_result($result,$i,'faecher.kuerzel'))." ".($aktuelles_jahr-sql_result($result,$i,'klasse.einschuljahr')+1)." ".sql_result($result,$i,'klasse.endung')." ".html_umlaute(sql_result($result,$i,'fach_klasse.gruppen_name')); ?></span></a>
      <?php } ?>
		<a href="index.php?tab=stundenplanung&amp;auswahl=planstatistik"<?php if ($_GET["fk"]=="") echo ' class="selected"'; ?>>aktive Fach-Klassen</a>
      </div>

	<div class="navigation_3"><?php echo $navigation; ?></div>
		<div class="inhalt">
		<?php
			$fks=db_conn_and_sql("SELECT *
				FROM `fach_klasse`, `faecher`,`klasse`
				WHERE `fach_klasse`.`fach`=`faecher`.`id`
					AND `fach_klasse`.`user`=".$_SESSION['user_id']."
					AND `fach_klasse`.`klasse`=`klasse`.`id`
					AND `fach_klasse`.`anzeigen`=1
				ORDER BY `faecher`.`kuerzel`, `klasse`.`einschuljahr` DESC, `klasse`.`endung`");
		
		function balken ($zahl, $zusatztitel) {
			$hoehe=2;
			$faktor=10;
			if ($zahl<0)
				$rueckgabe_text='<div style="clear: both; float: left; width: '.($faktor*($zahl+3)).'px; height: '.$hoehe.'px;"></div><div style="float: left; width: '.(-$faktor*($zahl)).'px; height: '.$hoehe.'px; background-color: red;" title="'.$zahl.' '.$zusatztitel.'"></div>';
			else $rueckgabe_text='<div style="clear: both; float: left; width: '.($faktor*3).'px; height: '.$hoehe.'px;"></div><div style="float: left; width: '.($faktor*($zahl)).'px; height: '.$hoehe.'px; background-color: green;" title="'.$zahl.' '.$zusatztitel.'"></div>';
			return $rueckgabe_text;
		}
		
		if ($_GET["new_sc"]>0) {
			$einfueger=' AND `fach_klasse`.`id`='.injaway($_GET["new_sc"]).' ';
            //db_conn_and_sql("UPDATE benutzer SET letzte_fachklasse=".injaway($_GET["fk"])." WHERE id=".$_SESSION['user_id']);
        }
		$auswertung=db_conn_and_sql("SELECT `plan`.`datum`, `plan`.`fach_klasse`,`plan_auswertung`.*
			FROM `plan_auswertung`,`plan`, `fach_klasse`
			WHERE `plan_auswertung`.`plan`=`plan`.`id`
				AND `fach_klasse`.`user`=".$_SESSION['user_id']."
				AND `plan`.`fach_klasse`=`fach_klasse`.`id`
				AND `fach_klasse`.`anzeigen`=1".$einfueger."
			ORDER BY `plan`.`datum` DESC");
		$auswertungsarray='';
		$auswertungsueberschriften=array('Gesamt-<br />eindruck', 'Selbst-<br />einsch&auml;tzung', 'Lern-<br />einsch&auml;tzung', 'Angst-<br />faktor', 'Lehrer-<br />sprache', 'Methode', 'Stoffbe-<br />w&auml;ltigung', 'Lob-<br />vergabe', 'Interesse<br />Motivation', 'Lern-<br />tempo', 'Gesamt');
		$gesamtbewertung='';
		for($i=0;$i<sql_num_rows($auswertung);$i++) {
			$auswertungsarray[]=array(
				'datum'=>sql_result($auswertung, $i, 'plan.datum'),
				'fach_klasse'=>sql_result($auswertung, $i, 'plan.fach_klasse'),
				'plan'=>sql_result($auswertung, $i, 'plan_auswertung.plan'),
				'bewertung'=>array(
					sql_result($auswertung, $i, 'plan_auswertung.gesamteindruck'),
					sql_result($auswertung, $i, 'plan_auswertung.selbsteinschaetzung'),
					sql_result($auswertung, $i, 'plan_auswertung.lerneinschaetzung'),
					sql_result($auswertung, $i, 'plan_auswertung.angstfaktor'),
					sql_result($auswertung, $i, 'plan_auswertung.lehrersprache'),
					sql_result($auswertung, $i, 'plan_auswertung.methode'),
					sql_result($auswertung, $i, 'plan_auswertung.stoffbewaeltigung'),
					sql_result($auswertung, $i, 'plan_auswertung.lob_geben'),
					sql_result($auswertung, $i, 'plan_auswertung.interesse'),
					sql_result($auswertung, $i, 'plan_auswertung.lerntempo'),
					(sql_result($auswertung, $i, 'plan_auswertung.selbsteinschaetzung')+sql_result($auswertung, $i, 'plan_auswertung.lerneinschaetzung')+sql_result($auswertung, $i, 'plan_auswertung.angstfaktor')+sql_result($auswertung, $i, 'plan_auswertung.lehrersprache')+sql_result($auswertung, $i, 'plan_auswertung.methode')+sql_result($auswertung, $i, 'plan_auswertung.stoffbewaeltigung')+sql_result($auswertung, $i, 'plan_auswertung.lob_geben')+sql_result($auswertung, $i, 'plan_auswertung.interesse')+sql_result($auswertung, $i, 'plan_auswertung.lerntempo'))/5
				)
			);
			if (sql_result($auswertung, $i, 'plan_auswertung.gesamteindruck')!="") { $gesamtbewertung[0]['wert']+=sql_result($auswertung, $i, 'plan_auswertung.gesamteindruck'); $gesamtbewertung[0]['anzahl']++; }
			if (sql_result($auswertung, $i, 'plan_auswertung.selbsteinschaetzung')!="") { $gesamtbewertung[1]['wert']+=sql_result($auswertung, $i, 'plan_auswertung.selbsteinschaetzung'); $gesamtbewertung[1]['anzahl']++; }
			if (sql_result($auswertung, $i, 'plan_auswertung.lerneinschaetzung')!="") { $gesamtbewertung[2]['wert']+=sql_result($auswertung, $i, 'plan_auswertung.lerneinschaetzung'); $gesamtbewertung[2]['anzahl']++; }
			if (sql_result($auswertung, $i, 'plan_auswertung.angstfaktor')!="") { $gesamtbewertung[3]['wert']+=sql_result($auswertung, $i, 'plan_auswertung.angstfaktor'); $gesamtbewertung[3]['anzahl']++; }
			if (sql_result($auswertung, $i, 'plan_auswertung.lehrersprache')!="") { $gesamtbewertung[4]['wert']+=sql_result($auswertung, $i, 'plan_auswertung.lehrersprache'); $gesamtbewertung[4]['anzahl']++; }
			if (sql_result($auswertung, $i, 'plan_auswertung.methode')!="") { $gesamtbewertung[5]['wert']+=sql_result($auswertung, $i, 'plan_auswertung.methode'); $gesamtbewertung[5]['anzahl']++; }
			if (sql_result($auswertung, $i, 'plan_auswertung.stoffbewaeltigung')!="") { $gesamtbewertung[6]['wert']+=sql_result($auswertung, $i, 'plan_auswertung.stoffbewaeltigung'); $gesamtbewertung[6]['anzahl']++; }
			if (sql_result($auswertung, $i, 'plan_auswertung.lob_geben')!="") { $gesamtbewertung[7]['wert']+=sql_result($auswertung, $i, 'plan_auswertung.lob_geben'); $gesamtbewertung[7]['anzahl']++; }
			if (sql_result($auswertung, $i, 'plan_auswertung.interesse')!="") { $gesamtbewertung[8]['wert']+=sql_result($auswertung, $i, 'plan_auswertung.interesse'); $gesamtbewertung[8]['anzahl']++; }
			if (sql_result($auswertung, $i, 'plan_auswertung.lerntempo')!="") { $gesamtbewertung[9]['wert']+=sql_result($auswertung, $i, 'plan_auswertung.lerntempo'); $gesamtbewertung[9]['anzahl']++; }
			$gesamtbewertung[10]['wert']+=0+sql_result($auswertung, $i, 'plan_auswertung.selbsteinschaetzung')+sql_result($auswertung, $i, 'plan_auswertung.lerneinschaetzung')+sql_result($auswertung, $i, 'plan_auswertung.angstfaktor')+sql_result($auswertung, $i, 'plan_auswertung.lehrersprache')+sql_result($auswertung, $i, 'plan_auswertung.methode')+sql_result($auswertung, $i, 'plan_auswertung.stoffbewaeltigung')+sql_result($auswertung, $i, 'plan_auswertung.lob_geben')+sql_result($auswertung, $i, 'plan_auswertung.interesse')+sql_result($auswertung, $i, 'plan_auswertung.lerntempo'); $gesamtbewertung[10]['anzahl']++;
		}
		
		if (sql_num_rows($auswertung)>0) {
			echo '<table class="tabelle" cellspacing="0"><tr>';
			
			for($i=0;$i<count($auswertungsueberschriften);$i++) {
				echo '<th style="width: ';
				if ($i==0 or $i==count($auswertungsueberschriften)-1) echo 120; else echo 80;
				echo 'px;">'.$auswertungsueberschriften[$i];
				if ($gesamtbewertung[$i]['anzahl']>0) echo '<br />'.number_format($gesamtbewertung[$i]['wert']/$gesamtbewertung[$i]['anzahl'], 2, ',', '.');
				echo '</th>';
			}
			echo '</tr>
			<tr>';
			for ($k=0;$k<count($auswertungsarray[0]['bewertung']);$k++) {
				echo '<td>';
				for($i=0;$i<count($auswertungsarray);$i++) {
					echo balken($auswertungsarray[$i]['bewertung'][$k], datum_strich_zu_punkt($auswertungsarray[$i]['datum']));
				}
				echo '</td>
				';
			}
			echo '</tr></table>';
		}
		else echo 'Es liegen keine Daten vor. F&uuml;hren Sie eine Unterrichtsstunden-Nachbereitung durch und beantworten Sie die Auswertungsfragen, damit eine Statistik erstellt werden kann.';
		?>
	<?php }
	echo '</div>';
	}
	
	
	// ------------------------------------ Verwaltung --------------------------------------------------------------
	if ($_GET['tab']=='kontakte') {
		echo '<div class="navigation_3">'.$navigation.'</div>
			<div class="inhalt">';
		$user=new user();
		$schule=$user->my["letzte_schule"];
		$formularziel="index.php?tab=kontakte";
		include $pfad."formular/personen.php";
		echo '</div>';
	}
	
	if ($_GET['tab']=='schueler') {
		echo '<div class="navigation_3">'.$navigation.'</div>
			<div class="inhalt">';
		$formularziel="index.php?tab=schueler";
		include $pfad."formular/schueler_bearbeiten_verwaltung.php";
		echo '</div>';
	}
	
	
	// ------------------------------------ Admin --------------------------------------------------------------
	if ($_GET['tab']=='angestellte') {
		echo '<div class="navigation_3">'.$navigation.'</div>
			<div class="inhalt">';
		$formularziel="index.php?tab=angestellte";
		include $pfad."formular/benutzerverwaltung.php";
		echo '</div>';
	}

	if ($_GET['tab']=='lav') {
		echo '<div class="navigation_3">'.$navigation.'</div>
			<div class="inhalt">';
		$formularziel="index.php?tab=lav";
		include $pfad."formular/lehrauftragsverteilung.php";
		echo '</div>';
	}

	if ($_GET['tab']=='stichnoten') {
		echo '<div class="navigation_3">'.$navigation.'</div>
			<div class="inhalt">';
		$formularziel="index.php?tab=stichnoten";
		include $pfad."formular/stichtagsnoten.php";
		echo '</div>';
	}

	if ($_GET['tab']=='kopfnoten') {
		echo '<div class="navigation_3">'.$navigation.'</div>
			<div class="inhalt">';
		$formularziel="index.php?tab=kopfnoten";
		include $pfad."formular/kopfnoten.php";
		echo '</div>';
	}

	
	
	
	
	
	
    
    if ($_GET['tab']=='einstellungen') { ?>
      <div class="tab_2">
        <a href="index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr=<?php echo $aktuelles_jahr; ?>"<?php if ($_GET["auswahl"]=="schuljahr") echo ' class="selected"'; ?>>Schuljahresdaten</a>
        <a href="index.php?tab=einstellungen&amp;auswahl=faecher"<?php if ($_GET["auswahl"]=="faecher") echo ' class="selected"'; ?>>F&auml;cher</a>
        <a href="index.php?tab=einstellungen&amp;auswahl=schulen&amp;erstellen=raum"<?php if ($_GET["auswahl"]=="schulen") echo ' class="selected"'; ?>>Schulen</a>
        <a href="index.php?tab=einstellungen&amp;auswahl=noten_bew"<?php if ($_GET["auswahl"]=="noten_bew") echo ' class="selected"'; ?>>Zensuren / Bewertung</a>
        <a href="index.php?tab=einstellungen&amp;auswahl=sitzplan"<?php if ($_GET["auswahl"]=="sitzplan") echo ' class="selected"'; ?>>Sitzordnungen</a>
        <a href="index.php?tab=einstellungen&amp;auswahl=allgemein"<?php if ($_GET["auswahl"]=="allgemein") echo ' class="selected"'; ?>>Unterricht</a>
        <a href="index.php?tab=einstellungen&amp;auswahl=programm"<?php if ($_GET["auswahl"]=="programm") echo ' class="selected"'; ?>>Programm</a>
      </div>
	<?php if ($_GET['auswahl']=='schuljahr') { ?>
      <div class="tab_3">
		<a href="index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr=allgemein"<?php if ($_GET["jahr"]=='allgemein') echo ' class="selected"'; ?>>Allgemeine Einstellungen</a>
        <?php
          for ($i=date("Y")-1;$i<date("Y")+2;$i++) { ?>
            <a href="index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr=<?php echo $i; ?>"<?php if ($_GET["jahr"]==$i) echo ' class="selected"'; ?>><?php echo $i.'/'.($i+1); ?></a> <?php }
            $result = db_conn_and_sql ( 'SELECT *
				FROM `benutzer`, `schuljahr` LEFT JOIN `schule_user` ON `schuljahr`.`schule`=`schule_user`.`schule` AND `schule_user`.`user`='.$_SESSION['user_id'].'
				WHERE `benutzer`.`bundesland`=`schuljahr`.`bundesland`
					AND `benutzer`.`id`='.$_SESSION['user_id'].'
					AND `schuljahr`.`jahr` ='.injaway($_GET['jahr']) );
        ?>
		</div>
	<?php if($_GET["jahr"]>2000) { ?>
      <div class="navigation_3">
		<a href="index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr=<?php echo $_GET["jahr"]-1; ?>" class="auswahl"><?php echo ($_GET["jahr"]-1); ?></a>
		<a href="index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr=<?php echo $_GET["jahr"]+1; ?>" class="auswahl"><?php echo ($_GET["jahr"]+1); ?></a>
		<?php echo $navigation;
		$user=new user();
		$schule=$user->my["letzte_schule"];
		$schuljahr_row=db_conn_and_sql("SELECT * FROM schuljahr WHERE jahr=".$_GET["jahr"]." AND schule=".$schule);
		
		$start_ende=schuljahr_start_ende(injaway($_GET["jahr"]), $schule);
		
		function datum_strich_zu_javascript($datum) {
			$datum_einzeln=explode("-", $datum);
			// Monat in Javascript immer verkehrt und GmT erfordert mind. +2 h
			return "new Date(".$datum_einzeln[0].", ".($datum_einzeln[1]-1).", ".$datum_einzeln[2].", 4, 0, 0)";
		}
		
		?></div>
	  <div class="inhalt">
	  <fieldset>
		<legend>Gesamtschuljahr <?php echo $_GET['jahr'].' / '.($_GET['jahr']+1); ?></legend>
      <form action="<?php echo $pfad; ?>formular/schuljahr.php" method="post" accept-charset="ISO-8859-1">
        <input type="hidden" name="schuljahr" value="<?php echo $_GET['jahr']; ?>" />
		<input type="hidden" name="schule" value="<?php echo $schule; ?>" />
        <script>
            $(function() {
				$("#hjw").datepicker("option", "minDate", new Date('<?php echo $start_ende["start"]; ?>'));
				$("#hjw").datepicker("option", "maxDate", new Date('<?php echo $start_ende["ende"]; ?>'));
            });
        </script>
        
		<label for="bundesland" style="width: 150px;">Bundesland:</label>
		<select name="bundesland"><?php
			$bundesland_vorauswahl=db_conn_and_sql("SELECT bundesland FROM schule WHERE id=".$schule);
			$bundesland_vorauswahl=sql_fetch_assoc($bundesland_vorauswahl);
			$bundesland_vorauswahl=$bundesland_vorauswahl["bundesland"];
			$bundesland_row=db_conn_and_sql("SELECT bundesland FROM ferien
				WHERE welche=0 AND schuljahr=".$_GET["jahr"]);
			while ($my_bundesland=sql_fetch_assoc($bundesland_row)) {
					echo '<option value="'.$my_bundesland["bundesland"].'"';
					if ($bundesland_vorauswahl==$my_bundesland["bundesland"]) echo ' selected="selected"';
					echo '>'.html_umlaute($bundesland[$my_bundesland["bundesland"]]["name"]).'</option>';
				} ?></select>
		<a href="<?php echo $pfad; ?>formular/ferien_von_bundesland.php?jahr=<?php echo $_GET["jahr"]; ?>&amp;bundesland=<?php echo $bundesland_vorauswahl; ?>" onclick="fenster(this.href, 'Titel unwichtig'); return false;">Ferien des Bundeslands eintragen</a>
        <br />
        <label for="hjw" style="width: 150px">Halbjahreswechsel:</label>
        <input type="text" class="datepicker" id="hjw" name="hjw" size="8" maxlength="10" value="<?php
			$my_schuljahr=sql_fetch_assoc($schuljahr_row);
			echo datum_strich_zu_punkt($my_schuljahr["halbjahreswechsel"]); ?>" />
        <br />
        <br />
        
		<div class="tooltip" id="tt_fehltage">
            Wenn Sie kein Klassenlehrer sind (und deshalb auch keine Fehlzeiten f&uuml;r Ihre Sch&uuml;ler eintragen m&uuml;ssen) ist diese Einstellung f&uuml;r Sie uninteressant.
            Ansonsten gehen Sie wie folgt vor:
            <ul><li>Ist der zus&auml;tzliche freie Tag ein schulfreier Tag, an dem es f&uuml;r die Schule unwichtig ist, ob ein Sch&uuml;ler entschuldigt oder unentschuldigt fehlt, lassen Sie das H&auml;kchen frei.</li>
            <li>Ist der zus&auml;tzliche freie Tag ein Tag, an dem Fehlzeiten trotzdem notiert werden (z.B. bei Sportfesten oder Wandertagen), setzen Sie hier das H&auml;kchen.</li>
            </ul>
		</div>
		<fieldset><legend>Zus&auml;tzliche unterrichtsfreie Tage</legend>
        <table>
          <tr>
            <th>Bezeichnung<em>*</em></th>
            <th>Datum<em>*</em></th>
            <th>Datum bis</th>
			<th>Fehltage mitrechnen <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_fehltage')" onmouseout="hideWMTT()" /></th>
          </tr>
          <?php
             $result = db_conn_and_sql ( 'SELECT *
				FROM `bewegliche_feiertage`, `schule_user`
				WHERE `schule_user`.`schule`=`bewegliche_feiertage`.`schule`
					AND `schule_user`.`schule`='.$schule.'
					AND `schule_user`.`user`='.$_SESSION['user_id'].'
					AND `schuljahr`='.injaway($_GET["jahr"]).' ORDER BY `von`' );
             for ($i=0;$i<sql_num_rows($result)+2;$i++) { ?>
        <script>
            $(function() {
                var append_to_regional = $.datepicker.regional['de'];
                append_to_regional.showOtherMonths=true;
                append_to_regional.selectOtherMonths=true;
                append_to_regional.onSelect=function(selectedDate) {
                    var option = this.id == "frei_datum_<?php echo $i; ?>" ? "minDate" : "maxDate",
                        instance = $(this).data("datepicker"),
                        date = $.datepicker.parseDate(
                            instance.settings.dateFormat ||
                            $.datepicker._defaults.dateFormat,
                            selectedDate, instance.settings );
                    fehlzeiten_dates.not(this).datepicker( "option", option, date );
                };
                $.datepicker.setDefaults($.datepicker.regional['']);
                var fehlzeiten_dates = $("#frei_datum_<?php echo $i; ?>, #frei_bis_<?php echo $i; ?>").datepicker(append_to_regional);
            });
        </script>
          <tr>
            <td><input type="hidden" name="frei_belegt_<?php echo $i; ?>" value="<?php echo sql_result ( $result, $i, 'bewegliche_feiertage.id' ); ?>" />
				<input type="text" name="frei_name_<?php echo $i; ?>" size="20" maxlength="30" value="<?php echo html_umlaute(sql_result ( $result, $i, 'bewegliche_feiertage.beschreibung' )); ?>" /></td>
            <td><input type="text" class="datepicker" id="frei_datum_<?php echo $i; ?>" name="frei_datum_<?php echo $i; ?>" size="8" maxlength="10" value="<?php echo datum_strich_zu_punkt(sql_result ( $result, $i, 'bewegliche_feiertage.von' )); ?>" /></td>
            <td><input type="text" class="datepicker" id="frei_bis_<?php echo $i; ?>" name="frei_bis_<?php echo $i; ?>" size="8" maxlength="10" value="<?php echo datum_strich_zu_punkt(sql_result ( $result, $i, 'bewegliche_feiertage.bis' )); ?>" /></td>
			<td style="text-align: center;"><input type="checkbox" name="fehltage_<?php echo $i; ?>"<?php if (sql_result ( $result, $i, 'bewegliche_feiertage.fehltage' )) echo ' checked="checked"'; ?> value="1" />
				<?php if ($i<sql_num_rows($result) and userrigths("schuljahresdaten", sql_result ( $result, $i, 'bewegliche_feiertage.schule' ))==2) { ?>
					<a href="<?php echo $pfad; ?>formular/ferien_loeschen.php?was=schulfrei&amp;tag=<?php echo sql_result ( $result, $i, 'bewegliche_feiertage.id' ); ?>&amp;schuljahr=<?php echo $_GET['jahr']; ?>" title="l&ouml;schen" class="icon"><img src="<?php echo $pfad; ?>icons/delete.png" alt="del" /></a>
				<?php } ?>
			</td>
          </tr>
          <?php } ?>
        </table>
		</fieldset><br />
        <button style="float: right;" onclick="auswertung=new Array();
        				i=0; while (document.getElementById('frei_datum_'+i)) {
						if (document.getElementById('frei_datum_'+i).value!='') {
							auswertung.push(new Array(0, 'frei_name_'+i,'nicht_leer'), new Array(0, 'frei_datum_'+i,'datum','<?php echo ($_GET["jahr"]); ?>-01-01','<?php echo ($_GET["jahr"]+1); ?>-12-31'));
							if (document.getElementById('frei_bis_'+i).value!='') auswertung.push(new Array(0, 'frei_bis_'+i,'datum','<?php echo ($_GET["jahr"]); ?>-01-01','<?php echo ($_GET["jahr"]+1); ?>-12-31'));
						}
						i++;
					}
			pruefe_formular(auswertung); return false;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
      </form>
	</fieldset>  
	<?php }
	else { ?>
      <div class="navigation_3"><?php echo $navigation; ?></div>
	  <div class="inhalt">
		<fieldset>
		<legend>Ber&uuml;cksichtigung fester Feiertage</legend>
      <form action="<?php echo $pfad; ?>formular/allgemein.php" method="post" accept-charset="ISO-8859-1">
		<label for="schule">Feiertage von:</label>
		<select name="schule" onchange="javascript:window.location.href = '<?php echo $pfad; ?>index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr=allgemein&amp;schule='+this.value">
		<?php
			$schulresult=db_conn_and_sql("SELECT * FROM schule, schule_user WHERE schule.id=schule_user.schule AND schule_user.aktiv=1 AND schule_user.user=".$_SESSION["user_id"]." ORDER BY schule.id DESC");
			$schule=sql_result($schulresult,0,"schule.id");
			for ($i=0;$i<sql_num_rows($schulresult);$i++) {
				echo '<option value="'.sql_result($schulresult,$i,"schule.id").'"';
				if (sql_result($schulresult,$i,"schule.id")==$_GET["schule"]) {
					echo ' selected="selected"';
					$schule=$_GET["schule"];
				}
				echo '>'.sql_result($schulresult,$i,"schule.kuerzel").'</option>';
			}
		?>
		</select>
        <table>
          <tr>
            <th>Feiertag</th>
            <th>Ber&uuml;cksichtigung</th>
          </tr>
          <?php
			$result = db_conn_and_sql ( 'SELECT * FROM feste_feiertage LEFT JOIN feiertage_schule ON feiertage_schule.ff=feste_feiertage.id AND feiertage_schule.schule='.$schule );
            for ($i=0;$i<sql_num_rows ( $result ); $i++) {
          ?>
          <tr>
            <td><?php echo sql_result ( $result, $i, 'feste_feiertage.name' ); ?></td>
            <td align="center"><input type="checkbox" name="feiertag_<?php echo sql_result ( $result, $i, 'feste_feiertage.id' ); ?>"<?php if (sql_result ( $result, $i, 'feiertage_schule.aktiv' )) echo ' checked="checked"'; ?> value="1" /></td>
          </tr>
          <?php } ?>
        </table>
        <?php if (userrigths("feste_feiertage", $schule)==2) { ?>
			<button style="float: right;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
		<?php } ?>
		</form>
		</fieldset>
	<?php } // Ende Schuljahr
	
	?>
    <?php } if ($_GET['auswahl']=='faecher') {
		$i=0;
		if ($_GET["aendern"]=="true")
			while(isset($_POST["fach_".$i])) {
				db_conn_and_sql("UPDATE `faecher` SET `anzeigen`=".leer_NULL($_POST["fach_anzeigen_".$i])." WHERE `id`=".$_POST["fach_".$i]); $i++;
			}
		?>
    <div class="navigation_2"><?php echo $navigation; ?></div>
	<div class="inhalt">
		<?php $faecher=db_conn_and_sql("SELECT * FROM `faecher` WHERE `faecher`.`user`=0 OR `faecher`.`user`=".$_SESSION['user_id']." ORDER BY `faecher`.`name`"); ?>
		<fieldset>
		<legend>Eingetragene Standardf&auml;cher</legend>
		<form action="index.php?tab=einstellungen&amp;auswahl=faecher&amp;aendern=true" method="post" accept-charset="ISO-8859-1">
		<ul>
		<?php
		for($i=0; $i<sql_num_rows($faecher);$i++) {
			echo '<li><input type="hidden" name="fach_'.$i.'" value="'.sql_result($faecher,$i,"faecher.id").'" />';
			if (sql_result($faecher,$i,"user")==$_SESSION['user_id']) {
				echo '<input type="checkbox" id="fach_anzeigen_'.$i.'" name="fach_anzeigen_'.$i.'" value="1"';
				if (sql_result($faecher,$i,"faecher.anzeigen"))
					echo ' checked="checked"';
				echo ' />';
			}
			echo html_umlaute(sql_result($faecher,$i,"faecher.name")).' ('.html_umlaute(sql_result($faecher,$i,"faecher.kuerzel")).')</li>';
		}
		?>
		</ul>
		<!--<input type="submit" class="button" value="speichern" onclick="i=0; speichern=0; while(getElementById('fach_anzeigen_'+i)) {if(getElementById('fach_anzeigen_'+i).checked==true) speichern=1; i++;} if(!speichern) { alert('Sie m&uuml;ssen mindestens ein Fach ausw&auml;hlen.'); return false;}" />-->
		</form>
		</fieldset>
		<?php
		$user = new user();
		$eintragbare_schulen=db_conn_and_sql("SELECT schule.id, schule.kuerzel, schule.name FROM schule_user, schule WHERE schule.aktiv=1 AND schule.id=schule_user.schule AND (schule_user.usertyp=4 OR schule_user.usertyp=6) AND schule_user.user=".$_SESSION["user_id"]." ORDER BY schule.aktiv, schule.kuerzel");
		if ($user->my["admin"])
			$eintragbare_schulen=db_conn_and_sql("SELECT schule.id, schule.kuerzel, schule.name FROM schule WHERE schule.aktiv=1 ORDER BY schule.aktiv, schule.kuerzel");
		
		// admin darf alle Schulen auswaehlen, SL darf seine schule auswaehlen, Einzelnutzer darf eigenes Fach angeben
		if (sql_num_rows($eintragbare_schulen)>0) {
		?>
		<br />
		<fieldset>
			<legend>Neues Fach</legend>
			<form action="<?php echo $pfad; ?>formular/fach_eintragen.php" method="post" accept-charset="ISO-8859-1">
			<label for="schule">Schule<em>*</em>:</label> <select name="schule">
			<?php while ($schule = sql_fetch_assoc($eintragbare_schulen)) {
					echo '<option value="'.$schule["id"].'" title="'.$schule["name"].'">'.$schule["kuerzel"].'</option>';
				} ?>
				</select><br />
			<label for="name">Name<em>*</em>:</label> <input type="text" name="name" size="10" maxlength="25" /><br />
			<label for="kuerzel">K&uuml;rzel<em>*</em>:</label> <input type="text" name="kuerzel" size="5" maxlength="7" />
			<input type="button" class="button" value="hinzuf&uuml;gen" onclick="auswertung=new Array(new Array(1, 'name','nicht_leer'), new Array(1, 'kuerzel','nicht_leer')); pruefe_formular(auswertung);" />
			</form>
		</fieldset>
    <?php }
    }
	
	if ($_GET['auswahl']=='noten_bew') {
		$user=new user();
		$schule=$user->my["letzte_schule"];
		if (userrigths("zensurentypen", $schule)!=2)
			die("Sie haben nicht die erforderlichen Rechte, Zensurentypen und Bewertungstabellen zu verwalten.");
		
		if($_GET["eintragen"]=="bewt") {
			//aktiv-Setzen?
			$i=0;
			while(isset($_POST["bt_id_".$i])) {
				db_conn_and_sql("UPDATE `bewertungstabelle` SET `aktiv`=".leer_NULL($_POST["bt_aktiv_".$i])." WHERE `id`=".injaway($_POST["bt_id_".$i]));
				$i++;
			}
			
			//neue Bewertungstabelle
			if ($_POST["bewt_name"]!="") {
				$id=db_conn_and_sql("INSERT INTO `bewertungstabelle` (`name`, `punkte`, `user`, `schule`) VALUES (".apostroph_bei_bedarf($_POST["bewt_name"]).", ".(injaway($_POST["punkte"])+0).", ".$_SESSION['user_id'].", ".$schule.");");
				$notenz=0;
				if ($_POST["punkte"])
					$feldname="prozent_bis_punkte_";
				else
					$feldname="prozent_bis_noten_";
                
				while(isset($_POST[$feldname.$notenz]) or $notenz==0) {
					if (isset($_POST[$feldname.$notenz])) {
						db_conn_and_sql("INSERT INTO `bewertung_note` (`bewertungstabelle`, `note`, `prozent_bis`) VALUES (".$id.", ".$notenz.", ".punkt_statt_komma_zahl($_POST[$feldname.$notenz]).");");
                    }
					$notenz++;
				}
			}
		}
	?>
    <div class="navigation_2"><?php echo $navigation; ?></div>
	<div class="inhalt">
	    <fieldset><legend>Zensurentypen</legend>
	<form action="<?php echo $pfad; ?>formular/notentyp.php?aktion=aktualisieren" method="post" accept-charset="ISO-8859-1">
	<input type="hidden" name="schule" value="<?php echo $schule; ?>" />
    <?php $notentypen_result = db_conn_and_sql ( 'SELECT * FROM `notentypen` WHERE id<11 OR schule='.$schule.' ORDER BY `notentypen`.`kuerzel`' ); ?>
    <ul><?php for($i=0;$i<sql_num_rows ( $notentypen_result );$i++) { ?>
        <li style="float: left; margin-right: 25px;">
			<input type="hidden" name="nt_id_<?php echo $i; ?>" value="<?php echo sql_result ( $notentypen_result, $i, 'notentypen.id' ); ?>" />
			<?php if (sql_result ( $notentypen_result, $i, 'notentypen.id' )>10) { ?>
			<input type="checkbox" id="nt_aktiv_<?php echo $i; ?>" name="nt_aktiv_<?php echo $i; ?>" value="1"<?php if(sql_result ( $notentypen_result, $i, 'notentypen.aktiv' )) echo ' checked="checked"'; ?> title="in Men&uuml;s anzeigen?" />
			<?php }
			echo html_umlaute(sql_result ( $notentypen_result, $i, 'notentypen.kuerzel' )); ?> (<?php echo html_umlaute(sql_result ( $notentypen_result, $i, 'notentypen.name' )); ?>)</li>
		<?php } ?>
    </ul>
		<input type="submit" class="button" value="speichern" onclick="i=0; speichern=0; while(getElementById('nt_aktiv_'+i)) {if(getElementById('nt_aktiv_'+i).checked==true) speichern=1; i++;} if(!speichern) { alert('Sie m&uuml;ssen mindestens einen Zensurentyp ausw&auml;hlen.'); return false;}" />
		</form>
    
    <p style="clear: both;">
    <form action="<?php echo $pfad; ?>formular/notentyp.php?aktion=hinzufuegen" method="post" accept-charset="ISO-8859-1">
	<input type="hidden" name="schule" value="<?php echo $schule; ?>" />
    <fieldset><legend>Neuer Zensurentyp</legend>
       <label for="kuerzel">K&uuml;rzel<em>*</em>:</label> <input type="text" name="kuerzel" size="2" maxlength="5" /> - 
       <label for="name">Beschreibung<em>*</em>:</label> <input type="text" name="name" size="10" maxlength="25" /> - 
		<input type="button" class="button" value="hinzuf&uuml;gen" onclick="auswertung=new Array(new Array(1, 'name','nicht_leer'), new Array(1, 'kuerzel','nicht_leer')); pruefe_formular(auswertung);" />
    </fieldset>
    </form>
    </p>
    </fieldset>
    
    <p>
		<fieldset>
		<legend>Bewertungstabellen:</legend>
		<form action="index.php?tab=einstellungen&amp;auswahl=noten_bew&amp;eintragen=bewt" method="post" accept-charset="ISO-8859-1">
		<ul>
		<?php $bewertungstabellen=db_conn_and_sql("SELECT *
			FROM `bewertungstabelle`
				LEFT JOIN `bewertung_note` ON `bewertung_note`.`bewertungstabelle`=`bewertungstabelle`.`id`
			WHERE `schule`=".$schule." OR (`schule` IS NULL AND `user`=".$_SESSION['user_id'].")
			ORDER BY `bewertungstabelle`.`name`,`bewertungstabelle`.`id`,`bewertung_note`.`note`");
		$bt=0; $bt_zaehler=0;
		for ($i=0;$i<sql_num_rows($bewertungstabellen);$i++) {
			if (sql_result($bewertungstabellen,$i,"bewertungstabelle.id")!=$bt) {
				if ($bt!=0) echo '</li>';
				echo '<li><input type="checkbox" value="1" id="bt_aktiv_'.$bt_zaehler.'" name="bt_aktiv_'.$bt_zaehler.'"';
				if (sql_result($bewertungstabellen,$i,"bewertungstabelle.aktiv")) echo ' checked="checked"';
				echo ' title="anzeigen?" /> '.html_umlaute(sql_result($bewertungstabellen,$i,"bewertungstabelle.name")).' <img src="'.$pfad.'icons/';
				if (sql_result($bewertungstabellen,$i,"bewertungstabelle.punkte")) echo 'haekchen.png" alt="haekchen"'; else echo 'abhaken.png" alt="kein haekchen"';
				echo ' title="Punkte (Sek. 2) oder Zensuren" />:'; // TODO: Haekchen durch Icon "1-6" bzw. "0-15" ersetzen
				$bt=sql_result($bewertungstabellen,$i,"bewertungstabelle.id");
				echo '<input type="hidden" name="bt_id_'.$bt_zaehler.'" value="'.$bt.'" />';
				$bt_zaehler++;
			}
			echo ' '.sql_result($bewertungstabellen,$i,"bewertung_note.note").' <small style="color: gray;">(-'.(0+sql_result($bewertungstabellen,$i,"bewertung_note.prozent_bis")).'%)</small> |';
		}
		?>
		</li>
		</ul>
			<fieldset><legend>Neu</legend><label for="bewt_name">Name<em>*</em>:</label> <input type="text" id="bewt_name" name="bewt_name" size="5" maxlength="20" title="Geben Sie hier den Namen der neuen Bewertungstabelle ein" />
			<label for="punkte">Punkte<em>*</em>:</label> <input type="checkbox" id="punkte" name="punkte" value="1" onchange="document.getElementById('bew_neu_noten').style.display=this.checked==1?'none':'block'; document.getElementById('bew_neu_punkte').style.display=this.checked==1?'block':'none';" title="mit Punktesystem bewerten" /><br />
			<span id="bew_neu_noten"><?php
				for($i=1;$i<=5;$i++)
					echo '<label for="prozent_bis_noten_'.$i.'">'.$i.':</label> <input type="text" id="prozent_bis_noten_'.$i.'" name="prozent_bis_noten_'.$i.'" size="1" maxlength="3" title="bis wieviel Prozent gibt es eine '.$i.'" /> ';
				?>
				<label for="prozent_bis_noten_6">6:</label> <input type="text" name="prozent_bis_noten_6" size="1" readonly="true" title="bis wieviel Prozent gibt es eine 6" value="0" /></span>
			<span id="bew_neu_punkte" style="display: none;"><?php
				for($i=15;$i>=1;$i--)
					echo '<label for="prozent_bis_punkte_'.$i.'">'.$i.':</label> <input type="text" id="prozent_bis_punkte_'.$i.'" name="prozent_bis_punkte_'.$i.'" size="1" maxlength="3" title="bis wieviel Prozent gibt es '.$i.' Punkte" /> ';
				?>
				<label for="prozent_bis_punkte_0">0:</label> <input type="text" name="prozent_bis_punkte_0" size="1" readonly="true" title="bis wieviel Prozent gibt es 0 Punkte" value="0" /></span>
			</fieldset>
		<input type="button" class="button" value="speichern" onclick="i=0; speichern=0; while(getElementById('bt_aktiv_'+i)) {if(getElementById('bt_aktiv_'+i).checked==true) speichern=1; i++;} <?php if (sql_num_rows($bewertungstabellen)<1) echo 'speichern=1; '; ?>if(!speichern) { alert('Sie m&uuml;ssen mindestens eine Bewertungstabelle ausw&auml;hlen.'); return false;}
			auswertung=new Array();
			if (getElementById('bewt_name').value!='') {
				auswertung=new Array(new Array(2, 'bewt_name','nicht_leer'));
				i=1; if (getElementById('punkte').checked==true) feldname='prozent_bis_punkte'; else feldname='prozent_bis_noten';
				while(getElementById(feldname+'_'+i)) {
					auswertung.push(new Array(2, feldname+'_'+i,'natuerliche_zahl'));
					i++;
				}
			}
			if (auswertung.length&gt;0) pruefe_formular(auswertung);
			else document.getElementsByTagName('form')[2].submit();" />
		</form>
		</fieldset>
	</p>
	<p><h2>Webseite f&uuml;r Sch&uuml;lernoten:</h2>
		<a href="<?php echo $pfad; ?>eltern/export_schuelerdaten.php">Export durchf&uuml;hren</a>
	</p>
	<?php
	}
	
	
	if ($_GET['auswahl']=='schulen') { ?>
    <div class="navigation_2"><?php echo $navigation; ?></div>
	<div class="inhalt"><?php
		if ($_GET["erstellen"]=="raum") { ?>
		<?php
		$schulen=db_conn_and_sql("SELECT *, GROUP_CONCAT(`schulart`.`kuerzel` SEPARATOR ', ') AS 'schularten'
			FROM `schule_user`, `schule`
				LEFT JOIN `schule_schulart` ON `schule_schulart`.`schule`=`schule`.`id`
				LEFT JOIN `schulart` ON `schule_schulart`.`schulart`=`schulart`.`id`
			WHERE `schule_user`.`schule`=`schule`.`id`
				AND `schule_user`.`user`=".$_SESSION['user_id']."
			GROUP BY `schule`.`id`
			ORDER BY `schule_user`.`aktiv` DESC, `schule_schulart`.`schule`, `schule`.`name`");
		if (sql_num_rows($schulen)>0) {
			echo 'Eingetragene Schulen:
				<ul>';
			for($i=0;$i<sql_num_rows($schulen);$i++) {
				$raeume=db_conn_and_sql("SELECT GROUP_CONCAT(`raum`.`name` SEPARATOR ', ') AS 'raeume'
					FROM `raum` WHERE `raum`.`schule`=".sql_result($schulen,$i,"schule.id")." GROUP BY `raum`.`schule` ORDER BY `raum`.`name`");
				$zeiten=db_conn_and_sql("SELECT `stundenzeiten`.`beginn` FROM `stundenzeiten` WHERE `stundenzeiten`.`schule`=".sql_result($schulen,$i,"schule.id")." ORDER BY `stundenzeiten`.`beginn`");
				echo '<li><img src="'.$pfad.'icons/';
				if (sql_result($schulen,$i,"schule_user.aktiv")) echo 'haekchen.png" alt="haekchen" title="Schule wird angezeigt (ist aktiv)"'; else echo 'abhaken.png" alt="kein_haekchen" title="Schule wird nicht angezeigt (ist nicht aktiv)"';
				echo ' /> ['.html_umlaute(sql_result($schulen,$i,"schule.kuerzel")).'] '.html_umlaute(sql_result($schulen,$i,"schule.name")).' ('.html_umlaute(sql_result($schulen,$i,"schularten")).')<br />R&auml;ume: '.html_umlaute(@sql_result($raeume,0,"raeume")).'
					<br />Unterrichtszeiten: ';
					for($k=0;$k<sql_num_rows($zeiten);$k++) echo substr(sql_result($zeiten,$k,"stundenzeiten.beginn"),0,5).' | ';
					if (userrigths("schuldaten", sql_result($schulen,$i,"schule.id")))
						echo '<a href="'.$pfad.'formular/schule_bearbeiten.php?schule='.sql_result($schulen,$i,"schule.id").'&amp;jahr='.$_GET["auswahl"].'" class="icon" title="bearbeiten"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a></li>';
			}
			echo '</ul>';
		}
		
		if (userrigths("admin",0)) { ?>
		
	<form action="<?php echo $pfad; ?>formular/schule_neu.php" method="post" accept-charset="ISO-8859-1">
    <fieldset><legend>Neue Schule anlegen</legend>
      <label for="kurz">K&uuml;rzel<em>*</em>:</label> <input type="text" name="kurz" size="5" maxlength="8" />
      <label for="name">Schulname<em>*</em>:</label> <input type="text" name="name" size="35" maxlength="50" /><br />
      <label for="schulart_0">Schulart(en)<em>*</em>:</label> <?php $schulart=db_conn_and_sql("SELECT * FROM `schulart`");
	  for ($i=0;$i<sql_num_rows($schulart);$i++) { ?>
        <input type="checkbox" name="schulart_<?php echo $i; ?>" value="1" /><?php echo html_umlaute(sql_result($schulart,$i,'schulart.kuerzel')); ?> 
      <?php } ?><br />
		<label for="adresse">Adresse:</label> <input type="text" name="adresse" size="35" maxlength="150" />
		<label for="plz_ort">PLZ Ort:</label> <input type="text" name="plz_ort" size="35" maxlength="150" /><br />
		<label for="telefon">Telefon:</label> <input type="text" name="telefon" size="35" maxlength="50" />
		<label for="fax">Fax:</label> <input type="text" name="fax" size="35" maxlength="50" /><br />
		<label for="direktor">Schulleiter:</label> <input type="text" name="direktor" size="35" maxlength="150" />
      <input type="button" class="button" value="neue Schule erstellen" onclick="auswertung=new Array(new Array(0, 'kurz','nicht_leer'), new Array(0, 'name','nicht_leer')); pruefe_formular(auswertung);" />
    </fieldset>
    </form>
	<?php
	}
	}
	
	?>        
		
		
		
		
    <?php } if ($_GET['auswahl']=='allgemein') { ?>
    <div class="navigation_2"><?php echo $navigation; ?></div>
	<div class="tooltip" id="tt_syntax" style="width: 600px;">
		<ul><li>Die Unterrichtstabelle gestalten Sie mit dem Tabellenzellen-Trennzeichen "<code>||</code>" (unter der Taste "A").</li>
			<li>Innerhalb der Tabelle sind folgende Inhalte erlaubt: <code>%Zeit</code> (genaue Uhrzeit), <code>%minuten</code>, <code>%Hefter</code> (Merkteil/&Uuml;bteil), <code>%Inhalt</code>, <code>%Kommentar</code>, <code>%Medium</code>, <code>%Sozialform</code>, <code>%Handlungsmuster</code> und <code>%Phase</code>. Einen Zeilenumbruch erreichen Sie innerhalb der Tabelle mit <code>//</code>.</li>
			<li>Au&szlig;erhalb der Tabelle sind folgende Inhalte erlaubt: <code>%Hausaufgaben</code>, <code>%Tests</code>, <code>%Struktur</code>, <code>%Notizen</code>, <code>%Hausaufgabenvergabe</code> und <code>%Testankuendigung</code></li>
		</ul>
		<p>Voreinstellung der Druckansicht ist: <pre><code>%Hausaufgaben
%Tests
%Struktur
%Notizen
|| %Zeit//%minuten//%Hefter || %Inhalt %Kommentar ||
%Hausaufgabenvergabe
%Testankuendigung</code></pre></p>
	</div>
	<div class="tooltip" id="tt_zweitansicht" style="width: 600px;">
		Gestalten Sie eine Zweitansicht (z.B. zur Abgabe bei einer Hospitation) oder lassen Sie das Textfeld leer, um keine zweite Ansicht zu nutzen.</div>
	<div class="tooltip" id="tt_grobplanungsfaktor" style="width: 600px;">
		<p>Mit dem Grobplanungsfaktor kann man die Lernbereich-&Uuml;berschriften-Anzeige bestimmen.
        Im Lehrplan sind f&uuml;r einen Lernbereich z.B. 20 Unterrichtsstunden vorgesehen.
        Diese Stundenanzahl wird mit dem hier eingegebenen Faktor multipliziert (z.B. 20&middot;1 = 20 oder 20&middot;1,3 = 26).
        Das Ergebnis ist die Grundlage f&uuml;r die Einordnung in der Grobplanung.</p>
        <p>Sie k&ouml;nnen die Anzeige der LB-&Uuml;berschriften deaktivieren, indem Sie "0" als Faktor eingeben.</p></div>
	<div class="tooltip" id="tt_schuljahr">
		<p>Das aktuelle Schuljahr hat einen gro&szlig;en Einfluss (auf die Klassenstufen-Bezeichnungen, die Grobplanung, Zensuren...).</p>
		<p>Falls das gew&uuml;nschte Jahr hier nicht auftauchen sollte, muss dieses zun&auml;chst mit Start- und Enddatum unter Start - Einstellungen - Schuljahresdaten eingetragen werden.
		Dieses kann allerdings nur durch Einzelnutzer, Schulleiter oder Administratoren eingetragen werden.</p>
	</div>
	
	<div class="inhalt">
		<form action="<?php echo $pfad; ?>formular/benutzereinstellungen.php" method="post" accept-charset="ISO-8859-1">
		<fieldset><legend>Benutzer-Einstellungen</legend>
			<p style="float: right;"><?php
				$my_user = new user();
				echo $my_user->my["vorname"]." ".$my_user->my["name"]."<br />";
				echo '<input type="text" placeholder="Stra&szlig;e" title="Stra&szlig;e" name="adress" value="'.$my_user->my["strasse"].'" /><br />';
				echo '<input type="text" placeholder="PLZ" title="Postleitzahl" name="postal_code" value="'.$my_user->my["plz"].'" style="width: 50px" />';
				echo '<input type="text" placeholder="Ort" title="Ort" name="city" value="'.$my_user->my["ort"].'" style="width: 170px" /><br />';
				echo '<input type="text" placeholder="Telefon 1" title="Telefon 1" name="tel1" value="'.$my_user->my["tel1"].'" /><br />';
				echo '<input type="text" placeholder="Telefon 2" title="Telefon 2" name="tel2" value="'.$my_user->my["tel2"].'" /><br />';
			?></p>
			<ol class="divider">
				<li><span style="font-weight: bold;">Passwort &auml;ndern:</span><?php if ($_GET["pwd_changed"]=="1") echo ' <span class="hinweis">Ihr Passwort wurde ge&auml;ndert.</span>'; ?><br />
					<label for="old_password" style="width: 210px;">Altes Passwort:</label> <input type="password" name="old_password" size="7" /><br />
					<label for="user_password_new" style="width: 210px;">Neues Passwort:</label> <input type="password" name="user_password_new" size="7" /><br />
					<label for="user_password_repeat" style="width: 210px;">Passwort wiederholen:</label> <input type="password" name="user_password_repeat" size="7" /></li>
				<li><label for="schuljahr" style="width: 210px;">Ausgew&auml;hltes Schuljahr:</label> <select name="schuljahr">
				<?php
				$result=db_conn_and_sql("SELECT * FROM `benutzer` WHERE `id`=".$_SESSION['user_id']);
				//$benutzer=db_conn_and_sql("SELECT * FROM `benutzer` WHERE `id`=".$_SESSION['user_id']);
				$sj_result=db_conn_and_sql("SELECT DISTINCT `ferien`.`schuljahr`
					FROM `ferien`, `schule_user`, `schule`
					WHERE `ferien`.`welche`=0
						AND `ferien`.`ende`<>'000-00-00'
						AND `ferien`.`bundesland`=`schule`.`bundesland`
						AND `schule_user`.`schule`=`schule`.`id`
						AND `schule_user`.`user`=".$_SESSION['user_id']."
					ORDER BY `schuljahr` DESC");
				while ($schuljahr_opt=sql_fetch_assoc($sj_result)) {
					echo '<option value="'.$schuljahr_opt["schuljahr"].'"';
					if ($schuljahr_opt["schuljahr"]==$my_user->my["aktuelles_schuljahr"])
						echo ' selected="selected"';
					echo '>'.$schuljahr_opt["schuljahr"].' / '.substr($schuljahr_opt["schuljahr"]+1,2).'</option>';
				} ?>
				</select> <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_schuljahr')" onmouseout="hideWMTT()" />
				</li>
			</ol>
			<ol class="divider">
			<?php if ($my_user->my["lehrer"] or $my_user->my["schulleitung"]) { ?>
			<li><label for="zensurenpunkte" style="width: 210px;">Zensuren mit Punktangaben:</label> <input type="checkbox" name="zensurenpunkte"<?php if ($my_user->my["zensurenpunkte"]) echo ' checked="checked"'; ?> value="1" /><br />
				<label for="zensurenkommentare" style="width: 210px;">Kommentarm&ouml;glichkeit:</label> <input type="checkbox" name="zensurenkommentare"<?php if ($my_user->my["zensurenkommentare"]) echo ' checked="checked"'; ?> value="1" /><br />
				<label for="zensuren_unt_ber" style="width: 210px;">Unterschriften/Berichtigungen:</label> <input type="checkbox" name="zensuren_unt_ber"<?php if ($my_user->my["zensuren_unt_ber"]) echo ' checked="checked"'; ?> value="1" /><br />
				<label for="zensuren_nicht_zaehlen" style="width: 210px;">M&ouml;glichkeit "nicht bewerten":</label> <input type="checkbox" name="zensuren_nicht_zaehlen"<?php if ($my_user->my["zensuren_nicht_zaehlen"]) echo ' checked="checked"'; ?> value="1" />
			</li>
			<?php } ?>
			<li><label for="dienstberatung" style="width: 210px;">Dienstberatungen:</label> <input type="checkbox" name="dienstberatungen"<?php if ($my_user->my["dienstberatungen"]) echo ' checked="checked"'; ?> value="1" /></li>
			<?php if ($my_user->my["lehrer"] or $my_user->my["schulleitung"]) { ?>
			<li><label for="sitzplan" style="width: 210px;">Sitzordnungen:</label> <input type="checkbox" name="sitzplan"<?php if ($my_user->my["sitzplan"]) echo ' checked="checked"'; ?> value="1" /></li>
			<li><label for="schuljahresplanung" style="width: 210px;">Schuljahresplanung:</label> <input type="checkbox" name="schuljahresplanung"<?php if ($my_user->my["schuljahresplanung"]) echo ' checked="checked"'; ?> value="1" /></li>
			<li><label for="statistiken" style="width: 210px;">Statistiken (HA, Fehlzeiten...):</label> <input type="checkbox" name="statistiken"<?php if ($my_user->my["statistiken"]) echo ' checked="checked"'; ?> value="1" /></li>
			<li><label for="ustd_planung" style="width: 210px;">Ustd.-Planung:</label> <input type="checkbox" name="ustd_planung"<?php if ($my_user->my["ustd_planung"]) echo ' checked="checked"'; ?> value="1" /></li>
            <li><label for="grobplanungfaktor" style="width: 210px;">Grobplanungsfaktor: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_grobplanungsfaktor')" onmouseout="hideWMTT()" /></label> <input type="text" name="grobplanungsfaktor" size="2" maxlength="3" value="<?php echo kommazahl($my_user->my["lb_faktor"]); ?>" /></li>
			<li><label for="druckansicht" style="width: 210px;">Druckansicht<em>*</em>: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_syntax')" onmouseout="hideWMTT()" /></label> <textarea name="druckansicht" cols="80" rows="6"><?php echo $my_user->my["druckansicht"]; ?></textarea><br />
				<label for="ansicht_2" style="width: 210px;">Zweitansicht: <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_zweitansicht')" onmouseout="hideWMTT()" /></label> <textarea name="ansicht_2" cols="80" rows="6"><?php echo $my_user->my["ansicht_2"]; ?></textarea></li>
			<li><label for="hefteransicht" style="width: 210px;">Merkteil &amp; &Uuml;bungen getrennt<em>*</em>:</label> <input type="checkbox" name="hefteransicht"<?php if ($my_user->my["merkhefter"]) echo ' checked="checked"'; ?> value="1" /></li>
			<?php } ?>
			</ol>
			<input type="submit" class="button" value="speichern" /></fieldset>
		</form>
	
    <?php } if ($_GET['auswahl']=='programm') { ?>
    <div class="navigation_2"><?php echo $navigation; ?></div>
	<div class="inhalt">
		<p>Benutzer: <?php $mein_benutzer=new user();
		echo $mein_benutzer->my["name"].', '.$mein_benutzer->my["vorname"]; ?></p>
		<?php if ($mein_benutzer->my["admin"]) { // Admin darf das
		?>
		<form action="<?php echo $pfad; ?>formular/benutzer.php" method="post" accept-charset="ISO-8859-1">
			<fieldset><legend>MySQL-Datenbankanbindung</legend>
				<label for="server">MySQL-Server:</label> <input type="text" name="server" value="<?php $db_anbindung=db_anbindung(); echo $db_anbindung["server"]; ?>" size="15" /><br />
				<label for="benutzer">MySQL-Benutzer:</label> <input type="text" name="benutzer" value="<?php echo $db_anbindung["benutzer"]; ?>" size="7" /><br />
				<label for="passwort">MySQL-Passwort:</label> <input type="password" name="passwort" size="9" /><br />
				<label for="db_name">MySQL-DB-Name:</label> <input type="text" name="db_name" value="<?php echo $db_anbindung["db_name"]; ?>" size="15" />
				</fieldset>
			<fieldset><legend>Backup auf FTP-Server</legend>
				<label for="ftp_server">FTP-Server:</label> <input type="text" name="ftp_server" value="<?php echo $db_anbindung["ftp_server"]; ?>" size="15" /><br />
				<label for="ftp_user">FTP-Benutzer:</label> <input type="text" name="ftp_user" value="<?php echo $db_anbindung["ftp_user"]; ?>" size="7" /><br />
				<label for="ftp_pwd">FTP-Passwort:</label> <input type="password" name="ftp_pwd" value="<?php echo $db_anbindung["ftp_pwd"]; ?>" size="9" /><br />
				<label for="ftp_port">FTP-Port:</label> <input type="text" name="ftp_port" value="<?php echo $db_anbindung["ftp_port"]; ?>" size="2" /><br />
				<label for="ftp_path">Pfad auf Server:</label> <input type="text" name="ftp_path" value="<?php echo $db_anbindung["ftp_path"]; ?>" size="15" />
			</fieldset>
			<input type="submit" class="button" value="speichern" />
		</form>
		<?php } ?>
		<p>Log:<br /><?php echo nl2br(sql_result($benutzer, 0, "log")); ?></p>
    <?php } if ($_GET['auswahl']=='sitzplan') { ?>
    <div class="navigation_2"><?php echo $navigation; ?></div>
	<div class="inhalt">
		<?php
		$user=new user();
		$schule=$user->my["letzte_schule"];
		
		if ($_GET["aktivitaet"]=="eintragen")
			for ($i=0;$_POST["id_".$i]>0;$i++)
				db_conn_and_sql("UPDATE sitzplan SET aktiv=".leer_NULL($_POST["aktiv_".$i])." WHERE id=".injaway($_POST["id_".$i]));
		
		if ($_GET["sitzplan"]=="neu" or $_GET["sitzplan"]>0) {
			if ($_GET["sitzplan"]=="neu") {
				$id=db_conn_and_sql("INSERT INTO sitzplan (name, aktiv, schule) VALUES (".apostroph_bei_bedarf($_POST["name"]).", 1, ".$schule.");");
			}
			else $id=$_GET["sitzplan"];
			
			if ($_POST["speichern"]==1) {
				for ($m=0; isset($_POST["typ_".$m]); $m++)
						if ($_POST["typ_".$m]>0) {
							db_conn_and_sql("INSERT INTO sitzplan_objekt (sitzplan, pos_x, pos_y, drehung, typ) VALUES (".$id.", ".$_POST["horizontal_".$m].", ".$_POST["vertikal_".$m].", ".$_POST["ausrichtung_".$m].", ".$_POST["typ_".$m].");");
							if ($_POST["typ_".$m]==2)
								switch($_POST["ausrichtung_".$m]) {
									case 1: db_conn_and_sql("INSERT INTO sitzplan_objekt (sitzplan, pos_x, pos_y, drehung, typ) VALUES (".$id.", ".($_POST["horizontal_".$m]+1).", ".($_POST["vertikal_".$m]+1).", ".$_POST["ausrichtung_".$m].", NULL);"); break;
									case 2: db_conn_and_sql("INSERT INTO sitzplan_objekt (sitzplan, pos_x, pos_y, drehung, typ) VALUES (".$id.", ".($_POST["horizontal_".$m]+1).", ".($_POST["vertikal_".$m]).", ".$_POST["ausrichtung_".$m].", NULL);"); break;
									case 3: db_conn_and_sql("INSERT INTO sitzplan_objekt (sitzplan, pos_x, pos_y, drehung, typ) VALUES (".$id.", ".($_POST["horizontal_".$m]+1).", ".($_POST["vertikal_".$m]-1).", ".$_POST["ausrichtung_".$m].", NULL);"); break;
									case 4: db_conn_and_sql("INSERT INTO sitzplan_objekt (sitzplan, pos_x, pos_y, drehung, typ) VALUES (".$id.", ".($_POST["horizontal_".$m]).", ".($_POST["vertikal_".$m]+1).", ".$_POST["ausrichtung_".$m].", NULL);"); break;
									case 6: db_conn_and_sql("INSERT INTO sitzplan_objekt (sitzplan, pos_x, pos_y, drehung, typ) VALUES (".$id.", ".($_POST["horizontal_".$m]).", ".($_POST["vertikal_".$m]+1).", ".$_POST["ausrichtung_".$m].", NULL);"); break;
									case 7: db_conn_and_sql("INSERT INTO sitzplan_objekt (sitzplan, pos_x, pos_y, drehung, typ) VALUES (".$id.", ".($_POST["horizontal_".$m]+1).", ".($_POST["vertikal_".$m]-1).", ".$_POST["ausrichtung_".$m].", NULL);"); break;
									case 8: db_conn_and_sql("INSERT INTO sitzplan_objekt (sitzplan, pos_x, pos_y, drehung, typ) VALUES (".$id.", ".($_POST["horizontal_".$m]+1).", ".($_POST["vertikal_".$m]).", ".$_POST["ausrichtung_".$m].", NULL);"); break;
									case 9: db_conn_and_sql("INSERT INTO sitzplan_objekt (sitzplan, pos_x, pos_y, drehung, typ) VALUES (".$id.", ".($_POST["horizontal_".$m]+1).", ".($_POST["vertikal_".$m]+1).", ".$_POST["ausrichtung_".$m].", NULL);"); break;
								}
						}
				echo 'Der Raum-Typ wurde gespeichert und steht nun in der Klassenauswahl zur Auswahl bereit.';
			}
			else {
				$sitzplan=db_conn_and_sql("SELECT * FROM sitzplan WHERE id=".$id);
				echo '<h3>'.html_umlaute(sql_result($sitzplan,0,"sitzplan.name")).'</h3>';
				$start=array("x"=>10, "y"=>180); // TODO: anpassen
				$faktor=61;
				$objekte=sitzplan_objektzuordnung ($faktor);
				for ($m=0; isset($_POST["typ_".$m]); $m++)
						if ($_POST["typ_".$m]>0)
							$platz[]=array(
								"typ"=>$_POST["typ_".$m],
								"drehung"=>$_POST["ausrichtung_".$m],
								"pos_x"=>$_POST["horizontal_".$m],
								"pos_y"=>$_POST["vertikal_".$m]);
				if (count($platz)>0)
				foreach ($platz as $einzelplatz)
					echo '<img src="'.$pfad.'look/sitzplan/'.$objekte[$einzelplatz["typ"]]["name"].'_'.$einzelplatz["drehung"].'.png" alt="'.$objekte[$einzelplatz["typ"]]["name"].'" style="position:absolute; z-index:0; top: '.($start["y"]+$faktor*$einzelplatz["pos_y"]-($faktor-15)+$objekte[$einzelplatz["typ"]]["drehung"][$einzelplatz["drehung"]]["pos_y"]).'px;
							left: '.($start["x"]+$faktor*$einzelplatz["pos_x"]-($faktor-15)+$objekte[$einzelplatz["typ"]]["drehung"][$einzelplatz["drehung"]]["pos_x"]).'px; width:'.$objekte[$einzelplatz["typ"]]["drehung"][$einzelplatz["drehung"]]["width"].'px;" />';
	
				$faktor=60;
				?>
				Beginnen Sie mit der Eintragung der Pl&auml;tze links oben.
				<form action="<?php echo $pfad; ?>index.php?tab=einstellungen&amp;auswahl=sitzplan&amp;sitzplan=<?php echo $id; ?>" method="post">
				<table colspan="0" style="float: left; margin-right: 20px;">
					<?php $lfd_nr=0;
						/*
						"typ"=>sql_result($sitzplan,$i,"sitzplan_objekt.typ"),
						"id"=>sql_result($sitzplan,$i,"sitzplan_objekt.id"),
						"drehung"=>sql_result($sitzplan,$i,"sitzplan_objekt.drehung"),
						"pos_x"=>sql_result($sitzplan,$i,"sitzplan_objekt.pos_x"),
						"pos_y"=>sql_result($sitzplan,$i,"sitzplan_objekt.pos_y")*/
					for ($m=1;$m<14;$m++) {
						echo '<tr>';
						for ($n=1;$n<12;$n++) {
							echo '<td style="width: '.$faktor.'px; height: '.$faktor.'px; text-align: center;">
								<input type="hidden" id="typ_'.$lfd_nr.'" name="typ_'.$lfd_nr.'" value="'.$_POST["typ_".$lfd_nr].'" />
								<input type="hidden" id="ausrichtung_'.$lfd_nr.'" name="ausrichtung_'.$lfd_nr.'" value="'.$_POST["ausrichtung_".$lfd_nr].'" />
								<input type="hidden" id="horizontal_'.$lfd_nr.'" name="horizontal_'.$lfd_nr.'" value="'.$n.'" />
								<input type="hidden" id="vertikal_'.$lfd_nr.'" name="vertikal_'.$lfd_nr.'" value="'.$m.'" />
								<input type="button" class="button" value="x" onclick="document.getElementById(\'typ_'.$lfd_nr.'\').value=document.getElementById(\'typ\').value;
									document.getElementById(\'ausrichtung_'.$lfd_nr.'\').value=document.getElementById(\'ausrichtung\').value;
									document.getElementsByTagName(\'form\')[0].submit();" /></td>';
							$lfd_nr++;
						}
						echo '</tr>';
					} ?>
				</table>
				<div>
					<fieldset><legend>Typ</legend>
						<input type="hidden" value="<?php if (isset($_POST["typ"])) echo $_POST["typ"]; else echo 1; ?>" id="typ" />
						<!--<label style="width: 30px;"><input type="radio" name="typ" value=""<?php if ($_POST["typ"]=="") echo ' checked="checked"'; ?> onchange="document.getElementById('typ').value='';" /></label> entfernen<br />-->
						<label style="width: 30px;"><input type="radio" name="typ" value="1"<?php if ($_POST["typ"]==1 or !isset($_POST["typ"])) echo ' checked="checked"'; ?> onchange="document.getElementById('typ').value=1;" /></label> <img src="<?php echo $pfad; ?>look/sitzplan/tischplatz_2.png" style="height: 60px;" alt="Tischplatz mit Stuhl" /><br />
						<label style="width: 30px;"><input type="radio" name="typ" value="2"<?php if ($_POST["typ"]==2) echo ' checked="checked"'; ?> onchange="document.getElementById('typ').value=2;" /></label> <img src="<?php echo $pfad; ?>look/sitzplan/tisch_2.png" style="height: 60px;" alt="Tisch mit Stuehlen" /><br />
						<!--<label style="width: 30px;"><input type="radio" name="typ" value="3" onchange="document.getElementById('typ').value=3;" /></label> <img src="<?php echo $pfad; ?>look/sitzplan/stuhl_2.png" style="height: 50px;" alt="Stuhl" /><br />-->
						<label style="width: 30px;"><input type="radio" name="typ" value="4"<?php if ($_POST["typ"]==4) echo ' checked="checked"'; ?> onchange="document.getElementById('typ').value=4;" /></label> <img src="<?php echo $pfad; ?>look/sitzplan/pc_platz_2.png" style="height: 60px;" alt="Sitzplatz mit PC" /></fieldset>
						
					<fieldset><legend>Sichtrichtung</legend>
						<input type="hidden" value="<?php if (isset($_POST["drehung"])) echo $_POST["drehung"]; else echo 2; ?>" id="ausrichtung" />
						<table>
							<tr><td><input type="radio" name="drehung" value="7"<?php if ($_POST["drehung"]==7) echo ' checked="checked"'; ?> onchange="document.getElementById('ausrichtung').value=7;" /></td><td><input type="radio" name="drehung" value="8"<?php if ($_POST["drehung"]==8) echo ' checked="checked"'; ?> onchange="document.getElementById('ausrichtung').value=8;" /></td><td><input type="radio" name="drehung" value="9"<?php if ($_POST["drehung"]==9) echo ' checked="checked"'; ?> onchange="document.getElementById('ausrichtung').value=9;" /></td></tr>
							<tr><td><input type="radio" name="drehung" value="4"<?php if ($_POST["drehung"]==4) echo ' checked="checked"'; ?> onchange="document.getElementById('ausrichtung').value=4;" /></td><td></td><td><input type="radio" name="drehung"<?php if ($_POST["drehung"]==6) echo ' checked="checked"'; ?> value="6" onchange="document.getElementById('ausrichtung').value=6;" /></td></tr>
							<tr><td><input type="radio" name="drehung" value="1"<?php if ($_POST["drehung"]==1) echo ' checked="checked"'; ?> onchange="document.getElementById('ausrichtung').value=1;" /></td><td><input type="radio" name="drehung" value="2"<?php if ($_POST["drehung"]==2 or !isset($_POST["drehung"])) echo ' checked="checked"'; ?> onchange="document.getElementById('ausrichtung').value=2;" /></td><td><input type="radio" name="drehung" value="3"<?php if ($_POST["drehung"]==3) echo ' checked="checked"'; ?> onchange="document.getElementById('ausrichtung').value=3;" /></td></tr>
						</table>
					</fieldset>
				</div>
				<br style="clear: both;" />
				<input type="hidden" name="speichern" id="speichern" value="0" />
				<input type="button" class="button" value="reset" onclick="window.location.href='<?php echo $pfad; ?>index.php?tab=einstellungen&amp;auswahl=sitzplan&amp;sitzplan=<?php echo $id; ?>';" />
				<input type="button" class="button" value="eintragen" onclick="document.getElementById('speichern').value=1; document.getElementsByTagName('form')[0].submit();" />
				</form>
			<?php
			}
		}
		else {
		
		if ($_GET["loeschen"]>0 and userrigths("sitzanordnung", $_GET["loeschen"])==2) {
			db_conn_and_sql("DELETE FROM sitzplan WHERE id=".injaway($_GET["loeschen"]));
			db_conn_and_sql("DELETE FROM sitzplan_objekt WHERE sitzplan=".injaway($_GET["loeschen"]));
		}
		
		$sitzplaene=db_conn_and_sql("SELECT * FROM sitzplan WHERE sitzplan.id=1 OR sitzplan.schule=".$schule." ORDER BY aktiv DESC");
		
		if (userrigths("sitzanordnungen",$schule)==2) {
		?>
		<fieldset><legend>neue Sitzanordnung erstellen</legend>
			<form action="<?php echo $pfad; ?>index.php?tab=einstellungen&amp;auswahl=sitzplan&amp;sitzplan=neu" method="post" accept-charset="ISO-8859-1">
				<label for="name">Name<em>*</em>:</label> <input type="text" name="name" size="7" maxlength="15" />
				<input type="button" class="button" value="erstellen" onclick="auswertung=new Array(new Array(0, 'name','nicht_leer')); pruefe_formular(auswertung);" />
			</form>
		</fieldset>
		
		<?php } ?>
		<fieldset><legend>vorhandene Sitzanordnungen</legend>
			<form action="<?php echo $pfad; ?>index.php?tab=einstellungen&amp;auswahl=sitzplan&amp;aktivitaet=eintragen" method="post">
			<ol class="divider">
			<?php
			for ($i=0; $i<@sql_num_rows($sitzplaene);$i++) {
				echo '<li>';
				if (sql_result($sitzplaene,$i,"sitzplan.id")>1) {
					echo '<input type="checkbox" value="1" id="aktiv_'.($i-1).'" name="aktiv_'.($i-1).'"';
					if (sql_result($sitzplaene,$i,'sitzplan.aktiv')==1) echo ' checked="checked"';
					echo '/> ';
					echo '<input type="hidden" name="id_'.($i-1).'" value="'.sql_result($sitzplaene,$i,'sitzplan.id').'" />';
				}
				echo html_umlaute(sql_result($sitzplaene,$i,'sitzplan.name'));
				if (sql_num_rows(db_conn_and_sql("SELECT id FROM sitzplan_klasse WHERE sitzplan=".sql_result($sitzplaene,$i,'sitzplan.id')))==0)
                    echo ' <a href="'.$pfad.'index.php?tab=einstellungen&amp;auswahl=sitzplan&amp;loeschen='.sql_result($sitzplaene,$i,'sitzplan.id').'" onclick="if (confirm(\'Die Sitzordnung wird endg&uuml;ltig gel&ouml;scht. Wollen Sie das wirklich?\')==false) return false;" class="icon"><img src="'.$pfad.'icons/delete.png" alt="loeschen" /></a>';
				echo '<!--<a href="" class="icon"><img src="'.$pfad.'icons/edit.png" alt="bearbeiten" /></a>--></li>';
			}
			?></ol>
			<input type="submit" class="button" value="Aktivit&auml;t speichern" />
			<!-- onclick="i=1; speichern=0; while(getElementById('aktiv_'+i)) {if(getElementById('aktiv_'+i).checked==true) speichern=1; i++;} if(!speichern) { alert('Sie m&uuml;ssen mindestens einen Raum-Typ ausw&auml;hlen.'); return false;} else document.getElementsByTagName('form')[1].submit();" -->
			</form>
		</fieldset>
	
	
    <?php }
	} if (!isset($_GET['auswahl'])) { ?>
    <div class="navigation_2"><?php echo $navigation; ?></div>
	<div class="inhalt">
	<?php
		$db_anbindung=db_anbindung();
        
        if ($db_anbindung["passwort"]!="")
            $passwd=" -p".$db_anbindung["passwort"];
        else
            $passwd="";
        
        
		if ($_GET["aufgabe"]=="ruecksetzen") {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
                system("..\..\mysql\bin\mysql -u".$db_anbindung["benutzer"].$passwd." -h ".$db_anbindung["server"]." lehrer < ".$pfad."backup/minimale_DB.sql", $fp);
            else
                system("mysql -u".$db_anbindung["benutzer"].$passwd." -h ".$db_anbindung["server"]." lehrer < ".$pfad."backup/minimale_DB.sql", $fp);
			
			if ($fp==0)
                echo "<script>alert('Datenbank zurückgesetzt.'); window.location.href='index.php?tab=einstellungen';</script>";
            else
                echo "Es ist ein Fehler beim R&uuml;cksetzen der Datenbank aufgetreten.";
        }
        
		if ($_GET["aufgabe"]=="backup") {
			// TODO: dump1 auf dump2 umbenennen; unlink dump3
			// TODO: Umlaute-Dateien werden nicht hochgeladen
            // /usr/bin/
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
                system("..\..\mysql\bin\mysqldump -u".$db_anbindung["benutzer"].$passwd." -h ".$db_anbindung["server"]." lehrer > ".$pfad."backup/backup_ohne_dateien.sql", $fp);
            else
                system("mysqldump -u".$db_anbindung["benutzer"].$passwd." -h ".$db_anbindung["server"]." lehrer > ".$pfad."backup/backup_ohne_dateien.sql", $fp);
			
			if ($fp==0) { // wenn lokale Sicherung erfolgreich war
				$backup_file_handle=fopen($pfad."backup/backup_ohne_dateien.sql", "r");
				
				// Verbindung aufbauen
				$ftp_data=db_anbindung();
				if ($ftp_data["ftp_path"]=="") $ftp_data["ftp_path"]='.';
				$conn_id = ftp_connect($ftp_data["ftp_server"]);
				
				// Login mit Benutzername und Passwort
				$login_result = ftp_login($conn_id, $ftp_data["ftp_user"], $ftp_data["ftp_pwd"]);
				
				// Versuche die Server-Backup-Datei zu löschen
				if (ftp_delete($conn_id, $ftp_data["ftp_path"]."/backup_ohne_dateien.sql")) {
					echo $ftp_data["ftp_path"]."/backup_ohne_dateien.sql deleted successful<br />";
				} else {
					echo "could not delete ".$ftp_data["ftp_path"]."/backup_ohne_dateien.sql<br />";
				}
				
				// Versuche $remote_file zu laden und in $handle zu speichern
				if (ftp_fput($conn_id, $ftp_data["ftp_path"]."/backup_ohne_dateien.sql", $backup_file_handle, FTP_BINARY, 0)) {
					echo $ftp_data["ftp_path"]."/backup_ohne_dateien.sql erfolgreich hochgeladen<br />";
				} else {
					echo $ftp_data["ftp_path"]."/backup_ohne_dateien.sql konnte nicht hochgeladen werden<br />";
				}
				
				// ------------------- Dateiabgleich (Grafiken und sonstige Dateien) -----------------------
				$grafics=db_conn_and_sql("SELECT * FROM grafik ORDER BY id");
				$files  =db_conn_and_sql("SELECT * FROM link ORDER BY id");
				// initialisieren
				$grundpfad='daten';
				// if (!exists(PFAD DATEN) { ANLEGEN }
				if (ftp_mkdir ($conn_id, $ftp_data["ftp_path"].'/'.$grundpfad) !== FALSE)
					echo '';
				else
					echo 'Anlegen eines neuen Verzeichnisses war NICHT erfolgreich!<br />';
				
				// hash-Datei vom Server holen
				if (!(ftp_get ($conn_id, $pfad."daten/hashes_g.csv", $ftp_data["ftp_path"]."/daten/hashes_g.csv", FTP_BINARY) === TRUE))
				{
					echo 'Der Download der Hash-Werte-Datei schlug fehl!<br />';
					$hashes_on_server=array(0,'','');
				}
				else {
					echo ''; // Download Hash ok<br />
					$hashes_on_server='';
				}
				if (file_exists($pfad."daten/hashes_g.csv")) {
					if (!($hash_server_handle=fopen($pfad."daten/hashes_g.csv", "r")))
						die ("Datei ".$pfad."daten/hashes_g.csv konnte nicht ge&ouml;ffnet werden!<br />");
					while(! feof($hash_server_handle))
					{
						$teil=fgets($hash_server_handle);
						$hashes_on_server[] = explode(";",$teil);
					}
				}
				rewind($hash_server_handle);
				
				fclose($hash_server_handle);
				//clearstatcache();
				array_pop($hashes_on_server);
				
				$new_hashes='';
				
				$hash_i=0;
				for ($i=0; $i<sql_num_rows($grafics);$i++) { //sql_num_rows($grafics)
					$ergebnis=handle_backedup_files($conn_id, $ftp_data["ftp_path"], $pfad, $grundpfad, sql_result($grafics,$i,"grafik.lernbereich"), sql_result($grafics,$i,"grafik.id"), sql_result($grafics,$i,"grafik.url"), $hashes_on_server, $hash_i);
					$new_hashes.=$ergebnis[0];
					$hash_i=$ergebnis[1];
				}
				
				// noch dateien auf Server übrig?
				while ($hash_i<count($hashes_on_server)) {
					echo "L&ouml;sche ".$hashes_on_server[$hash_i][1]." auf FTP-Server (Bereinigung)<br />";
					ftp_delete($conn_id, $ftp_data["ftp_path"]."/".$hashes_on_server[$hash_i][1]);
					$hash_i++;
				}
				
				// $new_hashes in Datei hashes_g.csv schreiben auf server /hashes_g.csv speichern
				if (!($hash_handle = fopen ($pfad."daten/hashes_g.csv", w)))
					die ("Datei ".$pfad."hashes_g.csv konnte lokal nicht angelegt werden!<br />");
				fwrite ($hash_handle, $new_hashes);
				fclose ($hash_handle);
				clearstatcache();
				
				if (!(ftp_put ($conn_id, $ftp_data["ftp_path"]."/".$grundpfad."/hashes_g.csv", $pfad."daten/hashes_g.csv", FTP_BINARY) === TRUE))
					echo "Hash-Werte-Datei (Grafiken) konnte nicht auf dem Server erstellt werden.";
				
				// selbe Anweisungen fuer andere Dateien
				if (!(ftp_get ($conn_id, $pfad."daten/hashes_f.csv", $ftp_data["ftp_path"]."/daten/hashes_f.csv", FTP_BINARY) === TRUE))
				{
					echo 'Der Download der Hash-Werte-Datei schlug fehl!<br />';
					$hashes_on_server=array(0,'','');
				}
				else {
					echo ''; // Download Hash ok<br />
					$hashes_on_server='';
				}
				if (file_exists($pfad."daten/hashes_f.csv")) {
					if (!($hash_server_handle=fopen($pfad."daten/hashes_f.csv", "r")))
						die ("Datei ".$pfad."daten/hashes_f.csv konnte nicht ge&ouml;ffnet werden!<br />");
					while(! feof($hash_server_handle))
					{
						$teil=fgets($hash_server_handle);
						$hashes_on_server[] = explode(";",$teil);
					}
				}
				rewind($hash_server_handle);
				
				fclose($hash_server_handle);
				//clearstatcache();
				array_pop($hashes_on_server);
				
				$new_hashes='';
				
				$hash_i=0;
				for ($i=0; $i<sql_num_rows($files);$i++) {
					$ergebnis=handle_backedup_files($conn_id, $ftp_data["ftp_path"], $pfad, $grundpfad, sql_result($files,$i,"link.lernbereich"), sql_result($files,$i,"link.id"), sql_result($files,$i,"link.url"), $hashes_on_server, $hash_i);
					$new_hashes.=$ergebnis[0];
					$hash_i=$ergebnis[1];
				}
				
				// noch dateien auf Server übrig?
				while ($hash_i<count($hashes_on_server)) {
					echo "L&ouml;sche ".$hashes_on_server[$hash_i][1]." auf FTP-Server (Bereinigung)<br />";
					ftp_delete($conn_id, $ftp_data["ftp_path"]."/".$hashes_on_server[$hash_i][1]);
					$hash_i++;
				}
				
				// $new_hashes in Datei hashes_g.csv schreiben auf server /hashes_g.csv speichern
				if (!($hash_handle = fopen ($pfad."daten/hashes_f.csv", w)))
					die ("Datei ".$pfad."hashes_f.csv konnte lokal nicht angelegt werden!<br />");
				fwrite ($hash_handle, $new_hashes);
				fclose ($hash_handle);
				clearstatcache();
				
				if (!(ftp_put ($conn_id, $ftp_data["ftp_path"]."/".$grundpfad."/hashes_f.csv", $pfad."daten/hashes_f.csv", FTP_BINARY) === TRUE))
					echo "Hash-Werte-Datei (Files) konnte nicht auf dem Server erstellt werden.";
				
				include($pfad."basic/db_infos_javascript.php");
				$Datei=$pfad."offline/sql-kreda-db-infos.js";
				
				@chmod ($Datei, 0777);
				$dateihandle = fopen($Datei,"w");
				
				fputs($dateihandle, db_infos_javascript());
				fclose($dateihandle);
				@chmod ($Datei, 0755); //700
				clearstatcache();
				
				if (!(ftp_put ($conn_id, $ftp_data["ftp_path"]."/".$grundpfad."/db_infos_javascript.js", $pfad."offline/sql-kreda-db-infos.js", FTP_BINARY) === TRUE))
					echo "DB-Datei f&uuml;r Tablet-Version konnte nicht auf dem Server erstellt werden.";
				
				// Verbindung schließen
				ftp_close($conn_id);
				
				fclose($backup_file_handle);
				
                echo "<script>alert('Daten exportiert'); window.location.href='index.php?tab=einstellungen';</script>";
            }
            else
                echo "Es ist ein Fehler beim Backup der Lokalen-Backup-Datei aufgetreten.";
			/*
			kein Problem: statt localhost einfach ServerA eingeben also z.B. deinedomain.de - wenn Du eines der Scripte nun auf ServerB ausfuehrst greift ServerB auf die Datenbank von ServerA zu!
			system("/usr/bin/mysqldump -uUSERNAME -pPASSWORT -h deinedomain.de DATENBANKNAME > ".dirname(__FILE__)."/dump.sql", $fp);
			*/
		}
		
		if ($_GET["aufgabe"]=="restore_ftp") {
			$ftp_data = db_anbindung();
			if ($ftp_data["ftp_path"]=="")
				$ftp_data["ftp_path"]='.';
			
			$local_file = $pfad."backup/backup_ohne_dateien_download.sql";
			
			// Verbindung aufbauen
			$conn_id = ftp_connect($ftp_data["ftp_server"]);
			
			// Login mit Benutzername und Passwort
			$login_result = ftp_login($conn_id, $ftp_data["ftp_user"], $ftp_data["ftp_pwd"]);
			
			// ------------------- Dateiabgleich (Grafiken und sonstige Dateien) -----------------------
			// hash-Datei vom Server holen
			if (!(ftp_get ($conn_id, $pfad."daten/hashes_g.csv", $ftp_data["ftp_path"]."/daten/hashes_g.csv", FTP_BINARY) === TRUE))
			{
				echo 'Der Download der Hash-Werte-Datei schlug fehl!<br />';
			}
			else echo 'Download Hash ok<br />';
			$hashes_on_server='';
			if (file_exists($pfad."daten/hashes_g.csv")) {
				if (!($hash_server_handle=fopen($pfad."daten/hashes_g.csv", "r")))
					die ("Datei ".$pfad."daten/hashes_g.csv konnte nicht ge&ouml;ffnet werden!<br />");
				while(! feof($hash_server_handle))
				{
					$teil=fgets($hash_server_handle);
					$hashes_on_server[] = explode(";",$teil);
				}
			}
			rewind($hash_server_handle);
			
			fclose($hash_server_handle);
			//clearstatcache();
			array_pop($hashes_on_server);
			
			
			$grafics=db_conn_and_sql("SELECT * FROM grafik WHERE user=".$_SESSION['user_id']." ORDER BY id");
			$files  =db_conn_and_sql("SELECT * FROM link WHERE user=".$_SESSION['user_id']." ORDER BY id");
			// initialisieren
			$grundpfad='daten';
			
			
			$i=0;
			for ($hash_i=0; $hash_i<count($hashes_on_server);$hash_i++) {
				$pfadebenen=explode("/",$hashes_on_server[$hash_i][1]);
				//$lokal_ebenen=lernbereich2pfadebenen(sql_result($grafics,$i,"grafik.lernbereich")); ueberfluessig
				@mkdir ($pfad.$grundpfad.'/'.$pfadebenen[0], 0755);
				@mkdir ($pfad.$grundpfad.'/'.$pfadebenen[0].'/'.$pfadebenen[1], 0755);
				@mkdir ($pfad.$grundpfad.'/'.$pfadebenen[0].'/'.$pfadebenen[1].'/'.$pfadebenen[2], 0755);
				$active_hash=md5_file($pfad.$grundpfad."/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3]);
				
				// in vorhandener hashes-Datei derzeitige ID suchen
				while ($hashes_on_server[$hash_i][0]>sql_result($grafics,$i,"grafik.id") and $i<sql_num_rows($grafics)) {
					echo "del ".$pfad."daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3]." lokal: ".$hashes_on_server[$hash_i][0].">".sql_result($grafics,$i,"grafik.id")."-".$i."<".sql_num_rows($grafics)."<br />";
					// @unlink($pfad."daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3]);
					$i++;
				}
				//echo $hash_i." - ".$i."<br />";
				if ($hashes_on_server[$hash_i][0]<sql_result($grafics,$i,"grafik.id") or $i>=sql_num_rows($grafics)) {
					ftp_get ($conn_id, $pfad."daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3], $ftp_data["ftp_path"]."/daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3], FTP_BINARY);
					echo $hashes_on_server[$hash_i][1]." neu erstellen (download)<br />";
				}
				if (sql_result($grafics,$i,"grafik.id")==$hashes_on_server[$hash_i][0]) {
					if (trim($hashes_on_server[$hash_i][2])!=trim($active_hash)) {
						echo sql_result($grafics,$i,"grafik.url").": ".$hashes_on_server[$hash_i][2]."!=".$active_hash." Hash verschieden - lokale Datei loeschen, neu downladen<br />";
						// @unlink($pfad."daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3]);
						ftp_get ($conn_id, $pfad."daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3], $ftp_data["ftp_path"]."/daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3], FTP_BINARY);
					}
					//else
					//	echo "alles ok mit ".sql_result($grafics,$i,"grafik.url")."<br />";
					$i++;
				}
			}
			
			// noch dateien auf Server übrig?
			while ($i<sql_num_rows($grafics)) {
				echo "del (after) ".sql_result($grafics,$i,"grafik.url")." lokal<br />";
				// @unlink($pfad."daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3]);
				$i++;
			}
			
			
			
			// selbe Anweisungen fuer andere Dateien
			if (!(ftp_get ($conn_id, $pfad."daten/hashes_f.csv", $ftp_data["ftp_path"]."/daten/hashes_f.csv", FTP_BINARY) === TRUE))
			{
				echo 'Der Download der Hash-Werte-Datei schlug fehl!<br />';
			}
			else echo 'Download Hash ok<br />';
			$hashes_on_server='';
			if (file_exists($pfad."daten/hashes_f.csv")) {
				if (!($hash_server_handle=fopen($pfad."daten/hashes_f.csv", "r")))
					die ("Datei ".$pfad."daten/hashes_f.csv konnte nicht ge&ouml;ffnet werden!<br />");
				while(! feof($hash_server_handle))
				{
					$teil=fgets($hash_server_handle);
					$hashes_on_server[] = explode(";",$teil);
				}
			}
			rewind($hash_server_handle);
			
			fclose($hash_server_handle);
			//clearstatcache();
			array_pop($hashes_on_server);
			
			
			// initialisieren
			$grundpfad='daten';
			
			
			$i=0;
			for ($hash_i=0; $hash_i<count($hashes_on_server);$hash_i++) {
				$pfadebenen=explode("/",$hashes_on_server[$hash_i][1]);
				//$lokal_ebenen=lernbereich2pfadebenen(sql_result($grafics,$i,"grafik.lernbereich")); ueberfluessig
				@mkdir ($pfad.$grundpfad.'/'.$pfadebenen[0], 0755);
				@mkdir ($pfad.$grundpfad.'/'.$pfadebenen[0].'/'.$pfadebenen[1], 0755);
				@mkdir ($pfad.$grundpfad.'/'.$pfadebenen[0].'/'.$pfadebenen[1].'/'.$pfadebenen[2], 0755);
				$active_hash=md5_file($pfad.$grundpfad."/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3]);
				
				// in vorhandener hashes-Datei derzeitige ID suchen
				while ($hashes_on_server[$hash_i][0]>sql_result($files,$i,"link.id") and $i<sql_num_rows($files)) {
					echo "del ".$pfad."daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3]." lokal: ".$hashes_on_server[$hash_i][0].">".sql_result($files,$i,"link.id")."-".$i."<".sql_num_rows($files)."<br />";
					// @unlink($pfad."daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3]);
					$i++;
				}
				//echo $hash_i." - ".$i."<br />";
				if ($hashes_on_server[$hash_i][0]<sql_result($files,$i,"link.id") or $i>=sql_num_rows($files)) {
					ftp_get ($conn_id, $pfad."daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3], $ftp_data["ftp_path"]."/daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3], FTP_BINARY);
					echo $hashes_on_server[$hash_i][1]." neu erstellen (download)<br />";
				}
				if (sql_result($files,$i,"link.id")==$hashes_on_server[$hash_i][0]) {
					if (trim($hashes_on_server[$hash_i][2])!=trim($active_hash)) {
						echo sql_result($files,$i,"link.url").": ".$hashes_on_server[$hash_i][2]."!=".$active_hash." Hash verschieden - lokale Datei loeschen, neu downladen<br />";
						// @unlink($pfad."daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3]);
						ftp_get ($conn_id, $pfad."daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3], $ftp_data["ftp_path"]."/daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3], FTP_BINARY);
					}
					//else
					//	echo "alles ok mit ".sql_result($grafics,$i,"grafik.url")."<br />";
					$i++;
				}
			}
			
			// noch dateien auf Server übrig?
			while ($i<sql_num_rows($files)) {
				echo "del (after) ".sql_result($files,$i,"link.url")." lokal<br />";
				// @unlink($pfad."daten/".$pfadebenen[0]."/".$pfadebenen[1]."/".$pfadebenen[2]."/".$pfadebenen[3]);
				$i++;
			}
			
			
			
			// -------------------- DB auf FTP-Stand setzen -----------------------
			// löschen der Backup-Datei, damit es neu beschrieben werden kann
			@unlink($local_file);
			
			// Öffne eine Datei zum Schreiben
			$downloaded_file_handle = fopen($local_file, 'w');
			
			// Versuche datei vom Server zu laden und in $downloaded_file_handle zu speichern
			if (ftp_fget($conn_id, $downloaded_file_handle, $ftp_data["ftp_path"]."/backup_ohne_dateien.sql", FTP_BINARY, 0)) {
				echo "Erfolgreich in $local_file geschrieben\n";
			} else {
				echo "Download von ftp://".$ftp_data["ftp_server"]."/".$ftp_data["ftp_path"]."/backup_ohne_dateien.sql zu $local_file war nicht m&ouml;glich\n";
			}
			
			
			
			// Verbindung und Verbindungshandler schließen
			ftp_close($conn_id);
			fclose($downloaded_file_handle);
			
			//if (@ftp_put($conn_id, $file, $file, FTP_BINARY)) { print "Server 1 - OK!"; } else { print "Server 1 - Kopieren fehlgeschlagen"; }
			
			if (filesize($local_file)==filesize("ftp://".$ftp_data["ftp_user"].":".$ftp_data["ftp_pwd"]."@".$ftp_data["ftp_server"]."/".$ftp_data["ftp_path"]."/backup_ohne_dateien.sql")) {
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
					$mysql_dumping_code="..\..\mysql\bin\mysql -u".$db_anbindung["benutzer"].$passwd." -h ".$db_anbindung["server"]." lehrer < ".$pfad."backup/backup_ohne_dateien_download.sql";
				else
					$mysql_dumping_code="mysql -u".$db_anbindung["benutzer"].$passwd." -h ".$db_anbindung["server"]." lehrer < ".$pfad."backup/backup_ohne_dateien_download.sql";
				
				system($mysql_dumping_code, $fp);
				//$fp=1;
				if ($fp==0)
					echo "<script>alert('Daten vom Internet-Server importiert'); window.location.href='index.php?tab=einstellungen';</script>";
				else
					echo "Es ist ein Fehler beim Ausf&uuml;hren des MySQL-Befehls aufgetreten.";
			} else {
				echo "Fehler: inkompletter Download (Server-Datei: ".filesize("ftp://".$ftp_data["ftp_user"].":".$ftp_data["ftp_pwd"]."@".$ftp_data["ftp_server"]."/".$ftp_data["ftp_path"]."/backup_ohne_dateien.sql")." Byte - Download: ".filesize($local_file)." Byte)";
			}
		}
		
		if ($_GET["aufgabe"]=="restore") {
            // /usr/bin/
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
                system("..\..\mysql\bin\mysql -u".$db_anbindung["benutzer"].$passwd." -h ".$db_anbindung["server"]." lehrer < ".$pfad."backup/backup_ohne_dateien.sql", $fp);
            else
                system("mysql -u".$db_anbindung["benutzer"].$passwd." -h ".$db_anbindung["server"]." lehrer < ".$pfad."backup/backup_ohne_dateien.sql", $fp);
			if ($fp==0)
                echo "<script>alert('Daten importiert'); window.location.href='index.php?tab=einstellungen';</script>";
            else
                echo "Es ist ein Fehler bei der Wiederherstellung aufgetreten.";
		} ?>
		<div class="tooltip" id="tt_backup" style="width: 600px;">
			<p>Das Backup erfasst alle Daten der Datenbank bis auf die Dateien(!). Wenn Sie also zwischen einer Backupsicherung und -wiederherstellung Arbeitsbl&auml;tter, Folien, Grafiken oder Tests (als Datei) hinzugef&uuml;gt haben, ist der Inhalt zwar vorhanden, aber der Weg &uuml;ber die Datenbank nicht m&ouml;glich.</p>
			<p>Bei einem ernsthaften Backup sollten Sie:
				<ol>
					<li>&uuml;ber diesen Link ein Backup der Datenbank anfertigen</li>
					<li>die Backup-Datei (befindet sich bei <code>Stick:\kreda\xampplite\htdocs\lehrer\backup\backup_ohne_dateien.sql</code>) sichern</li>
					<li>das Verzeichnis <code>Stick:\kreda\xampplite\htdocs\lehrer\daten\</code> (darin befinden sich die Dateien) komplett sichern</li>
				</ol></p></div>
		<fieldset>
		<legend><img src="icons/backup.png" alt="backup" /> Backup</legend>
		<?php
			$ftp_data=db_anbindung();
			$ftp_file_location = "ftp://".$ftp_data["ftp_user"].":".$ftp_data["ftp_pwd"]."@".$ftp_data["ftp_server"]."/".$ftp_data["ftp_path"]."/backup_ohne_dateien.sql";
		?>
		<ul>
			<li><a href="index.php?tab=einstellungen&amp;aufgabe=backup">Datenbank sichern (Backup)</a> <img src="<?php echo $pfad; ?>icons/information.png" class="hilfe_tooltip" alt="Hilfe" onmouseover="showWMTT('tt_backup')" onmouseout="hideWMTT()" /></li>
			<li><a href="index.php?tab=einstellungen&amp;aufgabe=restore" onclick="if (!confirm('Jegliche Daten werden mit dem Backup &uuml;berschrieben. Wollen Sie das wirklich?')) return false;">Datenbank (Lokale Sicherung vom <?php echo date('d. M Y - H:i:s', @filemtime($pfad."backup/backup_ohne_dateien.sql"))." - ".(round(@filesize($pfad."backup/backup_ohne_dateien.sql")/(1024))) . ' KByte';; ?>) wiederherstellen</a></li>
			<li><a href="index.php?tab=einstellungen&amp;aufgabe=restore_ftp" onclick="if (!confirm('Jegliche Daten werden mit dem Backup &uuml;berschrieben. Wollen Sie das wirklich?')) return false;">Datenbank (Internet-Sicherung vom <?php echo date('d. M Y - H:i:s', @filemtime($ftp_file_location))." - ".(round(@filesize($ftp_file_location)/(1024))) . ' KByte'; ?>) wiederherstellen</a></li>
			<li><a href="index.php?tab=einstellungen&amp;aufgabe=ruecksetzen" onclick="if (!confirm('Sind Sie sich Sicher, dass Sie den Reset durchf&uuml;hren wollen?')) return false;" title="Achtung! Nur wenn Sie sich ganz sicher sind, dass sie das tun wollen!">Alles zur&uuml;cksetzen</a></li>
		</ul>
		</fieldset>
    <?php
    }
    ?>
	</div>
    <?php } ?>
  </body>
</html>
