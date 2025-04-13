import { Oval } from 'react-loader-spinner';
import styles from './LoadingModal.module.scss';

export function LoadingModal() {
    return (
        <div className={styles.External}>
            <main className={styles.Main}>
                <h1>Conectando ao Whatsapp...</h1>
                <div className={styles.SpinnerContainer}>
                    <Oval
                        height={80}
                        width={80}
                        color="#4fa94d"
                        ariaLabel="loading"
                        secondaryColor="#4fa94d"
                        strokeWidth={2}
                        strokeWidthSecondary={2}
                    />
                </div>
            </main>
        </div>
    );
}