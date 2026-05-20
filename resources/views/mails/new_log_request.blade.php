<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
    <h2 style="color: #1f2937;">Novo Pedido de Inserção de Ponto</h2>
    
    <p>O utilizador <strong>{{ $solicitante->name }}</strong> solicitou a criação de um novo registo de ponto para o dia <strong>{{ \Carbon\Carbon::parse($log->data)->format('d/m/Y') }}</strong>.</p>

    <div style="background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 20px 0;">
        <h4 style="margin-top: 0; color: #4b5563;">Horários Solicitados:</h4>
        <ul style="list-style-type: none; padding: 0; margin: 0; color: #1f2937;">
            <li style="margin-bottom: 8px;"><strong>Entrada:</strong> {{ $log->entrada }}</li>
            <li style="margin-bottom: 8px;"><strong>Saída:</strong> {{ $log->saida }}</li>
            <li style="margin-bottom: 8px;"><strong>Total Horas:</strong> {{ $log->total_horas }}</li>
            <li><strong>Observações:</strong> {{ $log->obs }}</li>
        </ul>
    </div>

    <p style="margin-top: 30px;">
        <a href="{{ $approveUrl }}" style="background-color: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-right: 10px;">Aprovar Registo</a>
        
        <a href="{{ $rejectUrl }}" style="background-color: #ef4444; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;">Rejeitar</a>
    </p>
    
    <p style="font-size: 12px; color: #9ca3af; margin-top: 40px;">Este e-mail foi gerado pelo Sistema Timely e expira em 1 Hora.</p>
</div>