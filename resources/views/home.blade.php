@extends('layouts.public')

@section('content')
<!-- HERO SECTION -->
<section class="hero-section-modern" id="home">
    <div class="hero-background"></div>
    <div class="hero-overlay"></div>
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-12 text-center hero-content">
                <div class="animate-fade-in">
                    <div class="mb-4">
                        <span class="badge bg-warning text-dark px-4 py-2 rounded-pill fs-6 shadow-lg">
                            <i class="fas fa-star me-2"></i>
                            منصة الخدمات الأكثر ثقة في المملكة
                        </span>
                    </div>
                    <h1 class="display-1 fw-bold text-white mb-4 text-shadow">فايد</h1>
                    <h2 class="fs-2 text-white mb-4 fw-light">تطبيق يوصلك بمقدمي الخدمات حولك</h2>
                    <p class="fs-5 text-white mb-5 mx-auto opacity-90" style="max-width: 700px;">
                        احصل على خدمات السباكة، الكهرباء، التكييف، ورش السيارات وغيرها بسهولة وأمان
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="#services" class="btn btn-warning btn-lg px-5 py-3 fw-bold shadow-lg">
                            <i class="fas fa-tools me-2"></i>
                            استكشف الخدمات
                        </a>
                        <a href="#about" class="btn btn-outline-light btn-lg px-5 py-3 fw-bold">
                            <i class="fas fa-info-circle me-2"></i>
                            اعرف المزيد
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scroll Indicator -->
    <div class="scroll-down">
        <a href="#services" class="text-white text-decoration-none">
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</section>

<style>
.hero-section-modern {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url('https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=1920&q=80');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    z-index: 0;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0, 123, 255, 0.85) 0%, rgba(40, 167, 69, 0.85) 100%);
    z-index: 1;
}

.text-shadow {
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
}

.animate-fade-in {
    animation: fadeInUp 1s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.scroll-down {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 2;
    animation: bounce 2s ease-in-out infinite;
}

.scroll-down i {
    font-size: 2rem;
}

@keyframes bounce {
    0%, 100% {
        transform: translateX(-50%) translateY(0);
    }
    50% {
        transform: translateX(-50%) translateY(-10px);
    }
}

.hero-section-modern .btn-warning {
    transition: all 0.3s ease;
}

.hero-section-modern .btn-warning:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(255, 193, 7, 0.4) !important;
}

.hero-section-modern .btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-3px);
}

@media (max-width: 768px) {
    .hero-section-modern .display-1 {
        font-size: 4rem;
    }
    
    .hero-section-modern .fs-2 {
        font-size: 1.5rem !important;
    }
    
    .hero-background {
        background-attachment: scroll;
    }
}
</style>

<!-- SERVICES SECTION -->
<section id="services" class="py-5 bg-white">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">خدماتنا</h2>
            <p class="text-muted fs-5">مجموعة متنوعة من الخدمات المنزلية والتجارية</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="service-card text-center">
                    <div class="service-icon mx-auto">
                        <i class="fas fa-wrench"></i>
                    </div>
                    <h5 class="fw-bold mb-2">السباكة</h5>
                    <p class="text-muted small">إصلاح وصيانة السباكة</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="service-card text-center">
                    <div class="service-icon mx-auto">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h5 class="fw-bold mb-2">الكهرباء</h5>
                    <p class="text-muted small">أعمال الكهرباء والتمديدات</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="service-card text-center">
                    <div class="service-icon mx-auto">
                        <i class="fas fa-car"></i>
                    </div>
                    <h5 class="fw-bold mb-2">ورش السيارات</h5>
                    <p class="text-muted small">إصلاح وصيانة السيارات</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="service-card text-center">
                    <div class="service-icon mx-auto">
                        <i class="fas fa-snowflake"></i>
                    </div>
                    <h5 class="fw-bold mb-2">التكييف</h5>
                    <p class="text-muted small">صيانة وإصلاح التكييف</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="service-card text-center">
                    <div class="service-icon mx-auto">
                        <i class="fas fa-home"></i>
                    </div>
                    <h5 class="fw-bold mb-2">الصيانة المنزلية</h5>
                    <p class="text-muted small">خدمات الصيانة العامة</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="service-card text-center">
                    <div class="service-icon mx-auto">
                        <i class="fas fa-building"></i>
                    </div>
                    <h5 class="fw-bold mb-2">الخدمات التجارية</h5>
                    <p class="text-muted small">صيانة المباني التجارية</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="service-card text-center">
                    <div class="service-icon mx-auto">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h5 class="fw-bold mb-2">خدمات متخصصة</h5>
                    <p class="text-muted small">خدمات فنية متخصصة</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="service-card text-center">
                    <div class="service-icon mx-auto">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h5 class="fw-bold mb-2">خدمات طارئة</h5>
                    <p class="text-muted small">متوفر على مدار 24 ساعة</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT SECTION -->
<section id="about" class="py-5" style="background: #f8f9fa;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="display-5 fw-bold mb-4">عن فايد</h2>
                <p class="fs-5 text-muted mb-4">
                    فايد هو تطبيق يربطك بأفضل مقدمي الخدمات في منطقتك. نوفر لك خدمات موثوقة وسريعة بأسعار منافسة.
                </p>
                <div class="row g-4">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success fs-3 me-3"></i>
                            <span class="fw-bold">فنيون معتمدون</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success fs-3 me-3"></i>
                            <span class="fw-bold">أسعار شفافة</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success fs-3 me-3"></i>
                            <span class="fw-bold">خدمة سريعة</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success fs-3 me-3"></i>
                            <span class="fw-bold">دعم 24/7</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="bg-gradient rounded-4 p-5 d-inline-block" style="background: linear-gradient(135deg, #007bff, #28a745);">
                    <i class="fas fa-mobile-alt text-white" style="font-size: 150px;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">كيف يعمل فايد؟</h2>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 text-center">
                <div class="mb-4">
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                        1
                    </div>
                </div>
                <h4 class="fw-bold mb-3">اختر الخدمة</h4>
                <p class="text-muted">تصفح الخدمات واختر ما تحتاجه</p>
            </div>

            <div class="col-lg-4 text-center">
                <div class="mb-4">
                    <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                        2
                    </div>
                </div>
                <h4 class="fw-bold mb-3">احجز الموعد</h4>
                <p class="text-muted">حدد الوقت واحجز مقدم الخدمة</p>
            </div>

            <div class="col-lg-4 text-center">
                <div class="mb-4">
                    <div class="rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                        3
                    </div>
                </div>
                <h4 class="fw-bold mb-3">استمتع بالخدمة</h4>
                <p class="text-muted">يصل الفني ويقدم خدمة عالية الجودة</p>
            </div>
        </div>
    </div>
</section>

<!-- DOWNLOAD SECTION -->
<section class="py-5" style="background: linear-gradient(135deg, #007bff, #28a745);">
    <div class="container py-5">
        <div class="row align-items-center text-center">
            <div class="col-12">
                <h2 class="text-white fw-bold mb-3 display-5">حمّل التطبيق الآن</h2>
                <p class="text-white fs-5 mb-4">
                    متوفر على جميع الأجهزة الذكية
                </p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="#" class="btn btn-light btn-lg d-inline-flex align-items-center">
                        <i class="fab fa-apple fs-3 me-3"></i>
                        <div class="text-start">
                            <small class="d-block" style="font-size: 0.7rem;">حمّل من</small>
                            <strong>App Store</strong>
                        </div>
                    </a>
                    <a href="#" class="btn btn-light btn-lg d-inline-flex align-items-center">
                        <i class="fab fa-google-play fs-3 me-3"></i>
                        <div class="text-start">
                            <small class="d-block" style="font-size: 0.7rem;">حمّل من</small>
                            <strong>Google Play</strong>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
