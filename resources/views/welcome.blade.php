<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Koúrier - Secure dataset tooling</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            @keyframes kourier-rise {
                from { opacity: 0; transform: translateY(18px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @keyframes kourier-drift {
                0%, 100% { transform: translate3d(0, 0, 0) rotate(0deg); }
                50% { transform: translate3d(18px, -14px, 0) rotate(1.5deg); }
            }

            @media (prefers-reduced-motion: no-preference) {
                .kourier-rise { animation: kourier-rise 700ms ease-out both; }
                .kourier-rise-delay { animation: kourier-rise 850ms 120ms ease-out both; }
                .kourier-drift { animation: kourier-drift 9s ease-in-out infinite; }
            }
        </style>
    </head>
    <body class="min-h-screen bg-[#08110f] text-white antialiased">
        @php
            $currentTeam = auth()->user()?->currentTeam;
            $dashboardUrl = $currentTeam ? route('dashboard', $currentTeam) : route('teams.index');
        @endphp

        <main class="relative isolate min-h-screen overflow-hidden">
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_18%_18%,rgba(63,224,185,0.22),transparent_28%),radial-gradient(circle_at_72%_0%,rgba(251,191,36,0.2),transparent_30%),linear-gradient(135deg,#07110f_0%,#101a18_48%,#192016_100%)]"></div>
            <div class="absolute inset-0 -z-10 opacity-[0.16] [background-image:linear-gradient(rgba(255,255,255,.35)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.35)_1px,transparent_1px)] [background-size:54px_54px]"></div>

            <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-10">
                <a href="{{ route('home') }}" class="text-2xl font-semibold tracking-tight">Koúrier</a>

                <nav class="flex items-center gap-3 text-sm text-white/80">
                    @auth
                        <a href="{{ $dashboardUrl }}" class="rounded-full border border-white/20 px-4 py-2 transition hover:border-white/50 hover:text-white">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full px-4 py-2 transition hover:text-white">Log in</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-white px-4 py-2 font-medium text-[#101a18] transition hover:bg-amber-100">Register</a>
                    @endauth
                </nav>
            </header>

            <section class="mx-auto grid min-h-[calc(100svh-88px)] w-full max-w-7xl items-center gap-12 px-6 pb-16 pt-8 lg:grid-cols-[0.95fr_1.05fr] lg:px-10">
                <div class="max-w-3xl kourier-rise">
                    <p class="mb-5 text-sm font-medium uppercase tracking-[0.32em] text-emerald-200/80">Secure dataset tooling</p>
                    <h1 class="text-5xl font-semibold tracking-[-0.07em] text-white sm:text-7xl lg:text-8xl">
                        Private data in. Clean exports out.
                    </h1>
                    <p class="mt-7 max-w-2xl text-lg leading-8 text-white/68 sm:text-xl">
                        Upload artifacts, label review-ready records, build manifest-backed ZIP packages, and keep every sensitive download behind team authorization.
                    </p>

                    <div class="mt-9 flex flex-col gap-4 sm:flex-row">
                        @auth
                            <a href="{{ $dashboardUrl }}" class="inline-flex items-center justify-center rounded-full bg-emerald-300 px-6 py-3 text-sm font-semibold text-[#07110f] transition hover:bg-emerald-200">
                                Open dashboard
                            </a>
                            <a href="{{ $currentTeam ? route('projects.index', $currentTeam) : route('teams.index') }}" class="inline-flex items-center justify-center rounded-full border border-white/20 px-6 py-3 text-sm font-semibold text-white transition hover:border-white/50">
                                {{ $currentTeam ? 'View projects' : 'Choose team' }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full bg-emerald-300 px-6 py-3 text-sm font-semibold text-[#07110f] transition hover:bg-emerald-200">
                                Log in with demo
                            </a>
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full border border-white/20 px-6 py-3 text-sm font-semibold text-white transition hover:border-white/50">
                                Register
                            </a>
                        @endauth
                    </div>

                    <div class="mt-10 grid max-w-xl gap-3 border-l border-white/15 pl-5 text-sm text-white/68 sm:grid-cols-2 sm:border-l-0 sm:pl-0">
                        <div>
                            <div class="text-white">Demo email</div>
                            <code class="mt-1 block text-emerald-200">otsugua@example.com</code>
                        </div>
                        <div>
                            <div class="text-white">Demo password</div>
                            <code class="mt-1 block text-emerald-200">pass</code>
                        </div>
                    </div>
                </div>

                <div class="relative kourier-rise-delay">
                    <div class="kourier-drift absolute -right-10 -top-10 h-44 w-44 rounded-full bg-amber-300/25 blur-3xl"></div>
                    <div class="absolute -bottom-8 left-10 h-56 w-56 rounded-full bg-emerald-300/20 blur-3xl"></div>

                    <div class="relative overflow-hidden rounded-[2rem] border border-white/12 bg-white/[0.06] p-5 shadow-2xl shadow-black/40 backdrop-blur-xl">
                        <div class="flex items-center justify-between border-b border-white/10 pb-4">
                            <div>
                                <div class="text-sm font-medium text-white">Equine sensor demo</div>
                                <div class="mt-1 text-xs text-white/45">Private workspace · 3 approved artifacts</div>
                            </div>
                            <div class="rounded-full bg-emerald-300/15 px-3 py-1 text-xs font-medium text-emerald-200">ready</div>
                        </div>

                        <div class="grid gap-4 py-5 sm:grid-cols-[1fr_0.72fr]">
                            <div class="space-y-3">
                                @foreach ([['sensor-session-001.csv', 'approved'], ['stride-audio-note.txt', 'approved'], ['barn-camera-frame.json', 'reviewed']] as [$file, $status])
                                    <div class="group flex items-center justify-between border-b border-white/10 py-3 last:border-b-0">
                                        <div>
                                            <div class="text-sm text-white">{{ $file }}</div>
                                            <div class="mt-1 text-xs text-white/42">SHA-256 · labeled · {{ $status }}</div>
                                        </div>
                                        <div class="h-2 w-16 overflow-hidden rounded-full bg-white/10">
                                            <div class="h-full rounded-full bg-emerald-300 transition-all duration-500 group-hover:w-full {{ $loop->first ? 'w-11/12' : ($loop->last ? 'w-3/4' : 'w-5/6') }}"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="rounded-3xl bg-[#07110f]/70 p-4">
                                <div class="text-xs uppercase tracking-[0.22em] text-white/35">Manifest</div>
                                <div class="mt-5 space-y-3 font-mono text-[0.68rem] text-emerald-100/80">
                                    <div>artifact_id,filename,status</div>
                                    <div>1,sensor-session-001.csv,approved</div>
                                    <div>2,stride-audio-note.txt,approved</div>
                                    <div>3,barn-camera-frame.json,reviewed</div>
                                </div>
                                <div class="mt-6 rounded-2xl border border-amber-200/20 bg-amber-200/10 p-3 text-xs text-amber-100">
                                    export package completed · signed download only
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-px overflow-hidden rounded-3xl bg-white/10 sm:grid-cols-3">
                            <div class="bg-white/[0.06] p-4">
                                <div class="text-2xl font-semibold">3</div>
                                <div class="mt-1 text-xs text-white/45">artifacts</div>
                            </div>
                            <div class="bg-white/[0.06] p-4">
                                <div class="text-2xl font-semibold">1</div>
                                <div class="mt-1 text-xs text-white/45">export ZIP</div>
                            </div>
                            <div class="bg-white/[0.06] p-4">
                                <div class="text-2xl font-semibold">9</div>
                                <div class="mt-1 text-xs text-white/45">audit events</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
