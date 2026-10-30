# 🔥 CarvoTrack — Gestão & Monitoramento de Carvoaria
### Projeto Integrador (PI) | Atividade Prática de Programação Web (PHP, PDO & MySQL)

**Aluno:** Pedro Henrique de Lima Silveira  
**RA:** 118771  
**Tema do PI:** Plataforma de Gestão, Produção e Monitoramento de Carvão Vegetal (CarvoTrack)  
**Repositório GitHub:** [https://github.com/Lim4S2/PWEB-Atividade-PHP-CSS-e-JS](https://github.com/Lim4S2/PWEB-Atividade-PHP-CSS-e-JS)

---

## 📌 Sobre a Atividade

Implementação completa das operações de **CRUD (Create, Read, Update e Delete)** aplicadas ao domínio do Projeto Integrador (**CarvoTrack**), gerenciando os **Fornos de Carbonização** e os **Lotes de Madeira**, integrando controle de autenticação de usuários via sessões PHP e uma rotina de escrita atômica multi-tabelas com **Transação PDO**.

---

## ✅ Boas Práticas Apresentadas em Aula e Implementadas

1. **`require_once 'conexao.php'` no topo de todas as páginas:**  
   Garante carregamento único e centralizado da instância PDO em `index.php`, `protect.php`, `login.php`, `logout.php`, `forno_criar.php`, `forno_editar.php`, `forno_deletar.php`, `novo_processo.php`, `lotes.php` e `lote_criar.php`.

2. **`ERRMODE_EXCEPTION` ligado na criação do PDO:**  
   Configurado em `conexao.php` através de `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`, permitindo captura precisa de qualquer anomalia no banco através de blocos `try/catch (PDOException $e)`.

3. **`prepare()` e `execute()` com `?` para todo dado externo:**  
   Todas as consultas SQL com parâmetros recebidos via `$_GET` ou `$_POST` utilizam placeholders posicionais `?`, blindando o sistema contra ataques de SQL Injection.

4. **`charset=utf8mb4` na DSN e nas tabelas:**  
   - Na string de conexão DSN em `conexao.php`: `mysql:host=localhost;dbname=carvotrack;charset=utf8mb4`
   - Em todas as tabelas no script SQL `banco_de_dados.txt`: `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`.

5. **Transação quando a escrita envolve várias tabelas:**  
   Implementada em `novo_processo.php`, onde o início de uma fornada de carbonização realiza de forma atômica:
   - `INSERT` na tabela `processos_carbonizacao`
   - `UPDATE` na tabela `fornos` (altera status para `'em_processo'`)
   - `UPDATE` na tabela `lotes_madeira` (altera status para `'em_uso'`)
   - `INSERT` na tabela `historico_acoes` (auditoria do operador)  
   Tudo protegido por `$pdo->beginTransaction()`, `$pdo->commit()` e `$pdo->rollBack()`.

6. **Tratamento de `PDOException` (log no servidor e aviso genérico para o usuário):**  
   Todos os blocos `catch (PDOException $e)` registram o erro técnico no log do servidor através de `error_log(...)` e exibem ao usuário final apenas uma mensagem amigável e segura (ex: *"Ocorreu um erro interno no servidor ao processar a solicitação"*), sem expor detalhes estruturais da base de dados.

---

## 🗂️ Organização das Páginas

| Arquivo | Função / Papel |
|---|---|
| `conexao.php` | Conexão centralizada via PDO com `charset=utf8mb4`, `ERRMODE_EXCEPTION` e tratamento de exceções com fallback |
| `protect.php` | Controle e proteção de sessão (`$_SESSION['usuario_id']`), redirecionando acessos não autenticados |
| `login.php` | Autenticação com verificação de hash seguro (`password_verify`) e carga de usuário padrão |
| `logout.php` | Encerramento seguro da sessão PHP |
| `index.php` | **Read (Listar/Buscar)** — Painel com métricas, filtros de busca por código/status e tabela de fornos |
| `forno_criar.php` | **Create (Criar)** — Formulário de cadastro de novos fornos de carbonização |
| `forno_editar.php` | **Update (Atualizar)** — Formulário de edição dos dados técnicos do forno |
| `forno_deletar.php` | **Delete (Excluir)** — Exclusão com confirmação e validação de integridade referencial |
| `novo_processo.php` | **Transação Multi-Tabelas** — Escrita atômica em 4 tabelas com `beginTransaction()`, `commit()` e `rollBack()` |
| `lotes.php` | Visualização dos lotes de madeira (biomassa) disponíveis para queima |
| `lote_criar.php` | Cadastro de novos lotes de madeira |
| `banco_de_dados.txt` | Script SQL completo com criação do banco `carvotrack`, tabelas e dados iniciais |
| `identificacao.txt` / `Nome.txt` | Identificação do aluno, RA e nome do projeto |

---

## 🚀 Como Executar o Projeto

1. Inicie o **Apache** e o **MySQL** (ex: via XAMPP, WampServer ou Docker).
2. Importe o script contido em `banco_de_dados.txt` no seu gerenciador MySQL (ex: phpMyAdmin, MySQL Workbench ou terminal):
   ```bash
   mysql -u root -p < banco_de_dados.txt
   ```
   *(Nota: o `conexao.php` também possui rotina de auto-criação caso o banco ainda não exista).*
3. Coloque a pasta do projeto no diretório web (ex: `C:/xampp/htdocs/carvotrack` ou pasta atual).
4. Acesse pelo navegador: `http://localhost/carvotrack/login.php` (ou a URL configurada no seu ambiente).
5. **Credenciais padrão de acesso:**
   - **E-mail:** `admin@carvotrack.com`
   - **Senha:** `admin123`
