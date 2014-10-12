<?php
session_start();
$pfad="../";
if ($_SESSION["user_id"]>0)
	echo "Login war erfolgreich. <a href=\"".$pfad."index.php\">Gesch&uuml;tzer Bereich</a>
		<script>window.location=\"".$pfad."index.php\"</script>";
else
	echo "Login nicht erfolgreich.";
?>
