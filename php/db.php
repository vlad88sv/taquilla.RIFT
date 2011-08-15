<?php
$db_link = NULL;
static $db_contador;
db_conectar(); // Iniciamos la conexión a la base de datos.

function db_conectar(){
    global $db_link;
    $db_link = @mysql_connect(db__host, db__usuario, db__clave) or die("Fue imposible conectarse a la base de datos, posiblemente no ha ejecutado el instalador (instalar.php) correctamente.<br /><hr />Detalles del error:<pre>" . mysql_error() . "</pre>");
    mysql_select_db(db__db, $db_link) or die("Imposible seleccionar la base de datos: ". mysql_error());
    mysql_query("set lc_time_names='es_SV'", $db_link);
    mysql_query("SET NAMES 'utf8'", $db_link);
    mysql_query("SET GLOBAL group_concat_max_len=4294967295", $db_link);
}

function db_consultar($consulta){
    global $db_link;
    global $db_contador;
    if ( !$db_link ) {
        db_conectar();
    }
    DEPURAR($consulta,0);
    $resultado = @mysql_query($consulta, $db_link);
    if ( mysql_error($db_link) ) {
        echo '<pre>MySQL:' . mysql_error() . '</pre>';
    }
    $db_contador++;
    return $resultado;

}
function db_codex($datos){
    global $db_link;
    if ( !$db_link ) {
        db_conectar();
    }
    if (is_array($datos))
    {
        foreach($datos as $valor)
        {
            $arr[] = db_codex($valor);
        }
        return $arr;
    }
    else
    {
        return mysql_real_escape_string($datos, $db_link);
    }
}
function db_afectados(){
    global $db_link;
    if ( $db_link ) {
        return mysql_affected_rows($db_link);
    }
    return -1;
}
function db_crear_tabla($tabla, $campos, $botarPrimero=false){
    $salida = "";
    if ( $botarPrimero ) {
        if ( db_consultar ("DROP TABLE IF EXISTS $tabla") ) {
            $salida .= "Tabla '$tabla' botada"."<br />";
        } else {
            $salida .= "Tabla '$tabla' no pudo ser botada"."<br />";
        }
    }
    if ( db_consultar ("CREATE TABLE IF NOT EXISTS $tabla ($campos)") ) {
        $salida .= "Tabla '$tabla' creada"."<br />";
        $c = "explain $tabla";
        $resultado = db_consultar($c);
        $salida .= db_ui_tabla($resultado,"",true,"¡oops!, ¡parece que no se creó!");
    } else {
        $salida .= "Tabla '$tabla' no pudo ser creada"."<br />";
    }
    return $salida;
}

function db_agregar_datos($tabla, $datos) {
    global $db_link;
    $campos = $valores = NULL;
    foreach ($datos as $clave => $valor) {
        //echo "clave: $clave; valor: $valor<br />\n";
        $arr_campos[]   = mysql_real_escape_string($clave);
        $arr_valores[]  = mysql_real_escape_string($valor);
    }
    $campos = implode (",", $arr_campos);
    $valores = "'".implode ("','", $arr_valores)."'";
    $c = "INSERT INTO $tabla ($campos) VALUES ($valores)";
    $resultado = db_consultar ($c);
    $id = @mysql_insert_id ($db_link);
    DEPURAR ($c, 0);
    return $id;
}

function db_reemplazar_datos($tabla, $datos) {
    global $db_link;
    $campos = $valores = NULL;
    foreach ($datos as $clave => $valor) {
        //echo "clave: $clave; valor: $valor<br />\n";
        $arr_campos[]   = mysql_real_escape_string($clave);
        $arr_valores[]  = mysql_real_escape_string($valor);
    }
    $campos = implode (",", $arr_campos);
    $valores = "'".implode ("','", $arr_valores)."'";
    $c = "REPLACE INTO $tabla ($campos) VALUES ($valores)";
    $resultado = db_consultar ($c);
    $id = @mysql_insert_id ($db_link);
    DEPURAR ($c, 0);
    return $id;
}

function db_actualizar_datos($tabla, $datos, $donde = "0") {
    global $db_link;
    $DATA = NULL;
    foreach ($datos as $clave => $valor) {
        $arr_DATA[] = mysql_real_escape_string($clave) . "='".mysql_real_escape_string($valor)."'";
    }
    $DATA = join(",",$arr_DATA);
    $c = "UPDATE $tabla SET $DATA WHERE $donde";
    $resultado = db_consultar ($c);
    $id = @mysql_affected_rows();
    DEPURAR ($c, 0);
    return $id;
}

function db_resultado($resultado, $campo, $posicion='0'){
    return @mysql_result($resultado, $posicion, $campo);
}

function db_fila_a_array($resultado, $posicion='0'){
 $_arr = NULL;
 $n_campos = mysql_num_fields($resultado);
 $r = mysql_fetch_row($resultado);
 for ($i = 0; $i < $n_campos; $i++) {
     $clave = mysql_field_name($resultado, $i);
     $valor = $r[$i];
     $_arr[$clave] = $valor;
 }
 return $_arr;
}

function db_contar($tabla,$where="1")
{
    $c = "SELECT COUNT(*) AS cuenta FROM $tabla WHERE $where";
    $r = db_consultar($c);
    $f = mysql_fetch_array($r);
    return $f['cuenta'];
}

function db_obtener($tabla,$campo,$where)
{
	$c ="SELECT $campo AS 'resultado' FROM $tabla WHERE $where LIMIT 1";
	$r = db_consultar($c);
	$f = mysql_fetch_array($r);
	return $f['resultado'];
}
?>
