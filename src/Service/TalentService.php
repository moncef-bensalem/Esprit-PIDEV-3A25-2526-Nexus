<?php

namespace App\Service;

use App\Entity\Talent;

class TalentService
{
    /**
     * Business Rule 1: A talent is senior if they have more than 5 years of experience.
     */
    public function isSenior(Talent $talent): bool
    {
        return $talent->getAnneesExperience() > 5;
    }

    /**
     * Business Rule 2: Calculate a performance bonus based on experience.
     * Bonus = experience * 500, capped at 5000.
     */
    public function calculateBonus(Talent $talent): float
    {
        $experience = $talent->getAnneesExperience() ?? 0;
        $bonus = $experience * 500;

        return (float) min($bonus, 5000);
    }
}
