<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RajaOngkirService
{
    protected $apiKey;
    protected $baseUrl;
    protected $originCity;

    public function __construct()
    {
        $this->apiKey = env('RAJAONGKIR_API_KEY');
        $this->baseUrl = env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1');
        $this->originCity = env('RAJAONGKIR_ORIGIN_CITY', '278'); // Medan
    }

    /**
     * Mengambil daftar lokasi/kota menggunakan Direct Search Endpoint V2 Komerce
     */
    public function getCities()
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders([
                    'key' => $this->apiKey
                ])->get("{$this->baseUrl}/destination/domestic-destination", [
                    'search' => 'Medan', // Pencarian lokasi default
                    'limit'  => 20,
                    'offset' => 0
                ]);

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }
        } catch (\Exception $e) {
            // Fallback jika internet terputus
        }

        // Data cadangan lokal agar aplikasi tidak crash saat demo jika internet offline
        return [
            ['id' => '278', 'label' => 'Kota Medan, Sumatera Utara', 'city_name' => 'Medan'],
            ['id' => '279', 'label' => 'Kab. Deli Serdang, Sumatera Utara', 'city_name' => 'Deli Serdang'],
            ['id' => '21',  'label' => 'Kab. Asahan, Sumatera Utara', 'city_name' => 'Asahan'],
        ];
    }

    /**
     * Menghitung ongkos kirim menggunakan Endpoint V2
     */
    public function calculateCost($destinationCityId, $weightInGrams = 1000, $courier = 'jne')
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders([
                    'key' => $this->apiKey
                ])->post("{$this->baseUrl}/calculate/domestic-cost", [
                    'origin'      => $this->originCity,
                    'destination' => $destinationCityId,
                    'weight'      => $weightInGrams,
                    'courier'     => $courier
                ]);

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }
        } catch (\Exception $e) {
            // Fallback ongkir manual jika API timeout/error
        }

        return [
            [
                'service' => 'REG',
                'description' => 'Layanan Reguler',
                'cost' => [['value' => 12000, 'etd' => '1-2', 'note' => '']]
            ]
        ];
    }
}