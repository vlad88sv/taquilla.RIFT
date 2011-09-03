<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
    <title>Visor de ventas de taquilla RIFT</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Style-type" content="text/css" />
    <meta http-equiv="Content-Script-type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="es" />
    <meta name="robots" content="index, follow" />
    <style type="text/css" media="all">
        body{font-family:monospace;}
        table{border-collapse:collapse;}
        th,td{border:1px solid #DDD;text-align:center;padding:1px;}
        p {padding:0;margin:0;border:none;}
        .hora{font-weight:bolder;}
        .diminuto{font-size:smaller;color:#111;padding:0;margin:0;border:none;}
        h1, h2 {padding:1px;margin:1px;}
		#contendor_tablas td{vertical-align:top;}
    </style>
</head>
<body>
<?php
$cuerpo_tabla = "";
$dbh = @ibase_connect("192.168.1.104:C:\Program Files\P&CMicros\Database\ZoneSystems.gdb", "SYSDBA", "masterke");
if ($dbh){
$stmt = "SELECT a.GAME_ID, a.DEVICE_ALIAS, a.SHOTS_FIRED, a.PLAYING FROM T2GAMESTATS a WHERE a.GAME_ID = ".$_GET["gi"];
$sth = ibase_query($dbh, $stmt);

while ($f = ibase_fetch_assoc($sth))
{
    $cuerpo_tabla .= sprintf("<tr><td>%s</td><td>%s</td><td>%s</td></tr>",$f["DEVICE_ALIAS"],$f["SHOTS_FIRED"],($f["PLAYING"] == "1" ? "Jugando" : "Colgado"));
}
?>
<table>
    <tr><th>DEVICE_ALIAS</th><th>SHOTS_FIRED</th><th>PLAYING</th></tr>
    <?php echo $cuerpo_tabla; ?>
</table>
<?php } ?>
</body>
</html>