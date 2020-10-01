<?php

/**
 *this covers database connections and CRUD operations
*
*
* 
 */


class DbConn
{
  
  /*   
  protected $db = 'cyprexco_sensei';
  protected $user = 'cyprexco_user1';
  protected $pass = '0364Martin@';
    */
 
  protected $db = 'quora';
  protected $user = 'root';
  protected $pass = '';
  /**/
  
  protected $dsn = 'mysql:host=$host;dbname=$db;charset=$charset';
  protected $host = 'localhost';
  protected $charset = 'utf8';
  protected $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
private static $pdo;
function __construct(){
$this->connect();
self::$pdo = $this->get_connection();
}

private function connect() {
try {
$this->dbHandle = new PDO("mysql:host=$this->host;dbname=$this->db", $this->user, $this->pass);
$this->dbHandle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// always disable emulated prepared statement when using the MySQL driver
$this->dbHandle->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

return $this->dbHandle;

}//conncect


private function get_connection() {
        return $this->dbHandle;
    }//getConn


public function loadVals($load) {
$vals = 
$cols =  
$format =  
$icl = 
$joiner = 
$gluer=''; 
$bind = array();
$o = 0;
foreach ($load as $key => $value) {
  $vals .= '"'.$value.'",';//'"'.$vr[0].'",';
  $cols .= $key.',';
  $icl .= '?,';
  $joiner .= ($o == 0) ? " $key = ? ": " AND $key = ? ";
  $gluer .= ($o == 0) ? " $key = ? ": " , $key = ? ";
  $bind[] = $value;
  $o++;
}
//
$cols = rtrim($cols,",");
$icl = rtrim($icl,",");
$vals = rtrim($vals,",");
//$joiner = rtrim($joiner,",");
$types = str_repeat("s", count($load));
$arr = array(
    'placeholders' => $icl, 
    'values' => $vals, 
    'columns' => $cols, 
    'joiner' => $joiner, 
    'gluer' => $gluer, 
    'bind' => $bind, 
    'types' => $types
);

return $arr;

}//


public function insertDb($load,$tbl,$conn='pdo') {
//$conn = (isset($conn)) ? $conn : 'pdo';
$params = $load;
$lds = $this->loadVals($load);
$cols = $lds['columns'];
$icl = $lds['placeholders'];
$bind = $lds['bind'];
//
$sql = " INSERT INTO $tbl ($cols) VALUES ($icl) ";
if($conn == 'pdo'){

try{
$stmt = self::$pdo->prepare($sql);
$stmt->execute($bind);
$id = self::$pdo->lastInsertId();
$response = array(
  'pdo' => json_encode($stmt), 
  'code' => 200,
  'lastInsertId' => $id,
  'message' => 'success'
);
} catch (PDOException $e) {
$response = array(
  'code' => $e->getCode(), 
  'message' => $e->getMessage()
);
} catch (Exception $e) {
$response = array(
  'code' => $e->getCode(), 
  'message' => $e->getMessage()
);
}
return $response;

}


}//insertDb





public function executeSql($sql,$vals) {
try {
$stmt = self::$pdo->prepare($sql);
$stmt->execute($vals);
//$row = $stmt->fetch();
$response = array( 
  'code' => 200,  
  'data' => true, 
  'sql' => $sql, 
  'vals' => $vals, 
  'message' => 'success'
);
return $response;
} catch (PDOException $e) {
$response = array(
  'code' => $e->getCode(),
  'data' => false, 
  'sql' => $sql,  
  'message' => $e->getMessage()
);
return $response;
} catch (Exception $e) {
$response = array(
  'code' => $e->getCode(),
  'data' => false, 
  'sql' => $sql,  
  'message' => $e->getMessage()
);
return $response;
}



}//executeSql



public function isExists($load, $tbl) {
$lds = $this->loadVals($load);
$cols = $lds['columns'];
$joiner = $lds['joiner'];
$bind = $lds['bind'];
$sql = " SELECT 1 FROM $tbl WHERE $joiner  LIMIT 1 ";
try {
$stmt = self::$pdo->prepare($sql);
$stmt->execute($bind);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$response = array(
  'data' => $row, 
  'code' => 200,
  'stmt' => $stmt, 
  'message' => 'success'
);
} catch (PDOException $e) {
$response = array(
  'data' => false,
  'code' => $e->getCode(), 
  'message' => $e->getMessage()
);
} catch (Exception $e) {
$response = array(
  'data' => false,
  'code' => $e->getCode(), 
  'message' => $e->getMessage()
);
}
return $response;
}//


public function cleanRow($row) {
if(is_array($row)){
$payload = array();
foreach ($row as $key => $value) {
  if(!is_numeric($key)){
  $payload[$key] = $value;

  }
}
}else{
$payload = $row;  
}

return $payload;
}//cleanRow

public function getRow($sql,$vals) {
try {
$stmt = self::$pdo->prepare($sql);
$stmt->execute($vals);
$row = true;
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$cnt = is_array($row) ? count($row) : null;
$response = array(
  'data' => $row,//$this->cleanRow($row), 
  'code' => 200, 
  'count' => $cnt, 
  'message' => 'success'
);
} catch (PDOException $e) {
$response = array(
  'data' => false, 
  'code' => $e->getCode(), 
  'message' => $e->getMessage()
);
} catch (Exception $e) {
$response = array(
  'data' => false,
  'code' => $e->getCode(),  
  'message' => $e->getMessage()
);
}

return $response;
}//


public function getRows($sql,$vals) {
try {
$stmt = self::$pdo->prepare($sql);
$stmt->execute($vals); 
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$data = true;
$data = $results;
//
$response = array(
  'data' => $data, 
  'code' => 200, 
  'count' => count($data), 
  'message' => 'success'
);
} catch (PDOException $e) {
$response = array(
  'data' => false,
  'code' => $e->getCode(), 
  'message' => $e->getMessage()
);
} catch (Exception $e) {
$response = array(
  'data' => false,
  'code' => $e->getCode(), 
  'message' => $e->getMessage()
);
}

return $response;
}//




}//DbWorks



//$dbConn = new DbWorks();

?>