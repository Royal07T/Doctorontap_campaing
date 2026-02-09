# Vonage Network Connectivity Troubleshooting

## Issue: SSL Connection Timeout

If you're seeing errors like:
```
cURL error 28: SSL connection timeout for https://rest.nexmo.com/sms/json
```

This indicates your server cannot establish a network connection to Vonage's API servers.

## Diagnosis

### 1. Test Network Connectivity

```bash
# Test DNS resolution
nslookup rest.nexmo.com

# Test ping (may be blocked by firewall)
ping -c 3 rest.nexmo.com

# Test HTTPS connection
curl -I --connect-timeout 10 https://rest.nexmo.com/sms/json

# Test with verbose output
curl -v --connect-timeout 10 https://rest.nexmo.com/sms/json
```

### 2. Check Firewall Rules

Ensure outbound HTTPS (port 443) connections are allowed:

```bash
# Check if port 443 is accessible
telnet rest.nexmo.com 443

# Or use nc (netcat)
nc -zv rest.nexmo.com 443
```

### 3. Check Proxy Settings

If your server is behind a proxy, configure it:

```bash
# Set proxy environment variables
export HTTP_PROXY=http://proxy.example.com:8080
export HTTPS_PROXY=http://proxy.example.com:8080
export NO_PROXY=localhost,127.0.0.1
```

Or configure in PHP:
```php
// In bootstrap/app.php or config
putenv('HTTP_PROXY=http://proxy.example.com:8080');
putenv('HTTPS_PROXY=http://proxy.example.com:8080');
```

## Solutions

### Solution 1: Check Server Network Configuration

1. **Verify outbound internet access:**
   ```bash
   curl -I https://www.google.com
   ```

2. **Check firewall rules:**
   ```bash
   # Ubuntu/Debian
   sudo ufw status
   sudo iptables -L -n
   
   # CentOS/RHEL
   sudo firewall-cmd --list-all
   ```

3. **Allow outbound HTTPS:**
   ```bash
   # Ubuntu/Debian
   sudo ufw allow out 443/tcp
   
   # CentOS/RHEL
   sudo firewall-cmd --add-service=https --permanent
   sudo firewall-cmd --reload
   ```

### Solution 2: Configure Proxy (if behind corporate firewall)

Add to your `.env`:
```env
HTTP_PROXY=http://proxy.example.com:8080
HTTPS_PROXY=http://proxy.example.com:8080
NO_PROXY=localhost,127.0.0.1
```

### Solution 3: Check DNS Resolution

```bash
# Test DNS
dig rest.nexmo.com
nslookup rest.nexmo.com

# If DNS fails, try using IP directly (not recommended for SSL)
# Or configure custom DNS servers
```

### Solution 4: SSL Certificate Issues

If SSL verification is failing:

```bash
# Test SSL connection
openssl s_client -connect rest.nexmo.com:443 -showcerts

# Check certificate validity
echo | openssl s_client -connect rest.nexmo.com:443 2>/dev/null | openssl x509 -noout -dates
```

### Solution 5: Increase Timeout Settings

The code has been updated to use shorter timeouts (15s connection, 10s SSL). If you need longer timeouts, you can configure them in the VonageService.

### Solution 6: Use Messages API Instead

If Legacy API continues to have issues, switch to Messages API:

```env
VONAGE_API_METHOD=messages
VONAGE_APPLICATION_ID=your_application_id
VONAGE_PRIVATE_KEY_PATH=/path/to/private.key
```

Messages API uses a different endpoint that might have better connectivity.

## Common Causes

1. **Firewall blocking outbound HTTPS** - Most common
2. **Proxy not configured** - Corporate networks
3. **DNS resolution issues** - Network configuration
4. **ISP blocking** - Some ISPs block certain domains
5. **VPN/Network restrictions** - Security policies
6. **Server in restricted network** - Docker containers, VPCs without NAT

## Testing After Fix

```bash
# Test SMS sending
php artisan vonage:test-all --service=sms --to=+2347081114942

# Check logs
tail -f storage/logs/laravel.log | grep Vonage
```

## Contact Support

If the issue persists:

1. **Vonage Support**: https://help.nexmo.com/
2. **Check Vonage Status**: https://status.vonage.com/
3. **Network Administrator**: Contact your server/network admin

## Alternative: Use Queue for SMS

If network issues persist, consider using Laravel queues to retry failed SMS:

```php
// In BulkSmsController
dispatch(new SendSmsJob($phone, $message))->onQueue('sms');
```

This allows automatic retries when network connectivity is restored.

