<?php 
  header('Access-Control-Allow-Origin: http://127.0.0.1:5502');
  header('Access-Control-Allow-Methods: GET, POST');
  header("Access-Control-Allow-Headers: Content-Type");

  $username = "student";
  $servername = "mysql-blue.mysql.database.azure.com";
  $password = "student-online";   
  $dbname = "csitvocab";

  // Create connection
  $conn = new mysqli("p:".$servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  function fillArray($length) {
    $array = array();
    for ($i = 0; $i < 4; $i++) {
      $randIndex = rand(1,$length);
      while (in_array($randIndex, $array)) {
        $randIndex = rand(1,$length);
      }
      $array[$i] = $randIndex;
    }
    return $array;
  }

  if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if ($contentType == "application/json") {
      $content = trim(file_get_contents("php://input"));
      $decoded = json_decode($content, true);
      $table = $decoded['table'];
      $index = $decoded['index'];
    }
    else {
      $table = $_POST['table'];
      $sqlTable = "SELECT * FROM ".$table;
      $tableResult = $conn->query($sqlTable);
      $tableLength = mysqli_num_rows($tableResult);
      //$indexList = array(rand(1,$tableLength),rand(1,$tableLength),rand(1,$tableLength),rand(1,$tableLength));
      $indexList = fillArray($tableLength);
    }

    $response = array();

    $stmt = "SELECT * FROM ".$table." WHERE my_row_id =".$indexList[0].";";
    $stmt .= "SELECT * FROM ".$table." WHERE my_row_id =".$indexList[1].";";
    $stmt .= "SELECT * FROM ".$table." WHERE my_row_id =".$indexList[2].";";
    $stmt .= "SELECT * FROM ".$table." WHERE my_row_id =".$indexList[3];

    
    if ($conn->multi_query($stmt)) {
      do {
        if ($result = $conn->store_result()) {
          while ($row = $result -> fetch_assoc()) {
            $response[] = $row;
          }
         $result -> free_result();
        }
      } while ($conn -> next_result());
    } 
 
    /**foreach ($indexList as $item) {
      $stmt = "SELECT * FROM ".$table." WHERE my_row_id =".$item;
      $result = $conn->query($stmt);
      $row = $result -> fetch_assoc();
      $response[] = $row;
      mysqli_free_result($result);
    } */

    echo json_encode($response);
  }
?>
