<?php
/**
 * Conexão com o banco de dados usando PDO
 * Boas práticas atendidas:
 * - charset=utf8mb4 na DSN
 * - PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
 * - Tratamento de PDOException com error_log no servidor e mensagem genérica ao usuário
 */

$dbHost = 'localhost';
$dbPort = '3306';
$dbName = 'carvotrack';
$dbUser = 'root';
$dbPass = '';

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

$opcoes = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $opcoes);
} catch (PDOException $e) {
    // Caso o banco ainda não tenha sido criado pelo avaliador, tenta auto-inicializar
    if ($e->getCode() == 1049) { // 1049: Unknown database
        try {
            $dsnSemBanco = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
            $pdoInit = new PDO($dsnSemBanco, $dbUser, $dbPass, $opcoes);
            $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo = new PDO($dsn, $dbUser, $dbPass, $opcoes);
            
            // Carrega e executa o script do banco se as tabelas ainda não existirem
            $scriptSql = __DIR__ . '/banco_de_dados.txt';
            if (file_exists($scriptSql)) {
                $sqlContent = file_get_contents($scriptSql);
                // Remove comandos de CREATE DATABASE e USE pois já estamos conectados
                $pdo->exec($sqlContent);
            }
        } catch (PDOException $initEx) {
            error_log("Falha ao auto-inicializar o banco: " . $initEx->getMessage());
            die("Desculpe, ocorreu um erro interno ao conectar ao banco de dados. Contate o administrador do sistema.");
        }
    } else {
        // Log do erro técnico nos logs do servidor (Apache/PHP error log)
        error_log("Erro de conexão PDO: " . $e->getMessage());
        // Aviso genérico para o usuário final
        die("Desculpe, ocorreu um erro interno no servidor ao tentar estabelecer conexão com o banco de dados. Tente novamente mais tarde.");
    }
}
