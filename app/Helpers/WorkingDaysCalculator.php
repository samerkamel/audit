<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Working Days Calculator
 *
 * Calculates working days considering Middle East weekend (Friday/Saturday)
 * and optionally public holidays.
 */
class WorkingDaysCalculator
{
    /**
     * Weekend days for Middle East (Friday = 5, Saturday = 6)
     */
    protected array $weekendDays = [Carbon::FRIDAY, Carbon::SATURDAY];

    /**
     * Public holidays (can be loaded from database or config)
     */
    protected array $holidays = [];

    /**
     * Create a new WorkingDaysCalculator instance.
     *
     * @param array|null $weekendDays Custom weekend days (default: Friday, Saturday)
     * @param array $holidays Array of holiday dates (Y-m-d format)
     */
    public function __construct(?array $weekendDays = null, array $holidays = [])
    {
        if ($weekendDays !== null) {
            $this->weekendDays = $weekendDays;
        }
        $this->holidays = $holidays;
    }

    /**
     * Set weekend days.
     *
     * @param array $days Array of Carbon day constants (e.g., [Carbon::FRIDAY, Carbon::SATURDAY])
     * @return self
     */
    public function setWeekendDays(array $days): self
    {
        $this->weekendDays = $days;
        return $this;
    }

    /**
     * Set holidays.
     *
     * @param array $holidays Array of holiday dates (Y-m-d format or Carbon instances)
     * @return self
     */
    public function setHolidays(array $holidays): self
    {
        $this->holidays = array_map(function ($date) {
            return $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        }, $holidays);
        return $this;
    }

    /**
     * Add a holiday.
     *
     * @param string|Carbon $date Holiday date
     * @return self
     */
    public function addHoliday($date): self
    {
        $this->holidays[] = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        return $this;
    }

    /**
     * Check if a given date is a working day.
     *
     * @param string|Carbon $date Date to check
     * @return bool
     */
    public function isWorkingDay($date): bool
    {
        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        // Check if it's a weekend day
        if (in_array($carbon->dayOfWeek, $this->weekendDays)) {
            return false;
        }

        // Check if it's a holiday
        if (in_array($carbon->format('Y-m-d'), $this->holidays)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a given date is a weekend.
     *
     * @param string|Carbon $date Date to check
     * @return bool
     */
    public function isWeekend($date): bool
    {
        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        return in_array($carbon->dayOfWeek, $this->weekendDays);
    }

    /**
     * Check if a given date is a holiday.
     *
     * @param string|Carbon $date Date to check
     * @return bool
     */
    public function isHoliday($date): bool
    {
        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        return in_array($carbon->format('Y-m-d'), $this->holidays);
    }

    /**
     * Add working days to a date.
     *
     * @param string|Carbon $date Starting date
     * @param int $days Number of working days to add
     * @return Carbon
     */
    public function addWorkingDays($date, int $days): Carbon
    {
        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        if ($days < 0) {
            return $this->subtractWorkingDays($carbon, abs($days));
        }

        $addedDays = 0;
        while ($addedDays < $days) {
            $carbon->addDay();
            if ($this->isWorkingDay($carbon)) {
                $addedDays++;
            }
        }

        return $carbon;
    }

    /**
     * Subtract working days from a date.
     *
     * @param string|Carbon $date Starting date
     * @param int $days Number of working days to subtract
     * @return Carbon
     */
    public function subtractWorkingDays($date, int $days): Carbon
    {
        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        $subtractedDays = 0;
        while ($subtractedDays < $days) {
            $carbon->subDay();
            if ($this->isWorkingDay($carbon)) {
                $subtractedDays++;
            }
        }

        return $carbon;
    }

    /**
     * Calculate the difference in working days between two dates.
     *
     * @param string|Carbon $from Starting date
     * @param string|Carbon $to Ending date
     * @return int Number of working days (can be negative)
     */
    public function diffInWorkingDays($from, $to): int
    {
        $fromCarbon = $from instanceof Carbon ? $from->copy()->startOfDay() : Carbon::parse($from)->startOfDay();
        $toCarbon = $to instanceof Carbon ? $to->copy()->startOfDay() : Carbon::parse($to)->startOfDay();

        $negative = false;
        if ($toCarbon->lt($fromCarbon)) {
            $negative = true;
            $temp = $fromCarbon;
            $fromCarbon = $toCarbon;
            $toCarbon = $temp;
        }

        $workingDays = 0;
        $current = $fromCarbon->copy();

        while ($current->lt($toCarbon)) {
            $current->addDay();
            if ($this->isWorkingDay($current)) {
                $workingDays++;
            }
        }

        return $negative ? -$workingDays : $workingDays;
    }

    /**
     * Get the next working day from a given date.
     *
     * @param string|Carbon $date Starting date
     * @return Carbon
     */
    public function getNextWorkingDay($date): Carbon
    {
        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        while (!$this->isWorkingDay($carbon)) {
            $carbon->addDay();
        }

        return $carbon;
    }

    /**
     * Get the previous working day from a given date.
     *
     * @param string|Carbon $date Starting date
     * @return Carbon
     */
    public function getPreviousWorkingDay($date): Carbon
    {
        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        while (!$this->isWorkingDay($carbon)) {
            $carbon->subDay();
        }

        return $carbon;
    }

    /**
     * Count working days in a date range (inclusive).
     *
     * @param string|Carbon $from Starting date
     * @param string|Carbon $to Ending date
     * @return int
     */
    public function countWorkingDays($from, $to): int
    {
        $fromCarbon = $from instanceof Carbon ? $from->copy()->startOfDay() : Carbon::parse($from)->startOfDay();
        $toCarbon = $to instanceof Carbon ? $to->copy()->startOfDay() : Carbon::parse($to)->startOfDay();

        if ($toCarbon->lt($fromCarbon)) {
            $temp = $fromCarbon;
            $fromCarbon = $toCarbon;
            $toCarbon = $temp;
        }

        $workingDays = 0;
        $current = $fromCarbon->copy();

        while ($current->lte($toCarbon)) {
            if ($this->isWorkingDay($current)) {
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }

    /**
     * Get all working days in a date range.
     *
     * @param string|Carbon $from Starting date
     * @param string|Carbon $to Ending date
     * @return array Array of Carbon dates
     */
    public function getWorkingDaysInRange($from, $to): array
    {
        $fromCarbon = $from instanceof Carbon ? $from->copy()->startOfDay() : Carbon::parse($from)->startOfDay();
        $toCarbon = $to instanceof Carbon ? $to->copy()->startOfDay() : Carbon::parse($to)->startOfDay();

        if ($toCarbon->lt($fromCarbon)) {
            $temp = $fromCarbon;
            $fromCarbon = $toCarbon;
            $toCarbon = $temp;
        }

        $workingDays = [];
        $current = $fromCarbon->copy();

        while ($current->lte($toCarbon)) {
            if ($this->isWorkingDay($current)) {
                $workingDays[] = $current->copy();
            }
            $current->addDay();
        }

        return $workingDays;
    }

    /**
     * Create a new instance with default Middle East settings.
     *
     * @return static
     */
    public static function middleEast(): static
    {
        return new static([Carbon::FRIDAY, Carbon::SATURDAY]);
    }

    /**
     * Create a new instance with Western weekend settings (Saturday/Sunday).
     *
     * @return static
     */
    public static function western(): static
    {
        return new static([Carbon::SATURDAY, Carbon::SUNDAY]);
    }
}
