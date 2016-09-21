#!/usr/bin/php5
<?php
//This program is called by the receiver application by the Unicast fetch request.

//Receive info from the request.
$toi=$_REQUEST['TOI'];
$esi=$_REQUEST['ESI']-1; //-1 to send packets from ESI 0 to n-1
$port=$_REQUEST['port']; // port decides which log file to be seen.

if($port==4001)
{
   $Source="Elysium_1_0";
   $Dest="DASH_Content1";
}
else
{   $Source="ToS_1_0";   
    $Dest="DASH_Content2";
}
$filename="Deliverylog_session".$port.".txt";
if(file_exists($filename)){
$contents=file_get_contents($filename);
$toiPos=strpos($contents, "TOI=".$toi." ");
$startFrom=$toiPos+1;
$esiPos=strpos($contents, "ESI=".$esi." ", $startFrom);
$startFrom=$esiPos+1;
$bytePos=strpos($contents, "ByteSum=", $startFrom);
$endPos=strpos($contents,PHP_EOL, $startFrom);
$byteSum=substr($contents,$bytePos+8, ($endPos-($bytePos+8)));

//Send initialization segment before media data. IP address is hard coded - must be changed accordingly.
$cmd2="curl http://10.4.246.249/ATSC_ROUTE/Work/Route_Sender/bin/".$Source."/video_8M_init.mp4 -o /var/www/html/ATSC_ROUTE/Work/Route_Receiver/Receiver_MSE/".$Dest."/video_8M_init.mp4";
exec($cmd2);

$lastByte=$byteSum-1;

//Copy asked range of bytes of video segment from sender to receiver.
$cmd="curl -r 0-".$lastByte." http://10.4.246.249/ATSC_ROUTE/Work/Route_Sender/bin/".$Source."/video_8M_".($toi/2).".mp4 --limit-rate 25M -o /var/www/html/ATSC_ROUTE/Work/Route_Receiver/Receiver_MSE/".$Dest."/Unicast_".($toi/2).".mp4";
exec($cmd);


//$cmd2="cat /var/www/html/ATSC_ROUTE/Work/Route_Receiver/Receiver_MSE/".$Dest."/Unicast_".($toi/2).".mp4 /var/www/html/ATSC_ROUTE/Work/Route_Receiver/Receiver_MSE/".$Dest."/video_8M_".($toi/2).".mp4 >> /var/www/html/ATSC_ROUTE/Work/Route_Receiver/Receiver_MSE/".$Dest."/NewFile.mp4";

//exec($cmd2);
}


?>
