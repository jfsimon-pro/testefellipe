<?php
// Landing page - Vitrine de Cursos
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma Educacional Fellipe Ferini</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0284c7">
    <link rel="icon" href="/icons/icon-192.png">
    <style>
        body {
            margin: 0;
            font-family: 'Montserrat', Arial, sans-serif;
            background: #f7f8fa;
            color: #222;
        }
        header {
            background: #2d3e50;
            color: #fff;
            padding: 24px 0 16px 0;
            text-align: center;
            box-shadow: 0 2px 8px #0001;
        }
        header h1 {
            margin: 0;
            font-size: 2.2rem;
            letter-spacing: 1px;
        }
        .cta-btn {
            background: #ffb300;
            color: #222;
            border: none;
            padding: 12px 32px;
            border-radius: 24px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 16px;
            transition: background 0.2s;
        }
        .cta-btn:hover {
            background: #ffa000;
        }
        .hero {
            background: linear-gradient(90deg, #2d3e50 60%, #ffb300 100%);
            color: #fff;
            padding: 48px 16px 32px 16px;
            text-align: center;
        }
        .hero h2 {
            font-size: 2rem;
            margin-bottom: 12px;
        }
        .hero p {
            font-size: 1.1rem;
            margin-bottom: 24px;
        }
        .courses-section {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 16px;
        }
        .courses-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 24px;
            color: #2d3e50;
            text-align: center;
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 28px;
        }
        .course-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px #0001;
            padding: 24px 20px 20px 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .course-card:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 6px 24px #0002;
        }
        .course-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .course-meta {
            font-size: 0.98rem;
            color: #666;
            margin-bottom: 16px;
        }
        .course-type {
            display: inline-block;
            background: #ffb30022;
            color: #b47a00;
            border-radius: 8px;
            padding: 2px 10px;
            font-size: 0.92rem;
            margin-right: 8px;
        }
        .course-category {
            display: inline-block;
            background: #2d3e5011;
            color: #2d3e50;
            border-radius: 8px;
            padding: 2px 10px;
            font-size: 0.92rem;
        }
        .course-btn {
            margin-top: auto;
            background: #2d3e50;
            color: #fff;
            border: none;
            padding: 8px 22px;
            border-radius: 16px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .course-btn:hover {
            background: #ffb300;
            color: #222;
        }
        footer {
            background: #2d3e50;
            color: #fff;
            text-align: center;
            padding: 18px 0 12px 0;
            margin-top: 48px;
            font-size: 1rem;
        }
        @media (max-width: 600px) {
            .hero h2 { font-size: 1.3rem; }
            .courses-title { font-size: 1.1rem; }
        }
    </style>
</head>
<body>
    <header>
        <h1>Plataforma Educacional Fellipe Ferini</h1>
        <a href="/comprar-acesso"><button class="cta-btn">Comprar Acesso</button></a>
    </header>
    <section class="hero">
        <h2>Aprenda com os melhores cursos online!</h2>
        <p>Estude no seu ritmo, acompanhe seu progresso e tenha acesso vitalício aos conteúdos.<br>Faça parte da nossa comunidade de alunos!</p>
        <a href="/comprar-acesso"><button class="cta-btn">Quero Começar Agora</button></a>
    </section>
    <section class="courses-section">
        <div class="courses-title">Vitrine de Cursos</div>
        <div class="courses-grid">
            <!-- Cursos fictícios -->
            <div class="course-card">
                <div class="course-title">Matemática Essencial</div>
                <div class="course-meta">
                    <span class="course-type">PDF Único</span>
                    <span class="course-category">Exatas</span>
                </div>
                <div>Domine os principais conceitos de matemática para vestibulares e concursos.</div>
                <a href="#"><button class="course-btn">Saiba mais</button></a>
            </div>
            <div class="course-card">
                <div class="course-title">Inglês para Iniciantes</div>
                <div class="course-meta">
                    <span class="course-type">Aulas</span>
                    <span class="course-category">Idiomas</span>
                </div>
                <div>Aprenda inglês do zero com aulas práticas e material de apoio.</div>
                <a href="#"><button class="course-btn">Saiba mais</button></a>
            </div>
            <div class="course-card">
                <div class="course-title">Programação em PHP</div>
                <div class="course-meta">
                    <span class="course-type">Aulas</span>
                    <span class="course-category">Tecnologia</span>
                </div>
                <div>Construa aplicações web do básico ao avançado com PHP moderno.</div>
                <a href="#"><button class="course-btn">Saiba mais</button></a>
            </div>
            <div class="course-card">
                <div class="course-title">Redação Nota 1000</div>
                <div class="course-meta">
                    <span class="course-type">PDF Único</span>
                    <span class="course-category">Linguagens</span>
                </div>
                <div>Dicas e técnicas para escrever redações de alto nível para ENEM e vestibulares.</div>
                <a href="#"><button class="course-btn">Saiba mais</button></a>
            </div>
            <div class="course-card">
                <div class="course-title">História do Brasil</div>
                <div class="course-meta">
                    <span class="course-type">Aulas</span>
                    <span class="course-category">Humanas</span>
                </div>
                <div>Entenda os principais fatos e períodos da história do nosso país.</div>
                <a href="#"><button class="course-btn">Saiba mais</button></a>
            </div>
        </div>
    </section>
    <footer>
        &copy; <?php echo date('Y'); ?> Plataforma Fellipe Ferini. Todos os direitos reservados.
    </footer>
    <button id="btnInstallPWA" style="display:none;position:fixed;bottom:24px;right:24px;z-index:9999;padding:12px 24px;background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;border:none;border-radius:24px;font-size:1rem;box-shadow:0 2px 8px rgba(0,0,0,0.12);cursor:pointer;">
  <i class="fa fa-download" style="margin-right:8px"></i> Baixar app
</button>
    <script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/service-worker.js');
  });
}
let deferredPrompt;
const btnInstall = document.getElementById('btnInstallPWA');
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  btnInstall.style.display = 'block';
});
btnInstall.addEventListener('click', async () => {
  if (deferredPrompt) {
    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    if (outcome === 'accepted') {
      btnInstall.style.display = 'none';
    }
    deferredPrompt = null;
  }
});
</script>
</body>
</html>