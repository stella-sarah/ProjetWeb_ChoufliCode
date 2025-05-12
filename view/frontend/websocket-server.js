const WebSocket = require('ws');
const http = require('http');
const server = http.createServer();
const wss = new WebSocket.Server({ server });

const clients = new Map(); // userId -> WebSocket

wss.on('connection', (ws) => {
    console.log('New client connected');
    
    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);
            
            if (data.type === 'register') {
                // Enregistrer l'utilisateur
                clients.set(data.userId, ws);
                console.log(`User ${data.userId} registered`);
            }
            else if (data.type === 'new_message') {
                // Transférer le message au destinataire
                const recipientWs = clients.get(data.recipientId);
                if (recipientWs) {
                    recipientWs.send(JSON.stringify({
                        type: 'new_message',
                        conversationId: data.conversationId,
                        senderId: data.senderId,
                        content: data.content,
                        sent_at: data.sent_at
                    }));
                }
            }
            else if (data.type === 'typing') {
                // Notifier que l'utilisateur est en train de taper
                const recipientWs = clients.get(data.recipientId);
                if (recipientWs) {
                    recipientWs.send(JSON.stringify({
                        type: 'typing',
                        conversationId: data.conversationId,
                        senderId: data.senderId
                    }));
                }
            }
            else if (data.type === 'mark_read') {
                // Marquer les messages comme lus
                data.messageIds.forEach(messageId => {
                    // Ici vous devriez mettre à jour la base de données
                    // puis notifier l'expéditeur que ses messages ont été lus
                });
            }
        } catch (err) {
            console.error('Error processing message:', err);
        }
    });
    
    ws.on('close', () => {
        // Supprimer le client déconnecté
        for (let [userId, clientWs] of clients.entries()) {
            if (clientWs === ws) {
                clients.delete(userId);
                console.log(`User ${userId} disconnected`);
                break;
            }
        }
    });
});

server.listen(8080, () => {
    console.log('WebSocket server running on port 8080');
});