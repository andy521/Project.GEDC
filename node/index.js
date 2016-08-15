const net = require('net');
const dateFormat = require('dateformat');
const Processor = require('./libs/processor');

const server = net.createServer((socket) => {
    socket.setEncoding('utf-8');
    socket.on('data', (request) => {
        const processor = new Processor();
        request = request.trim();
        if (request) {
            const timeReceived = dateFormat(new Date(), 'yyyy-mm-dd HH:MM:ss');
            console.log(`[${ timeReceived }] ${ socket.remoteAddress } client => server ${ request }`);
            processor.handle(request)
            .then(response => response, error => Promise.resolve('error: ' + error.message))
            .then(response => {
                socket.write(response + '\r\n');
                const timeSent = dateFormat(new Date(), 'yyyy-mm-dd HH:MM:ss');
                console.log(`[${ timeSent }] ${ socket.remoteAddress } server => client ${ response }`);
            });
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
