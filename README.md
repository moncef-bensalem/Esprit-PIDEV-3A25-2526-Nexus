# Nexus Recruitment Management System (NRMS)

Bienvenue sur la plateforme de gestion du recrutement de **Nexus**. Ce projet est une solution complète de suivi des talents et des offres d'emploi, développée sous **Symfony 6.4**. Elle offre une séparation stricte entre l'espace d'administration (Recruteur) et le portail public (Candidats).

---

## 🌟 Fonctionnalités Clés

### 1. Tableau de Bord RH (Admin)
- **KPIs en Temps Réel** : Visualisez instantanément le nombre d'offres actives, le volume de candidatures en cours et le taux de conversion global.
- **Accès Rapides** : Navigation simplifiée vers les modules de gestion des entités.
- **Design Mode Sombre "Enterprise"** : Une interface moderne, sombre et épurée inspirée des standards professionnels (GitHub/Linear) utilisant exclusivement du CSS Vanilla.

### 2. Pipeline de Recrutement (Kanban)
- **Gestion Visuelle** : Suivez l'avancement de vos candidats via une vue Kanban structurée en 4 colonnes : `Reçu`, `En entretien`, `Offre faite`, `Rejeté`.
- **Flux de Travail Optimisé** : Glissez-déposez virtuellement vos talents d'une étape à l'autre pour une gestion fluide du pipeline.

### 3. Gestion des Entités (CRUDs)
- **Offres d'Emploi** : Création et édition complète avec gestion des dates de clôture, salaires, devises (TND, EUR, USD) et statuts (Brouillon, Publiée, Clôturée).
- **Candidatures** : Suivi détaillé de chaque postulant, incluant le score de matching et les notes internes des recruteurs.
- **Départements** : Organisation structurelle de l'entreprise.

### 4. Portail Public de Recrutement
- **Liste des Offres** : Un portail accessible aux candidats (`/jobs`) pour consulter les postes ouverts.
- **Candidature Simplifiée** : Formulaire de postulation rapide avec téléchargement obligatoire de CV (PDF ou DOCX).
- **Suivi des Candidatures (Tracker)** : Un espace (`/tracker`) permettant aux candidats de vérifier l'état de leur propre dossier.

---

## 🛡️ Sécurité et Intégrité des Données

- **Validation Serveur Strict** : Toutes les entrées sont rigoureusement vérifiées par Symfony (Assertions PHP) pour garantir l'intégrité de la base de données.
- **REGEX Avancées** : Protection contre l'injection de scripts et les formats de données invalides sur les noms et libellés.
- **Validation RFC Strict pour l'Email** : Utilisation de parseurs conformes aux standards officiels pour rejeter les emails frauduleux.
- **Contrôle de Clôture** : Système intelligent empêchant la création d'offres expirant dans le passé.

---

## 🛠️ Architecture Technique

- **Backend** : PHP 8.2+ / Symfony 6.4.
- **Base de Données** : MySQL (Laragon environment compatible).
- **Frontend** : Twig, CSS3 Vanilla (Custom logic), JavaScript (Minimalist & Efficient).
- **Icons & Typos** : FontAwesome 6, Google Fonts (Plus Jakarta Sans, Space Grotesk).

---

## 📦 Architecture des URLs

### Espace Public
- `/jobs` : Liste des offres d'emploi publiées.
- `/postuler/{id}` : Formulaire de candidature pour une offre spécifique.
- `/tracker` : Consultation du statut de candidature.

### Espace Administration
- `/admin/dashboard` : Vue d'ensemble et statistiques.
- `/admin/pipeline` : Tableau de bord Kanban.
- `/admin/offre-emploi` : Gestion du vivier d'offres.
- `/admin/candidatures` : Gestion des dossiers postulants.
- `/admin/departements` : Gestion de la structure organisationnelle.

---

## 🚀 Installation rapide

1. **Cloner le dépôt** :
   ```bash
   git clone [url_du_depot]
   ```
2. **Installer les dépendances** :
   ```bash
   composer install
   ```
3. **Configurer la base de données** dans votre fichier `.env.local`.
4. **Lancer le serveur de développement** :
   ```bash
   symfony serve
   ```

---

*Développé avec excellence pour l'écosystème Nexus.*
