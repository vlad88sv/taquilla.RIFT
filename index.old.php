<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once("php/vital.php");
function Ping($host)
{
	return strpos( shell_exec("ping -q -n -c1 $host"),'+1 errors') ? 'Apagada' : 'Encendida';
}
// Var
$fecha_sql = (!isset($_GET['fecha']) ? mysql_date() : $_GET['fecha']);
$cuerpo_tabla = "";
$buffer = "";
$hora = "";

// Construyamos la tabla virtual
for ($ii=0;$ii<65;$ii++)
{
    $hora = date('H:i',strtotime('08:00 a.m. +'.(15*$ii).' minutes'));
    for ($i=0;$i<14;$i++)
    {
            $pos[$hora][$i] = "-";
            $pos[$hora]['fecha_vendido'] = "";
    }

}

// Obtengamos los tiquetes vendidos
$c = "SELECT `fecha_vendido`, DATE(`fecha_juego`) AS 'fecha', DATE_FORMAT(`fecha_juego`,'%H:%i') AS `hora`, `numero_jugador`, `representacion`, `precio_grabado` FROM `tickets` LEFT JOIN `tipo_boleto` USING(`ID_tipo_boleto`) WHERE DATE(`fecha_juego`) = '".$fecha_sql."' ORDER BY `fecha_juego` ASC, `numero_jugador` ASC";
$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $pos[$f['hora']][$f['numero_jugador']] = $f['representacion'];
    $pos[$f['hora']]['fecha_vendido'] = $f['fecha_vendido'];
}

// Eventos
$c = "SELECT `eventos`.`ID_evento`, `eventos`.`precio_evento`, `eventos`.`precio_comida`, `eventos`.`precio_cafeteria`, `eventos`.`patrocinado_por`, `eventos`.`nombre_evento`, `eventos`.`notas`, `eventos`.`fecha_evento`, `fecha_vendido`, DATE_FORMAT(`eventos`.`hora_inicio`,'%I:%i %p') AS 'ffhora_inicio', DATE_FORMAT(`eventos`.`hora_inicio`,'%H:%i') AS 'fhora_inicio', DATE_FORMAT(`eventos`.`hora_final`,'%H:%i') AS 'fhora_final', `eventos`.`agregado_por_usuario` FROM `eventos` WHERE `eventos`.`fecha_evento`='".$fecha_sql."' ORDER BY `eventos`.`hora_inicio`";
$r = db_consultar($c);

while ($f = mysql_fetch_assoc($r))
{
    $cuartos = (strtotime($f['fhora_final']) - strtotime($f['fhora_inicio'])) / 900;
    for ($ii= 0; $ii <  $cuartos; $ii++)
        for ($i=0;$i<14;$i++)
            $pos[date('H:i',strtotime($f['ffhora_inicio'].' +'.(15*$ii).' minutes'))][$i] = "E";
}

// Construyamos la representacion de nuestro modelo virtual
for ($ii=0;$ii<65;$ii++)
{
    $hora = date('H:i',strtotime('08:00 a.m. +'.(15*$ii).' minutes'));
    $cuerpo_tabla .= sprintf("<tr><td class='hora'>%s</td><td title='Fecha vendido: ".$pos[$hora]['fecha_vendido']."'>%s</td><td title='Fecha vendido: ".$pos[$hora]['fecha_vendido']."'>%s</td><td title='Fecha vendido: ".$pos[$hora]['fecha_vendido']."'>%s</td><td title='Fecha vendido: ".$pos[$hora]['fecha_vendido']."'>%s</td><td title='Fecha vendido: ".$pos[$hora]['fecha_vendido']."'>%s</td><td title='Fecha vendido: ".$pos[$hora]['fecha_vendido']."'>%s</td><td title='Fecha vendido: ".$pos[$hora]['fecha_vendido']."'>%s</td><td title='Fecha vendido: ".$pos[$hora]['fecha_vendido']."'>%s</td><td title='Fecha vendido: ".$pos[$hora]['fecha_vendido']."'>%s</td><td title='Fecha vendido: ".$pos[$hora]['fecha_vendido']."'>%s</td><td title='Fecha vendido: ".$pos[$hora]['fecha_vendido']."'>%s</td><td title='Fecha vendido: ".$pos[$hora]['fecha_vendido']."'>%s</td><td title='Fecha vendido: ".$pos[$hora]['fecha_vendido']."'>%s</td></tr>",$hora,$pos[$hora][1],$pos[$hora][2],$pos[$hora][3],$pos[$hora][4],$pos[$hora][5],$pos[$hora][6],$pos[$hora][7],$pos[$hora][8],$pos[$hora][9],$pos[$hora][10],$pos[$hora][11],$pos[$hora][12],$pos[$hora][13]);
}

// Obtengamos el promedio esperado para este día
$c = "SELECT DATE_FORMAT('".$fecha_sql."','%W') AS dia, FORMAT(AVG(total),2) AS promedio, FORMAT(MIN(total),2) AS minimo, FORMAT(MAX(total),2) AS maximo FROM (SELECT SUM(COALESCE(precio_grabado,0)) + (SELECT COALESCE(SUM(precio_evento+precio_cafeteria+precio_comida),0) FROM eventos WHERE DATE(eventos.fecha_evento)=DATE(tickets.fecha_juego)) AS total FROM tickets WHERE tickets.fecha_juego<DATE(NOW()) AND DATE_FORMAT(tickets.fecha_juego,'%w')=DATE_FORMAT('".$fecha_sql."','%w') GROUP BY DATE(fecha_juego)) AS sub";
$r = db_consultar($c);
$f = mysql_fetch_assoc($r);
$promedio = $f['promedio'];
$minimo = $f['minimo'];
$maximo = $f['maximo'];
$dia = $f['dia'];
?>
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
    </style>
</head>
<body>
<h1>RIFT - Taquilla | <?php echo $fecha_sql; ?></h1>
<p>Conectado a <b><?php echo db__host; ?></b></p>
<div><form action="./" method="GET">Ir a fecha [año-mes-día]:<input type="input" name="fecha" value="<?php echo date("Y-m-d"); ?>"/><input type="submit" value="Ir" /></form>
<p>Promedio esperado para días <?php echo $dia; ?>: <b>$<?php echo $promedio; ?></b></p>
<p>Mínimo historico en días <?php echo $dia; ?>: <b>$<?php echo $minimo; ?></b></p>
<p>Máximo historico en días <?php echo $dia; ?>: <b>$<?php echo $maximo; ?></b></p>
<hr />
<?php
$c = "SELECT FORMAT(COALESCE(SUM(precio_grabado),0)+COALESCE((SELECT SUM(`eventos`.`precio_evento` + `eventos`.`precio_comida` + `eventos`.`precio_cafeteria`) FROM `eventos` WHERE DATE(`eventos`.`fecha_vendido`)= '".$fecha_sql."'),0),2) AS total FROM tickets WHERE DATE(fecha_vendido) =  '".$fecha_sql."'";
$r = db_consultar($c);
$f = mysql_fetch_assoc($r);
?>
<p>Dinero en caja + bouchers: <b>$<?php echo $f['total']; ?></b></p>
<p class="diminuto">"Total t" es la suma del ingreso registrado en el dia.</p>
<?php
$c = "SELECT FORMAT(COALESCE(SUM(precio_grabado),0)+COALESCE((SELECT SUM(`eventos`.`precio_evento` + `eventos`.`precio_comida` + `eventos`.`precio_cafeteria`) FROM `eventos` WHERE DATE(`eventos`.`fecha_evento`)= '".$fecha_sql."'),0),2) AS total FROM tickets WHERE DATE(fecha_juego) =  '".$fecha_sql."'";
$r = db_consultar($c);
$f = mysql_fetch_assoc($r)
?>
<p>Dinero generado por juegos este día: <b>$<?php echo $f['total']; ?></b></p>
<p class="diminuto">"Total T" es la suma de ventas por juegos en este día.</p>
<br />
<?php
$buffer_transacciones = '';
$c = "SELECT COUNT(*) AS 'cuenta', `descripcion`, precio_grabado, DATE(fecha_juego) AS fecha_juego2 FROM `tickets` LEFT JOIN `tipo_boleto` USING(ID_tipo_boleto) WHERE DATE(fecha_vendido) = '".$fecha_sql."' GROUP BY CONCAT(ID_tipo_boleto,precio_grabado,DATE(fecha_juego)) ORDER BY descripcion";
$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $buffer_transacciones .= '<tr><td>'.$f["descripcion"].'</td><td>$'.$f["precio_grabado"].'</td><td><a href="http://taquilla.riftelsalvador.com/?fecha='.$f["fecha_juego2"].'">'.$f["fecha_juego2"].'</a></td><td>'.$f["cuenta"].'</td></tr>';
}
?>
<h2>Detalle transacciones</h2>
<table>
    <tr><th>Grupo</th><th>Precio</th><th>Fecha juego</th><th>Cantidad</th></tr>
    <?php echo $buffer_transacciones; ?>
</table>
<br />

<?php
$buffer_eventos = '';
$c = "SELECT `patrocinado_por`, `fecha_evento`, `eventos`.`ID_evento`, (`eventos`.`precio_evento` + `eventos`.`precio_comida` + `eventos`.`precio_cafeteria`) AS precio_evento FROM `eventos` WHERE DATE(`eventos`.`fecha_vendido`)='".$fecha_sql."' ORDER BY `eventos`.`hora_inicio`";
$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $buffer_eventos .= '<tr><td>'.$f["patrocinado_por"].'</td><td>$'.$f["precio_evento"].'</td><td>'.$f["fecha_evento"].'</td></tr>';
}
?>
<?php if(mysql_num_rows($r)): ?>
<h2>Detalle eventos vendidos hoy</h2>
<table>
    <tr><th scope="Descripcion">Patrocinado por</th><th scope="Cantidad">Precio</th><th scope="Fecha">Fecha evento</th></tr>
    <?php echo $buffer_eventos; ?>
</table>
<br />
<?php endif; ?>
<?php
$buffer_eventos = '';
$c = "SELECT hora_inicio, hora_final, `patrocinado_por`, `eventos`.`fecha_vendido`, `eventos`.`ID_evento`, (`eventos`.`precio_evento` + `eventos`.`precio_comida` + `eventos`.`precio_cafeteria`) AS precio_evento, (SELECT `nombre` FROM `usuarios` WHERE `ID_usuario`= `eventos`.`agregado_por_usuario`) AS 'nombre_usuario' FROM `eventos` WHERE DATE(`eventos`.`fecha_evento`)='".$fecha_sql."' ORDER BY `eventos`.`hora_inicio`";
$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $buffer_eventos .= '<tr><td>'.$f["patrocinado_por"].'</td><td>$'.$f["precio_evento"].'</td><td>'.$f["fecha_vendido"].'</td><td>'.$f["nombre_usuario"].'</td><td>'.$f["hora_inicio"].'</td><td>'.$f["hora_final"].'</td></tr>';
}
?>
<?php if(mysql_num_rows($r)): ?>
<h2>Detalle eventos programados para hoy</h2>
<table>
    <tr><th scope="Descripcion">Patrocinado por</th><th scope="Cantidad">Precio</th><th scope="Fecha">Fecha vendido</th><th>Cajero</th><th>Hora inicio</th><th>Hora final</th></tr>
    <?php echo $buffer_eventos; ?>
</table>
<br />
<?php endif; ?>

<table><tr><td>
<h2>Tabla de venta de tiquetes</h2>
<table>
    <tr><th>Hora</th><th>e01</th><th>e02</th><th>e03</th><th>e04</th><th>e05</th><th>e06</th><th>e07</th><th>e08</th><th>e09</th><th>e10</th><th>e11</th><th>e12</th><th>e13</th></tr>
    <?php echo $cuerpo_tabla; ?>
</table>
</td><td>
<h2>Tabla de juegos</h2>
<?php
$cuerpo_tabla = "";
$dbh = ibase_connect("192.168.1.104:C:\Program Files\P&CMicros\Database\ZoneSystems.gdb", "SYSDBA", "masterke");
$stmt = "SELECT a.GAME_TIMESTAMP, a.EVENT_TYPE, a.GAME_ID, a.PROFILE_DESCRIPTION, a.DATA_1, a.DATA_2, a.DATA_3, a.DATA_4, a.DATA_5
FROM T2GAMELOG a WHERE CAST(a.GAME_TIMESTAMP AS DATE) = CAST('".$fecha_sql."' AS DATE) AND EVENT_TYPE=3";

$sth = ibase_query($dbh, $stmt);

while ($f = ibase_fetch_assoc($sth))
{
	$cuerpo_tabla .= sprintf("<tr><td class='hora'>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",$f["GAME_TIMESTAMP"],$f["DATA_1"],$f["DATA_2"],$f["DATA_4"]);
}
?>
<h2>Tabla de venta de tiquetes</h2>
<table>
    <tr><th>Hora</th><th>Duración</th><th>En línea</th><th>Jugadores</th></tr>
    <?php echo $cuerpo_tabla; ?>
</table>
</td></tr></table>

<h2>Tabla de juegos</h2>
<?php
$cuerpo_tabla = "";
$dbh = ibase_connect("192.168.1.104:C:\Program Files\P&CMicros\Database\ZoneSystems.gdb", "SYSDBA", "masterke");
$stmt = "SELECT a.GAME_TIMESTAMP, a.EVENT_TYPE, a.GAME_ID, a.PROFILE_DESCRIPTION, a.DATA_1, a.DATA_2, a.DATA_3, a.DATA_4, a.DATA_5
FROM T2GAMELOG a WHERE CAST(a.GAME_TIMESTAMP AS DATE) = CAST('".$fecha_sql."' AS DATE) AND EVENT_TYPE=3";

$sth = ibase_query($dbh, $stmt);

while ($f = ibase_fetch_assoc($sth))
{
	$cuerpo_tabla .= sprintf("<tr><td class='hora'>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",$f["GAME_TIMESTAMP"],$f["DATA_1"],$f["DATA_2"],$f["DATA_4"]);
}
?>
<?php
$buffer_eventos = '';
$c = "SELECT `ID_ingreso`, `descripcion`, `comprador`, `ingresado_por`, `total_compra`, `fechatiempo` FROM `cafeteria_ingresos` WHERE DATE(`fecha_tiempo`)='".$fecha_sql."' ORDER BY `fecha_tiempo`";
$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $buffer_eventos .= '<tr><td>'.$f["patrocinado_por"].'</td><td>$'.$f["precio_evento"].'</td><td>'.$f["fecha_evento"].'</td></tr>';
}
?>
<?php if(mysql_num_rows($r)): ?>
<h2>Detalle ingresos</h2>
<table>
    <tr><th scope="Descripcion">Patrocinado por</th><th scope="Cantidad">Precio</th><th scope="Fecha">Fecha evento</th></tr>
    <?php echo $buffer_eventos; ?>
</table>
<br />
<?php endif; ?>
</body>
</html>
