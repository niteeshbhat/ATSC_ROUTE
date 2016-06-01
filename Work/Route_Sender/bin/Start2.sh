#!/bin/bash

#This script:
#	1- Converts MPD to live and sets Availability Start Time to NOW + Delay
#	2- After (Delay - x) seconds has passed, FLUTE sender is triggered.

#Define Directories
#DASHContent=ToS720p_0_5
#DASHContent2=Elysium720p_0_5
DASHContent=Elysium_1_0
#DASHContent2=ElysiumLC_0_5

#if [ "$#" -gt 0 ] && [ "$1" -eq 1000 ]
#then
 # DASHContent=Hotel
  #DASHContent2=Wave
#fi

FLUTESender=.

#Variables
Delay=1.5					#AST will be set to NOW + Delay seconds	
Delay2=1.75
#x=10					#FLUTE receiver will be started after Delay - x seconds
bitRate=50000			#Bitrate in kb/s to be used in FLUTE Sender

fdtVid=efdt_Video.xml
fdtAud=efdt_Audio.xml
fdtMPD=efdt_MPD.xml

FLUTEVideoInput="FluteInput_Video.txt"
FLUTEAudioInput="FluteInput_Audio.txt"

sdp=SDP1.sdp
sdp2=SDP2.sdp					#SDP to be used by sender
sdp3=SDP3.sdp

sdp4=SDP4.sdp
sdp5=SDP5.sdp					#SDP to be used by sender	
sdp6=SDP6.sdp

encodingSymbolsPerPacket=1		#A value of zero indicates that different chunks of segment have different delay
								#and maximum transmission unit size is used (e.g. 1500 bytes per packet)

Log=Send_Log_MPD.txt			#Log containing delays corresponding to FLUTE server
Log2=Send_Log_Video.txt			#Log containing delays corresponding to FLUTE server
Log3=Send_Log_Audio.txt

Log4=Send_Log_MPD2.txt			#Log containing delays corresponding to FLUTE server
Log5=Send_Log_Video2.txt			#Log containing delays corresponding to FLUTE server
Log6=Send_Log_Audio2.txt

[ $encodingSymbolsPerPacket -lt 0 ] && echo "The number of encoding symbols should be zero or greater" && exit 

#Convert MPD from static to dyanamic
echo "Converting MPD"

#Brackets are used to temporarilSimAdminy change working directory
./ConvertMPD.sh $DASHContent MultiRate.mpd $Delay
#./ConvertMPD.sh $DASHContent2 MultiRate.mpd $Delay2

chmod 777 $DASHContent/*
#chmod 777 $DASHContent2/*

echo "Done"

#Starting the FLUTE Sender After Sleep
#echo "Starting FLUTE Sender in $(($Delay - $x)) seconds"
#sleep $(($Delay - $x))

#Make sure that sudo route -n add -net  224.0.0.0 netmask 240.0.0.0 dev eth0 is run...Check results with route -n

#Kill any previous leftovers
killall flute_sender


#(cd $FLUTESender && ./flute -S -r:$bitRate -B:$DASHContent -Q -f:$fdtVid -m:224.1.1.1 -p:4000 -t:1 -v:4 -y:$videoSegDur -Y:$videoSegDur -J:$Log)
(cd $FLUTESender && ./flute_sender -S -r:$bitRate -B:$DASHContent -f:$DASHContent/$fdtMPD -d:$sdp -Y:$encodingSymbolsPerPacket -J:$Log -C&)
(cd $FLUTESender && ./flute_sender -S -r:$bitRate -B:$DASHContent -f:$DASHContent/$fdtVid -d:$sdp2 -y:$DASHContent/$FLUTEVideoInput -Y:$encodingSymbolsPerPacket -J:$Log2&)
(cd $FLUTESender && ./flute_sender -S -r:$bitRate -B:$DASHContent -f:$DASHContent/$fdtAud -d:$sdp3 -y:$DASHContent/$FLUTEAudioInput -Y:$encodingSymbolsPerPacket -J:$Log3&)

#Sending of second video
#(cd $FLUTESender && ./flute_sender -S -r:$bitRate -B:$DASHContent -f:$DASHContent/$fdtMPD -d:$sdp4 -Y:$encodingSymbolsPerPacket -J:$Log4&)
#(cd $FLUTESender && ./flute_sender -S -r:$bitRate -B:$DASHContent2 -f:$DASHContent2/$fdtVid -d:$sdp5 -y:$DASHContent2/$FLUTEVideoInput -Y:$encodingSymbolsPerPacket -J:$Log5&)
#(cd $FLUTESender && ./flute_sender -S -r:$bitRate -B:$DASHContent2 -f:$DASHContent2/$fdtAud -d:$sdp6 -y:$DASHContent2/$FLUTEAudioInput -Y:$encodingSymbolsPerPacket -J:$Log6 && fg)

