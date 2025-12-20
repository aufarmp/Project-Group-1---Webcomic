package com.comic.model;

import lombok.Data; // Pakai lombok biar singkat

@Data
public class AuthRequest {
    private String username;
    private String password;
}