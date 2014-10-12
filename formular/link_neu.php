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
$titelleiste="Arbeitsblatt / Folie / Datei erstellen";
if ($_GET["type"]=="file")
    $titelleiste="Datei eingetragen";
else
    $titelleiste="Grafik eingetragen";
include $pfad."header.php";
include $pfad."funktionen.php";


if ($_GET["eintragen"]=="true") {
	?>
	<body>
	<?php

	if(empty($_FILES['link_file']['name']))
		$err[] = "Eine Datei muss ausgew&auml;hlt werden";

	if(empty($err)) {
		$tempname = $_FILES['link_file']['tmp_name'];
		$name = $_FILES['link_file']['name'];
		
		$type = $_FILES['link_file']['type'];
		$size = $_FILES['link_file']['size'];
		
        if ($_GET["type"]=="file") {
            $prefix=$_POST['link_typ'];
            $lernbereich = injaway($_POST["link_lernbereich"]);
        }
        else {
            $prefix='grafik';
            $lernbereich = injaway($_POST["grafik_lernbereich"]);
        }
		$dateiname=pfad_und_dateiname($lernbereich, $prefix, $name, $tempname, $pfad);
		
        if ($_GET["type"]=="file")
            $id=db_conn_and_sql("INSERT INTO `link` (`url`, `lokal`, `beschreibung`, `typ`, `lernbereich`, `user`) VALUES
                (".apostroph_bei_bedarf($dateiname["datei"]).", 1, ".apostroph_bei_bedarf($_POST['link_beschreibung']).", ".injaway($_POST['link_typ']).", ".injaway($_POST['link_lernbereich']).", ".$_SESSION['user_id'].");");
        else
            $id=db_conn_and_sql("INSERT INTO `grafik` (`url`, `alt`, `lernbereich`, `user`) VALUES
                (".apostroph_bei_bedarf($dateiname["datei"]).", ".apostroph_bei_bedarf($_POST['grafik_beschreibung']).", ".injaway($_POST['grafik_lernbereich']).", ".$_SESSION['user_id'].");");
		
		// Themen anheften
		$verwendete_themen='';
		$thema=0;
        if ($_GET["type"]=="file") {
            while($_POST["link_thema_".$thema]!="-" and $thema<10) {
				$verwendete_themen.=$_POST["link_thema_".$thema].';';
				db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (4,".$id.",".$_POST["link_thema_".$thema].");");
				$thema++;
			}
        }
        else {
            while($_POST["grafik_thema_".$thema]!="-" and $thema<10) {
                $verwendete_themen.=$_POST["grafik_thema_".$thema].';';
                db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (3,".$id.",".$_POST["grafik_thema_".$thema].");");
                $thema++;
            }
        }
        // zuletzt verwendete Lernbereiche und Themen der verwendeten Fach-Klasse aktualisieren
		db_conn_and_sql("UPDATE fach_klasse SET letzter_lernbereich=".$lernbereich.", letzte_themen_auswahl=".apostroph_bei_bedarf($verwendete_themen)." WHERE id=".sql_result(db_conn_and_sql("SELECT letzte_fachklasse FROM benutzer WHERE benutzer.id=".$_SESSION["user_id"]), 0, "benutzer.letzte_fachklasse"));
		
		// Thumbnail erstellen
		if ($_GET["type"]=="grafic") {
            thumbnail_erstellen($dateiname["pfad"], $dateiname["datei"]);
		}
		
		
		?>
		<script type="text/javascript">
		<?php
		
		// MarkItUp-Editor fuellen
        if ($_GET["type"]=="file") {
            ?>
            try {
                parent.$.markItUp( { replaceWith:'[file;'+'<?php echo $id; ?>'+']' } );
                window.setTimeout(parent.$( "#pictureframe" ).dialog('close'),100);
                history.back();
            } catch(e) {
                alert("No markItUp! Editor found");
            } <?php
        }
        else {
            ?>
            try {
                var breite=''+<?php echo $_POST["grafik_groesse"]; ?>;
                parent.$.markItUp( { replaceWith:'[grafic;'+'<?php echo $id; ?>'+';'+'middle'+';'+breite.replace(/,/, '.')+']' } );
                window.setTimeout(parent.$( "#pictureframe" ).dialog('close'),100);
                history.back();
            } catch(e) {
                alert("No markItUp! Editor found");
            } <?php
        }
	} ?>
	</script>
	<div id="mf">
		<ul class="r">
			<li><a href="javascript:window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>  
	<?php
	if (isset($err[$i]))
		echo '<h1>Fenster schlie&szlig;t sich</h1>';
	$i=0;
	while (isset($err[$i])) {
		echo $err[$i].'<br />';
		$i++;
	}
	?>
    <br />
    <a href="javascript:history.back()">zur&uuml;ck</a>
</body>
</html>
<?php
}
else { ?>
		<input type="checkbox" onclick="if (this.checked) { document.getElementById('arbeitsblatt_aus_datei_erstellen').style.display='none'; document.getElementById('arbeitsblatt_mit_aufgaben_erstellen').style.display='block'; } else { document.getElementById('arbeitsblatt_aus_datei_erstellen').style.display='block'; document.getElementById('arbeitsblatt_mit_aufgaben_erstellen').style.display='none';}" />
			Arbeitsblatt mit Einzel-Aufgaben erstellen<br />
		<span id="arbeitsblatt_aus_datei_erstellen">
			<div class="inhalt">
			<?php eintragung_grafik_link($pfad, "", "file"); ?>
			</div>
		</span>
		<span id="arbeitsblatt_mit_aufgaben_erstellen" style="display: none;">
			<?php echo test_druckansicht("neu", "arbeitsblatt_bearbeiten"); ?><br />
		</span>
</div>
</body>
</html>
	<?php
}
?>
