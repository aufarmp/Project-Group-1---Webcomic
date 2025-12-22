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
    public ResponseEntity<?> authenticateUser(@RequestBody LoginRequest loginRequest) {
        // ... (Logika autentikasi yang sudah ada) ...
        
        // Ambil UserDetails
        UserDetailsImpl userDetails = (UserDetailsImpl) authentication.getPrincipal();
        
        // Generate Token
        String jwt = jwtUtils.generateJwtToken(authentication);

        // Ambil Role (ambil role pertama saja)
        String role = userDetails.getAuthorities().stream()
                .findFirst().get().getAuthority();

        // RETURN JSON LENGKAP
        return ResponseEntity.ok(new AuthResponse(
                jwt, 
                userDetails.getId(), 
                role, 
                userDetails.getUsername()
        ));
    }
}