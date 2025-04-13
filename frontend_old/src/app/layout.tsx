import type { Metadata } from "next";
import localFont from "next/font/local";
import { AppWraper } from "@/context";
import "./globals.css";

const trebuchet = localFont({ src: './fonts/trebuchet.woff2' })

export const metadata: Metadata = {
  title: "GNSWhatsSender",
  description: "Software de envio de mensagens!",
  authors: [
    { name: 'Caio Gabriel', url: 'https://github.com/caiokronuz' }
  ]
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="pt-BR">
      <body className={trebuchet.className}>
        <AppWraper>
          {children}
        </AppWraper>
      </body>
    </html>
  );
}
