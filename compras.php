<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once("php/vital.php");
$fecha_sql = (!isset($_GET['fecha']) ? mysql_date() : $_GET['fecha']);
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
    <title>Visor de compras de RIFT</title>
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
$t = 'Ingresos (según ingreso manual)';
$c = "SELECT `descripcion`, `comprador`, `ingresado_por`, `total_compra`, DATE_FORMAT(`fechatiempo`,'%H:%i') AS 'hora', `nombre` FROM `cafeteria_ingresos` LEFT JOIN `usuarios` ON ingresado_por=ID_usuario WHERE DATE(`cafeteria_ingresos`.`fechatiempo`)='".$fecha_sql."'";
CrearTablaManual($c,$t);

$t = 'Ingresos (según ingreso asistido)';
$c = "SELECT  `ID_articulo` ,  `stock` ,  DATE_FORMAT(`fecha`,'%H:%i') AS 'hora',  `ingresado_por`,  `descripcion`, `nombre` FROM  (`cafeteria_stock` LEFT JOIN  `cafeteria_articulos` USING (  `ID_articulo` )) LEFT JOIN `usuarios` ON ingresado_por=ID_usuario  WHERE DATE(`fecha`)='".$fecha_sql."'";
CrearTablaAsistida($c,$t);

function CrearTablaManual($c, $t) {
    $r = db_consultar($c);
    echo "<h2>$t</h2>";
    echo "<table>";
    echo "<tr><th>Hora</th><th>Descripción</th><th>Comprador</th><th>Ingresado por</th><th>Total compra</th></tr>";
    while($f = mysql_fetch_assoc($r))
    {
        echo sprintf('<tr><td class="hora">%s</td><td>%s</td><td>%s</td><td>%s</td><td>$%s</td></tr>',$f["hora"],$f["descripcion"],$f["comprador"],$f["nombre"],$f["total_compra"]);
    }
    echo "<table>";
}

function CrearTablaAsistida($c, $t) {
    $r = db_consultar($c);
    echo "<h2>$t</h2>";
    echo "<table>";
    echo "<tr><th>Hora</th><th>Stock</th><th>Descripción</th><th>Ingresado por</th></tr>";
    while($f = mysql_fetch_assoc($r))
    {
        echo sprintf('<tr><td class="hora">%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$f["hora"],$f["stock"],$f["descripcion"],$f["nombre"]);
    }
    echo "<table>";
}
?>
</body>
</html>