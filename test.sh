#!/bin/bash

echo "🔍 Laravel 11 Debugging Commands"
echo "================================"

# Check Laravel version
echo "1. Laravel version:"
docker-compose exec app php artisan --version

echo ""
echo "2. Checking middleware configuration (Laravel 11):"
docker-compose exec app cat bootstrap/app.php

echo ""
echo "3. Checking if middleware is properly configured:"
docker-compose exec app php artisan route:list --path=dashboard --columns=uri,name,middleware

echo ""
echo "4. Testing auth middleware manually:"
docker-compose exec app php artisan tinker --execute="
echo 'Testing middleware...';
try {
    \$middleware = app('router')->getRoutes()->getByName('dashboard')->middleware();
    echo 'Dashboard middleware: ' . implode(', ', \$middleware);
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage();
}
"

echo ""
echo "5. Check session config:"
docker-compose exec app cat .env | grep -E "(SESSION_|APP_KEY|APP_URL)"

echo ""
echo "6. Testing user creation and login:"
docker-compose exec app php artisan tinker --execute="
try {
    // Create or find test user
    \$user = App\Models\User::firstOrCreate([
        'email' => 'test@example.com'
    ], [
        'name' => 'Test User',
        'password' => Hash::make('password123')
    ]);
    
    echo 'User exists: ' . \$user->email;
    
    // Test password
    if (Hash::check('password123', \$user->password)) {
        echo '\nPassword verification: SUCCESS';
    } else {
        echo '\nPassword verification: FAILED - updating password';
        \$user->update(['password' => Hash::make('password123')]);
        echo '\nPassword updated successfully';
    }
    
    // Test manual login
    Auth::login(\$user);
    if (Auth::check()) {
        echo '\nManual login: SUCCESS';
        echo '\nLogged in as: ' . Auth::user()->name;
        Auth::logout(); // Clean up
    } else {
        echo '\nManual login: FAILED';
    }
    
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage();
}
"

echo ""
echo "7. Check if Breeze is properly installed:"
docker-compose exec app ls -la app/Http/Controllers/Auth/

echo ""
echo "8. Test actual login process:"
echo "You should now test with these credentials:"
echo "Email: test@example.com"
echo "Password: password123"
