@extends('layouts.app')

@section('title', $product->name . ' - AnabulMart Medan')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- BREADCRUMB -->
    <nav class="flex text-xs text-gray-500 mb-6 gap-2 items-center">
        <a href="{{ route('catalog.index') }}" class="hover:text-amber-600">Katalog</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-gray-800 font-semibold truncate">{{ $product->name }}</span>
    </nav>

    @php
        $firstVariant = $product->variants->first();
        
        // BACA main_image ATAU variant_image
        $rawImage = $product->main_image ?? $firstVariant->variant_image ?? $firstVariant->image ?? null;
        
        if ($rawImage) {
            $cleanPath = ltrim(str_replace(['public/', 'storage/'], '', $rawImage), '/');
            $imageUrl = asset('storage/' . $cleanPath);
        } else {
            $imageUrl = 'https://placehold.co/500x500?text=Tanpa+Foto';
        }

        $displayPrice = ($firstVariant && $firstVariant->price > 0) ? $firstVariant->price : ($product->price ?? 0);
        $displayStock = $firstVariant->stock ?? $product->stock ?? 0;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-white p-6 rounded-3xl border border-gray-200 shadow-sm">
        <!-- GAMBAR PRODUK UTAMA -->
        <div class="w-full aspect-square bg-gray-50 rounded-2xl overflow-hidden border border-gray-100 relative">
            <img id="mainProductImg" 
                 src="{{ $imageUrl }}" 
                 alt="{{ $product->name }}" 
                 onerror="this.onerror=null; this.src='https://placehold.co/500x500?text=Gagal+Muat';"
                 class="w-full h-full object-cover transition-all duration-300">
        </div>

        <!-- INFORMASI PRODUK & VARIASI -->
        <div class="flex flex-col justify-between">
            <div>
                <span class="inline-block px-3 py-1 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-full mb-3">
                    {{ $product->category ?? 'Produk Petshop' }}
                </span>
                <h1 class="text-xl font-black text-gray-800 mb-2 leading-tight">{{ $product->name }}</h1>
                
                <!-- HARGA TERPILIH -->
                <div class="text-2xl font-black text-emerald-600 mb-2 flex items-center gap-2">
                    <span id="displayPriceText">Rp {{ number_format($displayPrice, 0, ',', '.') }}</span>
                </div>

                <!-- STOK TERPILIH -->
                <div class="text-xs font-semibold text-gray-500 mb-6">
                    Stok Tersedia: <span id="displayStockText" class="text-gray-800 font-bold">{{ $displayStock }} pcs</span>
                </div>

                <!-- DESKRIPSI -->
                <div class="mb-6">
                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Deskripsi Produk</h3>
                    <p class="text-xs text-gray-600 leading-relaxed whitespace-pre-line">
                        {{ $product->description ?? 'Tidak ada deskripsi khusus untuk produk ini.' }}
                    </p>
                </div>

                <!-- FORM PEMBELIAN & PILIH VARIASI -->
                <form action="{{ route('cart.add', $product->id) }}" method="POST" id="addToCartForm">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" id="selectedVariantId" value="{{ $firstVariant->id ?? '' }}">

                    <!-- LIST VARIASI INTERAKTIF -->
                    @if($product->variants && $product->variants->count() > 0)
                        <div class="mb-6">
                            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Pilih Variasi</h3>
                            <div class="flex flex-wrap gap-2" id="variantButtonsContainer">
                                @foreach($product->variants as $index => $variant)
                                    @php
                                        $vImg = $variant->variant_image ?? $variant->image ?? null;
                                        $vUrl = $vImg ? asset('storage/' . ltrim(str_replace(['public/', 'storage/'], '', $vImg), '/')) : $imageUrl;
                                        $vPrice = ($variant->price > 0) ? $variant->price : $displayPrice;
                                    @endphp
                                    <button type="button" 
                                            onclick="selectVariant(this, '{{ $variant->id }}', '{{ $vUrl }}', '{{ $vPrice }}', '{{ $variant->stock }}')"
                                            class="variant-btn flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all {{ $index === 0 ? 'bg-amber-500 text-white border-amber-500 shadow-sm' : 'bg-gray-50 text-gray-700 border-gray-200 hover:border-amber-400' }}">
                                        @if($vImg)
                                            <img src="{{ $vUrl }}" class="w-5 h-5 object-cover rounded border border-white">
                                        @endif
                                        <span>{{ $variant->variant_name }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- JUMLAH ORDER -->
                    <div class="mb-6">
                        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Jumlah</h3>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden bg-white">
                                <button type="button" onclick="adjustQty(-1)" class="px-3 py-2 bg-gray-50 hover:bg-gray-100 text-gray-600 font-bold text-xs transition-all">-</button>
                                <input type="number" name="quantity" id="quantityInput" value="1" min="1" max="{{ $displayStock }}" class="w-12 text-center text-xs font-bold text-gray-800 focus:outline-none border-none">
                                <button type="button" onclick="adjustQty(1)" class="px-3 py-2 bg-gray-50 hover:bg-gray-100 text-gray-600 font-bold text-xs transition-all">+</button>
                            </div>
                        </div>
                    </div>

                    <!-- TOMBOL AKSI: MASUKKAN KERANJANG & CHECKOUT -->
                    <div class="pt-4 border-t border-gray-100 flex items-center gap-3">
                        <button type="submit" name="action" value="add_to_cart" class="flex-1 py-3 px-4 bg-amber-100 hover:bg-amber-200 text-amber-800 font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-cart-shopping"></i> Masukkan Keranjang
                        </button>
                        
                        <button type="submit" name="action" value="checkout" class="flex-1 py-3 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                            <i class="fa-solid fa-bolt"></i> Beli Sekarang
                        </button>
                    </div>
                </form>
            </div>

            <!-- TOMBOL KEMBALI -->
            <div class="mt-4">
                <a href="{{ route('catalog.index') }}" class="text-xs text-gray-500 hover:text-amber-600 font-semibold inline-flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke Katalog
                </a>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT KLIK VARIASI & JUMLAH -->
<script>
    function selectVariant(btn, variantId, imgUrl, price, stock) {
        // Update input hidden ID variasi
        document.getElementById('selectedVariantId').value = variantId;

        // Ubah gambar utama
        if(imgUrl && imgUrl !== '') {
            document.getElementById('mainProductImg').src = imgUrl;
        }

        // Ubah teks harga
        const formattedPrice = new Intl.NumberFormat('id-ID').format(price);
        document.getElementById('displayPriceText').innerText = 'Rp ' + formattedPrice;

        // Ubah teks stok & batas input qty
        document.getElementById('displayStockText').innerText = stock + ' pcs';
        const qtyInput = document.getElementById('quantityInput');
        qtyInput.max = stock;
        if(parseInt(qtyInput.value) > parseInt(stock)) {
            qtyInput.value = stock > 0 ? stock : 1;
        }

        // Tampilan style tombol variasi yang aktif
        const allBtns = document.querySelectorAll('.variant-btn');
        allBtns.forEach(b => {
            b.classList.remove('bg-amber-500', 'text-white', 'border-amber-500', 'shadow-sm');
            b.classList.add('bg-gray-50', 'text-gray-700', 'border-gray-200');
        });

        btn.classList.remove('bg-gray-50', 'text-gray-700', 'border-gray-200');
        btn.classList.add('bg-amber-500', 'text-white', 'border-amber-500', 'shadow-sm');
    }

    function adjustQty(amount) {
        const input = document.getElementById('quantityInput');
        let current = parseInt(input.value) || 1;
        let maxStock = parseInt(input.max) || 999;
        let nextVal = current + amount;

        if (nextVal >= 1 && nextVal <= maxStock) {
            input.value = nextVal;
        }
    }
</script>
@endsection