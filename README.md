# Tonkra SMS Referral System Package v1.0.0

A Laravel package for adding a referral system to Tonkra SMS, designed for easy integration and seamless updates.

## Features
- Admin-managed referral system (enable/disable from settings)
- Custom referral links for users
- Registration flow modification without altering Tonkra SMS core files
- Optimized and secure

## Installation

### 1. Install the Package
Run the following command to install the package via Composer:
```sh
composer require tonkra/referral
```

### 2. Publish the Package Assets
Publish the configuration and migration files:
```sh
php artisan vendor:publish --provider="Tonkra\Referral\ReferralServiceProvider"
```
```sh
php artisan vendor:publish --tag=referral-config
php artisan vendor:publish --tag=referral-migrations
php artisan vendor:publish --tag=referral-seeders
```

### 3. Run Migrations
Migrate the database to create necessary tables:
```sh
php artisan migrate
```

### 4. Seed Initial Data (Optional)
If the package includes seeders, run:
```sh
php artisan db:seed --class="Database\Seeders\ReferralDatabaseSeeder"
```

## Configuration

### 1. Update `.env` File
Modify the `.env` file to include referral settings:
```sh
REFERRAL_ENABLED=true
REFERRAL_BONUS=5
REFERRAL_EMAIL_NOTIFICATION=true
REFERRAL_SMS_NOTIFICATION=true
REFERRAL_DEFAULT_SENDERID="TONKRA SMS" # Set your default sender ID for sms notifications
```

### 2. Verify Configuration File
Check `config/tonkra_referral.php` to adjust settings as needed.

## Usage

### 1. Generate a Referral Link
Users can generate their referral link:
```php
$referralUser = ReferralUser::find(auth()->id());
echo $referralUser->referral_link;
```

### 2. Track Referrals
Referrals are automatically tracked when new users register using a referral link.

### 3. Admin Management
Admins can enable or disable the referral system via the settings panel.

## Updating the Package
To update the package, run:
```sh
composer update tonkra/referral
```

## Troubleshooting
- Run `php artisan config:clear` and `php artisan cache:clear` if settings don’t apply.
- Ensure migrations have been run: `php artisan migrate`.
- Check logs for errors: `storage/logs/laravel.log`.

## License
This package is licensed under the MIT License.

## Support
For support, contact the package maintainer or submit an issue on the repository.

