-- Active: 1717089864265@@127.0.0.1@3306


    CREATE DATABASE perpustakaan;

USE perpustakaan;



CREATE TABLE mahasiswa (
    npm VARCHAR(50) NOT NULL PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL
);


CREATE TABLE buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255),
    penulis VARCHAR(255),
    tahun_terbit INT,
    tersedia BOOLEAN DEFAULT TRUE
);

CREATE TABLE peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    npm VARCHAR(20),
    buku_id INT,
    tanggal_pinjam DATETIME NOT NULL,    
    tanggal_kembali DATETIME NOT NULL,
    FOREIGN KEY (npm) REFERENCES mahasiswa(npm),
    FOREIGN KEY (buku_id) REFERENCES buku(id)
); information_schema

ALTER TABLE peminjaman ADD COLUMN keterangan VARCHAR(255) NOT NULL;

ALTER TABLE buku
MODIFY COLUMN judul VARCHAR NOT NULL;

ALTER TABLE buku
ADD COLUMN tahun_terbit INT after penulis;

ALTER TABLE buku  
DROP COLUMN tahun_terbit;

ALTER TABLE mahasiswa
DROP PRIMARY KEY, -- Hapus kunci primer yang sudah ada
ADD PRIMARY KEY (npm); -- Tentukan kombinasi dari npm dan id_mahasiswa sebagai kunci primer

ALTER TABLE mahasiswa
DROP PRIMARY KEY, -- Hapus kunci primer yang sudah ada
MODIFY COLUMN id_mahasiswa INT AUTO_INCREMENT FIRST, -- Tambahkan kolom id_mahasiswa dengan auto-increment
ADD PRIMARY KEY (npm, id_mahasiswa); -- Tentukan kombinasi dari npm dan id_mahasiswa sebagai kunci primer


DESC mahasiswa;

DROP TABLE mahasiswa;

DROP DATABASE perpustakaan;

-- Mengubah semua data yang ada di kolom tanggal_pinjam dan tanggal_kembali ke GMT+7
UPDATE peminjaman
SET tanggal_pinjam = CONVERT_TZ(tanggal_pinjam, '+00:00', '-09:00'),
    tanggal_kembali = CONVERT_TZ(tanggal_kembali, '+00:00', '+05:00');

    UPDATE peminjaman
SET tanggal_pinjam = DATE_FORMAT(tanggal_pinjam, '%W, %d %M %Y'),
    tanggal_kembali = DATE_FORMAT(tanggal_kembali, '%W, %d %M %Y');



DROP COLOUMN id_mahasiswa;  

SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_NAME = 'mahasiswa';

ALTER TABLE peminjaman
ADD tanggal_pinjam DATETIME DEFAULT NULL,
ADD tanggal_kembali DATETIME DEFAULT NULL;
