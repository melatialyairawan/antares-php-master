<?php
require_once('./Antares.php');

Antares::init([
  "PLATFORM_URL" => 'https://platform.antares.id:8443', // TODO: Change this to your platform URL
  // URL for Peruri On Premise https://iot.peruri.co.id:8443
  "ACCESS_KEY" => '65f708123a858355:7084ef0d7c21f8cd' // TODO: Change this to your access key
  //abcdefgh12345678:abcdefgh12345678
]);
?>

<!DOCTYPE html> 
<html lang="en"> 
  <head> 
    <title>Antares GET/POST</title> 
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
    .container {
      padding-top:100px;
    }
    table {
      border-collapse: collapse;
      width: 100%;
    }
    th, td {
      padding: 8px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    h1 {
      text-align: center;
    }
    input { 
      text-align: center; 
    }
    </style>
  </head> 
  <body>
    <div class="container">
      <div class="row">        
        <div class="col-md-12">
          <table>
            <tr>
              <th>Time (WIB)</th>
              <th>Resource Index (ri)</th>
              <th>Data</th>
            </tr>
            <?php
            try {
              $resp = Antares::getInstance()->get('/antares-cse/antares-id/ecosonic/promini'); // TODO: Change this to your container uri
              // example /antares-cse/antares-id/Ursalink/EM500-PT100-232877
              $first10 = $resp->listContentInstanceUris(5); //total data request
              foreach ($first10 as $uri) {
              $payload = Antares::getInstance()->get($uri);
              echo "<tr>";
                echo "<td>";
                  $date=strtotime($payload->getCreationTime());
                  echo date('Y-m-d h:i:s', $date);
                echo "</td>";
                echo "<td>";
                  $resuri=$payload->ri;
                  echo $resuri;
                echo "</td>";
                echo "<td>";
                  $data=json_decode($payload->getContent());
                  $encoded = json_encode($data, JSON_PRETTY_PRINT);
                  echo "<pre>".$encoded."<pre/>";
                echo "</td>";
              echo "</tr>";
              }
            }
          catch (Exception $e) {
            echo($e->getMessage());
          }
            ?>
            </td>
          </table>         
        </div>
      </div>
    </div>  
  </body> 
</html>