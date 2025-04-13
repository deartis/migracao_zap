"use client";
import React, { useEffect, useState } from 'react';
import * as XLSX from 'xlsx';
import { useAppContext } from '@/context';
import Link from 'next/link';

import styles from './styles.module.scss';
import whatsapp from '@/services/whatsapp';
import { FaPaperclip } from 'react-icons/fa'; // Importar ícone de clipe
import api from '@/services/api';
import moment from 'moment'

interface Contact {
  phone: string;
  message: string;
  name?: string;
}

export default function FromSheet() {
  const [data, setData] = useState<string[][] | null>(null);
  const [placeholders, setPlaceholders] = useState<string[]>([]);
  const [messageTemplate, setMessageTemplate] = useState<string>('');
  const [contacts, setContacts] = useState<Contact[]>([]);
  const [showModal, setShowModal] = useState<boolean>(false);
  const [instructionsVisible, setInstructionsVisible] = useState<boolean>(true);
  const [selectedColumns, setSelectedColumns] = useState<Set<number>>(new Set());
  const [isTextAreaEnabled, setIsTextAreaEnabled] = useState(false);
  const [file, setFile] = useState<File | null>(null);
  const [progress, setProgress] = useState<number>(0);
  const [isSending, setIsSending] = useState<boolean>(false);
  const [successCount, setSuccessCount] = useState<number>(0);
  const [failureCount, setFailureCount] = useState<number>(0);
  const [buttonIsBlocked, setButtonIsBlocked] = useState(true);
  const { token, connectionStatus } = useAppContext();

  async function getLastMessage() {
    try {
      const { data } = await api.get('/get-last-message', {
        headers: {
          Authorization: `Bearer ${token}`,
        }
      });

      if (data.lastMessage) {
        setMessageTemplate(data.lastMessage);
        setIsTextAreaEnabled(true);
      }
    } catch (err) {
      console.error(err);
    }
  }

  useEffect(() => {
    getLastMessage();
  }, []);

  const phonePatterns = [
    /^\d{10}$/,        // Aceita 10 dígitos (DDD + número)
    /^\d{11}$/,        // Aceita 11 dígitos (DDD + prefixo 9 + número)
    /^\d{12}$/,        // Aceita 12 dígitos (55 + DDD + número)
    /^\d{13}$/,        // Aceita 13 dígitos (55 + DDD + prefixo 9 + número)
    /^\d{2}\d{9}$/,    // Aceita 2 dígitos DDD seguidos por 9 dígitos
    /^\d{2}\d{5}-\d{4}$/, // Aceita 2 dígitos DDD, 5 dígitos, hífen, 4 dígitos
    /^\d{2} \d{9}$/,   // Aceita 2 dígitos DDD, espaço, 9 dígitos
    /^\d{2} \d{5}-\d{4}$/ // Aceita 2 dígitos DDD, espaço, 5 dígitos, hífen, 4 dígitos
  ];

  const isPhoneNumber = (value: string): boolean => {
    return phonePatterns.some((pattern) => pattern.test(value));
  };

  const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    if (event.target.files) {
      const reader = new FileReader();
      reader.onload = (e) => {
        const binaryStr = e.target?.result;
        if (binaryStr) {
          const workbook = XLSX.read(binaryStr, { type: 'binary' });
          const sheetName = workbook.SheetNames[0];
          const worksheet = workbook.Sheets[sheetName];
          const jsonData = XLSX.utils.sheet_to_json<string[]>(worksheet, { header: 1, defval: '' });

          const headers = jsonData[0];
          let phoneIndex = -1;

          for (let i = 0; i < headers.length; i++) {
            if (jsonData.slice(1).some((row) => {
              const cellValue = row[i];
              return cellValue && isPhoneNumber(cellValue.toString());
            })) {
              phoneIndex = i;
              break;
            }
          }

          if (phoneIndex === -1) {
            alert('Verifique se sua tabela possui uma coluna com números de telefone válidos (ddd + numero). Ex: 22990909090');
            setFile(null);
            setData(null);
            setShowModal(false);
            setInstructionsVisible(true);
            return;
          }

          const formattedData = jsonData.map((row) => {
            return row.map((cell) => {
              if (typeof cell === 'number') {
                // Verificar se o número é plausível como data
                if (cell > 1 && cell <= 73050) { // Aproximadamente até o ano 2099
                  const excelDateInMillis = (cell - 25569) * 86400 * 1000;
                  const date = new Date(excelDateInMillis);
                  return moment(date).format('DD/MM/YYYY');
                }

                if (cell <= 1) {
                  // Multiplique por 24 para obter a hora do dia
                  const totalHours = cell * 24;
                  const hours = Math.floor(totalHours);
                  const minutes = Math.floor((totalHours - hours) * 60);
                  return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                }
              }
              return cell?.toString().trim();
            });
          });

          setData(formattedData);
          setShowModal(true);
          setInstructionsVisible(false);
        }
      };
      reader.readAsBinaryString(event.target.files[0]);
    }
  };

  const handleTemplateChange = (event: React.ChangeEvent<HTMLTextAreaElement>) => {
    setMessageTemplate(event.target.value);
  };

  const handlePlaceholderClick = (placeholder: string) => {
    setMessageTemplate((prev) => `${prev} {{${placeholder}}}`);
  };

  const generateContacts = () => {
    if (data) {
      const headers = data[0];
      const phoneIndex = headers.findIndex((_, index) => data.slice(1).some((row) => {
        const cellValue = row[index];
        return cellValue && isPhoneNumber(cellValue.toString());
      }));
      const nameIndex = headers.findIndex(header => header.toLowerCase() === 'nome' || header.toLowerCase() === 'name');
      const startRowIndex = isPhoneNumber(data[1][phoneIndex].toString()) ? 1 : 0;

      const generatedContacts = data.slice(startRowIndex)
        .filter(row => row.some(cell => cell !== ''))
        .map((row) => {
          const phone = row[phoneIndex]?.toString().trim();
          if (!phone || !isPhoneNumber(phone)) return null;

          let message = messageTemplate;
          Array.from(selectedColumns).forEach((colIndex) => {
            const placeholder = headers[colIndex];
            const value = (row[colIndex] || '').toString().trim();
            message = message.replace(`{{${placeholder}}}`, value);
          });

          const name = nameIndex !== -1 ? (row[nameIndex] || '').toString().trim() : '';

          return { phone, message, name };
        })
        .filter(contact => contact !== null) as Contact[];

      return generatedContacts;
    }
    return [];
  };

  const updateSentMessagesCount = async (sentCount: number) => {
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
  };

  const handleSendMessages = async () => {
    const generatedContacts = generateContacts();
    setContacts(generatedContacts);

    if (connectionStatus != "connected") {
      alert("Você não está conectado no whatsapp.");
      return;
    }

    if (!messageTemplate) {
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
    setSuccessCount(0);
    setFailureCount(0);

    if (generatedContacts.length > 0) {
      try {
        await api.post('/update-last-message', {
          message: messageTemplate,
        }, {
          headers: {
            Authorization: `Bearer ${token}`,
          }
        });

        for (let i = 0; i < generatedContacts.length; i++) {
          const contact = generatedContacts[i];

          // Verifique o limite antes de cada envio
          const { data } = await api.get("/user-can-send", {
            headers: {
              Authorization: `Bearer ${token}`
            }
          });

          if (!data.send) {
            // Registra o erro de limite excedido
            await api.post("/create-user-historic", {
              contact: contact.phone.replace(/^55/, '').replace(/@c\.us$/, ''),
              status: "Não Enviado",
              name: contact.name || '',
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

            formData.append('contacts[0][phone]', contact.phone);
            formData.append('contacts[0][message]', contact.message);
            if (contact.name) {
              formData.append('contacts[0][name]', contact.name);
            }

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

          setProgress(((i + 1) / generatedContacts.length) * 100);
        }
        console.log("Processo de envio concluído.");
        setButtonIsBlocked(false);
      } catch (error) {
        console.error("Erro ao processar envio: ", error);
      }
    } else {
      console.log("Nenhum contato gerado.");
    }
  };

  const handleColumnSelection = (index: number) => {
    setSelectedColumns((prev) => {
      const newSelection = new Set(prev);
      if (newSelection.has(index)) {
        newSelection.delete(index);
      } else {
        newSelection.add(index);
      }
      return newSelection;
    });
  };

  const handleModalClose = () => {
    const headers = data ? data[0] : [];
    const selectedPlaceholders = Array.from(selectedColumns).map(index => headers[index]);
    setPlaceholders(selectedPlaceholders);
    setShowModal(false);
  };

  const handleFileUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
    if (event.target.files && event.target.files[0]) {
      setFile(event.target.files[0]);
    }
  };

  const resetStates = () => {
    setData(null);
    setPlaceholders([]);
    setMessageTemplate(''); // Reset para vazio
    setContacts([]);
    setShowModal(false);
    setInstructionsVisible(true);
    setSelectedColumns(new Set());
    setIsTextAreaEnabled(false);
    setFile(null);
    setProgress(0);
    setIsSending(false);
    setSuccessCount(0);
    setFailureCount(0);
    setButtonIsBlocked(true);

    // Recarregar a última mensagem após resetar
    getLastMessage();
  };
  return (
    <div>
      {instructionsVisible && (
        <div className={styles.firstPass}>
          <label htmlFor="sheet">Importar XLSX/CSV</label>
          <input type="file" accept=".xlsx, .csv" id="sheet" onChange={handleFileChange} disabled={isSending} />
          <div className={styles.instructions}>
            <h1>Instruções para Layout da Planilha</h1>
            <p>Para garantir que o sistema funcione corretamente, siga estas orientações ao preparar sua planilha de envio de mensagens:</p>
            <ol>
              <li><strong>Formato da Planilha:</strong> A planilha deve estar no formato XLSX/CSV.</li>
              <li><strong>Coluna Obrigatória:</strong>
                <ul>
                  <li><strong>Números de Telefone:</strong> Certifique-se de que há uma coluna com os números de telefone que receberão as mensagens. Esta coluna é obrigatória.</li>
                </ul>
              </li>
              <li><strong>Cabeçalho:</strong>
                <ul>
                  <li>A primeira linha de cada coluna deve conter o título. Por exemplo, se você tiver uma coluna com nomes, a primeira célula dessa coluna deve ser &quot;Nome&quot;.</li>
                </ul>
              </li>
              <li><strong>Dados Adicionais:</strong>
                <ul>
                  <li>As outras colunas podem conter qualquer informação adicional que você deseja, como nome, endereço, etc., desde que a primeira linha contenha o título correspondente.</li>
                </ul>
              </li>
            </ol>
            <p>Seguir estas instruções ajudará a garantir que o sistema processe sua planilha corretamente. Se a estrutura não for seguida, o sistema pode não funcionar conforme esperado.</p>
          </div>
        </div>
      )}

      {showModal && (
        <div className={styles.modal}>
          <h2>Selecione as colunas para placeholders</h2>
          {data && data[0].map((header, index) => (
            <div key={index}>
              <input
                type="checkbox"
                checked={selectedColumns.has(index)}
                onChange={() => handleColumnSelection(index)}
                disabled={isSending}
              />
              <label>{header}</label>
            </div>
          ))}
          <button onClick={handleModalClose} disabled={isSending}>Confirmar</button>
        </div>
      )}

      {placeholders.length > 0 && (
        <div className={styles.sendMessage}>
          {isSending && (
            <div className={styles.progressContainer}>
              <div className={styles.progressBarContainer}>
                <div className={styles.progressBar} style={{ width: `${progress}%` }}></div>
              </div>
              <p>{Math.round((progress / 100) * contacts.length)}/{contacts.length}</p>
              <div>
                <Link href="/dashboard/message/historic" className={styles.successCount}>{successCount} - Enviadas</Link>
                <Link href="/dashboard/message/historic" className={styles.failureCount}>{failureCount} - Não enviada{failureCount !== 1 ? 's' : ''}</Link>
              </div>
              <button onClick={resetStates} disabled={buttonIsBlocked} className={styles.sendNew}>Novo Envio</button>
            </div>
          )}
          <h1>Mensagens em Massa:</h1>
          <div className={styles.fileUpload}>
            <input type="file" id="file" onChange={handleFileUpload} disabled={isSending} />
            <label htmlFor="file">
              <FaPaperclip size={20} />
            </label>
            {file && <span className={styles.fileName}>{file.name}</span>}
          </div>
          <textarea
            placeholder="Escreva sua mensagem aqui..."
            value={messageTemplate}
            onChange={handleTemplateChange}
            disabled={isTextAreaEnabled || isSending}
          />
          <div className={styles.placeholders}>
            <p>Colunas da Tabela</p>
            {placeholders.map((placeholder) => (
              <button
                key={placeholder}
                disabled={isTextAreaEnabled || isSending}
                onClick={() => handlePlaceholderClick(placeholder)}
                style={{ background: '#0093dd' }}
              >
                {placeholder}
              </button>
            ))}
          </div>
          <div className={styles.buttons}>
            <button onClick={() => setIsTextAreaEnabled(!isTextAreaEnabled)} style={{ background: "orange" }} disabled={isSending}>
              Editar mensagem
            </button>
            <button onClick={handleSendMessages} style={{ background: "#00923f" }} disabled={isSending}>
              Enviar Mensagens
            </button>
          </div>
          <div className={styles.warn}>
            <p>
              <span>Importante: </span>O número utilizado deve ter sido utilizado
              no WhatsApp por alguns meses, pois se for um chip novo, ao utilizar o envio em massa, o
              WhatsApp irá entender que é spam e  irá bani-lo.
            </p>
          </div>
        </div>
      )}
    </div>
  );
}