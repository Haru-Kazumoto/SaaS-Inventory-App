<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Tidak Tersedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            950: '#060D1F',
                            900: '#0A1628',
                            800: '#0F2040',
                            700: '#162C58',
                            600: '#1E3A6E',
                            400: '#3A6BC4',
                            300: '#6B9DE8',
                            200: '#A8C4F0',
                            100: '#D6E5FA',
                        }
                    },
                    fontFamily: {
                        mono: ['"JetBrains Mono"', '"Fira Code"', 'ui-monospace', 'monospace'],
                        sans: ['"Inter"', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #0A1628;
        }

        .rule-line {
            border-top: 1px solid rgba(59, 107, 196, 0.25);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #EF4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
        }

        .code-block {
            border-left: 3px solid #1E3A6E;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col font-sans text-navy-200" style="background-color: #0A1628;">

    <!-- Top bar -->
    <div class="rule-line">
        <div class="max-w-4xl mx-auto px-6 py-3 flex items-center justify-between">
            <span class="font-mono text-xs text-navy-300 tracking-widest uppercase">System Status</span>
            <div class="flex items-center gap-2">
                <div class="status-dot"></div>
                <span class="font-mono text-xs text-red-400">Unavailable</span>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <main class="flex-1 flex items-center justify-center px-6 py-16">
        <div class="max-w-2xl w-full">

            <!-- Error code -->
            <div class="mb-8">
                <p class="font-mono text-xs tracking-widest uppercase text-navy-400 mb-3">HTTP Error</p>
                <h1 class="font-mono font-medium text-navy-100 leading-none"
                    style="font-size: clamp(4rem, 12vw, 8rem); color: rgba(168, 196, 240, 0.08); letter-spacing: -0.04em; line-height: 1;">
                    503
                </h1>
            </div>

            <div class="rule-line mb-8"></div>

            <!-- Message -->
            <div class="mb-10">
                <h2 class="text-xl font-semibold text-navy-100 mb-3" style="color: #D6E5FA;">
                    Layanan Sedang Tidak Tersedia
                </h2>
                <p class="text-navy-300 text-sm leading-relaxed max-w-lg" style="color: #6B9DE8;">
                    Server kami sedang menjalani pemeliharaan terjadwal atau mengalami lonjakan beban.
                    Tidak ada data yang hilang, aplikasi akan kembali normal dalam waktu dekat.
                </p>
            </div>

            <!-- Info block -->
            <div class="code-block pl-4 mb-10 space-y-2">
                <div class="flex items-start gap-3">
                    <span class="font-mono text-xs text-navy-400 mt-0.5 shrink-0">STATUS</span>
                    <span class="font-mono text-xs text-red-400">Service Unavailable</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="font-mono text-xs text-navy-400 mt-0.5 shrink-0">CODE&nbsp;&nbsp;</span>
                    <span class="font-mono text-xs text-navy-300">503</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="font-mono text-xs text-navy-400 mt-0.5 shrink-0">TIME&nbsp;&nbsp;</span>
                    <span class="font-mono text-xs text-navy-300" id="ts">—</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-3">
                <button
                    onclick="window.location.reload()"
                    class="px-5 py-2.5 text-sm font-medium rounded transition-colors duration-150 cursor-pointer"
                    style="background-color: #1E3A6E; color: #A8C4F0; border: 1px solid rgba(59,107,196,0.4);"
                    onmouseover="this.style.backgroundColor='#162C58'"
                    onmouseout="this.style.backgroundColor='#1E3A6E'">
                    Coba Lagi
                </button>
                <!--
                <a
                    href="/"
                    class="px-5 py-2.5 text-sm font-medium rounded transition-colors duration-150"
                    style="background: transparent; color: #6B9DE8; border: 1px solid rgba(59,107,196,0.2);"
                    onmouseover="this.style.borderColor='rgba(59,107,196,0.5)'"
                    onmouseout="this.style.borderColor='rgba(59,107,196,0.2)'">
                    Kembali ke Beranda
                </a>
                -->
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="rule-line">
        <div class="max-w-4xl mx-auto px-6 py-4 flex flex-wrap items-center justify-between gap-2">
            <span class="font-mono text-xs text-navy-400">
                Jika masalah berlanjut, hubungi tim teknis Anda.
            </span>
        </div>
    </footer>

    <script>
        // Timestamp
        const el = document.getElementById('ts');
        const now = new Date();
        el.textContent = now.toISOString().replace('T', ' ').substring(0, 19) + ' UTC';
    </script>
</body>

</html>