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
	
	$my_user=new user();
	$fach_klasse=injaway($_GET["fach_klasse"]);
	$rahmen=injaway($_GET["rahmen"]);
	$schule=$my_user->my["letzte_schule"];

	// Eintragung vornehmen
	if ($_GET["eintragen"]=="true") {
		$fach_klasse=injaway($_POST["fach_klasse"]);
		$rahmen=injaway($_POST["rahmen"]);
		
		if (!proofuser("fach_klasse", $fach_klasse))
			die("Diese Fach-Klasse-Kombination geh&ouml;rt Ihnen nicht.");
			
		// Falls ein YubiKey beim Benutzer eingetragen ist, soll dieser hier ueberprueft werden
		if ($my_user->my["token_id"]!="") {
			$otp=$_POST["token_otp"];
			
			if (substr ($otp, 0, 12) == $my_user->my["token_id"]) {
				require_once $pfad."login/yubikeyPHPclass/Yubikey.php";
				
				$token = new Yubikey(YUBIKEY_API_ID, YUBIKEY_SIGNATURE_KEY);
				
				$token->setCurlTimeout(20);
				$token->setTimestampTolerance(500);
				
				if (!$token->verify($otp))
					die("Yubikey-&Uuml;berpr&uuml;fung ist fehlgeschlagen: ".$token->getLastResponse().'<br />Sollte das ein Fehler sein, versuchen Sie es <a href="javascript:history.back()">erneut</a>.');
			}
			else
				die("Der YubiKey geh&ouml;rt Ihnen nicht.");
		}
		
		// nur wenn das Haekchen angeklickt ist, werden die Stichtagsnoten eingetragen
		if ($_POST["fertig"]) {
			$i=0;
			while (isset($_POST["schueler_".$i])) {
				if (isset($_POST["stn_".$i])) {
					$tend = "0";
					if (substr($_POST["stn_".$i], -1) == "+")
						$tend = "1";
					if (substr($_POST["stn_".$i], -1) == "-")
						$tend = "-1";
					if ($tend != "0")
						$wert = substr($_POST["stn_".$i], 0, strlen($_POST["stn_".$i])-2);
					else
						$wert = injaway($_POST["stn_".$i]);
					
					$stn_existiert=db_conn_and_sql("SELECT * FROM stichtagsnote WHERE rahmen=".$rahmen." AND fach_klasse=".$fach_klasse." AND schueler=".injaway($_POST["schueler_".$i]));
					if (sql_num_rows($stn_existiert)==1) {
						if ($_POST["stn_".$i]=="x")
							db_conn_and_sql("DELETE FROM stichtagsnote WHERE rahmen=".$rahmen." AND fach_klasse=".$fach_klasse." AND schueler=".injaway($_POST["schueler_".$i])." LIMIT 1");
						else //update
							db_conn_and_sql("UPDATE stichtagsnote SET wert=".$wert.", tendenz=".$tend." WHERE rahmen=".$rahmen." AND fach_klasse=".$fach_klasse." AND schueler=".injaway($_POST["schueler_".$i]));
					}
					else
						//insert
						if ($_POST["stn_".$i]!="x")
							db_conn_and_sql("INSERT INTO stichtagsnote (schueler, rahmen, fach_klasse, wert, tendenz) VALUES (".injaway($_POST["schueler_".$i]).", ".$rahmen.", ".$fach_klasse.", ".$wert.", ".$tend.");");
				}
				$i++;
			}
			db_conn_and_sql("UPDATE stichtagsnote_fk SET fertig='".date("Y-m-d")."', user=".$_SESSION["user_id"]." WHERE rahmen=".$rahmen." AND fach_klasse=".$fach_klasse);
		}
	}
	
	$start_ende=schuljahr_start_ende($aktuelles_jahr, $schule);
	
	$stichtagsnoten=db_conn_and_sql("SELECT *
		FROM stichtagsnote_fk, stichtagsnote_rahmen
		WHERE stichtagsnote_fk.rahmen=stichtagsnote_rahmen.id
			AND stichtagsnote_rahmen.bearbeitung_ab>".apostroph_bei_bedarf($start_ende["start"])."
			AND stichtagsnote_rahmen.bearbeitung_ab<".apostroph_bei_bedarf($start_ende["ende"])."
			AND user=".$_SESSION["user_id"]."
		ORDER BY stichtagsnote_rahmen.bearbeitung_bis DESC, stichtagsnote_rahmen.id");
		//			AND stichtagsnote_rahmen.bearbeitung_ab<=".$CURDATE."
	
	echo '<h3>Stichtagsnoten</h3>';
	
	if (sql_num_rows($stichtagsnoten)>0)
		echo '<ul><li>';
	$stn_id_old=0;
	while ($stn = sql_fetch_assoc($stichtagsnoten)) {
		if ($stn_id_old!=$stn["rahmen"]) {
			if ($stn_id_old!=0)
				echo "</li><li>";
			if ($rahmen==$stn["rahmen"]) {
				$rahmenname=$stn["name"];
				$tendenz_erlaubt=$stn["tendenz"];
				$bearbeitung_bis=$stn["bearbeitung_bis"];
			}
			echo $stn["name"].' ('.datum_strich_zu_punkt_uebersichtlich($stn["bearbeitung_ab"], true, false).' - '.datum_strich_zu_punkt_uebersichtlich($stn["bearbeitung_bis"], true, false).'):<br />';
			$stn_id_old=$stn["rahmen"];
		}
		echo ' ';
		if ($stn["fertig"])
			echo '<img src="'.$pfad.'icons/haekchen.png" alt="fertig" title="'.datum_strich_zu_punkt_uebersichtlich($stn["fertig"], true, false).'" />';
		if ($stn["bearbeitung_ab"]<=date("Y-m-d"))
			echo '<a href="index.php?tab=noten&amp;option=stichtagsnote&amp;rahmen='.$stn["rahmen"].'&amp;fach_klasse='.$stn["fach_klasse"].'">'.$subject_classes->nach_ids[$stn["fach_klasse"]]["name"].'</a>;';
		else
			echo '<span style="color: gray;">'.$subject_classes->nach_ids[$stn["fach_klasse"]]["name"].";</span>";
	}
	if (sql_num_rows($stichtagsnoten)>0)
		echo '</li></ul>';
	
	if ($_GET["eintragen"]!="true") {
		
		function option_selected($wert, $vergleich) {
			if ($vergleich==$wert and $vergleich!="")
				return ' selected="selected"';
			else
				return '';
		}
		
		function stn_options($vorauswahl, $tendenz, $punkte_zensuren) {
			if ($punkte_zensuren) {$s=0; $e=15;}
			else {$s=1; $e=6;}
			$return='<option value="x">-</option>';
			for ($i=$s; $i<=$e; $i++) {
				$return.='<option value="'.$i.'"'.option_selected($vorauswahl,$i).'>'.$i.'</option>';
				if ($tendenz) {
					$return.='<option value="'.$i.'_-"'.option_selected($vorauswahl,$i."_-").'>'.$i.'-</option>';
					$return.='<option value="'.$i.'_+"'.option_selected($vorauswahl,$i."_+").'>'.$i.'+</option>';
				}
			}
			return $return;
		}
		
		
		if ($fach_klasse>0 and proofuser("fach_klasse", $fach_klasse) and $rahmen>0) {
			$notenansicht=noten_von_fachklasse($fach_klasse, $aktuelles_jahr); ?>
			<form action="<?php echo $formularziel; ?>&amp;eintragen=true" method="post" autocomplete="off">
			<input type="hidden" name="fach_klasse" value="<?php echo $fach_klasse; ?>" />
			<input type="hidden" name="rahmen" value="<?php echo $rahmen; ?>" />
			<fieldset><legend><?php echo $rahmenname." f&uuml;r ".$subject_classes->nach_ids[$fach_klasse]["farbanzeige"]." eintragen"; ?></legend>
			<table class="tabelle">
				<tr><th>Sch&uuml;ler</th><th>HJ</th><th>2. HJ<br />bzw. GJ</th><th>Stichtags-<br />note</th></tr>
				<?php
				for ($i=0; $i<count($notenansicht["schueler"]);$i++) {
				?>
				<tr>
					<td><?php echo pictureOfPupil($notenansicht['schueler'][$i]['name'], $notenansicht['schueler'][$i]['vorname'], $notenansicht['schueler'][$i]['number'], $notenansicht['schueler'][$i]['username'], $pfad, 'style="width: 30px;"').'&nbsp;'.$notenansicht['schueler'][$i]['name'].',&nbsp;'.$notenansicht['schueler'][$i]['vorname']; ?></td>
					<td><?php echo '<span title="Berechnung:'."<br />".$notenansicht['schueler'][$i]['halbjahres_schnitt_berechnung'].'">'.$notenansicht['schueler'][$i]['halbjahres_schnitt_komma'].'</span>'; ?></td>
					<td><?php if ($notenansicht['notenbeschreibung'][0]['punkte_oder_zensuren']==1)
								echo '<span title="Berechnung:'."<br />".$notenansicht['schueler'][$i]['halbjahr_2_schnitt_berechnung'].'">'.$notenansicht['schueler'][$i]['halbjahr_2_schnitt_komma'].'</span>';
							else
								echo '<span title="Berechnung:'."<br />".$notenansicht['schueler'][$i]['ganzjahres_schnitt_berechnung'].'">'.$notenansicht['schueler'][$i]['ganzjahres_schnitt_komma'].'</span>'; ?></td>
					<td><?php
					$vorauswahl="";
					$stn_vaw_res=db_conn_and_sql("SELECT * FROM stichtagsnote WHERE rahmen=".$rahmen." AND fach_klasse=".$fach_klasse." AND schueler=".$notenansicht['schueler'][$i]['id']);
					if (sql_num_rows($stn_vaw_res)==1) {
						$stn_vaw=sql_fetch_assoc($stn_vaw_res);
						$tend="";
						if ($stn_vaw["tendenz"]==1)
							$tend="_+";
						if ($stn_vaw["tendenz"]==-1)
							$tend="_-";
						$vorauswahl=$stn_vaw["wert"].$tend;
					}
					
					if ($bearbeitung_bis<date("Y-m-d")) { // nicht <=, sonst ist der letzte Tag nicht mit dabei
						if (sql_num_rows($stn_vaw_res)==1) {
							echo $stn_vaw["wert"];
							if ($stn_vaw["tendenz"]==-1) echo "-";
							if ($stn_vaw["tendenz"]==1) echo "+";
						}
						else
							echo "-";
					}
					else {
						echo '<input type="hidden" name="schueler_'.$i.'" value="'.$notenansicht['schueler'][$i]['id'].'" />';
						echo '<select name="stn_'.$i.'">';
						// Theoretisch denkbar, die Noten voreinzutragen, aber ist das wirklich gut?
						//if ($vorauswahl=="") {
						//	if ($notenansicht['notenbeschreibung'][0]['punkte_oder_zensuren']==1)
						//		$vorauswahl=round($notenansicht['schueler'][$i]['halbjahr_2_schnitt']);
						//	else
						//		$vorauswahl=round($notenansicht['schueler'][$i]['ganzjahres_schnitt']);
						//}
						echo stn_options($vorauswahl, $tendenz_erlaubt, $notenansicht['notenbeschreibung'][0]['punkte_oder_zensuren']);
						echo '</select>';
					}
					?></td>
				</tr>
				<?php
				}
				?>
			</table>
			<p><input type="checkbox" name="fertig" value="1" required="required" />
			Ich habe die Eintragung mit gr&ouml;&szlig;ter Sorgfalt vorgenommen und die Berechnungen &uuml;berpr&uuml;ft.
			</p>
			<?php
			// yubikey-laenge wird oben ueberprueft, da teilweise ein Zeichen fehlt und es dadurch zu Fehlern kommt
			if ($my_user->my["token_id"]!="") {
				echo '<p>YubiKey-OTP: <input type="text" name="token_otp" id="token_otp" size="15" placeholder="Best&auml;tigungspasswort" /><br /></p>';
			}
			?>
			<button onclick="if (document.getElementById('token_otp')) auswertung=new Array(new Array(0, 'token_otp','yubikey')); pruefe_formular(auswertung); return false;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>			</fieldset>
			</form>
			<?php
		}
	}
?>
