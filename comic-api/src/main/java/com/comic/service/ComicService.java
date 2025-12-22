package com.comic.service;

import com.comic.model.Comic;
import com.comic.repository.ComicRepository;
import org.springframework.stereotype.Service;
import java.util.List;
import java.util.Optional;

@Service
public class ComicService {

    private final ComicRepository comicRepo;

    public ComicService(ComicRepository comicRepo) {
        this.comicRepo = comicRepo;
    }

    public List<Comic> getAll() {
        return comicRepo.findAll();
    }

    public Optional<Comic> getById(Long id) {
        return comicRepo.findById(id);
    }

    public Comic create(Comic comic) {
        return comicRepo.save(comic);
    }

    public Comic update(Long id, Comic comic) {
        return comicRepo.save(comic);
    }

    public void delete(Long id) {
        comicRepo.deleteById(id);
    }

    public List<Comic> search(String keyword) {
        return comicRepo.findByTitleContainingIgnoreCase(keyword);
    }
}