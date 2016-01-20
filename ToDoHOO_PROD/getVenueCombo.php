<?php  

require_once("/home/todohoo/php/todohoo_config.php"); 

function getVenueCombo() {

// Select all the rows in the categories table marked as a venue

  $query = "SELECT * FROM categories WHERE is_venue = 1 ORDER BY name ASC";
  $combo = executeQuery($query,
             function ($result) {
                $spaces = "                ";
                $select = "<select id='venue-type' name='venue-type' style='width:100%;'>\n";
                $select = $select . "{$spaces}  <option value='no-selection' id='no-cat'>Select Category</option>\n";
                while ($row = @mysql_fetch_assoc($result)){
                  $select = $select . "{$spaces}  <option value='{$row['path']}'>{$row['name']}</option>\n";
                }
                $select = $select . "{$spaces}</select>\n";
                return $select;
              });

  echo $combo;
}

?>