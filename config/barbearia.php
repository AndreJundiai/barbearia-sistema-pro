<?php

/*
| Valores padrão da barbearia. Tudo aqui pode ser alterado pelo painel
| (Admin > Configurações) sem precisar de novo deploy.
*/

return [
    'shop_name' => env('SHOP_NAME', 'Barbearia Elite'),
    'slogan' => env('SHOP_SLOGAN', 'Estilo & Tradição'),
    'about' => 'Onde a elegância clássica encontra a modernidade. Profissionais experientes, ambiente climatizado e atendimento com hora marcada — sem fila e sem espera.',
    'whatsapp' => env('SHOP_WHATSAPP', ''),        // somente números, com DDI: 5511999999999
    'instagram' => env('SHOP_INSTAGRAM', ''),      // usuário sem @
    'address' => env('SHOP_ADDRESS', ''),
    'city' => env('SHOP_CITY', ''),

    // Agenda
    'open_time' => '09:00',
    'close_time' => '19:30',
    'lunch_start' => '12:00',
    'lunch_end' => '14:00',
    'slot_interval' => 30,      // minutos entre horários
    'closed_days' => '0',       // dias fechados (0=domingo ... 6=sábado), separados por vírgula
    'booking_days_ahead' => 30, // até quantos dias à frente o cliente pode agendar
    'min_notice_minutes' => 30, // antecedência mínima para agendar
    'cancel_notice_hours' => 2, // até quantas horas antes o cliente pode cancelar pelo link

    // Pagamento e fidelidade
    'allow_pay_later' => '1',
    'loyalty_target' => 10,     // a cada X atendimentos, 1 grátis
];
