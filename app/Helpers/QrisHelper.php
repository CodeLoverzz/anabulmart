<?php

namespace App\Helpers;

class QrisHelper
{
    public static function generateDynamicQris($staticQrisPayload, $amount)
    {
        // 1. Potong Checksum lama (4 karakter terakhir)
        $base = substr($staticQrisPayload, 0, -4);
        
        // 2. Ubah indikator dari Statis (11) menjadi Dinamis (12)
        $base = str_replace('010211', '010212', $base);

        // 3. Format Tag 54 (Amount/Nominal Pembayaran)
        $amountStr = (string) intval($amount);
        $tag54 = '54' . sprintf('%02d', strlen($amountStr)) . $amountStr;

        // 4. Sisipkan Tag 54 sebelum Tag 58 (Country Code 'ID')
        $pos = strpos($base, '5802ID');
        if ($pos !== false) {
            $payload = substr($base, 0, $pos) . $tag54 . substr($base, $pos);
        } else {
            $payload = $base . $tag54;
        }

        // 5. Hitung Ulang CRC16 Checksum (Tag 6304)
        $crc = self::crc16($payload);
        return $payload . $crc;
    }

    private static function crc16($data)
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $x = (($crc >> 8) ^ ord($data[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
        }
        return strtoupper(sprintf('%04X', $crc));
    }
}