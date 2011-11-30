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
<h1>Rastreo de movimientos</h1>
<?php
$tiquetes = array();
$c = 'SELECT * FROM tickets WHERE (ID_pase IN (SELECT ID_ticket FROM tickets WHERE ID_tipo_boleto=4) AND DATE(fecha_juego) = "'.$fecha_sql.'") OR (ID_ticket IN (SELECT ID_pase FROM tickets WHERE (ID_pase IN (SELECT ID_ticket FROM tickets WHERE ID_tipo_boleto=4) AND DATE(fecha_juego) = "'.$fecha_sql.'"))) ORDER BY fecha_vendido DESC';
$r = db_consultar($c);
// Primero carguemolos todos en un array asociativo, con clave = ID_ticket
while ($f = mysql_fetch_assoc($r))
    $tiquetes[$f['ID_ticket']] = array('ID_ticket' => $f['ID_ticket'], 'ID_pase' => $f['ID_pase'], 'fecha_juego' => $f['fecha_juego'], 'fecha_vendido' => $f['fecha_vendido']);

foreach ($tiquetes AS $tiquete => $datos)
{       
    if ($datos['ID_pase'] > 0)
    {
	$tiquetes[$datos['ID_pase']]['movimiento'] = $tiquetes[$tiquete];
	unset($tiquetes[$tiquete]);
    }
}


reset($tiquetes);

foreach ($tiquetes AS $tiquete => $datos)
{
    $profundidad = 0;
    $continuar = true;

    $tmp = $datos;
    
    while ($continuar)
    {
	echo str_repeat("·", $profundidad);
	echo "#".$tmp['ID_ticket'].'[V:'.$tmp['fecha_vendido'].'][J:'.$tmp['fecha_juego'].'][P:'.$tmp['ID_pase'].']<br />'."\n";
	
	if (isset($tmp['movimiento']))
	    $tmp = $tmp['movimiento'];
	else
	    $continuar = false;
	
	$profundidad++;
    }    
}

?>
</body>
</html>