<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Assinatura não concluída</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Fonte simples via CDN --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #0f172a;
            --card-bg: #020617;
            --border: rgba(148, 163, 184, 0.25);
            --accent: #e11d48;
            --accent-soft: rgba(248, 113, 113, 0.14);
            --text: #e5e7eb;
            --text-muted: #9ca3af;
            --btn-bg: #0ea5e9;
            --btn-bg-hover: #0284c7;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: radial-gradient(circle at top, #1f2937 0, #020617 55%, #020617 100%);
            color: var(--text);
        }

        .shell {
            width: 100%;
            max-width: 640px;
        }

        .card {
            background: radial-gradient(circle at top left, rgba(248, 113, 113, 0.08), rgba(56, 189, 248, 0.04)) border-box,
                var(--card-bg) padding-box;
            border-radius: 18px;
            border: 1px solid var(--border);
            box-shadow:
                0 24px 80px rgba(15, 23, 42, 0.85),
                0 0 0 1px rgba(15, 23, 42, 0.9);
            padding: 32px 28px;
            backdrop-filter: blur(18px);
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background-color: var(--accent-soft);
            color: var(--accent);
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .pill-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(248, 113, 113, 0.25);
        }

        h1 {
            margin-top: 18px;
            font-size: clamp(24px, 3vw, 30px);
            font-weight: 600;
            letter-spacing: -0.03em;
        }

        .subtitle {
            margin-top: 10px;
            font-size: 15px;
            line-height: 1.6;
            color: var(--text-muted);
            max-width: 40rem;
        }

        .highlight {
            color: #f9fafb;
        }

        .list {
            margin-top: 24px;
            padding-left: 0;
            list-style: none;
            display: grid;
            gap: 10px;
        }

        .list-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .list-icon {
            width: 18px;
            height: 18px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: var(--text-muted);
            flex-shrink: 0;
            margin-top: 2px;
        }

        .list-text-strong {
            color: var(--text);
            font-weight: 500;
        }

        .actions {
            margin-top: 28px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-primary,
        .btn-ghost {
            border-radius: 999px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            outline: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 150ms ease, color 150ms ease, transform 80ms ease;
        }

        .btn-primary {
            background-color: var(--btn-bg);
            color: #0f172a;
        }

        .btn-primary:hover {
            background-color: var(--btn-bg-hover);
            transform: translateY(-1px);
        }

        .btn-ghost {
            background-color: transparent;
            color: var(--text-muted);
        }

        .btn-ghost:hover {
            background-color: rgba(15, 23, 42, 0.85);
            color: var(--text);
            transform: translateY(-1px);
        }

        .btn-icon {
            font-size: 14px;
        }

        .footer-note {
            margin-top: 18px;
            font-size: 12px;
            color: rgba(148, 163, 184, 0.85);
        }

        @media (max-width: 480px) {
            .card {
                padding: 24px 18px;
            }

            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-primary,
            .btn-ghost {
                justify-content: center;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="shell">
        <div class="card">
            <div class="pill">
                <span class="pill-dot"></span>
                <span>Checkout cancelado</span>
            </div>

            <h1>Você ainda não concluiu sua <span class="highlight">assinatura</span>.</h1>

            <p class="subtitle">
                Nenhuma cobrança foi efetuada. Se foi apenas um teste ou você precisa revisar os dados de pagamento,
                você pode retomar o fluxo quando quiser.
            </p>

            <ul class="list">
                <li class="list-item">
                    <span class="list-icon">1</span>
                    <span>
                        <span class="list-text-strong">Nada foi cobrado agora.</span>
                        Se você não finalizar o checkout, seu acesso continua limitado ao plano atual.
                    </span>
                </li>
                <li class="list-item">
                    <span class="list-icon">2</span>
                    <span>
                        <span class="list-text-strong">Você pode tentar novamente.</span>
                        Retorne ao checkout para concluir o pagamento em poucos passos.
                    </span>
                </li>
                <li class="list-item">
                    <span class="list-icon">3</span>
                    <span>
                        <span class="list-text-strong">Em caso de dúvida,</span>
                        fale com o suporte informando o e‑mail utilizado no Stripe.
                    </span>
                </li>
            </ul>

            <div class="actions">
                <a href="{{ route('checkout') }}" class="btn-primary">
                    Voltar para o checkout
                    <span class="btn-icon">↻</span>
                </a>

                <a href="{{ route('dashboard') }}" class="btn-ghost">
                    Ir para o painel
                </a>
            </div>

            <p class="footer-note">
                Se você acredita que a cobrança foi feita mesmo assim, aguarde alguns minutos e use a opção de
                revalidar assinatura no painel ou entre em contato com o suporte.
            </p>
        </div>
    </div>
</body>

</html>