#!/bin/bash

# DoctorOnTap Performance Check Script
# Quick script to check current performance status

echo "🔍 DoctorOnTap Performance Status Check"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Get script directory
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$DIR"

score=0
max_score=10

# 1. Check OPcache
echo "1️⃣  PHP OPcache:"
if php -r "exit(extension_loaded('Zend OPcache') ? 0 : 1);" 2>/dev/null; then
    if php -r "echo opcache_get_status()['opcache_enabled'] ? 'yes' : 'no';" 2>/dev/null | grep -q "yes"; then
        echo -e "   ${GREEN}✅ Enabled${NC} (+3 points)"
        score=$((score + 3))
    else
        echo -e "   ${YELLOW}⚠️  Loaded but not enabled${NC}"
    fi
else
    echo -e "   ${RED}❌ Not installed${NC}"
fi
echo ""

# 2. Check Redis
echo "2️⃣  Redis Cache:"
if redis-cli ping > /dev/null 2>&1; then
    CACHE_DRIVER=$(php artisan tinker --execute="echo config('cache.default');" 2>/dev/null)
    if [ "$CACHE_DRIVER" == "redis" ]; then
        echo -e "   ${GREEN}✅ Running and configured${NC} (+3 points)"
        score=$((score + 3))
    else
        echo -e "   ${YELLOW}⚠️  Running but not configured${NC}"
        echo "   Current cache: $CACHE_DRIVER"
    fi
else
    echo -e "   ${RED}❌ Not running${NC}"
fi
echo ""

# 3. Check Laravel Caches
echo "3️⃣  Laravel Optimization Caches:"
cached_count=0
if [ -f "bootstrap/cache/config.php" ]; then
    echo -e "   ${GREEN}✅${NC} Config cached"
    cached_count=$((cached_count + 1))
fi
if [ -f "bootstrap/cache/routes-v7.php" ]; then
    echo -e "   ${GREEN}✅${NC} Routes cached"
    cached_count=$((cached_count + 1))
fi
if [ -f "bootstrap/cache/events.php" ]; then
    echo -e "   ${GREEN}✅${NC} Events cached"
    cached_count=$((cached_count + 1))
fi

if [ $cached_count -eq 3 ]; then
    echo -e "   ${GREEN}All caches present${NC} (+2 points)"
    score=$((score + 2))
elif [ $cached_count -gt 0 ]; then
    echo -e "   ${YELLOW}Partial caching${NC} (+1 point)"
    score=$((score + 1))
else
    echo -e "   ${RED}No caches found${NC}"
fi
echo ""

# 4. Check Environment
echo "4️⃣  Production Settings:"
if grep -q "APP_DEBUG=false" .env 2>/dev/null && grep -q "APP_ENV=production" .env 2>/dev/null; then
    echo -e "   ${GREEN}✅ Production mode active${NC} (+1 point)"
    score=$((score + 1))
else
    echo -e "   ${YELLOW}⚠️  Debug/Local mode detected${NC}"
    echo "   Set APP_DEBUG=false & APP_ENV=production"
fi
echo ""

# 5. Check Queue Workers
echo "5️⃣  Queue Workers:"
if pgrep -f "queue:work" > /dev/null; then
    worker_count=$(pgrep -f "queue:work" | wc -l)
    echo -e "   ${GREEN}✅ $worker_count worker(s) running${NC} (+1 point)"
    score=$((score + 1))
else
    echo -e "   ${YELLOW}⚠️  No workers detected${NC}"
    echo "   Run: ./start-queue-worker.sh"
fi
echo ""

# Performance Score
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 PERFORMANCE SCORE: $score/$max_score"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ $score -ge 8 ]; then
    echo -e "${GREEN}🎉 EXCELLENT!${NC} Your app is highly optimized!"
elif [ $score -ge 5 ]; then
    echo -e "${YELLOW}⚡ GOOD${NC} - but room for improvement"
    echo "Run ./optimize-app.sh for quick wins"
else
    echo -e "${RED}🐌 NEEDS OPTIMIZATION${NC}"
    echo "Run ./optimize-app.sh to optimize"
    echo "See PERFORMANCE_OPTIMIZATION.md for full guide"
fi
echo ""

# Estimated Performance
echo "📈 Estimated Performance vs Baseline:"
if [ $score -ge 8 ]; then
    echo "   🚀 4-6x faster"
elif [ $score -ge 5 ]; then
    echo "   ⚡ 2-3x faster"
else
    echo "   🐌 Baseline (unoptimized)"
fi
echo ""

