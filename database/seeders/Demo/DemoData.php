<?php

namespace Database\Seeders\Demo;

use App\Enums\Gender;
use Faker\Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Shared helpers for the demo seeders: a seeded Faker (so the same seed always builds the same
 * school), Cambodian names, phone numbers and places, and the school-day calendar.
 */
class DemoData
{
    public const FAMILY_NAMES = [
        'Sok', 'Chan', 'Kim', 'Heng', 'Lim', 'Ly', 'Meas', 'Chea', 'Keo', 'Nhem', 'Pich', 'Prak', 'Seng', 'Sun',
        'Tep', 'Vann', 'Yim', 'Mao', 'Ouk', 'Phan', 'Hun', 'Khun', 'Long', 'Nget', 'Phal', 'Ros', 'Sam', 'Tan',
    ];

    public const MALE_NAMES = [
        'Dara', 'Rithy', 'Vannak', 'Sovann', 'Visal', 'Piseth', 'Rotha', 'Veasna', 'Kosal', 'Vichea', 'Panha',
        'Bunthoeun', 'Serey', 'Sokha', 'Chanthou', 'Makara', 'Pheakdey', 'Ratanak', 'Sambath', 'Thida', 'Vuthy',
        'Borey', 'Chamroeun', 'Kimhong', 'Monyrath', 'Narith', 'Sophal', 'Sothy', 'Vibol', 'Pisal',
    ];

    public const FEMALE_NAMES = [
        'Sophea', 'Bopha', 'Chenda', 'Kanha', 'Lina', 'Pisey', 'Sreymom', 'Nita', 'Sothea', 'Chanthy', 'Malis',
        'Sreyneang', 'Rachana', 'Sopheak', 'Sokunthea', 'Channary', 'Dalin', 'Kunthea', 'Leakena', 'Mealea',
        'Phalla', 'Raksmey', 'Sreypov', 'Theary', 'Vanny', 'Bormey', 'Chantrea', 'Davy', 'Kalyan', 'Somaly',
    ];

    public const PLACES = [
        ['city' => 'Phnom Penh', 'state' => 'Phnom Penh', 'zip' => '120101'],
        ['city' => 'Siem Reap', 'state' => 'Siem Reap', 'zip' => '170101'],
        ['city' => 'Battambang', 'state' => 'Battambang', 'zip' => '020101'],
        ['city' => 'Kampong Cham', 'state' => 'Kampong Cham', 'zip' => '030101'],
        ['city' => 'Kampot', 'state' => 'Kampot', 'zip' => '070101'],
        ['city' => 'Takeo', 'state' => 'Takeo', 'zip' => '210101'],
    ];

    /** Cambodian public holidays with fixed dates: [name, first day, last day] as month-day */
    public const HOLIDAYS = [
        ['International New Year\'s Day', '01-01', '01-01'],
        ['Victory over Genocide Day', '01-07', '01-07'],
        ['Khmer New Year', '04-14', '04-16'],
        ['International Labour Day', '05-01', '05-01'],
        ['King\'s Birthday', '05-14', '05-14'],
        ['Constitution Day', '09-24', '09-24'],
        ['Independence Day', '11-09', '11-09'],
    ];

    public const STREETS = ['Norodom Blvd', 'Monivong Blvd', 'Sihanouk Blvd', 'Russian Blvd', 'Mao Tse Toung Blvd', 'Street 63', 'Street 271', 'Street 2004'];

    private const PHONE_PREFIXES = ['012', '015', '016', '017', '069', '070', '077', '078', '085', '092', '093', '096', '097', '098'];

    private static ?Generator $faker = null;

    /** e-mail base => times used, for unique addresses */
    private static array $emails = [];

    /** The seeded Faker every demo seeder shares. */
    public static function faker(): Generator
    {
        if (! self::$faker) {
            self::$faker = fake();
            self::$faker->seed((int) config('school.demo.seed', 2026));
        }

        return self::$faker;
    }

    /** Start again from the configured seed (used before each full demo run). */
    public static function reset(): void
    {
        self::$faker = null;
        self::$emails = [];
    }

    public static function gender(): Gender
    {
        return self::faker()->boolean() ? Gender::Male : Gender::Female;
    }

    /** @return array{0: string, 1: string} [given name, family name] */
    public static function name(Gender $gender, ?string $family = null): array
    {
        $given = self::faker()->randomElement($gender === Gender::Female ? self::FEMALE_NAMES : self::MALE_NAMES);

        return [$given, $family ?? self::faker()->randomElement(self::FAMILY_NAMES)];
    }

    public static function phone(): string
    {
        return self::faker()->randomElement(self::PHONE_PREFIXES).self::faker()->numerify('######');
    }

    /** A unique, readable demo e-mail such as "dara.sok.12@school.test". */
    public static function email(string $given, string $family, string $domain = 'school.test'): string
    {
        $base = Str::slug($given.'.'.$family, '.');
        $key = $base.'@'.$domain;
        self::$emails[$key] = (self::$emails[$key] ?? 0) + 1;

        return $base.(self::$emails[$key] > 1 ? '.'.self::$emails[$key] : '').'@'.$domain;
    }

    /** @return array{city: string, state: string, zip: string, address: string} */
    public static function address(): array
    {
        $place = self::faker()->randomElement(self::PLACES);

        return $place + ['address' => 'No. '.self::faker()->numberBetween(1, 250).', '.self::faker()->randomElement(self::STREETS)];
    }

    /** Monday-Friday school days from $months months ago up to today, oldest first, skipping public holidays. */
    public static function schoolDays(int $months): array
    {
        $holiday = fn (Carbon $day) => collect(self::HOLIDAYS)->contains(fn (array $h) => $day->format('m-d') >= $h[1] && $day->format('m-d') <= $h[2]);
        $days = [];
        for ($day = today()->subMonthsNoOverflow($months)->startOfDay(); $day->lte(today()); $day = $day->copy()->addDay()) {
            if ($day->isWeekday() && ! $holiday($day)) {
                $days[] = $day->toDateString();
            }
        }

        return $days;
    }

    /** The first day of each of the last $months months plus this one, oldest first. */
    public static function months(int $months): array
    {
        return collect(range($months, 0))->map(fn (int $ago) => today()->startOfMonth()->subMonthsNoOverflow($ago))->all();
    }

    /**
     * Shuffle with the seeded Faker. (Collection::shuffle($seed) re-seeds PHP's shared random
     * generator afterwards, which would make every later Faker value unpredictable.)
     */
    public static function shuffle(iterable $items): Collection
    {
        return collect(self::faker()->shuffleArray(collect($items)->values()->all()));
    }

    /** Pick a key from ['value' => weight, ...]. */
    public static function weighted(array $weights): string
    {
        $roll = self::faker()->numberBetween(1, array_sum($weights));
        foreach ($weights as $value => $weight) {
            if (($roll -= $weight) <= 0) {
                return (string) $value;
            }
        }

        return (string) array_key_first($weights);
    }

    /** A date between two dates (inclusive). */
    public static function dateBetween(Carbon|string $from, Carbon|string $to): Carbon
    {
        return Carbon::instance(self::faker()->dateTimeBetween($from, $to))->startOfDay();
    }
}
