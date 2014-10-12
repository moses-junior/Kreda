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
		
		// nur wenn das Haekchen angeklickt ist, werden die kopfnoten eingetragen
		if ($_POST["fertig"]) {
			$zugeordnete_kategorien_result=db_conn_and_sql("SELECT * FROM kopfnotenkat_rahmen LEFT JOIN kopfnoten_kategorie ON kopfnoten_kategorie.id=kopfnotenkat_rahmen.kn_kat WHERE rahmen=".$rahmen." ORDER BY position");
			$zugeordnete_kategorien=array();
			while($z_kat=sql_fetch_assoc($zugeordnete_kategorien_result))
				$zugeordnete_kategorien[]=$z_kat;
			$i=0;
			while (isset($_POST["schueler_".$i])) {
				foreach($zugeordnete_kategorien as $zug_kat) {
						$tend = "0";
						if (substr($_POST["kn_".$i."_".$zug_kat["id"]], -1) == "+")
							$tend = "1";
						if (substr($_POST["kn_".$i."_".$zug_kat["id"]], -1) == "-")
							$tend = "-1";
						if ($tend != "0")
							$wert = substr($_POST["kn_".$i."_".$zug_kat["id"]], 0, strlen($_POST["kn_".$i."_".$zug_kat["id"]])-2);
						else
							$wert = injaway($_POST["kn_".$i."_".$zug_kat["id"]]);
						// um Dezimalzahlzahlen in der DB zu vermeiden, ist $wert bereits an dieser Stelle mit 10 multipliziert (Formular aufbereitet)
						
						$kn_existiert=db_conn_and_sql("SELECT * FROM kopfnote WHERE rahmen=".$rahmen." AND fach_klasse=".$fach_klasse." AND schueler=".injaway($_POST["schueler_".$i])." AND kategorie=".$zug_kat["id"]);
						if (sql_num_rows($kn_existiert)==1) {
							if ($_POST["kn_".$i."_".$zug_kat["id"]]=="x")
								db_conn_and_sql("DELETE FROM kopfnote WHERE rahmen=".$rahmen." AND fach_klasse=".$fach_klasse." AND schueler=".injaway($_POST["schueler_".$i])." AND kategorie=".$zug_kat["id"]." LIMIT 1");
							else //update
								db_conn_and_sql("UPDATE kopfnote SET wert=".$wert.", tendenz=".$tend." WHERE rahmen=".$rahmen." AND fach_klasse=".$fach_klasse." AND schueler=".injaway($_POST["schueler_".$i])." AND kategorie=".$zug_kat["id"]);
						}
						else
							//insert
							if ($_POST["kn_".$i."_".$zug_kat["id"]]!="x")
								db_conn_and_sql("INSERT INTO kopfnote (schueler, rahmen, fach_klasse, kategorie, wert, tendenz) VALUES (".injaway($_POST["schueler_".$i]).", ".$rahmen.", ".$fach_klasse.", ".$zug_kat["id"].", ".$wert.", ".$tend.");");
								//echo "INSERT INTO kopfnote (schueler, rahmen, fach_klasse, kategorie, wert, tendenz) VALUES (".injaway($_POST["schueler_".$i]).", ".$rahmen.", ".$fach_klasse.", ".$zug_kat["id"].", ".$wert.", ".$tend.");<br />";
				}
				$i++;
			}
			db_conn_and_sql("UPDATE kopfnote_fk SET fertig='".date("Y-m-d")."', user=".$_SESSION["user_id"]." WHERE rahmen=".$rahmen." AND fach_klasse=".$fach_klasse);
		}
	}
	
	$start_ende=schuljahr_start_ende($aktuelles_jahr, $schule);
	
	$kopfnoten=db_conn_and_sql("SELECT *
		FROM kopfnote_fk, kopfnote_rahmen
		WHERE kopfnote_fk.rahmen=kopfnote_rahmen.id
			AND kopfnote_rahmen.bearbeitung_ab>".apostroph_bei_bedarf($start_ende["start"])."
			AND kopfnote_rahmen.bearbeitung_ab<".apostroph_bei_bedarf($start_ende["ende"])."
			AND user=".$_SESSION["user_id"]."
		ORDER BY kopfnote_rahmen.bearbeitung_bis DESC, kopfnote_rahmen.id");
		//	AND kopfnote_rahmen.bearbeitung_ab<=".$CURDATE."
	
	echo '<h3>Kopfnoten</h3>';
	
	if (sql_num_rows($kopfnoten)>0)
		echo '<ul><li>';
	$kn_id_old=0;
	while ($kn = sql_fetch_assoc($kopfnoten)) {
		if ($kn_id_old!=$kn["rahmen"]) {
			if ($kn_id_old!=0)
				echo "</li><li>";
			if ($rahmen==$kn["rahmen"]) {
				$rahmenname=$kn["name"];
				$tendenz_erlaubt=$kn["tendenz"];
				$bearbeitung_bis=$kn["bearbeitung_bis"];
			}
			echo '<div>'.$kn["name"].' ('.datum_strich_zu_punkt_uebersichtlich($kn["bearbeitung_ab"], true, false).' - '.datum_strich_zu_punkt_uebersichtlich($kn["bearbeitung_bis"], true, false).'):</div>';
			echo '<div style="color: gray">'.$kn["beschreibung"]."</div>";
			$kn_id_old=$kn["rahmen"];
			
		
			// Klassenlehrer duerfen die Auswertung ihrer Klasse jederzeit ansehen
			$klassenlehrer_von=db_conn_and_sql("SELECT klasse.id
				FROM klasse, fach_klasse, kopfnote_fk
				WHERE fach_klasse.klasse=klasse.id
					AND kopfnote_fk.rahmen=".$kn["rahmen"]."
					AND kopfnote_fk.fach_klasse=fach_klasse.id
					AND (klassenlehrer=".$_SESSION["user_id"]." OR klassenlehrer2=".$_SESSION["user_id"].")
				GROUP BY klasse.id");
			$klassen_auswerten=array();
			while ($klasse_id=sql_fetch_assoc($klassenlehrer_von)) {
				$klassen_auswerten[]='<a href="'.$pfad.'formular/kopfnoten_auswertung.php?auswertung='.$kn["rahmen"].'&amp;klasse='.$klasse_id["id"].'" onclick="fenster(this.href, \'ohne Titel\'); return false;" class="icon">'.$school_classes->nach_ids[$klasse_id["id"]]["name"].' <img src="'.$pfad.'icons/statistik.png" alt="stat" /></a> ';
			}
			if (count($klassen_auswerten)>0)
				echo '<div>Auswertung f&uuml;r Klassenlehrer: '.implode("; ", $klassen_auswerten).'</div>';
		}
		echo ' ';
		if ($kn["fertig"])
			echo '<img src="'.$pfad.'icons/haekchen.png" alt="fertig" title="'.datum_strich_zu_punkt_uebersichtlich($kn["fertig"], true, false).'" />';
		if ($kn["bearbeitung_ab"]<=date("Y-m-d"))
			echo '<a href="index.php?tab=noten&amp;option=kopfnote&amp;rahmen='.$kn["rahmen"].'&amp;fach_klasse='.$kn["fach_klasse"].'">'.$subject_classes->nach_ids[$kn["fach_klasse"]]["name"].'</a>;';
		else
			echo $subject_classes->nach_ids[$kn["fach_klasse"]]["name"].";";
	}
	if (sql_num_rows($kopfnoten)>0)
		echo '</li></ul>';
	
	if ($_GET["eintragen"]!="true") {
		
		function option_selected($wert, $vergleich) {
			if ($vergleich==$wert and $vergleich!="")
				return ' selected="selected"';
			else
				return '';
		}
		
		function kn_options($vorauswahl, $art) {
			global $kopfnoten_arten;
			$return='<option value="x">-</option>';
			for ($i=$kopfnoten_arten[$art]["start"]; $i<=$kopfnoten_arten[$art]["ende"]; $i++) {
				$return.='<option value="'.(10*$i).'"'.option_selected($vorauswahl,10*$i).'>'.$i.'</option>';
				if ($kopfnoten_arten[$art]["kommazahl"])
					$return.='<option value="'.(10*$i+5).'"'.option_selected($vorauswahl,(10*$i+5)).'>'.$i.',5</option>';
				if ($kopfnoten_arten[$art]["tendenz"]) {
					$return.='<option value="'.(10*$i).'_-"'.option_selected($vorauswahl,(10*$i)."_-").'>'.$i.'-</option>';
					$return.='<option value="'.(10*$i).'_+"'.option_selected($vorauswahl,(10*$i)."_+").'>'.$i.'+</option>';
				}
			}
			return $return;
		}
		
		if ($fach_klasse>0 and proofuser("fach_klasse", $fach_klasse) and $rahmen>0) {
			$letzter_rahmen=db_conn_and_sql("SELECT kopfnote_rahmen.id
				FROM kopfnote_rahmen, kopfnote_fk
				WHERE kopfnote_fk.fertig IS NOT NULL
					AND kopfnote_fk.rahmen=kopfnote_rahmen.id
					AND kopfnote_rahmen.bearbeitung_bis<'".$bearbeitung_bis."'
					AND kopfnote_fk.fach_klasse=".$fach_klasse."
				ORDER BY kopfnote_rahmen.bearbeitung_bis DESC
				LIMIT 1");
			$letzter_rahmen=sql_fetch_assoc($letzter_rahmen);
			
			$kn_art=db_conn_and_sql("SELECT art FROM kopfnote_rahmen WHERE id=".$rahmen);
			$kn_art=sql_fetch_assoc($kn_art);
			$kn_art=$kn_art["art"];
			$zugeordnete_kategorien_result=db_conn_and_sql("SELECT kopfnoten_kategorie.*, kopfnotenkat_rahmen.position FROM kopfnotenkat_rahmen LEFT JOIN kopfnoten_kategorie ON kopfnoten_kategorie.id=kopfnotenkat_rahmen.kn_kat WHERE rahmen=".$rahmen." ORDER BY position");
			$zugeordnete_kategorien=array();
			while($z_kat=sql_fetch_assoc($zugeordnete_kategorien_result))
				$zugeordnete_kategorien[]=$z_kat;
			?>
			<form action="<?php echo $formularziel; ?>&amp;eintragen=true" method="post" autocomplete="off">
			<input type="hidden" name="fach_klasse" value="<?php echo $fach_klasse; ?>" />
			<input type="hidden" name="rahmen" value="<?php echo $rahmen; ?>" />
			<fieldset><legend><?php echo $rahmenname." f&uuml;r ".$subject_classes->nach_ids[$fach_klasse]["farbanzeige"]." eintragen"; ?></legend>
			<table class="tabelle">
				<tr>
					<th>Sch&uuml;ler</th>
					<?php foreach($zugeordnete_kategorien as $z_kat) echo '<th title="'.$z_kat["beschreibung"].'">'.$z_kat["name"].'</th>'; ?>
					<th colspan="<?php echo count($zugeordnete_kategorien); ?>">KL</th>
					<th colspan="<?php echo count($zugeordnete_kategorien); ?>">mein letzter</th>
					<th>HA | Ber | Unt</th>
					<th>MA</th>
					<th>Betragen</th>
					<th>Sonstiges<br />(Elterngespr., Bem.)</th>
				</tr>
				<?php
				$schueler_result=schueler_von_fachklasse($fach_klasse);
				$i=0;
				$anzahl_schueler=sql_num_rows($schueler_result); // fuer Tabreihenfolge
				while($schueler=sql_fetch_assoc($schueler_result)) {
				?>
				<tr>
					<td><?php echo '<input type="hidden" name="schueler_'.$i.'" value="'.$schueler['id'].'" />';
					echo pictureOfPupil($schueler['name'], $schueler['vorname'], $schueler['number'], $schueler['username'], $pfad, 'style="width: 30px;"').'&nbsp;';
					echo $schueler['name'].',&nbsp;'.$schueler['vorname']; ?></td>
					
					<?php
					// Kategorieneintrag je Schueler
					$kn_vaw_res=db_conn_and_sql("SELECT *
						FROM kopfnote
							LEFT JOIN kopfnotenkat_rahmen ON kopfnotenkat_rahmen.kn_kat=kopfnote.kategorie
								 AND kopfnotenkat_rahmen.rahmen=".$rahmen."
						WHERE kopfnote.rahmen=".$rahmen."
							AND fach_klasse=".$fach_klasse."
							AND schueler=".$schueler['id']."
						ORDER BY kopfnotenkat_rahmen.position");
					$kn_vaw=sql_fetch_assoc($kn_vaw_res);
					$kat_zaehler=1; // fuer Tabindex
					foreach($zugeordnete_kategorien as $zug_kat) {
						echo '<td>';
						$tend="";
						$vorauswahl="";
						$wert="";
						if (sql_num_rows($kn_vaw_res)>0) {
							if (!($kn_vaw["position"]>0))
								$kn_vaw=sql_fetch_assoc($kn_vaw_res);
							if ($kn_vaw["position"]==$zug_kat["position"]) {
								if ($kn_vaw["tendenz"]==1)
									$tend="_+";
								if ($kn_vaw["tendenz"]==-1)
									$tend="_-";
								$vorauswahl=$kn_vaw["wert"].$tend;
								$wert=$kn_vaw["wert"];
								
								$kn_vaw=sql_fetch_assoc($kn_vaw_res);
							}
						}
						
						if ($bearbeitung_bis<date("Y-m-d")) { // nicht <=, sonst ist der letzte Tag nicht mit dabei
							if (sql_num_rows($kn_vaw_res)>0) {
								echo $wert;
								if ($tend==-1) echo "-";
								if ($kn_vaw["tendenz"]==1) echo "+";
							}
							else
								echo "-";
						}
						else {
							echo '<select name="kn_'.$i.'_'.$zug_kat["id"].'" tabindex="'.($i+$anzahl_schueler*$kat_zaehler).'">';
							echo kn_options($vorauswahl, $kn_art);
							echo '</select>';
						}
						echo '</td>';
						$kat_zaehler++;
					}
					
					
					// Klassenleiternote - wird vom Fach KL (Klassenleiterstunde) mit id 35 geholt
					$klassenlehrer_result=db_conn_and_sql("SELECT *
						FROM schueler, fach_klasse, kopfnote
							LEFT JOIN kopfnotenkat_rahmen ON kopfnotenkat_rahmen.kn_kat=kopfnote.kategorie
								 AND kopfnotenkat_rahmen.rahmen=".$rahmen."
						WHERE schueler.id=".$schueler['id']."
							AND schueler.klasse=fach_klasse.klasse
							AND fach_klasse.fach=35
							AND fach_klasse.id=kopfnote.fach_klasse
							AND kopfnote.rahmen=".$rahmen." AND schueler=".$schueler['id']." ORDER BY kopfnotenkat_rahmen.position");
					
					$kl_kn=sql_fetch_assoc($klassenlehrer_result);
					foreach($zugeordnete_kategorien as $kl_kategorie) {
						echo '<td>';
						if ($kl_kn["kategorie"]==$kl_kategorie["id"]) {
							echo kommazahl($kl_kn["wert"]/10);
							if ($kl_kn["tendenz"]=="1")
								echo "+";
							if ($kl_kn["tendenz"]=="-1")
								echo "-";
							$kl_kn=sql_fetch_assoc($klassenlehrer_result);
						}
						echo '</td>';
					}
					
					// mein letzter Kopfnoteneintrag
					if ($letzter_rahmen["id"]>0) {
						$meine_letzte=db_conn_and_sql("SELECT *
							FROM kopfnote
								LEFT JOIN kopfnotenkat_rahmen ON kopfnotenkat_rahmen.kn_kat=kopfnote.kategorie
									AND kopfnotenkat_rahmen.rahmen=".$letzter_rahmen["id"]."
							WHERE kopfnote.schueler=".$schueler['id']."
								AND kopfnote.rahmen=".$letzter_rahmen["id"]."
								AND kopfnote.fach_klasse=".$fach_klasse."
							ORDER BY kopfnotenkat_rahmen.position");
						$my_kn=sql_fetch_assoc($meine_letzte);
						foreach($zugeordnete_kategorien as $my_kategorie) {
							echo '<td>';
							if ($my_kn["kategorie"]==$my_kategorie["id"]) {
								echo kommazahl($my_kn["wert"]/10);
								if ($my_kn["tendenz"]=="1")
									echo "+";
								if ($my_kn["tendenz"]=="-1")
									echo "-";
								$my_kn=sql_fetch_assoc($meine_letzte);
							}
							echo '</td>';
						}
					}
					else echo '<td colspan="'.count($zugeordnete_kategorien).'"></td>';
					
					
					
					// HA|Ber|Unt - aehnlich wie die Hausaufgabenstatistik einer Klasse
					$ha_text="";
					$ha_anzahl=0;
					$ber_text="";
					$ber_anzahl=0;
					$unt_text="";
					$unt_anzahl=0;
					$ver_text="";
					$ver_anzahl=0;
					$ma_text="";
					$ma_anzahl=0;
					$hausaufgaben=db_conn_and_sql("SELECT *
						FROM `hausaufgabe_vergessen`, `hausaufgabe`,`plan`
						WHERE `hausaufgabe`.`abgabedatum`>='".$start_ende["start"]."'
							AND `hausaufgabe`.`abgabedatum`<='".$start_ende["ende"]."'
							AND `hausaufgabe_vergessen`.`hausaufgabe`=`hausaufgabe`.`id`
							AND `hausaufgabe`.`plan`=`plan`.`id`
							AND `plan`.`fach_klasse`=".$fach_klasse."
							AND `hausaufgabe_vergessen`.`schueler`=".$schueler['id']);
					while($ha=sql_fetch_assoc($hausaufgaben))
						if ($ha["mitzaehlen"]) {
							$ha_text.= datum_strich_zu_punkt($ha["abgabedatum"]).' ('.$ha["anzahl"].') <br />';
							$ha_anzahl+=$ha["anzahl"];
						}
						$tests=db_conn_and_sql("SELECT *, IF(`notenbeschreibung`.`datum` IS NULL,`plan`.`datum`,`notenbeschreibung`.`datum`) as `MyDatum`
							FROM `berichtigung_vergessen`, `notenbeschreibung`
								LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
							WHERE (('".$start_ende["start"]."'<=`notenbeschreibung`.`datum` AND '".$start_ende["ende"]."'>=`notenbeschreibung`.`datum`)
									OR ('".$start_ende["start"]."'<=`plan`.`datum` AND '".$start_ende["ende"]."'>=`plan`.`datum`))
								AND `berichtigung_vergessen`.`notenbeschreibung`=`notenbeschreibung`.`id`
								AND `notenbeschreibung`.`fach_klasse`=".$fach_klasse."
								AND `berichtigung_vergessen`.`schueler`=".$schueler["id"]);
					while($test=sql_fetch_assoc($tests)) {
						if ($test["berichtigung_anzahl"]>0) {
							$ber_text.= $test["beschreibung"].' '.datum_strich_zu_punkt($test["MyDatum"]).' ('.$test["berichtigung_anzahl"].') <br />';
							$ber_anzahl+=$test["berichtigung_anzahl"];
						}
						if ($test["unterschrift_anzahl"]>0) {
							$unt_text.= $test["beschreibung"].' '.datum_strich_zu_punkt($test["MyDatum"]).' ('.$test["unterschrift_anzahl"].') <br />';
							$unt_anzahl+=$test["unterschrift_anzahl"];
						}
					}
					
					$verwarnungen=db_conn_and_sql("SELECT * FROM `verwarnungen`, `plan`
						WHERE `plan`.`datum`>='".$start_ende["start"]."'
							AND `plan`.`datum`<='".$start_ende["ende"]."'
							AND `verwarnungen`.`plan`=`plan`.`id`
							AND `plan`.`fach_klasse`=".$fach_klasse."
							AND `verwarnungen`.`schueler`=".$schueler["id"]);
					while($betr=sql_fetch_assoc($verwarnungen))
						if ($betr["anzahl"]!=0) {
							$ver_text.=datum_strich_zu_punkt($betr["datum"]).' ('.$betr["anzahl"].') <br />';
							$ver_anzahl+=$betr["anzahl"];
						}
					
					$mitarbeit=db_conn_and_sql("SELECT * FROM `mitarbeit`, `plan`
						WHERE `plan`.`datum`>='".$start_ende["start"]."'
							AND `plan`.`datum`<='".$start_ende["ende"]."'
							AND `mitarbeit`.`plan`=`plan`.`id`
							AND `plan`.`fach_klasse`=".$fach_klasse."
							AND `mitarbeit`.`schueler`=".$schueler["id"]);
					while($ma=sql_fetch_assoc($mitarbeit))
						if ($ma["anzahl"]!=0) {
							$ma_text.=datum_strich_zu_punkt($ma["datum"]).' ('.$ma["anzahl"].') <br />';
							$ma_anzahl+=$ma["anzahl"];
						}
					
					echo '<td title="Hausaufgaben:<br />'.$ha_text.'Berichtigungen:<br />'.$ber_text.'Unterschriften:<br />'.$unt_text.'">';
					if ($ha_anzahl+$ber_anzahl+$unt_anzahl!=0)
						echo $ha_anzahl.' | '.$ber_anzahl.' | '.$unt_anzahl;
					echo '</td><td title="'.$ma_text.'">';
					if ($ma_anzahl!=0)
						echo $ma_anzahl;
					echo '</td><td title="'.$ver_text.'">';
					if ($ver_anzahl!=0)
						echo $ver_anzahl;
					echo '</td>';
					
					
					// TODO: hier fehlts noch an Elterngespraechen...
					echo '<td>'.$schueler["bemerkungen"].'</td>';
					?>
				</tr>
				<?php
					$i++;
				}
				?>
			</table>
			<p><input type="checkbox" name="fertig" value="1" required="required" tabindex="<?php echo $anzahl_schueler*($kat_zaehler+1); ?>" />
			Ich habe die Eintragung mit gr&ouml;&szlig;ter Sorgfalt vorgenommen.
			</p>
			<?php
			// yubikey-laenge wird oben ueberprueft, da teilweise ein Zeichen fehlt und es dadurch zu Fehlern kommt
			if ($my_user->my["token_id"]!="") {
				echo '<p>YubiKey-OTP: <input type="text" name="token_otp" id="token_otp" size="15" placeholder="Best&auml;tigungspasswort" tabindex="'.($anzahl_schueler*($kat_zaehler+1)+1).'" /><br /></p>';
			}
			?>
			<button onclick="if (document.getElementById('token_otp')) auswertung=new Array(new Array(0, 'token_otp','yubikey')); pruefe_formular(auswertung); return false;"><img src="<?php echo $pfad; ?>icons/page_save.png" alt="save" /> speichern</button>
			</fieldset>
			</form>
			<?php
		}
	}
?>
