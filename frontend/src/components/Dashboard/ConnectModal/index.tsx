import { useState, useEffect } from 'react';
import { useAppContext } from '@/context';
import Image from 'next/image';
import { Oval } from 'react-loader-spinner';
import whatsapp from '@/services/whatsapp';
import { useRouter } from 'next/navigation';
import styles from './ConnectionModal.module.scss';
import api from '@/services/api';

export function ConnectModal({ token }: { token: string }) {

    const { push } = useRouter();

    const { connectionStatus, setConnectionStatus, setOpenConnectionModal, setToken } = useAppContext();
    const [isReadyForConnecting, setIsReadyForConnecting] = useState(false);
    const [qrCode, setQrCode] = useState("");
    const [error, setError] = useState("");
    const [loading, setLoading] = useState(false);



    useEffect(() => {
        if (isReadyForConnecting) {
            getQrCode();
        }
    }, [isReadyForConnecting]);

    async function checkConnectionStatus() {
        try {
            setLoading(true);
            const { data } = await whatsapp.get("/check-connection", {
                headers: {
                    Authorization: `Bearer ${token}`,
                }
            });

            if (data.status === 'connected') {
                setConnectionStatus(data.status);
                setOpenConnectionModal(false);
                try {
                    const response = await api.get('/get-user', {
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    });

                    const {user} = response.data;
    
                    if (!user.rightNumber) {
                        const verifyFolder = await whatsapp.get('/verify-folder', {
                            headers: {
                                Authorization: `Bearer ${token}`,
                            }
                        });

                        if (verifyFolder.data.result) {
                            await localStorage.removeItem('Authorization');
                            alert("ERRO: Você se conectou com um numero diferente do número cadastrado")
                            try {
                                await whatsapp.get('/delete-session', {
                                    headers: {
                                        Authorization: `Bearer ${token}`,
                                    }
                                })
                                setToken("");
                                return push("/")

                            } catch (err) {
                                console.error(err);
                                console.error("Erro ao desconectar sessão", err);
                            }
                        }
                    }
                } catch (err) {
                    console.error(err);
                }


            } else if (data.status == 'wrong_number') {
                // Número conectado diferente do cadastrado
                setError("O número conectado não corresponde ao número cadastrado. Por favor, conecte-se com o número correto.");
                setIsReadyForConnecting(false); // Permitir reiniciar o processo
            } else {
                setTimeout(checkConnectionStatus, 5000);
            }
        } catch (error) {
            console.error('Erro ao checar conexão:', error);
            setError("Erro ao verificar o status da conexão. Tente novamente.");
        } finally {
            setLoading(false);
        }
    }

    async function getQrCode() {
        try {
            setLoading(true);
            const { data } = await whatsapp.get("/start-whatsapp", {
                headers: {
                    Authorization: `Bearer ${token}`,
                }
            });

            if (data.qrCode) {
                setQrCode(data.qrCode);
                checkConnectionStatus();
            } else {
                setError("Não foi possível obter o QR Code. Tente novamente.");
            }
        } catch (error) {
            console.error('Erro ao obter QR Code:', error);
            setError("Erro ao iniciar a conexão com o WhatsApp. Tente novamente.");
        } finally {
            setLoading(false);
        }
    }

    if (error) {
        return (
            <div className={styles.External}>
                <main className={styles.Main}>
                    <h1>{error}</h1>
                    <button onClick={() => { setError(""); setIsReadyForConnecting(false); }}>Tentar Novamente</button>
                </main>
            </div>
        );
    }

    if (isReadyForConnecting) {
        if (!qrCode) {
            return (
                <div className={styles.External}>
                    <main className={styles.Main}>
                        <h1>Conectando ao serviço do Whatsapp... isso pode demorar um pouco!</h1>
                        {loading && (
                            <div className={styles.SpinnerContainer}>
                                <Oval
                                    height={80}
                                    width={80}
                                    color="#4fa94d"
                                    ariaLabel="loading"
                                    secondaryColor="#4fa94d"
                                    strokeWidth={2}
                                    strokeWidthSecondary={2}
                                />
                            </div>
                        )}
                    </main>
                </div>
            );
        }

        return (
            <div className={styles.External}>
                <main className={styles.Main}>
                    <h1>Leia esse QRCode no seu Whatsapp</h1>
                    <Image src={qrCode} alt='QR Code para conexão no whatsapp' width={300} height={300} />
                </main>
            </div>
        );
    }

    if (connectionStatus === "connected") {
        return (
            <div className={styles.External}>
                <main className={styles.Main}>
                    <h1>Agora você está conectado(a)! :D Em alguns segundos essa tela será fechada</h1>
                </main>
            </div>
        );
    }

    return (
        <div className={styles.External}>
            <main className={styles.Main}>
                <h1>Ops. Parece que seu Whatsapp ainda não foi conectado no sistema!</h1>
                <p>Abra o Whatsapp no seu celular e deixe preparado para conectar um novo dispositivo via QRCode</p>
                <button onClick={() => setIsReadyForConnecting(true)}>Meu Whatsapp já está preparado!</button>
            </main>
        </div>
    );
}