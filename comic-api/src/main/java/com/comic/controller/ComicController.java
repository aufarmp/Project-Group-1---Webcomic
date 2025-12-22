package com.comic.controller;

import com.comic.model.Comic;
import com.comic.service.ComicService;
import org.springframework.web.bind.annotation.*;
import java.util.List;

@RestController
@RequestMapping("/api/comics")
@CrossOrigin(origins = "*") // Penting agar PHP local bisa akses
public class ComicController {

    private final ComicService service;

    public ComicController(ComicService service) {
        this.service = service;
    }

    // GET: Ambil semua data
    @GetMapping
    public List<Comic> getAll() {
        return service.getAll();
    }

    // GET: Ambil satu data by ID
    @GetMapping("/{id}")
    public Comic getById(@PathVariable Long id) {
        return service.getById(id).orElse(null);
    }

    // GET: Cari komik (?title=Shang-ri La)
    @GetMapping("/search")
    public List<Comic> search(@RequestParam String title) {
        return service.search(title);
    }

    // POST: Tambah data baru
    @PostMapping
    public Comic create(@RequestBody Comic comic) {
        // Validasi Status saat Create
        if (comic.getStatus() != null && 
           (!"ongoing".equals(comic.getStatus()) && !"completed".equals(comic.getStatus()))) {
             throw new RuntimeException("Status harus 'ongoing' atau 'completed'");
        }
        return service.create(comic);
    }

    // PUT: Update data (IMPLEMENTASI BARU: FETCH & MERGE)
    @PutMapping("/{id}")
    public Comic update(@PathVariable Long id, @RequestBody Comic req) {
        // 1. Ambil data asli dari database
        Comic dbComic = service.getById(id)
                .orElseThrow(() -> new RuntimeException("Komik tidak ditemukan dengan ID: " + id));

        // 2. Cek satu per satu field. Jika ada isinya di Request, timpa data lama.
        // Jika null (tidak dikirim), biarkan data lama tetap ada.
        
        if (req.getTitle() != null) {
            dbComic.setTitle(req.getTitle());
        }
        if (req.getAuthor() != null) {
            dbComic.setAuthor(req.getAuthor());
        }
        if (req.getCoverImage() != null) {
            dbComic.setCoverImage(req.getCoverImage());
        }
        if (req.getDescription() != null) {
            dbComic.setDescription(req.getDescription());
        }
        if (req.getFirstChapterAt() != null) {
            dbComic.setFirstChapterAt(req.getFirstChapterAt());
        }

        // Khusus Status: Validasi dulu sebelum update
        if (req.getStatus() != null) {
            if ("ongoing".equals(req.getStatus()) || "completed".equals(req.getStatus())) {
                dbComic.setStatus(req.getStatus());
            } else {
                throw new RuntimeException("Status salah! Gunakan 'ongoing' atau 'completed'");
            }
        }

        // 3. Simpan kembali objek yang sudah digabungkan menggunakan method dari ComicService.java
        return service.create(dbComic); 
    }

    // DELETE: Hapus data
    @DeleteMapping("/{id}")
    public void delete(@PathVariable Long id) {
        service.delete(id);
    }
}