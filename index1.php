<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Shyam Yadav | Portfolio</title>
    <link rel="shortcut icon" href="assets/images/fav.png" type="image/x-icon" />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" />
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --sw: 265px;

            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #1e293b;

            --text: #f1f5f9;
            --muted: #94a3b8;

            --bg: #0f172a;
            --white: #020617;

            --border: #1e293b;

            --card-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
            --card-shadow-hover: 0 6px 20px rgba(0, 0, 0, 0.8);
        }
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: #0f172a;/*var(--bg);*/
            color: var(--text);
            overflow-x: hidden;
        }

        /* ══════════════════════════════════   SIDEBAR ══════════════════════════════════ */
        .head {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sw);
            height: 100vh;
            background: var(--white);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 3px 0 20px rgba(30, 35, 60, 0.08);
            transition: transform 0.32s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Sidebar top brand strip */
        .sidebar-brand {
            padding: 0 22px;
            height: 72px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

    

        /* Fallback name when image fails */
        .sidebar-brand-name {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.02em;
            display: none;
        }

        .sidebar-brand-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 1.1rem;
            color: #fff;
            flex-shrink: 0;
        }

        /* Nav */
        .navcol {
            flex: 1;
            padding: 12px 0;
            overflow-y: auto;
        }
        .navcol ul {
            list-style: none;
        }

        .navcol ul li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 22px;
            color: var(--muted);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.18s;
            border-left: 3px solid transparent;
            margin: 1px 0;
        }

        .navcol ul li a i {
            font-size: 1.05rem;
            width: 20px;
            text-align: center;
        }

        .navcol ul li a:hover {
            color: var(--primary);
            background: var(--primary-light);
        }

        .navcol ul li a.active {
            color: var(--primary);
            background: var(--primary-light);
            border-left-color: var(--primary);
            font-weight: 600;
        }

        /* ══════════════════════════════════   MOBILE TOPBAR ══════════════════════════════════ */
        .topbar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 58px;
            background: #020617;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            z-index: 200;
            box-shadow: 0 2px 12px rgba(26, 110, 245, 0.3);
        }

        .topbar-brand {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.03em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-brand img {
            height: 30px;
            filter: brightness(0) invert(1);
        }

        .topbar-brand-text {
            color: #fff;
        }

        .hamburger {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 7px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.3rem;
            color: #fff;
            transition: background 0.2s;
        }

        .hamburger:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 99;
            backdrop-filter: blur(2px);
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* ══════════════════════════════════   MAIN CONTENT ══════════════════════════════════ */
        .main-content {
            margin-left: var(--sw);
            min-height: 100vh;
        }

        /* ══════════════════════════════════   HERO ══════════════════════════════════ */
        .profile-head {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /*background: var(--white);*/
            position: relative;
            overflow: hidden;
        }

        /* Subtle decorative background blobs */
        .profile-head::before {
            content: "";
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(26, 110, 245, 0.07) 0%, transparent 70%);
            top: -120px;
            right: -80px;
            border-radius: 50%;
            pointer-events: none;
        }

        .profile-head::after {
            content: "";
            position: absolute;
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, rgba(26, 110, 245, 0.05) 0%, transparent 70%);
            bottom: -80px;
            left: 10%;
            border-radius: 50%;
            pointer-events: none;
        }

        .profile-inner {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 60px 40px;
            max-width: 620px;
            width: 100%;
            margin: 0 auto;
        }

        /* Profile image ring */
        .imgcover {
            margin-bottom: 24px;
        }

        .imgcover a {
            display: block;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            padding: 5px;
            background: linear-gradient(135deg, #1a6ef5, #7cb3ff);
            box-shadow: 0 8px 32px rgba(26, 110, 245, 0.28);
            transition:
                transform 0.3s,
                box-shadow 0.3s;
        }

        .imgcover a:hover {
            transform: scale(1.04);
            box-shadow: 0 12px 40px rgba(26, 110, 245, 0.38);
        }

        .imgcover a img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            display: block;
        }

        .profile-inner .greeting {
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--muted);
            margin-bottom: 10px;
            letter-spacing: 0.02em;
        }

        .profile-inner h1 {
            font-size: clamp(2rem, 5vw, 2.8rem);
            font-weight: 800;
            color: var(--text);
            line-height: 1.15;
            margin-bottom: 18px;
        }

        .profile-inner p.desc {
            font-size: 0.9rem;
            color: var(--muted);
            line-height: 1.8;
            margin-bottom: 28px;
            max-width: 440px;
        }

        /* Social icons */
        .social-media ul {
            list-style: none;
            display: inline-flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 28px;
        }

        .social-media ul li a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--bg);
            color: var(--text);
            font-size: 1.15rem;
            text-decoration: none;
            border: 1.5px solid var(--border);
            transition: all 0.22s;
            box-shadow: 0 2px 8px rgba(30, 35, 60, 0.07);
        }

        .social-media ul li a:hover {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(26, 110, 245, 0.3);
        }

        /* Download button */
        .btn-resume {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 34px;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 50px;
            font-family: "Poppins", sans-serif;
            font-weight: 700;
            font-size: 0.88rem;
            text-decoration: none;
            transition: all 0.22s;
            letter-spacing: 0.02em;
        }

        .btn-resume:hover {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 6px 20px rgba(26, 110, 245, 0.3);
            transform: translateY(-2px);
        }

        /* ══════════════════════════════════    SECTION COMMON ══════════════════════════════════ */
        .sec {
            padding: 72px 52px;
        }

        .sec.white {
            background: var(--white);
        }
        .sec.gray {
            background: var(--bg);
        }

        .sec-title {
            font-size: clamp(1.4rem, 3vw, 1.85rem);
            font-weight: 800;
            color: var(--text);
            margin-bottom: 6px;
            position: relative;
            padding-bottom: 14px;
        }

        .sec-title::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 44px;
            height: 3px;
            background: var(--primary);
            border-radius: 4px;
        }

        .sec-sub {
            font-size: 0.88rem;
            color: var(--muted);
            line-height: 1.75;
            margin-top: 14px;
            /*max-width: 580px;*/
        }

        /* ══════════════════════════════════   ABOUT ══════════════════════════════════ */
        .about-grid {
            display: grid;
            margin-top: 34px;
            align-items: start;
        }

        .about-text p {
            font-size: 0.88rem;
            line-height: 1.85;
            color: var(--muted);
            margin-bottom: 14px;
        }

        .about-text p:first-child {
            font-weight: 600;
            color: var(--text);
            font-size: 0.92rem;
        }

        .about-text a {
            color: var(--primary);
            text-decoration: none;
        }
        .about-text a:hover {
            text-decoration: underline;
        }

        .skill-heading {
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--text);
            margin: 28px 0 5px;
        }

        .skill-sub {
            font-size: 0.82rem;
            color: var(--muted);
            margin-bottom: 22px;
        }

        .skill-set {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 32px;
        }

        .skill-item h6 {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
        }

        .skill-item h6 span {
            font-weight: 400;
            color: var(--muted);
        }

        .progress {
            height: 7px;
            background: #e3e8f4;
            border-radius: 50px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), #5fa0ff);
            border-radius: 50px;
        }

        /* ══════════════════════════════════    SERVICES ══════════════════════════════════ */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            margin-top: 36px;
        }

        .serv-card {
            background: var(--white);
            border-radius: 14px;
            padding: 34px 24px 28px;
            text-align: center;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border);
            transition:
                transform 0.22s,
                box-shadow 0.22s;
        }

        .serv-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow-hover);
            border-color: rgba(26, 110, 245, 0.2);
        }

        .serv-icon {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 1.55rem;
            color: var(--primary);
            transition:
                background 0.22s,
                transform 0.22s;
        }

        .serv-card:hover .serv-icon {
            background: var(--primary);
            color: #fff;
            transform: scale(1.1);
        }

        .serv-card h5 {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text);
        }

        /* ══════════════════════════════════    PORTFOLIO ══════════════════════════════════ */
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            margin-top: 36px;
        }

        .proj-card {
            background: var(--white);
            /*border-radius: 14px;*/
            overflow: hidden;
            box-shadow: var(--card-shadow);
            border: 2px solid var(--border);
            transition:
                transform 0.22s,
                box-shadow 0.22s;
        }

        .proj-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow-hover);
            border-color: rgba(26, 110, 245, 0.2);
        }

        .proj-thumb {
            overflow: hidden;
            height: 195px;
        }

        .proj-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s;
        }

        .proj-card:hover .proj-thumb img {
            transform: scale(1.06);
        }

        .proj-info {
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .proj-info h5 {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text);
        }

        .proj-link {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            text-decoration: none;
            transition:
                background 0.2s,
                color 0.2s;
            flex-shrink: 0;
        }

        .proj-link:hover {
            background: var(--primary);
            color: #fff;
        }

        /* ══════════════════════════════════    CONTACT ══════════════════════════════════ */
        .contact-info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 36px;
        }

        .contact-info-card {
            background: var(--white);
            border-radius: 14px;
            padding: 22px 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .ci-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--primary);
            flex-shrink: 0;
        }

        .ci-body h6 {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .ci-body ul {
            list-style: none;
        }
        .ci-body ul li {
            font-size: 0.82rem;
            color: var(--text);
            font-weight: 500;
            line-height: 1.8;
        }

        .contact-bottom {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
            margin-top: 22px;
        }

        /* Form */
        .form-card {
            background: var(--white);
            /*border-radius: 14px;*/
            padding: 32px 28px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border);
        }

        .form-card .mb-3 {
            margin-bottom: 14px;
        }

        .form-card input,
        .form-card textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-family: "Poppins", sans-serif;
            font-size: 0.85rem;
            color: var(--text);
            background: var(--white);
            outline: none;
            transition:
                border-color 0.2s,
                box-shadow 0.2s;
        }

        .form-card input::placeholder,
        .form-card textarea::placeholder {
            color: #adb5bd;
        }

        .form-card input:focus,
        .form-card textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 110, 245, 0.1);
        }

        .form-card textarea {
            resize: vertical;
            min-height: 95px;
        }

        .form-card span {
            color: #dc3545;
            font-size: 0.74rem;
            display: block;
            margin-top: 4px;
        }

        .btn-send {
            display: block;
            margin: 0 auto;
            padding: 11px 36px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-family: "Poppins", sans-serif;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition:
                background 0.2s,
                transform 0.2s,
                box-shadow 0.2s;
            box-shadow: 0 4px 14px rgba(26, 110, 245, 0.28);
        }

        .btn-send:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 22px rgba(26, 110, 245, 0.38);
        }

        .map-card {
            background: var(--white);
            overflow: hidden;
            box-shadow: var(--card-shadow);
            border: 2px solid var(--border);
            padding: 10px;
        }

        .map-card iframe {
            width: 100%;
            height: 100%;
            min-height: 340px;
            display: block;
            border: 0;
        }

        /* ══════════════════════════════════   LOADING ══════════════════════════════════ */
        .loading {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.5);
        }

        .loading.active {
            display: flex;
        }

        .spinner {
            width: 42px;
            height: 42px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ══════════════════════════════════   RESPONSIVE ══════════════════════════════════ */
        @media (max-width: 1100px) {
            .sec {
                padding: 60px 36px;
            }
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .portfolio-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .contact-info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 900px) {
            :root {
                --sw: 0px;
            }
            .head {
                width: 265px;
                transform: translateX(-100%);
            }
            .head.open {
                transform: translateX(0);
            }
            .topbar {
                display: flex;
            }
            .main-content {
                margin-left: 0;
                padding-top: 58px;
            }
            .sec {
                padding: 52px 28px;
            }
            .about-grid {
                grid-template-columns: 1fr;
            }
            .contact-bottom {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .sec {
                padding: 44px 18px;
            }
            .profile-inner {
                padding: 44px 18px;
            }
            .imgcover a {
                width: 140px;
                height: 140px;
            }
            .skill-set {
                grid-template-columns: 1fr;
            }
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }
            .portfolio-grid {
                grid-template-columns: 1fr;
            }
            .contact-info-grid {
                grid-template-columns: 1fr;
            }
            .form-card {
                padding: 24px 18px;
            }
        }

        @media (max-width: 420px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
            .profile-inner h1 {
                font-size: 1.8rem;
            }
            .imgcover a {
                width: 120px;
                height: 120px;
            }
        }


        /* FADE IN */
        .fade-in {
          animation: fadeIn 1s ease forwards;
        }

        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }

        /* SLIDE UP */
        .slide-up {
          opacity: 0;
          transform: translateY(30px);
          animation: slideUp 0.8s ease forwards;
        }

        @keyframes slideUp {
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }

        /* ZOOM IMAGE */
        .zoom-in img {
          transform: scale(0.8);
          opacity: 0;
          animation: zoomIn 0.8s ease forwards;
        }

        @keyframes zoomIn {
          to {
            transform: scale(1);
            opacity: 1;
          }
        }

        /* DELAYS */
        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }
        .delay-4 { animation-delay: 0.8s; }
        .delay-5 { animation-delay: 1s; }
    </style>
</head>
<body>
    <div class="loading" id="loader"><div class="spinner"></div></div>

    <!-- Mobile topbar -->
    <div class="topbar">
        <div class="topbar-brand">
            <div class="sidebar-brand-avatar">
                <i class="bi bi-person-workspace"></i>
            </div>
            <span class="topbar-brand-text">Shyam Yadav</span>
        </div>

        <button class="hamburger" id="hbtn" aria-label="Menu">
            <i class="bi bi-list" id="hico"></i>
        </button>
    </div>

    <div class="sidebar-overlay" id="sov"></div>

    <!-- Sidebar -->
    <header class="head" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-avatar">
                <i class="bi bi-person-workspace"></i>
            </div>
            <span class="sidebar-brand-name" style="display: block">Shyam Yadav</span>
        </div>

        <nav class="navcol">
            <ul>
                <li>
                    <a href="#home" class="nav-link active"><i class="bi bi-house-door"></i> Home</a>
                </li>
                <li>
                    <a href="#about" class="nav-link"><i class="bi bi-info-circle"></i> About</a>
                </li>
                <li>
                    <a href="#service" class="nav-link"><i class="bi bi-gear"></i> Service</a>
                </li>
                <li>
                    <a href="#portfolio" class="nav-link"><i class="bi bi-columns-gap"></i> Projects</a>
                </li>
                <li>
                    <a href="#contact" class="nav-link"><i class="bi bi-envelope"></i> Contact</a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="main-content">
        <!-- ── HERO ── -->
        <div id="home" class="profile-head">
            <div class="profile-inner fade-in">
                <div class="imgcover zoom-in">
                    <a href="assets/images/shyam1.jpg" target="_blank">
                        <img
                            src="assets/images/shyam1.jpg"
                            alt="Shyam Yadav"
                            onerror="this.src='https://ui-avatars.com/api/?name=Shyam+Yadav&size=160&background=1a6ef5&color=ffffff&bold=true'"
                        />
                    </a>
                </div>

                <span class="greeting slide-up delay-1">Hello I am, Shyam Yadav</span>

                <h1 class="slide-up delay-2">Full Stack Developer</h1>

                <p class="desc slide-up delay-3">
                    Ability to work and thrive in a fast-paced environment, learn rapidly and master diverse web
                    technologies and techniques
                </p>

                <div class="social-media slide-up delay-4">
                    <ul >
                        <li>
                            <a target="_blank" href="https://www.facebook.com/shyammilan.yadav.50/"><i class="bi bi-facebook" ></i></a>
                        </li>
                        <li>
                            <a target="_blank" href="https://www.instagram.com/mr.shyam_97/"><i class="bi bi-instagram" ></i></a>
                        </li>
                        <li>
                            <a target="_blank" href="https://twitter.com/shyam_9616"><i class="bi bi-twitter" ></i></a>
                        </li>
                        <li>
                            <a target="_blank" href="https://www.linkedin.com/in/shyam-yadav-371420221/"><i class="bi bi-linkedin" ></i></a>
                        </li>
                    </ul>
                </div>

                <a href="assets/shyam_resume.pdf" class="btn-resume slide-up delay-5" download>
                    <i class="bi bi-download"></i> Download Resume
                </a>
            </div>
        </div>

        <!-- ── ABOUT ── -->
        <div id="about" class="sec white">
            <h2 class="sec-title">About Me</h2>
            <div class="about-grid">
                <div class="about-text">
                    <p><b>I am, Shyam Yadav</b></p>
                    <p>
                        I am a Full Stack Developer with over 4 years of experience in designing, developing, and
                        maintaining scalable web applications. I specialize in building robust backend systems and
                        creating responsive, user-friendly front-end interfaces that deliver seamless user experiences.
                        My expertise includes working with modern technologies such as PHP, Laravel, JavaScript, and
                        MySQL, along with developing RESTful APIs and integrating third-party services like payment
                        gateways. I focus on writing clean, efficient, and maintainable code while ensuring performance
                        and security. You can explore some of my work in the Projects section, where I have contributed
                        to real-world applications including fintech platforms, API-driven systems, and e-commerce
                        solutions. I am passionate about continuous learning and enjoy sharing knowledge with the
                        developer community. I am currently open to new opportunities where I can contribute my skills,
                        take on challenging problems, and grow as a developer. If you have an opportunity that aligns
                        with my experience, feel free to get in touch.
                    </p>
                    <h4 class="skill-heading">What is my skill level?</h4>
                    <p class="skill-sub">Here are a few technologies I've been working with recently:</p>
                    <div class="skill-set">
                        <div class="skill-item">
                            <h6>HTML <span>75%</span></h6>
                            <div class="progress"><div class="progress-bar" style="width: 75%"></div></div>
                        </div>
                        <div class="skill-item">
                            <h6>CSS <span>60%</span></h6>
                            <div class="progress"><div class="progress-bar" style="width: 60%"></div></div>
                        </div>
                        <div class="skill-item">
                            <h6>Core PHP <span>90%</span></h6>
                            <div class="progress"><div class="progress-bar" style="width: 90%"></div></div>
                        </div>
                        <div class="skill-item">
                            <h6>CodeIgniter <span>85%</span></h6>
                            <div class="progress"><div class="progress-bar" style="width: 85%"></div></div>
                        </div>
                        <div class="skill-item">
                            <h6>Laravel <span>70%</span></h6>
                            <div class="progress"><div class="progress-bar" style="width: 70%"></div></div>
                        </div>
                        <div class="skill-item">
                            <h6>MySQL <span>80%</span></h6>
                            <div class="progress"><div class="progress-bar" style="width: 80%"></div></div>
                        </div>
                        <div class="skill-item">
                            <h6>JavaScript <span>70%</span></h6>
                            <div class="progress"><div class="progress-bar" style="width: 70%"></div></div>
                        </div>
                        <div class="skill-item">
                            <h6>jQuery <span>60%</span></h6>
                            <div class="progress"><div class="progress-bar" style="width: 60%"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── SERVICES ── -->
        <div id="service" class="sec gray">
            <h2 class="sec-title">Services</h2>
            <p class="sec-sub">
                Creating website layout/user interfaces by using standard HTML/CSS practices. Integrating data from
                various back-end services and databases.
            </p>
            <div class="services-grid">
                <div class="serv-card">
                    <div class="serv-icon"><i class="bi bi-code-slash"></i></div>
                    <h5>Web Development</h5>
                </div>
                <div class="serv-card">
                    <div class="serv-icon"><i class="bi bi-palette2"></i></div>
                    <h5>Website Design</h5>
                </div>
                <div class="serv-card">
                    <div class="serv-icon"><i class="bi bi-boxes"></i></div>
                    <h5>API Integration</h5>
                </div>
                <div class="serv-card">
                    <div class="serv-icon"><i class="bi bi-person-workspace"></i></div>
                    <h5>Freelancer</h5>
                </div>
                <div class="serv-card">
                    <div class="serv-icon"><i class="bi bi-credit-card-2-front"></i></div>
                    <h5>Payment Gateway</h5>
                </div>
            </div>
        </div>

        <!-- ── PORTFOLIO ── -->
        <div id="portfolio" class="sec white">
            <h2 class="sec-title">Projects</h2>
            <p class="sec-sub">
                Here you will find some of the personal and clients projects that I created with each project containing
                its own case study.
            </p>
            <div class="portfolio-grid">
                <!-- Project Card -->
                <div class="proj-card">
                    <!--         <div class="proj-thumb">
            <img src="assets/images/blog/project2.png" alt="Akontopay">
        </div> -->
                    <div class="proj-info">
                        <h5>Akontopay</h5>
                        <a href="https://akontopay.com/" target="_blank" class="proj-link">
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>

                <div class="proj-card">
                    <!--         <div class="proj-thumb">
            <img src="assets/images/blog/project3.png" alt="UBankConnect">
        </div> -->
                    <div class="proj-info">
                        <h5>UBankConnect</h5>
                        <a href="https://ubankconnect.com/" target="_blank" class="proj-link">
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>

                <div class="proj-card">
                    <!--         <div class="proj-thumb">
            <img src="assets/images/blog/product3.png" alt="Time 2 Growmedia">
        </div> -->
                    <div class="proj-info">
                        <h5>Time 2 Growmedia</h5>
                        <a href="https://www.time2growmedia.com" target="_blank" class="proj-link">
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>

                <div class="proj-card">
                    <!--         <div class="proj-thumb">
            <img src="assets/images/blog/product3.png" alt="TUK Publications">
        </div> -->
                    <div class="proj-info">
                        <h5>TUK Publications</h5>
                        <a href="https://www.tukpublications.com/" target="_blank" class="proj-link">
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>

                <div class="proj-card">
                    <!--         <div class="proj-thumb">
            <img src="assets/images/blog/product3.png" alt="KLI School">
        </div> -->
                    <div class="proj-info">
                        <h5>KLI School</h5>
                        <a href="https://www.klischool.com" target="_blank" class="proj-link">
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>

                <div class="proj-card">
                    <!--         <div class="proj-thumb">
            <img src="assets/images/blog/product3.png" alt="Enjerry">
        </div> -->
                    <div class="proj-info">
                        <h5>Enjerry</h5>
                        <a href="https://enjerry.com" target="_blank" class="proj-link">
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>

                <div class="proj-card">
                    <!--         <div class="proj-thumb">
            <img src="assets/images/blog/product3.png" alt="Martinus Education">
        </div> -->
                    <div class="proj-info">
                        <h5>Martinus Education</h5>
                        <a href="https://martinus.edu" target="_blank" class="proj-link">
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── CONTACT ── -->
        <div id="contact" class="sec gray">
            <h2 class="sec-title">Contact Us</h2>
            <p class="sec-sub">Want to get in touch? We'd love to hear from you. Here's how you can reach us...</p>
            <div class="contact-info-grid">
                <div class="contact-info-card">
                    <div class="ci-icon"><i class="bi bi-telephone"></i></div>
                    <div class="ci-body">
                        <h6>Phone</h6>
                        <ul>
                            <li>+91 961 6776 594</li>
                        </ul>
                    </div>
                </div>
                <div class="contact-info-card">
                    <div class="ci-icon"><i class="bi bi-envelope"></i></div>
                    <div class="ci-body">
                        <h6>Email</h6>
                        <ul>
                            <li>shyammilan002@gmail.com</li>
                        </ul>
                    </div>
                </div>
                <div class="contact-info-card">
                    <div class="ci-icon"><i class="bi bi-geo-alt"></i></div>
                    <div class="ci-body">
                        <h6>Address</h6>
                        <ul>
                            <li>Sector 59, Noida Uttar Pradesh, India 201301</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="contact-bottom">
                <div class="form-card">
                    <form id="contactForm">
                        <div class="mb-3">
                            <input type="text" name="name" id="name" placeholder="Your Name" /><span
                                id="nameErr"
                            ></span>
                        </div>
                        <div class="mb-3">
                            <input type="email" name="email" id="email" placeholder="Your Email" /><span
                                id="emailErr"
                            ></span>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="phone" id="phone" placeholder="Your Phone Number" /><span
                                id="phoneErr"
                            ></span>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="subject" id="subject" placeholder="Subject" /><span
                                id="subjectErr"
                            ></span>
                        </div>
                        <div class="mb-3">
                            <textarea name="message" id="message" rows="3" placeholder="Message"></textarea
                            ><span id="messageErr"></span>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn-send sendmail">Send Message</button>
                        </div>
                    </form>
                </div>
                <div class="map-card">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3501.990538015637!2d77.37673801500891!3d28.630045682418817!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ceff8864e0cf1%3A0xa20290bf75099ebd!2sBSI%20Business%20Park%20H15!5e0!3m2!1sen!2sin!4v1677239001686!5m2!1sen!2sin"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Location Map"
                    >
                    </iframe>
                </div>
            </div>
        </div>
    </div>
    <!-- /main-content -->
    <script>
        document.querySelectorAll(".proj-thumb img").forEach((img) => {
            img.onerror = function () {
                this.src = "https://placehold.co/600x300/020617/38bdf8?text=Project";
            };
        });
    </script>
    <script>
        /* ── Contact form ── */
        document.getElementById("contactForm").addEventListener("submit", function (e) {
            e.preventDefault();
            const fields = ["name", "email", "phone", "subject", "message"];
            let valid = true;
            fields.forEach((f) => {
                document.getElementById(f + "Err").textContent = "";
                if (!document.getElementById(f).value.trim()) {
                    document.getElementById(f + "Err").textContent =
                        f.charAt(0).toUpperCase() + f.slice(1) + " field must be required";
                    valid = false;
                }
            });
            if (!valid) return;
            const btn = document.querySelector(".sendmail");
            const loader = document.getElementById("loader");
            btn.textContent = "Send...";
            loader.classList.add("active");
            fetch("action.php", { method: "POST", body: new FormData(this) })
                .then((r) => r.json())
                .then((j) => {
                    if (j.status === "success") location.reload();
                })
                .catch(() => {})
                .finally(() => {
                    btn.textContent = "Send Message";
                    loader.classList.remove("active");
                });
        });
    </script>

    <script>
        const navLinks = document.querySelectorAll(".nav-link");
        const sections = document.querySelectorAll("section, div[id]");

        window.addEventListener("scroll", () => {
            let current = "";

            sections.forEach((section) => {
                const sectionTop = section.offsetTop - 150;
                const sectionHeight = section.clientHeight;

                if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
                    current = section.getAttribute("id");
                }
            });

            navLinks.forEach((link) => {
                link.classList.remove("active");

                if (link.getAttribute("href") === "#" + current) {
                    link.classList.add("active");
                }
            });
        });
    </script>
    <script>
    const sidebar = document.getElementById("sidebar");
    const hbtn = document.getElementById("hbtn");
    const hico = document.getElementById("hico");
    const sov = document.getElementById("sov");

    function openSidebar() {
        sidebar.classList.add("open");
        sov.classList.add("active");
        hico.classList.replace("bi-list", "bi-x");
    }

    function closeSidebar() {
        sidebar.classList.remove("open");
        sov.classList.remove("active");
        hico.classList.replace("bi-x", "bi-list");
    }

    hbtn.addEventListener("click", () => {
        sidebar.classList.contains("open") ? closeSidebar() : openSidebar();
    });

    // Close when clicking the overlay
    sov.addEventListener("click", closeSidebar);

    // Close when a nav link is clicked (mobile UX)
    document.querySelectorAll(".nav-link").forEach(link => {
        link.addEventListener("click", closeSidebar);
    });
</script>
</body>
</html>
