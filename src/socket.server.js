const md5 = require('md5');

let redisKey =  md5(Math.floor(Date.now() / 1000) + "-"+Math.random().toString(36).slice(2));
let PORT = 2636;
let log = (process.env.NODE_ENV === 'development');

let io = require('socket.io')(PORT, { pingTimeout: 7000, pingInterval: 10000 });

// let redis = require('redis');
let Redis = require('ioredis');
var redisClient = new Redis();

// let redisClient = redis.createClient();
redisClient.set("redis-key", redisKey);

redisClient.on('error', (err) => console.log(`Error: ${err}`));

redisClient.on('pmessage', function(pattern, channel, message) {
    try {
        io.to(redisKey).json.send({event: 'queue', channel: channel, message: message});
        // console.info(channel, message);
    } catch (err) { console.log(`error in ${channel}: \n err: ${err} message: ${message}`); }
    return true;
});

redisClient.on('ready', function() {
    redisClient.psubscribe('*');
});

let mon = new Redis();
mon.monitor(function (err, monitor) {
    monitor.on('monitor', function (time, args, source, database) {
        io.to(redisKey).json.send({event: 'monitor', data: {time: time, args: args, source:source, database: database}});
        // console.log(time, args, source, database);
    });
});

io.sockets.on('connection', function (socket) {
    console.info('client connected');
    let time = (new Date).toLocaleTimeString();
    socket.emit("connected", time);

    socket.on('registe', function(data) {
        let params = data;
        try {
            params = (typeof params === 'string') ? JSON.parse(params) : params;
        } catch (err) { return console.log(`error: \n${err}`); }

        if (!params.hasOwnProperty('redisKey') ) return socket.disconnect();
        if(params.redisKey !== redisKey) return socket.disconnect();
        socket.join(redisKey);

        socket.on('disconnect', () => (log ? console.info('client disconnect', socket.apikey) : ''));

        if (log) console.info('client registe', socket.apikey);
        socket.emit('reget');
        return true;
    });
});

// let client = new Redis();
//
// function scan(cursor, resultSet, scanCallback) {
//     client.scan(cursor, 'MATCH', '*', 'COUNT', '100', function(error, result) {
//         cursor = result[0];
//         let keys = result[1];
//         keys.forEach(function(key, i) {
//             let split_keys = key.split(":");
//             let link = '';
//             let d = resultSet;
//             for(let split_key of split_keys) {
//                 link += ((link === '') ? '' :  ':') + split_key;
//                 if(!(split_key in d)) d[split_key] = {name: split_key, link: link, children: {}};
//                 d = d[split_key].children;
//             }
//         })
//         if (cursor == 0) scanCallback(resultSet);
//         else return scan(cursor, resultSet, scanCallback);
//     });
// }
//
// scan(0, {}, function(resultSet) {
//     console.log(JSON.stringify(resultSet));
//
// });