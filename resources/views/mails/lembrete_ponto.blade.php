<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <h2>Olá!</h2>
    <p>Notamos que não foi efetuado nenhum registo de ponto no dia ou que o ponto não foi fechado para o dia <strong>{{ $dataFalta }}</strong>.</p>
    
    <p>Por favor, aceda ao sistema para regularizar a sua situação ou fale com o administrador.</p>
    
    <div style="margin: 25px 0;">
        <a href="{{ route('userlogs') }}" 
           style="background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
            Adicionar Registo de Ponto
        </a>
    </div>

    
    <br>
    <p>Este é um lembrete automático. Se já regularizou, ignore esta mensagem.</p>
</body>
</html>