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
<html>
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
</head>
<body>
<?php
session_start();

header('Content-Type: text/html; charset=ISO-8859-1');
$pfad="../";
include $pfad."funktionen.php";
$titelleiste="Export";

$my_user = new user();
$schul_id = $my_user->my["letzte_schule"];
$jahr=$aktuelles_jahr; // TODO: aktuelles Jahr der Schule
$start_ende=schuljahr_start_ende($jahr, $schul_id);

$export_pwd="asdf";

$startzeit=microtime(true);
$schuelerdaten=db_conn_and_sql("SELECT schueler.*, klasse.id AS klasse_id FROM schueler, klasse WHERE schueler.klasse=klasse.id AND klasse.schule=".$schul_id);
// TODO: WHERE "die wollen das auch"

		$schueler_array=array();
		while ($schueler = sql_fetch_assoc($schuelerdaten))
			$schueler_array[$schueler["id"]]=array(
				"klasse_id"=>$schueler["klasse_id"],
				"name"=>html_umlaute($schueler["vorname"]).' '.html_umlaute($schueler["name"]),
				"pic"=>pictureOfPupil($schueler["name"], $schueler["vorname"], $schueler["number"], $schueler["username"], $pfad, 'style="float: right"'),
				"username"=>html_umlaute($schueler["username"]),
				"passwort"=>html_umlaute($schueler["passwort"]),
				"adresse"=>html_umlaute($schueler["strasse"]).'<br />'.html_umlaute($schueler["ort"]),
				"email"=>html_umlaute($schueler["email"]),
				"telefon"=>html_umlaute($schueler["telefon"]),
				"faecher"=>array()
			);
		
		$fach_klassen_ids = db_conn_and_sql("SELECT faecher.id AS fach_id, fach_klasse.id, faecher.kuerzel, faecher.name
			FROM klasse, fach_klasse, lehrauftrag, faecher
			WHERE fach_klasse.klasse=klasse.id
				AND faecher.id=fach_klasse.fach
				AND lehrauftrag.fach_klasse=fach_klasse.id
				AND lehrauftrag.schuljahr=".$aktuelles_jahr."
				AND klasse.schule=".$schul_id);
		
		while ($fk_id=sql_fetch_assoc($fach_klassen_ids)) {
			//echo ($aktuelles_jahr-$fk_id["einschuljahr"]+1).$fk_id["endung"].$fk_id["kuerzel"]."<br/>"; // uebergangsweise
			$notenansicht = noten_von_fachklasse($fk_id["id"], $aktuelles_jahr);
			$i=0;
			while ($i<count($notenansicht['schueler'])) {
				// Falls der Eintrag im $schueler_array existiert (die Eltern das also wollen); falls das fach nicht schon dran war (mehrfachlehrauftraege)
				if (count($schueler_array[$notenansicht['schueler'][$i]['id']])>1 and !isset($schueler_array[$notenansicht['schueler'][$i]['id']]["faecher"][$fk_id["fach_id"]])) {
					
					$inhalt='<tr><td title="'.$fk_id["name"].'">'.$fk_id["kuerzel"].'</td><td>';
					for($j=0;$j<count($notenansicht['notenbeschreibung']);$j++)
						// Achtung: hier wird zurueckgegeben gefordert
						if ($notenansicht['schueler'][$i]['noten'][$j]["mitzaehlen"]!=0 and date("Y-m-d")>=$notenansicht['notenbeschreibung'][$j]["zurueckgegeben"])
						{
							$inhalt.='<span style="text-align:center;" title="'.$notenansicht['notenbeschreibung'][$j]['notentyp_kuerzel']." ".$notenansicht['notenbeschreibung'][$j]['beschreibung']."\n".$notenansicht['schueler'][$i]['noten'][$j]['datum'].' | '.$notenansicht['schueler'][$i]['noten'][$j]['kommentar'];
							if ($notenansicht['schueler'][$i]['noten'][$j]['halbjahresnote']!=$notenansicht['notenbeschreibung'][$j]['halbjahresnote'] and $notenansicht['schueler'][$i]['noten'][$j]['wert']>0) {
								$inhalt.= ' | ';
								if ($notenansicht['schueler'][$i]['noten'][$j]['halbjahresnote'])
									$inhalt.='geht schon in Berechnung zur Halbjahresnote ein';
								else
									$inhalt.='geht erst in Berechnung zur Ganzjahresnote ein';
							}
							$inhalt.='">
								<span';
							if ($notenansicht['schueler'][$i]['noten'][$j]['halbjahresnote']!=$notenansicht['notenbeschreibung'][$j]['halbjahresnote'] and $notenansicht['schueler'][$i]['noten'][$j]['wert']>0)
								$inhalt.=' class="grade_on_wrong_side"';
							if ($notenansicht['schueler'][$i]['noten'][$j]['kommentar']!="")
								$inhalt.=' style="font-weight: bold;"';
							$inhalt.='>'.$notenansicht['schueler'][$i]['noten'][$j]['wert'].$notenansicht['schueler'][$i]['noten'][$j]['notenzusatz'].'</span>';
							if ($notenansicht['schueler'][$i]['noten'][$j]['punktzahl_mit_komma']!="" and ($notenansicht['notenbeschreibung'][$j]['gesamtpunktzahl']>0 or $notenansicht['schueler'][$i]['noten'][$j]['einzelpunkte'][0]['pkt']>0))
							{
								$inhalt.='<span style="font-size:9px; color: #555;"> <sup>'.$notenansicht['schueler'][$i]['noten'][$j]['punktzahl_mit_komma'].'</sup>/<sub>';
								$inhalt.=$notenansicht['schueler'][$i]['noten'][$j]['gesamtpunktzahl'];
								$inhalt.='</sub></span>';
							}
							$inhalt.='</span> | ';
						}
					$inhalt.='</td>';
					$inhalt.='<td>';
					if ($notenansicht['notenbeschreibung'][0]['punkte_oder_zensuren']==1)
						$inhalt.='<span title="'.$notenansicht['berechnungsvorlage']."\n".$notenansicht['schueler'][$i]['halbjahres_schnitt_berechnung'].'">'.$notenansicht['schueler'][$i]['halbjahres_schnitt_komma'].'</span> | <span title="'.$notenansicht['berechnungsvorlage']."\n".$notenansicht['schueler'][$i]['halbjahr_2_schnitt_berechnung'].'">'.$notenansicht['schueler'][$i]['halbjahr_2_schnitt_komma'].'</span></td>';
					else
						$inhalt.='<span title="'.$notenansicht['berechnungsvorlage']."\n".$notenansicht['schueler'][$i]['ganzjahres_schnitt_berechnung'].'">'.$notenansicht['schueler'][$i]['ganzjahres_schnitt_komma'].'</span></td>';
					$inhalt.='</tr>';
					
					$schueler_array[$notenansicht['schueler'][$i]['id']]["faecher"][$fk_id["fach_id"]]=$inhalt;
				}
				$i++;
			}
		}
		
		// ------- Chiffrierung Eingang --------------
		// Open the cipher
		//$td = mcrypt_module_open('tripledes', '', 'ecb', '');
		// Create the IV and determine the keysize length, use MCRYPT_RAND on Windows instead
		//$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		//$ks = mcrypt_enc_get_key_size($td);
		
		foreach ($schueler_array as $s_id => $eintrag) {
			$html_schueler='';
			$html_schueler .= $schueler_array[$s_id]["pic"];
			$html_schueler .= '<h1>'.$schueler_array[$s_id]["name"].'</h1>';
			$html_schueler .= '<h2>Kontaktdaten</h2>';
			$html_schueler .= 'Adresse: '.$schueler_array[$s_id]["adresse"].'<br />Mail: '.$schueler_array[$s_id]["email"].'<br />Tel: '.$schueler_array[$s_id]["telefon"];
			
			$html_schueler .= '<h2>Zensuren</h2>';
			
			$html_schueler .= '<table class="tabelle"><tr><th>Fach</th><th>Noten</th><th>Durchschnitt</th></tr>';
			foreach ($schueler_array[$s_id]["faecher"] as $tabellenzeile)
				$html_schueler .= $tabellenzeile;
			$html_schueler .= '</table>';
			
			// Fehlzeiten
			$html_schueler .= '<h2>Fehlzeiten</h2>';
			$fehlzeiten_result = db_conn_and_sql("SELECT * FROM schueler_fehlt WHERE schueler_fehlt.startdatum>='".$start_ende["start"]."' AND schueler_fehlt.startdatum<='".$start_ende["ende"]."' AND schueler=".$s_id);
			if (sql_num_rows($fehlzeiten_result)<1)
				$html_schueler .= 'Keine Fehlzeiten eingetragen.';
			else for ($i=0; $i<sql_num_rows($fehlzeiten_result); $i++) {
				if (sql_result($fehlzeiten_result,$i,"schueler_fehlt.entschuldigt")==0)
					$entschuldigt="Unentschuldigt";
				if (sql_result($fehlzeiten_result,$i,"schueler_fehlt.entschuldigt")==1)
					$entschuldigt="Entschuldigt";
				if (sql_result($fehlzeiten_result,$i,"schueler_fehlt.entschuldigt")==2)
					$entschuldigt="Krank";
				$html_schueler .= ($i+1).': '.datum_strich_zu_punkt(sql_result($fehlzeiten_result,$i,"schueler_fehlt.startdatum")).' - '.datum_strich_zu_punkt(sql_result($fehlzeiten_result,$i,"schueler_fehlt.enddatum")).': '.$entschuldigt.'<br />';
			}
			
			// create key
			// TODO: secret key -> password + random_des_exports
			//$key = substr(md5($export_pwd), 0, $ks);
			// Initialize encryption
			//mcrypt_generic_init($td, $key, $iv);
			
			// encrypt data
			//$encrypted_data = mcrypt_generic($td, $html_schueler);
			
			$schueler_array[$s_id]["html"] = $html_schueler; //base64_encode($encrypted_data);
		}
		echo $key."Dauer: ".(microtime(true)-$startzeit)." sec<br />";
		echo $schueler_array[497]["html"];
		// ----------------- Chiffrierung Deinitialisieren ------------------------------
		// terminate encryption handler
		//mcrypt_generic_deinit($td);
		//mcrypt_module_close($td);
		
		
		// ----------------- Schuelerdaten-Dateiinhalt festlegen ------------------------
		$inhalt_der_datei="<?php \n".'$schuelerarray=array();'."\n".'$export_pwd="'.$export_pwd.'";'."\n";
		foreach ($schueler_array as $s_id => $eintrag)
			$inhalt_der_datei.='$schuelerarray['.$s_id.']=array("name"=>\''.$eintrag["username"].'\',"pwd"=>\''.$eintrag["passwort"].'\', "html"=>\''.str_replace("'","\'",$eintrag["html"]).'\');'."\n";
		$inhalt_der_datei.="?>\n";
		//$inhalt_der_datei=iconv("UTF-8", "UTF-8", $inhalt_der_datei);
		
		// ----------------- Datei schreiben --------------------------------------------
		// TODO: mkdir
		
		if (!($schuelerdaten_datei=fopen($pfad.'eltern/export/schuelerdaten.php', "w")))
			die ("Datei ".$pfad.'eltern/export/schuelerdaten.php'.' konnte nicht erstellt werden!');
		fwrite($schuelerdaten_datei, $inhalt_der_datei);
		
		rewind($schuelerdaten_datei);
		
		fclose($schuelerdaten_datei);
		chmod ($pfad.'eltern/export/schuelerdaten.php', 0755);
		clearstatcache();
		
		
		//echo $schueler_array[497]["html"];
		//echo $encrypted_data;
		//echo $schueler_array[427]["html"];
		?>
</body></html>
