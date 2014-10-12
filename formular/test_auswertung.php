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

$pfad="../";
$titelleiste="Test-Auswertung";
$mit_style=true;
include $pfad."header.php";
include $pfad."funktionen.php";

?>
	<style type="text/css">
		<?php
		$schrittweise=array(0.8, 0.6, 0.4, 0.2, 0);
		$achtung_schwelle=1.1; //Zensurunterschied zum Gesamtdurchschnitt bei Extremleistung
		$achtung_schwelle_punkte=3.3;
		$durchschnitt_schwelle=0.5;
		$durchschnitt_schwelle_punkte=1.5;
		$faktor=150;
		$punkteanteil=''; $punkteanteil_alle='';
		$farbe_sehr_schlecht='#eb4d00';
		$farbe_schlecht='#e7db8d';
		$farbe_gut='#dceecd';
		$farbe_sehr_gut='#648f39';
		$farbe_punktanteil_jetzt='#8aa7ba';
		$farbe_punktanteil_gesamt='#c0d6e3';
		
		if ($_GET["fuer_schueler"]) { ?>
		span.durchschnitt {
			color: #<?php $hgr_farbe='fff'; echo $hgr_farbe; ?>;
			background-color: #fff;
			border-bottom: 2px dashed lightgray;
		}
		p.legende {
			visibility: hidden;
		}
		
		table.notenspiegel tr td, table.notenspiegel tr th, table.aufgabenstatistik tr td, table.aufgabenstatistik tr th {
			border: gray dotted 1px;
			border-top-width: 0px;
			border-left-width: 0px;
			padding: 4px;
			color: #<?php echo $hgr_farbe; ?>;
		}
		table.aufgabenstatistik tr td div.jetzt, table.aufgabenstatistik tr td div.alle {
			background-color: #<?php echo $hgr_farbe; ?>;
		}
		table.aufgabenstatistik_zeigen tr td div.jetzt {
			background-color: <?php echo $farbe_punktanteil_jetzt; ?>;
		}
		table.aufgabenstatistik_zeigen tr td div.alle {
			background-color: <?php echo $farbe_punktanteil_gesamt; ?>;
		}
		table.notenspiegel_zeigen tr td, table.aufgabenstatistik_zeigen tr td, table.notenspiegel_zeigen tr th, table.aufgabenstatistik_zeigen tr th {
			border: gray dotted 1px;
			border-top-width: 0px;
			border-left-width: 0px;
			padding: 4px;
			color: black;
		}
		table.schuelerstatistik {
			display: none;
		}
		span.durchschnitt_zeigen {
			color: black;
			border-bottom-width: 0;
		}
		<?php }
		else { ?>
		tabelle.notenspiegel, tabelle.aufgabenstatistik, tabelle.schuelerstatistik {
			border: black solid 0px;
		}
		
		table.notenspiegel tr td, table.notenspiegel tr th, table.aufgabenstatistik tr td, table.aufgabenstatistik tr th, table.schuelerstatistik tr td, table.schuelerstatistik tr th {
			border: gray dotted 1px;
			border-top-width: 0px;
			border-left-width: 0px;
			padding: 4px;
		}
		div.jetzt {
			background-color: <?php echo $farbe_punktanteil_jetzt; ?>;
		}
		div.alle {
			background-color: <?php echo $farbe_punktanteil_gesamt; ?>;
		}
		<?php } ?>
	</style>

  </head>
  <body>
	<div class="inhalt">
	<div id="mf">
		<ul class="r">
			<li><a id="pv" href="javascript:window.print()">diese Seite drucken</a></li>
			<li><a href="javascript:window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
		<?php
		
		if (!proofuser("notenbeschreibung",$_GET["beschreibung_id"]))
			die("Sie sind nicht berechtigt, die Auswertung des Tests zu sehen.");
		
		$notenbeschreibung=db_conn_and_sql("SELECT *
			FROM `bewertungstabelle`,`notenbeschreibung`, `noten`
				LEFT JOIN `note_aufgabe` ON `note_aufgabe`.`note`=`noten`.`id`
			WHERE `bewertungstabelle`.`id`=`notenbeschreibung`.`bewertungstabelle`
			    AND `noten`.`beschreibung`=`notenbeschreibung`.`id`
				AND `notenbeschreibung`.`id`=".injaway($_GET["beschreibung_id"]));
				/*
				LEFT JOIN `gruppe` ON `gruppe`.`fach_klasse`=`fach_klasse`.`id`
				LEFT JOIN `schueler` ON `gruppe`.`schueler`=`schueler`.`id`*/
		$punkte_noten='noten';
		if (sql_result($notenbeschreibung,0,"bewertungstabelle.punkte"))
			$punkte_noten='punkte';
		
		$notenansicht=noten_von_fachklasse(sql_result($notenbeschreibung,0,"notenbeschreibung.fach_klasse"),$aktuelles_jahr);
		
		if (@sql_result($notenbeschreibung,0,"note_aufgabe.punkte")!="")
		for ($i=0;$i<sql_num_rows($notenbeschreibung);$i++) {
			@$aufgabenstatistik[sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabe")][]=array(
				"aufgaben_id"=>sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabe"),
				"punkte_anteil"=>sql_result($notenbeschreibung,$i,"note_aufgabe.punkte")/sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabenpunkte")
				);
			if (empty($aufgabenstatistik[sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabe")]["alle"])) {
				$aufgabenstatistik[sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabe")]["punkte"]=sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabenpunkte");
				$aufgabenstatistik[sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabe")]["aufgabe_id"]=sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabe");
				//Alle
				if (sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabe")) {
					$zugeordneter_plan=sql_result($notenbeschreibung,$i,"notenbeschreibung.plan");
					if ($zugeordneter_plan=="")
						$zugeordneter_plan="NULL";
					$aufgabenstatistik_alle=db_conn_and_sql("SELECT * FROM `note_aufgabe`
						LEFT JOIN `test_aufgabe` ON `test_aufgabe`.`aufgabe`=`note_aufgabe`.`aufgabe`
						LEFT JOIN `test_abschnitt` ON `test_abschnitt`.`test`=`test_aufgabe`.`test`
						LEFT JOIN `abschnittsplanung` ON `test_abschnitt`.`abschnitt`=`abschnittsplanung`.`abschnitt` AND `abschnittsplanung`.`plan`=".$zugeordneter_plan."
					WHERE `note_aufgabe`.`aufgabe`=".sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabe"));
				}
				else
					$aufgabenstatistik_alle='';
				//$aufgabenstatistik[sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabe")]["pos_a"]=@sql_result($aufgabenstatistik_alle,0,"test_aufgabe.position");
				//$aufgabenstatistik[sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabe")]["pos_b"]=@sql_result($aufgabenstatistik_alle,0,"test_aufgabe.position_b");
				//$aufgabenstatistik[sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabe")]["zusatz"]=@sql_result($aufgabenstatistik_alle,0,"test_aufgabe.zusatzaufgabe");
				if (sql_num_rows($aufgabenstatistik_alle)>0)
				for ($n=0;$n<sql_num_rows($aufgabenstatistik_alle);$n++) {
					$pos=0;
					while(sql_result($aufgabenstatistik_alle,$n,"note_aufgabe.punkte")/sql_result($aufgabenstatistik_alle,$n,"note_aufgabe.aufgabenpunkte")<$schrittweise[$pos]
							and $pos<=count($schrittweise))
						$pos++;
					$aufgabenstatistik[sql_result($notenbeschreibung,$i,"note_aufgabe.aufgabe")]["alle"][$pos]++;
				}
			}
		}
        
        // Position und Zusatzaufgabe muss separat eingetragen werden, da zuvor jegliche Tests betrachtet werden. Da kommt es vor, dass eine Aufgabe im anderen Test auf anderer Position kam.
        $testaufgaben=db_conn_and_sql("SELECT * FROM notenbeschreibung, test_aufgabe
			WHERE notenbeschreibung.id=".injaway($_GET["beschreibung_id"])." AND notenbeschreibung.test=test_aufgabe.test");
        if (sql_num_rows($testaufgaben)>1)
            for ($i=0;$i<sql_num_rows($testaufgaben);$i++) {
                $aufgabenstatistik[sql_result($testaufgaben,$i,"test_aufgabe.aufgabe")]["pos_a"]=@sql_result($testaufgaben,$i,"test_aufgabe.position");
                $aufgabenstatistik[sql_result($testaufgaben,$i,"test_aufgabe.aufgabe")]["pos_b"]=@sql_result($testaufgaben,$i,"test_aufgabe.position_b");
                $aufgabenstatistik[sql_result($testaufgaben,$i,"test_aufgabe.aufgabe")]["zusatz"]=@sql_result($testaufgaben,$i,"test_aufgabe.zusatzaufgabe");
            }
        
        // $aufgabenstatistik nach position_a ordnen
        $hilf='';
        $i=0;
        if (count($aufgabenstatistik)>1) {
			while (count($hilf)<count($aufgabenstatistik) and $i<100) {
				foreach ($aufgabenstatistik as $value) {
					if ($value["pos_a"]==$i) {
						//echo $value["aufgabe_id"]."; ".$value["pos_a"]." - ".count($hilf)." - ".count($aufgabenstatistik)."<br>";
						$hilf[]=$value;
					}
				}
				$i++;
			}
			$aufgabenstatistik=$hilf;
		}
		
		// Ueberschrift und Notenspiegel
		if (count($notenansicht['schueler'])>0) {
			$i=0;
			while($notenansicht['notenbeschreibung'][$i]['id']!=$_GET["beschreibung_id"]) $i++;
			?>
			
				<?php echo '<h2>'.$notenansicht['notenbeschreibung'][$i]['notentyp_kuerzel'].' '.$notenansicht['notenbeschreibung'][$i]['beschreibung'];
				if ($notenansicht['notenbeschreibung'][$i]['kommentar']!="") echo ' ('.$notenansicht['notenbeschreibung'][$i]['kommentar'].')';
				echo ' '.$notenansicht['notenbeschreibung'][$i]['datum']; ?>
			<?php
			if ($notenansicht['notenbeschreibung'][$i]['notenspiegel']!=false) {
				if ($notenansicht['notenbeschreibung'][$i]['durchschnitt']>0) {
					$gesamt_schnitt=0; $zaehler=0;
					for ($n=0;$n<count($notenansicht['schueler']);$n++) { if ($notenansicht['schueler'][$n]['ganzjahres_schnitt']!="") $zaehler++; $gesamt_schnitt+=$notenansicht['schueler'][$n]['ganzjahres_schnitt'];}
					$gesamt_durchschnitt=($gesamt_schnitt/$zaehler);
					echo ' - <span';
					if (!$_GET["fuer_schueler"]) {
						echo ' style="';
						if ($punkte_noten=='punkte') {
							if ($notenansicht['notenbeschreibung'][$i]['durchschnitt']<$gesamt_durchschnitt-$durchschnitt_schwelle_punkte)
								echo 'background-color: '.$farbe_sehr_schlecht.';';
							else if ($notenansicht['notenbeschreibung'][$i]['durchschnitt']<$gesamt_durchschnitt)
								echo 'background-color: '.$farbe_schlecht.';';
							else if ($notenansicht['notenbeschreibung'][$i]['durchschnitt']<$gesamt_durchschnitt+$durchschnitt_schwelle_punkte)
								echo 'background-color: '.$farbe_gut.';';
							else
								echo 'background-color: '.$farbe_sehr_gut.';';
						}
						else {
							if ($notenansicht['notenbeschreibung'][$i]['durchschnitt']>$gesamt_durchschnitt+$durchschnitt_schwelle)
								echo 'background-color: '.$farbe_sehr_schlecht.';';
							else if ($notenansicht['notenbeschreibung'][$i]['durchschnitt']>$gesamt_durchschnitt)
								echo 'background-color: '.$farbe_schlecht.';';
							else if ($notenansicht['notenbeschreibung'][$i]['durchschnitt']>$gesamt_durchschnitt-$durchschnitt_schwelle)
								echo 'background-color: '.$farbe_gut.';';
							else
								echo 'background-color: '.$farbe_sehr_gut.';';
						}
						echo '"';
					}
					echo ' class="durchschnitt" onclick="this.className=\'durchschnitt_zeigen\'">&Oslash; '.number_format ($notenansicht['notenbeschreibung'][$i]['durchschnitt'], 2, ',', '.' ).'</span>';
				}
				echo '</h2>
				<div class="nicht_drucken, hinweis" style="float: right;">
				<b>Farb-Legende</b> (bewegen Sie die Maus &uuml;ber eine Farbe):<br />
				Punkt-Anteile:
				<span style="background-color: '.$farbe_punktanteil_jetzt.'; width: 10px; height: 10px; margin: 4px; margin-bottom: 0px;" title="Anteil an Sch&uuml;lern der Klasse, die x% der Aufgabe bew&auml;ltigten.">Klasse</span>
				<span style="background-color: '.$farbe_punktanteil_gesamt.'; width: 10px; height: 10px; margin: 4px; margin-bottom: 0px;" title="Anteil an Sch&uuml;lern insgesamt, die x% der Aufgabe bew&auml;ltigten.">insgesamt</span><br />
				Zensur(en) im Verh&auml;ltnis:
					<span style="background-color: '.$farbe_sehr_schlecht.'; width: 10px; height: 10px;" title="viel schlechter als sonst (Grenze beim Gesamtdurchschnitt: '.$durchschnitt_schwelle.'; Grenze bei Einzelnoten: '.$achtung_schwelle.')">sehr schlecht</span>
					<span style="background-color: '.$farbe_schlecht.'; width: 10px; height: 10px;" title="minimal schlechter als sonst">schlecht</span>
					<span style="background-color: '.$farbe_gut.'; width: 10px; height: 10px;" title="minimal besser als sonst">gut</span>
					<span style="background-color: '.$farbe_sehr_gut.'; width: 10px; height: 10px;" title="viel besser als sonst (Grenze beim Gesamtdurchschnitt: '.$durchschnitt_schwelle.'; Grenze bei Einzelnoten: '.$achtung_schwelle.')">sehr gut</span>
				</div>

				<span style="margin-right: 20px;">
				<table class="notenspiegel" onclick="this.className=\'notenspiegel_zeigen\'"><tr><th>Note</th>';
				foreach ($notenansicht['notenbeschreibung'][$i]['notenspiegel'] as $n) {
					echo '<td>'.$n['note'].'</td>';
				}
				echo '</tr><tr><th>Anzahl</th>';
				$punktespiegel=false;
				foreach ($notenansicht['notenbeschreibung'][$i]['notenspiegel'] as $n) {
					echo '<td>'.$n['anzahl_schueler'].'</td>';
					if($n['punkte_bis_zahl']>0) $punktespiegel=true;
				}
				if($punktespiegel) {
					echo '</tr><tr><th>Punkte n&ouml;tig</th>';
					foreach ($notenansicht['notenbeschreibung'][$i]['notenspiegel'] as $n)
						echo '<td>-'.$n['punkte_bis'].'</td>';
				}
				echo '</tr></table>
				</span>
				';
			}
			?>
		<?php }
		
		// Aufgabenuebersichts-Tabelle
		$aufgaben_zaehler=0;
		
		if (count($aufgabenstatistik)>1) {
			echo '<table class="aufgabenstatistik" onclick="this.className=\'aufgabenstatistik_zeigen\'"><tr><th></th>';
			foreach($aufgabenstatistik as $value)
				if ($value["punkte"]>0) {
					//notenbeschreibung-Schueler
					if ($value["pos_b"]>0)
						$pos_b_angeben='<br />B: '.$value["pos_b"];
					else
						$pos_b_angeben='';
					echo '<th>A: '.($value["pos_a"]).$pos_b_angeben.'<br />('.($value["punkte"]+0).' ';
					if ($value["zusatz"])
						echo ' Zus';
					else
						echo 'P.';
					echo ')</th>';
					
					for ($i=0;$i<count($value);$i++)
						if (isset($value[$i]["punkte_anteil"])) {
							$pos=0;
							while($value[$i]["punkte_anteil"]<$schrittweise[$pos] and $pos<=count($schrittweise))
								$pos++;
							$punkteanteil[$aufgaben_zaehler]["einzel"][$pos]++;
						}
					
					$punkteanteil[$aufgaben_zaehler]["alle"]=$value["alle"];
					$aufgaben_zaehler++;
				}
			echo '</tr>';
			
			if (count($punkteanteil)>=1) {
				for($n=0;$n<count($punkteanteil);$n++) if (count(@$punkteanteil[$n]["einzel"])>0) {
					$gesamtzahl[$n]=0; $gesamtzahl_alle[$n]=0;
					for($pos=0;$pos<count($schrittweise);$pos++) {
						$gesamtzahl[$n]+=$punkteanteil[$n]["einzel"][$pos];
						$gesamtzahl_alle[$n]+=$punkteanteil[$n]["alle"][$pos];
					}
					if ($gesamtzahl_alle[$n]==0)
						$gesamtzahl_alle[$n]=1; // Division durch 0 vermeiden
				}
				
				for($pos=0;$pos<count($schrittweise);$pos++) {
					echo '<tr><td>-'.($schrittweise[$pos]*100).'%</td>';
					for($n=0;$n<count($punkteanteil);$n++)
						echo '<td style="text-align: center;"><div class="jetzt" style="width: '.(($punkteanteil[$n]["einzel"][$pos]+0)/$gesamtzahl[$n]*$faktor).'px; height: 10px;">'.($punkteanteil[$n]["einzel"][$pos]+0).'</div>
						<div class="alle" style="width: '.(($punkteanteil[$n]["alle"][$pos]+0)/$gesamtzahl_alle[$n]*$faktor).'px; height: 7px;"></div></td>';
					echo '</tr>';
				}
			}
			echo '</table>';
		}
		?>
	<br />
    <table class="schuelerstatistik" onclick="this.className='schuelerstatistik_zeigen'" cellspacing="0">
      <tr>
        <th>Sch&uuml;ler</th><?php
		
		if (count($notenansicht['schueler'])>0) {
			$i=0;
			while($notenansicht['notenbeschreibung'][$i]['id']!=$_GET["beschreibung_id"]) $i++;
			$aufgaben_zaehler=0;
			if (count($aufgabenstatistik)>1)
				foreach($aufgabenstatistik as $value) if ($value["punkte"]>0) {
					//notenbeschreibung-Schueler
					echo '<th>Aufg '.($value["pos_a"]);
					if ($value["pos_b"]>0) echo '<br />B: '.$value["pos_b"];
					echo '<br />'.($value["punkte"]+0).' P.</th>';
					$aufgaben_zaehler++;
				}
		echo '<th>Gesamt-P.</th><th>Note</th><th>&Oslash; '.number_format ($gesamt_durchschnitt, 2, ',', '.' ).'</th></tr>';
			for($n=0;$n<count($notenansicht['schueler']);$n++)
				if(gehoert_zur_gruppe(sql_result($notenbeschreibung,0,"notenbeschreibung.fach_klasse"), $notenansicht['schueler'][$n]['id'])) { ?>
				<tr><td><?php echo $notenansicht['schueler'][$n]['position'].'&nbsp;'.$notenansicht['schueler'][$n]['name'].',&nbsp;'.$notenansicht['schueler'][$n]['vorname']; ?></td>
				<?php
				$aufgaben_zaehler=0;
				if (count($aufgabenstatistik)>1)
					foreach($aufgabenstatistik as $value) if ($value["punkte"]>0) {
						$k=0;
						while($k<=count($notenansicht['schueler'][$n]['noten'][$i]['einzelpunkte']) and $value["aufgabe_id"]!=$notenansicht['schueler'][$n]['noten'][$i]['einzelpunkte'][$k]["aufg_id"]) $k++;
						echo '<td style="text-align: center;">';
						if ($value["aufgabe_id"]!=$notenansicht['schueler'][$n]['noten'][$i]['einzelpunkte'][$k]["aufg_id"]) echo '-';
						else echo kommazahl($notenansicht['schueler'][$n]['noten'][$i]['einzelpunkte'][$k]["pkt"]+0);
						echo '</td>';
						$aufgaben_zaehler++;
					}
				
                $zusatzpunkte='';
                if ($notenansicht['schueler'][$n]['noten'][$i]['zusatzpunkte']!=0)
                    $zusatzpunkte=' <span style="font-size:8px; color: gray;">['.kommazahl($notenansicht['schueler'][$n]['noten'][$i]['punkte']).'+('.kommazahl($notenansicht['schueler'][$n]['noten'][$i]['zusatzpunkte']).')]</span>';
                    
				echo '<td>'.$notenansicht['schueler'][$n]['noten'][$i]['punktzahl_mit_komma'].$zusatzpunkte.'</td>';
				
				?><td style="<?php
					if ($notenansicht['schueler'][$n]['noten'][$i]['wert']>0)
						if ($punkte_noten=='punkte') {
							if ($notenansicht['schueler'][$n]['noten'][$i]['wert']<$notenansicht['schueler'][$n]['ganzjahres_schnitt']-$achtung_schwelle_punkte)
								echo 'background-color: '.$farbe_sehr_schlecht.';';
							else if ($notenansicht['schueler'][$n]['noten'][$i]['wert']<$notenansicht['schueler'][$n]['ganzjahres_schnitt'])
								echo 'background-color: '.$farbe_schlecht.';';
							else if ($notenansicht['schueler'][$n]['noten'][$i]['wert']<$notenansicht['schueler'][$n]['ganzjahres_schnitt']+$achtung_schwelle_punkte)
								echo 'background-color: '.$farbe_gut.';';
							else
								echo 'background-color: '.$farbe_sehr_gut.';';
						}
						else {
							if ($notenansicht['schueler'][$n]['noten'][$i]['wert']>$notenansicht['schueler'][$n]['ganzjahres_schnitt']+$achtung_schwelle)
								echo 'background-color: '.$farbe_sehr_schlecht.';';
							else if ($notenansicht['schueler'][$n]['noten'][$i]['wert']>$notenansicht['schueler'][$n]['ganzjahres_schnitt'])
								echo 'background-color: '.$farbe_schlecht.';';
							else if ($notenansicht['schueler'][$n]['noten'][$i]['wert']>$notenansicht['schueler'][$n]['ganzjahres_schnitt']-$achtung_schwelle)
								echo 'background-color: '.$farbe_gut.';';
							else
								echo 'background-color: '.$farbe_sehr_gut.';';
						}
					?> text-align: center;"><?php echo $notenansicht['schueler'][$n]['noten'][$i]['wert'].'<sup>'.$notenansicht['schueler'][$n]['noten'][$i]['notenzusatz'].'</sup>'; ?></td>
				<td><?php
					if ($notenansicht['notenbeschreibung'][0]['punkte_oder_zensuren']==1)
						echo $notenansicht['schueler'][$n]['halbjahr_2_schnitt_komma'];
					else
						echo "|".$notenansicht['schueler'][$n]['ganzjahres_schnitt_komma']; ?></td>
				</tr><?php
			}
		} ?>
    </table>

	</div>
  </body>
</html>
