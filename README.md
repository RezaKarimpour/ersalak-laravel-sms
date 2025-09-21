# 📦 Laravel SMS Client

یک پکیج لاراولی برای ارسال پیامک با استفاده از API Ersalak، طراحی شده برای توسعه‌پذیری، خوانایی و تجربه توسعه ساده.

---

## 🔧 نصب پکیج

ابتدا از طریق Composer نصب کنید:

```bash
composer require ersalak/ersalak-laravel-sms
```

سپس فایل پیکربندی را publish نمایید:

```bash
php artisan vendor:publish --tag=ersalak-config
```

و فایل `.env` پروژه را با مقادیر زیر تکمیل کنید:

```env
ERSALAK_SMS_USERNAME=your-username
ERSALAK_SMS_PASSWORD=your-password
ERSALAK_SMS_BASE_URL=https://sms.ersalak.ir
ERSALAK_SMS_LOG=true
```

پکیج به صورت اتوماتیک provider و facade را به اپلیکیشن اضافه می‌کند، نیاز به تعریف دستی نیست.

---

## ✅ استفاده در پروژه لاراول

### 1. ارسال پیامک ساده در کنترلر

```php
use Ersalak\Sms\Facade\ErsalakSmsFacade as ErsalakSms;

public function send()
{
    try {
        $data = ErsalakSms::sendSms(
            source: '9821XXXXX',
            destination: '09120000000',
            message: 'کد تایید شما: 123456',
            send_to_black_list: 1
        );
        //Log or save $data as messageIds for get message status report

    } catch (\Throwable $e) {
        echo $e->getMessage();
    }
}
```

### 2. پیامک نظیر به نظیر (P2P)

```php
use Ersalak\Sms\Facade\ErsalakSmsFacade as ErsalakSms;

try {
    $data = ErsalakSms::p2p(
        source: ['9821XXX1', '9821XXX2'],
        destination: ['09120000000', '09120000001'],
        message: ['متن اول', 'متن دوم'],
        send_to_black_list: [1, 0]
    );
    $messageIds = array_column($data, 'messageId');
} catch (\Throwable $e) {
    echo $e->getMessage();
}
```

### 3. پیامک OTP با قالب

```php
use Ersalak\Sms\Facade\ErsalakSmsFacade as ErsalakSms;

try {
    $data = ErsalakSms::template(
        template_id: 1234,
        parameters: ['code' => 67890],
        destination: '09120000000'
    );
    //Log or save $data as messageIds for get message status report
} catch (\Throwable $e) {
    echo $e->getMessage();
}
```

### 4. مشاهده وضعیت پیامک

```php
use Ersalak\Sms\Facade\ErsalakSmsFacade as ErsalakSms;

try {
    $data = ErsalakSms::msgStatus(['msgid1', 'msgid2']);
} catch (\Throwable $e) {
    echo $e->getMessage();
}
```

### 5. دریافت اعتبار پیامکی

```php
use Ersalak\Sms\Facade\ErsalakSmsFacade as ErsalakSms;

try {
    $data = ErsalakSms::getCredit();
    $credit = $data['credit'] ?? null;
} catch (\Throwable $e) {
    echo $e->getMessage();
}
```

### 7. مشاهده موجودی اعتبار پیامکی (ریال)

```php
use Ersalak\Sms\Facade\ErsalakSmsFacade as ErsalakSms;

try {
    $data = ErsalakSms::getRialCredit();
    $credit = $data['credit'] ?? null;
} catch (\Throwable $e) {
    echo $e->getMessage();
}
```

### 8. دریافت لیست قالب‌های پیامک

```php

use Ersalak\Sms\Facade\ErsalakSmsFacade as ErsalakSms;

try {
    $templates = ErsalakSms::getTemplates();
} catch (\Throwable $e) {
    echo $e->getMessage();
}
```

---

## 🧰 لاگ‌گذاری و مانیتورینگ

در صورتی که مقدار `ERSALAK_SMS_LOG` در `.env` برابر `true` باشد، لاگ درخواست‌ها و پاسخ‌ها در `log` لاراول ثبت می‌گردد.

---

## 🧪 تست پکیج در پروژه واقعی

پیشنهاد می‌شود برای تست اولیه، از ابزارهایی مانند [Mailtrap](https://mailtrap.io/) یا [Postman](https://postman.com) استفاده نمایید تا عملکرد API و پارامترهای ارسال را بررسی کنید.

---

## 🙋‍♂️ پشتیبانی

📞 تماس: [02191091557](https://ersalak.ir/contact)
