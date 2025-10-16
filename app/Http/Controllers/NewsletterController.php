<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    /**
     * Show the newsletter subscription form.
     */
    public function index()
    {
        return view('newsletter.index');
    }

    /**
     * Subscribe to the newsletter.
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255|unique:newsletter_subscribers,email',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|size:2',
            'interests' => 'nullable|array',
            'interests.*' => 'string|in:cities,cost_calculator,deals,articles,coworking_spaces',
            'source' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create subscription
        $subscriber = NewsletterSubscriber::create([
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'country_code' => $request->country_code,
            'interests' => $request->interests ?? [],
            'status' => 'active',
            'source' => $request->source ?? 'website',
            'utm_data' => $this->extractUtmData($request),
            'subscribed_at' => now(),
        ]);

        // Send welcome email
        $this->sendWelcomeEmail($subscriber);

        return redirect()->back()
            ->with('success', 'Thank you for subscribing! Check your email for a welcome message.');
    }

    /**
     * Unsubscribe from the newsletter.
     */
    public function unsubscribe(Request $request, $token = null)
    {
        if ($token) {
            // Unsubscribe via token (from email link)
            $subscriber = NewsletterSubscriber::where('email', $request->email)
                ->where('status', 'active')
                ->first();

            if ($subscriber) {
                $subscriber->unsubscribe();

                return view('newsletter.unsubscribed', compact('subscriber'));
            }
        }

        // Show unsubscribe form
        return view('newsletter.unsubscribe');
    }

    /**
     * Process unsubscribe form submission.
     */
    public function processUnsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:newsletter_subscribers,email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $subscriber = NewsletterSubscriber::where('email', $request->email)
            ->where('status', 'active')
            ->first();

        if ($subscriber) {
            $subscriber->unsubscribe();

            return view('newsletter.unsubscribed', compact('subscriber'));
        }

        return redirect()->back()
            ->with('error', 'Email not found or already unsubscribed.');
    }

    /**
     * Extract UTM data from request.
     */
    private function extractUtmData(Request $request)
    {
        $utmData = [];
        $utmParams = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];

        foreach ($utmParams as $param) {
            if ($request->has($param)) {
                $utmData[$param] = $request->input($param);
            }
        }

        return $utmData;
    }

    /**
     * Send welcome email to new subscriber.
     */
    private function sendWelcomeEmail(NewsletterSubscriber $subscriber)
    {
        try {
            Mail::send('emails.newsletter.welcome', compact('subscriber'), function ($message) use ($subscriber) {
                $message->to($subscriber->email, $subscriber->first_name)
                    ->subject('Welcome to Digital Nomad Guide Newsletter!');
            });
        } catch (\Exception $e) {
            // Log error but don't fail the subscription
            \Log::error('Failed to send welcome email: '.$e->getMessage());
        }
    }

    /**
     * Get subscription statistics for admin.
     */
    public function stats()
    {
        $stats = [
            'total_subscribers' => NewsletterSubscriber::count(),
            'active_subscribers' => NewsletterSubscriber::active()->count(),
            'unsubscribed' => NewsletterSubscriber::where('status', 'unsubscribed')->count(),
            'bounced' => NewsletterSubscriber::where('status', 'bounced')->count(),
            'subscribers_this_month' => NewsletterSubscriber::whereMonth('subscribed_at', now()->month)->count(),
            'top_countries' => NewsletterSubscriber::active()
                ->selectRaw('country_code, COUNT(*) as count')
                ->groupBy('country_code')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }
}
