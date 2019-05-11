<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Cache-Control, Accept, Origin, X-Session-ID');
header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE');
header('Access-Control-Allow-Credentials: false');
header('Access-Control-Max-Age: 86400');

session_start();

// JUNTAR GET Y POST en P
$P = $_POST;
$P = array_merge($_GET,$P);

function esc($s){
    global $P;
    return db_escape($P[$s]);
}

function com($s){
    return "'".$s."'";
}

function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

if($_SERVER['REQUEST_METHOD'] == 'PUT') {
    //parse_str(file_get_contents("php://input"),$_PUT);
    $_PUT = file_get_contents("php://input");
    if(isJson($_PUT)){
        $_PUT = json_decode($_PUT, true);
    }
}


//@ob_start("jsonp");

/***************************/
include_once('lib/db_utils.php');

$response = array("message"=>"error 400.275.123");

$urlbase = "/api";
$uri = explode("?", $_SERVER["REQUEST_URI"]);
$path = explode("/", str_replace($urlbase,"",$uri[0]));

$info = array();

$info["uuid"] = $_SERVER['HTTP_UUID'];

$info["method"] = $_SERVER['REQUEST_METHOD'];
$info["uri"] = $uri;
$info["path"] = $path;
$info["_GET"] = $_GET;
$info["_POST"] = $_POST;
$info["_PUT"] = $_PUT;
$info["_SESSION"] = $_SESSION;

/* LOG */
$fp = fopen('log/'.date("Y-m-d_H.i.s").'_'.implode("_",$path).'.txt', 'w');
fwrite($fp, print_r($info,true));
fclose($fp);
/* LOG */


switch($path[1]){
    /**
     * CLIENTES
     */
    case "document":{
        //if($_SESSION["user_id"]>0)
        switch($path[2]){
            case "active_document":{
                $active_id = 1; //db_qselect()
                $sql_document = "SELECT * FROM documents WHERE id=".com($active_id);
                $document = db_aqselect($sql_document);
        
                $sql_articles = "SELECT * FROM articles WHERE document_id=".com($active_id)." ORDER BY arrange ASC, id ASC";
                $articles = db_aselect($sql_articles);
        
                $document["articles"] = $articles;
                
        
                $response['data'] =  $document;
                $response['message'] = "ok"; 
                $response = $document;
            }break;
            case "vote":{
                if(!($_SESSION["user_id"]>0)) $_SESSION["user_id"] = db_qselect("SELECT id FROM users WHERE token LIKE ".com($info["uuid"]));
                $_POST["user_id"] = $_SESSION["user_id"];
                foreach($_POST as $k=>$v){
                    if($v=="true")$_POST[$k]="1";
                    if($v=="false")$_POST[$k]="0";
                }
                db_insert("documents_validation","id",$_POST);
                $data = array("voted"=>"true");
                $response['data'] = $data;
                $response['message'] = "ok"; 
                $response = $data;

                /* volver a enviar el documento */
                $active_id = 1; //db_qselect()
                $sql_document = "SELECT * FROM documents WHERE id=".com($active_id);
                $document = db_aqselect($sql_document);
        
                $sql_articles = "SELECT * FROM articles WHERE document_id=".com($active_id)." ORDER BY arrange ASC, id ASC";
                $articles = db_aselect($sql_articles);
        
                $document["articles"] = $articles;
                $document["voted"] = true;
                
        
                $response['data'] =  $document;
                $response['message'] = "ok"; 
                if($_GET["u"]!="io") $response = $document;
                }break;
        }
    }break;

    case "user" : {
        switch($path[2]){
            case "login":{
                //print_r($header);
                $sql = "SELECT * FROM users WHERE ncolegiado LIKE ".com($_POST["user"])." AND pass LIKE ".com(md5($_POST["pass"]));
                $user = db_aqselect($sql);
                unset($user["pass"]);
                $response = $user;
            }break;
        }
    }break;
    /* 
    case "clientes" : {
        switch($path[2]){
            case "add":{
                $response['data'] = db_insert("clientes","id",$_POST);
                $response['message'] = "ok";
            }break;
            case "update":{
                $bindParams = array();
                foreach($_PUT as $k=>$v){
                    $bindParams[] = " ".$k."='".$v."'";
                }
                $sqlParams = implode(",",$bindParams);
                $response['data'] = db_aselect("UPDATE clientes SET $sqlParams WHERE id=".com($path[3]));
                $response['message'] = "ok";
            }break;
            case "delete":{
                $response['data'] = db_aselect("DELETE FROM clientes WHERE id=".com($path[3]));
                $response['message'] = "ok";
            }break;
            default:{
                if(strlen($path[2])>0){
                    if(is_numeric($path[2])){
                        $response['data'] = db_aselect("SELECT * FROM clientes WHERE id=".com($path[2]));
                        $response['message'] = "ok";
                    }
                }else{
                        $response['data'] = db_aselect("SELECT * FROM clientes");
                        $response['message'] = "ok";
                }
            }
        }
    }break;
 */
}


switch($P["function"]){
    
    case "clientes" : {
        $response['data'] = db_aselect("SELECT * FROM clientes");
        $response['message'] = "ok";
    }break;
    case "cliente" : {
        $response['data'] = db_aselect("SELECT * FROM clientes WHERE id='".$_GET["id"]."'");
        $response['message'] = "ok";
    }break;
    case "addClientes" : {
        $response['data'] = db_insert("clientes","id",$_POST);
        $response['message'] = "ok";
    }break;


}

//$response["info"] = $info;
//$response["session"] = print_r($_SESSION);


//@ob_end_clean();
/***************************/


if(strlen($callback)>0){
    header("Content-Type:application/javascript; charset=utf-8");
    echo $callback . '(' . json_encode($response) . ')';
}else{
    header("Content-Type:application/json");
    echo json_encode($response);
}


?>