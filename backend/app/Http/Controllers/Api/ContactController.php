<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    use ApiResponse;

    /**
     * POST /api/v1/contact
     *
     * Rate-limited to 5 requests per minute per IP (configured in routes).
     * Sends an email to the site owner via Gmail SMTP.
     */
    public function store(ContactRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            Mail::send([], [], function ($message) use ($data) {
                $message
                    ->to(config('mail.from.address'))
                    ->replyTo($data['email'], $data['name'])
                    ->subject('New contact: ' . $data['name'])
                    ->html(
                        '<div style="font-family:sans-serif;color:#333;max-width:560px">'
                        . '<h2 style="margin:0 0 16px">New message from your portfolio</h2>'
                        . '<p><strong>Name:</strong> ' . e($data['name']) . '</p>'
                        . '<p><strong>Email:</strong> <a href="mailto:' . e($data['email']) . '">' . e($data['email']) . '</a></p>'
                        . '<hr style="border:none;border-top:1px solid #eee;margin:16px 0">'
                        . '<p style="white-space:pre-line">' . e($data['message']) . '</p>'
                        . '</div>'
                    );
            });
        } catch (\Exception $e) {
            // Log the error but don't expose details to the client
            \Illuminate\Support\Facades\Log::error('Contact mail failed: ' . $e->getMessage());
            return $this->error('Failed to send message. Please try again later.', 500);
        }

        return $this->success(null, 'Message sent successfully');
    }
}