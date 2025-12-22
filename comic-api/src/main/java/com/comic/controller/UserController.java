package com.comic.controller;

import com.comic.model.User;
import com.comic.service.UserService;
import org.springframework.web.bind.annotation.*;
import java.util.List;

@RestController
@RequestMapping("/api/users")
public class UserController {

    private final UserService service;

    public UserController(UserService service) {
        this.service = service;
    }

    // 1. Tambah User Baru
    @PostMapping
    public User create(@RequestBody User user) {
        return service.save(user);
    }

    // 2. Ambil List User (Bisa cari by username & email)
    // Contoh URL: /api/users?username=admin
    @GetMapping
    public List<User> list(
            @RequestParam(required = false) String username,
            @RequestParam(required = false) String email) {
        return service.search(username, email);
    }

    // 3. Detail User by ID
    @GetMapping("/{id}")
    public User detail(@PathVariable Long id) {
        return service.findById(id);
    }

    // 4. Update User by ID
    @PutMapping("/{id}")
    public User update(@PathVariable Long id, @RequestBody User requestUser) {
        // 1. Ambil data asli dari database (yang created_at nya masih ada)
        User dbUser = service.findById(id);

        // 2. Update field yang dikirim saja (Username, Email, Password)
        // Jika data dikirim di JSON, update. Jika tidak, biarkan data lama.
        if (requestUser.getUsername() != null) {
            dbUser.setUsername(requestUser.getUsername());
        }
        if (requestUser.getEmail() != null) {
            dbUser.setEmail(requestUser.getEmail());
        }
        if (requestUser.getPassword() != null) {
            dbUser.setPassword(requestUser.getPassword());
        }

        // 3. Simpan kembali data lama yang sudah diperbarui
        return service.save(dbUser);
    }

    // 5. Hapus User by ID
    @DeleteMapping("/{id}")
    public void delete(@PathVariable Long id) {
        service.delete(id);
    }
}