<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .result-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .result-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center;
        }
        .result-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 40px 30px;
        }
        .success-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        .result-body {
            padding: 40px 30px;
        }
        .amount-display {
            font-size: 2.5rem;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 20px;
        }
        .transaction-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .detail-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
        }
        .confetti {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <!-- Confetti Animation -->
    <div class="confetti" id="confetti"></div>

    <div class="result-container">
        <div class="result-card">
            <!-- Success Header -->
            <div class="result-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="mb-2">Payment Successful!</h2>
                <p class="mb-0 opacity-75">{{ $message ?? 'Your payment has been processed successfully' }}</p>
            </div>

            <!-- Result Body -->
            <div class="result-body">
                @if(isset($transaction))
                <!-- Amount Display -->
                <div class="amount-display">
                    {{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}
                </div>

                <!-- Transaction Details -->
                <div class="transaction-details">
                    <div class="detail-row">
                        <span><strong>Transaction ID:</strong></span>
                        <span class="font-monospace">{{ $transaction->id }}</span>
                    </div>
                    <div class="detail-row">
                        <span><strong>Bill Code:</strong></span>
                        <span class="font-monospace">{{ $transaction->toyyibpay_billcode }}</span>
                    </div>
                    @if($transaction->ghl_transaction_id)
                    <div class="detail-row">
                        <span><strong>GHL Transaction ID:</strong></span>
                        <span class="font-monospace">{{ $transaction->ghl_transaction_id }}</span>
                    </div>
                    @endif
                    @if($transaction->customer_name)
                    <div class="detail-row">
                        <span><strong>Customer:</strong></span>
                        <span>{{ $transaction->customer_name }}</span>
                    </div>
                    @endif
                    @if($transaction->customer_email)
                    <div class="detail-row">
                        <span><strong>Email:</strong></span>
                        <span>{{ $transaction->customer_email }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span><strong>Payment Date:</strong></span>
                        <span>{{ $transaction->updated_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="detail-row">
                        <span><strong>Status:</strong></span>
                        <span class="badge bg-success">{{ ucfirst($transaction->status) }}</span>
                    </div>
                </div>
                @endif

                <!-- Success Message -->
                <div class="alert alert-success">
                    <h6><i class="fas fa-info-circle me-2"></i>What happens next?</h6>
                    <ul class="mb-0 small text-start">
                        <li>You will receive a confirmation email shortly</li>
                        <li>Your order will be processed within 24 hours</li>
                        <li>You can track your order status in your account</li>
                        <li>For any questions, please contact our support team</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <button type="button" class="btn btn-outline-primary" onclick="printReceipt()">
                        <i class="fas fa-print me-2"></i>
                        Print Receipt
                    </button>
                    <button type="button" class="btn btn-primary" onclick="returnToStore()">
                        <i class="fas fa-arrow-left me-2"></i>
                        Return to Store
                    </button>
                </div>

                <!-- Support Info -->
                <div class="mt-4 text-muted">
                    <small>
                        <i class="fas fa-headset me-1"></i>
                        Need help? Contact support at 
                        <a href="mailto:support@example.com">support@example.com</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confetti animation
        function createConfetti() {
            const confetti = document.getElementById('confetti');
            const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#f9ca24', '#6c5ce7'];
            
            for (let i = 0; i < 50; i++) {
                const piece = document.createElement('div');
                piece.style.position = 'absolute';
                piece.style.width = '10px';
                piece.style.height = '10px';
                piece.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                piece.style.left = Math.random() * 100 + '%';
                piece.style.animationDuration = Math.random() * 3 + 2 + 's';
                piece.style.animationDelay = Math.random() * 2 + 's';
                piece.style.animation = 'fall linear infinite';
                confetti.appendChild(piece);
            }

            // Add CSS for falling animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fall {
                    to {
                        transform: translateY(100vh) rotate(360deg);
                    }
                }
            `;
            document.head.appendChild(style);

            // Remove confetti after 5 seconds
            setTimeout(() => {
                confetti.innerHTML = '';
            }, 5000);
        }

        function printReceipt() {
            window.print();
        }

        function returnToStore() {
            // Try to close the window/iframe or redirect
            if (window.parent !== window) {
                // We're in an iframe, send message to parent
                window.parent.postMessage({
                    type: 'payment_success',
                    @if(isset($transaction))
                    transaction_id: '{{ $transaction->id }}',
                    bill_code: '{{ $transaction->toyyibpay_billcode }}',
                    amount: {{ $transaction->amount }}
                    @endif
                }, '*');
            } else {
                // Redirect to homepage or provided return URL
                const returnUrl = new URLSearchParams(window.location.search).get('return_url');
                window.location.href = returnUrl || '/';
            }
        }

        // Notify parent window of successful payment
        if (window.parent !== window) {
            window.parent.postMessage({
                type: 'payment_completed',
                status: 'success',
                @if(isset($transaction))
                transaction_id: '{{ $transaction->id }}',
                bill_code: '{{ $transaction->toyyibpay_billcode }}',
                amount: {{ $transaction->amount }}
                @endif
            }, '*');
        }

        // Start confetti animation
        createConfetti();

        // Auto-return after 30 seconds if in iframe
        if (window.parent !== window) {
            setTimeout(() => {
                returnToStore();
            }, 30000);
        }
    </script>
</body>
</html>
