@extends('layouts.app')

@section('title', 'AnabulMart Petshop Medan - Pakan & Kebutuhan Anabul')

@section('content')

<!-- ================= HERO ================= -->
<section class="relative overflow-hidden bg-gradient-to-br from-amber-50 via-[#FFFBF5] to-orange-50 border-b border-amber-100">
    <!-- Aksen dekoratif tapak kaki, tipis, tidak ramai -->
    <i class="fa-solid fa-paw absolute -top-6 -right-6 text-[10rem] text-amber-200/40 rotate-12 pointer-events-none"></i>

    <div class="max-w-7xl mx-auto px-4 py-14 md:py-20 grid grid-cols-1 md:grid-cols-2 gap-10 items-center relative">
        <div>
            <span class="inline-flex items-center gap-2 bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1.5 rounded-full mb-5">
                <i class="fa-solid fa-location-dot"></i> Petshop Lokal Medan
            </span>

            <h1 class="font-display text-4xl md:text-5xl font-bold text-[#2B2118] leading-tight mb-4">
                Selamat Datang di AnabulMart Petshop Medan
            </h1>

            <p class="text-base text-gray-600 mb-8 max-w-md">
                Menyediakan pakan anabul kesayangan anda.
            </p>

            <div class="flex flex-wrap items-center gap-3">
                <a href="#katalog" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-xl shadow-md shadow-amber-500/20 transition-all flex items-center gap-2">
                    Lihat Katalog <i class="fa-solid fa-arrow-right-long text-xs"></i>
                </a>
                <a href="#tentang" class="px-6 py-3 bg-white hover:bg-amber-50 text-amber-700 font-bold text-sm rounded-xl border border-amber-200 transition-all">
                    Tentang Kami
                </a>
            </div>
        </div>

        <div class="relative">
            <div class="aspect-[4/3] rounded-3xl overflow-hidden border-4 border-white shadow-xl bg-amber-100">
                <img src="https://placehold.co/800x600/FDE68A/78350F?text=Foto+Anabul+%26+Produk"
                     alt="Anabul kesayangan AnabulMart"
                     class="w-full h-full object-cover">
            </div>
            <p class="text-[10px] text-gray-400 text-center mt-2 italic">
                *Ganti gambar ini dengan foto toko/produk asli di <code class="bg-gray-100 px-1 rounded">resources/views/catalog/index.blade.php</code>
            </p>
        </div>
    </div>
</section>

<!-- ================= KATALOG ================= -->
<section id="katalog" class="max-w-7xl mx-auto px-4 py-14 scroll-mt-20">
    <!-- HEADER KATALOG -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="font-display text-2xl font-bold text-[#2B2118]">Katalog Produk</h2>
            <p class="text-xs text-gray-500">Temukan makanan & kebutuhan terbaik untuk anabul kesayanganmu</p>
        </div>

        <!-- SEARCH -->
        <form action="{{ route('catalog.index') }}#katalog" method="GET" class="flex items-center gap-2">
            <div class="relative flex-1 md:w-64">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari produk..." class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400 text-xs"></i>
            </div>
            <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 transition-all">
                Cari
            </button>
        </form>
    </div>

    <!-- GRID PRODUK -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @forelse($products as $product)
            @php
                $firstVariant = $product->variants->first();
                $totalSold = $product->sold_count ?? 0;

                if ($totalSold >= 1000) {
                    $soldFormatted = floor($totalSold / 1000) . 'rb+ terjual';
                } else {
                    $soldFormatted = $totalSold . ' terjual';
                }

                // SESUAIKAN DENGAN KOLOM DATABASE ASLI ADMIN: main_image & variant_image
                $rawImage = $product->main_image ?? $firstVariant->variant_image ?? $firstVariant->image ?? null;

                if ($rawImage) {
                    $cleanPath = ltrim(str_replace(['public/', 'storage/'], '', $rawImage), '/');
                    $imageUrl = asset('storage/' . $cleanPath);
                } else {
                    $imageUrl = 'https://placehold.co/400x400?text=Tanpa+Foto';
                }

                $displayPrice = ($product->price && $product->price > 0) ? $product->price : ($firstVariant->price ?? 0);
            @endphp

            <!-- LINK MEMBUNGKUS SELURUH KARTU PRODUK -->
            <a href="{{ route('catalog.show', $product->id) }}" class="group bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col hover:shadow-lg hover:border-amber-400 transition-all duration-200 relative block">

                <!-- BADGE TERLARIS -->
                @if($totalSold > 2)
                    <div class="absolute top-2 left-2 z-10 bg-red-500 text-white text-[10px] font-black px-2 py-0.5 rounded-md shadow-sm flex items-center gap-1">
                        <i class="fa-solid fa-fire text-[9px]"></i> Terlaris
                    </div>
                @endif

                <!-- CONTAINER GAMBAR -->
                <div class="w-full aspect-square bg-gray-50 relative overflow-hidden">
                    <img src="{{ $imageUrl }}"
                         alt="{{ $product->name }}"
                         onerror="this.onerror=null; this.src='https://placehold.co/400x400?text=Gagal+Muat';"
                         class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300">
                </div>

                <!-- DETAIL PRODUK -->
                <div class="p-3.5 flex flex-col flex-1 justify-between bg-white">
                    <div>
                        <h3 class="text-xs font-bold text-gray-800 line-clamp-2 group-hover:text-amber-600 transition-all leading-snug mb-1.5">
                            {{ $product->name }}
                        </h3>

                        <!-- HARGA -->
                        <div class="text-sm font-black text-emerald-600 mb-2">
                            Rp {{ number_format($displayPrice, 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- FOOTER TERJUAL -->
                    <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-400">
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-bag-shopping text-amber-500 text-[10px]"></i> {{ $soldFormatted }}
                        </span>
                        <span class="text-[10px] text-amber-500 font-bold group-hover:translate-x-0.5 transition-transform">
                            Lihat <i class="fa-solid fa-chevron-right text-[8px]"></i>
                        </span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full py-12 text-center">
                <i class="fa-solid fa-box-open text-4xl text-gray-300 mb-3"></i>
                <p class="text-xs text-gray-500">Belum ada produk yang tersedia.</p>
            </div>
        @endforelse
    </div>
</section>

<!-- ================= TENTANG ================= -->
<section id="tentang" class="bg-white border-t border-amber-100 scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 py-14 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        <div class="order-2 md:order-1 rounded-3xl overflow-hidden border-4 border-amber-50 shadow-md aspect-[4/3] bg-amber-50">
            <img src="https://placehold.co/800x600/FEF3C7/92400E?text=Toko+AnabulMart"
                 alt="Toko AnabulMart Petshop Medan"
                 class="w-full h-full object-cover">
        </div>

        <div class="order-1 md:order-2">
            <h2 class="font-display text-2xl font-bold text-[#2B2118] mb-4">Tentang AnabulMart</h2>
            <p class="text-sm text-gray-600 leading-relaxed mb-4">
                AnabulMart adalah petshop rumahan di Medan yang berdiri karena kecintaan pada hewan peliharaan.
                Kami percaya setiap anabul, mulai dari kucing sampai anjing, berhak mendapatkan pakan dan
                perawatan terbaik tanpa perlu keluar rumah untuk mencarinya.
            </p>
            <p class="text-sm text-gray-600 leading-relaxed mb-6">
                Setiap produk yang kami jual dipilih langsung berdasarkan pengalaman merawat anabul sendiri,
                supaya kamu tidak perlu ragu lagi soal kualitas.
            </p>

            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-3 bg-amber-50 rounded-2xl">
                    <p class="font-display text-xl font-bold text-amber-600">{{ $products->count() }}+</p>
                    <p class="text-[11px] text-gray-500">Produk Tersedia</p>
                </div>
                <div class="text-center p-3 bg-amber-50 rounded-2xl">
                    <p class="font-display text-xl font-bold text-amber-600">100%</p>
                    <p class="text-[11px] text-gray-500">Produk Original</p>
                </div>
                <div class="text-center p-3 bg-amber-50 rounded-2xl">
                    <p class="font-display text-xl font-bold text-amber-600">Cepat</p>
                    <p class="text-[11px] text-gray-500">Respon Chat WA</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection