package com.comic.config;

import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.io.Decoders;
import io.jsonwebtoken.security.Keys;
import org.springframework.stereotype.Component;

import javax.crypto.SecretKey;
import java.util.Date;

@Component
public class JwtUtil {

    // 256-bit secret (BASE64)
    private static final String SECRET =
            "uA6ZlDzFf6FvO6YvWZ9yWv7mCS3uT4pQYJx2e7v8xWk=";

    private final SecretKey key =
            Keys.hmacShaKeyFor(Decoders.BASE64.decode(SECRET));

    // Generate token (dipakai di /auth/login)
    public String generateToken(String username) {
        long now = System.currentTimeMillis();
        return Jwts.builder()
                .subject(username)
                .issuedAt(new Date(now))
                .expiration(new Date(now + 1000 * 60 * 60)) // 1 jam
                .signWith(key)
                .compact();
    }

    // Validate token & ambil username
    public String validateToken(String token) {
        return Jwts.parser()
                .verifyWith(key)
                .build()
                .parseSignedClaims(token)
                .getPayload()
                .getSubject();
    }
}
