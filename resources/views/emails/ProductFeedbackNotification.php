<!DOCTYPE html>
<html>
<head>
    <title>New Product Feedback</title>
</head>
<body>
    <h2>New Feedback Received</h2>
    
    <p>Hello {{ $data['seller_name'] }},</p>
    
    <p>You have received new feedback for your product <strong>{{ $data['product_name'] }}</strong>.</p>
    
    <div style="margin: 20px 0; padding: 15px; border: 1px solid #eee; border-radius: 5px;">
        <p><strong>Rating:</strong> {{ $data['rating'] }}/5</p>
        
        @if(isset($data['feedback']) && !empty($data['feedback']))
            <p><strong>Comments:</strong> {{ $data['feedback'] }}</p>
        @else
            <p><strong>Comments:</strong> No written feedback provided.</p>
        @endif
        
        <p><strong>From:</strong> {{ $data['buyer_name'] }}</p>
    </div>
    
    <p>
        <a href="{{ $data['product_link'] }}" style="display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">
            View Product Details
        </a>
    </p>
    
    <p>Thank you for using our platform!</p>
    
    <p>Best regards,<br>
    The Support Team</p>
</body>
</html>