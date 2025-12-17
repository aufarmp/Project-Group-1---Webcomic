package com.comic.repository;

import com.comic.model.User;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;

public interface UserRepository extends JpaRepository<User, Long> {

    // 1. Cari berdasarkan Username (mirip EmpName di PDF)
    List<User> findByUsernameContainingIgnoreCase(String username);

    // 2. Cari berdasarkan Email (mirip EmpZipCode di PDF)
    List<User> findByEmailContainingIgnoreCase(String email);

    // 3. Cari berdasarkan keduanya
    List<User> findByUsernameContainingIgnoreCaseAndEmailContainingIgnoreCase(String username, String email);
}