<?php

namespace App\Tests\Service;

use App\Service\QrCodeService;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Writer\Result\ResultInterface;
use PHPUnit\Framework\TestCase;

class QrCodeServiceTest extends TestCase
{
    public function testGeneratePng(): void
    {
        $builder = $this->createMock(BuilderInterface::class);
        $result = $this->createMock(ResultInterface::class);

        $builder->method('writer')->willReturnSelf();
        $builder->method('data')->willReturnSelf();
        $builder->method('size')->willReturnSelf();
        $builder->method('margin')->willReturnSelf();
        $builder->method('errorCorrectionLevel')->willReturnSelf();
        $builder->method('backgroundColor')->willReturnSelf();
        $builder->method('foregroundColor')->willReturnSelf();
        $builder->method('build')->willReturn($result);

        $result->method('getString')->willReturn('dummy_qr_content');

        $qrCodeService = new QrCodeService($builder);
        $output = $qrCodeService->generatePng('https://nexus.com');

        $this->assertEquals('dummy_qr_content', $output);
    }
}
