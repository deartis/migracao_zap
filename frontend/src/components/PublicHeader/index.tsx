import styles from './PublicHeader.module.scss';
import Image from 'next/image';
import Link from 'next/link';

import logoImg from '@/public/logo.png';

export function PublicHeader(){
    return(
        <header className={styles.Header}>
            <Image src={logoImg} alt="Logo GNS Whats Sender" className={styles.logo}></Image>
            <ul>
                <li><Link href="/">Login</Link></li>
                <li>Planos</li>
                <li><a href="https://globalnetsis.com.br/#servicos" target="_blank" rel="noopener noreferrer">Soluções</a></li>
                <li><a href="https://globalnetsis.com.br/#quem-somos"target="_blank" rel="noopener noreferrer">Sobre Nós</a></li>
            </ul>
        </header>
    )
}