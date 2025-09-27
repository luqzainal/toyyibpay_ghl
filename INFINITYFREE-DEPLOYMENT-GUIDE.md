# 🚀 GHL ToyyibPay Integration - InfinityFree Deployment Guide

## 📋 Prerequisites

1. **InfinityFree Account** - Sign up at [infinityfree.com](https://infinityfree.com)
2. **MySQL Database** - Created through InfinityFree control panel
3. **FTP Access** - Get credentials from InfinityFree control panel

---

## 🗄️ Step 1: Database Setup

### 1.1 Create Database
1. Login to **InfinityFree Control Panel**
2. Go to **MySQL Databases**
3. Create database: `if0_XXXXXXXX_toyyibpayghl`
4. Note down your database credentials:
   ```
   Host: sqlXXX.infinityfree.com
   Database: if0_XXXXXXXX_toyyibpayghl
   Username: if0_XXXXXXXX
   Password: [your_password]
   ```

### 1.2 Import Database Structure
1. Go to **phpMyAdmin** in control panel
2. Select your database
3. Go to **Import** tab
4. Upload file: `database/infinityfree-setup.sql`
5. Click **Go** to execute

---

## 📁 Step 2: File Upload Structure

### 2.1 InfinityFree Directory Structure
```
htdocs/
├── index.php (Laravel's public/index.php)
├── .htaccess (Laravel's public/.htaccess)
├── css/ (from public/css)
├── js/ (from public/js)
├── images/ (from public/images)
├── favicon.ico (from public/)
├── app/ (Laravel app folder)
├── bootstrap/ (Laravel bootstrap folder)
├── config/ (Laravel config folder)
├── database/ (Laravel database folder)
├── resources/ (Laravel resources folder)
├── routes/ (Laravel routes folder)
├── storage/ (Laravel storage folder)
├── vendor/ (Composer vendor folder)
└── .env (Environment file)
```

### 2.2 File Upload Steps
1. **Upload Laravel files to `htdocs`:**
   - Copy contents of `public/` folder to `htdocs/`
   - Copy all other Laravel folders to `htdocs/`

2. **Update `htdocs/index.php`:**
   ```php
   <?php

   use Illuminate\Contracts\Http\Kernel;
   use Illuminate\Http\Request;

   define('LARAVEL_START', microtime(true));

   // Check if the application is under maintenance
   if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
       require $maintenance;
   }

   // Register the Composer autoloader
   require __DIR__.'/vendor/autoload.php';

   // Bootstrap Laravel and handle the request
   (require_once __DIR__.'/bootstrap/app.php')
       ->handleRequest(Request::capture());
   ```

---

## ⚙️ Step 3: Environment Configuration

### 3.1 Create `.env` file in `htdocs/`
```env
APP_NAME="GHL ToyyibPay Integration"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:ZlCALv1+wH31+5SR4ZkBaAM1pvdyN0ZQzX2VLoRdfWg=
APP_TIMEZONE=Asia/Kuala_Lumpur
APP_URL=https://yourdomain.infinityfree.app

# Database (Update with your InfinityFree credentials)
DB_CONNECTION=mysql
DB_HOST=sqlXXX.infinityfree.com
DB_PORT=3306
DB_DATABASE=if0_XXXXXXXX_toyyibpayghl
DB_USERNAME=if0_XXXXXXXX
DB_PASSWORD=your_database_password

# Optimized for InfinityFree
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
LOG_LEVEL=error

# GHL Integration (Update OAuth redirect URL)
GHL_CLIENT_ID=68d3da9b07280c17384ef694-mfxyk7wa
GHL_CLIENT_SECRET=1390bb5a-51b7-4d59-b151-adafb670bc06
GHL_SSO_KEY=66d21dab-0e21-498b-9f38-9196aa31c0b2
GHL_OAUTH_REDIRECT=https://yourdomain.infinityfree.app/oauth/callback
GHL_API_BASE_URL=https://services.leadconnectorhq.com

# ToyyibPay
TOYYIBPAY_SANDBOX_URL=https://dev.toyyibpay.com
TOYYIBPAY_PRODUCTION_URL=https://toyyibpay.com
```

### 3.2 Set Proper Permissions
```bash
# Make storage folder writable
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

---

## 🔗 Step 4: URL Structure & Routes

### 4.1 Available URLs
Your application URLs will be:

| Purpose | URL |
|---------|-----|
| **Homepage** | `https://yourdomain.infinityfree.app/` |
| **Config Page** | `https://yourdomain.infinityfree.app/config?location_id=LOCATION_ID` |
| **OAuth Callback** | `https://yourdomain.infinityfree.app/oauth/callback` |
| **Install Success** | `https://yourdomain.infinityfree.app/install-success` |
| **Install Failure** | `https://yourdomain.infinityfree.app/install-failure` |
| **API Endpoints** | `https://yourdomain.infinityfree.app/api/*` |

### 4.2 GHL Marketplace URLs
Update your GHL App settings with:
```
OAuth Redirect URI: https://yourdomain.infinityfree.app/oauth/callback
Payment URL: https://yourdomain.infinityfree.app/api/toyyibpay/create-payment
Query URL: https://yourdomain.infinityfree.app/api/ghl/query
Webhook URL: https://yourdomain.infinityfree.app/api/ghl/webhook/install
```

---

## 🐛 Step 5: Troubleshooting Common Issues

### 5.1 "Sessions table not found" Error
**Solution:** Make sure you ran `infinityfree-setup.sql`

### 5.2 "404 Not Found" for routes
**Solution:**
1. Check `.htaccess` file exists in `htdocs/`
2. Verify file permissions
3. Try accessing: `https://yourdomain.infinityfree.app/index.php/config`

### 5.3 "Internal Server Error"
**Solution:**
1. Check error logs in InfinityFree control panel
2. Set `APP_DEBUG=true` temporarily to see errors
3. Verify database connection

### 5.4 Database Connection Error
**Solution:**
1. Double-check database credentials in `.env`
2. Make sure database exists
3. Test connection through phpMyAdmin

### 5.5 File Permissions Error
**Solution:**
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod 644 .env
```

---

## 🔐 Step 6: Security Checklist

- [ ] `.env` file is protected (check .htaccess)
- [ ] `APP_DEBUG=false` in production
- [ ] Database credentials are secure
- [ ] Log level set to `error`
- [ ] HTTPS enabled (InfinityFree provides free SSL)
- [ ] File permissions set correctly

---

## 📝 Step 7: Testing Your Deployment

### 7.1 Basic Tests
1. **Homepage:** Visit `https://yourdomain.infinityfree.app/`
2. **Config Page:** `https://yourdomain.infinityfree.app/config?location_id=test123`
3. **API Health:** `https://yourdomain.infinityfree.app/api/webhook/health`

### 7.2 GHL Integration Tests
1. Test OAuth flow from GHL marketplace
2. Verify install success/failure pages
3. Test webhook endpoints

---

## 📊 Step 8: Monitoring & Maintenance

### 8.1 Log Monitoring
- Check InfinityFree error logs regularly
- Monitor database usage
- Watch for performance issues

### 8.2 Database Maintenance
- Regularly clean old sessions
- Monitor transaction logs
- Backup important data

---

## 🆘 Support & Resources

### InfinityFree Limitations
- **Memory Limit:** 128MB
- **Execution Time:** 30 seconds
- **Database Size:** 400MB
- **File Storage:** 5GB
- **No SSH access**
- **No background processes/cron jobs**

### Helpful Links
- [InfinityFree Knowledge Base](https://forum.infinityfree.net/)
- [Laravel on Shared Hosting Guide](https://laravel.com/docs/deployment#shared-hosting)
- [PHP Error Troubleshooting](https://forum.infinityfree.net/t/common-php-errors-and-how-to-fix-them/)

---

## ✅ Deployment Checklist

- [ ] Database created and imported
- [ ] Files uploaded to htdocs
- [ ] .env configured with correct credentials
- [ ] .htaccess file in place
- [ ] File permissions set
- [ ] URLs tested
- [ ] GHL app settings updated
- [ ] Security settings verified
- [ ] Error handling tested

**Your GHL ToyyibPay integration should now be live on InfinityFree!** 🎉