# Product Requirements Document: GHL-ToyyibPay Integration Plugin

## Introduction/Overview

This document outlines the requirements for developing a Laravel-based plugin that serves as a payment gateway integration between GoHighLevel (GHL) and ToyyibPay. The plugin enables GHL users to accept payments from Malaysian customers through ToyyibPay's payment gateway, providing a seamless payment experience within the GHL ecosystem.

**Problem:** GHL users in Malaysia lack a native payment gateway integration that supports local payment methods and Malaysian Ringgit (MYR) transactions.

**Goal:** Create a reliable, secure plugin that bridges GHL's payment system with ToyyibPay's payment gateway, allowing Malaysian businesses to process payments efficiently.

## Goals

1. Enable GHL users to install and configure ToyyibPay payment gateway through GHL Marketplace
2. Provide secure API key management for ToyyibPay integration (test and live environments)
3. Process payment transactions seamlessly between GHL invoices/funnels and ToyyibPay
4. Handle payment status callbacks and notify GHL of transaction outcomes
5. Ensure plugin installation and uninstallation work correctly with proper webhook handling

## User Stories

1. **As a GHL user**, I want to install the ToyyibPay plugin from GHL Marketplace so that I can accept Malaysian payments.

2. **As a business owner**, I want to input my ToyyibPay API keys (test and live) so that I can configure the payment gateway for my business.

3. **As a customer**, I want to make payments through ToyyibPay when purchasing from GHL invoices/funnels so that I can use familiar Malaysian payment methods.

4. **As a GHL user**, I want to receive automatic payment status updates so that my invoices are marked as paid when transactions are successful.

5. **As a system administrator**, I want proper installation/uninstallation handling so that the plugin integrates cleanly with GHL's system.

## Functional Requirements

1. **Plugin Installation & Configuration**
   - 1.1 The system must provide a GHL Marketplace-compatible plugin structure
   - 1.2 The system must display installation success/failure status to users
   - 1.3 The system must provide a configuration interface for API key input per location
   - 1.4 The system must validate ToyyibPay API keys upon input for each location
   - 1.5 The system must support both test and live API key environments per location
   - 1.6 The system must generate unique publishable keys for each location
   - 1.7 The system must switch between ToyyibPay production and sandbox environments based on live/test mode

2. **Payment Processing**
   - 2.1 The system must create ToyyibPay invoices when GHL payment requests are received
   - 2.2 The system must display payment pages within GHL iframe
   - 2.3 The system must redirect customers to ToyyibPay payment gateway
   - 2.4 The system must handle payment completion and redirect back to GHL

3. **Webhook Management**
   - 3.1 The system must receive and process ToyyibPay payment status callbacks
   - 3.2 The system must notify GHL of payment success/failure status
   - 3.3 The system must handle plugin installation webhook from GHL
   - 3.4 The system must handle plugin uninstallation webhook from GHL
   - 3.5 The system must validate webhook authenticity and security

4. **Backend Integration**
   - 4.1 The system must implement GHL OAuth token exchange using POST https://services.leadconnectorhq.com/oauth/token
   - 4.2 The system must register as payment provider using POST https://services.leadconnectorhq.com/payments/custom-provider/provider with proper Authorization header and API version
   - 4.3 The system must connect API keys to GHL using POST https://services.leadconnectorhq.com/payments/custom-provider/connect with proper JSON structure for live/test configurations
   - 4.4 The system must send payment status webhooks to GHL using POST https://backend.leadconnectorhq.com/payments/custom-provider/webhook
   - 4.5 The system must provide query/verification endpoint POST /api/query for GHL connection verification
   - 4.6 The system must integrate with ToyyibPay's API for invoice creation using proper environment URLs
   - 4.7 The system must support ToyyibPay production (https://toyyibpay.com) and sandbox (https://dev.toyyibpay.com) environments
   - 4.8 The system must maintain secure storage of location_id, access_token, refresh_token, company_id
   - 4.9 The system must store and manage payment information list
   - 4.10 The system must maintain secure storage of ToyyibPay API credentials for each location separately
   - 4.11 The system must store live/test API keys and generated publishable keys per location
   - 4.12 The system must log transaction activities for debugging purposes

5. **Frontend Interface**
   - 5.1 The system must provide a user-friendly API key configuration page
   - 5.2 The system must display installation status clearly
   - 5.3 The system must show payment processing status to customers
   - 5.4 The system must be responsive and work within GHL's iframe constraints

## Non-Goals (Out of Scope)

1. Multi-currency support (focus on MYR only)
2. Recurring payment handling
3. Refund management interface
4. Transaction reporting dashboard
5. Multiple payment gateway support
6. Customer data storage beyond transaction requirements
7. Advanced fraud detection
8. Custom branding options

## Design Considerations

1. **UI/UX Requirements:**
   - Clean, minimal interface that matches GHL's design language
   - Mobile-responsive design for payment pages
   - Clear error messages and status indicators
   - Loading states for API operations

2. **iframe Compatibility:**
   - Payment pages must work properly within GHL's iframe
   - Responsive design for various iframe sizes
   - Proper handling of iframe communication

## Technical Considerations

1. **Laravel Framework:**
   - Built specifically on Laravel 12 (latest version)
   - Utilize Laravel 12's enhanced features and performance improvements
   - Follow Laravel 12 best practices and conventions
   - Use Laravel 12's built-in security features and middleware
   - Leverage Laravel 12's improved routing and controller structure

2. **Security Requirements:**
   - Secure storage of API keys using Laravel's encryption
   - HTTPS-only communication
   - Webhook signature verification
   - Input validation and sanitization
   - OAuth token secure handling and refresh mechanism
   - Proper Bearer token authentication for GHL API calls
   - API versioning compliance (Version: 2021-07-28)

3. **GHL API Integration Endpoints:**
   - **OAuth Token Exchange:** POST https://services.leadconnectorhq.com/oauth/token
     - Body (application/x-www-form-urlencoded): client_id, client_secret, grant_type
     - grant_type values: authorization_code, refresh_token, client_credentials
     - Used for app installation and token refresh to access GHL resources
   - **Register Provider:** POST https://services.leadconnectorhq.com/payments/custom-provider/provider
     - Query Parameters: locationId (required)
     - Headers: Authorization (Bearer token - Location type), Version (2021-07-28)
     - Body: name, description, paymentsUrl, queryUrl, imageUrl
     - Register ToyyibPay as custom payment provider in GHL for specific location
   - **Connect Keys:** POST https://services.leadconnectorhq.com/payments/custom-provider/connect
     - Query Parameters: locationId (required)
     - Headers: Authorization (Bearer token - Location type), Version (2021-07-28)
     - Body (JSON): 
       - live: { apiKey, publishableKey }
       - test: { apiKey, publishableKey }
     - Complete payment provider configuration with plugin-generated unique keys
   - **Payment Status Webhook:** POST https://backend.leadconnectorhq.com/payments/custom-provider/webhook
     - Body: event, chargeId, ghlTransactionId, chargeSnapshot, locationId, apiKey
     - Notify GHL of payment status changes
   - **Query/Verification:** POST /api/query (plugin endpoint)
     - Body: type=verify
     - GHL verification endpoint (returns success: false as per requirement)

4. **API Integration:**
   - RESTful API design for GHL communication using Laravel 12's API resources
   - Proper error handling and timeout management with Laravel 12's HTTP client
   - Rate limiting using Laravel 12's built-in rate limiting features
   - API versioning support for future compatibility

5. **ToyyibPay Configuration:**
   - **Production Environment:** TOYYIBPAY_API=https://toyyibpay.com
   - **Sandbox Environment:** TOYYIBPAY_API_SANDBOX=https://dev.toyyibpay.com
   - Support for environment switching based on live/test mode
   - Note: Users must register separate accounts at dev.toyyibpay.com for sandbox testing
   - Sandbox mode provides no real transactions for safe testing

6. **Database:**
   - Use Laravel 12's Eloquent ORM for database operations
   - Laravel 12 migrations for database schema management
   - **Required Data Storage:**
     - location_id (GHL location identifier)
     - access_token (OAuth access token from GHL)
     - refresh_token (OAuth refresh token from GHL)
     - company_id (Company identifier)
     - api_credentials (ToyyibPay API credentials for each location)
       - live_api_key (ToyyibPay live API key per location)
       - test_api_key (ToyyibPay test API key per location)
       - live_publishable_key (Plugin-generated unique live key per location)
       - test_publishable_key (Plugin-generated unique test key per location)
     - payment_information (List of payment details/transactions)
     - transaction IDs, status, timestamps
   - Secure storage of sensitive tokens using Laravel's encryption
   - No direct sensitive payment data storage (card numbers, etc.)
   - Proper database indexing for performance using Laravel 12's schema builder

## Success Metrics

1. **Technical Metrics:**
   - Payment success rate > 95%
   - API response time < 3 seconds
   - Plugin installation success rate > 98%

2. **User Experience Metrics:**
   - Configuration completion rate > 90%
   - Customer payment completion rate > 85%
   - Support ticket reduction related to payment issues

## Open Questions

1. ToyyibPay callback URL configuration requirements
2. Error handling preferences for failed API calls  
3. Logging level requirements for production environment
4. Plugin update mechanism through GHL Marketplace
5. Specific OAuth client_id and client_secret provisioning process
6. Image URL requirements for payment provider logo in GHL
7. Unique key generation strategy for live/test apiKey and publishableKey
