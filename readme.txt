flipbook-app/
├─ admin/
│ ├─ index.php # dashboard + upload form + list
│ ├─ login.php # login page
│ ├─ logout.php # logout
│ ├─ guard.php # session guard helper
├─ assets/
│ └─ style.css # simple CSS
├─ data/
│ └─ app.db # SQLite database (auto-created by init_db.php)
├─ uploads/ # uploaded PDFs (protected from script execution)
│ └─ .htaccessflipbook-app/
├─ admin/
│ ├─ index.php # dashboard + upload form + list


├─ view.php # public flipbook viewer
├─ init_db.php # one-time DB initializer
├─ config.php # config (site URL, admin user)
└─ .htaccess # route hardening & security