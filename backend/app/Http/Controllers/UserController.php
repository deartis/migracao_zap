<?php

namespace App\Http\Controllers;

class UserController{
    public function canSend(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'send' => true, // Temporário para testes
                'test_debug' => [
                    'user_id' => $user->id,
                    'auth' => $user ? 'authenticated' : 'not authenticated'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
