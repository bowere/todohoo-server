<?php  

require_once("/home/todohoo/php/todohoo_config.php"); 

function buildTree() {
// Opens a connection to a MySQL server
global $database, $publicPassword, $publicUsername;

$connection=mysql_connect (localhost, $publicUsername, $publicPassword);
if (!$connection) {  die('Not connected : ' . mysql_error());} 

// Set the active MySQL database

$db_selected = mysql_select_db($database, $connection);
if (!$db_selected) {
  die ('Can\'t use db : ' . mysql_error());
} 

// Select all the rows in the markers table

$query = "SELECT * FROM categories WHERE 1 ORDER BY path ASC";
$result = mysql_query($query);
if (!$result) {  
  die('Invalid query: ' . mysql_error());
} 

echo "<div id='categoryTreeDiv' class='ui-widget-content'>\n";

$prevLevel = 0;
// Build Unordered List from database table
while ($row = @mysql_fetch_assoc($result)){  
  $path = $row['path'];
  $name = $row['name'];
  $newLevel = substr_count($path,".") + 1; // level is based on the number of dots in the path
  
// If moving down to another level, add <UL>
  if ($newLevel > $prevLevel) {
    if ($prevLevel == 0) {
      // Handle special case of main <UL> tag
      echo str_repeat(" ",$prevLevel *2) . "<ul id='categories'><!-- org=0 -->\n";
    } else {
      echo str_repeat(" ",$prevLevel *2) . "<ul><!-- org=1 -->\n";
    }
  }


  // If moving back up, close each level back up to where we are going
//  for ($cntr = 1; $cntr <= ($newLevel - $prevLevel); $cntr++) {
  if ($newLevel < $prevLevel) {
    echo str_repeat(" ",$prevLevel *2) . "</li><!-- org=2 -->\n";
    echo str_repeat(" ",$newLevel *2) . "</ul><!-- org=3 -->\n";
    echo str_repeat(" ",$newLevel *2) . "</li><!-- org=4 -->\n";
  }

  for ($cntr = 1; $cntr < ($prevLevel - $newLevel); $cntr++) {
    echo str_repeat(" ",$newLevel *2) . "</ul><!-- org=8 -->\n";
    echo str_repeat(" ",$newLevel *2) . "</li><!-- org=9 -->\n";
  }

  // Close <LI> from previous Same-Level category
  if ($newLevel == $prevLevel) {
    echo str_repeat(" ",$newLevel *2) . "</li><!-- org=5 -->\n";
  }

  // Place Category Checkbox
  echo str_repeat(" ",$newLevel *2) . "<li><input type='checkbox' value='" . $path . "'/>" . $name . " " . "\n";
  //echo str_repeat(" ",$newLevel *2) . "<li><input type='checkbox' value='" . $path . "'/>" . $name . " (path=" . $path . " newLevel=" . $newLevel . " prevLevel=" . $prevLevel . ")\n";

  // Retain this level for next reference
  $prevLevel = $newLevel;
} 

// Final Close tags
echo "  </li><!-- org=6 -->\n";
echo "</ul><!-- org=7 -->\n";
echo "</div>\n";
}
?>