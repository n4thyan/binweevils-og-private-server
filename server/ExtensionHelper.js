var fs = require('fs');
var xml = require("xml2js");

class ExtensionHelper {

    constructor() {};

    getRoom(roomId, callback) {
        fs.readFile('roomids.txt', 'utf8', function(err, data) {
            if(err) throw err;
        
            xml.parseString(data, function(error, result) {
                if(error) throw error;
        
                result.msg.body[0].rmList[0].rm.forEach(s => {
                    if(s.$.id == parseInt(roomId)) {
                        return callback({ id: s.$.id, priv: s.$.priv, temp: s.$.temp, game: s.$.game, ucnt: s.$.ucnt, lmb: s.$.lmb, maxu: s.$.maxu, maxs: s.$.maxs, name: s.n[0] });
                    }
                });
            });
        });
    }

}

module.exports = ExtensionHelper;