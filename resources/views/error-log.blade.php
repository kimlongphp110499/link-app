<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Log</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .log-entry {
            margin: 10px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .log-short {
            font-weight: bold;
            cursor: pointer;
            color: #007bff;
        }
        .log-details {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background-color: #fff;
            border-top: 1px solid #ddd;
            white-space: pre-wrap;
        }
        .log-details.show {
            display: block;
        }
        .timestamp {
            font-size: 12px;
            color: #888;
        }
        .level {
            font-size: 14px;
            font-weight: bold;
            color: #d9534f;
        }
    </style>
</head>
<body>
    <h1>Error Log</h1>
    @if ($error)
        <p style="color: red;">{{ $error }}</p>
    @else
        @foreach ($logs as $index => $log)
            <div class="log-entry">
                <div class="log-short" onclick="toggleDetails({{ $index }})">
                    <span class="timestamp">[{{ $log['timestamp'] }}]</span>
                    <span class="level">{{ $log['level'] }}</span>:
                    {{ $log['message'] }}
                </div>
                <div id="log-details-{{ $index }}" class="log-details">
                    {{ $log['details'] }}
                </div>
            </div>
        @endforeach
    @endif

    <script>
        function toggleDetails(index) {
            const details = document.getElementById(`log-details-${index}`);
            details.classList.toggle('show');
        }
    </script>
</body>
</html>