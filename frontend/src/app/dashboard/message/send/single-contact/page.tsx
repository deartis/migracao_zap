"use client"

import { FormEvent, useState } from 'react';
import { useAppContext } from '@/context';
import styles from './styles.module.scss';
import Link from 'next/link';
import { FaPaperclip } from 'react-icons/fa';
import api from '@/services/api';

export default function SingleContact() {
    const { token } = useAppContext(); // Removida a dependência de connectionStatus

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
        } catch (error) {
            console.error("Erro ao atualizar contagem:", error);
        }
    }

    async function handleSendMessage(e: FormEvent) {
        e.preventDefault();

        if (!message) {
            alert("Você não pode enviar uma mensagem em branco");
            return;
        }

        const { data: canSendData } = await api.get("/user-can-send", {
            headers: {
                Authorization: `Bearer ${token}`
            }
        });

        if (!canSendData.send) {
            alert("Limite de mensagens excedido!");
            return;
        }

        // Validação do número
        const cleanNumber = number.replace(/\D/g, '');
        if (!/^\d{10,11}$/.test(cleanNumber)) {
            alert("Número inválido (DDD + número com 10 ou 11 dígitos)");
            return;
        }

        setIsSending(true);
        setProgress(20);

        try {
            // Preparar os dados para envio
            const formData = new FormData();
            formData.append('number', `55${cleanNumber}`); // Adiciona código do Brasil
            formData.append('message', message);

            if (file) {
                formData.append('file', file);
            }

            setProgress(50);

            // Enviar via API Laravel (que repassa para o serviço Node.js)
            const response = await api.post('/whatsapp/send', formData, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'multipart/form-data',
                }
            });

            setProgress(80);

            if (response.data.success) {
                setStatusMessage("Mensagem enviada com sucesso!");
                setProgress(100);

                // Atualizar histórico
                await api.post("/create-user-historic", {
                    contact: number,
                    status: "Enviado",
                    name: ""
                }, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    }
                });

                await updateSentMessagesCount(1);

                // Resetar formulário
                setNumber("");
                setMessage("");
                setFile(null);

            } else {
                throw new Error(response.data.error || "Falha no envio");
            }

        } catch (error) {
            console.error("Erro:", error);
            setStatusMessage("Erro ao enviar mensagem");
            setProgress(0);

            // Registrar erro no histórico
            /*await api.post("/create-user-historic", {
                contact: number,
                status: "Falha",
                name: "",
                errorType: error.message
            }, {
                headers: {
                    Authorization: `Bearer ${token}`,
                }
            });*/

        } finally {
            setIsSending(false);
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
                <input
                    type="text"
                    placeholder="22990909090"
                    onChange={e => setNumber(e.target.value)}
                    value={number}
                    disabled={isSending}
                />
                <div className={styles.fileUpload}>
                    <input
                        type="file"
                        id="file"
                        onChange={e => setFile(e.target.files ? e.target.files[0] : null)}
                        disabled={isSending}
                    />
                    <label htmlFor="file">
                        <FaPaperclip size={20} />
                    </label>
                    {file && <span className={styles.fileName}>{file.name}</span>}
                </div>
                <p>Mensagem para único número:</p>
                <textarea
                    onChange={e => setMessage(e.target.value)}
                    value={message}
                    disabled={isSending}
                ></textarea>
                <button type="submit" disabled={isSending}>
                    {isSending ? 'Enviando...' : 'Enviar'}
                </button>
            </form>
        </main>
    );
}