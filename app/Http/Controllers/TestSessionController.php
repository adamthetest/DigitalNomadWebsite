<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestSessionController extends Controller
{
    public function testSession(Request $request)
    {
        // Start session manually
        $request->session()->start();

        // Set a test value
        $request->session()->put('test_key', 'test_value');

        // Get CSRF token
        $csrfToken = csrf_token();

        return response()->json([
            'session_id' => $request->session()->getId(),
            'test_value' => $request->session()->get('test_key'),
            'csrf_token' => $csrfToken,
            'session_started' => $request->session()->isStarted(),
            'session_driver' => config('session.driver'),
            'session_secure' => config('session.secure'),
        ]);
    }

    public function testSessionPost(Request $request)
    {
        // Test CSRF token validation
        $request->validate([
            'test_data' => 'required|string',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'CSRF token validation passed',
            'received_data' => $request->input('test_data'),
        ]);
    }
}
