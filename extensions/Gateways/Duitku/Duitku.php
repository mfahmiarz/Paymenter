<?php

namespace Paymenter\Extensions\Gateways\Duitku;

use App\Classes\Extension\Gateway;
use App\Helpers\ExtensionHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class Duitku extends Gateway
{
    public function boot()
    {
        require __DIR__ . '/routes.php';
    }

    public function getMetadata()
    {
        return [
            'display_name' => 'Duitku',
            'version'      => '1.0.0',
            'author'       => 'JOSPlay',
            'website'      => 'https://www.josplay.net',
        ];
    }

    public function getConfig($values = [])
    {
        return [
            [
                'name'         => 'merchant_code',
                'friendlyName' => 'Merchant Code',
                'type'        => 'text',
                'required'    => true,
            ],
            [
                'name'         => 'api_key',
                'friendlyName' => 'API Key',
                'type'        => 'text',
                'required'    => true,
            ],
            [
                'name'         => 'callback_url',
                'friendlyName' => 'Callback URL',
                'type'        => 'text',
                'required'    => true,
            ],
            [
                'name'         => 'environment',
                'friendlyName' => 'Environment (sandbox/production)',
                'type'        => 'text',
                'required'    => true,
            ],
        ];
    }

    public function pay($invoice, $total)
    {
	$orderId = $invoice->id;
	$products = $invoice->items; 
	$environment = $this->config('environment');
	
        $url = 'https://api-sandbox.duitku.com/api/merchant/createInvoice';
        if ($environment === 'production') {
            $url = 'https://api-prod.duitku.com/api/merchant/createInvoice';
        }

        $merchantCode = $this->config('merchant_code');
        $apiKey = $this->config('api_key');
        $callbackUrl = $this->config('callback_url');
        $returnUrl = route('invoices.show', $orderId);

        $description = 'Products: ';
        foreach ($products as $product) {
            $description .= $product->name . ' x' . $product->quantity . ', ';
        }
        $description = rtrim($description, ', ');

        $timestamp = round(microtime(true) * 1000);
        $signature = hash('sha256', $merchantCode . $timestamp . $apiKey);

        $params = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => (int) ceil($total),
            'merchantOrderId' => (string) $orderId,
            'productDetails' => $description,
            'merchantUserInfo' => 'client@example.com',
            'email' => 'client@example.com',
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
        ];

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'x-duitku-signature' => $signature,
            'x-duitku-timestamp' => $timestamp,
            'x-duitku-merchantcode' => $merchantCode,
        ];

        $response = Http::withHeaders($headers)->post($url, $params);

        if ($response->successful()) {
            $responseData = $response->json();
            if ($responseData['statusCode'] == '00') {
                return $responseData['paymentUrl'];
            } else {
                Log::error('Duitku Payment Error', ['response' => $responseData]);
                return false;
            }
        } else {
            Log::error('Duitku Payment Error', ['response' => $response->body()]);
            return false;
        }
    }

    public function webhook(Request $request)
    {
        $apiKey = $this->config('api_key');

        $merchantCode = $request->input('merchantCode');
        $amount = $request->input('amount');
        $merchantOrderId = $request->input('merchantOrderId');
        $signature = $request->input('signature');
        $resultCode = $request->input('resultCode');

        Log::debug('Duitku Webhook Data', $request->all());

        if (!$merchantCode || !$amount || !$merchantOrderId || !$signature) {
            Log::error('Missing parameters', $request->all());
            return response()->json(['success' => false, 'message' => 'Missing parameters'], 400);
        }

        $calculatedSignature = md5($merchantCode . $amount . $merchantOrderId . $apiKey);

        Log::debug('Duitku Webhook Signature Verification', [
            'received_signature' => $signature,
            'calculated_signature' => $calculatedSignature,
        ]);

        if ($signature !== $calculatedSignature) {
            Log::error('Invalid signature', [
                'received_signature' => $signature,
                'calculated_signature' => $calculatedSignature,
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        if ($resultCode === '00') {
		$transaction = ExtensionHelper::addPayment($merchantOrderId, 'Duitku', $amount);
		
		// Ensure invoice status is updated to paid if remaining <= 0 or within tolerance (0.21 for rounding)
		$invoice = $transaction->invoice;
		if ($invoice->remaining <= 0.21 && $invoice->status !== 'paid') {
			$invoice->update(['status' => 'paid']);
			Log::info('Duitku: Invoice status updated to paid', [
				'invoice_id' => $invoice->id,
				'invoice_number' => $invoice->number,
				'remaining' => $invoice->remaining,
				'amount_paid' => $amount,
			]);
		}
		
            return response()->json(['success' => true]);
        } elseif (in_array($resultCode, ['01', '02'])) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid status'], 400);
        }
    }
}

