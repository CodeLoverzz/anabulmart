<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    protected $apiKey;
    protected $baseUrl;
    protected $originCity;

    // Kurir yang ditawarkan ke pembeli. Bisa ditambah/kurangi sesuai kurir yang
    // benar-benar dilayani toko Anda.
    protected $availableCouriers = 'jne:jnt:sicepat:pos:tiki:anteraja';

    public function __construct()
    {
        $this->apiKey = env('RAJAONGKIR_API_KEY');
        $this->baseUrl = env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1');
        $this->originCity = env('RAJAONGKIR_ORIGIN_CITY', '278'); // Medan
    }

    protected function get(string $path, array $query = [])
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders(['key' => $this->apiKey])
                ->get("{$this->baseUrl}{$path}", $query);

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            Log::warning('RajaOngkir API gagal', ['path' => $path, 'status' => $response->status(), 'body' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('RajaOngkir API error', ['path' => $path, 'message' => $e->getMessage()]);
        }

        return null; // null = gagal total (beda dengan [] = berhasil tapi kosong)
    }

    /**
     * PENCARIAN LANGSUNG (kotak pencarian di checkout)
     * Bisa pakai nama kota, kecamatan, kelurahan, ATAU kode pos.
     */
    public function searchDestination(string $keyword)
    {
        if (strlen(trim($keyword)) < 3) {
            return [];
        }

        $result = $this->get('/destination/domestic-destination', [
            'search' => $keyword,
            'limit'  => 15,
            'offset' => 0,
        ]);

        return $result ?? [];
    }

    /**
     * DROPDOWN CADANGAN — Tahap 1: semua provinsi
     */
    public function getProvinces()
    {
        $result = $this->get('/destination/province');

        if ($result !== null) {
            return $result;
        }

        // Fallback minimal kalau API sedang down, supaya form tidak kosong total
        return [
            ['id' => '4', 'name' => 'Sumatera Utara'],
        ];
    }

    /**
     * DROPDOWN CADANGAN — Tahap 2: kota/kabupaten dalam satu provinsi
     */
    public function getCitiesByProvince(string $provinceId)
    {
        return $this->get("/destination/city/{$provinceId}") ?? [];
    }

    /**
     * DROPDOWN CADANGAN — Tahap 3: kecamatan dalam satu kota/kabupaten
     */
    public function getDistrictsByCity(string $cityId)
    {
        return $this->get("/destination/district/{$cityId}") ?? [];
    }

    /**
     * DROPDOWN CADANGAN — Tahap 4: kelurahan/desa dalam satu kecamatan
     */
    public function getSubdistrictsByDistrict(string $districtId)
    {
        return $this->get("/destination/sub-district/{$districtId}") ?? [];
    }

    /**
     * Menghitung ongkos kirim dari BEBERAPA kurir sekaligus.
     * $destinationId harus ID hasil dari searchDestination() atau dari
     * pemilihan dropdown kelurahan/kecamatan (Tahap 3/4 di atas).
     */
    public function calculateCost($destinationId, $weightInGrams = 1000, $courier = null)
    {
        $courier = $courier ?: $this->availableCouriers;
        $weightInGrams = max(1, (int) $weightInGrams); // API menolak berat 0

        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->withHeaders(['key' => $this->apiKey])
                ->asForm()
                ->post("{$this->baseUrl}/calculate/domestic-cost", [
                    'origin'      => $this->originCity,
                    'destination' => $destinationId,
                    'weight'      => $weightInGrams,
                    'courier'     => $courier,
                    'price'       => 'lowest',
                ]);

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            Log::warning('RajaOngkir hitung ongkir gagal', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('RajaOngkir hitung ongkir error', ['message' => $e->getMessage()]);
        }

        // Fallback ongkir manual jika API timeout/error, supaya checkout tidak buntu total
        return [
            [
                'name'    => 'Kurir Toko',
                'code'    => 'toko',
                'service' => 'REG',
                'cost'    => 15000,
                'etd'     => '1-2 hari',
            ],
        ];
    }

    /**
     * Meratakan hasil calculateCost() ke format seragam:
     * [ ['name'=>.., 'service'=>.., 'cost'=>angka, 'etd'=>..], ... ]
     *
     * Berjaga-jaga karena Komerce kadang membalas format lama (cost bersarang
     * per-service di dalam array 'costs') maupun format baru (cost angka langsung).
     */
    public function normalizeCostResult(array $rawResult): array
    {
        $flat = [];

        foreach ($rawResult as $entry) {
            // Format lama ala RajaOngkir V1: {code, name, costs: [{service, description, cost:[{value, etd}]}]}
            if (isset($entry['costs']) && is_array($entry['costs'])) {
                foreach ($entry['costs'] as $svc) {
                    $costValue = $svc['cost'][0]['value'] ?? ($svc['cost'] ?? 0);
                    $flat[] = [
                        'name'    => $entry['name'] ?? ($entry['code'] ?? 'Kurir'),
                        'service' => $svc['service'] ?? '-',
                        'cost'    => (int) $costValue,
                        'etd'     => $svc['cost'][0]['etd'] ?? ($svc['etd'] ?? '-'),
                    ];
                }
                continue;
            }

            // Format baru (flat, satu entri = satu layanan)
            $flat[] = [
                'name'    => $entry['name'] ?? ($entry['code'] ?? 'Kurir'),
                'service' => $entry['service'] ?? '-',
                'cost'    => (int) ($entry['cost'] ?? 0),
                'etd'     => $entry['etd'] ?? '-',
            ];
        }

        return $flat;
    }
}