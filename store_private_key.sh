#!/bin/bash

# Script to securely store Vonage private key
# Run this script to save your private key to a secure file

echo "🔐 Storing Vonage Private Key Securely"
echo "======================================="
echo ""

# Create secure directory if it doesn't exist
mkdir -p storage/app/private
chmod 700 storage/app/private

# Save private key to file
cat > storage/app/private/vonage_private_key.pem << 'PRIVATE_KEY_EOF'
-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDqHGgipH19aso1
lBfYI0n1Rfu9ItQ3IZPvlV8lBFUTTsF/epamyXjiMVYX+2hX2F7fU4zPj7i0+gz0
UMbZBBDl2CzyEgGe2/4Nid7zk071wjYFpY/lyRqalTmNSOcfgFPa0AjsndvlmB3A
+km0T9I7UorrpA5gfhzuHgVng2KLLZ6VmsXVK7/sOhhewqVzUgsSBBIVQ8I+GcQC
IPGQJcgyCv6nmDp2iCOQ2ozCv1/CqW/gUSshOInOAd3SMDrU7g+NE/cuda8n05eJ
UAQCAsWWY0LZ2fHtXjYHaBPtrdFVZQU+D4G7gIRTkJw2G9W7k0KGxtSoBsQ/4zRn
atWpCzCNAgMBAAECggEAGWr/JW+9jj3neXy8QAPI1mAETos3BktXgAY0P9UiFYgR
+zDIpZAvV0OrCMyLjioYVt//5FNC5ydU+7u/czV9Ti8z5g5tbZ2ODlM/SSvfwVAW
RHOu1XCJumnkR0I4kdOBhzraFTKoetuSs26ZTQHznew+2AnGY9SdeH768Du0Gc8j
hBtL9pCvQ5AXuRM5vyei8fesT3Ya3Zq3Q518/qbj0Zphm7PuXNN3FL98UmzQgW0t
l3ddSBotDSGKfzT3mKx+VG7xNr25pla8RALfx8TCMJitypk03XZwiDXr/P7B+rw1
COXw9VdVeyRC607reDGUfybyBBVU1nFnyWAXtJla8wKBgQD37reOoeEeY9xqooyy
ffBJeP6ACgdHSioLcz9L7mpUp4lx+7K9x3/+JTZw1hCbUw55JweYifgUGVcGSxTh
NMtIt4LIh5DqdofvgG4AlM6yict3TeIvoPWujasZkvLQgKcwD3cBnug0lut+RyOu
SWW+3h0cW+CAiOJuAZYhwrZViwKBgQDxuo5goj3iDMmQ1sg9vh/JkCYjbOIOaxeI
+8uNfFHJHU5mQxwUvA/d45IU6X5lzkGcvPVt/BAncWdOllza0RSVVNjF0vNcewbz
+ZZXcpSohi9EAieqK1Vxc6RBCSjzyhFBatv//A9+i32X13tNF7pTFJkzNu2Vlydo
KW2yBaFFRwKBgQCT7xwXPv/T4kHiXpsU5ZA6RreULmepYdc0fc+GvvszB8Q6/rPo
COGWAx44tYGzLBu2rlMZ8TjUxUnnt2T9+0JQYS7n9/u/6nmxri/0qNWsMgv1Y8NS
J/hv7pfo6oV3Y0xeoNuhW++xcVuSPWBM3f6XpHA6O5f5GbQBb3DlNAkXgQKBgQDN
ZsBkzHfZBRShxmAQVuWb2KkPoQ3VQpqVA6BLcT/trMFZk9zgoYy59w3CR0XoLAME
tGu4MV9opKwr0wjDxrW+zoIEVuQDyWZ3GbXuSKaZLKwtFceuiXPpG2KPAMGTpjhT
ZgHa2swvs0fdJnTXZTomLnKfWiU332DU2Z8kjjQjMQKBgARuVgouDp9Ivjm26bW3
Zvq6LwR45Rj1Ti7Lkh122bRAfWExNUQSh2gF+Shyay3wg3sEm5EbiKCp3l14qcJ9
3BpkzRz5xIpojQ4wt2z4trnqWRW0hNB/pp7mYwcdPGU6IVvk5CZ1gsrcJHVVBAsX
u6P3TlQjvMg5YfVcXcfivVM1
-----END PRIVATE KEY-----
PRIVATE_KEY_EOF

# Set secure permissions
chmod 600 storage/app/private/vonage_private_key.pem

echo "✅ Private key stored securely at: storage/app/private/vonage_private_key.pem"
echo ""
echo "📝 Update your .env file:"
echo "   VONAGE_PRIVATE_KEY_PATH=storage/app/private/vonage_private_key.pem"
echo "   VONAGE_APPLICATION_ID=87592234-e76c-4c4b-b4fe-401b71d15d45"
echo ""
echo "⚠️  SECURITY WARNING:"
echo "   If this is a production key, consider regenerating it since it was shared."
echo "   Private keys should NEVER be shared publicly."

