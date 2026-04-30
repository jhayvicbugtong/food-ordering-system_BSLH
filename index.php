<?php require_once __DIR__ . '/includes/db_connect.php'; ?>
<!DOCTYPE html>
<html class="cms4-page" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

<<<<<<< HEAD
  <title>Bente Sais Lomi House - Authentic Batangas Lomi</title>
  <meta name="description" content="Authentic Batangas-style lomi, silog meals, and pancit — freshly prepared for delivery or pickup in Nasugbu."/>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet"/>
=======
  <title>Bente Sais Lomi House - Batangas Style Comfort Food</title>
  <meta name="description" content="Authentic Batangas-style lomi, silog meals, and pancit — freshly prepared for delivery or pickup. Comfort food done right."/>
  <meta name="robots" content="index,follow"/>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>
  
  <style>
    :root {
      --accent: #5cfa63;
<<<<<<< HEAD
      --accent-hover: #4ade80;
      --dark: #1a1a1a;
      --light-bg: #f8f9fa;
      --card-radius: 20px;
      --section-gap: 100px;
    }
    
    body {
        background-color: #fff;
        color: var(--dark);
        font-family: 'Inter', sans-serif;
        overflow-x: hidden;
    }

    /* --- UTILITIES --- */
    .container-custom {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 24px;
    }
    
    /* Solid Green Buttons */
    .btn-main {
      display: inline-flex; align-items: center; gap: 12px;
      background-color: var(--accent); color: #000;
      padding: 16px 36px; border-radius: 50px;
      font-weight: 700; font-size: 1.05rem; text-decoration: none;
      transition: all 0.3s ease; border: none;
      box-shadow: 0 10px 25px rgba(92, 250, 99, 0.25);
      position: relative; overflow: hidden;
    }
    .btn-main::after {
        content: ''; position: absolute; top: 0; left: -100%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        transition: 0.5s;
    }
    .btn-main:hover::after { left: 100%; }
    .btn-main:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 35px rgba(92, 250, 99, 0.4);
      filter: brightness(0.98);
    }
    
    .btn-outline {
        background: transparent; border: 2px solid var(--dark); color: var(--dark);
        box-shadow: none; padding: 14px 34px; 
    }
    .btn-outline:hover {
        background: var(--dark); color: #fff; border-color: var(--dark);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    /* --- HERO SECTION (Restored "Visually Appealing" Style) --- */
    .hero-section {
      position: relative;
      /* Padding adjusted to accommodate the overlap (180px bottom) */
      padding: 80px 0 180px; 
      background: #fff;
      overflow: hidden;
    }
    
    /* Radial Background Pattern */
    .hero-section::before {
      content: '';
      position: absolute; top: -100px; right: -100px;
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(92,250,99,0.08) 0%, transparent 70%);
      border-radius: 50%; pointer-events: none;
    }

    .hero-inner {
      max-width: 1200px; margin: 0 auto; padding: 0 20px;
      display: grid; grid-template-columns: 1.2fr 1fr;
      gap: 40px; align-items: center;
      position: relative; z-index: 2;
    }

    .hero-content h4 {
      font-size: 1rem; font-weight: 700;
      color: #16a34a; letter-spacing: 2px; text-transform: uppercase;
      margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;
    }
    .hero-content h4::before {
        content: ''; width: 40px; height: 2px; background: #16a34a;
    }

    .hero-content h1 {
      font-size: clamp(3.5rem, 7vw, 5.5rem);
      font-weight: 800; line-height: 1;
      margin-bottom: 1.5rem; color: var(--dark);
    }
    
    /* "LOMI" styling - Outline + Solid */
    .text-highlight {
      color: transparent;
      -webkit-text-stroke: 2px var(--dark);
      display: block;
    }
    .text-filled {
        color: var(--accent);
        -webkit-text-stroke: 0;
    }

    .hero-content p {
      font-size: 1.25rem; line-height: 1.6;
      color: #6c757d; margin-bottom: 2.5rem;
      max-width: 500px;
    }

    /* Floating Image Composition */
    .hero-visual {
      position: relative;
      height: 600px;
      display: flex; align-items: center; justify-content: center;
    }
    
    .main-dish-img {
      width: 100%; max-width: 550px;
      filter: drop-shadow(0 30px 50px rgba(0,0,0,0.2));
      animation: floatDish 6s ease-in-out infinite;
      z-index: 2; position: relative;
    }
    
    @keyframes floatDish {
      0%, 100% { transform: translateY(0) rotate(2deg); }
      50% { transform: translateY(-20px) rotate(-2deg); }
    }

    .blob-bg {
      position: absolute; top: 50%; left: 50%;
      width: 500px; height: 500px;
      background: #f0fdf4;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      z-index: 1;
    }

    /* Floating Badge */
    .floating-badge {
      position: absolute; bottom: 100px; left: 0;
      background: #fff; padding: 15px 25px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.1);
      display: flex; align-items: center; gap: 15px;
      z-index: 3;
      animation: floatBadge 5s ease-in-out infinite 1s;
    }
    @keyframes floatBadge {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    
    .fb-icon {
        width: 45px; height: 45px; background: var(--dark);
        border-radius: 50%; color: var(--accent);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
    }

    /* --- HIGHLIGHTS (FLOATING OVERLAP) --- */
    .highlights-wrapper {
        position: relative;
        z-index: 10;
        /* Pull section up to overlap hero */
        margin-top: -100px; 
        padding-bottom: 80px;
    }
    .highlights-grid {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }
    
    .highlight-card {
        background: #fff; 
        padding: 20px;
        border-radius: var(--card-radius);
        box-shadow: 0 20px 50px rgba(0,0,0,0.08); /* Lifted shadow */
        border: 1px solid rgba(0,0,0,0.02);
        display: flex; align-items: center; gap: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-decoration: none; color: inherit;
        height: 100%;
    }
    .highlight-card:hover { 
        transform: translateY(-8px); 
        box-shadow: 0 30px 60px rgba(0,0,0,0.12);
        border-color: rgba(92, 250, 99, 0.3);
    }
    
    .hl-thumb {
        width: 90px; height: 90px; flex-shrink: 0;
        border-radius: 16px; overflow: hidden; background: #f1f3f5;
    }
    .hl-thumb img { width: 100%; height: 100%; object-fit: cover; }
    
    .hl-info h3 { font-size: 1.15rem; font-weight: 700; margin: 0 0 6px 0; color: var(--dark); }
    .hl-info p { font-size: 0.9rem; color: #868e96; margin: 0 0 8px 0; line-height: 1.3; }
    .hl-arrow { 
        color: #16a34a; font-weight: 700; font-size: 0.85rem; 
        display: flex; align-items: center; gap: 4px;
        opacity: 0.8; transition: opacity 0.2s;
    }
    .highlight-card:hover .hl-arrow { opacity: 1; gap: 8px; }

    /* --- STORY SPLIT SECTION --- */
    .story-section {
        padding: var(--section-gap) 0;
        background: #fff;
    }
    .split-grid {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 80px; align-items: center;
    }
    
    .story-img-box { position: relative; }
    .story-img-box img {
        width: 100%; border-radius: 30px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.1);
    }
    /* Decorative Circle behind image */
    .story-img-box::before {
        content: ''; position: absolute; bottom: -40px; left: -40px;
        width: 250px; height: 250px; 
        background: radial-gradient(circle, #f0fdf4 0%, transparent 70%);
        z-index: -1;
    }

    .story-label { 
        font-size: 0.9rem; font-weight: 800; color: #16a34a; 
        text-transform: uppercase; letter-spacing: 2px; margin-bottom: 16px; display: block; 
    }
    .story-title { 
        font-size: 2.8rem; font-weight: 800; margin-bottom: 24px; 
        color: var(--dark); line-height: 1.1; 
    }
    .story-desc { 
        font-size: 1.1rem; line-height: 1.7; color: #6c757d; margin-bottom: 32px; 
    }

    /* --- FEATURES ROW --- */
    .features-section {
        padding: var(--section-gap) 0;
        background: #f8f9fa;
        text-align: center;
    }
    .features-grid {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 40px;
    }
    .feature-item {
        background: #fff; padding: 40px 30px;
        border-radius: var(--card-radius);
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
        transition: transform 0.3s;
    }
    .feature-item:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.06); }
    
    .feat-icon-circle {
        width: 70px; height: 70px; margin: 0 auto 24px;
        background: #f0fdf4; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.75rem; color: #16a34a;
    }
    .feature-item:hover .feat-icon-circle { background: var(--accent); color: #000; }
    
    .feat-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 12px; color: var(--dark); }
    .feat-text { color: #6c757d; line-height: 1.6; font-size: 0.95rem; margin: 0; }

    /* --- DARK PROMO --- */
    .promo-section {
        padding: var(--section-gap) 0;
        background: var(--dark); color: #fff;
        overflow: hidden; position: relative;
    }
    .promo-section::after {
        content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background-image: radial-gradient(rgba(255,255,255,0.05) 1px, transparent 1px);
        background-size: 30px 30px; opacity: 0.5; pointer-events: none;
    }
    
    .promo-grid {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 80px; align-items: center; position: relative; z-index: 2;
    }
    .promo-img-wrap img {
        width: 100%; border-radius: 30px; transform: rotate(-3deg);
        box-shadow: 0 30px 60px rgba(0,0,0,0.4);
        border: 4px solid rgba(255,255,255,0.1);
        transition: transform 0.5s ease;
    }
    .promo-img-wrap:hover img { transform: rotate(0deg) scale(1.02); }
    
    .promo-content h2 { font-size: 3rem; font-weight: 800; margin-bottom: 20px; color: #fff; }
    .promo-content p { font-size: 1.2rem; color: #ced4da; margin-bottom: 35px; line-height: 1.6; }

    /* --- SIMPLE CTA --- */
    .cta-section {
        padding: 120px 0; background: #fff; text-align: center;
    }
    .cta-container { max-width: 700px; margin: 0 auto; }
    .cta-container h2 { font-size: 2.5rem; font-weight: 800; margin-bottom: 16px; color: var(--dark); }
    .cta-container p { font-size: 1.15rem; color: #6c757d; margin-bottom: 32px; }

    /* --- RESPONSIVE --- */
    @media (max-width: 991px) {
        .hero-inner { grid-template-columns: 1fr; text-align: center; }
        .hero-content h4 { justify-content: center; }
        .hero-visual { height: 400px; margin-top: 40px; }
        .main-dish-img { max-width: 400px; }
        .floating-badge { bottom: 0; left: 50%; transform: translateX(-50%); animation: none; width: max-content; }
        
        .highlights-wrapper { margin-top: -40px; }
        .highlights-grid { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
        
        .split-grid { grid-template-columns: 1fr; gap: 50px; text-align: center; }
        .story-img-box { order: -1; }
        
        .features-grid { grid-template-columns: 1fr; gap: 20px; }
        
        .promo-grid { grid-template-columns: 1fr; text-align: center; gap: 50px; }
        .promo-img-wrap { order: -1; } /* Image on top for mobile */
        .promo-img-wrap img { transform: rotate(0); max-width: 500px; margin: 0 auto; }
    }
    
    @media (max-width: 768px) {
        .hero-content h1 { font-size: 2.8rem; }
        .story-title { font-size: 2.2rem; }
        .promo-content h2 { font-size: 2.2rem; }
        .section-padding { padding: 60px 0; }
=======
      --accent-light: #7cf484;
      --dark: #1a1a1a;
      --dark-light: #2d2d2d;
      --text-light: #e9ecef;
      --text-muted: #6c757d;
    }

    /* Modern Hero Section */
    .hero-section {
      background: 
        url('https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/wave2.png?v=0');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      min-height: 100vh;
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
    }

    .hero-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: 
        radial-gradient(circle at 20% 80%, rgba(92, 250, 99, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(92, 250, 99, 0.05) 0%, transparent 50%);
      animation: pulse 6s ease-in-out infinite;
      pointer-events: none;
    }

    @keyframes pulse {
      0%, 100% { opacity: 0.3; }
      50% { opacity: 0.6; }
    }

    .section-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      align-items: center;
      gap: 60px;
      position: relative;
      z-index: 2;
    }

    .hero-left {
      flex: 1;
      color: #343a40;
    }

    .hero-left h1 {
      font-size: clamp(2.5rem, 5vw, 4rem);
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 1.5rem;
      background: linear-gradient(135deg, #343a40 0%, var(--accent) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero-left h1 span {
      color: var(--accent);
      -webkit-text-fill-color: var(--accent);
    }

    .hero-left h4 {
      font-size: clamp(1.1rem, 2vw, 1.3rem);
      font-weight: 400;
      line-height: 1.6;
      margin-bottom: 2.5rem;
      color: #6c757d;
      max-width: 500px;
    }

    .hero-cta {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      color: #000;
      padding: 16px 32px;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      box-shadow: 0 10px 30px rgba(92, 250, 99, 0.3);
      position: relative;
      overflow: hidden;
      border: none;
      cursor: pointer;
    }

    .hero-cta::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.5s;
    }

    .hero-cta:hover::before {
      left: 100%;
    }

    .hero-cta:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 40px rgba(92, 250, 99, 0.4);
    }

    .hero-right {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .hero-img {
      max-width: 100%;
      height: auto;
      max-height: 600px;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
      transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
      transition: transform 0.3s ease;
      animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { 
        transform: perspective(1000px) rotateY(-5deg) rotateX(5deg) translateY(0px); 
      }
      50% { 
        transform: perspective(1000px) rotateY(-5deg) rotateX(5deg) translateY(-20px); 
      }
    }

    /* Modern About Section with Image Box */
    .about-section {
      padding: 100px 0;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .about-img-col {
      position: relative;
    }

    .image-box-container {
      position: relative;
      max-width: 500px;
      margin: 0 auto;
    }

    .image-box {
      position: relative;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
      transform: perspective(1000px) rotateY(5deg) rotateX(2deg);
      transition: all 0.4s ease;
      border: 8px solid #fff;
      background: #fff;
    }

    .image-box:hover {
      transform: perspective(1000px) rotateY(5deg) rotateX(2deg) translateY(-10px);
      box-shadow: 0 35px 70px rgba(0, 0, 0, 0.2);
    }

    .image-box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, rgba(92, 250, 99, 0.1) 0%, transparent 60%);
      z-index: 1;
      pointer-events: none;
    }

    .image-box img {
      width: 100%;
      height: 500px;
      object-fit: cover;
      transition: transform 0.4s ease;
    }

    .image-box:hover img {
      transform: scale(1.05);
    }

    .image-box-decoration {
      position: absolute;
      z-index: 2;
    }

    .decoration-1 {
      top: -15px;
      left: -15px;
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      border-radius: 12px;
      transform: rotate(45deg);
      opacity: 0.8;
      animation: float-decoration 4s ease-in-out infinite;
    }

    .decoration-2 {
      bottom: -10px;
      right: -10px;
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      border-radius: 8px;
      transform: rotate(-30deg);
      opacity: 0.6;
      animation: float-decoration 3s ease-in-out infinite reverse;
    }

    .decoration-3 {
      top: 50%;
      left: -20px;
      width: 30px;
      height: 30px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      border-radius: 6px;
      transform: rotate(15deg);
      opacity: 0.4;
      animation: float-decoration 5s ease-in-out infinite;
    }

    @keyframes float-decoration {
      0%, 100% { transform: translateY(0px) rotate(45deg); }
      50% { transform: translateY(-10px) rotate(45deg); }
    }

    .image-box-badge {
      position: absolute;
      top: 20px;
      right: 20px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      color: #000;
      padding: 8px 16px;
      border-radius: 25px;
      font-weight: 600;
      font-size: 0.9rem;
      z-index: 3;
      box-shadow: 0 5px 15px rgba(92, 250, 99, 0.3);
      animation: pulse-badge 2s ease-in-out infinite;
    }

    @keyframes pulse-badge {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }

    .about-text-col {
      padding: 0 40px;
    }

    .about-text-col h4 {
      color: var(--accent);
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 1rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .about-text-col h2 {
      font-size: clamp(2rem, 4vw, 2.5rem);
      font-weight: 700;
      line-height: 1.2;
      margin-bottom: 1.5rem;
      color: #343a40;
    }

    .about-text-col p {
      font-size: 1.1rem;
      line-height: 1.7;
      color: var(--text-muted);
      margin-bottom: 2rem;
    }

    .about-cta {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: transparent;
      color: var(--accent);
      padding: 12px 24px;
      border: 2px solid var(--accent);
      border-radius: 50px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .about-cta:hover {
      background: var(--accent);
      color: #000;
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(92, 250, 99, 0.3);
    }

    /* Enhanced Features Section */
    .features-section {
      padding: 80px 0;
      background: #fff;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 40px;
      max-width: 1000px;
      margin: 0 auto;
    }

    .feature-card {
      text-align: center;
      padding: 40px 30px;
      border-radius: 20px;
      background: #fff;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      border: 1px solid #f1f3f4;
      position: relative;
      overflow: hidden;
    }

    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
    }

    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .feature-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto 1.5rem;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      color: #000;
      transition: transform 0.3s ease;
    }

    .feature-card:hover .feature-icon {
      transform: scale(1.1) rotate(5deg);
    }

    .feature-title {
      font-size: 1.3rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: #343a40;
    }

    .feature-desc {
      color: var(--text-muted);
      line-height: 1.6;
    }

    /* Modern Split Section */
    .middle-split-section {
      padding: 100px 0;
      background: linear-gradient(135deg, var(--dark) 0%, var(--dark-light) 100%);
      color: #fff;
    }

    .middle-split-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr auto 1fr;
      gap: 60px;
      align-items: center;
    }

    .split-text-block {
      text-align: center;
    }

    .split-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--accent);
    }

    .split-text-block p {
      color: #adb5bd;
      line-height: 1.6;
    }

    .middle-img-wrap {
      position: relative;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      transition: transform 0.3s ease;
    }

    .middle-img-wrap:hover {
      transform: scale(1.05);
    }

    .middle-img-wrap img {
      width: 300px;
      height: 300px;
      object-fit: cover;
      border-radius: 20px;
      transition: transform 0.3s ease;
    }

    /* Enhanced CTA Section - No Oval Shapes */
    .cta-section {
      padding: 100px 0;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .cta-block {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 60px;
      align-items: center;
    }

    .cta-img-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .cta-img-card {
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
      background: #fff;
      border: none;
      position: relative;
    }

    .cta-img-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }

    .cta-img-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      transition: transform 0.3s ease;
      border-radius: 0;
    }

    .cta-img-card:hover img {
      transform: scale(1.1);
    }

    .cta-text-col h2 {
      font-size: clamp(2rem, 4vw, 2.5rem);
      font-weight: 700;
      margin-bottom: 1rem;
      color: #343a40;
    }

    .cta-text-col h4 {
      font-size: 1.2rem;
      font-weight: 400;
      line-height: 1.6;
      margin-bottom: 2rem;
      color: var(--text-muted);
    }

    .cta-center-btn {
      text-align: left;
    }

    .cta-btn {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      color: #000;
      padding: 16px 32px;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      box-shadow: 0 10px 30px rgba(92, 250, 99, 0.3);
    }

    .cta-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 40px rgba(92, 250, 99, 0.4);
    }

    /* Responsive Design */
    @media (max-width: 968px) {
      .section-inner {
        flex-direction: column;
        text-align: center;
        gap: 40px;
      }

      .middle-split-inner {
        grid-template-columns: 1fr;
        gap: 40px;
      }

      .cta-block {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
      }

      .about-text-col {
        padding: 0 20px;
      }

      .hero-img {
        transform: none;
        animation: none;
        max-height: 400px;
      }

      .cta-center-btn {
        text-align: center;
      }

      .image-box {
        transform: none;
      }

      .image-box:hover {
        transform: translateY(-10px);
      }

      .decoration-1,
      .decoration-2,
      .decoration-3 {
        display: none;
      }
    }

    @media (max-width: 768px) {
      .features-grid {
        grid-template-columns: 1fr;
        gap: 30px;
      }

      .cta-img-grid {
        grid-template-columns: 1fr;
      }

      .hero-section,
      .about-section,
      .features-section,
      .middle-split-section,
      .cta-section {
        padding: 60px 0;
      }

      .section-inner {
        padding: 0 15px;
      }

      .image-box img {
        height: 400px;
      }
    }

    @media (max-width: 480px) {
      .hero-cta,
      .about-cta,
      .cta-btn {
        padding: 14px 28px;
        font-size: 1rem;
      }

      .feature-card {
        padding: 30px 20px;
      }

      .middle-img-wrap img {
        width: 250px;
        height: 250px;
      }

      .image-box img {
        height: 300px;
      }

      .image-box-badge {
        top: 10px;
        right: 10px;
        font-size: 0.8rem;
        padding: 6px 12px;
      }

      .cta-img-card img {
        height: 180px;
      }
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/customer/includes/header.php'; ?>

  <section class="hero-section">
    <div class="hero-inner">
      <div class="hero-content">
        <h4><i class="bi bi-fire"></i> Best in Batangas</h4>
        <h1>
          Hot, Filling,
          <span class="text-highlight">AUTHENTIC</span>
          <span class="text-filled">LOMI.</span>
        </h1>
<<<<<<< HEAD
        <p>
          Experience the real taste of Batangas comfort food. 
          Freshly cooked lomi, hearty silog meals, and savory pancit 
          delivered hot to your doorstep.
        </p>
        <div class="d-flex gap-3 flex-wrap justify-content-center justify-content-lg-start">
            <a href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>" class="btn-main">
              Order Now <i class="bi bi-arrow-right-short fs-4"></i>
            </a>
            <a href="customer/menu.php" class="btn-main btn-outline">
              View Menu
            </a>
        </div>
      </div>

      <div class="hero-visual">
        <div class="blob-bg"></div>
        <img src="uploads/logo/logo_home.png" class="main-dish-img" alt="Bente Sais Lomi Bowl">
        
        <div class="floating-badge">
            <div class="fb-icon"><i class="bi bi-clock-history"></i></div>
            <div>
                <strong class="d-block text-dark">Open Daily</strong>
                <small class="text-muted">8:00 AM - 10:00 PM</small>
            </div>
        </div>
      </div>
    </div>
  </section>

  <section class="highlights-wrapper">
    <div class="container-custom">
      <div class="highlights-grid">
        
        <a href="customer/menu.php#signature-lomi" class="highlight-card">
            <div class="hl-thumb">
                <img src="uploads/gallery/lomi.jpg" alt="Lomi">
            </div>
            <div class="hl-info">
                <h3>Signature Lomi</h3>
                <p>Thick noodles, rich broth.</p>
                <div class="hl-arrow">Order Now <i class="bi bi-arrow-right"></i></div>
            </div>
=======
        <h4>
          Enjoy our authentic Batangas-style lomi, silog meals, and pancit — 
          freshly prepared for delivery or pickup, Monday to Sunday.
        </h4>

        <a class="hero-cta" href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>">
          <i class="bi bi-bag-check-fill"></i>
          Order online
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
        </a>

<<<<<<< HEAD
        <a href="customer/menu.php#silog-meals" class="highlight-card">
            <div class="hl-thumb">
                <img src="uploads/gallery/silog.jpg" alt="Silog">
            </div>
            <div class="hl-info">
                <h3>Silog Meals</h3>
                <p>Garlic rice + egg + ulam.</p>
                <div class="hl-arrow">Order Now <i class="bi bi-arrow-right"></i></div>
            </div>
=======
      <div class="hero-right">
        <img class="hero-img"
             src="uploads/logo/logo_transparent.png"
             alt="Signature Lomi Bowl - Bente Sais Lomi House">
      </div>
    </div>
  </section>

  <section class="about-section">
    <div class="section-inner" style="flex-direction: row-reverse;">
      <div class="about-img-col">
        <div class="image-box-container">
          <div class="image-box">
            <div class="image-box-badge">
              <i class="bi bi-star-fill"></i> Fresh Daily
            </div>
            <img src="uploads/home/store_image.png" alt="Freshly Prepared Lomi - Bente Sais Lomi House">
          </div>
          <div class="image-box-decoration decoration-1"></div>
          <div class="image-box-decoration decoration-2"></div>
          <div class="image-box-decoration decoration-3"></div>
        </div>
      </div>

      <div class="about-text-col">
        <h4>Authentic Batangas Flavor</h4>
        <h2>Experience the taste of home with every bowl</h2>
        <p>
          We're passionate about serving authentic Batangas-style comfort food. 
          From our rich, flavorful lomi broth to our perfectly cooked silog meals 
          and pancit for sharing — every dish is prepared with fresh ingredients 
          and traditional recipes passed down through generations.
        </p>

        <a class="about-cta" href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>">
          <i class="bi bi-arrow-right"></i>
          Explore our menu
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
        </a>

<<<<<<< HEAD
        <a href="customer/menu.php#party-trays" class="highlight-card">
            <div class="hl-thumb">
                <img src="uploads/gallery/pancit.jpg" alt="Pancit">
            </div>
            <div class="hl-info">
                <h3>Party Trays</h3>
                <p>Perfect for sharing.</p>
                <div class="hl-arrow">Order Now <i class="bi bi-arrow-right"></i></div>
            </div>
        </a>

=======
  <section class="features-section">
    <div class="section-inner" style="flex-direction: column; text-align:center;">
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-cup-hot-fill"></i></div>
          <div class="feature-title">Rich & Flavorful Broth</div>
          <div class="feature-desc">
            Our signature lomi broth is slow-cooked for hours, packed with authentic 
            Batangas flavor and served piping hot in every bowl.
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-emoji-sunglasses-fill"></i></div>
          <div class="feature-title">Perfect for Gatherings</div>
          <div class="feature-desc">
            Whether it's family dinner or barkada hangout, our cozy atmosphere 
            and delicious food create the perfect setting for making memories.
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-egg-fried"></i></div>
          <div class="feature-title">Silog Classics</div>
          <div class="feature-desc">
            Traditional Filipino breakfast favorites available all day. 
            Perfect garlic rice, sunny-side-up egg, and your choice of ulam — 
            comfort in every bite.
          </div>
        </div>
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
      </div>
    </div>
  </section>

<<<<<<< HEAD
  <section class="story-section">
    <div class="container-custom">
      <div class="split-grid">
        <div class="story-img-box">
          <img src="uploads/home/store_image.png" alt="Bente Sais Kitchen">
        </div>
        <div class="story-content">
          <span class="story-label">Our Heritage</span>
          <h2 class="story-title">Cooking the food you grew up with.</h2>
          <p class="story-desc">
            We started with a simple goal: serve the kind of lomi that locals look for. 
            Hot, thick, and generous with toppings. Today, we continue that tradition 
            by preparing every bowl fresh upon order, ensuring you get that authentic 
            Batangas flavor every single time.
          </p>
          <a href="customer/about-us.php" class="btn-main btn-outline">
            Read Our Story
=======
  <section class="middle-split-section">
    <div class="middle-split-inner">
      <div class="col-split split-text-block">
        <div class="split-title">Quality & Value</div>
        <p>
          We believe that great food should be accessible to everyone. 
          Our generous portions and affordable prices ensure you leave 
          satisfied without breaking the bank.
        </p>
      </div>

      <div class="col-split middle-img-wrap">
        <img src="uploads/home/hotdog.jpg"
             alt="Fresh Ingredients - Bente Sais Lomi House">
      </div>

      <div class="col-split split-text-block">
        <div class="split-title">Fresh Daily</div>
        <p>
          We prepare everything in small batches throughout the day using 
          the freshest local ingredients. No shortcuts, no compromises — 
          just honest, delicious food.
        </p>
      </div>
    </div>
  </section>

  <section class="cta-section">
    <div class="cta-block">
      <div class="cta-img-col">
        <div class="cta-img-grid">
          <div class="cta-img-card">
            <img src="uploads/home/bulaklak.jpg" alt="Silog Meal Combo">
          </div>
          <div class="cta-img-card">
            <img src="uploads/home/maling.jpg" alt="Party Tray Pancit">
          </div>
        </div>
      </div>

      <div class="cta-text-col">
        <h2>Ready to experience authentic Batangas flavor?</h2>
        <h4>
          Place your order for pickup or delivery. We also cater to groups 
          and special occasions with our party trays and silog packs. 
          Message us for bulk orders and custom arrangements.
        </h4>
        <div class="cta-center-btn">
          <a class="cta-btn" href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>">
            <i class="bi bi-bag-check-fill"></i>
            Order now
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
          </a>
        </div>
      </div>
    </div>
  </section>

<<<<<<< HEAD
  <section class="features-section">
    <div class="container-custom">
      <div class="features-grid">
        <div class="feature-item">
          <div class="feat-icon-circle"><i class="bi bi-fire"></i></div>
          <h4 class="feat-title">Cooked Fresh</h4>
          <p class="feat-text">No reheated food here. We fire up the wok only when you order.</p>
        </div>
        <div class="feature-item">
          <div class="feat-icon-circle"><i class="bi bi-wallet2"></i></div>
          <h4 class="feat-title">Affordable</h4>
          <p class="feat-text">Generous servings that fill you up without emptying your wallet.</p>
        </div>
        <div class="feature-item">
          <div class="feat-icon-circle"><i class="bi bi-bicycle"></i></div>
          <h4 class="feat-title">Fast Delivery</h4>
          <p class="feat-text">Hungry? We deliver hot and fresh to nearby barangays in Nasugbu.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="promo-section">
    <div class="container-custom">
      <div class="promo-grid">
        <div class="promo-content">
          <span class="hero-badge" style="background:rgba(255,255,255,0.1); color:var(--accent); border:none;">Best Seller</span>
          <h2>The Ultimate Comfort Combo</h2>
          <p>
            Try our Silog meals paired with a hot bowl of soup. 
            Perfect for breakfast, lunch, or a late-night craving.
          </p>
          <a href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>" class="btn-main">
            Order for Delivery
          </a>
        </div>
        <div class="promo-img-wrap">
          <img src="uploads/home/bulaklak.jpg" alt="Delicious Silog Meal">
        </div>
      </div>
    </div>
  </section>

  <section class="cta-section">
    <div class="cta-container">
      <h2>Ready to eat?</h2>
      <p>Skip the queue. Order online and we'll have your food ready for pickup or delivery.</p>
      <a href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>" class="btn-main">
        Start Your Order
      </a>
    </div>
  </section>

=======
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
  <?php include __DIR__ . '/customer/includes/footer.php'; ?>

</body>
</html>