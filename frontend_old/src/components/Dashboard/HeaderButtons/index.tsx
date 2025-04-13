"use client"
import { GiHamburgerMenu } from "react-icons/gi";
import { HiUserCircle } from "react-icons/hi2";
import { useAppContext } from "@/context";

import styles from './HeaderButton.module.scss';

export function HeaderButtons(){

    const { isSideMenuOpen, setIsSideMenuOpen, connectionStatus, name} = useAppContext();

    return(
        <header className={styles.Header}>
            <div className={styles.sideButton} style={isSideMenuOpen ? {} : {visibility: 'hidden'}}>
                <GiHamburgerMenu size={24} color="#52aedd" onClick={() => setIsSideMenuOpen(!isSideMenuOpen)}/>
            </div>
            <div>
                {!connectionStatus && <span className={styles.Circle} style={{backgroundColor: "yellow"}}></span>}
                {connectionStatus == "not_connected" && <span className={styles.Circle} style={{backgroundColor: "red"}}></span>}
                {connectionStatus == "connected" && <span className={styles.Circle} style={{backgroundColor: "green"}}></span>}
                {!connectionStatus && <p>Verificando</p>}
                {connectionStatus == "not_connected" && <p>Offline</p>}
                {connectionStatus == "connected" && <p>Online</p>}
                <HiUserCircle size={48} color="black"/>
                <p>{name}</p>
            </div>
        </header>
    )
}