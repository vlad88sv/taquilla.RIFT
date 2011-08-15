<?php
define('__BASE__',str_replace('//','/',dirname(__FILE__).'/'));
define('__BASE__ARRANQUE',__BASE__.'../cePOSa/');
/*
$host = 'rift.zapto.org';
$query = `nslookup -timeout=4 -retry=1 $host`;
if(preg_match('/\nAddress: (.*)\n/', $query, $matches))
    $DB_IP = trim($matches[1]);
else
*/
    $DB_IP = '192.168.1.18';

define('db__host',$DB_IP);
define('db__usuario','root');
define('db__clave','RIFT');
define('db__db','rift3');

define('PROY_NOMBRE','Floristeria Flor360 - floristerias en El Salvador');
define('PROY_NOMBRE_CORTO','Floristeria Flor360');
define('PROY_TELEFONO','2514-0820 (Call center de Flor360.com, pedidos y atención al cliente)');
define('PROY_TELEFONO_PRINCIPAL','(+503) 2514-0820');
define('PROY_MAIL_POSTMASTER_NOMBRE','"Floristeria en El Salvador Flor360.com" ');
define('PROY_MAIL_POSTMASTER','<informacion@'.$_SERVER['HTTP_HOST'].'>');
define('PROY_MAIL_REPLYTO_NOMBRE','"Floristeria en El Salvador Flor360.com" ');
define('PROY_MAIL_REPLYTO','<cartero@'.$_SERVER['HTTP_HOST'].'>');
define('PROY_MAIL_BROADCAST_NOMBRE','"Floristeria en El Salvador Flor360.com" ');
define('PROY_MAIL_BROADCAST','<cartero@'.$_SERVER['HTTP_HOST'].'>');

define('FACEBOOK_APP_ID','348293355878');
define('GOOGLE_ANALYTICS','UA-12744164-1');
define('HEAD_KEYWORDS','regalos originales, regalos empresariales, navidad, flores a domicilio, envio de flores, san valentin, boda, regalos personalizados, ramos de flores, cumpleaños, promocionales, especiales, aniversario, romantico, cuadros de flores, para mujeres, corporativos, flores artificiales, regalos para bebes, flores secas');

// Mostrar o no el pie - necesario para la pagina de compras
$GLOBAL_MOSTRAR_PIE = true;

$HEAD_titulo = PROY_NOMBRE;
$HEAD_descripcion = 'Somos la mas destacada de las floristerias en El Salvador, enviamos a todo el pais de Lunes a Sabado. Aceptamos pedidos nacionales e internacionales, pague con tarjeta de credito o debito, deposito a cuenta o contraentrega. Ofrecemos regalos de arreglos florales, peluches, ramos florales, bouquet o ramo de novia, botonier para novio, decoracion y montaje de bodas, regalos para el dia de la madre y dia del padre.';

// Prefijo para tablas
define('db_prefijo','');
?>
