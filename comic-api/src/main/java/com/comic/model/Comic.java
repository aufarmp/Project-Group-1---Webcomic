package com.comic.model;

import jakarta.persistence.*;
import lombok.Data;
import java.time.LocalDate;

@Entity
@Table(name = "tb_komik") // Sesuai nama tabel
@Data
public class Comic {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "komik_id") // Sesuai nama kolom PK
    private Long id;

    @Column(name = "title", nullable = false)
    private String title;

    @Column(name = "author", nullable = false)
    private String author;

    @Column(name = "cover_image") // Bisa null
    private String coverImage;

    @Column(name = "description", columnDefinition = "TEXT", nullable = false)
    private String description;

    @Column(name = "status", nullable = false)
    private String status; 
    // Catatan: Wajib diisi "ongoing" atau "completed" persis (huruf kecil)

    @Column(name = "first_chapter_at", nullable = false)
    private LocalDate firstChapterAt; 
    // Catatan: Format JSON harus "YYYY-MM-DD"
}