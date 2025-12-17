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

    public Comic update(Long id, Comic comicDetails) {
        return comicRepo.findById(id).map(comic -> {
            comic.setTitle(comicDetails.getTitle());
            comic.setAuthor(comicDetails.getAuthor());
            comic.setCoverImage(comicDetails.getCoverImage());
            comic.setDescription(comicDetails.getDescription());
            comic.setStatus(comicDetails.getStatus());
            comic.setFirstChapterAt(comicDetails.getFirstChapterAt());
            return comicRepo.save(comic);
        }).orElse(null);
    }

    public void delete(Long id) {
        comicRepo.deleteById(id);
    }

    public List<Comic> search(String keyword) {
        return comicRepo.findByTitleContainingIgnoreCase(keyword);
    }
}