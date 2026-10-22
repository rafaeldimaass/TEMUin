CREATE TABLE IF NOT EXISTS tim (
    id SERIAL PRIMARY KEY,
    ketua_id INT NOT NULL, -- ID mahasiswa yang membuat tim
    nama_lomba VARCHAR(150) NOT NULL,
    nama_tim VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    jumlah_anggota INT NOT NULL,
    skills_dibutuhkan VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);