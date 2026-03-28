php artisan db:seed --class=BancoFebrabanSeeder
php artisan migrate:fresh --seed

.\stripe.exe  listen --forward-to http://127.0.0.1:8000/stripe/webhook