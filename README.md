<p align="center">
  <img src="public/images/brand/logo-icon.svg" width="96" alt="Logo Éducœur">
</p>

<h1 align="center">Éducœur · إديكور</h1>

<p align="center"><strong>Enseigner avec cœur · نُعلّم بقلب</strong></p>

Application web de l'école de cours de soutien **Éducœur**, Cité Ighil Taourga, Boumerdès (Algérie) : cours de mathématiques, physique, arabe, français et anglais pour le primaire et le CEM, en groupes de 15 élèves maximum ou en cours individuels.

L'application réunit trois espaces, tous en **français et en arabe** (arabe en RTL complet) :

| Espace | Adresse | Pour qui |
|---|---|---|
| Site public | `/`, `/fr`, `/ar` | Les parents : présentation de l'école, horaires, carte, demande d'inscription |
| Administration | `/admin` | La direction : enseignants, groupes, planning, paiements, paramètres |
| Espace enseignant | `/enseignant` | Les enseignants : séances du jour et de la semaine, appel, absences (installable en PWA) |

Il n'y a pas de compte élève ni parent : les parents sont prévenus par SMS / WhatsApp, à l'initiative de l'admin.

---

## Sommaire

1. [Fonctionnalités](#fonctionnalités)
2. [Stack technique](#stack-technique)
3. [Installation avec Docker](#installation-avec-docker)
4. [Base de données Supabase](#base-de-données-supabase)
5. [Comptes](#comptes)
6. [Commandes utiles](#commandes-utiles)
7. [Tests](#tests)
8. [Organisation du code](#organisation-du-code)
9. [Conventions du projet](#conventions-du-projet)
10. [Charte graphique](#charte-graphique)
11. [Avancement](#avancement)

---

## Fonctionnalités

### Site public
- Accroche, atouts de l'école, **« Notre histoire »** (fondée en 2019 par Essaid Baklioun) et ses trois missions.
- Matières, niveaux (primaire et CEM), formules (groupe ou individuel).
- **Horaires calculés automatiquement** à partir des créneaux de l'admin.
- Salles, contact, carte OpenStreetMap (Leaflet) et bouton WhatsApp.
- **Formulaire de demande d'inscription** : validation en temps réel, numéros algériens, champ anti-robot, 5 envois par heure au maximum. Les demandes sont enregistrées en base et visibles dans l'admin.
- Référencement : titre et description, `canonical`, `hreflang` FR / AR / x-default, Open Graph avec image dédiée, données structurées `EducationalOrganization`.
- Apparitions animées légères, désactivées si le visiteur a choisi de réduire les animations (`prefers-reduced-motion`).
- Tous les textes (accroche, atouts, histoire, missions, téléphone, WhatsApp, adresse, position de la carte) sont des **réglages bilingues** modifiables depuis l'admin. Si la version arabe est vide, le texte français s'affiche.

### Administration
- **Tableau de bord** :
  - chiffres clés (séances du jour, enseignants et élèves actifs, nouvelles demandes, séances à traiter) ;
  - **planning de la semaine**, du dimanche au samedi : chaque séance avec horaires, groupe, matière, salle et nom de l'enseignant ;
  - navigation de semaine en semaine.
- **Enseignants** :
  - création du compte de connexion (e-mail, mot de passe, langue), matières, tarif par séance, statut actif ou inactif ;
  - un enseignant inactif ne peut plus se connecter.
- **Fiche enseignant** :
  - chiffres du mois (séances faites, à venir, annulées, montant à payer) ;
  - **historique complet de ses séances**, remplacements compris, filtrable par période, statut et type, exportable en Excel.
- **Salles** : nom, capacité (15 au maximum), couleur dans le planning.
- Composants communs à toutes les pages de gestion :
  - tableaux avec recherche, filtres, tri, pagination et export Excel (cartes empilées sur mobile) ;
  - formulaires en modale avec validation en temps réel ;
  - fenêtre de confirmation et notifications « toast ».

### Espace enseignant
- Mise en page mobile d'abord, avec onglets en bas d'écran.
- Les fonctions de l'enseignant (voir ses séances, faire l'appel, déclarer une absence, voir ses heures) arrivent à l'étape 7 (voir [Avancement](#avancement)).

### Automatismes
- **Génération des séances** des groupes à partir de leurs créneaux récurrents, deux semaines à l'avance, chaque nuit à 00:15 :
  - une séance n'est jamais créée deux fois ;
  - une séance en conflit est ignorée et signalée.
- **Anti-conflits garantis par PostgreSQL** : des contraintes d'exclusion (`btree_gist`) refusent deux séances actives qui se chevauchent dans la même salle ou pour le même enseignant, même si le code oubliait une vérification.

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
| PostgreSQL local | `localhost:5433` (utilisateur `educoeur`, mot de passe `secret`) |

Pour recompiler le CSS automatiquement pendant le développement : `docker compose up -d vite`.

> **Pourquoi `vendor/` est dans un volume Docker** : sous Windows, le partage de fichiers entre Windows et Docker est très lent pour les milliers de fichiers de `vendor/` (environ 11 s par page). Dans un volume Docker, une page s'affiche en environ 0,3 s. Le dossier `vendor/` visible sur le disque ne sert qu'à l'autocomplétion de l'éditeur ; les commandes `composer` se lancent toujours dans le conteneur.

> **Port 8010** : le port 8000 était déjà utilisé sur la machine de développement. Il se change dans `docker-compose.yml`.

---

## Base de données Supabase

L'application utilise le projet Supabase **« Éducœur »** (région `eu-central-1`).

1. Dans Supabase, ouvrir **Connect > Session pooler**.
2. Renseigner `.env` :

   ```dotenv
   DB_CONNECTION=pgsql
   DB_HOST=aws-1-eu-central-1.pooler.supabase.com
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
- **La RLS est activée sur toutes les tables**, sans politique. L'API REST de Supabase (clé anon) n'a donc accès à rien. Laravel, propriétaire des tables, n'est pas concerné. **Chaque nouvelle migration qui crée une table doit aussi activer la RLS.**
- L'extension `btree_gist` (contraintes anti-chevauchement) est installée dans le schéma `extensions`.
- En développement local, le cache et les sessions sont en fichiers (`CACHE_STORE=file`, `SESSION_DRIVER=file`) et les connexions sont persistantes (`DB_PERSISTENT=true`), pour limiter les allers-retours vers Supabase.
- Le mot de passe de la base reste uniquement dans `.env`, qui n'est jamais versionné.

Pour travailler sur la base locale Docker, remettre `DB_HOST=db`, `DB_DATABASE=educoeur`, `DB_USERNAME=educoeur`, `DB_PASSWORD=secret`, `DB_SSLMODE=prefer`.

---

## Déploiement sur Vercel

Le site est en ligne sur **https://educoeur.vercel.app** (projet Vercel `educoeur`, région `fra1` (Francfort), la même que Supabase).

Laravel tourne sur Vercel grâce au runtime communautaire [`vercel-php`](https://github.com/juicyfx/vercel-php) (PHP 8.5) :

| Fichier | Rôle |
|---|---|
| `vercel.json` | Runtime PHP, région, routes (fichiers de `public/` servis directement, le reste vers Laravel), tâche planifiée, variables non secrètes |
| `api/index.php` | Point d'entrée : place `storage` dans `/tmp`, seul dossier modifiable en serverless |
| `.vercelignore` | Exclut `.env`, `vendor/` (réinstallé par Vercel), les tests et les fichiers Docker |

Adaptations au fonctionnement serverless :
- sessions en cookie, cache en base, file d'attente synchrone, journaux sur `stderr`, caches Laravel dans `/tmp` ;
- HTTPS forcé en production, proxys de Vercel acceptés ;
- **pas de `schedule:work`** : Vercel Cron appelle chaque nuit `/systeme/cron/seances` (protégée par `CRON_SECRET`) pour générer les séances.

Les secrets (`APP_KEY` propre à la production, `DB_PASSWORD`, `CRON_SECRET`) sont des variables d'environnement **« sensitive »** du projet Vercel. Ils ne sont jamais dans le code.

**Redéployer** (CSS compilé au préalable, car Vercel ne lance pas Vite) :

```bash
docker compose run --rm --no-deps vite sh -c "npm run build"
docker run --rm -v educoeur_vercel:/root/.local/share/com.vercel.cli -v "$PWD:/app" -w /app \
  node:24-alpine npx -y vercel@64.5.0 deploy --prod --yes
```

La première fois, connecter la CLI avec `npx vercel login` (même volume Docker).

Les migrations ne sont pas lancées par le déploiement : appliquer les nouvelles migrations sur Supabase depuis le poste (`docker compose exec app php artisan migrate`, avec `.env` pointé sur Supabase) **avant** de déployer le code qui en dépend.

---

## Comptes

**Données de démo (base locale uniquement).** `php artisan migrate --seed` hors production crée 5 enseignants, 8 groupes, 40 élèves et 6 semaines de séances.

| Rôle | E-mail | Mot de passe |
|---|---|---|
| Admin | `admin@educoeur.dz` | `password` |
| Enseignant | `enseignant@educoeur.dz` | `password` |

**Supabase.** Seules les données de référence sont installées (salles, matières, niveaux, créneaux, réglages), avec un compte admin `admin@educoeur.dz`. Les comptes enseignants se créent depuis **Admin > Enseignants**.

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

Le service `scheduler` exécute `php artisan schedule:work`, et le service `queue` traite la file d'attente (`php artisan queue:work`).

---

## Tests

```bash
docker compose exec app php artisan test --compact
```

- Les tests utilisent **toujours la base PostgreSQL locale** `educoeur_test`, jamais Supabase : c'est imposé dans `phpunit.xml` (`force="true"`), car les tests vident la base à chaque lancement.
- Ils couvrent :
  - la séparation des rôles et la connexion ;
  - le multilingue et le RTL ;
  - la parité des traductions FR et AR ;
  - les contraintes anti-chevauchement ;
  - la génération des séances ;
  - la gestion et l'historique des enseignants, et le planning ;
  - la page d'accueil et le formulaire d'inscription ;
  - les numéros algériens ;
  - les contrastes de la charte (WCAG AA).

---

## Organisation du code

```
app/
├── Actions/            Opérations métier (une classe = une action) : SaveTeacher, GenerateGroupLessons…
├── Console/Commands/   Commandes artisan (lessons:generate)
├── Enums/              Statuts et types (séance, paiement, présence…) avec libellés traduits
├── Http/Middleware/    SetLocale (langue), EnsureUserHasRole (séparation admin / enseignant)
├── Livewire/
│   ├── Admin/          Pages de l'administration
│   ├── Auth/           Connexion, mot de passe oublié
│   ├── Home/           Page d'accueil et formulaire d'inscription
│   ├── Teacher/        Espace enseignant
│   ├── Tables/         Tableau de données réutilisable (DataTable, Column, Filter, export Excel)
│   ├── Forms/          Formulaires Livewire (validation)
│   └── Concerns/       WithModalForm : création / modification en modale, suppression protégée
├── Models/             Modèles Eloquent ; Concerns/HasTranslations pour les champs FR/AR
├── Services/           ConflictChecker (salles, enseignants, élèves)
├── Settings/           Réglages bilingues modifiables dans l'admin (et valeurs par défaut)
└── Support/            Format (dates, DA), Phone, OpeningHours, Catalog (cache), AdminNavigation

lang/fr, lang/ar        Toutes les chaînes de l'interface (aucun texte en dur)
resources/views/
├── components/         Mises en page (base, public, admin, enseignant, auth), logo, icônes, composants ui
├── home/               Sections de la page d'accueil
├── livewire/           Vues des composants Livewire
└── tables/             Gabarit du tableau de données
routes/
├── web.php             Accueil, langues, authentification
├── admin.php           /admin (rôle admin)
├── teacher.php         /enseignant (rôle enseignant)
└── console.php         Tâches planifiées
```

---

## Conventions du projet

- **Les règles métier sont dans des actions et des services**, jamais dans les composants Livewire : anti-conflits, capacité, pointage, paiements.
- **Aucun texte en dur.** Toute chaîne passe par `lang/fr` et `lang/ar`, y compris les messages de validation et les e-mails. Un test vérifie que chaque clé existe dans les deux langues.
- **Contenus saisis par l'admin en deux langues** : colonnes `jsonb` `{fr, ar}` avec le trait `HasTranslations`, ou réglages `Settings::text()`. Si la version arabe est vide, le texte français s'affiche.
- **RTL** : utiliser les utilitaires logiques de Tailwind (`ms-`, `me-`, `ps-`, `pe-`, `start-`, `end-`, `text-start`) et `flip-rtl` pour les flèches.
- **Dates et montants** : passer par `App\Support\Format`. Les dates s'affichent dans la langue courante (mois algériens en arabe, par exemple جانفي), avec des chiffres occidentaux, au format `d/m/Y`, et les montants en « DA » / « دج ».
- **Fuseau horaire** : `Africa/Algiers`. La semaine va du dimanche au samedi.
- **Téléphones** : stockés au format national `0555123456` via `App\Support\Phone`, et validés par la règle `AlgerianPhone`.
- **Notifications dans l'application** : elles stockent une clé de traduction et ses paramètres, pas une phrase. Le texte est traduit à l'affichage, dans la langue de la personne qui le lit. Pas de rafraîchissement automatique (pas de `wire:poll`) : la cloche se met à jour à chaque page.
- **Nouvelles pages de gestion** : étendre `DataTable` et utiliser `WithModalForm` (voir `Admin\Rooms\Index`, l'exemple le plus simple).

---

## Charte graphique

| Rôle | Couleur | Token Tailwind |
|---|---|---|
| Couleur principale (titres, boutons, en-têtes) | Vert olive `#2F5D50` | `olive-700` |
| Éléments secondaires, fonds de cartes | Vert sauge `#8DB596` | `sage-400` |
| Accent (cœur du logo, icônes, décor) | Terracotta `#D9774B` | `terra-500` |
| Boutons d'action | Terracotta foncé `#B9572F` | `terra-600` |
| Liens et surtitres | Terracotta profond `#9E4C2A` | `terra-700` |
| Petites touches (badges, soulignements) | Jaune miel `#F2C14E` | `honey-400` |
| Fond | Sable `#FAF6EE` | `sand-100` |
| Texte principal / secondaire / aide | `#1F2A2E` / `#4F625C` / `#62716C` | `ink` / `muted` / `subtle` |

- **Accessibilité** : toutes les combinaisons texte/fond respectent **WCAG AA**, et le test `tests/Unit/BrandContrastTest.php` le vérifie. Le terracotta `#D9774B` et le sauge `#8DB596` ne portent jamais de texte blanc ; on utilise les nuances plus foncées ci-dessus, ou du texte foncé.
- **Polices** (Google Fonts) : Baloo 2 pour les titres latins, Baloo Bhaijaan 2 pour les titres arabes, Nunito pour le texte français, Cairo pour le texte arabe.
- **Logo** : un livre ouvert dont les deux pages forment un cœur, dans `public/images/brand/`. Versions : horizontale, icône carrée, symbole seul, monochrome, et image Open Graph.

---

## Avancement

| Étape | Contenu | État |
|---|---|---|
| 1 | Logo SVG, charte en tokens Tailwind | ✅ |
| 2 | Multilingue FR / AR et RTL | ✅ |
| 3 | Modèles, migrations, seeders | ✅ |
| 4 | Composants Livewire réutilisables, mises en page admin et enseignant | ✅ |
| 5 | Page d'accueil (dont « Notre histoire ») | ✅ |
| 6 | Admin : enseignants, historique et planning du tableau de bord ✅ — groupes, élèves, matières, niveaux, créneaux, grille de planning et paramètres à venir | 🚧 |
| 7 | Espace enseignant et pointage (appel de 10 min avant le début jusqu'à la fin de la séance) | ⏳ |
| 8 | Paiements par cycle de 4 séances, reçus PDF, alertes | ⏳ |
| 9 | Absences des enseignants, remplacements et rattrapages | ⏳ |
| 10 | Notifications (application, e-mail, SMS / WhatsApp aux parents validés par l'admin) | ⏳ |
| 11 | PWA (manifeste, icônes, service worker, message hors ligne) | ⏳ |
