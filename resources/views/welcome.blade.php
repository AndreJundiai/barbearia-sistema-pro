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
            --dark: #0a0a0a;
            --light: #f4f4f4;
            --gray: #222;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark);
            color: var(--light);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2 { font-family: 'Playfair Display', serif; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        /* Header */
        header {
            padding: 2rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: absolute;
            width: 100%;
            z-index: 10;
        }

        .logo { font-size: 1.5rem; font-weight: 700; color: var(--gold); letter-spacing: 2px; }

        nav a {
            color: var(--light);
            text-decoration: none;
            margin-left: 20px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: color 0.3s;
        }

        nav a:hover { color: var(--gold); }

        /* Hero */
        .hero {
            height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('/hero.png');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--gold);
        }

        .hero-content p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 2rem;
            font-weight: 300;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2.5rem;
            border: 1px solid var(--gold);
            color: var(--gold);
            text-decoration: none;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 2px;
            transition: all 0.4s ease;
        }

        .btn:hover {
            background-color: var(--gold);
            color: var(--dark);
        }

        /* Services */
        .services { padding: 100px 0; background-color: #050505; }
        .section-title { text-align: center; margin-bottom: 60px; font-size: 2.5rem; color: var(--gold); }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .service-card {
            padding: 40px;
            background: var(--gray);
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            text-align: center;
        }

        .service-card:hover {
            border-bottom-color: var(--gold);
            transform: translateY(-10px);
        }

        .service-card h3 { margin-bottom: 15px; color: var(--gold); }

        /* Footer */
        footer {
            padding: 60px 0;
            text-align: center;
            border-top: 1px solid #111;
            font-size: 0.8rem;
            color: #555;
        }

        @media (max-width: 768px) {
            .hero-content h1 { font-size: 2.5rem; }
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
