<?php

namespace App\Validator;

use App\Service\GeminiSafetyClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoHateSpeechValidator extends ConstraintValidator
{
    private const LOCAL_BANNED_WORDS_PATTERN = '/\b(?:salaud(?:s)?|salope(?:s)?|bete(?:s)?|stupid(?:s)?|maudite(?:s)?|sale(?:s)?)\b/ui';

    public function __construct(
        private readonly GeminiSafetyClient $geminiSafetyClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoHateSpeech) {
            throw new UnexpectedTypeException($constraint, NoHateSpeech::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            return;
        }

        if (preg_match(self::LOCAL_BANNED_WORDS_PATTERN, $value) === 1) {
            $this->context->buildViolation($constraint->message)->addViolation();
            return;
        }

        try {
            if ($this->geminiSafetyClient->containsHateSpeech($value)) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        } catch (\Throwable $exception) {
            // Fail-open policy: keep validation pass if moderation API fails.
            $this->logger->warning('Gemini moderation failed, validation continued.', [
                'exception' => $exception,
            ]);
        }
    }
}
