-- 2. Tabel Profil Mahasiswa (Skill & Portofolio)
CREATE TABLE profil_mahasiswa (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    skill TEXT,
    portofolio TEXT,
    is_complete BOOLEAN DEFAULT FALSE
);