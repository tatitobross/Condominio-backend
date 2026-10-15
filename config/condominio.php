<?php

return [
    // Número máximo de cuotas vencidas que impide autorizar visitas nuevas
    'max_cuotas_vencidas' => 2,

    // Día del mes en que vence la cuota (por defecto día 5)
    'dia_vencimiento_cuota' => 5,

    // Tiempo de expiración del token Sanctum (en minutos)
    'token_expiration_minutes' => 60,
];
?>
