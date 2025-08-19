<?php
// Configurações do banco de dados
$host = 'localhost';
$dbname = 'nofap_boltes';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Criar tabelas se não existirem
try {
    // Tabela de membros
    $sql_members = "CREATE TABLE IF NOT EXISTS members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        status ENUM('ativo', 'falhou', 'inativo') DEFAULT 'ativo',
        ultimo_checkin DATE,
        dias_consecutivos INT DEFAULT 0,
        data_falha DATE NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_members);
    
    // Tabela de check-ins
    $sql_checkins = "CREATE TABLE IF NOT EXISTS check_ins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        membro_id INT NOT NULL,
        data DATE NOT NULL,
        status ENUM('falhei', 'firme') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (membro_id) REFERENCES members(id) ON DELETE CASCADE,
        UNIQUE KEY unique_membro_data (membro_id, data)
    )";
    $pdo->exec($sql_checkins);
    
    // Tabela de configurações
    $sql_config = "CREATE TABLE IF NOT EXISTS config (
        id INT PRIMARY KEY DEFAULT 1,
        dt_inicio DATE,
        dt_fim DATE,
        qtd_dias INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_config);
    
    // Tabela de administradores
    $sql_admins = "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_admins);
    
    // Inserir configuração padrão se não existir
    $sql_check_config = "SELECT id FROM config WHERE id = 1";
    $stmt = $pdo->prepare($sql_check_config);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $sql_insert_config = "INSERT INTO config (id, dt_inicio, dt_fim, qtd_dias) VALUES (1, '2025-10-01', '2025-10-10', 10)";
        $pdo->exec($sql_insert_config);
    }
    
    // Inserir admin padrão se não existir
    $sql_check_admin = "SELECT id FROM admins WHERE username = 'admin'";
    $stmt = $pdo->prepare($sql_check_admin);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql_insert_admin = "INSERT INTO admins (username, password) VALUES ('admin', ?)";
        $stmt = $pdo->prepare($sql_insert_admin);
        $stmt->execute([$hashed_password]);
    }
    
} catch(PDOException $e) {
    // Ignorar erros de criação de tabelas (já existem)
    // O importante é que o banco funcione
}
?>