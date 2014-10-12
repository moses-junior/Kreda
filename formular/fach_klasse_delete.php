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
$pfad="../";
include $pfad."funktionen.php";

$notenspalten_da=db_conn_and_sql("SELECT * FROM notenbeschreibung WHERE notenbeschreibung.fach_klasse=".injaway($_GET["id"]));
$plan_da=db_conn_and_sql("SELECT * FROM plan WHERE plan.fach_klasse=".injaway($_GET["id"]));
$stundenplaneintrag_da=db_conn_and_sql("SELECT * FROM stundenplan WHERE stundenplan.fach_klasse=".injaway($_GET["id"]));

$err=array();
if (sql_num_rows($notenspalten_da)>0)
	$err[]="Die Fach-Klasse enth&auml;lt eingetragene Zensurenspalten.";
if (sql_num_rows($plan_da)>0)
	$err[]="Der Fach-Klasse wurden Stoffverteilungsplaneintr&auml;ge zugeordnet.";
if (sql_num_rows($stundenplaneintrag_da)>0)
	$err[]="Die Fach-Klasse wurde im Stundenplan eingetragen.";

if (userrigths("fachklasse_loeschen", $_GET["id"])==2 or empty($err))
	if($_GET["bestaetigen"]=="ja") {
		$lehrauftrag=db_conn_and_sql("SELECT fach_klasse FROM lehrauftrag WHERE fach_klasse=".injaway($_GET["id"]));
		if (sql_num_rows($lehrauftrag)>0) {
			$lehrauftrag=sql_fetch_assoc($lehrauftrag);
			db_conn_and_sql("DELETE FROM lehrauftrag WHERE fach_klasse=".$lehrauftrag["fach_klasse"]);
		}
		
		$deleter=del_array2echo(delete_db_object("fach_klasse", array(injaway($_GET["id"])), $pfad, false), "sql");
		foreach ($deleter as $del_line)
			db_conn_and_sql($del_line);
		echo '<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>';
	}
	else {
		include $pfad."header.php";
		echo '<body><div class="inhalt"><form action="'.$pfad.'formular/fach_klasse_delete.php?bestaetigen=ja&amp;id='.$_GET["id"].'" method="post">
			<p>Die Fach-Klasse-Kombination wird endg&uuml;ltig (zusammen mit allen ihr zugeordneten Daten) gel&ouml;scht.</p>';
		echo del_array2echo(delete_db_object("fach_klasse", array(injaway($_GET["id"])), $pfad, false), "info");
		echo '<p>Wollen Sie das wirklich?</p>
                <input type="button" class="button" value="nein" onclick="window.close()" />
                <input type="submit" class="button"  value="ja" />
            </form></div></body></html>';
	}
else
	if (empty($err))
		echo "Sie haben nicht die erforderlichen Rechte, um die Fach-Klasse-Kombination zu l&ouml;schen.";
	else
		echo '<div class="hinweis">Sie k&ouml;nnen die Fach-Klasse nicht l&ouml;schen, weil dazu folgende Eintr&auml;ge existieren:<br />'.implode("<br />", $err).'</div>';
?>
