<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function calculateDiscounts(Request $request)
    {

        $request->validate([
            'orderId' => 'required|integer'
        ]);
        $orderId = $request->input('orderId');

        $order = Order::findOrFail($orderId);

        $discounts = [];
        $subtotal = 0;
        $totalDiscount = 0;

        // Her bir ürün için indirim kurallarını uygulayın

        if (strstr($order->productId, '-')) {
            $productIdArray = explode("-", $order->productId);
            $quantityArray = explode("-", $order->quantity);
            $unitPriceArray = explode("-", $order->unitPrice);

            foreach ($productIdArray as $productIdArrayKey => $productIdArrayValue) {
                $productId = $productIdArray[$productIdArrayKey];
                $quantity = $quantityArray[$productIdArrayKey];
                $unitPrice = $unitPriceArray[$productIdArrayKey];

                $product = Product::findOrFail($productId);
                // Ürünün toplam tutarını hesaplayın
                $total = $unitPrice * $quantity;
                $subtotal += $total;
                // İndirim kuralı 1: Toplam 1000TL ve üzeri alışverişe %10 indirim
                if ($this->isEligibleFor10PercentDiscount($subtotal)) {


                    $discountAmount = $subtotal * 0.1;
                    $totalDiscount += $discountAmount;
                    $discounts['10_PERCENT_OVER_1000'] = [
                        'discountReason' => '10_PERCENT_OVER_1000',
                        'discountAmount' => number_format($discountAmount, 2),
                        'subtotal' => number_format($subtotal, 2),
                    ];
                }

                // İndirim kuralı 2: 2 ID'li kategoriye ait bir üründen 6 adet satın alındığında, bir tanesi ücretsiz


                if ($this->isEligibleForBuy5Get1Discount($product->category, $quantity)) {
                    $discountAmount = $unitPrice;
                    $totalDiscount += $discountAmount;
                    $discounts['BUY_5_GET_1'] = [
                        'discountReason' => 'BUY_5_GET_1',
                        'discountAmount' => number_format($discountAmount, 2),
                        'subtotal' => number_format($subtotal, 2),
                    ];
                }

                // İndirim kuralı 3: 1 ID'li kategoriden iki veya daha fazla ürün satın alındığında, en ucuz ürüne %20 indirim yapılır
                if ($this->isEligibleFor20PercentDiscount($product->category, $quantity)) {

                    $discountAmount = min($unitPriceArray) * 0.2;
                    $totalDiscount += $discountAmount;
                    $discounts['20_PERCENT_CHEAPEST'] = [
                        'discountReason' => '20_PERCENT_CHEAPEST',
                        'discountAmount' => $discountAmount,
                        'subtotal' => $subtotal,
                    ];
                }
                // Daha fazla indirim kuralı ekleyebilirsiniz...
            }

            // İndirimli toplam tutarı hesaplayın
            $discountedTotal = $subtotal - $totalDiscount;

            // Sonuçları response yapısıyla birleştirin
            $response = [
                'orderId' => $orderId,
                'discounts' => $discounts,
                'totalDiscount' => number_format($totalDiscount, 2),
                'discountedTotal' => number_format($discountedTotal, 2),
            ];

            return response()->json($response);
        }
    }

    private function isEligibleFor10PercentDiscount($subtotal)
    {
        return $subtotal >= 1000;
    }

    private function isEligibleForBuy5Get1Discount($productCategoryId, $quantity)
    {
        return $productCategoryId == 2 && $quantity == 6;
    }

    private function isEligibleFor20PercentDiscount($productCategoryId, $quantity)
    {
        return $productCategoryId == 1 && $quantity >= 2;
    }
}
