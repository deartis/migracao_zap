const express = require("express");
const { Client, MessageMedia, LocalAuth } = require("whatsapp-web.js");
const qrcode = require("qrcode-terminal");
const cors = require("cors");
const fs = require("fs");
const path = require("path");
const axios = require("axios");
const { pipeline } = require("stream/promises");
const multer = require("multer");
const upload = multer({ dest: "uploads/" }); // Cria uploads/ temporário

// Novo
const ffmpeg = require("fluent-ffmpeg");
const ffmpegPath = require("ffmpeg-static");

// Deixa o fluent-ffmpeg usar o caminho correto do ffmpeg
ffmpeg.setFfmpegPath(ffmpegPath);

const app = express();
app.use(express.json());

// Configuração de CORS
app.use(
  cors({
    origin: "*" /* ['http://localhost:3001', 'http://localhost:8000']*/,
    methods: ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
    allowedHeaders: ["Content-Type", "Authorization"],
    credentials: true,
  }),
);

// Armazenamento para clientes conectados por usuário/token
const clients = new Map();

// Função para obter ou criar cliente WhatsApp para um token específico
function getClientForUser(token) {
  if (!clients.has(token)) {
    const client = new Client({
      puppeteer: {
        headless: true,
        args: ["--no-sandbox", "--disable-setuid-sandbox"],
      },
      authStrategy: new LocalAuth({ clientId: `user-${token}` }),
    });

    const clientData = {
      client,
      qrCode: null,
      status: "disconnected", // disconnected, connecting, connected
      phoneNumber: null,
      ready: false,
    };

    // Configurar evento de QR Code
    client.on("qr", (qr) => {
      qrcode.generate(qr, { small: true });
      console.log(
        `Novo QR Code gerado para token: ${token.substring(0, 10)}...`,
      );

      // Convertendo QR para data URL (base64)
      // Nota: Na prática, você usaria uma biblioteca como qrcode-generator ou qrcode
      // para gerar um data URL real. Isso é apenas um placeholder para demonstração
      clientData.qrCode = qr; // Em produção, use algo como: `data:image/png;base64,${base64QrCode}`
      clientData.status = "connecting";
    });

    // Evento de pronto
    client.on("ready", () => {
      console.log(
        `Cliente WhatsApp pronto para token: ${token.substring(0, 10)}...`,
      );
      clientData.status = "connected";
      clientData.ready = true;

      // Obtém informações do cliente conectado
      client.getState().then((state) => {
        console.log(`Estado do cliente: ${state}`);
      });

      // Obtém o número de telefone conectado
      client.getWid().then((wid) => {
        // Formato típico: "553199999999@c.us"
        const phoneNumber = wid ? wid.replace(/@c\.us$/, "") : null;
        clientData.phoneNumber = phoneNumber;
        console.log(`Número conectado: ${phoneNumber}`);
      });
    });

    // Evento de desconexão
    client.on("disconnected", (reason) => {
      console.log(
        `Cliente desconectado para token ${token.substring(0, 10)}...: ${reason}`,
      );
      clientData.status = "disconnected";
      clientData.ready = false;
      clientData.qrCode = null;
    });

    // Inicializa o cliente
    client.initialize();

    // Armazena o cliente no mapa
    clients.set(token, clientData);
  }

  return clients.get(token);
}

// Middleware para extrair token do header Authorization
function extractToken(req, res, next) {
  const authHeader = req.headers.authorization;
  if (!authHeader || !authHeader.startsWith("Bearer ")) {
    return res.status(401).json({ error: "Token não fornecido" });
  }

  req.token = authHeader.substring(7); // Remove 'Bearer ' do início
  next();
}

// Rota inicial para obter QR Code - compatível com o frontend_old
app.get("/start-whatsapp", extractToken, async (req, res) => {
  try {
    const token = req.token;
    const clientData = getClientForUser(token);

    // Se já estiver conectado, retorna o status
    if (clientData.status === "connected") {
      return res.json({
        status: "connected",
        phoneNumber: clientData.phoneNumber,
      });
    }

    // Se já tiver QR code, retorna
    if (clientData.qrCode) {
      return res.json({ qrCode: clientData.qrCode });
    }

    // Aguarda 2 segundos para dar tempo de gerar o QR
    setTimeout(() => {
      if (clientData.qrCode) {
        return res.json({ qrCode: clientData.qrCode });
      } else {
        return res.status(202).json({
          message:
            "QR Code ainda não disponível, tente novamente em alguns segundos",
          status: "waiting",
        });
      }
    }, 2000);
  } catch (error) {
    console.error("Erro ao iniciar WhatsApp:", error);
    res.status(500).json({ error: error.message });
  }
});

// Verifica status da conexão
app.get("/check-connection", extractToken, (req, res) => {
  try {
    const token = req.token;
    const clientData = clients.get(token);

    if (!clientData) {
      return res.json({ status: "disconnected" });
    }

    return res.json({
      status: clientData.status,
      phoneNumber: clientData.phoneNumber,
    });
  } catch (error) {
    console.error("Erro ao verificar conexão:", error);
    res.status(500).json({ error: error.message });
  }
});

// Verifica se o número conectado é o correto
app.get("/verify-folder", extractToken, async (req, res) => {
  try {
    const token = req.token;
    const clientData = clients.get(token);

    if (!clientData || clientData.status !== "connected") {
      return res.json({ result: false, error: "WhatsApp não conectado" });
    }

    return res.json({
      result: true,
      phoneNumber: clientData.phoneNumber,
    });
  } catch (error) {
    console.error("Erro ao verificar pasta:", error);
    res.status(500).json({ error: error.message });
  }
});

// Deleta a sessão
app.get("/delete-session", extractToken, async (req, res) => {
  try {
    const token = req.token;
    const clientData = clients.get(token);

    if (clientData) {
      await clientData.client.destroy();
      clients.delete(token);

      // Remover arquivos de sessão (opcional)
      const sessionDir = path.join(
        __dirname,
        `.wwebjs_auth/session-user-${token}`,
      );
      if (fs.existsSync(sessionDir)) {
        fs.rmSync(sessionDir, { recursive: true, force: true });
      }
    }

    res.json({ success: true });
  } catch (error) {
    console.error("Erro ao deletar sessão:", error);
    res.status(500).json({ error: error.message });
  }
});

// Rota para enviar mensagens

app.post(
  "/send-message",
  extractToken,
  upload.single("media"),
  async (req, res) => {
    try {
      const token = req.token;
      console.log("Token recebido:", token);

      const { number, message } = req.body;
      const file = req.file;
      const clientData = clients.get(token);

      if (!clientData || clientData.status !== "connected") {
        return res.status(400).json({ error: "WhatsApp não conectado" });
      }

      let formattedNumber = number.replace(/\D/g, "");
      if (!formattedNumber.startsWith("55")) {
        formattedNumber = "55" + formattedNumber;
      }

      const chatId = `${formattedNumber}@c.us`;

      if (file) {
        console.log("Arquivo recebido:", file.originalname);

        const originalPath = file.path;
        const ext = path.extname(file.originalname).toLowerCase();
        const isVideo = [".mp4", ".mov", ".avi", ".mkv"].includes(ext);

        let mediaPathToUse = originalPath;

        if (isVideo) {
          console.log("Arquivo é vídeo, convertendo para compatibilidade...");

          const convertedPath =
            originalPath.replace(/\.[^/.]+$/, "") + "_converted.mp4";

          // Converte o vídeo para H264 + AAC
          await new Promise((resolve, reject) => {
            ffmpeg(originalPath)
              .outputOptions([
                "-vcodec libx264",
                "-acodec aac",
                "-strict -2",
                "-movflags faststart",
                "-preset veryfast",
              ])
              .output(convertedPath)
              .on("end", () => {
                console.log("Conversão concluída:", convertedPath);
                resolve();
              })
              .on("error", (err) => {
                console.error("Erro na conversão de vídeo:", err);
                reject(err);
              })
              .run();
          });

          mediaPathToUse = convertedPath;
        }

        // Lê o arquivo convertido (ou original)
        const mediaData = fs.readFileSync(mediaPathToUse);
        const base64Data = mediaData.toString("base64");

        const mediaMessage = new MessageMedia(
          isVideo ? "video/mp4" : file.mimetype,
          base64Data,
          file.originalname,
        );

        const sent = await clientData.client.sendMessage(chatId, mediaMessage, {
          caption: message,
        });

        // Limpa os arquivos temporários
        fs.unlinkSync(originalPath);
        if (mediaPathToUse !== originalPath) {
          fs.unlinkSync(mediaPathToUse);
        }

        return res.json({
          success: true,
          messageId: sent.id._serialized,
          timestamp: Date.now(),
        });
      } else {
        // Se for mensagem normal (sem mídia)
        const sent = await clientData.client.sendMessage(chatId, message);
        return res.json({
          success: true,
          messageId: sent.id._serialized,
          timestamp: Date.now(),
        });
      }
    } catch (e) {
      console.error("Erro ao enviar mensagem:", e);
      res.status(500).json({ error: e.message });
    }
  },
);

// Saúde do servidor
app.get("/health", (req, res) => {
  res.json({ status: "ok" });
});

const PORT = process.env.PORT || 3001;
app.listen(PORT, () =>
  console.log(`Servidor WhatsApp rodando na porta ${PORT}`),
);
