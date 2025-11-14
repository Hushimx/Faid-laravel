<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @vite('resources/js/app.js')
</head>

<body>

    <h1>test</h1>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM fully loaded and parsed');
            Echo.channel('ticket-chat')
                .listen('message.sent', e => {
                    alert('test');
                    console.log('Event received', e.message);
                });
        })
    </script>

</body>

</html>
