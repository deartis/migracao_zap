"use client"
import styles from './LoginCard.module.scss';
import { FormEvent, useEffect, useState } from 'react';
import {useRouter} from 'next/navigation';
import api from '@/services/api';

export function LoginCard() {

    const router = useRouter();

    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");

    useEffect(() => {
        const token = localStorage.getItem("Authorization");
        if(token) return router.push("/dashboard")
    },[])

    async function handleLogin(e: FormEvent){
        e.preventDefault();

        if(!email || !password) return alert("Preencha seus dados!");

        const {data} = await api.post("/login", {
            email,
            password
        })

        if(!data.token) return alert(data.error);

        await localStorage.setItem("Authorization", data.token);

        return router.push("/dashboard");

    }

    return (
        <div className={styles.Card}>
            <div className={styles.Content}>
                <h1>Login</h1>
                <form onSubmit={handleLogin}>
                    <div>
                        <label htmlFor="email">Usuário:</label>
                        <input type="email" name="email" id="email" placeholder='usuario@email.com' onChange={e => setEmail(e.target.value)}/>
                    </div>
                    <div>
                        <label htmlFor="pass">Senha:</label>
                        <input type="password" name="pass" id="pass" placeholder='Sua senha' onChange={e => setPassword(e.target.value)}/>
                    </div>
                    <button type="submit">Entrar</button>
                </form>
            </div>
        </div>
    )
}