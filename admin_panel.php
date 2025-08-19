<?php
// Iniciar sessão
session_start();

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Incluindo configurações do banco de dados
require_once 'includes/config.php';

// Processar configurações
$config_mensagem = '';
$member_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_config':
                $dt_inicio = $_POST['dt_inicio'] ?? '';
                $dt_fim = $_POST['dt_fim'] ?? '';
                
                // Validação: data inicial não pode ser maior que data final
                if ($dt_inicio && $dt_fim && $dt_inicio > $dt_fim) {
                    $config_mensagem = "Erro: A data de início não pode ser maior que a data de término!";
                } else {
                    // Calcular quantidade de dias automaticamente
                    $qtd_dias = 0;
                    if ($dt_inicio && $dt_fim) {
                        $data_inicio = new DateTime($dt_inicio);
                        $data_fim = new DateTime($dt_fim);
                        $interval = $data_inicio->diff($data_fim);
                        $qtd_dias = $interval->days + 1; // +1 para incluir ambos os dias
                    }
                    
                    // Atualizar configurações no banco
                    $sql = "UPDATE config SET dt_inicio = :dt_inicio, dt_fim = :dt_fim, qtd_dias = :qtd_dias WHERE id = 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':dt_inicio', $dt_inicio, PDO::PARAM_STR);
                    $stmt->bindValue(':dt_fim', $dt_fim, PDO::PARAM_STR);
                    $stmt->bindValue(':qtd_dias', $qtd_dias, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $config_mensagem = "Configurações atualizadas com sucesso!";
                    } else {
                        $config_mensagem = "Erro ao atualizar configurações!";
                    }
                }
                break;
                
            case 'register_member':
                $nome = trim($_POST['nome'] ?? '');
                
                if (!empty($nome)) {
                    // Verificar se o membro já existe
                    $sql_check = "SELECT id FROM members WHERE nome = :nome";
                    $stmt_check = $pdo->prepare($sql_check);
                    $stmt_check->bindValue(':nome', $nome, PDO::PARAM_STR);
                    $stmt_check->execute();
                    
                    if ($stmt_check->rowCount() > 0) {
                        $member_mensagem = "Este nome já está cadastrado!";
                    } else {
                        // Inserir novo membro
                        $sql_insert = "INSERT INTO members (nome, status, ultimo_checkin, dias_consecutivos) 
                                      VALUES (:nome, 'ativo', CURDATE(), 0)";
                        $stmt_insert = $pdo->prepare($sql_insert);
                        $stmt_insert->bindValue(':nome', $nome, PDO::PARAM_STR);
                        
                        if ($stmt_insert->execute()) {
                            $member_mensagem = "Membro '$nome' registrado com sucesso!";
                        } else {
                            $member_mensagem = "Erro ao registrar o membro!";
                        }
                    }
                } else {
                    $member_mensagem = "Nome é obrigatório!";
                }
                break;
                
            case 'delete_member':
                $membro_id = (int)$_POST['membro_id'] ?? 0;
                
                if ($membro_id > 0) {
                    // Excluir membro
                    $sql_delete = "DELETE FROM members WHERE id = :id";
                    $stmt_delete = $pdo->prepare($sql_delete);
                    $stmt_delete->bindValue(':id', $membro_id, PDO::PARAM_INT);
                    
                    if ($stmt_delete->execute()) {
                        $member_mensagem = "Membro excluído com sucesso!";
                    } else {
                        $member_mensagem = "Erro ao excluir o membro!";
                    }
                } else {
                    $member_mensagem = "ID do membro inválido!";
                }
                break;
        }
    }
}

// Obter configurações atuais
$sql_config = "SELECT * FROM config WHERE id = 1";
$stmt_config = $pdo->prepare($sql_config);
$stmt_config->execute();
$config = $stmt_config->fetch(PDO::FETCH_ASSOC);

// Se não existir configuração, criar padrão
if (!$config) {
    $sql_insert = "INSERT INTO config (id, dt_inicio, dt_fim, dt_max_inscricao, qtd_dias) VALUES (1, '', '', '', 0)";
    $pdo->prepare($sql_insert)->execute();
    $config = ['dt_inicio' => '', 'dt_fim' => '', 'dt_max_inscricao' => '', 'qtd_dias' => 0];
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
    <title>Painel Do Ademir</title>
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

        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.5em;
        }

        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        .admin-content {
            display: flex;
            gap: 20px;
        }

        .main-panel {
            flex: 1;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .panel-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .panel-section h2 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .btn-success {
            background-color: #27ae60;
        }

        .btn-success:hover {
            background-color: #219653;
        }

        .btn-danger {
            background-color: #e74c3c;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .btn-warning {
            background-color: #f39c12;
        }

        .btn-warning:hover {
            background-color: #e67e22;
        }

        .message {
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .members-list {
            margin-top: 20px;
        }

        .member-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .member-item:last-child {
            border-bottom: none;
        }

        .member-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 15px 0;
            border-radius: 0 4px 4px 0;
        }

        .info-box h3 {
            color: #0d47a1;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .admin-content {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Painel Do Ademir</h1>
            <a href="index.php" class="btn">Página Inicial</a>
            <a href="admin_logout.php" class="logout-btn">Sair</a>

        </div>
        
        <div class="admin-content">
            <!-- Painel Principal -->
            <div class="main-panel">
                <!-- Seção de Configurações -->
                <div class="panel-section">
                    <h2>Configurações do Desafio</h2>
                    
                    <?php if ($config_mensagem): ?>
                        <div class="message <?php echo strpos($config_mensagem, 'Erro') !== false ? 'error' : 'success'; ?>">
                            <?php echo htmlspecialchars($config_mensagem); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <input type="hidden" name="action" value="update_config">
                        
                        <div class="form-group">
                            <label for="dt_inicio">Data de Início do Desafio:</label>
                            <input type="date" id="dt_inicio" name="dt_inicio" value="<?php echo htmlspecialchars($config['dt_inicio']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="dt_fim">Data de Término do Desafio:</label>
                            <input type="date" id="dt_fim" name="dt_fim" value="<?php echo htmlspecialchars($config['dt_fim']); ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-success">Atualizar Configurações</button>
                    </form>
                </div>
                
                <!-- Seção de Registro de Membros -->
                <div class="panel-section">
                    <h2>Registrar Membros</h2>
                    
                    <?php if ($member_mensagem): ?>
                        <div class="message <?php echo strpos($member_mensagem, 'Erro') !== false || strpos($member_mensagem, 'já está cadastrado') !== false ? 'error' : 'success'; ?>">
                            <?php echo htmlspecialchars($member_mensagem); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <input type="hidden" name="action" value="register_member">
                        
                        <div class="form-group">
                            <label for="nome">Nome do Membro:</label>
                            <input type="text" id="nome" name="nome" placeholder="Digite o nome completo" required>
                        </div>
                        
                        <button type="submit" class="btn btn-success">Registrar Membro</button>
                    </form>
                </div>
                
                <!-- Seção de Lista de Membros -->
                <div class="panel-section">
                    <h2>Lista de Membros Cadastrados (<?php echo count($membros); ?>)</h2>
                    
                    <?php if (empty($membros)): ?>
                        <p>Nenhum membro cadastrado ainda.</p>
                    <?php else: ?>
                        <div class="members-list">
                            <?php foreach ($membros as $membro): ?>
                                <div class="member-item">
                                    <span><?php echo htmlspecialchars($membro['nome']); ?></span>
                                    <div style="display: flex; gap: 10px;">
                                        <span class="member-status <?php 
                                            switch($membro['status']) {
                                                case 'ativo': echo 'status-pending'; break;
                                                case 'falhou': echo 'status-failed'; break;
                                                default: echo 'status-active'; break;
                                            }
                                        ?>">
                                            <?php 
                                                switch($membro['status']) {
                                                    case 'ativo': echo 'Sem Informação'; break;
                                                    case 'falhou': echo 'Perdedor'; break;
                                                    default: echo 'Ativo'; break;
                                                }
                                            ?>
                                        </span>
                                        <form method="post" action="" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este membro?');">
                                            <input type="hidden" name="action" value="delete_member">
                                            <input type="hidden" name="membro_id" value="<?php echo $membro['id']; ?>">
                                            <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8em;">Excluir</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>