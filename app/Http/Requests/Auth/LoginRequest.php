<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request using username instead of email.
     * Implements per-account lockout and failed-attempts tracking.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotLocked();

        $credentials = ['username' => $this->input('username'), 'password' => $this->input('password')];

        $user = User::where('username', $this->input('username'))->first();

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            // failed attempt: increment counter if user exists
            if ($user) {
                $user->failed_login_attempts = (int) $user->failed_login_attempts + 1;
                if ($user->failed_login_attempts >= 5) {
                    $user->account_status = 'locked';
                    $user->locked_until = Carbon::now()->addMinutes(15);
                }
                $user->save();
            }

            throw ValidationException::withMessages([
                'username' => __('These credentials do not match our records.'),
            ]);
        }

        // successful login: reset counters
        if ($user) {
            $user->failed_login_attempts = 0;
            $user->locked_until = null;
            $user->account_status = 'active';
            $user->save();
        }
    }

    /**
     * Check if the account is currently locked and throw a validation exception if so.
     * If the lock has expired, unlock the account automatically.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function ensureIsNotLocked(): void
    {
        $username = $this->input('username');

        if (! $username) {
            return;
        }

        $user = User::where('username', $username)->first();

        if (! $user) {
            return;
        }

        // If locked_until has passed, automatically unlock
        if ($user->locked_until) {
            $lockedUntil = Carbon::parse($user->locked_until);
            if ($lockedUntil->isPast()) {
                $user->account_status = 'active';
                $user->failed_login_attempts = 0;
                $user->locked_until = null;
                $user->save();
                return;
            }
        }

        if ($user->account_status === 'locked' && $user->locked_until) {
            $lockedUntil = Carbon::parse($user->locked_until);
            $diff = $lockedUntil->diffForHumans(null, Carbon::DIFF_RELATIVE_TO_NOW | Carbon::ONE_WORD);
            throw ValidationException::withMessages([
                'username' => "Account is locked. Please try again in {$diff}.",
            ]);
        }
    }
}
