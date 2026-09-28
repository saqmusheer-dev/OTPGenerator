# OTPGenerator API

Base URL:
https://your-domain.com/api/v1

## Authentication

Each registered application receives:
- API Key: public identifier sent as `X-API-Key`
- API Secret: private credential used to authenticate server-to-server requests

Secrets must never be embedded in mobile/browser client code. Calls that create or verify OTPs should be made through the customer's backend.

## Send OTP

POST /otp/send

Headers:
```
Content-Type: application/json
X-API-Key: YOUR_API_KEY
X-API-Secret: YOUR_API_SECRET
```

Body:
```json
{
  "destination": "+919876543210",
  "channel": "sms",
  "purpose": "login"
}
```

Expected response:
```json
{
  "success": true,
  "request_id": "uuid",
  "expires_in": 300
}
```

## Verify OTP

POST /otp/verify

Body:
```json
{
  "request_id": "uuid",
  "otp": "123456"
}
```

Expected response:
```json
{
  "success": true,
  "verified": true
}
```

## Security requirements
- Store OTPs hashed, never in plaintext.
- Expire OTPs automatically.
- Limit verification attempts.
- Add resend cooldowns and rate limits.
- Never return the OTP from the API.
- Log request IDs and outcomes, not OTP values.
