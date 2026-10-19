# 🚗 SmartPark — Real-Time Smart Parking System

A modern, intelligent, responsive smart parking management platform that reduces fuel wastage, traffic congestion, and vehicular emissions caused by drivers searching for parking.

## ✨ Features
- Real-time parking availability
- Smart AI recommendations
- Interactive maps (Leaflet/OpenStreetMap)
- QR-based reservation entry/exit
- Online payments (UPI, Card, Wallet, Cash)
- Environmental impact dashboard (Fuel saved, CO₂ avoided)
- Admin panel with analytics & monitoring
- Notifications, reviews, parking history
- Dark/Light mode, fully responsive

## 🛠 Tech Stack
- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript, Chart.js, Leaflet
- **Backend:** PHP 8+
- **Database:** MySQL
- **Auth:** Secure PHP sessions, bcrypt, optional Google OAuth

## 🚀 Installation

1. Clone repo into `htdocs/` (XAMPP) or `www/` (WAMP).
2. Create MySQL database `smart_parking` and import `database/schema.sql` then `database/seed.sql`.
3. Copy `.env.example` to `.env` and configure DB credentials.
4. Run `composer install`.
5. Ensure `uploads/` is writable: `chmod -R 775 uploads/`.
6. Visit `http://localhost/smart-parking-system/`.

## 👤 Default Credentials
- **Admin:** `admin@smartpark.com` / `password123`
- **User:** `rahul@example.com` / `password123`

## 🌍 Environmental Assumptions (editable in `config/config.php`)
- Fuel per km: 0.08 L
- CO₂ per liter: 2.31 kg
- Avg search distance saved: 1.5 km
- Avg search time saved: 8 min

## 📁 Key Directories
- `api/` — REST endpoints
- `user/` — User-facing pages
- `admin/` — Admin panel
- `config/` — DB & site config
- `includes/` — Reusable PHP partials
- `database/` — SQL schema & seed
- `assets/` — CSS, JS, images

## 🔒 Security
- Password hashing (bcrypt)
- CSRF tokens on all POST routes
- Prepared statements (SQL injection safe)
- Role-based access control
- Audit logging
- Session regeneration on login

## 📈 Roadmap for IoT Integration
- Slot sensor endpoint: `POST /api/iot/slot-status`
- Camera/LPR webhook: `POST /api/iot/lpr-entry`
- WebSocket server (Ratchet) for live updates

## 📝 License
MIT