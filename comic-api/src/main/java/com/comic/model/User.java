package com.comic.model;

import jakarta.persistence.*;
import lombok.Data;
import java.time.LocalDate;

@Entity
@Table(name = "tb_user") // Mapping ke tabel yang sudah ada
@Data
public class User {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "user_id")
    private Long userId; // Primary Key (Long, bukan String)

    @Column(name = "username")
    private String username;

    @Column(name = "email")
    private String email;

    @Column(name = "password")
    private String password;

    @Column(name = "role")
    private String role;

    // Kolom ini boleh kosong (nullable)
    @Column(name = "profile_picture_url")
    private String profilePictureUrl;

    @Column(name = "created_at")
    private LocalDate createdAt;

    @PrePersist
    protected void onCreate() {
        // Jika tanggal kosong, otomatis isi dengan tanggal hari ini
        if (this.createdAt == null) {
            this.createdAt = LocalDate.now();
        }
        // Jika role kosong, otomatis set jadi "user" biasa
        if (this.role == null) {
            this.role = "user";
        }
    }
}