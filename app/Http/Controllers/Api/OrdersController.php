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
                // return response()->json(['message' => $product->stock < $quantityArray[$key]], 200);
                //Ürün stok kontrolü
                if ($product->stock < $quantityArray[$key]) {
                    return response()->json(['error' => 'Ürün stoku yetersiz.'], 400);
                }
            }
        } else {

            //Tek ürün siparisi
            $product = Product::findOrFail($request->productId);
            //Ürün stok kontrolü
            if ($product->stock < $request->quantity) {
                return response()->json(['error' => 'Ürün stoku yetersiz.'], 400);
            }
        }


        $order = new Order();
        $order->customerId = $request->user()->id;
        $order->productId = $request->productId;
        $order->quantity = $request->quantity;
        $order->save();

        return response()->json(['message' => 'Siparis Eklendi'], 200);
    }

    public function index()
    {
        $orders = Order::all();
        $orderResponses = [];
        // return response()->json(['message' => $order], 200);

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
        $orderResponses = [];

        foreach ($orders as $order) {
            $orderItems = [];

            foreach ($order->items as $item) {
                $orderItems[] = [
                    'productId' => $item->product->id,
                    'quantity' => $item->quantity,
                    'unitPrice' => $item->unit_price,
                    'total' => $item->total,
                ];
            }

            $orderResponse = [
                'id' => $order->id,
                'customerId' => $order->customer_id,
                'items' => $orderItems,
                'total' => $order->items->sum('total'),
            ];

            $orderResponses[] = $orderResponse;
        }

        return response()->json($orderResponses);
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
        $control = Order::where('id',$id)->count();
        if($control == 0) {return response()->json(['success' => false, 'message' => 'No Order.']);}
        Order::where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Deleted']);
    }
}
