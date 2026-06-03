<?php
namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        // ── Expense Categories ────────────────────────────────────────────────
        $categories = [
            ['name'=>'Rent & Premises',   'name_ar'=>'الإيجار والمقر',    'icon'=>'heroicon-o-home',              'color'=>'#3B82F6','type'=>'fixed',    'sort_order'=>1],
            ['name'=>'Utilities',          'name_ar'=>'المرافق',           'icon'=>'heroicon-o-bolt',              'color'=>'#F59E0B','type'=>'variable', 'sort_order'=>2],
            ['name'=>'Salaries & Wages',  'name_ar'=>'الرواتب والأجور',   'icon'=>'heroicon-o-banknotes',         'color'=>'#8B5CF6','type'=>'fixed',    'sort_order'=>3],
            ['name'=>'Marketing',          'name_ar'=>'التسويق',           'icon'=>'heroicon-o-megaphone',         'color'=>'#EC4899','type'=>'variable', 'sort_order'=>4],
            ['name'=>'Shipping & Courier', 'name_ar'=>'الشحن والتوصيل',   'icon'=>'heroicon-o-truck',             'color'=>'#14B8A6','type'=>'variable', 'sort_order'=>5],
            ['name'=>'Packaging',          'name_ar'=>'التغليف',           'icon'=>'heroicon-o-archive-box',       'color'=>'#F97316','type'=>'variable', 'sort_order'=>6],
            ['name'=>'Software & Tech',    'name_ar'=>'البرمجيات والتقنية','icon'=>'heroicon-o-computer-desktop',  'color'=>'#06B6D4','type'=>'variable', 'sort_order'=>7],
            ['name'=>'Professional Fees',  'name_ar'=>'الرسوم المهنية',    'icon'=>'heroicon-o-briefcase',         'color'=>'#6366F1','type'=>'variable', 'sort_order'=>8],
            ['name'=>'Bank Charges',       'name_ar'=>'رسوم البنك',        'icon'=>'heroicon-o-building-library',  'color'=>'#64748B','type'=>'variable', 'sort_order'=>9],
            ['name'=>'Photography',        'name_ar'=>'التصوير',           'icon'=>'heroicon-o-camera',            'color'=>'#A855F7','type'=>'variable', 'sort_order'=>10],
            ['name'=>'Travel & Transport', 'name_ar'=>'السفر والتنقل',     'icon'=>'heroicon-o-paper-airplane',    'color'=>'#10B981','type'=>'variable', 'sort_order'=>11],
            ['name'=>'Miscellaneous',      'name_ar'=>'متنوع',             'icon'=>'heroicon-o-ellipsis-horizontal','color'=>'#9CA3AF','type'=>'variable', 'sort_order'=>12],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }
        $this->command->info('✓ ' . count($categories) . ' expense categories seeded');

        // ── Example Suppliers ─────────────────────────────────────────────────
        $suppliers = [
            [
                'name'         => 'Al Noor Leather Trading',
                'name_ar'      => 'تجارة النور للجلود',
                'country'      => 'Oman',
                'contact_person'=> 'Ahmed Al Noor',
                'phone'        => '+968 2441 2233',
                'whatsapp'     => '+968 9441 2233',
                'email'        => 'info@alnoorleather.om',
                'category'     => 'leather_goods',
                'payment_terms'=> 'net_30',
                'currency'     => 'OMR',
                'lead_time_days'=> 7,
                'rating'       => 4,
                'notes'        => 'Local Muscat supplier. Good stock of full grain and pebbled leather.',
                'is_active'    => true,
            ],
            [
                'name'         => 'Badalassi Carlo — Italy',
                'name_ar'      => 'بادالاسي كارلو — إيطاليا',
                'country'      => 'Italy',
                'contact_person'=> 'Marco Rossi',
                'phone'        => '+39 055 1234 567',
                'email'        => 'export@badalassicarlo.it',
                'website'      => 'https://www.badalassicarlo.it',
                'category'     => 'leather_goods',
                'payment_terms'=> 'net_30',
                'currency'     => 'EUR',
                'lead_time_days'=> 28,
                'rating'       => 5,
                'notes'        => 'Premium Italian vegetable tanned leather. MOQ: 10 hides. Order 4 weeks in advance.',
                'is_active'    => true,
            ],
            [
                'name'         => 'Gulf Hardware & Fittings',
                'name_ar'      => 'خليج الأجهزة والتركيبات',
                'country'      => 'UAE',
                'contact_person'=> 'Ramesh Kumar',
                'phone'        => '+971 4 555 6677',
                'whatsapp'     => '+971 55 555 6677',
                'email'        => 'sales@gulfhardware.ae',
                'category'     => 'hardware',
                'payment_terms'=> 'prepaid',
                'currency'     => 'AED',
                'lead_time_days'=> 5,
                'rating'       => 4,
                'notes'        => 'Brass buckles, D-rings, rivets, zippers. Good quality, fast delivery.',
                'is_active'    => true,
            ],
            [
                'name'         => 'Al Rafiq Packaging LLC',
                'name_ar'      => 'الرفيق للتغليف',
                'country'      => 'Oman',
                'contact_person'=> 'Salim Al Habsi',
                'phone'        => '+968 2444 5566',
                'email'        => 'orders@alrafiqpack.om',
                'category'     => 'packaging',
                'payment_terms'=> 'net_15',
                'currency'     => 'OMR',
                'lead_time_days'=> 5,
                'rating'       => 4,
                'notes'        => 'Black boxes, dust bags, tissue paper, ribbons. Minimum order 100 units.',
                'is_active'    => true,
            ],
        ];

        foreach ($suppliers as $s) {
            Supplier::firstOrCreate(['name' => $s['name']], $s);
        }
        $this->command->info('✓ ' . count($suppliers) . ' example suppliers seeded');

        // ── Example expenses this month ────────────────────────────────────────
        $rent = ExpenseCategory::where('name', 'Rent & Premises')->first();
        $utils = ExpenseCategory::where('name', 'Utilities')->first();
        $marketing = ExpenseCategory::where('name', 'Marketing')->first();
        $software = ExpenseCategory::where('name', 'Software & Tech')->first();

        $sampleExpenses = [
            ['expense_category_id'=>$rent?->id,    'title'=>'Workshop Rent — June 2026','amount_omr'=>300,'expense_date'=>now()->startOfMonth(),'payment_method'=>'bank_transfer','is_recurring'=>true,'recurring_period'=>'monthly'],
            ['expense_category_id'=>$utils?->id,   'title'=>'Electricity — Workshop',   'amount_omr'=> 85,'expense_date'=>now()->startOfMonth()->addDays(5),'payment_method'=>'bank_transfer'],
            ['expense_category_id'=>$marketing?->id,'title'=>'Instagram Ads — June',    'amount_omr'=>150,'expense_date'=>now()->startOfMonth()->addDays(3),'payment_method'=>'card'],
            ['expense_category_id'=>$software?->id, 'title'=>'Website Hosting & Domain', 'amount_omr'=> 25,'expense_date'=>now()->startOfMonth(),'payment_method'=>'card','is_recurring'=>true,'recurring_period'=>'monthly'],
        ];

        foreach ($sampleExpenses as $expense) {
            if ($expense['expense_category_id']) {
                Expense::firstOrCreate(
                    ['title'=>$expense['title'],'expense_date'=>$expense['expense_date']],
                    array_merge($expense, ['notes'=>'Example expense — update with real data'])
                );
            }
        }
        $this->command->info('✓ Sample expenses seeded');
    }
}
