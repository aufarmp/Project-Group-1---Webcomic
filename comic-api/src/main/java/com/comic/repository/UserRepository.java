package com.comic.repository;

import com.comic.model.User;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;
import java.util.Optional;

public interface UserRepository extends JpaRepository<User, Long> {

    // --- 1: SEARCH --- 
    List<User> findByUsernameContainingIgnoreCase(String username);

    List<User> findByEmailContainingIgnoreCase(String email);

    List<User> findByUsernameContainingIgnoreCaseAndEmailContainingIgnoreCase(String username, String email);


    // --- 2: AUTHENTICATION ---
    Optional<User> findByUsername(String username);

    Boolean existsByUsername(String username);

    Boolean existsByEmail(String email);
}