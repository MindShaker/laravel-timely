<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
    <h2 style="color: #2563eb;">Pedido de Ponto Recebido</h2>
    
    <p>Olá, o teu pedido para inserir o registo de ponto do dia <strong>{{ \Carbon\Carbon::parse($log->data)->format('d/m/Y') }}</strong> foi enviado com sucesso e aguarda aprovação da administração.</p>

    <div style="background-color: #f8fafc; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #2563eb;">
        <h4 style="margin-top: 0; color: #4b5563;">Resumo do Pedido:</h4>
        <ul style="list-style-type: none; padding: 0; margin: 0; color: #1f2937;">
            <li style="margin-bottom: 8px;"><strong>Entrada:</strong> {{ $print_time = date('H:i', strtotime($log->entrada)) }}</li>
            <li style="margin-bottom: 8px;"><strong>Saída:</strong> {{ $print_time = date('H:i', strtotime($log->saida)) }}</li>
            <li style="margin-bottom: 8px;"><strong>Total Horas:</strong> {{ $print_time = date('H:i', strtotime($log->total_horas)) }}</li>
        </ul>
    </div>

    <p>Assim que um administrador analisar o teu pedido, receberás uma atualização.</p>
    
    <p style="font-size: 12px; color: #9ca3af; margin-top: 40px;">Este e-mail foi gerado pelo Sistema Timely.</p>
</div>