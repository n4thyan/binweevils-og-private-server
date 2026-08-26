const WebSocket = require('ws');
const xmlsocket = require("./xmlsocket")();
const host = '127.0.0.1',
    port = 10843;
const wss = new WebSocket.Server({ port: port, ip: host });
var timeout = 300;
let timerTimeout = setTimeout(() => wss.close(), timeout * 1000);

wss.on('connection', function connection(ws) {
   ws.on('message', function incoming(message) {
      clearTimeout(timerTimeout);
      console.log('received: %s', message);
      if(message == "friends/get-list{}"){
         ws.send('{"invites":[],"buddies":[],"httpCode":200,"responseCode":2,"cn":"friends.get-list"}');
      }
      if(message == 'weevil/get-notifications{}'){
         ws.send('{"buddy-requests":0,"nest-news":0,"conversations":0,"httpCode":200,"responseCode":1,"cn":"weevil.get-notifications"}');
      }
   });
   console.log("CONNECTION");
   ws.send('{"httpCode":200,"responseCode":1,"cn":"login.notify-websocket-connection"}');
});

console.log('Web socket server running at ' + host + ':' + port);
/*
private static function startRoomRequest() : void
      {
         var _loc1_:Responder = null;
         var _loc2_:String = null;
         var _loc3_:String = null;
         if(!useNodeJS)
         {
            _loc1_ = new Responder(onRoomRequestResponse,onRoomRequestError);
            _loc2_ = "" + ActorSession.getActorId();
            _loc3_ = AMSCommunicationManager.getInstance().getSessionHash();
            if(_isPrivateGame)
            {
               roomCon.call(ChatServerCommands.JOIN_PRIVATE_ROOM,_loc1_,_curRoomType,_existingRoomStr,_loc2_,_loc3_);
            }
            else if(_existingRoomStr != "")
            {
               roomCon.call(ChatServerCommands.CREATE_PRIVATE_ROOM,_loc1_,_existingRoomStr,_loc2_,_loc3_);
            }
            else
            {
               roomCon.call(ChatServerCommands.JOIN_PUBLIC_ROOM,_loc1_,_curRoomType,_loc2_,_loc3_);
            }
         }
         else if(isMinigameType(_curRoomType) == true)
         {
            multiplayerGamesConnectionService.roomRequestForMultiplayerGames(_existingRoomStr);
         }
         else
         {
            roomRequestForChatrooms();
         }
      }
         if(message[0] == 210) {
      var response = ["message",
         {
            "messageType":210,
            "messageContent":{
               "actorId":message[1]['messageContent']['actorId'],
               "animation":message[1]['messageContent']['animation']
            }
         }
      ];
      ws.send("42"+JSON.stringify(response));

   }
*/