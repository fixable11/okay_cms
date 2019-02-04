## Installation

1. Clone this repository

```bash
git clone https://github.com/fixable11/okay_cms.git
```
2.Install composer dependencies
```bash
composer install
```

3. Rename `config/config.php.example` with `config/config.php` and fill in the required parameters

4. Either use migration on exisiting database
```bash
cd migrations
php migrate
```
 or just upload `migrations/dump.sql`

5. Backend authority
```bash
Login: admin; Password: 123123
```
