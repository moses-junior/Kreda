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

// Pruefung, ob Benutzer angemeldet ist - hier und in funktionen.php
if(!isset($_SESSION['user_id']))
{
   echo "Bitte erst <a href=\"".$pfad."login/index.php\">einloggen</a> <script>window.location=\"".$pfad."login/index.php\"</script>";
   exit;
}

/*$content="Content-type: application/xhtml+xml; charset=iso-8859-1";
//$titelleiste=="Neuer Abschnitt"
if ($titelleiste=="Inhalt hinzuf&uuml;gen") $content="Content-type: text/html";
  header($content);
echo '<?xml version="1.0" encoding="iso-8859-1" ?>
<?xml-stylesheet href="'.$pfad.'MathML/mathml.xsl" type="text/xsl" ?>
';*/
$programmversion='0.98C';
?>
<!DOCTYPE HTML>

<html><!-- xmlns="http://www.w3.org/1999/xhtml" xmlns:xi="http://www.w3.org/2001/XInclude" xml:lang="de"-->
   <head>
	<title>Kreda - <?php echo $titelleiste; ?></title>
	<meta name="author" content="Micha Schubert" />
	<meta name="robots" content="noindex, nofollow" />
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<link rel="shortcut icon" href="<?php echo $pfad; ?>look/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="<?php echo $pfad; ?>look/format.css" />
	<script type="text/javascript" src="<?php echo $pfad; ?>javascript/tooltip.js"></script>
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/jquery-1.11.0.min.js"></script>
	<!--<script type="text/javascript" src="<?php echo $pfad; ?>javascript/jquery-1.6.1.min.js"></script>-->
	<script type="text/javascript" src="<?php echo $pfad; ?>javascript/jquery.tipsy.js"></script>
	<script type="text/javascript" src="<?php echo $pfad; ?>javascript/erweitern.js"></script>
	<link type="text/css" href="<?php echo $pfad; ?>javascript/jquery.tipsy.css" rel="Stylesheet" />
	
	<!-- BLM Multi-Level Effect Menu -->
	<link type="text/css" href="<?php echo $pfad; ?>javascript/blmmenu/blmmenu.css" rel="Stylesheet" />
	<script type="text/javascript" src="<?php echo $pfad; ?>javascript/blmmenu/blmmenu.js"></script>
	<!-- JQuery UI - Cupertino-Theme -->
	<link type="text/css" href="<?php echo $pfad; ?>jquery-ui-neu/jquery-ui.min.css" rel="Stylesheet" />
	<script type="text/javascript" src="<?php echo $pfad; ?>jquery-ui-neu/jquery-ui.min.js"></script>
	<!-- JQuery UI -->
	<!--<link type="text/css" href="<?php echo $pfad; ?>jquery-ui/css/cupertino/jquery-ui.css" rel="Stylesheet" />
	<script type="text/javascript" src="<?php echo $pfad; ?>jquery-ui/js/jquery-ui-1.8.13.custom.min.js"></script>-->
    
    <!-- deutsche Uebersetzung des JQueryUI-Datepickers -->
    <script type="text/javascript" src="<?php echo $pfad; ?>jquery-ui/js/jquery.ui.datepicker-de.js"></script>
    
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/jquery_ui_kreda.js"></script>
    
    <!-- enable Touch 2 JQuery UI -->
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/jquery.ui.touch-punch.min.js"></script>
    
<?php /*
    <!--Xinha-->
	<!--<script type="text/javascript">
        _editor_url  = "<?php echo $pfad; ?>xinha/"   // (preferably absolute) URL (including trailing slash) where Xinha is installed
        _editor_lang = "de";       // And the language we need to use in the editor.
        _editor_skin = "silva";    // If you want use a skin, add the name (of the folder) here
        _editor_icons = "classic"; // If you want to use a different iconset, add the name (of the folder, under the `iconsets` folder) here
    </script>
    <script type="text/javascript" src="<?php echo $pfad; ?>xinha/XinhaCore.js"></script>
    <script type="text/javascript" src="<?php echo $pfad; ?>xinha/my_config.js"></script>--> */ ?>
    
    <!-- MarkItUp (JQuery ist bereits einbezogen) -->
    <script type="text/javascript" src="<?php echo $pfad; ?>markitup/jquery.markitup.js"></script>
    <!-- markItUp! toolbar settings -->
    <script type="text/javascript" src="<?php echo $pfad; ?>markitup/sets/kredacode/set.js"></script>
    <!-- markItUp! skin -->
    <link rel="stylesheet" type="text/css" href="<?php echo $pfad; ?>markitup/skins/simple/style.css" />
    <!--  markItUp! toolbar skin -->
    <link rel="stylesheet" type="text/css" href="<?php echo $pfad; ?>markitup/sets/kredacode/style.css" />
    <!-- SyntaxHighlighter fuer Informatiklehrer -->
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/syntaxhighlighter_3.0.83/scripts/shCore.js"></script>
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/syntaxhighlighter_3.0.83/scripts/shBrushJScript.js"></script>
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/syntaxhighlighter_3.0.83/scripts/shBrushCpp.js"></script>
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/syntaxhighlighter_3.0.83/scripts/shBrushCSharp.js"></script>
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/syntaxhighlighter_3.0.83/scripts/shBrushCss.js"></script>
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/syntaxhighlighter_3.0.83/scripts/shBrushDelphi.js"></script>
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/syntaxhighlighter_3.0.83/scripts/shBrushJava.js"></script>
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/syntaxhighlighter_3.0.83/scripts/shBrushPhp.js"></script>
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/syntaxhighlighter_3.0.83/scripts/shBrushPython.js"></script>
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/syntaxhighlighter_3.0.83/scripts/shBrushSql.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo $pfad; ?>javascript/syntaxhighlighter_3.0.83/styles/shCore.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $pfad; ?>javascript/syntaxhighlighter_3.0.83/styles/shThemeDefault.css" />
    <!-- AsciiMathML implementieren -->
    <script type="text/javascript" src="<?php echo $pfad; ?>javascript/ASCIIMathML.js"></script>
    <script type="text/javascript">
    <!--
        // AsciiMathML
        decimalsign = ",";
        mathcolor = " ";
        notifyIfNoMathML = false;
        /* Buggy, wenn Aufgabe das Einzige ist:
        translateOnLoad = false;
        mathMLOnSite = false;*/
        translateOnLoad = true;
		
        $(document).ready(function() {
            load_markitup();
            $("*").tipsy({html: true, gravity: 'w'});
        });
    -->
    </script>
    
<?php if (!$mit_style) echo '</head>'; ?>
