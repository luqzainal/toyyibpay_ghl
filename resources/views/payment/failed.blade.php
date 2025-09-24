<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
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
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
            padding: 40px 30px;
        }
        .failed-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .result-body {
            padding: 40px 30px;
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
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="result-card">
            <!-- Failed Header -->
            <div class="result-header">
                <div class="failed-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h2 class="mb-2">Payment Failed</h2>
                <p class="mb-0 opacity-75">{{ $message ?? 'Your payment could not be processed' }}</p>
            </div>

            <!-- Result Body -->
            <div class="result-body">
                @if(isset($transaction))
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
                    <div class="detail-row">
                        <span><strong>Amount:</strong></span>
                        <span>{{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}</span>
                    </div>
                    @if($transaction->customer_name)
                    <div class="detail-row">
                        <span><strong>Customer:</strong></span>
                        <span>{{ $transaction->customer_name }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span><strong>Attempt Date:</strong></span>
                        <span>{{ $transaction->updated_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="detail-row">
                        <span><strong>Status:</strong></span>
                        <span class="badge bg-danger">{{ ucfirst($transaction->status) }}</span>
                    </div>
                </div>
                @endif

                <!-- Failure Reasons -->
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Common reasons for payment failure:</h6>
                    <ul class="mb-0 small text-start">
                        <li>Insufficient funds in your account</li>
                        <li>Card has expired or been blocked</li>
                        <li>Incorrect card details entered</li>
                        <li>Bank declined the transaction</li>
                        <li>Network timeout or connection issues</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <button type="button" class="btn btn-danger" onclick="retryPayment()">
                        <i class="fas fa-redo me-2"></i>
                        Try Again
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="contactSupport()">
                        <i class="fas fa-headset me-2"></i>
                        Contact Support
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="returnToStore()">
                        <i class="fas fa-arrow-left me-2"></i>
                        Return to Store
                    </button>
                </div>

                <!-- Alternative Payment Methods -->
                <div class="mt-4">
                    <h6 class="text-muted">Try alternative payment methods:</h6>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="useAlternativeMethod('fpx')">
                            <i class="fas fa-university me-1"></i>
                            FPX Banking
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="useAlternativeMethod('card')">
                            <i class="fas fa-credit-card me-1"></i>
                            Different Card
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="useAlternativeMethod('ewallet')">
                            <i class="fas fa-mobile-alt me-1"></i>
                            E-Wallet
                        </button>
                    </div>
                </div>

                <!-- Support Info -->
                <div class="mt-4 text-muted">
                    <small>
                        <i class="fas fa-shield-alt me-1"></i>
                        Your card was not charged. No money has been deducted from your account.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        @if(isset($transaction))
        const billCode = '{{ $transaction->toyyibpay_billcode }}';
        const transactionId = '{{ $transaction->id }}';
        @else
        const billCode = null;
        const transactionId = null;
        @endif

        function retryPayment() {
            if (billCode) {
                // Check if transaction is still valid for retry
                fetch(`/api/payment/status/${billCode}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.data.status === 'completed') {
                                window.location.href = `/payment/${billCode}/success`;
                            } else if (data.data.status === 'pending' || data.data.status === 'failed') {
                                // Redirect back to payment page
                                window.location.href = `/payment/${billCode}`;
                            } else {
                                alert('This transaction cannot be retried. Please create a new payment.');
                                returnToStore();
                            }
                        } else {
                            alert('Unable to retry payment. Please try again later.');
                        }
                    })
                    .catch(error => {
                        console.error('Error checking status:', error);
                        // Fallback: redirect to payment page anyway
                        window.location.href = `/payment/${billCode}`;
                    });
            } else {
                returnToStore();
            }
        }

        function contactSupport() {
            // Open support contact options
            const supportUrl = 'mailto:support@example.com?subject=Payment Failed - Transaction ' + (transactionId || 'Unknown');
            window.open(supportUrl, '_blank');
        }

        function returnToStore() {
            // Try to close the window/iframe or redirect
            if (window.parent !== window) {
                // We're in an iframe, send message to parent
                window.parent.postMessage({
                    type: 'payment_failed',
                    transaction_id: transactionId,
                    bill_code: billCode
                }, '*');
            } else {
                // Redirect to homepage or provided return URL
                const returnUrl = new URLSearchParams(window.location.search).get('return_url');
                window.location.href = returnUrl || '/';
            }
        }

        function useAlternativeMethod(method) {
            // Track the alternative method selection
            console.log('User selected alternative method:', method);
            
            // For now, just retry the payment
            retryPayment();
        }

        // Notify parent window of failed payment
        if (window.parent !== window) {
            window.parent.postMessage({
                type: 'payment_completed',
                status: 'failed',
                transaction_id: transactionId,
                bill_code: billCode
            }, '*');
        }

        // Auto-return after 60 seconds if in iframe
        if (window.parent !== window) {
            setTimeout(() => {
                returnToStore();
            }, 60000);
        }
    </script>
</body>
</html>
