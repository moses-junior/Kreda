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
$titelleiste = "Fehlzeiten";
include $pfad."header.php";
include $pfad."funktionen.php";

if (!proofuser("klasse", $_GET["klasse"]))
	die("Sie sind hierzu nicht berechtigt.");

/*$schueler=db_conn_and_sql("SELECT * FROM `schueler`, `fach_klasse`
	WHERE `schueler`.`klasse`=`fach_klasse`.`klasse`
		AND `fach_klasse`.`id`=".injaway($_GET["klasse"])."
		AND `schueler`.`aktiv`=1
	ORDER BY `schueler`.`position`");
*/
$schueler=db_conn_and_sql("SELECT * FROM `schueler`
	WHERE `schueler`.`klasse`=".injaway($_GET["klasse"])."
		AND `schueler`.`aktiv`=1
	ORDER BY `schueler`.`position`");

// if (isset($_GET["fehlzeit_bis"])) db_conn_and_sql("UPDATE `klasse` SET `fehlzeiten_erledigt_bis`='".$_GET["fehlzeit_bis"]."' WHERE `id`=".$_GET["klasse"]);
$fehlenzeiten_bis=db_conn_and_sql("SELECT `fehlzeiten_erledigt_bis`,`schule` FROM `klasse` WHERE `id`=".injaway($_GET["klasse"]));
$fehlenzeiten_bis=sql_fetch_assoc($fehlenzeiten_bis);

// fuer javascriptuebepruefung und ueberhaupt brauch ich start und ende des Schuljahres
$start_ende=schuljahr_start_ende($aktuelles_jahr,$fehlenzeiten_bis["schule"]);

?>
  <body>
	<script>
		$(function() {
            // datepicker fuer Fehlzeiten
            var append_to_regional = $.datepicker.regional['de'];
            append_to_regional.showOtherMonths=true;
            append_to_regional.selectOtherMonths=true;
            append_to_regional.onSelect=function(selectedDate) {
                var option = this.id == "startdatum" ? "minDate" : "maxDate",
                    instance = $(this).data("datepicker"),
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                fehlzeiten_dates.not(this).datepicker( "option", option, date );
            };
			$.datepicker.setDefaults($.datepicker.regional['']);
            var fehlzeiten_dates = $("#startdatum, #enddatum_text").datepicker(append_to_regional);
            
            if ($( "#startdatum" ).datepicker("getDate")!=null)
                $( "#enddatum_text" ).datepicker("option", "minDate", $( "#startdatum" ).datepicker("getDate") );
            if ($( "#enddatum_text" ).datepicker("getDate")!=null)
                $( "#startdatum" ).datepicker("option", "maxDate", $( "#enddatum_text" ).datepicker("getDate") );
		});
	</script>
	<?php
	if($_GET["ansicht"]!="druck") echo '<div class="inhalt">';
	if ($_GET["eintragen"]=="loeschen" and proofuser("schueler", $_GET["schueler"])) {
		db_conn_and_sql("DELETE FROM `schueler_fehlt` WHERE `schueler`=".injaway($_GET["schueler"])." AND `startdatum`='".injaway($_GET["startdatum"])."'");
		echo '<br /><input type="button" class="button" value="Fenster schlie&szlig;en" onclick="opener.location.reload(); window.close();" />';
	}
	
	if ($_GET["eintragen"]=="true" and proofuser("schueler", $_POST["schueler"])) {
		if ($_POST["enddatum"]=='') $enddatum=$_POST["startdatum"]; else $enddatum=$_POST["enddatum"];
		$startdatum=datum_punkt_zu_strich($_POST["startdatum"]);
		$enddatum=datum_punkt_zu_strich($enddatum);
		
		$fehlzeit_ueberschneidung=false;
		$test=db_conn_and_sql("SELECT * FROM `schueler_fehlt` WHERE `schueler`=".injaway($_POST["schueler"]));
		while ( $row = sql_fetch_assoc ( $test ) ) {
			if ($startdatum>=$row["startdatum"] AND $startdatum<=$row["enddatum"]
					or $enddatum>=$row["startdatum"] AND $enddatum<=$row["enddatum"]
					or $row["startdatum"]>=$startdatum AND $row["startdatum"]<=$enddatum
					or $row["enddatum"]>=$startdatum AND $row["enddatum"]<=$enddatum)
				$fehlzeit_ueberschneidung=true;
			//echo $startdatum." - ".$enddatum." | ".$row->startdatum." - ".$row->enddatum."<br>";
		}
		if ($fehlzeit_ueberschneidung)
			echo '<b>Fehlzeitdatum vom '.injaway($_POST["startdatum"]).' &uuml;berschneidet sich mit einer zuvor eingetragenen Fehlzeit und wurde nicht eingetragen</b>';
		else {
			db_conn_and_sql("INSERT INTO `schueler_fehlt` (`schueler`, `startdatum`, `enddatum`, `nur_stunden`,`entschuldigt`,`bemerkung`) VALUES
			(".injaway($_POST["schueler"]).", ".apostroph_bei_bedarf($startdatum).", ".apostroph_bei_bedarf($enddatum).", ".leer_NULL($_POST["nur_stunden"]).", ".leer_NULL($_POST["entschuldigt"]).", ".apostroph_bei_bedarf($_POST["bemerkung"]).");");
			echo '<b>Fehlzeit vom '.$_POST["startdatum"].' eingetragen</b>';
		}
	}
	
	if ($_GET["eintragen"]=="ueberschreiben" and proofuser("schueler", $_POST["schueler"])) {
		if ($_POST["enddatum"]=='') $enddatum=$_POST["startdatum"]; else $enddatum=$_POST["enddatum"];
		db_conn_and_sql("UPDATE `schueler_fehlt` SET `startdatum`=".apostroph_bei_bedarf(datum_punkt_zu_strich($_POST["startdatum"])).", `enddatum`=".apostroph_bei_bedarf(datum_punkt_zu_strich($enddatum)).", `nur_stunden`=".leer_NULL($_POST["nur_stunden"]).", `entschuldigt`=".leer_NULL($_POST["entschuldigt"]).", `bemerkung`=".apostroph_bei_bedarf($_POST["bemerkung"])."
			WHERE `schueler`=".injaway($_POST["schueler"])." AND `startdatum`=".apostroph_bei_bedarf($_POST["startdatum_old"]));
		echo '<b>Fehlzeit vom '.$_POST["startdatum"].' ge&auml;ndert.</b><script type="text/javascript">opener.location.reload(); window.close();</script>';
	}
		
	if ($_GET["eintragen"]!="uebersicht") {
		if ($_GET["eintragen"]!="ueberschreiben" and $_GET["eintragen"]!="loeschen") {
	?>
	<div id="mf">
		<ul class="r">
			<li><a href="javascript: opener.location.reload(); window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" title="schlie&szlig;en" /> Fenster schlie&szlig;en</a></li>
			<?php if (isset($_GET["startdatum"])) { ?>
                <li><a href="fehlzeiten.php?klasse=<?php echo $_GET["klasse"]; ?>&amp;eintragen=loeschen&amp;schueler=<?php echo $_GET["schueler"]; ?>&amp;startdatum=<?php echo $_GET["startdatum"]; ?>" class="icon" onclick="if (confirm('Wollen Sie die Fehlzeit wirklich l&ouml;schen?')==false) return false;"><img src="<?php echo $pfad; ?>icons/delete.png" alt="delete" title="l&ouml;schen" /> Fehlzeit l&ouml;schen</a></li>
            <?php } ?>
		</ul>
	</div>
	<form action="<?php echo $pfad; ?>formular/fehlzeiten.php?klasse=<?php echo $_GET["klasse"]; ?>&amp;eintragen=<?php if (isset($_GET["startdatum"])) echo "ueberschreiben"; else echo "true"; ?>" method="post" accept-charset="ISO-8859-1">
	<fieldset><legend>Fehlzeit</legend>
	<ol class="divider"><li>
		<?php if (isset($_GET["startdatum"]) and proofuser("schueler", $_GET["schueler"])) {
			$alt=db_conn_and_sql("SELECT * FROM `schueler`, `schueler_fehlt` WHERE `schueler_fehlt`.`schueler`=`schueler`.`id` AND `schueler_fehlt`.`schueler`=".injaway($_GET["schueler"])." AND `schueler_fehlt`.`startdatum`='".injaway($_GET["startdatum"])."'");
			echo '<b>'.html_umlaute(sql_result($alt,0,"schueler.name")).', '.html_umlaute(sql_result($alt,0,"schueler.vorname")).'</b><br />';
			?>
			<input type="hidden" name="schueler" value="<?php echo injaway($_GET["schueler"]) ?>" />
			<input type="hidden" name="startdatum_old" value="<?php echo injaway($_GET["startdatum"]) ?>" />
		<?php } else { ?>
		<label for="schueler">Sch&uuml;ler<em>*</em>:</label>
		<select name="schueler">
		<?php
		for($i=0;$i<sql_num_rows($schueler);$i++) {
			echo '<option value="'.sql_result($schueler,$i,"schueler.id").'">'.sql_result($schueler,$i,"schueler.position").': '.html_umlaute(sql_result($schueler,$i,"schueler.name")).', '.html_umlaute(sql_result($schueler,$i,"schueler.vorname")).'</option>';
		} ?>
		</select><br /> <?php } ?></li>
		<li><label for="startdatum">Datum<em>*</em>:</label> <input type="text" id="startdatum" name="startdatum" value="<?php echo datum_strich_zu_punkt(@sql_result($alt,0,"schueler_fehlt.startdatum")); ?>" size="7" />
		<span id="datum_bis"<?php if(@sql_result($alt,0,"schueler_fehlt.nur_stunden")>0) echo ' style="display:none;"'; ?>>
			bis: <input type="text" name="enddatum" id="enddatum_text" value="<?php echo datum_strich_zu_punkt(@sql_result($alt,0,"schueler_fehlt.enddatum")); ?>" size="7" onchange="document.getElementById('fuer_stunden').style.display=this.value==''?'inline':'none';" /></span>
		<span id="fuer_stunden"<?php if(@sql_result($alt,0,"schueler_fehlt.enddatum")!=@sql_result($alt,0,"schueler_fehlt.startdatum") and @sql_result($alt,0,"schueler_fehlt.startdatum")!='') echo ' style="display:none;"'; ?>>
			<label for="nur_stunden">f&uuml;r</label> <select name="nur_stunden" onchange="document.getElementById('datum_bis').style.display=this.value==''?'inline':'none';"><option value="">-</option>
		<?php for ($m=1;$m<7;$m++) { echo '<option value="'.$m.'"'; if (@sql_result($alt,0,"schueler_fehlt.nur_stunden")==$m) echo ' selected="selected"'; echo '>'.$m.'</option>'; } ?></select> Stunden<br /></span>
		<fieldset><legend>Status<em>*</em></legend>
			<input type="radio" name="entschuldigt" value="0"<?php if (@sql_result($alt,0,"schueler_fehlt.entschuldigt")==0) echo ' checked="checked"'; ?> /> keine Entschuldigung vorliegend<br />
			<input type="radio" name="entschuldigt" value="1"<?php if (@sql_result($alt,0,"schueler_fehlt.entschuldigt")==1) echo ' checked="checked"'; ?> /> entschuldigt<br />
			<input type="radio" name="entschuldigt" value="2"<?php if (@sql_result($alt,0,"schueler_fehlt.entschuldigt")==2) echo ' checked="checked"'; ?> /> krank</fieldset><br />
		<label for="bemerkung">Bemerkung:</label> <input type="text" name="bemerkung" value="<?php echo html_umlaute(@sql_result($alt,0,"schueler_fehlt.bemerkung")); ?>" size="25" /></li></ol>
	<input type="button" class="button" value="eintragen" onclick="auswertung=new Array(new Array(0, 'startdatum','datum','<?php echo $start_ende["start"]; ?>','<?php echo $start_ende["ende"]; ?>')); if (document.getElementById('enddatum_text').value!='') auswertung.push(new Array(0, 'enddatum','datum','<?php echo $start_ende["start"]; ?>','<?php echo $start_ende["ende"]; ?>')); pruefe_formular(auswertung);" />
	</fieldset>
	</form>
	<?php }
	}
	else {
		// Uebersicht
		if (isset($_GET["monat"])) $monat=$_GET["monat"];
		else if ($fehlenzeiten_bis["fehlzeiten_erledigt_bis"]!="") $monat=substr($fehlenzeiten_bis["fehlzeiten_erledigt_bis"],5,2); else $monat=date("n",$timestamp);
		if (isset($_GET["jahr"])) $jahr=$_GET["jahr"];
		else if ($fehlenzeiten_bis["fehlzeiten_erledigt_bis"]!="") $jahr=substr($fehlenzeiten_bis["fehlzeiten_erledigt_bis"],0,4); else $jahr=date("Y",$timestamp);;
		
		if($_GET["ansicht"]=="druck") { ?>
	<div id="mf">
		<ul class="r">
			<li><a id="pv" href="javascript:window.print()">diese Seite drucken</a></li>
			<li><a href="javascript:window.close()" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" title="schlie&szlig;en" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
	<?php } ?>
	
	<h1 style="font-size:12pt; margin-bottom:3px;">Vers&auml;umnisse</h1>
	<table cellspacing="0" class="fehlzeiten">
		<tr>
			<th colspan="32" rowspan="2" style="font-weight: bold; font-size: 12pt;">Monat
				<?php
				$lfd_monat=date("m",mktime(0,0,0,substr($start_ende["start"],5,2),substr($start_ende["start"],8,2),substr($start_ende["start"],0,4)));
				$ende_monat=12+date("m",mktime(0,0,0,substr($start_ende["ende"],5,2),substr($start_ende["ende"],8,2),substr($start_ende["ende"],0,4)));
				echo $monatsnamen_lang[$monat].' '.$jahr; ?>
			</th>
			<th colspan="4" style="font-weight: bold; height: 0.3cm">Monatssumme</th><th colspan="4" style="font-weight: bold;">Gesamtsumme</th></tr>
		<tr><th colspan="2" style="height: 0.3cm">E/K</th><th colspan="2">U</th><th colspan="2">E/K</th><th colspan="2">U</th></tr>
		<tr><td class="nummer" style="width: 1cm;">Nr.</td>
			<?php
			for ($i=0; $i<sql_num_rows($schueler); $i++) {
				$fehlen[$i]=db_conn_and_sql("SELECT * FROM `schueler_fehlt`
					WHERE `schueler_fehlt`.`schueler`=".sql_result($schueler,$i,"schueler.id")."
						AND ((`schueler_fehlt`.`startdatum`>'".$start_ende["start"]."' AND `schueler_fehlt`.`startdatum`<'".$jahr."-".$monat."-32')
							OR (`schueler_fehlt`.`enddatum`>'".$start_ende["start"]."' AND `schueler_fehlt`.`enddatum`<'".$jahr."-".$monat."-32'))
					ORDER BY `schueler_fehlt`.`startdatum`");
			}
			$wochentage=schuljahr_uebersicht($aktuelles_jahr,$fehlenzeiten_bis["schule"]);
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
									$schueler_fehlt[$i][date("j",$wochentage[$tag][$woche]["datum"])]=$a+1; // um 0 auszuschließen - wird danach wieder runtergerechnet
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
			<tr><td title="<?php echo html_umlaute(sql_result($schueler,$i,"schueler.name")); ?>" style="<?php if(floor($i/2)!=$i/2) echo 'background: lightgray;'; if (floor(($i+1)/5)==($i+1)/5) echo ' border-bottom-width: 0.6mm;'; ?>"><?php echo $i+1; ?></td>
				<?php
				for($k=1;$k<32;$k++) if (!$frei_im_monat[$k]) {
					echo '<td style="font-size:6pt;';
					if(floor($i/2)!=$i/2) echo ' background: lightgray;'; if (floor(($i+1)/5)==($i+1)/5) echo ' border-bottom-width: 0.6mm;';
					echo '">';
					if ($schueler_fehlt[$i][$k]>0) {
						if($_GET["ansicht"]!="druck") echo '<a href="fehlzeiten.php?klasse='.$_GET["klasse"].'&amp;schueler='.sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.schueler").'&amp;startdatum='.sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.startdatum").'" onclick="javascript:fenster(\'fehlzeiten.php?klasse='.$_GET["klasse"].'&amp;schueler='.sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.schueler").'&amp;startdatum='.sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.startdatum").'\',\'Fehlzeiten\'); return false;" title="'.html_umlaute(sql_result($schueler,$i,"schueler.vorname")).': '.datum_strich_zu_punkt(sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.startdatum")).' - '.datum_strich_zu_punkt(sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.enddatum")).' '.html_umlaute(@sql_result($fehlen[$i],($schueler_fehlt[$i][$k]-1),"schueler_fehlt.bemerkung")).'">';
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
	
	if($_GET["ansicht"]!="druck") { ?>
	</div>
	<?php } ?>
  </body>
</html>
