<?php
ini_set('memory_limit', '600M');  // Aumentar a 512 MB

include 'DataCharge.php';
$Clase = new PrerequisitosChecker('csvArchives/Prerequisitos.csv');
$list = $Clase->ReadData();
print(count($list));
?>