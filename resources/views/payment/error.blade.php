<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Error - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .error-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .error-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center;
        }
        .error-header {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            padding: 40px 30px;
        }
        .error-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .error-body {
            padding: 40px 30px;
        }
        .error-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
        }
        .btn-secondary {
            border-radius: 25px;
            padding: 12px 30px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <!-- Error Header -->
            <div class="error-header">
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="mb-2">Payment Error</h2>
                <p class="mb-0 opacity-75">Something went wrong with your payment</p>
            </div>

            <!-- Error Body -->
            <div class="error-body">
                <!-- Error Message -->
                <div class="alert alert-danger">
                    <h6><i class="fas fa-bug me-2"></i>Error Details:</h6>
                    <p class="mb-0">{{ $error ?? 'An unexpected error occurred while processing your payment.' }}</p>
                </div>

                @if(isset($bill_code))
                <!-- Error Details -->
                <div class="error-details">
                    <div class="row">
                        <div class="col-sm-4"><strong>Bill Code:</strong></div>
                        <div class="col-sm-8"><span class="font-monospace">{{ $bill_code }}</span></div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-sm-4"><strong>Error Time:</strong></div>
                        <div class="col-sm-8">{{ now()->format('d M Y, H:i:s') }}</div>
                    </div>
                    @if(isset($transaction))
                    <div class="row mt-2">
                        <div class="col-sm-4"><strong>Transaction ID:</strong></div>
                        <div class="col-sm-8"><span class="font-monospace">{{ $transaction->id }}</span></div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-sm-4"><strong>Amount:</strong></div>
                        <div class="col-sm-8">{{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}</div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- What to do next -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb me-2"></i>What you can do:</h6>
                    <ul class="mb-0 small text-start">
                        <li>Try refreshing the page and attempting the payment again</li>
                        <li>Check your internet connection and try again</li>
                        <li>Try using a different payment method</li>
                        <li>Contact our support team if the problem persists</li>
                        <li>Wait a few minutes and retry the transaction</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    @if(isset($bill_code))
                    <button type="button" class="btn btn-primary" onclick="retryPayment()">
                        <i class="fas fa-redo me-2"></i>
                        Try Again
                    </button>
                    @endif
                    <button type="button" class="btn btn-outline-primary" onclick="contactSupport()">
                        <i class="fas fa-headset me-2"></i>
                        Get Help
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="goBack()">
                        <i class="fas fa-arrow-left me-2"></i>
                        Go Back
                    </button>
                </div>

                <!-- Technical Details (for debugging) -->
                @if(config('app.debug') && isset($technical_details))
                <div class="mt-4">
                    <details>
                        <summary class="text-muted small">Technical Details (Debug Mode)</summary>
                        <pre class="mt-2 p-2 bg-light border rounded small">{{ json_encode($technical_details, JSON_PRETTY_PRINT) }}</pre>
                    </details>
                </div>
                @endif

                <!-- Support Contact -->
                <div class="mt-4 text-muted">
                    <small>
                        <i class="fas fa-envelope me-1"></i>
                        For immediate assistance, email us at 
                        <a href="mailto:support@example.com">support@example.com</a>
                        <br>
                        <i class="fas fa-clock me-1"></i>
                        Support hours: Monday - Friday, 9 AM - 6 PM (GMT+8)
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        @if(isset($bill_code))
        const billCode = '{{ $bill_code }}';
        @else
        const billCode = null;
        @endif

        function retryPayment() {
            if (billCode) {
                // Redirect back to payment page
                window.location.href = `/payment/${billCode}`;
            } else {
                // Go back to previous page
                goBack();
            }
        }

        function contactSupport() {
            const subject = encodeURIComponent('Payment Error - Need Assistance');
            let body = encodeURIComponent('Hello Support Team,\n\nI encountered an error while trying to make a payment.\n\n');
            
            @if(isset($bill_code))
            body += encodeURIComponent('Bill Code: {{ $bill_code }}\n');
            @endif
            
            @if(isset($transaction))
            body += encodeURIComponent('Transaction ID: {{ $transaction->id }}\n');
            body += encodeURIComponent('Amount: {{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}\n');
            @endif
            
            body += encodeURIComponent('Error: {{ $error ?? "Payment processing error" }}\n');
            body += encodeURIComponent('Time: {{ now()->format("d M Y, H:i:s") }}\n\n');
            body += encodeURIComponent('Please help me resolve this issue.\n\nThank you.');

            const mailtoUrl = `mailto:support@example.com?subject=${subject}&body=${body}`;
            window.location.href = mailtoUrl;
        }

        function goBack() {
            if (window.parent !== window) {
                // We're in an iframe, send message to parent
                window.parent.postMessage({
                    type: 'payment_error',
                    @if(isset($bill_code))
                    bill_code: '{{ $bill_code }}',
                    @endif
                    error: '{{ $error ?? "Payment processing error" }}'
                }, '*');
            } else {
                // Try to go back in history, or redirect to homepage
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = '/';
                }
            }
        }

        // Notify parent window of error
        if (window.parent !== window) {
            window.parent.postMessage({
                type: 'payment_error',
                @if(isset($bill_code))
                bill_code: '{{ $bill_code }}',
                @endif
                error: '{{ $error ?? "Payment processing error" }}'
            }, '*');
        }

        // Auto-return after 60 seconds if in iframe
        if (window.parent !== window) {
            setTimeout(() => {
                goBack();
            }, 60000);
        }
    </script>
</body>
</html>
