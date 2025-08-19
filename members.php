<?php
// Iniciar sessão
session_start();

// Incluindo configurações do banco de dados
require_once 'includes/config.php';

// Verificar se é administrador
$is_admin = false;
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $is_admin = true;
}

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

// Verificar se o período de atualização terminou
$periodo_atualizacao_ativo = true;
if ($config['dt_fim'] && $hoje > $config['dt_fim']) {
    $periodo_atualizacao_ativo = false;
}

// Calcular dias restantes com verificação
$dias_restantes = 0;
$desafio_nao_comecou = false;

if ($config['dt_inicio'] && $config['dt_fim']) {
    $data_inicio = new DateTime($config['dt_inicio']);
    $data_fim = new DateTime($config['dt_fim']);
    $data_hoje = new DateTime($hoje);
    
    // Verificar se o desafio ainda não começou
    if ($data_hoje < $data_inicio) {
        $desafio_nao_comecou = true;
        // Calcular dias até o início
        $interval = $data_hoje->diff($data_inicio);
        $dias_restantes = $interval->days;
    } else {
        // Calcular dias restantes até o fim
        $interval = $data_hoje->diff($data_fim);
        $dias_restantes = $interval->days;
        
        // Se já passou da data final, mostrar 0
        if ($hoje > $config['dt_fim']) {
            $dias_restantes = 0;
        }
    }
}

// Obter todos os membros ordenados por nome
$sql_membros = "SELECT * FROM members ORDER BY nome ASC";
$stmt_membros = $pdo->prepare($sql_membros);
$stmt_membros->execute();
$membros = $stmt_membros->fetchAll(PDO::FETCH_ASSOC);

// Processar atualização de status (APENAS PARA ADMIN)
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin && $periodo_atualizacao_ativo) {
    if (!empty($_POST['membro_id']) && !empty($_POST['novo_status'])) {
        $membro_id = (int)$_POST['membro_id'];
        $novo_status = $_POST['novo_status'];
        
        // Verificar se o membro existe
        $sql_check = "SELECT id, status FROM members WHERE id = :id";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindValue(':id', $membro_id, PDO::PARAM_INT);
        $stmt_check->execute();
        $membro = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if ($membro) {
            // Para membros já marcados como "falhou", não permite alteração
            if ($membro['status'] === 'falhou' && $novo_status !== 'falhou') {
                $erro = "O status de um membro falhado não pode ser alterado.";
            } else {
                // Atualizar status
                $sql_update = "UPDATE members SET status = :status WHERE id = :id";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->bindValue(':status', $novo_status, PDO::PARAM_STR);
                $stmt_update->bindValue(':id', $membro_id, PDO::PARAM_INT);
                
                if ($stmt_update->execute()) {
                    $mensagem = "Status atualizado com sucesso!";
                    // Atualizar a variável $membros após a alteração
                    $sql_membros = "SELECT * FROM members ORDER BY nome ASC";
                    $stmt_membros = $pdo->prepare($sql_membros);
                    $stmt_membros->execute();
                    $membros = $stmt_membros->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $erro = "Erro ao atualizar o status!";
                }
            }
        } else {
            $erro = "Membro não encontrado!";
        }
    } else {
        $erro = "Dados incompletos!";
    }
}

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

    <!-- Conteúdo Principal -->
    <div class="container">
        <div class="main-content">
            <!-- Coluna Principal -->
            <div class="column-main">
                <div style="text-align: center; margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <h1 style="font-size: 2.5em; margin: 0 0 10px 0; font-weight: 700; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">🏆 ACOMPANHAMENTO DE MEMBROS</h1>
                    <p style="font-size: 1.1em; opacity: 0.9;">Desafio Nofap em andamento</p>
                </div>
                
                <?php if ($erro): ?>
                    <div class="message error">
                        <?php echo htmlspecialchars($erro); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($mensagem): ?>
                    <div class="message success">
                        <?php echo htmlspecialchars($mensagem); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$periodo_atualizacao_ativo): ?>
                    <div class="message info">
                        <h3>Período de Atualização Encerrado</h3>
                        <p>O período para atualização de status terminou em <?php echo date('d/m/Y', strtotime($config['dt_fim'])); ?>.</p>
                        <p>Os resultados finais foram congelados.</p>
                    </div>
                <?php endif; ?>

                    <!-- Botões de Navegação -->
                <div style="text-align: center; margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 8px;">
                    <a href="index.php" class="btn" style="margin: 0 10px; background-color: #3498db;">🏠 Página Inicial</a>
                    
                    <!-- Botão de Resultados (aparece apenas quando o desafio terminou) -->
                    <?php if ($desafio_terminado): ?>
                        <a href="results.php" class="btn" style="margin: 0 10px; background-color: #27ae60;">🏆 Ver Resultados Finais</a>
                    <?php endif; ?>
                </div>

                
                <!-- Informações do Desafio com Design Aprimorado -->
                <div style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); padding: 25px; border-radius: 12px; margin: 30px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                        <div style="flex: 1; min-width: 200px;">
                            <h3 style="color: #333; margin-bottom: 15px; font-size: 1.4em; display: flex; align-items: center;">
                                <span style="background-color: #2c3e50; color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 1.2em;">📅</span>
                                Informações do Desafio
                            </h3>
                            
                            <div style="margin-top: 15px;">
                                <div style="display: flex; justify-content: space-between; padding: 12px; background-color: rgba(255,255,255,0.3); border-radius: 8px; margin-bottom: 10px;">
                                    <span style="font-weight: bold; color: #333;">Período:</span>
                                    <span style="color: #333;">
                                        <?php echo $config['dt_inicio'] ? date('d/m/Y', strtotime($config['dt_inicio'])) : 'Não definido'; ?> 
                                        <span style="color: #666;">a</span> 
                                        <?php echo $config['dt_fim'] ? date('d/m/Y', strtotime($config['dt_fim'])) : 'Não definido'; ?>
                                    </span>
                                </div>
                                
                                <div style="display: flex; justify-content: space-between; padding: 12px; background-color: rgba(255,255,255,0.3); border-radius: 8px;">
                                    <?php if ($desafio_nao_comecou): ?>
                                        <span style="font-weight: bold; color: #333;">Status:</span>
                                        <span style="color: #333; font-weight: bold; font-size: 1.2em; color: #f39c12;">
                                            Desafio ainda não começou
                                        </span>
                                    <?php else: ?>
                                        <span style="font-weight: bold; color: #333;">Dias Restantes:</span>
                                        <span style="color: #333; font-weight: bold; font-size: 1.2em;">
                                            <?php echo $dias_restantes; ?> dias
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contador de membros -->
                        <div style="flex: 1; min-width: 250px; background-color: rgba(255,255,255,0.2); padding: 20px; border-radius: 12px;">
                            <h4 style="color: #333; margin-bottom: 15px; text-align: center; font-size: 1.2em;">📊 Status Geral</h4>
                            <div style="display: flex; justify-content: space-around; text-align: center;">
                                <div style="color: #155724;">
                                    <div style="font-size: 1.8em; font-weight: bold;"><?php echo count($vencedores); ?></div>
                                    <div style="font-size: 0.9em;">Vencedores</div>
                                </div>
                                <div style="color: #721c24;">
                                    <div style="font-size: 1.8em; font-weight: bold;"><?php echo count($perdedores); ?></div>
                                    <div style="font-size: 0.9em;">Perdedores</div>
                                </div>
                                <div style="color: #856404;">
                                    <div style="font-size: 1.8em; font-weight: bold;"><?php echo count($sem_informacao); ?></div>
                                    <div style="font-size: 0.9em;">Sem Info</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Layout Horizontal -->
                <div style="display: flex; gap: 20px; margin: 30px 0; flex-wrap: wrap;">
                    <!-- Vencedores -->
                    <div style="flex: 1; min-width: 300px; background-color: #d4edda; padding: 20px; border-radius: 8px;">
                        <h2 style="color: #155724; margin-bottom: 15px; text-align: center;">🏆 Vencedores (<?php echo count($vencedores); ?>)</h2>
                        <?php if (empty($vencedores)): ?>
                            <p style="text-align: center; color: #155724;">Nenhum membro vencedor registrado.</p>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php foreach ($vencedores as $membro): ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background-color: white; border-radius: 4px;">
                                        <span style="font-weight: bold;"><?php echo htmlspecialchars($membro['nome']); ?></span>
                                        <div style="display: flex; gap: 10px;">
                                            <?php if ($is_admin && $periodo_atualizacao_ativo): ?>
                                                <form method="post" action="" style="display: inline;">
                                                    <input type="hidden" name="membro_id" value="<?php echo $membro['id']; ?>">
                                                    <input type="hidden" name="novo_status" value="falhou">
                                                    <button type="submit" style="background-color: #e74c3c; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em;">Perdeu</button>
                                                </form>
                                                <form method="post" action="" style="display: inline;">
                                                    <input type="hidden" name="membro_id" value="<?php echo $membro['id']; ?>">
                                                    <input type="hidden" name="novo_status" value="inativo">
                                                    <button type="submit" style="background-color: #f39c12; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em;">Sem Informação</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color: #155724; font-weight: bold;">Vencedor</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Perdedores -->
                    <div style="flex: 1; min-width: 300px; background-color: #f8d7da; padding: 20px; border-radius: 8px;">
                        <h2 style="color: #721c24; margin-bottom: 15px; text-align: center;">❌ Perdedores (<?php echo count($perdedores); ?>)</h2>
                        <?php if (empty($perdedores)): ?>
                            <p style="text-align: center; color: #721c24;">Nenhum membro perdedor registrado.</p>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php foreach ($perdedores as $membro): ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background-color: white; border-radius: 4px;">
                                        <span style="font-weight: bold; text-decoration: line-through;"><?php echo htmlspecialchars($membro['nome']); ?></span>
                                        <div style="display: flex; gap: 10px;">
                                            <?php if ($is_admin && $periodo_atualizacao_ativo): ?>
                                                <span style="color: #721c24; font-weight: bold;">Perdedor</span>
                                            <?php else: ?>
                                                <span style="color: #721c24; font-weight: bold;">Perdedor</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Sem Informação -->
                    <div style="flex: 1; min-width: 300px; background-color: #fff3cd; padding: 20px; border-radius: 8px;">
                        <h2 style="color: #856404; margin-bottom: 15px; text-align: center;">❓ Sem Informação (<?php echo count($sem_informacao); ?>)</h2>
                        <?php if (empty($sem_informacao)): ?>
                            <p style="text-align: center; color: #856404;">Nenhum membro sem informação.</p>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php foreach ($sem_informacao as $membro): ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background-color: white; border-radius: 4px;">
                                        <span style="font-weight: bold;"><?php echo htmlspecialchars($membro['nome']); ?></span>
                                        <div style="display: flex; gap: 10px;">
                                            <?php if ($is_admin && $periodo_atualizacao_ativo): ?>
                                                <form method="post" action="" style="display: inline;">
                                                    <input type="hidden" name="membro_id" value="<?php echo $membro['id']; ?>">
                                                    <input type="hidden" name="novo_status" value="falhou">
                                                    <button type="submit" style="background-color: #e74c3c; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em;">Perdeu</button>
                                                </form>
                                                <form method="post" action="" style="display: inline;">
                                                    <input type="hidden" name="membro_id" value="<?php echo $membro['id']; ?>">
                                                    <input type="hidden" name="novo_status" value="ativo">
                                                    <button type="submit" style="background-color: #27ae60; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em;">Venceu</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color: #856404; font-weight: bold;">Sem Info</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

