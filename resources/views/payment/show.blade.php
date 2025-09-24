<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .payment-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .payment-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .payment-body {
            padding: 40px 30px;
        }
        .amount-display {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
            text-align: center;
            margin-bottom: 10px;
        }
        .currency {
            font-size: 1.2rem;
            color: #6c757d;
        }
        .payment-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
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
        .pay-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 50px;
            padding: 15px 40px;
            font-size: 1.2rem;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
        }
        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .payment-methods {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .payment-method {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 10px 15px;
            text-align: center;
            min-width: 80px;
            transition: all 0.3s ease;
        }
        .payment-method:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        .payment-method i {
            font-size: 1.5rem;
            color: #667eea;
            margin-bottom: 5px;
        }
        .payment-method small {
            display: block;
            color: #6c757d;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .loading-content {
            text-align: center;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive iframe adjustments */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .payment-header {
                padding: 20px;
            }
            .payment-body {
                padding: 20px;
            }
            .amount-display {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h5>Redirecting to Payment Gateway...</h5>
            <p class="text-muted">Please wait while we redirect you to secure payment page</p>
        </div>
    </div>

    <div class="payment-container">
        <div class="payment-card">
            <!-- Payment Header -->
            <div class="payment-header">
                <img src="{{ asset('logo-colour-white.svg') }}" alt="ToyyibPay Logo" style="max-height: 50px; margin-bottom: 15px;">
                <h3 class="mb-2">
                    Complete Your Payment
                </h3>
                <p class="mb-0 opacity-75">Secure payment powered by ToyyibPay</p>
            </div>

            <!-- Payment Body -->
            <div class="payment-body">
                <!-- Amount Display -->
                <div class="text-center mb-4">
                    <div class="amount-display">
                        {{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}
                    </div>
                    <p class="text-muted mb-0">{{ $transaction->description }}</p>
                </div>

                <!-- Payment Methods -->
                <div class="payment-methods">
                    <div class="payment-method">
                        <i class="fas fa-university"></i>
                        <small>FPX Banking</small>
                    </div>
                    <div class="payment-method">
                        <i class="fas fa-credit-card"></i>
                        <small>Credit Card</small>
                    </div>
                    <div class="payment-method">
                        <i class="fas fa-mobile-alt"></i>
                        <small>E-Wallet</small>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="payment-details">
                    <div class="detail-row">
                        <span><strong>Transaction ID:</strong></span>
                        <span class="font-monospace">{{ $transaction->id }}</span>
                    </div>
                    <div class="detail-row">
                        <span><strong>Bill Code:</strong></span>
                        <span class="font-monospace">{{ $bill_code }}</span>
                    </div>
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
                        <span><strong>Environment:</strong></span>
                        <span class="badge {{ $transaction->environment === 'production' ? 'bg-success' : 'bg-warning' }}">
                            {{ ucfirst($transaction->environment) }}
                        </span>
                    </div>
                </div>

                <!-- Pay Button -->
                <button type="button" class="btn pay-btn" id="payBtn" onclick="proceedToPayment()">
                    <i class="fas fa-lock me-2"></i>
                    Pay Now Securely
                </button>

                <!-- Secure Badge -->
                <div class="secure-badge">
                    <i class="fas fa-shield-alt me-2"></i>
                    <span>256-bit SSL encryption • PCI DSS compliant • Bank-grade security</span>
                </div>

                <!-- Additional Info -->
                <div class="text-center mt-4">
                    <small class="text-muted">
                        By proceeding, you agree to ToyyibPay's terms and conditions.<br>
                        You will be redirected to ToyyibPay's secure payment gateway.
                    </small>
                </div>
            </div>
        </div>

        <!-- Transaction Status Check -->
        <div class="text-center mt-3">
            <button type="button" class="btn btn-outline-light btn-sm" onclick="checkStatus()">
                <i class="fas fa-sync-alt me-1"></i>
                Check Payment Status
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Payment URL from server
        const paymentUrl = @json($payment_url);
        const billCode = @json($bill_code);
        const transactionId = @json($transaction->id);

        function proceedToPayment() {
            // Show loading overlay
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            // Track payment initiation
            trackPaymentEvent('payment_initiated');

            // Redirect to ToyyibPay payment page
            setTimeout(() => {
                window.location.href = paymentUrl;
            }, 1500);
        }

        function checkStatus() {
            fetch(`/api/payment/status/${billCode}/refresh`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const status = data.data.status;
                    
                    if (status === 'completed') {
                        window.location.href = `/payment/${billCode}/success`;
                    } else if (status === 'failed') {
                        window.location.href = `/payment/${billCode}/failed`;
                    } else {
                        alert('Payment status: ' + status.toUpperCase());
                    }
                } else {
                    alert('Unable to check payment status. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error checking status:', error);
                alert('Error checking payment status.');
            });
        }

        function trackPaymentEvent(event) {
            // Track payment events for analytics
            fetch('/api/payment/track', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    event: event,
                    transaction_id: transactionId,
                    bill_code: billCode
                })
            }).catch(error => {
                console.log('Tracking error:', error);
            });
        }

        // Auto-refresh status every 30 seconds
        setInterval(() => {
            fetch(`/api/payment/status/${billCode}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.status === 'completed') {
                        window.location.href = `/payment/${billCode}/success`;
                    }
                })
                .catch(error => {
                    console.log('Auto-refresh error:', error);
                });
        }, 30000);

        // Handle iframe communication with parent window (GHL)
        window.addEventListener('message', function(event) {
            // Handle messages from parent window if needed
            if (event.data && event.data.type === 'payment_complete') {
                window.location.href = `/payment/${billCode}/success`;
            }
        });

        // Notify parent window about payment page load
        if (window.parent !== window) {
            window.parent.postMessage({
                type: 'payment_page_loaded',
                transaction_id: transactionId,
                bill_code: billCode,
                amount: {{ $transaction->amount }}
            }, '*');
        }
    </script>
</body>
</html>
