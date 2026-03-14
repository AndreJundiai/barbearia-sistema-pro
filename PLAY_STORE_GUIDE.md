# Guia: Publicando seu App na Google Play Store

Como o seu sistema é um **PWA (Progressive Web App)**, a melhor forma de publicá-lo na Google Play é usando uma **Trusted Web Activity (TWA)**. Isso cria um APK que "embrulha" seu site, e o melhor: toda vez que você atualizar o site, o app atualiza automaticamente!

## Passo 1: Requisitos
1.  **Conta de Desenvolvedor Google Play**: Você precisa criar uma conta no [Google Play Console](https://play.google.com/console/signup) (taxa única de $25).
2.  **Site Online**: O site já deve estar publicado (ex: no Render) com HTTPS.

## Passo 2: Gerar o APK (O Jeito Fácil)
A forma mais simples de gerar seu APK é usando o **PWABuilder**:
1.  Acesse [PWABuilder.com](https://www.pwabuilder.com/).
2.  Cole a URL do seu site (ex: `https://barbearia-pro-sistema.onrender.com`).
3.  Clique em **"Package for Stores"** e selecione **Android**.
4.  Clique em **"Generate"**. Isso vai baixar um arquivo `.zip` com o seu APK e o código fonte do Android.

## Passo 3: Configurar o Asset Links
Para que a barra de endereços do navegador suma dentro do app, o Google precisa confirmar que o site é seu:
1.  No arquivo baixado do PWABuilder, procure por um arquivo chamado `assetlinks.json` (ou as informações contidas nele).
2.  Abra o arquivo `public/.well-known/assetlinks.json` no seu código.
3.  Substitua os valores de `package_name` e `sha256_cert_fingerprints` pelos valores que o PWABuilder te deu.
4.  Faça o `git push` dessa alteração para o GitHub.

## Passo 4: Publicar no Console
1.  No [Google Play Console](https://play.google.com/console), crie um novo aplicativo.
2.  Preencha as informações (Nome, Descrição, Ícones).
3.  Vá em **"Produção"** e faça upload do arquivo `.aab` (Android App Bundle) que o PWABuilder gerou.
4.  Envie para revisão! (O Google costuma demorar de 2 a 7 dias para aprovar).

---

### Dicas Importantes:
- **Ícones**: Use o ícone de alta qualidade que gerei (`public/icons/icon-512.png`).
- **Privacidade**: O sistema de agendamento coleta dados (nome/telefone), então você precisará criar uma **Política de Privacidade**. 
- **Mudanças Visuais**: Tudo o que você mudar no site (cores, textos, horários) aparecerá **instantaneamente** no aplicativo dos clientes sem você precisar enviar uma nova versão para a loja.
