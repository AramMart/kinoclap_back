Hello {{ $first_name.' '.$last_name.' '.($middle_name || '') }} <br><br>

Welcome to {{$welcome_to}}.<br><br>

To verify your site please visit to
<a href="{{ $url }}">Verify Email</a>

Thank You,<br>
{{$welcome_to}}