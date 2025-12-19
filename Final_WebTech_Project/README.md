# EcoPoints (PHP + MySQL)

EcoPoints is a waste sorting & recycling rewards tracker. Users register/login, log recycling submissions, and earn points after admin approval. Users can redeem rewards; admins manage inventory and redemptions.

## Tech stack
- PHP 8+
- MySQL 8+
- HTML, CSS (Bootstrap), JavaScript
- PDO + prepared statements

## Setup (XAMPP/WAMP)
1. Import `database.sql` into MySQL (phpMyAdmin).
2. Update DB credentials: `public/includes/config.php`
3. Put the folder in `htdocs/` and open:
   - `http://localhost/EcoPoints_Full_Project/public/`

## Admin
After you register, make your account admin:
```sql
UPDATE users SET role='admin' WHERE email='you@example.com';
```

## Demo routes
- /public/register.php
- /public/login.php
- /public/dashboard.php
- /public/submit.php
- /public/leaderboard.php
- /public/rewards.php
- /public/redeem.php?reward_id=1
- /public/my_redemptions.php
- /public/admin/index.php
- /public/admin/review_submissions.php
- /public/admin/rewards.php
- /public/admin/redemptions.php
