<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><title>{{ config('app.name') }}</title></head>
<body style="font-family: Tahoma, Arial, sans-serif; background:#f6f6f6; padding:24px;">
  <div style="max-width:560px; margin:auto; background:#fff; border-radius:8px; padding:24px;">
    <h2 style="margin-top:0;">{{ __('Welcome :name', ['name' => $member->name]) }}</h2>
    <p>{{ __(':inviter invited you to join :app. Set your password to activate your account.', ['inviter' => $invitedBy, 'app' => config('app.name')]) }}</p>
    <p>{{ __('Your username') }}: <strong dir="ltr">{{ $member->username }}</strong></p>
    <p style="text-align:center; margin:32px 0;">
      <a href="{{ $activationUrl }}" style="background:#7367f0; color:#fff; padding:12px 24px; border-radius:6px; text-decoration:none;">{{ __('Activate account') }}</a>
    </p>
    <p style="color:#888; font-size:12px;">{{ __('This link expires in :hours hours.', ['hours' => config('users.invitation_ttl_hours')]) }}</p>
    <p style="color:#888; font-size:12px; word-break:break-all;">{{ $activationUrl }}</p>
  </div>
</body>
</html>
