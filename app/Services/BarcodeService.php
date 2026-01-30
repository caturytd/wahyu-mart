<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeService
{
    protected BarcodeGeneratorPNG $generator;

    public function __construct()
    {
        $this->generator = new BarcodeGeneratorPNG();
    }

    /**
     * Generate barcode simple
     */
    public function generateBarcode(string $noTransaksi, string $type = 'barang_keluar'): ?string
    {
        try {
            $barcode = $this->generator->getBarcode(
                $noTransaksi,
                BarcodeGeneratorPNG::TYPE_CODE_128,
                3,
                80
            );

            return $this->storeBarcode($barcode, $noTransaksi, $type);
        } catch (\Throwable $e) {
            Log::error('Generate barcode gagal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate barcode dengan label (STABIL)
     */
    public function generateBarcodeWithLabel(
        string $noTransaksi,
        array $additionalInfo = [],
        string $type = 'barang_keluar'
    ): ?string {
        try {
            // Barcode dasar
            $barcodeBinary = $this->generator->getBarcode(
                $noTransaksi,
                BarcodeGeneratorPNG::TYPE_CODE_128,
                3,
                60
            );

            $barcodeImg = imagecreatefrompng(
                'data://image/png;base64,' . base64_encode($barcodeBinary)
            );

            if (!$barcodeImg) {
                throw new \Exception('Gagal membuat image barcode (GD error)');
            }

            $bw = imagesx($barcodeImg);
            $bh = imagesy($barcodeImg);

            $font = 3;
            $lineHeight = 16;
            $totalHeight = $bh + 25 + (count($additionalInfo) * $lineHeight);

            $img = imagecreatetruecolor($bw, $totalHeight);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            imagefill($img, 0, 0, $white);

            imagecopy($img, $barcodeImg, 0, 0, 0, 0, $bw, $bh);

            // Nomor transaksi
            $txWidth = imagefontwidth(5) * strlen($noTransaksi);
            imagestring(
                $img,
                5,
                ($bw - $txWidth) / 2,
                $bh + 5,
                $noTransaksi,
                $black
            );

            // Additional info
            $y = $bh + 20;
            foreach ($additionalInfo as $info) {
                $text = "{$info['label']}: {$info['value']}";
                $tw = imagefontwidth($font) * strlen($text);
                imagestring($img, $font, ($bw - $tw) / 2, $y, $text, $black);
                $y += $lineHeight;
            }

            ob_start();
            imagepng($img);
            $imageData = ob_get_clean();

            imagedestroy($img);
            imagedestroy($barcodeImg);

            return $this->storeBarcode($imageData, $noTransaksi . '_label', $type);

        } catch (\Throwable $e) {
            Log::error('Generate barcode label gagal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Simpan barcode ke storage
     */
    protected function storeBarcode(string $imageData, string $filename, string $type): string
    {
        $directory = "barcodes/{$type}";
        $path = "{$directory}/{$filename}.png";

        Storage::disk('public')->makeDirectory($directory);
        Storage::disk('public')->put($path, $imageData);

        return $path;
    }

    /**
     * Delete barcode
     */
    public function deleteBarcode(?string $path): bool
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return true;
        }
        return false;
    }

    /**
     * Get public URL
     */
    public function getBarcodeUrl(?string $path): ?string
    {
        return $path && Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path)
            : null;
    }
}
