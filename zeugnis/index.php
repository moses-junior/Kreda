<?
$html = '<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<style type="text/css">
		body {font-size: 13px; line-height: 1.6; font-family:Arial;}
		.wrap_overall {width: auto; margin:20px 20px 0px 20px;}
		.title {font-weight:bold; font-size: 20px;}
		.clear {clear: both}
		.list_l {float: left; width: 25%}
		.list_r {float: right; width: 25%}
		.list-teil {margin: 0px 0px 0px 30px}
		.list2_l {float: left; width: 25%}
		.list2_r {float: right; width: 25%}
		.mark {width: 25%; padding: 0px 50px; background-color: #F2E9D8; border-bottom:1px solid #565754}
		.sign_l {font-size: 10px; border-top:1px solid #F2E9D8; float: left; width: 33%}
		.sign2_l {font-size: 10px; float: left; width: 33%}
		.sign_r {font-size: 10px; border-top:1px solid #F2E9D8; float: right; width: 33%}
		.el_sign {font-size: 10px; border-top:1px solid #F2E9D8; width: 33%; margin: 0px 222px 0px;clear:both}
		.note {float: left; width: 33%; margin 0px 0px 0px 50px}
		<!--@page {margin: 1.5em 1.5em 0 1.5em;}	-->			
	</style>
	</head>
	<body>
		<div class="wrap_overall">
			<div>
					Name der Schule:
					<b><span class="list-teil">1. Musterschule Musterhausen </span></b>
			</div>
			<br />
			<div align="center">
					<span class="title">Jahreszeugnis Grundschule </span>
			</div>
			<br />
			<br />
			<div class="list_l">
					Klasse: 
					<span class="list-teil">5 b</span>
			</div>
			<div class="list_r">					
					Schuljahr 
					<span class="list-teil">2012/2013</span>
			</div>
			<div class="clear"></div>
			<div>
				Name, Vorname: 
				<span class="list-teil">Muster, Max</span>	
			</div>
			<br />
			<div class="list2_l">
				Betragen
				<br />
				Fleiß			
			</div>
			<div class="list2_l">
				<span class="mark">2</span>
				<br />
				<span class="mark">2</span>
			</div>
			<div class="list2_l">
				<span>Mitarbeit
				<br />
				Ordnung</span>	
			</div>
			<div class="list2_l">
				<span class="mark">1</span>
				<br />
				<span class="mark">2</span>
			</div>
			<div class="clear"></div>
			<br />
			<div>
				<b>Einschätzung:</b>	
				<p class="text">The quick brown fox jumps over the lazy dog. The quick brown fox jumps over the lazy dog. 
				The quick brown fox jumps over the lazy dog. The quick brown fox jumps over the lazy dog. 
				The quick brown fox jumps over the lazy dog. The quick brown fox jumps over the lazy dog.
				The quick brown fox jumps over the lazy dog.</p>
			</div>
			<div class="clear">
				<br />
				<b>Leistungen in Einzelfächern: </b>
			</div>
			<div class="list2_l">
				Deutsch
				<br />
				Sachunterricht
				<br />
				Englich
				<br />
				Kunst
				<br />
				Musik	
			</div>
			<div class="list2_l">
				<span class="mark">2</span>
				<br />
				<span class="mark">2</span>
				<br />
				<span class="mark">3</span>
				<br />
				<span class="mark">2</span>
				<br />
				<span class="mark">2</span>
			</div>
			<div class="list2_l">
				Mathematik
				<br />
				Sport
				<br />
				Ethik
				<br />
				Werken	
			</div>
			<div class="list2_l">
				<span class="mark">3</span>
				<br />
				<span class="mark">1</span>
				<br />
				<span class="mark">3</span>
				<br />
				<span class="mark">1</span>
			</div>
			<div class="clear"></div>
			<br />
			<div class="list2_l">
				<b>Bemerkung:</b>
			</div>
				<div class="note">Fehltage entschuldigt: --- </div>
				<div class="note">unentschuldigt: 9 </div>
			<div class="clear">
				<p class="text">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut 
				labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.
				Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, 
				consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed 
				diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata 
				sanctus est Lorem ipsum dolor sit amet.</p>
				Versetzungsvermerk: <span class="list-teil">wird versetzt</span>
			</div>
			<br />
			<div>
				Datum: <span class="list-teil">28.06.2013</span>
			</div>
			<br />
			<br />
			<br />
			<div class="sign_l" align="center">
				Schulleiter(in)
			</div>
			<div class="sign2_l" align="center">
				Dienstsiegel der Schule
			</div>
			<div class="sign_r" align="center">
				Klassenlehrer(in)
			</div>
			<br />
			<br />
			<br />
			<div>
				Zur Kenntnis genommen:
			</div>
			<div align="center" class="el_sign" >
				Eltern
			</div>
			<div class="clear"></div>
		</div>
	</body>
</html>';
		

if(isset($_POST['gesendet'])) {
//Include DOMPDF
require_once("dompdf/dompdf_config.inc.php");


if ( get_magic_quotes_gpc() ) {
	$html = stripslashes($html);
}
  
$dompdf = new DOMPDF();
$dompdf->load_html($html);
$dompdf->set_paper("A4", "portrait");
$dompdf->render();

$dompdf->stream("zeugnis.pdf", array("Attachment" => false));

exit(0);
}



?>


<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	
	</head>
	<body>
	<form action="" method="post">
		 <p><input type="hidden" name="gesendet" /></p>		
		 <p><input type="submit" value="PDF erzeugen" /></p>
	</form>
	</body>
</html>


