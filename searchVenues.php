<?php  

require_once("/home/todohoo/php/todohoo_config.php"); 

# Initial Query
$query = "SELECT venues.*, categories.name AS type FROM venues, categories WHERE venues.name LIKE :searchString AND lat BETWEEN :startLat AND :endLat  AND lng BETWEEN :startLng AND :endLng AND venues.type = categories.path";

# Add Venue Name Search String
$searchString = $_GET["searchString"];
$startLat = $_GET["startLat"];
$endLat = $_GET["endLat"];
$startLng = $_GET["startLng"];
$endLng = $_GET["endLng"];

$parm_vals['searchString'] = "%" . $searchString . "%";
$parm_vals['startLat'] = $startLat;
$parm_vals['endLat'] = $endLat;
$parm_vals['startLng'] = $startLng;
$parm_vals['endLng'] = $endLng;

echo header("Content-type: text/json");
try {
    $results = executePDO(false, $query, $parm_vals,
                    function($PDO_prep) {

                      $json_record_format = '{venueId : "%s", venueName : "%s", address : "%s", phone : "%s", description : "%s", lat : %f, lng : %f, type : "%s"}';
                      $json_record_array = array();
                      $count = 0;
                      // Iterate through the rows
                       while ($row = $PDO_prep->fetch(PDO::FETCH_ASSOC)) { // check the documentation for the other options here
                        $json_record_array[$count] =
                        sprintf($json_record_format,
                                  str_replace('"',"&rdquo;",str_replace("'","&rsquo;",$row['id'])),
                                  str_replace('"',"&rdquo;",str_replace("'","&rsquo;",$row['name'])),
                                  str_replace('"',"&rdquo;",str_replace("'","&rsquo;",$row['address'])),
                                  str_replace('"',"&rdquo;",str_replace("'","&rsquo;",$row['phone'])),
                                  str_replace('"',"&rdquo;",str_replace("'","&rsquo;",$row['description'])),
                                  $row['lat'],
                                  $row['lng'],
                                  $row['type']);

                        $count++;
                       }
                      return $json_record_array;
                    });

    echo "[" . join(",", $results) . "]";

}  
catch(Execution $e) {
    //@todo:  need to handle this error case in the browser
    echo "{ error : " . $e->getMessage() . "}";
}

?>