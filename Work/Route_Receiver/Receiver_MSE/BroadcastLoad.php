<?php
//This program outputs the received incomplete segment just after tune-in.
//It waits till the data is completely received and then outputs.
$channel=$_REQUEST['channel'];
if($channel==1)
  $Folder="DASH_Content1";
else
  $Folder="DASH_Content2";


// echo $initContents;

$fileSearch = "Unicast_";

$file = glob("/var/www/html/ATSC_ROUTE/Work/Route_Receiver/Receiver_MSE/".$Folder."/" . $fileSearch . "*");
$path=$file[0];
header('Content-Type: binary');
header('Access-Control-Allow-Origin: *');





// echo $contents;

$pos=strpos($path, "Unicast_");
$endpos=strpos($path,".mp4", $pos+1);
$segNum=substr($path, $pos+8, $endpos-($pos+8));

while(!file_exists("/var/www/html/ATSC_ROUTE/Work/Route_Receiver/Receiver_MSE/".$Folder."/video_8M_".$segNum.".mp4")){
  usleep(100);
}

$videoSegcontents=file_get_contents("/var/www/html/ATSC_ROUTE/Work/Route_Receiver/Receiver_MSE/".$Folder."/video_8M_".$segNum.".mp4");

$output= $videoSegcontents;
echo $output;

//$newOUt=fopen("/var/www/html/ATSC_ROUTE/Work/Route_Receiver/Receiver_MSE/TempFile2.mp4", "w");
//fwrite($newOUt,$output."\n");
//fwrite($newOUt,$contents."\n");


//fclose($newOUt);
?>