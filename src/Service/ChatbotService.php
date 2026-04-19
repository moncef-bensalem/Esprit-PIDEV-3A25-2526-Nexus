<?php

namespace App\Service;

use App\Entity\Talent;
use Doctrine\ORM\EntityManagerInterface;

class ChatbotService
{
    public function __construct(
        private EntityManagerInterface $em,
        private GroqService $groq
    ) {}

    /**
     * Analyse la question de l'utilisateur et retourne la réponse appropriée.
     */
    public function handleMessage(string $userMessage): string
    {
        $msg = mb_strtolower(trim($userMessage));

        // ── Intention 1 : Liste des postes disponibles ─────────────────────────
        if ($this->matchesPostesDisponibles($msg)) {
            return $this->getPostesDisponibles();
        }

        // ── Intention 2 : C'est quoi le poste de [NomPoste] ? ─────────────────
        if ($poste = $this->extractPosteDescription($msg)) {
            return $this->describePoste($poste);
        }

        // ── Intention 3 : Quel salaire pour un [Métier] en Tunisie ? ──────────
        if ($metier = $this->extractSalaire($msg)) {
            return $this->getSalaire($metier);
        }

        // ── Intention 4 : Quelles compétences d'un [Métier] ? ─────────────────
        if ($metier = $this->extractCompetences($msg)) {
            return $this->getCompetences($metier);
        }

        // ── Fallback : question générale envoyée à Groq ───────────────────────
        return $this->generalQuestion($userMessage);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Détection / extraction
    // ──────────────────────────────────────────────────────────────────────────

    private function matchesPostesDisponibles(string $msg): bool
    {
        $patterns = [
            'postes disponibles',
            'postes exist',
            'quels postes',
            'liste des postes',
            'les postes',
            'quels sont les postes',
            'postes dans la base',
        ];
        foreach ($patterns as $p) {
            if (str_contains($msg, $p)) {
                return true;
            }
        }
        return false;
    }

    private function extractPosteDescription(string $msg): ?string
    {
        // "c'est quoi le poste de [X]", "qu'est-ce que le poste [X]", "describe le poste [X]"
        $patterns = [
            "/(?:c(?:'|')est quoi|description|décris|c'est quoi|qu[e']?est[-\s]ce que|what is)\s+(?:le\s+)?poste(?:\s+de)?\s+(.+)/u",
            "/poste\s+(?:de\s+)?[«\"']?(.+?)[«\"']?\s*[?!]?$/u",
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $msg, $m)) {
                return ucfirst(trim($m[1], " ?!\"'«»"));
            }
        }
        return null;
    }

    private function extractSalaire(string $msg): ?string
    {
        // "quel salaire pour un [X]", "salaire d'un [X]", "combien gagne un [X]"
        $patterns = [
            "/(?:quel(?:le)?\s+salaire|salaire)\s+(?:pour\s+)?(?:un|une|d(?:'|')un|d(?:'|')une)?\s*(.+?)(?:\s+en\s+tunisie)?[?!]?$/u",
            "/combien\s+(?:gagne|coûte|vaut)\s+(?:un|une)?\s*(.+?)(?:\s+en\s+tunisie)?[?!]?$/u",
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $msg, $m)) {
                return ucfirst(trim($m[1], " ?!\"'"));
            }
        }
        return null;
    }

    private function extractCompetences(string $msg): ?string
    {
        // "quelles compétences pour [X]", "compétences d'un [X]", "skills d'un [X]"
        $patterns = [
            "/(?:quelles?\s+compétences?|compétences?\s+(?:d(?:'|')un|pour(?:\s+un)?|requises?\s+(?:pour)?))\s*(.+?)[?!]?$/u",
            "/(?:skills?|aptitudes?)\s+(?:d(?:'|')un|pour(?:\s+un)?)\s*(.+?)[?!]?$/u",
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $msg, $m)) {
                return ucfirst(trim($m[1], " ?!\"'"));
            }
        }
        return null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Réponses concrètes
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Récupère la liste des postes uniques depuis la table Talent.
     */
    private function getPostesDisponibles(): string
    {
        $talents = $this->em->getRepository(Talent::class)->findAll();

        $postes = [];
        foreach ($talents as $talent) {
            $poste = trim($talent->getPoste());
            if ($poste && !in_array($poste, $postes, true)) {
                $postes[] = $poste;
            }
        }

        if (empty($postes)) {
            return "Aucun poste n'est disponible dans la base de données pour le moment.";
        }

        sort($postes);
        $liste = implode("\n- ", $postes);
        return "Voici les postes disponibles dans notre base de données :\n\n- " . $liste;
    }

    /**
     * Demande à Groq de décrire un poste donné.
     */
    private function describePoste(string $poste): string
    {
        // Vérifier si ce poste existe dans la BD
        $talents = $this->em->getRepository(Talent::class)->findAll();
        $postesDB = array_map(fn($t) => mb_strtolower(trim($t->getPoste())), $talents);

        $exists = in_array(mb_strtolower($poste), $postesDB, true);
        $context = $exists
            ? "Ce poste existe dans notre base de données."
            : "Ce poste n'est pas dans notre base de données, mais je vais quand même le décrire.";

        $messages = [
            [
                'role'    => 'system',
                'content' => "Tu es un assistant RH expert. Réponds en français. " . $context,
            ],
            [
                'role'    => 'user',
                'content' => "Donne-moi une description concise du poste : « {$poste} ». "
                           . "Décris les responsabilités principales, le profil recherché et les qualités requises. "
                           . "Sois précis et professionnel. Maximum 200 mots.",
            ],
        ];

        return $this->groq->chat($messages);
    }

    /**
     * Demande à Groq une approximation de salaire en Tunisie.
     */
    private function getSalaire(string $metier): string
    {
        $messages = [
            [
                'role'    => 'system',
                'content' => "Tu es un expert en ressources humaines et en marché du travail tunisien. "
                           . "Réponds toujours en français. Donne des fourchettes salariales en Dinars Tunisiens (TND) par mois. "
                           . "Base tes estimations sur le marché actuel en Tunisie (2024-2025). "
                           . "Mentionne les niveaux (junior, mid-level, senior) si pertinent.",
            ],
            [
                'role'    => 'user',
                'content' => "Quelle est la fourchette salariale mensuelle en Tunisie pour le métier de « {$metier} » ? "
                           . "Donne une approximation en TND avec les niveaux d'expérience si possible.",
            ],
        ];

        return $this->groq->chat($messages);
    }

    /**
     * Demande à Groq les compétences requises pour un métier.
     */
    private function getCompetences(string $metier): string
    {
        $messages = [
            [
                'role'    => 'system',
                'content' => "Tu es un expert RH et en recrutement. Réponds toujours en français. "
                           . "Donne des réponses structurées et pertinentes.",
            ],
            [
                'role'    => 'user',
                'content' => "Quelles sont les compétences techniques et comportementales requises pour le métier de « {$metier} » ? "
                           . "Structure ta réponse avec : 1) Compétences techniques, 2) Compétences comportementales (soft skills). "
                           . "Sois concis, maximum 250 mots.",
            ],
        ];

        return $this->groq->chat($messages);
    }

    /**
     * Question générale : envoyée à Groq avec contexte RH.
     */
    private function generalQuestion(string $question): string
    {
        $messages = [
            [
                'role'    => 'system',
                'content' => "Tu es un assistant RH intelligent de la plateforme Nexus. "
                           . "Tu réponds aux questions liées aux postes, compétences, recrutement et carrières en Tunisie. "
                           . "Réponds toujours en français, de manière concise et professionnelle. "
                           . "Si la question n'est pas liée aux RH ou aux métiers, redirige poliment l'utilisateur.",
            ],
            [
                'role'    => 'user',
                'content' => $question,
            ],
        ];

        return $this->groq->chat($messages);
    }
}
