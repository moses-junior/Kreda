-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 08. Okt 2014 um 18:09
-- Server Version: 5.5.38-0ubuntu0.14.04.1
-- PHP-Version: 5.5.9-1ubuntu4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: 'kreda'
--
CREATE DATABASE IF NOT EXISTS kreda DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE kreda;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'abschnitt'
--

CREATE TABLE IF NOT EXISTS abschnitt (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  hefter tinyint(1) unsigned NOT NULL DEFAULT '0',
  `medium` tinyint(4) unsigned DEFAULT NULL,
  ziel varchar(255) DEFAULT NULL COMMENT 'abschnittsziel',
  minuten smallint(6) unsigned DEFAULT NULL,
  nachbereitung text COMMENT 'gut gelaufen...',
  sozialform tinyint(4) unsigned DEFAULT NULL,
  handlungsmuster tinyint(4) unsigned DEFAULT NULL,
  inhaltspositionen varchar(255) DEFAULT NULL,
  aktiv tinyint(1) unsigned NOT NULL DEFAULT '1',
  methode tinyint(4) unsigned DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='wohl am umfangreichsten. Aenderungen ausschließen-neu.' AUTO_INCREMENT=4228 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'abschnittsplanung'
--

CREATE TABLE IF NOT EXISTS abschnittsplanung (
  abschnitt int(11) unsigned NOT NULL,
  plan int(11) unsigned NOT NULL,
  minuten tinyint(5) unsigned DEFAULT NULL,
  position tinyint(3) unsigned NOT NULL,
  `phase` tinyint(4) unsigned DEFAULT NULL,
  bemerkung varchar(255) DEFAULT NULL COMMENT 'Bemerkung, die nur in diesem Plan auftritt',
  sekunden_tatsaechlich tinyint(4) unsigned DEFAULT NULL,
  parallel tinyint(1) unsigned DEFAULT NULL,
  inhalt text,
  PRIMARY KEY (abschnitt,plan,position)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'aufgabe'
--

CREATE TABLE IF NOT EXISTS aufgabe (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  `text` text COMMENT 'Alternativenangabe moeglich',
  bemerkung text,
  kariert tinyint(2) unsigned NOT NULL DEFAULT '0',
  cm decimal(5,2) DEFAULT NULL,
  punkte decimal(5,2) DEFAULT NULL,
  loesung text COMMENT 'punkte kennzeichnen',
  lernbereich int(11) unsigned NOT NULL,
  bearbeitungszeit tinyint(4) unsigned DEFAULT NULL,
  bildanordnung tinyint(3) unsigned DEFAULT NULL,
  bildbeschriftung tinyint(3) unsigned DEFAULT NULL,
  schwierigkeitsgrad tinyint(4) unsigned DEFAULT NULL,
  teilaufgaben_nebeneinander tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'bei Aufzählungen',
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2953 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'aufgabe_abschnitt'
--

CREATE TABLE IF NOT EXISTS aufgabe_abschnitt (
  aufgabe int(11) NOT NULL,
  abschnitt int(11) NOT NULL,
  beispiel tinyint(1) NOT NULL,
  PRIMARY KEY (aufgabe,abschnitt)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'aufsicht'
--

CREATE TABLE IF NOT EXISTS aufsicht (
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  schule tinyint(4) unsigned NOT NULL,
  schuljahr year(4) NOT NULL,
  wochentag tinyint(4) unsigned NOT NULL,
  woche tinyint(1) unsigned DEFAULT NULL,
  nach_stunde tinyint(4) unsigned NOT NULL,
  bemerkung varchar(255) DEFAULT NULL,
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id),
  KEY schule (schule,schuljahr,wochentag,woche,nach_stunde)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=38 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'benutzer'
--

CREATE TABLE IF NOT EXISTS benutzer (
  id tinyint(4) NOT NULL AUTO_INCREMENT,
  aktuelles_schuljahr year(4) NOT NULL,
  druckansicht text NOT NULL,
  ansicht_2 text,
  merkhefter tinyint(1) unsigned NOT NULL DEFAULT '1',
  letzter_lernbereich mediumint(9) unsigned DEFAULT NULL,
  letzte_themen_auswahl varchar(50) DEFAULT NULL,
  letzte_schule smallint(6) unsigned DEFAULT NULL,
  letzte_fachklasse int(10) unsigned DEFAULT NULL,
  lb_faktor decimal(5,2) DEFAULT '1.30',
  username varchar(50) NOT NULL DEFAULT 'dontuse',
  zensurenpunkte tinyint(1) NOT NULL DEFAULT '0',
  zensurenkommentare tinyint(1) NOT NULL DEFAULT '0',
  zensuren_unt_ber tinyint(1) NOT NULL DEFAULT '0',
  zensuren_nicht_zaehlen tinyint(1) NOT NULL DEFAULT '0',
  dienstberatungen tinyint(1) NOT NULL DEFAULT '0',
  schuljahresplanung tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Stundenplan, Stoffverteilung',
  statistiken tinyint(1) NOT NULL DEFAULT '0' COMMENT 'HA, Unterricht',
  ustd_planung tinyint(1) NOT NULL DEFAULT '0',
  sitzplan tinyint(1) NOT NULL DEFAULT '0',
  admin tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=71 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'berichtigung_vergessen'
--

CREATE TABLE IF NOT EXISTS berichtigung_vergessen (
  notenbeschreibung int(11) unsigned NOT NULL,
  schueler int(11) unsigned NOT NULL,
  berichtigung_anzahl tinyint(2) NOT NULL DEFAULT '0',
  unterschrift_anzahl tinyint(2) NOT NULL DEFAULT '0',
  berichtigung_erledigt tinyint(1) NOT NULL DEFAULT '0',
  unterschrift_erledigt tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (notenbeschreibung,schueler)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'bewegliche_feiertage'
--

CREATE TABLE IF NOT EXISTS bewegliche_feiertage (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  beschreibung varchar(50) NOT NULL,
  von date NOT NULL,
  bis date DEFAULT NULL,
  schuljahr year(4) NOT NULL,
  schule tinyint(4) unsigned NOT NULL,
  fehltage tinyint(1) DEFAULT NULL COMMENT 'NULL=nicht mitzaehlen (Standard); 1=mitzaehlen',
  PRIMARY KEY (id),
  KEY id (id,schuljahr)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=39 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'bewertungstabelle'
--

CREATE TABLE IF NOT EXISTS bewertungstabelle (
  id tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL COMMENT 'schulart, bezeichnung...',
  punkte tinyint(1) NOT NULL COMMENT 'oder noten?',
  aktiv tinyint(1) DEFAULT '1',
  `user` int(11) unsigned DEFAULT '1',
  schule tinyint(4) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'bewertung_note'
--

CREATE TABLE IF NOT EXISTS bewertung_note (
  bewertungstabelle tinyint(3) unsigned NOT NULL,
  note tinyint(3) unsigned NOT NULL COMMENT 'wert',
  prozent_bis decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (bewertungstabelle,note)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'block'
--

CREATE TABLE IF NOT EXISTS `block` (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  block_hoeher int(11) unsigned DEFAULT NULL,
  stunden tinyint(2) unsigned NOT NULL COMMENT 'Einzelstunde, doppelstunde, mehr...',
  `name` varchar(255) NOT NULL COMMENT 'Kurzbeschreibung',
  methodisch text COMMENT 'methodisch-didaktische Ueberlegungen',
  verknuepfung_fach varchar(255) DEFAULT NULL COMMENT 'verknuepfung mit Fach und LB',
  kommentare text,
  position tinyint(3) unsigned NOT NULL,
  puffer tinyint(4) unsigned DEFAULT NULL,
  lernbereich int(11) unsigned NOT NULL,
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=509 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'block_abschnitt'
--

CREATE TABLE IF NOT EXISTS block_abschnitt (
  `block` int(11) unsigned NOT NULL,
  abschnitt int(11) unsigned NOT NULL,
  position tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`block`,abschnitt)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'brief'
--

CREATE TABLE IF NOT EXISTS brief (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  schueler int(11) unsigned DEFAULT NULL,
  datum date NOT NULL,
  anrede varchar(100) DEFAULT NULL,
  klasse mediumint(9) unsigned NOT NULL,
  inhalt text NOT NULL,
  `user` int(11) DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'buch'
--

CREATE TABLE IF NOT EXISTS buch (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  kuerzel varchar(10) NOT NULL,
  fach tinyint(4) NOT NULL,
  isbn varchar(16) DEFAULT NULL,
  schulart tinyint(3) unsigned DEFAULT NULL,
  untertitel varchar(255) DEFAULT NULL,
  verlag varchar(255) DEFAULT NULL,
  aktiv tinyint(1) NOT NULL DEFAULT '1',
  letztes_thema int(11) unsigned DEFAULT NULL,
  letzter_lernbereich int(11) unsigned DEFAULT NULL,
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'buch_aufgabe'
--

CREATE TABLE IF NOT EXISTS buch_aufgabe (
  buch smallint(6) unsigned NOT NULL,
  aufgabe int(11) unsigned NOT NULL,
  seite smallint(6) unsigned NOT NULL COMMENT 'zahl',
  nummer varchar(100) DEFAULT NULL,
  PRIMARY KEY (buch,aufgabe)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'buch_klassenstufe'
--

CREATE TABLE IF NOT EXISTS buch_klassenstufe (
  buch smallint(6) unsigned NOT NULL,
  klassenstufe tinyint(4) unsigned NOT NULL DEFAULT '0',
  verwenden tinyint(1) DEFAULT '1',
  PRIMARY KEY (buch,klassenstufe)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'elternkontakt'
--

CREATE TABLE IF NOT EXISTS elternkontakt (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  datum date NOT NULL,
  art tinyint(2) unsigned NOT NULL DEFAULT '0',
  schueler int(11) unsigned NOT NULL,
  inhalt text NOT NULL,
  `user` int(11) DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=58 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'eltern_schueler'
--

CREATE TABLE IF NOT EXISTS eltern_schueler (
  `user` int(11) NOT NULL,
  schueler int(11) NOT NULL,
  sorgeberechtigt tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(30) DEFAULT NULL,
  postempfaenger tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user`,schueler)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'fach_klasse'
--

CREATE TABLE IF NOT EXISTS fach_klasse (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  klasse int(11) unsigned NOT NULL,
  fach smallint(6) unsigned NOT NULL,
  anzeigen tinyint(1) DEFAULT '1',
  farbe varchar(6) DEFAULT NULL COMMENT 'html-Farbe fuer Stundenplan',
  gruppen_name varchar(15) DEFAULT NULL COMMENT 'Gruppenname',
  lehrplan smallint(6) unsigned DEFAULT NULL,
  info text,
  bewertungstabelle smallint(6) unsigned DEFAULT NULL COMMENT 'Standard-Auswahl',
  sitzplan_klasse smallint(6) unsigned DEFAULT NULL,
  letzte_themen_auswahl varchar(50) DEFAULT NULL,
  letzter_lernbereich mediumint(9) unsigned DEFAULT NULL,
  `user` int(11) unsigned DEFAULT '1',
  notenberechnungsvorlage smallint(6) DEFAULT NULL,
  klassenanzeige tinyint(1) NOT NULL DEFAULT '1',
  notenhash varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=605 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'faecher'
--

CREATE TABLE IF NOT EXISTS faecher (
  id tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'zB Mathematik',
  kuerzel varchar(7) NOT NULL COMMENT 'zB Ma',
  anzeigen tinyint(1) NOT NULL DEFAULT '1',
  `user` int(11) unsigned DEFAULT '0',
  schule smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=37 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'feiertage_schule'
--

CREATE TABLE IF NOT EXISTS feiertage_schule (
  ff smallint(6) unsigned NOT NULL,
  schule tinyint(4) unsigned NOT NULL,
  aktiv tinyint(1) DEFAULT '1',
  PRIMARY KEY (ff,schule)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'ferien'
--

CREATE TABLE IF NOT EXISTS ferien (
  welche tinyint(4) unsigned NOT NULL,
  beginn date NOT NULL,
  ende date NOT NULL,
  schuljahr year(4) NOT NULL,
  bundesland smallint(6) unsigned NOT NULL DEFAULT '12',
  PRIMARY KEY (welche,schuljahr,bundesland)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'feste_feiertage'
--

CREATE TABLE IF NOT EXISTS feste_feiertage (
  id tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  anzeigen tinyint(1) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'grafik'
--

CREATE TABLE IF NOT EXISTS grafik (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  url varchar(255) DEFAULT NULL,
  alt varchar(255) NOT NULL COMMENT 'alternativtext',
  lernbereich int(11) unsigned NOT NULL,
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=691 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'grafik_abschnitt'
--

CREATE TABLE IF NOT EXISTS grafik_abschnitt (
  grafik smallint(6) unsigned NOT NULL,
  abschnitt int(11) unsigned NOT NULL,
  position tinyint(4) unsigned DEFAULT NULL COMMENT 'zB links,mitte,rechts,oben,unten...',
  groesse decimal(5,1) unsigned DEFAULT NULL,
  PRIMARY KEY (grafik,abschnitt)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'grafik_aufgabe'
--

CREATE TABLE IF NOT EXISTS grafik_aufgabe (
  grafik smallint(6) unsigned NOT NULL,
  aufgabe int(11) unsigned NOT NULL,
  groesse decimal(5,2) unsigned NOT NULL,
  PRIMARY KEY (grafik,aufgabe)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'gruppe'
--

CREATE TABLE IF NOT EXISTS gruppe (
  fach_klasse int(11) unsigned NOT NULL,
  schueler int(11) unsigned NOT NULL,
  position tinyint(4) DEFAULT NULL,
  PRIMARY KEY (fach_klasse,schueler)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'handlungsmuster'
--

CREATE TABLE IF NOT EXISTS handlungsmuster (
  id tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  kuerzel varchar(100) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'hausaufgabe'
--

CREATE TABLE IF NOT EXISTS hausaufgabe (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  ziel varchar(255) DEFAULT NULL,
  bemerkung varchar(255) DEFAULT NULL,
  abgabedatum date DEFAULT NULL,
  kontrolliert tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=noch nicht; -1=nicht vollständig; 1=fertig',
  plan int(11) unsigned NOT NULL,
  mitzaehlen tinyint(1) NOT NULL DEFAULT '1' COMMENT '0=nein; 1=ja',
  PRIMARY KEY (id),
  KEY abgabedatum (abgabedatum,plan)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=233 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'hausaufgabe_abschnitt'
--

CREATE TABLE IF NOT EXISTS hausaufgabe_abschnitt (
  hausaufgabe int(11) unsigned NOT NULL,
  abschnitt int(11) unsigned NOT NULL,
  PRIMARY KEY (hausaufgabe,abschnitt)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'hausaufgabe_vergessen'
--

CREATE TABLE IF NOT EXISTS hausaufgabe_vergessen (
  hausaufgabe int(11) unsigned NOT NULL,
  schueler int(11) unsigned NOT NULL,
  anzahl tinyint(2) NOT NULL DEFAULT '1',
  bemerkung varchar(255) DEFAULT NULL,
  erledigt tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (hausaufgabe,schueler)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'klasse'
--

CREATE TABLE IF NOT EXISTS klasse (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  einschuljahr smallint(6) unsigned NOT NULL COMMENT '1. Klasse im Jahr ____',
  endung varchar(8) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  schule smallint(5) unsigned NOT NULL,
  schulart tinyint(4) unsigned NOT NULL,
  fehlzeiten_erledigt_bis date DEFAULT NULL,
  klassenlehrer smallint(6) DEFAULT NULL,
  klassenlehrer2 smallint(6) DEFAULT NULL COMMENT 'Stellvertreter',
  kl_sitzplan smallint(6) DEFAULT NULL COMMENT 'vom Klassenlehrer vorgeschlagener Sitzplan',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=68 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'kollege'
--

CREATE TABLE IF NOT EXISTS kollege (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(70) NOT NULL,
  vorname varchar(50) DEFAULT NULL,
  geburtstag date DEFAULT NULL,
  telefon varchar(255) DEFAULT NULL,
  email varchar(255) DEFAULT NULL,
  adresse varchar(255) DEFAULT NULL,
  ort varchar(255) DEFAULT NULL,
  schule tinyint(4) unsigned DEFAULT NULL,
  kommentar text,
  aktiv tinyint(1) unsigned NOT NULL DEFAULT '1',
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'konferenz'
--

CREATE TABLE IF NOT EXISTS konferenz (
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  datum date NOT NULL,
  zeit time NOT NULL,
  schule tinyint(4) unsigned NOT NULL,
  ort varchar(255) DEFAULT NULL,
  titel varchar(255) NOT NULL,
  inhalt text,
  klasse mediumint(8) unsigned DEFAULT NULL,
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=80 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'kopfnote'
--

CREATE TABLE IF NOT EXISTS kopfnote (
  schueler int(11) NOT NULL,
  rahmen smallint(6) NOT NULL,
  fach_klasse int(11) NOT NULL,
  kategorie smallint(6) NOT NULL,
  wert tinyint(2) NOT NULL,
  tendenz tinyint(2) DEFAULT NULL,
  kommentar varchar(255) DEFAULT NULL,
  PRIMARY KEY (schueler,rahmen,fach_klasse,kategorie)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'kopfnotenkat_rahmen'
--

CREATE TABLE IF NOT EXISTS kopfnotenkat_rahmen (
  kn_kat smallint(6) NOT NULL,
  rahmen smallint(6) NOT NULL,
  position tinyint(2) NOT NULL,
  PRIMARY KEY (kn_kat,rahmen)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'kopfnoten_kategorie'
--

CREATE TABLE IF NOT EXISTS kopfnoten_kategorie (
  id smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  beschreibung text,
  `user` int(11) DEFAULT NULL,
  schule smallint(6) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'kopfnote_festlegung'
--

CREATE TABLE IF NOT EXISTS kopfnote_festlegung (
  schueler int(11) NOT NULL,
  rahmen smallint(6) NOT NULL,
  kategorie smallint(6) NOT NULL,
  `user` int(11) NOT NULL,
  wert tinyint(2) NOT NULL,
  tendenz tinyint(2) DEFAULT NULL,
  kommentar varchar(255) DEFAULT NULL,
  PRIMARY KEY (schueler,rahmen,kategorie)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'kopfnote_fk'
--

CREATE TABLE IF NOT EXISTS kopfnote_fk (
  rahmen smallint(6) NOT NULL,
  fach_klasse int(11) NOT NULL,
  `user` int(11) NOT NULL,
  fertig date DEFAULT NULL,
  PRIMARY KEY (rahmen,fach_klasse)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'kopfnote_rahmen'
--

CREATE TABLE IF NOT EXISTS kopfnote_rahmen (
  id smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  bearbeitung_ab date NOT NULL,
  bearbeitung_bis date NOT NULL,
  art tinyint(4) NOT NULL,
  beschreibung text,
  schule smallint(6) NOT NULL,
  faktor tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'lehrauftrag'
--

CREATE TABLE IF NOT EXISTS lehrauftrag (
  `user` int(11) unsigned NOT NULL,
  schuljahr year(4) NOT NULL,
  klasse int(11) unsigned NOT NULL,
  fach smallint(6) unsigned NOT NULL,
  lfd_nr tinyint(1) unsigned NOT NULL DEFAULT '0',
  gemeinsame_noten tinyint(3) unsigned DEFAULT NULL,
  fach_klasse int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`user`,schuljahr,klasse,fach,lfd_nr)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'lehrplan'
--

CREATE TABLE IF NOT EXISTS lehrplan (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  bundesland tinyint(3) unsigned NOT NULL DEFAULT '12',
  schulart tinyint(3) unsigned NOT NULL COMMENT 'zB 1=Gymnasium',
  jahr year(4) NOT NULL,
  fach tinyint(2) unsigned NOT NULL,
  aktiv tinyint(1) NOT NULL DEFAULT '1',
  von tinyint(3) unsigned DEFAULT '1',
  bis tinyint(3) unsigned DEFAULT '13',
  bemerkung varchar(255) DEFAULT NULL,
  zusatz varchar(30) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'lernbereich'
--

CREATE TABLE IF NOT EXISTS lernbereich (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  nummer tinyint(4) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  klassenstufe tinyint(2) unsigned NOT NULL,
  ustd tinyint(2) unsigned NOT NULL,
  beschreibung text,
  lehrplan smallint(6) unsigned NOT NULL,
  wahl tinyint(1) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=204 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'link'
--

CREATE TABLE IF NOT EXISTS link (
  id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  url varchar(255) NOT NULL,
  lokal tinyint(1) DEFAULT NULL,
  beschreibung varchar(255) DEFAULT NULL,
  typ tinyint(3) unsigned NOT NULL DEFAULT '3',
  lernbereich mediumint(9) unsigned NOT NULL,
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=642 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'link_abschnitt'
--

CREATE TABLE IF NOT EXISTS link_abschnitt (
  link mediumint(9) unsigned NOT NULL,
  abschnitt int(11) unsigned NOT NULL,
  bemerkung varchar(255) DEFAULT NULL COMMENT 'zB Aufgaben links 1-3',
  PRIMARY KEY (link,abschnitt)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'liste'
--

CREATE TABLE IF NOT EXISTS liste (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  fach_klasse int(11) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  typ varchar(255) DEFAULT NULL,
  abgeschlossen date DEFAULT NULL,
  erstelldatum date NOT NULL,
  faellig date DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'liste_schueler'
--

CREATE TABLE IF NOT EXISTS liste_schueler (
  liste int(11) unsigned NOT NULL,
  schueler int(11) unsigned NOT NULL,
  inhalt text,
  fertig tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (liste,schueler)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'lp_user'
--

CREATE TABLE IF NOT EXISTS lp_user (
  lehrplan int(11) unsigned NOT NULL,
  `user` int(11) unsigned NOT NULL,
  aktiv tinyint(1) DEFAULT '1',
  PRIMARY KEY (lehrplan,`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'material'
--

CREATE TABLE IF NOT EXISTS material (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  beschreibung varchar(255) DEFAULT NULL,
  aufbewahrungsort varchar(255) DEFAULT NULL,
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=38 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'material_abschnitt'
--

CREATE TABLE IF NOT EXISTS material_abschnitt (
  material smallint(6) unsigned NOT NULL,
  abschnitt int(11) unsigned NOT NULL,
  PRIMARY KEY (material,abschnitt)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'medium'
--

CREATE TABLE IF NOT EXISTS `medium` (
  id tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  kuerzel varchar(255) NOT NULL,
  detail varchar(255) DEFAULT NULL COMMENT 'zB Tafel aufgeklappt mitte rechts',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'mitarbeit'
--

CREATE TABLE IF NOT EXISTS mitarbeit (
  schueler int(11) unsigned NOT NULL,
  plan int(11) unsigned NOT NULL,
  anzahl tinyint(4) NOT NULL,
  PRIMARY KEY (schueler,plan)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'noten'
--

CREATE TABLE IF NOT EXISTS noten (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  wert tinyint(4) DEFAULT NULL,
  punkte decimal(5,2) DEFAULT NULL,
  datum date NOT NULL,
  schueler int(11) unsigned NOT NULL,
  beschreibung int(11) unsigned NOT NULL,
  kommentar varchar(255) DEFAULT NULL,
  halbjahresnote tinyint(1) NOT NULL,
  zusatz tinyint(2) NOT NULL COMMENT '-1, 0, 1 heisst + oder -',
  zusatzpunkte decimal(5,2) NOT NULL DEFAULT '0.00',
  berichtigung tinyint(1) DEFAULT NULL,
  unterschrift tinyint(1) DEFAULT NULL,
  anzahl_vergessen tinyint(2) NOT NULL DEFAULT '0',
  mitzaehlen tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1, 0',
  gruppe_b tinyint(1) NOT NULL DEFAULT '0',
  geamtpunktzahl tinyint(4) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10189 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'notenberechnung'
--

CREATE TABLE IF NOT EXISTS notenberechnung (
  faktor decimal(5,2) NOT NULL DEFAULT '1.00',
  notentyp tinyint(4) unsigned NOT NULL,
  vorlage smallint(6) unsigned NOT NULL,
  gruppe smallint(6) unsigned DEFAULT NULL,
  PRIMARY KEY (notentyp,vorlage)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'notenberechnungsvorlage'
--

CREATE TABLE IF NOT EXISTS notenberechnungsvorlage (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  beschreibung varchar(255) DEFAULT NULL,
  schule tinyint(4) DEFAULT NULL,
  aktiv tinyint(1) DEFAULT '1',
  `user` int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'notenbeschreibung'
--

CREATE TABLE IF NOT EXISTS notenbeschreibung (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  beschreibung varchar(255) DEFAULT NULL,
  gesamtpunktzahl smallint(6) unsigned DEFAULT NULL,
  notentyp tinyint(4) unsigned NOT NULL,
  halbjahresnote tinyint(1) NOT NULL,
  fach_klasse int(11) unsigned NOT NULL,
  datum date DEFAULT NULL,
  kommentar varchar(255) DEFAULT NULL,
  bewertungstabelle smallint(6) unsigned NOT NULL,
  plan int(11) unsigned DEFAULT NULL,
  korrigiert date DEFAULT NULL,
  zurueckgegeben date DEFAULT NULL COMMENT 'wann die Arbeit ausgeteilt wurde',
  berichtigung tinyint(1) DEFAULT NULL COMMENT 'NULL=nicht erforderlich, 0=unvollstaendig; 1=fertig',
  unterschrift tinyint(1) DEFAULT NULL COMMENT 'NULL=nicht erforderlich, 0=unvollstaendig; 1=fertig',
  mitzaehlen tinyint(1) NOT NULL DEFAULT '1' COMMENT '1, -1 (teilweise), 0',
  test int(10) unsigned DEFAULT NULL,
  durchschnitt decimal(5,2) NOT NULL DEFAULT '0.00',
  notenspiegel text,
  notenspiegel_zeigen tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (id),
  KEY fach_klasse (fach_klasse,datum,plan)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=880 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'notengruppe'
--

CREATE TABLE IF NOT EXISTS notengruppe (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  faktor decimal(5,2) NOT NULL DEFAULT '1.00',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='z.B. um alle muendlichen und mitarbeitszensuren zusammenfass' AUTO_INCREMENT=57 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'notenstand'
--

CREATE TABLE IF NOT EXISTS notenstand (
  schueler int(11) unsigned NOT NULL,
  fach smallint(6) unsigned NOT NULL,
  schuljahr year(4) NOT NULL,
  datum date NOT NULL,
  wert decimal(5,2) NOT NULL COMMENT 'derzeitiger Durchschnitt',
  berechnung text NOT NULL,
  einzelnoten text NOT NULL COMMENT '[[nb_id,w,p,d,h,z]...]',
  PRIMARY KEY (schueler,fach,schuljahr,datum)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Schülerexport sonst zu rechenintensiv';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'notentypen'
--

CREATE TABLE IF NOT EXISTS notentypen (
  id tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  kuerzel varchar(10) NOT NULL,
  aktiv tinyint(1) DEFAULT '1',
  schule smallint(6) unsigned DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'note_aufgabe'
--

CREATE TABLE IF NOT EXISTS note_aufgabe (
  note int(11) unsigned NOT NULL,
  aufgabe int(11) unsigned NOT NULL,
  punkte decimal(5,2) NOT NULL,
  test int(11) unsigned NOT NULL,
  klassenstufe tinyint(4) unsigned NOT NULL,
  schulart tinyint(4) unsigned NOT NULL,
  notentyp tinyint(4) unsigned NOT NULL,
  schueler int(11) unsigned NOT NULL,
  aufgabenpunkte decimal(5,2) NOT NULL,
  PRIMARY KEY (note,aufgabe)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'notiz'
--

CREATE TABLE IF NOT EXISTS notiz (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  datum date DEFAULT NULL,
  inhalt text NOT NULL,
  fertig tinyint(1) unsigned NOT NULL DEFAULT '0',
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id),
  KEY datum (datum)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=88 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'phase'
--

CREATE TABLE IF NOT EXISTS `phase` (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  kuerzel varchar(30) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'plan'
--

CREATE TABLE IF NOT EXISTS plan (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  datum date NOT NULL COMMENT 'geht aus stundenplan hervor',
  startzeit time NOT NULL,
  schuljahr smallint(4) unsigned NOT NULL,
  fach_klasse smallint(6) unsigned NOT NULL COMMENT 'fach_klasse_id',
  nachbereitung tinyint(1) NOT NULL DEFAULT '0' COMMENT 'abgeschlossen?',
  zusatzziele text,
  gedruckt tinyint(1) NOT NULL COMMENT 'bool',
  material_da tinyint(1) NOT NULL COMMENT 'bool',
  block_1 int(11) unsigned DEFAULT NULL,
  block_2 int(11) unsigned DEFAULT NULL COMMENT 'falls noetig',
  ausfallgrund varchar(200) DEFAULT NULL COMMENT 'falls angegeben, ist die stunde ausgefallen - keine planung durchfuehren',
  notizen text COMMENT 'bemerkungen fuer die stunde, auch von vorher',
  vorbereitet tinyint(1) NOT NULL DEFAULT '0' COMMENT 'fertig vorbereitet?',
  bemerkung text COMMENT 'fuer Gesamtplanung?',
  ustd tinyint(3) unsigned NOT NULL DEFAULT '1',
  alternativtitel varchar(100) DEFAULT NULL COMMENT 'anstelle der Blockbezeichnungen',
  struktur text,
  ueberschriftsbeginn int(10) unsigned DEFAULT NULL,
  ohne_pause tinyint(1) DEFAULT '0',
  PRIMARY KEY (id),
  KEY datum (datum,schuljahr)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2345 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'plan_auswertung'
--

CREATE TABLE IF NOT EXISTS plan_auswertung (
  plan int(11) unsigned NOT NULL,
  gesamteindruck tinyint(4) DEFAULT NULL,
  selbsteinschaetzung tinyint(4) DEFAULT NULL COMMENT 'zufriedenheit',
  lerneinschaetzung tinyint(4) DEFAULT NULL COMMENT 'viel oder wenig gelernt',
  angstfaktor tinyint(4) DEFAULT NULL COMMENT 'wohl gefuehlt oder nicht',
  lehrersprache tinyint(4) DEFAULT NULL COMMENT 'gut erklaert',
  methode tinyint(4) DEFAULT NULL COMMENT 'gute methode',
  stoffbewaeltigung tinyint(4) DEFAULT NULL COMMENT 'verstanden oder schwierigkeiten',
  lob_geben tinyint(4) DEFAULT NULL,
  interesse tinyint(4) DEFAULT NULL COMMENT 'Interessant oder langweilig, Motivation',
  lerntempo tinyint(4) DEFAULT NULL COMMENT 'richtig oder falsch',
  PRIMARY KEY (plan)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='2 trifft voll zu; 1 trifft eher zu; -1 trifft eher nicht zu;';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'plan_dauer'
--

CREATE TABLE IF NOT EXISTS plan_dauer (
  datum date NOT NULL,
  schule tinyint(4) unsigned NOT NULL,
  stunde tinyint(4) unsigned DEFAULT NULL,
  minuten smallint(6) unsigned NOT NULL,
  KEY datum (datum,schule,stunde)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'raum'
--

CREATE TABLE IF NOT EXISTS raum (
  id tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL,
  schule tinyint(4) unsigned NOT NULL,
  kommentar varchar(255) DEFAULT NULL,
  aktiv tinyint(1) DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=84 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'rollen'
--

CREATE TABLE IF NOT EXISTS rollen (
  id tinyint(3) NOT NULL AUTO_INCREMENT,
  bezeichnung varchar(25) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'schueler'
--

CREATE TABLE IF NOT EXISTS schueler (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  vorname varchar(35) NOT NULL,
  rufname varchar(255) DEFAULT NULL COMMENT 'z.B. Anne-Sophie -> Sophie',
  geburtstag date DEFAULT NULL,
  strasse varchar(70) DEFAULT NULL,
  plz varchar(10) NOT NULL,
  ort varchar(70) DEFAULT NULL,
  klasse int(11) unsigned NOT NULL COMMENT 'klasse-id',
  email varchar(100) DEFAULT NULL,
  telefon varchar(100) DEFAULT NULL,
  bemerkungen text CHARACTER SET latin1 COLLATE latin1_german1_ci,
  maennlich tinyint(1) DEFAULT NULL,
  aktiv tinyint(1) NOT NULL DEFAULT '1',
  position tinyint(4) unsigned DEFAULT NULL COMMENT 'Klassenbuch-Position',
  geburtsort varchar(100) DEFAULT NULL,
  krankenkasse varchar(30) DEFAULT NULL,
  notfall varchar(255) DEFAULT NULL COMMENT 'Im Notfall verstaendigen',
  passwort varchar(64) DEFAULT NULL,
  username varchar(20) DEFAULT NULL,
  number varchar(10) DEFAULT NULL,
  konfession varchar(10) DEFAULT NULL,
  staatsangehoerigkeit varchar(5) NOT NULL DEFAULT 'D',
  aufnahme_am date DEFAULT NULL,
  abgang_am date DEFAULT NULL,
  abgebende_schule mediumint(9) DEFAULT NULL,
  aufnehmende_schule mediumint(9) DEFAULT NULL,
  schliessfach_schl_nr varchar(50) DEFAULT NULL,
  schliessfachnummer varchar(50) DEFAULT NULL,
  fotoerlaubnis tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY geburtstag (geburtstag)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1096 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'schueler_fehlt'
--

CREATE TABLE IF NOT EXISTS schueler_fehlt (
  schueler int(11) unsigned NOT NULL,
  startdatum date NOT NULL,
  enddatum date NOT NULL,
  nur_stunden tinyint(2) unsigned DEFAULT NULL COMMENT '!=NULL = Fehlstunden',
  entschuldigt tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0; 1 entsch.; 2 krank',
  bemerkung varchar(255) DEFAULT NULL,
  PRIMARY KEY (schueler,startdatum)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'schulart'
--

CREATE TABLE IF NOT EXISTS schulart (
  id tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL,
  kuerzel varchar(10) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'schule'
--

CREATE TABLE IF NOT EXISTS schule (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  kuerzel varchar(100) NOT NULL,
  adresse varchar(255) DEFAULT NULL,
  plz_ort varchar(255) DEFAULT NULL,
  telefon varchar(255) DEFAULT NULL,
  fax varchar(255) DEFAULT NULL,
  schulleiter smallint(6) DEFAULT NULL,
  bundesland smallint(6) unsigned NOT NULL DEFAULT '12',
  aktives_schuljahr year(4) DEFAULT NULL,
  aktiv tinyint(1) DEFAULT NULL,
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'schule_schulart'
--

CREATE TABLE IF NOT EXISTS schule_schulart (
  schule tinyint(4) unsigned NOT NULL,
  schulart tinyint(4) unsigned NOT NULL,
  PRIMARY KEY (schule,schulart)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='an einer Schule kann es mehrere Schularten geben';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'schule_user'
--

CREATE TABLE IF NOT EXISTS schule_user (
  schule int(11) unsigned NOT NULL,
  `user` int(11) unsigned NOT NULL,
  aktiv tinyint(1) DEFAULT '1',
  usertyp tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 gast; 2 lehrer; 3 fachleiter; 4 schulleiter; 5 verwaltung; 6 einzelnutzer',
  PRIMARY KEY (schule,`user`,usertyp)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'schuljahr'
--

CREATE TABLE IF NOT EXISTS schuljahr (
  jahr year(4) NOT NULL,
  halbjahreswechsel date DEFAULT NULL,
  schule tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (jahr,schule)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'sitzplan'
--

CREATE TABLE IF NOT EXISTS sitzplan (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  aktiv tinyint(1) unsigned DEFAULT '1',
  schule smallint(6) unsigned DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'sitzplan_klasse'
--

CREATE TABLE IF NOT EXISTS sitzplan_klasse (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  klasse int(11) unsigned NOT NULL,
  seit date NOT NULL,
  sitzplan smallint(6) unsigned NOT NULL,
  `user` int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=199 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'sitzplan_objekt'
--

CREATE TABLE IF NOT EXISTS sitzplan_objekt (
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  sitzplan smallint(5) unsigned NOT NULL,
  pos_x tinyint(3) unsigned NOT NULL,
  pos_y tinyint(3) unsigned NOT NULL,
  drehung tinyint(3) unsigned NOT NULL,
  typ tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=365 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'sitzplan_platz'
--

CREATE TABLE IF NOT EXISTS sitzplan_platz (
  sitzplan_klasse smallint(6) unsigned NOT NULL,
  objekt mediumint(8) unsigned NOT NULL,
  schueler int(11) unsigned NOT NULL,
  PRIMARY KEY (sitzplan_klasse,objekt)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'sonstiges'
--

CREATE TABLE IF NOT EXISTS sonstiges (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  abschnitt int(11) unsigned NOT NULL,
  inhalt text NOT NULL,
  typ tinyint(4) unsigned NOT NULL COMMENT 'erlaeuterung diskussion merke definition umrandet fest text',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2660 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'sozialform'
--

CREATE TABLE IF NOT EXISTS sozialform (
  id tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  kuerzel varchar(25) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'stichtagsnote'
--

CREATE TABLE IF NOT EXISTS stichtagsnote (
  schueler int(11) unsigned NOT NULL,
  rahmen smallint(6) unsigned NOT NULL,
  fach_klasse int(11) unsigned NOT NULL,
  wert tinyint(2) unsigned DEFAULT NULL,
  tendenz tinyint(2) DEFAULT NULL,
  kommentar varchar(255) DEFAULT NULL,
  PRIMARY KEY (schueler,rahmen,fach_klasse)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'stichtagsnote_fk'
--

CREATE TABLE IF NOT EXISTS stichtagsnote_fk (
  rahmen smallint(6) NOT NULL,
  fach_klasse int(11) NOT NULL,
  `user` int(11) DEFAULT NULL,
  fertig date DEFAULT NULL,
  PRIMARY KEY (rahmen,fach_klasse)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'stichtagsnote_rahmen'
--

CREATE TABLE IF NOT EXISTS stichtagsnote_rahmen (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  tendenz tinyint(1) unsigned DEFAULT NULL,
  bearbeitung_ab date NOT NULL,
  bearbeitung_bis date NOT NULL,
  beschreibung text,
  schule smallint(6) NOT NULL,
  halbjahresnote tinyint(1) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'stundenplan'
--

CREATE TABLE IF NOT EXISTS stundenplan (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  schuljahr year(4) NOT NULL,
  fach_klasse smallint(6) unsigned NOT NULL,
  gerade_woche tinyint(1) NOT NULL COMMENT '1=gerade 0=ungerade',
  wochentag tinyint(4) unsigned NOT NULL COMMENT '1=Montag',
  raum tinyint(4) unsigned NOT NULL COMMENT 'id=Raum207',
  stundenzeit mediumint(9) unsigned NOT NULL,
  gilt_ab date DEFAULT NULL,
  ohne_pause tinyint(1) DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=665 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'stundenzeiten'
--

CREATE TABLE IF NOT EXISTS stundenzeiten (
  id tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  beginn time NOT NULL,
  schule tinyint(4) unsigned NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='unterrichtsstunden gehen 45 minuten' AUTO_INCREMENT=59 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'stundenzeiten_beschreibung'
--

CREATE TABLE IF NOT EXISTS stundenzeiten_beschreibung (
  id tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  beschreibung varchar(100) NOT NULL COMMENT 'zB Gymnasium ab 2008',
  gilt_seit date NOT NULL DEFAULT '2008-07-15',
  schule tinyint(4) unsigned NOT NULL,
  schulart tinyint(4) unsigned NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'test'
--

CREATE TABLE IF NOT EXISTS test (
  id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  notentyp tinyint(4) unsigned NOT NULL,
  url varchar(255) DEFAULT NULL,
  lernbereich tinyint(4) unsigned DEFAULT NULL,
  platz_lassen tinyint(1) DEFAULT NULL,
  bearbeitungszeit tinyint(4) unsigned DEFAULT NULL COMMENT 'in minuten',
  bemerkung varchar(255) DEFAULT NULL,
  punkte smallint(5) unsigned DEFAULT NULL,
  vorspann text,
  hilfsmittel varchar(255) DEFAULT NULL COMMENT 'zB Taschenrechner, Tafelwerk, Hilfszettel...',
  titel varchar(255) DEFAULT NULL COMMENT 'anstelle der Themen',
  arbeitsblatt tinyint(1) DEFAULT NULL,
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=148 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'testthemen'
--

CREATE TABLE IF NOT EXISTS testthemen (
  thema mediumint(9) unsigned NOT NULL,
  test mediumint(9) unsigned NOT NULL,
  PRIMARY KEY (thema,test)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'test_abschnitt'
--

CREATE TABLE IF NOT EXISTS test_abschnitt (
  test mediumint(9) unsigned NOT NULL,
  abschnitt int(11) unsigned NOT NULL,
  PRIMARY KEY (test,abschnitt)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'test_aufgabe'
--

CREATE TABLE IF NOT EXISTS test_aufgabe (
  test mediumint(9) unsigned NOT NULL,
  aufgabe int(11) unsigned NOT NULL,
  zusatzaufgabe tinyint(1) NOT NULL,
  position tinyint(3) unsigned DEFAULT NULL,
  position_b tinyint(3) DEFAULT NULL COMMENT 'fuer eine Gruppe B',
  neue_seite tinyint(1) DEFAULT NULL,
  neue_seite_b tinyint(1) DEFAULT NULL,
  PRIMARY KEY (test,aufgabe)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'thema'
--

CREATE TABLE IF NOT EXISTS thema (
  id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  bezeichnung varchar(100) NOT NULL,
  fach tinyint(4) unsigned NOT NULL,
  oberthema mediumint(9) unsigned DEFAULT NULL,
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id),
  KEY oberthema (oberthema)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=81 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'themenzuordnung'
--

CREATE TABLE IF NOT EXISTS themenzuordnung (
  typ tinyint(3) unsigned NOT NULL COMMENT 'aufgabe=1, block=2, grafik=3, link=4, test=5',
  id int(10) unsigned NOT NULL,
  thema smallint(5) unsigned NOT NULL,
  PRIMARY KEY (typ,id,thema)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'ueberschrift'
--

CREATE TABLE IF NOT EXISTS ueberschrift (
  id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  abschnitt int(11) NOT NULL,
  ebene tinyint(4) unsigned NOT NULL DEFAULT '1',
  `text` varchar(150) NOT NULL,
  typ char(1) DEFAULT NULL COMMENT '1, a, I, A...',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=637 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'users'
--

CREATE TABLE IF NOT EXISTS users (
  user_id int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing user_id of each user, unique index',
  user_name varchar(64) DEFAULT NULL COMMENT 'user''s name, unique',
  user_email varchar(64) DEFAULT NULL COMMENT 'user''s email, unique',
  title varchar(10) DEFAULT NULL,
  surname varchar(50) DEFAULT NULL,
  forename varchar(100) DEFAULT NULL,
  male tinyint(1) unsigned DEFAULT '1' COMMENT '1=male NULL/0=female',
  last_login datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  adress varchar(150) DEFAULT NULL,
  postal_code varchar(10) DEFAULT NULL COMMENT 'Germans PLZ (Postleitzahl)',
  city varchar(50) DEFAULT NULL,
  tel1 varchar(50) DEFAULT NULL,
  tel2 varchar(50) DEFAULT NULL,
  tel3 varchar(50) DEFAULT NULL,
  comments text COMMENT 'comments for users only seen by user-admins',
  birthdate date DEFAULT NULL,
  user_active tinyint(1) NOT NULL DEFAULT '0' COMMENT 'user''s activation status',
  user_failed_logins tinyint(1) NOT NULL DEFAULT '0' COMMENT 'user''s failed login attemps',
  user_last_failed_login int(10) DEFAULT NULL COMMENT 'unix timestamp of last failed login attempt',
  user_registration_datetime datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  user_registration_ip varchar(39) NOT NULL DEFAULT '0.0.0.0',
  token_id varchar(255) DEFAULT NULL COMMENT 'Yubikey OTP',
  PRIMARY KEY (user_id),
  UNIQUE KEY user_email (user_email),
  UNIQUE KEY user_name (user_name)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='user data' AUTO_INCREMENT=1281 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'user_pwd'
--

CREATE TABLE IF NOT EXISTS user_pwd (
  `user` int(11) NOT NULL COMMENT 'user.user_id of each user, unique index',
  gilt_seit datetime NOT NULL COMMENT 'users activation datetime, unique',
  user_password_hash varchar(255) NOT NULL COMMENT 'users password in salted and hashed format',
  verbleibende_versuche tinyint(1) NOT NULL DEFAULT '3' COMMENT 'trys till break',
  gesperrt_bis datetime DEFAULT NULL COMMENT 'break till',
  ablauf_pwd date DEFAULT NULL COMMENT 'when password has gone, write date here',
  PRIMARY KEY (`user`,gilt_seit),
  UNIQUE KEY `user` (`user`,gilt_seit)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='user passwords';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'verwarnungen'
--

CREATE TABLE IF NOT EXISTS verwarnungen (
  schueler int(11) unsigned NOT NULL,
  plan int(11) unsigned NOT NULL,
  anzahl tinyint(4) NOT NULL,
  PRIMARY KEY (schueler,plan)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'vorlagen'
--

CREATE TABLE IF NOT EXISTS vorlagen (
  id int(11) unsigned NOT NULL,
  kurzinhalt varchar(255) DEFAULT NULL,
  inhalt text,
  `user` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'woche_ab'
--

CREATE TABLE IF NOT EXISTS woche_ab (
  schuljahr year(4) NOT NULL,
  datum date NOT NULL,
  `user` int(11) unsigned DEFAULT '1',
  schule smallint(6) unsigned DEFAULT NULL,
  PRIMARY KEY (schuljahr,datum)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

INSERT INTO `user_pwd` (`user`, `gilt_seit`, `user_password_hash`, `verbleibende_versuche`, `gesperrt_bis`, `ablauf_pwd`) VALUES 
('1', '2010-01-01 00:00:00', '$2y$10$DoVsLmm9zbCt4WjbHSAlEukb3xJbti96TQzRC3d3NAqSkP46V1jNW', '5', '', ''); /* Passwort: testuser */

INSERT INTO `users` (`user_id`, `user_name`, `user_email`, `title`, `surname`, `forename`, `male`, `last_login`, `adress`, `postal_code`, `city`, `tel1`, `tel2`, `tel3`, `comments`, `birthdate`, `user_active`, `user_failed_logins`, `user_last_failed_login`, `user_registration_datetime`, `user_registration_ip`, `token_id`) VALUES 
('1', 'testuser', 'testuser@test.de', '', 'User', 'Test', '1', '2014-10-08 19:27:49', '', '', '', '', '', '', '', '', '1', '0', '', '0000-00-00 00:00:00', '0.0.0.0', '');

INSERT INTO `benutzer` (`id`, `aktuelles_schuljahr`, `druckansicht`, `ansicht_2`, `merkhefter`, `letzter_lernbereich`, `letzte_themen_auswahl`, `letzte_schule`, `letzte_fachklasse`, `lb_faktor`, `username`, `zensurenpunkte`, `zensurenkommentare`, `zensuren_unt_ber`, `zensuren_nicht_zaehlen`, `dienstberatungen`, `schuljahresplanung`, `statistiken`, `ustd_planung`, `sitzplan`, `admin`) VALUES 
('1', '2014', '%Hausaufgaben
%Tests
%Struktur
%Notizen
%Ziele
|| %Zeit//%minuten//%Hefter || %Inhalt %Kommentar ||
%Hausaufgabenvergabe
%Testankuendigung', '', '1', '', '', '', '', '1.30', 'dontuse', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1');

INSERT INTO `schulart` (`id`, `name`, `kuerzel`) VALUES 
('1', 'Gymnasium', 'GY'),
('2', 'Oberschule', 'OS'),
('3', 'Berufsgrunbildungsjahr', 'BGW'),
('4', 'Berufsvorbereitungsjahr', 'BVJ'),
('5', 'Berufliches Gymnasium', 'BerGym'),
('6', 'Berufsfachschule', 'BFS'),
('7', 'Fachoberschule', 'FO'),
('8', 'Fachschule', 'FS'),
('9', 'Förderschule', 'Fö'),
('10', 'Grundschule', 'GS'),
('11', 'Hauptschule', 'HS'),
('12', 'Realschule', 'RS');

INSERT INTO `rollen` (`id`, `bezeichnung`) VALUES 
('1', 'Gast'),
('2', 'Lehrer'),
('3', 'Fachleiter'),
('4', 'Schulleiter'),
('5', 'Verwaltung'),
('6', 'Einzelnutzer'),
('7', 'Eltern'),
('8', 'Spender');

INSERT INTO `faecher` (`id`, `name`, `kuerzel`, `anzeigen`, `user`, `schule`) VALUES 
('1', 'Informatik', 'Inf', '1', '0', '0'),
('2', 'Mathematik', 'Ma', '1', '0', '0'),
('3', 'Deutsch', 'De', '0', '0', '0'),
('4', 'Technik/Computer', 'TC', '0', '0', '0'),
('5', 'Sorbisch', 'Sor', '0', '0', '5'),
('6', 'Englisch', 'En', '0', '0', '0'),
('7', 'Physik', 'Ph', '0', '0', '0'),
('8', 'Chemie', 'Ch', '0', '0', '0'),
('9', 'Biologie', 'Bio', '0', '0', '0'),
('10', 'Geschichte', 'Ge', '0', '0', '0'),
('11', 'Geographie', 'Geo', '0', '0', '0'),
('12', 'Gemeinschaftskunde/Rechtserziehung', 'GK', '0', '0', '0'),
('13', 'Ethik', 'Eth', '0', '0', '0'),
('14', 'Religion (evangelisch)', 'ReE', '0', '', '0'),
('15', 'Kunst', 'Ku', '0', '0', '0'),
('16', 'Musik', 'Mu', '0', '0', '0'),
('17', 'Sport', 'Spo', '0', '0', '0'),
('18', 'Wirtschaft-Technik-Haushalt/Soziales', 'WTH', '0', '0', '0'),
('19', 'Deutsch als Zweitsprache', 'DaZ', '0', '0', '5'),
('20', 'Förderunterricht Mathematik', 'FöMa', '0', '0', '5'),
('21', 'Astronomie', 'Ast', '1', '0', '0'),
('22', 'Profil Naturwissenschaften', 'Pn', '1', '0', '0'),
('25', 'Französisch', 'Fr', '1', '', '0'),
('26', 'Profil Orchester', 'Po', '1', '5', '6'),
('28', 'Profil Geisteswissensch.', 'Pg', '1', '0', '6'),
('29', 'Künstlerisches Profil', 'Pk', '1', '0', '6'),
('31', 'Latein', 'La', '1', '0', '0'),
('32', 'Russisch', 'Ru', '1', '0', '0'),
('33', 'Neigungskurs', 'Nk', '1', '0', '0'),
('34', 'Technik und Natur', 'TuN', '1', '0', '0'),
('35', 'Klassenleiterstunde', 'KL', '1', '0', '0'),
('36', 'Vertiefungskurs', 'VK', '1', '0', '0');

INSERT INTO `feste_feiertage` (`id`, `name`, `anzeigen`) VALUES 
('1', 'Mari&auml; Himmelfahrt', '0'),
('2', 'Tag der Deutschen Einheit', '1'),
('3', 'Reformationstag', '1'),
('4', 'Allerheiligen', '0'),
('5', 'Bu&szlig;- und Bettag', '1'),
('6', 'Heilige Drei K&ouml;nige', '0'),
('7', 'Karfreitag', '1'),
('8', 'Ostermontag', '1'),
('9', 'Christi Himmelfahrt', '1'),
('10', 'Pfingstmontag', '1'),
('11', 'Fronleichnam', '0'),
('12', 'Tag der Arbeit', '1');

INSERT INTO `sitzplan` (`id`, `name`, `aktiv`, `schule`) VALUES 
('1', 'Standard', '1', '1'),
('2', 'Informatik', '1', '1'),
('7', 'mal anders', '1', '1');

INSERT INTO `sitzplan_objekt` (`id`, `sitzplan`, `pos_x`, `pos_y`, `drehung`, `typ`) VALUES 
('1', '1', '1', '1', '2', '2'),
('2', '1', '2', '1', '2', ''),
('3', '1', '4', '1', '2', '2'),
('4', '1', '5', '1', '2', ''),
('5', '1', '7', '1', '2', '2'),
('8', '1', '8', '1', '2', ''),
('9', '1', '1', '3', '2', '2'),
('10', '1', '2', '3', '2', ''),
('11', '1', '4', '3', '2', '2'),
('12', '1', '5', '3', '2', ''),
('13', '1', '7', '3', '2', '2'),
('14', '1', '8', '3', '2', ''),
('15', '1', '1', '5', '2', '2'),
('16', '1', '2', '5', '2', ''),
('17', '1', '4', '5', '2', '2'),
('18', '1', '5', '5', '2', ''),
('19', '1', '7', '5', '2', '2'),
('20', '1', '8', '5', '2', ''),
('21', '1', '1', '7', '2', '2'),
('22', '1', '2', '7', '2', ''),
('23', '1', '4', '7', '2', '2'),
('24', '1', '5', '7', '2', ''),
('25', '1', '7', '7', '2', '2'),
('26', '1', '8', '7', '2', ''),
('27', '1', '1', '9', '2', '2'),
('28', '1', '2', '9', '2', ''),
('29', '1', '4', '9', '2', '2'),
('30', '1', '5', '9', '2', ''),
('31', '1', '7', '9', '2', '2'),
('32', '1', '8', '9', '2', ''),
('33', '2', '4', '1', '8', '1'),
('34', '2', '5', '1', '8', '4'),
('36', '2', '6', '1', '8', '4'),
('38', '2', '7', '1', '8', '4'),
('40', '2', '1', '3', '4', '4'),
('42', '2', '1', '4', '4', '4'),
('44', '2', '1', '5', '4', '4'),
('46', '2', '1', '6', '4', '4'),
('48', '2', '1', '7', '4', '4'),
('50', '2', '4', '3', '6', '4'),
('52', '2', '4', '4', '6', '4'),
('54', '2', '4', '5', '6', '4'),
('56', '2', '4', '6', '6', '4'),
('58', '2', '4', '7', '6', '4'),
('60', '2', '5', '3', '4', '4'),
('62', '2', '5', '4', '4', '4'),
('64', '2', '5', '5', '4', '4'),
('66', '2', '5', '6', '4', '4'),
('68', '2', '5', '7', '4', '4'),
('70', '2', '8', '3', '6', '4'),
('72', '2', '8', '4', '6', '4'),
('74', '2', '8', '5', '6', '4'),
('76', '2', '8', '7', '6', '4'),
('77', '7', '1', '1', '2', '2'),
('78', '7', '2', '1', '2', ''),
('79', '7', '3', '1', '2', '2'),
('80', '7', '4', '1', '2', ''),
('81', '7', '5', '1', '2', '2'),
('82', '7', '6', '1', '2', ''),
('83', '7', '7', '1', '2', '2'),
('84', '7', '8', '1', '2', ''),
('85', '7', '8', '2', '4', '2'),
('86', '7', '8', '3', '4', ''),
('87', '7', '1', '3', '6', '2'),
('88', '7', '1', '4', '6', ''),
('89', '7', '2', '3', '2', '2'),
('90', '7', '3', '3', '2', ''),
('91', '7', '6', '3', '2', '2'),
('92', '7', '7', '3', '2', ''),
('93', '7', '8', '4', '4', '2'),
('94', '7', '8', '5', '4', ''),
('95', '7', '1', '5', '6', '1'),
('96', '7', '3', '5', '2', '2'),
('97', '7', '4', '5', '2', ''),
('98', '7', '1', '6', '6', '2'),
('99', '7', '1', '7', '6', ''),
('100', '7', '3', '6', '6', '2'),
('101', '7', '3', '7', '6', ''),
('102', '7', '4', '6', '4', '2'),
('103', '7', '4', '7', '4', ''),
('104', '7', '8', '6', '4', '2'),
('105', '7', '8', '7', '4', ''),
('106', '7', '1', '8', '6', '2'),
('107', '7', '1', '9', '6', ''),
('108', '7', '8', '8', '4', '2'),
('109', '7', '8', '9', '4', ''),
('110', '7', '2', '9', '2', '1'),
('111', '7', '6', '9', '2', '2'),
('112', '7', '7', '9', '2', ''),
('113', '2', '8', '1', '8', '4'),
('114', '2', '1', '2', '4', '4');

INSERT INTO `notentypen` (`id`, `name`, `kuerzel`, `aktiv`, `schule`) VALUES 
('1', 'Klassenarbeit', 'KA', '1', '1'),
('2', 'Klausur', 'KL', '1', '1'),
('3', 'Kurzkontrolle', 'KK', '1', '1'),
('4', 'Mündlich', 'MDL', '1', '1'),
('5', 'Hausaufgabe', 'HA', '1', '1'),
('6', 'Mitarbeit', 'MA', '1', '1'),
('7', 'Tägliche Übung', 'TÜ', '1', '1'),
('8', 'Mündliche Leistungskontrolle', 'MLK', '1', '1'),
('9', 'Leistungskontrolle', 'LK', '1', '1'),
('10', 'Projekt', 'Prj', '1', '1'),
('12', 'Komplexe Leistung', 'KoL', '1', '1'),
('13', 'Praktische Arbeit', 'PrA', '1', '1');

INSERT INTO `bewertung_note` (`bewertungstabelle`, `note`, `prozent_bis`) VALUES 
('1', '1', '91.00'),
('1', '2', '80.00'),
('1', '3', '65.00'),
('1', '4', '49.00'),
('1', '5', '25.00'),
('1', '6', '0.00');

INSERT INTO `bewertungstabelle` (`id`, `name`, `punkte`, `aktiv`, `user`, `schule`) VALUES 
('1', 'Sek I', '0', '', '1', '');

INSERT INTO `ferien` (`welche`, `beginn`, `ende`, `schuljahr`, `bundesland`) VALUES 
('1', '2013-10-21', '2013-11-01', '2013', '12'),
('2', '2013-12-21', '2014-01-03', '2013', '12'),
('3', '2014-02-17', '2014-03-01', '2013', '12'),
('4', '2014-04-18', '2014-04-26', '2013', '12'),
('5', '2014-05-30', '2014-05-30', '2013', '12'),
('1', '2014-10-20', '2014-10-31', '2014', '12'),
('2', '2014-12-22', '2015-01-03', '2014', '12'),
('3', '2015-02-09', '2015-02-21', '2014', '12'),
('4', '2015-04-02', '2015-04-11', '2014', '12'),
('5', '2015-05-15', '2015-05-15', '2014', '12'),
('0', '2013-07-15', '2013-08-24', '2013', '12'),
('0', '2014-07-19', '2014-08-29', '2014', '12'),
('0', '2015-07-11', '0000-00-00', '2015', '12');

INSERT INTO `handlungsmuster` (`id`, `name`, `kuerzel`) VALUES 
('1', 'Lehrervortrag', 'Lehrervortrag'),
('2', 'Unterrichtsgespräch', 'Unterrichtsgespräch'),
('3', 'Tafelarbeit', 'Tafelarbeit'),
('4', 'Test', 'Test'),
('5', 'Schülerreferat', 'Schülerreferat'),
('6', 'Lehrgespräch', 'Lehrgespräch'),
('7', 'Streitgespräch', 'Streitgespräch'),
('8', 'Debatte', 'Debatte'),
('9', 'Experimentieren', 'Experimentieren');

INSERT INTO `medium` (`id`, `name`, `kuerzel`, `detail`) VALUES 
('1', 'Tafel', 'Tafel', ''),
('2', 'Folie', 'Folie', ''),
('3', 'Arbeitsblatt', 'AB', ''),
('4', 'Beamer', 'Beamer', ''),
('5', 'Sprache', 'Sprache', ''),
('6', 'Material', 'Material', ''),
('7', 'Buch', 'Buch', ''),
('8', '-', '-', ''),
('9', 'Computer', 'PC', '');

INSERT INTO `phase` (`id`, `name`, `kuerzel`) VALUES 
('1', 'Stundeneröffnung / Begrüßung / Vorstellen', 'Start'),
('2', 'Warming-up / Konzentration / Einstimmung / Vorbereitung / Zielstellung', 'Zielstellung'),
('3', 'Arbeitsauftrag / Arbeitsplanung', 'Arbeitsauftrag'),
('4', 'Erarbeitung / Verarbeitung', 'Erarbeitung'),
('5', 'Vertiefung / Problematisierung / Polarisierung / Konfrontation / Verallgemeinerung', 'Vertiefung'),
('6', 'Ergebnissicherung / Zusammenfassung / Vergleich / Dokumentation', 'Ergebnissicherung'),
('7', 'Würdigung / Prämierung', 'Prämierung'),
('8', 'Übung / Anwendung', 'Übung'),
('9', 'Vorschau / Ausblick', 'Ausblick'),
('10', 'Pause / Erholung', 'Erholung'),
('11', 'Wiederholung', 'Wiederholung'),
('12', 'Erteilung der Hausaufgaben', 'Hausaufgaben'),
('13', 'Ausstieg / Schlussritual / Rausschmeißer', 'Ausstieg'),
('14', 'Einstieg + Erarbeitung', 'Einst. + Erarb.'),
('15', 'Erarbeitung + Ergebnissicherung', 'Erarb. + Ergeb.'),
('16', 'Einstieg + Erarbeitung + Ergebnissicherung', 'EEE');

INSERT INTO `schuljahr` (`jahr`, `halbjahreswechsel`, `schule`) VALUES 
('2012', '0000-00-00', '6'),
('2013', '2014-02-05', '6'),
('2014', '2015-01-30', '6');

INSERT INTO `sozialform` (`id`, `name`, `kuerzel`) VALUES 
('1', 'Plenumsunterricht', 'Frontal'),
('2', 'Einzelarbeit', 'Einzelarbeit'),
('3', 'Gruppenunterricht', 'Gruppe'),
('4', 'Tandemarbeit', 'Partnerarbeit'),
('5', 'Großgruppenunterricht', 'klassenübergreifend');
