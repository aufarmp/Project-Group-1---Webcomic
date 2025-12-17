package com.comic.service;

import com.comic.model.User;
import com.comic.repository.UserRepository;
import org.springframework.stereotype.Service;
import org.springframework.security.crypto.password.PasswordEncoder; // Tambahan biar aman
import java.util.List;

@Service
public class UserService {

    private final UserRepository repo;
    private final PasswordEncoder passwordEncoder; // Opsional: Untuk hash password user baru

    public UserService(UserRepository repo, PasswordEncoder passwordEncoder) {
        this.repo = repo;
        this.passwordEncoder = passwordEncoder;
    }

    public User save(User user) {
        return repo.save(user);
    }

    public List<User> findAll() {
        return repo.findAll();
    }

    public User findById(Long id) {
        return repo.findById(id).orElseThrow(() -> new RuntimeException("User not found"));
    }

    public void delete(Long id) {
        repo.deleteById(id);
    }

    // Logika Pencarian Adaptasi dari PDF
    public List<User> search(String username, String email) {
        if (username != null && email != null) {
            return repo.findByUsernameContainingIgnoreCaseAndEmailContainingIgnoreCase(username, email);
        }
        if (username != null) {
            return repo.findByUsernameContainingIgnoreCase(username);
        }
        if (email != null) {
            return repo.findByEmailContainingIgnoreCase(email);
        }
        return repo.findAll();
    }
}