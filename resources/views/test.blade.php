<!DOCTYPE html>
<html>
<head>
    <title>Test</title>
</head>
<body>
    <h1>Test Page</h1>
    <p>This is a test page to verify if the Blade engine is working correctly.</p>
    
    @if(true)
        <p>If statement is working</p>
    @endif
    
    @foreach([1, 2, 3] as $number)
        <p>Number: {{ $number }}</p>
    @endforeach
</body>
</html>
