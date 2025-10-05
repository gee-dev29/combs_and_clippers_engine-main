<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\BillingInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\BillingInvoiceResource;

class InvoiceController extends Controller
{
  public function getBillingInvoices(Request $request)
  {
    $merchant_id = $this->getAuthID($request);
    try {
      $billing_invoices = User::find($merchant_id)->billingInvoices()->paginate($this->perPage);
      $billing_invoices = $this->addMeta(BillingInvoiceResource::collection($billing_invoices));
      return response()->json(compact('billing_invoices'), 200);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function downloadInvoice(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'invoiceID' => 'required|integer',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    $merchantID = $this->getAuthID($request);
    try {
      $direction = 'ltr';
      $text_align = 'left';
      $not_text_align = 'right';
      $font_family = "'Roboto','sans-serif'";
      $invoice = BillingInvoice::where(['id' => $request->invoiceID, 'merchant_id' => $merchantID])->first();
      if (!is_null($invoice)) {
        $home = asset('/storage');
        $filepath = '/invoices/' . $merchantID . '/' . 'invoice.pdf';
        $absolutePath = $home . $filepath;
        $data = [
          'invoice' => $invoice,
          'font_family' => $font_family,
          'direction' => $direction,
          'text_align' => $text_align,
          'not_text_align' => $not_text_align
        ];
        //return Pdf::loadView('backend.invoices.invoice', $data)->save(public_path('images/invoice.pdf'))->stream('download.pdf');
        $pdf = Pdf::loadView('backend.invoices.invoice', $data)->setPaper('a6', 'portrait')->output();
        $save = Storage::put($filepath, $pdf);

        if ($save) {
          return response()->json(['ResponseStatus' => 'Successful', 'file' => $absolutePath], 200);
        }
        return $this->errorResponse('Sorry! invoice could not be generated', 400);
      }
      return $this->errorResponse('Invoice not found', 404);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function downloadAllInvoice(Request $request)
  {
    try {
      $merchantID = $this->getAuthID($request);
      $direction = 'ltr';
      $text_align = 'left';
      $not_text_align = 'right';
      $font_family = "'Roboto','sans-serif'";
      $invoices = BillingInvoice::where(['merchant_id' => $merchantID])->get();
      if (!is_null($invoices)) {
        $home = asset('/storage');
        $filepath = '/invoices/' . $merchantID . '/' . 'all-invoices.pdf';
        $absolutePath = $home . $filepath;
        $data = [
          'invoices' => $invoices,
          'font_family' => $font_family,
          'direction' => $direction,
          'text_align' => $text_align,
          'not_text_align' => $not_text_align
        ];
        //return Pdf::loadView('backend.invoices.invoice', $data)->save(public_path('images/invoice.pdf'))->stream('download.pdf');
        $pdf = Pdf::loadView('backend.invoices.invoices', $data)->setPaper('a6', 'portrait')->output();
        $save = Storage::put($filepath, $pdf);

        if ($save) {
          return response()->json(['ResponseStatus' => 'Successful', 'file' => $absolutePath], 200);
        }
        return $this->errorResponse('Sorry! invoice could not be generated', 400);
      }
      return $this->errorResponse('Invoice not found', 404);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }
}
