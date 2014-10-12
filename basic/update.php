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


// Funktion zum Upgrade aller Dateien auf eine neue Version. Allerdings will ich das so doch nicht machen - muss allgemeiner gehen mit der Dateiliste aus einer Zip-Datei oder so
// not in use
function upgrade($pfad, $mode) {
    include($pfad."basic/upgrade.php");
    $db_version=sql_result(db_conn_and_sql("SELECT programmversion FROM benutzer WHERE id=1"),0,"benutzer.programmversion");
    if ($mode=="info") {
        $i=0;
        while ($upgrade_array[$i]["version"]>$db_version) {
            echo "Version ".$upgrade_array[$i]["version"].":<ul>";
            foreach($upgrade_array[$i]["description"] as $my_description)
                echo "<li>$my_description</li>";
            echo "</ul>";
            
            echo "Files:";
            foreach($upgrade_array[$i]["delete"] as $my_deleters)
                echo "unlink(".$pfad.$my_deleters.");<br>";
            foreach($upgrade_array[$i]["create"] as $my_files)
                echo "copy(http://kreda.de/upgrade/".$upgrade[$i]["name"].$my_files["location"].", ".$pfad.$my_files["location"].");<br>";
            echo "done";
            $i++;
        }
    }
    
    if ($mode=="handle") {
        
    }
}

// UPDATES:
$db_version=sql_result(db_conn_and_sql("SELECT programmversion FROM benutzer WHERE id=1"),0,"benutzer.programmversion");
if ($db_version<"0.96B Beta" && $programmversion>$db_version) {
    db_conn_and_sql("ALTER table benutzer add letzte_themen_auswahl varchar(50)");
    db_conn_and_sql("ALTER table benutzer add letzter_lernbereich mediumint(9) unsigned");
    db_conn_and_sql("ALTER table benutzer add programmversion varchar(15)");
    db_conn_and_sql("ALTER table fach_klasse add sitzplan_klasse smallint(6) unsigned");
}

if ($db_version<"0.96B Beta" && $programmversion>$db_version) {
    // lernbereich_faktor schreiben
    db_conn_and_sql("ALTER table benutzer add lb_faktor decimal(5,2)");
    db_conn_and_sql("UPDATE benutzer SET lb_faktor=0 WHERE id=1");
    // lernbereichs-nummern als zahlen (nicht mehr als varchar)
    db_conn_and_sql("ALTER TABLE lernbereich MODIFY nummer tinyint(4)");
}

if ($db_version<"0.96C Beta" && $programmversion>$db_version) {
    db_conn_and_sql("ALTER table benutzer ADD letzte_fachklasse int(11) unsigned");
    db_conn_and_sql("ALTER table benutzer ADD letzte_klasse smallint(6) unsigned");
    db_conn_and_sql("ALTER table fach_klasse add letzte_themen_auswahl varchar(50)");
    db_conn_and_sql("ALTER table fach_klasse add letzter_lernbereich mediumint(9) unsigned");
    //db_conn_and_sql("CREATE TABLE IF NOT EXISTS db_changes (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, tab_name VARCHAR(100) NOT NULL, tab_id1 INTEGER NOT NULL, tab_id2 INTEGER, operation VARCHAR(1));");
}
if ($db_version<"0.96D Beta" && $programmversion>$db_version) {
    echo "Update der Datenbank von ".$db_version." auf ".$programmversion." durchgef&uuml;hrt.";
    db_conn_and_sql("DROP INDEX `PRIMARY` ON `abschnittsplanung`;");
    db_conn_and_sql("ALTER table abschnittsplanung add PRIMARY KEY (abschnitt, plan, position);");
    db_conn_and_sql("ALTER table abschnittsplanung add inhalt text");
    db_conn_and_sql("CREATE TABLE liste (id int(11) unsigned NOT NULL AUTO_INCREMENT,
        fk_oder_klasse tinyint(1) unsigned DEFAULT 0,
        fach_klasse int(11) unsigned,
        klasse smallint(6) unsigned,
        name varchar(100),
        erstelldatum date NOT NULL,
        typ varchar(10) DEFAULT 'c',
        abgeschlossen tinyint(1),
        PRIMARY KEY (id)
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;");
    db_conn_and_sql("CREATE TABLE liste_schueler (liste int(11) unsigned NOT NULL,
        schueler int(11) unsigned NOT NULL,
        inhalt text,
        PRIMARY KEY (liste, schueler)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
    db_conn_and_sql("CREATE TABLE vorlagen (id int(11) unsigned NOT NULL,
        kurzinhalt varchar(255),
        inhalt text,
        PRIMARY KEY (id)
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;");
    db_conn_and_sql("ALTER TABLE abschnitt ADD methode tinyint(4) unsigned;");
    
    // nur 1 mal durchfuehren!!!
    // Update der abschnittsplanung
    $alle_plaene=db_conn_and_sql("SELECT * FROM plan");
    for ($n=0; $n<sql_num_rows($alle_plaene); $n++) {
        $old_plan=db_conn_and_sql("SELECT * FROM abschnittsplanung WHERE plan=".sql_result($alle_plaene, $n, "plan.id")." ORDER BY position DESC");
        for ($k=0;$k<sql_num_rows($old_plan); $k++)
            db_conn_and_sql("UPDATE abschnittsplanung SET position=".(sql_result($old_plan, $k,"abschnittsplanung.position")+1)." WHERE abschnitt=".sql_result($old_plan, $k,"abschnittsplanung.abschnitt")." AND plan=".sql_result($old_plan, $k,"abschnittsplanung.plan"));
        db_conn_and_sql('INSERT INTO abschnittsplanung (abschnitt, plan, minuten, position, inhalt) VALUES (0, '.sql_result($alle_plaene, $n, "plan.id").', '.sql_result($alle_plaene, $n, "plan.minuten_verschieben").', 0, '.apostroph_bei_bedarf(sql_result($alle_plaene, $n, "plan.eingangsbemerkungen")).');');
        db_conn_and_sql('INSERT INTO abschnittsplanung (abschnitt, plan, minuten, position, inhalt) VALUES (0, '.sql_result($alle_plaene, $n, "plan.id").', 1, '.(sql_result($old_plan, 0, "abschnittsplanung.position")+2).', '.apostroph_bei_bedarf(sql_result($alle_plaene, $n, "plan.schlussbemerkungen")).');');
    }
    db_conn_and_sql('ALTER TABLE `plan` DROP `eingangsbemerkungen`;');
    db_conn_and_sql('ALTER TABLE `plan` DROP `schlussbemerkungen`;');
    db_conn_and_sql('ALTER TABLE `plan` DROP `minuten_verschieben`;');
    
    db_conn_and_sql('ALTER TABLE `stundenplan` ADD `gilt_ab` date;');
    
    db_conn_and_sql("CREATE TABLE mitarbeit (
        schueler int(11) unsigned NOT NULL,
        plan int(11) unsigned NOT NULL,
        anzahl tinyint(4) NOT NULL,
        PRIMARY KEY (schueler, plan)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
    
    $alle_texte=db_conn_and_sql("SELECT * FROM abschnitt");
    for ($n=0; $n<sql_num_rows($alle_texte); $n++) {
        $mein_text=db_conn_and_sql("SELECT * FROM sonstiges WHERE abschnitt=".sql_result($alle_texte, $n, "abschnitt.id"));
        $bilder_wurden_eingetragen=false;
        for ($k=0;$k<sql_num_rows($mein_text); $k++) {
            $text=sql_result($mein_text, $k, "sonstiges.inhalt");
            if (preg_match("#(.*)\[fett\](.*)\[/fett\](.*)#is",$text)) $text=preg_replace('~\[fett\](.*)\[/fett\]~U', '[b]\1[/b]', $text);
            if (preg_match("#(.*)\[unterstreichen\](.*)\[/unterstreichen\](.*)#is",$text)) $text=preg_replace('~\[unterstreichen\](.*)\[/unterstreichen\]~U', '[u]\1[/u]', $text);
            if (preg_match("#(.*)\[kursiv\](.*)\[/kursiv\](.*)#is",$text)) $text=preg_replace('~\[kursiv\](.*)\[/kursiv\]~U', '[i]\1[/i]', $text);
            
            if (preg_match("#(.*)\[rot\](.*)\[/rot\](.*)#is",$text)) $text=preg_replace('~\[rot\](.*)\[/rot\]~U', '[red]\1[/red]', $text);
            if (preg_match("#(.*)\[gelb\](.*)\[/gelb\](.*)#is",$text)) $text=preg_replace('~\[gelb\](.*)\[/gelb\]~U', '[yellow]\1[/yellow]', $text);
            if (preg_match("#(.*)\[blau\](.*)\[/blau\](.*)#is",$text)) $text=preg_replace('~\[blau\](.*)\[/blau\]~U', '[blue]\1[/blue]', $text);
            if (preg_match("#(.*)\[gruen\](.*)\[/gruen\](.*)#is",$text)) $text=preg_replace('~\[gruen\](.*)\[/gruen\]~U', '[green]\1[/green]', $text);
            if (preg_match("#(.*)\[braun\](.*)\[/braun\](.*)#is",$text)) $text=preg_replace('~\[braun\](.*)\[/braun\]~U', '[brown]\1[/brown]', $text);
            if (preg_match("#(.*)\[grau\](.*)\[/grau\](.*)#is",$text)) $text=preg_replace('~\[grau\](.*)\[/grau\]~U', '[gray]\1[/gray]', $text);
            
            switch (sql_result($mein_text, $k, "sonstiges.typ")) {
                case 1: $text='[orange]Erlaeuterung:[/orange] '.$text; break;
                case 2: $text='[orange]Diskussion:[/orange] '.$text; break;
                case 3: $text='[brown]Merke:[/brown] '.$text; break;
                case 4: $text='[brown]Def:[/brown] '.$text; break;
                case 5: $text='[bor]'.$text.'[/bor]'; break;
                case 6: $text='[code]'.$text.'[/code]'; break;
                case 8: $hilf=explode(":",$text);
						$text= '[brown]'.$hilf[0].":[/brown]".substr($text,strlen($hilf[0])+1); break;
            }
            
            if (!$bilder_wurden_eingetragen) {
                $bilder_wurden_eingetragen=true;
                $meine_bilder=db_conn_and_sql("SELECT * FROM grafik_abschnitt WHERE abschnitt=".sql_result($alle_texte, $n, "abschnitt.id")." ORDER BY grafik_abschnitt.grafik DESC");
                for ($bi=0; $bi<sql_num_rows($meine_bilder); $bi++) {
                    $ausrichtung='middle';
                    if (sql_result($meine_bilder, $bi, "grafik_abschnitt.position")==1) $ausrichtung='left';
                    if (sql_result($meine_bilder, $bi, "grafik_abschnitt.position")==2) $ausrichtung='right';
                    $groesse='5';
                    if (sql_result($meine_bilder, $bi, "grafik_abschnitt.groesse")>0.5) $groesse=sql_result($meine_bilder, $bi, "grafik_abschnitt.groesse");
                    $text='[grafic;'.sql_result($meine_bilder, $bi, "grafik_abschnitt.grafik").';'.$ausrichtung.';'.$groesse.']
'.$text;
                }
                
                $meine_links=db_conn_and_sql("SELECT * FROM link_abschnitt, link WHERE link_abschnitt.link=link.id AND abschnitt=".sql_result($alle_texte, $n, "abschnitt.id")." ORDER BY link_abschnitt.link DESC");
                for ($bi=0; $bi<sql_num_rows($meine_links); $bi++) {
                    if (sql_result($meine_links, $bi, "link.lokal")==1)
                        $text='[file;'.sql_result($meine_links, $bi, "link_abschnitt.link").']
'.$text;
                    else {
                        $text='[url;'.sql_result($meine_links, $bi, "link.url").';'.sql_result($meine_links, $bi, "link.beschreibung").']
'.$text;
                        db_conn_and_sql("DELETE FROM link WHERE id=".sql_result($meine_links, $bi, "link_abschnitt.link"));
                    }
                }
            }
            
            echo ($n/sql_num_rows($alle_texte)).'<br />'.$text;
            db_conn_and_sql("UPDATE sonstiges SET inhalt=".apostroph_bei_bedarf($text)." WHERE id=".sql_result($mein_text, $k,"sonstiges.id"));
        }
        
        if (!$bilder_wurden_eingetragen) {
            $bilder_wurden_eingetragen=true;
            $text='';
            $meine_bilder=db_conn_and_sql("SELECT * FROM grafik_abschnitt WHERE abschnitt=".sql_result($alle_texte, $n, "abschnitt.id")." ORDER BY grafik_abschnitt.grafik DESC");
            for ($bi=0; $bi<sql_num_rows($meine_bilder); $bi++) {
                $ausrichtung='middle';
                if (sql_result($meine_bilder, $bi, "grafik_abschnitt.position")==1) $ausrichtung='left';
                if (sql_result($meine_bilder, $bi, "grafik_abschnitt.position")==2) $ausrichtung='right';
                $groesse=5;
                if (sql_result($meine_bilder, $bi, "grafik_abschnitt.groesse")>0.5) $groesse=sql_result($meine_bilder, $bi, "grafik_abschnitt.groesse");
                $text='[grafic;'.sql_result($meine_bilder, $bi, "grafik_abschnitt.grafik").';'.$ausrichtung.';'.$groesse.']
'.$text;
            }
            
            $meine_links=db_conn_and_sql("SELECT * FROM link_abschnitt, link WHERE link_abschnitt.link=link.id AND abschnitt=".sql_result($alle_texte, $n, "abschnitt.id")." ORDER BY link_abschnitt.link DESC");
            for ($bi=0; $bi<sql_num_rows($meine_links); $bi++) {
                if (sql_result($meine_links, $bi, "link.lokal")==1) {
                    $text='[file;'.sql_result($meine_links, $bi, "link_abschnitt.link").']
'.$text;
                }
                else {
                    $text='[url;'.sql_result($meine_links, $bi, "link.url").';'.sql_result($meine_links, $bi, "link.beschreibung").']
'.$text;
                    db_conn_and_sql("DELETE FROM link WHERE id=".sql_result($meine_links, $bi, "link_abschnitt.link"));
                }
            }
            if (sql_num_rows($meine_links)+sql_num_rows($meine_bilder)>0)
                db_conn_and_sql("INSERT INTO sonstiges (inhalt, abschnitt, typ) values (".apostroph_bei_bedarf($text).", ".sql_result($alle_texte, $n, "abschnitt.id").",7);");
            echo '<br />'.$text;
        }
    }
    db_conn_and_sql("UPDATE sonstiges SET typ=7 WHERE id<3000");
    
    // Update Aufgaben:
    $alle_texte=db_conn_and_sql("SELECT * FROM aufgabe");
    for ($n=0; $n<sql_num_rows($alle_texte); $n++) {
        $text=sql_result($alle_texte, $n, "aufgabe.text");
            if (preg_match("#(.*)\[fett\](.*)\[/fett\](.*)#is",$text)) $text=preg_replace('~\[fett\](.*)\[/fett\]~U', '[b]\1[/b]', $text);
            if (preg_match("#(.*)\[unterstreichen\](.*)\[/unterstreichen\](.*)#is",$text)) $text=preg_replace('~\[unterstreichen\](.*)\[/unterstreichen\]~U', '[u]\1[/u]', $text);
            if (preg_match("#(.*)\[kursiv\](.*)\[/kursiv\](.*)#is",$text)) $text=preg_replace('~\[kursiv\](.*)\[/kursiv\]~U', '[i]\1[/i]', $text);
            
            if (preg_match("#(.*)\[rot\](.*)\[/rot\](.*)#is",$text)) $text=preg_replace('~\[rot\](.*)\[/rot\]~U', '[red]\1[/red]', $text);
            if (preg_match("#(.*)\[gelb\](.*)\[/gelb\](.*)#is",$text)) $text=preg_replace('~\[gelb\](.*)\[/gelb\]~U', '[yellow]\1[/yellow]', $text);
            if (preg_match("#(.*)\[blau\](.*)\[/blau\](.*)#is",$text)) $text=preg_replace('~\[blau\](.*)\[/blau\]~U', '[blue]\1[/blue]', $text);
            if (preg_match("#(.*)\[gruen\](.*)\[/gruen\](.*)#is",$text)) $text=preg_replace('~\[gruen\](.*)\[/gruen\]~U', '[green]\1[/green]', $text);
            if (preg_match("#(.*)\[braun\](.*)\[/braun\](.*)#is",$text)) $text=preg_replace('~\[braun\](.*)\[/braun\]~U', '[brown]\1[/brown]', $text);
            if (preg_match("#(.*)\[grau\](.*)\[/grau\](.*)#is",$text)) $text=preg_replace('~\[grau\](.*)\[/grau\]~U', '[gray]\1[/gray]', $text);
            
        $loesung=sql_result($alle_texte, $n, "aufgabe.loesung");
            if (preg_match("#(.*)\[fett\](.*)\[/fett\](.*)#is",$loesung)) $loesung=preg_replace('~\[fett\](.*)\[/fett\]~U', '[b]\1[/b]', $loesung);
            if (preg_match("#(.*)\[unterstreichen\](.*)\[/unterstreichen\](.*)#is",$loesung)) $loesung=preg_replace('~\[unterstreichen\](.*)\[/unterstreichen\]~U', '[u]\1[/u]', $loesung);
            if (preg_match("#(.*)\[kursiv\](.*)\[/kursiv\](.*)#is",$loesung)) $loesung=preg_replace('~\[kursiv\](.*)\[/kursiv\]~U', '[i]\1[/i]', $loesung);
            
            if (preg_match("#(.*)\[rot\](.*)\[/rot\](.*)#is",$loesung)) $loesung=preg_replace('~\[rot\](.*)\[/rot\]~U', '[red]\1[/red]', $loesung);
            if (preg_match("#(.*)\[gelb\](.*)\[/gelb\](.*)#is",$loesung)) $loesung=preg_replace('~\[gelb\](.*)\[/gelb\]~U', '[yellow]\1[/yellow]', $loesung);
            if (preg_match("#(.*)\[blau\](.*)\[/blau\](.*)#is",$loesung)) $loesung=preg_replace('~\[blau\](.*)\[/blau\]~U', '[blue]\1[/blue]', $loesung);
            if (preg_match("#(.*)\[gruen\](.*)\[/gruen\](.*)#is",$loesung)) $loesung=preg_replace('~\[gruen\](.*)\[/gruen\]~U', '[green]\1[/green]', $loesung);
            if (preg_match("#(.*)\[braun\](.*)\[/braun\](.*)#is",$loesung)) $loesung=preg_replace('~\[braun\](.*)\[/braun\]~U', '[brown]\1[/brown]', $loesung);
            if (preg_match("#(.*)\[grau\](.*)\[/grau\](.*)#is",$loesung)) $loesung=preg_replace('~\[grau\](.*)\[/grau\]~U', '[gray]\1[/gray]', $loesung);
            
            $meine_bilder=db_conn_and_sql("SELECT * FROM grafik_aufgabe WHERE aufgabe=".sql_result($alle_texte, $n, "aufgabe.id")." ORDER BY grafik_aufgabe.grafik DESC");
            for ($bi=0; $bi<sql_num_rows($meine_bilder); $bi++) {
                    $ausrichtung='middle';
                    if (sql_result($alle_texte, $n, "aufgabe.bildanordnung")==3) $ausrichtung='left';
                    if (sql_result($alle_texte, $n, "aufgabe.bildanordnung")==2) $ausrichtung='right';
                    $groesse='5';
                    if (sql_result($meine_bilder, $bi, "grafik_aufgabe.groesse")>0.5) $groesse=sql_result($meine_bilder, $bi, "grafik_aufgabe.groesse");
                    $pre='';
                    if (sql_result($alle_texte, $n, "aufgabe.bildbeschriftung")==1) $pre='a) ';
                    if (sql_result($alle_texte, $n, "aufgabe.bildbeschriftung")==2) $pre='A) ';
                    if (sql_result($alle_texte, $n, "aufgabe.bildbeschriftung")==3) $pre='1) ';
                    if (sql_result($alle_texte, $n, "aufgabe.bildanordnung")>1)
                        $text='
'.$pre.'[grafic;'.sql_result($meine_bilder, $bi, "grafik_aufgabe.grafik").';'.$ausrichtung.';'.$groesse.']'.$text;
                    else $text.='
'.$pre.'[grafic;'.sql_result($meine_bilder, $bi, "grafik_aufgabe.grafik").';'.$ausrichtung.';'.$groesse.']';

             }
             if (sql_result($alle_texte, $n, "aufgabe.kariert")==1) $text.='
[boxed;15x'.sql_result($alle_texte, $n, "aufgabe.cm").';middle]';
             if (sql_result($alle_texte, $n, "aufgabe.kariert")==2) $text.='
[ruled;15x'.sql_result($alle_texte, $n, "aufgabe.cm").';middle]';
             if (sql_result($alle_texte, $n, "aufgabe.kariert")==3) $text.='
[millimeter;15x'.sql_result($alle_texte, $n, "aufgabe.cm").';middle]';
            
            
            echo ($n/sql_num_rows($alle_texte)).'<br />'.$text;
            db_conn_and_sql("UPDATE aufgabe SET text=".apostroph_bei_bedarf($text).", loesung=".apostroph_bei_bedarf($loesung)." WHERE id=".sql_result($alle_texte, $n,"aufgabe.id"));
        }
}

if ($db_version<"0.97A Beta" && $programmversion>$db_version) {
    db_conn_and_sql("ALTER TABLE lehrplan ADD von tinyint(3) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE lehrplan ADD bis tinyint(3) unsigned DEFAULT 13");
    db_conn_and_sql("ALTER TABLE lehrplan ADD bemerkung varchar(255) DEFAULT NULL");
    db_conn_and_sql("ALTER TABLE lehrplan ADD zusatz varchar(30) DEFAULT NULL");
    db_conn_and_sql("ALTER TABLE block_abschnitt MODIFY position tinyint(4);"); // negative Zahlen zulassen, damit die Verschiebung von Bloecken "an den Anfang" funktioniert
}

if ($db_version<"0.98A Beta" && $programmversion>$db_version) {
    // Kommazahlen bei Bewertungstabellen erlauben
    db_conn_and_sql("ALTER TABLE bewertung_note MODIFY prozent_bis decimal(5,2)");
	// Blockunterricht nutzen
    db_conn_and_sql("ALTER TABLE plan ADD ohne_pause tinyint(1) DEFAULT 0");
    db_conn_and_sql("ALTER TABLE stundenplan ADD ohne_pause tinyint(1) DEFAULT 0");
    
    // Maifeiertag fehlt
    db_conn_and_sql("INSERT INTO feste_feiertage (id, name, anzeigen) VALUES (12, 'Tag der Arbeit', 1)");

    // immer in letzte Update-Version rein:
    db_conn_and_sql("UPDATE benutzer SET programmversion=".apostroph_bei_bedarf($programmversion)." WHERE id=1");
}

if ($db_version<"0.98B Beta" && $programmversion>$db_version) {
    // Username und Password
    db_conn_and_sql("ALTER TABLE benutzer ADD username varchar(50) NOT NULL DEFAULT 'dontuse'");
    db_conn_and_sql("ALTER TABLE benutzer ADD md5password varchar(32) NOT NULL DEFAULT 'dontuse'");
    db_conn_and_sql("ALTER TABLE benutzer ADD email varchar(70) NOT NULL DEFAULT 'dontuse'");
    db_conn_and_sql("ALTER TABLE benutzer ADD log text DEFAULT NULL");
    db_conn_and_sql("ALTER TABLE benutzer ADD lastlogin timestamp DEFAULT NULL");
    db_conn_and_sql("ALTER TABLE benutzer ADD bundesland smallint(6) unsigned DEFAULT 12"); //sachsen
    db_conn_and_sql("ALTER TABLE aufgabe ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE aufsicht ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE bewertungstabelle ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE block ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE buch ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE fach_klasse ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE faecher ADD user int(11) unsigned DEFAULT 0");
    db_conn_and_sql("CREATE TABLE feiertage_user (ff smallint(6) unsigned NOT NULL, user int(11) unsigned NOT NULL, aktiv tinyint(1) DEFAULT 1, PRIMARY KEY(ff, user))");
    db_conn_and_sql("ALTER TABLE grafik ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE kollege ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE konferenz ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("CREATE TABLE lp_user (lehrplan int(11) unsigned NOT NULL, user int(11) unsigned NOT NULL, aktiv tinyint(1) DEFAULT 1, PRIMARY KEY(lehrplan, user))");
    db_conn_and_sql("ALTER TABLE link ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE material ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE notentypen ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE notiz ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE schule ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("CREATE TABLE schule_user (schule int(11) unsigned NOT NULL, user int(11) unsigned NOT NULL, aktiv tinyint(1) DEFAULT 1, PRIMARY KEY(schule, user))");
    db_conn_and_sql("ALTER TABLE schuljahr ADD bundesland smallint(6) unsigned DEFAULT 12"); //sachsen
    db_conn_and_sql("ALTER TABLE schuljahr ADD schule int(11) unsigned DEFAULT 0"); //allgemein
    db_conn_and_sql("ALTER TABLE sitzplan ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE test ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE thema ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE vorlagen ADD user int(11) unsigned DEFAULT 1");
    db_conn_and_sql("ALTER TABLE woche_ab ADD user int(11) unsigned DEFAULT 1");
	
    // immer in letzte Update-Version rein:
    db_conn_and_sql("UPDATE benutzer SET programmversion=".apostroph_bei_bedarf($programmversion)." WHERE id=1");
}


?>
