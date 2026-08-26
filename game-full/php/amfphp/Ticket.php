<?php
class Ticket {

    public static function createTicket($db, $userId) {
        //Todo
        $ticket = "MSPPS,".$userId.",MSPPS-CODE,".strval(Date('Y-m-d\TH:i:s')).",".$this->GenerateBase64String().",";
        return $ticket;
    }

    public static function verifyTicket($db, $ticket, $userId) {
        //Todo
    }

    private function GenerateBase64String() {
        $stream = tmpfile();
        stream_filter_append($stream, 'convert.base64-encode', STREAM_FILTER_WRITE, array('line-length' => 24));
        fwrite($stream, rand(100000000, 9999999999).rand(100000000, 9999999999));
        fseek($stream, 0);
        $encoded = stream_get_contents($stream);
        fclose($stream);
        return $encoded;
    }

}