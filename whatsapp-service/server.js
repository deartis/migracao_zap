const express = require('express');
const { Client } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');

const app = express();
app.use(express.json());

const client = new Client({
    puppeteer : { headless : true },
    session: null // Persiste a sessão se necessário
});

//Gera o QRCode no terminal (opcional: envia para o Laravel via API)
client.on('qr', qr => {
    qrcode.generate(qr, { small: true});
    console.log('Envia este QRCode para o frontend ou Laravel');
});

//Quando estiver conectado
client.on('ready', () => {
    console.log('Cliente conectado!');
});

// Rota para enviar mensagens (Chamada pelo Laravel)
app.post('/send-message', async (req, res) => {
    const {number, message, media } = req.body;
    try{
        if(media){
            //Baixa a mídia do storage do Laravel
            const mediaResponse = await axios.get(media, { responseType: 'stream' });
            const mediaPath = `/tmp/${path.basename(media)}`;
            await pipeline(mediaResponse.data, fs.createWriteStream(mediaPath));

            //Envia com midia
            const mediaMessage = MessageMedia.fromFilePath(mediaPath);
            await client.sendMessage(`${number}@c.us`, mediaMessage, { caption: message });
        } else {
            // Envia apenas texto
            await client.sendMessage(`${number}@c.us`, message);
        }
        await client.sendMessage(`${number}@c.US`, message);
        res.json({success: true});
    } catch (e) {
        res.status(500).json({ error: e.message });
    }
});

client.initialize();
app.listen(3000, () => console.log('Node.js rodando na porta 3000'));