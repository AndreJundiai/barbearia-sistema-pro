# Configuração do Sistema de Pagamento (Mercado Pago)

Para que a parte de pagamentos funcione de forma real (não apenas simulação), siga os passos abaixo:

## 1. Criar conta no Mercado Pago Developers
- Acesse [Mercado Pago Developers](https://www.mercadopago.com.br/developers/pt) e faça login.
- Crie uma "Aplicação" para obter suas credenciais (Public Key e Access Token).

## 2. Configurar Variáveis de Ambiente
No seu arquivo `.env`, adicione as seguintes chaves:
```env
MERCADO_PAGO_PUBLIC_KEY=sua_public_key_aqui
MERCADO_PAGO_ACCESS_TOKEN=seu_access_token_aqui
```

## 3. Instalar o SDK do Mercado Pago
No terminal, dentro da pasta do projeto, execute:
```bash
composer require mercadopago/dx-php
```

## 4. Integrar no `GuestBookingController.php`
Substitua a parte de "Simulação de processamento de pagamento" pelo código oficial do SDK. 

### Exemplo de fluxo para PIX:
```php
// Use o SDK do Mercado Pago
$client = new \MercadoPago\Client\Payment\PaymentClient();
$request = [
    "transaction_amount" => (float) $service->price,
    "description" => $service->name,
    "payment_method_id" => "pix",
    "payer" => [
        "email" => "cliente@email.com", // Adicione o campo de email no formulário
    ]
];
$payment = $client->create($request);
// Salve o QR Code e o link de pagamento no banco
```

## 5. Webhooks (Opcional, mas Recomendado)
Configure um endpoint de Webhook no Mercado Pago para que o seu sistema saiba automaticamente quando um pagamento foi aprovado, alterando o status do agendamento de `pending` para `paid`.

---
**Nota:** Atualmente o sistema está em modo **Simulação**. Isso significa que ele registra a transação no banco de dados como "paga" ou "pendente", mas não desconta dinheiro real nem gera QR Codes registrados no Banco Central.
