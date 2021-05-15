<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('Notification') }}</title>
</head>
<body>
    <h1>{{ __('New Reply to your post') }}</h1>
    <br>
    {{ __('Hello') }} {{ $postUsername }}!
    <p>{{ $replyUsername }} {{ __('replied to your post:') }}</p>
    <p>{{ __('Post-Title') }}: {{ $postTitle }}</p> 
    <p>{{ __('Post-Content') }}: {{ $postContent }}</p>
    <p>{{ __('Reply') }}:</p>
    <pre>{{ $replyContent }}</pre>
    <p>{{ __('We wish you a nice day.') }}</p>
</body>
</html>