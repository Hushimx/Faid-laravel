@extends('layouts.public')

@section('title', 'سياسة الخصوصية - فايد')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold mb-3" style="color: var(--dark-color);">سياسة الخصوصية</h1>
                <p class="lead text-muted">نحن في فايد نحترم خصوصيتك ونلتزم بحماية بياناتك الشخصية</p>
                <p class="text-muted">آخر تحديث: {{ date('d/m/Y') }}</p>
            </div>

            <!-- Privacy Content -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-lg-5">
                    <div class="privacy-content">
                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">مقدمة</h2>
                            <p class="mb-3">
                                مرحباً بك في تطبيق فايد. نحن نقدر ثقتك بنا ونلتزم بحماية خصوصيتك وبياناتك الشخصية. توضح سياسة الخصوصية هذه كيفية جمع واستخدام وحماية معلوماتك عند استخدام تطبيقنا وخدماتنا.
                            </p>
                            <p>
                                من خلال استخدام تطبيق فايد، فإنك توافق على جمع واستخدام معلوماتك وفقاً لهذه السياسة. يرجى قراءة هذه السياسة بعناية قبل استخدام خدماتنا.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">المعلومات التي نجمعها</h2>

                            <h4 class="h5 fw-bold mb-3">1. المعلومات التي تقدمها لنا:</h4>
                            <ul class="mb-4">
                                <li>الاسم الكامل</li>
                                <li>رقم الهاتف</li>
                                <li>عنوان البريد الإلكتروني</li>
                                <li>موقعك الجغرافي</li>
                                <li>معلومات الدفع</li>
                                <li>تفاصيل الخدمات المطلوبة</li>
                            </ul>

                            <h4 class="h5 fw-bold mb-3">2. المعلومات التي نجمعها تلقائياً:</h4>
                            <ul class="mb-4">
                                <li>عنوان IP الخاص بك</li>
                                <li>نوع الجهاز ونظام التشغيل</li>
                                <li>معلومات المتصفح</li>
                                <li>أنماط الاستخدام</li>
                                <li>موقعك الجغرافي (بموافقتك)</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">كيف نستخدم معلوماتك</h2>
                            <p class="mb-3">نستخدم المعلومات التي نجمعها للأغراض التالية:</p>
                            <ul>
                                <li>تقديم وتحسين خدماتنا</li>
                                <li>إدارة حسابك وطلباتك</li>
                                <li>معالجة المدفوعات</li>
                                <li>التواصل معك حول خدماتنا</li>
                                <li>إرسال تحديثات وإشعارات مهمة</li>
                                <li>تحسين تجربة المستخدم</li>
                                <li>ضمان الأمان والحماية</li>
                                <li>الامتثال للمتطلبات القانونية</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">مشاركة المعلومات</h2>
                            <p class="mb-3">نحن لا نبيع أو نؤجر أو نشارك معلوماتك الشخصية مع أطراف ثالثة، باستثناء الحالات التالية:</p>
                            <ul>
                                <li>مع مقدمي الخدمات المعتمدين لتنفيذ الطلبات</li>
                                <li>مع شركاء الدفع لمعالجة المعاملات المالية</li>
                                <li>عند الحاجة للامتثال للقوانين أو الأوامر القضائية</li>
                                <li>لحماية حقوقنا أو سلامة المستخدمين</li>
                                <li>بموافقتك الصريحة</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">أمان البيانات</h2>
                            <p class="mb-3">
                                نتخذ تدابير أمنية متقدمة لحماية معلوماتك الشخصية من الوصول غير المصرح به أو التغيير أو الكشف أو التدمير، وتشمل:
                            </p>
                            <ul>
                                <li>التشفير SSL/TLS للبيانات أثناء النقل</li>
                                <li>تشفير قواعد البيانات</li>
                                <li>ضوابط الوصول المحدودة</li>
                                <li>النسخ الاحتياطي المنتظم</li>
                                <li>التدقيق الأمني المستمر</li>
                                <li>تدريب الموظفين على ممارسات الأمان</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">حقوقك</h2>
                            <p class="mb-3">لديك الحق في:</p>
                            <ul>
                                <li>الوصول إلى بياناتك الشخصية</li>
                                <li>تصحيح أو تحديث معلوماتك</li>
                                <li>حذف بياناتك (حق النسيان)</li>
                                <li>تقييد معالجة بياناتك</li>
                                <li>الحصول على نسخة من بياناتك</li>
                                <li>الاعتراض على معالجة بياناتك</li>
                                <li>نقل بياناتك إلى خدمة أخرى</li>
                            </ul>
                            <p class="mt-3">
                                يمكنك ممارسة هذه الحقوق من خلال التواصل معنا عبر البريد الإلكتروني أو التطبيق.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">ملفات تعريف الارتباط (Cookies)</h2>
                            <p class="mb-3">
                                نستخدم ملفات تعريف الارتباط لتحسين تجربتك في استخدام التطبيق. يمكنك إدارة إعدادات ملفات تعريف الارتباط من خلال إعدادات متصفحك.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">الاحتفاظ بالبيانات</h2>
                            <p>
                                نحتفظ بمعلوماتك الشخصية للمدة اللازمة لتحقيق الأغراض المذكورة في هذه السياسة، أو كما يتطلبه القانون. بعد انتهاء هذه المدة، سيتم حذف أو إخفاء هويتك من البيانات.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">خصوصية الأطفال</h2>
                            <p>
                                خدماتنا غير موجهة للأطفال دون سن 13 عاماً. نحن لا نجمع عمداً معلومات شخصية من الأطفال دون موافقة الوالدين. إذا علمنا أننا جمعنا معلومات من طفل دون موافقة، سنقوم بحذفها فوراً.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">التحديثات على السياسة</h2>
                            <p class="mb-3">
                                قد نحدث سياسة الخصوصية هذه من وقت لآخر. سنخطرك بأي تغييرات جوهرية من خلال إشعار في التطبيق أو البريد الإلكتروني.
                            </p>
                            <p>
                                استمرار استخدامك لخدماتنا بعد التحديثات يعني موافقتك على السياسة المحدثة.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">تواصل معنا</h2>
                            <p class="mb-3">
                                إذا كان لديك أي أسئلة أو مخاوف بشأن سياسة الخصوصية أو كيفية تعاملنا مع بياناتك، يرجى التواصل معنا:
                            </p>
                            <div class="bg-light p-4 rounded">
                                <p class="mb-2"><strong>البريد الإلكتروني:</strong> privacy@faid.com</p>
                                <p class="mb-2"><strong>الهاتف:</strong> +966 XX XXX XXXX</p>
                                <p class="mb-0"><strong>العنوان:</strong> [عنوان الشركة]</p>
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="text-center mt-5">
                <a href="{{ route('home') }}" class="btn btn-primary px-4 py-2">
                    <i class="fa fa-arrow-right me-2"></i>
                    العودة للصفحة الرئيسية
                </a>
            </div>
        </div>
    </div>
</div>
@endsection



