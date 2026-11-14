# Barbearia Sistema Pro

Site de agendamento + painel de gestão para barbearias, com app Android.
Laravel 12 · Tailwind · Alpine · Mercado Pago (PIX e cartão) · Capacitor.

## O que o cliente vê
- **Site** (`/`): serviços e preços, equipe, galeria, fidelidade, FAQ, mapa e WhatsApp.
- **Agendamento** (`/agendar`): serviço → profissional → horário livre → dados e pagamento
  (PIX, cartão ou pagar na barbearia). Aceita links diretos: `/agendar?servico=ID` e `/agendar?profissional=ID`.
- **Meu horário** (`/meu-horario/{token}`): confirmação, PIX com verificação automática,
  cancelamento, salvar no Google Agenda e cartão fidelidade.
- **App Android**: ver [`mobile/README.md`](mobile/README.md).

## O que a equipe vê (`/painel-login`)
- **Agenda**: dia a dia, por profissional, com ações (concluir e receber, faltou, cancelar, WhatsApp).
- **Indicadores**: faturamento do mês, ticket médio, faltas, clientes para chamar de volta.
- **Financeiro**: receitas/despesas, importação CSV, comissões (sobre atendimentos concluídos).
- **Admin**: serviços e preços, equipe, galeria, usuários e **Configurações**
  (nome, WhatsApp, endereço, horários, almoço, dias fechados, fidelidade).

## Rodar localmente
```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate --seed
npm install && npm run build
php artisan serve
php artisan test
```

## Deploy (Render)
O `Dockerfile` compila o front-end, instala as dependências e o `deployment/init.sh`
roda migrations, seed (idempotente), storage link, caches e o agendador de lembretes.

Variáveis importantes: `APP_KEY`, `APP_URL`, `DATABASE_URL`, `MERCADO_PAGO_PUBLIC_KEY`,
`MERCADO_PAGO_ACCESS_TOKEN`, `TWILIO_*` (lembretes), `MAIL_*` (e-mails).
Webhook do Mercado Pago: `https://SEU-SITE/webhooks/mercadopago`.
