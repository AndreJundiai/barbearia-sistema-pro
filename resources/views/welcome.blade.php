<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barbearia Elite | Estilo & Tradição</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --gold-light: #E5C76B;
            --dark: #0a0a0a;
            --light: #f8f8f8;
            --gray: #1a1a1a;
            --glass: rgba(255, 255, 255, 0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark);
            color: var(--light);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3 { font-family: 'Playfair Display', serif; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

        /* Smooth Scroll */
        html { scroll-behavior: smooth; }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .animate-fade { animation: fadeIn 0.8s ease-out forwards; }

        /* Header */
        header {
            padding: 1.5rem 0;
            position: fixed;
            width: 100%;
            z-index: 100;
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .logo { 
            font-size: 1.2rem; 
            font-weight: 900; 
            color: var(--gold); 
            letter-spacing: 4px; 
            text-transform: uppercase;
        }

        nav a {
            color: var(--light);
            text-decoration: none;
            margin-left: 24px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        nav a:hover { color: var(--gold); transform: translateY(-2px); }

        .nav-login {
            background: var(--gold);
            color: var(--dark) !important;
            padding: 8px 20px;
            border-radius: 50px;
        }

        /* Hero */
        .hero {
            height: 100vh;
            background: linear-gradient(to bottom, rgba(10,10,10,0.4), rgba(10,10,10,0.9)), url('/hero.png');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
        }

        .hero-content { max-width: 800px; z-index: 2; }

        .hero-content h1 {
            font-size: clamp(3rem, 10vw, 5rem);
            margin-bottom: 1.5rem;
            line-height: 1.1;
            background: linear-gradient(135deg, #fff 0%, var(--gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-content p {
            font-size: 1.1rem;
            margin: 0 auto 2.5rem;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.7);
            letter-spacing: 1px;
        }

        .btn {
            display: inline-block;
            padding: 1.2rem 3rem;
            background: transparent;
            border: 1px solid var(--gold);
            color: var(--gold);
            text-decoration: none;
            text-transform: uppercase;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 3px;
            border-radius: 2px;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            background: var(--gold);
            color: var(--dark);
            box-shadow: 0 0 30px rgba(212, 175, 55, 0.3);
        }

        /* Services */
        .services { padding: 120px 0; background-color: var(--dark); }
        .section-title { 
            text-align: center; 
            margin-bottom: 80px; 
            font-size: 3rem; 
            color: #fff;
            position: relative;
        }
        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 2px;
            background: var(--gold);
            margin: 20px auto 0;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .service-card {
            padding: 50px 40px;
            background: var(--gray);
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 24px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: left;
            position: relative;
            overflow: hidden;
        }

        .service-card:hover {
            background: rgba(255, 255, 255, 0.02);
            border-color: var(--gold);
            transform: translateY(-15px);
        }

        .service-card h3 { 
            margin-bottom: 15px; 
            color: var(--gold); 
            font-size: 1.8rem;
        }

        .service-card p { color: rgba(255, 255, 255, 0.5); font-size: 0.95rem; }

        /* Footer */
        footer {
            padding: 80px 0;
            text-align: center;
            background: #050505;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.8rem;
            color: #444;
            letter-spacing: 1px;
        }

        @media (max-width: 768px) {
            .hero-content h1 { font-size: 3.5rem; }
            nav a { font-size: 0.7rem; margin-left: 12px; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <header>
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <div class="logo">ELITE BARBER</div>
            <nav>
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}">Painel</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Registro</a>
                        @endif
                    @endauth
                @endif
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Estilo & Tradição</h1>
                <p>Onde a elegância clássica encontra a modernidade. Reserve seu horário e experimente o melhor corte da cidade.</p>
                <a href="{{ route('booking.index') }}" class="btn">Agende Agora</a>
            </div>
        </div>
    </section>

    <section class="services">
        <div class="container">
            <h2 class="section-title">Nossos Serviços</h2>
            <div class="services-grid">
                <div class="service-card">
                    <h3>Corte Clássico</h3>
                    <p>Tesoura e máquina com acabamento impecável.</p>
                </div>
                <div class="service-card">
                    <h3>Barba Hunter</h3>
                    <p>Toalha quente e tratamento com óleos premium.</p>
                </div>
                <div class="service-card">
                    <h3>Combo Elite</h3>
                    <p>Cabelo e barba com relaxamento facial.</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2024 Barbearia Elite. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
