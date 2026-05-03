<?php

namespace App\Service;

use App\Entity\Planification;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Enum\EventStatus;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;

class ICalService
{
    /**
     * Génère un fichier iCal à partir d'une planification et retourne
     * son contenu en string, prêt à être envoyé en téléchargement.
     */
    public function generateFromPlanification(Planification $planification): string
    {
        // ── 1. Créer l'événement avec un identifiant unique ──────────────
        $event = new Event(
            new UniqueIdentifier(
                sprintf('planification-%d@nexus-platform', $planification->getIdEvent())
            )
        );

        // ── 2. Titre (SUMMARY) ───────────────────────────────────────────
        $event->setSummary(ucfirst((string) $planification->getTypeEvent()));

        // ── 3. Description (si présente) ─────────────────────────────────
        if ($planification->getDescription()) {
            $event->setDescription($planification->getDescription());
        }

        // ── 4. Localisation (si présente) ────────────────────────────────
        if ($planification->getLocalisation()) {
            $event->setLocation(new Location($planification->getLocalisation()));
        }

        // ── 5. Dates de début et de fin (DTSTART / DTEND) ────────────────
        $date      = $planification->getDate();
        $heureDebut = $planification->getHeureDebut();
        $heureFin   = $planification->getHeureFin();

        if ($date !== null) {
            // Combiner la date et l'heure en un seul DateTime PHP
            $dtStart = \DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $date->format('Y-m-d') . ' ' . ($heureDebut ? $heureDebut->format('H:i:s') : '00:00:00')
            );
            $dtEnd = \DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $date->format('Y-m-d') . ' ' . ($heureFin ? $heureFin->format('H:i:s') : '01:00:00')
            );

            $event->setOccurrence(
                new TimeSpan(
                    new DateTime($dtStart ?: new \DateTime('now'), false),
                    new DateTime($dtEnd   ?: new \DateTime('now'), false)
                )
            );
        }

        // ── 6. Statut : CONFIRMED si "confirmé", sinon TENTATIVE ─────────
        $event->setStatus(
            $planification->getStatut() === 'confirmé'
                ? EventStatus::CONFIRMED()
                : EventStatus::TENTATIVE()
        );

        // ── 7. URL du lien meeting (si présent) ──────────────────────────
        if ($planification->getLienMeeting()) {
            $event->setUrl(new Uri($planification->getLienMeeting()));
        }

        // ── 8. Organisateur : email du responsable (si présent) ──────────
        if ($planification->getUser()?->getEmail()) {
            $email = $planification->getUser()->getEmail();
            $event->setOrganizer(
                new Organizer(
                    new EmailAddress($email),
                    $email
                )
            );
        }

        // ── 9. Assembler le calendrier et générer le contenu iCal ────────
        $calendar = new Calendar([$event]);
        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);

        return (string) $calendarComponent;
    }
}
