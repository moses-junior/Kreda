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

?>
	<style type="text/css">
		h2 { display: none; }
		#kopfdaten { float: left; }
		@media print {
			h2 {margin-top: 0px; width: 90%; display: block;}
			#ablage {display: none;}
			#kopfdaten {display: none; }
			#sitzplan_speichern {display: none;}
		}
		.kreda-sitzplan-schueler { z-index: 10; }
	</style>

<?php
// $sitzplan_id kommt von index.php
if (injaway($sitzplan_id)>0 and !proofuser("sitzplan_klasse",$sitzplan_id)) {
	// welcher Sitzplan ist der Klassenlehrer-Sitzplan? - und ist dazu wenigstens Leseberechtigung da?
	if ($sitzplan_id==$school_classes->cont[$school_classes->active]["kl_sitzplan"] and userrigths("sitzplan_von_kl", $sitzplan_id)==0)
		die("Sie sind nicht berechtigt, den Sitzplan zu bearbeiten.");
}

if ($sitzplan_id>0) {
	$sitzplan=db_conn_and_sql("SELECT *
		FROM `sitzplan_klasse`, `sitzplan_objekt`
		WHERE `sitzplan_klasse`.`id`=".$sitzplan_id."
			AND `sitzplan_objekt`.`sitzplan`=`sitzplan_klasse`.`sitzplan`
		ORDER BY `sitzplan_klasse`.`seit` DESC, `sitzplan_objekt`.`id`");
}

if ((isset($_GET["sitzplan_klasse"]) and $_GET["sitzplan_klasse"]==0) or $sitzplan_id>0) {
	$alle_schueler=schueler_von_fachklasse($subject_classes->cont[$subject_classes->active]["id"]);
	
	$uebrige_schueler=array();
	while($einzelschueler=sql_fetch_assoc($alle_schueler)) {
		$uebrige_schueler[]=array(
			"id"=>$einzelschueler["id"],
			"vorname"=>$einzelschueler["vorname"],
			"name"=>$einzelschueler["name"],
			"rufname"=>$einzelschueler["rufname"],
			"pic"=>pictureOfPupil($einzelschueler["name"], $einzelschueler["vorname"], $einzelschueler["number"], $einzelschueler["username"], $pfad, 'height="50"'));
	}
	
	$platz=array();
	for ($i=0;$i<sql_num_rows($sitzplan);$i++) {
		if (sql_result($sitzplan,0,"sitzplan_klasse.seit")==sql_result($sitzplan,$i,"sitzplan_klasse.seit")) {
			$schueler=db_conn_and_sql("SELECT * FROM `sitzplan_platz`,`schueler`
				WHERE `sitzplan_platz`.`objekt`=".sql_result($sitzplan,$i,"sitzplan_objekt.id")."
					AND `sitzplan_platz`.`schueler`=`schueler`.`id`
					AND `sitzplan_platz`.`sitzplan_klasse`=".$sitzplan_id);
			
			// $uebrige_schueler entfernen
			$k=0;
			$such_id=sql_result($schueler,0,"schueler.id");
			while($k<count($uebrige_schueler)) {
				if ($such_id==$uebrige_schueler[$k]["id"]) {
					array_splice($uebrige_schueler, $k, 1);
					break;
				}
				$k++;
			}
			
			$platz[]=array(
				"typ"=>sql_result($sitzplan,$i,"sitzplan_objekt.typ")>0?sql_result($sitzplan,$i,"sitzplan_objekt.typ"):"",
				"id"=>sql_result($sitzplan,$i,"sitzplan_objekt.id"),
				"drehung"=>sql_result($sitzplan,$i,"sitzplan_objekt.drehung"),
				"pos_x"=>sql_result($sitzplan,$i,"sitzplan_objekt.pos_x"),
				"pos_y"=>sql_result($sitzplan,$i,"sitzplan_objekt.pos_y"),
				"schueler_id"=>$such_id,
				"schueler_pic"=>pictureOfPupil(sql_result ( $schueler, 0, 'schueler.name' ), sql_result ( $schueler, 0, 'schueler.vorname' ), sql_result ( $schueler, 0, 'schueler.number' ), sql_result ( $schueler, 0, 'schueler.username' ), $pfad, 'height="50"'),
				"schueler_vorname"=>html_umlaute(sql_result($schueler,0,"schueler.vorname")),
				"schueler_rufname"=>html_umlaute(sql_result($schueler,0,"schueler.rufname")),
				"schueler_name"=>html_umlaute(sql_result($schueler,0,"schueler.name")));
		}
	}
	
	
	if (isset($_GET["sitzplan_klasse"]) and $_GET["sitzplan_klasse"]==0)
		echo '<a href="'.$pfad.'index.php?tab=klassen&amp;auswahl='.$_GET["auswahl"].'&amp;option=sitzplan&amp;neu='.injaway($_GET["art"]).'" class="icon" title="neuen Sitzplan f&uuml;r die gew&auml;hlte Sitzordnung erstellen"><img src="'.$pfad.'icons/neu.png" alt="neu" /> neuen Sitzplan erstellen</a>';
	else {
	
		$objekte=sitzplan_objektzuordnung ($faktor);
		
		echo '<h2>'.html_umlaute(sql_result($sitzplan,0,"sitzplan_klasse.name")).' ('.(substr(datum_strich_zu_punkt(sql_result($sitzplan,0,"sitzplan_klasse.seit")),0,2)+0).'. '.$monatsnamen_kurz[substr(sql_result($sitzplan,0,"sitzplan_klasse.seit"),5,2)+0]." '".substr(sql_result($sitzplan,0,"sitzplan_klasse.seit"),2,2).')</h2>';
		?>
					<script type="text/javascript">
	
var kreda = {
    ui: {
        sitzplan: {
            ablage: "",
            drop: function( event, ui ) {
                var element = ui.draggable.context.outerHTML;
                var id = ui.draggable.context.id;
                var dropable = $( '#' +  this.id );
                ui.draggable.context.remove();
                if(dropable.html() == "")
                {
                    dropable.html(element);
                    var neu = $( '#' + id);
                    neu.css('left', 0);
                    neu.css('top', 0);
                    neu.draggable();
                    return true;
                }
                else
                {
                    kreda.ui.sitzplan.inAblage( dropable.html(), dropable.children().attr("id") );
                    dropable.html( element );
                    var neu = $( '#' + id);
                    neu.css( 'left', 0 );
                    neu.css( 'top', 0 );
                    neu.draggable();
                    return true;
                }
            },
            dropAblage: function( event, ui ) {
                var element = ui.draggable.context.outerHTML;
                var id = ui.draggable.context.id;
                var dropable = $( kreda.ui.sitzplan.ablage );
                ui.draggable.context.remove();
                dropable.append( element );
                var neu = $( '#' + id );
                neu.css('left', 0 );
                neu.css('top', 0 );
                neu.draggable();
                return true;
            },
            inAblage: function( html, id ) {
                $( kreda.ui.sitzplan.ablage ).append( html );
                var neu = $( '#' + id);
                neu.css( 'left', 0 );
                neu.css( 'top', 0 );
                neu.draggable();
            }
		}
	}
}





					$(document).ready(function() {
						$( ".kreda-sitzplan-schueler" ).draggable();
						$( ".kreda-sitz-platz" ).droppable({ drop: kreda.ui.sitzplan.drop });
                        $( "#ablage>.inner" ).droppable({ drop: kreda.ui.sitzplan.dropAblage })
							kreda.ui.sitzplan.ablage = "#ablage>.inner";
					});
					
					function sitzplan_auslesen() {
						var sitzplan = document.getElementById('mein_sitzplan').childNodes;
						var return_value='';
						for (var i=1; i < sitzplan.length; i++) {
							if (sitzplan[i].childNodes[1] && sitzplan[i].childNodes[1].hasChildNodes() && sitzplan[i].childNodes[1].firstChild.getAttribute('id')!="s-") {
								return_value=return_value+"&platz[]="+sitzplan[i].childNodes[1].getAttribute('id').substr(2)+"&schueler[]=" + sitzplan[i].childNodes[1].firstChild.getAttribute('id').substr(2);
							}
						}
						return return_value;
					}
				</script>
			<div class="kreda-sitzplan">
				<div id="ablage" style="width:300px; height: 500px;float: right; background-color: lightgray;">
					<div class="inner">
		<?php
			foreach($uebrige_schueler as $uebrig) {
				if ($uebrig["pic"]!="") {
					$schuelerplatzinhalt=$uebrig["pic"].'<br />';
					if ($uebrig["rufname"]=="") $schuelerplatzinhalt.=substr($uebrig["vorname"],0,12); else $schuelerplatzinhalt.=$uebrig["rufname"];
				}
				else
					$schuelerplatzinhalt='<br />'.substr($uebrig["vorname"],0,12).'<br />'.substr($uebrig["name"],0,12);
				echo '
						<div id="s-'.$uebrig["id"].'" class="kreda-sitzplan-schueler" style="font-size: 10pt; text-align: center; width: '.$faktor.'px;">
							<div id="ss-'.$uebrig["id"].'" class="kreda-schueler-name">'.$schuelerplatzinhalt.'</div>
						</div>';
			} ?>
					</div>
				</div>
			</div>
	<div id="mein_sitzplan">
         <?php
           //  onclick="document.getElementById('form').action += '&amp;'+$( '#sortable2' ).sortable('serialize');"
    $i=0;
	foreach($platz as $einzelplatz) {
		echo '<img src="'.$pfad.'look/sitzplan/'.$objekte[$einzelplatz["typ"]]["name"].'_'.$einzelplatz["drehung"].'.png" alt="sitzplatz" style="position:absolute;
				top: '.($start["y"]+$faktor*$einzelplatz["pos_y"]-($faktor-15)+$objekte[$einzelplatz["typ"]]["drehung"][$einzelplatz["drehung"]]["pos_y"]).'px;
				left: '.($start["x"]+$faktor*$einzelplatz["pos_x"]-($faktor-15)+$objekte[$einzelplatz["typ"]]["drehung"][$einzelplatz["drehung"]]["pos_x"]).'px;
				width: '.$objekte[$einzelplatz["typ"]]["drehung"][$einzelplatz["drehung"]]["width"].'px;" />';
		echo '<div style="position:absolute;
				top: '.($start["y"]+$faktor*$einzelplatz["pos_y"]-($faktor-15)+$objekte[$einzelplatz["typ"]]["drehung"][$einzelplatz["drehung"]]["pos_y"]).'px;
				left: '.($start["x"]+$faktor*$einzelplatz["pos_x"]-($faktor-15)+$objekte[$einzelplatz["typ"]]["drehung"][$einzelplatz["drehung"]]["pos_x"]).'px;
				width: '.$objekte[$einzelplatz["typ"]]["drehung"][$einzelplatz["drehung"]]["width"].'px; height: '.(2*$faktor+15).'px;
				background-image: url('.$pfad.'lookk/sitzplan/'.$objekte[$einzelplatz["typ"]]["name"].'_'.$einzelplatz["drehung"].'.png); background-repeat: no-repeat;
				background-size:'.$objekte[$einzelplatz["typ"]]["drehung"][$einzelplatz["drehung"]]["width"].'px auto;">
			<div id="p-'.$einzelplatz["id"].'" class="kreda-sitz-platz" style="width: '.$faktor.'px; height: '.$faktor.'px; position:relative; left: '.$objekte[$einzelplatz["typ"]]["drehung"][$einzelplatz["drehung"]]["name_x"].'px; top: '.$objekte[$einzelplatz["typ"]]["drehung"][$einzelplatz["drehung"]]["name_y"].'px;">'; // <input type="hidden" name="platz_id_'.$i.'" value="'.$einzelplatz["id"].'" />
		
		// schueler
		echo '<div id="s-'.$einzelplatz["schueler_id"].'" class="kreda-sitzplan-schueler" style="font-size: 10pt; text-align: center; width: '.$objekte[$einzelplatz["typ"]]["drehung"][$einzelplatz["drehung"]]["name_width"].'px;">';
		if ($einzelplatz["schueler_pic"]!=""){
			$schuelerplatzinhalt=$einzelplatz["schueler_pic"].'<br />';
			if ($einzelplatz["schueler_rufname"]=="") $schuelerplatzinhalt.=substr($einzelplatz["schueler_vorname"],0,12);
			else $schuelerplatzinhalt.=$einzelplatz["schueler_rufname"];
		}
		else {
			$schuelerplatzinhalt='<br />';
			if ($einzelplatz["schueler_rufname"]=="") $schuelerplatzinhalt.=substr($einzelplatz["schueler_vorname"],0,12);
			else $schuelerplatzinhalt.=$einzelplatz["schueler_rufname"];
			$schuelerplatzinhalt.='<br />'.substr($einzelplatz["schueler_name"],0,12);
		}
		echo '<div id="ss-'.$einzelplatz["schueler_id"].'" class="kreda-schueler-name">'.$schuelerplatzinhalt.'</div>'; // <input type="hidden" name="platz_'.$i.'" value="'.$einzelplatz["schueler_id"].'" />
		echo '</div>';
		
		echo '</div>
		</div>';
		$i++;
	}
	//echo '<div style="border: black 1px solid; width: 241px; height: 40px;  text-align: center; margin-top: 30px; margin-left: 268px; clear: left;">Sitzplan seit<br />'.datum_strich_zu_punkt(sql_result($sitzplan,0,"sitzplan_klasse.seit")).'</div>';
}
?>
	</div>
	<!--<a href="#" onclick="alert(sitzplan_auslesen()); return false;">auslesen</a>-->
	<button style="margin-left: 20px;" id="sitzplan_speichern" onclick="document.getElementById('form').action += sitzplan_auslesen();"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
<?php
}
else {
	echo 'Um einen Sitzplan zu erstellen, w&auml;hlen Sie zun&auml;chst oben die Sitzanordnung aus.';
} ?>
