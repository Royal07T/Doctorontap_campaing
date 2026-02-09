# WhatsApp First Contact - Template Message Requirement

## ⚠️ Important: WhatsApp Requirements

According to WhatsApp documentation:

> **"WhatsApp requires that a message that is sent to a user for the first time, or that is outside the Customer Care Window, be an MTM (Message Template) message."**

## 🔍 Why Your Test Failed

**Current Test:**
```php
sendWhatsAppMessage($to, $message) // ❌ Text message
```

**Problem:**
- You're trying to send a **text message** to a number that hasn't initiated contact
- WhatsApp **requires** a **template message** for first contact
- Text messages only work **within 24-hour window** after user contacts you

## ✅ Solution: Use Template Messages

### For First Contact:
```php
// Use template message (required)
$result = $service->sendWhatsAppTemplate(
    $to,
    'welcome_message',  // Approved template name
    'en',               // Language
    ['John', 'DoctorOnTap'] // Template parameters
);
```

### For Within 24-Hour Window:
```php
// Can use text message (if user contacted you first)
$result = $service->sendWhatsAppMessage($to, $message);
```

## 📋 Template Requirements

### 1. Template Must Be Approved
- Templates need WhatsApp approval before use
- Contact Vonage Account Manager to submit templates
- Approval can take time

### 2. Template Must Be In Your Namespace
- Only templates created in your own namespace work
- Using templates from other namespaces returns error 1022

### 3. Template Restrictions
- Header/Footer: 60 characters max
- Body: 1024 characters max

## 🎯 Current Status

**Your Configuration:**
- ✅ WhatsApp Business Number ID: `2347089146888`
- ✅ Production mode enabled
- ✅ Code supports templates
- ⚠️ **Need:** Approved template in WhatsApp Manager

## 📝 Next Steps

### Step 1: Create Template in WhatsApp Manager
1. Go to WhatsApp Manager
2. Create a template (e.g., "welcome_message")
3. Submit for approval
4. Wait for approval

### Step 2: Test with Template
```php
$result = $service->sendWhatsAppTemplate(
    '+2347081114942',
    'welcome_message', // Your approved template name
    'en',
    ['Patient Name', 'DoctorOnTap'] // Template parameters
);
```

### Step 3: After User Replies
- Once user replies, 24-hour window opens
- Can then send text messages freely

## ✅ Code Already Supports This

Your `VonageService` already has:
- ✅ `sendWhatsAppTemplate()` method
- ✅ Template parameter support
- ✅ Language support

**You just need:**
- ⚠️ Approved template in WhatsApp Manager
- ⚠️ Use template for first contact instead of text

## 🔧 Quick Fix for Testing

If you want to test text messages:
1. User must send you a message first (opens 24-hour window)
2. Then you can reply with text messages
3. Or use template message to initiate contact

## 📊 Summary

| Scenario | Message Type Required |
|----------|----------------------|
| **First Contact** | ✅ Template Message (MTM) |
| **Within 24h Window** | ✅ Text Message (OK) |
| **Outside 24h Window** | ✅ Template Message (MTM) |

**Your error is likely because you're trying to send a text message for first contact, which requires a template message instead.**

