'use strict'

const WebSocket = require('ws');
var wss;
var Weevil = require("./Weevil");
var cookie = require('cookie');
const https = require('https');
const fs = require('fs');
var express = require('express');
var app = express();

class BinWeevilsWeb {

    constructor(addr, serverPort) {
        this.address = addr;
        this.port = parseInt(serverPort);
        //this.privateKey  = fs.readFileSync('../../etc/ssl/private/key.pem', 'utf8');
        //this.certificate = fs.readFileSync('../../etc/ssl/certs/cert.pem', 'utf8');
        //this.credentials = {key: this.privateKey, cert: this.certificate};
        //this.httpsServer = https.createServer(this.credentials, app);
	this.httpsServer = https.createServer(app);
        wss = new WebSocket.Server({server:this.httpsServer});
        this.weevils = {};
        this.socketIdList = {};
        this.sockets = 0;
    }

    addWeevil(weevil) {
        this.removeWeevil(weevil);
        this.weevils[weevil.socketID] = weevil;
        this.socketIdList[weevil.socketID] = weevil.socketID;
    }
    
    removeWeevil(weevil) {
        if (this.weevils[weevil.socketID]) {
            weevil = this.weevils[weevil.socketID];
            delete this.weevils[weevil.socketID];
            delete this.socketIdList[weevil.socketID];
        
            weevil.socket.close();
            weevil.destroyed = true;
        }
    }

    sendBuddyOnlineReport(weevil) {
        for(var budIndex in weevil.buddyList) {
            for(var id in this.socketIdList) {
                if(this.weevils[parseInt(id)].nickname == weevil.buddyList[parseInt(budIndex)]["username"]) {
                    this.weevils[parseInt(id)].socket.send(JSON.stringify({"cn":"notify.login", "idx":weevil.idx.toString(), "username":weevil.nickname, "weevilDef":weevil.def, "ws":"1"}));
                }
            }
        }
    }

    sendBuddyOfflineReport(weevil) {
        for(var budIndex in weevil.buddyList) {
            for(var id in this.socketIdList) {
                if(this.weevils[parseInt(id)].nickname == weevil.buddyList[parseInt(budIndex)]["username"]) {
                    this.weevils[parseInt(id)].socket.send(JSON.stringify({"cn":"notify.logout", "idx":weevil.idx.toString(), "username":weevil.nickname, "weevilDef":weevil.def, "ws":"0"}));
                }
            }
        }
    }

    sendBuddyRequestReport(weevil, buddyIdx) {
        for(var id in this.socketIdList) {
            if(this.weevils[parseInt(id)].idx == parseInt(buddyIdx)) {
                this.weevils[parseInt(id)].socket.send(JSON.stringify({"cn":"notify.new-friendship-request", "idx":weevil.idx.toString(), "username":weevil.nickname, "weevil_def":weevil.def, "sent_on":"0"}));
            }
        }
    }

    sendBuddyAcceptedReport(weevil, buddyIdx) {
        for(var id in this.socketIdList) {
            if(this.weevils[parseInt(id)].idx == parseInt(buddyIdx)) {
                this.weevils[parseInt(id)].socket.send(JSON.stringify({"cn":"notify.handle-friendship-request", "idx":weevil.idx.toString(), "username":weevil.nickname, "weevil_def":weevil.def, "status":"1"}));
            }
        }
    }

    sendBuddyDeniedReport(weevil, buddyIdx) {
        for(var id in this.socketIdList) {
            if(this.weevils[parseInt(id)].idx == parseInt(buddyIdx)) {
                this.weevils[parseInt(id)].socket.send(JSON.stringify({"cn":"notify.handle-friendship-request", "idx":weevil.idx.toString(), "username":weevil.nickname, "weevil_def":weevil.def, "status":"2"}));
            }
        }
    }

    sendBuddyDeletedReport(weevil, buddyIdx) {
        for(var id in this.socketIdList) {
            if(this.weevils[parseInt(id)].idx == parseInt(buddyIdx)) {
                this.weevils[parseInt(id)].socket.send(JSON.stringify({"cn":"notify.delete-friendship", "idx":weevil.idx.toString(), "username":weevil.nickname}));
            }
        }
    }

    async handleData(dataStr, weevil) {
        try {
            switch(dataStr.split('{')[0]) {
                case "friends/get-list":
                    await weevil.getBuddyList(this.weevils, this.socketIdList);
                    var buddyRequests = await weevil.getBuddyRequests();
                    weevil.socket.send(JSON.stringify({"invites":(buddyRequests == null ? [] : buddyRequests), "buddies":weevil.buddyList, "httpCode":200, "responseCode":1, "cn":"friends.get-list"}));
                    return;
                case "weevil/get-notifications":
                    var buddyRequests = await weevil.getBuddyRequests();
                    weevil.socket.send(JSON.stringify({"buddy-requests":(buddyRequests == null ? 0 : buddyRequests.length), "nest-news":0, "conversations":0, "httpCode":200, "responseCode":1, "cn":"weevil.get-notifications"}));
                    return;
                case "friends/get-weevil":
                    if(JSON.parse(dataStr.split('friends/get-weevil')[1])["username"].toLowerCase() != weevil.nickname.toLowerCase()) {
                        var weevilData = await weevil.searchWeevil(JSON.parse(dataStr.split('friends/get-weevil')[1])["username"]);
                        if(weevilData == null) {
                            weevil.socket.send(JSON.stringify({"httpCode":200, "responseCode":2, "cn":"friends.get-weevil"}));
                        }
                        else if(JSON.parse(weevilData)["sp"] == 1 && weevil.isModerator != "1") {
                            weevil.socket.send(JSON.stringify({"httpCode":200, "responseCode":2, "cn":"friends.get-weevil"}));
                        }
                        else {
                            weevil.socket.send(JSON.stringify({"weevil":JSON.parse(weevilData), "httpCode":200, "responseCode":1, "cn":"friends.get-weevil"}));
                        }
                    }
                    else {
                        weevil.socket.send(JSON.stringify({"httpCode":200, "responseCode":2, "cn":"friends.get-weevil"}));
                    }
                    return;
                case "nest-news":
                    weevil.socket.send(JSON.stringify({"nest_news":[{"image":"weevilWorld_09_06_17_App-min.png", "app_link":"https:\/\/discord.gg/bWRHynAEZ9", "browser_link":"https:\/\/discord.gg/bWRHynAEZ9", "new":1, "news_date":"2021-02-11 15:16"}], "httpCode":200, "responseCode":1, "cn":"nest-news"}));
                    return;
                case "friends/send-request":
                    var sentRequest = await weevil.sendBuddyRequest(JSON.parse(dataStr.split('friends/send-request')[1])["buddy_idx"]);
                    if(sentRequest) {
                        weevil.socket.send(JSON.stringify({"httpCode":200, "responseCode":1, "cn":"friends.send-request"}));
                        this.sendBuddyRequestReport(weevil, JSON.parse(dataStr.split('friends/send-request')[1])["buddy_idx"]);
                    }
                    else {
                        weevil.socket.send(JSON.stringify({"httpCode":200, "responseCode":2, "cn":"friends.send-request"}));
                    }
                    return;
                case "friends/handle-request":
                    if(JSON.parse(dataStr.split('friends/handle-request')[1])["status"] == 1) {
                        // accept
                        var acceptRequest = await weevil.acceptBuddyRequest(JSON.parse(dataStr.split('friends/handle-request')[1])["buddy_idx"]);
                        if(acceptRequest) {
                            weevil.socket.send(JSON.stringify({"httpCode":200, "responseCode":1, "cn":"friends.handle-request"}));
                            this.sendBuddyAcceptedReport(weevil, JSON.parse(dataStr.split('friends/handle-request')[1])["buddy_idx"]);
                        }
                        else {
                            weevil.socket.send(JSON.stringify({"httpCode":200, "responseCode":2, "cn":"friends.handle-request"}));
                        }
                    }
                    else if(JSON.parse(dataStr.split('friends/handle-request')[1])["status"] == 2) {
                        // deny
                        var denyRequest = await weevil.denyBuddyRequest(JSON.parse(dataStr.split('friends/handle-request')[1])["buddy_idx"]);
                        if(denyRequest) {
                            weevil.socket.send(JSON.stringify({"httpCode":200, "responseCode":1, "cn":"friends.handle-request"}));
                            this.sendBuddyDeniedReport(weevil, JSON.parse(dataStr.split('friends/handle-request')[1])["buddy_idx"]);
                        }
                        else {
                            weevil.socket.send(JSON.stringify({"httpCode":200, "responseCode":2, "cn":"friends.handle-request"}));
                        }
                    }
                    else {
                        weevil.socket.send(JSON.stringify({"httpCode":200, "responseCode":2, "cn":"friends.handle-request"}));
                    }
                    return;
                case "friends/delete":
                    var deletedBuddy = await weevil.deleteBuddy(JSON.parse(dataStr.split('friends/delete')[1])["buddy_idx"]);
                    if(deletedBuddy) {
                        weevil.socket.send(JSON.stringify({"httpCode":200, "responseCode":1, "cn":"friends.delete"}));
                        this.sendBuddyDeletedReport(weevil, JSON.parse(dataStr.split('friends/delete')[1])["buddy_idx"]);
                    }
                    else {
                        weevil.socket.send(JSON.stringify({"httpCode":200, "responseCode":2, "cn":"friends.delete"}));
                    }
                    return;
                case "ping/pong":
                    if(weevil.socket != undefined) {
                        weevil.socket.send(JSON.stringify({"ping":1}));
                    }
                    return;
                default:
                    return;
            }
        }
        catch(Exception) {
            console.log(Exception);
            if(!weevil) return;
            if(!weevil.destroyed) {
                this.removeWeevil(weevil);
            }
        }
    }

    sendInitialResponse(ws) {
        ws.send(JSON.stringify({"httpCode":200, "responseCode":1, "cn":"login.notify-websocket-connection"}));
    }

    async runServer() {
        this.httpsServer.listen(this.port);

        wss.on('connection', async (ws, req) => {
            if(req.headers.hasOwnProperty('cookie')) {
                var cookies = cookie.parse(req.headers["cookie"]);

                if(cookies.hasOwnProperty('weevil_name') && cookies.hasOwnProperty('sessionId')) {
                    var client = new Weevil(ws);
                    var socketID = this.sockets++;
                    client.server = this;
                    client.socket = ws;
                    client.socketID = socketID;

                    var verified = await client.verifyUserWeb(cookies["weevil_name"].replace('+', ' '), decodeURI(cookies["sessionId"]));
                    
                    if(verified) {
                        this.addWeevil(client);
                        console.log("CONNECTION ESTABLISHED");
                        await client.getBuddyList(this.weevils, this.socketIdList);
                        this.sendInitialResponse(ws);
                        this.sendBuddyOnlineReport(client);
                    }
                    else {
                        //console.log("[CONNECTION]: Attemption with invalid user data");
                        ws.close();
                    }

                    ws.on('message', message => {
                        if(message != "ping/pong{}") console.log("[RECEIVED]: " + message);
                        this.handleData(message, this.weevils[socketID]);
                    });

                    ws.on('close', () => {
                        if(!this.weevils[socketID]) return;
                        if(!this.weevils[socketID].destroyed) {
                            this.sendBuddyOfflineReport(this.weevils[socketID]);
                            this.removeWeevil(this.weevils[socketID]);
                        }
                    });

                    ws.on('timeout', () => {
                        if(!this.weevils[socketID]) return;
                        if(!this.weevils[socketID].destroyed) {
                            this.sendBuddyOfflineReport(this.weevils[socketID]);
                            this.removeWeevil(this.weevils[socketID]);
                        }
                    });
                }
                else {
                    //console.log("[CONNECTION]: Attemption with invalid cookies");
                    ws.close();
                }
            }
            else {
                //console.log("[CONNECTION]: Attemption without cookies");
                ws.close();
            }
        });
    }

}

module.exports = BinWeevilsWeb;