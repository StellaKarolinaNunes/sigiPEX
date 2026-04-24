<?php
session_start();
$logado = isset($_SESSION['siape']);
$nome_professor = $logado ? $_SESSION['nome_professor'] : "";

// Busca projetos para exibir na vitrine
try {
  $pdo = new PDO("mysql:host=localhost;dbname=sigipex;charset=utf8mb4", "root", "");
  $stmt = $pdo->query("SELECT * FROM projetos ORDER BY codigo_projeto DESC");
  $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $extensao = array_filter($projetos, function ($p) {
    return $p['categoria_projeto'] === 'Extensão';
  });
  $pesquisa = array_filter($projetos, function ($p) {
    return $p['categoria_projeto'] === 'Pesquisa';
  });

  // Coleta Áreas (Linguagens) e Status únicos para os filtros
  $areas = [];
  $situacoes = [];
  foreach ($projetos as $p) {
    if (!empty($p['linguagem_projeto'])) {
      $langs = explode(", ", $p['linguagem_projeto']);
      foreach ($langs as $l)
        $areas[] = trim($l);
    }
    if (!empty($p['status'])) {
      $situacoes[] = $p['status'];
    }
  }
  $areas = array_unique(array_filter($areas));
  $situacoes = array_unique(array_filter($situacoes));
  sort($areas);
  sort($situacoes);

} catch (Exception $e) {
  $extensao = [];
  $pesquisa = [];
  $areas = [];
  $situacoes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SigiPEX - Organize, Inove e Transforme!</title>
  <link rel="shortcut icon" href="./assets/images/logo.png" />
  <link
    href="https://fonts.googleapis.com/css2?family=Philosopher:wght@700&family=Poppins:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

  <style>
    :root {
      --primary: #1a8e4c;
      --primary-light: #f1f9f5;
      --text-dark: #1e293b;
      --text-muted: #64748b;
      --bg-hero: #f0f7f3;
      /* Verde hero um pouco mais perceptível */
      --white: #ffffff;
      --shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      color: var(--text-dark);
      background: var(--white);
      overflow-x: hidden;
    }

    .container {
      max-width: 1450px;
      /* Aumentado para escala widescreen */
      margin: 0 auto;
      padding: 0 50px;
    }

    /* NAVBAR PREMIUM */
    .navbar {
      height: 90px;
      display: flex;
      align-items: center;
      background: var(--white);
      position: sticky;
      top: 0;
      z-index: 1000;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .nav-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
    }

    .nav-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      font-family: 'Philosopher', sans-serif;
      font-size: 2.4rem;
      font-weight: 700;
      color: #1e293b;
      text-decoration: none;
    }

    .nav-logo img {
      width: 40px;
    }

    .nav-links {
      display: flex;
      list-style: none;
      gap: 30px;
    }

    .nav-link {
      text-decoration: none;
      color: #64748b;
      font-weight: 500;
      transition: 0.3s;
      padding-bottom: 8px;
      font-size: 0.95rem;
    }

    .nav-link.active {
      color: var(--primary);
      border-bottom: 3px solid #8ccda8;
      /* Verde mais claro para a borda */
      font-weight: 600;
    }

    .nav-link:hover {
      color: var(--primary);
    }

    .btn-login {
      background: var(--primary);
      color: var(--white);
      padding: 12px 28px;
      border-radius: 100px;
      text-decoration: none;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: 0.3s;
      font-size: 0.95rem;
    }

    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(26, 142, 76, 0.25);
    }

    .hero-section {
      padding: 100px 0 140px;
      background: var(--bg-hero);
      position: relative;
    }

    .hero-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 80px;
      align-items: center;
    }

    .hero-content {
      max-width: 600px;
    }

    .hero-title {
      font-family: 'Philosopher', sans-serif;
      font-size: 6.5rem;
      line-height: 1;
      color: #1e293b;
      margin: 0;
      font-weight: 700;
    }

    .u-diamond {
      display: flex;
      align-items: center;
      gap: 15px;
      margin: 25px 0 35px;
      width: 240px;
    }

    .u-diamond::before,
    .u-diamond::after {
      content: "";
      flex: 1;
      height: 2.5px;
      background: #c2dfce;
      border-radius: 10px;
    }

    .u-diamond span {
      width: 14px;
      height: 14px;
      background: var(--primary);
      transform: rotate(45deg);
      display: block;
    }

    .hero-subtitle {
      font-size: 1.25rem;
      color: var(--text-muted);
      margin-bottom: 50px;
      line-height: 1.6;
      max-width: 500px;
    }

    .hero-btns {
      display: flex;
      gap: 20px;
    }

    .hero-btn-solid {
      background: var(--primary);
      color: white;
      padding: 18px 38px;
      border-radius: 14px;
      text-decoration: none;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 12px;
      transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-size: 1rem;
    }

    .hero-btn-solid:hover {
      background: #15733d;
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(26, 142, 76, 0.2);
    }

    .hero-btn-outline {
      border: 1.5px solid #c2dfce;
      background: transparent;
      color: var(--primary);
      padding: 18px 38px;
      border-radius: 14px;
      text-decoration: none;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 12px;
      transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-size: 1rem;
    }

    .hero-btn-outline:hover {
      background: white;
      border-color: var(--primary);
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }

    .hero-visual {
      position: relative;
      display: flex;
      justify-content: flex-end;
      padding-right: 40px;
    }

    .hero-img-bg {
      position: absolute;
      bottom: -35px;
      left: -20px;
      width: 90%;
      height: 90%;
      background: var(--primary);
      border-radius: 20px;
      z-index: 1;
    }

    .hero-img-card {
      position: relative;
      z-index: 2;
      background: white;
      padding: 30px;
      border-radius: 30px;
      box-shadow: 0 40px 100px rgba(0, 0, 0, 0.06);
      width: 100%;
      max-width: 580px;
    }

    .hero-img-card img {
      width: 100%;
      height: auto;
      border-radius: 20px;
    }

    /* SEÇÃO DE BENEFÍCIOS - LAYOUT LATERAL (CONFORME IMAGEM) */
    .benefits-section {
      padding: 120px 0;
      background: #fff;
      overflow: hidden;
    }

    .benefits-container {
      display: flex;
      gap: 80px;
      align-items: center;
    }

    .benefits-info {
      flex: 0 0 350px;
      text-align: left;
    }

    .benefits-slider-wrapper {
      flex: 1;
      min-width: 0;
      padding: 20px 0;
    }

    .section-header {
      text-align: center;
      margin-bottom: 60px;
      width: 100%;
    }

    .section-tag {
      color: var(--primary);
      font-weight: 800;
      font-size: 0.85rem;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      margin-bottom: 15px;
      display: block;
      text-align: center;
    }

    .section-title {
      font-family: 'Philosopher', sans-serif;
      font-size: 3.2rem;
      color: #1e293b;
      margin-bottom: 20px;
      line-height: 1.1;
      font-weight: 700;
      text-align: center;
    }

    .section-sub {
      color: var(--text-muted);
      font-size: 1.15rem;
      line-height: 1.6;
      margin-bottom: 30px;
    }

    .benefits-line {
      width: 50px;
      height: 4px;
      background: #c2dfce;
      border-radius: 10px;
    }

    /* CARDS DE ELITE - ESCALA AMPLIADA */
    .elite-card {
      background: var(--white);
      padding: 70px 50px;
      /* Preenchimento generoso */
      border-radius: 20px;
      border: 1px solid #f1f5f9;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      text-align: left;
      transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      height: 100%;
      min-height: 700px;
      /* Aumentado significativamente */
      position: relative;
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.04);
      overflow: hidden;
      margin: 20px 0;
    }

    .elite-card:hover {
      transform: translateY(-12px);
      box-shadow: 0 30px 80px rgba(26, 142, 76, 0.15);
    }

    .elite-card::after {
      content: "";
      position: absolute;
      bottom: -30px;
      right: -30px;
      width: 140px;
      height: 140px;
      background: rgba(26, 142, 76, 0.12);
      clip-path: polygon(100% 0, 0% 100%, 100% 100%);
      z-index: 0;
    }

    .square-icon-box {
      position: relative;
      width: 75px;
      /* Ícone maior */
      height: 75px;
      margin-bottom: 45px;
      z-index: 2;
    }

    .sq-shadow {
      position: absolute;
      bottom: -6px;
      right: -6px;
      width: 100%;
      height: 100%;
      background: #4a8c6a;
      border-radius: 8px;
      z-index: 1;
    }

    .sq-main {
      position: relative;
      width: 100%;
      height: 100%;
      background: #1a8e4c;
      border-radius: 8px;
      z-index: 2;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 2.2rem;
      /* Ícone maior */
    }

    .card-h3 {
      font-family: 'Philosopher', sans-serif;
      font-size: 2.3rem;
      /* Título maior */
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 30px;
      line-height: 1.1;
      z-index: 2;
    }

    .card-p {
      font-size: 1.15rem;
      /* Texto maior */
      color: #64748b;
      line-height: 1.8;
      z-index: 2;
      flex-grow: 1;
    }

    /* BARRA DE FILTROS (ESTILO IMAGEM) */
    .filter-bar {
      display: flex;
      justify-content: center;
      margin-bottom: 50px;
      gap: 15px;
      position: relative;
      flex-wrap: wrap;
    }

    .filter-select {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(26, 142, 76, 0.2);
      padding: 12px 25px;
      border-radius: 12px;
      color: #1a8e4c;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      width: 200px;
      transition: 0.4s;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
    }

    .filter-select:hover {
      border-color: var(--primary);
      background: white;
      transform: translateY(-2px);
    }

    .btn-ver-todos {
      background: var(--primary);
      border: none;
      color: white;
      padding: 12px 25px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 0.9rem;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: 0.4s;
      box-shadow: 0 8px 18px rgba(26, 142, 76, 0.2);
    }

    .btn-ver-todos:hover {
      background: #15733d;
      transform: translateY(-2px);
      box-shadow: 0 12px 25px rgba(26, 142, 76, 0.3);
    }

    /* NAVEGAÇÃO LADO A LADO DO SLIDER (ESTILO IMAGEM) */
    .slider-container-box {
      position: relative;
      width: 100%;
      margin-bottom: 80px;
      padding: 0 10px;
      /* Pequeno respiro para não encostar na borda */
    }

    .swiper-projects {
      width: 100%;
      height: 100%;
    }

    .slider-arrows {
      position: absolute;
      right: -90px;
      top: 50%;
      transform: translateY(-50%);
      display: flex;
      flex-direction: column;
      gap: 15px;
      z-index: 10;
    }

    .project-arrow {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      border: 1px solid #e2e8f0;
      background: white;
      color: #94a3b8;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: 0.3s;
      font-size: 1.2rem;
    }

    .project-arrow:hover {
      border-color: var(--primary);
      color: var(--primary);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* SEÇÃO BENEFÍCIOS - ALINHAMENTO CENTRALIZADO PREMIUM */
    .benefits-section {
      padding: 100px 0;
      background: #fff;
      overflow: hidden;
    }

    .benefits-header {
      text-align: center;
      max-width: 900px;
      margin: 0 auto 60px auto;
    }

    .benefits-slider-box {
      width: 100%;
      position: relative;
    }

    .mySwiper {
      padding-bottom: 70px !important;
    }
 
    .u-diamond {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      margin: 25px auto;
    }
    .u-diamond::before, .u-diamond::after {
      content: "";
      height: 1.5px;
      width: 50px;
      background: #1a8e4c;
      opacity: 0.25;
    }
    .u-diamond span {
      position: relative;
      width: 12px;
      height: 12px;
      background: #1a8e4c;
      transform: rotate(45deg);
      opacity: 0.4;
      display: block;
      box-shadow: 4px -4px 0 -1px rgba(26, 142, 76, 0.4);
    }

    /* SEÇÃO BENEFÍCIOS - LAYOUT SPLIT IDENTICO */
    .benefits-split {
      display: grid;
      grid-template-columns: 350px 1fr;
      gap: 60px;
      align-items: flex-start;
    }

    .benefits-text-side {
      text-align: left;
    }

    .benefits-text-side h2 {
      font-size: 2.8rem;
      line-height: 1.1;
      margin: 15px 0 25px;
      color: #1e293b;
    }

    .benefits-text-side .line-green {
      width: 60px;
      height: 3px;
      background: var(--primary);
      border-radius: 10px;
    }

    .benefits-cards-side {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      /* Única linha com 4 cards */
      gap: 20px;
    }

    .mini-benefit-card {
      background: white;
      border-radius: 12px;
      padding: 30px 20px;
      text-align: center;
      border: 1px solid #f1f5f9;
      transition: 0.3s;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
    }

    .mini-benefit-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.05);
    }

    .mini-icon-box {
      width: 65px;
      height: 65px;
      background: #f8fafc;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      color: var(--primary);
      margin-bottom: 20px;
    }

    .mini-benefit-card h4 {
      font-size: 1.1rem;
      color: #1e293b;
      margin-bottom: 15px;
    }

    .mini-benefit-card p {
      font-size: 0.85rem;
      color: #64748b;
      line-height: 1.5;
      margin-bottom: 20px;
    }

    .card-footer-line {
      width: 35px;
      height: 3px;
      background: #cbd5e1;
      border-radius: 10px;
      margin-top: auto;
    }

    .mini-benefit-card:hover .card-footer-line {
      background: #1a8e4c;
      width: 50px;
      transition: 0.3s;
    }

    /* SEÇÃO CIENTISTAS (REFINADO) */
    .team-section {
      background: #fbfcfb;
      padding: 100px 0;
    }

    .team-card {
      background: #f1f9f5;
      /* Verde clarinho identico */
      border: 1px solid #e2f2e9;
      border-radius: 12px;
      padding: 25px;
      display: flex;
      gap: 25px;
      align-items: center;
    }

    .team-img {
      width: 140px;
      height: 140px;
      border-radius: 8px;
      object-fit: cover;
    }

    .team-info h3 {
      font-size: 1.4rem;
      font-weight: 700;
      color: #1e293b;
    }

    .team-info p {
      color: var(--primary);
      font-weight: 600;
      font-size: 0.85rem;
      margin-bottom: 15px;
    }

    .social-links {
      display: flex;
      gap: 15px;
    }

    .social-links ion-icon {
      font-size: 1.3rem;
      color: #1a8e4c;
      cursor: pointer;
      opacity: 0.7;
      transition: 0.3s;
    }

    .social-links ion-icon:hover {
      opacity: 1;
      transform: scale(1.1);
    }

    /* CARD DE PROJETO (REFINAMENTO FINAL) */
    .project-card {
      background: white;
      padding: 35px;
      border-radius: 16px;
      border: 1px solid #e1e8e4;
      display: flex;
      flex-direction: column;
      text-align: left;
      height: 100%;
      min-height: 420px;
      transition: 0.4s ease;
      position: relative;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.03);
    }

    .project-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 25px 60px rgba(26, 142, 76, 0.08);
    }

    .project-tag {
      font-size: 0.7rem;
      font-weight: 800;
      color: #15733d;
      text-transform: uppercase;
      margin-bottom: 25px;
      letter-spacing: 0.5px;
      background: #e2f2e9;
      padding: 7px 16px;
      border-radius: 8px;
      width: max-content;
    }

    .project-title {
      font-family: 'Poppins', sans-serif;
      font-size: 1.45rem;
      font-weight: 700;
      color: #1e293b;
      line-height: 1.3;
      margin-bottom: 15px;
      min-height: 3.8rem;
    }

    .project-desc {
      font-size: 0.95rem;
      color: #64748b;
      line-height: 1.7;
      margin-bottom: 30px;
      flex-grow: 1;
    }

    .project-contributors {
      background: #f8fafc;
      padding: 15px;
      border-radius: 12px;
      font-size: 0.85rem;
      color: #475569;
      margin-bottom: 25px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      border-left: 4px solid var(--primary);
    }

    .project-contributors strong {
      color: #1e293b;
      font-weight: 700;
    }

    .project-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 20px;
      border-top: 1px solid #f1f5f9;
    }

    .footer-inst {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 700;
      font-size: 0.9rem;
      color: #1e293b;
    }

    .tech-tag {
      background: #f1f9f5;
      color: #1a8e4c;
      padding: 8px 18px;
      border-radius: 100px;
      font-size: 0.75rem;
      font-weight: 700;
    }

    .action-circle {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: #f1f9f5;
      color: #1a8e4c;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      cursor: pointer;
      transition: 0.3s;
    }

    .action-circle:hover {
      background: var(--primary);
      color: white;
      transform: rotate(-15deg);
    }

    /* NAVEGAÇÃO LADO A LADO DO SLIDER (ESTILO IMAGEM) */
    .slider-arrows {
      position: absolute;
      right: -70px;
      top: 50%;
      transform: translateY(-50%);
      display: flex;
      flex-direction: column;
      gap: 15px;
      z-index: 10;
    }

    .project-arrow {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      border: 1px solid #f1f5f9;
      background: white;
      color: #1a8e4c;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: 0.3s;
      font-size: 1.2rem;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .project-arrow:hover {
      background: var(--primary);
      color: white;
      transform: scale(1.1);
    }

    /* SEÇÃO CIENTISTAS (TEAM) */
    .team-section {
      padding: 100px 0;
      background: #fff;
    }

    .team-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
      gap: 40px;
      margin-top: 50px;
    }

    .team-card {
      background: #f8fafc;
      border-radius: 20px;
      padding: 25px;
      display: flex;
      align-items: center;
      gap: 25px;
      border: 1px solid #f1f5f9;
      transition: 0.3s;
    }

    .team-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
    }

    .team-img {
      width: 130px;
      height: 130px;
      border-radius: 15px;
      object-fit: cover;
      border: 3px solid white;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .team-info h3 {
      font-size: 1.35rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 5px;
    }

    .team-info p {
      font-size: 0.85rem;
      color: var(--primary);
      font-weight: 600;
      margin-bottom: 15px;
    }

    .team-social {
      display: flex;
      gap: 12px;
    }

    .social-circ {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      background: white;
      border: 1px solid #e2e8f0;
      color: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      transition: 0.3s;
      cursor: pointer;
    }

    .social-circ:hover {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }

    /* AJUSTE FINO NAS TAGS DE PROJETO */
    .project-tag.tech {
      color: #10b981;
      background: rgba(16, 185, 129, 0.08);
      padding: 4px 12px;
      border-radius: 6px;
    }

    /* BOTÕES DE ADMIN (SUTIS NO HOVER) */
    .admin-controls {
      position: absolute;
      top: 15px;
      right: 15px;
      display: flex;
      gap: 8px;
      z-index: 10;
      opacity: 0;
      transform: translateY(-10px);
      transition: 0.3s ease;
    }

    .project-card:hover .admin-controls {
      opacity: 1;
      transform: translateY(0);
    }

    .admin-btn {
      width: 32px;
      height: 32px;
      background: white;
      color: #64748b;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      transition: 0.3s;
    }

    .admin-btn:hover {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }

    /* USER MENU PREMIUM */
    .user-menu {
      position: relative;
      z-index: 1001;
      padding-bottom: 25px;
      margin-bottom: -25px;
    }

    .user-pill {
      background: var(--primary);
      color: white;
      padding: 12px 28px;
      border-radius: 100px;
      border: none;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 12px;
      cursor: pointer;
      box-shadow: 0 8px 25px rgba(26, 142, 76, 0.3);
      transition: 0.3s;
    }

    .user-pill:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 30px rgba(26, 142, 76, 0.4);
    }

    .dropdown {
      display: none;
      position: absolute;
      top: 65px;
      right: 0;
      background: white;
      min-width: 280px;
      border-radius: 24px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
      list-style: none;
      overflow: hidden;
      z-index: 1001;
      border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .user-menu:hover .dropdown {
      display: block;
      animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* BLUR OVERLAY */
    .blur-overlay {
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(255, 255, 255, 0.4);
      backdrop-filter: blur(8px);
      z-index: 1000;
      opacity: 0;
      pointer-events: none;
      transition: 0.4s;
    }

    .user-menu:hover ~ .blur-overlay {
      opacity: 1;
    }

    .dropdown-item {
      padding: 15px 25px;
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: var(--text-dark);
      font-weight: 500;
      transition: 0.2s;
    }

    .dropdown-item:hover {
      background: var(--primary-light);
      color: var(--primary);
    }

    /* FOOTER */
    .footer {
      background: #f8fafc;
      padding: 80px 0 40px;
      border-top: 1px solid #f1f5f9;
    }

    .footer-grid {
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr;
      gap: 60px;
    }

    .footer-logo {
      font-family: 'Philosopher', sans-serif;
      font-size: 2rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 20px;
      display: block;
      text-decoration: none;
    }

    .footer-text {
      color: var(--text-muted);
      line-height: 1.6;
      margin-bottom: 25px;
    }

    .footer-list {
      list-style: none;
    }

    .footer-list li {
      margin-bottom: 12px;
    }

    .footer-list a {
      text-decoration: none;
      color: var(--text-muted);
      transition: 0.3s;
    }

    .footer-list a:hover {
      color: var(--primary);
    }
  </style>
</head>

<body>

  <div class="blur-overlay"></div>

  <header class="navbar">
    <div class="container nav-container">
      <a href="index.php" class="nav-logo">
        <img src="./assets/images/logo.png" style="width: 45px;">
        <span>SigiPEX</span>
      </a>

      <ul class="nav-links">
        <li><a href="#home" class="nav-link active">Home</a></li>
        <li><a href="#benefits" class="nav-link">Benefícios</a></li>
        <li><a href="#projetos" class="nav-link">Projetos</a></li>
        <li><a href="#ajuda" class="nav-link">Ajuda</a></li>
        <li><a href="#sobre" class="nav-link">Sobre</a></li>
        <li><a href="#contato" class="nav-link">Contato</a></li>
      </ul>

      <?php if ($logado): ?>
        <div class="user-menu">
          <button class="user-pill">
            <ion-icon name="person-circle-outline" style="font-size: 1.8rem;"></ion-icon>
            <span><?php echo explode(" ", $nome_professor)[0]; ?></span>
            <ion-icon name="chevron-down-outline" style="font-size: 0.8rem;"></ion-icon>
          </button>
          <ul class="dropdown">
            <li><a href="painel.html?tab=configuracoes" class="dropdown-item"><ion-icon
                  name="settings-outline"></ion-icon> Perfil</a></li>
            <li><a href="painel.html?tab=criarProjetos" class="dropdown-item"><ion-icon name="add-outline"></ion-icon>
                Criar Projeto</a></li>
            <li><a href="deslogar_usuario.php" class="dropdown-item" style="color: #e74c3c;"><ion-icon
                  name="log-out-outline"></ion-icon> Sair</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a href="login_cadastro.html" class="btn-login"><ion-icon name="person-outline"></ion-icon> Login</a>
      <?php endif; ?>
    </div>
  </header>

  <main>
    <!-- HERO SECTION (ESTILO IMAGEM) -->
    <section class="hero-section" id="home">
      <div class="container hero-container">
        <div class="hero-content">
          <h1 class="hero-title">SigiPEX</h1>
          <div class="u-diamond" style="margin-bottom: 25px;"><span></span></div>
          <p class="hero-subtitle">Um sistema prático para cadastrar, gerenciar e acompanhar projetos de extensão e
            pesquisa, facilitando a organização acadêmica.</p>
          <div class="hero-btns">
            <a href="#benefits" class="hero-btn-solid"><ion-icon name="book-outline"></ion-icon> Saiba mais</a>
            <a href="#projetos" class="hero-btn-outline"><ion-icon name="apps-outline"></ion-icon> Conheça os
              projetos</a>
          </div>
        </div>
        <div class="hero-visual">
          <div class="hero-img-bg"></div>
          <div class="hero-img-card">
            <img src="./assets/images/home.jpg" alt="Equipe SigiPEX" style="object-fit: contain; padding: 20px;">
          </div>
        </div>
      </div>
    </section>

    <!-- BENEFÍCIOS SPLIT (ÚNICA LINHA - DESIGN IDENTICO) -->
    <section class="section-padding" id="benefits" style="background: #fff; padding: 120px 0;">
      <div class="container">
        <div class="benefits-split">
          <div class="benefits-text-side">
            <span class="section-tag">POR QUE USAR O SIGIPEX?</span>
            <h2>Benefícios do SigiPEX</h2>
            <div class="line-green"></div>
          </div>

          <div class="benefits-cards-side">
            <!-- ORGANIZAÇÃO -->
            <div class="mini-benefit-card">
              <div class="mini-icon-box"><ion-icon name="people-outline"></ion-icon></div>
              <h4>Organização</h4>
              <p>Gerencie e acompanhe projetos de forma rápida e eficiente.</p>
              <div class="card-footer-line"></div>
            </div>
            <!-- ACOMPANHAMENTO -->
            <div class="mini-benefit-card">
              <div class="mini-icon-box"><ion-icon name="stats-chart-outline"></ion-icon></div>
              <h4>Acompanhamento</h4>
              <p>Acompanhe o andamento dos projetos em tempo real.</p>
              <div class="card-footer-line"></div>
            </div>
            <!-- COLABORAÇÃO -->
            <div class="mini-benefit-card">
              <div class="mini-icon-box"><ion-icon name="people-circle-outline"></ion-icon></div>
              <h4>Colaboração</h4>
              <p>Facilite o trabalho em equipe e a troca de informações.</p>
              <div class="card-footer-line"></div>
            </div>
            <!-- SEGURANÇA -->
            <div class="mini-benefit-card">
              <div class="mini-icon-box"><ion-icon name="shield-checkmark-outline"></ion-icon></div>
              <h4>Segurança</h4>
              <p>Seus dados protegidos com segurança e confiabilidade.</p>
              <div class="card-footer-line"></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SEÇÃO VITRINE (FUNDÃO VERDE CONTÍNUO) -->
    <div style="background: var(--bg-hero); padding: 40px 0;">
      
      <!-- PROJETOS DE EXTENSÃO -->
      <section class="section-padding" id="projetos" style="background: transparent;">
        <div class="container">
          <div class="section-header">
            <span class="section-tag">VITRINE ACADÊMICA</span>
            <h2 class="section-title">Projetos de Extensão</h2>
            <div class="u-diamond" style="margin: 0 auto 30px;"><span></span></div>
          </div>

          <!-- BARRA DE FILTROS -->
          <div class="filter-bar">
            <select class="filter-select" id="filterArea">
              <option value="">Todas as áreas</option>
              <?php foreach ($areas as $area): ?>
                <option value="<?php echo htmlspecialchars($area); ?>"><?php echo htmlspecialchars($area); ?></option>
              <?php endforeach; ?>
            </select>

            <select class="filter-select" id="filterStatus">
              <option value="">Situação</option>
              <?php foreach ($situacoes as $situacao): ?>
                <option value="<?php echo htmlspecialchars($situacao); ?>"><?php echo htmlspecialchars($situacao); ?>
                </option>
              <?php endforeach; ?>
            </select>

            <select class="filter-select" id="sortProject">
              <option value="recent">Ordenar por: Recentes</option>
              <option value="old">Ordenar por: Antigos</option>
              <option value="alpha">Ordenar por: A-Z</option>
            </select>

            <a href="#" class="btn-ver-todos"><ion-icon name="arrow-redo-outline"></ion-icon> Ver Todos</a>
          </div>

          <!-- SLIDER CONTAINER -->
          <div class="slider-container-box">
            <div class="swiper swiperExt">
              <div class="swiper-wrapper"
                style="<?php echo empty($extensao) ? 'display: flex; justify-content: center;' : ''; ?>">
                <?php if (empty($extensao)): ?>
                  <div class="swiper-slide" style="display: flex; justify-content: center; width: 100%;">
                    <div style="text-align: center; padding: 80px 20px; color: var(--text-muted); width: 100%;">
                      <ion-icon name="folder-open-outline"
                        style="font-size: 3.5rem; margin-bottom: 15px; opacity: 0.3;"></ion-icon>
                      <p style="font-size: 1.1rem;">Nenhum projeto encontrado nesta categoria.</p>
                    </div>
                  </div>
                <?php else: ?>
                  <?php foreach ($extensao as $p): ?>
                    <div class="swiper-slide project-slide" 
                         data-area="<?php echo htmlspecialchars($p['area_projeto'] ?? ''); ?>" 
                         data-status="<?php echo htmlspecialchars($p['situacao_projeto'] ?? ''); ?>"
                         data-title="<?php echo htmlspecialchars($p['nome_projeto']); ?>"
                         data-id="<?php echo $p['codigo_projeto']; ?>">
                      <div class="project-card">
                        <div class="admin-controls">
                          <?php if ($logado && ($_SESSION['nivel_acesso'] == 1 || $p['siape_professor'] == $_SESSION['siape'])): ?>
                            <button class="admin-btn"
                              onclick="location.href='painel.html?edit=<?php echo $p['codigo_projeto']; ?>'"><ion-icon
                                name="pencil-outline"></ion-icon></button>
                            <button class="admin-btn" onclick="excluirProjeto('<?php echo $p['codigo_projeto']; ?>')"><ion-icon
                                name="trash-outline"></ion-icon></button>
                          <?php endif; ?>
                        </div>
                        <span class="project-tag"><?php echo strtoupper($p['categoria_projeto']); ?></span>
                        <h3 class="project-title"><?php echo htmlspecialchars($p['nome_projeto']); ?></h3>
                        <p class="project-desc">
                          <?php echo mb_strimwidth(htmlspecialchars($p['resumo_projeto']), 0, 110, "..."); ?></p>

                        <div class="project-contributors">
                          <span><strong>Orientador:</strong>
                            <?php echo htmlspecialchars($p['orientador_projeto'] ?? 'Docente IFPA'); ?></span>
                          <?php if (!empty($p['coorientador_projeto'])): ?>
                            <span><strong>Coorientador:</strong>
                              <?php echo htmlspecialchars($p['coorientador_projeto']); ?></span>
                          <?php endif; ?>
                        </div>

                        <div class="project-footer">
                          <div class="footer-inst">
                            <ion-icon name="person-outline"></ion-icon> IFPA
                          </div>
                          <div class="footer-right">
                            <div class="tech-tag">
                              <?php echo htmlspecialchars($p['linguagem_projeto'] ?? 'Geral'); ?>
                            </div>
                            <div class="action-circle"
                              onclick="window.location.href='projeto_detalhe.php?id=<?php echo $p['codigo_projeto']; ?>'">
                              <ion-icon name="chevron-forward-outline"></ion-icon>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
              <div class="swiper-pagination swiper-pagination-projects"></div>
            </div>

            <!-- SETAS LATERAIS -->
            <div class="slider-arrows">
              <div class="project-arrow swiper-button-prev-ext"><ion-icon name="arrow-back-outline"></ion-icon></div>
              <div class="project-arrow swiper-button-next-ext"><ion-icon name="arrow-forward-outline"></ion-icon></div>
            </div>
          </div>
        </div>
      </section>

      <!-- DIVISOR SUTIL -->
      <div class="container" style="height: 1px; background: rgba(26, 142, 76, 0.08); margin: 40px auto;"></div>

      <!-- PROJETOS DE PESQUISA -->
      <section class="section-padding" style="background: transparent;">
        <div class="container">
          <div class="section-header">
            <span class="section-tag">VITRINE ACADÊMICA</span>
            <h2 class="section-title">Projetos de Pesquisa</h2>
            <div class="u-diamond" style="margin: 0 auto 30px;"><span></span></div>
          </div>

          <!-- BARRA DE FILTROS -->
          <div class="filter-bar">
            <select class="filter-select" id="filterAreaPes">
              <option value="">Todas as áreas</option>
              <?php foreach ($areas as $area): ?>
                <option value="<?php echo htmlspecialchars($area); ?>"><?php echo htmlspecialchars($area); ?></option>
              <?php endforeach; ?>
            </select>

            <select class="filter-select" id="filterStatusPes">
              <option value="">Situação</option>
              <?php foreach ($situacoes as $situacao): ?>
                <option value="<?php echo htmlspecialchars($situacao); ?>"><?php echo htmlspecialchars($situacao); ?>
                </option>
              <?php endforeach; ?>
            </select>

            <select class="filter-select" id="sortProjectPes">
              <option value="recent">Ordenar por: Recentes</option>
              <option value="old">Ordenar por: Antigos</option>
              <option value="alpha">Ordenar por: A-Z</option>
            </select>

            <a href="#" class="btn-ver-todos"><ion-icon name="arrow-redo-outline"></ion-icon> Ver Todos</a>
          </div>

          <div class="slider-container-box">
            <div class="swiper swiperPes">
              <div class="swiper-wrapper"
                style="<?php echo empty($pesquisa) ? 'display: flex; justify-content: center;' : ''; ?>">
                <?php if (empty($pesquisa)): ?>
                  <div class="swiper-slide" style="display: flex; justify-content: center; width: 100%;">
                    <div style="text-align: center; padding: 80px 20px; color: var(--text-muted); width: 100%;">
                      <ion-icon name="folder-open-outline"
                        style="font-size: 3.5rem; margin-bottom: 15px; opacity: 0.3;"></ion-icon>
                      <p style="font-size: 1.1rem;">Nenhum projeto encontrado nesta categoria.</p>
                    </div>
                  </div>
                <?php else: ?>
                  <?php foreach ($pesquisa as $p): ?>
                    <div class="swiper-slide project-slide-pes" 
                         data-area="<?php echo htmlspecialchars($p['area_projeto'] ?? ''); ?>" 
                         data-status="<?php echo htmlspecialchars($p['situacao_projeto'] ?? ''); ?>"
                         data-title="<?php echo htmlspecialchars($p['nome_projeto']); ?>"
                         data-id="<?php echo $p['codigo_projeto']; ?>">
                      <div class="project-card">
                        <div class="admin-controls">
                          <?php if ($logado && ($_SESSION['nivel_acesso'] == 1 || $p['siape_professor'] == $_SESSION['siape'])): ?>
                            <button class="admin-btn"
                              onclick="location.href='painel.html?edit=<?php echo $p['codigo_projeto']; ?>'"><ion-icon
                                name="pencil-outline"></ion-icon></button>
                            <button class="admin-btn" onclick="excluirProjeto('<?php echo $p['codigo_projeto']; ?>')"><ion-icon
                                name="trash-outline"></ion-icon></button>
                          <?php endif; ?>
                        </div>
                        <span class="project-tag"><?php echo strtoupper($p['categoria_projeto']); ?></span>
                        <h3 class="project-title"><?php echo htmlspecialchars($p['nome_projeto']); ?></h3>
                        <p class="project-desc">
                          <?php echo mb_strimwidth(htmlspecialchars($p['resumo_projeto']), 0, 110, "..."); ?></p>

                        <div class="project-contributors">
                          <span><strong>Orientador:</strong>
                            <?php echo htmlspecialchars($p['orientador_projeto'] ?? 'Docente IFPA'); ?></span>
                          <?php if (!empty($p['coorientador_projeto'])): ?>
                            <span><strong>Coorientador:</strong>
                              <?php echo htmlspecialchars($p['coorientador_projeto']); ?></span>
                          <?php endif; ?>
                        </div>

                        <div class="project-footer">
                          <div class="footer-inst">
                            <ion-icon name="person-outline"></ion-icon> IFPA
                          </div>
                          <div class="footer-right">
                            <div class="tech-tag">
                              <?php echo htmlspecialchars($p['linguagem_projeto'] ?? 'Geral'); ?>
                            </div>
                            <div class="action-circle"
                              onclick="window.location.href='projeto_detalhe.php?id=<?php echo $p['codigo_projeto']; ?>'">
                              <ion-icon name="chevron-forward-outline"></ion-icon>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
              <div class="swiper-pagination swiper-pagination-projects"></div>
            </div>

            <!-- SETAS LATERAIS -->
            <div class="slider-arrows">
              <div class="project-arrow swiper-button-prev-pes"><ion-icon name="arrow-back-outline"></ion-icon></div>
              <div class="project-arrow swiper-button-next-pes"><ion-icon name="arrow-forward-outline"></ion-icon></div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- SEÇÃO CIENTISTAS (IDENTICA À IMAGEM) -->
    <section class="team-section" id="sobre">
      <div class="container">
        <div class="section-header" style="text-align: center; margin-bottom: 60px;">
          <span class="section-tag">ADMS</span>
          <h2 class="section-title" style="font-family: 'Philosopher'; font-size: 3rem;">Cientistas</h2>
          <div class="u-diamond" style="margin: 20px auto;"><span></span></div>
        </div>

        <div class="team-grid">
          <!-- STELLA -->
          <div class="team-card">
            <img src="./assets/images/autor_stella.jpeg" alt="Stella Karolina" class="team-img"
              onerror="this.src='https://ui-avatars.com/api/?name=Stella+Karolina&background=1a8e4c&color=fff'">
            <div class="team-info">
              <h3>Stella Karolina</h3>
              <p>Desenvolvedora</p>
              <div class="social-links">
                <ion-icon name="mail-outline"></ion-icon>
                <ion-icon name="logo-github"></ion-icon>
                <ion-icon name="logo-linkedin"></ion-icon>
              </div>
            </div>
          </div>

          <!-- JHENIFER -->
          <div class="team-card">
            <img src="./assets/images/autor_jhony.jpg" alt="Jhonefer Vinicius" class="team-img"
              onerror="this.src='https://ui-avatars.com/api/?name=Jhonefer+Vinicius&background=1a8e4c&color=fff'">
            <div class="team-info">
              <h3>Jhonefer Vinicius</h3>
              <p>Desenvolvedor</p>
              <div class="social-links">
                <ion-icon name="mail-outline"></ion-icon>
                <ion-icon name="logo-github"></ion-icon>
                <ion-icon name="logo-linkedin"></ion-icon>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="container footer-grid">
      <div>
        <a href="#" class="footer-logo">SigiPEX</a>
        <p class="footer-text">Sistema prático para gestão de projetos de extensão e pesquisa.</p>
        <div style="font-size: 0.85rem; color: #64748b; margin-top: 20px;">
          &copy; 2025 SigiPEX. Todos os direitos reservados.
        </div>
      </div>
      <div>
        <h4 style="margin-bottom: 25px;">Navegação</h4>
        <ul class="footer-list" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
          <li><a href="#home">Home</a></li>
          <li><a href="#ajuda">Ajuda</a></li>
          <li><a href="#benefits">Benefícios</a></li>
          <li><a href="#sobre">Sobre</a></li>
          <li><a href="#projetos">Projetos</a></li>
          <li><a href="#contato">Contato</a></li>
        </ul>
      </div>
      <div>
        <h4 style="margin-bottom: 25px;">Contato</h4>
        <div style="display: flex; flex-direction: column; gap: 15px; font-size: 0.9rem; color: #64748b;">
          <span style="display: flex; align-items: center; gap: 10px;"><ion-icon name="mail-outline"
              style="color: var(--primary);"></ion-icon> contato@sigipex.ifpa.edu.br</span>
          <span style="display: flex; align-items: center; gap: 10px;"><ion-icon name="location-outline"
              style="color: var(--primary);"></ion-icon> IFPA - Tucuruí, Pará</span>
        </div>
      </div>
    </div>
    <div class="container"
      style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; font-size: 0.85rem; color: #64748b;">
      Desenvolvido com <span style="color: #ef4444; margin: 0 5px;">❤</span> por SigiPEX
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script>
    // Slider de Benefícios
    var swiperBen = new Swiper(".mySwiper", {
      slidesPerView: 1, spaceBetween: 40,
      pagination: { el: ".swiper-pagination", clickable: true },
      breakpoints: { 768: { slidesPerView: 1.5 }, 1024: { slidesPerView: 2 } },
      autoplay: { delay: 3000, disableOnInteraction: false }
    });

    // Sliders de Projetos (Extensão)
    var swiperExt = new Swiper(".swiperExt", {
      slidesPerView: 1, spaceBetween: 35,
      observer: true, observeParents: true,
      navigation: { nextEl: ".swiper-button-next-ext", prevEl: ".swiper-button-prev-ext" },
      pagination: { el: ".swiper-pagination-projects", clickable: true },
      breakpoints: {
        768: { slidesPerView: 2 },
        1024: { slidesPerView: 2.5 } /* Mais largo para não esmagar */
      }
    });

    // Sliders de Projetos (Pesquisa)
    var swiperPes = new Swiper(".swiperPes", {
      slidesPerView: 1, spaceBetween: 35,
      observer: true, observeParents: true,
      navigation: { nextEl: ".swiper-button-next-pes", prevEl: ".swiper-button-prev-pes" },
      pagination: { el: ".swiper-pagination-projects", clickable: true },
      breakpoints: {
        768: { slidesPerView: 2 },
        1024: { slidesPerView: 2.5 }
      }
    });

    // --- LÓGICA DE FILTROS E ORDENAÇÃO ---

    // Filtros de Extensão
    const filterArea = document.getElementById('filterArea');
    const filterStatus = document.getElementById('filterStatus');
    const sortProject = document.getElementById('sortProject');
    const wrapperExt = document.querySelector('.swiperExt .swiper-wrapper');

    function applyFiltersExt() {
      const area = filterArea.value;
      const status = filterStatus.value;
      const criteria = sortProject.value;
      const slides = Array.from(document.querySelectorAll('.project-slide'));

      slides.forEach(slide => {
        const matchArea = !area || slide.getAttribute('data-area') === area;
        const matchStatus = !status || slide.getAttribute('data-status') === status;

        if (matchArea && matchStatus) {
          slide.style.display = 'block';
          slide.classList.remove('swiper-slide-hidden');
        } else {
          slide.style.display = 'none';
          slide.classList.add('swiper-slide-hidden');
        }
      });

      // Ordenação
      const visibleSlides = slides.filter(s => s.style.display !== 'none');
      visibleSlides.sort((a, b) => {
        if (criteria === 'alpha') return a.getAttribute('data-title').localeCompare(b.getAttribute('data-title'));
        if (criteria === 'recent') return b.getAttribute('data-id') - a.getAttribute('data-id');
        if (criteria === 'old') return a.getAttribute('data-id') - b.getAttribute('data-id');
        return 0;
      });

      visibleSlides.forEach(s => wrapperExt.appendChild(s));
      
      swiperExt.update();
      swiperExt.slideTo(0);
    }

    filterArea?.addEventListener('change', applyFiltersExt);
    filterStatus?.addEventListener('change', applyFiltersExt);
    sortProject?.addEventListener('change', applyFiltersExt);

    // Filtros de Pesquisa
    const filterAreaPes = document.getElementById('filterAreaPes');
    const filterStatusPes = document.getElementById('filterStatusPes');
    const sortProjectPes = document.getElementById('sortProjectPes');
    const wrapperPes = document.querySelector('.swiperPes .swiper-wrapper');

    function applyFiltersPes() {
      const area = filterAreaPes.value;
      const status = filterStatusPes.value;
      const criteria = sortProjectPes.value;
      const slides = Array.from(document.querySelectorAll('.project-slide-pes'));

      slides.forEach(slide => {
        const matchArea = !area || slide.getAttribute('data-area') === area;
        const matchStatus = !status || slide.getAttribute('data-status') === status;

        if (matchArea && matchStatus) {
          slide.style.display = 'block';
        } else {
          slide.style.display = 'none';
        }
      });

      const visibleSlides = slides.filter(s => s.style.display !== 'none');
      visibleSlides.sort((a, b) => {
        if (criteria === 'alpha') return a.getAttribute('data-title').localeCompare(b.getAttribute('data-title'));
        if (criteria === 'recent') return b.getAttribute('data-id') - a.getAttribute('data-id');
        if (criteria === 'old') return a.getAttribute('data-id') - b.getAttribute('data-id');
        return 0;
      });

      visibleSlides.forEach(s => wrapperPes.appendChild(s));

      swiperPes.update();
      swiperPes.slideTo(0);
    }

    filterAreaPes?.addEventListener('change', applyFiltersPes);
    filterStatusPes?.addEventListener('change', applyFiltersPes);
    sortProjectPes?.addEventListener('change', applyFiltersPes);

    function excluirProjeto(id) {
      if (confirm('Deseja realmente excluir este projeto?')) {
        window.location.href = 'excluir_projeto.php?id=' + id;
      }
    }
  </script>
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>