<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToyyibPay Configuration - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .config-container {
            max-width: 800px;
            margin: 50px auto;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 2rem;
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
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .mode-switch {
            display: flex;
            background: #e9ecef;
            border-radius: 25px;
            padding: 4px;
            margin-bottom: 20px;
        }
        .mode-switch input[type="radio"] {
            display: none;
        }
        .mode-switch label {
            flex: 1;
            text-align: center;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0;
        }
        .mode-switch input[type="radio"]:checked + label {
            background: #667eea;
            color: white;
        }
        .loading-spinner {
            display: none;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }
        .step.active:not(:last-child)::after {
            background: #667eea;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: bold;
            position: relative;
            z-index: 2;
        }
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container config-container">
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step active">
                <div class="step-number">1</div>
                <small>Environment</small>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <small>API Keys</small>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <small>Validation</small>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <small>Complete</small>
            </div>
        </div>

        <div class="card">
            <div class="card-header text-center">
                <img src="{{ asset('logo-colour-white.svg') }}" alt="ToyyibPay Logo" style="max-height: 60px; margin-bottom: 15px;">
                <h2 class="mb-2">
                    ToyyibPay Configuration
                </h2>
                <p class="mb-0 opacity-75">
                    Configure your ToyyibPay API credentials for this location
                </p>
            </div>
            <div class="card-body p-4">
                <!-- Success Alert -->
                <div id="successAlert" class="alert alert-success d-none">
                    <i class="fas fa-check-circle me-2"></i>
                    <span id="successMessage"></span>
                </div>

                <!-- Error Alert -->
                <div id="errorAlert" class="alert alert-danger d-none">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="errorMessage"></span>
                </div>

                <!-- Configuration Form -->
                <form id="configForm">
                    <input type="hidden" id="locationId" name="location_id" value="{{ $location_id ?? '' }}">
                    
                    <!-- Environment Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-server me-2"></i>
                            Environment Mode
                        </label>
                        <div class="mode-switch">
                            <input type="radio" id="sandbox" name="mode" value="sandbox" checked>
                            <label for="sandbox">
                                <i class="fas fa-flask me-1"></i>
                                Sandbox (Testing)
                            </label>
                            <input type="radio" id="production" name="mode" value="production">
                            <label for="production">
                                <i class="fas fa-rocket me-1"></i>
                                Production (Live)
                            </label>
                        </div>
                        <small class="text-muted">
                            Use Sandbox for testing, Production for live transactions
                        </small>
                    </div>

                    <!-- API Credentials -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="secretKey" class="form-label fw-bold">
                                <i class="fas fa-key me-2"></i>
                                Secret Key *
                            </label>
                            <input type="password" class="form-control" id="secretKey" name="secret_key" required>
                            <small class="text-muted">Your ToyyibPay secret key</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="categoryCode" class="form-label fw-bold">
                                <i class="fas fa-tag me-2"></i>
                                Category Code *
                            </label>
                            <input type="text" class="form-control" id="categoryCode" name="category_code" required>
                            <small class="text-muted">Your ToyyibPay category code</small>
                        </div>
                    </div>

                    <!-- Help Text -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Where to find your credentials:</h6>
                        <ol class="mb-0 small">
                            <li>Login to your <a href="https://toyyibpay.com" target="_blank">ToyyibPay account</a></li>
                            <li>Go to <strong>Settings</strong> → <strong>API</strong></li>
                            <li>Copy your <strong>Secret Key</strong> and <strong>Category Code</strong></li>
                            <li>For testing, use your <strong>Sandbox</strong> credentials</li>
                        </ol>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                            <i class="fas fa-arrow-left me-2"></i>
                            Back
                        </button>
                        <div>
                            <button type="button" class="btn btn-outline-primary me-2" id="validateBtn">
                                <i class="fas fa-check me-2"></i>
                                <span class="loading-spinner spinner-border spinner-border-sm me-2"></span>
                                Validate Credentials
                            </button>
                            <button type="submit" class="btn btn-primary" id="saveBtn" disabled>
                                <i class="fas fa-save me-2"></i>
                                <span class="loading-spinner spinner-border spinner-border-sm me-2"></span>
                                Save Configuration
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Current Configuration -->
        <div class="card mt-4" id="currentConfig" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Current Configuration
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Environment:</strong>
                        <span id="currentMode" class="badge bg-secondary ms-2"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <span id="currentStatus" class="badge bg-success ms-2"></span>
                    </div>
                </div>
                <div class="mt-3">
                    <strong>Last Updated:</strong>
                    <span id="lastUpdated" class="text-muted"></span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('configForm');
            const validateBtn = document.getElementById('validateBtn');
            const saveBtn = document.getElementById('saveBtn');
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');

            // Load existing configuration
            loadCurrentConfig();

            // Validate credentials
            validateBtn.addEventListener('click', function() {
                validateCredentials();
            });

            // Save configuration
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                saveConfiguration();
            });

            function loadCurrentConfig() {
                const locationId = document.getElementById('locationId').value;
                if (!locationId) return;

                fetch(`/api/toyyibpay/config/${locationId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            const config = data.data;
                            document.getElementById('currentConfig').style.display = 'block';
                            document.getElementById('currentMode').textContent = config.mode_active;
                            document.getElementById('currentStatus').textContent = config.is_configured ? 'Configured' : 'Not Configured';
                            document.getElementById('lastUpdated').textContent = new Date(config.updated_at).toLocaleString();
                            
                            // Pre-fill form if configured
                            if (config.is_configured) {
                                document.querySelector(`input[name="mode"][value="${config.mode_active}"]`).checked = true;
                            }
                        }
                    })
                    .catch(error => {
                        console.log('No existing configuration found');
                    });
            }

            function validateCredentials() {
                const formData = new FormData(form);
                showLoading(validateBtn);
                hideAlerts();

                fetch('/api/toyyibpay/validate-key', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading(validateBtn);
                    
                    if (data.success && data.data.valid) {
                        showSuccess('Credentials validated successfully!');
                        saveBtn.disabled = false;
                        updateStepIndicator(3);
                    } else {
                        showError('Invalid credentials. Please check your Secret Key and Category Code.');
                        saveBtn.disabled = true;
                    }
                })
                .catch(error => {
                    hideLoading(validateBtn);
                    showError('Error validating credentials: ' + error.message);
                    saveBtn.disabled = true;
                });
            }

            function saveConfiguration() {
                const formData = new FormData(form);
                showLoading(saveBtn);
                hideAlerts();

                fetch('/api/toyyibpay/config', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading(saveBtn);
                    
                    if (data.success) {
                        showSuccess('Configuration saved successfully!');
                        updateStepIndicator(4);
                        loadCurrentConfig();
                        
                        // Redirect after 2 seconds
                        setTimeout(() => {
                            window.location.href = '/install-success';
                        }, 2000);
                    } else {
                        showError('Error saving configuration: ' + data.message);
                    }
                })
                .catch(error => {
                    hideLoading(saveBtn);
                    showError('Error saving configuration: ' + error.message);
                });
            }

            function showLoading(button) {
                button.disabled = true;
                button.querySelector('.loading-spinner').style.display = 'inline-block';
            }

            function hideLoading(button) {
                button.disabled = false;
                button.querySelector('.loading-spinner').style.display = 'none';
            }

            function showSuccess(message) {
                document.getElementById('successMessage').textContent = message;
                successAlert.classList.remove('d-none');
                errorAlert.classList.add('d-none');
            }

            function showError(message) {
                document.getElementById('errorMessage').textContent = message;
                errorAlert.classList.remove('d-none');
                successAlert.classList.add('d-none');
            }

            function hideAlerts() {
                successAlert.classList.add('d-none');
                errorAlert.classList.add('d-none');
            }

            function updateStepIndicator(step) {
                const steps = document.querySelectorAll('.step');
                steps.forEach((stepEl, index) => {
                    if (index < step - 1) {
                        stepEl.classList.add('completed');
                        stepEl.classList.remove('active');
                    } else if (index === step - 1) {
                        stepEl.classList.add('active');
                        stepEl.classList.remove('completed');
                    } else {
                        stepEl.classList.remove('active', 'completed');
                    }
                });
            }

            // Update step indicator based on form changes
            document.querySelectorAll('input[name="mode"]').forEach(radio => {
                radio.addEventListener('change', () => updateStepIndicator(2));
            });

            document.querySelectorAll('input[required]').forEach(input => {
                input.addEventListener('input', () => {
                    const allFilled = Array.from(document.querySelectorAll('input[required]'))
                        .every(inp => inp.value.trim() !== '');
                    if (allFilled) updateStepIndicator(2);
                });
            });
        });
    </script>
</body>
</html>
