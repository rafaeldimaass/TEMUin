CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'mahasiswa'
);

-- 1. Insert data untuk role Mahasiswa
INSERT INTO users (nama, email, password, role) 
VALUES ('Ahmad Mahasiswa', 'mahasiswa@polinema.ac.id', '$2y$10$ContohHashPasswordMahasiswaAman123', 'mahasiswa');

-- 2. Insert data untuk role Dosen (Pembimbing)
INSERT INTO users (nama, email, password, role) 
VALUES ('Dr. Budi Dosen, M.Kom.', 'dosen@polinema.ac.id', '$2y$10$ContohHashPasswordDosenAman123', 'dosen');

-- 3. Insert data untuk role Admin
INSERT INTO users (nama, email, password, role) 
VALUES ('Administrator TEMUin', 'admin@polinema.ac.id', '$2y$10$ContohHashPasswordAdminAman123', 'admin');