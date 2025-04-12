'use client'
import React, { useState, useEffect } from 'react';
import { useAppContext } from '@/context';
import whatsapp from '@/services/whatsapp';
import styles from './styles.module.scss';

interface Contact {
  id: {
    server: string;
    user: string;
    _serialized: string;
  };
  name: string;
}

interface Message {
  from: string;
  to: string;
  content: string;
  timestamp: string;
}

export default function Live() {
  const [contacts, setContacts] = useState<Contact[]>([]);
  const [selectedContact, setSelectedContact] = useState<Contact | null>(null);
  const [messages, setMessages] = useState<Message[]>([]);
  const [newMessage, setNewMessage] = useState<string>('');
  const { token } = useAppContext();

  function formatPhoneNumber(number: string) {
    // Remove o código do país '55' do início
    if (number.startsWith('55')) {
      number = number.slice(2);
    }

    // Verifica se o número tem 10 dígitos e adiciona um '9' após o DDD
    if (number.length === 10) {
      number = number.slice(0, 2) + '9' + number.slice(2);
    }

    return number;
  }

  const fetchMessagesForContact = async (contact: Contact) => {
    try {
      const response = await whatsapp.get(`/message/${formatPhoneNumber(contact.id.user)}`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      setMessages(response.data.messages);
    } catch (error) {
      console.error('Erro ao buscar mensagens:', error);
      alert('Erro ao buscar mensagens. Por favor, tente novamente.');
    }
  };

  useEffect(() => {
    const fetchContacts = async () => {
      try {
        const response = await whatsapp.get('/contacts', {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        const messagesResponse = await whatsapp.get('/all-messages', {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        const messagesData = messagesResponse.data.messages;
        const contactsWithMessages = response.data.contacts.filter((contact: Contact) => {
          return messagesData.some((message: Message) =>
            message.from === formatPhoneNumber(contact.id.user) || message.to === formatPhoneNumber(contact.id.user)
          );
        });

        setContacts(contactsWithMessages);
      } catch (error) {
        console.error('Erro ao buscar contatos ou mensagens:', error);
        //alert('Erro ao buscar contatos ou mensagens. Por favor, tente novamente.');
      }
    };

    fetchContacts();
    const interval = setInterval(fetchContacts, 5000);

    return () => clearInterval(interval);
  }, [token]);

  useEffect(() => {
    if (selectedContact) {
      const interval = setInterval(() => {
        fetchMessagesForContact(selectedContact);
      }, 5000);

      return () => clearInterval(interval);
    }
  }, [selectedContact]);

  const handleContactClick = (contact: Contact) => {
    setSelectedContact(contact);
    fetchMessagesForContact(contact);
  };

  const handleSendMessage = async () => {
    if (selectedContact && newMessage.trim() !== '') {
      setNewMessage('');
      try {
        await whatsapp.post(
          '/send-message',
          {
            number: selectedContact.id._serialized,
            message: newMessage,
          },
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        setMessages((prevMessages) => [
          ...prevMessages,
          {
            from: 'me',
            to: formatPhoneNumber(selectedContact.id.user),
            content: newMessage,
            timestamp: new Date().toISOString(),
          },
        ]);
      } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        alert('Erro ao enviar mensagem. Por favor, tente novamente.');
      }
    }
  };

  return (
    <div className={styles.Main}>
      <div className={styles.ContactList}>
        <h2 className={styles.ChatHeader}>Usuários whatsapp</h2>
        <ul>
          {contacts.map((contact) => (
            <li
              key={contact.id._serialized}
              onClick={() => handleContactClick(contact)}
              className={styles.ContactItem}
            >
              {formatPhoneNumber(contact.id.user)}
            </li>
          ))}
        </ul>
      </div>
      <div className={styles.ChatWindow}>
        <h2 className={styles.ChatHeader}>{selectedContact ? <p>{formatPhoneNumber(selectedContact.id.user)}</p> : <p>Chat</p>}</h2>
        <div className={styles.MessageList}>
          {messages.map((message, index) => {
            let displayContent;

            if (message.content === undefined) {
              displayContent = "(audio)";
            } else if (/^[A-Za-z0-9+/=]{10,}$/.test(message.content)) {
              // Verifica se a string parece base64 ou uma hash (ajuste a regex conforme necessário)
              displayContent = "(arquivo/imagem/video)";
            } else {
              displayContent = message.content;
            }

            return (
              <div
                key={index}
                className={`${styles.MessageItem} ${message.from === 'me' ? styles['MessageItem--sent'] : styles['MessageItem--received']
                  }`}
              >
                <strong>{message.from === 'me' ? 'Você' : selectedContact?.id.user}:</strong> {displayContent}
              </div>
            );
          })}
        </div>
        <div className={styles.InputArea}>
          <input
            type="text"
            value={newMessage}
            onChange={(e) => setNewMessage(e.target.value)}
            className={styles.InputField}
            placeholder="Digite sua mensagem"
            disabled={!selectedContact}
          />
          <button onClick={handleSendMessage} className={styles.SendButton} disabled={!selectedContact}>
            Enviar
          </button>
        </div>
      </div>
    </div >
  );
}