<?php

namespace Database\Seeders;

use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Database\Seeder;

/**
 * Sample subjects and courses so the platform can be tried end to end.
 * Content is a draft — replace it with reviewed lessons before launch.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $cats = collect([
            ['ai', 'هوش مصنوعی', 'مصنوعي ځیرکتیا', 'Artificial Intelligence', 'teal', 'spark'],
            ['film', 'فیلم‌سازی', 'فلم جوړول', 'Film-making', 'red', 'film'],
            ['storytelling', 'داستان‌گویی', 'کیسه ویل', 'Storytelling', 'violet', 'story'],
            ['web-development', 'برنامه‌نویسی ویب', 'ویب پروګرامینګ', 'Web development', 'blue', 'code'],
            ['graphic-design', 'دیزاین گرافیک', 'ګرافیک ډیزاین', 'Graphic design', 'orange', 'palette'],
            ['book-writing', 'کتاب‌نویسی', 'کتاب لیکنه', 'Book writing', 'green', 'book'],
        ])->mapWithKeys(fn ($c, $i) => [$c[0] => Category::updateOrCreate(['slug' => $c[0]], [
            'name_prs' => $c[1], 'name_ps' => $c[2], 'name_en' => $c[3], 'color' => $c[4], 'icon' => $c[5], 'sort' => $i,
        ])]);

        $this->course($cats['ai'], [
            'slug' => 'ai-beginner', 'title' => 'مبتدی هوش مصنوعی', 'language' => 'prs', 'is_free' => true, 'is_featured' => true,
            'subtitle' => 'گفتگو با AI، نوشتن پرامپت خوب و استفاده از AI در زندگی روزمره.',
            'outcomes' => ['هوش مصنوعی چیست و چه کاری می‌تواند', 'نوشتن یک پرامپت خوب', 'استفاده از AI برای نامه و ترجمه', 'شناختن اشتباه‌های AI'],
            'instructor_name' => 'تیم زیرک',
        ], [
            ['هوش مصنوعی چیست', [
                ['هوش مصنوعی چیست', "هوش مصنوعی برنامه‌ای است که از مثال‌های زیاد یاد می‌گیرد و می‌تواند متن بنویسد، ترجمه کند و به سوال‌ها جواب بدهد.\n\nAI مثل یک همکار کمکی است: سریع است، ولی همیشه درست نمی‌گوید.", null, 'یک کار روزانه‌ات را بنویس که AI می‌تواند در آن کمک کند.', 360, true],
                ['AI چه کارهایی می‌تواند', "AI می‌تواند متن بنویسد، خلاصه کند، ترجمه کند، عکس بسازد و ایده بدهد.\n\nAI نمی‌تواند به جای تو تصمیم مهم بگیرد یا همیشه حقیقت را بداند.", null, null, 420, false],
            ], [
                ['کدام کار را AI به خوبی انجام می‌دهد؟', ['خلاصه کردن یک متن طولانی', 'تصمیم گرفتن به جای شما', 'دانستن همه حقایق بدون اشتباه', 'دیدن آینده'], 0, 'خلاصه کردن متن یکی از کارهای قوی AI است.'],
                ['جواب‌های AI همیشه درست است.', ['درست', 'نادرست'], 1, 'AI گاهی با اطمینان جواب اشتباه می‌دهد. چیزهای مهم را بررسی کن.'],
            ], [
                ['AI از چه چیزی یاد می‌گیرد؟', ['از مثال‌های زیاد', 'از خواب', 'از برق', 'از هیچ چیز'], 0],
                ['بهترین برخورد با جواب AI چیست؟', ['همیشه قبول کن', 'چیزهای مهم را بررسی کن', 'هرگز استفاده نکن', 'فقط به انگلیسی بپرس'], 1],
            ]],
            ['گفتگو با AI', [
                ['اولین گفتگو', 'یک اپلیکیشن AI مثل Claude یا ChatGPT را باز کن و به زبان دری سلام کن. بعد یک سوال ساده بپرس.', 'سلام! لطفاً سه نکته برای یادگیری بهتر به من بگو.', 'همین سوال را بپرس و جواب را بخوان.', 480, false],
                ['نوشتن یک پرامپت خوب', "یک پرامپت خوب پنج بخش دارد: **نقش**، **کار**، **جزئیات**، **شکل جواب** و **زبان**.\n\nهرچه جزئیات بیشتری بدهی، جواب بهتری می‌گیری.", 'تو یک مشاور کاریابی هستی. برای من یک درخواست وظیفه یک‌صفحه‌ای برای وظیفه محاسب در یک شرکت در کابل بنویس. من ۲ سال تجربه دارم. جواب را به زبان دری و با لحن رسمی بنویس.', 'این پرامپت را برای وظیفه دلخواه خودت تغییر بده و امتحان کن.', 480, false],
            ], [
                ['کدام پرامپت جواب بهتری می‌دهد؟', ['CV بنویس', 'یک CV یک‌صفحه‌ای برای محاسب ۲۴ ساله با ۲ سال تجربه، به انگلیسی بنویس', 'CV', 'کمک کن'], 1, 'پرامپت خوب نقش، جزئیات و شکل جواب را می‌گوید.'],
                ['کدام بخش در پرامپت خوب نیست؟', ['نقش', 'جزئیات', 'رمز عبور شما', 'زبان جواب'], 2, 'هرگز رمز عبور یا اطلاعات خصوصی را به AI نده.'],
            ], [
                ['پنج بخش پرامپت خوب کدام است؟', ['نقش، کار، جزئیات، شکل جواب، زبان', 'سلام، خداحافظ، تشکر، ببخشید، بله', 'فقط کار', 'فقط زبان'], 0],
                ['اگر جواب AI کامل نبود چه کنی؟', ['اپلیکیشن را پاک کن', 'سوال تکمیلی بپرس', 'همان را قبول کن', 'گوشی را خاموش کن'], 1],
            ]],
        ]);

        $this->course($cats['film'], [
            'slug' => 'mobile-filmmaking', 'title' => 'فیلم‌سازی با موبایل', 'language' => 'prs', 'price_afn' => 699, 'price_usd_cents' => 999, 'is_featured' => true,
            'subtitle' => 'با گوشی خودت فیلم کوتاه، مستند و ویدیوی تبلیغاتی بساز.',
            'outcomes' => ['نوشتن سناریوی کوتاه', 'تنظیم دوربین موبایل و نور', 'ضبط صدای تمیز', 'تدوین و زیرنویس'],
            'instructor_name' => '[نام استاد]', 'instructor_bio' => 'فیلمساز و تدوینگر',
        ], [
            ['از ایده تا سناریو', [
                ['فیلم خوب چه دارد؟', 'هر فیلم خوب یک داستان ساده، یک شخصیت و یک تغییر دارد.', null, 'یک ایده یک‌جمله‌ای برای فیلم کوتاهت بنویس.', 420, true],
                ['نوشتن سناریو با کمک AI', 'از AI بخواه ایده‌ات را به یک سناریوی ۶ نمایی تبدیل کند.', 'تو یک فیلمنامه‌نویس هستی. ایده من این است: [ایده]. آن را به یک سناریوی کوتاه با ۶ نما تبدیل کن.', null, 540, false],
            ], [
                ['هر فیلم خوب چه دارد؟', ['داستان، شخصیت و تغییر', 'فقط موسیقی', 'فقط دوربین گران', 'فقط بازیگر مشهور'], 0, 'داستان ساده با شخصیت و تغییر، پایه هر فیلم است.'],
            ], [
                ['سناریو کوتاه را چگونه شروع کنیم؟', ['با یک ایده یک‌جمله‌ای', 'با خرید دوربین', 'با تدوین', 'با موسیقی'], 0],
            ]],
            ['فیلمبرداری با موبایل', [
                ['نور و زاویه دوربین', 'همیشه طوری بایست که نور به صورت شخص بتابد، نه به لنز دوربین.', null, 'یک نمای ۱۰ ثانیه‌ای کنار پنجره بگیر.', 480, false],
            ], [
                ['در روز آفتابی، آفتاب کجا باشد بهتر است؟', ['پشت سر همان شخص', 'پشت سر یا کنار فیلمبردار', 'دقیقاً بالای سر', 'فرقی نمی‌کند'], 1, 'نور از پشت یا کنار فیلمبردار صورت را روشن می‌کند.'],
            ], [
                ['برای فیلمبرداری داخل خانه کجا بایستیم؟', ['کنار پنجره، رو به نور', 'پشت به پنجره', 'در تاریکی', 'زیر چراغ زرد'], 0],
            ]],
        ]);

        $this->course($cats['ai'], [
            'slug' => 'ai-start-pashto', 'title' => 'د مصنوعي ځیرکتیا پیل', 'language' => 'ps', 'is_free' => true,
            'subtitle' => 'له AI سره خبرې او ښه پرامپټ لیکل — په پښتو.',
            'outcomes' => ['AI څه شی دی', 'ښه پرامپټ لیکل'],
            'instructor_name' => 'د ځیرک ټیم',
        ], [
            ['مصنوعي ځیرکتیا څه ده', [
                ['مصنوعي ځیرکتیا څه ده', 'مصنوعي ځیرکتیا هغه پروګرام دی چې له ډېرو بېلګو زده کوي او متن لیکلی، ژباړه کولی او پوښتنو ته ځواب ویلی شي.', null, null, 360, true],
            ], [
                ['د AI ځوابونه تل سم دي.', ['سم', 'ناسم'], 1, 'AI کله ناکله په ډاډ سره غلط ځواب ورکوي.'],
            ], [
                ['AI له څه زده کوي؟', ['له ډېرو بېلګو', 'له خوب', 'له هېڅ', 'له برېښنا'], 0],
            ]],
        ]);

        foreach ([
            ['storytelling', 'social-storytelling', 'داستان‌گویی برای شبکه‌های اجتماعی', 'ps', 499],
            ['web-development', 'website-from-scratch', 'ساخت ویب‌سایت از صفر', 'prs', 999],
            ['graphic-design', 'canva-and-ai-design', 'دیزاین با Canva و AI', 'prs', 599],
            ['book-writing', 'first-book', 'نوشتن و نشر اولین کتاب', 'prs', 799],
        ] as [$cat, $slug, $title, $lang, $price]) {
            $this->course($cats[$cat], [
                'slug' => $slug, 'title' => $title, 'language' => $lang, 'price_afn' => $price, 'price_usd_cents' => 999,
                'level' => $slug === 'first-book' ? 'intermediate' : 'beginner',
                'subtitle' => 'دوره نمونه — محتوای کامل به زودی.', 'instructor_name' => '[نام استاد]',
            ], [
                ['فصل اول', [['درس اول', 'محتوای این درس به زودی اضافه می‌شود.', null, null, 300, true]], [], []],
            ]);
        }

        Coupon::updateOrCreate(['code' => 'WELCOME30'], ['type' => 'percent', 'value' => 30, 'max_uses' => 500]);
        Coupon::updateOrCreate(['code' => 'SCHOLARSHIP'], ['type' => 'percent', 'value' => 100, 'is_scholarship' => true, 'reason' => 'Women scholarship (demo)', 'max_uses' => 50]);
    }

    /**
     * @param  array<int, array{0:string,1:array,2:array,3:array}>  $chapters  [title, lessons, practice, test]
     */
    private function course(Category $category, array $attributes, array $chapters): Course
    {
        $course = Course::withTrashed()->updateOrCreate(['slug' => $attributes['slug']], $attributes + [
            'category_id' => $category->id,
            'status' => CourseStatus::Published,
            'published_at' => now(),
            'certificate_type' => 'achievement',
            'level' => 'beginner',
        ]);

        $course->chapters()->delete();

        foreach ($chapters as $ci => [$title, $lessons, $practice, $test]) {
            $chapter = $course->chapters()->create(['title' => $title, 'sort' => $ci]);

            foreach ($lessons as $li => [$lt, $body, $prompt, $task, $seconds, $preview]) {
                $chapter->lessons()->create([
                    'title' => $lt, 'body' => $body, 'prompt' => $prompt, 'task' => $task,
                    'duration_seconds' => $seconds, 'is_preview' => $preview, 'sort' => $li,
                ]);
            }
            foreach ($practice as $qi => [$q, $options, $correct, $why]) {
                $chapter->questions()->create(['kind' => 'practice', 'body' => $q, 'options' => $options, 'correct_index' => $correct, 'explanation' => $why, 'sort' => $qi]);
            }
            foreach ($test as $qi => [$q, $options, $correct]) {
                $chapter->questions()->create(['kind' => 'test', 'body' => $q, 'options' => $options, 'correct_index' => $correct, 'sort' => $qi]);
            }
        }

        return $course;
    }
}
