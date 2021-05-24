@component('mail::message')
{{$message}}

@component('mail::button', ['url' => $link])
Peržiūrėti pranešima
@endcomponent

Pagarbiai,<br>
{{ config('app.name') }}
@endcomponent
