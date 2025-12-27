# Testing Free Plan Restaurant Creation

## ✅ Setup Complete

A **Free Plan** has been created in the database for testing purposes.

---

## 📋 Free Plan Details

- **Name**: Free
- **Slug**: free
- **Price (Monthly)**: AED 0.00
- **Price (Yearly)**: AED 0.00
- **Max Restaurants**: 1
- **Features**: 
  - Basic POS
  - Inventory Management
  - Customer Loyalty
- **Status**: Active

---

## 🧪 How to Test

### Option 1: Test New User Onboarding (Recommended)

1. **Open a new incognito/private browser window** (to avoid login conflicts)

2. **Navigate to**: http://localhost:8000/onboard

3. **Step 1: Select Plan**
   - You should see 4 plans: Free, Basic, Pro, Enterprise
   - Click on the **Free** plan
   - Choose billing cycle (monthly or yearly - both are free)
   - Click "Continue to Setup"

4. **Step 2: Fill Registration Form**
   - **Restaurant Name**: "Test Free Restaurant"
   - **Full Name**: "Test User"
   - **Phone**: "+971501234567"
   - **Email**: "testfree@example.com" (use unique email)
   - **Password**: "password123"
   - **Confirm Password**: "password123"
   - **Loyalty Method**: Select either "Per Spend" or "Per Visit"
   - **Earning Points**: 1

5. **Submit Form**
   - Click "Create Account"
   - Should NOT ask for payment
   - Should redirect to dashboard immediately
   - Should show success message

6. **Verify Success**
   - You should be logged in
   - You should see the dashboard
   - Check that restaurant name appears in header/navigation

---

### Option 2: Test Adding Restaurant (Existing User)

1. **Login** as an existing owner (e.g., owner@demo.com / password)

2. **Navigate to**: http://localhost:8000/en/select-restaurant

3. **Click "Add New Restaurant"**

4. **Fill Form**:
   - **Restaurant Name**: "Second Test Restaurant"
   - **Loyalty Method**: Select either option
   - **Earning Points**: 1

5. **Submit**
   - Should create restaurant without payment
   - Should redirect to dashboard
   - Should show success message

---

## ✅ Expected Results

### For Free Plan Onboarding:

1. ✅ No payment page shown
2. ✅ User account created
3. ✅ Restaurant created
4. ✅ Subscription created with status = 'active'
5. ✅ User auto-logged in
6. ✅ Redirected to dashboard
7. ✅ Success message displayed

### Database Verification:

Run these commands to verify:

```bash
# Check if user was created
php artisan tinker --execute="echo \App\Models\User::where('email', 'testfree@example.com')->exists() ? 'User exists' : 'User not found';"

# Check if restaurant was created
php artisan tinker --execute="echo json_encode(\App\Models\Restaurant::where('name', 'Test Free Restaurant')->first()->toArray(), JSON_PRETTY_PRINT);"

# Check if subscription was created with active status
php artisan tinker --execute="
\$restaurant = \App\Models\Restaurant::where('name', 'Test Free Restaurant')->first();
if (\$restaurant) {
    \$subscription = \App\Models\Subscription::where('restaurant_id', \$restaurant->id)->first();
    echo json_encode(\$subscription->toArray(), JSON_PRETTY_PRINT);
}
"
```

---

## 🐛 Troubleshooting

### Issue: "Onboarding failed" error

**Check logs**:
```bash
tail -f storage/logs/laravel.log
```

**Common causes**:
- Email already exists
- MongoDB connection issue
- Missing required fields

### Issue: Payment page appears

**Cause**: Plan price is not 0
**Solution**: Verify plan price in database

### Issue: Subscription not created

**Check**:
```bash
php artisan tinker --execute="echo \App\Models\Subscription::count();"
```

**Solution**: Check OnboardingController logs

---

## 📊 Test Results Template

```
Date: ___________
Tester: ___________

Test Case: Free Plan Onboarding
- [ ] Free plan visible on onboarding page
- [ ] Free plan shows AED 0 price
- [ ] Can select free plan
- [ ] Can fill registration form
- [ ] Form submits successfully
- [ ] No payment page shown
- [ ] User auto-logged in
- [ ] Redirected to dashboard
- [ ] Success message displayed
- [ ] Restaurant appears in database
- [ ] Subscription created with status = 'active'

Issues Found:
___________________________________________
___________________________________________

Notes:
___________________________________________
___________________________________________
```

---

## 🎯 Success Criteria

The implementation is successful if:

1. ✅ Free plan (price = 0) can be selected
2. ✅ Registration form can be filled and submitted
3. ✅ **NO payment page is shown**
4. ✅ User is created and auto-logged in
5. ✅ Restaurant is created
6. ✅ Subscription is created with `status = 'active'`
7. ✅ User is redirected to dashboard
8. ✅ Success message is displayed
9. ✅ No errors in logs

---

**Ready to Test!** 🚀

Navigate to: http://localhost:8000/onboard
