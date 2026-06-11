-- Supabase/Postgres schema adapted from elearning.sql

create table if not exists prodi (
  id serial primary key,
  nama_prodi varchar(100)
);

create table if not exists matkul (
  id serial primary key,
  prodi_id int references prodi(id),
  nama_matkul varchar(100)
);

create table if not exists users (
  id serial primary key,
  nama varchar(100),
  username varchar(100) unique,
  password varchar(255),
  role varchar(50),
  prodi_id int references prodi(id),
  matkul_id int references matkul(id),
  umur int,
  no_hp varchar(20),
  agama varchar(50),
  alamat text,
  nisn varchar(20),
  nidn varchar(20),
  created_at timestamptz default now()
);

create table if not exists materi (
  id serial primary key,
  judul varchar(100),
  file varchar(255),
  matkul_id int references matkul(id)
);

create table if not exists tugas (
  id serial primary key,
  judul varchar(100),
  deskripsi text,
  deadline date,
  file varchar(255),
  matkul_id int references matkul(id)
);

create table if not exists pengumpulan_tugas (
  id serial primary key,
  tugas_id int references tugas(id),
  mahasiswa_id int,
  file varchar(255),
  tanggal timestamptz default now()
);

create table if not exists tugas_kumpul (
  id serial primary key,
  tugas_id int references tugas(id),
  mahasiswa varchar(100),
  file varchar(255),
  tanggal timestamptz default now()
);

create table if not exists presensi (
  id serial primary key,
  tanggal date,
  status varchar(20),
  matkul_id int references matkul(id)
);

create table if not exists presensi_mahasiswa (
  id serial primary key,
  presensi_id int references presensi(id),
  mahasiswa varchar(100),
  keterangan varchar(20),
  waktu time default current_time
);

create table if not exists payments (
  id serial primary key,
  student_id int references users(id),
  amount numeric(12,2),
  description varchar(255),
  status varchar(50) default 'pending',
  payment_method varchar(50),
  transaction_id varchar(100),
  payment_date timestamptz default now(),
  confirmed_by int references users(id),
  confirmed_date timestamptz,
  notes text
);

create table if not exists aktivitas (
  id serial primary key,
  user_id int references users(id),
  nama varchar(100),
  role varchar(50),
  aktivitas text,
  created_at timestamptz default now()
);

create table if not exists website_settings (
  id serial primary key,
  nama_website varchar(255),
  deskripsi text,
  alamat text,
  telepon varchar(50),
  email varchar(100),
  facebook varchar(255),
  twitter varchar(255),
  instagram varchar(255),
  jam_buka varchar(50),
  jam_tutup varchar(50),
  updated_at timestamptz default current_timestamp()
);

insert into website_settings (id, nama_website, deskripsi, alamat, telepon, email) values (1, 'E-Learning', 'Platform pembelajaran online', 'Jl. Pendidikan No. 123', '(021) 1234-5678', 'info@elearning.com') on conflict (id) do nothing;
