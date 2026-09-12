@extends('layouts.admin')

@section('title', 'Tambah Produk Baru - Admin AnabulMart')
@section('page_title', 'Tambah Produk Baru')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-200">
    <!-- BLOK NOTIFIKASI ERROR VALIDASI (AGAR REASON GAGAL SIMPAN KELIHATAN) -->
@if ($errors->any())
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm space-y-1">
        <div class="flex items-center text-red-800 font-bold text-xs mb-1">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i> Gagal Menyimpan Produk! Periksa Input Berikut:
        </div>
        <ul class="list-disc list-inside text-xs text-red-600 space-y-0.5 pl-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-800 rounded-xl text-xs font-bold">
        {{ session('error') }}
    </div>
@endif
    <form action="{{ route('admin.product.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf

        <div class="space-y-6">
            <!-- 1. INFORMASI UTAMA -->
            <div>
                <h3 class="text-base font-bold text-gray-800 mb-4 border-b pb-2">Informasi Produk</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Produk Utama *</label>
                        <input type="text" name="name" id="productName" required placeholder="Contoh: Cat Choize Adult" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">SKU (Otomatis)</label>
                        <input type="text" name="sku" id="productSku" readonly placeholder="Terbit Otomatis..." class="w-full text-sm bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 font-mono text-amber-600 font-bold cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kategori Produk</label>
                        <input type="text" name="category_name" list="categories" placeholder="Pilih atau ketik kategori..." class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <datalist id="categories">
                            <option value="Makanan Kucing">
                            <option value="Makanan Anjing">
                            <option value="Pasir & Perlengkapan">
                            <option value="Obat & Vitamin">
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Minimal Order (Pcs)</label>
                        <input type="number" name="min_order" value="1" min="1" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- 2. HARGA & LOGISTIK UTAMA -->
            <div>
                <h3 class="text-base font-bold text-gray-800 mb-4 border-b pb-2">Harga Utama & Berat</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Harga Utama / Default (Rp) *</label>
                        <input type="number" name="price" id="mainPriceInput" required placeholder="25000" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none font-bold text-gray-800">
                    </div>

                    <!-- KOLOM STOK UTAMA (SEMBUNYI JIKA ADA VARIASI) -->
                    <div id="mainStockCol">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Stok Produk Utama *</label>
                        <input type="number" name="stock" id="mainStockInput" required placeholder="50" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none font-bold text-emerald-600">
                        <span class="text-[10px] text-gray-400 block mt-1">*Diisi untuk produk tunggal (tanpa variasi)</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Berat Produk (Gram) *</label>
                        <input type="number" name="weight_gram" value="800" required placeholder="800" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- 3. FOTO UTAMA PRODUK -->
            <div>
                <h3 class="text-base font-bold text-gray-800 mb-4 border-b pb-2">Foto Utama Produk (Cover)</h3>
                <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-500 border border-gray-300 rounded-lg p-2">
            </div>

            <!-- 4. MENU VARIASI PRODUK & FITUR BULK FILL -->
            <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-4">
                <div class="flex justify-between items-center border-b pb-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Variasi Produk (Opsional)</h3>
                        <p class="text-xs text-gray-500">Contoh: Rasa Tuna, Rasa Salmon, Beda Ukuran, Dll.</p>
                    </div>
                    <button type="button" id="addVariantBtn" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 shadow-sm transition-all">
                        <i class="fa-solid fa-plus"></i> Tambah Variasi
                    </button>
                </div>

                <!-- PANEL BULK APPLY (ISI SERENTAK MENGHEMAT WAKTU) -->
                <div id="bulkApplyPanel" class="hidden bg-amber-50 p-3 rounded-lg border border-amber-200">
                    <span class="text-xs font-bold text-amber-900 block mb-1">⚡ Terapkan Harga & Stok Serentak ke Semua Variasi:</span>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="number" id="bulkPrice" placeholder="Harga Samakan (Rp)" class="text-xs border border-amber-300 rounded px-2 py-1.5 bg-white w-36 font-semibold">
                        <input type="number" id="bulkStock" placeholder="Stok Samakan (Pcs)" class="text-xs border border-amber-300 rounded px-2 py-1.5 bg-white w-32 font-semibold">
                        <button type="button" onclick="applyBulkToVariants()" class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-3 py-1.5 rounded text-xs transition-all shadow-sm">
                            Terapkan ke Semua Baris
                        </button>
                    </div>
                    <p class="text-[10px] text-amber-700 mt-1">*Isi sekali di atas lalu klik terapkan untuk mengisi belasan variasi secara otomatis.</p>
                </div>

                <div id="variantContainer" class="space-y-3">
                    <!-- Dynamic Variant Rows Will Be Inserted Here -->
                </div>
            </div>

            <!-- BUTTON SUBMIT -->
            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-200">Batal</a>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-bold shadow-sm transition-all">
                    Simpan Produk
                </button>
            </div>
        </div>
    </form>
</div>

<!-- SCRIPT DINAMIS VARIASI & AUTO SKU -->
<script src="{{ asset('js/image-compressor.js') }}"></script>
<script>
    // Auto Generate SKU
    document.getElementById('productName').addEventListener('input', function() {
        const val = this.value.trim();
        if(val.length >= 3) {
            const clean = val.replace(/[^a-zA-Z0-9]/g, '').substring(0, 4).toUpperCase();
            const randomNum = Math.floor(100 + Math.random() * 900);
            document.getElementById('productSku').value = 'ANB-' + clean + '-' + randomNum;
        } else {
            document.getElementById('productSku').value = '';
        }
    });

    let variantIndex = 0;

    // Fungsi Update Tampilan Stok Utama & Panel Bulk Apply
    function updateVariantUIState() {
        const container = document.getElementById('variantContainer');
        const rows = container.querySelectorAll('.variant-row');
        const mainStockCol = document.getElementById('mainStockCol');
        const mainStockInput = document.getElementById('mainStockInput');
        const bulkApplyPanel = document.getElementById('bulkApplyPanel');

        if (rows.length > 0) {
            // Sembunyikan stok utama & hapus sifat required-nya
            mainStockCol.style.display = 'none';
            mainStockInput.removeAttribute('required');
            mainStockInput.value = '';
            
            // Tampilkan panel isi serentak
            bulkApplyPanel.classList.remove('hidden');
        } else {
            // Tampilkan kembali stok utama
            mainStockCol.style.display = 'block';
            mainStockInput.setAttribute('required', 'required');
            
            // Sembunyikan panel isi serentak
            bulkApplyPanel.classList.add('hidden');
        }
    }

    // Dynamic Variant Row Logic
    document.getElementById('addVariantBtn').addEventListener('click', function() {
        const container = document.getElementById('variantContainer');
        const row = document.createElement('div');
        row.className = 'grid grid-cols-1 md:grid-cols-12 gap-3 p-3 bg-white rounded-lg border border-gray-200 items-center variant-row';
        row.id = `variant-row-${variantIndex}`;

        row.innerHTML = `
            <div class="md:col-span-3">
                <label class="block text-[10px] font-semibold text-gray-600 mb-1">Nama Variasi *</label>
                <input type="text" name="variants[${variantIndex}][name]" required placeholder="Misal: Tuna / Salmon" class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-amber-500 focus:outline-none">
            </div>
            <div class="md:col-span-3">
                <label class="block text-[10px] font-semibold text-gray-600 mb-1">Harga Variasi (Opsional)</label>
                <input type="number" name="variants[${variantIndex}][price]" placeholder="Kosongkan jika sama" class="variant-price-field w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-amber-500 focus:outline-none font-semibold">
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-semibold text-gray-600 mb-1">Stok *</label>
                <input type="number" name="variants[${variantIndex}][stock]" value="10" required class="variant-stock-field w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-amber-500 focus:outline-none font-bold text-emerald-600">
            </div>
            <div class="md:col-span-3">
                <label class="block text-[10px] font-semibold text-gray-600 mb-1">Foto Variasi</label>
                <input type="file" name="variants[${variantIndex}][image]" accept="image/*" class="w-full text-[10px] text-gray-500 border border-gray-300 rounded p-1">
            </div>
            <div class="md:col-span-1 text-center">
                <button type="button" onclick="removeVariant(${variantIndex})" class="text-red-500 hover:text-red-700 text-sm font-bold pt-3">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        `;

        container.appendChild(row);
        variantIndex++;
        updateVariantUIState();
    });

    function removeVariant(index) {
        const row = document.getElementById(`variant-row-${index}`);
        if(row) row.remove();
        updateVariantUIState();
    }

    // Fungsi Isi Serentak (Bulk Apply)
    function applyBulkToVariants() {
        const bulkPrice = document.getElementById('bulkPrice').value;
        const bulkStock = document.getElementById('bulkStock').value;

        const priceInputs = document.querySelectorAll('.variant-price-field');
        const stockInputs = document.querySelectorAll('.variant-stock-field');

        if (priceInputs.length === 0) return;

        if (bulkPrice !== '') {
            priceInputs.forEach(input => input.value = bulkPrice);
        }

        if (bulkStock !== '') {
            stockInputs.forEach(input => input.value = bulkStock);
        }
    }
</script>
@endsection