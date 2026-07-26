<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipApplicationRequest;
use App\Mail\MembershipAdminMail;
use App\Mail\MembershipConfirmationMail;
use App\Models\MembershipApplication;
use App\Models\StoreSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MembershipApplicationController extends Controller
{
    public function store(MembershipApplicationRequest $request)
    {
        $application = MembershipApplication::create([
            'full_name' => $request->string('full_name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->string('phone')->toString(),
            'health_goals' => $request->string('health_goals')->toString(),
            'status' => 'Pending',
        ]);

        $mailErrors = [];

        try {
            Log::info('Sending membership confirmation', [
                'email' => $application->email,
                'application_id' => $application->id,
            ]);
            Mail::to($application->email)->send(new MembershipConfirmationMail($application->full_name));
            Log::info('Customer confirmation email sent', [
                'email' => $application->email,
                'application_id' => $application->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Membership confirmation email failed', [
                'email' => $application->email,
                'application_id' => $application->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $mailErrors[] = $e;
        }

        $adminEmail = StoreSetting::query()->value('support_email') ?: config('mail.from.address');
        if ($adminEmail) {
            try {
                Log::info('Sending membership admin notification', [
                    'email' => $adminEmail,
                    'application_id' => $application->id,
                ]);
                Mail::to($adminEmail)->send(new MembershipAdminMail([
                    'id' => $application->id,
                    'full_name' => $application->full_name,
                    'email' => $application->email,
                    'phone' => $application->phone,
                    'health_goals' => $application->health_goals,
                    'submitted_at' => Carbon::now()->toDateTimeString(),
                ]));
                Log::info('Admin notification email sent', [
                    'email' => $adminEmail,
                    'application_id' => $application->id,
                ]);
            } catch (\Throwable $e) {
                Log::error('Membership admin notification failed', [
                    'email' => $adminEmail,
                    'application_id' => $application->id,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $mailErrors[] = $e;
            }
        }

        if (! empty($mailErrors)) {
            throw $mailErrors[0];
        }

        return response()->json([
            'success' => true,
            'message' => 'Membership application submitted successfully.',
            'data' => [
                'id' => $application->id,
                'status' => $application->status,
            ],
        ]);
    }
}
