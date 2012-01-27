<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once("php/vital.php");
$fecha_sql = (!isset($_GET['fecha']) ? mysql_date() : $_GET['fecha']);
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
// Obtengamos el promedio esperado para este día
$c = "SELECT DATE_FORMAT('".$fecha_sql."','%W') AS dia, FORMAT(AVG(total),2) AS promedio, FORMAT(MIN(total),2) AS minimo, FORMAT(MAX(total),2) AS maximo FROM (SELECT SUM(COALESCE(precio_grabado,0)) + (SELECT COALESCE(SUM(precio_evento+precio_cafeteria+precio_comida),0) FROM eventos WHERE DATE(eventos.fecha_evento)=DATE(tickets.fecha_juego)) + (COALESCE((SELECT SUM(precio_grabado*cantidad) FROM `cafeteria_transacciones` WHERE DATE(`cafeteria_transacciones`.`fecha`) = DATE(tickets.fecha_juego)),0)) AS total FROM tickets WHERE tickets.ID_tipo_boleto NOT IN (2,3,4) AND tickets.fecha_juego<DATE(NOW()) AND DATE_FORMAT(tickets.fecha_juego,'%w')=DATE_FORMAT('".$fecha_sql."','%w') GROUP BY DATE(fecha_juego)) AS sub";
$r = db_consultar($c);
$f = mysql_fetch_assoc($r);
$promedio = $f['promedio'];
$minimo = $f['minimo'];
$maximo = $f['maximo'];
$dia = $f['dia'];
?>
<p>Promedio esperado para días <?php echo $dia; ?>: <b>$<?php echo $promedio; ?></b></p>
<p>Mínimo historico en días <?php echo $dia; ?>: <b>$<?php echo $minimo; ?></b></p>
<p>Máximo historico en días <?php echo $dia; ?>: <b>$<?php echo $maximo; ?></b></p>
<hr />
<?php
$t = 'Desempeño cafetería - por producto';
$c = "SELECT cafeteria_articulos.ID_articulo, cafeteria_articulos.codigo_barra, IF (descripcion IS NULL, '-producto removido-',descripcion) AS descripcion, SUM(cantidad) AS cantidad_vendida, SUM(cantidad*precio_grabado) AS total_vendido FROM cafeteria_transacciones LEFT JOIN cafeteria_articulos USING(ID_articulo) GROUP BY cafeteria_articulos.ID_articulo ORDER BY total_vendido DESC";
CrearTabla(array('Código art.','Código barra','Descripcion','Cantidad','Total vendido'),$c,$t);

$t = 'Desempeño cafetería - por mes';
$c = "SELECT DATE_FORMAT(fecha,'%M/%y') AS mes, SUM(cantidad*precio_grabado) AS total_vendido FROM cafeteria_transacciones LEFT JOIN cafeteria_articulos USING(ID_articulo) GROUP BY DATE_FORMAT(fecha,'%m-%y') ORDER BY total_vendido DESC ";
CrearTabla(array('Mes','Total vendido'),$c,$t);

$t = 'Venta por día (Lu..Do)';
$c = "SELECT DATE_FORMAT(fecha_vendido,'%W') AS col1, COUNT(*) AS col2, COALESCE(SUM(precio_grabado),0) + (SELECT COALESCE(SUM(precio_evento+precio_cafeteria+precio_comida),0) FROM eventos WHERE DATE_FORMAT(eventos.fecha_vendido,'%w')=DATE_FORMAT(tickets.fecha_vendido,'%w')) AS col3 FROM tickets GROUP BY DATE_FORMAT(fecha_vendido,'%w') ORDER BY col3 DESC";
CrearTablaEstadistica($c,$t);

$t = 'Venta por mes';
$c = "SELECT DATE_FORMAT(fecha_vendido,'%M/%y') AS col1,COUNT(*) as col2, COALESCE(SUM(precio_grabado),0) + (SELECT COALESCE(SUM(precio_evento+precio_cafeteria+precio_comida),0) FROM eventos WHERE DATE_FORMAT(eventos.fecha_vendido,'%m-%y')=DATE_FORMAT(tickets.fecha_vendido,'%m-%y')) AS col3 FROM tickets GROUP BY DATE_FORMAT(fecha_vendido,'%m-%y') ORDER BY col3 DESC";
CrearTablaEstadistica($c,$t);

$t = 'Venta por hora';
$c = "SELECT DATE_FORMAT(fecha_juego,'%l%p') AS col1,COUNT(*) as col2, COALESCE(SUM(precio_grabado),0) + (SELECT COALESCE(SUM(precio_evento+precio_cafeteria+precio_comida),0) FROM eventos WHERE DATE_FORMAT(fecha_evento,'%k')=DATE_FORMAT(fecha_juego,'%k')) AS col3 FROM tickets GROUP BY DATE_FORMAT(fecha_juego,'%k') ORDER BY col3 DESC";
CrearTablaEstadistica($c,$t);

$t = 'Venta por día (1..{28,31})';
$c = "SELECT DATE_FORMAT(fecha_vendido,'%W %e de %M de %Y') AS col1, COUNT(*) as col2, COALESCE(SUM(precio_grabado),0) + (SELECT COALESCE(SUM(precio_evento+precio_cafeteria+precio_comida),0) FROM eventos WHERE DATE(eventos.fecha_vendido)=DATE(tickets.fecha_vendido)) AS col3 FROM tickets GROUP BY DATE(fecha_vendido) ORDER BY DATE(fecha_vendido) ASC";
CrearTablaEstadistica($c,$t);

function CrearTablaEstadistica($c, $t) {
    CrearTabla(array('Valor','Juegos vendidos','Total'),$c,$t);
}

function CrearTabla(array $columnas, $c, $t)
{
    $r = db_consultar($c);
    echo "<h2>$t</h2>";
    echo "<table>";
    echo "<tr><th>".implode('</th><th>',$columnas)."</th></tr>";
    while($f = mysql_fetch_assoc($r))
    {
        echo "<tr><td>".implode('</td><td>',$f)."</td></tr>";
    }
    echo "<table>";    
}
?>
</body>
</html>