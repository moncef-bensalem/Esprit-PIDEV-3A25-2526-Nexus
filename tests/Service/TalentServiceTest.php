<?php

namespace App\Tests\Service;

use App\Entity\Talent;
use App\Service\TalentService;
use PHPUnit\Framework\TestCase;

class TalentServiceTest extends TestCase
{
    private TalentService $talentService;

    protected function setUp(): void
    {
        $this->talentService = new TalentService();
    }

    public function testIsSenior(): void
    {
        $talent = new Talent();
        
        $talent->setAnneesExperience(6);
        $this->assertTrue($this->talentService->isSenior($talent));

        $talent->setAnneesExperience(5);
        $this->assertFalse($this->talentService->isSenior($talent));
    }

    public function testCalculateBonus(): void
    {
        $talent = new Talent();

        $talent->setAnneesExperience(2);
        $this->assertEquals(1000.0, $this->talentService->calculateBonus($talent));

        $talent->setAnneesExperience(12);
        $this->assertEquals(5000.0, $this->talentService->calculateBonus($talent));
    }
}
