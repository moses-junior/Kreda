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
$titelleiste="Neue Grafik";
include $pfad."header.php";
include $pfad."funktionen.php";

echo '<body><div class="inhalt">';
eintragung_grafik_link($pfad, "without width", "grafic");
/*if ($_GET["eintragen"]=="true") {
	$tempname = $_FILES['grafik_file']['tmp_name'];
	$name = $_FILES['grafik_file']['name'];
	$tempname_klein = $_FILES['grafik_file_klein']['tmp_name'];
	$name_klein = $_FILES['grafik_file_klein']['name'];
	
	if(empty($_FILES['grafik_file']['name'])) $err[] = "Eine Datei muss ausgew&auml;hlt werden";
	if(empty($err)) {
		$dateiname=pfad_und_dateiname($_POST["grafik_lernbereich"],'grafik',$name,$tempname);
		
		db_conn_and_sql("INSERT INTO `grafik` (`url`, `alt`, `lernbereich`) VALUES
		(".apostroph_bei_bedarf($dateiname["datei"]).", ".apostroph_bei_bedarf($_POST['grafik_alt']).", ".$_POST['grafik_lernbereich'].");");
		
		$id=sql_insert_id();
		$verwendete_themen='';
		$thema=0;
		while($_POST["grafik_thema_".$thema]!="-" and $thema<10) {
			$verwendete_themen.=$_POST["grafik_thema_".$thema].';';
			db_conn_and_sql("INSERT INTO `themenzuordnung` (`typ`,`id`,`thema`) VALUES (3,".$id.",".$_POST["grafik_thema_".$thema].");");
			$thema++;
		}
		db_conn_and_sql("UPDATE fach_klasse SET letzter_lernbereich=".$_POST['grafik_lernbereich'].", letzte_themen_auswahl=".apostroph_bei_bedarf($verwendete_themen)." WHERE id=".sql_result(db_conn_and_sql("SELECT letzte_fachklasse FROM benutzer WHERE benutzer.id=1"), 0, "benutzer.letzte_fachklasse"));
	}
?>
	Fertig<br />
	<input type="button" class="button" value="Fenster schlie&szlig;en" onclick="opener.location.reload(); window.close();" /><?php
}
else { ?>
		<form action="<?php echo $pfad; ?>formular/grafik_neu.php?eintragen=true" method="post" enctype="multipart/form-data" accept-charset="ISO-8859-1">
			<fieldset><legend>Grafik erstellen</legend>
				<?php echo eintragung_grafik(); ?>
				<br />
				<input type="button" class="button" value="eintragen" onclick="auswertung=new Array(new Array(0, 'grafik_alt','nicht_leer'), new Array(0, 'grafik_thema_0','nicht_leer','-'), new Array(0, 'grafik_lernbereich','nicht_leer','-'), new Array(0, 'grafik_file','nicht_leer')); pruefe_formular(auswertung);" />
			</fieldset>
		</form>
<?php
}*/
?>
Wenn eingetragen: <input type="button" class="button" value="Fenster schlie&szlig;en" onclick="opener.location.reload(); window.close();" />
</div>
</body>
</html>
