# Meridian Hospital Chattogram — Appointment Booking System

A full PHP + MySQL hospital appointment website: a patient-facing booking
site (light-green theme) and a full admin panel, built from your PRD.

---

## 1. Requirements

- PHP 8.0+ with `pdo_mysql` extension enabled
- MySQL 5.7+ / MariaDB 10.3+
- Apache or Nginx with PHP-FPM (any standard shared host or XAMPP/LAMP/WAMP works)

## 2. Installation

1. **Upload the `site/` folder contents** to your web root (e.g. `public_html/` or `htdocs/`).
2. **Create the database** — import the schema in this order using phpMyAdmin, Adminer, or the `mysql` CLI:
   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p meridian_hospital < database/seed_doctors.sql
   ```
   `schema.sql` creates all tables, the default admin account, and the 6 top-level
   specialty categories. `seed_doctors.sql` imports all **94 doctors** and their
   **25 sub-specialties** from the CSV export you provided
   (`database/data/doctors.csv` / `subcategories.csv`).
3. **Configure the database connection** in `config/db.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'meridian_hospital');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   ```
4. **Make the uploads folder writable** (for doctor photos added via the admin panel):
   ```bash
   chmod -R 755 uploads/
   ```
5. Visit `https://yourdomain.com/` for the patient site and
   `https://yourdomain.com/admin/login.php` for the admin panel.

## 3. Default Admin Login

| Field    | Value                          |
|----------|---------------------------------|
| URL      | `/admin/login.php`             |
| Email    | `Mohammadsajid1114@gmail.com`  |
| Password | `Meridian@2026`                |

**Change this password immediately after your first login** (via `admin/users.php`,
or directly in the database). The password hash is bcrypt — use PHP's
`password_hash()` to generate a new one if editing the database directly.

## 4. Re-importing / Updating Doctors from a New CSV

If you export an updated doctor list from your dashboard again:
1. Replace `database/data/doctors.csv` (and `subcategories.csv` if changed) with your new export — **keep the same column order** as the original.
2. Run:
   ```bash
   php database/import_doctors.php
   ```
   This adds any new doctors/sub-categories found in the CSV (it does not
   delete existing ones). Alternatively, use the **Add Doctor** form in the
   admin panel to add doctors one at a time — every field from your CSV
   (category, sub-category, degree, BMDC number, fee, availability, etc.)
   is represented there.

## 5. What's Included

### Patient-facing site (light-green theme)
- Home, All Doctors (specialty filter), Doctor Profile with live date/time
  slot picker, multi-step booking flow with confirmation + success screen
- Patient registration & login (shared `users` table with the admin system)
- "My Appointments" dashboard with cancel option
- About, Contact (with your real hospital info), responsive + animated UI

### Admin panel (`/admin/`)
- Separate admin login using **Gmail + password**, sharing the same `users`
  table/login mechanism as patients (role-based, not a separate database)
- Dashboard with animated stat cards + 7-day appointment trend chart
- **Doctor List**: search, status control (Approved/Pending/Hold/Banned),
  editing-lock toggle, CSV/Excel/Print export, edit/delete
- **Add / Edit Doctor**: every field from your CSV (category, sub-category,
  degree, higher degree, BMDC number, work place, fee, experience, about,
  priority, availability days/times/consultation length, image + signature upload)
- **Categories**: manage main specialties and the 25 "Specialist" sub-categories
- **Patients**: list of all registered patients with contact info & appointment count
- **Users**: manage admin/staff accounts (add, enable/disable)
- **Appointments**: analytics cards (All/Completed/Confirmed/Pending/Cancelled),
  date filter, search, per-row status control, export

## 6. Security Notes

- Passwords are hashed with bcrypt (`password_hash()` / `password_verify()`).
- All forms are CSRF-protected.
- All SQL uses parameterized PDO queries.
- `config/`, `database/` folders are blocked from direct web access via `.htaccess`.
- Set `display_errors` to `0` in `config/config.php` before going live.
- Double-booking is prevented at the database level (unique constraint on
  doctor + date + time).

## 7. Folder Structure

```
site/
├── admin/                 # Admin panel (dashboard, doctors, categories, patients, users, appointments)
├── assets/                # CSS, JS, images (logo, doctor photos, specialty icons)
├── config/                # config.php (site settings), db.php (DB credentials)
├── database/              # schema.sql, seed_doctors.sql, import_doctors.php, source CSVs
├── includes/               # Shared header/footer/functions for the patient site
├── uploads/doctors/       # Doctor photos/signatures uploaded via the admin panel
├── index.php, doctors.php, doctor.php, book-appointment.php, ...
└── README.md
```

---

**Meridian Hospital Chattogram** — Serving with Care & Compassion.
1367 CDA Avenue, GEC Circle, Chattogram, Bangladesh · 01622295857 · Mohammadsajid1114@gmail.com
