@extends('layouts.app')

@section('title', 'Katalog Produk - AnabulMart Medan')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- HEADER KATALOG -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-800">Katalog Produk AnabulMart</h1>
            <p class="text-xs text-gray-500">Temukan makanan & kebutuhan terbaik untuk anabul kesayanganmu</p>
        </div>
        
        <!-- SEARCH -->
        <form action="{{ route('catalog.index') }}" method="GET" class="flex items-center gap-2">
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
</div>
@endsection