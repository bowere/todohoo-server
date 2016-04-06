<?php
// Use Parameterized Queries to prevent SQL injection (PHP PDO-PHP Data Objects)
//  http://stackoverflow.com/questions/1299182/prepared-parameterized-query-with-pdo
// In short, do not concat user supplied values into $query. Instead put these
// in $parm_values and reference them by name in $query.

require_once("/home/todohoo/php/todohoo_config.php"); 

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
$NUM_EVENTS = 30;

// Parse URL Args
$page     = $_GET["page"];
$catList  = $_GET["catList"];
$fromDate = $_GET["fromDate"];
$toDate   = $_GET["toDate"];
$startLat = $_GET["startLat"];
$endLat   = $_GET["endLat"];
$startLng = $_GET["startLng"];
$endLng   = $_GET["endLng"];

try {
    $db = new PDO("mysql:dbname=".$GLOBALS['database'].";host=localhost",$GLOBALS['publicUsername'],$GLOBALS['publicPassword']);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Build Query SELECT / From / Joins
$query = "SELECT events.name as EVENT_NAME, events.performer_id, events.persistent, venues.name as VENUE_NAME, venues.lat, venues.lng, events.date, events.time, venues.type, categories.event_format_id FROM events JOIN venues ON events.venue_id = venues.id JOIN categories on events.cat_id = categories.path";

////////////////////////////////////////////////////
// Build category Clause
////////////////////////////////////////////////////
$catClause = "";
$cat_ids = explode(",",$catList);
for ($cat_cntr = 0; $cat_cntr < count($cat_ids); $cat_cntr++) {
  if ($cat_cntr > 0) {
    $catClause = "$catClause OR";
  }
  // If no categories are passed, we get an array count of 1 and first element is empty string.
  // Supress this case.
  if ($cat_ids[$cat_cntr] != "" ) {
    // Search for specific case (this category exists) and general case (sub category exists). This
    // Cannot be simplified to one LIKE statement. Ex. Cat 1 and 11 would match 1%. Maybe a regualr
    // expression would work.
    $catClause = "$catClause (cat_id = :cat_id_".$cat_cntr." OR cat_id LIKE :like_cat_id_".$cat_cntr.")";
    $parm_vals[':cat_id_'.$cat_cntr] = $cat_ids[$cat_cntr];
    $parm_vals[':like_cat_id_'.$cat_cntr] = $cat_ids[$cat_cntr].".%";
  }
}

////////////////////////////////////////////////////
// Build Date Clause
////////////////////////////////////////////////////
$dateClause = "";

// Handle One Specific Date (Both Dates are required. 
// This must be enforced in calling code.
if ($fromDate == "" ||  $toDate == "") {throw new Exception('Empty Date passed.');}
if ($fromDate == $toDate) {
  $dateClause = " events.date = :from_date";
  $parm_vals[':from_date'] = $fromDate;
} else {
  // Handle Date Range
  if ($fromDate != "") {
    $dateClause = " events.date >= :from_date AND events.date <= :to_date";
    $parm_vals[':from_date'] = $fromDate;
    $parm_vals[':to_date'] = $toDate;
  }
}
// Add persistent Events
$dateClause = "$dateClause OR events.persistent = 1";

////////////////////////////////////////////////////
// Build WHERE statement from all clauses
////////////////////////////////////////////////////
$query = "$query WHERE (";

// Add Category Clause
if ($catClause != "") {
$query = "$query ( $catClause )";
}
// Add Date Clause
if ($dateClause != "") {
  if ($catClause != "") {
    $query = "$query AND ";  
  }
  $query = "$query ( $dateClause )";
}
// Add GEO Clause
if ($catClause != "" || $dateClause != "") {
  $query = $query . " AND ";  
  $query = $query . "(venues.lat > :start_lat AND venues.lat < :end_lat AND venues.lng > :start_lng AND venues.lng < :end_lng)";
  $parm_vals[':start_lat'] = $startLat;
  $parm_vals[':end_lat']   = $endLat;
  $parm_vals[':start_lng'] = $startLng;
  $parm_vals[':end_lng']   = $endLng;
}
// Close Where parenthesis (opened before adding clauses) and LIMIT Clause
$query = "$query ) LIMIT :first_rec ,$NUM_EVENTS";  
$parm_vals[':first_rec'] = $page*$NUM_EVENTS;

//$firephp->log($query);
$firephp->log($parm_vals);

// LIMIT Numeric was being quoted. Google says: This setting is on by default for mysql, so turn it off.
// http://stackoverflow.com/questions/10437423/how-can-i-pass-an-array-of-pdo-parameters-yet-still-specify-their-types
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$prep = $db->prepare($query);

if (!$prep->execute($parm_vals)) {
   $error = $prep->errorInfo();
   echo "Error: {$error[2]}"; // element 2 has the string text of the error
} else {
  $json_record_format = '{event_name : "%s", venue_name : "%s", performer_name : "%s", event_format_id : %d, persistent : %s, lat : %f, lon : %f, type : "%s", date : "%s", time : "%s"}';
  $json_record_array = array();
  $count = 0;
  echo header("Content-type: text/json");
  // Iterate through the rows
   while ($row = $prep->fetch(PDO::FETCH_ASSOC)) { // check the documentation for the other options here
    $json_record_array[$count] =
    sprintf($json_record_format,
              str_replace('"',"&rdquo;",str_replace("'","&rsquo;",$row['EVENT_NAME'])),
              str_replace('"',"&rdquo;",str_replace("'","&rsquo;",$row['VENUE_NAME'])),
              str_replace('"',"&rdquo;",str_replace("'","&rsquo;",$row['performer_id'])),
              $row['event_format_id'],
              $row['persistent'],
              $row['lat'],
              $row['lng'],
              $row['type'],
              $row['date'],
              $row['time']);

    $count++;
   }
  echo "[" . join(",", $json_record_array) . "]";
}

?>