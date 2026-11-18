-- 6. Tabel Lamaran Anggota (Jalur Kandidat)
CREATE TABLE lamaran_anggota (
    id SERIAL PRIMARY KEY,
    tim_id INT REFERENCES tim_lomba(id) ON DELETE CASCADE,
    role_id INT REFERENCES kebutuhan_role(id) ON DELETE CASCADE,
    kandidat_id INT REFERENCES users(id) ON DELETE CASCADE,
    pesan_lamaran TEXT,
    status VARCHAR(20) DEFAULT 'pending', -- pending, diterima, ditolak
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);