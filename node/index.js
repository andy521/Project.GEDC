const net = require('net');
const Processor = require('./libs/processor');

const server = net.createServer((socket) => {
    socket.setEncoding('utf-8');
    socket.on('data', (request) => {
        const processor = new Processor();
        request = request.trim();
        if (request) {
            console.log(new Date() + ' client => server ' + request);
            processor.handle(request).then(response => response, error => {
                let message = error.message;
                if (message) {
                    message = message[0].toLowerCase() + message.slice(1);
                }
                return Promise.resolve('error: ' + message);
            }).then(response => {
                socket.write(response + '\r\n');
                console.log(new Date() + ' server => client ' + response);
                // TODO: Client close connection
                // socket.end();
            });
        } else {
            socket.end();
        }
    });
    socket.on('error', (error) => {
        if (error.errno === 'ECONNRESET') {
            return;
        }
        try { socket.end(); } catch(ignored) {}
        console.error('unexpected socket error: ' + JSON.stringify(error));
    });
});

server.listen(10000);
