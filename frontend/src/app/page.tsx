import Link from "next/link";

import { PublicHeader } from "@/components/PublicHeader";
import { LoginCard } from "@/components/LoginCard";

import styles from './page.module.scss';

export default function Home(){
  return(
    <main className={styles.Main}>
      <PublicHeader />
      <div className={styles.Content}>
        <LoginCard />
        <footer>
          <p>Direitos reservados a <Link href="https://globalnetsis.com.br" target="_blank" rel="noopener noreferrer">globalnetsis.com.br</Link></p>
        </footer>
      </div>
    </main>
  )
}