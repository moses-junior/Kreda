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

    include "../funktionen.php";
    $plan_id=injaway($_POST["plan_id"]);
    
    if (!proofuser("plan",$plan_id) or !proofuser("fach_klasse",$_POST["fach_klasse"]))
		die("Sie sind hierzu nicht berechtigt.");
    
    if ($_POST["ohne_pause_checkbox"]==1)
		$ohne_pause=0;
	else
		$ohne_pause=1;
	
    db_conn_and_sql("UPDATE `plan`
        SET `datum`='".datum_punkt_zu_strich($_POST['datum'])."', `startzeit`=".apostroph_bei_bedarf($_POST['zeit'].":00").", `bemerkung`=".apostroph_bei_bedarf($_POST['bemerkung']).", `zusatzziele`=".apostroph_bei_bedarf($_POST['zusatzziele']).", `struktur`=".apostroph_bei_bedarf($_POST['struktur']).", `ustd`=".apostroph_bei_bedarf($_POST['stunden']).", `notizen`=".apostroph_bei_bedarf($_POST['notizen']).", `vorbereitet`=".leer_NULL($_POST['vorbereitet']).", `ohne_pause`=".$ohne_pause."
        WHERE `id`=".$plan_id);
        // , `minuten_verschieben`=".leer_NULL($_POST['zeit_start']).", `eingangsbemerkungen`=".apostroph_bei_bedarf($_POST['einleitung']).", `schlussbemerkungen`=".apostroph_bei_bedarf($_POST['schluss'])."

    db_conn_and_sql("UPDATE `fach_klasse` SET `info`=".apostroph_bei_bedarf($_POST['fk_info'])." WHERE `id`=".injaway($_POST['fach_klasse']));

    $i=0;
    while (isset($_POST['abschnittsid_'.$i])) {
        // allgemeine Abschnittsaenderungen
        db_conn_and_sql("UPDATE `abschnittsplanung`
            SET `minuten`=".leer_NULL($_POST['zeit_'.$i]).", `phase`=".leer_NULL($_POST['phase_'.$i]).", `bemerkung`=".apostroph_bei_bedarf($_POST['plan_bemerkung_'.$i])."
            WHERE `abschnitt`=".$_POST['abschnittsid_'.$i]." AND `plan`=".$plan_id." AND position=".$_POST['abschnittsposition_'.$i]);
        
        // Einmalabschnitte koennen veraendert werden:
        if (isset($_POST['einmalabschnitt_'.$i])) {
            db_conn_and_sql("UPDATE `abschnittsplanung`
                SET `inhalt`=".apostroph_bei_bedarf($_POST['einmalabschnitt_'.$i])."
                WHERE `abschnitt`=".$_POST['abschnittsid_'.$i]." AND `plan`=".$plan_id." AND position=".$_POST['abschnittsposition_'.$i]);
        }
        
        $i++;
    }
    
    
    // Einmalabschnitt hinzufuegen, wenn ausgewaehlt:
    if ($_POST['new_plan_sec_checkbox']) {
        db_conn_and_sql("INSERT INTO abschnittsplanung (abschnitt, plan, position, minuten, phase, bemerkung, inhalt)
            VALUES (0, ".$plan_id.", ".$i.", ".leer_NULL($_POST['one_way_time']).", ".leer_NULL($_POST['phase_'.$i]).", ".apostroph_bei_bedarf($_POST['plan_bemerkung_'.$i]).", ".apostroph_bei_bedarf($_POST['one_way_section']).");" );
    }
    
    // Fundus-Abschnitt hinzufuegen, wenn ausgewaehlt:
    if ($_POST['new_section_checkbox']) {
		$id=db_conn_and_sql("INSERT INTO `abschnitt` (`hefter`, `medium`,`ziel`,`minuten`,`nachbereitung`,`sozialform`, `methode`) VALUES
			(".leer_NULL($_POST['hefter_1']).", ".leer_NULL($_POST['medium_1']).", ".apostroph_bei_bedarf($_POST['ziel_1']).", ".$_POST['minuten_1'].", ".apostroph_bei_bedarf($_POST['bemerkung_1']).", ".leer_NULL($_POST['sozialform_1']).", ".leer_NULL($_POST['method_1']).");");
		
		db_conn_and_sql("INSERT INTO `block_abschnitt` (`position`,`abschnitt`,`block`) VALUES (".(sql_num_rows(db_conn_and_sql("SELECT * FROM `block_abschnitt` WHERE `block`=".injaway($_POST["block"])))).", ".$id.", ".injaway($_POST['block']).");");
		
		// bei plan-Angabe in Plan einfuegen
		if ($plan_id>0)
            db_conn_and_sql("INSERT INTO `abschnittsplanung` (`abschnitt`, `plan`, `minuten`, `position`) VALUES
                (".$id.", ".$plan_id.", ".leer_NULL($_POST['minuten_1']).", ".(sql_num_rows(db_conn_and_sql("SELECT * FROM `abschnittsplanung` WHERE `plan`=".$plan_id))).");");
        
        
        switch ($_POST["add_content_ns"]) {
            case 1:
                db_conn_and_sql("INSERT INTO `ueberschrift` (`abschnitt`, `ebene`, `text`, `typ`) VALUES
                    (".$id.", ".$_POST['ueberschrift_ebene_1'].", ".apostroph_bei_bedarf($_POST['ueberschrift_text_1']).", ".apostroph_bei_bedarf($_POST["ueberschrift_typ_1"]).");");
                break;
            
            case 2:
                    if ($_POST["test_neu"]) {
                    if ($_POST["test_lokal"]=="erstellen") {
                        $test_id=db_conn_and_sql("INSERT INTO `test` (`notentyp`, `url`, `lernbereich`, `platz_lassen`, `bearbeitungszeit`, `bemerkung`,`punkte`,`vorspann`, `user`) VALUES
                                (".$_POST['test_notentyp'].", null, ".leer_NULL($_POST['test_lernbereich']).", ".leer_NULL($_POST['test_platz']).", ".leer_NULL($_POST['test_zeit']).", null, ".leer_NULL($_POST['test_punkte']).", ".apostroph_bei_bedarf($_POST['test_vorspann']).", ".$_SESSION['user_id'].");");
                    
                        }
                    else {
                        $tempname = $_FILES['test_datei']['tmp_name'];
                            $name = $_FILES['test_datei']['name'];
                        
                        if(empty($_FILES['test_datei']['name'])) $err[] = "Eine Datei muss ausgew&auml;hlt werden";
                            if(empty($err)) {
                            $dateiname=pfad_und_dateiname($_POST["lernbereich"],'test',$name,$tempname);
                            
                                $test_id=db_conn_and_sql("INSERT INTO `test` (`notentyp`, `url`, `lernbereich`, `platz_lassen`, `bearbeitungszeit`, `bemerkung`,`punkte`,`vorspann`, `user`) VALUES
                                (".$_POST['test_notentyp'].", ".apostroph_bei_bedarf($dateiname["test_datei"]).", ".leer_NULL($_POST['test_lernbereich']).", null, ".leer_NULL($_POST['test_zeit']).", null, ".leer_NULL($_POST['test_punkte']).", ".apostroph_bei_bedarf($_POST['test_vorspann']).", ".$_SESSION['user_id'].");");
                                
                        }
                    }
                        
                    for ($i=0;$i<10;$i++)
                        if ($_POST["test_thema_".$i]!="-")
                                db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`, `id`, `thema`) VALUES
                                (5, ".$test_id.", ".$_POST["test_thema_".$i].");");
                    
                        db_conn_and_sql("INSERT INTO `test_abschnitt` (`test`, `abschnitt`) VALUES
                        (".$test_id.", ".$id.");");
                }
                    else {
                    $ids = explode(";",$_POST["test_ids"]); array_pop($ids); // ...weil das letzte leer ist
                    foreach ($ids as $value) db_conn_and_sql("INSERT INTO `test_abschnitt` (`test`, `abschnitt`) VALUES
                            (".$value.", ".$id.");");
                }
                break;
                    
            case 3:
                if ($inhalt_untertyp==2) $beispiel=true; else $beispiel=0;
                    if ($_POST["aufgabe_neu"]) {
                        $aufgaben_id=db_conn_and_sql("INSERT INTO `aufgabe` (`text`, `bemerkung`,`loesung`,`punkte`,`lernbereich`,`bearbeitungszeit`,`schwierigkeitsgrad`, `teilaufgaben_nebeneinander`, `user`) VALUES
                            (".apostroph_bei_bedarf($_POST['text']).", ".apostroph_bei_bedarf($_POST['bemerkung']).", ".apostroph_bei_bedarf($_POST['loesung']).", ".leer_NULL($_POST['punkte']).", ".leer_NULL($_POST['lernbereich']).", ".leer_NULL($_POST['bearbeitungszeit']).", ".leer_NULL($_POST['schwierigkeitsgrad']).", ".leer_NULL($_POST["teilaufgaben_nebeneinander"]).", ".$_SESSION['user_id'].");");
                        
                    
                        $thema=0;
                    $verwendete_themen='';
                    while($_POST["thema_".$thema]!="-" and $thema<10) {
                            $verwendete_themen.=$_POST["thema_".$thema].';';
                        db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (1,".$aufgaben_id.",".$_POST["thema_".$thema].");");
                        $thema++;
                        }
                    
                    if ($_POST['art']!="text") {
                            /*echo "INSERT INTO `buch_aufgabe` (`aufgabe`, `buch`,`seite`,`nummer`) VALUES
                            (".$aufgaben_id.", ".$_POST['art'].", ".leer_NULL($_POST['seite']).", ".apostroph_bei_bedarf($_POST['nummer']).");";*/
                        db_conn_and_sql("INSERT INTO `buch_aufgabe` (`aufgabe`, `buch`,`seite`,`nummer`) VALUES
                                (".$aufgaben_id.", ".$_POST['art'].", ".leer_NULL($_POST['seite']).", ".apostroph_bei_bedarf($_POST['nummer']).");");
                        //db_conn_and_sql("UPDATE `buch` SET `letztes_thema`=".$_POST['thema_0'].", `letzter_lernbereich`= ".$_POST['lernbereich']." WHERE `id`=".$_POST['art']);
                    }
                        
                    //$bilder_ids = explode(";",$_POST["inhalt_ids"]); array_pop($bilder_ids); // ...weil das letzte leer ist
                    //foreach ($bilder_ids as $value) {
                    //        $hilf=explode(":",$value);
                    //    db_conn_and_sql("INSERT INTO `grafik_aufgabe` (`grafik`, `aufgabe`,`groesse`) VALUES
                    //(".$hilf[0].", ".$aufgaben_id.", ".punkt_statt_komma_zahl($hilf[1]).");");
                    //    }
                    /*echo "INSERT INTO `aufgabe_abschnitt` (`aufgabe`, `abschnitt`,`beispiel`) VALUES
                    (".$aufgaben_id.", ".$id.", ".$beispiel.");";*/
                        db_conn_and_sql("INSERT INTO `aufgabe_abschnitt` (`aufgabe`, `abschnitt`,`beispiel`) VALUES
                        (".$aufgaben_id.", ".$id.", ".$beispiel.");");
                    
                        db_conn_and_sql("UPDATE fach_klasse SET letzter_lernbereich=".$_POST['lernbereich'].", letzte_themen_auswahl=".apostroph_bei_bedarf($verwendete_themen)." WHERE id=".sql_result(db_conn_and_sql("SELECT letzte_fachklasse FROM benutzer WHERE benutzer.id=".$_SESSION['user_id']), 0, "benutzer.letzte_fachklasse"));
                }
                else {
                        $ids = explode(";",$_POST["aufgabe_ids"]); array_pop($ids); // ...weil das letzte leer ist
                    foreach ($ids as $value) db_conn_and_sql("INSERT INTO `aufgabe_abschnitt` (`aufgabe`, `abschnitt`,`beispiel`) VALUES
                        (".$value.", ".$id.", ".$beispiel.");");
                    }
                break;
            case 6:
                    if ($_POST["material_neu"]) {
                    $material_id=db_conn_and_sql("INSERT INTO `material` (`name`, `beschreibung`, `aufbewahrungsort`, `user`) VALUES
                        (".apostroph_bei_bedarf($_POST['material_name']).", ".apostroph_bei_bedarf($_POST['material_beschreibung']).", ".apostroph_bei_bedarf($_POST['material_aufbewahrungsort']).", ".$_SESSION['user_id'].");");
                        
                    db_conn_and_sql("DELETE FROM `themenzuordnung` WHERE `typ`=6 AND `id`=".$id);
                        $thema=0;
                    while($_POST["material_thema_".$thema]!="-" and $thema<10) {
                        db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (6,".$material_id.",".$_POST["material_thema_".$thema].");");
                            $thema++;
                    }
                    db_conn_and_sql("INSERT INTO `material_abschnitt` (`material`, `abschnitt`) VALUES
                            (".$material_id.", ".$id.");");
                }
                else {
                        $ids = explode(";",$_POST["material_ids"]); array_pop($ids); // ...weil das letzte leer ist
                    foreach ($ids as $value) db_conn_and_sql("INSERT INTO `material_abschnitt` (`material`, `abschnitt`) VALUES
                        (".$value.", ".$id.");");
                    }
                break;
            case 7:
                    db_conn_and_sql("INSERT INTO `sonstiges` (`abschnitt`, `inhalt`) VALUES
                    (".$id.", ".apostroph_bei_bedarf($_POST['sonstiges_inhalt']).");"); break;
            default: echo "Error: Nicht angegebener Inhaltstyp."; break;
        }
    }

    if ($_POST['vorbereitet'])
        header("Location: ../index.php?tab=stundenplanung&auswahl=fkplan&fk=".$_POST['fach_klasse']."&plan=".$plan_id."&ansicht=druck");
    else
        header("Location: ../index.php?tab=stundenplanung&auswahl=fkplan&fk=".$_POST['fach_klasse']."&plan=".$plan_id."&ansicht=planung");
    //exit;
?>
