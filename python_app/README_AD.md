# Konfigurasi Iklan Pendaftaran Kampus

Untuk menampilkan iklan di aplikasi, set variabel environment berikut di file .env pada folder python_app:

```env
CAMPUS_AD_URL=https://alamat-url-iklan-anda
```

URL tersebut bisa mengembalikan:
- JSON dengan field title, description, link, image_url
- atau teks biasa

Contoh file JSON tersedia di: campus_ad_example.json
