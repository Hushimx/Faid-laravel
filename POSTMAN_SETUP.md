# Postman Collection Setup Guide

## Importing the Collection

1. Open Postman
2. Click **Import** button (top left)
3. Select the file: `Service_API.postman_collection.json`
4. The collection will appear in your Postman workspace

## Setting Up Variables

The collection uses 3 variables that you need to configure:

### 1. `base_url`
- **Default**: `http://localhost:8000`
- **For Mobile Testing**: Use your computer's local IP address
  - **Windows**: Run `ipconfig` and look for IPv4 Address (e.g., `192.168.1.100`)
  - **Mac/Linux**: Run `ifconfig` or `ip addr` and find your local IP
  - **Example**: `http://192.168.1.100:8000`
- **For Production**: Your production API URL (e.g., `https://api.yourdomain.com`)

### 2. `auth_token`
- **Default**: `your_bearer_token_here`
- **How to get it**:
  1. Use the Login endpoint: `POST {{base_url}}/api/login`
  2. Send your credentials (email/password)
  3. Copy the `token` from the response
  4. Paste it in the variable (without "Bearer" prefix - it's added automatically)

### 3. `service_id`
- **Default**: `1`
- **How to set it**:
  1. Create a service using "Create Service" endpoint
  2. Copy the `id` from the response
  3. Update the variable with that ID

## How to Update Variables

1. Click on the collection name: **Service API Collection**
2. Click on the **Variables** tab
3. Update the values in the **Current Value** column
4. Click **Save**

## Testing on Mobile Device

### Option 1: Using Postman Mobile App
1. Install Postman mobile app on your device
2. Sign in with the same account
3. Sync your collections
4. Update `base_url` to your computer's IP address

### Option 2: Using ngrok (Recommended for Testing)
1. Install ngrok: `https://ngrok.com/`
2. Run: `ngrok http 8000`
3. Copy the forwarding URL (e.g., `https://abc123.ngrok.io`)
4. Update `base_url` variable to this URL
5. Now you can test from anywhere!

## Endpoints Overview

### Public Endpoints (No Auth Required)
- **GET /api/services** - List all services
- **GET /api/services/{id}** - Get single service

### Vendor Endpoints (Auth Required)
- **POST /api/services** - Create new service
- **PUT /api/services/{id}** - Update service
- **DELETE /api/services/{id}** - Delete service

## Important Notes

### For Create Service:
- `category_id` is **required**
- `title[ar]` is **required**
- `price_type` is **required** (fixed, negotiable, or unspecified)
- `price` is required only if `price_type` is "fixed"
- All other fields are optional

### For Update Service:
- **All fields are optional** - only send what you want to update
- To keep existing media, send `keep_media_ids[]` with the media IDs
- Media not in `keep_media_ids[]` will be deleted
- To add new media, use `media[0][file]` with the file

### Media Upload:
- Supported formats: `jpeg, jpg, png, gif, mp4, mov, avi`
- Max file size: 10MB
- Use `media[0][type]` = "image" or "video"
- Set `media[0][is_primary]` = "1" for primary image

### Price Types:
- **fixed**: Requires `price` field
- **negotiable**: Price can be negotiated
- **unspecified**: No price specified (غير محدد)

## Example Workflow

1. **Login** to get your auth token
2. **Update variables**: Set `auth_token` and `base_url`
3. **Create Service**: Use "Create Service" endpoint
4. **Copy Service ID**: From the response, copy the `id`
5. **Update variable**: Set `service_id` to the copied ID
6. **Get Service**: Test "Get Single Service" endpoint
7. **Update Service**: Modify fields and test "Update Service"
8. **Delete Service**: Test "Delete Service" endpoint

## Troubleshooting

### "Unauthenticated" Error
- Make sure `auth_token` is set correctly
- Token might have expired - login again to get a new token

### "Service not found" Error
- Check if `service_id` is correct
- Make sure you're the owner of the service (vendor)

### "Email verification required" Error
- Your account needs email verification
- Check your email and verify your account

### Connection Issues on Mobile
- Make sure your device and computer are on the same network
- Check if Laravel server is running: `php artisan serve --host=0.0.0.0`
- Try using ngrok for easier testing

## Quick Test Checklist

- [ ] Collection imported successfully
- [ ] Variables configured (base_url, auth_token, service_id)
- [ ] Can access "Get All Services" (public)
- [ ] Can login and get auth token
- [ ] Can create a service
- [ ] Can update a service
- [ ] Can delete a service
- [ ] Media upload works
- [ ] Bilingual fields (Arabic/English) work correctly


