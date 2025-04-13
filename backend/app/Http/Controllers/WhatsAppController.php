<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class WhatsAppController extends Controller
{
    public function sendMessage(Request $request){
        $response = Http::post('http://localhost:3000/sendMessage', [
            'number' => $request->number,
            'message' => $request->message
        ]);

        return $response->json();
    }
}
