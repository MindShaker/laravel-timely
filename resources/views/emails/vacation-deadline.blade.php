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
        .summary { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .summary .label { font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .5px; margin: 0 0 8px; }
        .progress-bar-bg { background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden; margin: 6px 0 10px; }
        .progress-bar-fill { background: #2563eb; height: 8px; border-radius: 4px; }
        .summary .count { font-size: 22px; font-weight: 700; color: #111827; }
        .summary .count span { font-size: 14px; font-weight: 400; color: #6b7280; }
        .deadline { background: #fef3c7; border: 1px solid #fcd34d; border-radius: 6px; padding: 14px 18px; margin: 20px 0; font-size: 14px; color: #92400e; }
        .deadline strong { color: #78350f; }
        .cta { display: inline-block; background: #2563eb; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-size: 15px; font-weight: 600; margin-top: 8px; }
        .footer { padding: 16px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; }
        .footer p { color: #9ca3af; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Timely — Férias</h1>
            <p>Mindshaker · Lembrete de planeamento</p>
        </div>
        <div class="body">
            <p>Olá <strong>{{ $user->name }}</strong>,</p>
            <p>Fevereiro é o momento certo para planeares as tuas férias. Tens até <strong>{{ $deadlineDate }}</strong> para registares os dias — depois dessa data, só um administrador poderá fazer alterações.</p>

            <div class="summary">
                <p class="label">Férias marcadas em {{ now()->year }}</p>
                <p class="count">{{ $vacationCount }} <span>/ {{ $allowance }} dias</span></p>
                @php $pct = min(100, round($vacationCount / $allowance * 100)); @endphp
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: {{ $pct }}%"></div>
                </div>
                @if ($vacationCount === 0)
                    <p style="margin:0;font-size:13px;color:#b45309;">Ainda não tens nenhum dia de férias marcado.</p>
                @else
                    <p style="margin:0;font-size:13px;color:#6b7280;">Faltam {{ $allowance - $vacationCount }} dias para completares o teu plafond.</p>
                @endif
            </div>

            <div class="deadline">
                <strong>Prazo limite: {{ $deadlineDate }}</strong><br>
                A partir desta data, os dias de férias ficam bloqueados para edição. Entra em contacto com um administrador caso precisas de fazer alterações depois do prazo.
            </div>

            <p>Acede ao Timely para planeares as tuas férias agora.</p>
            <a href="{{ $calendarUrl }}" class="cta">Planear férias</a>
        </div>
        <div class="footer">
            <p>Este email é enviado semanalmente durante o mês de fevereiro. Não replies a este email.</p>
        </div>
    </div>
</body>
</html>
