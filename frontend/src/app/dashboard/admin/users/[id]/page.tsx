"use client"
import api from '@/services/api';
import { useParams } from 'next/navigation';
import { FormEvent, useEffect, useState } from 'react';
import { useAppContext } from '@/context';
import { FaEye, FaEyeSlash } from "react-icons/fa";
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import styles from './styles.module.scss';

interface User {
  id: string,
  name: string,
  email: string,
  password: string,
  number: string,
  msgLimit: number,
  sendedMsg: number,
  role: string,
}
export default function EditUser() {
  const { push } = useRouter();
  const params = useParams();
  const id = params?.id;
  const { token } = useAppContext();

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
  const [isShowingPassword, setIsShowingPassword] = useState(false);

  async function handleUpdateUser(e: FormEvent) {
    e.preventDefault();

    if (!user.name || !user.email || !user.number) {
      alert("Dados faltando no cadastro, verifique e tente novamente")
      return;
    }

    if (user.number.length < 11) {
      alert("Número digitado é inválido, corriga para o padrão correto de 12 digitos: 22990909090")
      return;
    }

    if(user.msgLimit == 0){
      const confirm = window.confirm("Tem certeza que deseja cadastrar um usuario sem limite de mensagens?")
      if(!confirm){
        return;
      }
    }

    try {
      const { data } = await api.post(`/update-user/${id}`, {
        user,
      }, {
        headers: {
          Authorization: `Bearer ${token}`,
        }
      })

      alert(data.msg);
      push("/dashboard/admin/users");
    } catch (err) {
      alert("Erro ao atualizar o usuário");
      console.error(err);
    }
  }

  async function handleResetSendedCount(e: FormEvent) {
    e.preventDefault();

    const confirm = window.confirm("Tem certeza que deseja resetar o contador de mensagens?")
    if (confirm) {
      try {
        await api.get(`/reset-sended/${id}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          }
        })

        setUser({ ...user, sendedMsg: 0 })

        alert("Contador resetado com sucesso!")
      } catch (err) {
        console.error(err);
      }
    }
  }

  useEffect(() => {
    async function getUser() {
      const { data } = await api.get(`/get-user/${id}`, {
        headers: {
          Authorization: `Bearer ${token}`
        }
      })

      data.user.password = '';

      setUser(data.user);
    }

    getUser();
  }, [])

  return (
    <div className={styles.Main}>
      <h1>Editar Usuário</h1>
      <form onSubmit={handleUpdateUser}>
        <div>
          <label>Nome</label>
          <input type="text" value={user?.name} onChange={e => setUser({ ...user, name: e.target.value })} placeholder="Nome Completo"/>
        </div>
        <div>
          <label>Email/Login</label>
          <input type="email" value={user?.email} onChange={e => setUser({ ...user, email: e.target.value })} placeholder="email@exemplo.com.br" />
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
          <input type="text" value={user?.number} onChange={e => setUser({ ...user, number: e.target.value })} placeholder="22990909090"/>
        </div>
        <div>
          <label>Limite de Mensagens</label>
          <input type="number" value={user?.msgLimit} onChange={e => setUser({ ...user, msgLimit: Number(e.target.value) })} />
        </div>
        <div>
          <label>Total de Mensagens Enviadas</label>
          <div>
            <input type="number" value={user?.sendedMsg} disabled />
            <button onClick={handleResetSendedCount}>Resetar</button>
          </div>
        </div>
        <div>
          <label>Tipo de Usuário</label>
          <select onChange={e => setUser({ ...user, role: e.target.value })} value={user?.role}>
            <option value="nu">Normal</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div className={styles.buttons}>
          <button type="submit" style={{ background: '#00923f' }}>Salvar</button>
          <Link href="/dashboard/admin/users" style={{ background: '#da251d' }}>Cancelar</Link>
        </div>
      </form>
    </div>
  );
};
