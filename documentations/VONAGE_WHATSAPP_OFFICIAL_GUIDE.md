# WhatsApp Business API - Official Requirements Guide

Based on [Facebook WhatsApp Developer Documentation](https://developers.facebook.com/docs/whatsapp)

## 📋 Core WhatsApp Messaging Rules

### 1. 24-Hour Customer Care Window

**Key Concept:** WhatsApp has a **24-hour customer care window** during which businesses can freely message end users.

**Window Opens When:**
- ✅ An end user sends a message to the business, OR
- ✅ A business sends a templated message (window starts when user replies)

**Within 24-Hour Window:**
- ✅ Can send **free-form text messages**
- ✅ Can send **media messages** (images, videos, audio, documents)
- ✅ Can send **interactive messages** (buttons, lists)

**Outside 24-Hour Window:**
- ⚠️ **MUST** use **Message Templates (MTM)**
- ⚠️ Cannot send free-form text messages
- ⚠️ Templates must be approved by WhatsApp

### 2. Message Templates (MTM) - Required for First Contact

**Critical Rule:**
> **"WhatsApp requires that a message that is sent to a user for the first time, or that is outside the Customer Care Window, be an MTM message."**

**Template Requirements:**
- ✅ Must be **approved by WhatsApp** before use
- ✅ Only templates in **your own namespace** work
- ✅ Template restrictions:
  - Header/Footer: **60 characters max**
  - Body: **1024 characters max**

**Template Categories:**
1. **Utility** - Transaction updates, billing statements
2. **Authentication** - OTP, verification codes
3. **Marketing** - Promotional messages (requires opt-in)

### 3. Business Approval

- ✅ WhatsApp Business Solution messages can only be sent by **approved businesses**
- ✅ Business profile will have a **green verified label**
- ⚠️ **Limited Availability** - Vonage cannot guarantee WhatsApp account approval

## 🔧 Implementation in Your Code

### Current Methods Available:

#### 1. Text Message (Within 24-Hour Window Only)
```php
$service->sendWhatsAppMessage($to, $message);
```
**Use When:**
- ✅ User has contacted you within last 24 hours
- ✅ User has replied to your template message

**Cannot Use:**
- ❌ First contact with user
- ❌ Outside 24-hour window

#### 2. Template Message (Required for First Contact)
```php
$service->sendWhatsAppTemplate(
    $to,
    'template_name',    // Approved template name
    'en',              // Language code
    ['param1', 'param2'] // Template parameters
);
```
**Use When:**
- ✅ First contact with user
- ✅ Outside 24-hour window
- ✅ Need to initiate conversation

### Template Name Format

According to Vonage documentation, template names can be:
- Simple: `"welcome_message"`
- With namespace: `"namespace:template_name"` (e.g., `"whatsapp:hugotemplate"`)

**Important:** Only templates in your own namespace work. Using templates from other namespaces returns error code 1022.

## 🎯 Best Practices

### 1. Always Check 24-Hour Window Status

Before sending a message, check if:
- User has contacted you within 24 hours
- 24-hour window is still open

### 2. Use Templates for First Contact

**Never** send text messages to new contacts. Always use approved templates.

### 3. Template Approval Process

1. Create template in WhatsApp Manager
2. Submit for WhatsApp approval
3. Wait for approval (can take time)
4. Use approved template name in code

### 4. Handle Template Errors

Common errors:
- **1022** - Template not in your namespace
- **1020** - Template uses deprecated "fallback" locale (removed April 8, 2020)
- Template not approved

## 📊 Message Flow

```
┌─────────────────────────────────────┐
│   First Contact (New User)          │
│   ↓                                  │
│   Send Template Message (MTM)       │
│   ↓                                  │
│   User Replies                       │
│   ↓                                  │
│   24-Hour Window Opens               │
│   ↓                                  │
│   Can Send Text Messages             │
│   ↓                                  │
│   Window Expires (24 hours)          │
│   ↓                                  │
│   Must Use Template Again            │
└─────────────────────────────────────┘
```

## ⚠️ Current Issue Resolution

**Your Error:** "You did not provide correct credentials"

**Root Cause:** Trying to send **text message** to new contact (requires **template**)

**Solution:**
1. Create/approve template in WhatsApp Manager
2. Use `sendWhatsAppTemplate()` for first contact
3. After user replies, use `sendWhatsAppMessage()` within 24-hour window

## 📚 References

- [Facebook WhatsApp Developer Documentation](https://developers.facebook.com/docs/whatsapp)
- [Vonage WhatsApp Getting Started](https://developer.vonage.com/en/messages/concepts/getting-started/getting-started-whatsapp)
- [WhatsApp Message Templates](https://developers.facebook.com/docs/whatsapp/api/messages/message-templates)

## ✅ Your Code Status

**Already Implemented:**
- ✅ `sendWhatsAppMessage()` - For within 24-hour window
- ✅ `sendWhatsAppTemplate()` - For first contact/outside window
- ✅ Template parameter support
- ✅ Language support
- ✅ Business Number ID configuration

**What You Need:**
- ⚠️ Approved template in WhatsApp Manager
- ⚠️ Use template for first contact (not text message)

