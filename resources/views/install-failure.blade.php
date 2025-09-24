<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Failed - {{ config('app.name') }}</title>
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
        .failure-container {
            max-width: 700px;
            margin: 50px auto;
        }
        .failure-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .failure-header {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .failure-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .failure-body {
            padding: 40px 30px;
        }
        .error-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 15px 30px;
            font-size: 1.1rem;
        }
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            border: none;
            border-radius: 25px;
            padding: 15px 30px;
            font-size: 1.1rem;
        }
        .troubleshooting-step {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 3px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="failure-container">
        <div class="failure-card">
            <!-- Failure Header -->
            <div class="failure-header">
                <div class="failure-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h1 class="mb-3">Installation Failed</h1>
                <p class="mb-0 opacity-75 lead">
                    We encountered an issue while installing ToyyibPay integration
                </p>
            </div>

            <!-- Failure Body -->
            <div class="failure-body">
                <!-- Error Details -->
                <div class="error-details">
                    <h6 class="text-danger mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error Details
                    </h6>
                    <p class="mb-2">
                        <strong>Error:</strong> {{ $error ?? 'Unknown installation error occurred' }}
                    </p>
                    <p class="mb-2">
                        <strong>Time:</strong> {{ now()->format('d M Y, H:i:s') }}
                    </p>
                    @if(isset($location_id))
                    <p class="mb-0">
                        <strong>Location ID:</strong> {{ $location_id }}
                    </p>
                    @endif
                </div>

                <!-- Common Causes -->
                <h5 class="mb-3">
                    <i class="fas fa-search me-2"></i>
                    Common Causes
                </h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-wifi text-warning me-2"></i>
                        <strong>Network Issues:</strong> Connection timeout or network interruption
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-key text-warning me-2"></i>
                        <strong>Authentication:</strong> Invalid or expired OAuth credentials
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-server text-warning me-2"></i>
                        <strong>Server Issues:</strong> Temporary server overload or maintenance
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-shield-alt text-warning me-2"></i>
                        <strong>Permissions:</strong> Insufficient permissions in GoHighLevel account
                    </li>
                </ul>

                <!-- Troubleshooting Steps -->
                <h5 class="mb-3 mt-4">
                    <i class="fas fa-tools me-2"></i>
                    Troubleshooting Steps
                </h5>

                <div class="troubleshooting-step">
                    <strong>1. Check Your Internet Connection</strong>
                    <p class="mb-0 mt-1 text-muted small">
                        Ensure you have a stable internet connection and try again.
                    </p>
                </div>

                <div class="troubleshooting-step">
                    <strong>2. Verify GoHighLevel Permissions</strong>
                    <p class="mb-0 mt-1 text-muted small">
                        Make sure your GoHighLevel account has the necessary permissions to install apps.
                    </p>
                </div>

                <div class="troubleshooting-step">
                    <strong>3. Clear Browser Cache</strong>
                    <p class="mb-0 mt-1 text-muted small">
                        Clear your browser cache and cookies, then try the installation again.
                    </p>
                </div>

                <div class="troubleshooting-step">
                    <strong>4. Try Again Later</strong>
                    <p class="mb-0 mt-1 text-muted small">
                        If there are temporary server issues, waiting 5-10 minutes and retrying may resolve the problem.
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-5">
                    <button type="button" class="btn btn-danger btn-lg me-3" onclick="retryInstallation()">
                        <i class="fas fa-redo me-2"></i>
                        Try Installation Again
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-lg me-3" onclick="contactSupport()">
                        <i class="fas fa-headset me-2"></i>
                        Contact Support
                    </button>
                    <a href="https://app.gohighlevel.com" target="_blank" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to GoHighLevel
                    </a>
                </div>

                <!-- Support Information -->
                <div class="alert alert-info mt-5">
                    <h6><i class="fas fa-life-ring me-2"></i>Need Immediate Help?</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Email Support:</strong><br>
                            <small>support@example.com</small><br>
                            <small class="text-muted">Response within 4 hours</small>
                        </div>
                        <div class="col-md-6">
                            <strong>Documentation:</strong><br>
                            <small>Installation troubleshooting guide</small><br>
                            <small class="text-muted">Step-by-step solutions</small>
                        </div>
                    </div>
                </div>

                <!-- What to Include in Support Request -->
                <div class="mt-4">
                    <details>
                        <summary class="text-muted">What to include when contacting support</summary>
                        <div class="mt-2 small">
                            <ul>
                                <li>Error message: {{ $error ?? 'Unknown installation error' }}</li>
                                <li>Time of error: {{ now()->format('d M Y, H:i:s') }}</li>
                                @if(isset($location_id))
                                <li>Location ID: {{ $location_id }}</li>
                                @endif
                                <li>Browser and version you're using</li>
                                <li>Steps you took before the error occurred</li>
                                <li>Your GoHighLevel account email</li>
                            </ul>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function retryInstallation() {
            // Redirect to the installation/OAuth flow
            const installUrl = '{{ config("services.ghl.oauth_redirect") ?? "/" }}';
            window.location.href = installUrl;
        }

        function contactSupport() {
            const subject = encodeURIComponent('ToyyibPay Installation Failed - Need Help');
            let body = encodeURIComponent('Hello Support Team,\n\nI encountered an error while trying to install the ToyyibPay integration.\n\n');
            
            body += encodeURIComponent('Error Details:\n');
            body += encodeURIComponent('- Error: {{ $error ?? "Unknown installation error" }}\n');
            body += encodeURIComponent('- Time: {{ now()->format("d M Y, H:i:s") }}\n');
            
            @if(isset($location_id))
            body += encodeURIComponent('- Location ID: {{ $location_id }}\n');
            @endif
            
            body += encodeURIComponent('- Browser: ' + navigator.userAgent + '\n\n');
            body += encodeURIComponent('Please help me resolve this installation issue.\n\nThank you.');

            const mailtoUrl = `mailto:support@example.com?subject=${subject}&body=${body}`;
            window.location.href = mailtoUrl;
        }

        // Track failed installation for analytics
        console.error('ToyyibPay installation failed:', '{{ $error ?? "Unknown error" }}');

        // Auto-retry suggestion after 2 minutes
        setTimeout(() => {
            if (confirm('Would you like to try the installation again? Network issues may have been resolved.')) {
                retryInstallation();
            }
        }, 120000);
    </script>
</body>
</html>
