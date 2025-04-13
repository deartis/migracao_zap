"use client"

import { useEffect, useState } from "react";
import { useAppContext } from "@/context";
import api from "@/services/api";
import { useRouter } from "next/navigation";

import styles from './CardArea.module.scss';

interface Historic {
    id: string;
    contact: string;
    status: string;
    name: string;
    errorType: string;
    date: Date;
}

export function CardArea() {

    const {push} = useRouter();
    const { token } = useAppContext();
    const [historic, setHistoric] = useState<Historic[]>([]);

    useEffect(() => {
        async function getHistoric() {
            const { data } = await api.get('/get-historic', {
                headers: {
                    Authorization: `Bearer ${token}`,
                }
            })

            if (data) {
                setHistoric(data);
            }
        }

        getHistoric();
    }, [])

    async function clearHistoric() {

        const confirm = window.confirm("Tem certeza que deseja deletar o histórico?")
        
        if (confirm) {
            try {
                await api.get('/clear-historic', {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                })

                alert("Histórico deletado com sucesso!")
                setHistoric([]);
            } catch (err) {
                alert("Erro ao deletar o histórico")
                console.error(err);
            }
        }
    }

    return (
        <main className={styles.Main}>
            <div className={styles.cardArea}>
                <div className={styles.green} onClick={() => push("/dashboard/message/historic")}>
                    <h1>{historic.filter(historic => historic.status === "Enviado").length}</h1>
                    <h2>Enviadas</h2>
                </div>

                <div className={styles.red} onClick={() => push("/dashboard/message/historic")}>
                    <h1>{historic.filter(historic => historic.status === "Não Enviado").length}</h1>
                    <h2>Erro/Não Enviadas</h2>
                </div>

                <div className={styles.blue} onClick={() => push("/dashboard/user")}>
                    <h1>{historic.length}</h1>
                    <h2>Total</h2>
                </div>
            </div>

            <button onClick={clearHistoric}>Limpar informações e histórico</button>
        </main>
    )
}