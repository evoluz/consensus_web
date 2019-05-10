<?php

include_once('db_config.php');
$con = false;
$conection_count = 0;

date_default_timezone_set('Europe/Madrid');



    
function db_connect()
{
	global $DATABASE, $USERNAME, $HOST, $PASSWORD, $con, $conection_count;
	$conection_count++;
	if (!$con)
	{
		$con = mysqli_connect($HOST,$USERNAME,$PASSWORD);// or die(mysqli_error());
		if ($con)
		{
			if (mysqli_select_db($con,$DATABASE)){
				mysqli_query($con, "SET NAMES 'utf8'");
				return $con;
			}else
                mysqli_close($con);
		}
		return false;
	}
	else
		return $con;
}

function db_execute($sql_text)
{
    global $con;
	return mysqli_query($con, $sql_text);
}

function db_query($sql_text)
{
    global $con;
	$ret_value = mysqli_query($con, $sql_text) or die("Error sql:".$sql_text."<br />".mysqli_error()) ;
	return $ret_value;
}

function db_query_next($recordset)
{
	return mysqli_fetch_array($recordset);
}

function db_close()
{
	global $con,$conection_count;
	if (--$conection_count == 0)
	{
		$ret_value = mysqli_close($con);
		$con = false;
		return $ret_value;

	}
	return true;
}

function db_free($recordset)
{
	mysqli_free_result($recordset);
}

function db_escape($unscaped_string)
{
    global $con;
	return iconv("UTF-8","UTF-8//IGNORE",mysqli_real_escape_string($con, $unscaped_string));
}



/*
	a quick way to do a select that returns only one value
	ex:
		$num_regs = db_quick_select("SELECT COUNT(*) FROM table");
		$name = db_quick_select("SELECT name FROM users WHERE id=2");
*/
function db_qselect($sql_text)
{
	$rec_tmp = db_query($sql_text);
	$tmp_array = db_query_next($rec_tmp);
	db_free($rec_tmp);
	if(is_array($tmp_array))
		return $tmp_array[0];
	else
		return $tmp_array;
}

// delete numeric keys // for db_aqselect
function dnkeys($ar){
	foreach($ar as $k => $v){
		if(is_numeric($k))
			unset($ar[$k]);
	}
	return $ar;
}

function dnkeys2D($ar){
	foreach($ar as $ka => $va){
		foreach($va as $k => $v){
			if(is_numeric($k))
				unset($ar[$ka][$k]);
		}
	}
	return $ar;
}

function db_aqselect($sql_text)
{
	$rec_tmp = db_query($sql_text);
	$tmp_array = db_query_next($rec_tmp);

	if(is_array($tmp_array)){
		$tmp_array = dnkeys($tmp_array);
	}
	db_free($rec_tmp);
	return $tmp_array;
}

function db_aselect($sql_text)
{
	$rec_tmp = db_query($sql_text);	
	$k=0;
	$ar = array();
	while($tmp_array = db_query_next($rec_tmp))
	{
		$ar[$k]=$tmp_array;
		$k++;
	}
	db_free($rec_tmp);
	$ar = dnkeys2D($ar);
	return $ar;
}

/*
	a quick way to do a select that returns an associative array were the key will be the first column and each value the row of that key
	NOTE: if the first column is not a key (or at least unique for that query) some values will be hidden
	ex:
		$dic = db_keyselect("SELECT id,name,address FROM table");
		
		example result:
		$dic =>
			[23] => array([0] => 23, [1] => 'mikel', [2] => 'shire', ['id'] => 23, ['name'] => 'mikel', ['address'] => 'shire' )
			[42] => array([0] => 42, [1] => 'io', [2] => 'neverland', ['id'] => 42, ['name'] => 'io', ['address'] => 'neverland' )
		
		
*/
function db_keyselect($sql_text)
{
	$rec_tmp = db_query($sql_text);	
	$ar = array();
	while($tmp_array = db_query_next($rec_tmp))
	{
		$ar[$tmp_array[0]]=$tmp_array;
	}
	db_free($rec_tmp);
	$ar = dnkeys2D($ar);
	return $ar;
}

function db_keys2array($sql_text)
{
	$rec_tmp = db_query($sql_text);	
	$ar = array();
	$i = 0;
	while($tmp_array = db_query_next($rec_tmp))
	{
		$ar[$i]=$tmp_array[0];
		$i++;
	}
	db_free($rec_tmp);
	return $ar;
}

function db_array($sql_text)
{
	$rec_tmp = db_query($sql_text);
	$ret = array();
	while($tmp_array = db_query_next($rec_tmp))
	{
			$ret[] = $tmp_array[0];
	}
	db_free($rec_tmp);
	if(sizeof($ret)==0)return false;
	return $ret;
}


function db_implode($glue,$sql_text)
{
	$rec_tmp = db_query($sql_text);
	$ret = false;
	while($tmp_array = db_query_next($rec_tmp))
	{
		if ($ret !== false)
			$ret .= $glue . $tmp_array[0];
		else
			$ret = $tmp_array[0];
	}
	db_free($rec_tmp);
	return $ret;
}
/*
	this insert tries 15 times to insert the value getting the next id value from the table (max+1) and returns that id if success or false otherwise
	
		$table 		- table name where the insert will be done
		$id_column 	- this is the name of the colunm where the next id will be inserted, this must be a numeric value (int, bigint...)
		$values		- this is an associative array with the column names as array keys and values as array values, the values must be put with '' if they are strings and must come escaped in this case (db_escape)
		
		- para añadir id a contenidos de otros campos busca el token ::id_column:: en $sql y lo reemplaza por el valor $new_id al hacer la inserción
		
		example:
			db_insert("some_table","id",array("name" => "'mikel'", "sex" => "'big'", "age" => 25));
*/
function db_insert($table,$id_column,$values)
{
	global $ar;
	$max_sql_text = "SELECT MAX($id_column) FROM $table";
	$insert_text1 = "INSERT INTO $table ($id_column,".implode(",",array_keys($values)).") VALUES (";
	$insert_text2 = ",'".implode("','",$values)."')";
	$new_id = 0;
	$tries_left = 15;
	do
	{
		$new_id = db_qselect($max_sql_text);
		if ($new_id)
			$new_id++;
		else
			$new_id = 1;
		$tries_left--;
		$sql = $insert_text1.$new_id.$insert_text2;
		$sql = str_ireplace("::id_column::",$new_id,$sql);
	}while($new_id && $tries_left > 0 && !db_execute($sql));
	$ar['insert_sql'] .=  $insert_text1.$new_id.$insert_text2;
	if ($tries_left > 0 && $new_id)
		return $new_id;
	else
		return false;
}


function db_table_exist($table_name)
{
	return db_qselect("SHOW TABLES LIKE '".db_escape($table_name)."'");
}

function db_column_exist($table_name,$column_name)
{
	$column_name = db_escape($column_name);
	if (db_table_exist($table_name))
		return db_qselect("SHOW COLUMNS FROM $table_name LIKE '$column_name'");
	else
		return false;
}


// aqui se hace la llamada para conectar a la base de datos por defecto al hacer include de la libreria db_utils.php

db_connect();



?>