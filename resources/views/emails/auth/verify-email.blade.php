<x-mail::message>
# Verify Your Email Address

Hello {{ $notifiable->name ?? 'User' }},

Thank you for creating an account with {{ config('app.name') }}! To complete your registration, please verify your email address by clicking the button below.

<x-mail::button :url="$url" color="success">
Verify Email Address
</x-mail::button>

If you did not create an account, no further action is required.

Thanks,<br>
{{ config('app.name') }}

---

If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:

{{ $url }}
</x-mail::message>
