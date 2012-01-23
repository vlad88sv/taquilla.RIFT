<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once("php/vital.php");
function Ping($host)
{
	return strpos( shell_exec("ping -q -n -c1 $host"),'+1 errors') ? 'Apagada' : 'Encendida';
}
// Var
$RIFT_server = Ping('192.168.1.104');
$RIFT_encendida = ($RIFT_server == 'Encendida');
$fecha_sql = (!isset($_GET['fecha']) ? mysql_date() : $_GET['fecha']);
$cuerpo_tabla = "";
$buffer = "";
$hora = "";

// Construyamos la tabla virtual
for ($ii=0;$ii<65;$ii++)
{
	$hora = date('H:i',strtotime('08:00 a.m. +'.(15*$ii).' minutes'));
	$pos[$hora] = 0;
}

// Obtengamos los tiquetes vendidos
$c = "SELECT COUNT(*) AS 'vendidos', DATE(`fecha_juego`) AS 'fecha', DATE_FORMAT(`fecha_juego`,'%H:%i') AS `hora` FROM `tickets` LEFT JOIN `tipo_boleto` USING(`ID_tipo_boleto`) WHERE `tickets`.`ID_tipo_boleto` <> 2 AND `tickets`.`ID_tipo_boleto` <> 3 AND `tickets`.`ID_tipo_boleto` <> 4 AND `tickets`.`ID_tipo_boleto` <> 11 AND `fecha_juego` BETWEEN '".$fecha_sql." 00:00:00' AND '".$fecha_sql." 23:59:59' GROUP BY `fecha_juego` ORDER BY `fecha_juego` ASC, `numero_jugador` ASC ";

$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $pos[$f['hora']] = $f['vendidos'];
}

// Eventos
$c = "SELECT `eventos`.`ID_evento`, `eventos`.`precio_evento`, `eventos`.`precio_comida`, `eventos`.`precio_cafeteria`, `eventos`.`patrocinado_por`, `eventos`.`nombre_evento`, `eventos`.`notas`, `eventos`.`fecha_evento`, `fecha_vendido`, DATE_FORMAT(`eventos`.`hora_inicio`,'%I:%i %p') AS 'ffhora_inicio', DATE_FORMAT(`eventos`.`hora_inicio`,'%H:%i') AS 'fhora_inicio', DATE_FORMAT(`eventos`.`hora_final`,'%H:%i') AS 'fhora_final', `eventos`.`agregado_por_usuario` FROM `eventos` WHERE `eventos`.`fecha_evento`='".$fecha_sql."' ORDER BY `eventos`.`hora_inicio`";
$r = db_consultar($c);

while ($f = mysql_fetch_assoc($r))
{
    $cuartos = (strtotime($f['fhora_final']) - strtotime($f['fhora_inicio'])) / 900;
    for ($ii= 0; $ii <  $cuartos; $ii++)
            $pos[date('H:i',strtotime($f['ffhora_inicio'].' +'.(15*$ii).' minutes'))] = 13;
}

// Construyamos la representacion de nuestro modelo virtual
foreach ($pos as $hora => $jugadores)
{
	if ($jugadores > 0)
		$cuerpo_tabla .= sprintf("<tr><td class='hora'>%s</td><td>%s</td></tr>", $hora, $jugadores);
}
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
		#contendor_tablas td{vertical-align:top;}
    </style>
</head>
<?php
echo '<strong>Tiempo encendida</strong> ';
$uptime = `cat /proc/uptime | awk '{ print $1}'`;
echo floor($uptime / 86400). ' días - ';
echo floor(($uptime % 86400) / 3600). ' horas - ';
echo floor((($uptime % 86400) % 3600) / 60). ' minutos';
echo ' | <strong>iNodes</strong> ';
echo `df -i | grep sda1 | awk '{print $5}'`;
echo ' | <strong>Blocks</strong> ';
echo `df -h | grep sda1 | awk '{print $5}'`;
echo ' | <strong>RIFT Server</strong> ';
echo $RIFT_server;
echo ' | <strong>Capturas de pantalla </strong> <a href="http://rift.zapto.org:81/SS/'.$fecha_sql.'">ver</a>';
?>
<h1>RIFT - Taquilla | <?php echo $fecha_sql; ?></h1>
<div><form action="./" method="GET">Ir a fecha [año-mes-día]:<input type="input" name="fecha" value="<?php echo $fecha_sql; ?>"/><input type="submit" value="Ir" /></form>
<h2>Cortes</h2>
<ul>
<?php
if ($handle = opendir('./PDF')) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && preg_match("/CZ-".$fecha_sql."_.*/",$file)) {
            echo '<li><a href="PDF/'.$file.'">'.$file.'</a></li>';
        }
    }
    closedir($handle);
}
?>
</ul>
<hr />
<h2>Herramientas</h2>
<ul>
<li><a href="./estadisticas.php?fecha=<?php echo $fecha_sql; ?>">Estadísticas</a></li>
<li><a href="./compras.php?fecha=<?php echo $fecha_sql; ?>">Detalle de compras</a></li>
<li><a href="./cafeteria.php?fecha=<?php echo $fecha_sql; ?>">Ventas de cafetería</a></li>
<li><a href="./pases.php?fecha=<?php echo $fecha_sql; ?>">Rastrear pases</a></li>
<li><a href="./movimientos.php?fecha=<?php echo $fecha_sql; ?>">Rastrear movimientos</a></li>
</ul>
<hr />
<?php
$c = "SELECT COALESCE((SELECT COALESCE(SUM(precio_grabado),0) FROM tickets WHERE DATE(fecha_vendido) = '".$fecha_sql."'),0) AS totalJuegos,  COALESCE((SELECT SUM(`eventos`.`precio_evento` + `eventos`.`precio_comida` + `eventos`.`precio_cafeteria`) FROM `rift3`.`eventos` WHERE DATE(`eventos`.`fecha_vendido`)='".$fecha_sql."'),0) AS totalEventos, COALESCE((SELECT SUM(precio_grabado*cantidad) FROM `cafeteria_transacciones` WHERE cancelado=0 AND DATE(`cafeteria_transacciones`.`fecha`) = '".$fecha_sql."'),0) AS totalCafeteria";
$r = db_consultar($c);
$f = mysql_fetch_assoc($r);
?>

<p>Dinero en caja + bouchers: <b>$<?php echo number_format($f['totalCafeteria'] + $f['totalJuegos'] + $f['totalEventos'],2,'.',','); ?></b></p>
<p class="diminuto"><strong>Total [JUEGO]</strong> $<?php echo $f['totalJuegos']; ?></p>
<p class="diminuto"><strong>Total [EVENTOS]</strong> $<?php echo $f['totalEventos']; ?></p>
<p class="diminuto"><strong>Total [CAFETERIA]</strong> $<?php echo $f['totalCafeteria']; ?></p>
<p class="diminuto">"Total t" es la suma del ingreso registrado en el dia.</p>
<hr />
<?php
$c = "SELECT COALESCE((SELECT COALESCE(SUM(precio_grabado),0) FROM tickets WHERE DATE(fecha_juego) = '".$fecha_sql."'),0) AS totalJuegos,  COALESCE((SELECT SUM(`eventos`.`precio_evento` + `eventos`.`precio_comida` + `eventos`.`precio_cafeteria`) FROM `rift3`.`eventos` WHERE DATE(`eventos`.`fecha_evento`)='".$fecha_sql."'),0) AS totalEventos, COALESCE((SELECT SUM(precio_grabado*cantidad) FROM `cafeteria_transacciones` WHERE cancelado=0 AND DATE(`cafeteria_transacciones`.`fecha`) = '".$fecha_sql."'),0) AS totalCafeteria";
$r = db_consultar($c);
$f = mysql_fetch_assoc($r)
?>
<p>Dinero generado por juegos este día: <b>$<?php echo number_format($f['totalCafeteria'] + $f['totalJuegos'] + $f['totalEventos'],2,'.',','); ?></b></p>
<p class="diminuto">"Total T" es la suma de ventas por juegos en este día.</p>
<hr />
<?php
$buffer_transacciones = '';
$c = "SELECT COUNT(*) AS 'cuenta', `descripcion`, precio_grabado, DATE(fecha_vendido) AS fecha_juego2 FROM `tickets` LEFT JOIN `tipo_boleto` USING(ID_tipo_boleto) WHERE DATE(fecha_juego) = '".$fecha_sql."' AND DATE(fecha_vendido) <> '".$fecha_sql."' GROUP BY CONCAT(ID_tipo_boleto,'+',precio_grabado,'+',DATE(fecha_vendido))";
$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $buffer_transacciones .= '<tr><td>'.$f["descripcion"].'</td><td>$'.$f["precio_grabado"].'</td><td><a href="http://rift.zapto.org:81/?fecha='.$f["fecha_juego2"].'">'.$f["fecha_juego2"].'</a></td><td>'.$f["cuenta"].'</td></tr>';
}
?>
<h2>Detalle tiquetes para juegos hoy [IMPRESOS OTRO DIA]</h2>
<table>
    <tr><th>Grupo</th><th>Precio</th><th>Fecha de venta</th><th>Cantidad</th></tr>
    <?php echo $buffer_transacciones; ?>
</table>
<hr />
<?php
$buffer_transacciones = '';
$c = "SELECT COUNT(*) AS 'cuenta', `descripcion`, precio_grabado, DATE(fecha_juego) AS fecha_juego2 FROM `tickets` LEFT JOIN `tipo_boleto` USING(ID_tipo_boleto) WHERE DATE(fecha_vendido) = '".$fecha_sql."' GROUP BY CONCAT(ID_tipo_boleto,'+',precio_grabado,'+',DATE(fecha_juego))";
$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $buffer_transacciones .= '<tr><td>'.$f["descripcion"].'</td><td>$'.$f["precio_grabado"].'</td><td><a href="http://rift.zapto.org:81/?fecha='.$f["fecha_juego2"].'">'.$f["fecha_juego2"].'</a></td><td>'.$f["cuenta"].'</td></tr>';
}
?>
<h2>Detalle tiquetes [IMPRESOS DURANTE ESTE DIA]</h2>
<table>
    <tr><th>Grupo</th><th>Precio</th><th>Para fecha de juego</th><th>Cantidad</th></tr>
    <?php echo $buffer_transacciones; ?>
</table>
<br />
<?php
$buffer_transacciones = '';
$c = "SELECT COUNT(*) AS 'cuenta', `descripcion`, precio_grabado, DATE(fecha_juego) AS fecha_juego2 FROM `tickets` LEFT JOIN `tipo_boleto` USING(ID_tipo_boleto) WHERE ID_tipo_boleto <> 11 AND DATE(fecha_juego) = '".$fecha_sql."' GROUP BY CONCAT(ID_tipo_boleto,'+',precio_grabado)";
$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $buffer_transacciones .= '<tr><td>'.$f["descripcion"].'</td><td>$'.$f["precio_grabado"].'</td><td>'.$f["cuenta"].'</td></tr>';
}
?>
<h2>Detalle tiquetes [PARA JUGAR ESTE DIA]</h2>
<table>
    <tr><th>Grupo</th><th>Precio</th><th>Cantidad</th></tr>
    <?php echo $buffer_transacciones; ?>
</table>
<br />
<?php
$buffer_transacciones = '';
$c = "SELECT COUNT(*) AS 'cuenta', `tipo` FROM `transacciones` WHERE DATE(fecha) = '".$fecha_sql."' GROUP BY `tipo`";
$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $buffer_transacciones .= '<tr><td>'.$f["tipo"].'</td><td>'.$f["cuenta"].'</td></tr>';
}
?>
<h2>Detalle transacciones</h2>
<table>
    <tr><th>Tipo</th><th>Cantidad</th></tr>
    <?php echo $buffer_transacciones; ?>
</table>
<br />
<?php
$buffer_eventos = '';
$c = "SELECT `patrocinado_por`, `fecha_evento`, `eventos`.`ID_evento`, (`eventos`.`precio_evento` + `eventos`.`precio_comida` + `eventos`.`precio_cafeteria`) AS precio_evento, infoContacto FROM `eventos` WHERE DATE(`eventos`.`fecha_vendido`)='".$fecha_sql."' ORDER BY `eventos`.`hora_inicio`";
$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $buffer_eventos .= '<tr><td>'.$f["patrocinado_por"].'</td><td>$'.$f["precio_evento"].'</td><td>'.$f["fecha_evento"].'</td><td>'.$f["infoContacto"].'</td></tr>';
}
?>
<?php if(mysql_num_rows($r)): ?>
<h2>Detalle eventos vendidos hoy</h2>
<table>
    <tr><th scope="Descripcion">Patrocinado por</th><th scope="Cantidad">Precio</th><th scope="Fecha">Fecha evento</th><th>Contacto</th></tr>
    <?php echo $buffer_eventos; ?>
</table>
<br />
<?php endif; ?>
<?php
$buffer_eventos = '';
$c = "SELECT hora_inicio, hora_final, `patrocinado_por`, `eventos`.`fecha_vendido`, `eventos`.`ID_evento`, (`eventos`.`precio_evento` + `eventos`.`precio_comida` + `eventos`.`precio_cafeteria`) AS precio_evento, (SELECT `nombre` FROM `usuarios` WHERE `ID_usuario`= `eventos`.`agregado_por_usuario`) AS 'nombre_usuario', infoContacto FROM `eventos` WHERE DATE(`eventos`.`fecha_evento`)='".$fecha_sql."' ORDER BY `eventos`.`hora_inicio`";
$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $buffer_eventos .= '<tr><td>'.$f["patrocinado_por"].'</td><td>$'.$f["precio_evento"].'</td><td>'.$f["fecha_vendido"].'</td><td>'.$f["nombre_usuario"].'</td><td>'.$f["hora_inicio"].'</td><td>'.$f["hora_final"].'</td><td>'.$f["infoContacto"].'</td></tr>';
}
?>
<?php if(mysql_num_rows($r)): ?>
<h2>Detalle eventos programados para hoy</h2>
<table>
    <tr><th scope="Descripcion">Patrocinado por</th><th scope="Cantidad">Precio</th><th scope="Fecha">Fecha vendido</th><th>Cajero</th><th>Hora inicio</th><th>Hora final</th><th>Contacto</th></tr>
    <?php echo $buffer_eventos; ?>
</table>
<br />
<?php endif; ?>

<table id="contendor_tablas"><tr><td>
<h2>Taquilla</h2>
<table>
    <tr><th>Hora</th><th>Jugadores</th></tr>
    <?php echo $cuerpo_tabla; ?>
</table>
</td><td>
<?php
$cuerpo_tabla = "";
if ($RIFT_encendida)
	$dbh = @ibase_connect("192.168.1.104:C:\Program Files\P&CMicros\Database\ZoneSystems.gdb", "SYSDBA", "masterke");
else
	$dbh = null;

if ($dbh){
$stmt = "SELECT CAST(a.GAME_TIMESTAMP AS TIME) AS \"hora\", a.EVENT_TYPE, a.GAME_ID, a.PROFILE_DESCRIPTION, a.DATA_1, a.DATA_2, a.DATA_3, a.DATA_4, a.DATA_5
FROM T2GAMELOG a WHERE DATA_4 > 0 AND CAST(a.GAME_TIMESTAMP AS DATE) = CAST('".$fecha_sql."' AS DATE) AND EVENT_TYPE=3";
$sth = ibase_query($dbh, $stmt);

while ($f = ibase_fetch_assoc($sth))
{
	$cuerpo_tabla .= sprintf("<tr><td>%s</td><td class='hora'>%s</td><td>%s</td><td>%s</td></tr>",$f["DATA_4"],$f["hora"],(($f["DATA_1"]) / 60)."m",'<a href="./jugadores.php?gi='.$f["GAME_ID"].'">'.$f["DATA_2"].'</a>');
}
?>
<h2>LOG</h2>
<table>
    <tr><th>Jugadores</th><th>Hora</th><th>Duración</th><th>En línea</th></tr>
    <?php echo $cuerpo_tabla; ?>
</table>
<?php } ?>
</td></tr></table>
<br />
<?php
if ($dbh){
$cuerpo_tabla = "";
$stmt = "SELECT CAST(a.GAME_TIMESTAMP AS TIME) AS \"hora\", a.EVENT_TYPE, a.GAME_ID, a.PROFILE_DESCRIPTION, a.DATA_1, a.DATA_2, a.DATA_3, a.DATA_4, a.DATA_5
FROM T2GAMELOG a WHERE DATA_4 > 0 AND CAST(a.GAME_TIMESTAMP AS DATE) = CAST('".$fecha_sql."' AS DATE) AND EVENT_TYPE=1";
$sth = ibase_query($dbh, $stmt);

while ($f = ibase_fetch_assoc($sth))
{
	$cuerpo_tabla .= sprintf("<tr><td class='hora'>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",$f["hora"],$f["DATA_4"],(($f["DATA_1"]) / 60)."m",$f["DATA_2"]);
}
?>
<h2>LOG [Abortados]</h2>
<table>
    <tr><th>Hora</th><th>Segundos para terminar</th><th>Duración</th><th>En línea</th></tr>
    <?php echo $cuerpo_tabla; ?>
</table>
<?php } ?>
<br />
<?php
$cuerpo_tabla = "";
$c = "SELECT COUNT(*) AS 'vendidos', DATE_FORMAT(`fecha_juego`,'%H:%i') AS 'hora' FROM `tickets` WHERE `tickets`.`ID_tipo_boleto` = 11 AND DATE(`fecha_juego`) = '".$fecha_sql."' GROUP BY DATE_FORMAT(`fecha_juego`,'%H:%i') ORDER BY DATE_FORMAT(`fecha_juego`,'%H:%i') ASC";
$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $cuerpo_tabla .= sprintf("<tr><td class='hora'>%s</td><td>%s</td></tr>",$f["hora"],$f["vendidos"]);
}
?>
<h2>Pases generados</h2>
<table>
    <tr><th>Hora</th><th>Pases generados</th></tr>
    <?php echo $cuerpo_tabla; ?>
</table>
<br />
<?php
$cuerpo_tabla = '';
$c = "SELECT
`pase_razon`.`ID_pase_razon`,
`pase_razon`.`fechatiempo`,
DATE_FORMAT(`pase_razon`.`fechatiempo`,'%H:%i') AS 'hora',
`pase_razon`.`razon`,
`pase_razon`.`cantidad`,
`pase_razon`.`precio`,
`pase_razon`.`caducidad`,
`pase_razon`.`ID_usuario`,
`pase_razon`.`pase_dias_valido`,
`usuarios`.`nombre`
FROM `pase_razon` LEFT JOIN `usuarios` USING(ID_usuario)
WHERE DATE(fechatiempo) = '$fecha_sql'";
$r = db_consultar($c);
while ($f = mysql_fetch_assoc($r)) {
    $cuerpo_tabla .= sprintf("<tr><td class='hora'>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",$f["hora"],$f["razon"],$f["cantidad"],$f["precio"],$f["pase_dias_valido"],$f["caducidad"],$f["nombre"]);
}
?>
<h2>Motivo de generacion de pases
<table>
    <tr><th>Hora</th><th>Razón</th><th>Cantidad</th><th>Precio c/u</th><th>Días válidos</th><th>Caducidad</th><th>Cajero</th></tr>
    <?php echo $cuerpo_tabla; ?>
</table>
<br />
</html>
