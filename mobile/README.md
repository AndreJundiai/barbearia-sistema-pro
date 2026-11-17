# App Android

O app é o próprio site da barbearia empacotado com [Capacitor](https://capacitorjs.com).
Ele abre o site publicado, então **qualquer mudança no site aparece no app na hora**,
sem precisar mandar versão nova para a Play Store.

- Links do próprio site abrem dentro do app.
- WhatsApp, Google Maps, Instagram etc. abrem nos apps do celular.
- Sem internet, aparece uma tela amigável de "sem conexão".

## Gerar o app (sem instalar nada no computador)

1. No GitHub, vá em **Settings → Secrets and variables → Actions → Variables** e crie
   `APP_URL` com o endereço do site (ex.: `https://barbearia-pro-sistema.onrender.com`).
2. Vá em **Actions → App Android → Run workflow**.
3. Ao terminar (~5 min), baixe em *Artifacts*:
   - `app-teste-apk`: instale direto no celular Android para testar;
   - `play-store-aab`: arquivo para enviar à Google Play.

## Assinatura para a Play Store (uma vez só)

A Play Store exige que o `.aab` seja assinado. Crie a chave no computador (precisa do Java):

```bash
keytool -genkey -v -keystore barbearia.jks -keyalg RSA -keysize 2048 -validity 10000 -alias barbearia
base64 -w0 barbearia.jks > barbearia.jks.base64   # no Mac: base64 -i barbearia.jks -o barbearia.jks.base64
```

Depois, em **Settings → Secrets and variables → Actions → Secrets**, crie:

| Secret | Valor |
| --- | --- |
| `ANDROID_KEYSTORE_BASE64` | conteúdo do arquivo `barbearia.jks.base64` |
| `ANDROID_KEYSTORE_PASSWORD` | senha da keystore |
| `ANDROID_KEY_ALIAS` | `barbearia` |
| `ANDROID_KEY_PASSWORD` | senha da chave |

> Guarde o `barbearia.jks` e as senhas em lugar seguro. Sem eles você não consegue
> publicar atualizações do app. (Ou ative o "Play App Signing" no Console.)

## Publicar na Google Play

1. Crie a conta no [Play Console](https://play.google.com/console/signup) (taxa única de US$ 25).
2. Crie o app, preencha nome, descrição, ícone (`public/icons/icon-512.png`) e capturas de tela.
3. Política de privacidade: use `https://SEU-SITE/privacidade` (já existe no site).
4. Em *Produção* (ou *Teste interno* primeiro), envie o `.aab`.

## Mudar nome, pacote ou ícone

- Nome/pacote: variáveis `APP_NAME` / `APP_ID` em `capacitor.config.ts`
  (o pacote **não pode mudar** depois de publicado).
- Ícones: `android/app/src/main/res/mipmap-*`.

## Rodar localmente (opcional, precisa do Android Studio)

```bash
cd mobile
npm install
APP_URL=https://seu-site.com npx cap sync android
npx cap open android
```
