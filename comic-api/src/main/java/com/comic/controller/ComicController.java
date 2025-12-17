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

    // GET: Cari komik (?title=Naruto)
    @GetMapping("/search")
    public List<Comic> search(@RequestParam String title) {
        return service.search(title);
    }

    // POST: Tambah data baru
    @PostMapping
    public Comic create(@RequestBody Comic comic) {
        return service.create(comic);
    }

    // PUT: Update data
    @PutMapping("/{id}")
    public Comic update(@PathVariable Long id, @RequestBody Comic comic) {
        return service.update(id, comic);
    }

    // DELETE: Hapus data
    @DeleteMapping("/{id}")
    public void delete(@PathVariable Long id) {
        service.delete(id);
    }
}