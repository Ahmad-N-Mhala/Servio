# Real-Time Order Updates Setup Guide

## ✅ What's Been Implemented

Your RestoFy system now has **real-time order updates** using Laravel Broadcasting with Pusher! This means:

- **Instant updates** when orders are created or modified
- **< 1 second** delay instead of the previous 2-second polling
- **Automatic fallback** to 30-second polling if WebSockets fail
- **Per-item notes** displayed in kitchen view

## 🚀 Quick Start

### Option 1: Use Pusher (Recommended for Production)

1. **Sign up for Pusher** (free tier available):
   - Go to https://pusher.com/
   - Create a new app
   - Get your credentials

2. **Add to `.env`**:
   ```env
   BROADCAST_DRIVER=pusher
   
   PUSHER_APP_ID=your_app_id
   PUSHER_APP_KEY=your_app_key
   PUSHER_APP_SECRET=your_app_secret
   PUSHER_APP_CLUSTER=mt1
   
   VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
   VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
   ```

3. **Restart services**:
   ```bash
   npm run dev
   php artisan config:clear
   ```

### Option 2: Use Local WebSocket Server (Development)

For local development without Pusher:

1. **Install Laravel WebSockets**:
   ```bash
   composer require beyondcode/laravel-websockets
   php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"
   php artisan migrate
   php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="config"
   ```

2. **Update `.env`**:
   ```env
   BROADCAST_DRIVER=pusher
   
   PUSHER_APP_ID=local
   PUSHER_APP_KEY=local
   PUSHER_APP_SECRET=local
   PUSHER_APP_CLUSTER=mt1
   PUSHER_HOST=127.0.0.1
   PUSHER_PORT=6001
   PUSHER_SCHEME=http
   
   VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
   VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
   VITE_PUSHER_HOST=127.0.0.1
   VITE_PUSHER_PORT=6001
   VITE_PUSHER_SCHEME=http
   ```

3. **Start WebSocket server**:
   ```bash
   php artisan websockets:serve
   ```

4. **Access dashboard**: http://127.0.0.1:8000/laravel-websockets

## 📋 How It Works

### Backend (Broadcasting)

When an order is created or updated:

```php
// In OrderController.php
broadcast(new OrderUpdated($order, 'created'))->toOthers();
```

This sends a real-time event to all connected clients.

### Frontend (Listening)

The Kitchen view listens for events:

```typescript
window.Echo.channel(`restaurant.${restaurantId}.orders`)
    .listen('.order.created', (e) => {
        // Reload orders instantly
        router.reload({ only: ['orders', 'completedOrders'] });
    });
```

### Fallback System

If WebSockets fail, the system automatically falls back to polling every 30 seconds.

## 🎯 What Gets Updated in Real-Time

✅ **New Orders** - Appear instantly when created from POS
✅ **Status Changes** - Kitchen updates reflected immediately
✅ **Order Modifications** - Any changes to items or notes
✅ **Per-Item Notes** - Special instructions visible instantly

## 🧪 Testing

### Test Real-Time Updates

1. Open Kitchen Display on Device A: `http://127.0.0.1:8000/kitchen`
2. Open POS on Device B: `http://127.0.0.1:8000/orders/create`
3. Create an order with notes on Device B
4. Watch Device A - order appears **instantly** (< 1 second)

### Check Console

Open browser console on Kitchen Display:
- ✅ Should see: `✅ Real-time updates enabled via Laravel Echo`
- ❌ If you see: `⚠️ Laravel Echo not available, using fallback polling`
  - Check `.env` configuration
  - Verify `npm run dev` is running
  - Check browser console for errors

## 📊 Performance Comparison

| Method | Update Speed | Server Load | Network Usage |
|--------|--------------|-------------|---------------|
| **Old (Polling)** | 2 seconds | High (constant requests) | High |
| **New (WebSockets)** | < 1 second | Low (event-driven) | Very Low |
| **Fallback** | 30 seconds | Medium | Medium |

## 🔧 Troubleshooting

### Orders Not Updating Instantly

1. **Check browser console** for errors
2. **Verify `.env` settings** are correct
3. **Restart services**:
   ```bash
   npm run dev
   php artisan config:clear
   php artisan queue:restart  # if using queues
   ```

### WebSocket Connection Failed

1. **Check Pusher credentials** in `.env`
2. **Verify firewall** isn't blocking WebSocket ports
3. **Check Pusher dashboard** for connection logs
4. **System will automatically fall back** to 30-second polling

### Events Not Broadcasting

1. **Check queue is running** (if using queued broadcasts):
   ```bash
   php artisan queue:work
   ```

2. **Test broadcasting**:
   ```bash
   php artisan tinker
   broadcast(new \App\Events\OrderUpdated(\App\Models\Order::first(), 'test'));
   ```

## 🎨 UI Indicators

The Kitchen Display shows:
- **Green pulse** - Real-time updates active
- **"Auto-refresh active"** text
- Console logs for each event received

## 📝 Files Modified

### Backend
- `app/Events/OrderUpdated.php` - New broadcast event
- `app/Http/Controllers/Tenant/OrderController.php` - Broadcasts on create/update
- `app/Http/Controllers/Tenant/KitchenController.php` - Broadcasts on status change
- `config/broadcasting.php` - Broadcasting configuration

### Frontend
- `resources/js/app.ts` - Laravel Echo setup
- `resources/js/Pages/Kitchen/Index.vue` - Real-time listeners

## 🚀 Next Steps

1. **Choose your broadcasting method** (Pusher or local WebSockets)
2. **Configure `.env`** with your credentials
3. **Test the system** with multiple devices
4. **Monitor performance** in production

## 💡 Tips

- **Pusher free tier** supports up to 100 concurrent connections
- **Local WebSockets** are perfect for development
- **Fallback polling** ensures reliability even if WebSockets fail
- **Per-restaurant channels** ensure data isolation in multi-tenant setup

## 🎉 Benefits

✅ **Faster service** - Kitchen sees orders instantly
✅ **Better UX** - No more waiting for page refresh
✅ **Lower server load** - Event-driven vs constant polling
✅ **Scalable** - Handles multiple kitchen displays easily
✅ **Reliable** - Automatic fallback if WebSockets fail

Your system is now ready for real-time updates! 🚀
