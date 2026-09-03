const { timingSafeEqual } = require("crypto");
const { waitForDebugger } = require("inspector");
const { parse } = require("path");
const { runInThisContext } = require("vm");
const waitFor = (ms) => new Promise(r => setTimeout(r, ms))
const { stripHtml } = require("string-strip-html");
var db = require("./db");
const { response } = require("express");
const { clear } = require("console");
require('leo-profanity');

// TCP login brute-force protection (Checkpoint A, task 4).
// Separate from gameplay packet rate limiting. Sliding window keyed by remote address.
const TCP_LOGIN_WINDOW_MS = 60 * 1000;   // 60 second window
const TCP_LOGIN_MAX_ATTEMPTS = 10;       // max login attempts per window
const tcpLoginAttempts = new Map();        // remoteAddress -> [timestamps]

function recordTcpLoginAttempt(addr) {
    const now = Date.now();
    const arr = (tcpLoginAttempts.get(addr) || []).filter(t => now - t < TCP_LOGIN_WINDOW_MS);
    arr.push(now);
    tcpLoginAttempts.set(addr, arr);
    // soft-drop once over threshold: return true when blocked
    if (arr.length > TCP_LOGIN_MAX_ATTEMPTS) return true;
    return false;
}

function isTcpLoginBlocked(addr) {
    const arr = tcpLoginAttempts.get(addr);
    if (!arr) return false;
    const now = Date.now();
    const recent = arr.filter(t => now - t < TCP_LOGIN_WINDOW_MS);
    tcpLoginAttempts.set(addr, recent);
    return recent.length > TCP_LOGIN_MAX_ATTEMPTS;
}

// Checkpoint B (task 8): malformed/protocol-error disconnect refactor.
// Parser/protocol errors must NOT immediately hard-disconnect. They are dropped,
// logged, and counted per-connection; only after a threshold within a sliding
// window is the connection soft-disconnected. Moderation kick/ban (which target a
// DIFFERENT weevil via weevilList[...].socket) is intentionally untouched.
const PROTO_VIOLATION_WINDOW_MS = 60 * 1000;  // 60 second window
const PROTO_VIOLATION_THRESHOLD = 10;        // max protocol violations per window before soft-disconnect

class Weevil {

    constructor(socket = undefined) {

        this.nickname = "";
        this.def = "";
        this.idx = 0;
        this.userID = 0;
        this.isModerator = "0";
        this.curHat = "";
        this.curExpression = "0";
        this.ps = "0";

        this.buddyList = [];
        this.nestInvitations = {};
        this.declinedNestInvitations = {};
        this.warnAmt = 0;
        this.canSpeak = true;
        this.chatSpamTimer = undefined;
        this.chatSpamCount = 0;
        this.plazaOpened = false;

        this.konnectMulchData = {};
        this.poolData = {};
        this.squaresData = {};
        this.reverseMulchData = {};

        this.X = 0;
        this.Y = 0;
        this.Z = 0;
        this.R = 0; // rotation

        this.currentRoomId = 0;
        this.currentLocId = 0;
        this.currentRoomName = "";
        this.destroyed = false;

        this.socket = socket;
        this.loginServer = "";
        this.loggedIn = false;

        this.upd = false;

        // Checkpoint B: per-connection protocol-violation tracking (soft-disconnect).
        this.violations = 0;
        this.violationWindowStart = Date.now();

        // Checkpoint D (D9): capped ~200-entry packet history ring buffer for audit/debug.
        this.packetHistory = [];

        // Checkpoint D (D10): generic per-action sliding-window rate-limit buckets.
        this.actionRateBuckets = {};

    }

    // Checkpoint B: soft-disconnect for genuine malformed/protocol errors.
    // Logs the bad packet/context, increments a per-connection violation counter,
    // and only hard-disconnects once the threshold is exceeded within the window.
    // Single stray bad packets are dropped but the connection stays alive.
    softDrop(context = "protocol-error") {
        if (!this.socket) return;
        const now = Date.now();
        if (now - this.violationWindowStart > PROTO_VIOLATION_WINDOW_MS) {
            this.violationWindowStart = now;
            this.violations = 0;
        }
        this.violations++;
        const addr = (this.socket && this.socket.remoteAddress) ? this.socket.remoteAddress : "unknown";
        const pkt = (this.lastPacket ? this.lastPacket.toString().slice(0, 120) : context);
        console.log("PROTOCOL VIOLATION [" + addr + "] (" + this.violations + "/" + PROTO_VIOLATION_THRESHOLD + "): " + pkt);
        if (this.violations >= PROTO_VIOLATION_THRESHOLD) {
            console.log("SOFT-DISCONNECT [" + addr + "] exceeded protocol-violation threshold (" + this.violations + ")");
            this.socket.end();
            this.socket.destroy();
            this.destroyed = true;
        }
        // otherwise: drop the bad packet, keep the connection alive
    }

    // Checkpoint B: a known-action packet the handler internally fails to parse.
    // Dropped + logged for diagnostics, but NOT counted toward the soft-disconnect
    // threshold (a server/handler gap on a legitimate action is not client abuse).
    dropOnly(context = "packet-drop") {
        if (!this.socket) return;
        const addr = (this.socket && this.socket.remoteAddress) ? this.socket.remoteAddress : "unknown";
        const pkt = (this.lastPacket ? this.lastPacket.toString().slice(0, 120) : context);
        console.log("PACKET DROPPED [" + addr + "]: " + pkt);
    }

    // Checkpoint D (D9): record the raw packet into a capped ~200-entry ring buffer for audit.
    recordPacket(raw) {
        if (raw === undefined || raw === null) return;
        try {
            this.packetHistory.push({ t: Date.now(), p: raw.toString().slice(0, 256) });
            if (this.packetHistory.length > 200) this.packetHistory.shift();
        } catch (e) { /* logging must never break gameplay */ }
    }

    // Checkpoint D (D10): generic per-action sliding-window rate limiter.
    // Returns true if the action is allowed; false if it exceeds (max) within (windowMs).
    actionAllowed(action, max, windowMs) {
        const now = Date.now();
        if (!this.actionRateBuckets[action]) this.actionRateBuckets[action] = [];
        const bucket = this.actionRateBuckets[action];
        while (bucket.length && now - bucket[0] > windowMs) bucket.shift();
        if (bucket.length >= max) return false;
        bucket.push(now);
        return true;
    }

    // Checkpoint D (D8): shared moderator-auth check. cb(isMod) called after DB lookup.
    verifyMod(adminName, adminPassword, cb) {
        db.query("SELECT isModerator FROM users WHERE username = ? AND modToken = ?", [adminName, adminPassword], function(err, result) {
            if (err) { console.log(err); cb(false); return; }
            if (result.length > 0 && result[0].isModerator == "1") cb(true);
            else cb(false);
        });
    }

    // Checkpoint D (D8): log a moderation action (with reason) into game-logs for audit.
    logModeration(adminName, weevilName, type, reason) {
        try {
            db.query("INSERT INTO `game-logs` (`weevilName`, `adminName`, `type`, `reason`) VALUES (?, ?, ?, ?)",
                [weevilName, adminName, type, reason || ""], function(err) { if (err) console.log(err); });
        } catch (e) { /* non-fatal */ }
    }

    loginToBin(data, weevilList = undefined, socketIdList = undefined) {

        var weevil = this;

        // TCP login brute-force protection: soft-drop (no disconnect) when over threshold.
        var remote = (this.socket && this.socket.remoteAddress) ? this.socket.remoteAddress : 'unknown';
        if(isTcpLoginBlocked(remote)) {
            console.log("TCP login rate-limited: " + remote);
            return;
        }
        recordTcpLoginAttempt(remote);

        var server = data.split('login z=\'')[1].split('\'')[0];
        var weevilname = data.split('<nick><![CDATA[')[1].split(']]>')[0];
        weevilname = weevilname.replace('+', ' ');
        var loginkey = data.split('<pword><![CDATA[')[1].split(']]>')[0];
        loginkey = loginkey[0] + loginkey[1] + loginkey[2] + loginkey[3] + loginkey[4]; // gets first 5 chars of loginkey
        
        if(server != "Mulch") {
            this.softDrop();
            return;
        }

        db.query("SELECT id, isModerator, def, loginKey, canSpeak, curHat, active FROM users WHERE username = ?", [weevilname], function(err, result) {
            try {
                if(err) {
                    console.log(err);
                    weevil.socket.end();
                    weevil.socket.destroy();
                    return;
                }
                else {
                    if(result[0].active.toString() == "1" && result[0].loginKey.toString().includes(loginkey.toString())) {
                        // user verified

                        for(var id in socketIdList) {
                            // check if the same user is already logged in, dont need any duplicates
                            if(weevilList[parseInt(id)].nickname == weevilname && weevilList[parseInt(id)].socketID != weevil.socketID) {
                                // destroy that connection // not sure if this will work or not but leaving it anyway
                                weevilList[parseInt(id)].socket.end();
                                weevilList[parseInt(id)].socket.destroy();
                            }
                        }

                        weevil.loggedIn = true;
                        weevil.loginServer = server;
                        weevil.nickname = weevilname;
                        weevil.def = result[0].def;
                        weevil.idx = result[0].id;
                        weevil.userID = weevil.socketID;
                        weevil.isModerator = result[0].isModerator.toString();
                        weevil.canSpeak = weevil.canPlayerSpeak(parseInt(result[0].canSpeak));
                        weevil.curHat = result[0].curHat;
                        weevil.curExpression = "0";
                        weevil.currentRoomName = "nest_" + weevilname;
                        weevil.currentRoomId = 0;
                        weevil.currentLocId = 0;
                        weevil.server.addNest(weevil);
                        weevil.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>login</var><var n='success' t='s'>true</var><obj o='user' t='a'><var n='weevilDef' t='s'>" + weevil.def + "</var><var n='ip' t='s'>" + weevil.socket.remoteAddress + "</var><var n='apparel' t='s'>" + (result[0].curHat.toString().includes("|") ? result[0].curHat : "|null:-140,-140,-140") + "</var><var n='idx' t='s'>" + weevil.idx + "</var><var n='locale' t='s'></var><var n='userID' t='s'>" + weevil.userID + "</var></obj></dataObj>]]></body></msg>");
                    }
                    else {
                        //console.log("wrong shit");
                        weevil.socket.end();
                        weevil.socket.destroy();
                        return;
                    }
                }
            }
            catch(Exception) {
                console.log(Exception);
                weevil.socket.end();
                weevil.socket.destroy();
                return;
            }
        });

    }

    canPlayerSpeak(unix) {
        if(unix != 0) {
            var now = Math.floor(Date.now() / 1000);
            var mins = Math.floor((unix - now) / 60);

            if(mins <= 0) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return true;
        }
    }

    modMsg(msg) {
        if(this.loggedIn) {
            this.send("<msg t='sys'><body action='modMsg' r='-1'><txt>" + msg + "</txt></body></msg>");
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    changeRoom(roomName, x, y, z, r, locId, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            // will need to grab the weevils from list that are
            // also in this area, but we can do this another time

            if(parseInt(locId) == this.currentLocId && roomName.toString().substr(0, 5) != "nest_" && roomName != "Main") {
                this.softDrop();
                return;
            }

            if(this.konnectMulchData["slot"] != null) {
                this.leaveGame(this.nickname, this.konnectMulchData["slot"], this.konnectMulchData["gameTypeID"], weevilList, socketIdList);
            }

            if(this.poolData["slot"] != null) {
                this.leaveGame(this.nickname, this.poolData["slot"], this.poolData["gameTypeID"], weevilList, socketIdList);
            }

            if(this.squaresData["slot"] != null) {
                this.leaveGame(this.nickname, this.squaresData["slot"], this.squaresData["gameTypeID"], weevilList, socketIdList);
            }

            if(this.reverseMulchData["slot"] != null) {
                this.leaveGame(this.nickname, this.reverseMulchData["slot"], this.reverseMulchData["gameTypeID"], weevilList, socketIdList);
            }

            if(this.currentRoomId === 287 && this.isWaiter()) {
                for(var tray in this.server.figgsTrays) {
                    if(this.server.figgsTrays[tray] === this.nickname) {
                        this.retireWaiter(287, parseInt(tray), weevilList, socketIdList);
                        break;
                    }
                }
            }

            if(this.currentRoomId != 0 && this.currentRoomId != 260) {
                this.removeWeevil(this.currentRoomId, this.userID, weevilList, socketIdList);
            }

            if(roomName.toString().substr(0, 5) == "nest_") {
                for(var id in this.server.roomWithIds) {
                    if(roomName.toString() == id) {
                        if(roomName.toString().split('_')[1] != this.nickname) {
                            if(this.nestInvitations.hasOwnProperty(roomName.toString().split('_')[1])) {
                                this.currentRoomId = this.server.roomWithIds[id];
                                this.setBVars("'locName'><![CDATA[in " + roomName.toString().split('_')[1] + "'s nest]]>", weevilList, socketIdList);
                                break;
                            }
                            else if(parseInt(locId) == -50) {
                                this.currentRoomId = this.server.roomWithIds[id];
                                this.setBVars("'locName'><![CDATA[in " + roomName.toString().split('_')[1] + "'s plaza]]>", weevilList, socketIdList);
                                break;
                            }
                            else {
                                this.currentRoomId = this.server.roomWithIds["nest_" + this.nickname];
                                this.setBVars("'locName'><![CDATA[at home]]>", weevilList, socketIdList);
                                break;
                            }
                        }
                        else {
                            this.currentRoomId = this.server.roomWithIds[id];
                            this.setBVars("'locName'><![CDATA[at home]]>", weevilList, socketIdList);
                            break;
                        }
                    }
                }
            }
            else {
                for(var room in this.server.roomWithIds) {
                    if(room == roomName.toString()) {
                        this.currentRoomId = this.server.roomWithIds[room];
                        this.setBVars("'locName'><![CDATA[" + (roomName == "Main" ? "in a secret location" : roomName) + "]]>", weevilList, socketIdList);
                        break;
                    }
                }
            }

            this.currentLocId = parseInt(locId);
            this.X = parseInt(x);
            this.Y = parseInt(y); // Y will always remain 0
            this.Z = parseInt(z);
            this.R = parseInt(r);

            var joinok = this.returnJoinOK(this.currentRoomId, weevilList, socketIdList);
            this.send(joinok);
            
            // testing to get weevils showing for others when room joined
            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].socketID != this.socketID && weevilList[parseInt(id)].currentRoomId != 260 && weevilList[parseInt(id)].currentRoomId == this.currentRoomId) { // will check list of weevils if their currentLocId is the same as this locid
                    weevilList[parseInt(id)].spawnWeevil(this.currentRoomId, this.currentLocId, this.userID, this.nickname, this.isModerator, this.def, this.X, this.Y, this.Z, this.R, this.idx, this.curHat, this.curExpression); // send packet to users in same place so have other weevil show
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    returnJoinOK(roomId, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            var joinok = "<msg t='sys'><body action='joinOK' r='" + roomId + "'><pid id='0'/>";

            if(parseInt(roomId) == 282 && this.server.flumsMushrooms.length != 0) { // Flum's Fountain mushrooms
                joinok += "<vars>";

                for(var md in this.server.flumsMushrooms) {
                    const mushroom = this.server.flumsMushrooms[parseInt(md)];
                    const mushroomValue = mushroom.mushroomType + ";" + mushroom.growthStage + ";" + mushroom.X + ";" + mushroom.Z;
                    joinok += "<var n='m" + mushroom.m + "' t='s'><![CDATA[" + mushroomValue + "]]></var>";
                }

                joinok += "</vars><uLs r='" + roomId + "'>";
            }
            else if(parseInt(roomId) == 287) {
                // Figg's Cafe (room 287) — tray + plate state on join
                joinok += "<vars>";

                for (var tray in this.server.figgsTrays) {
                    joinok += "<var n='t" + tray + "' t='s'><![CDATA[" + this.server.figgsTrays[tray] + "]]></var>";
                }

                for (var plate in this.server.figgsPlates) {
                    joinok += "<var n='p" + plate + "' t='s'><![CDATA[" + this.server.figgsPlates[plate] + "]]></var>";
                }

                joinok += "</vars><uLs r='" + roomId + "'>";
            }
            else if(parseInt(roomId) == 291) {
                var p = 1;
                var weevilNames = "";
                var topTenPlayers = this.server.grabTopTenPoolLeaderBoardUsers();

                if(topTenPlayers.length > 0) {
                    for(var player in topTenPlayers) {
                        weevilNames += "<var n='p" + p.toString() + "' t='s'><![CDATA[" + topTenPlayers[player][0] + " : " + topTenPlayers[player][1] + "]]></var>";
                        p++;
                    }

                    joinok += "<vars>" + weevilNames + "</vars><uLs r='" + roomId + "'>";
                }
                else {
                    joinok += "<vars/><uLs r='" + roomId + "'>";
                }
            }
            else if(parseInt(roomId) == 304) {
                var p = 1;
                var weevilNames = "";
                var topTenPlayers = this.server.grabTopTenTrackLeaderBoardUsers(roomId);

                if(topTenPlayers.length > 0) {
                    for(var player in topTenPlayers) {
                        weevilNames += "<var n='p" + p.toString() + "' t='s'><![CDATA[" + topTenPlayers[player][0] + "]]></var><var n='s" + p.toString() + "' t='s'><![CDATA[" + topTenPlayers[player][1] + "]]></var>";
                        p++;
                    }

                    joinok += "<vars>" + weevilNames + "</vars><uLs r='" + roomId + "'>";
                }
                else {
                    joinok += "<vars/><uLs r='" + roomId + "'>";
                }
            }
            else if(parseInt(roomId) == 305) {
                var p = 1;
                var weevilNames = "";
                var topTenPlayers = this.server.grabTopTenTrackLeaderBoardUsers(roomId);

                if(topTenPlayers.length > 0) {
                    for(var player in topTenPlayers) {
                        weevilNames += "<var n='p" + p.toString() + "' t='s'><![CDATA[" + topTenPlayers[player][0] + "]]></var><var n='s" + p.toString() + "' t='s'><![CDATA[" + topTenPlayers[player][1] + "]]></var>";
                        p++;
                    }

                    joinok += "<vars>" + weevilNames + "</vars><uLs r='" + roomId + "'>";
                }
                else {
                    joinok += "<vars/><uLs r='" + roomId + "'>";
                }
            }
            else {
                joinok += "<vars/><uLs r='" + roomId + "'>";
            }

            if(parseInt(roomId) == 260) {
                joinok += "<u i='" + this.userID + "' m='" + this.isModerator + "'><n><![CDATA[" + this.nickname + "]]></n><vars><var n='weevilDef' t='s'><![CDATA[" + this.def + "]]></var><var n='r' t='n'><![CDATA[" + this.R + "]]></var><var n='ps' t='n'><![CDATA[" + this.ps + "]]></var><var n='ex' t='n'><![CDATA[" + this.curExpression + "]]></var><var n='x' t='n'><![CDATA[" + this.X + "]]></var><var n='y' t='n'><![CDATA[" + this.Y + "]]></var><var n='apparel' t='s'><![CDATA[" + (this.curHat.toString().includes("|") ? this.curHat : "|null:-140,-140,-140") + "]]></var><var n='z' t='n'><![CDATA[" + this.Z + "]]></var><var n='idx' t='s'><![CDATA[" + this.idx + "]]></var><var n='doorID' t='n'><![CDATA[0]]></var><var n='locID' t='s'><![CDATA[" + this.currentLocId + "]]></var></vars></u>";
            }
            else {
                for(var id in socketIdList) {
                    if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                        joinok += "<u i='" + weevilList[parseInt(id)].userID + "' m='" + weevilList[parseInt(id)].isModerator + "'><n><![CDATA[" + weevilList[parseInt(id)].nickname + "]]></n><vars><var n='weevilDef' t='s'><![CDATA[" + weevilList[parseInt(id)].def + "]]></var><var n='r' t='n'><![CDATA[" + weevilList[parseInt(id)].R + "]]></var><var n='ps' t='n'><![CDATA[" + weevilList[parseInt(id)].ps + "]]></var><var n='ex' t='n'><![CDATA[" + weevilList[parseInt(id)].curExpression + "]]></var><var n='x' t='n'><![CDATA[" + weevilList[parseInt(id)].X + "]]></var><var n='y' t='n'><![CDATA[" + weevilList[parseInt(id)].Y + "]]></var><var n='apparel' t='s'><![CDATA[" + (weevilList[parseInt(id)].curHat.toString().includes("|") ? weevilList[parseInt(id)].curHat : "|null:-140,-140,-140") + "]]></var><var n='z' t='n'><![CDATA[" + weevilList[parseInt(id)].Z + "]]></var><var n='idx' t='s'><![CDATA[" + weevilList[parseInt(id)].idx + "]]></var><var n='doorID' t='n'><![CDATA[0]]></var><var n='locID' t='s'><![CDATA[" + weevilList[parseInt(id)].currentLocId + "]]></var></vars></u>";
                    }
                    /*else if(weevilList[parseInt(id)].socketID == this.socketID) {
                        joinok += "<u i='" + weevilList[parseInt(id)].userID + "' m='0'><n><![CDATA[" + weevilList[parseInt(id)].nickname + "]]></n><vars><var n='weevilDef' t='s'><![CDATA[" + weevilList[parseInt(id)].def + "]]></var><var n='r' t='n'><![CDATA[" + weevilList[parseInt(id)].R + "]]></var><var n='ps' t='n'><![CDATA[0]]></var><var n='ex' t='n'><![CDATA[" + weevilList[parseInt(id)].curExpression + "]]></var><var n='x' t='n'><![CDATA[" + weevilList[parseInt(id)].X + "]]></var><var n='y' t='n'><![CDATA[" + weevilList[parseInt(id)].Y + "]]></var><var n='apparel' t='s'><![CDATA[" + (weevilList[parseInt(id)].curHat.toString().includes("|") ? weevilList[parseInt(id)].curHat : "|null:-140,-140,-140") + "]]></var><var n='z' t='n'><![CDATA[" + weevilList[parseInt(id)].Z + "]]></var><var n='idx' t='s'><![CDATA[" + weevilList[parseInt(id)].idx + "]]></var><var n='doorID' t='n'><![CDATA[0]]></var><var n='locID' t='s'><![CDATA[" + weevilList[parseInt(id)].currentLocId + "]]></var></vars></u>";
                    }*/
                }
            }

            joinok += "</uLs></body></msg>";
            return joinok;
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    setBVars(data, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            if(data.toString().includes("locName")) {
                var locName = data.split('locName\'><![CDATA[')[1].split("]]>")[0];
                this.currentRoomName = locName;
    
                for(var id in socketIdList) {
                    for(var budIndex in this.buddyList) {
                        if(weevilList[parseInt(id)].nickname == this.buddyList[parseInt(budIndex)]["username"]) {
                            weevilList[parseInt(id)].send("<msg t='sys'><body action='bUpd' r='-1'><b s='1' i='" + this.userID.toString() + "'><n><![CDATA[" + this.nickname + "]]></n><vs><v n='locName'><![CDATA[" + this.currentRoomName + "]]></v></vs></b></body></msg>");
                        }
                    }
                }
            }
            else {
                for(var id in socketIdList) {
                    for(var budIndex in this.buddyList) {
                        if(weevilList[parseInt(id)].nickname == this.buddyList[parseInt(budIndex)]["username"]) {
                            weevilList[parseInt(id)].send("<msg t='sys'><body action='bUpd' r='-1'><b s='1' i='" + this.userID.toString() + "'><n><![CDATA[" + this.nickname + "]]></n></b></body></msg>");
                        }
                    }
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    async sendBuddyData(weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            var packet = "<msg t='sys'><body action='bList' r='-1'><bList>";
            await this.getBuddyList(weevilList, socketIdList);

            if(this.buddyList.length != 0) {
                for(var budIndex in this.buddyList) {
                    if(this.buddyList[parseInt(budIndex)]["on"] == 1) {
                        for(var id in socketIdList) {
                            if(weevilList[parseInt(id)].nickname == this.buddyList[parseInt(budIndex)]["username"] && weevilList[parseInt(id)].loginServer == this.loginServer) {
                                packet += "<b s='1' i='" + weevilList[parseInt(id)].userID.toString() + "' x='0'><n><![CDATA[" + this.buddyList[parseInt(budIndex)]["username"] + "]]></n>";

                                if(weevilList[parseInt(id)].currentRoomName != "") {
                                    packet += "<vs><v n='locName'><![CDATA[" + weevilList[parseInt(id)].currentRoomName + "]]></v></vs>";
                                }

                                packet += "</b>";
                                if(!this.upd) {
                                    this.upd = true;
                                    weevilList[parseInt(id)].send("<msg t='sys'><body action='bUpd' r='-1'><b s='1' i='" + this.userID.toString() + "'><n><![CDATA[" + this.nickname + "]]></n>" + (this.currentRoomName == "" ? "" : "<vs><v n='locName'><![CDATA[" + this.currentRoomName + "]]></v></vs>") + "</b></body></msg>");
                                }
                            }
                        }
                    }
                    else {
                        packet += "<b s='0' i='-1' x='0'><n><![CDATA[" + this.buddyList[parseInt(budIndex)]["username"] + "]]></n></b>";
                    }
                }

                packet += "</bList></body></msg>";
                this.send(packet);
            }
            else {
                packet += "</bList></body></msg>";
                this.send(packet);
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    locateBuddy(data, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            var buddyID = "";

            try { buddyID = data.split('id=\'')[1].split('\'')[0]; }
            catch(Exception) { return; }

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].userID == parseInt(buddyID) && weevilList[parseInt(id)].loginServer == this.loginServer) {
                    this.send("<msg t='sys'><body action='roomB' r='-1'><br r='" + weevilList[parseInt(id)].currentRoomId.toString() + "' /></body></msg>");
                    break;
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    sendBuddyLogout(weevilList, socketIdList) {
        if(this.loggedIn) {
            for(var id in socketIdList) {
                for(var budIndex in this.buddyList) {
                    if(weevilList[parseInt(id)].nickname == this.buddyList[parseInt(budIndex)]["username"]) {
                        weevilList[parseInt(id)].send("<msg t='sys'><body action='bUpd' r='-1'><b s='0' i='-1'><n><![CDATA[" + this.nickname + "]]></n></b></body></msg>");
                    }
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    setUVars(data, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            var roomId = data.split('r=\'')[1].split('\'>')[0];
            var x = "";
            var y = "";
            var z = "";
            var r = "";

            try {
                x = data.split('n=\'x\' t=\'s\'><![CDATA[')[1].split(']]>')[0];
                y = data.split('n=\'y\' t=\'s\'><![CDATA[')[1].split(']]>')[0];
                z = data.split('n=\'z\' t=\'s\'><![CDATA[')[1].split(']]>')[0];
                r = data.split('n=\'r\' t=\'s\'><![CDATA[')[1].split(']]>')[0];
            } catch { x = this.X.toString(); y = this.Y.toString(); z = this.Z.toString(); r = this.R.toString(); }

            this.X = parseInt(x);
            this.Y = parseInt(y);
            this.Z = parseInt(z);
            this.R = parseInt(r);
            var packet = "<msg t='sys'><body action='uVarsUpdate' r='" + roomId + "'><user id='" + this.userID + "' /><vars><var n='x' t='s'><![CDATA[" + x + "]]></var><var n='z' t='s'><![CDATA[" + z + "]]></var><var n='r' t='s'><![CDATA[" + r + "]]></var><var n='y' t='s'><![CDATA[" + y + "]]></var></vars></body></msg>";

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].socketID != this.socketID && weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                    weevilList[parseInt(id)].send(packet);
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    publicMessageTimer(weevil) {
        weevil.canSpeak = true;
        weevil.chatSpamCount = 0;
        clearInterval(weevil.chatSpamTimer);
    }

    sendPublicMessage(data, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            if(!this.canSpeak) {
                this.chatSpamCount++;
                if(this.chatSpamCount >= 10) {
                    this.adminChatBan("MARIO200", "o+UazB76c2HvqbFfIWlZR7SrS2c=", this.nickname, 16, weevilList, socketIdList);
                    this.softDrop();
                }
                return;
            }

            // Checkpoint C: safely extract the chat message from the CDATA section.
            // Guard against malformed packets (no crash -> no false protocol drop).
            var cdParts = data.split('CDATA[');
            if (cdParts.length < 2) { this.dropOnly("chat: no CDATA section"); return; }
            var rawMsg = cdParts[1].split(']]>')[0];
            if (rawMsg === undefined) { this.dropOnly("chat: unterminated CDATA"); return; }

            if(!/(^[A-Z a-z.,!?]+$)/.test(rawMsg)) return;

            var roomId = data.split('r=\'')[1].split('\'')[0];
            // strip tag-open '<' (prevents tag injection); leave '>' (harmless without '<')
            var message = stripHtml(rawMsg).result.replace(/</g, '');

            var wasProfane = false;
            if (this.chatFilter && this.chatFilter.check(message.toLowerCase())) {
                this.warnAmt++;
                if(this.warnAmt >= 4) {
                    var weevil = this;
                    var unix = Math.floor(Date.now() / 1000);
                    var futureUnix = unix + (this.server.getRandomIntInclusive(2, 11)) * 60;

                    db.query("UPDATE users SET canSpeak = ? WHERE username = ?", [futureUnix.toString(), this.nickname], function(err, result) {
                        if(err) {
                            console.log(err);
                        }
                        else if(result.affectedRows == 1) {
                            weevil.canSpeak = false;
                            weevil.send("%xt%7#2%-1%" + (Math.floor((futureUnix - unix) / 60) - 1) + "%");
                        }
                        else {
                            console.log(result);
                        }
                    });
                }
                else {
                    this.modMsg("You have been caught using inappropriate language. Continuing to use inappropriate language could result in you getting an account/chat ban.");
                }

                // Checkpoint C: replace bad words with asterisks, then broadcast to EVERYONE
                // (not just the sender) so the cleaned message is visible in the room.
                message = this.chatFilter.clean(message);
                wasProfane = true;
            }

            if(message.length > 38) {
                message = message.substring(0, 38);
            }

            // Checkpoint C: escape XML ']]>' so a message cannot prematurely close the
            // outgoing CDATA section (would let a player inject XML/tags into chat).
            message = message.split(']]>').join(']]]]><![CDATA[>');

            if(!wasProfane && (message == "" || message == null || message.includes('*') || /([a-z A-Z]+\w)\1+$/.test(message))) {
                this.send("<msg t='sys'><body action='pubMsg' r='" + roomId + "'><user id='" + this.userID + "' /><txt><![CDATA[" + (message == null ? "" : message) + "]]></txt></body></msg>");
                return;
            }

            this.canSpeak = false;
            this.chatSpamTimer = setInterval(this.publicMessageTimer, 500, this);

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                    weevilList[parseInt(id)].send("<msg t='sys'><body action='pubMsg' r='" + roomId + "'><user id='" + this.userID + "' /><txt><![CDATA[" + message + "]]></txt></body></msg>"); // send response message to all users in area
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    getRmList() {
        var rmList = "<msg t='sys'><body action='rmList' r='0'><rmList>";
        for (var key in this.server.roomWithIds) {
            rmList += "<rm id='" + this.server.roomWithIds[key].toString() + "' priv='0' temp='1' game='0' ucnt='0' maxu='10000' maxs='0'><n><![CDATA["+key.toString()+"]]></n></rm>";
        }
        rmList += "</rmList></body></msg>";
        this.send(rmList);
    }

    send(data) {
        if(this.socket == undefined) return;
        if (!this.socket.destroyed) {
            //if(!data.toString().includes('"ping":1') && !this.socket.remoteAddress.toString().includes('144.91.93.101')
            //&& !data.toString().includes('%xt%b%') && !data.toString().includes('%xt%login%b%') && !data.toString().includes('rmList'))
            //console.log("[SENDING]:" + this.socket.remoteAddress + " - " + data);
            data += "\0";
            this.socket.write(data);
        }
    }

    spawnWeevil(roomId, locId, userId, weevilName, isMod, weevilDef, x, y, z, r, weevilIdx, currentHat, currentExpression) {
        if(this.loggedIn) {
            var packet = "<msg t='sys'><body action='uER' r='" + roomId + "'>";
            packet += "<u i ='" + userId + "' m='" + isMod + "'><n><![CDATA[" + weevilName + "]]></n><vars><var n='weevilDef' t='s'><![CDATA[" + weevilDef + "]]></var><var n='r' t='n'><![CDATA[" + r + "]]></var><var n='ps' t='n'><![CDATA[0]]></var><var n='ex' t='n'><![CDATA[" + currentExpression + "]]></var><var n='x' t='n'><![CDATA[" + x + "]]></var><var n='y' t='n'><![CDATA[" + y + "]]></var><var n='apparel' t='s'><![CDATA[" + (currentHat.toString().includes("|") ? currentHat : "|null:-140,-140,-140") + "]]></var><var n='z' t='n'><![CDATA[" + z + "]]></var><var n='idx' t='s'><![CDATA[" + weevilIdx + "]]></var><var n='doorID' t='n'><![CDATA[0]]></var><var n='locID' t='s'><![CDATA[" + locId + "]]></var>";
            packet += "</vars></u></body></msg>";
            this.send(packet);
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    moveWeevil(roomId, x, z, r, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            // Checkpoint D (D10/D11): rate-limit + validate movement coordinates.
            if(!this.actionAllowed("move", 30, 1000)) { this.dropOnly("movement rate-limited"); return; }
            const nx = parseInt(x), nz = parseInt(z), nr = parseInt(r);
            if(!isFinite(nx) || !isFinite(nz) || !isFinite(nr) ||
               nx < -20000 || nx > 20000 || nz < -20000 || nz > 20000 ||
               nr < -360 || nr > 360) {
                this.dropOnly("invalid movement coordinate"); return;
            }
            this.currentRoomId = parseInt(roomId);
            this.X = nx;
            this.Z = nz;
            this.R = nr;
            this.ps = "0";

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].socketID != this.socketID && weevilList[parseInt(id)].currentRoomId == this.currentRoomId) {
                    // should hopefully send back packets to others in area showing the weevil moving
                    weevilList[parseInt(id)].send("%xt%2#1%-1%" + this.userID + "%" + x + "%" + z + "%" + r + "%");
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    removeWeevil(roomId, userId, weevilList = undefined, socketIdList = undefined) {
        if(parseInt(roomId) == 0 || parseInt(roomId) == 260) return;

        var packet = "<msg t='sys'><body action='userGone' r='" + roomId + "'><user id='" + userId + "' /></body></msg>";

        for(var id in socketIdList) {
            if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId) && weevilList[parseInt(id)].socketID != userId && weevilList[parseInt(id)].currentRoomId != 260) {
                weevilList[parseInt(id)].send(packet); // remove weevil from area that others are in when they change rooms
            }
        }
    }

    doAction(roomId, userId, actionId, power, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            if(parseInt(actionId) == 34) {
                if(parseInt(roomId) != this.server.roomWithIds["nest_" + this.nickname]) return;
            }
            else if(parseInt(actionId) == 24) {
                if(parseInt(roomId) != this.server.roomWithIds["Diner"] || parseInt(roomId) != this.server.roomWithIds["FiggsCafeTerrace"]) return;
            }
            else if(parseInt(actionId) == 42) {
                if(parseInt(roomId) != this.server.roomWithIds["TycoonIslandSlides"]) return;
            }
            else if(parseInt(actionId) == 44) {
                if(parseInt(roomId) != this.server.roomWithIds["CrazyFunHouse4"]) return;
            }
            else if(parseInt(actionId) == 46) {
                if(parseInt(roomId) != this.server.roomWithIds["CrazyFunHouse4"]) return;
            }
            else if(parseInt(actionId) == 47) {
                if(parseInt(roomId) != this.server.roomWithIds["CrazyFunHouse4"]) return;
            }
            else if(parseInt(actionId) == 6) {
                this.ps = "6"; // user is sitting
            }
            else if(parseInt(actionId) == 7) {
                this.ps = "7"; // user is standing
            }
            else {
                this.ps = "0";
            }
    
            try {
                var coords = power.split(',');
                
                if(parseInt(actionId) == 10 && coords.length == 4) {
                    this.X = parseInt(coords[0]);
                    this.Y = parseInt(coords[1]);
                    this.Z = parseInt(coords[2]);
                    this.R = parseInt(coords[3]);
                }
                else if(parseInt(actionId) == 1 && coords.length > 1 || parseInt(actionId) == 7 && coords.length > 1 || parseInt(actionId) == 12 && coords.length > 1 || parseInt(actionId) == 14 && coords.length > 1 || parseInt(actionId) == 30 && coords.length > 1) return;
            }
            catch(Exception) {}
    
            var packet = "%xt%2#3%-1%" + userId + "%" + actionId + "%" + power + "%";
    
            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].socketID != this.socketID && weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                    weevilList[parseInt(id)].send(packet); // send action
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    roomEvent(roomId, eventArgs, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            if(parseInt(roomId) == 291 && this.currentRoomId == 291) {
                var packet = "%xt%2#5%-1%";
                var topTenPlayers = this.server.grabTopTenPoolLeaderBoardUsers();
    
                if(topTenPlayers.length == 0) return;
    
                for(var player in topTenPlayers) {
                    packet += topTenPlayers[player][0] + " : " + topTenPlayers[player][1] + "#";
                }
    
                packet = packet.substr(0, packet.length - 1);
                packet += "%";
    
                for(var id in socketIdList) {
                    if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                        weevilList[parseInt(id)].send(packet); // send event response
                    }
                }
            }
            else if(parseInt(roomId) == 304 && this.currentRoomId == 304) {
                var packet = "%xt%2#5%-1%";
                var topTenPlayers = this.server.grabTopTenTrackLeaderBoardUsers(roomId);
    
                if(topTenPlayers.length == 0) return;
    
                for(var player in topTenPlayers) {
                    packet += topTenPlayers[player][0] + ";" + topTenPlayers[player][1] + "#";
                }
    
                packet = packet.substr(0, packet.length - 1);
                packet += "%";
    
                for(var id in socketIdList) {
                    if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                        weevilList[parseInt(id)].send(packet); // send event response
                    }
                }
            }
            else if(parseInt(roomId) == 305 && this.currentRoomId == 305) {
                var packet = "%xt%2#5%-1%";
                var topTenPlayers = this.server.grabTopTenTrackLeaderBoardUsers(roomId);
    
                if(topTenPlayers.length == 0) return;
    
                for(var player in topTenPlayers) {
                    packet += topTenPlayers[player][0] + ";" + topTenPlayers[player][1] + "#";
                }
    
                packet = packet.substr(0, packet.length - 1);
                packet += "%";
    
                for(var id in socketIdList) {
                    if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                        weevilList[parseInt(id)].send(packet); // send event response
                    }
                }
            }
            else {
                var packet = "%xt%2#5%-1%" + eventArgs + "%";
    
                for(var id in socketIdList) {
                    if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                        weevilList[parseInt(id)].send(packet); // send event response
                    }
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    doChef(roomId, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            var packet = "%xt%2#5%" + roomId + "%4;" + this.nickname + "%";

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                    weevilList[parseInt(id)].send(packet); // send event response
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    retireChef(roomId, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            var packet = "%xt%2#5%" + roomId + "%4;0%";

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                    weevilList[parseInt(id)].send(packet); // send event response
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    doWaiter(roomId, trayId, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            var packet = "%xt%2#5%" + roomId + "%3;" + trayId + ";" + this.nickname + "%";

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                    weevilList[parseInt(id)].send(packet); // send event response
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    retireWaiter(roomId, trayId, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            roomId = parseInt(roomId);
            trayId = parseInt(trayId);
            if(roomId !== 287 || this.currentRoomId !== 287 || trayId < 1 || trayId > 3) return;
            if(this.server.figgsTrays[trayId] !== this.nickname) return;

            var packet = "%xt%2#5%" + roomId + "%3;" + trayId + ";0%";
            this.server.figgsTrays[trayId] = null;

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                    weevilList[parseInt(id)].send(packet); // send event response
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    becomeWaiter(roomId, trayId, weevilList = undefined, socketIdList = undefined) {
        if (this.loggedIn) {
            if (trayId < 1 || trayId > 3 || this.currentRoomId != 287) return;

            if (this.server.figgsTrays[trayId] != null || this.isWaiter()) return;
            else {
                var packet = "%xt%2#5%" + roomId + "%3;" + trayId + ";" + this.nickname + "%";
                this.server.figgsTrays[trayId] = this.nickname;

                for(var id in weevilList) {
                    if(weevilList[id].currentRoomId == roomId) {
                        weevilList[id].send(packet);
                    }
                }
            }
        }
        else {
            this.socket.end();
            this.socket.destroy();
        }
    }

    isWaiter() {
        if (this.loggedIn) {
            for (var tray in this.server.figgsTrays) {
                if (this.server.figgsTrays[tray] == this.nickname) return true;
            }

            return false;
        }
        else {
            this.socket.end();
            this.socket.destroy();
        }
    }

    wearHat(roomId, hatId, Rgb, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            var weevil = this;
            var packet = "%xt%2#7%-1%" + this.userID + "%" + hatId + "%" + Rgb + "%";

            if(parseInt(hatId) == 1 && Rgb == "-140,-140,-140") {
                db.query("UPDATE users SET curHat = ? WHERE id = ?", ['|' + hatId + ':' + Rgb, this.idx], function(er, res) {
                    if(er) console.log(er);
                    else if(res.affectedRows == 1) {
                        // hopefully it updated the hat
                        weevil.curHat = "|" + hatId + ":" + Rgb;
                        for(var id in socketIdList) {
                            if(weevilList[parseInt(id)].socketID != weevil.socketID && weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                                weevilList[parseInt(id)].send(packet);
                            }
                        }
                    }
                    else {
                        console.log(res);
                    }
                });
            }
            else {
                db.query("SELECT * FROM weevilhats WHERE ownerName = ? AND apparelId = ? AND colour = ?", [this.nickname, parseInt(hatId), Rgb], function(err, result) {
                    try {
                        if(err) {
                            console.log(err);
                        }
                        else if(result.length > 0) {
                            db.query("UPDATE users SET curHat = ? WHERE id = ?", ['|' + hatId + ':' + Rgb, weevil.idx], function(er, res) {
                                if(er) console.log(er);
                                else if(res.affectedRows == 1) {
                                    // hopefully it updated the hat
                                    weevil.curHat = "|" + hatId + ":" + Rgb;
                                    for(var id in socketIdList) {
                                        if(weevilList[parseInt(id)].socketID != weevil.socketID && weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                                            weevilList[parseInt(id)].send(packet);
                                        }
                                    }
                                }
                                else {
                                    console.log(res);
                                }
                            });
                        }
                        else {
                            var unix = Math.floor(Date.now() / 1000);
                            var futureUnix = unix + 1 * 86400; // day
    
                            db.query("UPDATE users SET bannedUntil = ? WHERE username = ?", [futureUnix.toString(), weevil.nickname], function(err, result) {
                                if(err) {
                                    console.log(err);
                                }
                                else if(result.affectedRows == 1) {
                                    for(var id in socketIdList) {
                                        if(weevilList[parseInt(id)].nickname == weevil.nickname) {
                                            weevilList[parseInt(id)].socket.end();
                                            weevilList[parseInt(id)].socket.destroy();
                                        }
                                    }
                                }
                                else {
                                    console.log(result);
                                }
                            });
                        }
                    }
                    catch(Exception) {
                        console.log(Exception);
                    }
                });
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    removeHat(roomId, hatId, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            var weevil = this;
            var packet = "%xt%2#8%-1%" + this.userID + "%" + hatId + "%";

            db.query("SELECT EXISTS(SELECT 1 FROM weevilhats WHERE ownerName = ? AND apparelId = ?)", [this.nickname, parseInt(hatId)], function(err, result) {
                try {
                    if(err) {
                        console.log(err);
                    }
                    else if(result.length > 0) {
                        db.query("UPDATE users SET curHat = '' WHERE id = ?", [weevil.idx], function(er, res) {
                            if(er) console.log(er);
                            else if(res.affectedRows == 1) {
                                weevil.curHat = "";
                                for(var id in socketIdList) {
                                    if(weevilList[parseInt(id)].socketID != weevil.socketID && weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                                        weevilList[parseInt(id)].send(packet);
                                    }
                                }
                            }
                            else {
                                console.log(res);
                            }
                        });
                    }
                    else {
                        console.log(err + " | " + result);
                    }
                }
                catch(Exception) {
                    console.log(Exception);
                }
            });
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    doExpression(roomId, expressionId, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            var packet = "%xt%2#2%-1%" + this.userID + "%" + expressionId + "%";
            this.curExpression = expressionId;

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].socketID != this.socketID && weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                    weevilList[parseInt(id)].send(packet); // sending expression
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    changeDef(def) {
        if(this.loggedIn) {
            var weevil = this;
            db.query("SELECT def FROM users WHERE username = ?", [this.nickname], function(err, result) {
                try {
                    if(err) console.log(err);
                    else if(def = result[0].def) {
                        weevil.def = def.toString();
                    }
                }
                catch(Exception) {
                    console.log(Exception);
                }
            });
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    sendNestInvite(weevilName, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].nickname == weevilName) {
                    if(!weevilList[parseInt(id)].nestInvitations.hasOwnProperty(this.nickname) && !weevilList[parseInt(id)].declinedNestInvitations.hasOwnProperty(this.nickname)) {
                        weevilList[parseInt(id)].nestInvitations[this.nickname] = this.nickname;
                        weevilList[parseInt(id)].send("%xt%5#3%-1%" + this.nickname + "%");
                    }
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    removeNestInvite(weevilName, weevilList = undefined, socketIdList = undefined) {
        if(weevilName != "-1") {
            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].nickname == weevilName) {
                    if(weevilList[parseInt(id)].nestInvitations.hasOwnProperty(this.nickname)) {
                        delete weevilList[parseInt(id)].nestInvitations[this.nickname];
                        weevilList[parseInt(id)].send("%xt%5#6%-1%" + this.nickname + "%");
                        
                        if(weevilList[parseInt(id)].currentRoomId == this.server.roomWithIds["nest_" + this.nickname]) {
                            weevilList[parseInt(id)].send("%xt%5#7%-1%" + this.nickname + "%");
                        }
                    }
                }
            }
        }
        else {
            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].nestInvitations.hasOwnProperty(this.nickname)) {
                    delete weevilList[parseInt(id)].nestInvitations[this.nickname];
                    weevilList[parseInt(id)].send("%xt%5#6%-1%" + this.nickname + "%");
                    
                    if(weevilList[parseInt(id)].currentRoomId == this.server.roomWithIds["nest_" + this.nickname]) {
                        weevilList[parseInt(id)].send("%xt%5#7%-1%" + this.nickname + "%");
                    }
                }
            }
        }
    }

    declineNestInvite(weevilName, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].nickname == weevilName) {
                    if(this.nestInvitations.hasOwnProperty(weevilName)) {
                        if(!this.declinedNestInvitations.hasOwnProperty(weevilName)) this.declinedNestInvitations[weevilName] = weevilName;
                        delete this.nestInvitations[weevilName];
                        weevilList[parseInt(id)].send("%xt%5#4%-1%" + this.nickname + "%");
                    }
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    sendInNestReport(weevilName, inNest, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].nickname == weevilName) {
                    if(this.nestInvitations.hasOwnProperty(weevilName)) {
                        weevilList[parseInt(id)].send("%xt%5#5%-1%" + this.nickname + "%" + inNest + "%");
                    }
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    sendInNestData(locId, rNum, x, y, z, r, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            this.currentLocId = parseInt(locId);
            this.X = parseInt(x);
            this.Y = parseInt(y);
            this.Z = parseInt(z);
            this.R = parseInt(r);

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].socketID != this.socketID && weevilList[parseInt(id)].currentRoomId == this.currentRoomId) {
                    weevilList[parseInt(id)].send("%xt%5#2%-1%" + this.socketID + "%" + locId + "%" + rNum + "%" + x + "%" + y + "%" + z + "%" + r + "%");
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    openBusiness() {
        if(this.loggedIn) {
            this.plazaOpened = true;
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    closeBusiness() {
        if(this.loggedIn) {
            this.plazaOpened = false;
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    async getBusinesses() {
        if(this.loggedIn) {
            var weevil = this;

            return new Promise(resolve => {
                db.query("SELECT locID FROM nestinfo WHERE Weevil = ?", [weevil.nickname], function(err, result) {
                    if(err) {
                        console.log(err);
                        resolve("-1");
                    }
                    else if(result.length > 0) {
                        var businesses = "";

                        result.forEach(element => {
                            if(element.locID == 51 || element.locID == 52 || element.locID == 53 || element.locID == 54) {
                                if(!businesses.includes('2')) {
                                    businesses += "2";
                                }
                            }
                            else if(element.locID == 55 && !businesses.includes('7')) {
                                businesses += "7";
                            }
                        });

                        resolve((businesses == "" ? "-1" : businesses));
                    }
                    else {
                        console.log(result);
                    }
                });
            });
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    sendSetNestDoor(doorMarking, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].socketID != this.socketID && weevilList[parseInt(id)].currentRoomId == this.currentRoomId) {
                    weevilList[parseInt(id)].send("%xt%5#1%" + this.userID + "%" + doorMarking + "%");
                }
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    verifyUserWeb(weevilName, sessionId) {
        var weevil = this;

        return new Promise(resolve => {
            db.query("SELECT id, username, sessionKey, def, isModerator FROM users WHERE username = ?", [weevilName], function(err, result) {
                try {
                    if(err) {
                        console.log(err);
                        resolve(false);
                    }
                    else if(weevilName == result[0].username && sessionId == result[0].sessionKey) {
                        weevil.nickname = weevilName;
                        weevil.idx = result[0].id;
                        weevil.def = result[0].def;
                        weevil.isModerator = result[0].isModerator;
                        resolve(true);
                    }
                    else {
                        resolve(false);
                    }
                }
                catch(Exception) {
                    resolve(false);
                }
            });
        });
    }

    searchWeevil(weevilName) {
        return new Promise(resolve => {
            db.query("SELECT id, username, def, level, tycoon, lastLogin, isModerator FROM users WHERE username = ?", [weevilName], function(err, result) {
                try {
                    if(err) {
                        console.log(err);
                        resolve(null);
                    }
                    /*else if(weevilName != result[0].username) {
                        resolve(null);
                    }*/
                    var weevilData = JSON.stringify({"idx":result[0].id, "username":result[0].username, "weevilDef":result[0].def, "on":0, "level":result[0].level, "tycoon":result[0].tycoon, "lastLog":result[0].lastLogin, "sp":result[0].isModerator});
                    resolve(weevilData);
                }
                catch(Exception) {
                    resolve(null);
                }
            });
        });
    }

    searchWeevilByIdx(weevilIdx) {
        return new Promise(resolve => {
            db.query("SELECT id, username, def, level, tycoon, lastLogin, isModerator FROM users WHERE id = ?", [weevilIdx], function(err, result) {
                try {
                    if(err) {
                        console.log(err);
                        resolve(null);
                    }
                    else {
                        var weevilData = JSON.stringify({"idx":result[0].id, "username":result[0].username, "weevilDef":result[0].def, "on":0, "level":result[0].level, "tycoon":result[0].tycoon, "lastLog":result[0].lastLogin, "sp":result[0].isModerator});
                        resolve(weevilData);
                    }
                }
                catch(Exception) {
                    resolve(null);
                }
            });
        });
    }

    async getBuddyList(weevilList = undefined, socketIdList = undefined) {
        var weevil = this;

        return new Promise(resolve => {
            db.query("SELECT namesList FROM buddylist WHERE ownerName = ?", [weevil.nickname], async function(err, result) {
                try {
                    if(err) {
                        console.log(err);
                        resolve(null);
                    }
                    else if(result.length > 0) {
                        weevil.buddyList.length = 0;
                        var buddyNamesList = result[0].namesList.split(',');
                        if(buddyNamesList.length == 0) resolve(null);
        
                        for(var budIndex in buddyNamesList) {
                            if(buddyNamesList[parseInt(budIndex)] != "" && buddyNamesList[parseInt(budIndex)] != null) {
                                var weevilData = await weevil.searchWeevil(buddyNamesList[parseInt(budIndex)]);
                                
                                if(weevilData != null) {
                                    var jsonData = JSON.parse(weevilData);

                                    for(var id in socketIdList) {
                                        //await waitFor(10);
                                        if(weevilList[parseInt(id)].nickname == jsonData["username"] && weevilList[parseInt(id)].idx == jsonData["idx"]) {
                                            jsonData["on"] = 1;
                                        }
                                    }

                                    weevil.buddyList.push(jsonData);
                                }
                            }
                        }

                        resolve(null);
                    }
                    else {
                        resolve(null);
                    }
                }
                catch(Exception) {
                    resolve(null);
                }
            }); 
        });
    }

    async getBuddyCountOnLogin(weevilName, weevilList = undefined, socketIdList = undefined) {
        var weevil = this;

        return new Promise(resolve => {
            db.query("SELECT namesList FROM buddylist WHERE ownerName = ?", [weevilName], async function(err, result) {
                try {
                    if(err) {
                        console.log(err);
                        resolve(null);
                    }
                    else if(result.length > 0) {
                        var buddyList = [];
                        var buddyNamesList = result[0].namesList.split(',');
                        if(buddyNamesList.length == 0) resolve(null);
        
                        for(var budIndex in buddyNamesList) {
                            if(buddyNamesList[parseInt(budIndex)] != "" && buddyNamesList[parseInt(budIndex)] != null) {
                                var weevilData = await weevil.searchWeevil(buddyNamesList[parseInt(budIndex)]);
                                
                                if(weevilData != null) {
                                    var jsonData = JSON.parse(weevilData);

                                    for(var id in socketIdList) {
                                        //await waitFor(10);
                                        if(weevilList[parseInt(id)].nickname == jsonData["username"] && weevilList[parseInt(id)].idx == jsonData["idx"]) {
                                            jsonData["on"] = 1;
                                            buddyList.push(jsonData);
                                        }
                                    }
                                }
                            }
                        }

                        resolve(buddyList);
                    }
                    else {
                        resolve(null);
                    }
                }
                catch(Exception) {
                    resolve(null);
                }
            }); 
        });
    }

    async getBuddyRequests() {
        var weevil = this;

        return new Promise(resolve => {
            db.query("SELECT requestList FROM buddylist WHERE ownerName = ?", [weevil.nickname], async function(err, result) {
                try {
                    if(err) {
                        console.log(err);
                        resolve(null);
                    }
                    else if(result.length > 0) {
                        var buddyRequestList = result[0].requestList.split(',');
                        if(buddyRequestList.length == 0) resolve(null);
                        var buddyRequests = [];

                        for(var reqIndex in buddyRequestList) {
                            if(buddyRequestList[parseInt(reqIndex)] != "" && buddyRequestList[parseInt(reqIndex)] != null) {
                                var weevilData = await weevil.searchWeevil(buddyRequestList[parseInt(reqIndex)]);

                                if(weevilData != null) {
                                    buddyRequests.push(JSON.parse(weevilData));
                                }
                            }
                        }

                        resolve(buddyRequests);
                    }
                    else {
                        resolve(null);
                    }
                }
                catch(Exception) {
                    resolve(null);
                }
            });
        });
    }

    sendBuddyRequest(buddyIdx) {
        var weevil = this;

        return new Promise(async resolve => {
            try {
                if(this.buddyList.length >= 100) {
                    resolve(false);
                }

                var weevilData = await this.searchWeevilByIdx(buddyIdx);

                if(weevilData != null && JSON.parse(weevilData)["username"] != this.nickname) {
                    var jsonData = JSON.parse(weevilData);

                    if(jsonData["sp"] == 1 && this.isModerator != "1") {
                        resolve(false);
                        return;
                    }

                    if(jsonData["sp"] == 0 && this.isModerator == "1") {
                        resolve(false);
                        return;
                    }

                    db.query("SELECT requestList FROM buddylist WHERE ownerName = ?", [jsonData["username"]], function(err, result) {
                        db.query("SELECT requestList FROM buddylist WHERE ownerName = ?", [weevil.nickname], function(err1, res1) {
                            if(err || err1) {
                                console.log(err);
                                console.log(err1);
                            }
                            else if(result.length > 0 && res1.length > 0) {
                                if(result[0].requestList.toString().includes(weevil.nickname) || res1[0].requestList.toString().includes(jsonData["username"])) {
                                    resolve(false);
                                }
                                else {
                                    db.query("UPDATE buddylist SET requestList = CONCAT(requestList, '" + weevil.nickname + ",') WHERE ownerName = ?", [jsonData["username"]], function(er, res) {
                                        if(er) {
                                            console.log(er);
                                            resolve(false);
                                        }
                                        else {
                                            if(res.affectedRows == 1) {
                                                resolve(true);
                                            }
                                            else {
                                                resolve(false);
                                            }
                                        }
                                    });
                                }
                            }
                            else {
                                resolve(false);
                            }
                        });
                    });
                }
                else {
                    resolve(false);
                }
            }
            catch(Exception) {
                resolve(false);
            }
        });
    }

    acceptBuddyRequest(buddyIdx) {
        var weevil = this;

        return new Promise(async resolve => {
            try {
                if(this.buddyList.length >= 100) {
                    resolve(false);
                }

                var weevilData = await this.searchWeevilByIdx(buddyIdx);

                if(weevilData != null && JSON.parse(weevilData)["username"] != this.nickname) {
                    var jsonData = JSON.parse(weevilData);

                    db.query("SELECT requestList FROM buddylist WHERE ownerName = ?", [weevil.nickname], function(err, result) {
                        if(err) {
                            console.log(err);
                            resolve(false);
                        }
                        else if(result.length > 0) {
                            if(result[0].requestList.toString().includes(jsonData["username"])) {
                                db.query("UPDATE buddylist SET requestList = REPLACE(requestList, '" + jsonData["username"] + ",', ''), namesList = CONCAT(namesList, '" + jsonData["username"] + ",') WHERE ownerName = ?", [weevil.nickname], function(er, res) {
                                    if(er) {
                                        console.log(er);
                                        resolve(false);
                                    }
                                    else if(res.affectedRows == 1) {
                                        db.query("UPDATE buddylist SET requestList = REPLACE(requestList, '" + weevil.nickname + ",', ''), namesList = CONCAT(namesList, '" + weevil.nickname + ",') WHERE ownerName = ?", [jsonData["username"]], function(err1, res1) {
                                            if(err1) {
                                                console.log(err1);
                                                resolve(false);
                                            }
                                            else if(res1.affectedRows == 1) {
                                                resolve(true);
                                            }
                                            else {
                                                resolve(false);
                                            }
                                        });
                                    }
                                    else {
                                        resolve(false);
                                    }
                                });
                            }
                            else {
                                resolve(false);
                            }
                        }
                        else {
                            resolve(false);
                        }
                    });
                }
                else {
                    resolve(false);
                }
            }
            catch(Exception) {
                resolve(false);
            }
        });
    }

    denyBuddyRequest(buddyIdx) {
        var weevil = this;

        return new Promise(async resolve => {
            try {
                var weevilData = await this.searchWeevilByIdx(buddyIdx);

                if(weevilData != null && JSON.parse(weevilData)["username"] != this.nickname) {
                    var jsonData = JSON.parse(weevilData);

                    db.query("SELECT requestList FROM buddylist WHERE ownerName = ?", [weevil.nickname], function(err, result) {
                        if(err) {
                            console.log(err);
                            resolve(false);
                        }
                        else if(result.length > 0) {
                            if(result[0].requestList.toString().includes(jsonData["username"])) {
                                db.query("UPDATE buddylist SET requestList = REPLACE(requestList, '" + jsonData["username"] + ",', '') WHERE ownerName = ?", [weevil.nickname], function(er, res) {
                                    if(er) {
                                        console.log(er);
                                        resolve(false);
                                    }
                                    else if(res.affectedRows == 1) {
                                        resolve(true);
                                    }
                                    else {
                                        resolve(false);
                                    }
                                });
                            }
                            else {
                                resolve(false);
                            }
                        }
                        else {
                            resolve(false);
                        }
                    });
                }
                else {
                    resolve(false);
                }
            }
            catch(Exception) {
                resolve(false);
            }
        });
    }

    deleteBuddy(buddyIdx) {
        var weevil = this;

        return new Promise(async resolve => {
            try {
                var weevilData = await this.searchWeevilByIdx(buddyIdx);

                if(weevilData != null && JSON.parse(weevilData)["username"] != this.nickname) {
                    var jsonData = JSON.parse(weevilData);

                    db.query("SELECT namesList FROM buddylist WHERE ownerName = ?", [this.nickname], function(err, result) {
                        if(err) {
                            console.log(err);
                            resolve(false);
                        }
                        else if(result.length > 0) {
                            if(result[0].namesList.toString().includes(jsonData["username"])) {
                                db.query("UPDATE buddylist SET namesList = REPLACE(namesList, '" + jsonData["username"] + ",', '') WHERE ownerName = ?", [weevil.nickname], function(er, res) {
                                    if(er) {
                                        console.log(er);
                                        resolve(false);
                                    }
                                    else if(res.affectedRows == 1) {
                                        db.query("UPDATE buddylist SET namesList = REPLACE(namesList, '" + weevil.nickname + ",', '') WHERE ownerName = ?", [jsonData["username"]], function(err1, res1) {
                                            if(err1) {
                                                console.log(err1);
                                                resolve(false);
                                            }
                                            else if(res1.affectedRows == 1) {
                                                resolve(true);
                                            }
                                            else {
                                                resolve(false);
                                            }
                                        });
                                    }
                                    else {
                                        resolve(false);
                                    }
                                });
                            }
                            else {
                                resolve(false);
                            }
                        }
                        else {
                            resolve(false);
                        }
                    });
                }
                else {
                    resolve(false);
                }
            }
            catch(Exception) {
                resolve(false);
            }
        });
    }

    gameTurnTimer(otherPlayerData, userID, slot, gameTypeID, weevilList = undefined, socketIdList = undefined) {
        // activated when its a players turn. anything below will be executed after 1 minute has gone by, unless deactivated.
        // player will be forced to leave the game and then disconnected from BWR entirely.
        otherPlayerData.leaveGame(userID, slot, gameTypeID, weevilList, socketIdList);
        //otherPlayerData.socket.end();
        //otherPlayerData.socket.destroy();
    }

    clearGame(gameTypeID) {
        switch(gameTypeID) {
            case "mulch4":
                for(var prop in this.konnectMulchData) {
                    if(this.konnectMulchData.hasOwnProperty(prop)) {
                        delete this.konnectMulchData[prop];
                    }
                }
                return;
            case "ballGame":
                for(var prop in this.poolData) {
                    if(this.poolData.hasOwnProperty(prop)) {
                        delete this.poolData[prop];
                    }
                }
                return;
            case "squaresGame":
                for(var prop in this.squaresData) {
                    if(this.squaresData.hasOwnProperty(prop)) {
                        delete this.squaresData[prop];
                    }
                }
                return;
            case "reverseMulch":
                for(var prop in this.reverseMulchData) {
                    if(this.reverseMulchData.hasOwnProperty(prop)) {
                        delete this.reverseMulchData[prop];
                    }
                }
                return;
            default:
                return;
        }
    }

    getAmountOfPlayersInGame(userID, slot, gameTypeID, checkUrWeevil, weevilList = undefined, socketIdList = undefined) {
        switch(gameTypeID) {
            case "mulch4":
                var amt = 0;

                for(var id in socketIdList) {
                    if(!checkUrWeevil) {
                        if(weevilList[parseInt(id)].konnectMulchData["slot"] == slot && weevilList[parseInt(id)].nickname != userID) {
                            amt++;
                        }
                    }
                    else {
                        if(weevilList[parseInt(id)].konnectMulchData["slot"] == slot) {
                            amt++;
                        }
                    }
                }

                return amt;
            case "ballGame":
                var amt = 0;

                for(var id in socketIdList) {
                    if(!checkUrWeevil) {
                        if(weevilList[parseInt(id)].poolData["slot"] == slot && weevilList[parseInt(id)].nickname != userID) {
                            amt++;
                        }
                    }
                    else {
                        if(weevilList[parseInt(id)].poolData["slot"] == slot) {
                            amt++;
                        }
                    }
                }

                return amt;
            case "squaresGame":
                var amt = 0;

                for(var id in socketIdList) {
                    if(!checkUrWeevil) {
                        if(weevilList[parseInt(id)].squaresData["slot"] == slot && weevilList[parseInt(id)].nickname != userID) {
                            amt++;
                        }
                    }
                    else {
                        if(weevilList[parseInt(id)].squaresData["slot"] == slot) {
                            amt++;
                        }
                    }
                }

                return amt;
            case "reverseMulch":
                var amt = 0;

                for(var id in socketIdList) {
                    if(!checkUrWeevil) {
                        if(weevilList[parseInt(id)].reverseMulchData["slot"] == slot && weevilList[parseInt(id)].nickname != userID) {
                            amt++;
                        }
                    }
                    else {
                        if(weevilList[parseInt(id)].reverseMulchData["slot"] == slot) {
                            amt++;
                        }
                    }
                }

                return amt;
            default:
                return 0;
        }
    }

    leaveGame(userID, slot, gameTypeID, weevilList = undefined, socketIdList = undefined) {
        switch(gameTypeID) {
            case "mulch4":
                if(this.konnectMulchData["slot"] == null && this.getAmountOfPlayersInGame(userID, slot, gameTypeID, false, weevilList, socketIdList) >= 1) {
                    return;
                }

                for(var id in socketIdList) {
                    if(weevilList[parseInt(id)].konnectMulchData["slot"] == slot) {
                        weevilList[parseInt(id)].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='trace' t='s'>SERVER TRACE-------------removing player " + userID + "</var><var n='command' t='s'>trace</var></dataObj>]]></body></msg>");
                        weevilList[parseInt(id)].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='reason' t='s'>pl</var><var n='commandType' t='s'>tbmt</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>go</var></dataObj>]]></body></msg>");
                        weevilList[parseInt(id)].clearGame(gameTypeID);
                    }
                }

                if(this.konnectMulchData["turnTimer"] != null) {
                    clearInterval(this.konnectMulchData["turnTimer"]);
                }

                this.clearGame(gameTypeID);
                return;
            case "ballGame":
                if(this.poolData["slot"] == null && this.getAmountOfPlayersInGame(userID, slot, gameTypeID, false, weevilList, socketIdList) >= 1) {
                    return;
                }

                for(var id in socketIdList) {
                    if(weevilList[parseInt(id)].poolData["slot"] == slot) {
                        weevilList[parseInt(id)].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='trace' t='s'>SERVER TRACE-------------removing player " + userID + "</var><var n='command' t='s'>trace</var></dataObj>]]></body></msg>");
                        weevilList[parseInt(id)].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='reason' t='s'>pl</var><var n='commandType' t='s'>tbmt</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>go</var></dataObj>]]></body></msg>");
                        weevilList[parseInt(id)].clearGame(gameTypeID);
                    }
                }

                if(this.poolData["turnTimer"] != null) {
                    clearInterval(this.poolData["turnTimer"]);
                }

                this.clearGame(gameTypeID);
                return;
            case "squaresGame":
                if(this.squaresData["slot"] == null && this.getAmountOfPlayersInGame(userID, slot, gameTypeID, false, weevilList, socketIdList) >= 1) {
                    return;
                }

                for(var id in socketIdList) {
                    if(weevilList[parseInt(id)].squaresData["slot"] == slot) {
                        weevilList[parseInt(id)].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='trace' t='s'>SERVER TRACE-------------removing player " + userID + "</var><var n='command' t='s'>trace</var></dataObj>]]></body></msg>");
                        weevilList[parseInt(id)].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='reason' t='s'>pl</var><var n='commandType' t='s'>tbmt</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>go</var></dataObj>]]></body></msg>");
                        weevilList[parseInt(id)].clearGame(gameTypeID);
                    }
                }

                if(this.squaresData["turnTimer"] != null) {
                    clearInterval(this.squaresData["turnTimer"]);
                }

                this.clearGame(gameTypeID);
                return;
            case "reverseMulch":
                if(this.reverseMulchData["slot"] == null && this.getAmountOfPlayersInGame(userID, slot, gameTypeID, false, weevilList, socketIdList) >= 1) {
                    return;
                }

                for(var id in socketIdList) {
                    if(weevilList[parseInt(id)].reverseMulchData["slot"] == slot) {
                        weevilList[parseInt(id)].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='trace' t='s'>SERVER TRACE-------------removing player " + userID + "</var><var n='command' t='s'>trace</var></dataObj>]]></body></msg>");
                        weevilList[parseInt(id)].send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='reason' t='s'>pl</var><var n='commandType' t='s'>tbmt</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>go</var></dataObj>]]></body></msg>");
                        weevilList[parseInt(id)].clearGame(gameTypeID);
                    }
                }

                if(this.reverseMulchData["turnTimer"] != null) {
                    clearInterval(this.reverseMulchData["turnTimer"]);
                }

                this.clearGame(gameTypeID);
                return;
            default:
                return;
        }
    }

    joinGame(userID, slot, gameTypeID, count, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            switch(gameTypeID) {
                case "mulch4":
                    // check if there is an existing game. if so, make user leave it before joining a new one. (this really shouldnt happen but whatever)
                    if(this.konnectMulchData["slot"] != null) {
                        this.leaveGame(userID, slot, gameTypeID, weevilList, socketIdList);
                    }
    
                    if(this.getAmountOfPlayersInGame(userID, slot, gameTypeID, false, weevilList, socketIdList) >= 2) {
                        this.modMsg("Sorry, there seems to be a game going on right now. Spectating games is currently unavailable.");
                        return;
                    }
    
                    // now that game data is cleared (hopefully), set the new data needed
                    // need to determine if player is 1st or 2nd. do a quick loop from the weevilList to see if anyone has the same slot and gametype
                    var otherPlayerData = undefined;
                    var playerId = 0; // auto set to player1 unless changed by loop
    
                    for(var id in socketIdList) {
                        if(weevilList[parseInt(id)].konnectMulchData["slot"] == slot && weevilList[parseInt(id)].konnectMulchData["gameTypeID"] == gameTypeID && weevilList[parseInt(id)].nickname != userID) {
                            otherPlayerData = weevilList[parseInt(id)];
                            playerId = 1;
                            break;
                        }
                    }
    
                    this.konnectMulchData["slot"] = slot;
                    this.konnectMulchData["gameTypeID"] = gameTypeID;
                    this.konnectMulchData["playerId"] = playerId;
                    this.konnectMulchData["grid"] = [
                        [-1, -1, -1, -1, -1, -1, -1],
                        [-1, -1, -1, -1, -1, -1, -1],
                        [-1, -1, -1, -1, -1, -1, -1],
                        [-1, -1, -1, -1, -1, -1, -1],
                        [-1, -1, -1, -1, -1, -1, -1],
                        [-1, -1, -1, -1, -1, -1, -1]
                    ];
    
                    // after all game data is set, send the response packets to both players
                    if(otherPlayerData == undefined) {
                        // indicates player1, so only send response to yourself
                        this.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='gameStart' t='s'>false</var><var n='player1' t='s'>" + userID + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>j</var><var n='joinData' t='s'>needOverride</var></dataObj>]]></body></msg>");
                    }
                    else {
                        //otherPlayerData.konnectMulchData["turnTimer"] = setInterval(this.gameTurnTimer, 60000, otherPlayerData, otherPlayerData.nickname, slot, gameTypeID, weevilList, socketIdList);
                        this.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='gameStart' t='s'>true</var><var n='player1' t='s'>" + otherPlayerData.nickname + "</var><var n='player2' t='s'>" + userID + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>j</var><var n='joinData' t='s'>needOverride</var></dataObj>]]></body></msg>");
                        otherPlayerData.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='gameStart' t='s'>true</var><var n='player1' t='s'>" + otherPlayerData.nickname + "</var><var n='player2' t='s'>" + userID + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>j</var><var n='joinData' t='s'>needOverride</var></dataObj>]]></body></msg>");
                    }
    
                    return;
                case "ballGame":
                    if(this.poolData["slot"] != null) {
                        this.leaveGame(userID, slot, gameTypeID, weevilList, socketIdList);
                    }
    
                    if(this.getAmountOfPlayersInGame(userID, slot, gameTypeID, false, weevilList, socketIdList) >= 2) {
                        this.modMsg("Sorry, there seems to be a game going on right now. Spectating games is currently unavailable.");
                        return;
                    }
    
                    var otherPlayerData = undefined;
                    var playerId = 0;
    
                    for(var id in socketIdList) {
                        if(weevilList[parseInt(id)].poolData["slot"] == slot && weevilList[parseInt(id)].poolData["gameTypeID"] == gameTypeID && weevilList[parseInt(id)].nickname != userID) {
                            otherPlayerData = weevilList[parseInt(id)];
                            playerId = 1;
                            break;
                        }
                    }
    
                    this.poolData["slot"] = slot;
                    this.poolData["gameTypeID"] = gameTypeID;
                    this.poolData["playerId"] = playerId;
                    this.poolData["ballCount"] = parseInt(count);
    
                    if(otherPlayerData == undefined) {
                        this.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='gameStart' t='s'>false</var><var n='player1' t='s'>" + userID + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>j</var><var n='joinData' t='s'>needOverride</var></dataObj>]]></body></msg>");
                    }
                    else {
                        //otherPlayerData.poolData["turnTimer"] = setInterval(this.gameTurnTimer, 60000, otherPlayerData, otherPlayerData.nickname, slot, gameTypeID, weevilList, socketIdList);
                        this.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='gameStart' t='s'>true</var><var n='player1' t='s'>" + otherPlayerData.nickname + "</var><var n='player2' t='s'>" + userID + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>j</var><var n='joinData' t='s'>needOverride</var></dataObj>]]></body></msg>");
                        otherPlayerData.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='gameStart' t='s'>true</var><var n='player1' t='s'>" + otherPlayerData.nickname + "</var><var n='player2' t='s'>" + userID + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>j</var><var n='joinData' t='s'>needOverride</var></dataObj>]]></body></msg>");
                    }
    
                    return;
                case "squaresGame":
                    if(this.squaresData["slot"] != null) {
                        this.leaveGame(userID, slot, gameTypeID, weevilList, socketIdList);
                    }
    
                    if(this.getAmountOfPlayersInGame(userID, slot, gameTypeID, false, weevilList, socketIdList) >= 2) {
                        this.modMsg("Sorry, there seems to be a game going on right now. Spectating games is currently unavailable.");
                        return;
                    }
    
                    var otherPlayerData = undefined;
                    var playerId = 0;
    
                    for(var id in socketIdList) {
                        if(weevilList[parseInt(id)].squaresData["slot"] == slot && weevilList[parseInt(id)].squaresData["gameTypeID"] == gameTypeID && weevilList[parseInt(id)].nickname != userID) {
                            otherPlayerData = weevilList[parseInt(id)];
                            playerId = 1;
                            break;
                        }
                    }
    
                    this.squaresData["slot"] = slot;
                    this.squaresData["gameTypeID"] = gameTypeID;
                    this.squaresData["playerId"] = playerId;
    
                    if(otherPlayerData == undefined) {
                        this.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='gameStart' t='s'>false</var><var n='player1' t='s'>" + userID + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>j</var><var n='joinData' t='s'>needOverride</var></dataObj>]]></body></msg>");
                    }
                    else {
                        //otherPlayerData.squaresData["turnTimer"] = setInterval(this.gameTurnTimer, 60000, otherPlayerData, otherPlayerData.nickname, slot, gameTypeID, weevilList, socketIdList);
                        this.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='gameStart' t='s'>true</var><var n='player1' t='s'>" + otherPlayerData.nickname + "</var><var n='player2' t='s'>" + userID + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>j</var><var n='joinData' t='s'>needOverride</var></dataObj>]]></body></msg>");
                        otherPlayerData.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='gameStart' t='s'>true</var><var n='player1' t='s'>" + otherPlayerData.nickname + "</var><var n='player2' t='s'>" + userID + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>j</var><var n='joinData' t='s'>needOverride</var></dataObj>]]></body></msg>");
                    }
    
                    return;
                case "reverseMulch":
                    if(this.reverseMulchData["slot"] != null) {
                        this.leaveGame(userID, slot, gameTypeID, weevilList, socketIdList);
                    }
    
                    if(this.getAmountOfPlayersInGame(userID, slot, gameTypeID, false, weevilList, socketIdList) >= 2) {
                        this.modMsg("Sorry, there seems to be a game going on right now. Spectating games is currently unavailable.");
                        return;
                    }
    
                    var otherPlayerData = undefined;
                    var playerId = 0;
    
                    for(var id in socketIdList) {
                        if(weevilList[parseInt(id)].reverseMulchData["slot"] == slot && weevilList[parseInt(id)].reverseMulchData["gameTypeID"] == gameTypeID && weevilList[parseInt(id)].nickname != userID) {
                            otherPlayerData = weevilList[parseInt(id)];
                            playerId = 1;
                            break;
                        }
                    }
    
                    this.reverseMulchData["slot"] = slot;
                    this.reverseMulchData["gameTypeID"] = gameTypeID;
                    this.reverseMulchData["playerId"] = playerId;
    
                    if(otherPlayerData == undefined) {
                        this.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='gameStart' t='s'>false</var><var n='player1' t='s'>" + userID + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>j</var><var n='joinData' t='s'>needOverride</var></dataObj>]]></body></msg>");
                    }
                    else {
                        //otherPlayerData.reverseMulchData["turnTimer"] = setInterval(this.gameTurnTimer, 60000, otherPlayerData, otherPlayerData.nickname, slot, gameTypeID, weevilList, socketIdList);
                        this.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='gameStart' t='s'>true</var><var n='player1' t='s'>" + otherPlayerData.nickname + "</var><var n='player2' t='s'>" + userID + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>j</var><var n='joinData' t='s'>needOverride</var></dataObj>]]></body></msg>");
                        otherPlayerData.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='gameStart' t='s'>true</var><var n='player1' t='s'>" + otherPlayerData.nickname + "</var><var n='player2' t='s'>" + userID + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>j</var><var n='joinData' t='s'>needOverride</var></dataObj>]]></body></msg>");
                    }
    
                    return;
                default:
                    return;
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    placeMulchBlock(userID, slot, gameTypeID, column, row, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            switch(gameTypeID) {
                case "mulch4":
                    var newGrid = this.konnectMulchData["grid"];
                    var otherPlayerData = undefined;
                    column = parseInt(column);
    
                    for(var id in socketIdList) {
                        if(weevilList[parseInt(id)].konnectMulchData["slot"] == slot && weevilList[parseInt(id)].konnectMulchData["gameTypeID"] == gameTypeID && weevilList[parseInt(id)].nickname != userID) {
                            otherPlayerData = weevilList[parseInt(id)];
                            break;
                        }
                    }
    
                    if(otherPlayerData != undefined && this.getAmountOfPlayersInGame(userID, slot, gameTypeID, true, weevilList, socketIdList) >= 2) {
                        // this should patch people trying to exploit packets
                        for(var rowNum = 0; rowNum <= newGrid.length - 1; rowNum++) {
                            if(newGrid[rowNum][column] == -1) {
                                clearInterval(this.konnectMulchData["turnTimer"]); // should cancel the timer
                                newGrid[rowNum][column] = this.konnectMulchData["playerId"];
                                this.konnectMulchData["grid"] = newGrid;
                                otherPlayerData.konnectMulchData["grid"] = newGrid;
    
                                var winningSlots = this.checkForWinner(gameTypeID, column, rowNum, this.konnectMulchData["playerId"], newGrid);
                                var staleMate = (winningSlots == null ? this.checkBoardFull(0, newGrid) : false);
    
                                this.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='winnerFound' t='s'>" + (winningSlots != null ? "true" : "false") + "</var>" + (winningSlots != null ? "<var n='winningSlots' t='s'>" + winningSlots + "</var>" : "") + "<var n='col' t='s'>" + column + "</var><var n='commandType' t='s'>tbmt</var>" + (winningSlots == null ? "<var n='nextPlayer' t='s'>" + otherPlayerData.nickname + "</var>" : "") + "<var n='success' t='s'>true</var><var n='row' t='s'>" + rowNum + "</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>tt</var><var n='staleMate' t='s'>" + (staleMate ? "true" : "false") + "</var></dataObj>]]></body></msg>");
                                otherPlayerData.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='winnerFound' t='s'>" + (winningSlots != null ? "true" : "false") + "</var>" + (winningSlots != null ? "<var n='winningSlots' t='s'>" + winningSlots + "</var>" : "") + "<var n='col' t='s'>" + column + "</var><var n='commandType' t='s'>tbmt</var>" + (winningSlots == null ? "<var n='nextPlayer' t='s'>" + otherPlayerData.nickname + "</var>" : "") + "<var n='success' t='s'>true</var><var n='row' t='s'>" + rowNum + "</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>tt</var><var n='staleMate' t='s'>" + (staleMate ? "true" : "false") + "</var></dataObj>]]></body></msg>");
    
                                if(winningSlots != null || staleMate) {
                                    this.clearGame(gameTypeID);
                                    otherPlayerData.clearGame(gameTypeID);
                                }
                                else {
                                    // set a new timer for the next players turn
                                    //otherPlayerData.konnectMulchData["turnTimer"] = setInterval(this.gameTurnTimer, 60000, otherPlayerData, otherPlayerData.nickname, slot, gameTypeID, weevilList, socketIdList);
                                }
    
                                return;
                            }
                        }
                    }
                    /*else {
                        this.modMsg("Nice exploit attempt loooool - BWR Team");
                    }*/
    
                    return;
                case "reverseMulch":
                    var otherPlayerData = undefined;
                    column = parseInt(column);
                    row = parseInt(row);
    
                    for(var id in socketIdList) {
                        if(weevilList[parseInt(id)].reverseMulchData["slot"] == slot && weevilList[parseInt(id)].reverseMulchData["gameTypeID"] == gameTypeID && weevilList[parseInt(id)].nickname != userID) {
                            otherPlayerData = weevilList[parseInt(id)];
                            break;
                        }
                    }
    
                    if(otherPlayerData != undefined) {
                        this.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='winnerFound' t='s'>false</var><var n='col' t='s'>" + column + "</var><var n='commandType' t='s'>tbmt</var><var n='nextPlayer' t='s'>" + otherPlayerData.nickname + "</var><var n='success' t='s'>true</var><var n='row' t='s'>" + row + "</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>tt</var><var n='staleMate' t='s'>false</var></dataObj>]]></body></msg>");
                        otherPlayerData.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='winnerFound' t='s'>false</var><var n='col' t='s'>" + column + "</var><var n='commandType' t='s'>tbmt</var><var n='nextPlayer' t='s'>" + otherPlayerData.nickname + "</var><var n='success' t='s'>true</var><var n='row' t='s'>" + row + "</var><var n='userID' t='s'>" + userID + "</var><var n='command' t='s'>tt</var><var n='staleMate' t='s'>false</var></dataObj>]]></body></msg>");
                    }
                    /*else {
                        this.modMsg("Nice exploit attempt loooool - BWR Team");
                    }*/
    
                    return;
                default:
                    return;
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    checkBoardFull(col = 0, newGrid) {
        for(var rowNum = 0; rowNum <= newGrid.length - 1; rowNum++) {
            if(newGrid[rowNum][col] == -1) {
                return false;
            }
        }

        if(col < 6) {
            return this.checkBoardFull(col + 1, newGrid);
        }
        else {
            return true;
        }
    }

    checkForWinner(gameTypeID, column, rowNum, playerNumber, newGrid) {
        switch(gameTypeID) {
            case "mulch4":
                for(let axis in this.server.directionsMatrix) {
                    let numMatches = 1;
                    var winningSlots = column + ":" + rowNum;

                    for(let direction in this.server.directionsMatrix[axis]) {
                        let cellReference = [rowNum, column];
                        let testCell = newGrid[cellReference[0]][cellReference[1]];

                        while(testCell == playerNumber) {
                            try {
                                cellReference[0] += this.server.directionsMatrix[axis][direction][0];
                                cellReference[1] += this.server.directionsMatrix[axis][direction][1];
                                testCell = newGrid[cellReference[0]][cellReference[1]];

                                if(testCell == playerNumber) {
                                    numMatches += 1;
                                    winningSlots += "," + cellReference[1] + ":" + cellReference[0];

                                    if(numMatches >= 4) {
                                        return winningSlots;
                                    }
                                }
                            } catch(error) {
                                break;
                            }
                        }

                        if(numMatches >= 4) {
                            return winningSlots;
                        }
                    }
                }
                return null;
            default:
                return null;
        }
    }

    takePoolShot(userID, slot, gameTypeID, x, y, idd, ids, poss, dirx, diry, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            switch(gameTypeID) {
                case "ballGame":
                    var otherPlayerData = undefined;
    
                    for(var id in socketIdList) {
                        if(weevilList[parseInt(id)].poolData["slot"] == slot && weevilList[parseInt(id)].poolData["gameTypeID"] == gameTypeID && weevilList[parseInt(id)].nickname != userID) {
                            otherPlayerData = weevilList[parseInt(id)];
                            break;
                        }
                    }
    
                    if(otherPlayerData != undefined && this.getAmountOfPlayersInGame(userID, slot, gameTypeID, true, weevilList, socketIdList) >= 2) {
                        //clearInterval(this.poolData["turnTimer"]);
                        //otherPlayerData.poolData["turnTimer"] = setInterval(this.gameTurnTimer, 60000, otherPlayerData, otherPlayerData.nickname, slot, gameTypeID, weevilList, socketIdList);
                        otherPlayerData.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='nextPlayer' t='s'>" + otherPlayerData.nickname + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='x' t='s'>" + x + "</var><var n='y' t='s'>" + y + "</var><var n='id' t='s'>" + idd + "</var><var n='ids' t='s'>" + ids + "</var><var n='poss' t='s'>" + poss + "</var><var n='dirx' t='s'>" + dirx + "</var><var n='diry' t='s'>" + diry + "</var><var n='command' t='s'>tt</var></dataObj>]]></body></msg>");
                    }
                    /*else {
                        this.modMsg("Nice exploit attempt loooool - BWR Team");
                    }*/
    
                    return;
                default:
                    return;
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    placeSquareLine(userID, slot, gameTypeID, col1, col2, row1, row2, p1sc, p2sc, keepPlaying, nextPlayer, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            switch(gameTypeID) {
                case "squaresGame":
                    var otherPlayerData = undefined;
                    p1sc = parseInt(p1sc);
                    p2sc = parseInt(p2sc);
    
                    for(var id in socketIdList) {
                        if(weevilList[parseInt(id)].squaresData["slot"] == slot && weevilList[parseInt(id)].squaresData["gameTypeID"] == gameTypeID && weevilList[parseInt(id)].nickname != userID) {
                            otherPlayerData = weevilList[parseInt(id)];
                            break;
                        }
                    }
    
                    if(otherPlayerData != undefined && this.getAmountOfPlayersInGame(userID, slot, gameTypeID, true, weevilList, socketIdList) >= 2) {
                        clearInterval(this.squaresData["turnTimer"]);
                        var winner = "";
    
                        if(p1sc + p2sc >= 25) {
                            if(p1sc > p2sc) {
                                if(this.squaresData["playerId"] == 0) {
                                    winner = userID;
                                }
                                else {
                                    winner = otherPlayerData.nickname;
                                }
                            }
                            else if(p2sc > p1sc) {
                                if(this.squaresData["playerId"] == 1) {
                                    winner = userID;
                                }
                                else {
                                    winner = otherPlayerData.nickname;
                                }
                            }
                        }
    
                        this.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='nextPlayer' t='s'>" + nextPlayer + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='row1' t='s'>" + row1 + "</var><var n='row2' t='s'>" + row2 + "</var><var n='col1' t='s'>" + col1 + "</var><var n='col2' t='s'>" + col2 + "</var><var n='keepingPlay' t='s'>" + keepPlaying + "</var>" + (winner != "" ? "<var n='winnerFound' t='s'>true</var><var n='winner' t='s'>" + winner + "</var>" : "") + "<var n='command' t='s'>tt</var></dataObj>]]></body></msg>");
                        otherPlayerData.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='nextPlayer' t='s'>" + nextPlayer + "</var><var n='success' t='s'>true</var><var n='userID' t='s'>" + userID + "</var><var n='row1' t='s'>" + row1 + "</var><var n='row2' t='s'>" + row2 + "</var><var n='col1' t='s'>" + col1 + "</var><var n='col2' t='s'>" + col2 + "</var><var n='keepingPlay' t='s'>" + keepPlaying + "</var>" + (winner != "" ? "<var n='winnerFound' t='s'>true</var><var n='winner' t='s'>" + winner + "</var>" : "") + "<var n='command' t='s'>tt</var></dataObj>]]></body></msg>");
    
                        if(winner != "") {
                            if(otherPlayerData.squaresData["turnTimer"] != null) {
                                clearInterval(otherPlayerData.squaresData["turnTimer"]);
                            }
    
                            this.clearGame(gameTypeID);
                            otherPlayerData.clearGame(gameTypeID);
                        }
                        else if(keepPlaying == "true") {
                            //this.squaresData["turnTimer"] = setInterval(this.gameTurnTimer, 60000, this, this.nickname, slot, gameTypeID, weevilList, socketIdList);
                        }
                        else {
                            //otherPlayerData.squaresData["turnTimer"] = setInterval(this.gameTurnTimer, 60000, otherPlayerData, otherPlayerData.nickname, slot, gameTypeID, weevilList, socketIdList);
                        }
                    }
                    /*else {
                        this.modMsg("Nice exploit attempt loooool - BWR Team");
                    }*/
    
                    return;
                default:
                    return;
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    sendGameInfo(userID, slot, gameTypeID, userWinner, userLoser, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            switch(gameTypeID) {
                case "ballGame":
                    var otherPlayerData = undefined;
    
                    for(var id in socketIdList) {
                        if(weevilList[parseInt(id)].poolData["slot"] == slot && weevilList[parseInt(id)].poolData["gameTypeID"] == gameTypeID && weevilList[parseInt(id)].nickname != userID) {
                            otherPlayerData = weevilList[parseInt(id)];
                            break;
                        }
                    }
    
                    if(otherPlayerData != undefined && this.getAmountOfPlayersInGame(userID, slot, gameTypeID, true, weevilList, socketIdList) >= 2) {
                        if(this.poolData["ballCount"] == 12 && this.currentRoomId == 291) {
                            // add 2 points
                            if(userWinner == userID) {
                                this.server.addPoolLeaderboardPoints(this, 2);
                                this.server.removePoolLeaderBoardPoints(otherPlayerData, 1);
                            }
                            else {
                                this.server.addPoolLeaderboardPoints(otherPlayerData, 2);
                                this.server.removePoolLeaderBoardPoints(this, 1);
                            }
                            
                            this.roomEvent(this.currentRoomId, null, weevilList, socketIdList);
                        }
                        else if(this.poolData["ballCount"] == 6 && this.currentRoomId == 291) {
                            // add 1 point
                            if(userWinner == userID) {
                                this.server.addPoolLeaderboardPoints(this, 1);
                                this.server.removePoolLeaderBoardPoints(otherPlayerData, 1);
                            }
                            else {
                                this.server.addPoolLeaderboardPoints(otherPlayerData, 1);
                                this.server.removePoolLeaderBoardPoints(this, 1);
                            }
    
                            this.roomEvent(this.currentRoomId, null, weevilList, socketIdList);
                        }
    
                        this.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='userWinner' t='s'>" + userWinner + "</var><var n='userLoser' t='s'>" + userLoser + "</var><var n='winnerPoints' t='s'>35</var><var n='LoserPoints' t='s'>15</var><var n='command' t='s'>cpwg</var></dataObj>]]></body></msg>");
                        otherPlayerData.send("<msg t='xt'><body action='xtRes' r='-1'><![CDATA[<dataObj><var n='commandType' t='s'>tbmt</var><var n='userWinner' t='s'>" + userWinner + "</var><var n='userLoser' t='s'>" + userLoser + "</var><var n='winnerPoints' t='s'>35</var><var n='LoserPoints' t='s'>15</var><var n='command' t='s'>cpwg</var></dataObj>]]></body></msg>");
                    }
                    /*else {
                        this.modMsg("Nice exploit attempt loooool - BWR Team");
                        return;
                    }*/
    
                    if(this.poolData["turnTimer"] != null) {
                        clearInterval(this.poolData["turnTimer"]);
                    }
    
                    if(otherPlayerData.poolData["turnTimer"] != null) {
                        clearInterval(otherPlayerData.poolData["turnTimer"]);
                    }
    
                    this.clearGame(gameTypeID);
                    otherPlayerData.clearGame(gameTypeID);
                    return;
                default:
                    return;
            }
        }
        else {
            this.dropOnly("pre-login action (race) ignored");
        }
    }

    adminChatBan(adminName, adminPassword, weevilName, duration, weevilList = undefined, socketIdList = undefined) {
        var weevil = this;
        console.log(adminName + weevilName);
        
        db.query("SELECT isModerator FROM users WHERE username = ? AND modToken = ?", [adminName, adminPassword], function(err, result) {
            if(result.length > 0) {
                if(result[0].isModerator == "1") {
                    var unix = Math.floor(Date.now() / 1000);
                    var futureUnix = unix + parseInt(duration) * 60;
        
                    db.query("UPDATE users SET canSpeak = ? WHERE username = ?", [futureUnix.toString(), weevilName], function(err, result) {
                        if(err) {
                            console.log(err);
                        }
                        else if(result.affectedRows == 1) {
                            for(var id in socketIdList) {
                                if(weevilList[parseInt(id)].nickname == weevilName) {
                                    weevilList[parseInt(id)].canSpeak = false;
                                    weevilList[parseInt(id)].send("%xt%7#2%-1%" + (Math.floor((futureUnix - unix) / 60) - 1) + "%");
                                }
                            }
                        }
                        else {
                            console.log(result);
                        }
                    });
                }
                else {
                    // perm ban account for exploit attempt
                    db.query("UPDATE users SET active = ? WHERE username = ?", [0, this.nickname], function(err, result) {
                        if(err) {
                            console.log(err);
                        }
                        else if(result.affectedRows == 1) {
                            weevil.socket.end();
                            weevil.socket.destroy();
                        }
                    });
                }
            }
        });
    }

    adminBanUser(adminName, adminPassword, weevilName, duration, weevilList = undefined, socketIdList = undefined) {
        var weevil = this;
        console.log(adminName + weevilName);
        this.verifyMod(adminName, adminPassword, function(isMod) {
            if(isMod) {
                if(parseInt(duration) == 999) {
                    // perm ban
                    db.query("UPDATE users SET active = ? WHERE username = ?", [0, weevilName], function(err, result) {
                        if(err) { console.log(err); }
                        else if(result.affectedRows == 1) {
                            weevil.logModeration(adminName, weevilName, 1, "PERMANENT BAN");
                            for(var id in socketIdList) {
                                if(weevilList[parseInt(id)].nickname == weevilName) {
                                    weevilList[parseInt(id)].socket.end();
                                    weevilList[parseInt(id)].socket.destroy();
                                }
                            }
                        }
                    });
                }
                else {
                    var unix = Math.floor(Date.now() / 1000);
                    var futureUnix = unix + parseInt(duration) * 86400;
                    db.query("UPDATE users SET bannedUntil = ? WHERE username = ?", [futureUnix.toString(), weevilName], function(err, result) {
                        if(err) { console.log(err); }
                        else if(result.affectedRows == 1) {
                            weevil.logModeration(adminName, weevilName, 2, "temp ban " + duration + "d");
                            for(var id in socketIdList) {
                                if(weevilList[parseInt(id)].nickname == weevilName) {
                                    weevilList[parseInt(id)].socket.end();
                                    weevilList[parseInt(id)].socket.destroy();
                                }
                            }
                        }
                        else { console.log(result); }
                    });
                }
            }
            else {
                // perm ban account for exploit attempt
                db.query("UPDATE users SET active = ? WHERE username = ?", [0, adminName], function(err, result) {
                    if(err) { console.log(err); }
                    else if(result.affectedRows == 1) {
                        weevil.logModeration(adminName, adminName, 3, "non-mod ban attempt -> self-ban");
                        weevil.socket.end();
                        weevil.socket.destroy();
                    }
                });
            }
        });
    }

    adminKickUser(adminName, adminPassword, weevilName, weevilList = undefined, socketIdList = undefined) {
        var weevil = this;
        console.log(adminName + weevilName);
        this.verifyMod(adminName, adminPassword, function(isMod) {
            if(isMod) {
                weevil.logModeration(adminName, weevilName, 4, "kick");
                for(var id in socketIdList) {
                    if(weevilList[parseInt(id)].nickname == weevilName) {
                        weevilList[parseInt(id)].socket.end();
                        weevilList[parseInt(id)].socket.destroy();
                    }
                }
            }
            else {
                // perm ban account for exploit attempt
                db.query("UPDATE users SET active = ? WHERE username = ?", [0, adminName], function(err, result) {
                    if(err) { console.log(err); }
                    else if(result.affectedRows == 1) {
                        weevil.logModeration(adminName, adminName, 3, "non-mod kick attempt -> self-ban");
                        weevil.socket.end();
                        weevil.socket.destroy();
                    }
                });
            }
        });
    }

    adminWarnUser(adminName, adminPassword, weevilName, msg, weevilList = undefined, socketIdList = undefined) {
        var weevil = this;
        console.log(adminName + weevilName);
        this.verifyMod(adminName, adminPassword, function(isMod) {
            if(isMod) {
                weevil.logModeration(adminName, weevilName, 5, "warn: " + (msg || ""));
                for(var id in socketIdList) {
                    if(weevilList[parseInt(id)].nickname == weevilName) {
                        weevilList[parseInt(id)].modMsg(msg);
                    }
                }
            }
            else {
                // perm ban account for exploit attempt
                db.query("UPDATE users SET active = ? WHERE username = ?", [0, adminName], function(err, result) {
                    if(err) { console.log(err); }
                    else if(result.affectedRows == 1) {
                        weevil.logModeration(adminName, adminName, 3, "non-mod warn attempt -> self-ban");
                        weevil.socket.end();
                        weevil.socket.destroy();
                    }
                });
            }
        });
    }

    applyPetState(p, stateStr) {
        if (!stateStr || stateStr.indexOf("locID:") !== 0) return;
        var parts = stateStr.split(",");
        for (var i = 0; i < parts.length; i++) {
            var kv = parts[i].split(":");
            if (kv.length !== 2) continue;
            switch (kv[0]) {
                case "locID": p.locID = parseInt(kv[1]);  break;
                case "ps":    p.ps    = parseInt(kv[1]);  break;
                case "x":     p.x     = this.isInt(kv[1]) ? parseInt(kv[1]) : parseFloat(kv[1]); break;
                case "y":     p.y     = this.isInt(kv[1]) ? parseInt(kv[1]) : parseFloat(kv[1]); break;
                case "z":     p.z     = this.isInt(kv[1]) ? parseInt(kv[1]) : parseFloat(kv[1]); break;
                case "r":     p.r     = this.isInt(kv[1]) ? parseInt(kv[1]) : parseFloat(kv[1]); break;
            }
        }
        p.scale = this.server.locWithScales[p.locID] * 0.5;
    }

    toRawTarget(prefixed, pet, cb) {
        if (!prefixed || prefixed.indexOf("locID:") !== 0) return cb(prefixed);
        var parts = prefixed.split(",");
        for (var i = 0; i < parts.length; i++) {
            var kv = parts[i].split(":");
            if (kv.length !== 2) continue;
            if (kv[0] === "x") pet.x = this.isInt(kv[1]) ? parseInt(kv[1]) : parseFloat(kv[1]);
            else if (kv[0] === "z") pet.z = this.isInt(kv[1]) ? parseInt(kv[1]) : parseFloat(kv[1]);
            else if (kv[0] === "r") pet.r = this.isInt(kv[1]) ? parseInt(kv[1]) : parseFloat(kv[1]);
        }
        if (isNaN(pet.x) || isNaN(pet.z) || isNaN(pet.r)) return cb(prefixed);

        var self = this;
        function finish(speed) {
            if (isNaN(speed) || speed <= 0) speed = 4 * 0.14 * 0.5;
            cb(pet.x + "," + pet.z + "," + pet.r + "," + speed + ",0");
        }
        function clampPerf(perf, energy) {
            if (energy > 75) return Math.max(perf, 0.5);
            if (energy > 50) return perf;
            return Math.min(perf, 0.5);
        }

        if (pet && !isNaN(pet.fitness) && !isNaN(pet.mentalEnergy) && !isNaN(pet.scale)) {
            var perf = Math.sqrt(pet.fitness * pet.fitness + pet.mentalEnergy * pet.mentalEnergy) * 0.01;
            finish(4 * pet.scale * clampPerf(perf, pet.mentalEnergy));
        } else {
            var owner = (pet && pet.owner) ? pet.owner : self.nickname;
            db.query("SELECT fitness, mentalEnergy FROM pets WHERE id = ? AND ownerID = ?",
                [pet ? pet.id : null, owner],
                function(err, result) {
                    if (err || !result || result.length === 0) return finish(NaN);
                    var fit = parseFloat(result[0].fitness);
                    var men = parseFloat(result[0].mentalEnergy);
                    var perf = Math.sqrt(fit * fit + men * men) * 0.01;
                    var scale = (pet && !isNaN(pet.scale)) ? pet.scale : 0.14;
                    finish(4 * scale * clampPerf(perf, men));
                });
        }
    }

    sendPetAction(roomId, petId, actionId, petState, inNest, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            petId    = parseInt(petId);
            actionId = parseInt(actionId);
            inNest   = parseInt(inNest);

            var pet = this.myPet[petId];
            if(!pet) return;

            if(pet.ps == 28 && ![-28, 6, 7, 15, 20, 21, 22, 23, 24].includes(actionId)) return;

            if (actionId == 28) {
                pet.ps = 28;
                pet.ridingOwner = true;
            }
            else if (actionId == -28) {
                pet.ps = 0;
                pet.ridingOwner = false;
                this.applyPetState(pet, petState);
            }
            else if (actionId == 13) {
                pet.ps = 13;
            }
            var self = this;
            function emitPetAction(argStr) {
                var packet = "%xt%6#4%-1%" + petId + "%" + actionId + "%" + argStr + "%" + inNest + "%" + "-1%";
                for(var id in socketIdList) {
                    if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId) && weevilList[parseInt(id)].socketID != self.socketID) {
                        weevilList[parseInt(id)].send(packet);
                    }
                }
            }

            if ([1, 2, 3, 16, 30].includes(actionId)) {
                this.toRawTarget(petState, pet, function(raw) {
                    emitPetAction(raw);
                });
            } else {
                emitPetAction(petState);
            }
        }
        else {
            this.socket.end();
            this.socket.destroy();
        }
    }

    sendPetExpression(roomId, petId, expressionId, inNest, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            if(parseInt(expressionId) < 0 || parseInt(expressionId) > 7 || !this.myPet[parseInt(petId)]) return;

            var packet = "%xt%6#3%-1%" + petId + "%" + expressionId + "%" + inNest + "%";

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId) && weevilList[parseInt(id)].socketID != this.socketID) {
                    weevilList[parseInt(id)].send(packet);
                }
            }
        }
        else {
            this.socket.end();
            this.socket.destroy();
        }
    }

    sendPetGotBall(roomId, petId, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            if(!this.myPet[parseInt(petId)]) return;

            var packet = "%xt%6#5%-1%" + petId + "%";

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId) && weevilList[parseInt(id)].socketID != this.socketID) {
                    weevilList[parseInt(id)].send(packet);
                }
            }
        }
        else {
            this.socket.end();
            this.socket.destroy();
        }
    }

    sendPetHome(roomId, petId, petState, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            var pet = this.myPet[parseInt(petId)];
            if(!pet) return;

            this.applyPetState(pet, petState);

            var packet = "%xt%6#6%-1%" + petId + "%" + petState + "%";

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId) && weevilList[parseInt(id)].socketID != this.socketID) {
                    weevilList[parseInt(id)].send(packet);
                }
            }
        }
        else {
            this.socket.end();
            this.socket.destroy();
        }
    }

    sendPetNestDoor(roomId, petId, doorId, inNest, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            if(!this.myPet[parseInt(petId)]) return;

            var packet = "%xt%6#2%-1%"+ petId +"%"+ doorId +"%"+ inNest +"%";

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId) && weevilList[parseInt(id)].socketID != this.socketID) {
                    weevilList[parseInt(id)].send(packet);
                }
            }
        }
        else {
            this.socket.end();
            this.socket.destroy();
        }
    }

    sendPetJoinNestLoc(roomId, petId, locId, doorId, x, y, z, r, inNest, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            var pet = this.myPet[parseInt(petId)];
            if(!pet) return;

            pet.locID = parseInt(locId);
            pet.x = this.isInt(x) ? parseInt(x) : parseFloat(x);
            pet.y = this.isInt(y) ? parseInt(y) : parseFloat(y);
            pet.z = this.isInt(z) ? parseInt(z) : parseFloat(z);
            pet.r = this.isInt(r) ? parseInt(r) : parseFloat(r);
            pet.scale = this.server.locWithScales[parseInt(locId)] * 0.5;

            var packet = "%xt%6#1%-1%"+ petId +"%"+ locId +"%"+ doorId +"%"+ x +"%"+ y +"%"+ z +"%"+ r +"%"+ inNest +"%";

            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId) && weevilList[parseInt(id)].socketID != this.socketID) {
                    weevilList[parseInt(id)].send(packet);
                }
            }
        }
        else {
            this.socket.end();
            this.socket.destroy();
        }
    }

    sendPetCommand(roomId, petName, nameHash, commandId, weevilList = undefined, socketIdList = undefined) {
        if(this.loggedIn) {
            if(parseInt(commandId) < 0 || parseInt(commandId) > 21) return;

            var pet = this.getPet();

            if(!pet || pet.name != petName || pet.nameHash != nameHash) {
                console.log("[sendPetCommand] Pet not found or hash mismatch:", petName, nameHash);
                return;
            }

            var packet = "%xt%6#7%" + this.nickname + "%" + this.userID + "%" + petName + "%" + commandId + "%";
            
            for(var id in socketIdList) {
                if(weevilList[parseInt(id)].currentRoomId == parseInt(roomId)) {
                    weevilList[parseInt(id)].send(packet);
                }
            }
        }
        else {
            this.socket.end();
            this.socket.destroy();
        }
    }
}

module.exports = Weevil;