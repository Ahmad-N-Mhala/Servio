<!DOCTYPE html>
<html>

<head>
    <title>New Registration Interest</title>
</head>

<body>
    <h2>New Registration Interest Submission</h2>
    <p><strong>Plan:</strong> {{ $data['plan_name'] }}</p>

    <h3>Contact Details:</h3>
    <ul>
        <li><strong>Name:</strong> {{ $data['name'] }}</li>
        <li><strong>Email:</strong> {{ $data['email'] }}</li>
        <li><strong>Phone:</strong> {{ $data['phone'] }}</li>
        <li><strong>Restaurant Name:</strong> {{ $data['restaurant_name'] }}</li>
    </ul>

    <p><strong>Message:</strong></p>
    <p>{{ $data['message'] ?? 'No message provided.' }}</p>
</body>

</html>