<?php
$sucesso = isset($_GET['sucesso']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Acesso - Plataforma Educacional</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <style>
        body { background: #f7f8fa; font-family: 'Montserrat', Arial, sans-serif; }
        .container {
            max-width: 420px;
            margin: 60px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px #0001;
            padding: 32px 28px 24px 28px;
        }
        h2 { text-align: center; color: #2d3e50; margin-bottom: 24px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; color: #444; }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #bbb;
            border-radius: 8px;
            font-size: 1rem;
        }
        .btn {
            width: 100%;
            background: #ffb300;
            color: #222;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .btn:hover { background: #2d3e50; color: #fff; }
        .checkout-area {
            margin-top: 32px;
            text-align: center;
        }
        .msg-sucesso {
            color: #2d3e50;
            background: #e0ffd8;
            border: 1px solid #b2e6a8;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 18px;
            text-align: center;
        }
        .msg-erro {
            color: #c00;
            background: #ffe0e0;
            border: 1px solid #e6b2b2;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 18px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Comprar Acesso</h2>
        <?php if ($sucesso): ?>
            <div class="msg-sucesso">Pagamento realizado! Você receberá os dados de acesso por e-mail.</div>
        <?php endif; ?>
        <div id="msg-erro" class="msg-erro" style="display:none;"></div>
        <form method="post" id="form-comprar">
            <div class="form-group">
                <label for="nome">Nome completo</label>
                <input type="text" name="nome" id="nome" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" required>
            </div>
            <button type="submit" class="btn">Ir para o pagamento</button>
        </form>
        <div class="checkout-area" id="checkout-area" style="display:none;">
            <p>Carregando checkout seguro...</p>
            <div id="wallet_container"></div>
        </div>
    </div>
    <script>
    const mp = new MercadoPago('APP_USR-70db0715-bad2-46ca-b67c-269218f67c1f');
    document.getElementById('form-comprar').onsubmit = async function(e) {
        e.preventDefault();
        document.getElementById('msg-erro').style.display = 'none';
        const nome = document.getElementById('nome').value.trim();
        const email = document.getElementById('email').value.trim();
        if (!nome || !email) return;
        document.getElementById('form-comprar').style.display = 'none';
        document.getElementById('checkout-area').style.display = 'block';
        try {
            const resp = await fetch('criar-preferencia.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ nome, email })
            });
            const data = await resp.json();
            if (data && data.id) {
                document.getElementById('checkout-area').innerHTML = '<div id="wallet_container"></div>';
                mp.bricks().create("wallet", "wallet_container", {
                    initialization: {
                        preferenceId: data.id
                    },
                    customization: {
                        texts: {
                            valueProp: 'security_safety'
                        }
                    }
                });
            } else {
                throw new Error(data.erro || 'Erro ao criar preferência de pagamento.');
            }
        } catch (err) {
            document.getElementById('checkout-area').style.display = 'none';
            document.getElementById('form-comprar').style.display = 'block';
            document.getElementById('msg-erro').innerText = err.message;
            document.getElementById('msg-erro').style.display = 'block';
        }
    };
    </script>
</body>
</html> 