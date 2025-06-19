<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function scanKode()
    {
        return view('scankode');
    }

    public function processScan(Request $request)
    {
        // Validasi permintaan
        $request->validate([
            'code' => 'required|string',
        ]);

        // Ambil barcode
        $scanData = $request->input('code');

        // Cari produk
        $product = Product::where('sku', $scanData)->first();

        if ($product) {
            return response()->json([
                'success' => true,
                'code' => $scanData,
                'product' => [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'description' => $product->description,
                    'image' => $product->image // pastikan berupa URL
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'code' => $scanData,
                'message' => 'Produk tidak ditemukan.'
            ]);
        }
    }

    public function processScanProduk(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = $request->input('code');
        $product = Product::where('sku', $code)->first();

        if ($product) {
            return response()->json([
                'success' => true,
                'code' => $code,
                'product' => [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'description' => $product->description,
                    'image' => $product->image,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Produk tidak ditemukan.',
            'code' => $code
        ]);
    }
}
