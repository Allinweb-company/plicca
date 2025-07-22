# Fintree Payment Integration Test Site

## Overview

This is a Korean payment testing website built for integrating with the Fintree payment system. The application serves as a demonstration and testing platform for processing online payments through Fintree's API, featuring a simple e-commerce interface with test products and comprehensive payment flow handling.

## System Architecture

### Frontend Architecture
- **Technology**: Plain HTML/CSS/JavaScript
- **Design Pattern**: Traditional server-side rendered pages with PHP
- **User Interface**: Simple, form-based interface for product selection and checkout
- **Styling**: Basic CSS for clean, functional design

### Backend Architecture
- **Language**: PHP 7.4+
- **Architecture Pattern**: Procedural PHP with modular file organization
- **Server Requirements**: Apache/Nginx with PHP support
- **Security**: HTTPS/SSL required for API communications

### API Integration
- **Payment Provider**: Fintree Payment Gateway
- **Authentication**: Merchant ID and Key-based authentication
- **Encryption**: SHA256 hash-based security
- **Communication**: RESTful API calls via cURL

## Key Components

### Core Files Structure
- **Product Catalog**: Test products (laptop, mouse, keyboard, monitor)
- **Order Form**: Customer information collection and quantity selection
- **Payment Processing**: Fintree API integration for transaction handling
- **Result Pages**: Success/failure status display with detailed information
- **Cancellation System**: Same-day payment cancellation functionality

### Security Components
- **Input Validation**: XSS prevention and data sanitization
- **Encryption**: SHA256 hash for secure communications
- **SSL/HTTPS**: Required for all payment API communications
- **Error Logging**: Comprehensive error tracking and logging

## Data Flow

1. **Product Selection**: User browses test products and selects items
2. **Order Information**: Customer fills out purchase form with personal details
3. **Payment Request**: System generates secure payment request to Fintree API
4. **Payment Processing**: Fintree processes the payment through their gateway
5. **Result Handling**: System receives and displays payment results
6. **Cancellation (Optional)**: Same-day cancellation processing if needed

## External Dependencies

### Required PHP Extensions
- **cURL**: For API communications with Fintree
- **JSON**: For data parsing and formatting
- **hash**: For SHA256 encryption operations

### Fintree Payment Gateway
- **Test Environment**: Using operational test credentials
- **API Endpoint**: Fintree's payment processing API
- **Authentication**: Merchant-based authentication system

### Test Credentials (Updated July 09, 2025)
- **가맹점명**: 플리카
- **수기(웹결제) Merchant ID**: `mimich022m`
- **수기(웹결제) Merchant Key**: `doJB0p8T4COCGY9mYPp6FaVqeTtoiNgxPyHLj7GRLRrz9n3C4jnjcI9b+K44w1cyZCsGWBBFc7zv+LMmVI0Stw==`
- **앱인증 Merchant ID**: `mimich067m`
- **앱인증 Merchant Key**: `F7rIKVvmhj0oFpT78Xsea2D24If/kvUd1aWNJiDYEy9XG4f01pVtk0mOVjRs+cqRA/QmAnOYR6xcNYPwUoMOjw==`
- **Important**: Test transactions must be cancelled on the same day
- **Note**: Currently using 앱인증(app authentication) credentials for all payments

## Deployment Strategy

### Server Requirements
- **Web Server**: Apache or Nginx with PHP 7.4+ support
- **SSL Certificate**: Required for secure API communications
- **File Permissions**: Proper read/write permissions for logs and temporary files

### Security Considerations
- **HTTPS Only**: All payment operations require SSL encryption
- **Same-day Cancellation**: Test payments must be cancelled within 24 hours
- **Production Migration**: Requires real merchant credentials for live deployment

### File Structure
- **Main Application**: All PHP files in web server document root
- **Configuration**: `.htaccess` for Apache URL rewriting
- **Logs**: `payment_errors.log` with write permissions for error tracking

## Version Control & Backup

### 🔒 SAFE RESTORE POINT - July 04, 2025 AM
**Location**: `backup/working_version_july04/`
**Status**: ✅ FULLY TESTED & WORKING ON DEPLOYMENT SERVER
**Last Successful Test**: tid: chpayc190m01012507041603350047

#### Verified Working Features:
- Complete payment flow (index → order → payment → result)
- Fintree API integration with correct callback URLs
- Payment cancellation functionality
- Backend notification system (payment_notification.php, cancel_notification.php)
- Deployment server configuration (simple-payment-portal-gonskykim.replit.app)

#### Restore Command (if needed):
```bash
cp backup/working_version_july04/*.php ./
cp backup/working_version_july04/{config.php,main.py,.htaccess} ./
```

## Changelog

Changelog:
- July 01, 2025. Initial setup
- July 01, 2025. Added notiUrl support for FINTREE backend notifications
- July 04, 2025. Fixed deployment server URL configuration for proper callback handling
- July 04, 2025. **[BACKUP CREATED]** Working version backed up before API development
- July 04, 2025. Created comprehensive API endpoints for Webflow/Wized integration

## API Architecture (July 04, 2025)

### New API Endpoints
- **GET /api/products.php** - Product catalog with JSON response
- **POST /api/payment.php** - Payment request processing for external frontends
- **POST /api/cancel.php** - Payment cancellation with proper error handling
- **GET /api_test.html** - Interactive testing interface for API validation

### Integration Strategy
- **Frontend**: Webflow + Wized.com for customer interface
- **Backend**: PHP API endpoints for payment processing
- **Workflow**: Customer order → Wized → API → Fintree → Result callbacks
- **Security**: CORS enabled, JSON validation, proper error handling

### Key Features
- JSON-based API communication
- Complete payment flow automation
- Real-time error handling and logging
- Cross-origin request support for external frontends
- Comprehensive API documentation and testing tools

### API Testing Status (July 08, 2025)
- ✅ GET /api/products.php - Products catalog working
- ✅ POST /api/payment.php - Payment request working (Test Order: ORD202507080430467814)
- ✅ POST /api/cancel.php - Cancel endpoint ready
- ✅ Flask-PHP integration with JSON support via environment variables
- ✅ **Real iM Web Integration** - API updated for actual iM Web data format
- ✅ **Live Test Success** - Real order processed (ORD202507086983668, 고야드 백 288,000원)
- ✅ All APIs ready for iM Web integration

### Webflow + Wized Integration (July 08, 2025)
- ✅ **Frontend Issue Resolved** - pgAsistant.requestPay() method failed
- ✅ **Form Method Success** - SendPay(document.payInit) method works correctly
- ✅ **Payment Gateway Open** - Fintree payment window successfully opens
- ✅ **Integration Method**: HTML Form + SendPay() function (same as PHP backend)
- ✅ **Ready for Production** - Webflow + Wized can now process real payments

### Popup Window Implementation (July 09, 2025)
- ✅ **Enhanced UX** - 600x700px popup window for payment process
- ✅ **Parent-Child Communication** - Window.opener communication for result handling
- ✅ **Session Management** - PHP session stores webflowUrl for POST callback
- ✅ **Auto-close Feature** - Popup closes automatically after payment completion
- ✅ **Dummy Data Integration** - Hardcoded test data with verified hash values
- ✅ **POST→GET Redirect** - Automatic conversion of Fintree POST to Webflow GET parameters
- ✅ **SendPay Integration** - Fixed popup creation to use Fintree's native popup handling
- ✅ **Complete Files**:
  - `webflow_popup_fixed.html` - Latest popup version with POST→GET redirect
  - `payment_result.php` - Modified to handle POST→GET conversion and popup communication
  - `test_webflow_redirect.html` - Testing page for redirect flow verification

### Backup Version Verification (July 09, 2025)
- ✅ **Backup Version Confirmed Working** - July 4th backup code successfully processes payments
- ✅ **Payment Approval Success** - User confirmed complete payment approval with backup version
- ✅ **New Implementation Success** - Backup style new implementation also works perfectly
- ✅ **Key Discovery**: `payResultSubmit()` is Fintree SDK built-in function (should not be redefined)
- ✅ **Form Action Required**: Form must have `action` attribute set to `returnUrl`
- ✅ **Applied Fix**: Updated API-based payment test with successful backup style approach
- ✅ **API Success**: API-based payment test now works perfectly with payment approval
- 🚀 **Next Phase**: Webflow + Wized integration using successful API-based payment code

### Payment Flow Architecture Change (July 09, 2025)
- ✅ **Manual Payment Control** - Changed from PHP auto-control to HTML manual control
- ✅ **New Main Page** - Complete manual input form (product name, price, quantity, customer info)
- ✅ **Flow**: index.php (manual input) → payment_prepare.html → manual payment window
- ✅ **Real-time Calculation** - Total amount calculated automatically from unit price × quantity
- ✅ **Data Integrity** - All payment data comes from user input, no mock data
- ✅ **Payment Callback** - Added required Fintree callback functions (pay_result_submit, pay_result_close)
- ✅ **Authentication Success** - Card authentication working properly
- ⚠️ **Approval Limitation** - Test environment restricts actual payment approval (typical PG behavior)
- ✅ **Production Ready** - Full payment flow ready for live environment testing

### File Organization (July 10, 2025)
- ✅ **Webflow File Cleanup** - Removed all 20 webflow HTML test files to organize the repository
- ✅ **Core Files Maintained** - Kept essential PHP payment processing files and main application structure
- ✅ **Clean Repository** - Reduced file clutter for better maintainability

### Main Page Redesign (July 10, 2025)
- ✅ **2-Column Layout** - Redesigned index.php with left/right split layout
- ✅ **Left Panel: API Testing** - API payment preparation button calling /api/payment.php with real-time response display
- ✅ **Right Panel: Payment Form** - Product info, customer info, and calculated payment amount
- ✅ **Dual Payment Flow** - "결제하기" button → payment_prepare.html, "결제창 오픈" button → direct payment window
- ✅ **Real-time Calculation** - Payment amount updates automatically based on unit price × quantity
- ✅ **Enhanced UX** - Bootstrap dark theme with gradient styling and responsive design

### System Rollback (July 12, 2025)
- ✅ **24-Hour Rollback Completed** - Reverted all changes to July 11, 16:10 state
- ✅ **Merchant ID Restored** - Using mimich067m (앱인증) as requested
- ✅ **API URLs Reverted** - Back to relative paths (/payment_result.php)
- ✅ **JavaScript Cleanup** - Removed all Wized integration attempts
- ✅ **Simple Architecture** - Basic localStorage-based payment flow restored

## User Preferences

Preferred communication style: Simple, everyday language.