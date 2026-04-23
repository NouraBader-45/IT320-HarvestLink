# HarvestLink — Agricultural Surplus Donation Platform 🌾
HarvestLink is a role-based web platform that connects farmers with surplus agricultural products to charitable organizations that need them.

## Roles
- **Farmer**: register/login, manage profile, add/edit/delete products, review incoming requests (approve/reject)
- **Charity**: register/login, manage profile, browse/search listings, submit requests, track request status
- **Admin**: login, dashboard statistics, block/unblock users and product listings

## Scope Preserved
This implementation is aligned with the approved project logic:
- client-server architecture
- no payment, delivery/logistics, storage, or third-party service modules
- direct registration for Farmer and Charity only
- Admin is not publicly registerable
- role-based login redirects
- profile data stored in `users` table (with default profile image fallback)

## Tech Stack
- Front-end: HTML/CSS/Vanilla JS
- Back-end: Node.js + Express
- Database: SQLite (`better-sqlite3`)
- Auth: Session-based auth + `bcryptjs` password hashing
- Uploads: `multer` for profile/product images

## Run Locally
1. Install dependencies:
   ```bash
   npm install
   ```
2. Initialize database:
   ```bash
   npm run init-db
   ```
3. Start server:
   ```bash
   npm start
   ```
4. Open:
   - `http://localhost:3000`

## Default Admin Account
- Email: `admin@harvestlink.local`
- Password: `Admin@123`

## Database Files
- Schema: `db/schema.sql`
- Runtime DB: `db/harvestlink.db` (created after initialization)

## Main API Areas
- `/api/auth` (register/login/logout/session)
- `/api/profile` (read/update profile + image upload)
- `/api/products` (listing CRUD and browse)
- `/api/requests` (submit/track/respond)
- `/api/notifications` (role notifications)
- `/api/admin` (stats, moderation)