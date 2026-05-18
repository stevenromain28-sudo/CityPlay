# ROADMAP GLOBALE — CITYPLAY

## Présentation du projet

CITYPLAY est une plateforme web immersive permettant aux touristes et utilisateurs de découvrir une ville via des jeux d’énigmes géolocalisées.

Les utilisateurs progressent dans une ville en résolvant des énigmes liées à des lieux réels, avec validation GPS, contenu culturel débloqué, gameplay individuel ou collectif et système de score.

---

# Objectifs du projet

## Objectifs fonctionnels

- Valorisation touristique et culturelle
- Découverte immersive des villes
- Jeu collaboratif ou compétitif
- Géolocalisation réelle
- Contenus historiques/audio débloqués

---

## Objectifs techniques

Construire une application :

- Scalable
- Temps réel
- Mobile first
- Architecture propre
- Backend robuste
- Frontend réactif

---

# Stack technique retenue

## Backend

- Laravel 12
- InertiaJS
- Laravel Breeze
- Sanctum
- Laravel Reverb
- Spatie Permission
- Spatie Media Library

---

## Frontend

- Vue 3
- Inertia
- TailwindCSS
- VueUse

---

## Géolocalisation

- Leaflet
- OpenStreetMap
- Browser Geolocation API

---

## Temps réel

- Laravel Reverb
- Echo

---

# Équipe projet

Projet développé par 3 développeurs.

---

## DEV 1 — Administration / Backoffice

Responsable : Steven

### Modules :

- Dashboard admin
- Gestion villes
- Gestion lieux
- Gestion énigmes
- Gestion indices
- Gestion contenus culturels
- Statistiques

### Backend :

Controllers :

- VilleController
- LieuController
- EnigmeController
- IndiceController

Services :

- VilleService
- LieuService
- EnigmeService

---

## DEV 2 — Gameplay / Moteur du jeu

Responsable : Dylan

Modules :

- Sessions de jeu
- Progression
- Scores
- GPS
- Invitations
- Modes coopératif/mercenaire

Services :

- SessionJeuService
- GPSService
- ScoreService
- InvitationService

---

## DEV 3 — Frontend / UX / Temps réel

Responsable : Arsène


Modules :

- Dashboard joueur
- Carte interactive
- Profil
- Classement
- Websocket
- Interface utilisateur

---

# Architecture base de données

## Tables conçues

### Utilisateurs

users

---

### Villes

villes

Contient :

- nom
- description
- pays
- bannière

---

### Lieux

lieux

Contient :

- ville_id
- coordonnées GPS
- rayon
- image
- difficulté

---

### Énigmes

enigmes

Contient :

- niveau
- ordre
- image
- GPS
- validation

---

### Indices

indices

---

### Contenus culturels

contenus_culturels

---

### Sessions

sessions_jeu

---

### Joueurs sessions

joueur_sessions

---

### Tentatives

tentatives_enigmes

---

### Invitations

invitations

---

# Relations Eloquent

Relations implémentées :

Ville

```php
hasMany(Lieu)
hasMany(SessionJeu)
```

Lieu

```php
belongsTo(Ville)
hasMany(Enigme)
hasOne(ContenuCulturel)
```

Enigme

```php
belongsTo(Lieu)
hasMany(Indice)
```

SessionJeu

```php
hasMany(JoueurSession)
belongsToMany(User)
```

JoueurSession

```php
belongsTo(User)
belongsTo(SessionJeu)
```

---

# Structure backend prévue

```bash

```
users_tables
villes_tables
lieux_tables
enigmes_tables
indices_tables
contenu_culturels_tables
session_jeu_table
joueur_session_table
tentative_enigme_table
invitation_table

Ce sont les tables que nous manipulons jusque là pour l'instant,on va sûrement en rajouter ou améliorer les champs 

---

# Structure frontend prévue

```bash
resources/js/

Pages/

Components/

Layouts/

Stores/

Composables/

Services/

Utils/

Config/
```

---

# Fonctionnalités principales

## Gestion villes

Créer :

- ville
- image
- description
- infos culturelles

---

## Gestion lieux

Créer :

- coordonnées
- rayon GPS
- difficulté

---

## Gestion énigmes

Créer :

- texte
- image
- indice
- réponse
- GPS

---

## Sessions de jeu

Créer :

- démarrage
- pause
- abandon
- reprise

---

## Modes

### Coopératif

Progression partagée

---

### Mercenaire

Compétition

---

## Système score

Calcul selon :

- temps
- indices
- distance
- rapidité

---

## Invitations

Support :

- lien
- WhatsApp
- email
- SMS

---

## Contenu culturel

Déblocage :

- audio
- texte
- histoire

---

# Roadmap développement

---

# PHASE 1 — Initialisation projet

Terminé :

- Laravel installé
- Inertia installé
- Vue installé
- Packages installés

---

# PHASE 2 — Architecture backend

Terminé :

- Modèles créés
- Migrations créées
- Relations conçues

---

# PHASE 3 — Structure équipe

Terminé :

- Répartition des développeurs

---

# PHASE 4 — Compléter modèles

À faire :

Ajouter :

- fillable
- casts
- scopes
- méthodes métier

---

# PHASE 5 — Factories + Seeders

Créer :

- VilleFactory
- EnigmeFactory
- LieuFactory

---

# PHASE 6 — Requests

Créer :

Validation :

```bash
VilleRequest
LieuRequest
EnigmeRequest
SessionJeuRequest
```

---

# PHASE 7 — Policies

Créer :

```bash
VillePolicy
EnigmePolicy
SessionJeuPolicy
```

---

# PHASE 8 — CRUD Administration

Développer :

- villes
- lieux
- énigmes
- contenus

---

# PHASE 9 — Gameplay

Développer :

- création session
- rejoindre session
- progression
- pause
- reprise

---

# PHASE 10 — GPS

Développer :

- validation géographique
- calcul distance
- rayon

---

# PHASE 11 — Score

Développer :

- calcul
- classement

---

# PHASE 12 — Invitations

Développer :

- email
- WhatsApp
- SMS

---

# PHASE 13 — Temps réel

Développer :

- progression live
- score live
- joueurs connectés

---

# PHASE 14 — Carte interactive

Développer :

- Leaflet
- marqueurs
- itinéraire

---

# PHASE 15 — Dashboard joueur

Développer :

- score
- progression
- historique

---

# PHASE 16 — Dashboard admin

Développer :

- statistiques
- sessions
- activité

---

# PHASE 17 — Optimisation

Ajouter :

- cache
- queues
- Redis

---

# PHASE 18 — Tests

Créer :

- tests unitaires
- tests fonctionnels

---

# PHASE 19 — Déploiement

Configurer :

- VPS
- Nginx
- SSL
- CI/CD

---

# Objectif MVP

Version minimale :

✓ Authentification

✓ Villes

✓ Lieux

✓ Énigmes

✓ Sessions

✓ GPS

✓ Score

✓ Invitations

✓ Dashboard

---

# Vision long terme

CITYPLAY peut devenir :

- Plateforme touristique
- Solution pour mairies
- Application internationale
- SaaS
- Application mobile

---

# État actuel du projet

Progression estimée :

Architecture : ✓

Base données : ✓

Modèles : ✓

Organisation équipe : ✓

Frontend : En cours

Gameplay : À faire

Temps réel : À faire

GPS : À faire

MVP global : ~20%