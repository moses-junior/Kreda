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

        // im idNfremd-Array sind die dortigen ids und fremd-Schluessel nacheinander. Dabei heißt "all" ignorieren; 0 bedeutet die in der aktuellen Tabelle 0. id
		$tables_array=array(
			// abschnitt: ueberschriften, texte, Verlinkungen von material/aufgabe/test, hausaufgabe_abschnitt
			"abschnitt"=>array(
				"ids"=>array("id"),
                "description"=>"Abschnittscontainer",
				"abhaengig"=>array(
					// heisst: wenn ich den Abschnitt loesche, dann auch alle Ueberschriften, welche als fremdschluessel abschnitt die 0. id (also abschnitt.id) haben
					array("tablename"=>"ueberschrift", "idNfremd"=>array("all",0)),
					array("tablename"=>"sonstiges", "idNfremd"=>array("all",0)),
					array("tablename"=>"link_abschnitt", "idNfremd"=>array("all",0)),
					array("tablename"=>"aufgabe_abschnitt", "idNfremd"=>array("all",0)),
					array("tablename"=>"material_abschnitt", "idNfremd"=>array("all",0)),
					array("tablename"=>"test_abschnitt", "idNfremd"=>array("all",0)),
					array("tablename"=>"hausaufgabe_abschnitt", "idNfremd"=>array("all",0)),
					array("tablename"=>"abschnittsplanung", "idNfremd"=>array(0, "all", "all")))),
			"abschnittsplanung"=>array(
                "description"=>"zu einer Unterrichtsstunde geh&ouml;rende Abschnitts-Zuordnungen",
				"ids"=>array("abschnitt", "plan", "position")),
			// aufgabe: alle verlinkungen, note_aufgabe, themenzuordnung
			"aufgabe"=>array(
				"ids"=>array("id"),
				"fremd"=>array("lernbereich"),
                "description"=>"Aufgaben",
				"abhaengig"=>array(
					array("tablename"=>"aufgabe_abschnitt", "idNfremd"=>array(0)),
					array("tablename"=>"note_aufgabe", "idNfremd"=>array("all", 0)),
					array("tablename"=>"themenzuordnung", "idNfremd"=>array("all", 0), "bedingung"=>array(1)))),
			"aufgabe_abschnitt"=>array(
				"ids"=>array("aufgabe", "abschnitt")),
			"aufsicht"=>array(
				"ids"=>array("id"),
                "description"=>"Aufsicht",
				"fremd"=>array("schule", "schuljahr")),
			// benutzer (nicht loeschbar)
			"berichtigung_vergessen"=>array(
				"ids"=>array("notenbeschreibung", "schueler"),
                "description"=>"Berichtigungseintr&auml;ge"),
			"bewegliche_feiertage"=>array(
				"ids"=>array("id"),
				"fremd"=>array("schule", "schuljahr")),
			// bewertungstabelle: bewertung_note, notenspalte
			"bewertungstabelle"=>array(
				"ids"=>array("id"),
                "description"=>"Bewertungstabellen",
				"abhaengig"=>array(
					array("tablename"=>"notenbeschreibung", "idNfremd"=>array("all", "all", "all", "all", 0)),
					array("tablename"=>"bewertung_note", "idNfremd"=>array(0)))),
			"bewertung_note"=>array(
				"ids"=>array("bewertungstabelle", "note")),
			// block: block_abschnitte (falls einziger block, auch den abschnitt selbst), abhaengige plaene (2), unterbloecke, themenzuordnung
			"block"=>array(
				"ids"=>array("id"),
				"fremd"=>array("block_hoeher", "lernbereich"),
                "description"=>"Bl&ouml;cke bzw. Unterrichtseinheiten",
				"abhaengig"=>array(
					array("tablename"=>"block_abschnitt", "idNfremd"=>array(0)),
					array("tablename"=>"block", "idNfremd"=>array("all", 0)),
					array("tablename"=>"plan", "idNfremd"=>array("all", "all", "all", 0)),
					array("tablename"=>"plan", "idNfremd"=>array("all", "all", "all", "all", 0)),
					array("tablename"=>"themenzuordnung", "idNfremd"=>array("all", 0), "bedingung"=>array(2))
                    )),
			"block_abschnitt"=>array(
				"ids"=>array("block", "abschnitt"),
				"bedingt"=>array(
					array("tablename"=>"abschnitt", "idNfremd"=>array(0), "bedingung"=>false, "zusatzbedingung"=>array("lonely"=>"block")))),
			"brief"=>array(
				"ids"=>array("id"),
                "description"=>"Briefe",
				"fremd"=>array("schueler", "klasse")),
			// buch: aufgaben, seiten-zuordnungen
			"buch"=>array(
				"ids"=>array("id"),
				"fremd"=>array("fach", "schulart"),
                "description"=>"B&uuml;cher",
				"abhaengig"=>array(
					array("tablename"=>"buch_aufgabe", "idNfremd"=>array(0)),
					array("tablename"=>"buch_klassenstufe", "idNfremd"=>array(0)))),
			"buch_aufgabe"=>array(
				"ids"=>array("buch", "aufgabe"),
				"abhaengig"=>array(
					array("tablename"=>"aufgabe", "idNfremd"=>array(1)))),
			"buch_klassenstufe"=>array(
				"ids"=>array("buch", "klassenstufe")),
			"elternkontakt"=>array(
				"ids"=>array("id"),
                "description"=>"Elterngespr&auml;che",
				"fremd"=>array("schueler")),
			// fach_klasse: sitzplaene, notenberechnung und notengruppen, notenspalten, stundenplan, gruppe, liste, plan
			"fach_klasse"=>array(
				"ids"=>array("id"),
				"fremd"=>array("klasse", "fach", "lehrplan", "bewertungstabelle", "sitzplan_klasse", "notenberechnungsvorlage"),
                "description"=>"Fach-Klasse-Kombinationen",
				"abhaengig"=>array(
					array("tablename"=>"gruppe", "idNfremd"=>array(0)),
					array("tablename"=>"liste", "idNfremd"=>array("all", 0)),
					array("tablename"=>"notenbeschreibung", "idNfremd"=>array("all", "all", 0)),
					//array("tablename"=>"notenberechnung", "idNfremd"=>array("all", 0)),
					array("tablename"=>"plan", "idNfremd"=>array("all", "all", 0)),
					array("tablename"=>"stundenplan", "idNfremd"=>array("all", 0))
					)),
			// faecher: fach_klasse, lehrplan, themen, buecher
			"faecher"=>array(
				"ids"=>array("id"),
                "description"=>"F&auml;cher",
				"abhaengig"=>array(
					array("tablename"=>"fach_klasse", "idNfremd"=>array("all", "all", 0)),
					array("tablename"=>"lehrplan", "idNfremd"=>array("all", "all", 0)),
					array("tablename"=>"thema", "idNfremd"=>array("all", 0)),
					array("tablename"=>"buch", "idNfremd"=>array("all", 0)))),
			"ferien"=>array(
                "description"=>"Ferien",
				"ids"=>array("welche", "schuljahr")),
			// nicht loeschbar
			"feste_feiertage"=>array(
				"ids"=>array("id")),
			// grafik: alle verlinkungen, datei loeschen, themenzuordnung
			"grafik"=>array(
				"ids"=>array("id"),
				"fremd"=>array("lernbereich"),
                "description"=>"Grafik-Dateien (Dateien werden noch nicht automatisch gel&ouml;scht)",
				"zusatz"=>"delete_file",
				"abhaengig"=>array(
					array("tablename"=>"grafik_abschnitt", "idNfremd"=>array(0)),
					array("tablename"=>"grafik_aufgabe", "idNfremd"=>array(0)),
					array("tablename"=>"themenzuordnung", "idNfremd"=>array("all", 0), "bedingung"=>array(3)))),
			"grafik_abschnitt"=>array(
				"ids"=>array("grafik", "abschnitt"),
				"bedingt"=>array(
					array("tablename"=>"abschnitt", "idNfremd"=>array(0), "bedingung"=>false, "zusatzbedingung"=>array("lonely"=>"gesamtmaterial")))),
			"grafik_aufgabe"=>array(
				"ids"=>array("grafik", "aufgabe")),
			"gruppe"=>array(
                "description"=>"Sch&uuml;lerzuordnungen zu einer Fach-Klasse-Kombination",
				"ids"=>array("fach_klasse", "schueler")),
			// hausaufgabe: hausaufgabe_abschnitt, hausaufgabe_vergessen
			"hausaufgabe"=>array(
				"ids"=>array("id"),
				"fremd"=>array("plan"),
                "description"=>"Hausaufgaben",
				"abhaengig"=>array(
					array("tablename"=>"hausaufgabe_abschnitt", "idNfremd"=>array(0)),
					array("tablename"=>"hausaufgabe_vergessen", "idNfremd"=>array(0)))),
			"hausaufgabe_abschnitt"=>array(
				"ids"=>array("hausaufgabe", "abschnitt")),
			"hausaufgabe_vergessen"=>array(
				"ids"=>array("hausaufgabe", "schueler"),
                "description"=>"Hausaufgaben-Eintragungen"),
			// klasse: fach_klasse, schueler, briefe, elternabende (konferenz), liste, sitzplan_klasse
			"klasse"=>array(
				"ids"=>array("id", "schuljahr"),
				"fremd"=>array("schule", "schulart"),
                "description"=>"Klassen",
				"abhaengig"=>array(
					array("tablename"=>"fach_klasse", "idNfremd"=>array("all", 0)),
					array("tablename"=>"schueler", "idNfremd"=>array("all", 0)),
					array("tablename"=>"brief", "idNfremd"=>array("all", "all", 0)),
					array("tablename"=>"konferenz", "idNfremd"=>array("all", "all", 0)),
					array("tablename"=>"liste", "idNfremd"=>array("all", "all", 0)),
					array("tablename"=>"sitzplan_klasse", "idNfremd"=>array("all", 0)))),
			"kollege"=>array(
				"ids"=>array("id"),
                "description"=>"Kollegen",
				"fremd"=>array("schule")),
			"konferenz"=>array(
				"ids"=>array("id"),
                "description"=>"Konferenzen bzw. Elternabende",
				"fremd"=>array("schule", "klasse")),
			// lehrplan: lernbereiche, fach
			"lehrplan"=>array(
				"ids"=>array("id"),
				"fremd"=>array("schulart", "fach"),
                "description"=>"Lehrpl&auml;ne",
				"abhaengig"=>array(
					array("tablename"=>"lernbereich", "idNfremd"=>array("all", 0)),
					array("tablename"=>"fach_klasse", "idNfremd"=>array("all", "all", "all", 0))
					)),
			// lernbereich: block, materialien, grafik, link, aufgaben, test
			"lernbereich"=>array(
				"ids"=>array("id"),
				"fremd"=>array("lehrplan"),
                "description"=>"Lernbereiche",
				"abhaengig"=>array(
					array("tablename"=>"block", "idNfremd"=>array("all", "all", 0)),
					array("tablename"=>"aufgabe", "idNfremd"=>array("all", 0)),
					array("tablename"=>"grafik", "idNfremd"=>array("all", 0)),
					array("tablename"=>"link", "idNfremd"=>array("all", 0)),
					array("tablename"=>"test", "idNfremd"=>array("all", "all", 0))
					)),
			// link: alle verlinkungen, datei loeschen, themenzuordnung
			"link"=>array(
				"ids"=>array("id"),
				"fremd"=>array("lernbereich"),
                "description"=>"Dateien (Dateien selbst werden noch nicht automatisch gel&ouml;scht)",
				"zusatz"=>"delete_file",
				"abhaengig"=>array(
					array("tablename"=>"link_abschnitt", "idNfremd"=>array(0)),
					array("tablename"=>"themenzuordnung", "idNfremd"=>array("all", 0), "bedingung"=>array(4)))),
			"link_abschnitt"=>array(
				"ids"=>array("link", "abschnitt"),
				"bedingt"=>array(
					array("tablename"=>"abschnitt", "idNfremd"=>array(0), "bedingung"=>false, "zusatzbedingung"=>array("lonely"=>"gesamtmaterial")))),
			// liste: liste_schueler
			"liste"=>array(
				"ids"=>array("id"),
				"fremd"=>array("fach_klasse", "klasse"),
                "description"=>"Sch&uuml;lerlisten",
				"abhaengig"=>array(
					array("tablename"=>"liste_schueler", "idNfremd"=>array(0)))),
			"liste_schueler"=>array(
				"ids"=>array("liste", "schueler")),
			// material: alle verlinkungen, themenzuordnung
			"material"=>array(
				"ids"=>array("id"),
                "description"=>"sonstige Materialien",
				"abhaengig"=>array(
					array("tablename"=>"material_abschnitt", "idNfremd"=>array(0)),
					array("tablename"=>"themenzuordnung", "idNfremd"=>array("all", 0), "bedingung"=>array(5)))),
			"material_abschnitt"=>array(
				"ids"=>array("material", "abschnitt"),
				"bedingt"=>array(
					array("tablename"=>"abschnitt", "idNfremd"=>array(0), "bedingung"=>false, "zusatzbedingung"=>array("lonely"=>"gesamtmaterial")))),
			"mitarbeit"=>array(
                "description"=>"Mitarbeitseintragungen",
				"ids"=>array("schueler", "plan")),
			// noten: note_aufgabe
			"noten"=>array(
				"ids"=>array("id"),
				"fremd"=>array("schueler", "beschreibung"),
                "description"=>"Zensuren",
				"abhaengig"=>array(
					array("tablename"=>"note_aufgabe", "idNfremd"=>array(0)))),
			"notenberechnung"=>array(
				"ids"=>array("notentyp", "vorlage"),
				"fremd"=>array("gruppe"),
                "description"=>"Zensurberechnungsvorschriften", // raus?
				"abhaengig"=>array(
					array("tablename"=>"notengruppe", "idNfremd"=>array(2)))),
			// notenspalte (nur, wenn fach_klasse gelöscht wird): noten, berichtigung_vergessen
			"notenberechnungsvorlage"=>array(
				"ids"=>array("id"),
				"fremd"=>array("schule"),
                "description"=>"Zensurberechnungsvorlagen",
				"abhaengig"=>array(
					array("tablename"=>"notenberechnung", "idNfremd"=>array("all",0)))),
			"notenbeschreibung"=>array(
				"ids"=>array("id"),
				"fremd"=>array("notentyp", "fach_klasse", "plan", "bewertungstabelle", "test"),
                "description"=>"Zensurenspalten",
				"abhaengig"=>array(
					array("tablename"=>"noten", "idNfremd"=>array("all", "all", 0))
					)),
			"notengruppe"=>array(
				"ids"=>array("id")),
			// notentyp: notenberechnung, notenspalte, note_aufgabe
			"notentypen"=>array(
				"ids"=>array("id"),
                "description"=>"Zensurtypen",
				"abhaengig"=>array(
					array("tablename"=>"notenberechnung", "idNfremd"=>array(0)),
					array("tablename"=>"notenbeschreibung", "idNfremd"=>array("all", 0)))),
					// schon durch notenbeschreibung-noten: array("tablename"=>"note_aufgabe", "idNfremd"=>array("all", "all", "all", 0)))),
			"note_aufgabe"=>array(
				"ids"=>array("note", "aufgabe"),
				"fremd"=>array("schulart", "notentyp", "schueler", "test", "schulart")),
			"notiz"=>array(
                "description"=>"Notizen",
				"ids"=>array("id")),
			// plan: notenspalten-eintrag, abschnittszuordnungen, hausaufgaben, plan_auswertung, mitarbeit, verwarnungen
			"plan"=>array(
				"ids"=>array("id"),
				"fremd"=>array("schuljahr", "fach_klasse", "block_1", "block_2"),
                "description"=>"Unterrichtsstunden",
				"abhaengig"=>array(
					array("tablename"=>"notenbeschreibung", "idNfremd"=>array("all", "all", "all", 0)),
					array("tablename"=>"abschnittsplanung", "idNfremd"=>array("all", 0)),
					array("tablename"=>"hausaufgabe", "idNfremd"=>array("all", 0)),
					array("tablename"=>"plan_auswertung", "idNfremd"=>array(0)),
					array("tablename"=>"mitarbeit", "idNfremd"=>array("all", 0)),
					array("tablename"=>"verwarnungen", "idNfremd"=>array("all", 0)))),
			"plan_auswertung"=>array(
				"ids"=>array("plan"),
                "description"=>"Unterrichtsstunden-Auswertungen"),
			"plan_dauer"=>array(
				"ids"=>array("datum", "schule", "stunde")),
			// raum: stundenplan
			"raum"=>array(
				"ids"=>array("id"),
				"fremd"=>array("schule"),
				"abhaengig"=>array(
					array("tablename"=>"stundenplan", "idNfremd"=>array("all", "all", 0)),
					)),
			// schueler: schueler_fehlt, sitzplan_eintrag, hausaufgabe_vergessen, berichtigung_vergessen, briefe, elterngespraeche,
			// gruppe, liste_schueler, mitarbeit, verwarnungen, noten, note_aufgabe
			"schueler"=>array(
				"ids"=>array("id"),
				"fremd"=>array("klasse"),
                "description"=>"Sch&uuml;ler",
				"abhaengig"=>array(
					array("tablename"=>"schueler_fehlt", "idNfremd"=>array(0)),
					array("tablename"=>"sitzplan_platz", "idNfremd"=>array("all", "all", 0)),
					array("tablename"=>"hausaufgabe_vergessen", "idNfremd"=>array("all", 0)),
					array("tablename"=>"berichtigung_vergessen", "idNfremd"=>array("all", 0)),
					array("tablename"=>"brief", "idNfremd"=>array("all", 0)),
					array("tablename"=>"gruppe", "idNfremd"=>array("all", 0)),
					array("tablename"=>"liste_schueler", "idNfremd"=>array("all", 0)),
					array("tablename"=>"mitarbeit", "idNfremd"=>array(0)),
					array("tablename"=>"verwarnungen", "idNfremd"=>array(0)),
					array("tablename"=>"noten", "idNfremd"=>array("all", 0)))),
					// schon durch noten: array("tablename"=>"note_aufgabe", "idNfremd"=>array("all", "all", "all", "all", 0)))),
			"schueler_fehlt"=>array(
				"ids"=>array("schueler", "startdatum"),
                "description"=>"Sch&uuml;lerfehlzeiten"),
			// eig. nicht loeschen
			"schulart"=>array(
				"ids"=>array("id")),
			// schule: klassen, stundenzeiten, räume, (ferien), bewegliche_feiertage, aufsicht, kollegen, konferenz, plan_dauer,
			// schule_schulart, (schuljahr), stundenzeiten_beschreibung
			"schule"=>array(
				"ids"=>array("id"),
                "description"=>"Schulen",
				"abhaengig"=>array(
					array("tablename"=>"klasse", "idNfremd"=>array("all", "all", 0)),
					array("tablename"=>"raum", "idNfremd"=>array("all", 0)),
					//array("tablename"=>"ferien"),
					array("tablename"=>"bewegliche_feiertage", "idNfremd"=>array("all", 0)),
					array("tablename"=>"aufsicht", "idNfremd"=>array("all", 0)),
					array("tablename"=>"kollege", "idNfremd"=>array("all", 0)),
					array("tablename"=>"konferenz", "idNfremd"=>array("all", 0)),
					array("tablename"=>"plan_dauer", "idNfremd"=>array("all", 0)),
					array("tablename"=>"schule_schulart", "idNfremd"=>array(0)),
					array("tablename"=>"schuljahr", "idNfremd"=>array("all", 0)),
					array("tablename"=>"stundenzeiten", "idNfremd"=>array("all", 0)),
					array("tablename"=>"stundenzeiten_beschreibung", "idNfremd"=>array("all", 0)))),
			"schule_schulart"=>array(
				"ids"=>array("schule", "schulart")),
			// (schuljahr: ferien, woche_a_b, aufsicht, bewegliche_feiertage)
			"schuljahr"=>array(
				"ids"=>array("id"),
				"fremd"=>array("schule"),
				"abhaengig"=>array(
					array("tablename"=>"aufsicht", "idNfremd"=>array("all", "all", 0)),
					array("tablename"=>"ferien", "idNfremd"=>array("all", 0)),
					array("tablename"=>"woche_ab", "idNfremd"=>array("all", 0)),
					array("tablename"=>"bewegliche_feiertage", "idNfremd"=>array("all", "all", 0)))),
			// sitzplan: sitzplan_klasse, fach_klassen
			"sitzplan"=>array(
				"ids"=>array("id"),
                "description"=>"Sitzordnungen f&uuml;r R&auml;me",
				"abhaengig"=>array(
					array("tablename"=>"sitzplan_klasse", "idNfremd"=>array("all", "all", 0)),
					array("tablename"=>"sitzplan_objekt", "idNfremd"=>array("all", 0)),
					array("tablename"=>"fach_klasse", "idNfremd"=>array("all", "all", "all", "all", "all", "all", 0)))),
			"sitzplan_klasse"=>array(
				"ids"=>array("id"),
				"fremd"=>array("klasse", "sitzplan"),
                "description"=>"Sitzpl&auml;ne von Klassen",
				"abhaengig"=>array(
					array("tablename"=>"sitzplan_platz", "idNfremd"=>array(0)))),
			// sitzordnung: alle sitzplaene, alle sitzplan_objekte
			"sitzplan_objekt"=>array(
				"ids"=>array("id"),
				"fremd"=>array("sitzplan"),
				"abhaengig"=>array(
					array("tablename"=>"sitzplan_platz", "idNfremd"=>array("all", 0)),
					)),
			"sitzplan_platz"=>array(
				"ids"=>array("sitzplan_klasse", "objekt"),
				"fremd"=>array("schueler")),
			"sonstiges"=>array(
				"ids"=>array("id"),
				"fremd"=>array("abschnitt"),
                "description"=>"Texte",
				"bedingt"=>array(
					array("tablename"=>"abschnitt", "idNfremd"=>array(0), "bedingung"=>false, "zusatzbedingung"=>array("lonely"=>"gesamtmaterial")))),
			// stundenplan: stundenzeit, 
			"stundenplan"=>array(
				"ids"=>array("id"),
                "description"=>"Stundenplandaten",
				"fremd"=>array("fach_klasse", "raum", "stundenzeiten")),
			"stundenzeiten"=>array(
				"ids"=>array("id"),
				"fremd"=>array("schule")),
			"stundenzeiten_beschreibung"=>array(
				"ids"=>array("id"),
				"fremd"=>array("schule", "schulart")),
			// test: alle verlinkungen, alle test_aufgaben-verlinkungen, notenspalte, note_aufgabe, notentyp, themenzuordnung
			"test"=>array(
				"ids"=>array("id"),
				"fremd"=>array("klasse", "lernbereich", "notentyp"),
                "description"=>"Tests (eine evtl. zugeordnete Datei wird noch nicht automatisch gel&ouml;scht)",
				"abhaengig"=>array(
					array("tablename"=>"test_abschnitt", "idNfremd"=>array(0)),
					array("tablename"=>"test_aufgabe", "idNfremd"=>array(0)),
					array("tablename"=>"notenbeschreibung", "idNfremd"=>array("all", "all", "all", "all", "all", 0)),
					array("tablename"=>"note_aufgabe", "idNfremd"=>array("all", "all", "all", "all", "all", 0)),
					array("tablename"=>"themenzuordnung", "idNfremd"=>array("all", 0), "bedingung"=>array(6)))),
			"test_abschnitt"=>array(
				"ids"=>array("test", "abschnitt"),
				"bedingt"=>array(
					array("tablename"=>"abschnitt", "idNfremd"=>array(1), "bedingung"=>false, "zusatzbedingung"=>array("lonely"=>"gesamtmaterial")))),
			"test_aufgabe"=>array(
				"ids"=>array("test", "aufgabe")),
			// themen: block, aufgabe, test, material, AB, Grafik..., unterthemen
			"thema"=>array(
				"ids"=>array("id"),
				"fremd"=>array("fach", "oberthema"),
                "description"=>"Themen",
				"abhaengig"=>array(
					array("tablename"=>"thema", "idNfremd"=>array("all", "all", 0)),
					array("tablename"=>"themenzuordnung", "idNfremd"=>array("all", "all", 0)))),
			"themenzuordnung"=>array(
				"ids"=>array("typ", "id", "thema"),
				"abhaengig"=>array( // Bedingung wird nicht benoetigt
					array("tablename"=>"aufgabe", "idNfremd"=>array(1), "bedingung"=>array(1)), // Bedingung typ=1
					array("tablename"=>"block", "idNfremd"=>array(1), "bedingung"=>array(2)),
					array("tablename"=>"grafik", "idNfremd"=>array(1), "bedingung"=>array(3)),
					array("tablename"=>"link", "idNfremd"=>array(1), "bedingung"=>array(4)),
					array("tablename"=>"material", "idNfremd"=>array(1), "bedingung"=>array(5)),
					array("tablename"=>"test", "idNfremd"=>array(1), "bedingung"=>array(6)))),
			"ueberschrift"=>array(
				"ids"=>array("id"),
				"fremd"=>array("abschnitt"),
                "description"=>"&Uuml;berschriften",
				"bedingt"=>array(
					array("tablename"=>"abschnitt", "idNfremd"=>array(1), "bedingung"=>false, "zusatzbedingung"=>array("lonely"=>"gesamtmaterial")))),
			"verwarnungen"=>array(
                "description"=>"Sch&uuml;lerbetragen-Eintragungen",
				"ids"=>array("schueler", "plan")),
			"vorlagen"=>array(
                "description"=>"Briefvorlagen",
				"ids"=>array("id")),
			"woche_ab"=>array(
				"ids"=>array("schuljahr_id", "datum"))
		);

?>
