# BloodLink — handoff for the next coding assistant

Read this file, then `README.md`, `index.php`, and the relevant controllers/views before changing the project. Continue from the current local branch and preserve the French interface, XAMPP/PHP/MySQL stack, MVC separation, and existing data.

## Repository and local environment

- Local path: `C:\xampp\htdocs\Esprit-PIWEB-2A6-2026-BloodLink`
- GitHub remote: `https://github.com/Bacem-Trimech/Esprit-PIWEB-2A6-2026-BloodLink`
- Active branch: `BloodLink-Bacem`; the latest implementation commit before this handoff is `6636d46`. Local commits are ahead of `origin/BloodLink-Bacem`; check `git log` for the current HEAD. Changes have **not** been pushed.
- Local URL used for development: `http://127.0.0.1:8088/Esprit-PIWEB-2A6-2026-BloodLink/`. Standard XAMPP Apache URL is documented in `README.md`.
- Local MariaDB database: `bloodlink_piweb_2a6`. `config/config.php` reads `BLOODLINK_DB_HOST`, `BLOODLINK_DB_NAME`, `BLOODLINK_DB_USER`, `BLOODLINK_DB_PASS`; defaults are local XAMPP values.
- Fresh installation: import `database.sql`. Existing installation: apply migrations `002`, `003`, `004` once, in order. The local database already has them. **Do not import `database.sql` over the current database.**
- Local demo users and collection are database records, not Git files. No password or invitation token is stored in this handoff. Ask the project owner for test credentials if needed.

## What the user wants

BloodLink is a French blood-donation platform with distinct visitor, donor, responsable, and admin experiences. Keep the public website for visitors/donors. The responsable should work in a focused collection workspace. The admin manages users, responsables, all collections, statistics, and SOS; the admin has a separate “Voir le site public” link. The user strongly prefers clear inline validation, save actions without full page reload, a responsive layout, and the established red/white BloodLink theme.

## Current implementation map

- `index.php`: front controller and route switch. `Service/Securite.php`: sessions, CSRF, roles, escaping, redirects.
- `Controller/AuthController.php`, `UtilisateurController.php`, `InvitationController.php`, `CompteController.php`: signup/login, admin CRUD and roles, one-use 30-day invitations, profiles, password changes, opaque donor QR.
- `Controller/CollecteController.php`: collections, slots/capacity, booking, arrivals by numeric account ID, 12-character reservation code, or QR token; confirmed donations; withdrawal before confirmed donation. It checks for a duplicate active participation and the 12-week interval after a recorded whole-blood donation.
- `Controller/MedicalController.php`: pre-donation measurements and decisions; laboratory summary; staff-entered status for a possible future donation. The UI values are loaded back from `pourCollecte()` into `View/back-office/collecte-gestion.php`.
- `Controller/SosController.php`: public two-hour SOS alerts, red-cell group compatibility hints, admin moderation.
- `Controller/ParametreController.php`: **responsable-owned** settings in `parametres_responsable`, not admin settings. `TableauBordController.php`: admin statistics.
- `View/back-office/collecte-gestion.php`: redesigned schedule, walk-in invitation, arrival form, participation table, and separate examination/result cards. `View/back-office/layout.php`: role-specific navigation. `View/front-office/`: public and donor pages.
- `public/assets/css/bloodlink.css`: original BloodLink-Atelier theme. `public/assets/css/app.css`: responsive refinements and component overrides. `public/assets/js/validation.js`: client validation. `forms.js`: AJAX form submission and inline errors. `experience.js`: QR and SOS countdown. No HTML `required` attributes were intentionally used.

## Meaning of the responsable settings screen

The screenshot discussed with the user shows **Paramètres de mes collectes**. Its fields belong to the signed-in responsable:

| Field | Effect |
| --- | --- |
| Nom de l’organisation | Displayed as the organizer on that responsable’s public collection details. |
| E-mail public | Displayed as the contact on those collection details. |
| Capacité par défaut | Prefills the capacity when creating a **new slot**. It does not edit existing slots. |
| Horizon de réservation (jours) | Limits how far ahead donors may book that responsable’s collection slots. |
| Message d’accueil | Displayed on that responsable’s public collection details; it is not the global homepage text. |

The demo values shown in the screenshot are `Centre Démo BloodLink`, `responsable.demo@bloodlink.test`, capacity `3`, booking horizon `90` days, and a welcome sentence. The `.test` address is demo data and does not receive email.

## Local demo state at handoff

The current local database contains an active admin `bacemtrimech6@gmail.com`, active responsable `responsable.demo@bloodlink.test`, and three active demo donors `demo.donneur1@bloodlink.test` through `demo.donneur3@bloodlink.test`. It has one published collection, “Collecte démo BloodLink,” in Tunis on **29 November 2026, 09:00–14:00** with five hourly slots, owned by the demo responsable. Some demo arrivals, examinations, and one confirmed donation have been entered while checking flows. Treat these records as user-visible demo data; do not reset the database silently.

## Security and medical boundaries

Server-side PHP validation is authoritative; JavaScript assists the UI. Passwords are hashed; tokens are random and stored as hashes; SQL uses prepared PDO statements; views escape user content; POST actions require CSRF; access and collection ownership are checked server-side. The donor QR contains an opaque ID and staff lookup requires authentication.

The app records the center’s decisions. It does not medically certify donation eligibility. The future-donation state (`EN_COURS`, `AUTORISE`, `NON_AUTORISE`) is entered by staff, with a date for `AUTORISE`. Booking and walk-in registration additionally use a conservative 12-week interval after the last confirmed donation recorded **in this database**. A center must assess the person again. SOS blood-group suggestions are red-cell hints only; clinical compatibility needs laboratory testing. Do not expose unvalidated or sensitive reactive screening details to the public.

## Checks already performed and follow-up

- PHP and JavaScript syntax checks passed after the latest UI changes.
- The reported flow was checked locally: pre-donation values saved in `EN_ATTENTE`, an invalid systolic value produced a readable error, and a corrected value saved afterward. The saved values and entry time reappeared on the responsable page.
- Role navigation was checked: the responsable has no donor booking prompt or public-site sidebar link; the admin has a separate public-site link. Donor examination/history pages show recorded arrival and donation times.
- A narrow browser view of the collection page was inspected after changing the schedule into readable slot cards. Desktop appearance should still be reviewed at several widths before final presentation.
- Recheck SOS admin removal end to end in the browser; an earlier automated pass did not reliably verify that action.
- Invitation delivery is manual (link/QR display, no transactional email/SMS). Phone camera scanning needs an address reachable from the phone and generally HTTPS.

## Immediate instruction for the next assistant

First inspect `git status`, the current branch, and the local database before changing anything. Do not assume the GitHub remote includes the local commits or the demo database. Address the owner’s next request using the current implementation, then run focused verification for the changed flow and update `README.md` or this handoff when behavior changes.
