# Application de gestion d'une école de soutien scolaire

Application web pour une école de cours de soutien (primaire et collège) : site de présentation pour les parents, administration de l'école et espace des enseignants. Les cours se font en petits groupes ou en cours individuels.

L'application réunit trois espaces, en **français, arabe et anglais** (arabe en RTL complet) :

| Espace | Adresse | Pour qui |
|---|---|---|
| Site public | `/`, `/fr`, `/ar`, `/en` | Les parents : présentation de l'école, matières, horaires, photos, demande d'inscription |
| Administration | `/admin` | La direction : enseignants, tarifs, planning, paramètres |
| Espace enseignant | `/enseignant` | Les enseignants : groupes, élèves, séances, appel, paiements (mobile d'abord) |

Il n'y a pas de compte élève ni parent.

---

## Sommaire

1. [Fonctionnalités](#fonctionnalités)
2. [Stack technique](#stack-technique)
3. [Installation avec Docker](#installation-avec-docker)
4. [Base de données Supabase](#base-de-données-supabase)
5. [Déploiement sur Vercel](#déploiement-sur-vercel)
6. [Comptes](#comptes)
7. [Commandes utiles](#commandes-utiles)
8. [Tests](#tests)
9. [Organisation du code](#organisation-du-code)
10. [Conventions du projet](#conventions-du-projet)
11. [Charte graphique](#charte-graphique)
12. [Avancement](#avancement)

---

## Fonctionnalités

### Site public
- Accroche avec un visuel simple (logo et trois atouts), atouts de l'école, section « Notre histoire » avec ses missions et une galerie de petites photos (agrandies au clic).
- Matières, niveaux, formules (groupe ou individuel).
- **Horaires calculés automatiquement** à partir des créneaux de l'admin.
- Contact, carte OpenStreetMap (Leaflet) et bouton WhatsApp.
- **Formulaire de demande d'inscription** : validation en temps réel, numéros algériens, champ anti-robot, 5 envois par heure au maximum. La langue de la page est enregistrée avec la demande.
- **Une page = une seule langue** : une page en français n'affiche aucun texte arabe ou anglais (seul le menu des langues garde les noms natifs).
- Référencement : `canonical`, `hreflang` FR / AR / EN / x-default, Open Graph, données structurées `EducationalOrganization`.
- Apparitions animées légères, désactivées si le visiteur a choisi de réduire les animations (`prefers-reduced-motion`).
- Les textes de la page (accroche, atouts, histoire, missions, contact, position de la carte) sont des **réglages multilingues** modifiables depuis l'admin. Si une traduction est vide, le texte français s'affiche.

### Administration
- **Tableau de bord** : chiffres clés et **planning de la semaine** (dimanche → samedi), avec navigation de semaine en semaine.
- **Enseignants** :
  - compte de connexion, matières, statut actif / inactif (un enseignant inactif ne peut plus se connecter) ;
  - **deux tarifs par séance** : groupe collectif et cours individuel.
- **Fiche enseignant** : chiffres du mois et historique complet des séances, filtrable et exportable en Excel.
- **Élèves** : liste avec séances suivies, absences, taux de présence et statut de paiement (le plus urgent parmi leurs enseignants), recherche, filtres et export Excel. **Fiche élève** : statistiques, cycle de séances et statut chez chaque enseignant, historique des paiements et dernières présences.
- **Salles** : nom, capacité, couleur dans le planning.
- Composants communs : tableaux avec recherche, filtres, tri, pagination et export Excel ; formulaires en modale ; confirmations et notifications « toast ».

### Espace enseignant
- **Menu latéral** (repliable sur ordinateur, tiroir sur mobile) : Tableau de bord, Séances, Programme, Groupes, Élèves.
- **Séances** : liste à venir / passées / annulées, filtrable par groupe. **Programme** : semaine du dimanche au samedi.
- **Notifications de paiement** dans la cloche (mises à jour à chaque page, pas en temps réel) : un élève atteint son cycle de séances ou passe en retard.
- **Tableau de bord** : planning de la semaine mis en avant, séances du jour (état en direct : à venir / en cours / terminée), prochains jours, chiffres du mois, tarifs, alerte des paiements à demander.
- **Groupes** :
  - création, modification, archivage ; choix des créneaux avec contrôle des conflits (salle, enseignant) ;
  - le groupe est attribué à l'enseignant qui le crée, qui peut le confier à un collègue de la même matière ;
  - fiche groupe : élèves (ajout d'un élève existant ou nouveau, retrait), places restantes, prochaines séances, historique des séances passées.
- **Élèves** : élèves des groupes et des cours individuels, filtres, programmation d'un cours individuel, appel et WhatsApp aux parents.
- **Appel et pointage** :
  - l'appel s'ouvre 10 minutes avant le début de la séance et reste corrigeable jusqu'à la fin de la journée ;
  - sa validation pointe l'enseignant (tarif de groupe ou individuel figé) et passe la séance à « faite » ;
  - saisie du travail réalisé et des devoirs.
- **Annulation d'une séance** : pour cette semaine seulement, ou définitivement (le créneau est retiré du groupe et les séances suivantes sont annulées).
- **Traçabilité** : chaque action sur une séance (appel, correction des présences, travail saisi, annulation) est inscrite dans un journal non modifiable, avec son auteur, sa date et les valeurs avant / après.
- **Paiements** : les séances de chaque élève sont comptées par enseignant. Le paiement est signalé à la fin du cycle (4 séances par défaut, pré-rappel à la 3ᵉ) ; l'enseignant enregistre le paiement reçu, ce qui remet le compteur à zéro.

### Application installable (PWA)
- Installable sur téléphone et ordinateur (`public/manifest.webmanifest`, icônes dans `public/images/pwa`) ; à l'ouverture, chacun arrive dans son espace (admin ou enseignant).
- Bouton « Installer l'application » dans le menu du profil quand le navigateur le permet (sur iPhone : Partager → Sur l'écran d'accueil).
- Service worker (`public/sw.js`) : pages toujours chargées depuis le réseau (aucune donnée connectée en cache), CSS / JS / images / polices en cache ; hors connexion, page `public/offline.html` dans la dernière langue utilisée.

### Automatismes
- **Génération des séances** des groupes à partir de leurs créneaux récurrents, deux semaines à l'avance, chaque nuit : une séance n'est jamais créée deux fois, une séance en conflit est ignorée et signalée.
- **Anti-conflits garantis par PostgreSQL** : des contraintes d'exclusion (`btree_gist`) refusent deux séances actives qui se chevauchent dans la même salle ou pour le même enseignant.

---

## Stack technique

| Domaine | Choix |
|---|---|
| Langage | PHP 8.4 |
| Framework | **Laravel 13** |
| Interface | **Livewire 4** (composants en classes) + Blade + Alpine.js (fourni par Livewire) |
| CSS | **Tailwind CSS 4** (via Vite 8) |
| Base de données | **PostgreSQL 17** : Supabase en ligne, PostgreSQL dans Docker pour les tests |
| Tests | Pest 4 + tests Livewire |
| Paquets serveur | `barryvdh/laravel-dompdf` (PDF), `maatwebsite/excel` (Excel), `laravel-notification-channels/webpush` (push PWA) |
| JavaScript | Leaflet (carte), chargé à la demande |
| Environnement | Docker Compose (PHP, file d'attente, planificateur, Vite, PostgreSQL, Mailpit) |

Il n'y a ni Filament ni framework JavaScript (React, Vue) : toutes les interfaces sont des composants Livewire et Blade.

---

## Installation avec Docker

Prérequis : **Docker Desktop**. Ni PHP ni Node ne sont nécessaires sur la machine.

```bash
# 1. Configuration
cp .env.example .env            # puis renseigner la base (voir « Base de données Supabase »)

# 2. Image PHP et dépendances
docker compose build app
docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate

# 3. Assets (Tailwind / Vite)
docker compose run --rm --no-deps vite sh -c "npm install && npm run build"

# 4. Base de données : tables + données de référence (+ démo hors production)
docker compose run --rm app php artisan migrate --seed

# 5. Démarrage
docker compose up -d app queue scheduler db mailpit
```

| Service | Adresse |
|---|---|
| Application | http://localhost:8010 |
| E-mails de test (Mailpit) | http://localhost:8025 |
| PostgreSQL local | `localhost:5433` (identifiants dans `docker-compose.yml`) |

Pour recompiler le CSS automatiquement pendant le développement : `docker compose up -d vite`.

> **Pourquoi `vendor/` est dans un volume Docker** : sous Windows, le partage de fichiers entre Windows et Docker est très lent pour les milliers de fichiers de `vendor/`. Dans un volume Docker, une page s'affiche en environ 0,3 s. Le dossier `vendor/` visible sur le disque ne sert qu'à l'autocomplétion de l'éditeur ; les commandes `composer` se lancent toujours dans le conteneur.

> **Port 8010** : modifiable dans `docker-compose.yml`.

> **Ne pas garder de fichier `.env.local`** (créé par exemple par `vercel env pull`) : avec `APP_ENV=local`, Laravel le chargerait à la place de `.env`.

---

## Base de données Supabase

1. Dans Supabase, ouvrir **Connect > Session pooler**.
2. Renseigner `.env` :

   ```dotenv
   DB_CONNECTION=pgsql
   DB_HOST=<hôte du session pooler>
   DB_PORT=5432
   DB_DATABASE=postgres
   DB_USERNAME=postgres.<référence-du-projet>
   DB_PASSWORD=<mot de passe de la base>
   DB_SSLMODE=require
   DB_PERSISTENT=true
   ```

3. Lancer `docker compose exec app php artisan migrate`.

Points importants :
- **Utiliser le Session pooler (IPv4)**. La connexion directe `db.<ref>.supabase.co` est en IPv6 uniquement et n'est pas joignable depuis Docker Desktop.
- **La RLS est activée sur toutes les tables**, sans politique : l'API REST de Supabase (clé anon) n'a accès à rien. Laravel, propriétaire des tables, n'est pas concerné. **Chaque nouvelle table doit aussi activer la RLS.**
- L'extension `btree_gist` (contraintes anti-chevauchement) est installée dans le schéma `extensions`.
- En développement local, le cache et les sessions sont en fichiers et les connexions sont persistantes, pour limiter les allers-retours vers Supabase.
- Le mot de passe de la base reste uniquement dans `.env`, qui n'est jamais versionné.

Pour travailler sur la base locale Docker, remettre `DB_HOST=db` et les identifiants de `docker-compose.yml`, avec `DB_SSLMODE=prefer`.

---

## Déploiement sur Vercel

Laravel tourne sur Vercel grâce au runtime communautaire [`vercel-php`](https://github.com/juicyfx/vercel-php) :

| Fichier | Rôle |
|---|---|
| `vercel.json` | Runtime PHP, région, routes (fichiers de `public/` servis directement, le reste vers Laravel), tâche planifiée, variables non secrètes |
| `api/index.php` | Point d'entrée : place `storage` dans `/tmp`, seul dossier modifiable en serverless |
| `.vercelignore` | Exclut les fichiers `.env*`, `vendor/` (réinstallé par Vercel), `public/build` (recompilé par Vercel), les tests et les fichiers Docker |

Adaptations au fonctionnement serverless :
- sessions en cookie, cache en base, file d'attente synchrone, journaux sur `stderr`, caches Laravel dans `/tmp` ;
- HTTPS forcé en production, proxys de Vercel acceptés ;
- **pas de `schedule:work`** : Vercel Cron appelle chaque nuit `/systeme/cron/seances` (protégée par `CRON_SECRET`) pour générer les séances.

Les secrets (`APP_KEY` de production, `DB_PASSWORD`, `CRON_SECRET`) sont des variables d'environnement **« sensitive »** du projet Vercel, jamais dans le code.

**Déploiement automatique** : le projet Vercel est relié au dépôt GitHub. Chaque push sur `main` déploie en production, les autres branches donnent un déploiement de prévisualisation. Vercel compile lui-même le CSS et le JavaScript (`npm ci` puis `npm run build`, voir `vercel.json`).

**Déploiement manuel** (sans passer par Git) :

```bash
docker run --rm -v <volume-de-la-cli-vercel>:/root/.local/share/com.vercel.cli -v "$PWD:/app" -w /app   node:24-alpine npx -y vercel@64.5.0 deploy --prod --yes
```

La première fois, connecter la CLI avec `npx vercel login` (même volume Docker).

Les migrations ne sont pas lancées par le déploiement : mettre à jour le schéma sur Supabase **avant** de déployer le code qui en dépend. Une fois de vraies données en base, ne jamais lancer `migrate:fresh` en production.

---

## Comptes

**Données de démo (base locale uniquement).** `php artisan migrate --seed` hors production crée un compte admin, des enseignants, des groupes, des élèves et plusieurs semaines de séances. Les identifiants sont dans `database/seeders/DemoSeeder.php` (mot de passe `password`).

**En production.** Seules les données de référence sont installées (`ReferenceDataSeeder` : salles, matières, niveaux, créneaux, réglages) et un compte admin. Les comptes enseignants se créent depuis **Admin > Enseignants**.

> Changer les mots de passe par défaut avant toute mise en ligne.

---

## Commandes utiles

Toutes les commandes se lancent dans le conteneur : `docker compose exec app …`

| Commande | Rôle |
|---|---|
| `php artisan migrate` | Applique les nouvelles migrations |
| `php artisan db:seed --class=ReferenceDataSeeder` | (Ré)installe les données de référence sans écraser les réglages modifiés |
| `php artisan lessons:generate --days=14` | Génère les séances des groupes (lancée automatiquement chaque nuit) |
| `php artisan schedule:list` | Liste les tâches planifiées |
| `php artisan db:show` | Affiche la base utilisée |
| `vendor/bin/pint` | Formate le code PHP (style Laravel) |
| `php artisan test --compact` | Lance les tests |

Le service `scheduler` exécute `php artisan schedule:work`, et le service `queue` traite la file d'attente.

---

## Tests

```bash
docker compose exec app php artisan test --compact
```

- Les tests utilisent **toujours la base PostgreSQL locale** de test, jamais Supabase : c'est imposé dans `phpunit.xml` (`force="true"`), car les tests vident la base à chaque lancement.
- Ils couvrent notamment :
  - la séparation des rôles et la connexion ;
  - le multilingue (FR / AR / EN), le RTL et la parité des traductions ;
  - les contraintes anti-chevauchement et la génération des séances ;
  - l'espace enseignant : groupes, élèves, appel, journal, annulation, compteur de séances et paiements ;
  - la page d'accueil (une seule langue par page) et le formulaire d'inscription ;
  - les numéros algériens et les contrastes de la charte (WCAG AA).

---

## Organisation du code

```
app/
├── Actions/            Opérations métier (une classe = une action) : SaveGroup, TakeAttendance, CancelLesson…
├── Console/Commands/   Commandes artisan (lessons:generate)
├── Enums/              Statuts et types (séance, paiement, présence, journal…) avec libellés traduits
├── Http/Middleware/    SetLocale (langue en session), EnsureUserHasRole (séparation admin / enseignant)
├── Notifications/      Notifications « application » (alerte de paiement)
├── Livewire/
│   ├── Admin/          Pages de l'administration
│   ├── Auth/           Connexion, mot de passe oublié
│   ├── Home/           Page d'accueil et formulaire d'inscription
│   ├── Teacher/        Espace enseignant (accueil, groupes, élèves, séances)
│   ├── Tables/         Tableau de données réutilisable (DataTable, Column, Filter, export Excel)
│   ├── Forms/          Formulaires Livewire (validation)
│   └── Concerns/       WithModalForm : création / modification en modale, suppression protégée
├── Models/             Modèles Eloquent ; Concerns/HasTranslations pour les champs multilingues
├── Repositories/
│   ├── Contracts/      Interfaces de lecture : LessonRepository, GroupRepository, StudentRepository…
│   └── Eloquent/       Implémentations Eloquent (reliées dans RepositoryServiceProvider)
├── Services/           ConflictChecker (salles, enseignants, élèves), LessonCounter (séances et paiements)
├── Settings/           Réglages multilingues modifiables dans l'admin (et valeurs par défaut)
├── Support/            Format, Phone, OpeningHours, Catalog (cache), navigations, galerie
└── View/Components/    Composants Blade avec classe (logo, drapeaux, sélecteur de langue…)

lang/fr, lang/ar, lang/en   Toutes les chaînes de l'interface (aucun texte en dur)
resources/views/
├── components/         Mises en page, logo, icônes, composants ui
├── home/               Sections de la page d'accueil
├── livewire/           Vues des composants Livewire
└── tables/             Gabarit du tableau de données
routes/
├── web.php             Accueil, langues, authentification, tâche planifiée
├── admin.php           /admin (rôle admin)
├── teacher.php         /enseignant (rôle enseignant)
└── console.php         Tâches planifiées
```

---

## Conventions du projet

- **Pattern Repository** pour l'accès aux données :
  - les **repositories** (`app/Repositories`) contiennent les requêtes de lecture. Chaque contrat (`Contracts/`) est relié à son implémentation Eloquent (`Eloquent/`) dans `RepositoryServiceProvider` ;
  - les **composants Livewire, formulaires et services** dépendent des contrats (`app(LessonRepository::class)`), jamais d'Eloquent ni de `DB` directement ; un test le vérifie pour l'espace enseignant ;
  - les **actions** (`app/Actions`) portent les règles métier et les écritures : anti-conflits, capacité, appel, annulation, paiements.
- Les pages d'administration existantes (tableaux `DataTable`) seront migrées vers les repositories au fur et à mesure.
- **Aucun code PHP dans les vues Blade** : pas de bloc `@php` ni de calcul. Les données sont préparées par les composants Livewire, les modèles ou des composants Blade avec classe ; la vue ne fait qu'afficher.
- **Aucun texte en dur.** Toute chaîne passe par `lang/fr`, `lang/ar` et `lang/en`. Un test vérifie que chaque clé existe dans les trois langues.
- **Langue** : gardée uniquement en session (français par défaut), jamais en base.
- **Contenus saisis par l'admin dans plusieurs langues** : colonnes `jsonb` `{fr, ar, en}` avec le trait `HasTranslations`, ou réglages `Settings::text()`. Si une traduction est vide, le texte français s'affiche.
- **RTL** : utiliser les utilitaires logiques de Tailwind (`ms-`, `me-`, `ps-`, `pe-`, `start-`, `end-`, `text-start`) et `flip-rtl` pour les flèches.
- **Dates et montants** : passer par `App\Support\Format`. Les dates s'affichent dans la langue courante avec des chiffres occidentaux ; les montants en « DA » / « دج ».
- **Fuseau horaire** : `Africa/Algiers`. La semaine va du dimanche au samedi.
- **Téléphones** : stockés au format national `0555123456` via `App\Support\Phone`, et validés par la règle `AlgerianPhone`.
- **Notifications dans l'application** : elles stockent une clé de traduction et ses paramètres, pas une phrase. Pas de rafraîchissement automatique (pas de `wire:poll`).
- **Nouvelles pages de gestion** : étendre `DataTable` et utiliser `WithModalForm` (voir `Admin\Rooms\Index`, l'exemple le plus simple).

---

## Charte graphique

| Rôle | Couleur | Token Tailwind |
|---|---|---|
| Couleur principale (titres, boutons, en-têtes) | Vert olive `#2F5D50` | `olive-700` |
| Éléments secondaires, fonds de cartes | Vert sauge `#8DB596` | `sage-400` |
| Accent (logo, icônes, décor) | Terracotta `#D9774B` | `terra-500` |
| Boutons d'action | Terracotta foncé `#B9572F` | `terra-600` |
| Liens et surtitres | Terracotta profond `#9E4C2A` | `terra-700` |
| Petites touches (badges, soulignements) | Jaune miel `#F2C14E` | `honey-400` |
| Fond | Sable `#FAF6EE` | `sand-100` |
| Texte principal / secondaire / aide | `#1F2A2E` / `#4F625C` / `#62716C` | `ink` / `muted` / `subtle` |

- **Accessibilité** : toutes les combinaisons texte/fond respectent **WCAG AA**, et le test `tests/Unit/BrandContrastTest.php` le vérifie.
- **Polices** (Google Fonts) : Baloo 2 et Baloo Bhaijaan 2 pour les titres, Nunito pour le texte latin, Cairo pour le texte arabe.
- **Interactions** : utilitaires `card-hover`, `segmented`, `chip`, `progress`, `press`, animation `animate-fade-up`, compteurs animés.

---

## Avancement

| Étape | Contenu | État |
|---|---|---|
| 1 | Logo SVG, charte en tokens Tailwind | ✅ |
| 2 | Multilingue FR / AR / EN et RTL | ✅ |
| 3 | Modèles, migrations, seeders | ✅ |
| 4 | Composants Livewire réutilisables, mises en page admin et enseignant | ✅ |
| 5 | Page d'accueil | ✅ |
| 6 | Admin : enseignants (deux tarifs), historique et planning ✅ — groupes, élèves, matières, niveaux, créneaux et paramètres à venir | 🚧 |
| 7 | Espace enseignant : groupes, élèves, appel et pointage, journal, annulation | ✅ |
| 8 | Paiements : compteur par cycle et signalement ✅ — reçus PDF et suivi côté admin à venir | 🚧 |
| 9 | Absences des enseignants, remplacements et rattrapages | ⏳ |
| 10 | Notifications (application, e-mail, SMS / WhatsApp aux parents validés par l'admin) | ⏳ |
| 11 | PWA : manifeste, icônes, service worker, page hors connexion, bouton « Installer l’application » ✅ — notifications push à venir | 🚧 |
