<?php

class sock {

    private $address = "";
    private $port = 0;
    private $sock;
    private $byteLength = 2048;
    private $areaByteLength = 10000023;

    public function __construct($url, $p) {
        $this->address = gethostbyname($url);
        $this->port = $p;
    }

    public function ConnectToSocket($close = false) {
        //set_time_limit(0);
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Could not create");
        $result = socket_connect($this->sock, $this->address, $this->port) or die("Could not connect");

        if($close) {
            socket_close($this->sock);
            return true;
        }
        else return true;
    }

    public function CloseSocket() {
        socket_close($this->sock);
    }

    public function GrabRoomList() {
        $this->SendRawPackets("<policy-file-request/>");
        $result = socket_read($this->sock, $this->byteLength) or die("Could not read");
        $this->SendRawPackets("<msg t='sys'><body action='verChk' r='0'><ver v='154' /></body></msg>");
        $result = socket_read($this->sock, $this->byteLength) or die("Could not read");

        $this->SendRawPackets("<msg t='sys'><body action='getRmList' r='-1'></body></msg>");
        $result = socket_read($this->sock, $this->byteLength) or die("Could not read");
        socket_close($this->sock);
        return $result;
    }

    public function LoginToBinWeevils($server_name, $user, $logkey) {
        $this->SendRawPackets("<policy-file-request/>");
        $result = socket_read($this->sock, $this->byteLength) or die("Could not read");
        $this->SendRawPackets("<msg t='sys'><body action='verChk' r='0'><ver v='154' /></body></msg>");
        $result = socket_read($this->sock, $this->byteLength) or die("Could not read");
        $this->SendRawPackets("<msg t='sys'><body action='login' r='0'><login z='" . $server_name . "'><nick><![CDATA[" . $user . "]]></nick><pword><![CDATA[" . $logkey . "]]></pword></login></body></msg>");
        $result = socket_read($this->sock, $this->byteLength) or die("Could not read");

        if(strpos($result, "success") !== false) {
            //$this->SendRawPackets("%xt%login%2#4%" . rand() . "%FlumsFountain%62.78016494587064%0%247.67837218940258%-180%0%190%");
            //$result = socket_read($this->sock, $this->areaByteLength);
            //echo $result;

            //$this->SendRawPackets("<msg t='sys' bid='197158'><body action='roomB' r='-1'><b id='197158' /></body></msg>");
            //$result = socket_read($this->sock, $this->byteLength);
            //echo $result;

            //$this->SendRawPackets("<msg t='sys'><body action='addB' r='-1'><n>Developer</n></body></msg>");
            //$result = socket_read($this->sock, $this->byteLength);
            //echo $result;
            return true;
        }
    }

    public function SendBuddyRequest($buddyname) {
        $this->SendRawPackets("<msg t='sys'><body action='addB' r='-1'><n>$buddyname</n></body></msg>");
        $result = socket_read($this->sock, $this->byteLength);
        return $result;
    }

    public function GetUsersInArea($loc, $user) {
        $this->SendRawPackets("%xt%login%2#4%" . rand() . "%" . explode(':', $loc)[1] . "%62.78016494587064%0%247.67837218940258%-180%0%" . explode(':', $loc)[0] . "%");
        $result = socket_read($this->sock, $this->areaByteLength) or die("Could not read");
        
        if(strpos($result, "<n><![CDATA[" . $user . "]]></n>") !== false) {
            $weevils = array();

            $arr = explode("</u>", $result);
            foreach($arr as $list) {
                $arr2 = explode("<n><![CDATA[", $list);
                $arr3 = explode("]]></n>", $arr2[1]);
                $weevil_name = $arr3[0];

                if($weevil_name != $user && $weevil_name != null && $weevil_name != "") {
                    array_push($weevils, $weevil_name);
                }
            }

            socket_close($this->sock);
            return $weevils;
        }

        return null;
    }

    public function GetUsersInAreaWithInfo($loc, $user) {
        $this->SendRawPackets("%xt%login%2#4%" . rand() . "%" . explode(':', $loc)[1] . "%62.78016494587064%0%247.67837218940258%-180%0%" . explode(':', $loc)[0] . "%");
        $result = socket_read($this->sock, $this->areaByteLength) or die("Could not read");
        
        if(strpos($result, "<n><![CDATA[" . $user . "]]></n>") !== false) {
            $weevils = array();

            $arr = explode("</u>", $result);
            foreach($arr as $list) {
                $arr2 = explode("<n><![CDATA[", $list);
                $arr3 = explode("]]></n>", $arr2[1]);
                $weevil_name = $arr3[0];

                $owns_hats = $this->WeevilOwnsHats($list);

                $arr2 = explode("<u i='", $list);
                $arr3 = explode("' m='0'>", $arr2[1]);
                $userid = $arr3[0];

                $arr2 = explode("n='r' t='n'><![CDATA[", $list);
                $arr3 = explode("]]></var>", $arr2[1]);
                $weevil_rotation = $arr3[0];

                $arr2 = explode("n='x' t='n'><![CDATA[", $list);
                $arr3 = explode("]]></var>", $arr2[1]);
                $weevil_x = $arr3[0];

                $arr2 = explode("n='y' t='n'><![CDATA[", $list);
                $arr3 = explode("]]></var>", $arr2[1]);
                $weevil_y = $arr3[0];

                $arr2 = explode("n='z' t='n'><![CDATA[", $list);
                $arr3 = explode("]]></var>", $arr2[1]);
                $weevil_z = $arr3[0];

                if($weevil_name != $user && $weevil_name != null && $weevil_name != "") {
                    array_push($weevils, $weevil_name . " => In-Game UserID: " . $userid . ", Has Hats?: " . (!!$owns_hats ? "Yes" : "No") . 
                    ", Rotation: " . $weevil_rotation . ", X: " . $weevil_x . ", Y: " . $weevil_y . ", Z: " . $weevil_z);
                }
            }

            socket_close($this->sock);
            return $weevils;
        }

        return null;
    }

    public function OnlineWeevils(){
        $this->SendRawPackets("%xt%login%-1#0%onlineweevils%");
        $result = socket_read($this->sock, $this->byteLength) or die("Could not read");
        return $result;
    }

    private function WeevilOwnsHats($data) {
        if(strpos($data, "n='apparel'") !== false)
        return true;
        else return false;
    }

    public function SendRawPackets($packet) {
        socket_write($this->sock, $packet . "\0", strlen($packet . "\0")) or die("Unable to send packets");
    }

    public function ReturnSocket() {
        return $this->sock;
    }

}

?>