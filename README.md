# Fintree Payment Integration System

🚀 **Ready-to-Deploy Korean Payment System**

A complete Fintree payment gateway integration with mobile-responsive popup interface, built for seamless e-commerce transactions.

## ✨ Key Features

- **Mobile-Optimized**: 500x600 popup window with responsive design
- **Secure Payment Flow**: SHA256 encryption and HTTPS-only communication
- **Real-time Processing**: Instant amount calculation and payment validation
- **Admin Management**: Transaction history and same-day cancellation system
- **URL Security**: Automatic parameter masking for user privacy
- **Error Recovery**: F12 developer mode guidance for troubleshooting

## 🎯 Quick Start

1. **Deploy to Replit**:
   - Import this repository to your Replit account
   - Upgrade to Replit Core plan ($20/month) for 24/7 hosting
   - Run the project - no additional configuration needed!

2. **Test Payment Flow**:
   - Visit your deployed URL
   - Click "시작하기" to open payment popup
   - Test with sample products and customer information
   - Verify payment processing with Fintree test environment

3. **Production Setup**:
   - Update Fintree merchant credentials in `config.php`
   - Configure custom domain (optional)
   - Enable production payment processing

## 🛠 System Architecture

- **Frontend**: Responsive HTML/CSS/JavaScript with Bootstrap Dark Theme
- **Backend**: PHP 7.4+ with Flask wrapper for Replit compatibility
- **Payment Gateway**: Fintree API integration with mimich067m credentials
- **Database**: File-based logging system for transaction records

## 📱 Mobile-First Design

The system automatically switches to mobile-optimized UI when accessed through the 500x600 popup window, ensuring perfect user experience across all devices.

## 🔒 Security Features

- HTTPS-only API communication
- SHA256 hash-based authentication
- Automatic URL parameter masking
- Secure session management
- Comprehensive error logging

## 📋 File Structure

```
├── index.php              # Main landing page
├── mobile_payment.php     # Popup payment interface
├── payment_result.php     # Payment success/failure handling
├── cancel.php             # Admin transaction management
├── config.php             # Fintree API configuration
├── api/                   # RESTful API endpoints
└── backup/                # Version control backups
```

## 🆘 Support

- **Technical Documentation**: See `DEPLOYMENT_GUIDE.md`
- **Korean Documentation**: See `replit_korean.md`
- **Project History**: See `replit.md`

## 📄 License

Ready for commercial deployment. Includes complete handover documentation for client migration.

---

**Built for Replit | Ready for Production | Mobile-Optimized**