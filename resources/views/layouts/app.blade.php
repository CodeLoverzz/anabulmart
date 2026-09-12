<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnabulMart - Toko Pakan & Aksesoris Hewan Medan</title>
    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Icon FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans flex flex-col min-h-screen">

    <!-- Header / Navbar -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
            <a href="{{ route('catalog.index') }}" class="flex items-center gap-2 text-2xl font-bold text-orange-600">
                <i class="fa-solid fa-paw"></i> AnabulMart
            </a>
            
            <div class="flex items-center gap-4">
                <a href="{{ route('order.status') }}" class="text-sm font-medium text-gray-600 hover:text-orange-600 flex items-center gap-1">
                    <i class="fa-solid fa-magnifying-glass-location"></i> Cek Pesanan
                </a>

                @php
                    $cart = session()->get('cart', []);
                    $cartCount = array_sum(array_column($cart, 'quantity'));
                @endphp
                
                <a href="{{ route('cart.index') }}" class="relative bg-orange-100 text-orange-600 px-3 py-2 rounded-lg font-medium hover:bg-orange-200 transition flex items-center gap-2">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Keranjang</span>
                    @if($cartCount > 0)
                        <span class="bg-orange-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 py-6">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-6 border-t mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-sm">
            <p>&copy; 2026 AnabulMart Medan. All rights reserved.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>