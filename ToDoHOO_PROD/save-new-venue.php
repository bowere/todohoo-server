<?php
// Use Parameterized Queries to prevent SQL injection (PHP PDO-PHP Data Objects)
//  http://stackoverflow.com/questions/1299182/prepared-parameterized-query-with-pdo
// In short, do not concat user supplied values into $query. Instead put these
// in $parm_values and reference them by name in $query.

require_once("/home/todohoo/php/todohoo_config.php"); 
    //TODO: What is This? Pre JSON artifact?
    function pg_query_params_return_sql($query, $array)
    {
        $query_parsed = $query;
       
        for ($a = 0, $b = sizeof($array); $a < $b; $a++)
        {
            if ( is_numeric($array[$a]) )
            {
                $query_parsed = str_replace(('$'.($a+1)), str_replace("'","''", $array[$a]), $query_parsed );
            }
            else
            {
                $query_parsed = str_replace(('$'.($a+1)), "'".str_replace("'","''", $array[$a])."'", $query_parsed );
            }
        }
       
        return $query_parsed;
    } 
    
    
// Constants

// Parse URL Args
$parm_vals[':name']         = $_GET["name"];
$parm_vals[':address_1']    = $_GET["address_1"];
$parm_vals[':address_2']    = $_GET["address_2"];
$parm_vals[':city']         = $_GET["city"];
$parm_vals[':state']        = $_GET["state"];
$parm_vals[':zipcode']      = $_GET["zipcode"];
$parm_vals[':phone']        = $_GET["phone"];
$parm_vals[':website_url']  = $_GET["website_url"];
$parm_vals[':logo_url']     = $_GET["logo_url"];
$parm_vals[':description']  = $_GET["description"];
$parm_vals[':lat']          = $_GET["lat"];
$parm_vals[':lng']          = $_GET["lng"];
$parm_vals[':type']         = $_GET["type"];
$parm_vals[':member_owner'] = $_GET["member_owner"];

$firephp->log($parm_vals);
try {
    $db = new PDO("mysql:dbname=".$GLOBALS['database'].";host=localhost",$GLOBALS['memberUsername'],$GLOBALS['memberPassword']);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Build INSERT Query
$query = "INSERT INTO venues(name, address_1, address_2, city, state, zipcode, phone, website_url, logo_url, description, lat, lng, type, member_owner, access_date) VALUES (:name, :address_1, :address_2, :city, :state, :zipcode, :phone, :website_url, :logo_url, :description, :lat, :lng, :type, :member_owner, CURDATE())";
$firephp->log($query);

// LIMIT Numeric was being quoted. Google says: This setting is on by default for mysql, so turn it off.
// http://stackoverflow.com/questions/10437423/how-can-i-pass-an-array-of-pdo-parameters-yet-still-specify-their-types
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$prep = $db->prepare($query);

if (!$prep->execute($parm_vals)) {
   $error = $prep->errorInfo();
   echo "Error: {$error[2]}"; // element 2 has the string text of the error
} else {
  $json_record_format = '{results : "%s", details : "%s"}';
  $json_record_array = array();
  echo header("Content-type: text/json");
  $json_record_array[$count] = sprintf($json_record_format, "success","none");
  echo "[" . join(",", $json_record_array) . "]";
}

?>