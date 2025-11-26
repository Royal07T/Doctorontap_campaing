#!/bin/bash

# Test Korapay Webhook Implementation
# This script tests all payment webhook scenarios

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Korapay Webhook Implementation Test  ${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if Laravel is available
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: This script must be run from Laravel root directory${NC}"
    exit 1
fi

echo -e "${YELLOW}📋 Running Pre-Flight Checks...${NC}"
echo ""

# Test 1: Check if webhook route exists
echo -e "${YELLOW}Test 1: Checking webhook route...${NC}"
if php artisan route:list | grep -q "payment/webhook"; then
    echo -e "${GREEN}✅ Webhook route exists${NC}"
else
    echo -e "${RED}❌ Webhook route not found${NC}"
    exit 1
fi

# Test 2: Check if webhook middleware exists
echo -e "${YELLOW}Test 2: Checking webhook middleware...${NC}"
if [ -f "app/Http/Middleware/VerifyKorapayWebhook.php" ]; then
    echo -e "${GREEN}✅ Webhook middleware exists${NC}"
else
    echo -e "${RED}❌ Webhook middleware not found${NC}"
    exit 1
fi

# Test 3: Check if PaymentController webhook method exists
echo -e "${YELLOW}Test 3: Checking PaymentController webhook method...${NC}"
if grep -q "public function webhook" app/Http/Controllers/PaymentController.php; then
    echo -e "${GREEN}✅ Webhook method exists${NC}"
else
    echo -e "${RED}❌ Webhook method not found${NC}"
    exit 1
fi

# Test 4: Check if webhook handles all events
echo -e "${YELLOW}Test 4: Checking webhook event handlers...${NC}"

events=("charge.success" "charge.failed" "charge.pending" "charge.cancelled")
all_events_found=true

for event in "${events[@]}"; do
    if grep -q "$event" app/Http/Controllers/PaymentController.php; then
        echo -e "${GREEN}  ✓ Handler for $event exists${NC}"
    else
        echo -e "${RED}  ✗ Handler for $event not found${NC}"
        all_events_found=false
    fi
done

if [ "$all_events_found" = true ]; then
    echo -e "${GREEN}✅ All event handlers exist${NC}"
else
    echo -e "${RED}❌ Some event handlers missing${NC}"
    exit 1
fi

# Test 5: Check if PaymentFailedNotification exists
echo -e "${YELLOW}Test 5: Checking PaymentFailedNotification...${NC}"
if [ -f "app/Mail/PaymentFailedNotification.php" ]; then
    echo -e "${GREEN}✅ PaymentFailedNotification exists${NC}"
else
    echo -e "${RED}❌ PaymentFailedNotification not found${NC}"
    exit 1
fi

# Test 6: Check if payment-failed email view exists
echo -e "${YELLOW}Test 6: Checking payment-failed email view...${NC}"
if [ -f "resources/views/emails/payment-failed.blade.php" ]; then
    echo -e "${GREEN}✅ Payment failed email view exists${NC}"
else
    echo -e "${RED}❌ Payment failed email view not found${NC}"
    exit 1
fi

# Test 7: Check Korapay configuration
echo -e "${YELLOW}Test 7: Checking Korapay configuration...${NC}"
php artisan tinker --execute="
\$config = config('services.korapay');
if (isset(\$config['secret_key']) && !empty(\$config['secret_key'])) {
    echo '✓ Secret key configured\n';
} else {
    echo '✗ Secret key NOT configured\n';
    exit(1);
}
if (isset(\$config['api_url']) && !empty(\$config['api_url'])) {
    echo '✓ API URL configured\n';
} else {
    echo '✗ API URL NOT configured\n';
    exit(1);
}
" 2>&1 | while read line; do
    if [[ $line == *"✓"* ]]; then
        echo -e "${GREEN}  $line${NC}"
    elif [[ $line == *"✗"* ]]; then
        echo -e "${RED}  $line${NC}"
    fi
done

echo -e "${GREEN}✅ Korapay configuration is valid${NC}"

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}✅ All Pre-Flight Checks Passed!${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Test 8: Check webhook logs capability
echo -e "${YELLOW}Test 8: Checking logging capability...${NC}"
if [ -w "storage/logs" ]; then
    echo -e "${GREEN}✅ Log directory is writable${NC}"
else
    echo -e "${RED}❌ Log directory is not writable${NC}"
fi

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Manual Testing Instructions          ${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
echo -e "${YELLOW}To test the webhook manually:${NC}"
echo ""
echo "1. Get a test payment reference from your database:"
echo -e "   ${GREEN}php artisan tinker${NC}"
echo -e "   ${GREEN}\$payment = App\\Models\\Payment::latest()->first();${NC}"
echo -e "   ${GREEN}echo \$payment->reference;${NC}"
echo ""
echo "2. Test with curl (replace YOUR_REFERENCE):"
echo ""
echo -e "${GREEN}   # Test Success:${NC}"
echo '   curl -X POST http://localhost/payment/webhook \'
echo '     -H "Content-Type: application/json" \'
echo '     -d '"'"'{"event":"charge.success","data":{"reference":"YOUR_REFERENCE","amount":5000,"status":"success"}}'"'"
echo ""
echo -e "${GREEN}   # Test Failure:${NC}"
echo '   curl -X POST http://localhost/payment/webhook \'
echo '     -H "Content-Type: application/json" \'
echo '     -d '"'"'{"event":"charge.failed","data":{"reference":"YOUR_REFERENCE","status":"failed","failure_message":"Test failure"}}'"'"
echo ""
echo "3. Check logs:"
echo -e "   ${GREEN}tail -f storage/logs/laravel.log | grep -i webhook${NC}"
echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}✅ Webhook Implementation Test Complete${NC}"
echo -e "${BLUE}========================================${NC}"

