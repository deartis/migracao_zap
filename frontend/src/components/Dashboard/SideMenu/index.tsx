import Image from 'next/image';
import { useAppContext } from '@/context';
import Link from 'next/link';
import { GiHamburgerMenu } from "react-icons/gi";
import { GoTriangleDown, GoTriangleUp } from "react-icons/go";
import { FaQrcode } from "react-icons/fa";
import { FaGear } from "react-icons/fa6";
import { CiLogout } from "react-icons/ci";
import { MdAdminPanelSettings } from "react-icons/md";
import { useRouter } from 'next/navigation';

import styles from './SideMenu.module.scss';

import dashIco from '@/public/dashboard.png'
import MsgIco from '@/public/msgs.png'

import whats1 from '@/public/whats1.png'
import whats2 from '@/public/whats2.png'
import whats3 from '@/public/whats3.png'
import historic from '@/public/historic.png'
import { useState } from 'react';

export function SideMenu() {

    const { isSideMenuOpen, setIsSideMenuOpen, isAdmin, setOpenConnectionModal, connectionStatus } = useAppContext();
    const [isMenuContentOpen, setIsMenuContentOpen] = useState({
        dashboard: true,
        messages: true,
        historic: true,
        config: true,
    })

    const { push } = useRouter();

    async function handleLogout() {
        const confirm = window.confirm("Tem certeza que deseja deslogar?")
        if (confirm) {
            await localStorage.removeItem("Authorization");
            return push("/")
        }
    }



    if (isSideMenuOpen) {
        return (
            <nav className={styles.SideMenu}>
                <div className={styles.Bar} />
                <div className={styles.Content}>
                    <div>
                        <p className={styles.Title} style={{ color: '#dc7621' }}>
                            {!isMenuContentOpen.dashboard && <Image src={dashIco} alt="Icone Dashboard" />}
                            Dashboard
                            {isMenuContentOpen.dashboard ?
                                <GoTriangleDown onClick={() => setIsMenuContentOpen({ ...isMenuContentOpen, dashboard: !isMenuContentOpen.dashboard })} size={18} color='#000' />
                                :
                                <GoTriangleUp onClick={() => setIsMenuContentOpen({ ...isMenuContentOpen, dashboard: !isMenuContentOpen.dashboard })} size={18} color='#000' />
                            }
                        </p>
                        {isMenuContentOpen.dashboard &&
                            (<ul>
                                <li>
                                    <Link href="/dashboard">
                                        <Image src={dashIco} alt="Icone Dashboard" />
                                        Inicio
                                    </Link>
                                </li>
                            </ul>)
                        }
                    </div>
                    <div>
                        <p className={styles.Title} style={{ color: '#00923f' }}>
                            {!isMenuContentOpen.messages && <Image src={whats1} alt="Icone Para Envio de Mensagens em Massa por XLSX/CSV" />}
                            Mensagem
                            {isMenuContentOpen.messages ?
                                <GoTriangleDown onClick={() => setIsMenuContentOpen({ ...isMenuContentOpen, messages: !isMenuContentOpen.messages })} size={18} color='#000' />
                                :
                                <GoTriangleUp onClick={() => setIsMenuContentOpen({ ...isMenuContentOpen, messages: !isMenuContentOpen.messages })} size={18} color='#000' />
                            }
                        </p>
                        {isMenuContentOpen.messages && (
                            <ul>
                                <li>
                                    <Link href="/dashboard/message/send/from-sheet">
                                        <Image src={whats1} alt="Icone Para Envio de Mensagens em Massa por XLSX/CSV" />
                                        Em massa: xlsx/csv
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/dashboard/message/send/from-contacts">
                                        <Image src={whats2} alt="Icone Para Envio de Mensagens em Massa por Contatos" />
                                        Em massa: Contatos do telefone
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/dashboard/message/send/single-contact">
                                        <Image src={whats3} alt="Icone Para Envio de Mensagem para um Unico Número" />
                                        Unico número
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/dashboard/message/live">
                                        <Image src={MsgIco} alt="Icone Para Visualizar as Respostas dos Cientes" />
                                        Respostas &#40;Beta&#41;
                                    </Link>
                                </li>
                            </ul>
                        )}
                    </div>
                    <div>
                        <p className={styles.Title} style={{ color: 'purple' }}>
                            {!isMenuContentOpen.historic && <Image src={historic} alt="Icone Para Visualizar o Historico" />}
                            Histórico de Envios
                            {isMenuContentOpen.historic ?
                                <GoTriangleDown onClick={() => setIsMenuContentOpen({ ...isMenuContentOpen, historic: !isMenuContentOpen.historic })} size={18} color='#000' />
                                :
                                <GoTriangleUp onClick={() => setIsMenuContentOpen({ ...isMenuContentOpen, historic: !isMenuContentOpen.historic })} size={18} color='#000' />
                            }
                        </p>
                        {isMenuContentOpen.historic && (
                            <ul>
                                <li>
                                    <Link href="/dashboard/message/historic">
                                        <Image src={historic} alt="Icone Para Visualizar o Historico" />
                                        Registro de Envio
                                    </Link>
                                </li>
                            </ul>
                        )}
                    </div>
                    <div>
                        <p className={styles.Title} style={{ color: 'red' }}>
                            {!isMenuContentOpen.config && <FaGear size={20} color="#000" />}
                            Configurações
                            {isMenuContentOpen.config ?
                                <GoTriangleDown onClick={() => setIsMenuContentOpen({ ...isMenuContentOpen, config: !isMenuContentOpen.config })} size={18} color='#000' />
                                :
                                <GoTriangleUp onClick={() => setIsMenuContentOpen({ ...isMenuContentOpen, config: !isMenuContentOpen.config })} size={18} color='#000' />
                            }
                        </p>
                        {isMenuContentOpen.config && (
                            <ul>
                                <li>
                                    <Link href="/dashboard/user">
                                        <FaGear size={20} color="#000" />
                                        Configurações
                                    </Link>
                                </li>
                                {isAdmin &&
                                    <li>
                                        <Link href="/dashboard/admin/users" onClick={() => localStorage.removeItem("Authorization")}>
                                            <MdAdminPanelSettings size={20} color="#000" />
                                            Área Administrativa
                                        </Link>
                                    </li>
                                }
                                {(isAdmin && connectionStatus != "connected") &&
                                    <li>
                                        <button onClick={() => setOpenConnectionModal(true)}>
                                            <FaQrcode size={20} color="#000" />
                                            Conectar ao Whatsapp
                                        </button>
                                    </li>
                                }
                                <li>
                                    <button onClick={handleLogout}>
                                        <CiLogout size={20} color="#000" />
                                        Deslogar
                                    </button>
                                </li>
                            </ul>
                        )}
                    </div>
                </div>
            </nav>
        )
    }

    if (!isSideMenuOpen) {
        return (
            <nav className={styles.SideMenuClosed}>
                <div className={styles.Bar} />
                <div className={styles.Content}>
                    <div>
                        <GiHamburgerMenu size={20} color="#52aedd" onClick={() => setIsSideMenuOpen(!isSideMenuOpen)} />
                    </div>
                    <div>
                        <Link href="/dashboard">
                            <Image src={dashIco} alt="Inicio do Dashboard" />
                        </Link>
                    </div>
                    <div>
                        <Link href="/dashboard/message/send/from-sheet">
                            <Image src={whats1} alt="Icone para Mensagens em Massa por XLSX/CSV" />
                        </Link>
                    </div>
                    <div>
                        <Link href="/dashboard/message/send/from-contacts">
                            <Image src={whats2} alt="Icone para Mensagens em Massa por Contatos" />
                        </Link>
                    </div>
                    <div>
                        <Link href="/dashboard/message/send/single-contact">
                            <Image src={whats3} alt="Icone para Mensagens para Número Único" />
                        </Link>
                    </div>
                    <div>
                        <Link href="/dashboard/message/live">
                            <Image src={MsgIco} alt="Icone para Responder Mensagens" />
                        </Link>
                    </div>
                    <div>
                        <Link href="/dashboard/message/historic">
                            <Image src={historic} alt="Icone para Historico" />
                        </Link>
                    </div>
                    <div>
                        <Link href="/dashboard/user">
                            <FaGear size={20} color="#000" />
                        </Link>
                    </div>
                    {(isAdmin && connectionStatus != "connected") &&
                        <div>
                            <button onClick={() => setOpenConnectionModal(true)}>
                                <FaQrcode size={20} color="#000" />
                            </button>
                        </div>
                    }
                    {isAdmin &&
                        <div>
                            <Link href="/dashboard/admin/users">
                                <MdAdminPanelSettings size={20} color="#000" />
                            </Link>
                        </div>
                    }
                    <div>
                        <button onClick={handleLogout}>
                            <CiLogout size={20} color="#000" />
                        </button>
                    </div>
                </div>
            </nav>
        )
    }
}
