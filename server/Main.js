var BinWeevils = require("./BinWeevils");
var BinWeevilsWeb = require("./BinWeevilsWeb");
var fs = require("fs");
var path = require("path");

var s = new BinWeevils("", 9339);
var x = new BinWeevilsWeb("", 2087);

s.runServer();
x.runServer();

// Website status bridge. Keep this deliberately one-way and data-minimal:
// the game process writes only freshness + authenticated player count, while
// PHP reads the file. No game/admin action is exposed over HTTP.
// PHP reads from Apache's actual served tree during local XAMPP testing. Keep an
// environment override so deployments can choose a different DocumentRoot.
var defaultStatusPath = process.platform === "win32"
    ? "C:/xampp/htdocs/site/runtime-status.json"
    : path.resolve(__dirname, "../game-full/site/runtime-status.json");
var statusPath = path.resolve(process.env.BW_WEBSITE_STATUS_PATH || defaultStatusPath);
var statusTmpPath = statusPath + ".tmp";

function writeWebsiteStatus() {
    try {
        var players = Object.keys(s.weevils || {}).reduce(function(total, key) {
            var weevil = s.weevils[key];
            return total + (weevil && weevil.loggedIn === true && weevil.destroyed !== true ? 1 : 0);
        }, 0);

        var payload = JSON.stringify({
            online: true,
            players: players,
            generatedAt: Date.now()
        });

        fs.writeFileSync(statusTmpPath, payload, "utf8");
        try {
            fs.renameSync(statusTmpPath, statusPath);
        }
        catch(renameError) {
            // Windows cannot always replace an existing destination atomically.
            try { fs.unlinkSync(statusPath); } catch(unlinkError) {}
            fs.renameSync(statusTmpPath, statusPath);
        }
    }
    catch(error) {
        console.log("website status write failed: " + (error && error.message ? error.message : error));
    }
}

writeWebsiteStatus();
setInterval(writeWebsiteStatus, 5000);
