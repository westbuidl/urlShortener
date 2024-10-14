<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{
    private $apiKey = 'YOUR_EXCHANGE_RATE_API_KEY';
    private $baseUrl = 'https://v6.exchangerate-api.com/v6/';

    public function getExchangeRates(Request $request)
    {
        $baseCurrency = $request->input('base_currency', 'USD');
        
        try {
            $response = Http::get($this->baseUrl . $this->apiKey . "/latest/" . $baseCurrency);
            
            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'base_currency' => $baseCurrency,
                    'rates' => $data['conversion_rates']
                ]);
            } else {
                return response()->json(['error' => 'Failed to fetch exchange rates'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}