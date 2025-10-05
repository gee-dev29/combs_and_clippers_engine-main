<?php

namespace App\Http\Controllers\App;

use App\Models\Cart;
use App\Models\User;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Validator;


class CartController extends Controller
{
  public function addToCart(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'productID' => 'required|integer',
      'quantity' => 'required|integer',
    ]);
    if ($validator->fails()) {
      return $this->validationError($validator);
    }


    $customerID = $this->getAuthID($request);
    $customer = User::find($customerID);
    if (is_null($customer)) {
      return $this->errorResponse('Customer not found', 404);
    }

    $product = Product::find($request['productID']);
    if (is_null($product)) {
      return $this->errorResponse('Product not found', 404);
    }

    if ($request['quantity'] > $product->quantity) {
      return $this->errorResponse('Added quantity is more than available quantity', 400);
    }


    $cart = Cart::firstOrCreate(
      ['buyer_id' =>  $customerID, 'status' => CART::UNFULFILLED, 'merchant_id' => $product->merchant_id],
      [
        'buyer_id' => $customerID,
        'merchant_id' => $product->merchant_id,
        'max_delivery_period'  => $product->deliveryperiod,
        'min_delivery_period'  => $product->deliveryperiod,
        'totalprice'  => 0,
        'shipping'   => 0,
        'total_sum'  => 0,
        'items_count' => 0,
        'currency' => $product->currency,
      ]
    );

    $itemCost = floatval($product->price) * $request['quantity'];

    $cartItem = CartItem::firstOrCreate(
      ['cart_id' =>  $cart->id, 'productID' => $request['productID']],
      [
        'cart_id' => $cart->id,
        'productID' => $product->id,
        'productname' => $product->productname,
        'image_url' => $product->image_url,
        'description' => $product->description,
        'quantity' => $request['quantity'],
        'deliveryperiod' => $product->deliveryperiod,
        'currency' => $product->currency,
        'price' => $product->price,
        'total_cost' => $itemCost,
      ]
    );

    $totalprice = $cart->totalprice + $itemCost;
    $shipping = 0;
    $max_deliveryperiod = ($cart->max_delivery_period < $product->deliveryperiod) ? $product->deliveryperiod : $cart->max_delivery_period;
    $min_deliveryperiod = ($cart->min_delivery_period > $product->deliveryperiod) ? $product->deliveryperiod : $cart->min_delivery_period;

    if ($cartItem->wasRecentlyCreated === true) {
      $cart->update([
        'totalprice' => $totalprice,
        'items_count' => $cart->items_count + 1,
        'max_delivery_period'  => $max_deliveryperiod,
        'min_delivery_period'  => $min_deliveryperiod,
        'shipping'   => $shipping,
        'total_sum'  => $totalprice,
      ]);
    } else {
      $cartItem->update([
        'quantity' => $cartItem->quantity +  $request['quantity'],
        'total_cost' => $cartItem->total_cost + $itemCost
      ]);

      $cart->update([
        'totalprice' => $totalprice,
        'total_sum'  => $totalprice,
      ]);
    }

    $cart = new CartResource($cart);
    return response()->json(compact('cart'), 201);
  }

  public function removeFromCart(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'cart_id' => 'required|integer',
      'productID' => 'required|integer',
      'quantity' => 'integer|nullable',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    $customerID = $this->getAuthID($request);
    $customer = User::find($customerID);
    if (is_null($customer)) {
      return $this->errorResponse('Customer not found', 404);
    }

    $product = Product::find($request['productID']);
    if (is_null($product)) {
      return $this->errorResponse('Product not found', 404);
    }

    $cart = Cart::where([['buyer_id', $customerID], ['id', $request->input('cart_id')], ['status', CART::UNFULFILLED]])->orderBy('id', 'DESC')->first();
    if (is_null($cart)) {
      return $this->errorResponse('Cart not found', 404);
    }

    $cartItem = CartItem::where([['cart_id', $cart->id], ['productID', $request['productID']]])->first();
    if (is_null($cartItem)) {
      return $this->errorResponse('Product not found in cart', 404);
    }
    //remove product and deduct from cost from cart total cost
    if (is_null($request['quantity'])) {
      $itemCost = $cartItem->total_cost;
      $totalprice = $cart->totalprice - $itemCost;
      $shipping = 0;
      if ($totalprice < 1) {
        $totalprice = 0;
      }
      // if ($cart->max_delivery_period < $product->deliveryperiod ) {
      //   $max_deliveryperiod = $product->deliveryperiod;
      // }else{
      //   $max_deliveryperiod = $cart->max_delivery_period;
      // }

      // if ($cart->min_delivery_period > $product->deliveryperiod ) {
      //   $min_deliveryperiod = $product->deliveryperiod;
      // }else{
      //   $min_deliveryperiod = $cart->min_delivery_period;
      // }

      $cart->update([
        'totalprice' => $totalprice,
        'items_count' => $cart->items_count - 1,
        //'max_delivery_period'  => $max_deliveryperiod, 
        //'min_delivery_period'  => $min_deliveryperiod,
        'shipping'   => $shipping,
        'total_sum'  => $totalprice + $shipping,
      ]);

      $cartItem->delete();
    } else {
      $itemCost = $cartItem->total_cost;
      $rem_quantity = $cartItem->quantity - $request['quantity'];

      if ($rem_quantity < 1) {
        $totalprice = $cart->totalprice - $itemCost;
        $shipping = 0;
        $cart->update([
          'totalprice' => $cart->totalprice - $itemCost,
          'items_count' => $cart->items_count - 1,
          'shipping'   => $shipping,
          'total_sum'  => $totalprice + $shipping,
        ]);
        $cartItem->delete();
      } else {
        $cartItem->update([
          'quantity' => $rem_quantity,
          'total_cost' => $rem_quantity * $product->price
        ]);

        $totalprice = ($cart->totalprice - $itemCost) + $cartItem->total_cost;
        $shipping = 0;
        $cart->update([
          'totalprice' => $totalprice,
          'shipping'   => $shipping,
          'total_sum'  => $totalprice + $shipping,
        ]);
      }
    }
    if (!$cartItem->count()) {
      $cart->delete();
      $cart = $cart->fresh();
    } else {
      $cart = new CartResource($cart);
    }
    return response()->json(compact('cart'), 201);
  }


  public function getBuyerCart(Request $request)
  {
    $customerID = $this->getAuthID($request);
    $customer = User::find($customerID);
    if (is_null($customer)) {
      return $this->errorResponse('Customer not found', 404);
    }
    $carts = Cart::where([['buyer_id', $customerID], ['status', CART::UNFULFILLED]])->orderBy('id', 'DESC')->get();
    if (!is_null($carts)) {
      $carts = CartResource::collection($carts);
      return response()->json(compact('carts'), 200);
    }
    $carts = [];
    return response()->json(compact('carts'), 200);
  }

  public function clearCart(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'cart_id' => 'required|integer'
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    $customerID = $this->getAuthID($request);
    $customer = User::find($customerID);
    if (is_null($customer)) {
      return $this->errorResponse('Customer not found', 404);
    }

    $cart = Cart::where([['buyer_id', $customerID], ['id', $request->input('cart_id')], ['status', CART::UNFULFILLED]])->orderBy('id', 'DESC')->first();
    if (is_null($cart)) {
      return $this->errorResponse('Cart not found', 404);
    }

    $cart->cartItems()->delete();
    $cart->delete();
    $cart = $cart->fresh();
    return response()->json(compact('cart'), 200);
  }
}
