<?PHP
///////////////////////////////////////////////////////////////////////////
// ToDoHOO Configuration                                                  /
//                                                                        /
// Abstract: This should be included in all PGP web pages (require_once). /
// It will set the environment/paths for Prod/Dev, etc.                   /
//                                                                        /
///////////////////////////////////////////////////////////////////////////

  $siteVersion = '0.1';

  // Set database/PHP_Path based on Subdomain
  $SUB_DOM = explode(".",$_SERVER['HTTP_HOST']);
  switch (array_shift($SUB_DOM)) {
    case "dev":
      $subDom="dev";
      $database="todohoo_DEV";
      $phpPath="/home/todohoo/php/ToDoHOO_DEV";
      break;
    default:
      $subDom="prod";
      $database="todohoo_PROD";
      $phpPath="/home/todohoo/php/ToDoHOO_PROD";
      break;
  }

  // Set Database login credentials for Public/Member
  $publicUsername="todohoo_joepub";
  $publicPassword="bupe0j";
  $memberUsername="todohoo_joemem";
  $memberPassword="meme0j";
  
  $googleMap = "https://maps.googleapis.com/maps/api/js?key=AIzaSyBmRML7NJwoBME7-Z5MN2t1qUqPLb7bTDU";

  // Set up firePHP. firePHP is a security risk in production. Thus,
  //   it should typically be disabled. If needed, for a quick production
  //   debugging issue, it can be enabled here.
  // For Each Subdomain enable or disable firePHP by:
  //   require_once($phpPath .'/FirePHP_disabled.class.php');
  //   Enable:  require_once($phpPath .'/FirePHPCore/FirePHP.class.php');
  switch ($GLOBALS['subDom']) {
    case "dev":
      require_once($phpPath .'/FirePHPCore/FirePHP.class.php');
      break;
    case "prod":
      require_once($phpPath .'/FirePHPCore/FirePHP.class.php');
      //require_once($phpPath .'/FirePHP_disabled.class.php');
      break;
  }
  $firephp = FirePHP::getInstance(true);

  // Function to Fetch Prod/Dev FavIcon
  function getFavIcon() {
    switch ($GLOBALS['subDom']) {
      case "dev":
        return "http://todohoo.com/dev/dev_favicon.png";
        break;
      case "prod":
        return "http://todohoo.com/favicon.png";
        break;
    }
  }

  // Function to Fetch Prod/Dev ToDoHOO Image
  function getToDoHooIcon() {
    switch ($GLOBALS['subDom']) {
      case "dev":
        return "http://todohoo.com/dev/resources/images/dev_ToDoHoo.png";
        break;
      case "prod":
        return "http://todohoo.com/resources/images/ToDoHoo.png";
        break;
    }
  }
  
  // @todo -> remove me sucka
  function executeQuery($query, $callbackFxn) {
    // Opens a connection to a MySQL server
    $connection = mysql_connect (localhost, $GLOBALS['publicUsername'], $GLOBALS['publicPassword']);
    if (!$connection) {
      $GLOBALS['firephp']->log("unable to connect to database");
      die('Not connected : ' . mysql_error());
    } 

    // Set the active MySQL database
    $db_selected = mysql_select_db($GLOBALS['database'], $connection);
    if (!$db_selected) {
      $GLOBALS['firephp']->log("unable to select database");
      die ('Can\'t use db : ' . mysql_error());
    } 
    
    $result = mysql_query($query);
    if (!$result) {  
      $GLOBALS['firephp']->log('Invalid query: ' . mysql_error());
      die('Invalid query: ' . mysql_error());
    } 

    //process the results with user supplied function
    $processedResults = $callbackFxn($result);
    
    //close the database connection
    mysql_close($connection);
    
    return $processedResults;
  }
  
  function executePDO($isMember, $query, $parm_vals, $callbackFxn) {
    try {
      if($isMember) {
        $db = new PDO("mysql:dbname=".$GLOBALS['database'].";host=localhost",
                      $GLOBALS['memberUsername'],
                      $GLOBALS['memberPassword']);
      }
      else {
        $db = new PDO("mysql:dbname=".$GLOBALS['database'].";host=localhost",
                      $GLOBALS['publicUsername'],
                      $GLOBALS['publicPassword']);
      }
      

      // LIMIT Numeric was being quoted. Google says: This setting is on by default for mysql, so turn it off.
      // http://stackoverflow.com/questions/10437423/how-can-i-pass-an-array-of-pdo-parameters-yet-still-specify-their-types
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
      $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      
      $prep = $db->prepare($query);
      if (!$prep->execute($parm_vals)) {
        $db = null;
        throw new Exception($prep->errorInfo());
      }
      else {
        // to loop over the results, create a while loop like this
        //    while ($row = $prep->fetch(PDO::FETCH_ASSOC)) { $myVal = $row['a_value']; }
        $result = $callbackFxn($prep);
        $db = null;
        return $result;
      }
    }
    catch (Exception $e) {
      die("Database Connection Failed: " . $e->getMessage());
      throw $e;
    }
  }
      
  function addSiteVersion($url) {
    global $siteVersion;
    return $url . "?v=" . $siteVersion;
  }


?>