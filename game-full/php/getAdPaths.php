<?php
if (isset($_REQUEST['area']))
   $area = $_REQUEST['area'];
else $area = 99;
switch ($area) {
	case "0": // LOADER PAGE
		//echo "paths=BinWeevils/LoaderPage_BAFTA_02.swf";
		//echo "paths=BinWeevils/Loader_magazine_NOW.swf,BinWeevils/Loader_BW_books_03.swf";
		echo "paths=0";
		//echo "paths=pussInBoots/Loader_pussInBoots.swf,deadHeadz/deadHeadz_Loader_02.swf,innocent/kitchen_Loader.swf,innocent/kitchen_Loader.swf,BinWeevils/LoaderPage_LabsLab_02.swf,BinWeevils/LoaderPage_Crosswords_02.swf,BinWeevils/LoaderPage_TinksBlocks_02.swf,BinWeevils/LoaderPage_WeevilWeekly_02.swf,BinWeevils/LoaderPage_Shopping4NestItems_02.swf,BinWeevils/LoaderPage_MulchTastic_02.swf,BinWeevils/Loader_01_Mulch_topUps.swf,BinWeevils/LoaderPage_TycoonBonanza_002_aa.swf,BinWeevils/Loader_magazine_NOW.swf,BinWeevils/Loader_magazine_NOW.swf,BinWeevils/Loader_magazine_NOW.swf,BinWeevils/Loader_magazine_NOW.swf,BinWeevils/Loader_magazine_NOW.swf,BinWeevils/Loader_BW_books_03.swf,BinWeevils/Loader_BW_books_03.swf,BinWeevils/Loader_BW_books_03.swf";
		break;
	case "1":
		//echo "ad1=0&ad2=ads/spectrobes/spectrobes-mpu-971515_30fps.swf";
		break;
	case "2": // OUTSIDE SHOPPING MALL
		//echo "adPath=ads/honeyMonsters/honeyMonsters2011.swf";
		//echo "&adLink=http://www.findhoneymonster.co.uk/bwreg";
		//echo "adPath=ads/sky/SkyKids_Immersive.swf,ads/mrPoppersPenguins/MrPopper_Immersive_small.swf";
		//echo "&adLink=0,0";
		//echo "adPath=ads/horridHenry/2011/horridhenry_Immersive_smallv2.swf,ads/kreo/kreo_Immersive_Small.swf";
		//echo "&adLink=128,http://ad.doubleclick.net/clk;244226690;67958358;z";
		//echo "adPath=ads/binweevils/Video1_sign4.swf";
		//echo "&adLink=externalUIs/survey_04_05_11.swf*surveyID:9";
		//echo "adPath=ads/binweevils/breakfastsign_001.swf";
		//echo "&adLink=externalUIs/survey_07_04_11.swf*surveyID:2";
		//echo "adPath=ads/binweevils/booksign_001.swf,ads/Rio/Immersive_small_10.swf,ads/chelsea2011/Banner Ads_Chelsea_Large_03_aa.swf";
		//echo "&adLink=externalUIs/survey_07_04_11.swf*surveyID:1,http://www.rio-themovie.co.uk,http://bridgekids.chelseafc.com/en-GB/home/default.html?ref=BWBBOARD";
		//echo "adPath=ads/binweevils/toysign_001.swf";
		//echo "&adLink=externalUIs/surveyNew_22032011.swf";
		//echo "ad1Path=0&ad2Path=ads/gnomeoAndJuliet/gj_vidMPU_richload5_170.4x174_ver2.swf&ad2Link=http://servedby.flashtalking.com/click/1/14850;96566;50126;211;0/?%26url=263355";		
		//echo "ad1Path=0&ad2Path=0";
		echo "adPath=0";
		break;
	case "3": // RUM'S COVE
		echo "adPath=ads/monsterInParis/Immersive_MIP.swf,ads/binweevils/Immersive_Small_02_InclJokeBook.swf";
		echo "&adLink=111,http://www.binweevils.com/shop";
		
		//echo "ad1Path=ads/sackBoy/IMM_Ad_sackBoy.swf&ad1Link=http://mini-mix-online.com/lbp/uk/";
		//echo "adPath=ads/Rio/Immersive_small_09.swf";
		//echo "&adLink=http://www.rio-themovie.com";
		//echo "ad1Path=ads/RealLifeBugs/Immersive_RLB_small.swf&ad1Link=http://www.reallifebugs.co.uk";
		//echo "adPath=0";
		//echo "&adLink=0";
		//echo "ad1Path=ads/footballSuperstars/footballSuperstars.swf&ad1Link=http://footballsuperstars.com/football/?affid=452da0d4e9c99564a1b58e1eb8f91778";
		//echo "ad1Path=ads/furryVengeance/immersiveAd_Furry_small.swf&ad1Link=103";
		//echo "ad1Path=ads/toothFairy/toothFairy_Ad.swf&ad1Link=http://hmv.com/hmvweb/displayProductDetails.do?ctx=280;0;-1;-1;-1%26sku=723739%26WT.mc_id=101993";
		//echo "ad1Path=ads/alphaOmega/BannerAd_AlphaOmega_Small.swf&ad1Link=http://bs.serving-sys.com/BurstingPipe/adServer.bs?cn=tf%26c=20%26mc=click%26pli=1648957%26PluID=0%26ord=[timestamp]";
		//echo "ad1Path=ads/africaUnited/immersiveAd_AfricaUnited.swf&ad1Link=112";
		//echo "ad1Path=ads/despicableMe/BannerAd_DespicableMe.swf&ad1Link=http://bs.serving-sys.com/BurstingPipe/adServer.bs?cn=tf%26c=20%26mc=click%26pli=1853008%26PluID=0%26ord=%5Btimestamp%5D";
		//echo "ad1Path=ads/theden/sony__ImmAd_NOV2010_small.swf&ad1Link=http://www.playstationden.co.uk/cards/";
		//echo "ad1Path=ads/ravingRabbids/IMM_Ad_RR.swf&ad1Link=http://bs.serving-sys.com/BurstingPipe/adServer.bs?cn=tf%26c=20%26mc=click%26pli=1962712%26PluID=0%26ord=[timestamp]";
		//echo "ad1Path=ads/fred/IMM_Ad_Fred_small.swf&ad1Link=http://bs.serving-sys.com/BurstingPipe/adServer.bs?cn=tf%26c=20%26mc=click%26pli=1913090%26PluID=0%26ord=[timestamp]";
		//echo "ad1Path=ads/Megamind/MMadIQ.swf&ad1Link=0";
		//echo "adPath=ads/uDraw/BannerAds_uDraw_74_03.swf";
		//echo "&adLink=http://altfarm.mediaplex.com/ad/ck/13329-123369-39038-5";
		break;
	case "4": // FLEM MANOR
		echo "ad1Path=0";
		//echo "ad1Path=ads/CBBC/hh_viking_mpu_1_day.swf&ad2Link=http://ad-emea.doubleclick.net/clk;225276483;49070789;w";
		//echo "ad1Path=ads/CBBC/hh_history_mpu2_74.swf&ad1Link=http://ad-emea.doubleclick.net/clk;225276483;49070789;w";
		//echo "ad1Path=ads/swizzels/Ad_4_shoppingMall_06_May_10.swf&ad1Link=externalUIs/comps/comps_050510.swf";
		//echo "ad1Path=ads/kidsco/immersiveAd_kidsco.swf&ad1Link=http://www.mykidscotv.com/au/";
		break;
	case "5": // PARTY BOX
		echo "ad1Path=0";
		//echo "ad1Path=ads/CBBC/hh_history_mpu2_74.swf&ad1Link=http://ad-emea.doubleclick.net/clk;225276483;49070789;w";
		//echo "ad1Path=ads/despicableMe/BannerAd_DespicableMe.swf&ad1Link=http://bs.serving-sys.com/BurstingPipe/adServer.bs?cn=tf%26c=20%26mc=click%25pli=1853008%26PluID=0%26ord=[timestamp]";
		//echo "ad1Path=ads/theden/sony__ImmAd_NOV2010_small.swf&ad1Link=http://www.playstationden.co.uk/cards/";
		//echo "ad1Path=ads/Megamind/MMadAM.swf&ad1Link=0";
		break;
	case "6": // DIRT VALLEY 1
		echo "ad1Path=0";
		break;
	case "7": // RIGG'S PALLADIUM
		echo "ad1Path=0&ad2Path=0";
		//echo "ad1Path=ads/binweevils/riggs_ad_board_left.swf&ad1Link=114&ad2Path=ads/binweevils/riggs_ad_board_newNestItem_racingCarBed.swf&ad2Link=105";
		break;
}
?>