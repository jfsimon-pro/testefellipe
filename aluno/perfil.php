<?php
require_once '../includes/auth.php';
require_login('aluno');
require_once '../includes/db.php';

$usuario_id = $_SESSION['usuario_id'];

// Buscar dados do aluno
$stmt = $pdo->prepare('SELECT nome, email FROM usuarios WHERE id = ?');
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$mensagem = '';

// Atualizar nome e e-mail
if (isset($_POST['atualizar_dados'])) {
    $novo_nome = trim($_POST['nome'] ?? '');
    $novo_email = trim($_POST['email'] ?? '');
    if ($novo_nome && $novo_email) {
        $stmt = $pdo->prepare('UPDATE usuarios SET nome = ?, email = ? WHERE id = ?');
        $stmt->execute([$novo_nome, $novo_email, $usuario_id]);
        $mensagem = 'Dados atualizados com sucesso!';
        $usuario['nome'] = $novo_nome;
        $usuario['email'] = $novo_email;
    } else {
        $mensagem = 'Preencha todos os campos.';
    }
}

// Alterar senha
if (isset($_POST['alterar_senha'])) {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $nova_senha2 = $_POST['nova_senha2'] ?? '';
    if ($senha_atual && $nova_senha && $nova_senha2) {
        $stmt = $pdo->prepare('SELECT senha_hash FROM usuarios WHERE id = ?');
        $stmt->execute([$usuario_id]);
        $hash = $stmt->fetchColumn();
        if (password_verify($senha_atual, $hash)) {
            if ($nova_senha === $nova_senha2) {
                if (strlen($nova_senha) >= 6) {
                    $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?');
                    $stmt->execute([$novo_hash, $usuario_id]);
                    $mensagem = 'Senha alterada com sucesso!';
                } else {
                    $mensagem = 'A nova senha deve ter pelo menos 6 caracteres.';
                }
            } else {
                $mensagem = 'As novas senhas não coincidem.';
            }
        } else {
            $mensagem = 'Senha atual incorreta.';
        }
    } else {
        $mensagem = 'Preencha todos os campos da senha.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Aluno</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #fafafa;
            font-family: 'Inter', sans-serif;
            color: #1f2937;
            padding-bottom: 100px;
        }
        .container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 40px 32px 32px 32px;
            position: relative;
            margin: 40px auto;
        }
        @media (min-width: 769px) {
            .container {
                max-width: 420px;
            }
        }
        .profile-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .profile-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 32px;
            margin: 0 auto 16px auto;
        }
        .profile-title {
            font-size: 24px;
            font-weight: 700;
            color: #0ea5e9;
            margin-bottom: 4px;
        }
        .profile-subtitle {
            font-size: 14px;
            color: #64748b;
        }
        .mensagem {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .section {
            margin-bottom: 32px;
        }
        label {
            display: block;
            margin-top: 16px;
            color: #1f2937;
            font-weight: 500;
        }
        .input-field {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            background: #fff;
            margin-top: 6px;
            font-family: 'Inter', sans-serif;
            transition: border 0.3s, box-shadow 0.3s;
            outline: none;
        }
        .input-field:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }
        .btn {
            width: 100%;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: #fff;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            margin-top: 22px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.15);
        }
        .btn:active { transform: translateY(0); }
        .back-btn {
            background: #e5e7eb;
            color: #0ea5e9;
            margin-top: 0;
            margin-bottom: 8px;
        }
        .back-btn:hover {
            background: #cbd5e1;
            color: #0284c7;
        }
        .logout-btn {
            position: absolute;
            top: 24px;
            right: 24px;
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }
        .logout-btn:hover { background: #dc2626; }
        @media (max-width: 768px) {
            .container {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 16px !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }
            .profile-title {
                font-size: 20px;
            }
            .logout-btn {
                display: none;
            }
        }
        /* Floating nav */
        .floating-nav {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            padding: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            display: flex;
            gap: 8px;
            z-index: 1000;
        }
        .nav-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            background: #fff;
            color: #6b7280;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .nav-btn.active {
            background: #0ea5e9;
            color: #fff;
        }
        .nav-btn:hover { background: #f3f4f6; }
        .nav-btn.active:hover { background: #0284c7; }
    </style>
</head>
<body>
    <div class="container">
        <button class="logout-btn" onclick="window.location.href='../logout.php'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
        <div class="profile-header">
            <div class="profile-icon"><i class="fas fa-user"></i></div>
            <div class="profile-title">Meu Perfil</div>
            <div class="profile-subtitle">Gerencie seus dados e senha</div>
        </div>
        <?php if ($mensagem): ?>
            <div class="mensagem"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <div class="section">
            <form method="post">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" class="input-field" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" class="input-field" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                <button type="submit" name="atualizar_dados" class="btn">Atualizar Dados</button>
            </form>
        </div>
        <div class="section">
            <form method="post">
                <label for="senha_atual">Senha atual</label>
                <input type="password" id="senha_atual" name="senha_atual" class="input-field" required>
                <label for="nova_senha">Nova senha</label>
                <input type="password" id="nova_senha" name="nova_senha" class="input-field" required>
                <label for="nova_senha2">Confirmar nova senha</label>
                <input type="password" id="nova_senha2" name="nova_senha2" class="input-field" required>
                <button type="submit" name="alterar_senha" class="btn">Alterar Senha</button>
            </form>
        </div>
        <a href="meus-cursos.php" class="btn back-btn"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
    <div class="floating-nav">
        <button class="nav-btn" onclick="window.location.href='meus-cursos.php'">
            <i class="fas fa-home"></i>
        </button>
        <button class="nav-btn" onclick="window.location.href='cursos-matriculados.php'">
            <i class="fas fa-bookmark"></i>
        </button>
        <button class="nav-btn active" onclick="window.location.href='perfil.php'">
            <i class="fas fa-user"></i>
        </button>
        <button class="nav-btn" onclick="window.location.href='../logout.php'">
            <i class="fas fa-sign-out-alt"></i>
        </button>
    </div>
    <script>
        // Marcar botão ativo no menu
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname;
            const navButtons = document.querySelectorAll('.nav-btn');
            navButtons.forEach(btn => {
                if (btn.onclick.toString().includes(currentPage.split('/').pop())) {
                    btn.classList.add('active');
                }
            });
        });
    </script>
</body>
</html> 