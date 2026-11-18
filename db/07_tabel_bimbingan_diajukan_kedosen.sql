-- 7. Tabel Pengajuan Bimbingan (Diajukan ke Dosen)
CREATE TABLE pengajuan_bimbingan (
    id SERIAL PRIMARY KEY,
    tim_id INT REFERENCES tim_lomba(id) ON DELETE CASCADE,
    dosen_id INT REFERENCES users(id) ON DELETE CASCADE,
    proposal TEXT,
    status VARCHAR(20) DEFAULT 'pending', -- pending, diterima, ditolak
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);