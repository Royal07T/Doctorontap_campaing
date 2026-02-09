# WhatsApp Messaging - Requirements & Implementation Guide

## 📋 Key Requirements from WhatsApp

### 1. Business Approval Required
- ✅ WhatsApp Business Solution messages can only be sent by **approved businesses**
- ✅ Business profile will have a **green verified label**
- ⚠️ **Limited Availability** - Vonage cannot guarantee you will receive a WhatsApp account

### 2. 24-Hour Customer Care Window
- ✅ Within 24 hours: Can send **free-form text messages**
- ⚠️ Outside 24 hours: Must use **Message Templates (MTM)**
- ✅ Window starts when:
  - User sends message to business, OR
  - Business sends templated message (window starts when user replies)

### 3. Message Templates (MTM)
- ⚠️ **Required** for first contact or outside 24-hour window
- ⚠️ Templates must be **approved by WhatsApp** before use
- ⚠️ Only templates in **your own namespace** work
- ⚠️ Templates subject to restrictions:
  - Header/Footer: 60 characters max
  - Body: 1024 characters max

### 4. Pricing Model
- **Per-Message Pricing (PMP)** - Effective July 1, 2025
- Vonage platform fee: **Per message**
- WhatsApp fee: **Per template message** (varies by category)
- Template categories:
  - **Utility** - Transaction updates, billing
  - **Authentication** - OTP, verification codes
  - **Marketing** - Promotional messages (requires opt-in)

## 🔍 Current Issue Analysis

**Error:** "You did not provide correct credentials"

**Possible Causes:**
1. ⚠️ **Account Status** - Business may not be fully approved by WhatsApp
2. ⚠️ **First Contact** - Trying to send text message without template (outside 24-hour window)
3. ⚠️ **Limited Availability** - Account might be in test/limited mode
4. ⚠️ **Template Required** - Need to use Message Template for initial contact

## ✅ Current Implementation

### What We Have:
- ✅ WhatsApp service configured
- ✅ Business Number ID configured (`2347089146888`)
- ✅ Template message support implemented
- ✅ Text message support implemented
- ✅ Production mode enabled

### What Might Be Missing:
- ⚠️ **Template Approval** - Templates need WhatsApp approval
- ⚠️ **First Contact** - May need to use template instead of text
- ⚠️ **Account Approval** - Business may need full approval

## 🎯 Solution: Use Template Messages

For **first contact** or **outside 24-hour window**, you **must** use Message Templates.

### Current Test Issue:
We're trying to send a **text message** to a number that hasn't initiated contact. This requires a **template message** instead.

### Fix: Send Template Message First

```php
// Instead of text message for first contact:
$result = $service->sendWhatsAppMessage($to, $message); // ❌ Won't work for first contact

// Use template message for first contact:
$result = $service->sendWhatsAppTemplate(
    $to,
    'welcome_message', // Approved template name
    'en',
    ['John', 'DoctorOnTap'] // Template parameters
); // ✅ Required for first contact
```

## 📝 Implementation Recommendations

### 1. Check Template Availability
- Verify you have approved templates in WhatsApp Manager
- Templates must be in your namespace
- Contact Vonage Account Manager to submit templates

### 2. Use Templates for First Contact
- Always use template messages for new contacts
- Text messages only work within 24-hour window

### 3. Account Status
- Verify business is fully approved by WhatsApp
- Check if account has production access (not just test)

## 🔧 Code Updates Needed

The code already supports templates, but we should:
1. ✅ Default to template messages for first contact
2. ✅ Use text messages only within 24-hour window
3. ✅ Handle template approval status

## 📊 Summary

**Current Status:**
- ✅ Code: Correctly configured
- ✅ Configuration: Production mode, Business Number ID set
- ⚠️ **Issue:** Likely need to use **template message** for first contact
- ⚠️ **Account:** May need business approval or template approval

**Next Steps:**
1. Create/approve WhatsApp templates in WhatsApp Manager
2. Use template messages for first contact
3. Verify business approval status with Vonage

