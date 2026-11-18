-- 5. Tabel Kebutuhan Role Tim (Lowongan yang dibuka)
CREATE TABLE kebutuhan_role (
    id SERIAL PRIMARY KEY,
    tim_id INT REFERENCES tim_lomba(id) ON DELETE CASCADE,
    nama_role VARCHAR(100) NOT NULL,
    deskripsi_role TEXT,
    kuota INT DEFAULT 1,
    terisi INT DEFAULT 0
);
