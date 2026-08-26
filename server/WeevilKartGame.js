require("./Weevil");
require("./BinWeevils");
const e = require("express");
var ExtensionHelper = require("./ExtensionHelper");
var GameFinishObjects = require("./GameFinishObjects");

class WeevilKartGame {

    constructor(man, playerCount, area, gameID, roomID) {

        this.m_gameID = parseInt(gameID);
        this.m_playersReady = 0;
        this.m_playersDrivenOff = 0;
        this.m_playerCount = parseInt(playerCount);
        this.m_initialPlayerCount = parseInt(playerCount);
        this.m_currentPlayerCount = 0;
        this.m_roomID = parseInt(roomID);
        this.m_gameStarted = false;
        this.m_gaveWinnerPoint = false;

        this.m_gameReady = false;
        this.m_playersJoined = 0;
        this.m_sentDriveOff = false;
        this.m_userReadyTimedOut = false;
        this.m_players = [];
        this.m_playersDrivenOffList = [];
        this.m_playersReadyList = [];
        this.m_playersStarted = [];
        this.m_playersFinished = [];
        this.m_gameFinishObjects = [];
        this.m_startTime = 0;
        this.m_playersHash = new Map();
        this.m_playerColoursHash = new Map();
        this.WEEVIL_KART_GAME_TYPE = "b";
        this.m_area = area;
        this.m_manager = man;

        this.m_timer = undefined;
        this.m_drivenOffTimer = undefined;
        this.m_userReadyTimer = undefined;
        this.m_pingTimer = undefined;
        this.m_roomObj = {};
        this.m_pingingUsers = false;

        this.PREPARE_GAME = 1;
        this.GET_READY = 2;
        this.START = 3;
        this.WAITING_FOR_OTHERS = 0;
        this.DRIVE_OFF = 4;
        this.STARTING_WAITING_FOR_READY_MESSAGES = 5;
        this.m_state = 0;

        this.getRoomObj(this.m_roomID);
        this.setMPlayersDefault();
    }

    getRoomObj(roomID) {
        var struct = this;

        new ExtensionHelper().getRoom(roomID, function(data) {
            struct.m_roomObj = data;
        });
    }

    setMPlayersDefault() {
        for(var i = 0; i < this.m_initialPlayerCount; i++) {
            this.m_players.push(null);
        }
    }

    getUserName(id) {
        try {
            if(this.m_players[id] != null)
            return this.m_players[id].nickname;
            else
            return null;
        }
        catch(Exception) {
            console.log(Exception);
            return null;
        }
    }

    getUserCount() {
        return this.m_players.length;
    }

    kickAllUserWhoHaventSentDrivenOffMessages() {
        try {
            if(this.m_playersReady > 0) {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        var drivenOff = false;
    
                        for(var j = 0; j < this.m_playersDrivenOffList.length; j++) {
                            if(this.m_players[i].nickname == this.m_playersDrivenOffList[j].nickname) {
                                drivenOff = true;
                            }
                        }
    
                        if(!drivenOff) {
                            var weevil = this.m_players[i];
                            this.m_manager.forceUserDisconnect(weevil);
                            this.playerLeaveGame(weevil);
                            // do other things here
                        }
                    }
                }
    
                if(this.m_playersDrivenOff >= this.getNoPlayers() && !this.m_gameStarted) {
                    this.m_gameStarted = true;
                    //clearInterval(this.m_drivenOffTimer);
                    // do other things here
                    this.tellOtherUsersInRoomToResetGame(this.m_roomID);
                    this.prepareGame();
                }
            }
            else {
                for(var i = 0; i < this.m_players.length; i++) {
                    var weevil = this.m_players[i];
    
                    if(weevil != null) {
                        // do stuff here
                        this.m_manager.forceUserDisconnect(weevil);
                        this.m_manager.removeUserGameMapping(weevil.nickname);
                    }
                }
    
                this.m_manager.cleanUp(this);
                this.m_manager.removeGame(this.m_gameID);
                this.tellOtherUsersInRoomToResetGame(this.m_roomID);
            }
        }
        catch(Exception) {
            // nothing
            console.log(Exception);
        }
    }

    kickAllUserWhoHaventSentUserReadyMessages() {
        try {
            if(this.m_playersReady > 0) {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        var ready = false;
                        
                        for(var j = 0; j < this.m_playersReadyList.length; j++) {
                            if(this.m_players[i].nickname == this.m_playersReadyList[j].nickname)
                            ready = true;
                        }
    
                        if(!ready) {
                            var weevil = this.m_players[i];
                            this.m_manager.forceUserDisconnect(weevil);
                            this.playerLeaveGame(weevil);
                            // do other things here
                        }
                    }
                }
    
                if(this.m_playersReady >= this.getNoPlayers()) {
                    //clearInterval(this.m_userReadyTimer);
                    this.m_state = this.DRIVE_OFF;
                    this.m_currentPlayerCount = this.getNoPlayers();
                    this.m_sentDriveOff = true;
                    this.m_playersDrivenOff = 0;
                    this.m_playersDrivenOffList.length = 0;
                    //this.m_drivenOffTimer = setInterval(3000);
                    // do other stuff
                    this.sendDriveOffToAllUsersInRoom(this.getNextNonNullPlayer());
                }
            }
            else {
                for(var i = 0; i < this.m_players.length; i++) {
                    var weevil = this.m_players[i];
    
                    if(weevil != null) {
                        // do stuff here
                        this.m_manager.forceUserDisconnect(weevil);
                        this.m_manager.removeUserGameMapping(weevil.nickname);
                    }
                }
    
                this.m_manager.cleanUp(this);
                this.m_manager.removeGame(this.m_gameID);
                this.tellOtherUsersInRoomToResetGame(this.m_roomID);
            }
        }
        catch(Exception) {
            // nothing
            console.log(Exception);
        }
    }

    getNextNonNullPlayer() {
        try {
            for(var i = 0; i < this.m_players.length; i++) {
                if(this.m_players[i] != null)
                return this.m_players[i];
            }
    
            return null;
        }
        catch(Exception) {
            console.log(Exception);
            return null;
        }
    }

    removeUserFromLists(weevil) {
        try {
            for(var i = 0; i < this.m_gameFinishObjects.length; i++) {
                if(this.m_gameFinishObjects[i] != null) {
                    var name = this.m_gameFinishObjects[i].getUser();
    
                    if(name.nickname == weevil.nickname && !this.m_gameFinishObjects[i].getAlreadyDeterminedPosition()) {
                        this.m_gameFinishObjects.splice(i, 1);
                    }
                }
            }
        }
        catch(Exception) {
            console.log(Exception);
            // nothing
        }
    }

    sendGetReadyMessage() {
        try {
            for(var i = 0; i < this.m_players.length; i++) {
                if(this.m_players[i] != null) {
                    this.m_players[i].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>" + this.WEEVIL_KART_GAME_TYPE + "</var><var n='command' t='s'>getReady</var></dataObj>]]></body></msg>");
                }
            }
        }
        catch(Exception) {
            console.log(Exception);
            // nothing
        }
    }

    sendStartMessage() {
        try {
            this.m_gameFinishObjects.length = 0;
                
            for(var i = 0; i < this.m_players.length; i++) {
                if(this.m_players[i] != null) {
                    this.m_players[i].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>" + this.WEEVIL_KART_GAME_TYPE + "</var><var n='command' t='s'>startRace</var></dataObj>]]></body></msg>");

                    var gfo = new GameFinishObjects();
                    gfo.setUser(this.m_players[i]);
                    this.m_gameFinishObjects.push(gfo);
                }
            }
        }
        catch(Exception) {
            console.log(Exception);
            // nothing
        }
    }

    actionPerformed(struct) {
        try {
            switch(struct.m_state) {
                case struct.PREPARE_GAME:
                    clearInterval(struct.m_timer);
                    struct.sendGetReadyMessage();
                    struct.m_state = struct.GET_READY;
                    struct.m_timer = setInterval(struct.actionPerformed, 2000, struct);
                    break;
                
                case struct.GET_READY:
                    clearInterval(struct.m_timer);
                    struct.sendStartMessage();
                    break;
            }
        }
        catch(Exception) {
            console.log(Exception);
            // nothing
        }
    }

    getGameID() {
        return this.m_gameID;
    }

    inPlayerList(weevil) {
        try {
            for(var i = 0; i < this.m_players.length; i++) {
                if(this.m_players[i] != null) {
                    if(this.m_players[i].nickname == weevil.nickname)
                    return true;
                }
            }
    
            return false;
        }
        catch(Exception) {
            console.log(Exception);
            return false;
        }
    }

    canAddPlayerToSlot(buggyNo) {
        try {
            if(this.m_players[parseInt(buggyNo)] == null)
            return true;
            else
            return false;
        }
        catch(Exception) {
            console.log(Exception);
            return false;
        }
    }

    getSlotName(id) {
        return "p" + id.toString();
    }

    getNoPlayers() {
        var count = 0;

        for(var i = 0; i < this.m_players.length; i++) {
            if(this.m_players[i] != null) count++;
        }

        return count;
    }

    canAddPlayerToPlayerList(weevil, buggyNo, colour) {
        try {
            var packet = "<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>" + this.WEEVIL_KART_GAME_TYPE + "</var><var n='command' t='s'>joinGame</var>";
            var packet2 = "<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>" + this.WEEVIL_KART_GAME_TYPE + "</var><var n='command' t='s'>playerJoinGame</var>";

            if(this.m_playersJoined < this.m_initialPlayerCount && !this.m_gameStarted && !this.m_gameReady && !this.inPlayerList(weevil)) {
                if(this.canAddPlayerToSlot(buggyNo)) {
                    this.m_players[buggyNo] = weevil;
                    this.m_playersHash.set(weevil.nickname, buggyNo);
                    this.m_playerColoursHash.set(weevil.nickname, colour);
                    packet += "<var n='success' t='s'>true</var>";
                    this.m_playersJoined++;

                    for(var i = 0; i < this.m_players.length; i++) {
                        if(this.m_players[i] == null) {
                            packet += "<var n='" + this.getSlotName(i) + "' t='s'>-1</var>";
                        }
                        else {
                            packet += "<var n='" + this.getSlotName(i) + "' t='s'>" + this.m_players[i].userID + "</var><var n='" + this.getSlotName(i) + "_kartClr' t='s'>" + this.m_playerColoursHash.get(this.m_players[i].nickname) + "</var>";
                        }
                    }

                    this.m_manager.addUserGameMapping(weevil.nickname, this);
                    packet2 += "<var n='" + this.getSlotName(buggyNo) + "' t='s'>" + this.m_players[buggyNo].userID + "</var><var n='" + this.getSlotName(buggyNo) + "_kartClr' t='s'>" + this.m_playerColoursHash.get(this.m_players[buggyNo].nickname) + "</var>";

                    if(this.m_playersJoined >= this.m_initialPlayerCount) {
                        this.m_gameReady = true;
                        this.m_state = this.STARTING_WAITING_FOR_READY_MESSAGES;
                        this.m_playersReadyList.length = 0;
                        this.m_userReadyTimedOut = false;

                        packet += "<var n='gameReady' t='s'>true</var>";
                        packet2 += "<var n='gameReady' t='s'>true</var>";
                    }
                    else {
                        packet += "<var n='gameReady' t='s'>false</var>";
                        packet2 += "<var n='gameReady' t='s'>false</var>";
                    }
                    // send responses

                    packet += "</dataObj>]]></body></msg>";
                    packet2 += "</dataObj>]]></body></msg>";

                    weevil.send(packet);

                    for(var i = 0; i < this.m_players.length; i++) {
                        if(this.m_players[i] != null) {
                            if(this.m_players[i].nickname != weevil.nickname) {
                                this.m_players[i].send(packet2);
                            }
                        }
                    }
                    return true;
                }
                else {
                    packet += "<var n='success' t='s'>false</var></dataObj>]]></body></msg>";
                    weevil.send(packet);
                    weevil.modMsg("This happened because its not possible to put you in this kart as its taken. please contact BWR team for assistance.");
                    return false;
                }
            }
            else {
                this.m_gameStarted = false;
                this.m_gameReady = false; // trying to fix all of this
                packet += "<var n='success' t='s'>false</var></dataObj>]]></body></msg>";
                weevil.send(packet);
                weevil.modMsg("Something wrong happened. Please try again, and or relog and try again. If this does not work, please contact BWR team.");
                this.m_manager.freeGameSlot(this.m_initialPlayerCount, this.m_area);
                this.m_manager.removeUserGameMapping(weevil.nickname);
                return false;
            }
        }
        catch(Exception) {
            console.log(Exception);
            return false;
        }
    }

    setGameStartTime() {
        this.m_startTime = Date.now();
    }

    getTimeOffset() {
        var offset = Date.now() - this.m_startTime;
        return offset.toString();
    }

    prepareGame() {
        try {
            clearInterval(this.m_timer);
    
            this.setGameStartTime();
            this.m_state = this.PREPARE_GAME;
    
            for(var i = 0; i < this.m_players.length; i++) {
                if(this.m_players[i] != null) {
                    this.m_players[i].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>" + this.WEEVIL_KART_GAME_TYPE + "</var><var n='command' t='s'>prepareGame</var></dataObj>]]></body></msg>");
                }
            }
    
            this.m_timer = setInterval(this.actionPerformed, 3000, this);
        }
        catch(Exception) {
            console.log(Exception);
            // nothing
        }
    }

    containsUser(weevilName) {
        try {
            if(this.m_players == null)
            return false;

            for(var i = 0; i < this.m_players.length; i++) {
                if(this.m_players[i] != null) {
                    if(this.m_players[i].nickname == weevilName)
                    return true;
                }
            }

            return false;
        }
        catch(Exception) {
            console.log(Exception);
            return false;
        }
    }

    createFinishObject(weevil, time) {
        var res = new GameFinishObjects();
        res.setTime(time);
        res.setUser(weevil);
        return res;
    }

    swapGFO(i, j) {
        try {
            var gfo1 = this.m_gameFinishObjects[i];
            var gfo2 = this.m_gameFinishObjects[j];

            this.m_gameFinishObjects[i] = gfo2;
            this.m_gameFinishObjects[j] = gfo1;
        }
        catch(Exception) {
            console.log(Exception);
            // nothing
        }
    }

    sortGFObjects() {
        if(this.m_gameFinishObjects.length <= 1) return;

        var swapped = false;
        
        try {
            do {
                swapped = false;

                for(var i = 0; i < this.m_gameFinishObjects.length - 1; i++) {
                    if((this.m_gameFinishObjects[i + 1].getTime() < this.m_gameFinishObjects[i].getTime())) {
                        this.swapGFO(i + 1, i);
                        swapped = true;
                    }
                }
            }
            while(swapped);
        }
        catch(Exception) {
            console.log(Exception);
            // nothing
        }
    }

    inFinishList(weevilName) {
        try {
            for(var i = 0; i < this.m_playersFinished.length; i++) {
                if(this.m_playersFinished[i] == weevilName)
                return true;
            }
    
            return false;
        }
        catch(Exception) {
            console.log(Exception);
            return false;
        }
    }

    getUserFinishPos(weevil) {
        try {
            for(var i = 0; i < this.m_gameFinishObjects.length; i++) {
                var u = this.m_gameFinishObjects[i].getUser();
    
                if(u.nickname == weevil.nickname) {
                    return i;
                }
            }
    
            return -1;
        }
        catch(Exception) {
            console.log(Exception);
            return -1;
        }
    }

    placeUserTimeInAppropriateSlot(weevil, time) {
        for(var i = 0; i < this.m_gameFinishObjects.length; i++) {
            var user = this.m_gameFinishObjects[i].getUser();

            if(user.nickname == weevil.nickname) {
                this.m_gameFinishObjects[i].setTime(time);
                this.m_gameFinishObjects[i].setPingState(1);
                this.m_gameFinishObjects[i].setAlreadyDeterminedPosition(true);
            }
        }

        this.sortGFObjects();
        return this.getUserFinishPos(weevil);
    }

    setGotPing(weevil) {
        try {
            for(var i = 0; i < this.m_gameFinishObjects.length; i++) {
                var user = this.m_gameFinishObjects[i].getUser();

                if(user.userID == weevil.userID) {
                    this.m_gameFinishObjects[i].setPingState(1);
                }
            }
        }
        catch(Exception) {
            console.log(Exception);
            // nothing
        }
    }

    gotAllPings(weevil) {
        try {
            this.setGotPing(weevil);
            var count = 0;

            for(var i = 0; i < this.m_gameFinishObjects.length; i++) {
                if(this.m_gameFinishObjects[i].getpingState() == 1) {
                    count++;
                }
            }

            if(count >= this.m_gameFinishObjects.length) {
                this.m_pingingUsers = false;
                return true;
            }

            return true;
        }
        catch(Exception) {
            console.log(Exception);
            return false;
        }
    }

    getUserSlot(weevil) {
        try {
            for(var i = 0; i < this.m_players.length; i++) {
                if(this.m_players[i] != null) {
                    if(this.m_players[i].userID == weevil.userID)
                    return i;
                }
            }
    
            return -1;
        }
        catch(Exception) {
            console.log(Exception);
            return -1;
        }
    }

    setMulch(weevil, mulch, position, area) {
        var xp = 0;

        if(area == "dirtValley_1") {
            switch(position) {
                case 1:
                    xp = 2;
                    break;
                case 2:
                case 3:
                case 4:
                    xp = 1;
                    break;
            }
        }
        else if(area == "dirtValley_2") {
            switch(position) {
                case 1:
                    xp = 3;
                    break;
                case 2:
                case 3:
                case 4:
                    xp = 2;
                    break;
            }
        }
        else if(area == "dirtValley_3") {
            switch(position) {
                case 1:
                    xp = 4;
                    break;
                case 2:
                case 3:
                case 4:
                    xp = 3;
                    break;
            }
        }

        return;
    }

    inCurrentPlayerList(weevil) {
        try {
            for(var i = 0; i < this.m_players.length; i++) {
                if(this.m_players[i] != null) {
                    if(this.m_players[i].nickname == weevil.nickname)
                    return true;
                }
            }
    
            return false;
        }
        catch(Exception) {
            console.log(Exception);
            return false;
        }
    }

    sendUserPositions() {
        try {
            var usersToSendTo = [];
            var packet = "<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>" + this.WEEVIL_KART_GAME_TYPE + "</var><var n='success' t='s'>true</var><var n='command' t='s'>ranks</var>";

            for(var i = 0; i < this.m_gameFinishObjects.length; i++) {
                if(!this.m_gameFinishObjects[i].getShouldTimeOut()) {
                    if(this.inCurrentPlayerList(this.m_gameFinishObjects[i].getUser())) {
                        usersToSendTo.push(this.m_gameFinishObjects[i].getUser());
                    }
                }

                try {
                    var position = 0;

                    if(this.m_gameFinishObjects[i].getAlreadyDeterminedPosition()) {
                        var slot = this.getUserSlot(this.m_gameFinishObjects[i].getUser());
                        position = i + 1;

                        if(position == 1 && !this.m_gaveWinnerPoint) {
                            this.m_gaveWinnerPoint = true;
                            this.m_manager.addTrackLeaderboardPoints(this.m_gameFinishObjects[i].getUser(), 1, this.m_roomID);
                        }

                        var time = this.m_gameFinishObjects[i].getTime();
                        var slotName = this.getSlotName(slot);

                        packet += "<var n='" + slotName + "_pos" + "' t='s'>" + position + "</var><var n='" + slotName + "_time" + "' t='s'>" + time + "</var>";

                        if(!this.m_gameFinishObjects[i].alreadySetMulch()) {
                            this.m_gameFinishObjects[i].setAlreadySetMulch(true);
                        }
                    }
                }
                catch(Exception) {
                    console.log(Exception);
                    // nothing
                }
            }

            packet += "</dataObj>]]></body></msg>";

            for(var j = 0; j < usersToSendTo.length; j++) {
                usersToSendTo[j].send(packet);
            }
        }
        catch(Exception) {
            console.log(Exception);
            // nothing
        }
    }

    sendPingToAllUsersBarMe(weevil) {
        try {
            for(var i = 0; i < this.m_players.length; i++) {
                if(this.m_players[i] != null) {
                    if(this.m_players[i].nickname != weevil.nickname) {
                        this.m_players[i].send("%xt%b%-1%ping%");
                    }
                }
            }
        }
        catch(Exception) {
            console.log(Exception);
            // nothing
        }
    }
    
    getUserIndex(weevil) {
        try {
            for(var i = 0; i < this.m_players.length; i++) {
                if(this.m_players[i] != null) {
                    if(this.m_players[i].nickname == weevil.nickname)
                    return i;
                }
            }
    
            return -1;
        }
        catch(Exception) {
            console.log(Exception);
            return -1;
        }
    }

    processUserDisconnect(weevil) {
        this.playerLeaveGame(weevil);
    }

    playerLeaveGame(weevil) {
        try {
            if(!this.inFinishList(weevil.nickname) && this.m_gameStarted) {
                this.m_manager.removeTrackLeaderBoardPoints(weevil, 1, this.m_roomID);
            }

            var slot = this.getUserSlot(weevil);
            var idx = this.getUserIndex(weevil);
            this.m_playersHash.delete(weevil.nickname);

            if(idx != -1) this.m_players[idx] = null;

            this.m_manager.removeUserGameMapping(weevil.nickname);
            this.m_gameReady = false;
            this.removeUserFromLists(weevil);

            if(this.m_currentPlayerCount > 0)
            this.m_currentPlayerCount--;

            if(this.m_playersJoined > 0)
            this.m_playersJoined--;

            if(this.getNoPlayers() <= 0) {
                // do other things
                this.m_manager.freeGameSlot(this.m_initialPlayerCount, this.m_area);
                this.m_manager.removeGame(this.getGameID());
            }
            else {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        if(this.m_players[i].nickname != weevil.nickname) {
                            this.m_players[i].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>" + this.WEEVIL_KART_GAME_TYPE + "</var><var n='command' t='s'>playerLeaveGame</var><var n='kartID' t='s'>" + slot + "</var></dataObj>]]></body></msg>");
                        }
                    }
                }
            }

            if(this.m_playersDrivenOff >= this.getNoPlayers() && this.m_state == this.DRIVE_OFF && !this.m_gameStarted) {
                this.m_gameStarted = true;
                // do other things
                this.tellOtherUsersInRoomToResetGame(this.m_roomID);
                this.prepareGame();
            }

            if(this.m_playersReady >= this.getNoPlayers() && this.m_state == this.STARTING_WAITING_FOR_READY_MESSAGES) {
                this.m_state = this.DRIVE_OFF;
                this.m_currentPlayerCount = this.getNoPlayers();
                this.m_sentDriveOff = true;
                this.m_playersDrivenOff = 0;
                this.m_playersDrivenOffList.length = 0;
                // do other things
                this.sendDriveOffToAllUsersInRoom(weevil);
            }
        }
        catch(Exception) {
            console.log(Exception);
            // nothing here
        }
    }

    sendDriveOffToAllUsersInRoom(weevil) {
        try {
            for(var id in this.m_manager.socketIdList) {
                if(this.m_manager.weevils[parseInt(id)].currentRoomId == this.m_roomID) {
                    this.m_manager.weevils[parseInt(id)].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>driveOff</var><var n='playerCount' t='s'>" + this.m_initialPlayerCount + "</var></dataObj>]]></body></msg>");
                }
            }
        }
        catch(Exception) {
            console.log(Exception);
            // nothing
        }
    }

    tellOtherUsersInRoomToResetGame(roomID) {
        try {
            this.m_manager.freeGameSlot(this.m_initialPlayerCount, this.m_area);

            for(var id in this.m_manager.socketIdList) {
                if(this.m_manager.weevils[parseInt(id)].currentRoomId == parseInt(roomID)) {
                    this.m_manager.weevils[parseInt(id)].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>resetGame</var><var n='playerCount' t='s'>" + this.m_initialPlayerCount + "</var></dataObj>]]></body></msg>");
                }
            }
        }
        catch(Exception) {
            console.log(Exception);
            // nothing
        }
    }

    prepareGameAndTellUsersToReset() {
        try {
            this.tellOtherUsersInRoomToResetGame(this.m_roomID);
            this.prepareGame();
        }
        catch(Exception) {
            console.log(Exception);
            // nothing
        }
    }

    processRequest(weevil, params) {
        try {
            if(params[5] == "p") {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        this.m_players[i].send("%xt%b%-1%p%" + params[6] + "%" + params[7] + "%" + params[8] + "%" + params[9] + "%" + params[10] + "%" + params[11] + "%" + params[12] + "%" + params[13] + "%" + params[14] + "%");
                    }
                }
            }

            if(params[5] == "mb") {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        if(this.m_players[i].nickname != weevil.nickname) {
                            this.m_players[i].send("%xt%b%-1%mb%" + params[6] + "%" + params[7] + "%" + params[8] + "%" + params[9] + "%" + params[10] + "%" + params[11] + "%" + params[12] + "%" + params[13] + "%");
                        }
                    }
                }
            }

            if(params[5] == "dmb") {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        if(this.m_players[i].nickname != weevil.nickname) {
                            this.m_players[i].send("%xt%b%-1%dmb%" + params[6] + "%");
                        }
                    }
                }
            }

            if(params[5] == "hm") {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        if(this.m_players[i].nickname != weevil.nickname) {
                            this.m_players[i].send("%xt%b%-1%hm%" + params[6] + "%" + params[7] + "%");
                        }
                    }
                }
            }

            if(params[5] == "dhm") {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        if(this.m_players[i].nickname != weevil.nickname) {
                            this.m_players[i].send("%xt%b%-1%dhm%" + params[6] + "%" + params[7] + "%");
                        }
                    }
                }
            }

            if(params[5] == "phm") {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        if(this.m_players[i].nickname != weevil.nickname) {
                            this.m_players[i].send("%xt%b%-1%phm%" + params[6] + "%" + params[7] + "%");
                        }
                    }
                }
            }

            if(params[5] == "em") {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        if(this.m_players[i].nickname != weevil.nickname) {
                            this.m_players[i].send("%xt%b%-1%em%" + params[6] + "%" + params[7] + "%" + params[8] + "%" + params[9] + "%" + params[10] + "%" + params[11] + "%" + params[12] + "%" + params[13] + "%");
                        }
                    }
                }
            }

            if(params[5] == "dem") {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        if(this.m_players[i].nickname != weevil.nickname) {
                            this.m_players[i].send("%xt%b%-1%dem%" + params[6] + "%" + params[7] + "%" + params[8] + "%");
                        }
                    }
                }
            }

            if(params[5] == "j") {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        this.m_players[i].send("%xt%b%-1%j%" + params[6] + "%" + params[7] + "%" + params[8] + "%" + params[9] + "%" + params[10] + "%" + params[11] + "%" + params[12] + "%" + params[13] + "%" + params[14] + "%");
                    }
                }
            }

            if(params[5] == "sp") {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        this.m_players[i].send("%xt%b%-1%sp%" + params[6] + "%" + params[7] + "%" + params[8] + "%" + params[9] + "%" + params[10] + "%" + params[11] + "%" + params[12] + "%" + params[13] + "%");
                    }
                }
            }

            if(params[5] == "gst") {
                weevil.send("%xt%b%-1%gst%" + this.getTimeOffset() + "%");
            }

            if(params[5] == "ehm") {
                for(var i = 0; i < this.m_players.length; i++) {
                    if(this.m_players[i] != null) {
                        this.m_players[i].send("%xt%b%-1%ehm%" + params[6] + "%");
                    }
                }
            }

            if(params[5] == "joinGame") {
                this.canAddPlayerToPlayerList(weevil, parseInt(params[8]), params[9]);
            }

            if(params[5] == "userReady") {
                this.m_playersReadyList.push(weevil);
                this.m_playersReady++;

                if((this.m_playersReady >= this.getNoPlayers()) && this.m_sentDriveOff != true) {
                    this.m_state = this.DRIVE_OFF;
                    this.m_currentPlayerCount = this.getNoPlayers();
                    this.m_sentDriveOff = true;
                    this.m_playersDrivenOff = 0;

                    this.m_playersDrivenOffList.length = 0;
                    this.sendDriveOffToAllUsersInRoom(weevil);
                }
            }

            if(params[5] == "playerLeaveGame") {
                this.playerLeaveGame(weevil);
            }

            if(params[5] == "drivenOff") {
                this.m_playersDrivenOff++;
                weevil.ps = "0";

                this.m_playersDrivenOffList.push(weevil);
                this.m_playersStarted.push(weevil.nickname);

                if((this.m_playersDrivenOff >= this.getNoPlayers()) && !this.m_gameStarted) {
                    this.m_gameStarted = true;

                    this.tellOtherUsersInRoomToResetGame(this.m_roomID);
                    this.prepareGame();
                }
            }

            if(params[5] == "finishLine") {
                this.placeUserTimeInAppropriateSlot(weevil, parseInt(params[6]));
                this.m_playersFinished.push(weevil.nickname);

                if(this.m_pingingUsers) {
                    if(this.gotAllPings(weevil)) {
                        this.sendUserPositions();
                    }
                }
                else if(this.m_currentPlayerCount > 1) {
                    this.sendPingToAllUsersBarMe(weevil);
                }
                else {
                    this.sendUserPositions();
                }
            }

            if(params[5] == "ping") {
                if(this.gotAllPings(weevil)) {
                    this.sendUserPositions();
                }
            }
        }
        catch(Exception) {
            console.log(Exception);
        }
    }

}

module.exports = WeevilKartGame;