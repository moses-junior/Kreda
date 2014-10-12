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
$titelleiste = "Lernbereiche-&Uuml;bersicht";
include $pfad."header.php";
include $pfad."funktionen.php";

if (!proofuser("fach_klasse",$_GET["fk"]))
	die("Sie sind hierzu nicht berechtigt.");

?>
  <body>
	<div id="mf">
		<ul class="r">
			<li><a id="pv" href="javascript:window.print()">diese Seite drucken</a></li>
			<li><a href="javascript:window.back();" class="icon"><img src="<?php echo $pfad; ?>icons/pfeil_links.png" alt="zurueck" /> zur&uuml;ck</a></li>
		</ul>
	</div>
		<?php
		$lb_blocke=db_conn_and_sql("SELECT * FROM `faecher`, `klasse`, `fach_klasse`, `lehrplan`, `lernbereich`, `block`
			WHERE `fach_klasse`.`fach`=`faecher`.`id`
				AND `fach_klasse`.`klasse`=`klasse`.`id`
				AND `fach_klasse`.`lehrplan`=`lehrplan`.`id`
				AND `lernbereich`.`lehrplan`=`lehrplan`.`id`
				AND `lernbereich`.`klassenstufe`=(".$aktuelles_jahr."-`klasse`.`einschuljahr`+1)
				AND `block`.`lernbereich`=`lernbereich`.`id`
				AND `block`.`block_hoeher` IS NULL
				AND `fach_klasse`.`id`=".injaway($_GET["fk"])."
			ORDER BY `lernbereich`.`nummer`, `block`.`position`");
		for ($i=0; $i<sql_num_rows($lb_blocke); $i++)
			$block[sql_result($lb_blocke,$i,"block.id")] = array(
				"gesamt_pos"=>$i,
				"lb_pos"=>@sql_result($lb_blocke,$i,"lernbereich.nummer"),
				"wahl"=>sql_result($lb_blocke,$i,"lernbereich.wahl"),
				"block_pos"=>@sql_result($lb_blocke,$i,"block.position"),
				"block_id"=>sql_result($lb_blocke,$i,"block.id"),
				"stunden_uebrig"=>@sql_result($lb_blocke,$i,"block.stunden")+@sql_result($lb_blocke,$i,"block.puffer"),
				"methodisch"=>syntax_zu_html(@sql_result($lb_blocke,$i,"block.methodisch"),1,0,$pfad,'A'),
				"bemerkung"=>syntax_zu_html(@sql_result($lb_blocke,$i,"block.kommentare"),1,0,$pfad,'A'),
				"name"=>html_umlaute(@sql_result($lb_blocke,$i,"block.name")),
				"md_und_kommentare"=>0);
		$eintraege=fachklassen_zeitplanung(injaway($_GET["fk"]),$aktuelles_jahr);
		
		// was wurde inzwischen alles behandelt?
		if (count($eintraege)>1) {
		for ($i=0; $i<count($eintraege);$i++)
			if ($eintraege[$i]["typ"]=="eingetragen" or $eintraege[$i]["typ"]=="zusatz") {
				if ($eintraege[$i]["block_1_id"]>0) {
					$ue_result=db_conn_and_sql("SELECT * FROM `block` WHERE `block`.`id`=".$eintraege[$i]["block_1_id"]);
					if (sql_result($ue_result,0,"block.block_hoeher")>0)
						$ue_1=sql_result($ue_result,0,"block.block_hoeher");
					else $ue_1=$eintraege[$i]["block_1_id"];
				}
				else $ue_1=0;
				
				if ($eintraege[$i]["block_2_id"]>0) {
					$ue_result=db_conn_and_sql("SELECT * FROM `block` WHERE `block`.`id`=".$eintraege[$i]["block_2_id"]);
					if (sql_num_rows($ue_result)>0)
						if (sql_result($ue_result,0,"block.block_hoeher")>0)
							$ue_2=sql_result($ue_result,0,"block.block_hoeher");
						else $ue_2=$eintraege[$i]["block_2_id"];
				}
				else $ue_2=0;
				
				if ($ue_1==$ue_2) $ue_2=0;
				
				if ($ue_2>0) {
					$block[$ue_1]['stunden_uebrig']-=($eintraege[$i]["stunden"]/2);
					$block[$ue_2]['stunden_uebrig']-=($eintraege[$i]["stunden"]/2);
				}
				else $block[$ue_1]['stunden_uebrig']-=$eintraege[$i]["stunden"];
			}
		
		// was fehlt wird aufgefuellt mit:
		if(count($block)>0)
		foreach($block as $value)
			if ($value["stunden_uebrig"]>1) {
				//echo "LB ".$value["lb_pos"].$value["block_pos"].": ".$value["stunden_uebrig"]."<br />";
				// WENN LB VORBEI, DANN NICHT
				$auffuellen[]=$value;
			}
		$auffuell_position=0;
		
	$benutzer=new user();
	echo $benutzer->my["name"].", ".$benutzer->my["vorname"]."<br />";
	?>
	<h1 contenteditable="true">Lehrstoffplanung <?php echo html_umlaute(@sql_result($lb_blocke,0,"faecher.name")); ?> Kl. <?php echo sql_result($lb_blocke,0,"lernbereich.klassenstufe").sql_result($lb_blocke,0,"klasse.endung");
	if (sql_result($lb_blocke,0,"fach_klasse.gruppen_name")!=NULL)
		echo " Gr. ".sql_result($lb_blocke,0,"fach_klasse.gruppen_name"); ?>
		 - <?php echo $aktuelles_jahr."/".($aktuelles_jahr+1); ?></h1>
	<table class="tabelle" cellspacing="0" contenteditable="true">
        <tr><th>Datum<br />(Ustd)</th><th>Lernbereich</th><th>Thema / Methoden / Hilfmittel</th></tr>
		<?php
		// woechentliche Zusammenfassung
		// nicht berücksichtigt sind die methodischen Informationen und Kommentare!
		$woche_zaehler=-1;
		$stunden_der_woche=0;
        for ($i=0; $i<count($eintraege);$i++) {
			if (date("W",$eintraege[$i]['datum'])==$woche_zaehler) {
				$stunden_der_woche+=$eintraege[$i]["stunden"];
				if ($eintraege[$i]["block_1_id"]!=$eintraege[$i+1]["block_1_id"] and $eintraege[$i]["block_1_id"]>0)
					$eintraege[$i+1]["block_1"]=$eintraege[$i]["block_1"].';<br />'.$eintraege[$i+1]["block_1"];
			}
			if (date("W",$eintraege[$i+1]['datum'])!=$woche_zaehler) {
				$woche_zaehler=date("W",$eintraege[$i+1]['datum']);
				$eintraege[$i]["stunden"]=$stunden_der_woche;
				
				// Typ, block.id und alternativtitel verändern, wenn grade der Typ auf Feiertag eingestellt ist
				$hilf=0;
				while ($stunden_der_woche>0 and $eintraege[$i-$hilf]["typ"]=="feiertag") $hilf++;
				if ($eintraege[$i]["typ"]=="feiertag") {
					$eintraege[$i]["typ"]=$eintraege[$i-$hilf]["typ"];
					$eintraege[$i]["block_1_id"]=$eintraege[$i-$hilf]["block_1_id"];
					$eintraege[$i]["block_2_id"]=$eintraege[$i-$hilf]["block_2_id"];
					$eintraege[$i]["alternativtitel"]=$eintraege[$i-$hilf]["alternativtitel"];
				}
				
				$eintraege_hilf[]=$eintraege[$i];
				$stunden_der_woche=0;
			}
		}
		$eintraege=$eintraege_hilf;
        
		for ($i=0; $i<count($eintraege);$i++) { ?>
				<tr><td><?php
				// Datum vom Montag der Woche anzeigen
				echo date("d.m.",$eintraege[$i]["datum"]-24*60*60*(date("w",$eintraege[$i]["datum"])-1));
				if ($eintraege[$i]["stunden"]>0)
					echo "&nbsp;(".$eintraege[$i]["stunden"].")"; ?></td><td>
				<?php
					switch($eintraege[$i]["typ"]) {
						case "zusatz":
						case "eingetragen":
							if ($eintraege[$i]["block_1_id"]>0)
								$lb_von_block1=db_conn_and_sql("SELECT * FROM `block`, `lernbereich` WHERE `block`.`lernbereich`=`lernbereich`.`id` AND `block`.`id`=".$eintraege[$i]["block_1_id"]);
							if ($eintraege[$i]["block_2_id"]>0)
								$lb_von_block2=db_conn_and_sql("SELECT * FROM `block`, `lernbereich` WHERE `block`.`lernbereich`=`lernbereich`.`id` AND `block`.`id`=".$eintraege[$i]["block_2_id"]);
							if ($eintraege[$i]["block_1_id"]>0)
								$block1_hoeher=db_conn_and_sql("SELECT * FROM `block` AS `block_hoeher`, `block` AS `unterblock` WHERE `unterblock`.`block_hoeher`=`block_hoeher`.`id` AND `unterblock`.`id`=".$eintraege[$i]["block_1_id"]);
							if ($eintraege[$i]["block_2_id"]>0)
								$block2_hoeher=db_conn_and_sql("SELECT * FROM `block` AS `block_hoeher`, `block` AS `unterblock` WHERE `unterblock`.`block_hoeher`=`block_hoeher`.`id` AND `unterblock`.`id`=".$eintraege[$i]["block_2_id"]);
							if ($block[$eintraege[$i]["block_1_id"]]["wahl"])
								echo 'W';
							else {
								echo 'LB '.sql_result($lb_von_block1,0,"lernbereich.nummer").' '.sql_result($lb_von_block1,0,"lernbereich.name"); //.'.';
								if ($eintraege[$i]["block_2_id"]>0 and sql_result($lb_von_block2,0,"lernbereich.nummer") and sql_result($lb_von_block1,0,"lernbereich.nummer")!=sql_result($lb_von_block2,0,"lernbereich.nummer"))
									echo '<br />LB '.sql_result($lb_von_block2,0,"lernbereich.nummer").' '.sql_result($lb_von_block2,0,"lernbereich.name");
								/*if (@sql_result($block1_hoeher,0,"unterblock.position")!="")
									echo sql_result($block1_hoeher,0,"block_hoeher.position"); // echo '.'.sql_result($block1_hoeher,0,"unterblock.position");
								else
									echo sql_result($lb_von_block1,0,"block.position");*/
							}
							echo '</td><td>';
							if ($eintraege[$i]["alternativtitel"]!="") echo $eintraege[$i]["alternativtitel"];
							else {
								echo $eintraege[$i]["block_1"];
								if($eintraege[$i]["block_2"]!="")
									echo ' &amp; '.$eintraege[$i]["block_2"];
							}
							//$block[sql_result($lb_von_block1,0,"block.id")]['stunden_uebrig']-=$eintraege[$i]["stunden"];
							$comment_set=0;
							if ((!$block[sql_result($lb_von_block1,0,"block.id")]['md_und_kommentare'] and sql_result($lb_von_block1,0,"block.methodisch").@sql_result($lb_von_block1,0,"block.kommentare")!="")
									or (!$block[@sql_result($lb_von_block2,0,"block.id")]['md_und_kommentare'] and @sql_result($lb_von_block2,0,"block.methodisch").@sql_result($lb_von_block2,0,"block.kommentare")!="")) {
								echo '<div class="lb_kommentare" id="comment_'.$i.'"><a href="#" onclick="document.getElementById(\'comment_'.$i.'\').style.display=\'none\'; return false;" title="f&uuml;r Druck entfernen (wird nicht gel&ouml;scht)"><img src="'.$pfad.'icons/remove.png" /></a>';
								$comment_set=1;
							}
							if (sql_num_rows($lb_von_block1)>0 and !$block[sql_result($lb_von_block1,0,"block.id")]['md_und_kommentare']) {
								echo syntax_zu_html(sql_result($lb_von_block1,0,"block.methodisch"),1,0,$pfad,'A');
								if (sql_result($lb_von_block1,0,"block.methodisch")!="") echo '<br/>';
								echo syntax_zu_html(sql_result($lb_von_block1,0,"block.kommentare"),1,0,$pfad,'A');
								if (sql_result($lb_von_block1,0,"block.kommentare")!="") echo '<br/>';
								$block[sql_result($lb_von_block1,0,"block.id")]['md_und_kommentare']=1;
							}
							if (sql_num_rows($lb_von_block2)>0 and !$block[sql_result($lb_von_block2,0,"block.id")]['md_und_kommentare']) {
								echo syntax_zu_html(sql_result($lb_von_block2,0,"block.methodisch"),1,0,$pfad,'A');
								if (sql_result($lb_von_block2,0,"block.methodisch")!="")
									echo '<br/>';
								echo syntax_zu_html(sql_result($lb_von_block2,0,"block.kommentare"),1,0,$pfad,'A');
								if (sql_result($lb_von_block2,0,"block.kommentare")!="") echo '<br/>';
								$block[sql_result($lb_von_block2,0,"block.id")]['md_und_kommentare']=1;
							}
							
							if ($comment_set)
								echo '</div>';
							break;
						case "frei_fuer_eintragung":
							/*
							if ($auffuell_position<count($auffuellen)) {
								if ($auffuellen[$auffuell_position]['wahl']) echo 'W '.$auffuellen[$auffuell_position]['name'].'</td><td>';
								else echo 'LB '.$auffuellen[$auffuell_position]['lb_pos'].'.'.$auffuellen[$auffuell_position]['block_pos'].' '.$auffuellen[$auffuell_position]['name'].'</td><td>';
								if (!$block[$auffuellen[$auffuell_position]['block_id']]['md_und_kommentare']) {
									echo $auffuellen[$auffuell_position]['methodisch'].'</td><td>'.$auffuellen[$auffuell_position]['bemerkung'].'';
									$block[$auffuellen[$auffuell_position]['block_id']]['md_und_kommentare']=1;
								}
								else echo '</td><td>';
								
								$auffuellen[$auffuell_position]['stunden_uebrig']-=$eintraege[$i]["stunden"];
								if ($auffuellen[$auffuell_position]['stunden_uebrig']<=0) $auffuell_position++;
							}
							else echo '</td><td></td><td>';
							*/
							echo '</td><td>Reserve';
							break;
						case "ausfall":
							echo "<b>".$eintraege[$i]["grund"]."</b></td><td>";
							break;
						case "feiertag":
							echo "<b>".$eintraege[$i]["grund"]."</b></td><td>";
							break;
					} ?></td></tr><?php
			} ?>
      </table>
<?php }
else echo 'Erst Stundenplan erstellen.';
?>
</body>
</html>
