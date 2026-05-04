<?php

namespace App\Tests\Validator;

use App\Service\GeminiSafetyClient;
use App\Validator\NoHateSpeech;
use App\Validator\NoHateSpeechValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class NoHateSpeechValidatorTest extends ConstraintValidatorTestCase
{
    private GeminiSafetyClient&MockObject $geminiSafetyClient;
    private LoggerInterface&MockObject $logger;

    protected function createValidator(): NoHateSpeechValidator
    {
        $this->geminiSafetyClient = $this->createMock(GeminiSafetyClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        return new NoHateSpeechValidator($this->geminiSafetyClient, $this->logger);
    }

    public function testAddsViolationWhenHateSpeechDetected(): void
    {
        $constraint = new NoHateSpeech();

        $this->geminiSafetyClient
            ->expects(self::once())
            ->method('containsHateSpeech')
            ->with('insulting text')
            ->willReturn(true);

        $this->logger->expects(self::never())->method('warning');

        $this->validator->validate('insulting text', $constraint);

        $this
            ->buildViolation($constraint->message)
            ->assertRaised();
    }

    public function testNoViolationWhenTextIsClean(): void
    {
        $constraint = new NoHateSpeech();

        $this->geminiSafetyClient
            ->expects(self::once())
            ->method('containsHateSpeech')
            ->with('neutral feedback')
            ->willReturn(false);

        $this->logger->expects(self::never())->method('warning');

        $this->validator->validate('neutral feedback', $constraint);

        $this->assertNoViolation();
    }

    public function testAddsViolationForLocalBannedWordWithoutCallingApi(): void
    {
        $constraint = new NoHateSpeech();

        $this->geminiSafetyClient
            ->expects(self::never())
            ->method('containsHateSpeech');

        $this->logger->expects(self::never())->method('warning');

        $this->validator->validate('Ce candidat est un salaud.', $constraint);

        $this
            ->buildViolation($constraint->message)
            ->assertRaised();
    }

    public function testApiFailureDoesNotBlockValidationAndLogsWarning(): void
    {
        $constraint = new NoHateSpeech();

        $this->geminiSafetyClient
            ->expects(self::once())
            ->method('containsHateSpeech')
            ->willThrowException(new \RuntimeException('timeout'));

        $this->logger
            ->expects(self::once())
            ->method('warning')
            ->with(
                'Gemini moderation failed, validation continued.',
                self::arrayHasKey('exception')
            );

        $this->validator->validate('any content', $constraint);

        $this->assertNoViolation();
    }
}
