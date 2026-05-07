<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    /**
     * Hitung harga dinamis berdasarkan produk, kuantitas, dan opsi terpilih.
     *
     * Route  : POST /api/pricing/calculate
     * Request: { product_id, quantity, selected_options: [id, ...] }
     * Response: { unit_price, subtotal, quantity, formatted_unit, formatted_total, options_summary }
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id'           => ['required', 'integer', 'exists:products,id'],
            'quantity'             => ['required', 'integer', 'min:1'],
            'selected_options'     => ['nullable', 'array'],
            'selected_options.*'   => ['integer', 'exists:product_options,id'],
        ]);

        $product = Product::active()->findOrFail($validated['product_id']);

        // Hitung unit price mulai dari base_price
        $unitPrice    = (float) $product->base_price;
        $optionLabels = [];

        if (! empty($validated['selected_options'])) {
            // Ambil hanya opsi yang memang milik produk ini
            $selectedOptions = ProductOption::whereIn('id', $validated['selected_options'])
                ->where('product_id', $product->id)
                ->sorted()
                ->get();

            foreach ($selectedOptions as $option) {
                $modifier = (float) $option->price_modifier;

                $unitPrice += match ($option->modifier_type) {
                    'percentage' => $product->base_price * ($modifier / 100),
                    default      => $modifier,
                };

                if (abs($modifier) > 0) {
                    $optionLabels[] = $option->option_name . ' (' . $option->formatted_price_modifier . ')';
                } else {
                    $optionLabels[] = $option->option_name;
                }
            }
        }

        // Paksa minimum order quantity
        $quantity = (int) max($validated['quantity'], $product->min_qty);
        $subtotal = $unitPrice * $quantity;

        return response()->json([
            'unit_price'      => round($unitPrice, 2),
            'subtotal'        => round($subtotal, 2),
            'quantity'        => $quantity,
            'min_qty'         => $product->min_qty,
            'unit'            => $product->unit,
            'formatted_unit'  => 'Rp ' . number_format($unitPrice, 0, ',', '.'),
            'formatted_total' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
            'options_summary' => implode(' · ', $optionLabels) ?: '-',
        ]);
    }
}
