PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS donation_requests;
DROP TABLE IF EXISTS surplus_products;
DROP TABLE IF EXISTS charitable_organizations;
DROP TABLE IF EXISTS farmers;
DROP TABLE IF EXISTS administrators;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  user_id INTEGER PRIMARY KEY AUTOINCREMENT,
  full_name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL CHECK (role IN ('farmer','charity','admin')),
  account_status TEXT NOT NULL DEFAULT 'active' CHECK (account_status IN ('active','blocked')),
  phone_number TEXT,
  address TEXT,
  profile_image TEXT NOT NULL DEFAULT 'assets/images/default-profile.png',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE farmers (
  farmer_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL UNIQUE,
  farm_location TEXT,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE charitable_organizations (
  charity_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL UNIQUE,
  organization_name TEXT NOT NULL,
  organization_type TEXT,
  contact_number TEXT,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE administrators (
  admin_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL UNIQUE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE surplus_products (
  product_id INTEGER PRIMARY KEY AUTOINCREMENT,
  farmer_id INTEGER NOT NULL,
  crop_type TEXT NOT NULL,
  quantity REAL NOT NULL CHECK (quantity > 0),
  expiration_date TEXT NOT NULL,
  product_condition TEXT NOT NULL CHECK (product_condition IN ('Fresh','Near Expiry')),
  image TEXT,
  product_status TEXT NOT NULL DEFAULT 'Available' CHECK (product_status IN ('Available','Blocked','Deleted')),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE donation_requests (
  request_id INTEGER PRIMARY KEY AUTOINCREMENT,
  product_id INTEGER NOT NULL,
  charity_id INTEGER NOT NULL,
  requested_quantity REAL NOT NULL CHECK (requested_quantity > 0),
  request_status TEXT NOT NULL DEFAULT 'Pending' CHECK (request_status IN ('Pending','Approved','Rejected','Delivered')),
  request_date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  decision_date TEXT,
  delivered_date TEXT,
  FOREIGN KEY (product_id) REFERENCES surplus_products(product_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (charity_id) REFERENCES charitable_organizations(charity_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE notifications (
  notification_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  request_id INTEGER,
  message TEXT NOT NULL,
  notification_date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_read INTEGER NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (request_id) REFERENCES donation_requests(request_id) ON DELETE SET NULL ON UPDATE CASCADE
);