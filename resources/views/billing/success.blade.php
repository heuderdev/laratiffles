<!DOCTYPE html>
<html lang="pt-BR" class="h-full">

<head>
    <meta charset="UTF-8">
    <title>Pagamento confirmado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-950 text-slate-50 flex items-center justify-center px-4">
    <div class="max-w-lg w-full">
        <div
            class="bg-gradient-to-br from-emerald-500/10 via-slate-900 to-slate-950 border border-emerald-500/40 rounded-2xl shadow-2xl shadow-emerald-500/20 p-8 relative overflow-hidden">
            <div
                class="pointer-events-none absolute -top-24 -right-24 w-72 h-72 rounded-full bg-emerald-500/20 blur-3xl">
            </div>
            <div class="relative flex flex-col items-center text-center space-y-6">
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/10 border border-emerald-400/60">
                    <svg class="h-9 w-9 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12.75L11.25 15 15 9.75" />
                        <circle cx="12" cy="12" r="9" />
                    </svg>
                </div>

                <div class="space-y-2">
                    <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">
                        Pagamento confirmado
                    </h1>
                    <p class="text-sm md:text-base text-slate-300 max-w-md mx-auto">
                        Sua assinatura foi ativada com sucesso. Em poucos instantes você será redirecionado
                        para o painel da plataforma.
                    </p>
                </div>

                @if(session('success'))
                <div
                    class="w-full rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                    {{ session('success') }}
                </div>
                @endif

                <div class="flex flex-col sm:flex-row gap-3 w-full justify-center mt-4">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-emerald-500 text-slate-950 text-sm font-medium shadow-lg shadow-emerald-500/30 hover:bg-emerald-400 transition">
                        Ir para o painel
                    </a>

                    <a href="{{ route('portal') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-slate-600/70 text-sm font-medium text-slate-200 hover:bg-slate-800/60 transition">
                        Gerenciar cobrança
                    </a>
                </div>

                <p class="text-xs text-slate-400 pt-2">
                    Se algo não estiver certo com sua assinatura, você pode revalidar o status pela área de cobrança.
                </p>
            </div>
        </div>
    </div>

    <script>
        setTimeout(function () {
            window.location.href = "{{ route('dashboard') }}";
        }, 5000);
    </script>
</body>

</html>