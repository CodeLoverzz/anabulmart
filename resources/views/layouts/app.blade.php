<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AnabulMart - Toko Pakan & Aksesoris Hewan Medan')</title>
    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Icon FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Font judul: Fredoka (ramah & bulat, cocok untuk brand petshop) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        .font-display { font-family: 'Fredoka', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="bg-[#FFFBF5] text-[#2B2118] font-sans flex flex-col min-h-screen">

    <!-- Header / Navbar -->
    <header class="bg-white/90 backdrop-blur shadow-sm border-b border-amber-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center gap-4">
            <a href="{{ route('catalog.index') }}" class="flex items-center gap-2 text-xl font-display font-bold text-orange-600 shrink-0">
                <i class="fa-solid fa-paw"></i> AnabulMart
            </a>

            <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
                <a href="{{ route('catalog.index') }}#katalog" class="hover:text-orange-600 transition">Katalog</a>
                <a href="{{ route('catalog.index') }}#tentang" class="hover:text-orange-600 transition">Tentang</a>
                <a href="{{ route('catalog.index') }}#kontak" class="hover:text-orange-600 transition">Hubungi Kami</a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-4">
                <a href="{{ route('order.status') }}" class="text-xs sm:text-sm font-medium text-gray-600 hover:text-orange-600 flex items-center gap-1">
                    <i class="fa-solid fa-magnifying-glass-location"></i> <span class="hidden sm:inline">Cek Pesanan</span>
                </a>

                @php
                    $cart = session()->get('cart', []);
                    $cartCount = array_sum(array_column($cart, 'quantity'));
                @endphp

                <a href="{{ route('cart.index') }}" class="relative bg-orange-100 text-orange-600 px-3 py-2 rounded-lg font-medium hover:bg-orange-200 transition flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="hidden sm:inline">Keranjang</span>
                    @if($cartCount > 0)
                        <span class="bg-orange-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>

        <!-- Menu Tentang/Hubungi untuk layar kecil -->
        <div class="md:hidden flex justify-center gap-6 text-xs font-medium text-gray-500 pb-2">
            <a href="{{ route('catalog.index') }}#katalog" class="hover:text-orange-600">Katalog</a>
            <a href="{{ route('catalog.index') }}#tentang" class="hover:text-orange-600">Tentang</a>
            <a href="{{ route('catalog.index') }}#kontak" class="hover:text-orange-600">Hubungi Kami</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow w-full">
        <div class="max-w-7xl mx-auto px-4 pt-6">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative">
                    {{ session('error') }}
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <!-- Footer / Hubungi Kami -->
    <footer id="kontak" class="bg-[#2B2118] text-amber-50 mt-auto">
        <div class="max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-3 gap-10">
            <div>
                <div class="flex items-center gap-2 text-xl font-display font-bold text-amber-400 mb-3">
                    <i class="fa-solid fa-paw"></i> AnabulMart
                </div>
                <p class="text-sm text-amber-100/70 leading-relaxed">
                    Petshop rumahan di Medan yang menyediakan pakan dan kebutuhan harian anabul kesayanganmu, dari kucing sampai anjing.
                </p>
            </div>

            <div>
                <h4 class="text-sm font-bold text-amber-400 uppercase tracking-wide mb-3">Hubungi Kami</h4>
                <ul class="space-y-2.5 text-sm text-amber-100/80">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-location-dot mt-1 text-amber-400"></i>
                        <span>Jl. Kasuari No. 13, Kec. Medan Sunggal, Kota Medan</span>
                    </li>
                    <li>
                        <a href="https://wa.me/6285175217503" target="_blank" class="flex items-center gap-2 hover:text-amber-300 transition">
                            <i class="fa-brands fa-whatsapp text-amber-400"></i> 0851-7521-7503
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-regular fa-clock text-amber-400"></i> Setiap Hari, 08.00 - 20.00 WIB
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-bold text-amber-400 uppercase tracking-wide mb-3">Tautan Cepat</h4>
                <ul class="space-y-2.5 text-sm text-amber-100/80">
                    <li><a href="{{ route('catalog.index') }}#katalog" class="hover:text-amber-300 transition">Katalog Produk</a></li>
                    <li><a href="{{ route('catalog.index') }}#tentang" class="hover:text-amber-300 transition">Tentang Kami</a></li>
                    <li><a href="{{ route('order.status') }}" class="hover:text-amber-300 transition">Cek Status Pesanan</a></li>
                    <li><a href="{{ route('cart.index') }}" class="hover:text-amber-300 transition">Keranjang Belanja</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-amber-100/10 py-4 text-center text-xs text-amber-100/50">
            &copy; {{ date('Y') }} AnabulMart Petshop Medan. Semua hak dilindungi.
        </div>
    </footer>

    <!-- Tombol Scroll ke Atas -->
    <button id="backToTop" aria-label="Kembali ke atas"
            class="hidden fixed bottom-6 right-6 z-50 w-11 h-11 rounded-full bg-amber-500 text-white shadow-lg hover:bg-amber-600 transition-all items-center justify-center">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    <script>
        const backToTopBtn = document.getElementById('backToTop');
        window.addEventListener('scroll', function () {
            if (window.scrollY > 400) {
                backToTopBtn.classList.remove('hidden');
                backToTopBtn.classList.add('flex');
            } else {
                backToTopBtn.classList.add('hidden');
                backToTopBtn.classList.remove('flex');
            }
        });
        backToTopBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>

    @stack('scripts')
</body>
</html>