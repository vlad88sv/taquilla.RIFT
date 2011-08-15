<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once("php/vital.php");
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
    <title>Visor de estadísticas de RIFT</title>
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
$t = 'Venta por día (Lu..Do)';
$c = "SELECT DATE_FORMAT(fecha_vendido,'%W') AS col1, COUNT(*) AS col2, COALESCE(SUM(precio_grabado),0) + (SELECT COALESCE(SUM(precio_evento+precio_cafeteria+precio_comida),0) FROM eventos WHERE DATE_FORMAT(eventos.fecha_vendido,'%w')=DATE_FORMAT(tickets.fecha_vendido,'%w')) AS col3 FROM tickets GROUP BY DATE_FORMAT(fecha_vendido,'%w') ORDER BY col3 DESC";
CrearTablaEstadistica($c,$t);

$t = 'Venta por mes';
$c = "SELECT DATE_FORMAT(fecha_vendido,'%M') AS col1,COUNT(*) as col2, COALESCE(SUM(precio_grabado),0) + (SELECT COALESCE(SUM(precio_evento+precio_cafeteria+precio_comida),0) FROM eventos WHERE DATE_FORMAT(eventos.fecha_vendido,'%m')=DATE_FORMAT(tickets.fecha_vendido,'%m')) AS col3 FROM tickets GROUP BY DATE_FORMAT(fecha_vendido,'%m') ORDER BY col3 DESC";
CrearTablaEstadistica($c,$t);

$t = 'Venta por hora';
$c = "SELECT DATE_FORMAT(fecha_juego,'%l%p') AS col1,COUNT(*) as col2, COALESCE(SUM(precio_grabado),0) + (SELECT COALESCE(SUM(precio_evento+precio_cafeteria+precio_comida),0) FROM eventos WHERE DATE_FORMAT(fecha_evento,'%k')=DATE_FORMAT(fecha_juego,'%k')) AS col3 FROM tickets GROUP BY DATE_FORMAT(fecha_juego,'%k') ORDER BY col3 DESC";
CrearTablaEstadistica($c,$t);

$t = 'Venta por día (1..{28,31})';
$c = "SELECT DATE_FORMAT(fecha_vendido,'%W %e de %M de %Y') AS col1, COUNT(*) as col2, COALESCE(SUM(precio_grabado),0) + (SELECT COALESCE(SUM(precio_evento+precio_cafeteria+precio_comida),0) FROM eventos WHERE DATE(eventos.fecha_vendido)=DATE(tickets.fecha_vendido)) AS col3 FROM tickets GROUP BY DATE(fecha_vendido) ORDER BY DATE(fecha_vendido) ASC";
CrearTablaEstadistica($c,$t);

function CrearTablaEstadistica($c, $t) {
    $r = db_consultar($c);
    echo "<h2>$t</h2>";
    echo "<table>";
    echo "<tr><th>Valor</th><th>Juegos vendidos</th><th>Total</th></tr>";
    while($f = mysql_fetch_assoc($r))
    {
            echo sprintf('<tr><td>%s</td><td>%s</td><td>$%s</td></tr>',$f["col1"],$f["col2"],$f["col3"]);
    }
    echo "<table>";
}
?>
</body>
</html>