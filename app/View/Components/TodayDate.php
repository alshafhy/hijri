<?php

namespace App\View\Components;

use App\Support\HijriDate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\View\Component;

class TodayDate extends Component
{
    public function render(): View
    {
        $now = Carbon::now()->timezone((string) config('app.timezone'));
        $hijri = HijriDate::fromGregorian($now);

        if (app()->getLocale() === 'en') {
            $datePart = $now->format('l d F Y').' — '.$hijri->numeric().' AH';
        } else {
            $datePart = "{$hijri->dayName()} {$hijri->day} {$hijri->monthName()} {$hijri->year} هـ — {$now->day} {$hijri->gregorianMonthName()} {$now->year} م";
        }

        return view('components.today-date', [
            'datePart' => $datePart,
            'timePart' => $now->format('H:i:s'),
        ]);
    }
}
