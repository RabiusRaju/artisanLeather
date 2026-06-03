<?php
namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class HRSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Departments ────────────────────────────────────────────────
        $depts = [
            ['name'=>'Workshop',    'name_ar'=>'الورشة',         'sort_order'=>1],
            ['name'=>'Design & QC', 'name_ar'=>'التصميم والجودة','sort_order'=>2],
            ['name'=>'Sales',       'name_ar'=>'المبيعات',        'sort_order'=>3],
            ['name'=>'Operations',  'name_ar'=>'العمليات',        'sort_order'=>4],
        ];
        foreach ($depts as $d) {
            Department::firstOrCreate(['name' => $d['name']], $d);
        }

        $workshop  = Department::where('name', 'Workshop')->first();
        $design    = Department::where('name', 'Design & QC')->first();
        $sales     = Department::where('name', 'Sales')->first();
        $ops       = Department::where('name', 'Operations')->first();

        // ── 2. Employees ──────────────────────────────────────────────────
        $employees = [
            // Workshop — craftsmen
            [
                'department_id'    => $workshop->id,
                'name'             => 'Khalid Al Balushi',
                'name_ar'          => 'خالد البلوشي',
                'date_of_birth'    => '1985-03-15',
                'gender'           => 'male',
                'nationality'      => 'Omani',
                'visa_type'        => 'citizen',
                'phone'            => '+968 9111 2233',
                'whatsapp'         => '+968 9111 2233',
                'email'            => 'khalid@artisanleather.om',
                'governorate'      => 'Muscat',
                'city'             => 'Al Seeb',
                'job_title'        => 'Head Craftsman',
                'employment_type'  => 'full_time',
                'date_hired'       => '2015-01-10',
                'monthly_salary_omr' => 650.000,
                'skills'           => ['cutting','stitching','hand_stitching','burnishing','hardware_fitting','quality_control'],
                'status'           => 'active',
                'notes'            => 'Senior craftsman with 15+ years experience. Expert in saddle-stitch technique.',
            ],
            [
                'department_id'    => $workshop->id,
                'name'             => 'Ahmed Rizwan',
                'name_ar'          => 'أحمد رضوان',
                'date_of_birth'    => '1992-07-22',
                'gender'           => 'male',
                'nationality'      => 'Pakistani',
                'visa_type'        => 'employment_visa',
                'visa_expiry'      => '2027-03-15',
                'passport_number'  => 'PK8823441',
                'phone'            => '+968 9234 5678',
                'governorate'      => 'Muscat',
                'city'             => 'Ruwi',
                'job_title'        => 'Leather Artisan — Stitching',
                'employment_type'  => 'full_time',
                'date_hired'       => '2019-06-01',
                'hourly_rate_omr'  => 1.500,
                'skills'           => ['stitching','hand_stitching','machine_stitching','edge_finishing'],
                'status'           => 'active',
            ],
            [
                'department_id'    => $workshop->id,
                'name'             => 'Mohammed Al Harthi',
                'name_ar'          => 'محمد الحارثي',
                'date_of_birth'    => '1990-11-08',
                'gender'           => 'male',
                'nationality'      => 'Omani',
                'visa_type'        => 'citizen',
                'phone'            => '+968 9345 6789',
                'governorate'      => 'Muscat',
                'city'             => 'Qurum',
                'job_title'        => 'Leather Artisan — Cutting & Finishing',
                'employment_type'  => 'full_time',
                'date_hired'       => '2018-03-15',
                'monthly_salary_omr' => 480.000,
                'skills'           => ['cutting','pattern_making','burnishing','dyeing','edge_finishing'],
                'status'           => 'active',
            ],
            [
                'department_id'    => $workshop->id,
                'name'             => 'Priya Menon',
                'name_ar'          => 'بريا مينون',
                'date_of_birth'    => '1994-05-30',
                'gender'           => 'female',
                'nationality'      => 'Indian',
                'visa_type'        => 'employment_visa',
                'visa_expiry'      => '2026-09-20',
                'passport_number'  => 'IN4456789',
                'phone'            => '+968 9456 7890',
                'governorate'      => 'Muscat',
                'city'             => 'Ghubrah',
                'job_title'        => 'Monogramming Specialist',
                'employment_type'  => 'full_time',
                'date_hired'       => '2021-02-01',
                'hourly_rate_omr'  => 1.750,
                'skills'           => ['monogramming','design','pattern_making','quality_control'],
                'status'           => 'active',
                'notes'            => 'Specialises in custom monogramming and bespoke engravings.',
            ],

            // Design & QC
            [
                'department_id'    => $design->id,
                'name'             => 'Sara Al Rashidi',
                'name_ar'          => 'سارة الراشدية',
                'date_of_birth'    => '1988-09-12',
                'gender'           => 'female',
                'nationality'      => 'Omani',
                'visa_type'        => 'citizen',
                'phone'            => '+968 9567 8901',
                'email'            => 'sara.design@artisanleather.om',
                'governorate'      => 'Muscat',
                'city'             => 'Madinat Qaboos',
                'job_title'        => 'Head of Design & Quality Control',
                'employment_type'  => 'full_time',
                'date_hired'       => '2016-08-20',
                'monthly_salary_omr' => 700.000,
                'skills'           => ['design','quality_control','pattern_making','customer_service'],
                'status'           => 'active',
            ],

            // Sales
            [
                'department_id'    => $sales->id,
                'name'             => 'Tariq Al Wahaibi',
                'name_ar'          => 'طارق الوهيبي',
                'date_of_birth'    => '1991-04-18',
                'gender'           => 'male',
                'nationality'      => 'Omani',
                'visa_type'        => 'citizen',
                'phone'            => '+968 9678 9012',
                'whatsapp'         => '+968 9678 9012',
                'email'            => 'tariq@artisanleather.om',
                'governorate'      => 'Muscat',
                'city'             => 'Bawshar',
                'job_title'        => 'Sales & Customer Relations Manager',
                'employment_type'  => 'full_time',
                'date_hired'       => '2020-01-05',
                'monthly_salary_omr' => 580.000,
                'skills'           => ['sales','customer_service','inventory'],
                'status'           => 'active',
            ],

            // Operations
            [
                'department_id'    => $ops->id,
                'name'             => 'Rabius Sani Raju',
                'name_ar'          => 'ربيع الثاني راجو',
                'date_of_birth'    => '1985-01-01',
                'gender'           => 'male',
                'nationality'      => 'Bangladeshi',
                'visa_type'        => 'employment_visa',
                'phone'            => '+968 1234 5678',
                'email'            => 'owner@artisanleather.om',
                'governorate'      => 'Muscat',
                'city'             => 'Muscat',
                'job_title'        => 'Founder & Managing Director',
                'employment_type'  => 'full_time',
                'date_hired'       => '2009-01-01',
                'monthly_salary_omr' => 0,
                'skills'           => ['design','quality_control','sales','customer_service','inventory'],
                'status'           => 'active',
                'notes'            => 'Founder of Artisan Leather. Oversees all operations.',
            ],
        ];

        foreach ($employees as $data) {
            Employee::firstOrCreate(['email' => $data['email'] ?? null, 'phone' => $data['phone']], $data);
        }

        $this->command->info('✓ ' . count($depts) . ' departments seeded');
        $this->command->info('✓ ' . count($employees) . ' employees seeded');
        $this->command->line('');
        $this->command->line('Department summary:');
        Department::withCount('employees')->get()->each(fn($d) =>
            $this->command->line("  {$d->name}: {$d->employees_count} employees")
        );
    }
}
