<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\User;
use App\Models\Order;
use App\Mail\TestMail;
use App\Models\Address;
use App\Models\PickupAddress;
use App\Repositories\LetaUtils;
use App\Repositories\PawaPayUtils;
use App\Repositories\PesaPalUtils;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class TestController extends Controller
{
    /**
     * The LetaUtils instance.
     *
     * @var LetaUtils
     */
    protected $letaUtils;

    /**
     * The PawaPayUtils instance.
     *
     * @var PawaPayUtils
     */
    protected $pawaPayUtils;

    /**
     * The PesaPalUtils instance.
     *
     * @var PesaPalUtils
     */
    protected $pesaPalUtils;

    public function __construct(LetaUtils $letaUtils, PawaPayUtils $pawaPayUtils, PesaPalUtils $pesaPalUtils)
    {
        $this->letaUtils = $letaUtils;
        $this->pawaPayUtils = $pawaPayUtils;
        $this->pesaPalUtils = $pesaPalUtils;
    }

    public function retrieveMailConfig()
    {
        try {
            $data = [
                "username" => env('MAIL_USERNAME'),
                "password" => env('MAIL_PASSWORD'),
                "mailFromAddress" => config('mail.from.address'),
                "mailFromName" => config('mail.from.name'),
            ];
            return response()->json(compact('data'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function sendTestMail($to)
    {
        try {
            Mail::to($to)->send(new TestMail());
            return response()->json(["message" => "Test email sent successfully."], 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }


    public function testLeta()
    {
        $from = PickupAddress::find(1);
        $to = Address::find(2);
        $user = User::find(2);
        $order = Order::find(1);
        $leta = $this->letaUtils->getShipmentRates($from, $to);
        //$leta = $this->letaUtils->bookShipment($user, $order->orderRef, $order->orderItems, $from, $to);
        return response()->json(compact('leta'), 200);
    }

    public function testPawaPay()
    {
        $ref = $this->pawaPayUtils->gen_uuid();
        $pawaPayDeposit = $this->pawaPayUtils->requestDeposit("256783456789", 2000, "GBP", $ref, "Test transaction");
        return response()->json(compact('pawaPayDeposit'), 200);
    }


    public function testPesaPal()
    {
        $url = "https://api.mudala.co.ug/api/ipn/pesapal/subscription";
        $pesapalResponse = $this->pesaPalUtils->registerIPN_url($url);
        return response()->json(compact('pesapalResponse'), 200);
    }
}
