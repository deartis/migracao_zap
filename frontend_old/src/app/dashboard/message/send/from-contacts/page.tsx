"use client"
import React, { useState, useEffect } from 'react';
import { useAppContext } from "@/context";
import whatsapp from "@/services/whatsapp";
import Link from 'next/link';
import { FaPaperclip } from 'react-icons/fa'; // Importar ícone de clipe

import styles from './styles.module.scss';
import api from '@/services/api';

interface Contact {
    id: {
        server: string;
        user: string;
        _serialized: string;
    };
    name: string;
}

function FromContacts() {
    const { token, connectionStatus } = useAppContext();
    const [contacts, setContacts] = useState<Contact[]>([]);
    const [showModal, setShowModal] = useState<boolean>(false);
    const [message, setMessage] = useState<string>('');
    const [selectedContacts, setSelectedContacts] = useState<Contact[]>([]);
    const [file, setFile] = useState<File | null>(null);
    const [isSending, setIsSending] = useState<boolean>(false);
    const [progress, setProgress] = useState<number>(0);
    const [successCount, setSuccessCount] = useState<number>(0);
    const [failureCount, setFailureCount] = useState<number>(0);
    const [buttonIsBlocked, setButtonIsBlocked] = useState(true);

    async function fetchContacts() {
        try {
            const { data } = await whatsapp.get<{ contacts: Contact[] }>('/contacts', {
                headers: {
                    Authorization: `Bearer ${token}`,
                }
            });
            setContacts(data.contacts);
        } catch (error) {
            console.error('Erro ao obter contatos:', error);
        }
    }

    useEffect(() => {
        fetchContacts();
    }, [token]);

    function toggleModal() {
        if (connectionStatus !== "connected") {
            alert("Você não está conectado no WhatsApp.");
            return;
        }

        setShowModal(!showModal);
    }

    function handleSelectContact(contact: Contact) {
        setSelectedContacts(prev =>
            prev.includes(contact)
                ? prev.filter(c => c !== contact)
                : [...prev, contact]
        );
    }

    const handleFileUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
        if (event.target.files && event.target.files[0]) {
            setFile(event.target.files[0]);
        }
    };

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

    async function handleSendMessages() {
        if (connectionStatus !== "connected") {
            alert("Você não está conectado no WhatsApp.");
            return;
        }

        if (selectedContacts.length === 0) {
            alert("Por favor, selecione pelo menos um contato.");
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

        setIsSending(true);
        setProgress(0);
        setSuccessCount(0);
        setFailureCount(0);

        try {
            for (let i = 0; i < selectedContacts.length; i++) {
                const contact = selectedContacts[i];

                // Verifique o limite antes de cada envio
                const { data } = await api.get("/user-can-send", {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                });

                if (!data.send) {
                    // Registra o erro de limite excedido
                    await api.post("/create-user-historic", {
                        contact: contact.id.user.replace(/^55/, ''),
                        status: "Não Enviado",
                        name: contact.name,
                        errorType: "Limite de envios excedido"
                    }, {
                        headers: {
                            Authorization: `Bearer ${token}`,
                        }
                    });
                    setFailureCount(prev => prev + 1);
                } else {
                    const formData = new FormData();

                    if (file) {
                        formData.append('file', file);
                    }

                    formData.set('contacts[0][phone]', contact.id._serialized);
                    formData.set('contacts[0][message]', message);
                    formData.set('contacts[0][name]', contact.name || '');

                    try {
                        await whatsapp.post("/send-many-message", formData, {
                            headers: {
                                Authorization: `Bearer ${token}`,
                            }
                        });
                        setSuccessCount(prev => prev + 1);
                        await updateSentMessagesCount(1);

                    } catch (error) {
                        console.error("Erro ao enviar mensagem: ", error);
                        setFailureCount(prev => prev + 1);
                    }
                }

                setProgress(((i + 1) / selectedContacts.length) * 100);
            }

            console.log("Processo de envio concluído.");
            setButtonIsBlocked(false);
        } catch (error) {
            console.error("Erro ao processar envio: ", error);
        }
    }

    // Função para resetar todos os estados
    const resetStates = () => {
        setContacts([]);
        setShowModal(false);
        setMessage('');
        setSelectedContacts([]);
        setFile(null);
        setIsSending(false);
        setProgress(0);
        setSuccessCount(0);
        setFailureCount(0);
        setButtonIsBlocked(true);

        // Recarregar contatos
        fetchContacts();
    };

    return (
        <div>
            {showModal && (
                <Modal
                    contacts={contacts}
                    onClose={toggleModal}
                    onSelectContact={handleSelectContact}
                    selectedContacts={selectedContacts}
                    setSelectedContacts={setSelectedContacts}
                />
            )}
            <div className={styles.Main}>
                {isSending && (
                    <div className={styles.progressContainer}>
                        <div className={styles.progressBarContainer}>
                            <div className={styles.progressBar} style={{ width: `${progress}%` }}></div>
                        </div>
                        <p>{Math.round((progress / 100) * selectedContacts.length)}/{selectedContacts.length}</p>
                        <div>
                            <Link href="/dashboard/message/historic" className={styles.successCount}>{successCount} - Enviadas</Link>
                            <Link href="/dashboard/message/historic" className={styles.failureCount}>{failureCount} - Não enviada{failureCount !== 1 ? 's' : ''}</Link>
                        </div>
                        {/* Botão "Novo Envio" que reseta os estados */}
                        <button onClick={resetStates} disabled={buttonIsBlocked} className={styles.sendNew}>Novo Envio</button>
                    </div>
                )}
                <h1>Mensagem em massa para os contatos da agenda do telefone:</h1>
                <div className={styles.fileUpload}>
                    <input type="file" id="file" onChange={handleFileUpload} disabled={isSending} />
                    <label htmlFor="file">
                        <FaPaperclip size={20} />
                    </label>
                    {file && <span className={styles.fileName}>{file.name}</span>}
                </div>
                <textarea value={message} onChange={(e) => setMessage(e.target.value)} placeholder="Digite sua mensagem" disabled={isSending}></textarea>
                <div className={styles.buttons}>
                    <button onClick={toggleModal} style={{ background: "#8e84b7" }} disabled={isSending}>Contatos</button>
                    <button onClick={handleSendMessages} style={{ background: "#00923f" }} disabled={isSending}>Enviar Mensagens</button>
                </div>
                <div className={styles.warn}>
                    <p>
                        <span>Importante: </span>O número utilizado deve ter sido utilizado
                        no WhatsApp por alguns meses, pois se for um chip novo, ao utilizar o envio em massa, o
                        WhatsApp irá entender que é spam e  irá bani-lo.
                    </p>
                </div>
            </div>
        </div>
    );
};

interface ModalProps {
    contacts: Contact[];
    onClose: () => void;
    onSelectContact: (contact: Contact) => void;
    selectedContacts: Contact[];
    setSelectedContacts: React.Dispatch<React.SetStateAction<Contact[]>>;
}

function Modal({ contacts, onClose, onSelectContact, selectedContacts, setSelectedContacts }: ModalProps) {
    const [searchTerm, setSearchTerm] = useState('');

    const filteredContacts = contacts.filter(contact =>
        (contact.name && contact.name.toLowerCase().includes(searchTerm.toLowerCase())) ||
        contact.id._serialized.includes(searchTerm)
    );

    function clearSelection() {
        setSelectedContacts([]);
    }

    function selectAll() {
        setSelectedContacts(filteredContacts);
    }

    return (
        <div className={styles.Modal}>
            <div className={styles.ModalContent}>
                <header>
                    <h2>Selecionar contatos</h2>
                    <div>
                        <button onClick={selectAll} style={{ background: '#0093dd' }}>Selecionar todos</button>
                        <button onClick={clearSelection} style={{ background: '#dfea4a' }}>Limpar Seleção</button>
                        <button onClick={onClose} style={{ background: '#00923f' }}>Salvar Selecao</button>
                    </div>
                </header>
                <input
                    type="text"
                    placeholder='Pesquisar por nome/numero'
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                />
                <ul>
                    {filteredContacts.map(contact => (
                        contact.name && (
                            <li key={contact.id._serialized}>
                                <label>
                                    <input
                                        type="checkbox"
                                        checked={selectedContacts.includes(contact)}
                                        onChange={() => onSelectContact(contact)}
                                    />
                                    {contact.name}
                                </label>
                            </li>
                        )
                    ))}
                </ul>
            </div>
        </div>
    );
}

export default FromContacts;