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
//  OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
//  Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
//  Siehe die GNU Affero Public License für weitere Details.
//
//  Sie sollten eine Kopie der GNU Affero Public License zusammen mit diesem
//  Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.

$pfad="./";
$titelleiste="Einzelstundenansicht";
include $pfad."header.php";
include $pfad."funktionen.php";
?>
  <body>
	<div id="mf">
		<ul class="r">
			<li><a id="pv" href="javascript:window.print()">diese Seite drucken</a></li>
			<li><a href="javascript:window.back();" class="icon"><img src="<?php echo $pfad; ?>icons/pfeil_links.png" alt="schliessen" /> zur&uuml;ck</a></li>
		</ul>
	</div>
  <?php
	$i=0;
	while ($_GET["plan_".$i]>0) {
		$id=intval(injaway($_GET["plan_".$i]));
		if (sql_result(db_conn_and_sql("SELECT fach_klasse.user FROM fach_klasse, plan WHERE fach_klasse.id=plan.fach_klasse AND plan.id=".$id),0,"user")==$_SESSION['user_id']) {
			$plan=planelemente($id,"nicht bearbeiten",$pfad);
			echo '<div'; if($i>0) echo ' style="page-break-before: always;"'; echo '>';
			einzelstunde_druckansicht($plan, sql_result(db_conn_and_sql("SELECT druckansicht FROM benutzer WHERE id=".$_SESSION['user_id']), 0, "benutzer.druckansicht"));
			echo '</div>';
		}
		$i++;
	} ?>
  </body>
</html>
