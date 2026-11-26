# ركن · Rukn

متجر إلكتروني لعبايات محتشمة ومباخر كهربائية في هولندا، بثلاث لغات: الهولندية والإنجليزية والعربية (من اليمين لليسار).

**التقنيات:** Laravel 12 · PHP 8.3 · Blade + Alpine.js · Tailwind CSS 3 (Vite) · PostgreSQL في الإنتاج · الدفع عبر Mollie

## ما في المتجر

- روابط بكل لغة: `/nl/…` و `/en/…` و `/ar/…`. روابط المنتجات بالاسم، والروابط القديمة تُحوَّل تلقائياً.
- مقاسات وألوان لكل منتج، ولكل منها سعر ومخزون.
- الشراء بحساب أو بدون حساب (رابط خاص بالطلب + صفحة "ابحث عن طلبي").
- الدفع أونلاين (Mollie) و/أو الدفع عند الاستلام.
- إيميلات: ترحيب، تأكيد طلب للعميل، وإشعار بالطلبات الجديدة للمتجر.
- لوحة تحكم: منتجات، أقسام، طلبات، رسائل "اتصل بنا".
- صفحات قانونية: الشروط، الخصوصية، الكوكيز، ونموذج التراجع الأوروبي.

## التشغيل محلياً

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
```

ثم في نافذتين:

```bash
php artisan serve
```

```bash
npm run dev
```

## الاختبارات

```bash
php vendor/bin/phpunit
```

تعمل على SQLite في الذاكرة. للتجربة على PostgreSQL مرّر `DB_CONNECTION=pgsql` مع بيانات الاتصال.

## النشر (Render بـ Docker)

الـ `Dockerfile` يبني صورة إنتاج (Apache + PHP 8.3 مع intl و opcache و pdo_pgsql). عند كل تشغيل، `docker/start.sh`:

1. يستمع على المنفذ `$PORT` الذي يعطيه Render.
2. يطبّق تحديثات قاعدة البيانات (`migrate --force`). لإيقافها: `RUN_MIGRATIONS=false`.
3. يجهّز الكاش (`php artisan optimize`).

### متغيرات البيئة المهمة

| المتغير | الغرض |
|---|---|
| `APP_KEY`, `APP_URL`, `APP_DEBUG=false` | أساسية |
| `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | قاعدة البيانات |
| `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` | إرسال الإيميلات |
| `MOLLIE_KEY` | الدفع أونلاين (ابدأ بمفتاح `test_`) |
| `SHOP_PAYMENT_COD` | الدفع عند الاستلام (`true`/`false`) |
| `SHOP_CONTACT_EMAIL`, `SHOP_ORDER_NOTIFY_EMAILS` | بريد المتجر ومن يستلم إشعارات الطلبات |
| `SHOP_BUSINESS_NAME`, `SHOP_BUSINESS_ADDRESS`, `SHOP_BUSINESS_KVK` | بيانات الشركة (تظهر في نموذج التراجع وصفحة الاتصال) |
| `SHOP_SHIPPING_FLAT_RATE`, `SHOP_FREE_SHIPPING_THRESHOLD`, `SHOP_DELIVERY_DAYS_MIN/MAX`, `SHOP_RETURN_DAYS` | سياسة الشحن والإرجاع (كل نصوص المتجر تقرأ منها) |
| `SHOP_SOCIAL_INSTAGRAM`, `SHOP_SOCIAL_FACEBOOK`, `SHOP_SOCIAL_X`, `SHOP_SOCIAL_TIKTOK` | روابط الفوتر. الفارغ يُخفى |

القائمة الكاملة مع الشرح في `.env.example`.

### تنبيه: صور المنتجات

الصور المرفوعة من لوحة التحكم تُحفظ في `storage/app/public`. على Render يُمسح هذا المجلد مع كل نشر، إلا إذا ربطت **Persistent Disk** على المسار `/var/www/html/storage/app/public`. الحل البديل تخزين خارجي (مثل S3)، ويحتاج تعديلاً بسيطاً في الكود.

## هيكل مختصر

- `app/Support/`: منطق المتجر (الشحن، السلة للزوار، المخزون، الدفع، Mollie، الإيميلات).
- `config/shop.php`: كل إعدادات المتجر.
- `resources/lang/{nl,en,ar}/`: النصوص بالثلاث لغات.
- `resources/views/emails/`: قوالب الإيميلات.
- `docker/`: إعدادات Apache و PHP وسكربت التشغيل.
