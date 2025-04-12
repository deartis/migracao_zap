"use client"
import { useAppContext } from "@/context";
import api from "@/services/api";
import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from 'next/link';
import styles from './styles.module.scss';
import { CiSearch } from "react-icons/ci";
import { FaTrash } from "react-icons/fa"; // Importa o ícone de lixeira
import whatsapp from "@/services/whatsapp";

interface User {
    id: string;
    name: string;
    email: string;
    password: string;
    number: string;
    msgLimit: number;
    sendedMsg: number;
    role: string;
    enabled: boolean;
}

export default function Users() {
    const [users, setUsers] = useState<User[]>([]);
    const [searchTerm, setSearchTerm] = useState<string>('');

    const { push, refresh } = useRouter();
    const { token } = useAppContext();

    useEffect(() => {
        async function getUser() {
            const { data } = await api.get('/get-user', {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            if (!data || data.user.role !== "admin") {
                push("/dashboard");
            }
        }

        getUser();

        async function getAllUsers() {
            const { data } = await api.get("/get-all-users", {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            console.log(data.users);
            setUsers(data.users);
        }

        getAllUsers();
    }, [push, token]);

    function handleSearchChange(event: React.ChangeEvent<HTMLInputElement>) {
        setSearchTerm(event.target.value);
    };

    async function handleDeleteUser(name: string, id: string) {
        const confirm = window.confirm(`Tem certeza que deseja excluir o usuário: ${name}?`)
        if (confirm) {
            try {
                await whatsapp.get(`/delete-session/${id}`, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    }
                })

                alert("Sessão deletada e finalizada com sucesso");
                return refresh();
            } catch (err) {
                console.error(err);
                alert("Erro ao deletar sessão do usuário")
            }
        }
    }

    async function handleLogoutSession(name: string, id: string) {
        const confirm = window.confirm(`Tem certeza que deseja deslogar a sessao do usuario: ${name}?`)
        if (confirm) {
            try {
                await api.get(`/delete-user/${id}`, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    }
                })

                alert("Usuário deletado com sucesso");
                return refresh();
            } catch (err) {
                console.error(err);
                alert("Erro ao deletar usuário")
            }
        }
    }

    const filteredUsers = users.filter((user) =>
        user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        user.email.toLowerCase().includes(searchTerm.toLowerCase())
    );

    async function handleChangeUserStatus(id: string, status: boolean) {
        const confirm = window.confirm(`Tem certeza que deseja ${status ? "desabilitar" : "habilitar"} esse usuário?`)
        if (confirm) {
            try {
                const { data } = await api.post(`/change-user-status/${id}`, { status: !status }, {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                })

                console.log(data);

                setUsers((prevUsers) =>
                    prevUsers.map((user) =>
                        user.id === id ? { ...user, enabled: !status } : user
                    )
                );

                if (!status) {
                    alert("Usuário Habilitado com sucesso!")
                } else {
                    alert("Usuário desabilitado com sucesso!")
                }
            } catch (err) {
                alert("Erro ao alterar o status do usuário");
                console.error(err);
            }
        }
    }

    return (
        <div className={styles.Main}>
            <h1>Usuários</h1>
            <header>
                <div>
                    <input
                        type="text"
                        placeholder="Pesquisar usuário"
                        value={searchTerm}
                        onChange={handleSearchChange}
                    />
                    <CiSearch size={24} color="#000" />
                </div>
                <Link href={`/dashboard/admin/create`}>Novo usuário</Link>
            </header>
            <table className={styles.Users}>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Mensagens Enviadas</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    {filteredUsers.map((user, index) => (
                        <tr key={user.id}>
                            <td>{index + 1}</td>
                            <td>{user.name}</td>
                            <td>{user.email}</td>
                            <td>{user.sendedMsg} / {user.msgLimit}</td>
                            <td className={styles.functions}>
                                <Link href={`/dashboard/admin/users/${user.id}`}>Editar</Link>
                                <button
                                    onClick={() => handleChangeUserStatus(user.id, user.enabled)}
                                    style={user.enabled ? { background: '#da251d' } : { background: '#00923f' }}
                                >
                                    {user.enabled ? "Desativar" : "Ativar"}
                                </button>
                                <button onClick={() => handleLogoutSession(user.name, user.id)} className={styles.delete_session}>
                                    Deletar Sessão
                                </button>
                                <button onClick={() => handleDeleteUser(user.name, user.id)} className={styles.delete_user}>
                                    <FaTrash size={16} color="#000" />
                                </button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}