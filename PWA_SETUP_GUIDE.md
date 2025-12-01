# PWA Setup Guide for DoctorOnTap

This guide explains the Progressive Web App (PWA) implementation for DoctorOnTap.

## What's Been Implemented

### 1. **Web App Manifest** (`public/manifest.json`)
   - Defines app name, colors, icons, and display mode
   - Enables "Add to Home Screen" functionality
   - Configures app shortcuts for quick actions

### 2. **Service Worker** (`public/sw.js`)
   - Enables offline functionality
   - Caches static assets for faster loading
   - Implements network-first strategy with cache fallback
   - Supports push notifications (ready to implement)
   - Background sync capability (ready to implement)

### 3. **Offline Page** (`public/offline.html`)
   - Displays when user is offline and page isn't cached
   - Auto-retries connection every 5 seconds
   - Beautiful branded interface

### 4. **PWA Meta Tags**
   - Added to `app-livewire.blade.php` layout
   - Added to `welcome.blade.php`
   - Includes Apple-specific tags for iOS support
   - Theme colors and status bar styling

### 5. **Service Worker Registration**
   - Automatic registration on page load
   - Update detection and handling
   - Install prompt handling

## Generating PWA Icons

### Option 1: Automated Generation (Recommended)

Run the provided PHP script:

```bash
php generate-pwa-icons.php
```

**Requirements:**
- PHP GD extension or Imagick extension
- Source logo at `public/img/sitelogo.png`

### Option 2: Online Tools

If you don't have the required PHP extensions, use these online tools:

1. **PWA Builder Image Generator**
   - Visit: https://www.pwabuilder.com/imageGenerator
   - Upload your logo
   - Download generated icons
   - Extract to `public/img/pwa/`

2. **RealFaviconGenerator**
   - Visit: https://realfavicongenerator.net/
   - Upload your logo
   - Configure PWA settings
   - Download and extract icons

### Option 3: Manual Creation

Create PNG icons with these exact sizes:
- 72x72
- 96x96
- 128x128
- 144x144
- 152x152
- 192x192
- 384x384
- 512x512

Save them as: `public/img/pwa/icon-{size}x{size}.png`

## Testing Your PWA

### Desktop (Chrome/Edge)

1. Open DevTools (F12)
2. Go to **Application** tab
3. Check **Manifest** section
4. Verify **Service Workers** are registered
5. Click the "+" icon in the address bar to install

### Mobile (Android)

1. Visit your site on Chrome mobile
2. Tap the menu (⋮)
3. Select "Add to Home screen"
4. Confirm the installation
5. Launch the app from your home screen

### Mobile (iOS/Safari)

1. Visit your site on Safari
2. Tap the Share button
3. Select "Add to Home Screen"
4. Confirm and name the app
5. Launch from home screen

## Testing Offline Functionality

1. Open DevTools → Network tab
2. Check "Offline" box
3. Refresh the page
4. You should see the offline page
5. Turn offline mode off
6. Page should reload automatically

## Customization

### Changing Theme Color

Edit `public/manifest.json`:

```json
{
  "theme_color": "#9333EA",
  "background_color": "#9333EA"
}
```

Also update in layout files:
```html
<meta name="theme-color" content="#9333EA">
```

### Adding App Shortcuts

Edit `public/manifest.json` shortcuts section to add quick actions:

```json
{
  "shortcuts": [
    {
      "name": "New Consultation",
      "url": "/create-consultation",
      "icons": [{ "src": "/img/pwa/icon-192x192.png", "sizes": "192x192" }]
    }
  ]
}
```

### Caching Strategy

Edit `public/sw.js` to modify what gets cached:

```javascript
const STATIC_CACHE = [
  '/',
  '/offline.html',
  '/css/app.css',
  '/js/app.js',
  // Add more resources
];
```

## Push Notifications (Future Enhancement)

The service worker is ready for push notifications. To implement:

1. Get VAPID keys for web push
2. Request notification permission
3. Subscribe user to push service
4. Send notifications from backend

Example implementation available in `sw.js` (push event listener).

## Browser Support

| Browser | Supported | Notes |
|---------|-----------|-------|
| Chrome (Android) | ✅ | Full support |
| Chrome (Desktop) | ✅ | Full support |
| Edge | ✅ | Full support |
| Safari (iOS 16.4+) | ✅ | Full support |
| Safari (iOS < 16.4) | ⚠️ | Limited support |
| Firefox | ✅ | Full support |
| Samsung Internet | ✅ | Full support |

## Performance Benefits

- **Faster Loading**: Cached assets load instantly
- **Offline Access**: Core functionality works offline
- **Reduced Server Load**: Static assets served from cache
- **Better UX**: Native app-like experience
- **SEO Benefits**: Better Core Web Vitals scores

## Monitoring & Analytics

Track PWA metrics:

1. **Installation Rate**: Monitor `beforeinstallprompt` and `appinstalled` events
2. **Offline Usage**: Log service worker cache hits
3. **Update Success**: Track service worker updates
4. **Engagement**: Compare standalone vs browser mode usage

## Troubleshooting

### Icons Not Showing
- Verify icons exist at correct paths
- Check browser cache
- Validate manifest.json format
- Ensure HTTPS is enabled (required for PWA)

### Service Worker Not Registering
- Check browser console for errors
- Verify `sw.js` is accessible at root
- Ensure site is served over HTTPS (except localhost)
- Clear browser cache and try again

### Install Prompt Not Showing
- PWA criteria must be met:
  - Valid manifest.json
  - HTTPS enabled
  - Service worker registered
  - Icons available
- Chrome may delay the prompt based on user engagement

### Offline Page Not Working
- Check if `offline.html` exists
- Verify it's being cached in service worker
- Test with DevTools offline mode

## Production Checklist

- [ ] Generate all required icon sizes
- [ ] Replace placeholder screenshots with real ones
- [ ] Test on multiple devices and browsers
- [ ] Verify HTTPS is enabled
- [ ] Test offline functionality
- [ ] Validate manifest.json
- [ ] Monitor service worker errors
- [ ] Set up analytics for PWA metrics
- [ ] Test install/uninstall flows
- [ ] Review and optimize cache strategy

## Security Considerations

- Always serve over HTTPS in production
- Service workers have full cache control - review carefully
- Validate all cached content
- Implement proper CORS headers
- Keep service worker updated

## Resources

- [MDN Web Docs - PWA](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)
- [Google PWA Guide](https://web.dev/progressive-web-apps/)
- [PWA Builder](https://www.pwabuilder.com/)
- [Workbox (Advanced SW)](https://developers.google.com/web/tools/workbox)

## Support

For issues or questions about the PWA implementation, check:
- Browser console for errors
- Application tab in DevTools
- Network tab for cache behavior
- Service worker lifecycle events

---

**Note**: This PWA implementation follows best practices and is production-ready. Make sure to test thoroughly before deployment.

