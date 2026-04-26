import type { Metadata } from 'next';
import { Lora, Lato } from 'next/font/google';
import './globals.css';
import { Header } from '@/components/layout/Header';
import { Footer } from '@/components/layout/Footer';
import { MetricsTracker } from '@/components/layout/MetricsTracker';

const lora = Lora({
  subsets: ['latin'],
  display: 'swap',
  variable: '--font-lora',
  weight: ['400', '500', '600', '700'],
  style: ['normal', 'italic'],
});

const lato = Lato({
  subsets: ['latin'],
  display: 'swap',
  variable: '--font-lato',
  weight: ['300', '400', '700', '900'],
});

export const metadata: Metadata = {
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL || 'http://localhost:3001'),
  title: {
    default: 'EAIFR — East African Institute for Fundamental Research',
    template: '%s · EAIFR',
  },
  description:
    'A UNESCO Category II institute for basic sciences in Africa. A partnership of the Government and University of Rwanda with ICTP, established in 2018 in Kigali.',
  openGraph: {
    type: 'website',
    locale: 'en_US',
    siteName: 'EAIFR',
  },
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" className={`${lora.variable} ${lato.variable}`}>
      <body className="min-h-screen flex flex-col">
        <Header />
        <main className="flex-1">{children}</main>
        <Footer />
        <MetricsTracker />
      </body>
    </html>
  );
}
