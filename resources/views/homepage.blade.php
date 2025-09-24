<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Malaysian Payment Gateway Integration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        .install-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        .install-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        .logo {
            max-height: 40px;
            width: auto;
        }
        .hero-logo {
            max-height: 120px;
            margin-bottom: 30px;
        }
        .step-number {
            background: #667eea;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 15px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="{{ url('/') }}">
                <img src="{{ asset('logo-colour-white.svg') }}" alt="ToyyibPay Logo" class="logo me-2">
                GHL ToyyibPay Integration
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-white" href="#features">Features</a>
                <a class="nav-link text-white" href="#how-it-works">How It Works</a>
                <a class="nav-link text-white" href="#support">Support</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <img src="{{ asset('logo-colour-white.svg') }}" alt="ToyyibPay Logo" class="hero-logo">
                    <h1 class="display-4 fw-bold mb-4">
                        Accept Malaysian Payments in GoHighLevel
                    </h1>
                    <p class="lead mb-5">
                        Seamlessly integrate ToyyibPay with your GoHighLevel funnels and accept payments 
                        via FPX, Credit Card, and E-Wallets from Malaysian customers.
                    </p>
                    <a href="{{ config('services.ghl.oauth_redirect') ?? '#' }}" 
                       class="btn btn-light install-btn btn-lg">
                        <i class="fas fa-download me-2"></i>
                        Install to GoHighLevel
                    </a>
                    <p class="mt-3 small opacity-75">
                        <i class="fas fa-shield-alt me-1"></i>
                        Secure • Easy Setup • Malaysian Ringgit Support
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold">Why Choose Our Integration?</h2>
                    <p class="lead text-muted">Everything you need to accept Malaysian payments in GoHighLevel</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <i class="fas fa-university feature-icon"></i>
                        <h4>FPX Online Banking</h4>
                        <p class="text-muted">
                            Accept payments from all major Malaysian banks including Maybank, CIMB, 
                            Public Bank, and more through secure FPX gateway.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <i class="fas fa-credit-card feature-icon"></i>
                        <h4>Credit & Debit Cards</h4>
                        <p class="text-muted">
                            Support for Visa, Mastercard, and local Malaysian cards with 
                            secure 3D authentication for customer protection.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <i class="fas fa-mobile-alt feature-icon"></i>
                        <h4>E-Wallet Payments</h4>
                        <p class="text-muted">
                            Accept payments from popular e-wallets like GrabPay, Boost, 
                            and TouchNGo for maximum customer convenience.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold">How It Works</h2>
                    <p class="lead text-muted">Get started in just a few simple steps</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="text-center">
                        <div class="step-number">1</div>
                        <h5>Install Plugin</h5>
                        <p class="text-muted">
                            Click the install button above to add the ToyyibPay integration 
                            to your GoHighLevel account.
                        </p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="text-center">
                        <div class="step-number">2</div>
                        <h5>Configure API</h5>
                        <p class="text-muted">
                            Enter your ToyyibPay API credentials in the configuration page 
                            to connect your merchant account.
                        </p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="text-center">
                        <div class="step-number">3</div>
                        <h5>Setup Funnels</h5>
                        <p class="text-muted">
                            Add ToyyibPay as a payment option in your funnels and 
                            order forms within GoHighLevel.
                        </p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="text-center">
                        <div class="step-number">4</div>
                        <h5>Accept Payments</h5>
                        <p class="text-muted">
                            Start accepting Malaysian Ringgit payments from your customers 
                            with automatic transaction tracking.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-6 fw-bold mb-4">Built for Malaysian Market</h2>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>MYR Currency Support</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Local Bank Integration</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Secure Transactions</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Real-time Notifications</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Sandbox Testing</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>24/7 Transaction Logs</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="https://via.placeholder.com/500x300/667eea/ffffff?text=ToyyibPay+Integration" 
                         alt="Integration Preview" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <h2 class="display-6 fw-bold mb-4">Ready to Accept Malaysian Payments?</h2>
                    <p class="lead mb-4">
                        Join hundreds of businesses already using our ToyyibPay integration 
                        to grow their revenue in Malaysia.
                    </p>
                    <a href="{{ config('services.ghl.oauth_redirect') ?? '#' }}" 
                       class="btn btn-light install-btn btn-lg">
                        <i class="fas fa-rocket me-2"></i>
                        Get Started Now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Support Section -->
    <section id="support" class="py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <i class="fas fa-book feature-icon"></i>
                    <h5>Documentation</h5>
                    <p class="text-muted">
                        Comprehensive guides and API documentation to help you get started quickly.
                    </p>
                </div>
                <div class="col-md-4 mb-4">
                    <i class="fas fa-headset feature-icon"></i>
                    <h5>24/7 Support</h5>
                    <p class="text-muted">
                        Our support team is available around the clock to help with any issues.
                    </p>
                </div>
                <div class="col-md-4 mb-4">
                    <i class="fas fa-shield-alt feature-icon"></i>
                    <h5>Secure & Reliable</h5>
                    <p class="text-muted">
                        Bank-grade security with 99.9% uptime guarantee for your peace of mind.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">
                        &copy; {{ date('Y') }} GHL ToyyibPay Integration. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        Powered by ToyyibPay • Integrated with GoHighLevel
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
