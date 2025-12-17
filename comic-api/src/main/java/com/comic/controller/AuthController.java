package com.comic.controller;

import com.comic.config.JwtUtil;
import com.comic.model.AuthRequest;
import com.comic.model.AuthResponse;
import com.comic.service.MyUserDetailsService;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/auth")
@CrossOrigin(origins = "*") 
public class AuthController {

    private final JwtUtil jwtUtil;
    private final MyUserDetailsService userDetailsService;
    private final PasswordEncoder passwordEncoder;

    public AuthController(JwtUtil jwtUtil, MyUserDetailsService userDetailsService, PasswordEncoder passwordEncoder) {
        this.jwtUtil = jwtUtil;
        this.userDetailsService = userDetailsService;
        this.passwordEncoder = passwordEncoder;
    }

    @PostMapping("/login")
    public AuthResponse login(@RequestBody AuthRequest req) {
        // 1. Ambil User
        UserDetails user = userDetailsService.loadUserByUsername(req.getUsername());

        // 2. Cek Password (Tanpa Log aneh-aneh)
        if (!passwordEncoder.matches(req.getPassword(), user.getPassword())) {
            throw new RuntimeException("Password Salah!");
        }

        // 3. Generate Token
        String token = jwtUtil.generateToken(user.getUsername());
        return new AuthResponse(token);
    }
}