
# GHL ToyyibPay Integration

<p align="center">
  <img src="public/logo-colour-white.svg" width="400" alt="ToyyibPay Logo">
</p>

<p align="center">
  <strong>Malaysian Payment Gateway Integration for GoHighLevel</strong><br>
  Accept FPX, Credit Cards, and E-Wallet payments seamlessly in your GHL funnels
</p>

## 🚀 About This Project

This Laravel-based integration connects **ToyyibPay** (Malaysia's leading payment gateway) with **GoHighLevel (GHL)** platform, enabling businesses to accept Malaysian Ringgit (MYR) payments through multiple payment methods.

### ✨ Key Features

- **🏦 FPX Online Banking** - All major Malaysian banks (Maybank, CIMB, Public Bank, etc.)
- **💳 Credit & Debit Cards** - Visa, Mastercard with 3D Secure authentication
- **📱 E-Wallet Support** - GrabPay, Boost, TouchNGo, and more
- **🔐 Secure Integration** - OAuth 2.0 authentication with GHL
- **📊 Real-time Webhooks** - Instant payment status updates
- **🎯 Easy Configuration** - Simple API key setup per GHL location
- **💼 Multi-tenant** - Supports multiple GHL locations/companies

## 🛠️ Tech Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Database:** MySQL / SQLite
- **Frontend:** Blade Templates, Bootstrap 5
- **API Integration:** Guzzle HTTP Client
- **Authentication:** GHL OAuth 2.0
- **Payment Gateway:** ToyyibPay API

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- MySQL 5.7+ or SQLite
- GHL Developer Account
- ToyyibPay Merchant Account

## ⚡ Quick Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/gohl-toyyibpay.git
   cd gohl-toyyibpay
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp env-template.txt .env
   # Edit .env with your credentials
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

## 🔧 Configuration

### GHL App Configuration

1. Create a new app in GHL Developer Portal
2. Set OAuth Redirect URL:
   - **Production:** `https://yourdomain.com/oauth/callback`
   - **Development:** `http://localhost:8000/oauth/callback`

### Environment Variables

Copy `env-template.txt` to `.env` and configure:

```env
# GHL Configuration
GHL_CLIENT_ID=your_ghl_client_id
GHL_CLIENT_SECRET=your_ghl_client_secret
GHL_SSO_KEY=your_ghl_sso_key
GHL_OAUTH_REDIRECT=https://yourdomain.com/oauth/callback

# ToyyibPay Configuration
TOYYIBPAY_PRODUCTION_URL=https://toyyibpay.com
TOYYIBPAY_SANDBOX_URL=https://dev.toyyibpay.com
TOYYIBPAY_DEFAULT_MODE=sandbox

# Database Configuration
DB_CONNECTION=mysql
DB_DATABASE=gohl_toyyibpay
DB_USERNAME=root
DB_PASSWORD=
```

## 📚 API Endpoints

### GHL Integration
- `GET /oauth/callback` - OAuth callback handler
- `POST /api/ghl/provider/register` - Register payment provider
- `POST /api/ghl/connect-keys` - Send API keys to GHL
- `POST /api/ghl/webhook/install` - Handle app installation
- `POST /api/ghl/webhook/uninstall` - Handle app uninstallation
- `POST /api/ghl/payment/status` - Update payment status

### ToyyibPay Integration
- `POST /api/toyyibpay/create-payment` - Create payment bill
- `GET /api/toyyibpay/payment-status/{billcode}` - Check payment status
- `POST /api/toyyibpay/webhook/callback` - Payment status webhook
- `POST /api/toyyibpay/validate-key` - Validate API credentials

### Frontend Pages
- `/` - Homepage with installation guide
- `/config` - ToyyibPay API configuration
- `/payment/{billCode}` - Payment processing page
- `/install-success` - Installation success page

## 🗄️ Database Schema

### Tables

1. **integrations** - GHL location integration data
2. **toyyibpay_config** - ToyyibPay credentials per location  
3. **transactions** - Payment transaction records

### Key Features
- Encrypted storage of sensitive tokens
- Proper indexing for performance
- Foreign key constraints for data integrity
- Comprehensive transaction logging

## 🔐 Security Features

- **Token Encryption** - All OAuth tokens and API keys encrypted at rest
- **Request Validation** - Comprehensive input validation and sanitization
- **CORS Protection** - Configured for secure cross-origin requests
- **Authentication Middleware** - GHL Bearer token validation
- **Webhook Verification** - SSO key validation for GHL webhooks

## 🚦 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Generate test coverage
php artisan test --coverage
```

## 📖 Usage Guide

### For GHL Users

1. **Install the App** - Click install button from GHL Marketplace
2. **Configure ToyyibPay** - Enter your ToyyibPay credentials in the config page
3. **Create Payment Forms** - Use the payment provider in your GHL funnels
4. **Monitor Transactions** - View payment status in GHL dashboard

### For Developers

1. **Webhook Handling** - All payment status changes are automatically synced
2. **Error Logging** - Comprehensive logging for debugging
3. **API Documentation** - RESTful API with proper status codes
4. **Extensible Design** - Easy to add new payment methods or features

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation:** Check the `/docs` folder for detailed guides
- **Issues:** Create an issue on GitHub for bug reports
- **Email:** support@yourdomain.com
- **Discord:** Join our developer community

## 🙏 Acknowledgments

- **GoHighLevel** - For the amazing CRM platform
- **ToyyibPay** - For reliable Malaysian payment processing
- **Laravel Community** - For the excellent framework
- **Contributors** - Thank you to all who helped build this integration

---

<p align="center">
  Made with ❤️ for the Malaysian business community
</p>
>>>>>>> 83dbd2b (first commit)
