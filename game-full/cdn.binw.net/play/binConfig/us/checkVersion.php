<?php

// increment only when it is essential that users clear their cache for the site to function
$latestVersion = "14";  // site core files version. This is only changed when we want to force the user to clear their cache.

// increment with every new version of core.swf and name the .swf file accordingly (eg. core4.swf) - MAKE SURE THE SWF FILE IS UPLOADED FIRST!
$coreVersion = "140"; // leave this as zero in version on beaker

// increment with every new version of the video player and name the .swf file accordingly. Make sure the swf is uploaded to both the video streaming server (for verification) and wherever it's to be hosted
$VODplayerVersion = "15";
// increment with every new version of binConfig/VOD.xml and name the .xml file accordingly.
$VODcontentVersion = "6";

// increment with every update of binConfig/locationDefinitions.xml
// you could also put a date, like 2010080401 (yyyymmdd{SEQUENCE})
$locDefVersion = 342;
// This is a description of all the areas, and items within each area

// increment with every update of binConfig/nestLocDefs.xml
// you could also put a date, like 2010080401 (yyyymmdd{SEQUENCE})
$nestLocDefVersion = 44;
// binConfig/nestLocDefs.xml is the maximum structure of the rooms in a nest
// - though not all will be used all the time, eg, not bought & not tycoon

// we are passed in the version of the main swfs & the locDefinitions
$version = (isset($_POST['version']) ? $_POST['version'] : 0);
$rand    = (isset($_POST['rand'])    ? $_POST['rand']    : 0);  // gndn


$vOK = 0;   // assume the worst
if ($version >= $latestVersion) {
    $vOK = 1;// we have got the latest
}


echo "vOK=$vOK&core=$coreVersion&VODplayer=$VODplayerVersion&VODcontent=$VODcontentVersion&locDef=$locDefVersion&nestDef=$nestLocDefVersion&p=s";
