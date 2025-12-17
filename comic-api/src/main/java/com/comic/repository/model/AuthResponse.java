package com.comic.model;

import lombok.AllArgsConstructor;
import lombok.Data;

@Data
@AllArgsConstructor // Biar bisa langsung new AuthResponse(token)
public class AuthResponse {
    private String token;
}