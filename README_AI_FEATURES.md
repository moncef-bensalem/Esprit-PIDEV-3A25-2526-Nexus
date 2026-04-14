# 🚀 Nexus Recruitment Platform - Advanced AI Features

Bienvenue dans la documentation des fonctionnalités avancées (Métiers) de la plateforme **Nexus**. Ce projet utilise des technologies de pointe pour automatiser et optimiser le processus de recrutement.

---

## 🧠 1. Analyse de CV par IA (Google Gemini 1.5 Flash)
Le cœur de Nexus repose sur une IA capable de "lire" et comprendre les dossiers de candidature.
- **Fonctionnement :** Dès qu'un candidat soumet son CV (PDF), le service `CvAnalysisService` extrait les données et les envoie à l'API **Google Gemini**.
- **Scoring en 5 Piliers :**
    - **Technique :** Évaluation des langages et outils.
    - **Expérience :** Analyse de la pertinence des années de pratique.
    - **Formation :** Vérification des diplômes requis.
    - **Langues :** Score basé sur la maîtrise linguistique.
    - **Soft Skills :** Évaluation comportementale extraite du texte.
- **Score de Matching :** Calcul d'un pourcentage global d'adéquation entre le candidat et l'offre d'emploi spécifique.

## 📊 2. Visualisation Dynamique (QuickChart API)
Pour rendre les scores IA digestes, Nexus intègre des **Radar Charts** (Spider Maps) professionnels.
- **API :** QuickChart.io (Service server-side conforme aux exigences académiques).
- **Aesthetic :** Design épuré, mode sombre, sans légendes encombrantes pour une lecture immédiate des points forts du candidat.

## 🗺️ 3. Cartographie Régionale (Leaflet & Mapbox)
Nexus adapte dynamiquement l'affichage géographique selon le contexte de l'offre.
- **Logic de Centrage :** La carte détecte la devise (`devise`) de l'offre :
    - **TND :** Centrage sur la Tunisie.
    - **EUR :** Centrage sur l'Europe (Paris).
    - **USD :** Centrage sur les USA (Washington DC).
- **Popups Interactifs :** Affiche le titre du poste et le département directement sur le marqueur.

## 📧 4. Notifications Automatisées (SendGrid API)
Une communication fluide entre Nexus et ses utilisateurs.
- **Technologie :** Intégration via Symfony Mailer et l'infrastructure **SendGrid**.
- **Localized Messages :** Emails entièrement traduits en **Français**, avec une mise en page HTML premium (Logo, couleurs de la marque).

## 📱 5. Alertes SMS en Temps Réel (Twilio API)
Nexus informe l'administrateur instantanément pour les profils exceptionnels.
- **Seuil de Déclenchement :** Une alerte est envoyée si le score de matching est **≥ 95%**.
- **Contenu :** SMS détaillé incluant le **Nom du candidat**, son **Score** et le **Titre du poste**.
- **Technologie :** SDK Twilio Cloud.

---

## 🛠️ Stack Technique & APIs
| Feature | Service Provider |
| :--- | :--- |
| **Artificial Intelligence** | Google Gemini 1.5 |
| **SMS Notifications** | Twilio SDK |
| **Email Infrastructure** | SendGrid / TurboSMTP |
| **Data Visualization** | QuickChart API |
| **Mapping** | Leaflet.js |

---

## 🚀 Installation & Activation
Pour activer ces fonctionnalités, assurez-vous que votre fichier `.env` contient les clés valides :
```env
# AI
GEMINI_API_KEY=xxx

# Messaging
TWILIO_SID=xxx
TWILIO_TOKEN=xxx
TWILIO_FROM_NUMBER=xxx
TWILIO_ADMIN_PHONE_NUMBER=xxx

# Email
SENDGRID_DSN=xxx
```

*Nexus - Redéfinir le recrutement par la technologie.*
