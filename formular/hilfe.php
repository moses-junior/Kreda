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
	$titelleiste="Hilfe";
	include $pfad."header.php";
	include $pfad."funktionen.php";
?>
	<body>
	<div class="inhalt">
		<?php
			if ($_GET["inhalt"]=="start") { ?>
			<p>Der Kreda Lehrstoffplaner verwaltet Ihren Unterricht - mit allem, was dazu geh&ouml;rt.</p>
			<p>Zun&auml;chst sollten Sie:
				<ol>
					<li>Ihre <a href="<?php echo $pfad; ?>index.php?tab=einstellungen&amp;auswahl=faecher" onclick="opener.location.href=this.href; return false;">Unterrichtsf&auml;cher</a> angeben</li>
					<li>einen <a href="<?php echo $pfad; ?>index.php?tab=stundenplanung&amp;auswahl=lernbereiche&amp;aktion=alle" onclick="opener.location.href=this.href; return false;">Lehrplan</a> anlegen</li>
					<li><a href="<?php echo $pfad; ?>index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr=<?php echo $aktuelles_jahr; ?>" onclick="opener.location.href=this.href; return false;">Ferientermine</a> und schulfreie Tage eintragen</li>
					<li>aktuelles <a href="<?php echo $pfad; ?>index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr=allgemein" onclick="opener.location.href=this.href; return false;">Schuljahr</a> festlegen</li>
					<li>eine <a href="<?php echo $pfad; ?>index.php?tab=einstellungen&amp;auswahl=schulen&amp;erstellen=raum" onclick="opener.location.href=this.href; return false;">Schule</a> inklusive der Unterrichtszeiten anlegen</li>
					<li>darin befindliche <a href="<?php echo $pfad; ?>index.php?tab=klassen&amp;zweit=alle" onclick="opener.location.href=this.href; return false;">Klassen</a> eintragen</li>
					<li>Fach-Klassen-Kombinationen eintragen</li>
					<li>Sch&uuml;ler eintragen</li>
					<li><a href="<?php echo $pfad; ?>index.php?tab=noten" onclick="opener.location.href=this.href; return false;">Zensurenspalten</a> einer Fach-Klassen-Kombination erstellen</li>
					<li>Ihren <a href="<?php echo $pfad; ?>index.php?tab=stundenplan&amp;auswahl=stundenplan" onclick="opener.location.href=this.href; return false;">Stundenplan</a> erstellen</li>
					<li><a href="<?php echo $pfad; ?>index.php?tab=material&amp;auswahl=themen" onclick="opener.location.href=this.href; return false;">Themen</a> erstellen (kann auch w&auml;hrend der Unterrichtsplanung vorgenommen werden)</li>
					<li>im Lehrplan <a href="<?php echo $pfad; ?>index.php?tab=stundenplanung&amp;auswahl=lernbereiche&amp;aktion=alle" onclick="opener.location.href=this.href; return false;">Lernbereiche</a> mit Unterrichtseinheiten und (bei Bedarf) Bl&ouml;cken anlegen</li>
					<li>die <a href="<?php echo $pfad; ?>index.php?tab=stundenplanung&amp;auswahl=fkplan" onclick="opener.location.href=this.href; return false;">Unterrichtsstoffverteilung</a> durchf&uuml;hren</li>
					<li>einzelne Unterrichtsstunden erstellen</li>
			</ol></p>
			<a href="<?php echo $pfad; ?>look/voraussetzungen.png"><img src="<?php echo $pfad; ?>look/voraussetzungen.png" alt="voraussetzungen" style="width: 600px;" /></a>
		<?php
			}
			if ($_GET["inhalt"]=="drucktipps") { ?>
			<p>Drucken Sie Ihren Stundenplan, Sitzpl&auml;ne, Unterrichtsvorbereitungen, Teststatistiken, Zensurenst&auml;nde... mit Kreda,
				werden unn&ouml;tigte Seitenelemente (Men&uuml;leiste, Hinweise...) nicht mit gedruckt.</p>
			<p>Es ist sinnvoll folgende Einstellungen vorzunehmen, damit alle Bilder und Hintergrundfarben gedruckt werden:
			</p>
			<img src="<?php echo $pfad; ?>basic/help/druckoptionen.png" alt="drucktipps" />
		<?php
			}
			if ($_GET["inhalt"]=="stundenplan") { ?>
			<p>Stellen Sie hier Ihren <b>Stundenplan</b> zusammen, indem Sie das H&auml;kchen vor dem Symbol <img src="<?php echo $pfad; ?>icons/edit.png" alt="bearbeiten" /> setzen. Danach k&ouml;nnen Sie Unterrichtsstunden eintragen (<img src="<?php echo $pfad; ?>icons/neu.png" alt="neu" />) oder vorhandene l&ouml;schen (<img src="<?php echo $pfad; ?>icons/delete.png" alt="loeschen" />) bzw. &auml;ndern (<img src="<?php echo $pfad; ?>icons/edit.png" alt="bearbeiten" />).</p>
			<p>Den <img src="<?php echo $pfad; ?>icons/kalender.png" alt="kalender" /> <b>Kalender</b> gibt es zur besseren &Uuml;bersicht des Schuljahres. Darin werden Feiertage, Ferientermine und schulfreie Tage gekennzeichnet. Wenn mehrere Schulen eingetragen sind und sich Unterschiede ergeben, wird auch dies kenntlich gemacht.</p>
			<p><ul><li>Feiertage werden automatisch berechnet und werden in den <a href="<?php echo $pfad; ?>index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr=allgemein" onclick="opener.location.href=this.href; return false;">Einstellungen</a> lediglich ausgew&auml;hlt.</li>
				<li>Ferientermine und schulfreie Tage werden ebenfalls in den <a href="<?php echo $pfad; ?>index.php?tab=einstellungen&amp;auswahl=schuljahr&amp;jahr=<?php echo $aktuelles_jahr; ?>" onclick="opener.location.href=this.href; return false;">Einstellungen</a> vorgenommen.</li>
				<li>Dazu z&auml;hlen allerdings keine Wandertage o.&auml;., da diese jeweils nur eine einzige Klasse, nicht aber die ganze Schule betreffen. Solche Tage k&ouml;nnen in der <a href="<?php echo $pfad; ?>index.php?tab=stundenplanung&amp;auswahl=fkplan" onclick="opener.location.href=this.href; return false;">Unterrichtsgrobplanung</a> als Ausfallgrund angegeben werden.</li></ul></p>
			<p>Wenn sie A/B-Wochen in Ihrem Stundenplan nutzen, k&ouml;nnen Sie die Abfolge &auml;ndern z.B. aufgrund von Verschiebungen wegen Ferien. Auch die erste Woche sollte kontrolliert werden. Klicken Sie dazu auf das Tausch-Symbol (<img src="<?php echo $pfad; ?>icons/ab_woche_tauschen.png" alt="tausch" />) der jeweiligen Woche - danach werden alle darauffolgenden Wochen-Eintr&auml;ge ebenfalls verschoben. Dieser Schritt l&auml;sst sich nat&uuml;rlich wieder umkehren.</p>
		<?php
			}
			if ($_GET["inhalt"]=="nachbereitung") { ?>
			Startseite: Nachbereitung, Plan-Druckansicht, Geburtstage, Tests
		<?php
			}
			if ($_GET["inhalt"]=="noten_bearbeiten") { ?>
			<p>Die Zensuren werden weitgehend selbst berechnet - Kreda unterscheidet:
			<ul><li>Angabe einer Zensur ohne Punkteangabe</li>
			<li>Eine Gesamtpunktzahl wird angegeben und bei Sch&uuml;lern die Testpunktzahl: Zensur wird mithilfe der gew&auml;hlten Bewertungstabelle berechnet.</li>
			<li>Ein Test mit eingetragenen Aufgaben wird der Zensurenspalte zugeordnet: Zensur wird mithilfe der Einzelaufgabenpunktzahlen und der gew&auml;hlten Bewertungstabelle berechnet. Dies hat den Vorteil, dass eine Statistik zu den einzelnen Aufgaben erstellt wird.</li>
			</ul>
			</p>
			<p>Falls Sie einzelne Zensuren gern in der Zensurenliste stehen haben m&ouml;chten, aber diese nicht in die Durchschnitts-Berechnung eingehen sollen, k&ouml;nnen Sie entweder die gesamte Zensurenspalte auf "nicht bewerten" setzen oder "einzeln w&auml;hlen". Damit erscheint ein Zus&auml;tzliches Auswahlfeld, in dem Sie einzelne Sch&uuml;ler an- bzw. abw&auml;hlen k&ouml;nnen.</p>
		<?php
			}
			if ($_GET["inhalt"]=="einzelstundenplanung") { ?>
			<p>Damit eine Einzelstunde im Detail geplant werden kann, muss die Stunde zun&auml;chst in einer der Fach-Klassen-Kombinationen mit Datum eingetragen werden.
				Danach ist die Stunde bereit, um mit Abschnitten gef&uuml;llt zu werden.
				Die Abschnitte k&ouml;nnen alternativ auch schon zuvor im "Fundus" eingetragen sein.</p>
			<p>Zuvor m&uuml;ssen aber auf jeden Fall im Fundus Lehrplan, Lernbereiche und mindestens eine Unterrichtseinheit / ein Block eingetragen werden, damit man diese der Einzelstunde zuweisen kann. Das kann z.B. relativ z&uuml;gig mit einem Lehrplan im PDF-Format per Copy&amp;Paste geschehen.</p>
		<?php
			}
			if ($_GET["inhalt"]=="lehrplaene") { ?>
			<p>Hier finden Sie alle eingetragenen Lehrpl&auml;ne der <a href="index.php?tab=einstellungen&auswahl=faecher">aktiven</a> F&auml;cher. Wenn einer Klassenstufe Lernbereiche zugeordnet sind, werden diese farbig hervorgehoben. Um einen neuen Lernbereich einzutragen, klicken Sie auf eine graue Klassenstufe.</p>
			<p>W&auml;hlen Sie per H&auml;kchen au&szlig;erdem aus, welche Lehrpl&auml;ne in den Men&uuml;s angezeigt werden sollen.</p>
		<?php
			}
			if ($_GET["inhalt"]=="material") { ?>
				<p>Hier k&ouml;nnen Sie der Datenbank Materialien hinzuf&uuml;gen.</p>
				<p>Themen fungieren dabei wie Tags, die man einem Bild u.a. anheften kann. &Uuml;ber diese Themen kann man sp&auml;ter Material leichter finden. Sie gelten gleichzeitig als Standard-Titel bei Tests.</p>
				<p>Folgende Materialien k&ouml;nnen Sie hinzuf&uuml;gen:
					<ul><li>Aufgaben</li>
						<li>Tests (bestehend aus Aufgaben oder einer Datei)</li>
						<li>Arbeitsbl&auml;tter</li>
						<li>Folien</li>
						<li>Links (z.B. Dateien oder Web-Seiten)</li>
						<li>Schulb&uuml;cher (daraus k&ouml;nnen Aufgaben entnommen werden)</li>
						<li>Grafiken</li>
						<li>sonstiges Material, wie Zeichenger&auml;te, Taschenrechner...</li></ul></p>
		<?php
			}
			if ($_GET["inhalt"]=="syntax") { ?>
			Sie k&ouml;nnen folgende Syntax verwenden:
			<span class="hinweis">Leerzeichen werden mit "_" dargestellt</span>
			<h4>Hervorhebungen:</h4>
			<p>folgende Hervorhebungen k&ouml;nnen verwendet werden:
					<ul>
						<li><pre><code>Es folgt ein [red]farbiger[/red] Text.</code></pre>
							Ergibt: <p class="einzelstunde"><?php echo syntax_zu_html('Es folgt ein [red]farbiger[/red] Text.', 1); ?></p>
							Funktioniert ebenfalls mit <code>[yellow], [brown], [blue], [green], [orange], [gray]</code></li>
						<li><code>Textteile k&ouml;nnen ebenfalls [b]fett[/b], [i]kursiv[/i] oder [u]unterstrichen[/u] dargestellt werden.</code>
							Ergibt: <p class="einzelstunde"><?php echo syntax_zu_html('Textteile k&ouml;nnen ebenfalls [b]fett[/b], [i]kursiv[/i] oder [u]unterstrichen[/u] dargestellt werden.', 1); ?></p></li>
					</ul>
					</p>
				<h4>Aufz&auml;hlung:</h4>
				<pre><code>*_Aufz&auml;hlung Level 1
**_Aufz&auml;hlung Level 2
**_Aufz&auml;hlung Level 2
*_Aufz&auml;hlung Level 1</code></pre>
					Ergibt: <p class="einzelstunde"><?php echo syntax_zu_html('* Aufz&auml;hlung Level 1
** Aufz&auml;hlung Level 2
** Aufz&auml;hlung Level 2
* Aufz&auml;hlung Level 1', 1); ?></p>
				<h4>Auflistung:</h4>
				<pre><code>a)_Mercedes
a)_VW
a)_Opel</code></pre>
					Ergibt: <p class="einzelstunde"><?php echo syntax_zu_html('a) Mercedes
a) VW
a) Opel', 1); ?><br style="clear: both;" /></p>
					<div class="hinweis"><ul><li>Falls Sie einen <span style="font-weight: bold;">Zeilenumbruch</span> innerhalb einer Auflistung, Aufz&auml;hlung oder Tabelle einbauen wollen, verwenden Sie <code>[nl]</code>.</li>
						<li>Achtung: nie "b)" verwenden.</li>
						<li>Alternativ zu "a)" kann auch "A)", "1)", oder "I)" verwendet werden.</li></ul>
					</div>
				<h4>Tabellen:</h4>
				Tabellen werden mithilfe von zwei "|" erzeugt (Taste unter "A").<p>Ein einfaches Beispiel:
					<pre><code>|| Zelle 1 || Zelle 2 || Zelle 3 ||
|| Zelle 4 || Zelle 5 || Zelle 6 ||</code></pre>
					Ergibt: <p class="einzelstunde"><?php echo syntax_zu_html('|| Zelle 1 || Zelle 2 || Zelle 3 ||
|| Zelle 4 || Zelle 5 || Zelle 6 ||', 1); ?></p></p>
					<p>Sie k&ouml;nnen au&szlig;erdem Zellen senkrecht (mit "&lt;|4&gt;" f&uuml;r 4 Zellverbindungen) und waagerecht (mit "&lt;-3&gt;" f&uuml;r 3 Zellverbindungen) zusammenfassen.
					Diese Angabe muss gleich nach Beginn des Doppel-Strichs (ohne Leerzeichen) erfolgen:
					<pre><code>||&lt;|2&gt; Zelle 1 ||&lt;-2&gt; Zelle 2 ||
|| Zelle 3 || Zelle 4 ||</code></pre>
					Ergibt: <p class="einzelstunde"><?php echo syntax_zu_html('||&lt;|2&gt; Zelle 1 ||&lt;-2&gt; Zelle 2 ||
|| Zelle 3 || Zelle 4 ||', 1); ?></p>
					</p>
				<h4>Mathematische Formeln:</h4>
				Formeln k&ouml;nnen mit Hilfe von AsciiMathML dargestellt werden. Beispiel:
<pre><code>'sum_(n=1)^oo n/(n+1)'</code></pre>
Ergibt: <p class="einzelstunde"><?php echo syntax_zu_html('`sum_(n=1)^oo n/(n+1)`'); ?> <div class="hinweis">Sollten Sie hier keine richtig formatierte Summenformel vorfinden, unterst&uuml;tzt Ihr Browser kein MathML. Wenn Sie auf dieses Feature nicht verzichten wollen, nutzen Sie z.B. die neuste Version des Firefox Webbrowsers.</div></p>
				<h4>Programmiersprachen:</h4>
				Um Programmiersprachen aufzuschreiben nutzen Sie <pre><code>[code]if (Beispiel==true)
   then write("Hallo Welt")[/code]</code></pre>
				Ergibt: <p class="einzelstunde"><?php echo syntax_zu_html('[code]if (Beispiel==true)
   then write("Hallo Welt")[/code]', 1); ?></p>
		<p>[code] und &lt;math&gt; sind die einzigen Elemente, welche &uuml;ber mehrere Zeilen verwendet werden k&ouml;nnen.</p>
		<?php
			}
		?>
	</div>
</body>
</html>
