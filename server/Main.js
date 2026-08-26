var BinWeevils = require("./BinWeevils");
var BinWeevilsWeb = require("./BinWeevilsWeb");
var s = new BinWeevils("", 9339);
var x = new BinWeevilsWeb("", 2087);
s.runServer();
x.runServer();