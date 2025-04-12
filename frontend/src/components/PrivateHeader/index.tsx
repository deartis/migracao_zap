import Image from 'next/image';
import styles from './PrivateHeader.module.scss'

import logoWS from '@/public/logo.png';
import logoGNS from '@/public/gns_logo.png'

export function PrivateHeader(){
    return(
       <header className={styles.Header}>
            <Image src={logoWS} alt="Logo GNS Whats Sender"/>
            <Image src={logoGNS} alt="Logo GlobalNetSis"/>
       </header>
    )
}