# Vonage Video Testing Checklist

## ✅ Pre-Testing Verification

### Backend Status
```bash
php artisan vonage:test-all --service=video
```

**Expected:**
- ✅ Enabled: Yes
- ✅ Initialized: Yes
- ✅ Session created successfully
- ✅ Token generated successfully

### Frontend Status
- ✅ OpenTok SDK loaded (check browser console)
- ✅ JavaScript functions available
- ✅ Routes accessible

## 🧪 Test Scenarios

### Test 1: Video Call (Full Test)

**Setup:**
1. Create consultation with `consultation_mode = 'video'`
2. Assign doctor to consultation
3. Open in two different browsers (or incognito windows)

**Steps:**
- [ ] **As Doctor:**
  - [ ] Navigate to consultation page
  - [ ] Click "Join Consultation"
  - [ ] Allow camera and microphone permissions
  - [ ] Verify local video appears
  - [ ] Verify controls are visible

- [ ] **As Patient:**
  - [ ] Navigate to consultation page
  - [ ] Click "Join Consultation"
  - [ ] Allow camera and microphone permissions
  - [ ] Verify both videos appear (local + remote)
  - [ ] Verify audio works (can hear doctor)

- [ ] **Test Controls:**
  - [ ] Mute button works
  - [ ] Video toggle works
  - [ ] End call works
  - [ ] Connection quality indicator shows

**Expected Results:**
- ✅ Both participants see each other's video
- ✅ Audio works both ways
- ✅ Controls function properly
- ✅ No console errors

---

### Test 2: Audio-Only Call (Voice Consultation)

**Setup:**
1. Create consultation with `consultation_mode = 'voice'`
2. Assign doctor to consultation
3. Open in two different browsers

**Steps:**
- [ ] **As Doctor:**
  - [ ] Navigate to consultation page
  - [ ] Click "Join Consultation"
  - [ ] Allow microphone permission (camera not needed)
  - [ ] Verify avatar interface appears
  - [ ] Verify no video is shown

- [ ] **As Patient:**
  - [ ] Navigate to consultation page
  - [ ] Click "Join Consultation"
  - [ ] Allow microphone permission
  - [ ] Verify both avatars appear
  - [ ] Verify audio works

- [ ] **Test Controls:**
  - [ ] Mute button works
  - [ ] End call works
  - [ ] Connection quality indicator shows

**Expected Results:**
- ✅ No video shown (audio-only)
- ✅ Avatars displayed for participants
- ✅ Audio works both ways
- ✅ Controls function properly

---

### Test 3: Error Handling

**Test Cases:**
- [ ] **No Camera/Microphone Permission:**
  - [ ] Deny permissions
  - [ ] Verify error message appears
  - [ ] Verify user can retry

- [ ] **Network Disconnection:**
  - [ ] Disconnect internet during call
  - [ ] Verify reconnection attempt
  - [ ] Verify error handling

- [ ] **Token Expiration:**
  - [ ] Wait for token to expire (1 hour)
  - [ ] Verify token refresh works
  - [ ] Verify call continues

- [ ] **Room Not Created (Patient):**
  - [ ] Patient tries to join before doctor
  - [ ] Verify appropriate error message
  - [ ] Verify patient can retry after doctor joins

---

### Test 4: Multi-Party (3+ Participants)

**Setup:**
1. Create video consultation
2. Add multiple participants (if supported)

**Steps:**
- [ ] First participant joins
- [ ] Second participant joins
- [ ] Third participant joins
- [ ] Verify all can see each other
- [ ] Verify audio works for all

**Expected Results:**
- ✅ All participants visible
- ✅ Audio works for all
- ✅ No performance issues

---

### Test 5: Recording

**Setup:**
1. Start video consultation
2. Test recording functionality

**Steps:**
- [ ] **Start Recording:**
  - [ ] Click "Start Recording" button
  - [ ] Verify recording indicator appears
  - [ ] Verify recording status updates

- [ ] **Stop Recording:**
  - [ ] Click "Stop Recording" button
  - [ ] Verify recording stops
  - [ ] Verify archive ID is returned

**Expected Results:**
- ✅ Recording starts successfully
- ✅ Recording stops successfully
- ✅ Archive can be retrieved later

---

### Test 6: Screen Sharing

**Setup:**
1. Start video consultation
2. Test screen sharing

**Steps:**
- [ ] Click "Share Screen" button
- [ ] Select screen/window to share
- [ ] Verify screen share appears
- [ ] Verify other participants see screen
- [ ] Click "Stop Sharing"
- [ ] Verify camera returns

**Expected Results:**
- ✅ Screen sharing works
- ✅ Other participants see shared screen
- ✅ Can stop and return to camera

---

## 🔍 Browser Testing

### Desktop Browsers
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

### Mobile Browsers
- [ ] iOS Safari
- [ ] Android Chrome

### Test on Each:
- [ ] Video call works
- [ ] Audio-only call works
- [ ] Controls work
- [ ] No console errors

---

## 📊 Performance Testing

- [ ] **Connection Quality:**
  - [ ] Good connection → High quality
  - [ ] Poor connection → Quality adjusts
  - [ ] Audio fallback works (video drops, audio continues)

- [ ] **Bandwidth Usage:**
  - [ ] Monitor network usage
  - [ ] Verify reasonable bandwidth consumption
  - [ ] Audio-only uses less bandwidth

- [ ] **Latency:**
  - [ ] Measure audio/video delay
  - [ ] Should be < 500ms for good experience

---

## 🐛 Known Issues to Check

- [ ] Token refresh works correctly
- [ ] Reconnection after network loss
- [ ] Multiple tabs don't cause conflicts
- [ ] Browser permissions handled gracefully
- [ ] Mobile browser compatibility

---

## ✅ Success Criteria

All tests should pass:
- ✅ Video calls work end-to-end
- ✅ Audio-only calls work end-to-end
- ✅ Controls function properly
- ✅ Error handling works
- ✅ Recording works (if tested)
- ✅ Screen sharing works (if tested)
- ✅ Multi-party works (if tested)
- ✅ No critical console errors
- ✅ Good user experience

---

## 📝 Test Results Template

```
Test Date: ___________
Tester: ___________

Video Call Test: [ ] Pass [ ] Fail
Audio-Only Test: [ ] Pass [ ] Fail
Error Handling: [ ] Pass [ ] Fail
Recording: [ ] Pass [ ] Fail
Screen Sharing: [ ] Pass [ ] Fail

Issues Found:
1. ________________________________
2. ________________________________
3. ________________________________

Notes:
___________________________________
___________________________________
```

---

## 🚀 Ready for Production

Once all tests pass:
- ✅ Backend fully functional
- ✅ Frontend integrated
- ✅ Both modes working
- ✅ Error handling complete
- ✅ User experience verified

**Status:** Ready to deploy! 🎉

