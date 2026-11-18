CREATE TABLE info_lomba (
    id SERIAL PRIMARY KEY,
    admin_id INT REFERENCES users(id) ON DELETE SET NULL, -- Mengetahui admin siapa yang memposting
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    kategori VARCHAR(255), -- Contoh isi: "Hackathon, Business Plan"
    tanggal_penutupan DATE NOT NULL,
    poster VARCHAR(255), -- Menyimpan nama file gambar poster lomba
    link_detail TEXT, -- Link eksternal pendaftaran lomba aslinya
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);