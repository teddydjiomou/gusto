const WebSocket = require('ws');
const http = require('http');

const server = http.createServer();
const wss = new WebSocket.Server({ server });

const etablissement = {};

wss.on('connection', (ws) => {
    console.log('✅ Client connected');

    ws.id_etablissement = null;

    ws.on('message', (message) => {
        let data;

        try {
            data = JSON.parse(message.toString());
        } catch (e) {
            console.log('❌ Invalid JSON');
            return;
        }

        const { type } = data;

        // ==========================
        // 🏷️ REGISTER
        // ==========================
        if (type === 'register') {
            const id = data.id_etablissement;

            if (!id) {
                console.log('⚠️ Missing id_etablissement');
                return;
            }

            ws.id_etablissement = id;

            if (!etablissement[id]) {
                etablissement[id] = new Set();
            }

            etablissement[id].add(ws);

            console.log(`🏷️ Registered: ${id}`);
            return;
        }

        // ❌ BLOQUER si pas register
        if (!ws.id_etablissement) {
            console.log('⚠️ Not registered');
            return;
        }

        const broadcast = (payload) => {
            const clients = etablissement[ws.id_etablissement];
            if (!clients) return;

            const msg = JSON.stringify(payload);

            clients.forEach((client) => {
                if (client.readyState === WebSocket.OPEN) {
                    client.send(msg);
                }
            });
        };

        // ==========================
        // EVENTS
        // ==========================
        if (type === 'table_opened') {
            broadcast(data);
            console.log(`🟢 Table opened`);
            return;
        }

        if (type === 'new_command') {
            broadcast(data);
            console.log(`📤 Command`);
            return;
        }

        if (type === 'command_status_changed') {
            broadcast(data);
            console.log(`🔄 Status changed`);
            return;
        }

        if (type === 'table_completed') {
            broadcast(data);
            console.log(`🧾 Table completed`);
            return;
        }
    });

    ws.on('close', () => {
        const id = ws.id_etablissement;

        if (id && etablissement[id]) {
            etablissement[id].delete(ws);

            if (etablissement[id].size === 0) {
                delete etablissement[id];
            }
        }

        console.log('⚠️ Client disconnected');
    });
});

const PORT = process.env.PORT || 8080;

server.listen(PORT, () => {
    console.log(`🚀 WebSocket running on ${PORT}`);
});