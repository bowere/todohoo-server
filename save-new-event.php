<?php
//TODO: This was copied from SAVE VENUE, modify as all the tables/parms
// Use Parameterized Queries to prevent SQL injection (PHP PDO-PHP Data Objects)
//  http://stackoverflow.com/questions/1299182/prepared-parameterized-query-with-pdo
// In short, do not concat user supplied values into $query. Instead put these
// in $parm_values and reference them by name in $query.

require_once("/home/todohoo/php/todohoo_config.php");

$parm_vals = array();
$query = array();

// Parse URL Args
$parm_vals[':name']         = $_GET["name"];
$parm_vals[':venue_id']     = $_GET["venue_id"];
$parm_vals[':performer_id'] = $_GET["performer_id"];
$parm_vals[':date']         = $_GET["date"];
$parm_vals[':time']         = $_GET["time"];
$parm_vals[':persistent']   = $_GET["persistent"];
$parm_vals[':cat_id']       = $_GET["cat_id"];
$parm_vals[':event_url']    = $_GET["event_url"];
$parm_vals[':member_owner'] = $_GET["member_owner"];

$query = "INSERT INTO events(name, venue_id, performer_id, date, time, persistent, cat_id, event_url, member_owner) VALUES (:name, :venue_id, :performer_id, :date, :time, :persistent, :cat_id, :event_url, :member_owner)";

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
  $newEventID = $db->lastInsertId();
  $db->commit();
  $db = null;
  echo sprintf($GLOBALS['json_record_format'], "success", "none");
  //return $result;
} catch (Exception $e) {
  echo sprintf($json_record_format, "error", $e->getMessage());
}
?>