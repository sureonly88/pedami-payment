var server = require('http').Server();

var io = require('socket.io')(server);

var Redis = require('ioredis');
var redis = new Redis();

redis.subscribe('topup-channel');
redis.subscribe('login-channel');
redis.subscribe('logout-channel');
redis.subscribe('topup-verifikasi-channel');

redis.on('message', function(channel, message){
	console.log(channel, message);

	message = JSON.parse(message);
	
	//console.log(channel + ":" + message.event);

	io.emit(channel + ":" + message.event, message.data);

});

server.listen(3000);