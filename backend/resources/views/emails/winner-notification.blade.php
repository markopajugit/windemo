<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You Won!</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .trophy {
            font-size: 64px;
            text-align: center;
            margin: 20px 0;
        }
        h1 {
            color: #10b981;
            text-align: center;
            margin-bottom: 20px;
        }
        .lottery-info {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .lottery-title {
            font-size: 20px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 10px;
        }
        .ticket-number {
            display: inline-block;
            background-color: #6366f1;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #64748b;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">win24</div>
        </div>
        
        <div class="trophy">🏆</div>
        
        <h1>Congratulations, {{ $userName }}!</h1>
        
        <p style="text-align: center; font-size: 18px;">
            You are the lucky winner of our lottery!
        </p>
        
        <div class="lottery-info">
            <div class="lottery-title">{{ $lottery->title }}</div>
            <p><strong>Product Value:</strong> €{{ number_format($lottery->product_value, 2) }}</p>
            <p><strong>Your Winning Ticket:</strong> <span class="ticket-number">#{{ $ticket->ticket_number }}</span></p>
        </div>
        
        <p>
            The lottery creator will be in touch with you shortly to arrange delivery of your prize.
        </p>
        
        <p>
            Thank you for participating in win24!
        </p>
        
        <div class="footer">
            <p>This is an automated email from win24.</p>
            <p>&copy; {{ date('Y') }} win24. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

