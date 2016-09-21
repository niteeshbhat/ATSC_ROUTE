<?php
// Returns offset of the Tune-in instant


ini_set('memory_limit','-1');//remove memory limit

$currDir=dirname(__FILE__);

$channel = $_REQUEST['channel'];
$TuneinPhp = $_REQUEST['TuneinPhp'];

$DASHContentBase="DASH_Content";
$DASHContentDir=$DASHContentBase . (string)$channel;
$DASHContent=$currDir . "/" . $DASHContentDir;
$OriginalMPD= "MultiRate_Dynamic.mpd";

while (!glob($DASHContent."/$OriginalMPD")) usleep(5000);



$MPD = simplexml_load_file($DASHContent . "/" . $OriginalMPD);
    if (!$MPD)
	    die("Failed loading XML file");

    $dom_sxe = dom_import_simplexml($MPD);
    if (!$dom_sxe) 
    {
        echo 'Error while converting XML';
        exit;
    }
    
    $dom = new DOMDocument('1.0');
    $dom_sxe = $dom->importNode($dom_sxe, true);
    $dom_sxe = $dom->appendChild($dom_sxe);
    
    $periods = parseMPD($dom->documentElement);
    
    
    $MPDNode = &$periods[0]['node']->parentNode;
    
    $MPD_AST = $MPDNode->getAttribute("availabilityStartTime");
    preg_match('/\.\d*/',$MPD_AST,$matches);
    $fracAST = "0" . $matches[0];
    $originalAST = new DateTime($MPD_AST);   
    $deltaTimeASTTuneIn = $TuneinPhp - ($originalAST->getTimestamp() + $fracAST);  //Time elapsed between the original AST and Tune-in time
    
     $videoSegmentTemplate = &$periods[0]['adaptationSet'][0]['representation'][0]['segmentTemplate']['node'];
     $videoTimescale = $videoSegmentTemplate->getAttribute("timescale");
        $videoSegmentDuration = $videoSegmentTemplate->getAttribute("duration");
        $videoStartNum = $videoSegmentTemplate->getAttribute("startNumber");
    
    $deltaTimeASTTillTunein=($deltaTimeASTTuneIn*$videoTimescale/$videoSegmentDuration) + $videoStartNum;
    echo $deltaTimeASTTillTunein;
    
function &parseMPD($docElement)
{
    foreach ($docElement->childNodes as $node)
    {
        //echo $node->nodeName; // body
        if($node->nodeName === 'Location')
            $locationNode = $node;
        if($node->nodeName === 'BaseURL')
            $baseURLNode = $node;    
        if($node->nodeName === 'Period')
        {
            $periods[]['node'] = $node;

            $currentPeriod = &$periods[count($periods) - 1];
            foreach ($currentPeriod['node']->childNodes as $node)
            {
                if($node->nodeName === 'AdaptationSet')
                {
                    $currentPeriod['adaptationSet'][]['node'] = $node;
                    
                    $currentAdaptationSet = &$currentPeriod['adaptationSet'][count($currentPeriod['adaptationSet']) - 1];                    
                    foreach ($currentAdaptationSet['node']->childNodes as $node)
                    {
                        if($node->nodeName === 'Representation')
                        {
                            $currentAdaptationSet['representation'][]['node'] = $node;
                            
                            $currentRepresentation = &$currentAdaptationSet['representation'][count($currentAdaptationSet['representation']) - 1];

                            foreach ($currentRepresentation['node']->childNodes as $node)
                            {
                                if($node->nodeName === 'SegmentTemplate')
                                    $currentRepresentation['segmentTemplate']['node'] = $node;
                            }
                        }
                    }
                }
            }            
        }
    }
    
    return $periods;
}   
    
?>
     
