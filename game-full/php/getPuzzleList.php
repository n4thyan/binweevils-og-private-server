<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $userID = $_COOKIE['weevil_name'];
    $typeID = $_POST['typeID'];
    $puzzleData = getPuzzleTypeData($typeID);

    if($puzzleData != null) {
        $levelList     = '';
        $nameList      = '';
        $configList    = '';
        $completedList = '';

        $typeName       = $puzzleData['name'];
        $gamePath       = $puzzleData['gamePath'];
        $configBasePath = $puzzleData['configBasePath'];
        $locName        = $puzzleData['locName'];
        $table1         = $puzzleData['mainTableName'];
        $table2         = $puzzleData['progressTableName'];
        $puzzleListData = getPuzzleListData($table1, $table2);

        if($puzzleListData != null) {
            foreach($puzzleListData as $puzzleList) {
                if($nameList != '') {
                    $levelList     .= '|';
                    $nameList      .= '|';
                    $configList    .= '|';
                    $completedList .= '|';
                }

                $levelList  .= $puzzleList['minLevel'];
                $nameList   .= $puzzleList['name'];
                $configList .= $puzzleList['configPath'];

                if($puzzleList['completed'] == 1)
                $completed = 1;
                else
                $completed = 0;

                $completedList .= $completed;
            }

            echo 'typeName=' . $typeName . '&gamePath=' . $gamePath . '&configBasePath=' . $configBasePath . '&locName=' . $locName . '&levelList=' . $levelList . '&nameList=' . $nameList . '&configList=' . $configList . '&completedList=' . $completedList . '&b=r';
        }
        else
        echo 'responseCode=999';
    }
    else
    echo 'responseCode=999';
}
else
echo 'repsonseCode=999';
//echo 'typeName=wordsearch&gamePath=externalUIs/wordSearch_11_02_11.swf&configBasePath=externalUIs/wordSearch/&locName=doing a wordsearch&levelList=0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|1|1|2|2|3|3|4|4|4|5|5|5|5|6|6|7|7|7|8|8|8|9|9|9|10|10|10|10|11|11|12|12|12|13|13|14|14|15|15|16|16|17|17|18|18|19|19|20|20|21|21|22|22|23|23|24|25|25|26|27|28|29|30|31|32|33|34|35&nameList=Sky Kids Wordsearch|StarFighter WordSearch|HYBW wordseach|Panini wordsearch|AppGear2 Wordsearch|Sports Stars Wordsearch|CharacterOptions Wordsearch|Diary of a Wimpy Kid 3 Wordsearch|BW Sticker Book|Ninja Turtles Wordseach|Honey Monster 2011|leapFrog wordsearch|A Monster In Paris Wordsearch|Honey Monster 2011 B|Zookeeper Wordseach|Sky Kids Wordsearch 2|Horrid Henry|Honey Monster 2011 C|deadly360 Wordsearch|Live And Deadly Wordsearch|Innocent Wordsearch 2011|ReoDVD wordsearch|chedds wordsearch|Dr Who adCampaign|Get Santa|Turbo Wordsearch|Epic AdCampaign|WWD Wordsearch|RLB_wordsearch|Rocks Word Search|HYBW2014 Wordsearch|BoomCo Wordsearch|Postman Pat Wordsearch|TomGates wordsearch|Ball Games|Pets|Body Talk|Flowers|Eat Your Greens|Weather|Musical Instruments|Recycling|Party Time|Halloween|Ice Cream Flavours|Football Frenzy|Art Attack|African Countries |Monopoly|Languages|Fiesta|W is for ...|School|Fairy Tale|TOP SECRET|Sea Life|Tennis|Science Lab|Animals|Tinks Tree|Celebrity Party|Lost Bin Pet|Types of Fruit|At The Circus|Zoom Zoom... Weevil Wheels!|Vehicles|Fame and Fortune|Hockey|Types of Job|Dinosaurs|Insects|Afternoon Tea Time |Secret Weevil Service|Christmas|Types of Trees|Summer Holidays|Sports|Asian Countries |Nest Inspector|European Countries|Film Genres|Types of Fish|Types of Rubbish|Valentines Day|Z is for...|Mathematics|Scooby Doo|Mythical Monsters|Space|Elements|Types of Pasta|Famous Painters|Skateboarding|African Capitals|Gardening|Types of Cheese|M is for...|European Capitals|Plumbing|Types of Birds|Airplanes|Photography&configList=wordsearch_sky.xml|starFighter_wordSearch.xml|HYBW_wordsearch.xml|panini_wordsearch.xml|appGear2_wordsearch.xml|sportsStars_wordsearch.xml|characterOptions_wordsearch.xml|doawk3_wordsearch.xml|BWstickerBook_wordsearch.xml|TMNT_wordsearch|wordsearch.xml|leapFrog_wordsearch.xml|MIP_wordsearch.xml|wordsearch2.xml|wordsearch_zoo.xml|wordsearch_sky2.xml|wordsearch.xml|wordsearch3.xml|deadly360_wordsearch.xml|live_and_deadly_wordsearch.xml|wordsearch_Innocent2011|wordsearch_RioDVD.xml|chedds_wordsearch.xml|wordsearch_drWho.xml|getSanta_ws.xml|turbo_wordsearch.xml|epic_wordsearch.xml|WWD_wordsearch.xml|RLB_wordsearch.xml|RBA_wordsearch.xml|HYBW2014_wordsearch.xml|boomco_wordsearch.xml|PP_wordsearch.xml|tomGates_wordsearch.xml|ballgames.xml|pets.xml|bodyparts.xml|flower.xml|vegetables.xml|weather.xml|musical_instruments.xml|recycling.xml|parties.xml|halloween.xml|ice_cream.xml|football.xml|art.xml|african.xml|monopoly.xml|languages.xml|fiesta.xml|wwords.xml|school_ws.xml|fairy_tale.xml|goodvsweevil.xml|sealife.xml|tennis.xml|science_lab.xml|animals_ws.xml|tinks_tree.xml|celeb_party.xml|lost_binpet.xml|fruits_ws.xml|circus.xml|weevil_wheels.xml|vehicles.xml|famous.xml|hockey.xml|Type of Jobs.xml|DINOSAURS.xml|insects.xml|AfternoonTEA.xml|secret.xml|christmas_ws.xml|trees.xml|summer.xml|wordSearch4.xml|asiancountries_ws.xml|nestinspector.xml|europeancountries_ws.xml|filmgenres.xml|fish.xml|wordSearch2.xml|valentines.xml|zwords.xml|maths.xml|scooby_doo.xml|monsters.xml|space.xml|elements.xml|pasta.xml|painters.xml|skateboarding.xml|africancapitals.xml|gardening.xml|cheese.xml|mwords.xml|europeancapitals.xml|plumbing.xml|birds.xml|airplanes.xml|photography.xml&completedList=0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|1|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0&b=r'
?>