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

تعمل على SQLite في الذاكرة. للتجربة على MariaDB/MySQL (قاعدة Hostinger) مرّر `DB_CONNECTION=mariadb` مع بيانات الاتصال. وعلى GitHub تعمل تلقائياً على SQLite و MariaDB و PostgreSQL مع كل تعديل، وبعد كل دمج ناجح في `main` يُحدَّث فرع `production` بملفات التصميم المبنيّة.

## النشر على Hostinger (عبر SSH)

المتجر يعمل على استضافة Hostinger، والدومين يشير إلى مجلد `public` في المشروع. خطوات نشر تحديث جديد بعد الدخول بـ SSH إلى مجلد المشروع:

1. **نسخة احتياطية من قاعدة البيانات** من hPanel ← Databases ← phpMyAdmin ← Export.
2. سحب الكود الجديد من فرع `production`، وهو نفس `main` ومعه ملفات التصميم المبنيّة (`public/build`). يحدّثه GitHub تلقائياً بعد كل دمج في `main` تنجح اختباراته، فلا يحتاج السيرفر Node.js:

```bash
git pull origin production
```

أول مرة فقط، إذا كان السيرفر على فرع آخر: احذف مجلد `public/build` القديم الذي بُني يدوياً (git يرفض الكتابة فوقه، ونسخته الجديدة تأتي مع الفرع)، ثم انتقل للفرع:

```bash
rm -rf public/build
```

```bash
git fetch origin production && git checkout production
```

3. تثبيت مكتبات PHP بدون أدوات التطوير:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

4. تحديثات قاعدة البيانات، ثم ربط مجلد الصور، ثم تجهيز الكاش:

```bash
php artisan migrate --force
```

```bash
php artisan storage:link
```

```bash
php artisan optimize
```

**لا تشغّل `php artisan db:seed` على السيرفر:** هو لبيانات التجربة فقط، ويُنشئ حسابات مدير بكلمة سر معروفة.

### المهام المجدولة (Cron Job واحد)

الاستضافة المشتركة لا تسمح بعملية تعمل باستمرار، فيكفي Cron Job **واحد** يشغّل مجدول Laravel كل دقيقة. هو بدوره يرسل الإيميلات المنتظرة كل دقيقة، ويرسل طلبات التقييم كل ساعة (`routes/console.php`). في hPanel ← Advanced ← Cron Jobs أضف مهمة **كل دقيقة** بهذا الأمر (مع مسار مشروعك، ومسار PHP كما يعرضه hPanel):

```bash
cd /home/USER/domains/rukn.nl/PROJECT && php artisan schedule:run >/dev/null 2>&1
```

### الإيميلات في الخلفية

كل إيميلات المتجر (الترحيب، تأكيد الطلب، إشعار المتجر، الشحن، كلمة السر، رسائل التواصل) تُوضع في طابور في قاعدة البيانات (`QUEUE_CONNECTION=database`) وترسلها المهمة المجدولة كل دقيقة، فلا ينتظر العميل سيرفر البريد. إذا فشل الإرسال تُعاد المحاولة بعد دقيقة، ثم 5 دقائق، ثم 30 دقيقة.

إذا بقي إيميل فاشلاً بعد كل المحاولات، تظهر رسالة في لوحة التحكم مع زرّي "إعادة الإرسال" و"حذف". وتظهر أيضاً إذا كانت إيميلات تنتظر أكثر من 15 دقيقة (أي أن المهمة المجدولة لا تعمل). من الطرفية:

```bash
php artisan queue:failed
```

```bash
php artisan queue:retry all
```

### متغيرات البيئة المهمة

| المتغير | الغرض |
|---|---|
| `APP_KEY`, `APP_URL`, `APP_DEBUG=false` | أساسية |
| `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | قاعدة البيانات (MySQL/MariaDB من hPanel) |
| `QUEUE_CONNECTION=database` | الإيميلات في الطابور (لا تضع `sync`) |
| `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` | إرسال الإيميلات |
| `MOLLIE_KEY` | الدفع أونلاين (ابدأ بمفتاح `test_`) |
| `SHOP_PAYMENT_COD` | الدفع عند الاستلام (`true`/`false`) |
| `SHOP_CONTACT_EMAIL`, `SHOP_ORDER_NOTIFY_EMAILS` | بريد المتجر ومن يستلم إشعارات الطلبات |
| `SHOP_BUSINESS_NAME`, `SHOP_BUSINESS_ADDRESS`, `SHOP_BUSINESS_KVK` | بيانات الشركة (تظهر في نموذج التراجع وصفحة الاتصال) |
| `SHOP_SHIPPING_FLAT_RATE`, `SHOP_FREE_SHIPPING_THRESHOLD`, `SHOP_DELIVERY_DAYS_MIN/MAX`, `SHOP_RETURN_DAYS` | سياسة الشحن والإرجاع (كل نصوص المتجر تقرأ منها) |
| `SHOP_SOCIAL_INSTAGRAM`, `SHOP_SOCIAL_FACEBOOK`, `SHOP_SOCIAL_X`, `SHOP_SOCIAL_TIKTOK` | روابط الفوتر. الفارغ يُخفى |

هذه المتغيرات تُكتب في ملف `.env` على السيرفر، والقائمة الكاملة مع الشرح في `.env.example`.

### صور المنتجات (Cloudflare R2)

الصور التي تُرفع من لوحة التحكم (صور المنتجات والصور الشخصية) تُحفظ في "قرص الوسائط":

- **افتراضياً** (`SHOP_MEDIA_DISK=public`) على قرص السيرفر في `storage/app/public`، وتُعرض عبر الرابط `public/storage` (يُنشئه `php artisan storage:link`). على Hostinger تبقى الصور محفوظة بين التحديثات، فلا تحتاج R2 إلا إذا أردت ذلك.
- **مع R2** (`SHOP_MEDIA_DISK=r2`) تُرفع إلى Cloudflare R2 وتُعرض من رابطه العام، فلا تتأثر بالنشر.

متغيرات R2:

| المتغير | القيمة |
|---|---|
| `SHOP_MEDIA_DISK` | `r2` |
| `R2_ACCESS_KEY_ID`, `R2_SECRET_ACCESS_KEY` | من مفتاح API تنشئه في R2 بصلاحية Object Read & Write على الـ bucket |
| `R2_BUCKET` | اسم الـ bucket |
| `R2_ENDPOINT` | `https://<Account ID>.r2.cloudflarestorage.com` |
| `R2_PUBLIC_URL` | رابط الـ bucket العام (`https://pub-….r2.dev` أو دومينك) بدون `/` في الآخر |

لنقل صور موجودة على قرص السيرفر إلى R2 (المسارات لا تتغير، فلا تعديل على قاعدة البيانات):

```bash
php artisan media:copy
```

## هيكل مختصر

- `app/Support/`: منطق المتجر (الشحن، السلة للزوار، المخزون، الدفع، Mollie، الإيميلات).
- `config/shop.php`: كل إعدادات المتجر.
- `resources/lang/{nl,en,ar}/`: النصوص بالثلاث لغات.
- `resources/views/emails/`: قوالب الإيميلات.
