@extends('layouts.admin')

@section('title', 'Data Produk - Admin AnabulMart')
@section('page_title', 'Kelola Data Produk')

@section('content')
<div class="flex justify-between items-center mb-6">
    <p class="text-sm text-gray-500">Daftar seluruh produk petshop beserta variasi & stok otomatis.</p>
    <a href="{{ route('admin.product.create') }}" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-4 py-2 rounded-lg text-sm flex items-center gap-2 shadow-sm transition-all">
        <i class="fa-solid fa-plus"></i> Tambah Produk Baru
    </a>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg text-sm font-semibold flex items-center justify-between">
        <span>{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" class="text-emerald-800 font-bold">&times;</button>
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-100 text-gray-700 font-semibold uppercase text-xs tracking-wider border-b border-gray-200">
                <tr>
                    <th class="py-3.5 px-5">SKU & Produk Utama</th>
                    <th class="py-3.5 px-5">Rincian Variasi & Foto</th>
                    <th class="py-3.5 px-5">Harga Jual</th>
                    <th class="py-3.5 px-5">Berat</th>
                    <th class="py-3.5 px-5">Total Stok</th>
                    <th class="py-3.5 px-5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                
                {{-- [PERBAIKAN 1]: Blok PHP dimasukkan DI DALAM @forelse setelah $product dipanggil --}}
                @php
                    $hasVariants = $product->variants && $product->variants->count() > 0;
                    $totalStok = $hasVariants ? $product->variants->sum('stock') : ($product->stock ?? 0);
                    $firstVariant = $hasVariants ? $product->variants->first() : null;
                    $realPrice = ($product->price && $product->price > 0) ? $product->price : ($firstVariant->price ?? 0);

                    // STREAM MEDIA GAMBAR PRODUK UTAMA (BYPASS 403 XAMPP)
                    $imgFile = $product->main_image ?? $product->image ?? ($firstVariant->image ?? null);
                    $cleanMainImg = $imgFile ? ltrim(str_replace(['public/', 'storage/', '\\'], ['', '', '/'], $imgFile), '/') : null;
                    $mainImageUrl = $cleanMainImg ? route('media.show', ['path' => $cleanMainImg]) : null;
                @endphp

                <tr class="hover:bg-gray-50/80 transition-all">
                    <!-- PRODUK UTAMA -->
                    <td class="py-4 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0 border flex items-center justify-center">
                                
                                {{-- [PERBAIKAN 2]: Gunakan $mainImageUrl agar gambar tidak 403 --}}
                                @if($mainImageUrl)
                                    <img src="{{ $mainImageUrl }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-[10px] text-gray-400 font-medium">No Img</span>
                                @endif

                            </div>
                            <div>
                                <span class="font-bold text-gray-800 block">{{ $product->name }}</span>
                                <span class="text-xs font-mono text-amber-600 font-semibold">SKU: {{ $product->sku ?? 'ANB-AUTO' }}</span>
                            </div>
                        </div>
                    </td>

                    <!-- RINCIAN VARIASI & FOTO VARIASI -->
                    <td class="py-4 px-5">
                        @if($hasVariants)
                            <div class="flex flex-col gap-1.5">
                                @foreach($product->variants as $variant)
                                    
                                    {{-- [PERBAIKAN 3]: Stream Media Gambar Varian --}}
                                    @php
                                        $vImg = $variant->variant_image ?? $variant->image ?? null;
                                        $cleanVImg = $vImg ? ltrim(str_replace(['public/', 'storage/', '\\'], ['', '', '/'], $vImg), '/') : null;
                                        $variantUrl = $cleanVImg ? route('media.show', ['path' => $cleanVImg]) : null;
                                    @endphp

                                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-2 py-1 rounded-md text-xs">
                                        <div class="w-6 h-6 rounded bg-gray-200 overflow-hidden flex-shrink-0 border">
                                            
                                            {{-- [PERBAIKAN 4]: Gunakan $variantUrl --}}
                                            @if($variantUrl)
                                                <img src="{{ $variantUrl }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-[8px] text-gray-400">N/A</div>
                                            @endif

                                        </div>
                                        <span class="font-semibold text-gray-700">{{ $variant->variant_name ?? $variant->name }}</span>
                                        <span class="text-gray-400">|</span>
                                        <span class="text-amber-700 font-medium">{{ $variant->stock }} pcs</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-gray-400 italic">Tanpa Variasi</span>
                        @endif
                    </td>

                    <!-- HARGA JUAL -->
                    <td class="py-4 px-5 font-bold text-gray-800">
                        Rp {{ number_format($realPrice, 0, ',', '.') }}
                    </td>

                    <!-- BERAT -->
                    <td class="py-4 px-5 text-xs">
                        {{ $product->weight_gram ?? 1000 }} gram
                    </td>

                    <!-- TOTAL STOK -->
                    <td class="py-4 px-5">
                        <div class="flex flex-col">
                            <span class="font-bold {{ $totalStok < 5 ? 'text-red-500' : 'text-emerald-600' }} text-base">
                                {{ $totalStok }} pcs
                            </span>
                            @if($hasVariants)
                                <span class="text-[10px] text-gray-400">(Total Sum Variasi)</span>
                            @endif
                        </div>
                    </td>

                    <!-- AKSI (EDIT & HAPUS POP-UP) -->
                    <td class="py-4 px-5 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('admin.product.edit', $product->id) }}" class="text-xs px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded font-medium transition-all shadow-sm">
                                Edit
                            </a>

                            <!-- Tombol Trigger Modal Pop-Up Custom -->
                            <button type="button" onclick="openDeleteModal('{{ $product->id }}', '{{ addslashes($product->name) }}')" class="text-xs px-2.5 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded font-medium transition-all shadow-sm">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-400 italic">
                        Belum ada data produk. Klik tombol "Tambah Produk Baru" untuk menambahkan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODAL CONFIRMATION DELETE CUSTOM ================= -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 transition-all">
    <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 transform transition-all scale-95 opacity-0 duration-200" id="modalBox">
        
        <!-- Icon Peringatan -->
        <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl shadow-inner">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <!-- Teks Konfirmasi -->
        <div class="text-center mb-6">
            <h3 class="text-lg font-extrabold text-gray-900 mb-2">Hapus Produk Ini?</h3>
            <p class="text-xs text-gray-500 leading-relaxed">
                Apakah Anda yakin ingin menghapus produk <span id="modalProductName" class="font-bold text-gray-800 underline"></span>? 
                <br><span class="text-red-500 font-semibold">Tindakan ini akan menghapus produk beserta seluruh variasinya secara permanen.</span>
            </p>
        </div>

        <!-- Form & Tombol Aksi -->
        <form id="deleteForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="flex items-center justify-center gap-3">
                <button type="button" onclick="closeDeleteModal()" class="w-1/2 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-xs transition-all">
                    Batal
                </button>
                <button type="submit" class="w-1/2 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow-md shadow-red-200 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-trash-can"></i> Ya, Hapus Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPT UNTUK BUKA/TUTUP MODAL -->
<script>
    function openDeleteModal(productId, productName) {
        const modal = document.getElementById('deleteModal');
        const modalBox = document.getElementById('modalBox');
        const deleteForm = document.getElementById('deleteForm');
        const productNameSpan = document.getElementById('modalProductName');

        // Set Form Action Route URL
        deleteForm.action = `/admin/products/${productId}`;
        productNameSpan.innerText = productName;

        // Tampilkan Modal dengan animasi smooth
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalBox.classList.remove('scale-95', 'opacity-0');
            modalBox.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        const modalBox = document.getElementById('modalBox');

        modalBox.classList.remove('scale-100', 'opacity-100');
        modalBox.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 150);
    }

    // Close modal if user clicks outside the modal box
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('deleteModal');
        if (e.target === modal) {
            closeDeleteModal();
        }
    });
</script>
@endsection