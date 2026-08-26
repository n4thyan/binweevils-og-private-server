const express = require('express');
const { Router } = require('express');
const fs = require('fs');
const app = express()
app.use(express.static('static'));
app.use(express.urlencoded());
app.use(require("morgan")("dev"));


app.use(express.json());
app.get('/', function (req, res) {
  res.send('Hello World')
});
app.all("/v1/logins/:somekey/profiles", function(req, res) {
  console.log(req.body);
  res.json([{id: "AAAA"}]);
});
app.all("/connect/token", function(req, res) {
  console.log(req.body);
  if(req.body.grant_type == 'password' || req.body.grant_type == 'refresh_token') {
    res.json({expires_in: 3600000, refresh_token: 'bX-MiUUL1-My8qJMSrxXUR2SAydzrykB4vK3vGPbtCM', access_token: "eyJhbGciOiJSUzI1NiIsInR5cCI6ImF0K2p3dCJ9.eyJuYmYiOjE2MDAxOTAzMDQsImV4cCI6MTYwMDE5MzkwNCwiaXNzIjoiaHR0cHM6Ly91cy5tc3BhcGlzLmNvbS9sb2dpbmlkZW50aXR5IiwiYXVkIjoibmVidWxhIiwiY2xpZW50X2lkIjoidW5pdHkuY2xpZW50Iiwic3ViIjoiZjg2ZGYzOWU0NDAzNDlhZjhkMTI4YTg3MDI0ZmE3ZjkiLCJhdXRoX3RpbWUiOjE2MDAxOTAzMDMsImlkcCI6ImxvY2FsIiwibG9naW5JZCI6ImY4NmRmMzllNDQwMzQ5YWY4ZDEyOGE4NzAyNGZhN2Y5IiwibmFtZSI6IlVTfGphZCB0ZXN0IiwibG9naW5OYW1lIjoiVVN8amFkIHRlc3QiLCJpc0d1ZXN0IjpmYWxzZSwiZ2FtZUlkIjoiNW9vaSIsInByb2ZpbGVJZCI6IjYzNjc1YzBlNTI4ZTQ1M2FiODQwYmJhZGVmMjI4MWM1Iiwic2NvcGUiOlsib3BlbmlkIiwibmVidWxhIiwib2ZmbGluZV9hY2Nlc3MiXSwiYW1yIjpbInB3ZCJdfQ.PE6yM05lWvoXQL0MnQX-t15iCeldsezyl-0cYicS6U4Uh_diGl84WgSXVwC_EubO1DLbktA5DYsJG38u6u1O7eSjQe07HGTqBTqf6305g1L9ScUOasSjc-9kAZs_xPnHaJVT5ARmmcG2D0dd-6XiZS7TdBXZ-EsCOimJuVkAtQz2FqxCSlDB88VLby7j-Lw2AtjPhceQfG7n3zw1ULwpX_epmEEOZ9UERS8cIu1cF55GFhlJOuVroDnEYP8ed87QDLInM4CqBSlVq8iGHEinK3hWcVs7MSuFJVQIhptwhWP8XC8TFBwZnOv2tGtN2H51MxIcSXECQuWkxqEX-Jk0Zw"})
  }
});
app.all("/getServer", function(req, res) {
  res.send('127-0-0-1:10843');
});

app.all("/getServerEx", function(req, res) { //chatroom
  res.send('127-0-0-1:10842');
});

app.listen(1122)
