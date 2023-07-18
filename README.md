# Run the project(Backend - Local);
1 - Set up the composer.json -> composer install<br><br>
2 - composer require laravel/passport <br><br>
3 - .env.example to .env <br><br>
4 - Php artisan migrate<br><br>
5 - php artisan passport:install <br><br>
6 - Php artisan serve<br><br>


# Run the project(Backend - Docker);
1 - docker-compose build app<br><br>
2 - docker-compose up -d<br><br>
3 - docker-compose exec app ls -l<br><br>
4 - docker-compose exec app rm -rf vendor composer.lock<br><br>
5 - docker-compose exec app composer install<br><br>
6 - docker-compose exec app php artisan migrate<br><br>
7 - docker-compose exec app php artisan passport:install <br><br>
8 - docker-compose exec app php artisan key:generate <br><br>

# Now go to your browser and access your server’s domain name or IP address on port 8000:
- http://server_domain_or_IP:8000




# Endpoints Info
- http://server_domain_or_IP:8000/api/auth/register - POST - form-data => name, email, password
- http://server_domain_or_IP:8000/api/auth/login - POST - form-data => email, password
- http://server_domain_or_IP:8000/api/logout - POST - headers => Authorization : Bearer + access_token
- http://server_domain_or_IP:8000/api/product - GET - headers => Authorization : Bearer + access_token
- http://server_domain_or_IP:8000/api/product - POST - headers => Authorization : Bearer + access_token | form-data => name, category, price, stock
- http://server_domain_or_IP:8000/api/product/$id - DELETE - headers => Authorization : Bearer + access_token
- http://server_domain_or_IP:8000/api/order - GET - headers => Authorization : Bearer + access_token
- http://server_domain_or_IP:8000/api/order - POST - headers => Authorization : Bearer + access_token | form-data => productId, quantity | example: productId: 1,2 quantity 5,6 for buy multiple products.
- http://server_domain_or_IP:8000/api/order/$id - DELETE - headers => Authorization : Bearer + access_token
- http://server_domain_or_IP:8000/api/customer - GET - headers => Authorization : Bearer + access_token
- http://server_domain_or_IP:8000/api/discount - POST - headers => Authorization : Bearer + access_token | form-data => orderId 
