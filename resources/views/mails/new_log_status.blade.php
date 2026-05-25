<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
    <h2 style="color: #1f2937;">Atualização do teu Pedido de Ponto</h2>
    
    <p>Olá,</p>
    
    <p>O teu pedido para a criação de um novo registo de ponto para o dia <strong>{{ \Carbon\Carbon::parse($log->data)->format('d/m/Y') }}</strong> foi avaliado pelo administrador.</p>

    {{-- Caixa de Estado Dinâmica: Verde para aprovado, Vermelho para recusado --}}
    <div style="padding: 15px; border-radius: 6px; margin: 20px 0; font-weight: bold; text-align: center; 
        background-color: {{ $status === 'approved' ? '#d1fae5' : '#fee2e2' }}; 
        color: {{ $status === 'approved' ? '#065f46' : '#991b1b' }};">
        ESTADO DO PEDIDO: {{ $status === 'approved' ? 'APROVADO' : 'RECUSADO' }}
    </div>

    <div style="background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 20px 0;">
        <h4 style="margin-top: 0; color: #4b5563;">Horários Solicitados:</h4>
        <ul style="list-style-type: none; padding: 0; margin: 0; color: #1f2937;">
            <li style="margin-bottom: 8px;"><strong>Entrada:</strong> {{ $print_time = date('H:i', strtotime($log->entrada)) }}</li>
            <li style="margin-bottom: 8px;"><strong>Saída:</strong> {{ $print_time = date('H:i', strtotime($log->saida)) }}</li>
            <li style="margin-bottom: 8px;"><strong>Total Horas:</strong> {{ $print_time = date('H:i', strtotime($log->total_horas)) }}</li>
            @if($log->obs)
                <li><strong>Observações:</strong> {{ $log->obs }}</li>
            @endif
        </ul>
    </div>
    
    <p>Se tiveres alguma dúvida ou detetares algum erro, entra em contacto com a administração do sistema.</p>
    
    <p style="font-size: 12px; color: #9ca3af; margin-top: 40px;">Este e-mail foi gerado automaticamente pelo Sistema Timely.</p>
</div>