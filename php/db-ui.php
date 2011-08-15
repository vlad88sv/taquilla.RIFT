<?PHP
function db_ui_opciones($clave, $valor, $tabla, $cuales="", $orden="", $grupo_ui="", $seleccionada="") {
 $html = NULL;
 //La función es crear un combobox con name=id=$clave y value=$valor y HTML a partir de un SELECT $clave, $valor FROM $tabla
 $c = "SELECT $clave, $valor FROM $tabla $cuales $orden";
 DEPURAR ($c, 0);
 $resultado = db_consultar ($c);
 $n_campos = mysql_num_rows($resultado);
 if ( $grupo_ui ) {
    $html .= "<optgroup label='$grupo_ui'>";
 }
for ($i = 0; $i < $n_campos; $i++) {
  $t_clave = mysql_result($resultado, $i, $clave);
  $t_valor = mysql_result($resultado, $i, $valor);
  if ($t_clave == $seleccionada) {
      $selected = ' selected="selected"';
  } else {
      $selected = "";
  }
  $html .= '<option value="' . $t_clave . '"' . $selected . '>' . $t_valor . '</option>';
}
return $html;
}

function db_ui_lista($resultado)
{
 $html = NULL;
 $html .= "<table>";
 while ($r = mysql_fetch_row($resultado)) {
    foreach ($r as $column)
    {
    $html .= "<tr><td>$column</td></tr>";
    }
 }
  $html .= "</table>";
 return $html;
}

function db_ui_tabla($resultado, $CLASE="", $Titulos=true, $NoMas = "No hay datos") {
 global $db_link;
 if ( !mysql_num_rows($resultado) ) {
    return $NoMas;
 }

 $table = "";
 $table .= "<table $CLASE>\n";
 $noFields = mysql_num_fields($resultado);
 if ($Titulos)
 {
 $table .= "<tr>";
 for ($i = 0; $i < $noFields; $i++) {
 $field = mysql_field_name($resultado, $i);
 $table .= "<th>$field</th>\n";
 }
 $table .= "</tr>\n";
 }
 while ($r = mysql_fetch_row($resultado)) {
 $table .= "<tr>";
 foreach ($r as $column) {
 $table .= "<td>$column</td>";
 }
 $table .= "</tr>\n";
 }
 $table .= "</table>";
 return $table;
 }

function db_ui_tabla_vertical($resultado, $CSS="") {
 global $db_link;
 if ( !mysql_num_rows($resultado) ) {
    return "No se encontraron datos";
 }

 $table = "";
 $table .= "<table $CSS>\n\n";
 $noFields = mysql_num_fields($resultado);
 $r = mysql_fetch_row($resultado);

 for ($i = 0; $i < $noFields; $i++) {
 $field = mysql_field_name($resultado, $i);
 $table .= "<tr>";
 $table .= "<td>$field</td><td>".$r[$i]."</td>";
 $table .= "</tr>\n";
 }
 return $table;
 }

//Obtiene TODA la tabla y dibuja la jerarquia.
//No utilizar con tablas muy grandes.
function db_ui_jerarquia($tabla, $padre, $dato)
{
$c = "SELECT $padre, $dato FROM $tabla";
$resultado = db_consultar($c);

}

function db_ui_checkboxes($guid, $tabla, $valor, $texto, $explicacion, $default = array(), $extra="", $where="1")
{
    $c = "SELECT $valor, $texto, $explicacion FROM $tabla WHERE $where";
    $r = db_consultar($c);
    $html = '';
    if (is_array($default)) $arr = array_flip($default); else $arr = array();
    while ($row = mysql_fetch_array($r)) {
        $strDefault = isset($arr[$row[$valor]]) ? "checked=\"checked\"" : "";
        $html .= "<span title='".$row[$explicacion]."'>" . ui_input($guid, $row[$valor], "checkbox","","",$strDefault. " " . $extra) . $row[$texto] . "</span><br />";
    }
    return $html;
}

function db_ui_checkboxes_auto($guid, $tabla, $valor, $texto, $explicacion, $prueba, $extra="", $where="1")
{
    $c = "SELECT $valor, $texto, $explicacion, $prueba AS 'chequear' FROM $tabla WHERE $where";
    $r = db_consultar($c);
    $html = '';
    while ($row = mysql_fetch_array($r)) {
        $strDefault = $row['chequear'] == 1 ? "checked=\"checked\"" : "";
        $html .= "<span title='".$row[$explicacion]."'>" . ui_input($guid, $row[$valor], "checkbox","","",$strDefault. " " . $extra) . $row[$texto] . "</span><br />";
    }
    return $html;
}
?>
