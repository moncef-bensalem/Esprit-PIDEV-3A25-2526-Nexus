
# Nexus — Plateforme RH & Gestion des Talents

> Application web complète de recrutement, gestion des talents et évaluation des candidats, construite avec **Symfony 6.4** et enrichie par des intégrations IA.

---

## Table des matières

1. [Description du projet](#description-du-projet)
2. [Fonctionnalités principales](#fonctionnalités-principales)
3. [Architecture technique](#architecture-technique)
4. [Entités & modèle de données](#entités--modèle-de-données)
5. [Rôles & sécurité](#rôles--sécurité)
6. [Intégrations externes](#intégrations-externes)
7. [Stack technique](#stack-technique)
8. [Installation & démarrage](#installation--démarrage)
9. [Structure du projet](#structure-du-projet)
10. [Mots-clés & topics](#mots-clés--topics)

---

## Description du projet

**Nexus** est une plateforme RH full-stack destinée aux équipes de recrutement et de gestion des ressources humaines. Elle couvre l'intégralité du cycle de vie d'un recrutement — de la publication d'une offre d'emploi jusqu'à la décision finale sur un candidat — tout en gérant en parallèle les talents internes de l'entreprise.

L'application se distingue par :
- une **analyse automatique des CV** par intelligence artificielle (scoring multi-critères)
- un **système d'évaluation structuré** avec scoring par compétence (0–20) et décision préliminaire
- une **authentification multi-modale** (formulaire, Google OAuth, reconnaissance faciale, 2FA)
- des **notifications multi-canal** (email, SMS Twilio, push OneSignal)
- une **API REST sécurisée par JWT** pour les intégrations tierces

---

## Fonctionnalités principales

### Recrutement
- Création et gestion des offres d'emploi (statuts : Brouillon / Publiée / Fermée)
- Page publique des offres avec formulaire de candidature
- Analyse automatique des CV (PDF/DOCX) par IA : score technique, expérience, formation, langues, soft skills
- Vue Kanban des candidatures par état (Reçu → En entretien → Offre faite → Rejeté)
- Score de matching candidat/offre

### Évaluation des candidats
- Création d'évaluations par les recruteurs (`ROLE_RH`)
- Scoring par critère de compétence (note 0–20, appréciation spécifique)
- Décision préliminaire : `FAVORABLE`, `DEFAVORABLE`, `A_REVOIR`
- Dashboard interactif avec filtres AJAX, calendrier et graphiques (Chart.js)
- Export PDF des évaluations (Dompdf)
- Envoi de la décision par email au candidat
- Détection automatique de discours haineux dans les commentaires (Gemini AI)

### Gestion des talents internes
- Profils de talents (poste, département, expérience, niveau d'études)
- Compétences associées avec niveau de maîtrise et années de pratique
- Profil public avec QR code partageable
- Vivier de talents (états : ACTIF, INACTIF, etc.)

### Planification & suivi
- Calendrier des entretiens et événements (présentiel / visio)
- Génération d'invitations iCal
- Reviews et feedbacks sur les planifications (rating 1–5)

### Authentification & sécurité
- Login classique avec rate limiting (5 tentatives / 15 min)
- Connexion Google OAuth2
- Reconnaissance faciale (face-api.js)
- Authentification à deux facteurs (2FA)
- Réinitialisation de mot de passe par email

### Administration
- Dashboard admin avec statistiques globales
- Gestion des utilisateurs (CRUD, recherche)
- Export Excel des utilisateurs (PhpSpreadsheet)
- Notifications admin en temps réel
- Audit trail des événements utilisateur

### Fonctionnalités transverses
- Chatbot IA (Groq) pour les questions RH
- Traduction automatique (DeepL / MyMemory)
- Estimation de salaire (Adzuna API)
- Notifications push (OneSignal)
- SMS (Twilio)

---

## Architecture technique

L'application suit le pattern **MVC** standard de Symfony avec les couches suivantes :

```
Requête HTTP
    │
    ▼
Controller (src/Controller/)
    │  ← Voters (autorisation granulaire)
    ▼
Service (src/Service/)
    │  ← Validation (Constraints + Validators custom)
    ▼
Repository (src/Repository/)
    │
    ▼
Entity / Doctrine ORM (src/Entity/)
    │
    ▼
Base de données MySQL
```

**Patterns notables :**
- **Voters Symfony** pour le contrôle d'accès granulaire (ex. `EvaluationVoter`)
- **Form Types** Symfony pour tous les formulaires
- **Services** isolés pour la logique métier (mailer, stats, IA, notifications)
- **Lifecycle Callbacks** Doctrine (`@PrePersist`) pour les timestamps automatiques
- **Turbo + Stimulus** pour une UI réactive sans SPA complète
- **JWT** pour l'API REST stateless

---

## Entités & modèle de données

| Entité | Table | Description |
|---|---|---|
| `User` | `user` | Utilisateur système (recruteur, admin, candidat) |
| `Candidat` | `candidat` | Candidat externe avec scores IA |
| `OffreEmploi` | `offre_emploi` | Offre d'emploi publiée |
| `Candidature` | `candidature` | Candidature d'un candidat à une offre |
| `Evaluation` | `evaluation` | Évaluation d'un candidat par un recruteur |
| `ScoreCompetence` | `score_competence` | Score d'une compétence dans une évaluation |
| `Talent` | `talent` | Employé interne |
| `TalentCompetence` | `talent_competence` | Compétence d'un talent avec niveau |
| `ProfilTalent` | `profil_talent` | Profil public d'un talent |
| `Competence` | `competence` | Référentiel de compétences |
| `Planification` | `planification` | Événement calendaire (entretien, réunion) |
| `Review` | `review` | Feedback sur une planification |
| `Departement` | `departement` | Département de l'entreprise |
| `TypeEntretien` | `type_entretien` | Type d'entretien (téléphone, visio, etc.) |
| `AdminNotification` | `admin_notification` | Notifications pour les admins |
| `LoginAttempt` | `login_attempt` | Suivi des tentatives de connexion |

### Relations clés

```
User ──< Evaluation (as candidat)
User ──< Evaluation (as recruteur)
Evaluation ──< ScoreCompetence
Candidat ──< Candidature >── OffreEmploi
Talent ──< TalentCompetence >── Competence
User ──< Planification
Planification ──< Review
User ──  ProfilTalent
```

---

## Rôles & sécurité

| Rôle | Accès |
|---|---|
| `ROLE_ADMIN` | Accès complet + hérite de ROLE_RH et ROLE_CANDIDATE |
| `ROLE_RH` | Évaluations, offres, candidatures, talents, admin |
| `ROLE_CANDIDATE` | Dashboard candidat, planifications, reviews |
| Public | Offres d'emploi, formulaire de candidature, profils talents publics |

**Mécanismes de sécurité :**
- Sessions + CSRF pour l'interface web
- JWT (RS256) pour l'API REST
- Rate limiting sur le login (5 tentatives / 15 min)
- Vérification email à l'inscription
- Détection de hate speech via Gemini AI sur les champs texte libres
- `UserChecker` personnalisé (vérification compte actif/vérifié)

---

## Intégrations externes

| Service | Usage | Librairie |
|---|---|---|
| **Google OAuth** | Authentification sociale | `league/oauth2-google` |
| **Gemini API** | Détection hate speech, analyse CV | `guzzlehttp/guzzle` |
| **Groq API** | Chatbot IA, analyse CV | `guzzlehttp/guzzle` |
| **DeepL** | Traduction automatique | `guzzlehttp/guzzle` |
| **MyMemory** | Traduction de secours | `guzzlehttp/guzzle` |
| **Twilio** | Notifications SMS | `twilio/sdk` |
| **OneSignal** | Notifications push | JS SDK |
| **Adzuna** | Estimation de salaire | `guzzlehttp/guzzle` |
| **OpenWeatherMap** | Météo (dashboard) | `guzzlehttp/guzzle` |
| **Datamuse** | Vérification orthographe | JS fetch |
| **face-api.js** | Reconnaissance faciale | JS (public/) |

---

## Stack technique

### Backend
- **PHP 8.2+**
- **Symfony 6.4** (LTS)
- **Doctrine ORM 3.6** + Migrations
- **MySQL** (UTF-8mb4)
- **Lexik JWT Authentication** 2.18
- **KnpPaginator** + **Pagerfanta**
- **Dompdf** 3.1 (PDF)
- **PhpSpreadsheet** 5.6 (Excel)
- **smalot/pdfparser** 2.12 (parsing CV)
- **eluceo/ical** 2.14 (calendrier)
- **endroid/qr-code** 5.1 (QR codes)
- **SymfonyCasts Reset Password** + **Verify Email**
- **PHPStan** 2.1 (analyse statique)
- **PHPUnit** 10.5 (tests)

### Frontend
- **Twig** 3.x (templating)
- **Stimulus** 2.34 (contrôleurs JS)
- **Turbo** 2.34 (navigation AJAX)
- **Bootstrap** (CSS framework)
- **Chart.js** (graphiques)
- **face-api.js** (reconnaissance faciale)
- **Symfony Asset Mapper** (gestion des assets)

### Infrastructure
- **Docker** (compose.yaml + compose.override.yaml)
- **Symfony Messenger** (Doctrine transport)
- **Symfony Mailer** (SMTP / SendGrid / Gmail)
- **Monolog** (logging)

---

## Installation & démarrage

### Prérequis
- PHP 8.2+
- Composer
- Node.js (pour les assets)
- MySQL 8+
- Docker (optionnel)

### Installation

```bash
# Cloner le projet
git clone <repo-url>
cd nexus

# Installer les dépendances PHP
composer install

# Configurer l'environnement
cp .env .env.local
# Éditer .env.local : DATABASE_URL, MAILER_DSN, clés API...

# Créer la base de données et exécuter les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Générer les clés JWT
php bin/console lexik:jwt:generate-keypair

# Installer les assets
php bin/console importmap:install
php bin/console asset-map:compile

# Lancer le serveur de développement
symfony server:start
```

### Avec Docker

```bash
docker compose up -d
```

### Variables d'environnement clés

| Variable | Description |
|---|---|
| `DATABASE_URL` | DSN de connexion MySQL |
| `MAILER_DSN` | Configuration SMTP |
| `JWT_SECRET_KEY` | Chemin clé privée JWT |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` | OAuth Google |
| `GEMINI_API_KEY` | API Gemini (IA) |
| `GROQ_API_KEY` | API Groq (chatbot) |
| `TWILIO_*` | Credentials Twilio (SMS) |
| `ONESIGNAL_APP_ID` | OneSignal (push) |
| `DEEPL_API_KEY` | API DeepL (traduction) |

---

## Structure du projet

```
nexus/
├── assets/                     # Frontend (Stimulus, CSS, JS)
│   ├── controllers/            # Contrôleurs Stimulus
│   ├── styles/                 # CSS global
│   └── onesignal-init.js       # Init push notifications
├── config/
│   ├── packages/               # Configuration des bundles
│   └── security.yaml           # Authentification & autorisation
├── migrations/                 # Migrations Doctrine
├── public/
│   ├── models/face_api/        # Modèles face-api.js
│   └── uploads/                # CVs uploadés
├── src/
│   ├── Command/                # Commandes Symfony CLI
│   ├── Controller/             # Contrôleurs MVC
│   │   └── Admin/              # Contrôleurs admin
│   ├── Entity/                 # Entités Doctrine
│   ├── Form/                   # Form Types Symfony
│   ├── Repository/             # Repositories Doctrine
│   ├── Security/               # Voters, Authenticators, UserChecker
│   ├── Service/                # Services métier & intégrations
│   └── Validator/              # Contraintes de validation custom
├── templates/                  # Templates Twig
├── tests/                      # Tests PHPUnit
├── compose.yaml                # Docker Compose
└── composer.json               # Dépendances PHP
```

---

## Mots-clés & topics

### Topics GitHub suggérés
`symfony` `php` `recruitment` `hr-management` `talent-management` `doctrine-orm` `jwt-authentication` `oauth2` `face-recognition` `ai` `gemini-api` `groq` `twilio` `onesignal` `dompdf` `phpspreadsheet` `rest-api` `twig` `stimulus` `turbo`

### Mots-clés métier
- Recrutement, RH, Ressources Humaines
- Gestion des talents, vivier de talents
- Évaluation des candidats, scoring de compétences
- Offres d'emploi, candidatures, matching
- Planification d'entretiens, calendrier RH
- Décision préliminaire, feedback recruteur

### Mots-clés techniques
- Symfony 6.4, PHP 8.2, Doctrine ORM
- JWT, OAuth2, 2FA, reconnaissance faciale
- Intelligence artificielle, analyse de CV, NLP
- API REST, JSON, iCal
- PDF, Excel, QR Code
- Notifications push, SMS, email
- Docker, MySQL, Twig, Stimulus, Turbo

---

*Nexus Platform — Symfony 6.4 — PHP 8.2+*
