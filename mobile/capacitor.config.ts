import type { CapacitorConfig } from '@capacitor/cli';

// URL do site publicado. O app abre esse endereço, então qualquer mudança no
// site (preços, horários, visual) aparece no app sem publicar nova versão.
// Para trocar: defina APP_URL antes do "npx cap sync" (o GitHub Actions já faz isso).
const appUrl = process.env.APP_URL || 'https://barbearia-pro-sistema.onrender.com';

const config: CapacitorConfig = {
    appId: process.env.APP_ID || 'com.barbeariaelite.app',
    appName: process.env.APP_NAME || 'Barbearia Elite',
    webDir: 'www',
    server: {
        url: appUrl,
        cleartext: false,
        // Só o próprio site abre dentro do app; WhatsApp, Maps, Instagram e
        // Mercado Pago abrem nos apps/navegador do celular.
        allowNavigation: [new URL(appUrl).host],
        errorPath: 'offline.html',
    },
    android: {
        backgroundColor: '#0b0b0c',
    },
};

export default config;
