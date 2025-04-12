"use client"

import api from "@/services/api";
import Link from "next/link"
import { FormEvent, useState } from "react";
import { useAppContext } from "@/context";
import {FaEye, FaEyeSlash} from 'react-icons/fa'
import styles from './styles.module.scss';
import { useRouter } from "next/navigation";


interface User {
    name: string,
    email: string,
    password: string,
    number: string,
    msgLimit: number,
    role: string,
}

export default function CreateUser() {

    const {push} = useRouter();

    const { token } = useAppContext();

    const [user, setUser] = useState<User>({
        name: '',
        email: '',
        password: '',
        number: '',
        msgLimit: 0,
        role: ''
    });
    const [isShowingPassword, setIsShowingPassword] = useState(false);

    async function createUser(e: FormEvent) {
        e.preventDefault();

        if(!user.name || !user.email || !user.number){
            alert("Dados faltando no cadastro, verifique e tente novamente")
            return;
        }

        if(user.number.length < 11){
            alert("Número digitado é inválido, corriga para o padrão correto de 13 digitos: 22990909090")
            return;
        }

        if(user.msgLimit == 0){
            const confirm = window.confirm("Tem certeza que deseja cadastrar um usuario sem limite de mensagens?")
            if(!confirm){
              return;
            }
          }

        try{
            console.log(user);

            if (user.role == "") {
                user.role = "nu";
            }

            const { data } = await api.post('/register', { user }, {
                headers: {
                    Authorization: `Bearer ${token}`,
                }
            })

            alert("Usuário criado com sucesso");
            push("/dashboard/admin/users")

        console.log(data);
        }catch(err){
            alert("Erro ao criar novo usuário");
            console.error(err);
        }
    }

    return (
        <div className={styles.Main}>
            <h1>Cadastrar novo usuário</h1>
            <form onSubmit={createUser}>
                <div>
                    <label>Nome</label>
                    <input type="text" value={user.name} onChange={e => setUser({ ...user, name: e.target.value })} placeholder="Nome Completo"/>
                </div>
                <div>
                    <label>Email/Login</label>
                    <input type="email" value={user.email} onChange={e => setUser({ ...user, email: e.target.value })} placeholder="email@exemplo.com.br" />
                </div>
                <div>
                    <label>Senha</label>
                    <div className={styles.inputPassword}>
                        <input type={isShowingPassword ? "text" : "password"} onChange={e => setUser({ ...user, password: e.target.value })} />
                        {isShowingPassword ?
                            <FaEyeSlash size={24} color="#000" onClick={() => setIsShowingPassword(false)} />
                            :
                            <FaEye size={24} color="#000" onClick={() => setIsShowingPassword(true)} />}
                    </div>
                </div>
                <div>
                    <label>Telefone</label>
                    <input type="text" value={user.number} onChange={e => setUser({ ...user, number: e.target.value })} placeholder="22990909090"/>
                </div>
                <div>
                    <label>Limite de Mensagens</label>
                    <input type="number" value={user.msgLimit} onChange={e => setUser({ ...user, msgLimit: Number(e.target.value) })} />
                </div>
                <div>
                    <label>Tipo de Usuário</label>
                    <select onChange={e => setUser({ ...user, role: e.target.value })} value={user.role || "nu"}>
                        <option value="nu">Normal</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div className={styles.buttons}>
                    <button type="submit" style={{background: '#00923f'}}>Cadastrar</button>
                    <Link href="/dashboard/admin/users" style={{background: '#da251d'}}>Cancelar</Link>
                </div>
            </form>
        </div>
    )
}