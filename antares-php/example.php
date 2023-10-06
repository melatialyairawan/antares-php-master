<?php
require_once('./Antares.php');

Antares::init([
  "PLATFORM_URL" => 'https://platform.antares.id:8443', // TODO: Change this to your platform URL
  "ACCESS_KEY" => '65f708123a858355:7084ef0d7c21f8cd' // TODO: Change this to your access key
]);

try {
  // RETRIEVE DATA
  echo "============================ Retrieve data =================================\n";
  echo "";
  // get application
  // application name example '/antares-cse/antares-id/example'
  $resp = Antares::getInstance()->get('/antares-cse/antares-id/ecosonic'); // Change this to your application uri
  
  if ($resp instanceof AE) {
    echo nl2br("AE: " . $resp->getName() . "\n");
    
    // list all application's devices
    $cntUris = $resp->listContainerUris();
    echo "Containers: " . count($cntUris) . "\n";
    echo "";
    
    foreach ($cntUris as $cntUri) {
      echo "  " . $cntUri . "\n";
      echo "";

      // get device
      $cnt = Antares::getInstance()->get($cntUri);
      echo "    " . $cnt->getName() . "\n";
      echo "";

      try {
        // get latest data
        $la = $cnt->getLatestContentInstance();
        echo "      [$la->ct]:$la->rn $la->con\n";
        echo "";
      } catch (Exception $e) {
        echo "      last data: " . $e->getMessage() . "\n";
        echo "";
      }
    }
  }
} catch (Exception $e) {
  echo($e->getMessage());
}
