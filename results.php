<?php
// Iniciar sessão
session_start();

// Incluindo configurações do banco de dados
require_once 'includes/config.php';

// Obter configurações do painel admin
$sql_config = "SELECT dt_inicio, dt_fim, dt_max_inscricao, qtd_dias FROM config WHERE id = 1";
$stmt_config = $pdo->prepare($sql_config);
$stmt_config->execute();
$config = $stmt_config->fetch(PDO::FETCH_ASSOC);

// Verificar se o desafio já terminou
$hoje = date('Y-m-d');
$desafio_terminado = false;
if ($config['dt_fim'] && $hoje >= $config['dt_fim']) {
    $desafio_terminado = true;
}

// Se o desafio ainda não terminou, mostrar mensagem de redirecionamento
if (!$desafio_terminado) {
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Final - NoFap Bolteano</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .redirect-container {
            max-width: 600px;
            width: 100%;
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }

        .redirect-icon {
            font-size: 4em;
            color: #3498db;
            margin-bottom: 20px;
        }

        .redirect-title {
            font-size: 1.8em;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .redirect-message {
            font-size: 1.1em;
            color: #555;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .countdown {
            font-size: 1.2em;
            color: #e74c3c;
            font-weight: bold;
            margin: 20px 0;
        }
    </style>
    <meta http-equiv="refresh" content="5;url=members.php">
</head>
<body>
    <div class="redirect-container">
        <div class="redirect-icon">⏰</div>
        <h1 class="redirect-title">Aguarde um pouco!</h1>
        
        <p class="redirect-message">
            Essa página de conclusão do desafio mostra que o mesmo ainda não acabou e por isso você será redirecionado para o acompanhamento do desafio que está em andamento.
        </p>
        
        <p class="countdown">Redirecionando em 5 segundos...</p>
        
        <a href="members.php" class="btn">Ir para o Acompanhamento do Desafio</a>
    </div>

    <script>
        // Atualizar o contador
        let timeLeft = 5;
        const countdownElement = document.querySelector('.countdown');
        
        const timer = setInterval(() => {
            timeLeft--;
            countdownElement.textContent = `Redirecionando em ${timeLeft} segundos...`;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
            }
        }, 1000);
    </script>
</body>
</html>
<?php
    exit();
}

// Obter todos os membros ordenados por nome
$sql_membros = "SELECT * FROM members ORDER BY nome ASC";
$stmt_membros = $pdo->prepare($sql_membros);
$stmt_membros->execute();
$membros = $stmt_membros->fetchAll(PDO::FETCH_ASSOC);

// Agrupar membros por status
$vencedores = [];
$perdedores = [];
$sem_informacao = [];

foreach ($membros as $membro) {
    switch ($membro['status']) {
        case 'falhou':
            $perdedores[] = $membro;
            break;
        case 'inativo':
            $sem_informacao[] = $membro;
            break;
        default:
            $vencedores[] = $membro;
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados Finais - NoFap Bolteano</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header com imagem */
        .header {
            width: 100%;
            text-align: center;
            padding: 0;
            margin: 0;
            background-color: #2c3e50;
        }

        .header-image {
            width: 150px;
            height: 120px;
            object-fit: cover;
            object-position: center;
            border-radius: 0;
            margin: 0 auto 10px;
            border: none;
            padding: 0;
            display: block;
            box-sizing: border-box;
            max-width: 100%;
            height: auto;
        }

        .header-content {
            background-color: #2c3e50;
            color: white;
            padding: 10px 0;
            margin: 0;
            border-radius: 0 0 8px 8px;
            width: 100%;
        }

        .header-content h1 {
            margin: 0 0 5px 0;
            font-size: 1.3em;
            font-family: 'Crimson Text', Georgia, serif;
        }

        .header-content p {
            margin: 0;
            font-size: 0.9em;
        }

        .main-content {
            margin-top: 20px;
        }

        .results-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #2c3e50;
            color: white;
            border-radius: 8px;
        }

        .results-header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .results-header p {
            font-size: 1.2em;
        }

        /* Layout Horizontal */
        .results-container {
            display: flex;
            gap: 20px;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .results-panel {
            flex: 1;
            min-width: 300px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .panel-vencedores {
            background-color: #d4edda;
            color: #155724;
        }

        .panel-perdedores {
            background-color: #f8d7da;
            color: #721c24;
        }

        .panel-sem-informacao {
            background-color: #fff3cd;
            color: #856404;
        }

        .panel-title {
            text-align: center;
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .panel-title i {
            margin-right: 10px;
        }

        .panel-count {
            font-size: 1.2em;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .motivational-message {
            text-align: center;
            font-style: italic;
            margin-bottom: 20px;
            padding: 15px;
            background-color: rgba(255,255,255,0.3);
            border-radius: 8px;
        }

        .members-list {
            margin-top: 20px;
        }

        .member-item {
            padding: 10px;
            margin-bottom: 10px;
            background-color: white;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
        }

        .btn-container {
            text-align: center;
            margin: 30px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #219653;
        }

        /* Estilos para impressão */
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            
            .header {
                background-color: #2c3e50;
                color: white;
                padding: 15px;
                margin-bottom: 20px;
            }
            
            .header-content h1 {
                color: white;
                font-size: 1.5em;
            }
            
            .results-header {
                background-color: #2c3e50;
                color: white;
                padding: 15px;
                margin-bottom: 20px;
            }
            
            .results-header h1 {
                font-size: 1.8em;
            }
            
            .results-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .results-panel {
                margin-bottom: 15px;
                box-shadow: none;
                border: 1px solid #ccc;
            }
            
            .btn-container {
                display: none;
            }
            
            /* Estilo especial para impressão em formato de lista */
            .print-list-view {
                display: block;
                page-break-inside: avoid;
                margin-bottom: 30px;
            }
            
            .print-list-title {
                font-size: 1.3em;
                font-weight: bold;
                margin-bottom: 10px;
                color: #2c3e50;
                border-bottom: 2px solid #3498db;
                padding-bottom: 5px;
            }
            
            .print-list-content {
                font-size: 1.1em;
                line-height: 1.4;
            }
            
            .print-member-item {
                margin-bottom: 8px;
                padding: 8px;
                border-bottom: 1px dashed #ddd;
            }
            
            .print-member-item:last-child {
                border-bottom: none;
            }
        }

        @media (max-width: 768px) {
            .results-container {
                flex-direction: column;
            }
        }
    </style>
    <script>
        function printResults() {
            // Criar uma versão especial para impressão
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Resultados Finais - NoFap Bolteano</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 20px;
                            font-size: 12pt;
                        }
                        .print-header {
                            text-align: center;
                            margin-bottom: 30px;
                            padding: 15px;
                            background-color: #2c3e50;
                            color: white;
                        }
                        .print-header h1 {
                            margin: 0;
                            font-size: 1.8em;
                        }
                        .print-section {
                            margin-bottom: 25px;
                        }
                        .print-section-title {
                            font-size: 1.4em;
                            font-weight: bold;
                            color: #2c3e50;
                            margin-bottom: 15px;
                            border-bottom: 2px solid #3498db;
                            padding-bottom: 5px;
                        }
                        .print-member {
                            margin-bottom: 8px;
                            padding: 8px;
                            border-bottom: 1px dashed #ddd;
                        }
                        .print-member:last-child {
                            border-bottom: none;
                        }
                        .print-footer {
                            margin-top: 30px;
                            text-align: center;
                            font-size: 0.9em;
                            color: #666;
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h1>Resultados Finais - NoFap Bolteano</h1>
                        <p>Desafio concluído em ${new Date().toLocaleDateString('pt-BR')}</p>
                        
                    </div>
                    
                    <div class="print-section">
                        <div class="print-section-title">VENCEDORES (${<?php echo count($vencedores); ?>})</div>
                        <div class="print-section-content">
                            <?php 
                            if (empty($vencedores)) {
                                echo '<p>Nenhum vencedor registrado.</p>';
                            } else {
                                $contador = 1;
                                foreach ($vencedores as $membro) {
                                    echo '<div class="print-member">' . $contador . '. ' . htmlspecialchars($membro['nome']) . ' - VENCEU</div>';
                                    $contador++;
                                }
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="print-section">
                        <div class="print-section-title">PERDEDORES (${<?php echo count($perdedores); ?>})</div>
                        <div class="print-section-content">
                            <?php 
                            if (empty($perdedores)) {
                                echo '<p>Nenhum perdedor registrado.</p>';
                            } else {
                                $contador = 1;
                                foreach ($perdedores as $membro) {
                                    echo '<div class="print-member">' . $contador . '. ' . htmlspecialchars($membro['nome']) . ' - PERDEU</div>';
                                    $contador++;
                                }
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="print-section">
                        <div class="print-section-title">SEM INFORMAÇÃO (${<?php echo count($sem_informacao); ?>})</div>
                        <div class="print-section-content">
                            <?php 
                            if (empty($sem_informacao)) {
                                echo '<p>Nenhum membro sem informação.</p>';
                            } else {
                                $contador = 1;
                                foreach ($sem_informacao as $membro) {
                                    echo '<div class="print-member">' . $contador . '. ' . htmlspecialchars($membro['nome']) . ' - SEM INFORMAÇÃO</div>';
                                    $contador++;
                                }
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="print-footer">
                        <p>Gerado em <?php echo date('d/m/Y H:i'); ?></p>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }
    </script>
</head>
<body>
    <!-- Header com imagem -->
    <div class="header">
        <div style="text-align: center; padding: 10px 0;">
            <img src="uploads/images.jpg" alt="Logo do Grupo Nofap" class="header-image" onerror="this.style.display='none';">
            <div class="header-content">
                <h1>No Fap Bolteano</h1>
                <p>Bem-vindos ao desafio Nofap!</p>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="main-content">
            <div class="results-header">
                <h1>RESULTADOS FINAIS</h1>
                <p>Desafio concluído em <?php echo date('d/m/Y', strtotime($config['dt_fim'])); ?></p>
                <a href="index.php" class="btn">Página Inicial</a>

            </div>
            
            <!-- Layout Horizontal -->
            <div class="results-container">
                <!-- Vencedores -->
                <div class="results-panel panel-vencedores">
                    <h2 class="panel-title">🏆 VENCEDORES (<?php echo count($vencedores); ?>)</h2>
                    <div class="panel-count">Parabéns!</div>
                    
                    <div class="motivational-message">
                        "Disciplina é escolher entre o que você quer agora e o que você quer mais tarde."
                    </div>
                    
                    <div class="members-list">
                        <?php if (empty($vencedores)): ?>
                            <p style="text-align: center;">Nenhum vencedor registrado.</p>
                        <?php else: ?>
                            <?php foreach ($vencedores as $membro): ?>
                                <div class="member-item"><?php echo htmlspecialchars($membro['nome']); ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Perdedores -->
                <div class="results-panel panel-perdedores">
                    <h2 class="panel-title">❌ PERDEDORES (<?php echo count($perdedores); ?>)</h2>
                    <div class="panel-count">Que pena!</div>
                    
                    <div class="motivational-message">
                        "Você não perdeu, você só descobriu mais uma maneira que não funciona."
                    </div>
                    
                    <div class="members-list">
                        <?php if (empty($perdedores)): ?>
                            <p style="text-align: center;">Nenhum perdedor registrado.</p>
                        <?php else: ?>
                            <?php foreach ($perdedores as $membro): ?>
                                <div class="member-item"><?php echo htmlspecialchars($membro['nome']); ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Sem Informação -->
                <div class="results-panel panel-sem-informacao">
                    <h2 class="panel-title">❓ SEM INFORMAÇÃO (<?php echo count($sem_informacao); ?>)</h2>
                    <div class="panel-count">Sem compromisso!</div>
                    
                    <div class="motivational-message">
                        "A falta de informação é pior que a derrota. Pelo menos na derrota você sabe onde errou."
                    </div>
                    
                    <div class="members-list">
                        <?php if (empty($sem_informacao)): ?>
                            <p style="text-align: center;">Nenhum membro sem informação.</p>
                        <?php else: ?>
                            <?php foreach ($sem_informacao as $membro): ?>
                                <div class="member-item"><?php echo htmlspecialchars($membro['nome']); ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
            <!-- Botão de impressão -->
            <div class="btn-container">
                <button onclick="printResults()" class="btn">
                    🖨️ Salvar/Imprimir Resultados
                </button>
            </div>
             

          </div>
    </div>
</body>
</html>