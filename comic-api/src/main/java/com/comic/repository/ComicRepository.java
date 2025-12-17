package com.comic.repository;

import com.comic.model.Comic;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;

public interface ComicRepository extends JpaRepository<Comic, Long> {
    
    // Mencari berdasarkan Title (Judul)
    // Spring otomatis membuat query: SELECT * FROM tb_komik WHERE lower(title) LIKE %keyword%
    List<Comic> findByTitleContainingIgnoreCase(String title);
}