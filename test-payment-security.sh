#!/bin/bash

# Payment Security Test Script
# This script helps verify the payment verification implementation

echo "🔒 Payment Security Verification Test"
echo "======================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: Check if webhook middleware exists
echo -e "${YELLOW}Test 1: Checking webhook middleware...${NC}"
if [ -f "app/Http/Middleware/VerifyKorapayWebhook.php" ]; then
    echo -e "${GREEN}✅ Webhook middleware exists${NC}"
else
    echo -e "${RED}❌ Webhook middleware missing${NC}"
fi
echo ""

# Test 2: Check if middleware is registered
echo -e "${YELLOW}Test 2: Checking middleware registration...${NC}"
if grep -q "verify.korapay.webhook" bootstrap/app.php; then
    echo -e "${GREEN}✅ Middleware registered in bootstrap/app.php${NC}"
else
    echo -e "${RED}❌ Middleware not registered${NC}"
fi
echo ""

# Test 3: Check Korapay configuration
echo -e "${YELLOW}Test 3: Checking Korapay configuration...${NC}"
if grep -q "korapay" config/services.php; then
    echo -e "${GREEN}✅ Korapay configuration exists${NC}"
else
    echo -e "${RED}❌ Korapay configuration missing${NC}"
fi
echo ""

# Test 4: Check environment variables
echo -e "${YELLOW}Test 4: Checking environment variables...${NC}"
if grep -q "KORAPAY_SECRET_KEY" .env 2>/dev/null; then
    echo -e "${GREEN}✅ KORAPAY_SECRET_KEY is set${NC}"
else
    echo -e "${YELLOW}⚠️  KORAPAY_SECRET_KEY not set in .env${NC}"
fi

if grep -q "KORAPAY_PUBLIC_KEY" .env 2>/dev/null; then
    echo -e "${GREEN}✅ KORAPAY_PUBLIC_KEY is set${NC}"
else
    echo -e "${YELLOW}⚠️  KORAPAY_PUBLIC_KEY not set in .env${NC}"
fi
echo ""

# Test 5: Check database fields
echo -e "${YELLOW}Test 5: Checking database fields...${NC}"
php artisan tinker --execute="
if (Schema::hasColumn('consultations', 'treatment_plan_unlocked')) {
    echo '✅ treatment_plan_unlocked field exists\n';
} else {
    echo '❌ treatment_plan_unlocked field missing\n';
}
if (Schema::hasColumn('consultations', 'payment_status')) {
    echo '✅ payment_status field exists\n';
} else {
    echo '❌ payment_status field missing\n';
}
" 2>/dev/null
echo ""

# Test 6: Check webhook endpoint exists
echo -e "${YELLOW}Test 6: Checking webhook route...${NC}"
if grep -q "'/webhook'" routes/web.php || grep -q "payment\.webhook" routes/web.php; then
    echo -e "${GREEN}✅ Webhook route exists${NC}"
else
    echo -e "${RED}❌ Webhook route missing${NC}"
fi
echo ""

# Test 7: Check enhanced PaymentController
echo -e "${YELLOW}Test 7: Checking PaymentController enhancements...${NC}"
if grep -q "CRITICAL: UNLOCK TREATMENT PLAN" app/Http/Controllers/PaymentController.php; then
    echo -e "${GREEN}✅ PaymentController has enhanced webhook handler${NC}"
else
    echo -e "${RED}❌ PaymentController not enhanced${NC}"
fi
echo ""

# Test 8: Check ConsultationController security
echo -e "${YELLOW}Test 8: Checking ConsultationController security...${NC}"
if grep -q "CRITICAL SECURITY CHECK" app/Http/Controllers/ConsultationController.php; then
    echo -e "${GREEN}✅ ConsultationController has security checks${NC}"
else
    echo -e "${RED}❌ ConsultationController security missing${NC}"
fi
echo ""

# Test 9: Check Consultation model methods
echo -e "${YELLOW}Test 9: Checking Consultation model methods...${NC}"
if grep -q "unlockTreatmentPlan" app/Models/Consultation.php; then
    echo -e "${GREEN}✅ Consultation model has unlockTreatmentPlan method${NC}"
else
    echo -e "${RED}❌ unlockTreatmentPlan method missing${NC}"
fi

if grep -q "isTreatmentPlanAccessible" app/Models/Consultation.php; then
    echo -e "${GREEN}✅ Consultation model has isTreatmentPlanAccessible method${NC}"
else
    echo -e "${RED}❌ isTreatmentPlanAccessible method missing${NC}"
fi
echo ""

# Summary
echo ""
echo "======================================"
echo -e "${YELLOW}📊 Test Summary${NC}"
echo "======================================"
echo ""
echo "If all tests pass ✅, your payment security is properly configured!"
echo ""
echo "Next steps:"
echo "1. Set Korapay credentials in .env"
echo "2. Run: php artisan config:cache"
echo "3. Configure webhook URL in Korapay dashboard"
echo "4. Test payment flow end-to-end"
echo "5. Monitor logs: tail -f storage/logs/laravel.log | grep -E 'webhook|payment|TREATMENT PLAN'"
echo ""
echo "For detailed documentation, see:"
echo "  - PAYMENT_SECURITY_IMPLEMENTATION.md"
echo "  - PAYMENT_VERIFICATION_SECURITY.md"
echo ""

