"use client"
import { useEffect, useState } from "react";
import { useAppContext } from "@/context";
import { useRouter } from "next/navigation";

import { PrivateHeader } from "@/components/PrivateHeader";
import { SideMenu } from "@/components/Dashboard/SideMenu";
import { HeaderButtons } from "@/components/Dashboard/HeaderButtons";
import { ConnectModal } from "@/components/Dashboard/ConnectModal";
//import { LoadingModal } from "@/components/Dashboard/LoadingModal";

import styles from './styles.module.scss';
import whatsapp from "@/services/whatsapp";
import api from "@/services/api";

export default function RootLayout({
    children,
}: Readonly<{
    children: React.ReactNode;
}>) {

    const { push } = useRouter();
    const { isSideMenuOpen, openConnectionModal, setOpenConnectionModal, setConnectionStatus, setToken, token, setIsAdmin, setName} = useAppContext();
    //const [isLoading, setIsLoading] = useState(false);
    const [isMobile, setIsMobile] = useState(false);

    useEffect(() => {
        // Função para verificar o tamanho da tela
        const checkMobile = () => {
            setIsMobile(window.innerWidth <= 768);
        };

        checkMobile();
        window.addEventListener('resize', checkMobile);

        return () => window.removeEventListener('resize', checkMobile);
    }, []);

    useEffect(() => {
        async function verifyUserIsLogged() {
            const token = await localStorage.getItem("Authorization");

            if (!token) {
                return push("/");
            }

            setToken(token);

            try {
                const response = await api.get('/get-user', {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                });

                const { user } = response.data;

                setName(user.name);

                if (!user.enabled) {
                    alert("Usuário Desabilitado!");
                    await localStorage.removeItem('Authorization');
                    return push("/");
                }

                if(user.role == "admin"){
                    setIsAdmin(true);
                }

                const { data } = await whatsapp.get("/check-connection", {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    }
                });

                if (data.status === "connected") {
                    setConnectionStatus(data.status);
                } else {
                    const { data } = await whatsapp.get('/verify-folder', {
                        headers: {
                            Authorization: `Bearer ${token}`,
                        }
                    });

                    if (data.result) {
                        /*setIsLoading(true);
                        const { data } = await whatsapp.get('/start-whatsapp', {
                            headers: {
                                Authorization: `Bearer ${token}`,
                            }
                        });

                        if (data.status === "connected") {
                            setConnectionStatus(data.status);
                            setIsLoading(false);
                        } else {
                            setIsLoading(false);
                            setOpenConnectionModal(true);
                        }*/
                       try{
                            setConnectionStatus("not_connected");
                            await whatsapp.get('/delete-session', {headers:{
                                Authorization: `Bearer ${token}`
                            }})

                            if(user.role != "admin"){
                                setOpenConnectionModal(true);
                            }
                       }catch(err){
                            console.error(err);
                            alert("Erro ao tentar iniciar o whatsapp novamente");
                       }
                    } else {
                        setConnectionStatus("not_connected");
                        if(user.role != "admin"){
                            setOpenConnectionModal(true);
                        }
                    }
                }
            } catch (err) {
                console.error(err);
                //await localStorage.removeItem("Authorization");
                //return push("/");
            }
        }

        verifyUserIsLogged();
    }, []);

    if (!token) return <h1>Carregando...</h1>;

    return (
        <>
            {openConnectionModal && <ConnectModal token={token} />}
            {/*isLoading && <LoadingModal />*/}
            <main className={styles.Main}>
                <PrivateHeader />
                <div className={styles.Separator}>
                    <div className={styles.Side} style={isMobile ? { width: "100%" } : (isSideMenuOpen ? { width: "30%" } : { width: "5%" })}>
                        <SideMenu />
                    </div>
                    <div className={styles.ContentArea}>
                        <div className={styles.Header}>
                            <HeaderButtons />
                        </div>
                        <div className={styles.Content}>
                            {children}
                        </div>
                    </div>
                </div>
            </main>
        </>
    );
}