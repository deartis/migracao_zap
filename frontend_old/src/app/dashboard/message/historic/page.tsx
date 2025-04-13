"use client"
import { useAppContext } from "@/context";
import api from "@/services/api";
import { useEffect, useState } from "react";

import styles from './Historic.module.scss';

interface Historic {
    id: string;
    contact: string;
    status: string;
    name: string;
    errorType: string;
    date: Date;
}

export default function Historic() {
    const [historic, setHistoric] = useState<Historic[]>([]);
    const [searchTerm, setSearchTerm] = useState('');
    const { token } = useAppContext();

    useEffect(() => {
        async function getHistoric() {
            const { data } = await api.get('/get-historic', {
                headers: {
                    Authorization: `Bearer ${token}`,
                }
            });

            if (data) {
                setHistoric(data);
            }
        }

        getHistoric();
    }, []);

    const filteredHistoric = historic.filter(h =>
        h.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        h.contact.toLowerCase().includes(searchTerm.toLowerCase()) ||
        h.status.toLowerCase().includes(searchTerm.toLowerCase()) ||
        h.errorType.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <main className={styles.Main}>
            <form onSubmit={(e) => e.preventDefault()}>
                <input 
                    type="text"
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    placeholder="Pesquisar..."
                />
            </form>
            <div>
                <table>
                    <thead>
                        <tr>
                            <td>ID</td>
                            <td>Contato</td>
                            <td>Status</td>
                            <td>Nome</td>
                            <td>Tipo de Erro</td>
                            <td>Data</td>
                        </tr>
                    </thead>
                    <tbody>
                        {filteredHistoric.map((historic, index) => (
                            <tr 
                                key={historic.id}
                                className={historic.status.toLowerCase() === 'enviado' ? styles['status-enviado'] : styles['status-nao-enviado']}
                            >
                                <td>{index + 1}</td>
                                <td>{historic.contact}</td>
                                <td>{historic.status}</td>
                                <td>{historic.name}</td>
                                <td>{historic.errorType}</td>
                                <td>{new Date(historic.date).toLocaleString()}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </main>
    );
}