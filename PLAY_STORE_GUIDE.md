# Guia: App Android na Google Play

O app Android fica na pasta [`mobile/`](mobile/README.md) e é gerado automaticamente
pelo GitHub Actions (**Actions → App Android → Run workflow**). Veja o passo a passo
completo — gerar o APK de teste, assinar e publicar — em [`mobile/README.md`](mobile/README.md).

Resumo:
1. Configure a variável `APP_URL` no GitHub com o endereço do site.
2. Rode o workflow e instale o `app-teste-apk` no celular para testar.
3. Crie a chave de assinatura e cadastre os secrets.
4. Envie o `play-store-aab` no Play Console. Política de privacidade: `https://SEU-SITE/privacidade`.

Como o app abre o site publicado, mudanças no site aparecem no app na hora.
