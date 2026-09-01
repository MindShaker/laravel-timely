<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #111827; padding: 28px 32px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 20px; font-weight: 600; letter-spacing: -.3px; }
        .header p { color: #9ca3af; margin: 4px 0 0; font-size: 13px; }
        .body { padding: 28px 32px; }
        .body p { color: #374151; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
        .days { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 14px 18px; margin: 20px 0; }
        .days p { margin: 0 0 8px; font-size: 13px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .5px; }
        .days ul { margin: 0; padding: 0 0 0 18px; }
        .days ul li { color: #111827; font-size: 14px; line-height: 1.8; }
        .cta { display: inline-block; background: #2563eb; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-size: 15px; font-weight: 600; margin-top: 8px; }
        .footer { padding: 16px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; }
        .footer p { color: #9ca3af; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Timely — Planeamento</h1>
            <p>Mindshaker · Lembrete semanal</p>
        </div>
        <div class="body">
            <p>Olá <strong>{{ $user->name }}</strong>,</p>
            <p>
                O teu calendário de planeamento tem
                <strong>{{ count($unfilledDays) }} {{ count($unfilledDays) === 1 ? 'dia' : 'dias' }} por preencher</strong>
                nas próximas duas semanas:
            </p>
            <div class="days">
                <p>Dias em falta</p>
                <ul>
                    @foreach ($unfilledDays as $day)
                        <li>{{ \Carbon\Carbon::parse($day)->isoFormat('dddd, D [de] MMMM') }}</li>
                    @endforeach
                </ul>
            </div>
            <p>Acede ao Timely para registar o teu planeamento antes de começares a trabalhar.</p>
            <a href="{{ $calendarUrl }}" class="cta">Abrir calendário</a>
        </div>
        <div class="footer">
            <p>Este email foi enviado automaticamente todas as segundas-feiras às 6h. Não replies a este email.</p>
        </div>
    </div>
</body>
</html>
