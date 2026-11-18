-- 3. Tabel Profil Dosen (Kepakaran)
CREATE TABLE profil_dosen (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    bidang_kepakaran VARCHAR(255),
    is_complete BOOLEAN DEFAULT FALSE
);