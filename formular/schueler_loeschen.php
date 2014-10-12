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


if($_GET["endgueltig"]=="ja") {
	if (userrigths("einzelschueler", $_GET["wen"])!=2)
		die("Sie haben nicht die erforderlichen Rechte, um den Sch&uuml;ler zu l&ouml;schen.");
    if ($_GET["vorschau"]=="ja") {
        include $pfad."header.php";
        echo '<body><div class="inhalt"><form action="'.$pfad.'formular/schueler_loeschen.php?endgueltig=ja&amp;wen='.$_GET["wen"].'" method="post">
            <p>Der Sch&uuml;ler wird endg&uuml;ltig (zusammen mit allen ihm zugeordneten Daten) gel&ouml;scht.</p>';
        echo del_array2echo(delete_db_object("schueler", array(injaway($_GET["wen"])), $pfad, false), "info");
        echo '<p>Wollen Sie das wirklich?</p>
                    <input type="button" class="button" value="nein" onclick="window.close()" />
                    <input type="submit" class="button"  value="ja" />
                </form></div></body></html>';
    }
    else {
       $deleter=del_array2echo(delete_db_object("schueler", array(injaway($_GET["wen"])), $pfad, false), "sql");
            foreach ($deleter as $del_line)
                db_conn_and_sql($del_line);
        echo '<html><head><script type="text/javascript">opener.location.reload(); window.close();</script></head><body>Sie k&ouml;nnen das Fenster jetzt schlie&szlig;en.</body></html>';
    }
}
else {
	if (userrigths("schueler_verschieben", $_GET["wen"])!=2)
		die("Sie haben nicht die erforderlichen Rechte, um den Sch&uuml;ler zu deaktivieren.");
	
    if ($_GET["aktiv"]==1)
        db_conn_and_sql("UPDATE `schueler` SET `aktiv`=1
            WHERE `id`=".injaway($_GET['wen']).";");
        else db_conn_and_sql("UPDATE `schueler` SET `aktiv`=0
            WHERE `id`=".injaway($_GET['wen']).";");

    header("Location: ../index.php?tab=klassen&auswahl=".$_GET["auswahl"]);
    exit;
}
?>
