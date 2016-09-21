<?php
//This program sends the init and Unicast fetched segment to the JS page.
$channel=$_REQUEST['channel'];
if($channel==1)
  $Folder="DASH_Content1";
else
  $Folder="DASH_Content2";
  
$init="/var/www/html/ATSC_ROUTE/Work/Route_Receiver/Receiver_MSE/".$Folder."/video_8M_init.mp4";
$initContents=file_get_contents($init);

// echo $initContents;

$fileSearch = "Unicast_";

$file = glob("/var/www/html/ATSC_ROUTE/Work/Route_Receiver/Receiver_MSE/".$Folder."/" . $fileSearch . "*");
$path=$file[0];
header('Content-Type: binary');
header('Access-Control-Allow-Origin: *');

$contents=file_get_contents($path);



// echo $contents;



$output= $initContents.$contents;
echo $output;

//$newOUt=fopen("/var/www/html/ATSC_ROUTE/Work/Route_Receiver/Receiver_MSE/TempFile1.mp4", "w");
//fwrite($newOUt,$output."\n");

//fclose($newOUt);
?>