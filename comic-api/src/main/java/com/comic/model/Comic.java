package com.comic.model;

import jakarta.persistence.*;
import lombok.Data;
import java.time.LocalDate; // Untuk format tanggal (YYYY-MM-DD)

@Entity
@Table(name = "tb_komik") // Sesuai nama tabel di database
@Data
public class Comic {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "komik_id") // Mapping ke kolom Primary Key database
    private Long id;

    @Column(name = "title")
    private String title;

    @Column(name = "author")
    private String author;

    @Column(name = "cover_image") // Mapping ke kolom snake_case
    private String coverImage;

    @Column(name = "description", columnDefinition = "TEXT") // Agar muat teks panjang
    private String description;

    @Column(name = "status")
    private String status; // ongoing / completed

    @Column(name = "first_chapter_at")
    private LocalDate firstChapterAt; // Tipe data tanggal
}