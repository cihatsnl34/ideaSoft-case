<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrdersController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $productUnitPrice = '';
        $total = 0;
        // Payload validasyonu
        $request->validate([
            'productId' => 'required',
            'quantity' => 'required'
        ]);

        if (strstr($request->productId, '-')) {

            //Birden fazla ürün siparisi
            $productIdArray = explode("-", $request->productId);
            $quantityArray = explode("-", $request->quantity);
            foreach ($productIdArray as $key => $value) {
                $product = Product::findOrFail($value);

                $productUnitPrice .= $product->price . '-';
                $total += $quantityArray[$key] * $product->price;
                // return response()->json(['message' => $product->stock < $quantityArray[$key]], 200);
                //Ürün stok kontrolü
                if ($product->stock < $quantityArray[$key]) {
                    return response()->json(['error' => 'Ürün stoku yetersiz.'], 400);
                }
                $product->stock -= $quantityArray[$key];
                $product->save();
            }
            $productUnitPrice = substr($productUnitPrice, 0, -1);
        } else {
            //Tek ürün siparisi
            $product = Product::findOrFail($request->productId);
            $productUnitPrice = $product->price;
            $total = $request->quantity * $product->price . '-';

            //Ürün stok kontrolü
            if ($product->stock < $request->quantity) {
                return response()->json(['error' => 'Ürün stoku yetersiz.'], 400);
            }
            $product->stock -= $request->quantity;
            $product->save();
        }


        $order = new Order();
        $order->customerId = $request->user()->id;
        $order->productId = $request->productId;
        $order->quantity = $request->quantity;
        $order->unitPrice = $productUnitPrice;
        $order->total = $total;
        $order->save();


        return response()->json(['message' => 'Siparis Eklendi'], 200);
    }

    public function index()
    {
        $orders = Order::all();

        foreach ($orders as $orderKey => $orderValue) {
            $total = 0;
            $orderResponse[$orderKey]['id'] = $orderValue->id;
            $orderResponse[$orderKey]['customerId'] = $orderValue->customerId;
            if (strstr($orderValue->productId, '-')) {
                $productIdArray = explode("-", $orderValue->productId);
                $quantityArray = explode("-", $orderValue->quantity);
                foreach ($productIdArray as $key => $value) {
                    $product = Product::findOrFail($value);
                    $orderResponse[$orderKey]['items'][$key]['productId'] = $value;
                    $orderResponse[$orderKey]['items'][$key]['quantity'] = $quantityArray[$key];
                    $orderResponse[$orderKey]['items'][$key]['unitPrice'] = $product->price;
                    $orderResponse[$orderKey]['items'][$key]['total'] = $quantityArray[$key] * $product->price;
                    $total += $quantityArray[$key] * $product->price;
                }
            } else {
                $product = Product::findOrFail($orderValue->productId);
                $orderResponse[$orderKey]['items']['productId'] = $orderValue->productId;
                $orderResponse[$orderKey]['items']['quantity'] = $orderValue->quantity;
                $orderResponse[$orderKey]['items']['unitPrice'] = $product->price;
                $orderResponse[$orderKey]['items']['total'] = $orderValue->quantity * $product->price;
                $total = $orderValue->quantity * $product->price;
            }
            $orderResponse[$orderKey]['total'] = $total;
        }
        return response()->json(['message' => $orderResponse], 200);
    }

    public function customerReport()
    {
        $orders = Order::all();
        $groupedOrders = collect($orders)->groupBy('customerId');
        $customerTotals = [];
        foreach ($groupedOrders as $customerId => $orders) {
            $totalAmount = $orders->sum('total');
            $customerTotals[$customerId]['id'] = $customerId;
            $customerTotals[$customerId]['name'] = request()->user()->name;
            $customerTotals[$customerId]['since'] = request()->user()->created_at->format('Y-m-d');
            $customerTotals[$customerId]['revenue'] = $totalAmount;
        }
        return response()->json($customerTotals, 200);

        // $customerIdArray = [];
        // $orders = Order::all();
        // foreach ($orders as $orderKey => $orderValue) {
        //     $customerIdArray[] = $orderValue->customerId;

        // }
        // return response()->json($orderValue, 200);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $control = Order::where('id', $id)->count();
        if ($control == 0) {
            return response()->json(['success' => false, 'message' => 'No Order.']);
        }
        Order::where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Deleted']);
    }
}
