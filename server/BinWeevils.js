var net = require("net");
var Weevil = require("./Weevil");
var WeevilKartGame = require("./WeevilKartGame");
var xml = require("xml2js");
var moment = require("moment");
const { FORMERR } = require("dns");
//const setTitle = require('node-bash-title');
const waitFor = (ms) => new Promise(r => setTimeout(r, ms))
var fs = require('fs');
var filter = require('leo-profanity');
var linereader = require('line-reader');
var db = require('./db');

class BinWeevils {

    constructor(addr, serverPort) {
        // server stuff
        this.address = addr;
        this.port = parseInt(serverPort);
        this.weevils = {};
        this.socketIdList = {};
        this.sockets = 0;
        this.setFilters();
        // server stuff

        // room stuff
        this.flumMushroomsData = [];
        this.canSpawnPuddles = true;
        this.puddleTimer = undefined;
        this.roomWithIds = {};

        // Flum's Fountain (modern mushroom event state)
        this.flumsMushrooms = [];
        this.flumsMushroomsGrowthSteps = {0: true, 4: false, 8: false, 12: false, 16: false, 20: false, 24: false, 28: false, 32: false, 36: false, 40: false, 44: false, 48: false};
        this.mushroomCap = 10;
        this.currentPadID = undefined;
        this.padIDTimer = undefined;

        // Figg's Cafe (room 287)
        this.figgsTrays = {1: null, 2: null, 3: null}; // tray id => nickname (null if tray isn't occupied)
        this.figgsPlates = {1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0}; // plate id => food id

        // Dosh's Palace (room 265)
        this.doshCount = 0;
        this.doshCountTimer = undefined;
        this.doshTimer = undefined;
        this.setRoomIds();
        // room stuff

        // game stuff
        this.directionsMatrix = {
            vertical: { south: [1, 0], north: [-1, 0] },
            horizontal: { east: [0, 1], west: [0, -1] },
            backward: { southEast: [1, 1], northWest: [-1, -1] },
            forward: { southWest: [1, -1], northEast: [-1, 1] },
        };

        this.maxGames = 1500;
        this.gameSlotsHash = new Map();
        this.userToGamesHash = new Map();
        this.gamesHash = new Map();
        this.games = [];
        this.noOfActiveGames = 0;
        this.noOfActivePlayers = 0;
        // game stuff

        // leaderboard stuff
        this.poolHallPoints = {};
        this.trackTwoPoints = {};
        this.trackThreePoints = {};
        // leaderboard stuff
    }

    setFilters() {
        new Promise(async () => {
            linereader.eachLine('profanity.txt', async function(line) {
                filter.add(line.replace(',', '').toLowerCase());
                await waitFor(10);
            });

            await waitFor(1000);
        });
    }

    async setRoomIds() {
        var bws = this;
        fs.readFile('roomids.txt', 'utf8', function(err, data) {
            if(err) throw err;
        
            xml.parseString(data, function(error, result) {
                if(error) throw error;
        
                result.msg.body[0].rmList[0].rm.forEach(s => {
                    bws.roomWithIds[s.n] = s.$.id;
                });
            });
        });
    }

    // ---- Room-event timer helpers (Flum's Fountain / Dosh's Palace) ----
    onPuddleTimerFinish(weevil) {
        weevil.canSpawnPuddles = true;
        clearInterval(weevil.puddleTimer);
    }

    onPadTimerFinish(weevil) {
        weevil.currentPadID = undefined;
        clearInterval(weevil.padIDTimer);
    }

    onDoshCountTimerFinish(weevil) {
        clearInterval(weevil.doshCountTimer);
        weevil.doshCountTimer = undefined;
    }

    onDoshTimerFinish(weevil) {
        clearInterval(weevil.doshTimer);
        weevil.doshTimer = undefined;
    }

    // ---- Flum's Fountain (room 282) ----
    isMushroomCoordsValid(mushroomX, mushroomZ) {
        const fountainCenter = { x: 0, z: 1000 };
        const fountainRadius = 170;

        const dx = mushroomX - fountainCenter.x;
        const dz = mushroomZ - fountainCenter.z;
        const distance = Math.sqrt(dx * dx + dz * dz);

        if (distance <= fountainRadius) return false;
        else return true;
    }

    hasMushroomFinishedGrowing(mushroom, data, growthStage) {
        if (data == ("m" + mushroom.m + ":0") && mushroom.growthSteps[44] == true && growthStage == 48) return true;
        else return false;
    }

    setNewMushroomUnix(mushroomType) {
        const validUntil = Math.floor(Date.now() / 1000) + 7;

        db.query("UPDATE mushrooms SET validUntil = ? WHERE mushroomType = ?", [validUntil, mushroomType], function(err, result) {
            if(err) {
                console.log(err);
                return false;
            }
            else if (result.affectedRows == 1) {
                return true;
            }
            else return false;
        });
    }

    handleFlums(data, weevil) {
        if (weevil.loggedIn) {
            const roomID = parseInt(data[4]);
            let flumsData = data[5].toString();

            if (roomID == 282 && weevil.currentRoomID == 282 && flumsData != null) {
                let splitFlumsData = flumsData.split(";");
                const action = parseInt(splitFlumsData[0]);

                if (action == 1) {
                    // Spawning a puddle
                    const packetIdx = parseInt(splitFlumsData[1]);
                    const padId = parseInt(splitFlumsData[2]);

                    if (packetIdx != weevil.idx || padId < 0 || padId > 26) return;

                    if (!this.canSpawnPuddles) return;
                    else {
                        this.canSpawnPuddles = false;
                        this.puddleTimer = setInterval(this.onPuddleTimerFinish, 9000, this);
                        this.currentPadID = padId;
                        this.padIDTimer = setInterval(this.onPadTimerFinish, 4000, this);
                    }

                    weevil.roomEvent(roomID, flumsData, this.weevils);
                    return;
                }
                else if (action == 2) {
                    // Spawning a droplet / stepping on puddle
                    const packetPadId = parseInt(splitFlumsData[1]);
                    const packetIdx = parseInt(splitFlumsData[2]);

                    if (packetIdx != weevil.idx || this.currentPadID == undefined || packetPadId != this.currentPadID) return;
                    else {
                        this.currentPadID = undefined;
                        clearInterval(this.padIDTimer);
                        this.padIDTimer = undefined;
                    }

                    weevil.roomEvent(roomID, flumsData, this.weevils);
                    return;
                }
                else if (action == 3) {
                    // Spawn Mushroom
                    const m = parseInt(splitFlumsData[1]);
                    const mushroomType = parseInt(splitFlumsData[2]);
                    const mushroomX = parseInt(splitFlumsData[3]);
                    const mushroomZ = parseInt(splitFlumsData[4]);

                    if(this.flumsMushrooms.length >= this.mushroomCap || mushroomType < 1 || mushroomType > 13) return;

                    if(!this.isMushroomCoordsValid(mushroomX, mushroomZ)) {
                        console.log(weevil.nickname + " tried to spawn an invalid mushroom at X: " + mushroomX.toString() + ", Z: " + mushroomZ.toString());
                        return;
                    }

                    let mushroomData = JSON.parse(JSON.stringify({"m": m, "mushroomType": mushroomType, "growthStage": 0, "X": mushroomX, "Z": mushroomZ, "growthSteps": this.flumsMushroomsGrowthSteps}));
                    this.flumsMushrooms.push(mushroomData);

                    weevil.roomEvent(roomID, flumsData, this.weevils);
                    return;
                }
                else if (action == 4) {
                    // Growing Mushroom
                    let tryingToPop = false;
                    let success = false;
                    const mushroomID = parseInt(splitFlumsData[1]);
                    const newGrowthStage = parseInt(splitFlumsData[2]);
                    const mushroomData = data[6].toString();
                    if (mushroomData != ("m" + mushroomID + ":0")) {
                        var mushroomX = parseInt(mushroomData.split(";")[2]);
                        var mushroomZ = parseInt(mushroomData.split(";")[3]);
                    }
                    else {
                        tryingToPop = true;
                    }

                    if (newGrowthStage < 0 || newGrowthStage > 48) return;

                    for (let i = 0; i < this.flumsMushrooms.length; i++) {
                        const mushroom = this.flumsMushrooms[i];

                        if (tryingToPop) {
                            if (mushroom.m == mushroomID) {
                                if (this.hasMushroomFinishedGrowing(mushroom, mushroomData, newGrowthStage)) {
                                    this.setNewMushroomUnix(mushroom.mushroomType);
                                    this.flumsMushrooms.splice(i, 1);
                                    success = true;
                                    break;
                                }
                                else return;
                            }
                        }
                        else if (mushroom.m == mushroomID && mushroom.X == mushroomX && mushroom.Z == mushroomZ) {
                            if ((newGrowthStage - mushroom.growthStage) != 4) return;

                            if (mushroom.growthSteps[newGrowthStage] == false) {
                                this.flumsMushrooms[i].growthStage = newGrowthStage;
                                this.flumsMushrooms[i].growthSteps[newGrowthStage] = true;
                                success = true;
                                break;
                            }
                            else return;
                        }
                    }

                    if (success) weevil.roomEvent(roomID, flumsData, this.weevils);
                    return;
                }
                else {
                    console.log("Invalid flums packet action from " + weevil.nickname + ":", action);
                    return;
                }
            }
            else return;
        }
        else {
            weevil.socket.end();
            weevil.socket.destroy();
        }
    }

    // ---- Figg's Cafe (room 287) ----
    handleFiggs(data, weevil) {
        if (weevil.loggedIn) {
            const roomID = parseInt(data[4]);
            const figgsData = data[5].toString();

            if (roomID == 287 && weevil.currentRoomID == 287 && figgsData != null) {
                const splitFiggsData = figgsData.split(";");
                const action = parseInt(splitFiggsData[0]);

                if (action == 1) {
                    // Updating food/plate states
                    const p = parseInt(splitFiggsData[1]);
                    const value = parseInt(splitFiggsData[2]);

                    if (p < 1 || p > 15 || value > 8 || value < 0 || !weevil.isWaiter()) return;
                    else {
                        this.figgsPlates[p] = value;
                    }

                    weevil.roomEvent(roomID, figgsData, this.weevils);
                    return;
                }
                else if (action == 2) {
                    // Eating food
                    const p = parseInt(splitFiggsData[1]);

                    if (p < 1 || p > 12 || weevil.isWaiter() || this.figgsPlates[p] < 2) return;
                    else {
                        this.figgsPlates[p] = 1; // 1 => empty plate
                    }

                    weevil.roomEvent(roomID, figgsData, this.weevils);
                    return;
                }
                else {
                    console.log(weevil.nickname + " sent an invalid action for Figg's Cafe:", action);
                    return;
                }
            }
        }
        else {
            weevil.socket.end();
            weevil.socket.destroy();
        }
    }

    // ---- Dosh's Palace (room 265) ----
    handleDoshs(data, weevil) {
        if (weevil.loggedIn) {
            const roomID = parseInt(data[4]);
            const count = parseInt(data[5]);

            if (roomID == 265 && weevil.currentRoomID == 265 && count != null) {
                if(count - this.doshCount != 1 || this.doshCountTimer != undefined || this.doshTimer != undefined) return;
                else {
                    this.doshCount = count;

                    if(this.doshCount >= 25) { // 25 means dosh spawns
                        this.doshCount = -1;
                        this.doshTimer = setInterval(this.onDoshTimerFinish, 30000, this);
                    }
                    else {
                        this.doshCountTimer = setInterval(this.onDoshCountTimerFinish, 5950, this);
                    }

                    weevil.roomEvent(roomID, count, this.weevils);
                    return;
                }
            }
            else return;
        }
        else {
            weevil.socket.end();
            weevil.socket.destroy();
        }
    }

    addWeevil(weevil) {
        this.removeNest(weevil);
        this.removeWeevil(weevil);
        //setTitle('BinWeevils Rewritten [Online: ' + Object.keys(this.weevils).length + ']');
        this.weevils[weevil.socketID] = weevil;
        this.socketIdList[weevil.socketID] = weevil.socketID;
    }
    
    removeWeevil(weevil) {
        if (this.weevils[weevil.socketID]) {
            weevil = this.weevils[weevil.socketID];
            delete this.weevils[weevil.socketID];
            delete this.socketIdList[weevil.socketID];
        
            //setTitle('BinWeevils Rewritten [Online: ' + Object.keys(this.weevils).length + ']');
            weevil.socket.end();
            weevil.socket.destroy();
            weevil.destroyed = true;
        }
    }

    removeNest(weevil) {
        if(this.roomWithIds.hasOwnProperty("nest_" + weevil.nickname)) {
            var nestRoomId = this.roomWithIds["nest_" + weevil.nickname];
            delete this.roomWithIds["nest_" + weevil.nickname];

            for(var id in this.socketIdList) {
                if(this.weevils[parseInt(id)].nickname != weevil.nickname) {
                    this.weevils[parseInt(id)].send("<msg t='sys'><body action='roomDel'><rm id='" + nestRoomId.toString() + "'/></body></msg>");
                }
            }
        }
    }

    addNest(weevil) {
        // generate a random roomid for nests
        var nestRoomId = this.getRandomIntInclusive(100000, 999999);

        // loop through the current roomlist to see if the roomid already exists
        for(var id in this.roomWithIds) {
            // if the roomid is already taken, restart the function
            if(nestRoomId == this.roomWithIds[id]) {
                this.addNest(weevil);
                return;
            }
        }

        // if the roomid for the nest isnt taken, continue to add it to roomlist
        this.roomWithIds["nest_" + weevil.nickname] = nestRoomId;

        for(var id in this.socketIdList) {
            if(this.weevils[parseInt(id)].socketID != weevil.socketID && this.weevils[parseInt(id)].nickname != weevil.nickname) {
                this.weevils[parseInt(id)].send("<msg t='sys'><body action='roomAdd'><rm id='"+nestRoomId.toString()+"' priv='0' temp='1' game='0' ucnt='0' maxu='30' maxs='0'><n><![CDATA[nest_"+weevil.nickname+"]]></n></rm></body></msg>");
            }
        }
    }

    addPoolLeaderboardPoints(weevil, points) {
        if(this.poolHallPoints.hasOwnProperty(weevil.nickname)) {
            this.poolHallPoints[weevil.nickname] += points;
        }
        else {
            this.poolHallPoints[weevil.nickname] = 0;
            this.poolHallPoints[weevil.nickname] += points;
        }
    }

    removePoolLeaderBoardPoints(weevil, points) {
        if(this.poolHallPoints.hasOwnProperty(weevil.nickname)) {
            if(this.poolHallPoints[weevil.nickname] - points <= 0) {
                this.poolHallPoints[weevil.nickname] = 0;
            }
            else {
                this.poolHallPoints[weevil.nickname] -= points;
            }
        }
    }

    grabTopTenPoolLeaderBoardUsers() {
        if(Object.keys(this.poolHallPoints).length > 0) {
            var poolPoints = this.poolHallPoints;

            var items = Object.keys(poolPoints).map(function(key) {
                return [key, poolPoints[key]];
            });

            items.sort(function(first, second) {
                return second[1] - first[1];
            });

            var topTenPlayers = items.splice(0, 10);

            return topTenPlayers;
        }
        else {
            return [];
        }
    }

    addTrackLeaderboardPoints(weevil, points, roomId) {
        if(parseInt(roomId) == 304) {
            if(this.trackTwoPoints.hasOwnProperty(weevil.nickname)) {
                this.trackTwoPoints[weevil.nickname] += points;
            }
            else {
                this.trackTwoPoints[weevil.nickname] = 0;
                this.trackTwoPoints[weevil.nickname] += points;
            }

            weevil.roomEvent(roomId, null, this.weevils, this.socketIdList);
        }
        else if(parseInt(roomId) == 305) {
            if(this.trackThreePoints.hasOwnProperty(weevil.nickname)) {
                this.trackThreePoints[weevil.nickname] += points;
            }
            else {
                this.trackThreePoints[weevil.nickname] = 0;
                this.trackThreePoints[weevil.nickname] += points;
            }

            weevil.roomEvent(roomId, null, this.weevils, this.socketIdList);
        }
    }

    removeTrackLeaderBoardPoints(weevil, points, roomId) {
        if(parseInt(roomId) == 304) {
            if(this.trackTwoPoints.hasOwnProperty(weevil.nickname)) {
                if(this.trackTwoPoints[weevil.nickname] - points <= 0) {
                    this.trackTwoPoints[weevil.nickname] = 0;
                }
                else {
                    this.trackTwoPoints[weevil.nickname] -= points;
                }
            }

            weevil.roomEvent(roomId, null, this.weevils, this.socketIdList);
        }
        else if(parseInt(roomId) == 305) {
            if(this.trackThreePoints.hasOwnProperty(weevil.nickname)) {
                if(this.trackThreePoints[weevil.nickname] - points <= 0) {
                    this.trackThreePoints[weevil.nickname] = 0;
                }
                else {
                    this.trackThreePoints[weevil.nickname] -= points;
                }
            }

            weevil.roomEvent(roomId, null, this.weevils, this.socketIdList);
        }
    }

    grabTopTenTrackLeaderBoardUsers(roomId) {
        if(parseInt(roomId) == 304) {
            if(Object.keys(this.trackTwoPoints).length > 0) {
                var trackPoints = this.trackTwoPoints;
    
                var items = Object.keys(trackPoints).map(function(key) {
                    return [key, trackPoints[key]];
                });
    
                items.sort(function(first, second) {
                    return second[1] - first[1];
                });
    
                var topTenPlayers = items.splice(0, 10);
    
                return topTenPlayers;
            }
            else {
                return [];
            }
        }
        else if(parseInt(roomId) == 305) {
            if(Object.keys(this.trackThreePoints).length > 0) {
                var trackPoints = this.trackThreePoints;
    
                var items = Object.keys(trackPoints).map(function(key) {
                    return [key, trackPoints[key]];
                });
    
                items.sort(function(first, second) {
                    return second[1] - first[1];
                });
    
                var topTenPlayers = items.splice(0, 10);
    
                return topTenPlayers;
            }
            else {
                return [];
            }
        }
    }

    getRandomIntInclusive(min, max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min + 1) + min);
    }

    dataIsXml(dataStr) {
        try {
            xml.parseString(dataStr, function(error, result) {
                var action = result.msg.body[0].$.action;
            });
            return true;
        }
        catch(Exception) {
            return false;
        }
    }

    async handleData(dataStr, weevil) {
        try {
            for(var i = 0; i < dataStr.length; i++) {
                //if(!weevil.socket.remoteAddress.toString().includes('144.91.93.101') && !dataStr[i].toString().includes('%xt%b%')
                //&& !dataStr[i].toString().includes('%xt%login%b%') && dataStr[i].toString() != "" && dataStr[i].toString() != null)
                //console.log("[RECEIVED]:" + weevil.socket.remoteAddress + " - " + dataStr[i]);
                if(dataStr[i] == "" || dataStr[i] == null) return;

                if(dataStr[i].includes("Stress Test")){
                    weevil.def = JSON.parse(dataStr[i].substr(11))['def'];
                    weevil.changeRoom2('FlumsFountain', '43.08083554729819', '0', '986.500544231385', '-180', '190', this.weevils, this.socketIdList);
                }
                else if(dataStr[i] == "Stress Walk Test"){
                    weevil.userID = weevil.socketID;
                    weevil.moveWeevil('282','84','993','133', this.weevils, this.socketIdList);
                }
                else if(dataStr[i].includes("Stress Move")){
                    var action = JSON.parse(dataStr[i].substr(11))['action'];
                    var coords = JSON.parse(dataStr[i].substr(11))['coords'];
                    weevil.userID = weevil.socketID;
                    weevil.doAction('282', weevil.userID, action, coords, this.weevils, this.socketIdList);
                }
                else if(dataStr[i].startsWith('<')) {
                    // msg packet
                    if(dataStr[i].includes("<policy-file-request/>")) {
                        weevil.send("<cross-domain-policy><allow-access-from domain='*' to-ports='9339' /></cross-domain-policy>");
                    }
                    else if(this.dataIsXml(dataStr[i])) {
                        if(dataStr[i].startsWith('%xt%')) {
                            this.handleData(dataStr[i].split('<msg ')[0], weevil);
                            dataStr[i] = '<msg ' + dataStr[i].split('<msg ')[1];
                        }
                        else if(dataStr[i].includes('%xt%')) {
                            this.handleData(dataStr[i].split('</msg>')[1], weevil);
                            dataStr[i] = dataStr[i].split('</msg>')[0] + '</msg>';
                        }
                        // parse xml packets here.
                        // NOTE: xml2js parseString is ASYNCHRONOUS, so the action
                        // dispatch MUST happen inside the callback, not after it.
                        var self = this;
                        xml.parseString(dataStr[i], function(error, result) {
                            var action = "";
                            try { action = result.msg.body[0].$.action; } catch(e) { action = ""; }
                            weevil.lastPacket = dataStr[i]; // for protocol-violation diagnostics
                            weevil.recordPacket(dataStr[i]); // Checkpoint D (D9): ~200-packet audit ring buffer

                            try {
                                if(action == "verChk") {
                                    weevil.send("<msg t='sys'><body action='apiOK' r='0'></body></msg>");
                                }
                                else if(action == "login") {
                                    weevil.loginToBin(dataStr[i], self.weevils, self.socketIdList); // just for db testing
                                }
                                else if(action == "getRmList") {
                                    weevil.getRmList();
                                }
                                else if(action == "loadB") {
                                    // no one cares about buddy lists right now
                                    weevil.sendBuddyData(self.weevils, self.socketIdList);
                                }
                                else if(action == "roomB") {
                                    weevil.locateBuddy(dataStr[i], self.weevils, self.socketIdList);
                                }
                                else if(action == "setBvars") {
                                    weevil.setBVars(dataStr[i], self.weevils, self.socketIdList);
                                }
                                else if(action == "setUvars") {
                                    // this doesnt really do anything, but it sends back shit in game so doing it anyway
                                    weevil.setUVars(dataStr[i], self.weevils, self.socketIdList);
                                }
                                else if(action == "pubMsg") {
                                    weevil.sendPublicMessage(dataStr[i], self.weevils, self.socketIdList);
                                }
                                else {
                                    console.log("No hardcoded data on: [" + weevil.socket.remoteAddress + "] - " + dataStr[i]);
                                }
                            }
                            catch(e) {
                                // Checkpoint B: a malformed/unexpected packet must not crash the
                                // handler or disconnect immediately. Drop + log (no threshold count —
                                // a known-action packet the handler mis-parses is a server gap, not abuse).
                                weevil.dropOnly("handler exception: " + (e && e.message ? e.message : e));
                            }
                        });
                    }
                    else {
                        console.log("Issue: [" + weevil.socket.remoteAddress + "] - " + dataStr[i]);
                    }
                }
                else if(dataStr[i].startsWith("%xt%")) {
                    if(dataStr[i].includes('<msg')) {
                        this.handleData('<msg' + dataStr[i].split('<msg')[1], weevil);
                        dataStr[i] = dataStr[i].split('<msg')[0];
                    }
                    // xt packet
                    var data = dataStr[i].toString().split("%");
                    //console.log(data);

                    if(data[3] == "-1#0") {
                        weevil.send(JSON.stringify({ weevils: (Object.keys(this.weevils).length == 0 ? 0 : Object.keys(this.weevils).length - 1) }));
                    }
                    else if(data[3] == "-1#1") {
                        var buddyList = await weevil.getBuddyCountOnLogin(data[5], this.weevils, this.socketIdList);

                        if(buddyList != null) {
                            weevil.send(buddyList.length.toString());
                        }
                        else {
                            weevil.send("0");
                        }
                    }
                    else if(data[3] == "1#2") {
                        weevil.send("%xt%1#2%-1%" + (weevil.canSpeak ? "1" : "0") + "%");
                        //weevil.modMsg("Welcome to the BinWeevils Private Server - Created by: HD and Darkk | Hope you enjoy your time here :)");
                    }
                    else if(data[3] == "2#1") {
                        // movement packet request
                        weevil.moveWeevil(data[4], data[5], data[6], data[7], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "2#2") {
                        // expression change request
                        weevil.doExpression(data[4], data[5], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "2#3") {
                        // action packet request
                        weevil.doAction(data[4], this.weevils[weevil.socketID].userID, data[5], data[7], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "2#4") {
                        // change room packet
                        require('fs').appendFileSync("C:/Users/pc/Desktop/Project Binweevils/Binweevils-main (1)/Binweevils-main/server/roomchange_debug.log",
                            new Date().toISOString() + " roomChange: data[5]=" + data[5] + " data[6]=" + data[6] + " data[7]=" + data[7] + " data[8]=" + data[8] + " data[9]=" + data[9] + " data[11]=" + data[11] + "\n");
                        weevil.changeRoom(data[5], data[6], data[7], data[8], data[9], data[11], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "2#5") {
                        // Room events (Flum's Fountain / Figg's Cafe / Dosh's Palace)
                        const roomEvt = parseInt(data[4]);
                        switch(roomEvt) {
                            case 265:
                                this.handleDoshs(data, weevil);
                                return;
                            case 282:
                                this.handleFlums(data, weevil);
                                return;
                            case 287:
                                this.handleFiggs(data, weevil);
                                return;
                            default:
                                // Legacy 2#5 behaviour (e.g. tinks tree) preserved
                                if(data[6] != "" && data[6] != null) {
                                    if(data[6].toString().includes(":")) {
                                        var edited = false;
                                        for(var id in this.flumMushroomsData) {
                                            if(data[6].split(':')[0].toString() == this.flumMushroomsData[parseInt(id)]["m"]) {
                                                if(data[6].split(':')[1].toString() == "0") {
                                                    delete this.flumMushroomsData[parseInt(id)];
                                                }
                                                else {
                                                    this.flumMushroomsData[parseInt(id)]["data"] = data[6].split(':')[1].toString();
                                                    edited = true;
                                                }
                                            }
                                        }

                                        if(!edited) {
                                            var mushroomData = JSON.parse(JSON.stringify({"m":data[6].split(':')[0].toString(), "data":data[6].split(':')[1].toString()}));
                                            this.flumMushroomsData.push(mushroomData);
                                        }
                                    }
                                }
                                weevil.roomEvent(data[4], data[5], this.weevils, this.socketIdList);
                        }
                    }
                    else if(data[3] == "2#6") {
                        // gives current datetime, dont care
                        weevil.send("%xt%2#6%-1%" + moment().format("YYYY-MM-DD hh:mm:ss") + "%0%");
                    }
                    else if(data[3] == "2#7") {
                        // put on hat
                        weevil.wearHat(data[4], data[5], data[6], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "2#8") {
                        // take off hat
                        weevil.removeHat(data[4], data[5], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "2#10") {
                        // change weevil def request (only server sided, not changed in db)
                        weevil.changeDef(data[5]);
                    }
                    else if(data[3] == "5#1") {
                        // will set nest door marking (the door the weevil is about to leave from)
                        weevil.sendSetNestDoor(data[5], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "5#2") {
                        // send changeRoom/xyz for nests
                        weevil.sendInNestData(data[5], data[6], data[7], data[8], data[9], data[10], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "5#3") {
                        // sending nest invite
                        weevil.sendNestInvite(data[5], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "5#4") {
                        // removing nest invite
                        weevil.removeNestInvite(data[5], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "5#5") {
                        // telling sender that the person is in their nest
                        weevil.sendInNestReport(data[5], data[6], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "5#6") {
                        // decline nest invite
                        weevil.declineNestInvite(data[5], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "5#7") {
                        // evict user from nest (ex: business/plaza)
                        for(var id in this.socketIdList) {
                            if(this.weevils[parseInt(id)].nickname == data[5] && this.weevils[parseInt(id)].currentRoomId == this.roomWithIds["nest_" + weevil.nickname]) {
                                this.weevils[parseInt(id)].send("%xt%5#7%-1%" + weevil.nickname + "%");
                            }
                        }
                    }
                    else if(data[3] == "7#2") {
                        // chat ban a user
                        weevil.adminChatBan(data[5], data[6], data[7], data[8], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "7#100") {
                        // ban a user
                        weevil.adminBanUser(data[5], data[6], data[7], data[8], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "7#200") {
                        // kick a user
                        weevil.adminKickUser(data[5], data[6], data[7], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "7#300") {
                        // warn a user
                        weevil.adminWarnUser(data[5], data[6], data[7], data[8], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "9#1") {
                        // waiter event
                        //console.log("waiter function: " + data.toString());
                        weevil.doWaiter(data[4], data[5], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "9#2") {
                        // retire waiter event
                        //console.log("retire waiter function: " + data.toString());
                        weevil.retireWaiter(data[4], data[5], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "9#3") {
                        // become chef event
                        weevil.doChef(data[4], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "9#4") {
                        // retire as chef event
                        weevil.retireChef(data[4], this.weevils, this.socketIdList);
                    }
                    else if(data[3] == "10#1") {
                        // update player score
                        // actually not going to do this so that it cant be exploited.
                    }
                    else if(data[3] == "12#1") {
                        // open/close business (plaza)
                        if(data[5] != "-1") {
                            // open
                            weevil.openBusiness();
                        }
                        else {
                            // close
                            weevil.closeBusiness();
                        }
                    }
                    else if(data[3] == "12#2") {
                        var plazaList = "";

                        for(var id in this.socketIdList) {
                            if(this.weevils[parseInt(id)].plazaOpened) {
                                plazaList += this.weevils[parseInt(id)].nickname + "|" + await this.weevils[parseInt(id)].getBusinesses() + ":";
                            }
                        }

                        weevil.send("%xt%12#2%-1%" + (plazaList == "" ? "-1" : plazaList.substr(0, plazaList.length - 1)) + "%");
                    }
                    else if(data[3] == "12#3") {
                        for(var id in this.socketIdList) {
                            if(this.weevils[parseInt(id)].nickname == data[5] && this.weevils[parseInt(id)].plazaOpened) {
                                weevil.send("%xt%12#3%-1%1%");
                                return;
                            }
                        }

                        weevil.send("%xt%12#3%-1%-1%");
                    }
                    else if(data[3] == "tbmt") {
                        var gameTypeID = data[5].split('gameTypeID:')[1].split(',')[0];
                        var command = data[5].split('command:')[1].split(',')[0];
                        var slot = data[5].split('slot:')[1].split(',')[0];
                        var userID = data[5].split('userID:')[1].split(',')[0];

                        switch(gameTypeID) {
                            case "mulch4":
                                switch(command) {
                                    case "j":
                                        weevil.joinGame(userID, slot, gameTypeID, 0, this.weevils, this.socketIdList);
                                        return;
                                    case "rp":
                                        weevil.leaveGame(userID, slot, gameTypeID, this.weevils, this.socketIdList);
                                        return;
                                    case "tt":
                                        var column = data[5].split('col:')[1].split(',')[0];
                                        weevil.placeMulchBlock(userID, slot, gameTypeID, column, 0, this.weevils, this.socketIdList);
                                        return;
                                    default:
                                        return;
                                }
                            case "ballGame":
                                switch(command) {
                                    case "j":
                                        var ballCount = data[5].split('count:')[1].split(',')[0];
                                        weevil.joinGame(userID, slot, gameTypeID, ballCount, this.weevils, this.socketIdList);
                                        return;
                                    case "rp":
                                        weevil.leaveGame(userID, slot, gameTypeID, this.weevils, this.socketIdList);
                                        return;
                                    case "tt":
                                        var x = "";
                                        var y = "";
                                        var idd = "";
                                        var tdata = data[5].split(',');

                                        for(var id in tdata) {
                                            if(tdata[id].split(':')[0] == "x") {
                                                x = tdata[id].split(':')[1];
                                            }
                                            else if(tdata[id].split(':')[0] == "y") {
                                                y = tdata[id].split(':')[1];
                                            }
                                            else if(tdata[id].split(':')[0] == "id") {
                                                idd = tdata[id].split(':')[1];
                                            }
                                        }

                                        var ids = data[5].split('ids:')[1].split(',')[0];
                                        var poss = data[5].split('poss:')[1].split(',')[0];
                                        var dirx = data[5].split('dirx:')[1].split(',')[0];
                                        var diry = data[5].split('diry:')[1].split(',')[0];

                                        weevil.takePoolShot(userID, slot, gameTypeID, x, y, idd, ids, poss, dirx, diry, this.weevils, this.socketIdList);
                                        return;
                                    case "cpwg":
                                        var userWinner = data[5].split('userWinner:')[1].split(',')[0];
                                        var userLoser = data[5].split('userLoser:')[1].split(',')[0];
                                        weevil.sendGameInfo(userID, slot, gameTypeID, userWinner, userLoser, this.weevils, this.socketIdList);
                                        return;
                                    default:
                                        return;
                                }
                            case "squaresGame":
                                switch(command) {
                                    case "j":
                                        weevil.joinGame(userID, slot, gameTypeID, 0, this.weevils, this.socketIdList);
                                        return;
                                    case "rp":
                                        weevil.leaveGame(userID, slot, gameTypeID, this.weevils, this.socketIdList);
                                        return;
                                    case "tt":
                                        var col1 = data[5].split('col1:')[1].split(',')[0];
                                        var col2 = data[5].split('col2:')[1].split(',')[0];
                                        var row1 = data[5].split('row1:')[1].split(',')[0];
                                        var row2 = data[5].split('row2:')[1].split(',')[0];
                                        var p1sc = data[5].split('p1sc:')[1].split(',')[0];
                                        var p2sc = data[5].split('p2sc:')[1].split(',')[0];
                                        var keepPlaying = data[5].split('keepingPlay:')[1].split(',')[0];
                                        var nextPlayer = data[5].split('nextPlayer:')[1].split(',')[0];

                                        weevil.placeSquareLine(userID, slot, gameTypeID, col1, col2, row1, row2, p1sc, p2sc, keepPlaying, nextPlayer, this.weevils, this.socketIdList);
                                        return;
                                    default:
                                        return;
                                }
                            case "reverseMulch":
                                switch(command) {
                                    case "j":
                                        weevil.joinGame(userID, slot, gameTypeID, 0, this.weevils, this.socketIdList);
                                        return;
                                    case "rp":
                                        weevil.leaveGame(userID, slot, gameTypeID, this.weevils, this.socketIdList);
                                        return;
                                    case "tt":
                                        var col = data[5].split('col:')[1].split(',')[0];
                                        var row = data[5].split('row:')[1].split(',')[0];

                                        weevil.placeMulchBlock(userID, slot, gameTypeID, col, row, this.weevils, this.socketIdList);
                                        return;
                                    default:
                                        return;
                                }
                            default:
                                weevil.modMsg("Sorry, this game is currently unavailable.");
                                return;
                        }
                    }
                    else if(data[3] == "b") {
                        var g = this.getUserGame(weevil);

                        if(g != null && g != undefined) g.processRequest(weevil, data);
                        else if(data[5] == "joinGame") {
                            if(!this.checkValidKartColor(data[9])) {
                                this.forceUserDisconnect(weevil);
                                return;
                            }

                            var area = data[6];
                            var playerCount = parseInt(data[7]);

                            var game = this.createNewOrGetExistingGameByArea(weevil, playerCount, area);

                            if(game != null && game != undefined) game.processRequest(weevil, data);
                            else this.forceUserDisconnect(weevil);
                        }
                        else this.forceUserDisconnect(weevil);
                    }
                    else {
                        console.log("No hardcoded data on: [" + weevil.socket.remoteAddress + "] - " + dataStr[i]);
                    }
                }
                else {
                    console.log("Issue: [" + weevil.socket.remoteAddress + "] - " + dataStr[i]);
                }
            }
        }
        catch(Exception) {
            console.log(Exception);
        }
    }

    createGame(weevil, playerCount, area) {
        for(var i = 0; i < this.maxGames; i++) {
            if(!this.gamesHash.has(i)) {
                var newGame = new WeevilKartGame(this, parseInt(playerCount), area, i, weevil.currentRoomId);
                this.gamesHash.set(i, newGame);
                this.noOfActiveGames++;
                this.games.push(newGame);
                return newGame;
            }
        }

        return null;
    }

    removeGame(gameID) {
        for(var i = 0; i < this.games.length; i++) {
            if(this.games[i].getGameID() == parseInt(gameID)) {
                this.noOfActiveGames--;
                this.games.splice(i, 1);
                this.gamesHash.delete(parseInt(gameID));
            }
        }
    }

    freeGameSlot(playerCount, area) {
        var slotMap = new Map();

        if(this.gameSlotsHash.has(area)) {
            slotMap = this.gameSlotsHash.get(area);

            if(slotMap.has(parseInt(playerCount))) {
                slotMap.set(parseInt(playerCount), null);
            }
        }
    }

    getAreaGame(playerCount, area) {
        var res = undefined;
        var slotMap = new Map();

        if(this.gameSlotsHash.has(area)) {
            slotMap = this.gameSlotsHash.get(area);

            if(slotMap.has(parseInt(playerCount))) {
                res = slotMap.get(parseInt(playerCount));
            }
        }

        return res;
    }

    addAreaSlotMapping(area, playerCount, game) {
        var slotMap = new Map();

        if(this.gameSlotsHash.has(area)) {
            slotMap = this.gameSlotsHash.get(area);
            slotMap.set(parseInt(playerCount), game);
        }
        else {
            var newSlotMap = new Map();
            newSlotMap.set(parseInt(playerCount), game);
            this.gameSlotsHash.set(area, newSlotMap);
        }
    }

    addUserGameMapping(weevilName, game) {
        this.userToGamesHash.set(weevilName, game);
    }

    removeUserGameMapping(weevilName) {
        this.userToGamesHash.delete(weevilName);
    }

    cleanUp(game) {
        for(var i = 0; i < game.getUserCount(); i++) {
            if(game.getUserName(i) != null)
            this.userToGamesHash.delete(game.getUserName(i));
        }
    }

    createNewOrGetExistingGameByArea(weevil, playerCount, area) {
        var res = this.getAreaGame(parseInt(playerCount), area);

        if(res == null || res == undefined) {
            res = this.createGame(weevil, parseInt(playerCount), area);
            this.addAreaSlotMapping(area, parseInt(playerCount), res);
        }

        return res;
    }

    getUserGame(weevil) {
        return this.userToGamesHash.get(weevil.nickname);
    }

    forceUserDisconnect(weevil) {
        this.removeUserGameMapping(weevil.nickname);
        weevil.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>b</var><var n='command' t='s'>forceDisconnect</var></dataObj>]]></body></msg>");
    }

    checkValidKartColor(color) {
        switch(color) {
            case "255,200,200":
                return true;
            case "255,180,0":
                return true;
            case "80,255,80":
                return true;
            case "180,0,255":
                return true;
            case "255,80,80":
                return true;
            case "0,255,255":
                return true;
            case "255,240,0":
                return true;
            case "100,100,255":
                return true;
            case "255,255,255":
                return true;
            case "84,84,255":
                return true;
            case "120,120,255":
                return true;
            case "0,245,245":
                return true;
            case "255,160,0":
                return true;
            case "255,60,60":
                return true;
            default:
                return false;
        }
    }

    runServer() {
        var server = net.createServer(socket => {
            var client = new Weevil(socket);
            var socketID = this.sockets++;
            client.server = this;
            client.socket = socket;
            client.socketID = socketID;
            client.chatFilter = filter;
            this.addWeevil(client);

            socket.on("data", data => {
                this.handleData(data.toString().split('\0'), this.weevils[socketID]);
            });

            socket.on("close", () => {
                try {
                    if(!this.weevils[socketID]) return;
                    if(!this.weevils[socketID].destroyed) {
                        const w = this.weevils[socketID];
                        w.destroyed = true;
                        try { w.clearGame("mulch4"); } catch(e){}
                        try { w.clearGame("ballGame"); } catch(e){}
                        try { w.clearGame("squaresGame"); } catch(e){}
                        try { w.clearGame("reverseMulch"); } catch(e){}
                        try { w.sendBuddyLogout(this.weevils, this.socketIdList); } catch(e){}
                        try { w.removeWeevil(w.currentRoomId, w.userID, this.weevils, this.socketIdList); } catch(e){}
                        try { w.removeNestInvite("-1", this.weevils, this.socketIdList); } catch(e){}
                        try { this.removeNest(w); } catch(e){}
                        try { this.removeWeevil(w); } catch(e){}
                    }
                } catch(e) {
                    console.log("cleanup-on-close error (ignored): " + (e && e.message ? e.message : e));
                }
            });

            socket.on("timeout", () => {
                try {
                    if(!this.weevils[socketID]) return;
                    if(!this.weevils[socketID].destroyed) {
                        const w = this.weevils[socketID];
                        w.destroyed = true;
                        try { w.clearGame("mulch4"); } catch(e){}
                        try { w.clearGame("ballGame"); } catch(e){}
                        try { w.clearGame("squaresGame"); } catch(e){}
                        try { w.clearGame("reverseMulch"); } catch(e){}
                        try { w.sendBuddyLogout(this.weevils, this.socketIdList); } catch(e){}
                        try { w.removeWeevil(w.currentRoomId, w.userID, this.weevils, this.socketIdList); } catch(e){}
                        try { w.removeNestInvite("-1", this.weevils, this.socketIdList); } catch(e){}
                        try { this.removeNest(w); } catch(e){}
                        try { this.removeWeevil(w); } catch(e){}
                    }
                } catch(e) {
                    console.log("cleanup-on-timeout error (ignored): " + (e && e.message ? e.message : e));
                }
            });

            socket.on("error", err => {
                // Checkpoint hardening: a client disconnect (ECONNRESET/ETIMEDOUT) must
                // never crash the whole server process. Log and move on.
                console.log("socket error [" + (err && err.code ? err.code : "") + "]: " + (err && err.message ? err.message : err));
            });
        }).listen(this.port, this.address, () => {
            console.log("Server running on port " + this.port.toString());
            //setTitle('BinWeevils Rewritten [Online: 0]');
        });

        server.on("error", err => {
            console.log(err);
        });
    }

}

module.exports = BinWeevils;