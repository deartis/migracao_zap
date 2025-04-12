"use client"
import { useAppContext } from "@/context"
import { FormEvent, useEffect, useState } from "react";
import { FaEye, FaEyeSlash } from "react-icons/fa";
import { useRouter } from "next/navigation";

import api from "@/services/api";

import styles from  './styles.module.scss';
import Link from "next/link";

export default function User(){

    const {push} = useRouter();

    interface User{
        id: string,
        name: string,
        email: string,
        password: string,
        number: string,
        msgLimit: number,
        sendedMsg: number,
        role: string,
    }

    const {token, setToken} = useAppContext();

    const [user, setUser] = useState<User>({
        id: '',
        name: '',
        email: '',
        password: '',
        number: '',
        msgLimit: 0,
        sendedMsg: 0,
        role: ''
    });
    
    const [isEditingPassword, setIsEditingPassword] = useState(false);
    const [isShowingPassword, setIsShowingPassword] = useState(false);

    useEffect(() => {
        async function getUser(){
            const {data} = await api.get('/get-user', {
                headers:{
                    Authorization: `Bearer ${token}`,
                }
            })

            data.user.password = "";

            setUser(data.user);
        }

        getUser();
    }, [])

    function handleChangeEditingPasswordStatus(e: FormEvent){
        e.preventDefault();

        if(isEditingPassword == false){
            setUser({...user, password: ''});
        }

        setIsEditingPassword(!isEditingPassword);
    }

    async function handleEditUser(e: FormEvent){
        e.preventDefault();
        
        if(!user.email){
            alert("Verifique os dados inputados e tente novamente");
        } 

        try{
            const {data} = await api.post('/update-user', {
                user,
            }, {
                headers:{
                    Authorization: `Bearer ${token}`, 
                }
            })
    
            localStorage.setItem("Authorization", data.token);
            setToken(data.token);

            alert("Dados atualizados com sucesso!")
            push("/dashboard");
        }catch(err){
            alert("Erro ao atualizar seus dados")
            console.error(err);
        }
    }

    return(
        <div className={styles.Main}>
            <h1>Suas informações</h1>
            <form onSubmit={handleEditUser}>
                <div>
                    <label>Nome</label>
                    <input type="text" value={user?.name} disabled/>
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" value={user?.email} onChange={e => setUser({...user, email: e.target.value})}/>
                </div>
                <div>
                    <label>Telefone</label>
                    <input type="text" value={user?.number} disabled/>
                </div>
                <div>
                    <label>Mensagens Enviadas</label>
                    <input type="text" value={`${user.sendedMsg} / ${user.msgLimit}`} disabled/>
                </div>
                <button onClick={handleChangeEditingPasswordStatus}>{isEditingPassword ? "Cancelar Edição":"Editar Senha"}</button>
                {isEditingPassword && (
                    <>
                        <div>
                            <label>Nova senha</label>
                            <div className={styles.inputPassword}>
                                <input type={isShowingPassword ? "text":"password"} onChange={e => setUser({...user, password: e.target.value})}/>
                                {isShowingPassword ? 
                                    <FaEyeSlash size={24} color="#000" onClick={() => setIsShowingPassword(false)}/>
                                    :
                                    <FaEye size={24} color="#000" onClick={() => setIsShowingPassword(true)}/>}
                            </div>
                        </div>
                    </>
                )}
                <div className={styles.buttons}>
                    <button type="submit" style={{backgroundColor: '#00923f'}}>Salvar edições</button>
                    <Link href="/dashboard" style={{backgroundColor: '#da251d'}}>Cancelar</Link>
                </div>
            </form>
        </div>
    )
}