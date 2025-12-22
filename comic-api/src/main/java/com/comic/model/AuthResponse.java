package com.comic.model;

import lombok.AllArgsConstructor;
import lombok.Data;

@Data
public class AuthResponse {
    private String accessToken;
    private String tokenType = "Bearer";
    private Long userId;
    private String role;
    private String username;

    // Constructor custom untuk mengisi semua data sekaligus
    public AuthResponse(String accessToken, Long userId, String role, String username) {
        this.accessToken = accessToken;
        this.userId = userId;
        this.role = role;
        this.username = username;
    }
}