---

```markdown
# 📚 Flipbook PDF App -- Web Based

Flipbook Library adalah aplikasi manajemen dokumen berbasis web yang dilengkapi dengan fitur **unggah PDF**, **flipbook viewer (DearFlip)**, serta **dashboard admin (AdminLTE)** untuk mengelola dokumen, jenis dokumen, dan pengaturan sistem perpustakaan.

---

## ✨ Fitur Utama

- **Frontend**
  - Tampilan katalog modern (grid + cover).
  - Pencarian dokumen berdasarkan judul.
  - Filter berdasarkan **Jenis Dokumen** (kategori).
  - Flipbook viewer interaktif menggunakan **DearFlip**.
  - Tampilan responsif (mobile friendly).

- **Backend (Admin)**
  - Login aman (bcrypt hash).
  - Dashboard dengan statistik dokumen per jenis.
  - CRUD dokumen:
    - Judul
    - Deskripsi
    - Jenis dokumen
    - Cover (gambar)
    - File PDF
  - CRUD Jenis Dokumen.
  - CRUD Pengaturan Sistem:
    - Nama Perpustakaan
    - Tagline
    - Alamat
    - Email
    - Telepon
  - Tampilan Admin menggunakan **AdminLTE**.

---

## 🛠️ Teknologi

- **PHP 8+** dengan PDO (MySQL).
- **MySQL/MariaDB** sebagai database.
- **AdminLTE** untuk template dashboard.
- **DearFlip (dFlip)** untuk flipbook.
- **jQuery** untuk kebutuhan JavaScript dasar.
- **Bootstrap 4** (dibundel dengan AdminLTE).

---

## 📂 Struktur Direktori

```

flipbook/
├── admin/           # Panel admin (CRUD dokumen, jenis, setting)
│   ├── index.php    # Dashboard
│   ├── login.php    # Login admin
│   ├── menus.php    # Sidebar menu
│   ├── types.php    # Manajemen jenis dokumen
│   ├── settings.php # Pengaturan sistem
│   └── ...
├── assets/          # Asset statis (CSS, JS, icon, cover placeholder)
│   ├── vendor/      # AdminLTE, DearFlip, jQuery dll
│   └── style.css
├── uploads/         # File PDF & cover
│   └── covers/
├── config.php       # Konfigurasi DB & helper
├── index.php        # Halaman depan (frontend katalog)
├── view\.php         # Halaman baca dokumen (flipbook)
└── README.md

````

---

## ⚙️ Instalasi

1. Clone repo ini:

   ```bash
   git clone https://github.com/username/flipbook-library.git
   cd flipbook-library
````

2. Buat database MySQL dan import schema:

   ```sql
   CREATE DATABASE flipbook;
   USE flipbook;

   -- tabel users
   CREATE TABLE users (
     id INT AUTO_INCREMENT PRIMARY KEY,
     username VARCHAR(50) UNIQUE NOT NULL,
     password_hash VARCHAR(255) NOT NULL
   );

   -- tabel doc_types
   CREATE TABLE doc_types (
     id INT AUTO_INCREMENT PRIMARY KEY,
     name VARCHAR(100) NOT NULL
   );

   -- tabel documents
   CREATE TABLE documents (
     id INT AUTO_INCREMENT PRIMARY KEY,
     title VARCHAR(255) NOT NULL,
     slug VARCHAR(255) UNIQUE NOT NULL,
     filename VARCHAR(255) NOT NULL,
     description TEXT,
     cover VARCHAR(255),
     type_id INT,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     FOREIGN KEY (type_id) REFERENCES doc_types(id) ON DELETE SET NULL
   );

   -- tabel settings
   CREATE TABLE settings (
     name VARCHAR(100) PRIMARY KEY,
     value TEXT
   );
   ```

3. Tambahkan akun admin:

   ```php
   INSERT INTO users (username, password_hash)
   VALUES ('admin', '<?= password_hash("admin123", PASSWORD_BCRYPT) ?>');
   ```

4. Sesuaikan konfigurasi database di `config.php`.

5. Pastikan folder `uploads/` dan `uploads/covers/` bisa ditulis (permission 775/777).

6. Jalankan di browser:

   ```
   http://localhost/flipbook
   http://localhost/flipbook/admin
   ```

---

## 📖 Cara Pakai

* **Admin**

  * Login di `/admin/login.php`
  * Unggah dokumen baru, cover, atur jenis dokumen, setting perpustakaan.
* **Frontend**

  * Lihat katalog dokumen di `/index.php`
  * Klik dokumen untuk membuka flipbook interaktif.

---

## 📸 Screenshots

* **Frontend**

  * Katalog dokumen dengan cover.
  * Flipbook interaktif.
* **Backend**

  * Dashboard statistik.
  * Form unggah dokumen.
  * Manajemen jenis & setting.

*(Tambahkan screenshot sesuai kebutuhan)*

---

## 📜 Lisensi

Proyek ini bersifat open source dengan lisensi [MIT](LICENSE).
DearFlip yang digunakan bersifat **non-commercial lite version**.

---

## 👨‍💻 Kontributor

* Dibangun oleh **\Erwan Setyo Budi]**
* Email: \[erwans818@gmail.com]

---

```

---

