-- 4. Tabel Tim Lomba (Dibuat oleh Ketua Tim)
CREATE TABLE tim_lomba (
    id SERIAL PRIMARY KEY,
    ketua_id INT REFERENCES users(id) ON DELETE CASCADE,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    kategori VARCHAR(100),
    status_tim VARCHAR(50) DEFAULT 'open', -- open, closed, banned (oleh admin)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE tim_lomba 
ADD COLUMN gambar VARCHAR(255),
ADD COLUMN skills_dibutuhkan TEXT,
ADD COLUMN jumlah_anggota INT,
ADD COLUMN link_portofolio TEXT,
ADD COLUMN tanggal_penutupan DATE;