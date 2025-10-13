<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Digital Nomad Guide!</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #374151;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #3B82F6, #1D4ED8);
            color: white;
            padding: 40px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: white;
            padding: 40px 20px;
            border: 1px solid #E5E7EB;
            border-top: none;
        }
        .footer {
            background: #F9FAFB;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            border: 1px solid #E5E7EB;
            border-top: none;
            font-size: 14px;
            color: #6B7280;
        }
        .button {
            display: inline-block;
            background: #3B82F6;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 5px;
        }
        .button:hover {
            background: #2563EB;
        }
        .feature {
            margin: 20px 0;
            padding: 20px;
            background: #F8FAFC;
            border-radius: 6px;
            border-left: 4px solid #3B82F6;
        }
        .emoji {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to Digital Nomad Guide! 🌍</h1>
        <p>Thanks for joining our community of digital nomads</p>
    </div>
    
    <div class="content">
        <h2>Hi {{ $subscriber->first_name ?? 'there' }}! 👋</h2>
        
        <p>Welcome to the Digital Nomad Guide newsletter! We're thrilled to have you join our community of remote workers, freelancers, and digital nomads exploring the world.</p>
        
        <div class="feature">
            <h3><span class="emoji">🎯</span>What you'll get:</h3>
            <ul>
                <li><strong>Weekly destination guides</strong> - Discover new cities perfect for digital nomads</li>
                <li><strong>Cost of living insights</strong> - Budget planning tips and real cost data</li>
                <li><strong>Exclusive deals</strong> - Special discounts on accommodation, coworking spaces, and travel</li>
                <li><strong>Community stories</strong> - Real experiences from fellow digital nomads</li>
                <li><strong>Productivity tips</strong> - Remote work best practices and tools</li>
            </ul>
        </div>
        
        <div class="feature">
            <h3><span class="emoji">🚀</span>Get started:</h3>
            <p>Explore our website to discover amazing destinations, calculate your budget, and find exclusive deals:</p>
            <a href="{{ route('cities.index') }}" class="button">Explore Cities</a>
            <a href="{{ route('calculator.index') }}" class="button">Cost Calculator</a>
            <a href="{{ route('deals.index') }}" class="button">Exclusive Deals</a>
        </div>
        
        @if(!empty($subscriber->interests))
        <div class="feature">
            <h3><span class="emoji">❤️</span>Based on your interests:</h3>
            <p>We'll prioritize content about: 
                @foreach($subscriber->interests as $interest)
                    @switch($interest)
                        @case('cities')
                            🏙️ Cities & Destinations
                            @break
                        @case('cost_calculator')
                            💰 Cost Calculator
                            @break
                        @case('deals')
                            🎯 Exclusive Deals
                            @break
                        @case('articles')
                            📝 Articles & Tips
                            @break
                        @case('coworking_spaces')
                            🏢 Coworking Spaces
                            @break
                    @endswitch
                    @if(!$loop->last), @endif
                @endforeach
            </p>
        </div>
        @endif
        
        <p><strong>Next steps:</strong></p>
        <ol>
            <li>Check your spam folder and add us to your contacts</li>
            <li>Follow us on social media for daily updates</li>
            <li>Join our community discussions</li>
            <li>Share your digital nomad experiences with us!</li>
        </ol>
        
        <p>If you have any questions or suggestions, just reply to this email. We'd love to hear from you!</p>
        
        <p>Happy travels!<br>
        The Digital Nomad Guide Team</p>
    </div>
    
    <div class="footer">
        <p>You're receiving this email because you subscribed to our newsletter.</p>
        <p>
            <a href="{{ route('newsletter.unsubscribe') }}?email={{ $subscriber->email }}">Unsubscribe</a> | 
            <a href="{{ route('home') }}">Visit Website</a>
        </p>
        <p>© {{ date('Y') }} Digital Nomad Guide. All rights reserved.</p>
    </div>
</body>
</html>
