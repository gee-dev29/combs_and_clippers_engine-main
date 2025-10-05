<?php

namespace App\Http\Controllers\App;

use Exception;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use Ramsey\Uuid\Uuid;
use App\Models\Coupon;
use App\Models\Address;
use App\Models\Country;
use App\Models\Dispute;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\OrderItem;
use App\Repositories\Util;
use App\Models\DisputeFile;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\OrderLogistic;
use App\Models\PendingPayment;
use App\Repositories\PawaPayUtils;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\OrderResource;
use App\Http\Resources\AddressResource;
use App\Http\Resources\DisputeResource;
use Bmatovu\MtnMomo\Products\Collection;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\Rules\Phone;

class OrderController extends Controller
{
  public function updateOrderStatus(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'orderID' => 'required|integer',
      'issues' => 'nullable|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $user = $this->getAuthUser($request);
      if (!$user) {
        return $this->errorResponse('Merchant not found', 404);
      }

      $tranx = Order::find($request->input('orderID'));
      if (!is_null($tranx)) {
        if ($request->filled('issues')) {
          $tranx->update([
            'issues' => $request->input('issues')
          ]);
        }
        $order = new OrderResource($tranx);
        return response()->json(compact('order'), 201);
      }
      return $this->errorResponse('Order not found', 404);
      // if (!is_null($tranx)) {
      //   $statusArr = cc('transaction.statusArray');
      //   $statusArray = cc('transaction.status');
      //   $status_before = $statusArray[$tranx->status];
      //   $action = $request->action;
      //   if (isset($statusArr[$action])) {
      //     $status = $statusArr[$action];
      //     $tranx->update([
      //       "status" => $status,
      //       'tracking_code' => $request->filled('tracking_code') ? $request->input('tracking_code') : $tranx->tracking_code,
      //       'issues' => $request->filled('issues') ? $request->input('issues') : $tranx->issues,
      //     ]);
      //   } else {
      //     return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'Invalid action.', "ResponseMessage" => 'Invalid action.', "ResponseCode" => 401], 401);
      //   }

      //Send Notification to both Buyer and seller
      // $users = User::where('id', $tranx->buyer_id)->orWhere('customerID', $tranx->merchant_id)->get();
      // if (!is_null($users)) {
      //   $subject = "Status of Order - $tranx->orderRef status was changed to ". $request->input('action'); 
      //   $this->notifyUtils->orderNotificationToUsers($users, $tranx, $subject);
      // }
      //Send Notification to both Buyer and seller

      // if ($action == 'Delivered') {
      //   $tranx->update([
      //     "confirmation_pin" => rand(100000, 999999),
      //     "confirmation_pin_expires_at" => Carbon::now()->addDays(20)
      //   ]);

      //   $this->peppUtil->send_placed_order_status_change_email_with_otp($tranx, $user);
      // } else {
      //   $this->peppUtil->send_placed_order_status_change_email($tranx, $user);
      // }

      // $status_after = $statusArray[$tranx->status];

    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function getOrderStatuses(Request $request)
  {
    $orderStatuses =  cc('transaction.sellerControlledOrderStatus');
    return response()->json(compact('orderStatuses'), 201);
  }

  public function getBuyerOrders(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'type' => 'required|string',  // in ['All', 'Processing', 'Canceled', 'Shipped', 'Delivered']
      'search' => 'nullable|string'
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $buyer = User::find($this->getAuthID($request));

      if (!is_null($buyer)) {
        $buyer_id = $buyer->id;

        if (!is_null($request['search'])) {
          return $this->orderSearch($buyer, $request['type'], $request['search']);
        }

        if ($request['type'] == 'All') {
          $orders = Order::where('buyer_id', $buyer_id)->latest()->paginate($this->perPage);
        } else {
          $type = $request->type;
          $statusArr = cc('transaction.statusArray');
          if (isset($statusArr[$type])) {
            $status = $statusArr[$type];
            $orders = Order::where([['buyer_id', $buyer_id], ['status', $status]])->latest()->paginate($this->perPage);
          } else {
            return $this->errorResponse('Invalid order type', 400);
          }
        }

        $orders = $this->addMeta(OrderResource::collection($orders));
        return response()->json(compact('orders'), 201);
      }
      return $this->errorResponse('User not found', 404);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }


  public function myOrders(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'type' => 'required|string',  // in ['All', 'Processing', 'Canceled', 'Shipped', 'Delivered']
      'search' => 'nullable'
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    $merchantID = $this->getAuthID($request);
    try {
      $merchant = User::find($merchantID);

      if (!is_null($merchant) && $merchant->account_type == 'Merchant') {
        $merchant_id = $merchant->id;

        if (!is_null($request['search'])) {
          return $this->orderSearch($merchant, $request['type'], $request['search']);
        }

        $statusArr = cc('transaction.statusArray');
        if ($request['type'] == 'All') {
          $orders = Order::where('merchant_id', $merchant_id)->latest()->paginate($this->perPage);
        } else {
          $type = $request->type;
          if (isset($statusArr[$type])) {
            $status = $statusArr[$type];
            $orders = Order::where([['merchant_id', $merchant_id], ['status', $status]])->latest()->paginate($this->perPage);
          } else {
            return $this->errorResponse('Invalid order type', 400);
          }
        }

        $orders = $this->addMeta(OrderResource::collection($orders));

        return response()->json(compact('orders'), 201);
      }
      return $this->errorResponse('User not found or not a merchant', 404);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }


  public function getOrder($id)
  {
    $order = Order::find($id);
    return response()->json(compact('order'), 201);
  }


  protected function orderSearch($user, $type, $search)
  {
    $statusArr = cc('transaction.statusArray');
    if ($user->account_type == 'Merchant') {

      if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
        //search parameter is buyer email
        $email = $search;
        $customer_ids = User::where('email', $email)->get(['id'])->toArray();
        if (!is_null($customer_ids)) {
          if ($type == 'All') {
            $orders = Order::where('merchant_id', $user->id)->whereIn('buyer_id', $customer_ids)->latest()->paginate($this->perPage);
          } else {
            if (isset($statusArr[$type])) {
              $status = $statusArr[$type];
              $orders = Order::where([['merchant_id', $user->id], ['status', $status]])->whereIn('buyer_id', $customer_ids)->latest()->paginate($this->perPage);
            } else {
              $orders = Order::where('merchant_id', $user->id)->whereIn('buyer_id', $customer_ids)->latest()->paginate($this->perPage);
            }
          }
          $orders = $this->addMeta(OrderResource::collection($orders));
          return response()->json(compact('orders'), 200);
        }
        return $this->errorResponse('A buyer corresponding to the search was not found', 404);
      } elseif (filter_var($search, FILTER_VALIDATE_INT)) {
        //search parameter is an id
        $order_id = $search;
        if ($type == 'All') {
          $orders = Order::where([['merchant_id', $user->id], ['id', $order_id]])->latest()->paginate($this->perPage);
        } else {
          if (isset($statusArr[$type])) {
            $status = $statusArr[$type];
            $orders = Order::where([['merchant_id', $user->id], ['id', $order_id], ['status', $status]])->latest()->paginate($this->perPage);
          } else {
            $orders = Order::where([['merchant_id', $user->id], ['id', $order_id]])->latest()->paginate($this->perPage);
          }
        }

        $orders = $this->addMeta(OrderResource::collection($orders));
        return response()->json(compact('orders'), 200);
      } elseif (is_string($search)) {
        //search parameter is a string
        $search = filter_var($search, FILTER_SANITIZE_STRING);
        $customer_ids = User::where('name', 'like', '%' . $search . '%')
          ->orWhere('email', 'like', '%' . $search . '%')
          ->get(['id'])->toArray();
        if (!is_null($customer_ids)) {
          if ($type == 'All') {
            $orders = Order::where('merchant_id', $user->id)->whereIn('buyer_id', $customer_ids)->latest()->paginate($this->perPage);
          } else {
            if (isset($statusArr[$type])) {
              $status = $statusArr[$type];
              $orders = Order::where([['merchant_id', $user->id], ['status', $status]])->whereIn('buyer_id', $customer_ids)->latest()->paginate($this->perPage);
            } else {
              $orders = Order::where('merchant_id', $user->id)->whereIn('buyer_id', $customer_ids)->latest()->paginate($this->perPage);
            }
          }
          $orders = $this->addMeta(OrderResource::collection($orders));
          return response()->json(compact('orders'), 200);
        }
        return $this->errorResponse('A buyer corresponding to the search was not found', 404);
      }
      return $this->errorResponse('Your search parameter is invalid', 400);
    } else {
      if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
        //search parameter is an email
        $email = $search;
        $customer_ids = User::where('email', $email)->get(['id'])->toArray();
        if (!is_null($customer_ids)) {
          if ($type == 'All') {
            $orders = Order::where('buyer_id', $user->id)->whereIn('merchant_id', $customer_ids)->latest()->paginate($this->perPage);
          } else {
            if (isset($statusArr[$type])) {
              $status = $statusArr[$type];
              $orders = Order::where([['buyer_id', $user->id], ['status', $status]])->whereIn('merchant_id', $customer_ids)->latest()->paginate($this->perPage);
            } else {
              $orders = Order::where('buyer_id', $user->id)->whereIn('merchant_id', $customer_ids)->latest()->paginate($this->perPage);
            }
          }

          $orders = $this->addMeta(OrderResource::collection($orders));
          return response()->json(compact('orders'), 200);
        }
        return $this->errorResponse('A merchant corresponding to the search was not found', 400);
      } elseif (filter_var($search, FILTER_VALIDATE_INT)) {
        //search parameter is an id
        $order_id = $search;
        if ($type == 'All') {
          $orders = Order::where([['buyer_id', $user->id], ['id', $order_id]])->latest()->paginate($this->perPage);
        } else {
          if (isset($statusArr[$type])) {
            $status = $statusArr[$type];
            $orders = Order::where([['buyer_id', $user->id], ['id', $order_id], ['status', $status]])->latest()->paginate($this->perPage);
          } else {
            $orders = Order::where([['buyer_id', $user->id], ['id', $order_id]])->latest()->paginate($this->perPage);
          }
        }

        $orders = $this->addMeta(OrderResource::collection($orders));
        return response()->json(compact('orders'), 200);
      } elseif (is_string($search)) {
        $search = filter_var($search, FILTER_SANITIZE_STRING);
        $customer_ids = User::where('name', 'like', '%' . $search . '%')
          ->orWhere('email', 'like', '%' . $search . '%')
          ->get(['id'])->toArray();
        if (!is_null($customer_ids)) {
          if ($type == 'All') {
            $orders = Order::where('buyer_id', $user->id)->whereIn('merchant_id', $customer_ids)->latest()->paginate($this->perPage);
          } else {
            if (isset($statusArr[$type])) {
              $status = $statusArr[$type];
              $orders = Order::where([['buyer_id', $user->id], ['status', $status]])->whereIn('merchant_id', $customer_ids)->latest()->paginate($this->perPage);
            } else {
              $orders = Order::where('buyer_id', $user->id)->whereIn('merchant_id', $customer_ids)->latest()->paginate($this->perPage);
            }
          }

          $orders = $this->addMeta(OrderResource::collection($orders));
          return response()->json(compact('orders'), 200);
        }
        return $this->errorResponse('A merchant corresponding to the search was not found', 400);
      }
      return $this->errorResponse('Your search parameter is invalid', 400);
    }
    return $this->errorResponse('Account type is invalid', 400);
  }

  public function openDispute(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'orderID' => 'integer|required',
      'dispute_category' => 'nullable|string',
      'dispute_description' => 'required|string',
      'dispute_option' => 'required|string|in:Refund,Replace',
      'dispute_files' => 'nullable|array',
      'dispute_files.*' => 'nullable|mimes:jpeg,jpg,png,pdf,gif,bmp|max:5120',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      $customerID = $this->getAuthID($request);

      $order = Order::where(['id' => $request['orderID'], 'buyer_id' => $customerID])->first();

      if (is_null($order)) {
        return $this->errorResponse('Order/transaction was not found', 404);
      }

      if (in_array($order->status, [ORDER::PAID, ORDER::PROCESSING, ORDER::SHIPPED]) && $order->delivery_type == ORDER::TYPE_DELIVERY) {
        return $this->errorResponse('Dispute cannot be opened as order is yet to be delivered', 403);
      }

      foreach (ORDER::DISPUTE_NOT_ALLOWED as $status => $errorMessage) {
        if ($order->status == $status) {
          return $this->errorResponse($errorMessage, 403);
        }
      }
      //get merchant and customer info
      $merchant = User::find($order->merchant_id);
      if (is_null($merchant)) {
        return $this->errorResponse('Merchant not found', 404);
      }

      $customer = User::find($order->buyer_id);
      if (is_null($customer)) {
        return $this->errorResponse('User not found', 404);
      }

      $dispute = Dispute::create([
        'merchant_id' => $merchant->id,
        'customer_id' => $customer->id,
        'customer_email' => $customer->email,
        'merchant_email' => $merchant->email,
        'order_id' => $request->input('orderID'),
        'dispute_referenceid' => "Mdis-" . time() . $merchant->id . $customer->id  . $order->id,
        'dispute_category' => $request->input('dispute_category'),
        'dispute_description' => $request->input('dispute_description'),
        'dispute_option' => $request->input('dispute_option'),
        'dispute_status' => Dispute::OPEN
      ]);
      $statusArr = cc('transaction.statusArray');
      $order->update(['status' => ORDER::DISPUTED]);

      if ($request->hasFile('dispute_files') && !is_null($dispute)) {

        $linkArray = $this->imageUtil->saveDocument($request->file('dispute_files'), '/dispute_files/', $dispute->id);

        if (!is_null($linkArray)) {
          foreach ($linkArray as $link) {
            DisputeFile::create([
              'dispute_id' => $dispute->id,
              'file_link'  => $link,
            ]);
          }
        }
      }
      //Send dispute email to both buyer and seller
      $this->Mailer->sendDisputeEmail($order, $dispute);
      $this->saveActivity('Order', $order->id, $merchant->id, $customer->id, "{$customer->name} raised a dispute on order", [], $order);
      $dispute = new DisputeResource($dispute);
      return response()->json(compact('dispute'), 201);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function acceptDispute(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'orderID' => 'integer|required',
      'dispute_referenceid' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      $merchantID = $this->getAuthID($request);
      $order = Order::where(['id' => $request['orderID'], 'merchant_id' => $merchantID])->first();
      if (is_null($order)) {
        return $this->errorResponse('Order was not found', 404);
      }

      $dispute = Dispute::where([['order_id', $request->input('orderID')], ['dispute_referenceid', $request->input('dispute_referenceid')]])->first();
      if (is_null($dispute)) {
        return $this->errorResponse('Order Dispute was not found', 404);
      }

      $tranx = Transaction::where('transcode', $order->paymentRef)->first();
      if (is_null($tranx)) {
        return $this->errorResponse('Order transaction was not found', 404);
      }

      //Send dispute accepted email to buyer, seller and admin
      $this->Mailer->sendDisputeAcceptedEmail($order, $dispute);

      $order->update(['status' => ORDER::IN_REVIEW]);

      $dispute->update(['dispute_status' => Dispute::ACCEPTED]);

      return response()->json(["ResponseStatus" => "Successful", 'Detail' => 'Dispute has been accepted and under review.', 'message' => 'Dispute has been accepted and under review', "ResponseCode" => 200], 200);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function rejectDispute(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'orderID' => 'required|integer',
      'dispute_referenceid' => 'required|string',
      'reason' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      $merchantID = $this->getAuthID($request);
      $order = Order::where(['id' => $request['orderID'], 'merchant_id' => $merchantID])->first();
      if (is_null($order)) {
        return $this->errorResponse('Order was not found', 404);
      }

      $dispute = Dispute::where([['order_id', $request->input('orderID')], ['dispute_referenceid', $request->input('dispute_referenceid')]])->first();
      if (is_null($dispute)) {
        return $this->errorResponse('Order Dispute was not found', 404);
      }

      $tranx = Transaction::where('transcode', $order->paymentRef)->first();
      if (is_null($tranx)) {
        return $this->errorResponse('Order transaction was not found', 404);
      }

      //Send dispute rejected email to buyer, seller and admin
      //$this->Mailer->sendDisputeRejectedEmail($order, $dispute);

      $order->update(['status' => ORDER::IN_REVIEW]);

      $dispute->update(['dispute_status' => Dispute::REJECTED]);

      return response()->json(["ResponseStatus" => "Successful", 'Detail' => 'Dispute has been rejected and under review.', 'message' => 'Dispute has been rejected and under review', "ResponseCode" => 200], 200);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function getDisputedOrders(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'type' => 'nullable|string',  // in ['Disputed', 'Refunded', 'Replaced']
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      $merchantID = $this->getAuthID($request);
      if ($request->filled('type')) {
        switch ($request->type) {
          case 'Disputed':
            $orders = Order::with('disputes.disputeFiles')->disputed($merchantID)->paginate($this->perPage);
            $orders = $this->addMeta(OrderResource::collection($orders));
            return response()->json(compact('orders'), 200);
            break;
          case 'All':
            $orders = Order::with('disputes.disputeFiles')->all($merchantID)->paginate($this->perPage);
            $orders = $this->addMeta(OrderResource::collection($orders));
            return response()->json(compact('orders'), 200);
            break;
          case 'Refunded':
            $orders = Order::with('disputes.disputeFiles')->refunded($merchantID)->paginate($this->perPage);
            $orders = $this->addMeta(OrderResource::collection($orders));
            return response()->json(compact('orders'), 200);
            break;
          case 'Replaced':
            $orders = Order::with('disputes.disputeFiles')->replaced($merchantID)->paginate($this->perPage);
            $orders = $this->addMeta(OrderResource::collection($orders));
            return response()->json(compact('orders'), 200);
            break;
          case 'In-Review':
            $orders = Order::with('disputes.disputeFiles')->inreview($merchantID)->paginate($this->perPage);
            $orders = $this->addMeta(OrderResource::collection($orders));
            return response()->json(compact('orders'), 200);
            break;
          default:
            return $this->errorResponse('Invalid type', 400);
        }
      }
      $orders = Order::with('disputes.disputeFiles')->all($merchantID)->paginate($this->perPage);
      $orders = $this->addMeta(OrderResource::collection($orders));
      return response()->json(compact('orders'), 200);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function getShipmentRates(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'cart_id' => 'required|integer',
      'address_id' => 'required|integer',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $user = $this->getAuthUser($request);
      if (!is_null($user)) {
        $cart = Cart::find($request->input('cart_id'));
        if (!is_null($cart)) {
          $items = $cart->cartItems;
          $toAddress = Address::find($request->input('address_id'));
          $merchant = User::find($cart->merchant_id);
          if (is_null($merchant)) {
            return $this->errorResponse('Merchant not found', 404);
          }
          //get Shipment cost from Sendy
          //$shipmentRates = $this->Sendy->getShipmentCost($items);
          $shipmentRates = [
            'error' => 0,
            'statusCode' => 200,
            'cost_of_goods' => $cart->totalprice,
            'fulfilment_fee' => Order::ORDER_SHIPPING_FEE,
            'currency' => $this->currency
          ];
          $shipmentRates['provider'] = 'Flatrate';
          if ($shipmentRates['error'] != 1) {
            $logistic = OrderLogistic::firstOrCreate(
              ['cart_id' => $cart->id],
              [
                'delivery_address_id' => $toAddress->id,
                'currency' => $shipmentRates['currency'],
                'amount' => $shipmentRates['fulfilment_fee']
              ]
            );
            return response()->json(compact('shipmentRates'), 201);
          } else {
            return response()->json(compact('shipmentRates'), 401);
          }
        } else {
          return $this->errorResponse('Cart not found', 404);
        }
      } else {
        return $this->errorResponse('User not found', 404);
      }
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  protected function updateProductQuantity($orderItems)
  {
    try {
      foreach ($orderItems as $item) {
        $product = Product::find($item->productID);
        $productQty = $product->quantity;
        $product->update([
          'quantity' => $productQty - $item->quantity
        ]);
      }
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function acceptRefund(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'orderID' => 'integer|required',
      'dispute_referenceid' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      $merchantID = $this->getAuthID($request);
      $order = Order::where(['id' => $request['orderID'], 'merchant_id' => $merchantID])->first();
      if (is_null($order)) {
        return $this->errorResponse('Order was not found', 404);
      }

      $dispute = Dispute::where([['order_id', $request->input('orderID')], ['dispute_referenceid', $request->input('dispute_referenceid')]])->first();
      if (is_null($dispute)) {
        return $this->errorResponse('Order Dispute was not found', 404);
      }

      $tranx = Transaction::where('transcode', $order->paymentRef)->first();
      if (is_null($tranx)) {
        return $this->errorResponse('Order transaction was not found', 404);
      }

      //process the refund
      $this->initiateRefund($order, $tranx);
      return response()->json(["ResponseStatus" => "Successful", 'Detail' => 'Refund has been initiated successfully.', 'message' => 'Refund has been initiated successfully.', "ResponseCode" => 201], 201);

      //Send refund email
      //$this->peppUtil->send_order_dispute_resolution_email($order, $dispute);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }


  public function replaceItem(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'orderID' => 'integer|required',
      'dispute_referenceid' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {

      $merchantID = $this->getAuthID($request);
      $order = Order::where(['id' => $request['orderID'], 'merchant_id' => $merchantID])->first();

      if (is_null($order)) {
        return $this->errorResponse('Order/transaction was not found', 404);
      }

      $dispute = Dispute::where([['order_id', $request->input('orderID')], ['dispute_referenceid', $request->input('dispute_referenceid')]])->first();

      if (is_null($dispute)) {
        return $this->errorResponse('Order Dispute was not found', 404);
      }

      $statusArr = cc('transaction.statusArray');

      //create a new order 
      // $newOrder = $order->replicate();
      // $newOrder->status = $statusArr['Processing'];
      // $newOrder->save();


      //send order for shipment
      $this->sendShipment($order->id);

      $dispute->update([
        'dispute_status' => Dispute::CLOSED_REPLACED,
        'comment' => 'Item has been replaced',
        'resolution_date' => Carbon::now()
      ]);

      $order->update([
        'status' => Order::REPLACED
      ]);

      //Send email
      //$this->peppUtil->send_order_replaced_email($order, $dispute);

      return response()->json(["ResponseStatus" => "Successful", 'Detail' => 'Order has been processed for replacement', 'message' => 'Order has been processed for replacement.', "ResponseCode" => 200], 200);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function preCheckout(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255|regex:/^[a-zA-Z]+(?:\s[a-zA-Z]+)+$/',
      'email' => 'required|email',
      'phone' => ['required', 'numeric', (new Phone)->country('GB')],
      'street' => 'required_if:delivery_type,Delivery|string',
      'city' => 'required_if:delivery_type,Delivery|string',
      'state' => 'required_if:delivery_type,Delivery|string',
      'postal_code' => 'numeric|nullable',
      'cart' => 'required|array',
      'delivery_type' => 'required|string|in:Delivery,Pickup',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $phone = $request->phone;
      $delivery_type = $request->delivery_type;
      //fetch user using email or create an account
      $name_arr = explode(" ", request('name'));
      $user = User::firstOrCreate(
        ['email' =>  request('email')],
        [
          'name' => request('name'),
          'firstName' =>  isset($name_arr[0]) ? $name_arr[0] : null,
          'lastName' =>  isset($name_arr[1]) ? $name_arr[1] : null,
          'password' => Hash::make('12345'),
          'account_type' => 'Buyer',
          'phone' => $phone
        ]
      );

      if ($user->phone != $phone) {
        $user->update(['phone' => $phone]);
      }

      if ($user->name != $request->name) {
        $name = $request->name;
        $name_arr = explode(" ", $name);
        $user->update([
          'name' => $name,
          'firstName' =>  isset($name_arr[0]) ? $name_arr[0] : null,
          'lastName' =>  isset($name_arr[1]) ? $name_arr[1] : null
        ]);
      }

      //process cart and cart items
      $carts = $request->cart;
      foreach ($carts as $cart) {
        $product = Product::find($cart['productID']);
        if (!is_null($product)) {
          if ($cart['quantity'] > $product->quantity) {
            return $this->errorResponse('Quantity is more than available quantity', 400);
          }
        } else {
          return $this->errorResponse('Product not found', 404);
        }
        $buyerCart = Cart::firstOrCreate(
          ['buyer_id' =>  $user->id, 'status' => CART::UNFULFILLED],
          [
            'buyer_id' => $user->id,
            'merchant_id' => $product->merchant_id,
            'max_delivery_period'  => $product->deliveryperiod,
            'min_delivery_period'  => $product->deliveryperiod,
            'totalprice'  => 0,
            'shipping'   => 0,
            'total_sum'  => 0,
            'items_count' => 0,
            'currency' => $this->currency,
            'delivery_type' => $delivery_type,
          ]
        );

        $total_cost = floatval($product->price) * $cart['quantity'];
        $itemCost = floatval($product->price) * $cart['quantity'];
        $cartItem = CartItem::firstOrCreate(
          ['cart_id' =>  $buyerCart->id, 'productID' => $product->id],
          [
            'cart_id' => $buyerCart->id,
            'productID' => $product->id,
            'productname' => $product->productname,
            'image_url' => $product->image_url,
            'description' => $product->description,
            'quantity' => $cart['quantity'],
            'deliveryperiod' => $product->deliveryperiod,
            'currency' => $this->currency,
            'price' => $product->price,
            'total_cost' => $total_cost,
          ]
        );

        $totalprice = $buyerCart->totalprice + $itemCost;

        $max_deliveryperiod = ($buyerCart->max_delivery_period < $product->deliveryperiod) ? $product->deliveryperiod : $buyerCart->max_delivery_period;
        $min_deliveryperiod = ($buyerCart->min_delivery_period > $product->deliveryperiod) ? $product->deliveryperiod : $buyerCart->min_delivery_period;

        if ($cartItem->wasRecentlyCreated === true) {
          $buyerCart->update([
            'totalprice' => $totalprice,
            'items_count' => $buyerCart->items_count + 1,
            'max_delivery_period'  => $max_deliveryperiod,
            'min_delivery_period'  => $min_deliveryperiod,
            'min_delivery_date' => Carbon::now()->addDays($min_deliveryperiod),
            'max_delivery_date' => Carbon::now()->addDays($max_deliveryperiod),
            'total_sum'  => $totalprice,
          ]);
        } else {
          $cartItem->update([
            'quantity' => $cartItem->quantity +  $cart['quantity'],
            'total_cost' => $cartItem->total_cost + $total_cost
          ]);
          $buyerCart->update([
            'totalprice' => $totalprice,
            'total_sum'  => $totalprice,
          ]);
        }
      }

      $buyerCart->update(['status' => CART::PROCESSED]);

      if ($delivery_type == ORDER::TYPE_DELIVERY) {
        $request_address = $request->street . ', ' . $request->city . ', ' . $request->state . ', ' . $this->country;

        //check if address already exists and has been validated before
        $address = Address::where(['recipient' => $user->id, 'name' => $request->name, 'email' => $request->email, 'address' => $request_address])
          ->whereNotNull('longitude')
          ->whereNotNull('latitude')
          ->first();

        //address not found ?
        if (is_null($address)) {
          //validate address
          $add_info = Util::validateAddressWithGoogle($user, $request_address);
          if ($add_info['error'] == 0) {
            //save the address after validation
            $address = Address::create([
              'recipient' => $user->id,
              //'address_code' => $add_info['addressDetails']['address_code'],
              'address' => $add_info['addressDetails']['address'],
              'name' => $request->name,
              'email' => $add_info['addressDetails']['email'],
              'street' => $add_info['addressDetails']['street'],
              'phone' => $add_info['addressDetails']['phone'],
              'formatted_address' => $add_info['addressDetails']['formatted_address'],
              'country' => $add_info['addressDetails']['country'],
              'country_code' => $add_info['addressDetails']['country_code'],
              'city' => $add_info['addressDetails']['city'],
              'city_code' => $add_info['addressDetails']['city_code'],
              'state' => $add_info['addressDetails']['state'],
              'state_code' => $add_info['addressDetails']['state_code'],
              'longitude' => $add_info['addressDetails']['longitude'],
              'latitude' => $add_info['addressDetails']['latitude'],
              'postal_code' => $request['postal_code'],
            ]);
          } else {
            return $this->errorResponse('Address error: ' . $add_info['responseMessage'], 400);
          }
        }

        //get shipment rates
        if (!is_null($buyerCart)) {
          $items = $buyerCart->cartItems;
          $merchant = User::find($buyerCart->merchant_id);
          if (is_null($merchant)) {
            return $this->errorResponse('Merchant not found', 404);
          }
          if (is_null($merchant->pickup_address)) {
            return $this->errorResponse('Merchant does not have a pickup address', 404);
          }
          //get Shipment cost from Sendy
          //$shipmentRates = $this->Sendy->getShipmentCost($items);
          $shipmentRates = [
            'error' => 0,
            'statusCode' => 200,
            'cost_of_goods' => $buyerCart->totalprice,
            'fulfilment_fee' => Order::ORDER_SHIPPING_FEE,
            'currency' => $this->currency
          ];
          $shipmentRates['provider'] = 'Flatrate';
          if ($shipmentRates['error'] != 1) {
            $logistic = OrderLogistic::firstOrCreate(
              ['cart_id' => $buyerCart->id],
              [
                'delivery_address_id' => $address->id,
                'currency' => $shipmentRates['currency'],
                'amount' => $shipmentRates['fulfilment_fee']
              ]
            );
            $cart = new CartResource($buyerCart);
            $address = new AddressResource($address);
            $token = $this->respondWithToken(JWTAuth::fromUser($user));
            return response()->json(compact('token', 'cart', 'address', 'user', 'shipmentRates'), 201);
          } else {
            return response()->json(compact('cart', 'address', 'user', 'shipmentRates'), 201);
          }
        }
        return $this->errorResponse('Error with cart', 400);
      }

      $cart = new CartResource($buyerCart);
      $token = $this->respondWithToken(JWTAuth::fromUser($user));
      return response()->json(compact('token', 'cart', 'user'), 201);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      Log::error('PreCheckout error', [$e->getMessage() . ' - ' . $e->__toString()]);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }


  public function prepareCheckout(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255|regex:/^[a-zA-Z]+(?:\s[a-zA-Z]+)+$/',
      'email' => 'required|email',
      'phone' => ['required', 'numeric', (new Phone)->country('GB')],
      'street' => 'required|string',
      'city' => 'required|string',
      'state' => 'nullable|string',
      'postal_code' => 'required|string',
      'country' => 'required|string',
      'cart_id' => 'required|integer',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $delivery_type = "Delivery";
      $userID = $this->getAuthID($request);
      $user = User::find($userID);
      if (is_null($user)) {
        return $this->errorResponse('User not found', 404);
      }

      $cart = Cart::where(['buyer_id' => $user->id, 'id' => $request['cart_id']])->first();
      if (is_null($cart)) {
        return $this->errorResponse('Cart not found', 404);
      }

      $merchant = User::find($cart->merchant_id);
      if (is_null($merchant)) {
        return $this->errorResponse('Merchant not found', 404);
      }

      if ($delivery_type == ORDER::TYPE_DELIVERY) {
        $country = $request->country;
        $state = $request->filled('state') ? $request->state . ', ' : '';
        $buyerAddress = $request->input('street') . ', ' . $request->input('city') . ', ' . $state . $country;

        //check if address already exists and has been validated before
        $address = Address::where(['recipient' => $user->id, 'name' => $request->name, 'email' => $request->email, 'address' => $buyerAddress])
          ->whereNotNull('longitude')
          ->whereNotNull('latitude')
          ->first();

        //address not found ?
        if (is_null($address)) {
          //validate address
          $add_info = Util::validateAddressWithGoogle($user, $buyerAddress);
          if ($add_info['error'] == 0) {
            //save the address after validation
            $address = Address::create([
              'recipient' => $user->id,
              'address_code' => generateAddressCode($user),
              'address' => $add_info['addressDetails']['address'],
              'name' => $request->name,
              'email' => $request->email,
              'phone' => $request->phone,
              'street' => $add_info['addressDetails']['street'],
              'formatted_address' => $add_info['addressDetails']['formatted_address'],
              'country' => $add_info['addressDetails']['country'],
              'country_code' => $add_info['addressDetails']['country_code'],
              'city' => $add_info['addressDetails']['city'],
              'city_code' => $add_info['addressDetails']['city_code'],
              'state' => $add_info['addressDetails']['state'],
              'state_code' => $add_info['addressDetails']['state_code'],
              'longitude' => $add_info['addressDetails']['longitude'],
              'latitude' => $add_info['addressDetails']['latitude'],
              'postal_code' => $add_info['addressDetails']['postal_code'],
              'zip' => $request['postal_code']
            ]);
          } else {
            return $this->errorResponse('Address error: ' . $add_info['responseMessage'], 400);
          }
        }

        //delivery fee
        $shipping = 100;
        $cart->update(['shipping' => $shipping]);

        $logistic = OrderLogistic::firstOrCreate(
          ['cart_id' => $cart->id],
          [
            'delivery_address_id' => $address->id,
            'currency' => $cart->currency,
            'amount' => $shipping
          ]
        );
        $cart = new CartResource($cart);
        $address = new AddressResource($address);
        return response()->json(compact('cart', 'address'), 201);
      }
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      Log::error('Prepare Checkout error', [$e->getMessage() . ' - ' . $e->__toString()]);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function checkout(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'cart_id' => 'required|integer',
      'address_id' => 'nullable|integer',
      'delivery_note' => 'nullable|string',
      'estimated_days' => 'nullable|string',
      'payWith' => 'required|in:stripe',
      'redirectUrl' => 'url|required',
      'cancelUrl' => 'url|required',
      'shipping' => 'nullable|numeric',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $customerID = $this->getAuthID($request);
      $customer = User::find($customerID);
      if (is_null($customer)) {
        return $this->errorResponse('User not found', 404);
      }
      $paymentProvider = $request->input('payWith');
      $redirectUrl = $request->input('redirectUrl');
      $cancelUrl = $request->input('cancelUrl');
      $cart = Cart::find($request['cart_id']);
      if (is_null($cart)) {
        return $this->errorResponse('Cart not found', 404);
      }

      $merchant = User::find($cart->merchant_id);
      $maxDelivery = Carbon::now()->addWeekdays($cart->max_delivery_period);
      $minDelivery = Carbon::now()->addWeekdays($cart->min_delivery_period);

      $amount = $cart->total_sum;
      $currency = $cart->currency;
      $shipment_cost = $request->filled('shipping') ? $request->input('shipping') : 0;
      //create the order
      $order = Order::create([
        'maxdeliverydate' => $maxDelivery,
        'mindeliverydate' => $minDelivery,
        'merchant_id' => $merchant->id,
        'buyer_id' => $customer->id,
        'address_id' => $request['address_id'],
        'totalprice' => $cart->totalprice,
        'shipping' => $cart->shipping,
        'status' => ORDER::UNPAID,
        'total' => $amount,
        'currency' => $currency,
        'cart_id' => $request['cart_id'],
        'delivery_type' => $cart->delivery_type,
        'coupon_id' => $cart->coupon_id,
        'confirmation_pin' => Str::uuid()
      ]);
      $orderRef = strtoupper(substr($customer->name, 0, 2)) . $order->id . '-' . time();
      $order->update(['orderRef' => $orderRef]);

      $cartItems = $cart->cartItems;
      if (!is_null($cartItems)) {
        $items = $cartItems;
        $count = count($items);
        foreach ($items as $item) {
          OrderItem::create([
            'order_id' => $order->id,
            'productname' =>  $item->productname,
            'price' => $item->price,
            'quantity' => $item->quantity,
            'totalCost' => $item->total_cost,
            'image' => $item->image_url,
            'productID' => $item->productID,
          ]);
        }
      }

      $logistic = OrderLogistic::where('cart_id', $cart->id)->first();
      if (!is_null($logistic)) {
        $logistic->update([
          'order_id' => $order->id,
          'delivery_note' => $request->input('delivery_note'),
          'estimated_days' => $request->input('estimated_days'),
        ]);
        $shipment_cost = $logistic->amount;
      }

      if (!is_null($order->coupon_id)) {
        $coupon = Coupon::find($order->coupon_id);
        if (!is_null($coupon)) {
          $amount = $coupon->getDiscountedAmount($amount);
        }
      }

      $amount = $amount + $shipment_cost;

      if ($paymentProvider == 'stripe') {
        if (is_null($merchant->stripe_account_id)) {
          $paymentLink = $this->stripeUtils->generatePayLink($customer, $items, $redirectUrl, $cancelUrl, $shipment_cost, $order->tax);
          if ($paymentLink['error'] == 0) {
            $paymentUrl = $paymentLink['paymentUrl'];
            $paymentRef = $paymentLink['paymentRef'];
          }
        } else {
          $paymentLink = $this->stripeUtils->generateMerchantPayLink($merchant->stripe_account_id, $customer, $items, $redirectUrl, $cancelUrl, $shipment_cost, $order->tax);
          if ($paymentLink['error'] == 0) {
            $paymentUrl = $paymentLink['paymentUrl'];
            $paymentRef = $paymentLink['paymentRef'];
          }
        }
        //update order data and update cart status
        if ($paymentLink['error'] == 0) {
          $order->update([
            'payurl' => $paymentUrl,
            'paymentRef' => $paymentRef,
            'payment_gateway' => $paymentProvider,
            'shipping' => $shipment_cost
          ]);
          $cart->update(['shipping' => $shipment_cost]);
          $fulfill_days = $cart->max_delivery_period;

          //Log Transaction
          $this->createTransaction($order, $customer, $merchant, $fulfill_days, $paymentRef);
          //Create Pending payment
          $this->createPendingPayment('Order_Payment', $amount, $currency, $paymentRef, $customer->id, $paymentProvider);

          //Return payment link and reference.
          return response()->json(compact('paymentUrl', 'paymentRef', 'order', 'cart'), 201);
        } else {
          return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => $paymentLink, "ResponseMessage" => 'Payment link could not be generated, try again', "ResponseCode" => 500], 500);
        }
      }
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      Log::error('Checkout error', [$e->getMessage() . ' - ' . $e->__toString()]);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }


  public function verifyPayment(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'paymentReference' => 'string|required',
      'sessionId' => 'string|required',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      $user = $this->getAuthUser($request);
      if (is_null($user)) {
        return $this->errorResponse('User not found', 404);
      }
      $paymentRef = $request->paymentReference;
      $sessionID = $request->sessionId;

      $order = Order::with('orderAddress')->where('paymentRef', $paymentRef)->first();

      if (is_null($order)) {
        return $this->errorResponse('Order not found', 404);
      }

      //find the order merchant
      $merchant = User::find($order->merchant_id);
      if (is_null($merchant)) {
        return $this->errorResponse('Merchant not found', 404);
      }

      $paymentDetail = PendingPayment::where('reference', $paymentRef)->first();

      if (is_null($paymentDetail)) {
        return $this->errorResponse('Order payment details not found', 404);
      }

      //verify stripe Payment
      if (!is_null($merchant->stripe_account_id)) {
        $session = $this->stripeUtils->getMerchantSessionDetails($merchant->stripe_account_id, $sessionID);
      } else {
        $session = $this->stripeUtils->getSessionDetails($sessionID);
      }

      //return $session;
      if ($session['error'] == 0) {
        $paymentStatus = $session['session']->payment_status;
        $sessionStatus = $session['session']->status;
        $customerId = $session['session']->customer;
        $invoiceId = $session['session']->invoice;
        $ref = $session['session']->client_reference_id;
        $amount = $session['session']->amount_total;
        $paymentDetail = $this->updatePendingPayment($ref, $paymentStatus);
        if ($order->status == Order::UNPAID && $paymentStatus == 'paid') {
          if (!is_null($order->coupon_id)) {
            $coupon = Coupon::find($order->coupon_id);
            if (!is_null($coupon)) {
              $this->recordCouponUsage($user->id, $coupon->id);
            }
          }
          //process order
          $this->processOrder($order);
          Cart::find($order->cart_id)->update(['status' => CART::FULFILLED]);
        }
        return response()->json(compact('paymentStatus', 'paymentDetail', 'order'), 201);
      } else {
        return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseMessage" => "Payment verification failed! please try again.", 'Detail' => $session, "ResponseCode" => 400], 400);
      }
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function trackOrder(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'orderRef' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      $order = Order::where('orderRef', $request->orderRef)->first();
      if (is_null($order)) {
        return $this->errorResponse('Order not found', 404);
      }

      if ($order->delivery_type != Order::TYPE_DELIVERY) {
        return $this->errorResponse('Order is a pickup order', 400);
      }

      $logistics = $order->orderLogistics;

      if (is_null($logistics) || is_null($logistics->fulfilment_request_id)) {
        return $this->errorResponse('Order logistics not found', 404);
      }

      $trackingInfo = $this->Sendy->trackOrder($logistics->fulfilment_request_id);

      if ($trackingInfo['error'] != 1) {
        $statusBefore = $order->status;
        $sendyTrackingStatuses = [
          'ORDER_RECEIVED' => 'Processing',
          'AWAITING_INVENTORY_TO_FULFIL' => 'Processing',
          'PROCESSING_ORDER_AT_HUB' => 'Processing',
          'ORDER_CANCELLED' => 'Canceled',
          'IN_TRANSIT_TO_BUYER' => 'Shipped',
          'ORDER_COMPLETED' => 'Delivered',
        ];
        $status = $sendyTrackingStatuses[$trackingInfo['status']];
        $statusArr = cc('transaction.statusArray');
        $statusNow = $statusArr[$status];
        //check if status has changed
        if ($statusBefore != $statusNow) {
          $order->update(['status' => $statusNow]);
          $logistics->update(['delivery_status' => $status]);
          //$this->Mailer->sendOrderStatusChangeEmail($order, $status);
        }
      }

      return response()->json(["ResponseStatus" => "Successful", 'trackingInfo' => $trackingInfo, "ResponseCode" => 200], 200);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function confirmOrder(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'orderRef' => 'required|string',
      'confirmation_pin' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $order = Order::where(['orderRef' => $request->orderRef, 'confirmation_pin' => $request->confirmation_pin])->first();
      if (!is_null($order)) {
        if ($order->status != Order::COMPLETED && $order->disbursement_status != 1) {
          $wallet_id = $order->seller->wallet_id;
          $amount = $order->totalprice;
          $this->createInternalTransaction($order);
          $this->creditWallet($wallet_id, $amount);
          $order->update(['status' => ORDER::COMPLETED, 'disbursement_status' => 1]);
        }
        return response()->json(["ResponseStatus" => "Successful", 'Detail' => 'Order has been confirmed successfully.', 'ResponseMessage' => 'Order has been confirmed successfully.', "ResponseCode" => 200], 200);
      }
      return $this->errorResponse('Order not found', 404);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function cancelOrder(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'orderRef' => 'required|string',
      'reason' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      $reason = $request->reason;

      $order = Order::where('orderRef', $request->orderRef)->first();
      if (is_null($order)) {
        return $this->errorResponse('Order not found', 404);
      }

      $tranx = Transaction::where('transcode', $order->paymentRef)->first();
      if (is_null($tranx)) {
        return $this->errorResponse('Order transaction was not found', 404);
      }

      foreach (ORDER::CANCEL_NOT_ALLOWED as $status => $errorMessage) {
        if ($order->status == $status) {
          return $this->errorResponse($errorMessage, 403);
        }
      }

      $logistics = $order->orderLogistics;
      if (is_null($logistics) || is_null($logistics->fulfilment_request_id)) {
        //order has not been sent to Sendy or its a pickup order, so just update order status, initiate refund and send email
        $this->processCancelation($order, $reason);
        return response()->json(["ResponseStatus" => "Successful", 'Detail' => 'Order canceled and refund initiated successfully.', 'message' => 'Order canceled and refund initiated successfully.', "ResponseCode" => 201], 201);
      }

      $trackingInfo = $this->Sendy->trackOrder($logistics->fulfilment_request_id);
      if ($trackingInfo['error'] != 1) {
        if (in_array($trackingInfo['status'], ['IN_TRANSIT_TO_BUYER', 'ORDER_COMPLETED'])) {
          return $this->errorResponse('Order cannot be canceled as it has been shipped or delivered', 403);
        }

        $cancelOrder = $this->Sendy->cancelOrder($logistics->fulfilment_request_id, $reason);
        if ($cancelOrder['error'] != 1) {
          $logistics->update(['delivery_status' => $cancelOrder['status']]);
          $this->processCancelation($order, $reason);
          return response()->json(["ResponseStatus" => "Successful", 'Detail' => 'Order canceled and refund initiated successfully.', 'message' => 'Order canceled and refund initiated successfully.', "ResponseCode" => 201], 201);
        }
        return $this->errorResponse('Order could not be canceled, please try again later', 400);
      }
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function applyCoupon(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'cart_id' => 'required|integer',
      'coupon_code' => 'required|string'
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $customerID = $this->getAuthID($request);

      $customer = User::find($customerID);
      if (is_null($customer)) {
        return $this->errorResponse('User not found', 404);
      }

      $cart = Cart::find($request['cart_id']);
      if (is_null($cart)) {
        return $this->errorResponse('Cart not found', 404);
      }

      $coupon = Coupon::where('code', $request->coupon_code)->first();
      if (is_null($coupon)) {
        return $this->errorResponse('Invalid coupon supplied', 400);
      }

      if (!$coupon->isActive()) {
        return $this->errorResponse('Coupon cannot be redeemed', 400);
      }

      if ($coupon->hasReachedLimit()) {
        return $this->errorResponse('Coupon limit has been reached', 400);
      }

      if ($customer->hasUsedCoupon($coupon->id)) {
        return $this->errorResponse('You have used this coupon!', 400);
      }


      $amount = $cart->total_sum;
      $cart->update(['coupon_id' => $coupon->id]);

      $responseMessage = "Coupon applied successfully";
      $couponValue = $coupon->getCouponValue($amount);

      return response()->json(["ResponseStatus" => "Successful", "ResponseMessage" => $responseMessage, 'couponValue' => $couponValue], 200);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      Log::error('applyCoupon error', [$e->getMessage() . ' - ' . $e->__toString()]);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }
}
