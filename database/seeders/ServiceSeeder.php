<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Media;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ServiceSeeder extends Seeder
{
    /**
     * Sample service data
     */
    private array $serviceTemplates = [
        [
            'title' => ['en' => 'Professional Cleaning Service', 'ar' => 'خدمة تنظيف احترافية'],
            'description' => [
                'en' => 'High-quality cleaning services for homes and offices. We provide thorough cleaning with eco-friendly products.',
                'ar' => 'خدمات تنظيف عالية الجودة للمنازل والمكاتب. نقدم تنظيفاً شاملاً بمنتجات صديقة للبيئة.'
            ],
            'price_type' => Service::PRICE_TYPE_FIXED,
            'price' => 150.00,
            'address' => [
                'en' => '3154 Hassan Al Shouri, Al Sinaiyah, Riyadh 12844, Saudi Arabia',
                'ar' => '3154 حسن الشوري، الصناعية، الرياض 12844، السعودية'
            ],
            'lat' => 24.6494842,
            'lng' => 46.7454945,
        ],
        [
            'title' => ['en' => 'Home Maintenance & Repair', 'ar' => 'صيانة وإصلاح المنازل'],
            'description' => [
                'en' => 'Expert home maintenance and repair services. From plumbing to electrical work, we handle it all.',
                'ar' => 'خدمات صيانة وإصلاح منزلية متخصصة. من السباكة إلى الأعمال الكهربائية، نحن نتعامل مع كل شيء.'
            ],
            'price_type' => Service::PRICE_TYPE_NEGOTIABLE,
            'price' => 200.00,
            'address' => [
                'en' => 'King Fahd Road, Olaya, Riyadh 12211, Saudi Arabia',
                'ar' => 'طريق الملك فهد، العليا، الرياض 12211، السعودية'
            ],
            'lat' => 24.7136,
            'lng' => 46.6753,
        ],
        [
            'title' => ['en' => 'Landscaping & Garden Design', 'ar' => 'تنسيق الحدائق والتصميم'],
            'description' => [
                'en' => 'Transform your outdoor space with our professional landscaping services. Custom designs to suit your needs.',
                'ar' => 'حول مساحتك الخارجية بخدمات تنسيق الحدائق الاحترافية. تصاميم مخصصة تناسب احتياجاتك.'
            ],
            'price_type' => Service::PRICE_TYPE_FIXED,
            'price' => 500.00,
            'address' => [
                'en' => 'Al Malaz, Riyadh 12611, Saudi Arabia',
                'ar' => 'الملك فيصل، الرياض 12611، السعودية'
            ],
            'lat' => 24.6408,
            'lng' => 46.7728,
        ],
        [
            'title' => ['en' => 'Catering Services', 'ar' => 'خدمات التموين'],
            'description' => [
                'en' => 'Delicious catering for events and gatherings. We provide quality food and professional service.',
                'ar' => 'تموين لذيذ للفعاليات والتجمعات. نقدم طعاماً عالي الجودة وخدمة احترافية.'
            ],
            'price_type' => Service::PRICE_TYPE_NEGOTIABLE,
            'price' => 300.00,
            'address' => [
                'en' => 'Al Wurud, Riyadh 12284, Saudi Arabia',
                'ar' => 'الورود، الرياض 12284، السعودية'
            ],
            'lat' => 24.7800,
            'lng' => 46.6750,
        ],
        [
            'title' => ['en' => 'Photography Services', 'ar' => 'خدمات التصوير'],
            'description' => [
                'en' => 'Professional photography for events, portraits, and commercial projects. High-quality results guaranteed.',
                'ar' => 'تصوير احترافي للفعاليات والصور الشخصية والمشاريع التجارية. نتائج عالية الجودة مضمونة.'
            ],
            'price_type' => Service::PRICE_TYPE_FIXED,
            'price' => 400.00,
            'address' => [
                'en' => 'Al Nakheel, Riyadh 12382, Saudi Arabia',
                'ar' => 'النخيل، الرياض 12382، السعودية'
            ],
            'lat' => 24.7500,
            'lng' => 46.7000,
        ],
        [
            'title' => ['en' => 'Moving & Relocation', 'ar' => 'خدمات النقل والانتقال'],
            'description' => [
                'en' => 'Safe and efficient moving services. We handle your belongings with care and ensure timely delivery.',
                'ar' => 'خدمات نقل آمنة وفعالة. نتعامل مع ممتلكاتك بعناية ونتأكد من التسليم في الوقت المحدد.'
            ],
            'price_type' => Service::PRICE_TYPE_FIXED,
            'price' => 250.00,
            'address' => [
                'en' => 'Al Murabba, Riyadh 12613, Saudi Arabia',
                'ar' => 'المربع، الرياض 12613، السعودية'
            ],
            'lat' => 24.6500,
            'lng' => 46.7200,
        ],
        [
            'title' => ['en' => 'IT Support & Setup', 'ar' => 'دعم تقني وإعداد'],
            'description' => [
                'en' => 'Professional IT support and computer setup services. We help with installations, troubleshooting, and maintenance.',
                'ar' => 'دعم تقني احترافي وخدمات إعداد الكمبيوتر. نساعد في التثبيت واستكشاف الأخطاء والصيانة.'
            ],
            'price_type' => Service::PRICE_TYPE_NEGOTIABLE,
            'price' => 180.00,
            'address' => [
                'en' => 'King Abdullah Road, Al Wurud, Riyadh 12284, Saudi Arabia',
                'ar' => 'طريق الملك عبدالله، الورود، الرياض 12284، السعودية'
            ],
            'lat' => 24.7700,
            'lng' => 46.6800,
        ],
        [
            'title' => ['en' => 'Interior Design Consultation', 'ar' => 'استشارة تصميم داخلي'],
            'description' => [
                'en' => 'Expert interior design consultation to transform your living spaces. Modern and elegant designs.',
                'ar' => 'استشارة تصميم داخلي متخصصة لتحويل مساحاتك المعيشية. تصاميم عصرية وأنيقة.'
            ],
            'price_type' => Service::PRICE_TYPE_FIXED,
            'price' => 350.00,
            'address' => [
                'en' => 'Al Olaya, Riyadh 12211, Saudi Arabia',
                'ar' => 'العليا، الرياض 12211، السعودية'
            ],
            'lat' => 24.7100,
            'lng' => 46.6800,
        ],
        [
            'title' => ['en' => 'Car Wash & Detailing', 'ar' => 'غسيل وتفصيل السيارات'],
            'description' => [
                'en' => 'Professional car wash and detailing services. We make your vehicle look brand new.',
                'ar' => 'خدمات غسيل وتفصيل احترافية للسيارات. نجعل سيارتك تبدو وكأنها جديدة.'
            ],
            'price_type' => Service::PRICE_TYPE_FIXED,
            'price' => 80.00,
            'address' => [
                'en' => 'Al Malqa, Riyadh 13521, Saudi Arabia',
                'ar' => 'الملكة، الرياض 13521، السعودية'
            ],
            'lat' => 24.7200,
            'lng' => 46.6500,
        ],
        [
            'title' => ['en' => 'Event Planning Services', 'ar' => 'خدمات تخطيط الفعاليات'],
            'description' => [
                'en' => 'Complete event planning services from concept to execution. We make your events memorable.',
                'ar' => 'خدمات تخطيط فعاليات كاملة من المفهوم إلى التنفيذ. نجعل فعالياتك لا تُنسى.'
            ],
            'price_type' => Service::PRICE_TYPE_NEGOTIABLE,
            'price' => 600.00,
            'address' => [
                'en' => 'Al Hamra, Riyadh 12211, Saudi Arabia',
                'ar' => 'الحمراء، الرياض 12211، السعودية'
            ],
            'lat' => 24.7000,
            'lng' => 46.6900,
        ],
    ];

    /**
     * Specific images to use for seeding
     */
    private array $seedImages = [
        '1761069602775963915.jpg',
        '1765046901310206880.jpg',
        '1768118166430612358.jpg',
        '1768120405730994502.jpg',
        '1768239308577838255.jpg',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have at least one category
        if (Category::count() === 0) {
            Category::create([
                'name' => ['en' => 'General Services', 'ar' => 'خدمات عامة'],
                'description' => ['en' => 'General services category', 'ar' => 'تصنيف للخدمات العامة'],
                'status' => 'active',
                'image' => null,
            ]);
        }
        $categoryIds = Category::pluck('id')->toArray();

        // Ensure we have a city (Riyadh)
        $city = City::where('name->ar', 'الرياض')->orWhere('name->en', 'Riyadh')->first();
        if (!$city) {
            // Ensure we have Saudi Arabia country
            $country = Country::firstOrCreate(
                ['id' => 1],
                ['name' => ['en' => 'Saudi Arabia', 'ar' => 'السعودية']]
            );
            
            $city = City::create([
                'country_id' => $country->id,
                'name' => ['en' => 'Riyadh', 'ar' => 'الرياض'],
            ]);
        }
        $cityId = $city->id;

        // Get the specific vendor user (vendor@test.com)
        $vendor = User::where('email', 'vendor@test.com')->first();
        
        if (!$vendor) {
            $this->command->warn('Vendor user (vendor@test.com) not found. Please run UserSeeder first.');
            return;
        }

        // Get the specific seed images from storage/services directory
        $imagesPath = public_path('storage/services');
        $availableImages = [];
        
        if (File::exists($imagesPath)) {
            foreach ($this->seedImages as $imageFilename) {
                $fullPath = $imagesPath . '/' . $imageFilename;
                if (File::exists($fullPath)) {
                    $availableImages[] = [
                        'path' => 'services/' . $imageFilename,
                        'fullPath' => $fullPath,
                    ];
                } else {
                    $this->command->warn("Image not found: {$imageFilename}");
                }
            }
        }

        if (empty($availableImages)) {
            $this->command->warn('No seed images found in storage/services directory.');
            return;
        }

        // Create services for the vendor
        $serviceCount = count($this->serviceTemplates);
        
        for ($i = 0; $i < $serviceCount; $i++) {
            $template = $this->serviceTemplates[$i];
            $categoryId = $categoryIds[array_rand($categoryIds)];
            
            $service = Service::create([
                'category_id' => $categoryId,
                'vendor_id' => $vendor->id,
                'title' => $template['title'],
                'description' => $template['description'],
                'price_type' => $template['price_type'],
                'price' => $template['price'],
                'address' => $template['address'] ?? null,
                'lat' => $template['lat'] ?? null,
                'lng' => $template['lng'] ?? null,
                'city_id' => $cityId,
                'status' => Service::STATUS_ACTIVE,
                'admin_status' => 'approved',
                'published_at' => now()->subDays(rand(1, 365)),
            ]);

            // Attach images to the service
            // Use 1-3 images per service, cycling through available images
            $numImages = min(rand(1, 3), count($availableImages));
            $imageIndex = $i % count($availableImages);
            
            for ($j = 0; $j < $numImages; $j++) {
                $currentImageIndex = ($imageIndex + $j) % count($availableImages);
                $imageData = $availableImages[$currentImageIndex];
                
                $fullPath = $imageData['fullPath'];
                $imagePath = $imageData['path'];
                
                if (File::exists($fullPath)) {
                    $fileInfo = File::mimeType($fullPath);
                    $fileSize = File::size($fullPath);
                    
                    Media::create([
                        'mediable_type' => Service::class,
                        'mediable_id' => $service->id,
                        'type' => 'image',
                        'path' => $imagePath,
                        'mime_type' => $fileInfo,
                        'size' => $fileSize,
                        'order' => $j,
                        'is_primary' => $j === 0, // First image is primary
                    ]);
                }
            }
        }

        $this->command->info("Created {$serviceCount} services for vendor: {$vendor->email}");
    }
}
