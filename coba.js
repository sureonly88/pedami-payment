var app = require('express')();
var server = require('http').Server(app);
var io = require('socket.io')(server);

server.listen(3000);

app.get('/', function(request, response) {
response.send('Hello World');
});

io.on('connection', function(){
	console.log('A connection was made.');
})
.on('connect_error', function(){
	console.log('Conn error');
})
.on('disconnect', function(){
	console.log('Disconnected'); 
});