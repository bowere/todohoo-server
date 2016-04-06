<?php

// Use Parameterized Queries to prevent SQL injection (PHP PDO-PHP Data Objects)
//  http://stackoverflow.com/questions/1299182/prepared-parameterized-query-with-pdo
// In short, do not concat user supplied values into $query. Instead put these
// in $parm_values and reference them by name in $query.

require_once("/home/todohoo/php/todohoo_config.php");

$parm_vals = array();
$query = array();

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

$query = "INSERT INTO venues(name, address_1, address_2, city, state, zipcode, phone, website_url, logo_url, description, lat, lng, type, member_owner, access_date) VALUES (:name, :address_1, :address_2, :city, :state, :zipcode, :phone, :website_url, :logo_url, :description, :lat, :lng, :type, :member_owner, CURDATE())";

$json_record_format = '{"results" : "%s", "details" : "%s"}';

echo header("Content-type: text/json");

try {
  $db = new PDO("mysql:dbname=" . $GLOBALS['database'] . ";host=localhost", $GLOBALS['memberUsername'], $GLOBALS['memberPassword']);

  $db->beginTransaction();

  // LIMIT Numeric was being quoted. Google says: This setting is on by default for mysql, so turn it off.
  // http://stackoverflow.com/questions/10437423/how-can-i-pass-an-array-of-pdo-parameters-yet-still-specify-their-types
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Add Venue
  $prep = $db->prepare($query);
  if (!$prep->execute($parm_vals)) {
    $db->rollBack();
    $db = null;
    throw new Exception($prep->errorInfo());
  }

  // Prepare to add Persistent Events
  $newVenueID = $db->lastInsertId();
  $perActAry = explode(",", $_GET["per_act_list"]);
  $query = "INSERT INTO events(venue_id, persistent, cat_id) VALUES (:venue_id, :persistent, :cat_id)";
  $prep = $db->prepare($query);
  $parm_vals = array();
  
  // Add Each Persistent Activity
  for ($i = 0; $i < count($perActAry); $i++) {
    $parm_vals[':venue_id'] = $newVenueID;
    $parm_vals[':persistent'] = 1;
    $parm_vals[':cat_id'] = $perActAry[$i];
    if (!$prep->execute($parm_vals)) {
      $db->rollBack();
      $db = null;
      throw new Exception($prep->errorInfo());
    } else {
      // to loop over the results, create a while loop like this
      //    while ($row = $prep->fetch(PDO::FETCH_ASSOC)) { $myVal = $row['a_value']; }
      //$result = $callbackFxn($prep);
      $firephp->log("Yay");
    }
  }
  $db->commit();
  $db = null;
  echo sprintf($GLOBALS['json_record_format'], "success", "none");
  //return $result;
} catch (Exception $e) {
  echo sprintf($json_record_format, "error", $e->getMessage());
}
?>