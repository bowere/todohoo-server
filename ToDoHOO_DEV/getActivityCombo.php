<?php  

require_once("/home/todohoo/php/todohoo_config.php"); 

function getActivityCombo() {

// Select all the rows in the categories table marked as a persistent activity

  $query = "SELECT * FROM categories WHERE is_per_activity = 1 ORDER BY name ASC";
  
  echo executePDO(false, $query, array(),
                  function($PDO_prep) {
                    $spaces = "                ";
                    $select = "<select name='activity'>\n";
                    $select = $select . "{$spaces}  <option value='no-selection' id='no-activity'>Add Activity</option>\n";
                    while ($row = $PDO_prep->fetch(PDO::FETCH_ASSOC)) {
                      $select = $select . "{$spaces}  <option value='{$row['path']}'>{$row['name']}</option>\n";
                    }
                    $select = $select . "{$spaces}</select>\n";
                    return $select;
                  });
}
?>