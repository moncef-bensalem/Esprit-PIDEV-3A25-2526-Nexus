<?php

namespace App\Service;

use Twilio\Rest\Client;
use Psr\Log\LoggerInterface;

class SmsNotificationService
{
    private string $sid;
    private string $token;
    private string $fromNumber;
    private string $adminNumber;
    private LoggerInterface $logger;

    public function __construct(
        string $twilioSid,
        string $twilioToken,
        string $twilioFromNumber,
        string $adminPhoneNumber,
        LoggerInterface $logger
    ) {
        $this->sid = $twilioSid;
        $this->token = $twilioToken;
        $this->fromNumber = $twilioFromNumber;
        $this->adminNumber = $adminPhoneNumber;
        $this->logger = $logger;
    }

    public function sendAdminHighToScoreAlert(string $candidateName, float $score, string $jobTitle): void
    {
        try {
            // Log for debugging
            $this->logger->info("Tentative d'envoi SMS Twilio", [
                'from' => $this->fromNumber,
                'to' => $this->adminNumber,
                'job' => $jobTitle
            ]);

            $twilio = new Client($this->sid, $this->token);
            
            $body = "Nexus Admin : Profil TOP (" . $score . "%) pour le poste '" . $jobTitle . "'. Candidat : " . $candidateName;

            $message = $twilio->messages->create(
                $this->adminNumber, // to
                [
                    "from" => $this->fromNumber,
                    "body" => $body
                ]
            );

            $this->logger->info("SMS envoyé avec succès. SID: " . $message->sid);
        } catch (\Exception $e) {
            $this->logger->error("ÉCHEC Twilio : " . $e->getMessage());
        }
    }
}
