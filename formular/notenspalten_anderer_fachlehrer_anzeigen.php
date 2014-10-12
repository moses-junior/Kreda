				<div class="hinweis" id="tests_der_fk" style="display: none; float: right;"></div>
				<script>
					function js_datum_zu_woche(datum) {
						// Inhalte stammen von: http://www.web-toolbox.net/webtoolbox/datum/code-wocheaktuell.htm#ixzz39E5gpPGq
						var DonnerstagDat = new Date(datum.getTime() + (3-((datum.getDay()+6) % 7)) * 86400000);
						KWJahr = DonnerstagDat.getFullYear();
						var DonnerstagKW = new Date(new Date(KWJahr,0,4).getTime() + (3-((new Date(KWJahr,0,4).getDay()+6) % 7)) * 86400000);
						KW = Math.floor(1.5 + (DonnerstagDat.getTime() - DonnerstagKW.getTime()) / 86400000/7);
						return KW;
					}
					
					function woche_sichtbar(woche) {
						if (tests_woche[woche]!=undefined)
							document.getElementById('tests_der_fk').style.display='block';
						else
							document.getElementById('tests_der_fk').style.display='none';
						document.getElementById('tests_der_fk').innerHTML='In dieser Woche sind in der Klasse folgende Zensuren geplant: <br />'+tests_woche[woche];
					}
						
					var tests_woche= new Array();
				<?php
				$tests_der_fach_klasse=db_conn_and_sql("SELECT notenbeschreibung.beschreibung, notenbeschreibung.kommentar, notentypen.kuerzel AS notentyp_kuerzel, faecher.kuerzel AS fach_kuerzel, users.user_name AS lehrerkuerzel,
					IF(`notenbeschreibung`.`datum` IS NULL,`plan`.`datum`,`notenbeschreibung`.`datum`) as `MyDatum`
				FROM `notentypen`, `fach_klasse`, `users`, `faecher`, `notenbeschreibung`
					LEFT JOIN `plan` ON `notenbeschreibung`.`plan`=`plan`.`id`
				WHERE fach_klasse.klasse=".$subject_classes->nach_ids[$fach_klasse]["klasse_id"]."
					AND fach_klasse.fach=faecher.id
					AND fach_klasse.user=users.user_id
					AND `notenbeschreibung`.`fach_klasse`=fach_klasse.id
					AND `notenbeschreibung`.`notentyp`=`notentypen`.`id`
					AND (('".$start_ende["start"]."'<=`notenbeschreibung`.`datum` AND '".$start_ende["ende"]."'>=`notenbeschreibung`.`datum`)
					OR ('".$start_ende["start"]."'<=`plan`.`datum` AND '".$start_ende["ende"]."'>=`plan`.`datum`))
				ORDER BY `notenbeschreibung`.`halbjahresnote` DESC, `MyDatum`");
				while ($einzeltest=sql_fetch_assoc($tests_der_fach_klasse)) {
					$datum=$einzeltest["MyDatum"];
					if ($woche != datum_zu_woche($datum)) {
						$woche = datum_zu_woche($datum);
						echo "tests_woche[".datum_zu_woche($datum)."]='';\n";
					}
					echo "tests_woche[".datum_zu_woche($datum)."]+='".$einzeltest["fach_kuerzel"]." (".$einzeltest["lehrerkuerzel"].") ".$einzeltest["notentyp_kuerzel"]." ".$einzeltest["beschreibung"]." (".datum_strich_zu_punkt_uebersichtlich($datum, "wochentag_kurz", false).")<br />';\n";
				}
				?>
				</script>
