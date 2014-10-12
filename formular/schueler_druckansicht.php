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
$titelleiste="Klassenbuchansicht";
include $pfad."header.php";
include $pfad."funktionen.php";

if (!proofuser("fach_klasse",$_GET["fach_klasse"]))
	die("Sie sind hierzu nicht berechtigt.");

?>
	<body>
	<div id="mf">
		<ul class="r">
			<li><a id="pv" href="javascript:window.print()">diese Seite drucken</a></li>
			<li><a href="javascript:window.close();" class="icon"><img src="<?php echo $pfad; ?>icons/fenster_schliessen.png" alt="schliessen" /> Fenster schlie&szlig;en</a></li>
		</ul>
	</div>
<?php
    //$result = db_conn_and_sql ( 'SELECT * FROM `schueler` WHERE `schueler`.`klasse` = '.injaway($_GET['klasse'])." AND `schueler`.`aktiv`=1 ORDER BY `schueler`.`position`" );
    $result=schueler_von_fachklasse(injaway($_GET["fach_klasse"]));
    if (sql_num_rows ( $result )<1)
		die('Der Klasse sollten Sch&uuml;ler hinzugef&uuml;gt werden.');
    else { ?>
    <table cellspacing="0" class="klassenbuch">
      <tr>
		<th style="background-color: lightgrey;">Nr.</th>
        <th style="background-color: lightgrey;">Name, Vorname</th>
	</tr>
      <?php
      for($i=0;$i<(sql_num_rows ( $result )) or $i<35;$i++) { ?>
      <tr>
        <td style="width: 1cm; text-align: center; <?php if($i/2!=round($i/2)) echo ' background-color: lightgrey;'; if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;'; ?>"><?php echo $i+1; ?></td>
        <td style="width: 6cm;<?php if($i/2!=round($i/2)) echo ' background-color: lightgrey;'; if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;'; ?>"><?php if (@sql_result ( $result, $i, 'schueler.name' )!="") echo html_umlaute(@sql_result ( $result, $i, 'schueler.name' )).", ".html_umlaute(@sql_result ( $result, $i, 'schueler.vorname' )); ?></td>
      </tr>
      <?php } ?>
	</table>
	<!-- <br /> -->
    <table cellspacing="0" style="page-break-before: always;" class="klassenbuch">
      <tr>
		<th>Nr.</th>
        <th>Wohnanschrift</th>
        <th>Geburtsdatum</th>
        <th>Geburtsort</th>
	</tr>
      <?php
      for($i=0;$i<(sql_num_rows ( $result ));$i++) { ?>
      <tr>
        <td style="width: 1cm; text-align: center; <?php if($i/2!=round($i/2)) echo ' background-color: lightgrey;'; if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;'; ?>"><?php echo $i+1; ?></td>
        <td style="width: 8cm;<?php if($i/2!=round($i/2)) echo ' background-color: lightgrey;'; if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;'; ?>"><?php echo @sql_result ( $result, $i, 'schueler.strasse' ); ?>; <?php echo html_umlaute(@sql_result ( $result, $i, 'schueler.ort' )); ?></td>
        <td style="text-align: center; width: 3cm;<?php if($i/2!=round($i/2)) echo ' background-color: lightgrey;'; if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;'; ?>"><?php echo datum_strich_zu_punkt(@sql_result ( $result, $i, 'schueler.geburtstag' )); ?></td>
		<td style="text-align: center; width: 5.5cm;<?php if($i/2!=round($i/2)) echo ' background-color: lightgrey;'; if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;'; ?>"><?php echo html_umlaute(@sql_result ( $result, $i, 'schueler.geburtsort' )); ?></td>
      </tr>
      <?php } ?>
	</table>
	<br />
	
    <table cellspacing="0" style="page-break-before: always;" class="klassenbuch">
      <tr>
		<th>Nr.</th>
        <th>Telefon-Nr.</th>
        <th>Krankenkasse</th>
        <th>Im Notfall zu verst&auml;ndigen</th>
      </tr>
      <?php
      for($i=0;$i<(sql_num_rows ( $result ));$i++) { ?>
      <tr>
        <td style="width: 1cm; text-align: center; <?php if($i/2!=round($i/2)) echo ' background-color: lightgrey;'; if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;'; ?>"><?php echo $i+1; ?></td>
        <td style="width: 3.2cm;<?php if($i/2!=round($i/2)) echo ' background-color: lightgrey;'; if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;'; ?>"><?php echo html_umlaute(@sql_result ( $result, $i, 'schueler.telefon' )); ?></td>
		<td style="text-align: center; width: 2.8cm;<?php if($i/2!=round($i/2)) echo ' background-color: lightgrey;'; if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;'; ?>"><?php echo html_umlaute(@sql_result ( $result, $i, 'schueler.krankenkasse' )); ?></td>
        <td style="width: 10.3cm;<?php if($i/2!=round($i/2)) echo ' background-color: lightgrey;'; if(($i+1)/5==round(($i+1)/5)) echo ' border-bottom-width: 2px;'; ?>"><?php echo html_umlaute(@sql_result ( $result, $i, 'schueler.notfall' )); ?></td>
      </tr>
      <?php } ?>
    </table>
<?php } ?>
</body>
</html>
