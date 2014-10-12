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
include($pfad."funktionen.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
  <head>
    <title>Kreda - Durchf&uuml;hransicht</title>
    <meta name="author" content="Micha Schubert" />
    <meta name="robots" content="noindex, nofollow" />
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="content-language" content="de" />
    <link rel="shortcut icon" href="<?php echo $pfad; ?>look/favicon.ico" type="image/x-icon" />
    <style type="text/css" media="all">@import "<?php echo $pfad; ?>look/format.css";
      td.red td.green td.yellow td.gray {font: 8pt Arial;}
      td.green{background-color:#37c429;}
      td.red{background-color:red;}
      td.yellow{background-color:yellow;}
      td.gray{background-color:lightgray;}
      
      td.aktiver_abschnitt{background: #f9f3ae; font-size: 22px;}
      button.durchfuehrsteuerung{width:50px; height:32px;margin-bottom: 5px;}
    </style>
	<?php
		if (!proofuser("plan",$_GET["plan"]))
			die("Sie sind hierzu nicht berechtigt.");
		
		$plan=planelemente($_GET["plan"],"click to show content",$pfad);
		
		// wenn Sitzplanauswahl geaendert wurde: in DB speichern
		if (isset($_GET["sitzplan"]) and $_GET["sitzplan"]>=1)
			db_conn_and_sql('UPDATE fach_klasse SET sitzplan_klasse='.$_GET["sitzplan"].' WHERE id='.$plan["fach_klasse_id"]);
		
		$sitzplan = db_conn_and_sql('SELECT * FROM fach_klasse, sitzplan_klasse
		   WHERE fach_klasse.id='.$plan["fach_klasse_id"].'
		      AND fach_klasse.sitzplan_klasse=sitzplan_klasse.id');
		$sitzplan_klasse_id=sql_result($sitzplan,0,"sitzplan_klasse.id");
		// Sitzplan geaendert am 31.8.2012
		$sitzplan=db_conn_and_sql("SELECT * FROM sitzplan, sitzplan_klasse WHERE sitzplan_klasse.sitzplan=sitzplan.id AND sitzplan_klasse.klasse=".$plan['klasse_id']." ORDER BY sitzplan.name, sitzplan_klasse.seit DESC");
   	// Sitzplan berechnen - erst einer Fach-Klasse zugeordnet? - dann den neusten Sitzplan der FACH-Klasse waehlen
       /* natuerlich falsch: $sitzplan = db_conn_and_sql ( "SELECT * FROM `sitzplan`,`fach_klasse`, `klasse`,`faecher`
   		WHERE `fach_klasse`.`fach`=`faecher`.`id`
   			AND `plan`.`fach_klasse`=`fach_klasse`.`id`
   			AND `fach_klasse`.`klasse`=`klasse`.`id`
   			AND `klasse`.`id`=".$ansicht['klasse_id']);*/
       // $ansicht['sitzplan']=sql_result($rahmen, 0, 'sitzplan.id');
		$gespeicherter_sitzplan=db_conn_and_sql("SELECT *
		FROM sitzplan_objekt, schueler, sitzplan_platz
		WHERE sitzplan_platz.sitzplan_klasse=".$sitzplan_klasse_id."
		 AND sitzplan_platz.objekt=sitzplan_objekt.id
		 AND sitzplan_platz.schueler=schueler.id
		ORDER BY sitzplan_objekt.pos_y, sitzplan_objekt.pos_x");
		/*$pause[0]=time()-date("H",time())*60*60-date("i",time())*60-date("s",time())+date("H",$plan['abschnitte'][0]['php_zeit'])*60*60+date("i",$plan['abschnitte'][0]['php_zeit'])*60+date("s",$plan['abschnitte'][0]['php_zeit']) + 45*60;
		$i=0;
		while (isset($plan['abschnitte'][$i])) {
		   if ($plan['abschnitte'][$i]['pause']) {
		      $pause[]=time()-date("H",time())*60*60-date("i",time())*60-date("s",time())+date("H",$plan['abschnitte'][$i]['php_zeit'])*60*60+date("i",$plan['abschnitte'][$i]['php_zeit'])*60+date("s",$plan['abschnitte'][$i]['php_zeit']) + 45*60;
		   }
	      $i++;
	   }
		$i=0;
		while (isset($pause[$i]) and $pause[$i]>time()+60*45) {
		   $naechste_pause=$pause[$i]; $i++;
		}*/
		
		//echo date("D m H:i:s",$naechste_pause);
	?>
   <script type="text/javascript" src="<?php echo $pfad; ?>javascript/erweitern.js"></script>
	<script type="text/javascript">
<!--
var pupilwindow = '';
		function OpenPupilwindow () {
			pupilwindow = window.open("durchfuehransicht.php?plan=<?php echo $_GET["plan"]; ?>", "Sch&uuml;leransicht", "height=500,width=1000");
			pupilwindow.focus();
		}
var startzeit = new Date();
var now = new Date();
var klasse_id = <?php echo $plan['klasse_id']; ?>;
var fach_klasse_id = <?php echo $plan['fach_klasse_id']; ?>;
var schueler = new Array(<?php
$schueler = db_conn_and_sql ('SELECT *
   FROM fach_klasse, schueler
	WHERE schueler.klasse=fach_klasse.klasse
		AND fach_klasse.id='.$plan['fach_klasse_id'].'
		AND schueler.aktiv=1
	ORDER BY schueler.vorname, schueler.name, schueler.position');
$schuelergruppe='';
   if (sql_num_rows($gespeicherter_sitzplan)>0) {
   	for ($s=0;$s<sql_num_rows($gespeicherter_sitzplan);$s++)
  	      $schuelergruppe[]=array("id"=>sql_result($gespeicherter_sitzplan,$s,"schueler.id"), "position"=>sql_result($gespeicherter_sitzplan,$s,"schueler.position"), "name"=>sql_result($gespeicherter_sitzplan,$s,"schueler.name"), "vorname"=>sql_result($gespeicherter_sitzplan,$s,"schueler.vorname"));
   	for($s=0; $s<count($schuelergruppe); $s++) {
         if ($s>0) echo ', ';
         echo $schuelergruppe[$s]["id"]; 
      }
   }
   else {
   	for ($s=0;$s<sql_num_rows($schueler);$s++)
   	   if (gehoert_zur_gruppe($plan['fach_klasse_id'],sql_result($schueler,$s,"schueler.id")))
   	      $schuelergruppe[]=array("id"=>sql_result($schueler,$s,"schueler.id"), "position"=>sql_result($schueler,$s,"schueler.position"), "name"=>sql_result($schueler,$s,"schueler.name"), "vorname"=>sql_result($schueler,$s,"schueler.vorname"));
   	if (count($schuelergruppe)>1)
        for($s=0; $s<count($schuelergruppe); $s++) {
            if ($s>0) echo ', ';
                echo $schuelergruppe[$s]["id"]; 
         }
   } ?>);
<?php /* vor allem brauch ich die Sitzplanordnung */
    ?>
var sitzplan_klasse_id = <?php if (sql_num_rows($sitzplan)>0) echo sql_result($sitzplan,0,'sitzplan_klasse.id'); else echo '-1'; ?>;

var token=false;
var token_array = new Array(<?php
   for($s=0; $s<count($schuelergruppe); $s++) {
      if ($s>0) echo ', ';
         echo '0'; 
      } ?>);

<?php
	if (isset($plan['hausaufgaben_kontrolle'])) {
	   $HA_Nummer=0;
   	foreach ($plan['hausaufgaben_kontrolle'] as $value) {
       $vergesser=db_conn_and_sql("SELECT * FROM `hausaufgabe_vergessen`,`schueler` WHERE `hausaufgabe_vergessen`.`erledigt`=0 AND `hausaufgabe_vergessen`.`schueler`=`schueler`.`id` AND `hausaufgabe_vergessen`.`hausaufgabe`=".$value["id"]." ORDER BY `schueler`.`id`");
   	   echo "\n".'var HA_'.$HA_Nummer.'_kontrolliert = 0; '; // ja (teilweise), nein, fertig
   	   echo "\n".'var HA_'.$HA_Nummer.'_array = new Array(';
           for($s=0; $s<count($schuelergruppe); $s++) {
               if ($s>0) echo ', ';
               if (sql_num_rows($vergesser)<1)
                  echo '1';
                else echo '0';
            }
       echo '); ';
   	   echo "\n".'var HA_'.$HA_Nummer.'_array_fertig = new Array(';
           for($s=0; $s<count($schuelergruppe); $s++) {
               if ($s>0) echo ', ';
                  if (sql_num_rows($vergesser)>0)
                    echo 'true';
                  else echo 'false';
            }
       echo '); ';
			for ($i=0;$i<sql_num_rows($vergesser);$i++) {
                $vergessen_anzahl=0;
                for($s=0; $s<count($schuelergruppe); $s++)
                    if ($schuelergruppe[$s]['id']==sql_result($vergesser,$i,"schueler.id")) {
                        echo 'HA_'.$HA_Nummer.'_array['.$s.'] = '.(sql_result($vergesser,$i,"hausaufgabe_vergessen.anzahl")+1).'; '; // schon mal eins vorzaehlen
                        echo 'HA_'.$HA_Nummer.'_array_fertig['.$s.'] = false; ';
                    }
			}
			$HA_Nummer++;
		}
	}


		if (isset($plan['berichtigung_kontrolle'])) {
            $BerUnt_Nummer=0;
			foreach ($plan['berichtigung_kontrolle'] as $berichtigung) {
				$vergesser=db_conn_and_sql("SELECT * FROM `berichtigung_vergessen`,`schueler` WHERE (`berichtigung_vergessen`.`berichtigung_erledigt`=0 OR `berichtigung_vergessen`.`unterschrift_erledigt`=0) AND `berichtigung_vergessen`.`schueler`=`schueler`.`id` AND `berichtigung_vergessen`.`notenbeschreibung`=".$berichtigung["id"]." ORDER BY `schueler`.`position`, `schueler`.`vorname`, `schueler`.`name`");
                echo "\n".'var Ber_'.$BerUnt_Nummer.'_kontrolliert = 0; '; // ja (teilweise), nein, fertig
                echo "\n".'var Unt_'.$BerUnt_Nummer.'_kontrolliert = 0; '; // ja (teilweise), nein, fertig
                echo "\n".'var Ber_'.$BerUnt_Nummer.'_array = new Array(';
                    for($s=0; $s<count($schuelergruppe); $s++) {
                        if ($s>0) echo ', ';
                        if (sql_num_rows($vergesser)<1)
                            echo '1';
                            else echo '0';
                        }
                echo '); ';
                echo "\n".'var Unt_'.$BerUnt_Nummer.'_array = new Array(';
                    for($s=0; $s<count($schuelergruppe); $s++) {
                        if ($s>0) echo ', ';
                        if (sql_num_rows($vergesser)<1)
                            echo '1';
                            else echo '0';
                        }
                echo '); ';
                echo "\n".'var Ber_'.$BerUnt_Nummer.'_array_fertig = new Array(';
                    for($s=0; $s<count($schuelergruppe); $s++) {
                        if ($s>0) echo ', ';
                            if (sql_num_rows($vergesser)>0)
                                echo 'true';
                            else echo 'false';
                        }
                echo '); ';
                echo "\n".'var Unt_'.$BerUnt_Nummer.'_array_fertig = new Array(';
                    for($s=0; $s<count($schuelergruppe); $s++) {
                        if ($s>0) echo ', ';
                            if (sql_num_rows($vergesser)>0)
                                echo 'true';
                            else echo 'false';
                        }
                echo '); ';
       
				   
                for ($i=0;$i<sql_num_rows($vergesser);$i++)
					if (($berichtigung["berichtigung_gefordert"]=="0" and sql_result($vergesser,$i,"berichtigung_vergessen.berichtigung_erledigt")==0) or ($berichtigung["unterschrift_gefordert"]=="0" and sql_result($vergesser,$i,"berichtigung_vergessen.unterschrift_erledigt")==0)) {
                        //$Ber_vergessen_anzahl=0;
                        //$Unt_vergessen_anzahl=0;
                        for($s=0; $s<count($schuelergruppe); $s++)
                            if ($schuelergruppe[$s]['id']==sql_result($vergesser,$i,"schueler.id")) {
                                if ($berichtigung["berichtigung_gefordert"]=="0" and sql_result($vergesser,$i,"berichtigung_vergessen.berichtigung_erledigt")==0) {
                                    echo 'Ber_'.$BerUnt_Nummer.'_array['.$s.'] = '.(sql_result($vergesser,$i,"berichtigung_anzahl.berichtigung_anzahl")+1).'; '; // schon mal eins vorzaehlen
                                    echo 'Ber_'.$BerUnt_Nummer.'_array_fertig['.$s.'] = false; ';
                                }
                                if ($berichtigung["unterschrift_gefordert"]=="0" and sql_result($vergesser,$i,"berichtigung_vergessen.unterschrift_erledigt")==0) {
                                    echo 'Unt_'.$BerUnt_Nummer.'_array['.$s.'] = '.(sql_result($vergesser,$i,"berichtigung_vergessen.unterschrift_anzahl")+1).'; '; // schon mal eins vorzaehlen
                                    echo 'Unt_'.$BerUnt_Nummer.'_array_fertig['.$s.'] = false; ';
                                }
                            }
                        }
                $BerUnt_Nummer++;
			}
		}


?>

var abschnittszeiten = new Array(<?php for ($i=0; $i<count($plan['abschnitte']); $i++) {
   if ($i>0) echo ', ';
   echo $plan['abschnitte'][$i]['minuten'];
} ?>);
var tatsaechlich_benoetigte_sekunden = new Array(<?php for ($i=0; $i<count($plan['abschnitte']); $i++) {
   if ($i>0) echo ', ';
   echo 0;
} ?>);
var uhrzeiten = new Array(<?php for ($i=0; $i<count($plan['abschnitte']); $i++) {
   if ($i>0) echo ', ';
   $differenz_berechnung = explode(":", $plan['abschnitte'][$i]['zeit']);
   echo "new Array(".$differenz_berechnung[0].", ".$differenz_berechnung[1].")";
} ?>);
var puffer = 0;
var aktiv = -1;

function start() {
	time();
	window.setInterval("time()", 6000); // Aktualisierung alle 6 Sekunden
}

function aktives_aendern(neuer_abschnitt) {
   if (aktiv>-1) {
      benoetigte_zeit_des_abschnitts = now.getHours()*60*60+now.getMinutes()*60+now.getSeconds()-startzeit.getHours()*60*60-startzeit.getMinutes()*60-startzeit.getSeconds();
      tatsaechlich_benoetigte_sekunden[aktiv] += benoetigte_zeit_des_abschnitts;
      //document.getElementById("abschnitt_"+ aktiv +"_zeit").className="";
      document.getElementById("abschnitt_"+ aktiv +"_inhalt").className="";
      //document.getElementById("abschnitt_"+ aktiv +"_infos").className="";
      document.getElementById("steuerung_links_"+aktiv).innerHTML = "";
      document.getElementById("steuerung_rechts_"+aktiv).innerHTML = "";
   }
   if (aktiv!=neuer_abschnitt) {
      //document.getElementById("abschnitt_"+ neuer_abschnitt +"_zeit").className="aktiver_abschnitt";
      document.getElementById("abschnitt_"+ neuer_abschnitt +"_inhalt").className="aktiver_abschnitt";
      //document.getElementById("abschnitt_"+ neuer_abschnitt +"_infos").className="aktiver_abschnitt";
   }
   
   startzeit = new Date();
   
   element_3 = document.getElementById("puffer_"+neuer_abschnitt);
   var differenz = (uhrzeiten[neuer_abschnitt][0]-hours)*60 + uhrzeiten[neuer_abschnitt][1]-minutes;
   if (aktiv!=neuer_abschnitt)
      if (differenz<0)
   	   element_3.innerHTML = "<span style='color: red;'>" + differenz + "min</span>";
      else
         element_3.innerHTML = "<span style='color: green;'>+" + differenz + "min</span>";
   
   steuerelement_links = document.getElementById("steuerung_links_"+neuer_abschnitt);
   steuerelement_rechts = document.getElementById("steuerung_rechts_"+neuer_abschnitt);
   if (aktiv!=neuer_abschnitt) {
      //steuerelement_links.innerHTML  = "<button class='durchfuehrsteuerung'><img src='<?php echo $pfad; ?>icons/mitarbeit.png' alt='MA' title='Mitarbeit' /></button><br />";
      //steuerelement_links.innerHTML += "<button class='durchfuehrsteuerung'><img src='<?php echo $pfad; ?>icons/verwarnung.png' alt='Betr' title='Betragen' /></button><br />";
      steuerelement_links.innerHTML += "<button class='durchfuehrsteuerung' onclick='per_sitzplan_eintragen(\"token\", 0, token_array, 0, 0, 0)'><img src='<?php echo $pfad; ?>icons/token.png' alt='Tok' title='Token' /></button><br />";
      /*steuerelement_links.innerHTML += "<button class='durchfuehrsteuerung'><img src='<?php echo $pfad; ?>icons/uhr.png' alt='Uhr' title='Stoppuhr' /></button><br />";
      steuerelement_links.innerHTML += "<button class='durchfuehrsteuerung'><img src='<?php echo $pfad; ?>icons/ampel.png' alt='Amp' title='Ampel' /></button>";*/
   

      /*steuerelement_rechts.innerHTML =  "<button class='durchfuehrsteuerung'><img src='<?php echo $pfad; ?>icons/durchfuehrung.png' alt='Pr&auml;s' title='Pr&auml;sentationsfenster &ouml;ffnen' /></button><br />";
      steuerelement_rechts.innerHTML += "<button class='durchfuehrsteuerung'><img src='<?php echo $pfad; ?>icons/struktur.png' alt='Struktur' title='Struktur' /></button><br />";
      steuerelement_rechts.innerHTML += "<button class='durchfuehrsteuerung'><img src='<?php echo $pfad; ?>icons/' alt='Schw' title='Schwarzbild' /></button><br />";
      steuerelement_rechts.innerHTML += "<button class='durchfuehrsteuerung'><img src='<?php echo $pfad; ?>icons/' alt='Fr' title='Freeze' /></button>";*/
   }
   
   if (aktiv!=neuer_abschnitt)
      aktiv = neuer_abschnitt;
   else
      aktiv = -1;
}

function progressbar(progress) {
   var html = "<table cellspacing='1' cellpadding='0' border='0'>";
   //html += "";
   for( var x = 0; x < 100; x += 10 )
   {
  	   if (progress > 100) {
  	      if (progress > 200) {
         	if( (x+200) < progress )
           		html += "<tr><td class='red' nowrap='nowrap'> </td></tr>";
         	else
           		html += "<tr><td class='yellow' nowrap='nowrap'> </td></tr>";
        	}
        	else {
         	if( (x+100) < progress )
           		html += "<tr><td class='yellow' nowrap='nowrap'> </td></tr>";
         	else
           		html += "<tr><td class='green' nowrap='nowrap'> </td></tr>";
        	}
     	}
     	else {
      	if( x < progress )
        		html += "<tr><td class='green' nowrap='nowrap'> </td></tr>";
      	else
      		html += "<tr><td class='gray' nowrap='nowrap'> </td></tr>";
      }
   }
   //html += "";
   html += "</table>";
   return html;
}

function token_sichtbar(option) {
   if (option=="an_aus")
      if (token) token=false
      else token=true;
   
   if (token) {
      for (i=0; i<schueler.length;i++)
         document.getElementById('schueler_token_'+schueler[i]).style.display='block';
   }
   else 
      for (i=0; i<schueler.length;i++)
         document.getElementById('schueler_token_'+schueler[i]).style.display='none';
}

function time() {
	now = new Date();
	hours = now.getHours();
	minutes = now.getMinutes();
	seconds = now.getSeconds();
   
	thetime = (hours < 10) ? "0" + hours + ":" : hours + ":";
	thetime += (minutes < 10) ? "0" + minutes + ":" : minutes + ":";
	thetime += (seconds < 10) ? "0" + seconds : seconds;
   
   if (aktiv>-1) {
   	var zehntelzeit = Math.round((now.getHours()*60*60+now.getMinutes()*60+now.getSeconds() - startzeit.getHours()*60*60-startzeit.getMinutes()*60-startzeit.getSeconds() + tatsaechlich_benoetigte_sekunden[aktiv])/6)/10; //thetime
   	var zeit_des_aktiven_abschnitts = abschnittszeiten[aktiv];
   	
   	element_progbar = document.getElementById("progressbar_"+aktiv);
   	if (zeit_des_aktiven_abschnitts>0)
      	element_progbar.innerHTML = progressbar(zehntelzeit/zeit_des_aktiven_abschnitts*100);
      
   	element = document.getElementById("time_"+aktiv);
  	   element.innerHTML = zehntelzeit;
  	   
   
   	/*element_2 = document.getElementById("uhrzeit_"+aktiv);
   	element_2.innerHTML = thetime;*/
   
	   //element_4 = document.getElementById("pause_"+aktiv);
   	//element_4.innerHTML = 'Kl: ' + Math.round((<?php echo date("H",$naechste_pause)*60*60+date("i",$naechste_pause)*60+date("s",$naechste_pause); ?>-(now.getHours()*60*60+now.getMinutes()*60+now.getSeconds()))/6)/10;
   
   	//element_5 = document.getElementById("stoppuhr_"+aktiv);
   	//element_5.innerHTML = (now.getHours()-startzeit.getHours()) + ":" + (now.getMinutes()-startzeit.getMinutes()) + ":" + (now.getSeconds()-startzeit.getSeconds());
   }
}

function per_sitzplan_eintragen(typ, nummer, werte, werte2, werte3, werte4) {
   fehlende=window.open("about:blank", "Sitzplan", 'width=800,height=500,left=10,top=20,resizable=yes,scrollbars=yes');
   fehlende.document.write('<html><head></head><body>');
      <?php
      // Sitzplan in einer Tabelle erzeugen
      
      // $gespeicherter_sitzplan ist ganz oben definiert
      
      if (sql_num_rows($gespeicherter_sitzplan)>0) {
         $max_x=0; $max_y=0; $min_x=20; $min_y=20;
         for($i=0; $i<sql_num_rows($gespeicherter_sitzplan); $i++) {
            if (sql_result($gespeicherter_sitzplan,$i,"sitzplan_objekt.pos_x")>$max_x) $max_x=sql_result($gespeicherter_sitzplan,$i,"sitzplan_objekt.pos_x");
            if (sql_result($gespeicherter_sitzplan,$i,"sitzplan_objekt.pos_x")<$min_x) $min_x=sql_result($gespeicherter_sitzplan,$i,"sitzplan_objekt.pos_x");
            if (sql_result($gespeicherter_sitzplan,$i,"sitzplan_objekt.pos_y")>$max_y) $max_y=sql_result($gespeicherter_sitzplan,$i,"sitzplan_objekt.pos_y");
            if (sql_result($gespeicherter_sitzplan,$i,"sitzplan_objekt.pos_y")<$min_y) $min_y=sql_result($gespeicherter_sitzplan,$i,"sitzplan_objekt.pos_y");
         }
         $i=0;
         echo 'fehlende.document.write(\'<table colspan="0" cellpadding="0" border="1px" width="100%">\'); ';
         for($n=$min_y; $n<=$max_y; $n++) {
            echo 'fehlende.document.write(\'<tr>\'); ';
            for($k=$min_x; $k<=$max_x; $k++) {
               echo 'fehlende.document.write(\'<td style="text-align:center; width: 10px; vertical-align:top;\'); ';
               if ($k==sql_result($gespeicherter_sitzplan,$i,"sitzplan_objekt.pos_x")
                     && $n==sql_result($gespeicherter_sitzplan,$i,"sitzplan_objekt.pos_y")) echo 'fehlende.document.write(\'background-color: #DED;\'); ';
               echo 'fehlende.document.write(\'">\'); ';
               if ($k==sql_result($gespeicherter_sitzplan,$i,"sitzplan_objekt.pos_x")
                     && $n==sql_result($gespeicherter_sitzplan,$i,"sitzplan_objekt.pos_y")) {
                  echo 'fehlende.document.write(\''.html_umlaute(sql_result($gespeicherter_sitzplan,$i,"schueler.vorname")).'\'); ';
                  echo 'if (typ=="token") {
                     fehlende.document.write(\'<br /><input type="button" value="+" onclick="opener.token_array['.$i.']+=1; document.getElementById(\\\'schueler_token_'.sql_result($gespeicherter_sitzplan,$i,"schueler.id").'\\\').value=opener.token_array['.$i.'];" /> <select id="schueler_token_'.sql_result($gespeicherter_sitzplan,$i,"schueler.id").'" name="schueler_token_'.sql_result($gespeicherter_sitzplan,$i,"schueler.id").'" onchange="opener.token_array['.$i.']=this.value;">\');
                     for(n=0; n<6; n++) {
                       if (parseInt(n)==parseInt(werte['.$i.']))
                         fehlende.document.write(\'<option selected="selected">\' + n + \'</option>\');
                       else
                         fehlende.document.write(\'<option>\' + n + \'</option>\');
                     }
                     fehlende.document.write(\'</select> <input type="button" value="-" onclick="opener.token_array['.$i.']-=1; document.getElementById(\\\'schueler_token_'.sql_result($gespeicherter_sitzplan,$i,"schueler.id").'\\\').value=opener.token_array['.$i.'];" />\'); }
                     ';
                     
                  echo 'if (typ=="HA") {
                     if (!werte2['.$i.']) {
                        fehlende.document.write(\'<br /><input type="checkbox" name="schueler_ha_fertig"\');
                        if (werte2['.$i.']) fehlende.document.write(\' checked="checked"\');
                        fehlende.document.write(\' onchange="opener.HA_\' + nummer + \'_array_fertig['.$i.']=this.checked==1?true:false; this.checked==1?opener.HA_\' + nummer + \'_array['.$i.']-=1:opener.HA_\' + nummer + \'_array['.$i.']+=1; document.getElementById(\\\'schueler_ha_'.sql_result($gespeicherter_sitzplan,$i,"schueler.id").'\\\').value=opener.HA_\' + nummer + \'_array['.$i.'];" /> \');
                        fehlende.document.write(\'<select id="schueler_ha_'.sql_result($gespeicherter_sitzplan,$i,"schueler.id").'" name="schueler_ha_'.sql_result($gespeicherter_sitzplan,$i,"schueler.id").'" onchange="opener.HA_\' + nummer + \'_array['.$i.']=this.value;">\');
                        for(n=0; n<6; n++) {
                            if (parseInt(n)==parseInt(werte['.$i.']))
                                fehlende.document.write(\'<option selected="selected">\' + n + \'</option>\');
                            else
                                fehlende.document.write(\'<option>\' + n + \'</option>\');
                        }
                        fehlende.document.write(\'</select>\'); }
                    }
                     ';
                     
                  echo 'if (typ=="BerUnt") {
                     if (!werte2['.$i.']) {
                        fehlende.document.write(\'<br />B<input type="checkbox" name="schueler_Ber_fertig"\');
                        if (werte2['.$i.']) fehlende.document.write(\' checked="checked"\');
                        fehlende.document.write(\' onchange="opener.Ber_\' + nummer + \'_array_fertig['.$i.']=this.checked==1?true:false; this.checked==1?opener.Ber_\' + nummer + \'_array['.$i.']-=1:opener.Ber_\' + nummer + \'_array['.$i.']+=1; document.getElementById(\\\'schueler_Ber_'.sql_result($gespeicherter_sitzplan,$i,"schueler.id").'\\\').value=opener.Ber_\' + nummer + \'_array['.$i.'];" /> \');
                        fehlende.document.write(\'<select id="schueler_Ber_'.sql_result($gespeicherter_sitzplan,$i,"schueler.id").'" name="schueler_Ber_'.sql_result($gespeicherter_sitzplan,$i,"schueler.id").'" onchange="opener.Ber_\' + nummer + \'_array['.$i.']=this.value;">\');
                        for(n=0; n<6; n++) {
                            if (parseInt(n)==parseInt(werte['.$i.']))
                                fehlende.document.write(\'<option selected="selected">\' + n + \'</option>\');
                            else
                                fehlende.document.write(\'<option>\' + n + \'</option>\');
                        }
                        fehlende.document.write(\'</select>\'); }
                     if (!werte4['.$i.']) {
                        fehlende.document.write(\'<br />U<input type="checkbox" name="schueler_Unt_fertig"\');
                        if (werte4['.$i.']) fehlende.document.write(\' checked="checked"\');
                        fehlende.document.write(\' onchange="opener.Unt_\' + nummer + \'_array_fertig['.$i.']=this.checked==1?true:false; this.checked==1?opener.Unt_\' + nummer + \'_array['.$i.']-=1:opener.Unt_\' + nummer + \'_array['.$i.']+=1; document.getElementById(\\\'schueler_Unt_'.sql_result($gespeicherter_sitzplan,$i,"schueler.id").'\\\').value=opener.Unt_\' + nummer + \'_array['.$i.'];" /> \');
                        fehlende.document.write(\'<select id="schueler_Unt_'.sql_result($gespeicherter_sitzplan,$i,"schueler.id").'" name="schueler_Unt_'.sql_result($gespeicherter_sitzplan,$i,"schueler.id").'" onchange="opener.Unt_\' + nummer + \'_array['.$i.']=this.value;">\');
                        for(n=0; n<6; n++) {
                            if (parseInt(n)==parseInt(werte3['.$i.']))
                                fehlende.document.write(\'<option selected="selected">\' + n + \'</option>\');
                            else
                                fehlende.document.write(\'<option>\' + n + \'</option>\');
                        }
                        fehlende.document.write(\'</select>\'); }
                    }
                     ';
                     
                  $i++;
               }
               echo 'fehlende.document.write(\'</td>\'); ';
            }
            echo 'fehlende.document.write(\'</tr>\'); ';
         }
         echo 'fehlende.document.write(\'</table>\'); ';
      }
      ?>
   /*fehlende.document.write('<?php foreach($schuelergruppe as $einzelschueler) echo "<tr><td>".$einzelschueler["name"]."</td><td><span id=\"zusatz_schueler_id_".$einzelschueler["id"]."\"></span></td></tr>"; ?>');*/
   fehlende.document.write('</table></body></html>');
   //fehlende=window.open('<?php echo $pfad; ?>formular/sitzplan.php?sitzplan_klasse='+sitzplan_klasse_id , 'Fehlende Sch&uuml;ler', 'width=800,height=500,left=10,top=20,resizable=yes,scrollbars=yes');
   i=0;
   while(i<schueler.length) {
      if (fehlende.document.getElementById("zusatz_schueler_id_"+schueler[i]) !== null)
         fehlende.document.getElementById("zusatz_schueler_id_"+schueler[i]).innerHTML = '<br /><input type="checkbox" value="1" id="fehlender_schueler_'+ schueler[i] +'" />';
      i++;
   }
}

function hausaufgaben_eintragen() {
   hausaufgaben = window.open('<?php echo $pfad; ?>formular/sitzplan.php?sitzplan_klasse='+sitzplan_klasse_id , 'Hausaufgaben eintragen', 'width=800,height=500,left=10,top=20,resizable=yes,scrollbars=yes');
   i=0;
   hausaufgaben.alert(2);
   while(i<schueler.length) {
      if (hausaufgaben.document.getElementById("zusatz_schueler_id_"+schueler[i]) !== null) {
         hausaufgaben.document.getElementById("zusatz_schueler_id_"+schueler[i]).innerHTML = '<br /><input type="button" style="padding-right: 2px; padding-left: 2px;" value="-" />';
         hausaufgaben.document.getElementById("zusatz_schueler_id_"+schueler[i]).innerHTML += '<span id="hausaufgaben_'+ schueler[i] +'">+1</span>';
         hausaufgaben.document.getElementById("zusatz_schueler_id_"+schueler[i]).innerHTML += '<input type="button" style="padding-right: 2px; padding-left: 2px;" value="+" />';
      }
      i++;
   }
}

function in_textfeld_schreiben() {
   var text = 'Auswertung:\nToken: ';
   text += '<?php foreach($schuelergruppe as $value) echo $value['vorname'].', '; ?>';
   for (s=0; s<schueler.length; s++)
      text += token_array[s] + ', ';
   text += '\nHausaufgaben: ';
   <?php
   if (isset($plan['hausaufgaben_kontrolle'])) {
	   $HA_Nummer=0;
   	foreach ($plan['hausaufgaben_kontrolle'] as $value) {
        echo 'text += \'\nHA_'.$value["id"].': \';
   for (s=0; s<schueler.length; s++)
      text += HA_'.$HA_Nummer.'_array[s] + \', \';
';
        $HA_Nummer++;
    }
    // Achtung: nur die HAs eintragen, die anders als 0 vergessen haben.
   }
   ?>
   document.getElementById('notiz_naechste_stunde').value = text;
}

//-->
</script>
</head>
<body onload="start();" onclose="alert('Wollen Sie das wirklich?');">
  <?php
		/*$differenz_berechnung=explode(":",$plan['abschnitte'][$_GET["abschnitt"]]['zeit']);
		if (isset($_GET["abschnitt"])) $differenz=$differenz_berechnung[0]*60-date("H",time())*60+$differenz_berechnung[1]-date("i",time());
		*/
		/*if (isset($_GET["altabschnitt"]))
		   db_conn_and_sql("UPDATE `abschnittsplanung` SET `sekunden_tatsaechlich`=".$_GET["zeit"]." WHERE `plan`=".$_GET["plan"]." AND `abschnitt`=".$plan['abschnitte'][$_GET["altabschnitt"]]['id']);*/
		
	   // function aktiv($derzeit, $hier) { if (isset($derzeit) and $derzeit==$hier) return ' style="background: yellow; font-size: 22px;"'; }
   	// function uhrzeit($derzeit, $hier) { if (isset($derzeit) and $derzeit==$hier) return '<br /><span id="time"></span>'; }
		echo "<h4>".$plan["fach"].": ".$plan["klassenstufe"].$plan["klasse_endung"]." ".$plan["fachklasse_gruppenname"]." - ".$plan["wochentag"].", ".$plan["datum"]."
		- ";
		
	    if (sql_num_rows($schueler)<1)
			echo '<a href="'.$pfad.'formular/schueler_neu.php?klasse='.$plan['klasse_id'].'" onclick="javascript:fenster(this.href,\'Sch&uuml;ler eintragen\'); return false;" class="icon"><img src="'.$pfad.'icons/schueler.png" alt="schueler" title="Sch&uuml;ler hinzuf&uuml;gen" /></a>';
		else
			if (sql_num_rows($sitzplan)>=1) { ?>
				<label for="sitzplan">Sitzplan:</label>
				<select name="sitzplan" onchange="window.location.href = '<?php echo $pfad; ?>lessons/durchfuehransicht_lehrer.php?sitzplan='+this.value+'&amp;plan=<?php echo $_GET["plan"]; ?>'" title="legen Sie hier den Sitzplan der Fach-Klassen-Kombination fest">
					<option value="">-</option>
					<?php
					if (sql_num_rows($sitzplan)>0)
						for ($j=0;$j<sql_num_rows($sitzplan);$j++) {
							echo '<option value="'.sql_result($sitzplan,$j,"sitzplan_klasse.id").'"';
							if (sql_result($sitzplan,$j,"sitzplan_klasse.id")==$sitzplan_klasse_id)
								echo ' selected="selected"';
							echo '>'.html_umlaute(sql_result($sitzplan,$j,"sitzplan.name")).' - '.html_umlaute(sql_result($sitzplan,$j,"sitzplan_klasse.name")).' ('.datum_strich_zu_punkt(sql_result($sitzplan,$j,"sitzplan_klasse.seit")).')</option>';
						} ?></select>
					<?php
				//echo sql_result($sitzplan,0,"sitzplan_klasse.name");
			}
		
		echo '<a href="'.$pfad.'index.php?tab=klassen&amp;option=sitzplan&amp;auswahl='.$plan['klasse_id'].'" onclick="window.opener.location=this.href; return false;" class="icon"><img src="'.$pfad.'icons/sitzplan.png" alt="sitzplan" title="Sitzplan &auml;ndern" /></a>';
		echo "</h4>";
		
		
		
		if (isset($plan['hausaufgaben_kontrolle'])) {
         //$hausaufgaben.= '<button class="durchfuehrsteuerung"><img src="'.$pfad.'icons/hausaufgaben.png" alt="HA/Unt" title="Hausaufgaben / Unterschriften" /></button><br />';
			$hausaufgaben.='<img src="'.$pfad.'icons/hausaufgaben.png" alt="hausaufgaben" title="Hausaufgabenkontrolle" style="float: left;" />
			<ul style="list-style-image:url('.$pfad.'icons/abhaken.png);">';
			$HA_Nummer=0;
			foreach ($plan['hausaufgaben_kontrolle'] as $value) {
			   $HA_Nummer++;
				$hausaufgaben.='<li>'.$HA_Nummer.' '.hausaufgabe_zeigen($value).'';
				if ($value["status"]==-1) {
				   $hausaufgaben.=' <button onclick="per_sitzplan_eintragen(\'HA\', '.($HA_Nummer-1).', HA_'.($HA_Nummer-1).'_array, HA_'.($HA_Nummer-1).'_array_fertig, 0, 0);">eintragen</button>';
				}
				else {
				   //$hausaufgaben.=" Fertig:<br />";
               $hausaufgaben.=' <button onclick="per_sitzplan_eintragen(\'HA\', '.($HA_Nummer-1).', HA_'.($HA_Nummer-1).'_array, HA_'.($HA_Nummer-1).'_array_fertig, 0, 0);">eintragen</button>';				   
				   /*foreach ($schuelergruppe as $einzelschueler) {
				      $hausaufgaben.='<label for="ha_'.$value["id"].'_'.$einzelschueler["id"].'">'.html_umlaute($einzelschueler["vorname"]).' '.html_umlaute(substr($einzelschueler["name"],0,1)).'.</label><input name="ha_'.$value["id"].'_'.$einzelschueler["id"].'" type="checkbox" checked="checked" /> | ';
				   }*/
				}
				$hausaufgaben.="</li>";
			}
			$hausaufgaben.='</ul>';
		}
		
		if (isset($plan['test_rueckgabe']) or isset($plan['berichtigung_kontrolle'])) {
			$tests.= '<img src="'.$pfad.'icons/test.png" alt="test" title="Tests" style="float: left;" />
				<ul style="list-style-image:url('.$pfad.'icons/abhaken.png);">';
            $BerUnt_Nummer=0;
			if (isset($plan['berichtigung_kontrolle']))
			foreach ($plan['berichtigung_kontrolle'] as $berichtigung) {
                $BerUnt_Nummer++;
				$tests.= '<li>'.$BerUnt_Nummer.' '.berichtigung_zeigen($berichtigung).': ';
				   $tests.=' <button onclick="per_sitzplan_eintragen(\'BerUnt\', '.($BerUnt_Nummer-1).', Ber_'.($BerUnt_Nummer-1).'_array, Ber_'.($BerUnt_Nummer-1).'_array_fertig, Unt_'.($BerUnt_Nummer-1).'_array, Unt_'.($BerUnt_Nummer-1).'_array_fertig);">eintragen</button>';
				$tests.= "</li>";
			}
			if (isset($plan['test_rueckgabe']))
			   foreach ($plan['test_rueckgabe'] as $test_rueckgabe) {
   				$tests.= '<li>'.test_zeigen($test_rueckgabe).'</li>';
			}
			$tests.= '</ul>';
		}
		
		if ($plan['struktur']!="")
			$struktur='<img src="'.$pfad.'icons/struktur.png" alt="struktur" title="Struktur" />: '.nl2br($plan['struktur']).'<br />';
		if ($plan['notizen']!="")
			$notizen ='<img src="'.$pfad.'icons/note.png" alt="notizen" title="Notizen" />: '.nl2br($plan['notizen']).'<br />';
		
		echo $hausaufgaben.$tests.$struktur.$notizen;
		
		?>
      <button onclick="javascript:fehlende_schueler_eintragen();" class="durchfuehrsteuerung"><img src="<?php echo $pfad; ?>icons/fehlzeit.png" alt="FZ" title="Fehlzeiten" /></button>
      <button onclick="hausaufgaben_eintragen();" class="durchfuehrsteuerung"><img src="<?php echo $pfad; ?>icons/schuelerliste.png" alt="Liste" title="Sch&uuml;lerliste" /></button>
      <button onclick="OpenPupilwindow();" class="durchfuehrsteuerung"><img src="<?php echo $pfad; ?>icons/durchfuehrung.png" alt="Schueleransicht" title="Sch&uuml;leransicht" /></button>
      
      
		<table class="einzelstunde" cellspacing="0" cellpadding="0" style="clear: both;">
			<tr><th>Zeit</th><th>T&auml;tigkeit</th><th></th></tr>
			<?php for ($i=0;$i<count($plan['abschnitte']);$i++) { ?>
			<tr>
				<td id="abschnitt_<?php echo $i; ?>_zeit" style="text-align: center;">
				   <table><tr><td style="border-width:0px;">
				      <span id="steuerung_links_<?php echo $i; ?>"></span></td>
				      <td style="border-width:0px;">
				        <span id="progressbar_<?php echo $i; ?>"></span></td>
				      <td style="border-width:0px;">
   				    <!--<a name="anker_<?php echo $i; ?>" href="#anker_<?php echo $i; ?>"></a>-->
	   			    <?php echo $plan['abschnitte'][$i]['zeit']."<br /><br />
	   			    <span id=\"time_".$i."\"></span><br />---<br />".$plan['abschnitte'][$i]['minuten']; ?></td>
	   			    </tr>
				    </table>
				    </td>
				<td id="abschnitt_<?php echo $i; ?>_inhalt">
					<?php switch( $plan['abschnitte'][$i]['hefter']) {
					   case 1: echo '<img src="'.$pfad.'icons/merkteil.png" alt="Merkteil" />'; break;
					   case 2: echo '<img src="'.$pfad.'icons/uebungsteil.png" alt="&Uuml;bungsteil" />'; break;} ?>
					<!--<?php echo $plan['abschnitte'][$i]['medium']; ?> / <?php echo $plan['abschnitte'][$i]['sozialform']; ?>: -->
					<?php echo $plan['abschnitte'][$i]['inhalt'];
					if ($plan['abschnitte'][$i]['bemerkung']!="") echo "Kommentar: ".$plan['abschnitte'][$i]['bemerkung']; ?></td>
				<td id="abschnitt_<?php echo $i; ?>_infos" style="text-align: center;">
					<input type="button" class="button" onclick="aktives_aendern(<?php echo $i; ?>); pupilwindow.location.href='durchfuehransicht.php?plan=<?php echo $_GET["plan"];?>&amp;abschnitt=<?php echo $i; ?>'" value="aktiv" />
					<span id="uhrzeit_<?php echo $i; ?>"></span><br />
					<span id="puffer_<?php echo $i; ?>" nowrap="nowrap"></span><br />
					<span id="pause_<?php echo $i; ?>"></span><br />
					<span id="stoppuhr_<?php echo $i; ?>"></span>
					<span id="steuerung_rechts_<?php echo $i; ?>"></span>
					</td>
			</tr><?php } ?>
		</table>
		<?php
		$naechster_test=db_conn_and_sql("SELECT *
			FROM `notentypen`, `notenbeschreibung` LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
			WHERE `notentypen`.`id`=`notenbeschreibung`.`notentyp`
				AND `notenbeschreibung`.`fach_klasse`=".$plan["fach_klasse_id"]."
				AND ((`notenbeschreibung`.`datum`>'".datum_punkt_zu_strich($plan["datum"])."' AND `notenbeschreibung`.`datum`<('".datum_punkt_zu_strich($plan["datum"])."'+ INTERVAL 15 DAY))
					OR (`plan`.`datum`>'".datum_punkt_zu_strich($plan["datum"])."' AND `plan`.`datum`<('".datum_punkt_zu_strich($plan["datum"])."'+ INTERVAL 15 DAY)))");
		if (sql_num_rows($naechster_test)>0) {
			$testankuendigung='<br /><img src="'.$pfad.'icons/test.png" alt="test" title="n&auml;chster Test" style="float: left;" />: ';
			for ($i=0;$i<sql_num_rows($naechster_test);$i++) {
				if ($i>0) $testankuendigung.='<br />';
				$testankuendigung.= '<input type="checkbox" name="test_'.$i.'_angekuendigt" /> '.datum_strich_zu_wochentag(sql_result($naechster_test,$i,"notenbeschreibung.datum").@sql_result($naechster_test,$i,"plan.datum"), "kurzform").', '.datum_strich_zu_punkt(sql_result($naechster_test,$i,"notenbeschreibung.datum")).datum_strich_zu_punkt(@sql_result($naechster_test,$i,"plan.datum")).' '.html_umlaute(sql_result($naechster_test,$i,"notentypen.kuerzel")).' '.html_umlaute(sql_result($naechster_test,$i,"notenbeschreibung.beschreibung"));
			}
		}
		if (isset($plan['hausaufgaben_vergeben'])) {
			$hausaufgabenvergabe.= '<br /><img src="'.$pfad.'icons/hausaufgaben.png" alt="Hausaufgabe" title="Hausaufgabe" style="float: left;" />: '; foreach ($plan['hausaufgaben_vergeben'] as $value) $hausaufgabenvergabe.=hausaufgabe_zeigen($value);
		}
		echo $testankuendigung;
		echo $hausaufgabenvergabe;
		?>
   <br style="clear: both;" />
   <table cellpadding="5" >
   <?php
   $i=0;
   foreach ($schuelergruppe as $einzelschueler) {
		if ($i==0) echo '<tr>';
		echo '<td>'.html_umlaute($einzelschueler["vorname"]).' '.html_umlaute(substr($einzelschueler["name"],0,1)).'.</td><td><input name="schueler_checkbox_'.$einzelschueler["id"].'" type="checkbox" /></td><td><input id="schueler_text_'.$einzelschueler["id"].'" name="schueler_text_'.$einzelschueler["id"].'" type="text" /></td>';
		if ($i==0) $i=1;
		else {
			echo '</tr>';
			$i=0;
		}
   }
   if ($i==1) echo '<td></td></tr>';
   ?>
   </table>
   <label for="notiz_naechste_stunde"><img src="<?php echo $pfad; ?>icons/note.png" alt="Notiz" title="Notizen" />:</label><textarea id="notiz_naechste_stunde" name="notiz_naechste_stunde" cols="50" rows="5"></textarea>
   <button onclick="in_textfeld_schreiben()">speichern</button>
  </body>
</html>
