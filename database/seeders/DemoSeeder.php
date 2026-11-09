<?php

namespace Database\Seeders;

use App\Models\Setting;
use Database\Seeders\Demo\AcademicsSeeder;
use Database\Seeders\Demo\CommunicationSeeder;
use Database\Seeders\Demo\DemoData;
use Database\Seeders\Demo\FinanceSeeder;
use Database\Seeders\Demo\OperationsSeeder;
use Database\Seeders\Demo\PeopleSeeder;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * A complete sample school on top of the base data (DatabaseSeeder). Run it with
 * `php artisan school:demo` (see config/school.php for the sizes). It runs once per database:
 * use `php artisan school:demo --fresh` to rebuild.
 */
class DemoSeeder extends Seeder
{
    public const MARKER = 'demo_seeded_at';

    public function run(): void
    {
        if (Setting::where('key', self::MARKER)->exists()) {
            throw new RuntimeException('Demo data is already loaded. Use `php artisan school:demo --fresh` to rebuild the database.');
        }

        DemoData::reset();
        $this->call(DatabaseSeeder::class);
        $this->settings();
        $this->call([
            PeopleSeeder::class,
            AcademicsSeeder::class,
            FinanceSeeder::class,
            OperationsSeeder::class,
            CommunicationSeeder::class,
        ]);

        Setting::updateOrCreate(['key' => self::MARKER], ['value' => now()->toDateTimeString()]);
        Setting::store([]); // clears the settings cache
    }

    /** Sample school details, only for settings that were never saved. */
    private function settings(): void
    {
        $saved = Setting::pluck('key')->all();
        Setting::store(array_diff_key([
            'school_name' => 'Angkor International School',
            'school_email' => 'office@school.test',
            'school_phone' => '023 880 123',
            'school_address' => "No. 12, Street 271\nSangkat Tuol Tumpung, Phnom Penh\nCambodia",
            'tax_name' => 'VAT',
            'tax_rate' => '10',
            'invoice_terms' => 'Payment is due within 14 days. Please quote the invoice number with your payment.',
            'bank_account_holder' => 'Angkor International School',
            'bank_name' => 'ABA Bank',
            'bank_ifsc_code' => 'ABAAKHPP',
            'bank_account_number' => '000 123 456',
            'library_fine_per_day' => '0.25',
        ], array_flip($saved)));
    }
}
