<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once("php/vital.php");
$fecha_sql = (!isset($_GET['fecha']) ? mysql_date() : $_GET['fecha']);
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
    <title>Visor de compras de cafetería RIFT</title>
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
$t = 'Ventas de cafeteria';
$c = "SELECT ID_transaccion, ID_ticket, (precio_grabado*cantidad) AS precio_total, precio_grabado, DATE_FORMAT(fecha, '%r') AS hora, descripcion, cantidad FROM  `cafeteria_transacciones` LEFT JOIN  `cafeteria_articulos` USING ( ID_articulo ) WHERE DATE(fecha) ='".$fecha_sql."' AND cantidad > 0 ORDER BY ID_transaccion ASC";
CrearTablaManual($c,$t);

function CrearTablaManual($c, $t) {
    $r = db_consultar($c);
    echo "<h2>$t</h2>";
    echo "<table>";
    echo "<tr><th>Hora</th><th>Descripción</th><th>Cantidad</th><th>Precio unitario</th><th>Total compra</th></tr>";
    while($f = mysql_fetch_assoc($r))
    {
        echo sprintf('<tr><td class="hora">%s</td><td>%s</td><td>%s</td><td>$%s</td><td>$%s</td></tr>',$f["hora"],$f["descripcion"],$f["cantidad"],$f["precio_grabado"],$f["precio_total"]);
    }
    echo "<table>";
}
?>
</body>
</html>