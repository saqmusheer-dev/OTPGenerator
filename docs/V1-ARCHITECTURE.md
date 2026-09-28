# OTPGenerator V1 Architecture

## Verification methods
1. OTP Code — user views a short-lived code in OTPGenerator and enters it into the requesting app.
2. QR Scan — requesting website displays a short-lived QR token; OTPGenerator scans and approves it.
3. One-Tap Approval — OTPGenerator app receives a pending request and the user approves/rejects it.
4. Deep Link — a verification request can open OTPGenerator directly when the app is installed.

## Rules
- No SMS or WhatsApp delivery.
- Every verification is tied to an app, user/session, purpose and expiry.
- QR tokens are random, short-lived and single-use.
- OTP values are never returned by the API.
- API secrets remain server-side.
- Verification completion returns a signed/opaque verification result, not the OTP.

## Core entities
users
apps
api_credentials
verification_requests
verification_attempts
user_app_connections
sessions
activity_logs

## Integration example

POST /api/v1/verifications

{
  "user_reference": "customer-123",
  "purpose": "login",
  "method": "qr"
}

Response:

{
  "success": true,
  "verification_id": "ver_xxxxx",
  "status": "pending",
  "expires_in": 120,
  "qr_token": "..."
}

The requesting application polls or uses a server-side callback/webhook until the verification is completed.
