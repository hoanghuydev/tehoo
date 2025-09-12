<x-mail::message>
# Introduction
Welcome to our platform, {{ $user->name }}!
The body of your message.

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
