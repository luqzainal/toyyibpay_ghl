<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Successful - {{ config('app.name') }}</title>
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
        .success-container {
            max-width: 800px;
            margin: 50px auto;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .success-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
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
        .success-body {
            padding: 40px 30px;
        }
        .step-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .step-number {
            width: 30px;
            height: 30px;
            background: #28a745;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 15px 30px;
            font-size: 1.1rem;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .feature-list i {
            color: #28a745;
            margin-right: 10px;
            width: 20px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <!-- Success Header -->
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="mb-3">Installation Successful!</h1>
                <p class="mb-0 opacity-75 lead">
                    ToyyibPay integration has been successfully installed to your GoHighLevel account
                </p>
            </div>

            <!-- Success Body -->
            <div class="success-body">
                <!-- What's Installed -->
                <div class="row mb-5">
                    <div class="col-md-6">
                        <h4 class="mb-3">
                            <i class="fas fa-puzzle-piece text-success me-2"></i>
                            What's Now Available
                        </h4>
                        <ul class="feature-list">
                            <li><i class="fas fa-check"></i> ToyyibPay payment gateway integration</li>
                            <li><i class="fas fa-check"></i> FPX online banking support</li>
                            <li><i class="fas fa-check"></i> Credit card and e-wallet payments</li>
                            <li><i class="fas fa-check"></i> Malaysian Ringgit (MYR) currency</li>
                            <li><i class="fas fa-check"></i> Real-time payment notifications</li>
                            <li><i class="fas fa-check"></i> Secure transaction logging</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h4 class="mb-3">
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            Security Features
                        </h4>
                        <ul class="feature-list">
                            <li><i class="fas fa-lock"></i> SSL encrypted transactions</li>
                            <li><i class="fas fa-lock"></i> PCI DSS compliant processing</li>
                            <li><i class="fas fa-lock"></i> Bank-grade security standards</li>
                            <li><i class="fas fa-lock"></i> Encrypted credential storage</li>
                            <li><i class="fas fa-lock"></i> Secure webhook callbacks</li>
                            <li><i class="fas fa-lock"></i> Transaction audit trails</li>
                        </ul>
                    </div>
                </div>

                <!-- Next Steps -->
                <h4 class="mb-4">
                    <i class="fas fa-rocket text-primary me-2"></i>
                    Next Steps to Start Accepting Payments
                </h4>

                <div class="step-card">
                    <div class="d-flex align-items-start">
                        <div class="step-number">1</div>
                        <div>
                            <h6 class="mb-2">Configure Your ToyyibPay Account</h6>
                            <p class="mb-2 text-muted">
                                Set up your ToyyibPay API credentials to connect your merchant account.
                            </p>
                            <a href="/config?location_id={{ $location_id ?? '' }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-cog me-1"></i>
                                Configure Now
                            </a>
                        </div>
                    </div>
                </div>

                <div class="step-card">
                    <div class="d-flex align-items-start">
                        <div class="step-number">2</div>
                        <div>
                            <h6 class="mb-2">Add Payment Options to Your Funnels</h6>
                            <p class="mb-2 text-muted">
                                Go to your funnels and order forms in GoHighLevel to add ToyyibPay as a payment option.
                            </p>
                            <a href="https://app.gohighlevel.com" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt me-1"></i>
                                Open GoHighLevel
                            </a>
                        </div>
                    </div>
                </div>

                <div class="step-card">
                    <div class="d-flex align-items-start">
                        <div class="step-number">3</div>
                        <div>
                            <h6 class="mb-2">Test Your Payment Flow</h6>
                            <p class="mb-2 text-muted">
                                Use the sandbox mode to test your payment process before going live.
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Sandbox mode allows you to test without real money transactions
                            </small>
                        </div>
                    </div>
                </div>

                <div class="step-card">
                    <div class="d-flex align-items-start">
                        <div class="step-number">4</div>
                        <div>
                            <h6 class="mb-2">Go Live and Start Earning</h6>
                            <p class="mb-2 text-muted">
                                Switch to production mode and start accepting real payments from your Malaysian customers.
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-rocket me-1"></i>
                                You're ready to grow your business in Malaysia!
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Support Information -->
                <div class="alert alert-info mt-4">
                    <h6><i class="fas fa-life-ring me-2"></i>Need Help?</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Documentation:</strong><br>
                            <small>Complete setup guides and API documentation</small>
                        </div>
                        <div class="col-md-6">
                            <strong>24/7 Support:</strong><br>
                            <small>Email: support@example.com</small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-5">
                    <a href="/config?location_id={{ $location_id ?? '' }}" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-play me-2"></i>
                        Start Configuration
                    </a>
                    <a href="https://app.gohighlevel.com" target="_blank" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-external-link-alt me-2"></i>
                        Back to GoHighLevel
                    </a>
                </div>

                <!-- Installation Details -->
                <div class="mt-5 text-center">
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        Installed on {{ now()->format('d M Y, H:i') }}
                        @if(isset($location_id))
                        • Location ID: {{ $location_id }}
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-redirect to configuration after 30 seconds if location_id is available
        @if(isset($location_id))
        setTimeout(() => {
            if (confirm('Would you like to configure ToyyibPay now?')) {
                window.location.href = '/config?location_id={{ $location_id }}';
            }
        }, 30000);
        @endif

        // Track successful installation
        console.log('ToyyibPay integration installed successfully');
    </script>
</body>
</html>
