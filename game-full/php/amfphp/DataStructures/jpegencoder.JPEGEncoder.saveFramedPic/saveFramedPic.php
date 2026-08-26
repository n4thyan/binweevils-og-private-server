<?php
    function saveFramedPic($db, $data){
        $stats = $db->GetWeevilStats($data[0]);
        $myNest = $data[1] == $_COOKIE['weevil_name'] ? 1:0;
        $frameDetails = json_decode($db->GetFrameDetails($data[4], $myNest));

        if(intval($stats['activated']) == 1){
            if($frameDetails->price > $data[6] || $frameDetails->price < $data[6]){
                $db->GrantBan(time() + 24*60*60, $data[0]);
                return 2;
            }
            else{
                if($stats['mulch'] >= $frameDetails->price){
                    $image = $db->SaveSnapshot($data[0], $data[4], $frameDetails->frameSwf, $data[5]);
                    $blob = $data[3]->jpegstream->data;
                    file_put_contents('/var/www/play/php/amfphp/Images/sp'.strval($image).'.jpg', $blob);
                    if($myNest == 0){
                        $db->giveBusinessEarnings($data[1], 'Photo Studio', $frameDetails->price);
                    }
                    $db->removeWeevilMulch($stats['username'], $frameDetails->price);
                    return 1;
                }
                else return 3;
            }
        }
        else return 3;
    }
  
?>