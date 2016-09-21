<?php
$micro_date = microtime();
$date_array = explode(" ",$micro_date);
$date = date("Y-m-d H:i:s",$date_array[1]);
unlink ('../bin/timelog.txt');
file_put_contents ( "timelog.txt" , "Start:" . $date . $date_array[0] . " \r\n" , FILE_APPEND );

ini_set('memory_limit','-1');//remove memory limit
#In case previous instances are running due to may be a crash
exec("sudo killall flute");

/* 
Main script for starting flure reception and MPD re-writing
 */
chdir('../bin/');
$currDir=dirname(__FILE__);

$channel = $_REQUEST['channel'];
//$response=array();
$response="Started channel ". $channel;
//echo "Started channel ". $channel;

#Define Paths

$DASHContentBase="DASH_Content";
$DASHContentDir=$DASHContentBase . (string)$channel;
$DASHContent=$currDir . "/" . $DASHContentDir;

$FLUTEReceiver="./";

unlink('../bin/socketServerReady.trig');

$index=$channel + 4;
$sdp="SDP" . (string)$index . ".sdp";			#SDP to be used by sender
//$sdp="SDP5.sdp";
//$index=$index + 1;
//$sdp2="SDP" . (string)$index . ".sdp";			#SDP to be used by sender
//$index=$index + 1;
//$sdp3="SDP" . (string)$index . ".sdp";
$Log="Rcv_Log_MPD" . (string)$channel . ".txt";			#Log containing delays corresponding to FLUTE receiver
$Log2="Rcv_Log_Video" . (string)$channel . ".txt";
$Log3="Rcv_Log_Audio" . (string)$channel . ".txt";
$encodingSymbolsPerPacket=1;	#For Receiver, Only a value of zero makes a difference. Otherwise, it is ignored 
							#This means that more than one encoding symbol is included packet. This could be varying

#Initialize DASHContent Folder
exec("mkdir -p $DASHContent");
array_map('unlink', glob("$DASHContent/*"));

#In case previous instances are running due to may be a crash
#exec("sudo killall flute");
#usleep(1000);
$micro_date = microtime();
$date_array = explode(" ",$micro_date);
$date = date("Y-m-d H:i:s",$date_array[1]);
file_put_contents ( "timelog.txt" , "Launching FLUTE:" . $date . $date_array[0] . " \r\n" , FILE_APPEND );
# Start first flute sender
#Start MPD receiving session followed by Audio and Video sessions.
$cmd=  "sudo nice --20 ./flute -A -B:". $DASHContent ." -d:" . $sdp . " -Q -Y:" . $encodingSymbolsPerPacket . " -J:" . $Log . " > /dev/null &"; // > logout2.txt &";
exec($cmd);


// After receive SLS fragments ,i.e S-TSID and MPD. Extract info from S-TSID and start Video and Audio LCT sessions.
while (!glob($DASHContent."/S-TSID.xml")) usleep(5000);
$s_tsid= simplexml_load_file($DASHContent."/S-TSID.xml");
$sourceIP=$s_tsid->RS[0]['sIpAddr'];
$tsi_v=$s_tsid->RS[0]->LS[0]['tsi'];
$tsi_a=$s_tsid->RS[0]->LS[1]['tsi'];
$port=$s_tsid->RS[0]['dport'];
$port_v=$port+$tsi_v;
$port_a=$port+$tsi_a;
unlink('../bin/socketServerReady.trig');
unlink('../bin/UnicastLog.txt');

$cmd=  "sudo nice --20 ./flute -A -B:". $DASHContent ." -m:224.1.1.1 -s:"  .$sourceIP . " -p:".$port_v." -t:".$tsi_v. " -Q -Y:" . $encodingSymbolsPerPacket . " -J:" . $Log2 . " > logout1.txt &";//> /dev/null &";
exec($cmd);

# Start second flute sender
$cmd=  "sudo nice --20 ./flute -A -B:". $DASHContent ." -m:224.1.1.1 -s:"  .$sourceIP . " -p:".$port_a." -t:".$tsi_a. " -Q -Y:" . $encodingSymbolsPerPacket . " -J:" . $Log3 . " > logout2.txt &";//> /dev/null &";
exec($cmd);
$micro_date = microtime();
$date_array = explode(" ",$micro_date);
$date = date("Y-m-d H:i:s",$date_array[1]);
file_put_contents ( "timelog.txt" , "Started Globbing:" . $date . $date_array[0] . " \r\n" , FILE_APPEND );
//To find Live Edge
$TuneinPhp_SEC = new DateTime( 'now',  new DateTimeZone( 'UTC' ) );	/* initializer for live edge time*/
$TuneinPhp_SEC->setTimestamp($date_array[1]);    //Better use a single time than now above
$TuneinPhp=$TuneinPhp_SEC->getTimestamp() + round($date_array[0],4);

//


//Unicast fetching request needs ESI, TOI , so its extracted from a log file.
while(1){
  if(file_exists("../bin/UnicastLog.txt")){
  $Unicastfile=file_get_contents("../bin/UnicastLog.txt");
  //TOI extract
  $pos = strpos(($Unicastfile),"Entry TOI=");
	  if($pos!==FALSE){
	    $endpos=strpos($Unicastfile,PHP_EOL,$pos+10);
	    $toi= substr($Unicastfile,$pos+10,$endpos-($pos+10));
	    //echo $toi;
	  }
	  else{
	    continue;
	    }
	//ESI extract
	$pos1 = strpos(($Unicastfile),"Entry ESI=");
	  if($pos1!==FALSE){
	    $endpos1=strpos($Unicastfile,PHP_EOL,$pos1+1);
	    $esi= substr($Unicastfile,$pos1+10,$endpos1-($pos1+10));
	    //echo $toi;
	    break;
	  }
	  else{
	    continue;
	    }
  }
  else{
    usleep(100);
    continue;
    }

}


  
//$response[]=$esi;
//$response[]=$port_v;

echo json_encode(array("message"=>$response,"toi"=> $toi,"esi"=>$esi,"port"=>$port_v, "TuneinPhp"=>$TuneinPhp));
while (!glob("../bin/socketServerReady.trig")) usleep(1000);

?>
