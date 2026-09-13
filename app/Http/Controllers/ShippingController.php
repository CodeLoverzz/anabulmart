<?php

namespace App\Http\Controllers;

use App\Services\RajaOngkirService;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    protected RajaOngkirService $rajaOngkir;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
    }

    // Kotak pencarian: ketik nama daerah / kode pos
    public function search(Request $request)
    {
        $keyword = trim($request->query('keyword', ''));
        $results = $this->rajaOngkir->searchDestination($keyword);

        return response()->json(['data' => $results]);
    }

    // Dropdown cadangan - Tahap 1
    public function provinces()
    {
        return response()->json(['data' => $this->rajaOngkir->getProvinces()]);
    }

    // Dropdown cadangan - Tahap 2
    public function cities(string $provinceId)
    {
        return response()->json(['data' => $this->rajaOngkir->getCitiesByProvince($provinceId)]);
    }

    // Dropdown cadangan - Tahap 3
    public function districts(string $cityId)
    {
        return response()->json(['data' => $this->rajaOngkir->getDistrictsByCity($cityId)]);
    }

    // Dropdown cadangan - Tahap 4
    public function subdistricts(string $districtId)
    {
        return response()->json(['data' => $this->rajaOngkir->getSubdistrictsByDistrict($districtId)]);
    }

    // Hitung ongkir dari beberapa kurir sekaligus
    public function cost(Request $request)
    {
        $request->validate([
            'destination_id' => 'required',
            'weight'         => 'nullable|integer|min:1',
        ]);

        $weight = $request->input('weight', 1000);
        $raw = $this->rajaOngkir->calculateCost($request->destination_id, $weight);
        $options = $this->rajaOngkir->normalizeCostResult($raw);

        return response()->json(['data' => $options]);
    }
}