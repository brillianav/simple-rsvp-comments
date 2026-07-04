# Simple RSVP Comments

Simple RSVP Comments adalah plugin WordPress untuk menampilkan form RSVP dan daftar ucapan tamu. Form dikirim melalui AJAX, sehingga ucapan baru bisa tersimpan dan daftar komentar diperbarui tanpa refresh halaman.

Plugin ini cocok untuk halaman undangan, event, atau guestbook sederhana.

## Fitur

- Shortcode form RSVP: `[simple_rsvp_comments]`
- Alias shortcode: `[rsvp_form]`
- Input nama, status kehadiran, dan komentar atau ucapan.
- Opsi kehadiran: `Hadir`, `Tidak Hadir`, dan `Masih Ragu`.
- Submit via AJAX tanpa reload halaman.
- Daftar ucapan dengan pagination.
- Data RSVP disimpan sebagai custom post type WordPress.
- Halaman admin `RSVP Entries` untuk melihat dan mengedit data RSVP.
- Kolom admin khusus untuk status kehadiran.
- Meta box admin untuk mengubah status kehadiran.
- Styling frontend menggunakan file CSS terpisah.

## Struktur File

```text
simple-rsvp-comments/
|-- README.md
|-- simple-rsvp-comments.php
`-- assets/
    |-- simple-rsvp-comments.css
    `-- simple-rsvp-comments.js
```

## Instalasi

1. Download atau clone repository ini.
2. Salin folder `simple-rsvp-comments` ke direktori plugin WordPress:

```text
wp-content/plugins/simple-rsvp-comments/
```

3. Masuk ke dashboard WordPress.
4. Buka menu `Plugins`.
5. Aktifkan plugin `Simple RSVP Comments`.

## Penggunaan

Tambahkan shortcode berikut ke halaman atau post WordPress:

```text
[simple_rsvp_comments]
```

Shortcode alias juga tersedia:

```text
[rsvp_form]
```

Secara default, daftar ucapan menampilkan 5 item per halaman. Jumlah item per halaman bisa diubah dengan atribut `per_page`:

```text
[simple_rsvp_comments per_page="10"]
```

## Cara Kerja

Saat plugin aktif, WordPress akan mendaftarkan custom post type bernama `simple_rsvp_entry`. Setiap RSVP yang dikirim akan disimpan sebagai post dengan:

- Judul post: nama tamu.
- Konten post: komentar atau ucapan.
- Meta `_src_attendance`: status kehadiran.

Frontend menggunakan AJAX WordPress melalui `admin-ajax.php` untuk:

- Mengirim RSVP baru dengan action `simple_rsvp_submit`.
- Memuat daftar ucapan dengan action `simple_rsvp_load`.

## Prefill Nama Tamu

Plugin akan mencoba membaca shortcode `[to_name]` jika shortcode tersebut tersedia di website. Jika ada, nilai tersebut otomatis digunakan sebagai isi awal field nama.

Fitur ini berguna untuk halaman undangan yang sudah memiliki shortcode nama tamu dari plugin atau sistem lain.

## Styling

Style frontend berada di:

```text
assets/simple-rsvp-comments.css
```

Script frontend berada di:

```text
assets/simple-rsvp-comments.js
```

CSS menggunakan font family `Born2` dan `"Merchant Copy"`. Pastikan font tersebut sudah tersedia dari theme atau builder yang digunakan. Jika belum tersedia, browser akan memakai fallback font.

## Keamanan dan Validasi

Plugin menggunakan:

- WordPress nonce untuk request AJAX.
- Sanitasi input dengan fungsi bawaan WordPress.
- Validasi pilihan kehadiran.
- Permission check saat menyimpan meta box di admin.

## Kebutuhan

- WordPress.
- jQuery bawaan WordPress.
- PHP dengan dukungan fungsi WordPress standar.

## Lisensi

Belum ditentukan.
