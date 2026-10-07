<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;

/**
 * Tabular (arithmetic) Hijri conversion; may differ from Umm al-Qura by a day.
 */
final class HijriDate
{
    private const DAY_NAMES = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

    private const HIJRI_MONTHS = [
        'محرم', 'صفر', 'ربيع الأول', 'ربيع الآخر', 'جمادى الأولى', 'جمادى الآخرة',
        'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة',
    ];

    private const GREGORIAN_MONTHS = [
        'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
        'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر',
    ];

    public function __construct(
        public readonly int $year,
        public readonly int $month,
        public readonly int $day,
        public readonly Carbon $gregorian,
    ) {}

    public static function fromGregorian(DateTimeInterface|string $date): self
    {
        $gregorian = Carbon::parse($date)->startOfDay();
        [$year, $month, $day] = [$gregorian->year, $gregorian->month, $gregorian->day];

        $a = intdiv(14 - $month, 12);
        $y = $year + 4800 - $a;
        $m = $month + 12 * $a - 3;
        $jd = $day + intdiv(153 * $m + 2, 5) + 365 * $y + intdiv($y, 4) - intdiv($y, 100) + intdiv($y, 400) - 32045;

        $l = $jd - 1948440 + 10632;
        $n = intdiv($l - 1, 10631);
        $l = $l - 10631 * $n + 354;
        $j = intdiv(10985 - $l, 5316) * intdiv(50 * $l, 17719) + intdiv($l, 5670) * intdiv(43 * $l, 15238);
        $l = $l - intdiv(30 - $j, 15) * intdiv(17719 * $j, 50) - intdiv($j, 16) * intdiv(15238 * $j, 43) + 29;

        $hijriMonth = intdiv(24 * $l, 709);
        $hijriDay = $l - intdiv(709 * $hijriMonth, 24);
        $hijriYear = 30 * $n + $j - 30;

        return new self($hijriYear, $hijriMonth, $hijriDay, $gregorian);
    }

    public function dayName(): string
    {
        return self::DAY_NAMES[$this->gregorian->dayOfWeek];
    }

    public function monthName(): string
    {
        return self::HIJRI_MONTHS[$this->month - 1];
    }

    public function gregorianMonthName(): string
    {
        return self::GREGORIAN_MONTHS[$this->gregorian->month - 1];
    }

    public function numeric(): string
    {
        return sprintf('%d/%02d/%02d', $this->year, $this->month, $this->day);
    }

    public function gregorianNumeric(): string
    {
        return $this->gregorian->format('Y/m/d');
    }

    public function long(): string
    {
        return "{$this->dayName()} {$this->day} {$this->monthName()} {$this->year} هـ";
    }
}
