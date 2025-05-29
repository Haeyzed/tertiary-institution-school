<x-mail::message>
# Reset Your Password

Hello {{ $notifiable->name ?? 'User' }},

You are receiving this email because we received a password reset request for your account.

<x-mail::button :url="$url" color="primary">
Reset Password
</x-mail::button>

This password reset link will expire in {{ $count }} minutes.

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}

---

If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:

{{ $url }}
</x-mail::message>
