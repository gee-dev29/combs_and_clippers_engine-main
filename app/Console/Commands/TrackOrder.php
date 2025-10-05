<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Repositories\Mailer;
use Illuminate\Console\Command;
use App\Repositories\SendyUtils;
use App\Models\InternalTransaction;
use Illuminate\Support\Facades\Log as Logger;

class TrackOrder extends Command
{
    /**
     * The SendyUtils instance.
     *
     * @var SendyUtils
     */
    protected $Sendy;

    /**
     * The Mailer instance.
     *
     * @var Mailer
     */
    protected $Mailer;

    /**OrderTransaction
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:track {--order_id= : ID of the order you want to track}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This tracks orders and sends email to buyers notifying them of their order status.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SendyUtils $sendy, Mailer $mailer)
    {
        parent::__construct();
        $this->Sendy = $sendy;
        $this->Mailer = $mailer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!empty($this->option('order_id'))) {
            $order_id = $this->option('order_id');
            $orders = Order::where(['id' => $order_id, 'delivery_type' => ORDER::TYPE_DELIVERY])->whereIn('status', [ORDER::PAID, ORDER::PROCESSING, ORDER::SHIPPED])->get();
        } else {
            $orders = Order::where(['delivery_type' => ORDER::TYPE_DELIVERY])->whereIn('status', [ORDER::PAID, ORDER::PROCESSING, ORDER::SHIPPED])->get();
        }

        if (!$orders->count()) {
            $this->error('No pending orders.');
            return;
        }
        $count = count($orders);
        $messages = ["Found {$count} pending orders"];

        foreach ($orders as $order) {
            $logistics = $order->orderLogistics;

            if (is_null($logistics)) {
                $messages[] = "Order logistics not found for {$order->orderRef}";
                continue;
            }

            if (!isset($logistics->fulfilment_request_id) || empty($logistics->fulfilment_request_id)) {
                $messages[] = "Delivery request has not been made for {$order->orderRef}";
                continue;
            }

            $trackingInfo = $this->Sendy->trackOrder($logistics->fulfilment_request_id);

            if ($trackingInfo['error'] != 1) {
                $messages[] = "Tracked Order {$order->orderRef} successfully";
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
                    $messages[] = "Order {$order->orderRef} status has changed to {$status}";
                    $order->update(['status' => $statusNow]);
                    $logistics->update(['delivery_status' => $status]);
                    $this->Mailer->sendOrderStatusChangeEmail($order, $status);
                } else {
                    $messages[] = "Order {$order->orderRef} status has not changed";
                }
            } else {
                $messages[] = "Order {$order->orderRef} could not be tracked";
            }
        }
        $messages = implode("\n", $messages);
        $this->info($messages);
    }
}
