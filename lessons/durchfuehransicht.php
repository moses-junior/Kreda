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
$mit_style=1;
$titelleiste="Durchf&uuml;hransicht";
include $pfad."header.php";
include $pfad."funktionen.php";

?>
	<style type="text/css">
		div.navi {
			margin: 10px;
			text-align: center;
			background-color: lightgray;
		}
		
		div.navi a {
			margin-left: 10px;
		}
		
		span.durchfuehr, span.buch, span.aufgabentext, span.lehrbuch, span.ueberschrift, span.testzeit, span.loesungstext, span.erlaeuterung, span.diskussion, span.merke, span.definition, span.umrandet, code.feste_zeichenbreite, span.sonstiges, span.beschreibung {
			color: #<?php $hgr_farbe='bec'; echo $hgr_farbe; ?>;
			border-bottom: 2px dashed lightgray;
		}
		span.lehrbuch_sel, span.testzeit_sel {
			color: brown;
			border-bottom-width: 0;
		}
		span.ueberschrift_sel {
			font-weight: bold; font-size: 16pt; color: green;
			border-bottom-width: 0;
		}
		span.loesungstext_sel {
			color: gray;
			border-bottom-width: 0;
		}
		span.erlaeuterung_sel, span.diskussion_sel, span.merke_sel, span.definition_sel, span.umrandet_sel, code.feste_zeichenbreite_sel, span.sonstiges_sel, span.beschreibung_sel {
			color: black;
			border-bottom-width: 0;
		}
	</style>
	<?php
		if(!proofuser("plan",$_GET["plan"]))
			die("Sie sind hierzu nicht berechtigt.");
		$plan=planelemente(injaway($_GET["plan"]),"not visible",$pfad);
		/*
		$pause[0]=time()-date("H",time())*60*60-date("i",time())*60-date("s",time())+date("H",$plan['abschnitte'][0]['php_zeit'])*60*60+date("i",$plan['abschnitte'][0]['php_zeit'])*60+date("s",$plan['abschnitte'][0]['php_zeit']) + 45*60;
		$i=0; while (isset($plan['abschnitte'][$i])) { if ($plan['abschnitte'][$i]['pause']) { $pause[]=time()-date("H",time())*60*60-date("i",time())*60-date("s",time())+date("H",$plan['abschnitte'][$i]['php_zeit'])*60*60+date("i",$plan['abschnitte'][$i]['php_zeit'])*60+date("s",$plan['abschnitte'][$i]['php_zeit']) + 45*60;} $i++; }
		$i=0;
		while (isset($pause[$i]) and $pause[$i]>time()+60*45) {$naechste_pause=$pause[$i]; $i++;}
		
		//echo date("D m H:i:s",$naechste_pause);
	?>
	<script type="text/javascript">
<!--
var startzeit = new Date();
var now = new Date();

function start() {
	time();
	window.setInterval("time()", 1000);
}

function time() {
	now = new Date();
	hours = now.getHours();
	minutes = now.getMinutes();
	seconds = now.getSeconds();

	thetime = (hours < 10) ? "0" + hours + ":" : hours + ":";
	thetime += (minutes < 10) ? "0" + minutes + ":" : minutes + ":";
	thetime += (seconds < 10) ? "0" + seconds : seconds;

	element = document.getElementById("time");
	element.innerHTML = Math.round((now.getHours()*60*60+now.getMinutes()*60+now.getSeconds()-startzeit.getHours()*60*60-startzeit.getMinutes()*60-startzeit.getSeconds())/6)/10; //thetime

	element_2 = document.getElementById("uhrzeit");
	element_2.innerHTML = thetime;

	element_4 = document.getElementById("pause");
	element_4.innerHTML = 'Kl: ' + Math.round((<?php echo date("H",$naechste_pause)*60*60+date("i",$naechste_pause)*60+date("s",$naechste_pause); ?>-(now.getHours()*60*60+now.getMinutes()*60+now.getSeconds()))/6)/10;

	element_5 = document.getElementById("stoppuhr");
	//element_5.innerHTML = (now.getHours()-startzeit.getHours()) + ":" + (now.getMinutes()-startzeit.getMinutes()) + ":" + (now.getSeconds()-startzeit.getSeconds());

}

//-->
</script>
</head>
<body onload="start();">
  <?php
		$differenz_berechnung=explode(":",$plan['abschnitte'][$_GET["abschnitt"]]['zeit']);
		if (isset($_GET["abschnitt"])) $differenz=$differenz_berechnung[0]*60-date("H",time())*60+$differenz_berechnung[1]-date("i",time());
		
		if (isset($_GET["altabschnitt"])) db_conn_and_sql("UPDATE `abschnittsplanung` SET `sekunden_tatsaechlich`=".$_GET["zeit"]." WHERE `plan`=".$_GET["plan"]." AND `abschnitt`=".$plan['abschnitte'][$_GET["altabschnitt"]]['id']);
		
	function aktiv($derzeit, $hier) { if (isset($derzeit) and $derzeit==$hier) return ' style="background: lightblue; font-size: 22px; font-family: Helvetica;"'; }
	function uhrzeit($derzeit, $hier) { if (isset($derzeit) and $derzeit==$hier) return '<br /><span id="time"></span>'; }


		echo "<h4>".$plan["fach"].": ".$plan["klassenstufe"].$plan["klasse_endung"]." - ".$plan["datum"]."</h4>"; 
		if (isset($plan['hausaufgaben_kontrolle'])) {
			echo "<b><u>Hausaufgabenkontrolle:</u></b> "; foreach ($plan['hausaufgaben_kontrolle'] as $value) echo hausaufgabe_zeigen($value);
		}
		if (isset($plan['struktur'])) echo "<b>Struktur:</b> ".nl2br(syntax_zu_html($plan['struktur']));
		if (isset($plan['notizen'])) echo "<b>Notizen:</b> ".nl2br($plan['notizen']);
		?>
		<table class="einzelstunde" cellspacing="0" cellpadding="0" style="clear: both;">
			<tr><th>Zeit</th><th>T&auml;tigkeit</th><th></th></tr>
			<?php for ($i=0;$i<count($plan['abschnitte']);$i++) { ?>
			<tr>
				<td<?php echo aktiv($_GET["abschnitt"],$i); ?>><a name="anker_<?php echo $i; ?>" href="#anker_<?php echo $i; ?>"></a><?php echo $plan['abschnitte'][$i]['zeit']."<br />".uhrzeit($_GET["abschnitt"],$i)."/".$plan['abschnitte'][$i]['minuten']; ?></td>
				<td<?php echo aktiv($_GET["abschnitt"],$i); ?>>
					<?php switch( $plan['abschnitte'][$i]['hefter']) { case 1: echo '<img src="./icons/format-text-bold.png" alt="Merkteil" />'; break; case 2: echo '<img src="./icons/format-text-italic.png" alt="&Uuml;bungsteil" />'; break;} ?>
					<!--<?php echo $plan['abschnitte'][$i]['medium']; ?> / <?php echo $plan['abschnitte'][$i]['sozialform']; ?>: -->
					<?php echo $plan['abschnitte'][$i]['inhalt']; if ($plan['abschnitte'][$i]['bemerkung']!="") echo "Kommentar: ".$plan['abschnitte'][$i]['bemerkung']; ?></td>
					<td<?php echo aktiv($_GET["abschnitt"],$i); ?>>
					<?php if ($_GET["abschnitt"]==$i)
							echo '<span id="uhrzeit"></span><br />
							<span id="puffer">Pu: '.$differenz.'</span><br />
							<span id="pause"></span><br />
							<span id="stoppuhr"></span>'; ?>
					<input type="button" onclick="javascript:window.location.href='./durchfuehransicht.php?plan=<?php echo $_GET["plan"]; ?>&amp;altabschnitt=<?php echo $_GET["abschnitt"]; ?>&amp;zeit='+(now.getHours()*60*60+now.getMinutes()*60+now.getSeconds()-startzeit.getHours()*60*60-startzeit.getMinutes()*60-startzeit.getSeconds())+'&amp;abschnitt=<?php echo $i; ?>#anker_<?php echo ($i-2); ?>'" value="aktiv" /></td>
			</tr><?php } ?>
		</table>
		<?php
		if (isset($plan['hausaufgaben_vergeben'])) {
			echo "<b><u>Hausaufgaben:</u></b> "; foreach ($plan['hausaufgaben_vergeben'] as $value) echo hausaufgabe_zeigen($value);
		}
	*/
	if ($_GET["abschnitt"]>0)
		$aktiver_abschnitt=injaway($_GET["abschnitt"]);
	else
		$aktiver_abschnitt=0;
	?>
	</head>
	<body>
		
		<?php echo '<div class="navi">'.$plan["fach"].': '.$plan["klassenstufe"].$plan["klasse_endung"].' - '.$plan["datum"]; ?>
		<a href="<?php echo $pfad; ?>lessons/durchfuehransicht.php?plan=<?php echo $_GET["plan"]; ?>&amp;abschnitt=<?php echo $aktiver_abschnitt-1; ?>" class="icon" onclick="javascript:window.location.href=this.href;"><img src="<?php echo $pfad; ?>icons/pfeil_links.png" alt="pfeil_links" /></a>
		<a href="<?php echo $pfad; ?>lessons/durchfuehransicht.php?plan=<?php echo $_GET["plan"]; ?>&amp;abschnitt=<?php echo $aktiver_abschnitt+1; ?>" class="icon" onclick="javascript:window.location.href=this.href;"><img src="<?php echo $pfad; ?>icons/pfeil_rechts.png" alt="pfeil_rechts" /></a>
		<?php
		if (isset($plan['hausaufgaben_kontrolle']))
			if ($_GET["anzeige"]!='ha_kontrolle') echo '<a href="'.$pfad.'lessons/durchfuehransicht.php?plan='.$_GET["plan"].'&amp;abschnitt='.$aktiver_abschnitt.'&amp;anzeige=ha_kontrolle" class="icon" onclick="javascript:window.location.href=this.href;"><img src="'.$pfad.'icons/hausaufgaben.png" alt="hausaufgaben" title="Hausaufgaben kontrollieren" /></a>';
			else echo '<a href="'.$pfad.'lessons/durchfuehransicht.php?plan='.$_GET["plan"].'&amp;abschnitt='.$aktiver_abschnitt.'" class="icon" onclick="javascript:window.location.href=this.href;"><img src="'.$pfad.'icons/hausaufgaben.png" alt="hausaufgaben" title="Hausaufgaben kontrollieren" /></a>';
		
		if (isset($plan['test_rueckgabe'])) {
			if ($_GET["anzeige"]!='test_rueckgabe') echo '<a href="'.$pfad.'lessons/durchfuehransicht.php?plan='.$_GET["plan"].'&amp;abschnitt='.$aktiver_abschnitt.'&amp;anzeige=test_rueckgabe" class="icon" onclick="javascript:window.location.href=this.href;"><img src="'.$pfad.'icons/test.png" alt="hausaufgaben" title="Test R&uuml;ckgabe" /></a>';
			else echo '<a href="'.$pfad.'lessons/durchfuehransicht.php?plan='.$_GET["plan"].'&amp;abschnitt='.$aktiver_abschnitt.'" class="icon" onclick="javascript:window.location.href=this.href;"><img src="'.$pfad.'icons/test.png" alt="hausaufgaben" title="Test R&uuml;ckgabe" /></a>';
		}
		if (isset($plan['struktur']))
			if ($_GET["anzeige"]!='struktur') echo '<a href="'.$pfad.'lessons/durchfuehransicht.php?plan='.$_GET["plan"].'&amp;abschnitt='.$aktiver_abschnitt.'&amp;anzeige=struktur" class="icon" onclick="javascript:window.location.href=this.href;"><img src="'.$pfad.'icons/struktur.png" alt="struktur" title="Struktur" /></a>';
			else echo '<a href="'.$pfad.'lessons/durchfuehransicht.php?plan='.$_GET["plan"].'&amp;abschnitt='.$aktiver_abschnitt.'" class="icon" onclick="javascript:window.location.href=this.href;"><img src="'.$pfad.'icons/struktur.png" alt="struktur" title="Struktur" /></a>';

		if (isset($plan['notizen']))
			if ($_GET["anzeige"]!='notizen') echo '<a href="'.$pfad.'lessons/durchfuehransicht.php?plan='.$_GET["plan"].'&amp;abschnitt='.$aktiver_abschnitt.'&amp;anzeige=notizen" class="icon" onclick="javascript:window.location.href=this.href;"><img src="'.$pfad.'icons/note.png" alt="notizen" title="Notizen" /></a>';
			else echo '<a href="'.$pfad.'lessons/durchfuehransicht.php?plan='.$_GET["plan"].'&amp;abschnitt='.$aktiver_abschnitt.'" class="icon" onclick="javascript:window.location.href=this.href;"><img src="'.$pfad.'icons/note.png" alt="notizen" title="Notizen" /></a>';
		
		if (isset($plan['hausaufgaben_vergeben']))
			if ($_GET["anzeige"]!='ha_vergeben') echo '<a href="'.$pfad.'lessons/durchfuehransicht.php?plan='.$_GET["plan"].'&amp;abschnitt='.$aktiver_abschnitt.'&amp;anzeige=ha_vergeben" class="icon" onclick="javascript:window.location.href=this.href;"><img src="'.$pfad.'icons/hausaufgaben.png" alt="hausaufgaben" title="Hausaufgaben vergeben" /></a>';
			else echo '<a href="'.$pfad.'lessons/durchfuehransicht.php?plan='.$_GET["plan"].'&amp;abschnitt='.$aktiver_abschnitt.'" class="icon" onclick="javascript:window.location.href=this.href;"><img src="'.$pfad.'icons/hausaufgaben.png" alt="hausaufgaben" title="Hausaufgaben vergeben" /></a>';
		?>
		
		</div>
		<div class="inhalt" style="font-size: 14pt; background-color: #<?php echo $hgr_farbe; ?>;" contenteditable="true">
		<?php
		switch ($_GET["anzeige"]) {
			case 'ha_kontrolle': echo "<b><u>Hausaufgabenkontrolle:</u></b><ul>"; foreach ($plan['hausaufgaben_kontrolle'] as $value) echo '<li>'.hausaufgabe_zeigen($value).'</li>'; echo '</ul>'; break;
			case 'test_rueckgabe': echo "<b><u>Test-R&uuml;ckgabe:</u></b> "; foreach ($plan['test_rueckgabe'] as $value) {echo '<br />'.$value["notentyp_kuerzel"].' '.$value["beschreibung"].' vom '.$value["datum"].': <input type="checkbox" /> Unterschrift / <input type="checkbox" /> Berichtigung bis: <input type="text" size="10" /> ';  echo '<a href="'.$pfad.'formular/test_auswertung.php?beschreibung_id='.$value['id'].'&amp;fuer_schueler=1" onclick="fenster(this.href,\'Test-Auswertung\'); return false;" class="icon" title="Test-Auswertung"><img src="'.$pfad.'icons/test_auswertung.png" alt="A" /></a>'; } break;
			case 'struktur': echo "<b><u>Struktur:</u></b><br /> ".syntax_zu_html($plan['struktur'],1).'<br />'; break;
			case 'notizen': echo '<b><u>Notizen:</u></b><br /> '.nl2br($plan['notizen']).'<br />'; break;
			case 'ha_vergeben': echo "<b><u>Hausaufgaben:</u></b><ul>"; foreach ($plan['hausaufgaben_vergeben'] as $value) echo '<li>'.hausaufgabe_zeigen($value).'</li>'; echo '</ul>'; break;
			default:
				switch( $plan['abschnitte'][$aktiver_abschnitt]['hefter']) {
					case 1: echo '<img src="'.$pfad.'icons/merkteil.png" alt="Merkteil" />'; break; case 2: echo '<img src="'.$pfad.'icons/uebungsteil.png" alt="&Uuml;bungsteil" />'; break;} ?>
							<!--<?php echo $plan['abschnitte'][$aktiver_abschnitt]['medium']; ?> / <?php echo $plan['abschnitte'][$aktiver_abschnitt]['sozialform']; ?>: -->
							<?php echo $plan['abschnitte'][$aktiver_abschnitt]['inhalt'];
								if ($plan['abschnitte'][$aktiver_abschnitt]['bemerkung']!="")
								echo "Kommentar: ".$plan['abschnitte'][$aktiver_abschnitt]['bemerkung'];
				break;
		}
		?>
		</div>
	</body>
</html>
