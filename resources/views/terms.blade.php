@extends('layouts.public')

@section('title', 'الشروط والأحكام - فايد')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold mb-3" style="color: var(--dark-color);">الشروط والأحكام</h1>
                <p class="lead text-muted">القواعد والإرشادات التي تحكم استخدام تطبيق فايد وخدماته</p>
                <p class="text-muted">آخر تحديث: {{ date('d/m/Y') }}</p>
            </div>

            <!-- Terms Content -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-lg-5">
                    <div class="terms-content">
                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">الموافقة على الشروط</h2>
                            <p class="mb-3">
                                باستخدامك لتطبيق فايد وخدماته، فإنك توافق على الالتزام بهذه الشروط والأحكام. إذا كنت لا توافق على أي من هذه الشروط، يرجى عدم استخدام التطبيق أو الخدمات.
                            </p>
                            <p>
                                هذه الشروط تشكل اتفاقية قانونية بينك وبين فايد، وتحكم استخدامك للتطبيق والخدمات المقدمة من خلاله.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">تعريف المصطلحات</h2>
                            <ul>
                                <li><strong>"فايد" أو "نحن" أو "الشركة":</strong> تشير إلى شركة فايد للخدمات الرقمية</li>
                                <li><strong>"التطبيق":</strong> يشير إلى تطبيق فايد المتوفر على الهواتف الذكية</li>
                                <li><strong>"الخدمات":</strong> تشمل جميع الخدمات المقدمة من خلال التطبيق</li>
                                <li><strong>"المستخدم" أو "أنت":</strong> تشير إلى أي شخص يستخدم التطبيق أو الخدمات</li>
                                <li><strong>"مقدم الخدمة":</strong> الفني أو الشركة المسجلة في التطبيق لتقديم الخدمات</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">وصف الخدمة</h2>
                            <p class="mb-3">
                                فايد هو منصة إلكترونية تربط بين العملاء ومقدمي الخدمات المحترفين في مجالات متعددة مثل:
                            </p>
                            <ul>
                                <li>خدمات السباكة والصيانة</li>
                                <li>أعمال الكهرباء</li>
                                <li>إصلاح وصيانة السيارات</li>
                                <li>صيانة أجهزة التكييف والتبريد</li>
                                <li>خدمات الصيانة المنزلية الأخرى</li>
                            </ul>
                            <p>
                                التطبيق يتيح للعملاء طلب الخدمات وحجز المواعيد، وللمحترفين عرض خدماتهم والرد على الطلبات.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">شروط الاستخدام</h2>

                            <h4 class="h5 fw-bold mb-3">1. الأهلية:</h4>
                            <ul class="mb-4">
                                <li>يجب أن تكون فوق سن 18 عاماً أو تحصل على موافقة ولي الأمر</li>
                                <li>يجب تقديم معلومات صحيحة ودقيقة عند التسجيل</li>
                                <li>أنت مسؤول عن الحفاظ على سرية كلمة المرور</li>
                            </ul>

                            <h4 class="h5 fw-bold mb-3">2. التزامات المستخدم:</h4>
                            <ul class="mb-4">
                                <li>استخدام التطبيق للأغراض المشروعة فقط</li>
                                <li>عدم انتهاك حقوق الملكية الفكرية</li>
                                <li>عدم إرسال محتوى ضار أو مسيء</li>
                                <li>الالتزام بمعايير السلوك الأخلاقي</li>
                                <li>دفع رسوم الخدمات في الوقت المحدد</li>
                            </ul>

                            <h4 class="h5 fw-bold mb-3">3. التزامات مقدم الخدمة:</h4>
                            <ul class="mb-4">
                                <li>تقديم خدمات عالية الجودة</li>
                                <li>الحضور في المواعيد المحددة</li>
                                <li>امتلاك التراخيص والشهادات المطلوبة</li>
                                <li>الالتزام بمعايير السلامة</li>
                                <li>تقديم ضمان على الأعمال</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">الدفع والرسوم</h2>

                            <h4 class="h5 fw-bold mb-3">رسوم الخدمات:</h4>
                            <ul class="mb-4">
                                <li>تحدد أسعار الخدمات من قبل مقدمي الخدمة</li>
                                <li>يتم عرض الأسعار بوضوح قبل تأكيد الطلب</li>
                                <li>قد تطبق رسوم إضافية للخدمات الطارئة أو خارج أوقات العمل</li>
                            </ul>

                            <h4 class="h5 fw-bold mb-3">طرق الدفع:</h4>
                            <ul class="mb-4">
                                <li>الدفع عبر التطبيق باستخدام البطاقات الائتمانية أو المحافظ الإلكترونية</li>
                                <li>الدفع نقداً عند اكتمال الخدمة</li>
                                <li>جميع المعاملات المالية محمية بأحدث تقنيات الأمان</li>
                            </ul>

                            <h4 class="h5 fw-bold mb-3">سياسة الاسترداد:</h4>
                            <p>
                                يمكن طلب استرداد المبلغ في حالة عدم تقديم الخدمة أو عدم الرضا عنها، وفقاً لسياسة الاسترداد المعمول بها.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">الإلغاء والتعديل</h2>

                            <h4 class="h5 fw-bold mb-3">إلغاء الطلب:</h4>
                            <ul class="mb-4">
                                <li>يمكن إلغاء الطلب قبل وصول مقدم الخدمة</li>
                                <li>قد تطبق رسوم إلغاء في حالات معينة</li>
                                <li>سياسة الإلغاء تختلف حسب نوع الخدمة ووقت الإلغاء</li>
                            </ul>

                            <h4 class="h5 fw-bold mb-3">تعديل الطلب:</h4>
                            <p>
                                يمكن تعديل تفاصيل الطلب قبل بدء العمل، مع مراعاة إمكانية ذلك حسب نوع الخدمة.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">الضمان والمسؤولية</h2>

                            <h4 class="h5 fw-bold mb-3">ضمان الخدمات:</h4>
                            <ul class="mb-4">
                                <li>يوفر فايد ضماناً على جميع الخدمات المقدمة</li>
                                <li>مدة الضمان تختلف حسب نوع الخدمة</li>
                                <li>الضمان يغطي الأعمال والمواد المستخدمة</li>
                            </ul>

                            <h4 class="h5 fw-bold mb-3">حدود المسؤولية:</h4>
                            <ul class="mb-4">
                                <li>فايد غير مسؤول عن أخطاء مقدمي الخدمة</li>
                                <li>المسؤولية محدودة بقيمة الخدمة المقدمة</li>
                                <li>لا نتحمل مسؤولية الأضرار غير المباشرة</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">الملكية الفكرية</h2>
                            <p class="mb-3">
                                جميع حقوق الملكية الفكرية للتطبيق ومحتواه مملوكة لفايد أو مرخصة لنا، وتشمل:
                            </p>
                            <ul>
                                <li>الكود البرمجي والتصميم</li>
                                <li>الشعار والعلامات التجارية</li>
                                <li>المحتوى والنصوص</li>
                                <li>قواعد البيانات والمعلومات</li>
                            </ul>
                            <p>
                                يحظر نسخ أو توزيع أو استخدام هذه المواد دون إذن كتابي مسبق.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">الإنهاء والتعليق</h2>
                            <p class="mb-3">
                                يحق لفايد إنهاء أو تعليق حسابك في الحالات التالية:
                            </p>
                            <ul>
                                <li>انتهاك شروط الاستخدام</li>
                                <li>تقديم معلومات كاذبة</li>
                                <li>سوء استخدام الخدمة</li>
                                <li>عدم دفع الرسوم المستحقة</li>
                                <li>طلبك أو بناءً على قرار قضائي</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">القانون المطبق وحل النزاعات</h2>
                            <p class="mb-3">
                                هذه الشروط والأحكام تخضع لقوانين المملكة العربية السعودية. في حالة وجود نزاع:
                            </p>
                            <ul>
                                <li>نسعى أولاً لحل النزاع ودياً</li>
                                <li>يمكن اللجوء للتحكيم التجاري</li>
                                <li>المحاكم السعودية هي الجهة المختصة</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">التعديل على الشروط</h2>
                            <p class="mb-3">
                                نحتفظ بالحق في تعديل هذه الشروط والأحكام في أي وقت. سيتم إخطار المستخدمين بالتغييرات الجوهرية:
                            </p>
                            <ul>
                                <li>عبر إشعار في التطبيق</li>
                                <li>عبر البريد الإلكتروني</li>
                                <li>على الموقع الإلكتروني</li>
                            </ul>
                            <p>
                                استمرار استخدام الخدمة بعد التعديل يعني الموافقة على الشروط الجديدة.
                            </p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h3 fw-bold mb-4" style="color: var(--primary-color);">التواصل والاستفسارات</h2>
                            <p class="mb-3">
                                لأي استفسارات حول هذه الشروط أو الخدمات، يمكنك التواصل معنا:
                            </p>
                            <div class="bg-light p-4 rounded">
                                <p class="mb-2"><strong>البريد الإلكتروني:</strong> support@faid.com</p>
                                <p class="mb-2"><strong>الهاتف:</strong> +966 XX XXX XXXX</p>
                                <p class="mb-2"><strong>أوقات العمل:</strong> 24/7 للطوارئ، 9 صباحاً - 6 مساءً للاستفسارات</p>
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



