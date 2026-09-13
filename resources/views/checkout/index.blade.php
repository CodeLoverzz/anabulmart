@extends('layouts.app')

@section('title', 'Checkout Pesanan - AnabulMart Medan')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-black text-gray-800 mb-6">Checkout Pesanan</h1>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 text-xs font-bold rounded-xl flex items-center justify-between">
            <span><i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 text-xs font-bold rounded-xl">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- FORM PENGIRIMAN -->
        <div class="lg:col-span-2 space-y-6">
            <form action="{{ route('checkout.store') }}" method="POST" id="checkoutForm" class="space-y-6">
                @csrf
                <input type="hidden" name="checkout_type" value="{{ $checkoutType ?? 'cart' }}">

                <!-- Field tersembunyi hasil pemilihan alamat & ongkir, diisi lewat JS -->
                <input type="hidden" name="destination_id" id="destination_id" value="{{ old('destination_id') }}">
                <input type="hidden" name="destination_label" id="destination_label" value="{{ old('destination_label') }}">
                <input type="hidden" name="shipping_courier" id="shipping_courier" value="{{ old('shipping_courier') }}">
                <input type="hidden" name="shipping_service" id="shipping_service" value="{{ old('shipping_service') }}">
                <input type="hidden" name="shipping_cost" id="shipping_cost" value="{{ old('shipping_cost', 0) }}">

                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                    <h2 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-user text-amber-500"></i> Informasi Pembeli
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap *</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}" required placeholder="Masukkan nama penerima" class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor WhatsApp *</label>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required placeholder="Contoh: 081234567890" class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>
                </div>

                <!-- ================= PILIH WILAYAH TUJUAN ================= -->
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                    <h2 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-amber-500"></i> Wilayah Tujuan Pengiriman
                    </h2>

                    <!-- Kotak pencarian -->
                    <div class="relative">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Cari Kecamatan / Kelurahan / Kode Pos *</label>
                        <input type="text" id="destinationSearch" autocomplete="off" placeholder="Contoh: Medan Kota, atau 20211"
                               value="{{ old('destination_label') }}"
                               class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <div id="searchResults" class="hidden absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto"></div>
                        <p id="destinationConfirmed" class="hidden text-[11px] text-emerald-600 font-semibold mt-1">
                            <i class="fa-solid fa-circle-check"></i> <span></span>
                        </p>
                    </div>

                    <!-- Toggle ke dropdown manual -->
                    <button type="button" id="toggleManualBtn" class="text-[11px] text-amber-600 font-semibold underline">
                        Tidak ketemu? Pilih manual lewat Provinsi &gt; Kota &gt; Kecamatan &gt; Kelurahan
                    </button>

                    <!-- Dropdown bertingkat (cadangan, disembunyikan sampai tombol di atas diklik) -->
                    <div id="manualDropdowns" class="hidden grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Provinsi</label>
                            <select id="selectProvince" class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2.5">
                                <option value="">-- Pilih Provinsi --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Kota / Kabupaten</label>
                            <select id="selectCity" disabled class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2.5 disabled:bg-gray-100">
                                <option value="">-- Pilih Provinsi Dulu --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Kecamatan</label>
                            <select id="selectDistrict" disabled class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2.5 disabled:bg-gray-100">
                                <option value="">-- Pilih Kota Dulu --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Kelurahan / Desa</label>
                            <select id="selectSubdistrict" disabled class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2.5 disabled:bg-gray-100">
                                <option value="">-- Pilih Kecamatan Dulu --</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Detail Alamat (Nama Jalan, No. Rumah, Patokan) *</label>
                        <textarea name="shipping_address_detail" id="shipping_address_detail" rows="3" required
                                  placeholder="Contoh: Jl. Mawar No. 10, dekat Indomaret"
                                  class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500">{{ old('shipping_address_detail') }}</textarea>
                    </div>
                    <!-- shipping_address final (detail + label wilayah) dikirim lewat field tersembunyi ini -->
                    <input type="hidden" name="shipping_address" id="shipping_address_full" value="{{ old('shipping_address') }}">
                </div>

                <!-- ================= PILIH KURIR & ONGKIR ================= -->
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                    <h2 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-truck-fast text-amber-500"></i> Pilih Kurir & Layanan
                    </h2>

                    <div id="courierPlaceholder" class="text-xs text-gray-400 italic py-3 text-center">
                        Pilih wilayah tujuan terlebih dahulu untuk melihat pilihan kurir & ongkir.
                    </div>

                    <div id="courierLoading" class="hidden text-xs text-gray-500 py-3 text-center">
                        <i class="fa-solid fa-spinner fa-spin"></i> Menghitung ongkir...
                    </div>

                    <div id="courierOptions" class="hidden space-y-2"></div>
                </div>

                <button type="submit" id="submitBtn" disabled
                        class="w-full py-3.5 bg-amber-500 hover:bg-amber-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-lock"></i> Buat Pesanan & Bayar Sekarang
                </button>
                <p id="submitHint" class="text-[11px] text-gray-400 text-center -mt-3">Lengkapi wilayah tujuan & pilih kurir dulu untuk mengaktifkan tombol ini.</p>
            </form>
        </div>

        <!-- RINGKASAN HARGA -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm h-fit space-y-4">
            <h2 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-bag-shopping text-amber-500"></i> Ringkasan Item
            </h2>

            @php
                $items = $itemsToCheckout ?? session()->get('cart', []);
                $calculatedSubtotal = 0;
            @endphp

            <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                @forelse($items as $item)
                    @php
                        $itemPrice = $item['price'] ?? 0;
                        $itemQty = $item['quantity'] ?? 1;
                        $totalItemPrice = $itemPrice * $itemQty;
                        $calculatedSubtotal += $totalItemPrice;

                        $rawImg = $item['image'] ?? null;
                        $imgUrl = $rawImg ? asset('storage/' . ltrim(str_replace(['public/', 'storage/'], '', $rawImg), '/')) : 'https://placehold.co/100x100?text=No+Foto';
                    @endphp

                    <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="w-12 h-12 bg-white rounded-lg overflow-hidden border border-gray-200 flex-shrink-0">
                            <img src="{{ $imgUrl }}" onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=Gagal+Muat';" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-gray-800 truncate">{{ $item['product_name'] ?? 'Produk' }}</h4>
                            <p class="text-[10px] text-amber-600 font-semibold">
                                Variasi: {{ $item['variant_name'] ?? 'Default' }} (x{{ $itemQty }})
                            </p>
                        </div>
                        <div class="text-xs font-black text-emerald-600">
                            Rp {{ number_format($totalItemPrice, 0, ',', '.') }}
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic text-center py-4">Tidak ada item untuk di-checkout.</p>
                @endforelse
            </div>

            <div class="pt-3 border-t border-gray-100 space-y-2">
                <div class="flex justify-between text-xs text-gray-600 font-semibold">
                    <span>Subtotal Produk:</span>
                    <span class="text-gray-800 font-bold" id="summarySubtotal">Rp {{ number_format($subtotal ?? $calculatedSubtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-xs text-gray-600 font-semibold">
                    <span>Ongkos Kirim:</span>
                    <span class="text-emerald-600 font-bold" id="summaryShipping">Rp 0</span>
                </div>
                <div class="pt-2 border-t border-gray-100 flex justify-between text-sm font-black text-gray-800">
                    <span>Total Pembayaran:</span>
                    <span class="text-emerald-600" id="summaryTotal">Rp {{ number_format($subtotal ?? $calculatedSubtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const SUBTOTAL = {{ (int) ($subtotal ?? $calculatedSubtotal) }};
    const TOTAL_WEIGHT = {{ (int) ($totalWeight ?? 1000) }};

    const rupiah = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');

    // ---------- ELEMENTS ----------
    const searchInput      = document.getElementById('destinationSearch');
    const searchResultsBox = document.getElementById('searchResults');
    const confirmedLabel   = document.getElementById('destinationConfirmed');
    const toggleManualBtn  = document.getElementById('toggleManualBtn');
    const manualBox        = document.getElementById('manualDropdowns');

    const selectProvince   = document.getElementById('selectProvince');
    const selectCity       = document.getElementById('selectCity');
    const selectDistrict   = document.getElementById('selectDistrict');
    const selectSubdistrict= document.getElementById('selectSubdistrict');

    const destinationIdInput    = document.getElementById('destination_id');
    const destinationLabelInput = document.getElementById('destination_label');
    const shippingCourierInput  = document.getElementById('shipping_courier');
    const shippingServiceInput  = document.getElementById('shipping_service');
    const shippingCostInput     = document.getElementById('shipping_cost');
    const addressDetailInput    = document.getElementById('shipping_address_detail');
    const addressFullInput      = document.getElementById('shipping_address_full');

    const courierPlaceholder = document.getElementById('courierPlaceholder');
    const courierLoading     = document.getElementById('courierLoading');
    const courierOptionsBox  = document.getElementById('courierOptions');

    const submitBtn  = document.getElementById('submitBtn');
    const submitHint = document.getElementById('submitHint');

    const summaryShipping = document.getElementById('summaryShipping');
    const summaryTotal    = document.getElementById('summaryTotal');

    function buildFullAddress() {
        const detail = (addressDetailInput.value || '').trim();
        const label = destinationLabelInput.value || '';
        addressFullInput.value = label ? (detail + ', ' + label) : detail;
    }
    addressDetailInput.addEventListener('input', buildFullAddress);

    function checkFormReady() {
        const ready = destinationIdInput.value && shippingCourierInput.value && addressDetailInput.value.trim().length > 0;
        submitBtn.disabled = !ready;
        submitHint.classList.toggle('hidden', ready);
    }

    // ---------- PENCARIAN LANGSUNG ----------
    let searchTimer = null;
    searchInput.addEventListener('input', function () {
        const keyword = this.value.trim();
        clearTimeout(searchTimer);

        if (keyword.length < 3) {
            searchResultsBox.classList.add('hidden');
            return;
        }

        searchTimer = setTimeout(() => {
            fetch(`{{ route('shipping.search') }}?keyword=` + encodeURIComponent(keyword))
                .then(r => r.json())
                .then(res => renderSearchResults(res.data || []))
                .catch(() => renderSearchResults([]));
        }, 400);
    });

    function renderSearchResults(list) {
        if (!list.length) {
            searchResultsBox.innerHTML = '<div class="p-3 text-[11px] text-gray-400">Tidak ditemukan. Coba kata kunci lain atau pakai dropdown manual.</div>';
            searchResultsBox.classList.remove('hidden');
            return;
        }
        searchResultsBox.innerHTML = list.map(item => {
            const label = item.label || [item.subdistrict_name, item.district_name, item.city_name, item.province_name].filter(Boolean).join(', ');
            return `<button type="button" data-id="${item.id}" data-label="${label.replace(/"/g, '&quot;')}"
                        class="w-full text-left px-3 py-2 text-[11px] hover:bg-amber-50 border-b border-gray-50 last:border-0 destination-pick">
                        ${label}
                    </button>`;
        }).join('');
        searchResultsBox.classList.remove('hidden');

        searchResultsBox.querySelectorAll('.destination-pick').forEach(btn => {
            btn.addEventListener('click', function () {
                selectDestination(this.dataset.id, this.dataset.label);
                searchResultsBox.classList.add('hidden');
                searchInput.value = this.dataset.label;
            });
        });
    }

    // ---------- TOGGLE DROPDOWN MANUAL ----------
    toggleManualBtn.addEventListener('click', function () {
        manualBox.classList.toggle('hidden');
        if (!manualBox.classList.contains('hidden') && selectProvince.options.length <= 1) {
            fetch(`{{ route('shipping.provinces') }}`)
                .then(r => r.json())
                .then(res => {
                    (res.data || []).forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.id;
                        opt.textContent = extractName(p);
                        selectProvince.appendChild(opt);
                    });
                });
        }
    });

    selectProvince.addEventListener('change', function () {
        resetSelect(selectCity, '-- Pilih Kota/Kabupaten --');
        resetSelect(selectDistrict, '-- Pilih Kota Dulu --');
        resetSelect(selectSubdistrict, '-- Pilih Kecamatan Dulu --');
        selectCity.disabled = true; selectDistrict.disabled = true; selectSubdistrict.disabled = true;
        if (!this.value) return;

        fetch(`{{ url('shipping/cities') }}/` + this.value)
            .then(r => r.json())
            .then(res => {
                (res.data || []).forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = extractName(c);
                    selectCity.appendChild(opt);
                });
                selectCity.disabled = false;
            });
    });

    selectCity.addEventListener('change', function () {
        resetSelect(selectDistrict, '-- Pilih Kecamatan --');
        resetSelect(selectSubdistrict, '-- Pilih Kecamatan Dulu --');
        selectDistrict.disabled = true; selectSubdistrict.disabled = true;
        if (!this.value) return;

        fetch(`{{ url('shipping/districts') }}/` + this.value)
            .then(r => r.json())
            .then(res => {
                (res.data || []).forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = extractName(d);
                    selectDistrict.appendChild(opt);
                });
                selectDistrict.disabled = false;
            });
    });

    selectDistrict.addEventListener('change', function () {
        resetSelect(selectSubdistrict, '-- Pilih Kelurahan --');
        selectSubdistrict.disabled = true;
        if (!this.value) return;

        fetch(`{{ url('shipping/subdistricts') }}/` + this.value)
            .then(r => r.json())
            .then(res => {
                (res.data || []).forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = extractName(s);
                    selectSubdistrict.appendChild(opt);
                });
                selectSubdistrict.disabled = false;
            });
    });

    selectSubdistrict.addEventListener('change', function () {
        if (!this.value) return;
        const label = [
            this.options[this.selectedIndex].textContent,
            selectDistrict.options[selectDistrict.selectedIndex]?.textContent,
            selectCity.options[selectCity.selectedIndex]?.textContent,
            selectProvince.options[selectProvince.selectedIndex]?.textContent,
        ].filter(Boolean).join(', ');
        selectDestination(this.value, label);
    });

    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
    }

    // Beberapa endpoint Komerce memakai nama field yang sedikit berbeda
    // (name / province_name / city_name / dst). Coba semua kemungkinan.
    function extractName(obj) {
        return obj.name || obj.province_name || obj.city_name || obj.district_name || obj.subdistrict_name || obj.label || ('#' + obj.id);
    }

    // ---------- PILIH TUJUAN & HITUNG ONGKIR ----------
    function selectDestination(id, label) {
        destinationIdInput.value = id;
        destinationLabelInput.value = label;
        confirmedLabel.classList.remove('hidden');
        confirmedLabel.querySelector('span').textContent = label;
        buildFullAddress();
        fetchCourierOptions(id);
    }

    function fetchCourierOptions(destinationId) {
        courierPlaceholder.classList.add('hidden');
        courierOptionsBox.classList.add('hidden');
        courierLoading.classList.remove('hidden');

        // Reset pilihan kurir sebelumnya karena tujuan berubah
        shippingCourierInput.value = '';
        shippingServiceInput.value = '';
        shippingCostInput.value = 0;
        checkFormReady();

        fetch(`{{ route('shipping.cost') }}?destination_id=` + encodeURIComponent(destinationId) + `&weight=` + TOTAL_WEIGHT)
            .then(r => r.json())
            .then(res => renderCourierOptions(res.data || []))
            .catch(() => renderCourierOptions([]));
    }

    function renderCourierOptions(options) {
        courierLoading.classList.add('hidden');

        if (!options.length) {
            courierOptionsBox.innerHTML = '<p class="text-xs text-red-500 italic text-center py-2">Gagal mengambil data ongkir. Coba pilih ulang wilayah tujuan.</p>';
            courierOptionsBox.classList.remove('hidden');
            return;
        }

        courierOptionsBox.innerHTML = options.map((opt, idx) => `
            <label class="flex items-center justify-between p-3 border border-gray-200 rounded-xl cursor-pointer hover:border-amber-400 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                <span class="flex items-center gap-3">
                    <input type="radio" name="courierPick" value="${idx}" class="text-amber-500 focus:ring-amber-400">
                    <span>
                        <span class="block text-xs font-bold text-gray-800">${opt.name} - ${opt.service}</span>
                        <span class="block text-[10px] text-gray-500">Estimasi ${opt.etd} hari</span>
                    </span>
                </span>
                <span class="text-xs font-black text-emerald-600">${rupiah(opt.cost)}</span>
            </label>
        `).join('');
        courierOptionsBox.classList.remove('hidden');

        courierOptionsBox.querySelectorAll('input[name="courierPick"]').forEach(radio => {
            radio.addEventListener('change', function () {
                const opt = options[this.value];
                shippingCourierInput.value = opt.name;
                shippingServiceInput.value = opt.service;
                shippingCostInput.value = opt.cost;

                summaryShipping.textContent = rupiah(opt.cost);
                summaryTotal.textContent = rupiah(SUBTOTAL + Number(opt.cost));
                checkFormReady();
            });
        });
    }

    // Tutup dropdown pencarian saat klik di luar
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !searchResultsBox.contains(e.target)) {
            searchResultsBox.classList.add('hidden');
        }
    });
})();
</script>
@endsection