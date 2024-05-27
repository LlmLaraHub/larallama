<x-mail::message>
# Your Magic SignIn Link

Just click the link below to sign in, if you did not request this email just ignore it.


<x-mail::button :url="$url">
Login
</x-mail::button>

Thanks from,<br>
{{ $app_name }}
</x-mail::message>
