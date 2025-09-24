## Relevant Files

- `app/Http/Controllers/GHLController.php` - Main controller for handling GHL OAuth, provider registration, and webhook endpoints
- `app/Http/Controllers/ToyyibPayController.php` - Controller for ToyyibPay API integration and payment processing
- `app/Http/Controllers/PaymentController.php` - Controller for payment page display and processing within iframe
- `app/Http/Controllers/WebhookController.php` - Controller for handling ToyyibPay payment status callbacks
- `app/Models/Integration.php` - Eloquent model for storing GHL integration data per location
- `app/Models/ToyyibPayConfig.php` - Eloquent model for storing ToyyibPay configuration per location
- `app/Models/Transaction.php` - Eloquent model for storing payment transaction information
- `app/Services/GHLService.php` - Service class for GHL API communication and OAuth handling
- `app/Services/ToyyibPayService.php` - Service class for ToyyibPay API integration
- `app/Services/KeyGeneratorService.php` - Service for generating unique publishable keys per location
- `database/migrations/create_integrations_table.php` - Database migration for integrations table
- `database/migrations/create_toyyibpay_config_table.php` - Database migration for ToyyibPay config table
- `database/migrations/create_transactions_table.php` - Database migration for transactions table
- `resources/views/homepage.blade.php` - Static homepage with install button link
- `resources/views/config.blade.php` - Frontend configuration page for API key input
- `resources/views/payment.blade.php` - Payment page displayed within GHL iframe
- `resources/views/install-success.blade.php` - Installation success page
- `resources/views/install-failure.blade.php` - Installation failure page
- `routes/web.php` - Web routes for frontend pages
- `routes/api.php` - API routes for GHL and ToyyibPay integration
- `config/toyyibpay.php` - Configuration file for ToyyibPay API URLs and settings
- `env-template.txt` - Environment variables template including GHL and ToyyibPay configuration
- `.env.example` - Environment variables example (to be created by user from env-template.txt)
- `.env` - Environment variables file (to be created manually by user)

### Notes

- Unit tests should be created alongside each controller and service class
- Use `php artisan test` to run Laravel tests
- All API endpoints must be secured with proper authentication and validation
- Database migrations must include proper indexing for performance

### Manual Setup Required

**Environment Configuration:**
1. Copy `env-template.txt` to `.env.example` 
2. Copy `.env.example` to `.env`
3. Update the following values in `.env`:
   - `GHL_CLIENT_ID` - Your GHL app client ID
   - `GHL_CLIENT_SECRET` - Your GHL app client secret  
   - `GHL_SSO_KEY` - Your GHL app SSO key
   - `GHL_OAUTH_REDIRECT` - Your domain callback URL (Production: https://yourdomain.com/oauth/callback, Localhost: http://localhost:8000/oauth/callback)
   - `DB_DATABASE` - Your MySQL database name
   - `DB_USERNAME` - Your MySQL username
   - `DB_PASSWORD` - Your MySQL password
4. Run `php artisan key:generate` to generate APP_KEY

## Tasks

- [x] 1.0 Setup Laravel 12 Project Structure and Configuration
  - [x] 1.1 Install Laravel 12 with required dependencies (guzzlehttp/guzzle for HTTP requests)
  - [x] 1.2 Configure MySQL database connection in config/database.php
  - [x] 1.3 Setup .env variables: GHL_CLIENT_ID, GHL_CLIENT_SECRET, GHL_SSO_KEY, GHL_OAUTH_REDIRECT (template provided in env-template.txt)
  - [x] 1.4 Add GHL API base URL (https://services.leadconnectorhq.com) to environment configuration
  - [x] 1.5 Create ToyyibPay configuration file with production and sandbox URLs
  - [x] 1.6 Setup Laravel middleware for API authentication and CORS
  - [x] 1.7 Configure Laravel encryption for sensitive data storage
  - [x] 1.8 Setup logging configuration for transaction debugging
  - [x] 1.9 Create basic directory structure for controllers, services, and models

- [ ] 2.0 Implement Database Schema and Models
  - [x] 2.1 Create "integrations" migration with location_id, company_id, access_token, refresh_token, api_key, installed_at
  - [x] 2.2 Create "toyyibpay_config" migration with location_id, secret_key_live, category_code_live, secret_key_sandbox, category_code_sandbox, mode_active
  - [x] 2.3 Create "transactions" migration with ghl_order_id, ghl_transaction_id, toyyibpay_billcode, amount, status for debugging
  - [x] 2.4 Run database migrations to create all tables
  - [x] 2.5 Implement Integration Eloquent model with encrypted attributes for sensitive tokens
  - [x] 2.6 Implement ToyyibPayConfig Eloquent model with relationship to Integration
  - [x] 2.7 Implement Transaction Eloquent model with proper relationships
  - [x] 2.8 Add database indexes on location_id, transaction_id, and billcode for performance
  - [x] 2.9 Create model factories and seeders for testing

- [x] 2.0 Implement Database Schema and Models
- [x] 3.0 Develop GHL API Integration and OAuth Flow
  - [x] 3.1 Create GHLService class for OAuth token exchange and refresh
  - [x] 3.2 Implement provider registration endpoint for GHL marketplace
  - [x] 3.3 Create connect keys endpoint to send plugin-generated keys to GHL
  - [x] 3.4 Implement payment status webhook sender to notify GHL of transaction results
  - [x] 3.5 Create query/verification endpoint that returns success: false
  - [x] 3.6 Implement webhook handlers for plugin install/uninstall from GHL
  - [x] 3.7 Add proper Bearer token authentication and API versioning
  - [x] 3.8 Create KeyGeneratorService for unique publishable key generation

- [x] 4.0 Create ToyyibPay Payment Processing System
  - [x] 4.1 Implement ToyyibPayService class for API communication
  - [x] 4.2 Create invoice creation functionality with environment switching
  - [x] 4.3 Implement payment page controller for iframe display
  - [x] 4.4 Create webhook receiver for ToyyibPay payment status callbacks
  - [x] 4.5 Implement payment completion handler and redirect logic
  - [x] 4.6 Add proper error handling and timeout management
  - [x] 4.7 Create transaction logging and status tracking system

- [x] 5.0 Build Frontend Configuration and Payment Interface
  - [x] 5.1 Create static homepage with install button link for GHL marketplace
  - [x] 5.2 Create configuration page for API key input per location
  - [x] 5.3 Implement API key validation interface with ToyyibPay
  - [x] 5.4 Build installation success/failure status pages
  - [x] 5.5 Create responsive payment page that works within GHL iframe
  - [x] 5.6 Implement loading states and error message displays
  - [x] 5.7 Add environment switching UI for test/live mode selection
  - [x] 5.8 Create proper iframe communication handling
