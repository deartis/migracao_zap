"use client"
import React, {createContext, Dispatch, useContext, useState} from 'react';

type Context = {
    isSideMenuOpen: boolean;
    setIsSideMenuOpen: Dispatch<React.SetStateAction<boolean>>;
    connectionStatus: string;
    setConnectionStatus: Dispatch<React.SetStateAction<string>>;
    openConnectionModal: boolean;
    setOpenConnectionModal: Dispatch<React.SetStateAction<boolean>>;
    token: string;
    setToken: Dispatch<React.SetStateAction<string>>;
    isAdmin: boolean;
    setIsAdmin: Dispatch<React.SetStateAction<boolean>>;
    idToDisconnect: string;
    setIdToDisconnect: Dispatch<React.SetStateAction<string>>;
    name: string;
    setName: Dispatch<React.SetStateAction<string>>;
}

const AppContext = createContext<Context>({
    isSideMenuOpen: true,
    setIsSideMenuOpen: () => {},
    connectionStatus: "",
    setConnectionStatus: () => {},
    openConnectionModal: false,
    setOpenConnectionModal: () => {},
    token: "",
    setToken: () => {},
    isAdmin: false,
    setIsAdmin: () => {},
    idToDisconnect: "",
    setIdToDisconnect: () => {},
    name: "",
    setName: () => {},
});

export function AppWraper({children}: {children: React.ReactNode}){
    const [isSideMenuOpen, setIsSideMenuOpen] = useState(true);
    const [connectionStatus, setConnectionStatus] = useState("");
    const [openConnectionModal, setOpenConnectionModal] = useState(false);
    const [token, setToken] = useState("");
    const [isAdmin, setIsAdmin] = useState(false);
    const [idToDisconnect, setIdToDisconnect] = useState("");
    const [name, setName] = useState("");

    return(
        <AppContext.Provider value={{
            isSideMenuOpen,
            setIsSideMenuOpen,
            connectionStatus,
            setConnectionStatus,
            openConnectionModal,
            setOpenConnectionModal,
            token,
            setToken,
            isAdmin,
            setIsAdmin,
            idToDisconnect,
            setIdToDisconnect,
            name,
            setName,
        }}>
            {children}
        </AppContext.Provider>
    )
}

export function useAppContext(){
    return useContext(AppContext);
}