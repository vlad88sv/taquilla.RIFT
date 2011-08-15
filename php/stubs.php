<?php

// Genera un simple contenedor JavaScript
function JS($script){
    return "<script type='text/javascript'>".$script."</script>";
}

// Genera un simple contenedor JavaScript para JQuery ON DOM READY
function JS_onload($script){
    return "<script type='text/javascript'>$(document).ready(function(){".$script."});</script>";
}

// Genera un pequeño GROWL
function JS_growl($mensaje){
    return "$.jGrowl('".addslashes($mensaje)."', { sticky: true });";
}

function suerte($una, $dos){
    if (rand(0,1)) {
        return $una;
    } else {
        return $dos;
    }
}

function Truncar($cadena, $largo) {
    if (strlen($cadena) > $largo) {
        $cadena = substr($cadena,0,($largo -3));
            $cadena .= '...';
    }
    return $cadena;
}


function _F_form_cache($campo)
{
    if (!isset($_POST))
        return '';
    if (array_key_exists($campo, $_POST))
    {
        return $_POST[$campo];
    }
    else
    {
        return '';
    }
}

// http://www.linuxjournal.com/article/9585
function validcorreo($correo)
{
   $isValid = true;
   $atIndex = strrpos($correo, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($correo, $atIndex+1);
      $local = substr($correo, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}


function scaleImage($x,$y,$cx,$cy) {
    //Set the default NEW values to be the old, in case it doesn't even need scaling
    list($nx,$ny)=array($x,$y);

    //If image is generally smaller, don't even bother
    if ($x>=$cx || $y>=$cx) {

        //Work out ratios
        if ($x>0) $rx=$cx/$x;
        if ($y>0) $ry=$cy/$y;

        //Use the lowest ratio, to ensure we don't go over the wanted image size
        if ($rx>$ry) {
            $r=$ry;
        } else {
            $r=$rx;
        }

        //Calculate the new size based on the chosen ratio
        $nx=intval($x*$r);
        $ny=intval($y*$r);
    }

    //Return the results
    return array($nx,$ny);
}
function Imagen__Redimenzionar($Origen, $Ancho = 640, $Alto = 480)
{
    $im=new Imagick($Origen);

    $im->setImageColorspace(255);
    $im->setCompression(Imagick::COMPRESSION_JPEG);
    $im->setCompressionQuality(80);
    $im->setImageFormat('jpeg');

    list($newX,$newY)=scaleImage($im->getImageWidth(),$im->getImageHeight(),$Ancho,$Alto);
    $im->scaleImage($newX,$newY,true);
    return $im->writeImage($Origen);
}

/*
 * Imagen__CrearMiniatura()
 * Crea una versión reducida de la imagen en $Origen
*/
function Imagen__CrearMiniatura($Origen, $Destino, $Ancho = 100, $Alto = 100)
{
    $im=new Imagick($Origen);

    $im->setImageColorspace(255);
    $im->setCompression(Imagick::COMPRESSION_JPEG);
    $im->setCompressionQuality(80);
    $im->setImageFormat('jpeg');

    list($newX,$newY)=scaleImage($im->getImageWidth(),$im->getImageHeight(),$Ancho,$Alto);
    $im->thumbnailImage($newX,$newY,false);
    return $im->writeImage($Destino);
}

function SEO($URL){
    $URL = preg_replace("`\[.*\]`U","",$URL);
    $URL = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$URL);
    $URL = htmlentities($URL, ENT_COMPAT, 'utf-8');
    $URL = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $URL );
    $URL = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $URL);
    return strtolower(trim($URL, '-')).".html";
}
// http://www.webcheatsheet.com/PHP/get_current_page_url.php
// Obtiene la URL actual, $stripArgs determina si eliminar la parte dinamica de la URL
function curPageURL($stripArgs=false,$friendly=false) {
$pageURL = '';
if (!$friendly)
{
   $pageURL = 'http';
   if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
   $pageURL .= "://";
}

if ($_SERVER["SERVER_PORT"] != "80") {
   $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
} else {
   $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
}

if ($stripArgs) {$pageURL = preg_replace("/\?.*/", "",$pageURL);}

if ($friendly)
{
    $pageURL = preg_replace('/www\./', '',$pageURL);
    $pageURL = "www.$pageURL";
}

return $pageURL;
}

function URL_ObtenerReferencia()
{
    return $_SERVER["REQUEST_URI"];
}

// Wrapper de envío de correo electrónico. HTML/utf-8
function correo($para, $asunto, $mensaje,$exHeaders=null)
{
    $headers = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=UTF-8' . "\r\n";
    $headers .= 'From: '. PROY_MAIL_POSTMASTER_NOMBRE .' '. PROY_MAIL_POSTMASTER . "\r\n";
    if (!empty($exHeaders))
    {
        $headers .= $exHeaders;
    }
    $mensaje = sprintf('<html><head><title>%s</title></head><body>%s</body>',PROY_NOMBRE,$mensaje);
    return mail($para,'=?UTF-8?B?'.base64_encode($asunto).'?=',$mensaje,$headers);
}

function correo_x_nivel($nivel, $asunto, $mensaje)
{
    $c = sprintf('SELECT correo FROM %s WHERE nivel=%s', db_prefijo.'usuarios', $nivel);
    $r = db_consultar($c);
    while ($f = mysql_fetch_array($r)) {
        correo($f['correo'],PROY_NOMBRE." - $asunto",$mensaje);
    }
}

function correo_x_interes($asunto, $mensaje)
{
    $c = sprintf('SELECT `correo` FROM %s ORDER BY fecha ASC LIMIT 1', db_prefijo.'correo_oferta');
    $r = db_consultar($c);
    while ($f = mysql_fetch_assoc($r)) {
        correo($f['correo'],PROY_NOMBRE." - $asunto",$mensaje);
        echo $f['correo'].'<br>';
    }
}

function HEAD_JS()
{
    global $arrJS;
    require_once (__BASE_cePOSa__.'PHP/jsmin-1.1.1.php');
    echo "\n";
    $buffer = '';
    foreach ($arrJS as $JS)
    {
        //$buffer .= '<script type="text/javascript">'.JSMin::minify(file_get_contents("JS/".$JS.".js"))."</script>\n";
        $buffer .= '<script type="text/javascript" src="JS/'.$JS.'.js"></script>'."\n";
    }

    echo $buffer;
    echo "\n";
}

function HEAD_CSS()
{
    global $arrCSS;
    $buffer = '';
    foreach ($arrCSS as $CSS)
    {
        //$buffer .= '<style type="text/css">'.file_get_contents($CSS.".css")."</style>\n";
        //$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        //$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

        $buffer .= '<link rel="stylesheet" type="text/css" href="'.$CSS.'.css" />'."\n";

    }
    echo $buffer;
    echo "\n";
}

function HEAD_EXTRA()
{
    global $arrHEAD;
    echo "\n";
    echo implode("\n",$arrHEAD);
    echo "\n";
}

function SI_ADMIN($texto)
{
    if (_F_usuario_cache('nivel') == _N_administrador)
    {
        return $texto;
    }
}

// Obtener las opciones
function CargarOpciones()
{
    global $OPCIONES_GLOBALES;
    $OPCIONES_GLOBALES = array();
    $c = sprintf('SELECT campo, valor FROM %s', db_prefijo.'opciones');
    $r = db_consultar($c);
    while ($f = mysql_fetch_assoc($r))
        $OPCIONES_GLOBALES[$f['campo']] = $f['valor'];
}

function opcion($campo, $defecto = ''){
    global $OPCIONES_GLOBALES;

    if (empty ($OPCIONES_GLOBALES[$campo]))
    {
        return $defecto;
    } else {
        return $OPCIONES_GLOBALES[$campo];
    }
}

function escribir_opcion($campo, $valor)
{
    $c = sprintf('REPLACE INTO %s SET campo="%s", valor="%s"', db_prefijo.'opciones', db_codex($campo), db_codex($valor));
    $r = db_consultar($c);
    CargarOpciones();
    return db_afectados();
}

function protegerme($solo_salir=false,$niveles=array())
{

    if (_F_usuario_cache('nivel') == _N_administrador || in_array(_F_usuario_cache('nivel'),$niveles))
        return;

    if (!$solo_salir)
        header('Location: '. PROY_URL.'iniciar?ref='.curPageURL());
    ob_end_clean();
    exit;
}

function cargar_editable($archivo,$link=true,$noMCE=false,$include=true)
{
    if ($include)
        include(__BASE__.'/TXT/'.$archivo.'.editable');
    else
        readfile(__BASE__.'/TXT/'.$archivo.'.editable');

    if ($noMCE)
        $archivo = $archivo.'&noMCE=1';

    if ($link)
        echo SI_ADMIN('<div style="clear:both;display:block;margin:10px 0"><a class="btnlnk" href="'.PROY_URL.'editar?archivo='.$archivo.'">~editar</a></div>');
}

/**
 * http://www.php.net/manual/en/function.unlink.php#87045
 * Recursively delete a directory
 *
 * @param string $dir Directory name
 * @param boolean $deleteRootToo Delete specified top-level directory as well
 */
function unlinkRecursive($dir, $deleteRootToo)
{
    if(!$dh = @opendir($dir))
    {
        return;
    }
    while (false !== ($obj = readdir($dh)))
    {
        if($obj == '.' || $obj == '..')
        {
            continue;
        }

        if (!@unlink($dir . '/' . $obj))
        {
            unlinkRecursive($dir.'/'.$obj, true);
        }
    }

    closedir($dh);

    if ($deleteRootToo)
    {
        @rmdir($dir);
    }

    return;
}

function __regexar($regex)
{
    return '/'.$regex.' /i';
}

/*Tweeter*/
function tweet($status)
{
    $username = 'flor360';
    $password = '22436017';

    if (!$status)
        return false;

    $tweetUrl = 'http://www.twitter.com/statuses/update.xml';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "$tweetUrl");
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "status=$status");
    curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");

    $result = curl_exec($curl);
    $resultArray = curl_getinfo($curl);

    echo $result;

    curl_close($curl);

    return ($resultArray['http_code'] == 200);
}

function imagen_URL($HASH, $ancho, $alto, $servidor=null)
{
    if (!(defined('_B_FORZAR_SERVIDOR_IMG_NULO')) && ($servidor === null))
    {
            $servidor = 'img'.substr(hexdec(substr($HASH,0,2)),-1,1).'.';
    }
    else
    {
        $servidor = '';
    }

    DEPURAR('TESTER:'.dirname($_SERVER['REQUEST_URI']),false);
    $URI = dirname($_SERVER['REQUEST_URI'].' ');
    return 'http://'.preg_replace(array("/\/?$/","/www./",'/\/\//'),array('','','/'),$servidor.$_SERVER['HTTP_HOST'].$URI."/imagen_".$ancho.'_'.$alto.'_'.$HASH.'.jpg');
}
?>
