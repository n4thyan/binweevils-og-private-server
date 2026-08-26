var mysql = require('mysql');

var pool = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'bwps',
    port: 3306
});

module.exports = {
    query: function() {
        var sql_args = [];
        var args = [];
        for(var i = 0; i < arguments.length; i++) {
            args.push(arguments[i]);
        }

        var callback = args[args.length - 1];
        pool.getConnection(function(err, connection) {
            if(err) {
                console.log(err);
                return callback(err);
            }

            if(args.length > 2) {
                sql_args = args[1];
            }

            connection.query(args[0], sql_args, function(err, results) {
                connection.release();

                if(err) {
                    console.log(err);
                    return callback(err);
                }

                callback(null, results);
            });
        });
    }
};