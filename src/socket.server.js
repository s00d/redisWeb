const md5 = require('md5');

let redisKey =  md5(Math.floor(Date.now() / 1000) + "-"+Math.random().toString(36).slice(2));
let PORT = 2635;
let log = (process.env.NODE_ENV === 'development');

let io = require('socket.io')(PORT, { pingTimeout: 7000, pingInterval: 10000 });

let redis = require('redis');
let redisClient = redis.createClient();
redisClient.set("redis-key", redisKey);

redisClient.on('error', (err) => console.log(`Error: ${err}`));

redisClient.on('pmessage', function(pattern, channel, message) {
    try {
        io.to(redisKey).json.send({'event': 'queue', 'channel': channel, 'message': message});
        console.info(channel, message);
    } catch (err) { console.log(`error in ${channel}: \n err: ${err} message: ${message}`); }
    return true;
});

redisClient.on('ready', function() {
    redisClient.psubscribe('*');
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