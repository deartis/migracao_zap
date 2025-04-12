"use client"

import { FormEvent, useState } from 'react';
import { useAppContext } from '@/context';

import styles from './styles.module.scss';
import whatsapp from '@/services/whatsapp';
import Link from 'next/link';
import { FaPaperclip } from 'react-icons/fa'; // Importar ícone de clipe
import api from '@/services/api'; // Importar o serviço de API

export default function SingleContact() {
    const { token, connectionStatus } = useAppContext();

    const [number, setNumber] = useState("");
    const [message, setMessage] = useState("");
    const [file, setFile] = useState<File | null>(null);
    const [isSending, setIsSending] = useState<boolean>(false);
    const [progress, setProgress] = useState<number>(0);
    const [statusMessage, setStatusMessage] = useState<string>("");

    async function updateSentMessagesCount(sentCount: number) {
        try {
            await api.post('/increment-sended', { sended: sentCount }, {
                headers: {
                    Authorization: `Bearer ${token}`,
                }
            });
            console.log("Contagem de mensagens enviadas atualizada com sucesso.");
        } catch (error) {
            console.error("Erro ao atualizar contagem de mensagens enviadas: ", error);
        }
    }

    async function handleSendMessage(e: FormEvent) {
        e.preventDefault();

        if (connectionStatus !== "connected") {
            alert("Você não está conectado no WhatsApp.");
            return;
        }

        if (!message) {
            alert("Você não pode enviar uma mensagem em branco");
            return;
        }

        const { data } = await api.get("/user-can-send", {
            headers: {
                Authorization: `Bearer ${token}`
            }
        });

        if (!data.send) {
            alert("O seu pacote de envio de mensagens excedeu o limite, entre em contato com o suporte!")
            return;
        }

        // Verificação do formato do número
        const cleanNumber = number.replace(/\D/g, ''); // Remove caracteres não numéricos
        if (!/^\d{10,11}$/.test(cleanNumber)) {
            alert("O número deve estar no formato DDD + número, com 10 ou 11 dígitos.");
            return;
        }

        setIsSending(true);
        setProgress(0); // Inicia o progresso

        try {
            // Verifique o limite antes de tentar enviar a mensagem
            const { data } = await api.get("/user-can-send", {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            });

            if (!data.send) {
                // Registra o erro de limite excedido
                await api.post("/create-user-historic", {
                    contact: number,
                    status: "Não Enviado",
                    name: "", // Se houver um nome associado, adicione aqui
                    errorType: "Limite de envios excedido"
                }, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    }
                });
                setStatusMessage("O seu pacote de envio de mensagens excedeu o limite, entre em contato com o suporte!");
                setProgress(0);
                return;
            }

            const formData = new FormData();
            formData.append("number", cleanNumber);
            formData.append("message", message);
            if (file) {
                formData.append("file", file);
            }

            setProgress(50); // Simula o progresso inicial

            const response = await whatsapp.post("/send-message", formData, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'multipart/form-data',
                }
            });

            if (response.data.status === "success") {
                setStatusMessage("Mensagem enviada com sucesso!");
                setProgress(100);

                // Atualiza a contagem de mensagens enviadas
                await updateSentMessagesCount(1);

                setNumber("");
                setMessage("");
                setFile(null);
            } else {
                setStatusMessage("Falha ao enviar mensagem.");
                setProgress(0);
            }
        } catch (error) {
            console.error("Error sending message:", error);
            setStatusMessage("Erro ao enviar mensagem. Tente novamente.");
            setProgress(0);
        }
    }

    return (
        <main className={styles.Main}>
            {isSending && (
                <div className={styles.progressContainer}>
                    <div className={styles.progressBarContainer}>
                        <div className={styles.progressBar} style={{ width: `${progress}%` }}></div>
                    </div>
                    <Link href="/dashboard/message/historic">{statusMessage}</Link>
                </div>
            )}
            <h1>Mensagem única:</h1>
            <form onSubmit={handleSendMessage}>
                <input type="text" placeholder="22990909090" onChange={e => setNumber(e.target.value)} value={number} disabled={isSending} />
                <div className={styles.fileUpload}>
                    <input type="file" id="file" onChange={e => setFile(e.target.files ? e.target.files[0] : null)} disabled={isSending} />
                    <label htmlFor="file">
                        <FaPaperclip size={20} />
                    </label>
                    {file && <span className={styles.fileName}>{file.name}</span>}
                </div>
                <p>Mensagem para único número:</p>
                <textarea onChange={e => setMessage(e.target.value)} value={message} disabled={isSending}></textarea>
                <button type="submit" disabled={isSending}>Enviar</button>
            </form>
        </main>
    );
}