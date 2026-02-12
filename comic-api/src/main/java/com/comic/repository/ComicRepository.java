package com.comic.repository;

import com.comic.model.Comic;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;

public interface ComicRepository extends JpaRepository<Comic, Long> {
    
    // Mencari berdasarkan Title (Judul)
    List<Comic> findByTitleContainingIgnoreCase(String title);
}